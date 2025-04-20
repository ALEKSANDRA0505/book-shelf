<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('GET');
if (!isset($_GET['query'])) {
    send_json_response(400, ['error' => 'Missing search query parameter']);
}
$query = trim($_GET['query']);
if (empty($query)) {
    send_json_response(200, ['books' => [], 'writers' => [], 'genres' => []]);
}
$search_term = '%' . $query . '%';
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$results = [
    'books' => [],
    'writers' => [],
    'genres' => [],
    'authors' => []
];
$limit = 10;
try {
    $sql_books = "SELECT 
                    b.id_book, 
                    b.title, 
                    b.description, 
                    b.cover_image_url, 
                    AVG(r.rating) as average_rating, 
                    COUNT(r.id_review) as review_count 
                  FROM Book b 
                  LEFT JOIN Review r ON b.id_book = r.id_book 
                  WHERE b.title LIKE ?
                  GROUP BY b.id_book, b.title, b.description, b.cover_image_url 
                  ORDER BY b.title ASC 
                  LIMIT ?";
    $stmt_books = $conn->prepare($sql_books);
    if (!$stmt_books) throw new Exception("Prepare failed (books): " . $conn->error);
    $stmt_books->bind_param("si", $search_term, $limit);
    $stmt_books->execute();
    $result_books = $stmt_books->get_result();
    $results['books'] = $result_books->fetch_all(MYSQLI_ASSOC);
    $stmt_books->close();
    if (!empty($results['books'])) {
        $sql_writers_for_books = "SELECT w.id_writer, w.name
                                   FROM Writer w
                                   JOIN BookWriter bw ON w.id_writer = bw.id_writer
                                   WHERE bw.id_book = ?";
        $stmt_writers_fb = $conn->prepare($sql_writers_for_books);
        if (!$stmt_writers_fb) throw new Exception("Prepare failed (writers for books): " . $conn->error);
        
        foreach ($results['books'] as $key => $book) {
            $stmt_writers_fb->bind_param('i', $book['id_book']);
            $stmt_writers_fb->execute();
            $result_writers_fb = $stmt_writers_fb->get_result();
            $results['books'][$key]['writers'] = $result_writers_fb->fetch_all(MYSQLI_ASSOC);
        }
        $stmt_writers_fb->close();
    }
    $sql_writers = "SELECT id_writer, name, profile_picture_url FROM Writer WHERE name LIKE ? ORDER BY name ASC LIMIT ?";
    $stmt_writers = $conn->prepare($sql_writers);
    if (!$stmt_writers) throw new Exception("Prepare failed (writers): " . $conn->error);
    $stmt_writers->bind_param("si", $search_term, $limit);
    $stmt_writers->execute();
    $result_writers = $stmt_writers->get_result();
    $results['writers'] = $result_writers->fetch_all(MYSQLI_ASSOC);
    $stmt_writers->close();
    $sql_authors = "SELECT id_user, username, profile_picture_url 
                    FROM UserProfile 
                    WHERE username LIKE ? AND status = 'Автор'
                    ORDER BY username ASC 
                    LIMIT ?";
    $stmt_authors = $conn->prepare($sql_authors);
    if (!$stmt_authors) throw new Exception("Prepare failed (authors): " . $conn->error);
    $stmt_authors->bind_param("si", $search_term, $limit);
    $stmt_authors->execute();
    $result_authors = $stmt_authors->get_result();
    $results['authors'] = $result_authors->fetch_all(MYSQLI_ASSOC); 
    $stmt_authors->close();
    $sql_genres = "SELECT id_genre, name, slug FROM Genre WHERE name LIKE ? ORDER BY name ASC LIMIT ?";
    $stmt_genres = $conn->prepare($sql_genres);
    if (!$stmt_genres) throw new Exception("Prepare failed (genres): " . $conn->error);
    $stmt_genres->bind_param("si", $search_term, $limit);
    $stmt_genres->execute();
    $result_genres = $stmt_genres->get_result();
    $results['genres'] = $result_genres->fetch_all(MYSQLI_ASSOC);
    $stmt_genres->close();
} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    if ($conn) $conn->close();
    send_json_response(500, ['error' => 'An error occurred during search', 'details' => $e->getMessage()]);
}
$conn->close();
send_json_response(200, $results);
?> 