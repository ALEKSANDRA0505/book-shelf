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
    if ($data !== null) {
        $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, "Method Not Allowed.");
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
    $user_id = $decoded->data->userId;
} catch (ExpiredException $e) {
    send_json_response(401, "Token expired.");
} catch (SignatureInvalidException $e) {
    send_json_response(401, "Invalid token signature.");
} catch (Exception $e) {
    send_json_response(401, "Invalid token: " . $e->getMessage());
}
if (!$user_id) {
    send_json_response(401, "Could not extract user ID from token.");
}
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    $error_message = "No file uploaded or upload error.";
    if (isset($_FILES['avatar']['error'])) {
        switch ($_FILES['avatar']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message = "File is too large.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message = "File was only partially uploaded.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message = "No file was uploaded.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message = "Missing a temporary folder.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message = "Failed to write file to disk.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $error_message = "A PHP extension stopped the file upload.";
                break;
            default:
                $error_message = "Unknown upload error.";
                break;
        }
    }
    send_json_response(400, $error_message);
}
$file = $_FILES['avatar'];
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
$file_mime_type = mime_content_type($file['tmp_name']);
if (!in_array($file_mime_type, $allowed_mime_types)) {
    send_json_response(400, "Invalid file type. Only JPG, PNG, GIF are allowed.");
}
$max_file_size = 5 * 1024 * 1024;
if ($file['size'] > $max_file_size) {
    send_json_response(400, "File is too large. Maximum size is 5MB.");
}
$upload_dir_relative = '../uploads/avatars/';
$upload_dir_absolute = realpath(__DIR__ . '/..') . '/uploads/avatars/';
if (!is_dir($upload_dir_absolute)) {
    if (!mkdir($upload_dir_absolute, 0777, true)) {
        send_json_response(500, "Failed to create upload directory.");
    }
}
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$unique_filename = uniqid('avatar_' . $user_id . '_', true) . '.' . $file_extension;
$destination_path = $upload_dir_absolute . $unique_filename;
if (!move_uploaded_file($file['tmp_name'], $destination_path)) {
    send_json_response(500, "Failed to move uploaded file.");
}
$api_dir_url = dirname($_SERVER['SCRIPT_NAME']);
$base_url_path = dirname($api_dir_url);
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_absolute_url = $protocol . "://" . $host . ($base_url_path === '.' || $base_url_path === '/' ? '' : $base_url_path);
$file_url = $base_absolute_url . '/uploads/avatars/' . $unique_filename; 

$database = new Database();
$db = $database->getConnection();
$query = "UPDATE UserProfile SET profile_picture_url = :profile_picture_url WHERE id_user = :id_user";
$stmt = $db->prepare($query);
$stmt->bindParam(':profile_picture_url', $file_url);
$stmt->bindParam(':id_user', $user_id, PDO::PARAM_INT);
if ($stmt->execute()) {
    send_json_response(200, "Avatar updated successfully.", ['profile_picture_url' => $file_url]);
} else {
    send_json_response(500, "Failed to update user profile picture in database.");
}
?>