<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../includes/auth.php';
setup_cors();
check_request_method('POST');
$user = require_authentication();
$user_id = $user['id_user'];
$data = get_json_input();
if ($data === null) {
    send_json_response(400, ['error' => 'Invalid or empty JSON request body']);
}
if (!isset($data['id_book'])) {
    send_json_response(400, ['error' => 'Missing id_book in request body']);
}
$book_id = filter_var($data['id_book'], FILTER_VALIDATE_INT);
if ($book_id === false || $book_id <= 0) {
    send_json_response(400, ['error' => 'Invalid id_book']);
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$stmt_check_book = $conn->prepare("SELECT id_book FROM Book WHERE id_book = ?");
if ($stmt_check_book) {
    $stmt_check_book->bind_param('i', $book_id);
    $stmt_check_book->execute();
    $stmt_check_book->store_result();
    if ($stmt_check_book->num_rows === 0) {
        send_json_response(404, ['error' => 'Book not found']);
        $stmt_check_book->close();
        $conn->close();
        exit;
    }
    $stmt_check_book->close();
}
$sql = "INSERT IGNORE INTO WishlistItem (id_user, id_book) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
$stmt->bind_param('ii', $user_id, $book_id);
if ($stmt->execute()) {
    send_json_response(200, ['message' => 'Book added to wishlist or already exists', 'id_book' => $book_id]);
} else {
    send_json_response(500, ['error' => 'Failed to add book to wishlist: ' . $stmt->error]);
}
$stmt->close();
$conn->close();
?> 