<?php
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/auth.php';
setup_cors();
check_request_method('POST');
$user_payload = require_authentication(); 
$follower_id = $user_payload['id_user'] ?? null;
if (!$follower_id) {
    send_json_response(401, ['error' => 'Authentication required or user ID not found in token']);
}
$input = get_json_input();
$following_id = isset($input['id_following_user']) ? intval($input['id_following_user']) : null;
if (!$following_id || $following_id <= 0) {
    send_json_response(400, ['error' => 'Missing or invalid id_following_user']);
}
if ($follower_id === $following_id) {
    send_json_response(400, ['error' => 'Cannot subscribe to yourself']);
}
$conn = get_db_connection();
$stmt_check = $conn->prepare("SELECT id_subscription FROM UserSubscription WHERE id_follower_user = ? AND id_following_user = ?");
$stmt_check->bind_param("ii", $follower_id, $following_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows > 0) {
    $stmt_check->close();
    $conn->close();
    send_json_response(409, ['error' => 'Already subscribed']);
}
$stmt_check->close();
$stmt_insert = $conn->prepare("INSERT INTO UserSubscription (id_follower_user, id_following_user) VALUES (?, ?)");
if (!$stmt_insert) {
     send_json_response(500, ['error' => 'Failed to prepare insert statement: ' . $conn->error]);
}
$stmt_insert->bind_param("ii", $follower_id, $following_id);
if ($stmt_insert->execute()) {
    send_json_response(201, ['message' => 'Subscription added successfully']);
} else {
    send_json_response(500, ['error' => 'Failed to add subscription: ' . $stmt_insert->error]);
}
$stmt_insert->close();
$conn->close();
?> 