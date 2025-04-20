<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('GET');
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$writer_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$sql = "";
$stmt = null;
$fields = "id_writer, name, profile_picture_url"; 
if ($writer_id !== null && $writer_id > 0) {
    $sql = "SELECT $fields FROM Writer WHERE id_writer = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
    }
    $stmt->bind_param('i', $writer_id);
} else {
    $sql = "SELECT $fields FROM Writer ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
    }
}
if (!$stmt->execute()) {
    send_json_response(500, ['error' => 'Failed to execute query: ' . $stmt->error]);
}
$result = $stmt->get_result();
if ($writer_id !== null && $writer_id > 0) {
    $writer = $result->fetch_assoc();
    if ($writer) {
        /*
        if ($writer['profile_picture_url'] && strpos($writer['profile_picture_url'], '/') === 0) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $base_path = dirname(dirname($_SERVER['SCRIPT_NAME'])); 
            $writer['profile_picture_url'] = $protocol . "://" . $host . ($base_path === '/' ? '' : $base_path) . $writer['profile_picture_url'];
        }
        */
        send_json_response(200, $writer);
    } else {
        send_json_response(404, ['error' => 'Writer not found']);
    }
} else {
    $writers = $result->fetch_all(MYSQLI_ASSOC);
    /*
    foreach ($writers as &$writer) {
        if ($writer['profile_picture_url'] && strpos($writer['profile_picture_url'], '/') === 0) {
             $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
             $host = $_SERVER['HTTP_HOST'];
             $base_path = dirname(dirname($_SERVER['SCRIPT_NAME'])); 
             $writer['profile_picture_url'] = $protocol . "://" . $host . ($base_path === '/' ? '' : $base_path) . $writer['profile_picture_url'];
        }
    }
    unset($writer);
    */
    send_json_response(200, $writers);
}
$stmt->close();
$conn->close();
?> 