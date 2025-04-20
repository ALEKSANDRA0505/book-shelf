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
$conn = get_db_connection();
$sql = "SELECT 
            u.id_user, 
            u.username, 
            u.profile_picture_url
        FROM UserSubscription us
        JOIN UserProfile u ON us.id_following_user = u.id_user
        WHERE us.id_follower_user = ?
        ORDER BY u.username ASC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
$stmt->bind_param("i", $follower_id);
if (!$stmt->execute()) {
    send_json_response(500, ['error' => 'Failed to execute query: ' . $stmt->error]);
}
$result = $stmt->get_result();
$subscriptions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
send_json_response(200, $subscriptions);
?> 