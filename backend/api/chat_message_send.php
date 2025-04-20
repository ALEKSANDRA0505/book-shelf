<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
check_request_method('POST');
$data = get_json_input();
if (empty($data['id_user']) || !is_numeric($data['id_user']) || $data['id_user'] <= 0) {
    send_json_response(400, ['error' => 'Valid id_user is required.']);
}
if (empty($data['message_text'])) {
     send_json_response(400, ['error' => 'Message text cannot be empty.']);
}
$id_user = intval($data['id_user']);
$message_text = trim($data['message_text']);

$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}

$stmt_insert = $conn->prepare("INSERT INTO ChatMessage (id_sender, message_text) VALUES (?, ?)");
if (!$stmt_insert) {
    send_json_response(500, ['error' => 'Failed to prepare insert statement: ' . $conn->error]);
}
$stmt_insert->bind_param('is', $id_user, $message_text);
if ($stmt_insert->execute()) {
    $new_message_id = $conn->insert_id;
     send_json_response(201, [ 
        'message' => 'Message sent successfully', 
        'id_message' => $new_message_id,
        'id_user' => $id_user,
        'message_text' => $message_text,
    ]);
} else {
    if ($conn->errno === 1452) {
         send_json_response(400, ['error' => 'Invalid user ID provided.']);
    } else {
        send_json_response(500, ['error' => 'Failed to send message: ' . $stmt_insert->error]);
    }
}
$stmt_insert->close();
$conn->close();
?> 