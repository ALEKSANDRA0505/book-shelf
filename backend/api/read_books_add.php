<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
require_once '../config/database.php';
require_once '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
$secret_key = getenv('JWT_SECRET_KEY') ?: "8d3d8fa623320894928a3a092c442f6b0fbbb231319a6f08000e71fc723772fd8069c1e4fe99750d695a2685a031643c0dc09fb6278994b3703e9e58c9f87217d031cc3cca900ba6bcda95464c38a06cdfa233c82e38337afe92db9c3a72bb83dc8f35e2dca3b23e730c08f614f5220530b66cb466ebd8f0671b8bfb7f4e672207e4f7618e2ff645fb28a242851ba6c4496b270378f10f35429e97f8c8750e899468aa194c8cd40106656b39ec6564edf7571a21f271a2d9533f943941dd0c0a8c272199a51f61637735dd9294c53ac6bbd66c13424c901a40b4bab44bfa4ffda892e2cb3d596048106bf1b2b19b1a2b7432f611b8650d6512494f4a644fba76";
function send_json_response($code, $message, $data = null) {
    http_response_code($code);
    $response = ['message' => $message];
    if ($data !== null) $response = array_merge($response, $data);
    echo json_encode($response);
    exit();
}
$authHeader = null;
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) { 
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
} elseif (function_exists('getallheaders')) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
}
if (!$authHeader) {
    send_json_response(401, "Authorization header missing.");
}
list($jwt) = sscanf($authHeader, 'Bearer %s');
if (!$jwt) {
    send_json_response(401, "Invalid Authorization header format.");
}
$user_id = null;
try {
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    if (!isset($decoded->data) || !isset($decoded->data->id_user)) {
        throw new Exception('Invalid token payload (expected data->id_user)');
    }
    $user_id = $decoded->data->id_user;
} catch (ExpiredException $e) {
    send_json_response(401, "Token expired.");
} catch (SignatureInvalidException $e) {
    send_json_response(401, "Invalid token signature.");
} catch (Exception $e) {
    send_json_response(401, "Invalid token: " . $e->getMessage());
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, "Method Not Allowed.");
}
$authHeader = null;
if (isset($_SERVER['HTTP_AUTHORIZATION'])) { $authHeader = $_SERVER['HTTP_AUTHORIZATION']; }
if (!$user_id) {
    send_json_response(401, "Could not extract user ID from token.");
}

$data = json_decode(file_get_contents("php://input"));
if (!$data || !isset($data->title) || !isset($data->author)) {
    send_json_response(400, "Missing title or author in request body.");
}
$title = trim($data->title);
$author = trim($data->author);
if (empty($title) || empty($author)) {
    send_json_response(400, "Title and author cannot be empty.");
}
$database = new Database();
$db = $database->getConnection();
try {
    $db->beginTransaction();
    $query_insert = "INSERT INTO ReadBooks (id_user, title, author) VALUES (:id_user, :title, :author)";
    $stmt_insert = $db->prepare($query_insert);
    $stmt_insert->bindParam(':id_user', $user_id, PDO::PARAM_INT);
    $stmt_insert->bindParam(':title', $title);
    $stmt_insert->bindParam(':author', $author);
    if (!$stmt_insert->execute()) {
        throw new Exception("Failed to insert read book.");
    }
    $last_id = $db->lastInsertId();
    $query_update = "UPDATE UserProfile SET read_books_count = read_books_count + 1 WHERE id_user = :id_user";
    $stmt_update = $db->prepare($query_update);
    $stmt_update->bindParam(':id_user', $user_id, PDO::PARAM_INT);
    if (!$stmt_update->execute()) {
        throw new Exception("Failed to update read books count.");
    }
    $db->commit();
    send_json_response(201, "Book added successfully.", [
        'id_read_book' => $last_id,
        'id_user' => $user_id,
        'title' => $title,
        'author' => $author
    ]);
} catch (Exception $e) {
    $db->rollBack();
    error_log("Error adding read book: " . $e->getMessage());
    send_json_response(500, "Failed to add book. " . $e->getMessage());
}
?>