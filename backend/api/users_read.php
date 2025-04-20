<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../config/database.php'; 
require_once __DIR__ . '/../vendor/autoload.php'; 
setup_cors(); 
check_request_method('GET');
$user_id = null;
$is_admin = false;
try {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$auth_header || !preg_match('/^Bearer\s+(.*?)$/', $auth_header, $matches)) {
        throw new Exception('Authorization header missing or invalid', 401);
    }
    $jwt = $matches[1];
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET_KEY, 'HS256'));
    
    if (!isset($decoded->data) || !isset($decoded->data->id_user)) {
        throw new Exception('Invalid token payload structure', 401);
    }
    $user_id = $decoded->data->id_user;
    $conn = get_db_connection();
    if (!$conn) {
        throw new Exception('Database connection failed', 500);
    }
    $stmt_check_admin = $conn->prepare("SELECT status FROM UserProfile WHERE id_user = ?");
    if (!$stmt_check_admin) throw new Exception('Failed to prepare admin check', 500);
    $stmt_check_admin->bind_param("i", $user_id);
    $stmt_check_admin->execute();
    $result_admin = $stmt_check_admin->get_result();
    if ($admin_data = $result_admin->fetch_assoc()) {
        if ($admin_data['status'] === 'Админ') {
            $is_admin = true;
        }
    }
    $stmt_check_admin->close();
} catch (Exception $e) {
    $code = $e->getCode() ?: 401;
    send_json_response($code, ['error' => 'Authentication failed', 'details' => $e->getMessage()]);
}
if (!$is_admin) {
    if (isset($conn) && $conn) $conn->close();
    send_json_response(403, ['error' => 'Forbidden: Administrator access required.']);
}
try {
    $sql = "SELECT id_user, username, email, status, city FROM UserProfile ORDER BY id_user ASC";
    $result = $conn->query($sql);
    if ($result === false) {
        throw new Exception("Failed to fetch users: " . $conn->error, 500);
    }
    
    $users = $result->fetch_all(MYSQLI_ASSOC);
    send_json_response(200, $users);
} catch (Exception $e) {
    error_log("Admin users read error: " . $e->getMessage());
    $code = $e->getCode() ?: 500;
    send_json_response($code, ['error' => 'Failed to fetch users', 'details' => $e->getMessage()]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?> 