<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('GET');
if (!isset($_GET['genre_id'])) {
    send_json_response(400, ['error' => 'Missing genre_id parameter']);
}
$genre_id = intval($_GET['genre_id']);
if ($genre_id <= 0) {
    send_json_response(400, ['error' => 'Invalid genre_id']);
}
$exclude_book_id = isset($_GET['exclude_book_id']) ? intval($_GET['exclude_book_id']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if ($limit <= 0) {
    $limit = 10;
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$sql = "SELECT DISTINCT b.id_book, b.title, b.description, b.cover_image_url
        FROM Book b
        JOIN BookGenre bg ON b.id_book = bg.id_book
        WHERE bg.id_genre = ?";
$params = [$genre_id];
$types = 'i';
if ($exclude_book_id > 0) {
    $sql .= " AND b.id_book != ?";
    $params[] = $exclude_book_id;
    $types .= 'i';
}
$sql .= " ORDER BY RAND() LIMIT ?";
$params[] = $limit;
$types .= 'i';
$stmt = $conn->prepare($sql);
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) {
    send_json_response(500, ['error' => 'Failed to execute query: ' . $stmt->error]);
}
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
send_json_response(200, $books);
?> 