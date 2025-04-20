<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('GET');
$title = isset($_GET['title']) ? trim($_GET['title']) : null;
$author = isset($_GET['author']) ? trim($_GET['author']) : null;
if (empty($title) || empty($author)) {
    send_json_response(400, ['error' => 'Missing required parameters: title, author']);
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
try {
    $sql = "SELECT b.id_book
            FROM Book b
            JOIN BookWriter bw ON b.id_book = bw.id_book
            JOIN Writer w ON bw.id_writer = w.id_writer
            WHERE b.title = ? AND w.name = ?
            LIMIT 1";
            
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    
    $stmt->bind_param("ss", $title, $author);
    
    if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
    
    $result = $stmt->get_result();
    
    $exists = $result->num_rows > 0;
    
    send_json_response(200, ['exists' => $exists]);
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Check book exists error: " . $e->getMessage());
    send_json_response(500, ['error' => 'An error occurred while checking book existence.']);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?> 