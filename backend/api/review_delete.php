<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['error' => 'Method Not Allowed. Expected DELETE or POST']);
}
$id_review = null;
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id_review = intval($_GET['id']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = get_json_input();
    if (isset($data['id_review']) && is_numeric($data['id_review'])) {
        $id_review = intval($data['id_review']);
    }
}
if ($id_review === null || $id_review <= 0) {
    send_json_response(400, ['error' => 'Review ID is required and must be a positive integer']);
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}


$stmt = $conn->prepare("DELETE FROM Review WHERE id_review = ?");
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare delete statement: ' . $conn->error]);
}
$stmt->bind_param('i', $id_review);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        send_json_response(200, ['message' => 'Review deleted successfully', 'id_review' => $id_review]);
    } else {
        send_json_response(404, ['error' => 'Review not found']);
    }
} else {
    send_json_response(500, ['error' => 'Failed to delete review: ' . $stmt->error]);
}
$stmt->close();
$conn->close();
?> 