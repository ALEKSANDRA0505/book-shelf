<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('POST');
$data = get_json_input();
$required_fields = ['subscriber_user_id', 'target_user_id'];
foreach ($required_fields as $field) {
    if (empty($data[$field]) || !is_numeric($data[$field]) || $data[$field] <= 0) {
        send_json_response(400, ['error' => "Valid {$field} is required."]);
    }
}
$subscriber_user_id = intval($data['subscriber_user_id']);
$target_user_id = intval($data['target_user_id']);
if ($subscriber_user_id === $target_user_id) {
    send_json_response(400, ['error' => 'Cannot subscribe to yourself.']);
}

$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$stmt_check = $conn->prepare("SELECT id_subscription FROM UserSubscription WHERE subscriber_user_id = ? AND target_user_id = ?");
if (!$stmt_check) {
    send_json_response(500, ['error' => 'Failed to prepare check statement: ' . $conn->error]);
}
$stmt_check->bind_param('ii', $subscriber_user_id, $target_user_id);
if (!$stmt_check->execute()) {
     send_json_response(500, ['error' => 'Failed to execute check query: ' . $stmt_check->error]);
}
$stmt_check->store_result(); 
if ($stmt_check->num_rows > 0) {
    send_json_response(409, ['error' => 'Subscription already exists.']);
}
$stmt_check->close();
$stmt_insert = $conn->prepare("INSERT INTO UserSubscription (subscriber_user_id, target_user_id) VALUES (?, ?)");
if (!$stmt_insert) {
    send_json_response(500, ['error' => 'Failed to prepare insert statement: ' . $conn->error]);
}
$stmt_insert->bind_param('ii', $subscriber_user_id, $target_user_id);
if ($stmt_insert->execute()) {
    $new_subscription_id = $conn->insert_id;
    send_json_response(201, [
        'message' => 'Subscription created successfully', 
        'id_subscription' => $new_subscription_id,
        'subscriber_user_id' => $subscriber_user_id,
        'target_user_id' => $target_user_id
    ]);
} else {
    if ($conn->errno === 1452) {
         send_json_response(400, ['error' => 'Invalid subscriber or target user ID provided.']);
    } else {
        send_json_response(500, ['error' => 'Failed to create subscription: ' . $stmt_insert->error]);
    }
}
$stmt_insert->close();
$conn->close();
?> 