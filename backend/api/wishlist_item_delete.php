<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['error' => 'Method Not Allowed. Expected DELETE or POST']);
}
$id_user = null;
$id_book = null;
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (isset($_GET['user_id']) && is_numeric($_GET['user_id']) && $_GET['user_id'] > 0) {
        $id_user = intval($_GET['user_id']);
    }
    if (isset($_GET['book_id']) && is_numeric($_GET['book_id']) && $_GET['book_id'] > 0) {
        $id_book = intval($_GET['book_id']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = get_json_input();
    if (isset($data['id_user']) && is_numeric($data['id_user']) && $data['id_user'] > 0) {
        $id_user = intval($data['id_user']);
    }
     if (isset($data['id_book']) && is_numeric($data['id_book']) && $data['id_book'] > 0) {
        $id_book = intval($data['id_book']);
    }
}
if ($id_user === null || $id_book === null) {
    send_json_response(400, ['error' => 'Both valid user_id and book_id are required.']);
}

$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$stmt = $conn->prepare("DELETE FROM WishlistItem WHERE id_user = ? AND id_book = ?");
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare delete statement: ' . $conn->error]);
}
$stmt->bind_param('ii', $id_user, $id_book);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        send_json_response(200, ['message' => 'Item removed from wishlist successfully', 'id_user' => $id_user, 'id_book' => $id_book]);
    } else {
        send_json_response(404, ['error' => 'Item not found in wishlist for this user.']);
    }
} else {
    send_json_response(500, ['error' => 'Failed to remove item from wishlist: ' . $stmt->error]);
}
$stmt->close();
$conn->close();
?> 