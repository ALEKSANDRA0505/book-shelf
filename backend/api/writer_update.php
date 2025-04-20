<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
setup_cors();
$conn = null;
try {
    $user_data = verify_jwt_token();
    if (!$user_data || !isset($user_data['id_user'])) {
        send_json_response(401, ['error' => 'Unauthorized: Invalid token']);
        exit;
    }
    $id_user_requesting = $user_data['id_user'];
    $conn = get_db_connection();
    if (!$conn) {
        send_json_response(500, ['error' => 'Database connection failed']);
        exit;
    }
    $stmt_status = $conn->prepare("SELECT status FROM UserProfile WHERE id_user = ?");
    if (!$stmt_status) throw new Exception('Failed to prepare status statement: ' . $conn->error);
    $stmt_status->bind_param('i', $id_user_requesting);
    if (!$stmt_status->execute()) {
        $stmt_status->close();
        throw new Exception('Failed to execute status statement: ' . $stmt_status->error);
    }
    $result_status = $stmt_status->get_result();
    if ($result_status->num_rows === 0) {
        $stmt_status->close();
        send_json_response(404, ['error' => 'User not found']);
        exit;
    }
    $user_status = $result_status->fetch_assoc()['status'];
    $stmt_status->close();
    if ($user_status !== 'Админ') {
        send_json_response(403, ['error' => 'Forbidden: Admin rights required']);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        send_json_response(405, ['error' => 'Method Not Allowed. Use PUT or POST.']);
        exit;
    }
    $data = get_json_input();
    if (empty($data['id_writer']) || !is_numeric($data['id_writer']) || $data['id_writer'] <= 0) {
        send_json_response(400, ['error' => 'Valid Writer ID is required']);
        exit;
    }
    if (empty($data['name'])) {
        send_json_response(400, ['error' => 'Writer name is required']);
        exit;
    }
    $id_writer = intval($data['id_writer']);
    $name = trim($data['name']);
    $biography = isset($data['biography']) ? trim($data['biography']) : null;
    $photo_url = isset($data['photo_url']) ? trim($data['photo_url']) : null;
    if ($photo_url !== null && !filter_var($photo_url, FILTER_VALIDATE_URL) && !empty($photo_url)) {
         if (!preg_match('/^\/assets\/img\/.*\.(jpg|jpeg|png|gif)$/i', $photo_url)) {
            send_json_response(400, ['error' => 'Invalid photo URL format. Must be a valid URL or a path like /assets/img/your_image.jpg']);
            exit;
        }
    }
    $stmt = $conn->prepare("UPDATE Writer SET name = ?, biography = ?, photo_url = ? WHERE id_writer = ?");
    if (!$stmt) throw new Exception('Failed to prepare statement: ' . $conn->error);
    $stmt->bind_param('sssi', $name, $biography, $photo_url, $id_writer);
    if (!$stmt->execute()) {
        $err_no = $conn->errno;
        $err_msg = $stmt->error;
        $stmt->close();
        if ($err_no === 1062) {
            send_json_response(409, ['error' => 'Writer name already exists']);
        } else {
            throw new Exception('Failed to update writer: ' . $err_msg);
        }
        exit;
    }
    if ($stmt->affected_rows > 0) {
        send_json_response(200, ['message' => 'Writer updated successfully', 'id_writer' => $id_writer]);
    } else {
        send_json_response(200, ['message' => 'No changes made to the writer data', 'id_writer' => $id_writer]);
    }
    $stmt->close();
} catch (Exception $e) {
    $errorCode = 500;
    $errorMessage = 'An internal server error occurred: ' . $e->getMessage();
    if (strpos($e->getMessage(), 'Unauthorized') !== false) {
        $errorCode = 401;
        $errorMessage = $e->getMessage();
    } elseif (strpos($e->getMessage(), 'Forbidden') !== false) {
        $errorCode = 403;
        $errorMessage = $e->getMessage();
    }
    send_json_response($errorCode, ['error' => $errorMessage]);
} finally {
    if ($conn !== null && $conn->ping()) {
        $conn->close();
    }
}
?> 