<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('GET');
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$popular_only = isset($_GET['popular']) && $_GET['popular'] === 'true';
$sql_books = "SELECT 
                b.id_book, 
                b.title, 
                b.description, 
                b.cover_image_url, 
                AVG(r.rating) as average_rating, -- Вычисляем средний рейтинг
                COUNT(r.id_review) as review_count -- Считаем количество рецензий (опционально)
              FROM Book b 
              LEFT JOIN Review r ON b.id_book = r.id_book -- LEFT JOIN чтобы книги без рейтинга тоже попали
              GROUP BY b.id_book, b.title, b.description, b.cover_image_url";
if ($popular_only) {
    $sql_books .= " ORDER BY AVG(r.rating) DESC, COUNT(r.id_review) DESC LIMIT 10";
} else {
    $sql_books .= " ORDER BY b.title ASC";
}
$stmt_books = $conn->prepare($sql_books);
if (!$stmt_books) {
    send_json_response(500, ['error' => 'Failed to prepare books statement: ' . $conn->error]);
    $conn->close();
    exit;
}
if (!$stmt_books->execute()) {
    send_json_response(500, ['error' => 'Failed to execute books query: ' . $stmt_books->error]);
    $stmt_books->close();
    $conn->close();
    exit;
}
$result_books = $stmt_books->get_result();
$all_books = $result_books->fetch_all(MYSQLI_ASSOC);
$stmt_books->close();
$sql_writers = "SELECT w.id_writer, w.name
                FROM Writer w
                JOIN BookWriter bw ON w.id_writer = bw.id_writer
                WHERE bw.id_book = ?";
$stmt_writers = $conn->prepare($sql_writers);
if (!$stmt_writers) {
    send_json_response(500, ['error' => 'Failed to prepare writers statement: ' . $conn->error]);
    $conn->close();
    exit;
}
$sql_genres = "SELECT g.id_genre, g.name
               FROM Genre g
               JOIN BookGenre bg ON g.id_genre = bg.id_genre
               WHERE bg.id_book = ?";
$stmt_genres = $conn->prepare($sql_genres);
if (!$stmt_genres) {
    send_json_response(500, ['error' => 'Failed to prepare genres statement: ' . $conn->error]);
    $stmt_writers->close();
    $conn->close();
    exit;
}
foreach ($all_books as $key => $book) {
    $book_id = $book['id_book'];
    $current_writers = [];
    $stmt_writers->bind_param('i', $book_id);
    if (!$stmt_writers->execute()) {
        error_log("Failed to execute writers query for book ID {$book_id}: " . $stmt_writers->error);
        $all_books[$key]['writers'] = [];
    } else {
        $result_writers = $stmt_writers->get_result();
        $writers_data = $result_writers->fetch_all(MYSQLI_ASSOC);
        $all_books[$key]['writers'] = $writers_data;
        foreach ($writers_data as $writer) {
            $current_writers[] = $writer['name'];
        }
    }
    $all_books[$key]['author_string'] = implode(', ', $current_writers);
    $stmt_genres->bind_param('i', $book_id);
    if (!$stmt_genres->execute()) {
        error_log("Failed to execute genres query for book ID {$book_id}: " . $stmt_genres->error);
        $all_books[$key]['genres'] = [];
    } else {
        $result_genres = $stmt_genres->get_result();
        $all_books[$key]['genres'] = $result_genres->fetch_all(MYSQLI_ASSOC);
    }
}
$stmt_writers->close();
$stmt_genres->close();
$conn->close();
send_json_response(200, $all_books);
?> 