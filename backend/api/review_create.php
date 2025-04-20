<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('POST');
$data = get_json_input();
$required_fields = ['id_user', 'id_book', 'rating'];
foreach ($required_fields as $field) {
    if (empty($data[$field]) || !is_numeric($data[$field]) || $data[$field] <= 0) {
        send_json_response(400, ['error' => "Valid {$field} is required."]);
    }
}
$id_user = intval($data['id_user']);
$id_book = intval($data['id_book']);
$rating = intval($data['rating']);
$comment = isset($data['comment']) ? trim($data['comment']) : null;
if ($rating < 1 || $rating > 5) {
    send_json_response(400, ['error' => 'Rating must be between 1 and 5.']);
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$stmt_check = $conn->prepare("SELECT id_review FROM Review WHERE id_user = ? AND id_book = ?");
if (!$stmt_check) {
    send_json_response(500, ['error' => 'Failed to prepare check statement: ' . $conn->error]);
}
$stmt_check->bind_param('ii', $id_user, $id_book);
if (!$stmt_check->execute()) {
     send_json_response(500, ['error' => 'Failed to execute check query: ' . $stmt_check->error]);
}
$stmt_check->store_result();
if ($stmt_check->num_rows > 0) {
    send_json_response(409, ['error' => 'User has already reviewed this book.']);
}
$stmt_check->close();
$stmt_insert = $conn->prepare("INSERT INTO Review (id_user, id_book, rating, review_text) VALUES (?, ?, ?, ?)");
if (!$stmt_insert) {
    send_json_response(500, ['error' => 'Failed to prepare insert statement: ' . $conn->error]);
}
$stmt_insert->bind_param('iiis', $id_user, $id_book, $rating, $comment);
if ($stmt_insert->execute()) {
    $new_review_id = $conn->insert_id;
    send_json_response(201, ['message' => 'Review created successfully', 'id_review' => $new_review_id]);
} else {
    if ($conn->errno === 1452) {
         send_json_response(400, ['error' => 'Invalid user ID or book ID provided.']);
    } else {
        send_json_response(500, ['error' => 'Failed to create review: ' . $stmt_insert->error]);
    }
}
$stmt_insert->close();
$conn->close();
?> 