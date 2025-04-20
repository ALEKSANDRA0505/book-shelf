<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);    error_reporting(E_ALL);
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('GET');
if (!isset($_GET['id'])) {
    send_json_response(400, ['error' => 'Missing book id parameter']);
    ob_end_flush();
    exit;
}
$book_id = intval($_GET['id']);
if ($book_id <= 0) {
    send_json_response(400, ['error' => 'Invalid book id parameter']);
    ob_end_flush();
    exit;
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
    ob_end_flush();
    exit;
}
$sql_book = "SELECT id_book, title, description, cover_image_url FROM Book WHERE id_book = ?";
$stmt_book = $conn->prepare($sql_book);
if (!$stmt_book) {
    send_json_response(500, ['error' => 'Failed to prepare book statement: ' . $conn->error]);
    $conn->close();
    ob_end_flush();
    exit;
}
$stmt_book->bind_param('i', $book_id);
if (!$stmt_book->execute()) {
    send_json_response(500, ['error' => 'Failed to execute book query: ' . $stmt_book->error]);
    $stmt_book->close();
    $conn->close();
    ob_end_flush();
    exit;
}
$result_book = $stmt_book->get_result();
$book = $result_book->fetch_assoc();
$stmt_book->close();
if (!$book) {
    send_json_response(404, ['error' => 'Book not found']);
    $conn->close();
    ob_end_flush();
    exit;
}
$sql_writers = "SELECT w.id_writer, w.name
                FROM Writer w
                JOIN BookWriter bw ON w.id_writer = bw.id_writer
                WHERE bw.id_book = ?";
$stmt_writers = $conn->prepare($sql_writers);
if (!$stmt_writers) {
    send_json_response(500, ['error' => 'Failed to prepare writers statement: ' . $conn->error]);
    $conn->close();
    ob_end_flush();
    exit;
}
$stmt_writers->bind_param('i', $book_id);
if (!$stmt_writers->execute()) {
    send_json_response(500, ['error' => 'Failed to execute writers query: ' . $stmt_writers->error]);
    $stmt_writers->close();
    $conn->close();
    ob_end_flush();
    exit;
}
$result_writers = $stmt_writers->get_result();
$writers = $result_writers->fetch_all(MYSQLI_ASSOC);
$stmt_writers->close();
$book['writers'] = $writers;
$sql_genres = "SELECT g.id_genre, g.name
               FROM Genre g
               JOIN BookGenre bg ON g.id_genre = bg.id_genre
               WHERE bg.id_book = ?";
$stmt_genres = $conn->prepare($sql_genres);
if (!$stmt_genres) {
    send_json_response(500, ['error' => 'Failed to prepare genres statement: ' . $conn->error]);
    $conn->close();
    ob_end_flush();
    exit;
}
$stmt_genres->bind_param('i', $book_id);
if (!$stmt_genres->execute()) {
    send_json_response(500, ['error' => 'Failed to execute genres query: ' . $stmt_genres->error]);
    $conn->close();
    ob_end_flush();
    exit;
}
$result_genres = $stmt_genres->get_result();
$genres = $result_genres->fetch_all(MYSQLI_ASSOC);
$stmt_genres->close();
$book['genres'] = $genres; 
$conn->close();
send_json_response(200, $book);
ob_end_flush();
?> 