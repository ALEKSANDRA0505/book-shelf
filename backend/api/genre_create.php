<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('POST');
$data = get_json_input();
if (empty($data['name'])) {
    send_json_response(400, ['error' => 'Genre name is required']);
}
$name = trim($data['name']);
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$slug = generate_slug($name, $conn);
$stmt = $conn->prepare("INSERT INTO Genre (name, slug) VALUES (?, ?)");
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
    $conn->close();
    exit;
}
$stmt->bind_param('ss', $name, $slug);
if ($stmt->execute()) {
    $new_genre_id = $conn->insert_id;
    send_json_response(201, ['message' => 'Genre created successfully', 'id_genre' => $new_genre_id, 'slug' => $slug]);
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
        send_json_response(500, ['error' => 'Failed to create genre: ' . $stmt->error]);
    }
}
$stmt->close();
$conn->close();
?> 