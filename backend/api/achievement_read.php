<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('GET');
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$achievement_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$sql = "";
$stmt = null;
$fields = "id_achievement, name, description, icon_url";
if ($achievement_id !== null && $achievement_id > 0) {
    $sql = "SELECT $fields FROM Achievement WHERE id_achievement = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
    }
    $stmt->bind_param('i', $achievement_id);
} else {
    $sql = "SELECT $fields FROM Achievement ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
    }
}
if (!$stmt->execute()) {
    send_json_response(500, ['error' => 'Failed to execute query: ' . $stmt->error]);
}
$result = $stmt->get_result();
if ($achievement_id !== null && $achievement_id > 0) {
    $achievement = $result->fetch_assoc();
    if ($achievement) {
        send_json_response(200, $achievement);
    } else {
        send_json_response(404, ['error' => 'Achievement not found']);
    }
} else {
    $achievements = $result->fetch_all(MYSQLI_ASSOC);
    send_json_response(200, $achievements);
}
$stmt->close();
$conn->close();
?> 