<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
setup_cors();
check_request_method('GET');

$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$writer_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$sql = "SELECT id_writer, name, profile_picture_url FROM Writer";
$params = [];
$types = "";
if ($writer_id !== null && $writer_id > 0) {
    $sql .= " WHERE id_writer = ?";
    $params[] = $writer_id;
    $types .= "i";
} else {
    $sql .= " ORDER BY name ASC";
}
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
if ($writer_id !== null && $writer_id > 0) {
    $writer = $result->fetch_assoc();
    if ($writer) {
        send_json_response(200, $writer);
    } else {
        send_json_response(404, ['error' => 'Writer not found']);
    }
} else {
    $writers = $result->fetch_all(MYSQLI_ASSOC);
    send_json_response(200, $writers);
}
$stmt->close();
$conn->close();
?> 