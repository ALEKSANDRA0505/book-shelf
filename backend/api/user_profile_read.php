<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('GET');
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$user_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$sql = "";
$stmt = null;
$fields = "id_user, username, email, profile_picture_url"; 
if ($user_id !== null && $user_id > 0) {
    $sql = "SELECT $fields FROM UserProfile WHERE id_user = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
    }
    $stmt->bind_param('i', $user_id);
} else {
    $sql = "SELECT $fields FROM UserProfile ORDER BY username ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
    }
}
if (!$stmt->execute()) {
    send_json_response(500, ['error' => 'Failed to execute query: ' . $stmt->error]);
}
$result = $stmt->get_result();
if ($user_id !== null && $user_id > 0) {
    $user = $result->fetch_assoc();
    if ($user) {
        send_json_response(200, $user);
    } else {
        send_json_response(404, ['error' => 'User not found']);
    }
} else {
    $users = $result->fetch_all(MYSQLI_ASSOC);
    send_json_response(200, $users);
}
$stmt->close();
$conn->close();
?> 