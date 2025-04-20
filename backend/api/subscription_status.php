<?php
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/auth.php';
setup_cors();
check_request_method('GET');
$user_payload = require_authentication();
$follower_id = $user_payload['id_user'] ?? null;
if (!$follower_id) {
    send_json_response(401, ['error' => 'Authentication required or user ID not found in token']);
}
$following_id = isset($_GET['id_following_user']) ? intval($_GET['id_following_user']) : null;
if (!$following_id || $following_id <= 0) {
    send_json_response(400, ['error' => 'Missing or invalid id_following_user parameter']);
}
$conn = get_db_connection();
$stmt = $conn->prepare("SELECT id_subscription FROM UserSubscription WHERE id_follower_user = ? AND id_following_user = ?");
if (!$stmt) {
     send_json_response(500, ['error' => 'Failed to prepare select statement: ' . $conn->error]);
}
$stmt->bind_param("ii", $follower_id, $following_id);
if (!$stmt->execute()) {
    send_json_response(500, ['error' => 'Failed to execute select query: ' . $stmt->error]);
}
$result = $stmt->get_result();
$isSubscribed = $result->num_rows > 0;
$stmt->close();
$conn->close();
send_json_response(200, ['isSubscribed' => $isSubscribed]);
?> 