<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('GET');
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? intval($_GET['limit']) : 50;
if ($limit <= 0 || $limit > 200) {
    $limit = 50;
}
$after_id = isset($_GET['after_id']) && is_numeric($_GET['after_id']) ? intval($_GET['after_id']) : null;
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$sql = "
    SELECT 
        cm.id_message, 
        cm.id_sender AS id_user,
        up.username AS user_username, 
        up.profile_picture_url AS user_profile_picture_url,
        cm.message_text, 
        cm.sent_at
    FROM 
        ChatMessage cm
    JOIN 
        UserProfile up ON cm.id_sender = up.id_user
";
$params = [];
$types = "";
if ($after_id !== null && $after_id > 0) {
    $sql .= " WHERE cm.id_message > ?";
    $params[] = $after_id;
    $types .= "i";
    $sql .= " ORDER BY cm.id_message ASC"; 
} else {
    $sql .= " ORDER BY cm.id_message DESC";
}
$sql .= " LIMIT ?";
$params[] = $limit;
$types .= "i";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
if (!empty($params)) {
     $stmt->bind_param($types, ...$params);
}
if (!$stmt->execute()) {
    send_json_response(500, ['error' => 'Failed to execute query: ' . $stmt->error]);
}
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
if ($after_id === null) {
    $messages = array_reverse($messages);
}
send_json_response(200, $messages);
$stmt->close();
$conn->close();
?> 