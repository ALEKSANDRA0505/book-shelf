<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../includes/jwt_handler.php';
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
    if (!$stmt_status) {
        throw new Exception('Failed to prepare status statement: ' . $conn->error);
    }
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

    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        send_json_response(405, ['error' => 'Method Not Allowed. Use DELETE or POST with id_writer.']);
        exit;
    }
    $id_writer = null;
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (isset($_GET['id_writer'])) {
            $id_writer = filter_input(INPUT_GET, 'id_writer', FILTER_VALIDATE_INT);
        }
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['id_writer'])) {
            $id_writer = filter_var($input['id_writer'], FILTER_VALIDATE_INT);
        }
    }
    if ($id_writer === null || $id_writer === false || $id_writer <= 0) {
        send_json_response(400, ['error' => 'Writer ID is required and must be a positive integer']);
        exit;
    }
    $stmt_bookwriter = $conn->prepare("DELETE FROM BookWriter WHERE id_writer = ?");
    if (!$stmt_bookwriter) {
        throw new Exception('Failed to prepare BookWriter delete statement: ' . $conn->error);
    }
    $stmt_bookwriter->bind_param('i', $id_writer);
    if (!$stmt_bookwriter->execute()) {
        $stmt_bookwriter->close();
        throw new Exception('Failed to delete related BookWriter entries: ' . $stmt_bookwriter->error);
    }
    $stmt_bookwriter->close();
    $stmt = $conn->prepare("DELETE FROM Writer WHERE id_writer = ?");
    if (!$stmt) {
        throw new Exception('Failed to prepare Writer delete statement: ' . $conn->error);
    }
    $stmt->bind_param('i', $id_writer);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('Failed to delete writer: ' . $stmt->error);
    }
    if ($stmt->affected_rows > 0) {
        send_json_response(200, ['message' => 'Writer deleted successfully', 'id_writer' => $id_writer]);
    } else {
        send_json_response(404, ['error' => 'Writer not found or already deleted']);
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