<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['error' => 'Method Not Allowed. Expected DELETE or POST']);
}
$id_genre = null;
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id_genre = intval($_GET['id']);
    } 
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = get_json_input();
    if (isset($data['id_genre']) && is_numeric($data['id_genre'])) {
        $id_genre = intval($data['id_genre']);
    }
}
if ($id_genre === null || $id_genre <= 0) {
    send_json_response(400, ['error' => 'Genre ID is required and must be a positive integer']);
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$stmt = $conn->prepare("DELETE FROM Genre WHERE id_genre = ?");
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
$stmt->bind_param('i', $id_genre);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        send_json_response(200, ['message' => 'Genre deleted successfully', 'id_genre' => $id_genre]);
    } else {
        send_json_response(404, ['error' => 'Genre not found']);
    }
} else {
    send_json_response(500, ['error' => 'Failed to delete genre: ' . $stmt->error]);
}
$stmt->close();
$conn->close();
?> 