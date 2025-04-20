<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('POST');
$data = get_json_input();
if (empty($data['name'])) {
    send_json_response(400, ['error' => 'Achievement name is required']);
}
$name = trim($data['name']);
$description = isset($data['description']) ? trim($data['description']) : null;
$icon_url = isset($data['icon_url']) ? trim($data['icon_url']) : null;
if ($icon_url !== null && !filter_var($icon_url, FILTER_VALIDATE_URL) && !empty($icon_url)) {
     if (!preg_match('/^\/assets\/(icons|img)\/.*\.(jpg|jpeg|png|gif|svg)$/i', $icon_url)) {
        send_json_response(400, ['error' => 'Invalid icon URL format. Must be a valid URL or a path like /assets/icons/your_icon.svg']);
    }
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$stmt = $conn->prepare("INSERT INTO Achievement (name, description, icon_url) VALUES (?, ?, ?)");
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
$stmt->bind_param('sss', $name, $description, $icon_url);
if ($stmt->execute()) {
    $new_achievement_id = $conn->insert_id;
    send_json_response(201, ['message' => 'Achievement created successfully', 'id_achievement' => $new_achievement_id]);
} else {
    if ($conn->errno === 1062) {
        send_json_response(409, ['error' => 'Achievement name already exists']);
    } else {
        send_json_response(500, ['error' => 'Failed to create achievement: ' . $stmt->error]);
    }
}
$stmt->close();
$conn->close();
?> 