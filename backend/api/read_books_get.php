<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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
function send_json_response($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}
$authHeader = null;
if (isset($_SERVER['HTTP_AUTHORIZATION'])) { $authHeader = $_SERVER['HTTP_AUTHORIZATION']; }
elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) { 
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
} elseif (function_exists('getallheaders')) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
}
if (!$authHeader) {
    send_json_response(401, ["message" => "Authorization header missing."]);
}
list($jwt) = sscanf($authHeader, 'Bearer %s');
if (!$jwt) {
    send_json_response(401, ["message" => "Invalid Authorization header format."]);
}
$user_id = null;
try {
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    if (!isset($decoded->data) || !isset($decoded->data->id_user)) {
        throw new Exception('Invalid token payload (expected data->id_user)');
    }
    $user_id = $decoded->data->id_user;
} catch (ExpiredException $e) {
    send_json_response(401, ["message" => "Token expired."]);
} catch (SignatureInvalidException $e) {
    send_json_response(401, ["message" => "Invalid token signature."]);
} catch (Exception $e) {
    send_json_response(401, ["message" => "Invalid token: " . $e->getMessage()]);
}
if (!$user_id) {
    send_json_response(401, ["message" => "Could not extract user ID from token."]);
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json_response(405, ["message" => "Method Not Allowed."]);
}
$database = new Database();
$db = $database->getConnection();
try {
    $query = "SELECT id_read_book, title, author, added_at FROM ReadBooks WHERE id_user = :id_user ORDER BY added_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_user', $user_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        send_json_response(200, $books ?: []);
    } else {
        send_json_response(500, ["message" => "Failed to fetch read books."]);
    }
} catch (Exception $e) {
    error_log("Error fetching read books: " . $e->getMessage());
    send_json_response(500, ["message" => "Failed to fetch read books. " . $e->getMessage()]);
}
?>