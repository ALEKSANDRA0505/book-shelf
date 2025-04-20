<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../includes/auth.php';
setup_cors();
check_request_method('GET');
$user = require_authentication();
$user_id = $user['id_user'];
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}

$sql = "SELECT b.id_book, b.title, b.description, b.cover_image_url
        FROM Book b
        JOIN WishlistItem wi ON b.id_book = wi.id_book
        WHERE wi.id_user = ?
        ORDER BY b.title ASC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
$stmt->bind_param('i', $user_id);
if (!$stmt->execute()) {
    send_json_response(500, ['error' => 'Failed to execute query: ' . $stmt->error]);
}
$result = $stmt->get_result();
$wishlist_books = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
send_json_response(200, $wishlist_books);
?> 