<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../includes/auth.php';
setup_cors();
check_request_method('DELETE');
$user = require_authentication();
$user_id = $user['id_user'];
if (!isset($_GET['id_book'])) {
    send_json_response(400, ['error' => 'Missing id_book parameter']);
}
$book_id = filter_var($_GET['id_book'], FILTER_VALIDATE_INT);
if ($book_id === false || $book_id <= 0) {
    send_json_response(400, ['error' => 'Invalid id_book']);
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$sql = "DELETE FROM WishlistItem WHERE id_user = ? AND id_book = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
$stmt->bind_param('ii', $user_id, $book_id);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        send_json_response(200, ['message' => 'Book removed from wishlist', 'id_book' => $book_id]);
    } else {
        send_json_response(404, ['message' => 'Book not found in wishlist', 'id_book' => $book_id]); 
    }
} else {
    send_json_response(500, ['error' => 'Failed to remove book from wishlist: ' . $stmt->error]);
}
$stmt->close();
$conn->close();
?> 