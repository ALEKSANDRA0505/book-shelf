<?php
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../includes/db_connection.php';
setup_cors();
check_request_method('GET');
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$author_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$sql = "SELECT id_user, username, profile_picture_url, age, city, status, about_me 
        FROM UserProfile";
$params = [];
$types = "";
$where_clauses = [];
$where_clauses[] = "status = 'Автор'"; 
if ($author_id !== null && $author_id > 0) {
    $where_clauses[] = "id_user = ?";
    $params[] = $author_id;
    $types .= "i";
}
$sql .= " WHERE " . implode(" AND ", $where_clauses);
$sql .= " ORDER BY username ASC";
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
if ($author_id !== null && $author_id > 0) {
    $author = $result->fetch_assoc();
    if ($author) {
        send_json_response(200, $author);
    } else {
        send_json_response(404, ['error' => 'Author (User) not found']);
    }
} else {
    $authors = $result->fetch_all(MYSQLI_ASSOC);
    send_json_response(200, $authors);
}
$stmt->close();
$conn->close();
?> 