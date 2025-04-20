<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['error' => 'Method Not Allowed. Expected PUT or POST']);
}
$data = get_json_input();
if (empty($data['id_review']) || !is_numeric($data['id_review']) || $data['id_review'] <= 0) {
    send_json_response(400, ['error' => 'Valid Review ID is required']);
}
if (!isset($data['rating']) || !is_numeric($data['rating'])) {
     send_json_response(400, ['error' => 'Rating is required and must be numeric']);
}
$id_review = intval($data['id_review']);
$rating = intval($data['rating']);
$comment = isset($data['comment']) ? trim($data['comment']) : null;
if ($rating < 1 || $rating > 5) {
    send_json_response(400, ['error' => 'Rating must be between 1 and 5.']);
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}



$stmt_update = $conn->prepare("UPDATE Review SET rating = ?, comment = ? WHERE id_review = ?");
if (!$stmt_update) {
    send_json_response(500, ['error' => 'Failed to prepare update statement: ' . $conn->error]);
}
$stmt_update->bind_param('isi', $rating, $comment, $id_review);
if ($stmt_update->execute()) {
    if ($stmt_update->affected_rows > 0) {
        send_json_response(200, ['message' => 'Review updated successfully', 'id_review' => $id_review]);
    } else {
        $stmt_check = $conn->prepare("SELECT id_review FROM Review WHERE id_review = ?");
        $stmt_check->bind_param('i', $id_review);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows === 0) {
            send_json_response(404, ['error' => 'Review not found']);
        } else {
            send_json_response(200, ['message' => 'Review found, but no changes were made', 'id_review' => $id_review]);
        }
        $stmt_check->close();
    }
} else {
    send_json_response(500, ['error' => 'Failed to update review: ' . $stmt_update->error]);
}
$stmt_update->close();
$conn->close();
?> 