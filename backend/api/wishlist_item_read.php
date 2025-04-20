<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('GET');
if (empty($_GET['user_id']) || !is_numeric($_GET['user_id']) || $_GET['user_id'] <= 0) {
    send_json_response(400, ['error' => 'Valid user_id parameter is required.']);
}
$id_user = intval($_GET['user_id']);

$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$sql = "
    SELECT 
        w.id_wishlist_item, 
        w.id_user, 
        w.id_book, 
        w.added_at,
        b.title AS book_title, 
        b.description AS book_description, 
        b.cover_image_url AS book_cover_image_url
        -- Можно добавить информацию об авторах и жанрах книги, если нужно (усложнит запрос)
    FROM 
        WishlistItem w
    JOIN 
        Book b ON w.id_book = b.id_book
    WHERE 
        w.id_user = ?
    ORDER BY 
        w.added_at DESC -- Сначала последние добавленные
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
$stmt->bind_param('i', $id_user);
if (!$stmt->execute()) {
    send_json_response(500, ['error' => 'Failed to execute query: ' . $stmt->error]);
}
$result = $stmt->get_result();
$wishlist_items = $result->fetch_all(MYSQLI_ASSOC);
send_json_response(200, $wishlist_items);
$stmt->close();
$conn->close();
?> 