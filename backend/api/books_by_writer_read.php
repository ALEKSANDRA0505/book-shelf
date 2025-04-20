<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('GET');
if (!isset($_GET['writer_id'])) {
    send_json_response(400, ['error' => 'Missing writer_id parameter']);
}
$writer_id = intval($_GET['writer_id']);
if ($writer_id <= 0) {
    send_json_response(400, ['error' => 'Invalid writer_id parameter']);
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$sql = "SELECT b.id_book, b.title, b.description, b.cover_image_url
        FROM Book b
        JOIN BookWriter bw ON b.id_book = bw.id_book
        WHERE bw.id_writer = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
$stmt->bind_param('i', $writer_id);
if (!$stmt->execute()) {
    send_json_response(500, ['error' => 'Failed to execute query: ' . $stmt->error]);
}
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
send_json_response(200, $books);
?> 