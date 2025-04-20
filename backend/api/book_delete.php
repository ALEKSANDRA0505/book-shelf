<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
setup_cors();
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['error' => 'Method Not Allowed. Expected DELETE or POST']);
}
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
$id_book = null;
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id_book = intval($_GET['id']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = get_json_input();
    if (isset($data['id_book']) && is_numeric($data['id_book'])) {
        $id_book = intval($data['id_book']);
    }
}
if ($id_book === null || $id_book <= 0) {
    if ($conn_check) $conn_check->close();
    send_json_response(400, ['error' => 'Book ID is required and must be a positive integer']);
}
$conn = $conn_check ?? get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$conn->begin_transaction();
try {
    $stmt_bg = $conn->prepare("DELETE FROM BookGenre WHERE id_book = ?");
    if (!$stmt_bg) throw new Exception('Prep del BookGenre failed: ' . $conn->error);
    $stmt_bg->bind_param('i', $id_book);
    if (!$stmt_bg->execute()) throw new Exception('Exec del BookGenre failed: ' . $stmt_bg->error);
    $stmt_bg->close();
    $stmt_bw = $conn->prepare("DELETE FROM BookWriter WHERE id_book = ?");
    if (!$stmt_bw) throw new Exception('Prep del BookWriter failed: ' . $conn->error);
    $stmt_bw->bind_param('i', $id_book);
    if (!$stmt_bw->execute()) throw new Exception('Exec del BookWriter failed: ' . $stmt_bw->error);
    $stmt_bw->close();
    $stmt_review = $conn->prepare("DELETE FROM Review WHERE id_book = ?");
    if (!$stmt_review) throw new Exception('Prep del Review failed: ' . $conn->error);
    $stmt_review->bind_param('i', $id_book);
    if (!$stmt_review->execute()) throw new Exception('Exec del Review failed: ' . $stmt_review->error);
    $stmt_review->close();
    $stmt_wish = $conn->prepare("DELETE FROM WishlistItem WHERE id_book = ?");
    if (!$stmt_wish) throw new Exception('Prep del WishlistItem failed: ' . $conn->error);
    $stmt_wish->bind_param('i', $id_book);
    if (!$stmt_wish->execute()) throw new Exception('Exec del WishlistItem failed: ' . $stmt_wish->error);
    $stmt_wish->close();
    $stmt_book = $conn->prepare("DELETE FROM Book WHERE id_book = ?");
    if (!$stmt_book) throw new Exception('Prep del Book failed: ' . $conn->error);
    $stmt_book->bind_param('i', $id_book);
    if (!$stmt_book->execute()) throw new Exception('Exec del Book failed: ' . $stmt_book->error);
    $affected_rows = $stmt_book->affected_rows;
    $stmt_book->close();
    if ($affected_rows > 0) {
        $conn->commit();
        send_json_response(200, ['message' => 'Book deleted successfully', 'id_book' => $id_book]);
    } else {
        $conn->rollback();
        send_json_response(404, ['error' => 'Book not found']);
    }
} catch (Exception $e) {
    $conn->rollback();
    send_json_response(500, ['error' => 'Failed to delete book: ' . $e->getMessage()]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?> 