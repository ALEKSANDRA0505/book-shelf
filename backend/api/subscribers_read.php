<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('GET');
if (empty($_GET['user_id']) || !is_numeric($_GET['user_id']) || $_GET['user_id'] <= 0) {
    send_json_response(400, ['error' => 'Valid user_id parameter (target user) is required.']);
}
$target_user_id = intval($_GET['user_id']);
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$sql = "
    SELECT 
        us.id_subscription,
        us.subscriber_user_id AS id_user, -- Возвращаем ID подписчика
        up.username, 
        up.profile_picture_url,
        us.subscription_date
    FROM 
        UserSubscription us
    JOIN 
        UserProfile up ON us.subscriber_user_id = up.id_user
    WHERE 
        us.target_user_id = ?
    ORDER BY 
        up.username ASC -- Сортируем по имени подписчика
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
$stmt->bind_param('i', $target_user_id);
if (!$stmt->execute()) {
    send_json_response(500, ['error' => 'Failed to execute query: ' . $stmt->error]);
}
$result = $stmt->get_result();
$subscribers = $result->fetch_all(MYSQLI_ASSOC);
send_json_response(200, $subscribers);
$stmt->close();
$conn->close();
?> 