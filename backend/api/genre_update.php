<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['error' => 'Method Not Allowed. Expected PUT or POST']);
}
$data = get_json_input();
if (empty($data['id_genre']) || !is_numeric($data['id_genre'])) {
    send_json_response(400, ['error' => 'Genre ID is required and must be numeric']);
}
if (empty($data['name'])) {
    send_json_response(400, ['error' => 'Genre name is required']);
}
$id_genre = intval($data['id_genre']);
$name = trim($data['name']);
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$slug = generate_slug($name, $conn, 'Genre', 'slug', $id_genre);
$stmt = $conn->prepare("UPDATE Genre SET name = ?, slug = ? WHERE id_genre = ?");
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
    $conn->close();
    exit;
}
$stmt->bind_param('ssi', $name, $slug, $id_genre);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        send_json_response(200, ['message' => 'Genre updated successfully', 'id_genre' => $id_genre, 'slug' => $slug]);
    } else {
        send_json_response(404, ['error' => 'Genre not found or no changes made']);
    }
} else {
    if ($conn->errno === 1062) {
        if (strpos($conn->error, '.name') !== false) {
             send_json_response(409, ['error' => 'Genre name already exists']);
        } elseif (strpos($conn->error, '.slug') !== false) {
            send_json_response(409, ['error' => 'Generated slug conflict. Please try a slightly different name.']);
        } else {
             send_json_response(409, ['error' => 'Duplicate entry violation.']);
        }
    } else {
        send_json_response(500, ['error' => 'Failed to update genre: ' . $stmt->error]);
    }
}
$stmt->close();
$conn->close();
?> 