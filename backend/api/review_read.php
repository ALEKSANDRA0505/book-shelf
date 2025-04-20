<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('GET');
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$review_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : null;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$sql = "
    SELECT 
        r.id_review, 
        r.id_user, 
        u.username AS user_username, -- Алиас для имени пользователя
        u.profile_picture_url,      -- Добавляем URL аватара пользователя
        r.id_book, 
        b.title AS book_title,       -- Алиас для названия книги
        r.rating, 
        r.review_text,
        r.created_at
    FROM 
        Review r
    JOIN 
        UserProfile u ON r.id_user = u.id_user
    JOIN 
        Book b ON r.id_book = b.id_book
";
$params = [];
$types = "";
$where_clauses = [];
if ($review_id !== null && $review_id > 0) {
    $where_clauses[] = "r.id_review = ?";
    $params[] = $review_id;
    $types .= "i";
} elseif ($book_id !== null && $book_id > 0) {
    $where_clauses[] = "r.id_book = ?";
    $params[] = $book_id;
    $types .= "i";
} elseif ($user_id !== null && $user_id > 0) {
    $where_clauses[] = "r.id_user = ?";
    $params[] = $user_id;
    $types .= "i";
}
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql .= " ORDER BY r.created_at DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
if (!$stmt->execute()) {
    send_json_response(500, ['error' => 'Failed to execute query: ' . $stmt->error]);
}
$result = $stmt->get_result();
if ($review_id !== null && $review_id > 0) {
    $review = $result->fetch_assoc();
    if ($review) {
        send_json_response(200, $review);
    } else {
        send_json_response(404, ['error' => 'Review not found']);
    }
} else {
    $reviews = $result->fetch_all(MYSQLI_ASSOC);
    send_json_response(200, $reviews);
}
$stmt->close();
$conn->close();
?> 