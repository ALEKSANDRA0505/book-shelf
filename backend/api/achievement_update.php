<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['error' => 'Method Not Allowed. Expected PUT or POST']);
}
$data = get_json_input();
if (empty($data['id_achievement']) || !is_numeric($data['id_achievement']) || $data['id_achievement'] <= 0) {
    send_json_response(400, ['error' => 'Valid Achievement ID is required']);
}
if (empty($data['name'])) {
    send_json_response(400, ['error' => 'Achievement name is required']);
}
$id_achievement = intval($data['id_achievement']);
$name = trim($data['name']);
$description = isset($data['description']) ? trim($data['description']) : null;
$icon_url = isset($data['icon_url']) ? trim($data['icon_url']) : null;
if ($icon_url !== null && !filter_var($icon_url, FILTER_VALIDATE_URL) && !empty($icon_url)) {
    if (!preg_match('/^\/assets\/(icons|img)\/.*\.(jpg|jpeg|png|gif|svg)$/i', $icon_url)) {
        send_json_response(400, ['error' => 'Invalid icon URL format']);
    }
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$stmt = $conn->prepare("UPDATE Achievement SET name = ?, description = ?, icon_url = ? WHERE id_achievement = ?");
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
$stmt->bind_param('sssi', $name, $description, $icon_url, $id_achievement);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        send_json_response(200, ['message' => 'Achievement updated successfully', 'id_achievement' => $id_achievement]);
    } else {
        $stmt_check = $conn->prepare("SELECT id_achievement FROM Achievement WHERE id_achievement = ?");
        $stmt_check->bind_param('i', $id_achievement);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows === 0) {
            send_json_response(404, ['error' => 'Achievement not found']);
        } else {
            send_json_response(200, ['message' => 'Achievement found, but no changes were made', 'id_achievement' => $id_achievement]);
        }
         $stmt_check->close();
    }
} else {
    if ($conn->errno === 1062) {
        send_json_response(409, ['error' => 'Achievement name already exists']);
    } else {
        send_json_response(500, ['error' => 'Failed to update achievement: ' . $stmt->error]);
    }
}
$stmt->close();
$conn->close();
?> 