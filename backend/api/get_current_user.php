<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
setup_cors();
check_request_method('GET');
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
if (!$auth_header) {
    send_json_response(401, ['error' => 'Authorization header not found']);
}
if (!preg_match('/^Bearer\s+(.*?)$/', $auth_header, $matches)) {
    send_json_response(401, ['error' => 'Invalid Authorization header format']);
}
$jwt = $matches[1];
if (!$jwt) {
    send_json_response(401, ['error' => 'No token provided']);
}
try {
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET_KEY, 'HS256'));
    error_log("Decoded JWT payload: " . print_r($decoded, true));
    if (!isset($decoded->data) || !isset($decoded->data->id_user)) {
        error_log("Invalid token payload structure: 'data->id_user' not found.");
        throw new Exception('Invalid token payload');
    }
    $user_id = $decoded->data->id_user;
    error_log("Extracted user ID: " . $user_id);
    try {
        $conn = get_db_connection();
        if (!$conn) {
            error_log("Database connection failed in get_current_user.");
            send_json_response(500, ['error' => 'Database connection failed']);
        }
        $stmt = $conn->prepare("SELECT id_user, username, email, profile_picture_url, age, city, status, about_me, reading_goal, read_books_count FROM UserProfile WHERE id_user = ?");
        if (!$stmt) throw new Exception("Prepare failed (get user): " . $conn->error);
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user_profile = $result->fetch_assoc();
            send_json_response(200, $user_profile);
        } else {
             error_log("User with ID {$user_id} not found in DB.");
            send_json_response(404, ['error' => 'User not found']);
        }
        $stmt->close();
        $conn->close();
    } catch (Exception $dbException) {
        error_log("Database error in get_current_user: " . $dbException->getMessage());
        send_json_response(500, ['error' => 'Database query failed']); 
    }
} catch (ExpiredException $e) {
    send_json_response(401, ['error' => 'Token has expired', 'details' => $e->getMessage()]);
} catch (SignatureInvalidException $e) {
    send_json_response(401, ['error' => 'Invalid token signature', 'details' => $e->getMessage()]);
} catch (BeforeValidException $e) {
    send_json_response(401, ['error' => 'Token not yet valid', 'details' => $e->getMessage()]);
} catch (Exception $e) {
    error_log("Get current user error: " . $e->getMessage());
    send_json_response(401, ['error' => 'Invalid token', 'details' => $e->getMessage()]);
}
?> 