<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('GET');
$genre_id = null;
$genre_slug = null;
if (isset($_GET['id'])) {
    $genre_id = intval($_GET['id']);
    if ($genre_id <= 0) {
        send_json_response(400, ['error' => 'Invalid genre ID parameter']);
    }
} elseif (isset($_GET['slug'])) {
    $genre_slug = trim($_GET['slug']);
    if (empty($genre_slug)) {
        send_json_response(400, ['error' => 'Genre slug parameter cannot be empty']);
    }
} else {
    send_json_response(400, ['error' => 'Missing genre ID or slug parameter']);
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$sql = "SELECT id_genre, name, slug FROM Genre WHERE ";
$params = [];
$types = "";
if ($genre_id !== null) {
    $sql .= "id_genre = ?";
    $params[] = $genre_id;
    $types = "i";
} else {
    $sql .= "slug = ?";
    $params[] = $genre_slug;
    $types = "s";
}
$stmt = $conn->prepare($sql);
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
    $conn->close();
    exit;
}
$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) {
    send_json_response(500, ['error' => 'Failed to execute query: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}
$result = $stmt->get_result();
$genre = $result->fetch_assoc();
$stmt->close();
$conn->close();
if ($genre) {
    send_json_response(200, $genre);
} else {
    send_json_response(404, ['error' => 'Genre not found']);
}
?> 