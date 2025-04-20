<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['error' => 'Method Not Allowed. Expected DELETE or POST']);
}
$subscriber_user_id = null;
$target_user_id = null;
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (isset($_GET['subscriber_user_id']) && is_numeric($_GET['subscriber_user_id'])) {
        $subscriber_user_id = intval($_GET['subscriber_user_id']);
    }
    if (isset($_GET['target_user_id']) && is_numeric($_GET['target_user_id'])) {
        $target_user_id = intval($_GET['target_user_id']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = get_json_input();
    if (isset($data['subscriber_user_id']) && is_numeric($data['subscriber_user_id'])) {
        $subscriber_user_id = intval($data['subscriber_user_id']);
    }
     if (isset($data['target_user_id']) && is_numeric($data['target_user_id'])) {
        $target_user_id = intval($data['target_user_id']);
    }
}
if ($subscriber_user_id === null || $subscriber_user_id <=0 || $target_user_id === null || $target_user_id <= 0) {
    send_json_response(400, ['error' => 'Both valid subscriber_user_id and target_user_id are required.']);
}

$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$stmt = $conn->prepare("DELETE FROM UserSubscription WHERE subscriber_user_id = ? AND target_user_id = ?");
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare delete statement: ' . $conn->error]);
}
$stmt->bind_param('ii', $subscriber_user_id, $target_user_id);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        send_json_response(200, [
            'message' => 'Unsubscribed successfully', 
            'subscriber_user_id' => $subscriber_user_id, 
            'target_user_id' => $target_user_id
        ]);
    } else {
        send_json_response(404, ['error' => 'Subscription not found.']);
    }
} else {
    send_json_response(500, ['error' => 'Failed to unsubscribe: ' . $stmt->error]);
}
$stmt->close();
$conn->close();
?> 