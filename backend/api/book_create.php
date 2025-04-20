<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
setup_cors();
check_request_method('POST');
$admin_user_id = null; 
$is_admin = false;
$conn_check = null; 
try {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$auth_header || !preg_match('/^Bearer\s+(.*?)$/', $auth_header, $matches)) {
        throw new Exception('Authorization header missing or invalid', 401);
    }
    $jwt = $matches[1];
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET_KEY, 'HS256'));
    
    if (!isset($decoded->data) || !isset($decoded->data->id_user)) {
        throw new Exception('Invalid token payload structure', 401);
    }
    $admin_user_id = $decoded->data->id_user;
    $conn_check = get_db_connection(); 
    if (!$conn_check) {
        throw new Exception('Database connection failed for admin check', 500);
    }
    $stmt_check_admin = $conn_check->prepare("SELECT status FROM UserProfile WHERE id_user = ?");
    if (!$stmt_check_admin) throw new Exception('Failed to prepare admin check', 500);
    $stmt_check_admin->bind_param("i", $admin_user_id);
    $stmt_check_admin->execute();
    $result_admin = $stmt_check_admin->get_result();
    if ($admin_data = $result_admin->fetch_assoc()) {
        if ($admin_data['status'] === 'Админ') {
            $is_admin = true;
        }
    }
    $stmt_check_admin->close();
} catch (Exception $e) {
    if ($conn_check) $conn_check->close();
    $code = $e->getCode() ?: 401;
    send_json_response($code, ['error' => 'Authentication failed', 'details' => $e->getMessage()]);
}
if (!$is_admin) {
    if ($conn_check) $conn_check->close();
    send_json_response(403, ['error' => 'Forbidden: Administrator access required.']);
}
$data = get_json_input();
if (empty($data['title'])) {
    send_json_response(400, ['error' => 'Book title is required']);
}
$title = trim($data['title']);
$description = isset($data['description']) ? trim($data['description']) : null;
$cover_image_url = isset($data['cover_image_url']) ? trim($data['cover_image_url']) : null;
$genre_ids = isset($data['genre_ids']) && is_array($data['genre_ids']) ? $data['genre_ids'] : [];
$writer_ids = isset($data['writer_ids']) && is_array($data['writer_ids']) ? $data['writer_ids'] : [];
if ($cover_image_url !== null && !filter_var($cover_image_url, FILTER_VALIDATE_URL) && !empty($cover_image_url)) {
     if (!preg_match('/^\/assets\/img\/.*\.(jpg|jpeg|png|gif)$/i', $cover_image_url)) {
        send_json_response(400, ['error' => 'Invalid cover image URL format. Must be a valid URL or a path like /assets/img/your_image.jpg']);
    }
}
$validated_genre_ids = [];
foreach ($genre_ids as $id) {
    if (!is_numeric($id) || $id <= 0) {
        send_json_response(400, ['error' => 'Invalid genre ID provided: ' . $id]);
    }
    $validated_genre_ids[] = intval($id);
}
$validated_writer_ids = [];
foreach ($writer_ids as $id) {
    if (!is_numeric($id) || $id <= 0) {
        send_json_response(400, ['error' => 'Invalid writer ID provided: ' . $id]);
    }
    $validated_writer_ids[] = intval($id);
}
$conn = $conn_check ?? get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$conn->begin_transaction();
try {
    $stmt_book = $conn->prepare("INSERT INTO Book (title, description, cover_image_url) VALUES (?, ?, ?)");
    if (!$stmt_book) {
        throw new Exception('Failed to prepare book statement: ' . $conn->error);
    }
    $stmt_book->bind_param('sss', $title, $description, $cover_image_url);
    if (!$stmt_book->execute()) {
        throw new Exception('Failed to insert book: ' . $stmt_book->error);
    }
    $new_book_id = $conn->insert_id;
    $stmt_book->close();
    if (!empty($validated_genre_ids)) {
        $sql_genre = "INSERT INTO BookGenre (id_book, id_genre) VALUES (?, ?)";
        $stmt_genre = $conn->prepare($sql_genre);
        if (!$stmt_genre) {
            throw new Exception('Failed to prepare genre statement: ' . $conn->error);
        }
        foreach ($validated_genre_ids as $genre_id) {
            $stmt_genre->bind_param('ii', $new_book_id, $genre_id);
            if (!$stmt_genre->execute()) {
                throw new Exception('Failed to insert book genre link: ' . $stmt_genre->error);
            }
        }
        $stmt_genre->close();
    }
    if (!empty($validated_writer_ids)) {
        $sql_writer = "INSERT INTO BookWriter (id_book, id_writer) VALUES (?, ?)";
        $stmt_writer = $conn->prepare($sql_writer);
        if (!$stmt_writer) {
            throw new Exception('Failed to prepare writer statement: ' . $conn->error);
        }
        foreach ($validated_writer_ids as $writer_id) {
            $stmt_writer->bind_param('ii', $new_book_id, $writer_id);
            if (!$stmt_writer->execute()) {
                throw new Exception('Failed to insert book writer link: ' . $stmt_writer->error);
            }
        }
        $stmt_writer->close();
    }
    $conn->commit();
    send_json_response(201, ['message' => 'Book created successfully', 'id_book' => $new_book_id]);
} catch (Exception $e) {
    $conn->rollback();
    send_json_response(500, ['error' => 'Failed to create book: ' . $e->getMessage()]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?> 