<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
setup_cors();
check_request_method('POST'); 
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
if (!$auth_header || !preg_match('/^Bearer\s+(.*?)$/', $auth_header, $matches)) {
    send_json_response(401, ['error' => 'Authorization header missing or invalid']);
}
$jwt = $matches[1];
$admin_user_id = null;
$is_admin = false;
$conn = null;
try {
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET_KEY, 'HS256'));
    if (!isset($decoded->data) || !isset($decoded->data->id_user)) { 
        throw new Exception('Invalid token payload structure', 401);
    }
    $admin_user_id = intval($decoded->data->id_user);
    $conn = get_db_connection();
    if (!$conn) {
        throw new Exception('Database connection failed', 500);
    }
    $stmt_check_admin = $conn->prepare("SELECT status FROM UserProfile WHERE id_user = ?");
    if (!$stmt_check_admin) throw new Exception('Failed to prepare admin check', 500);
    $stmt_check_admin->bind_param("i", $admin_user_id);
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
    if ($conn) $conn->close(); 
    send_json_response($code, ['error' => 'Authentication failed', 'details' => $e->getMessage()]);
}
if (!$is_admin) {
    if ($conn) $conn->close();
    send_json_response(403, ['error' => 'Forbidden: Administrator access required.']);
}
$data = get_json_input();

if (!isset($data['username']) || empty(trim($data['username']))) {
    if ($conn) $conn->close();
    send_json_response(400, ['error' => 'Username is required.']);
}
if (!isset($data['email']) || empty(trim($data['email']))) {
    if ($conn) $conn->close();
    send_json_response(400, ['error' => 'Email is required.']);
}
if (!isset($data['password']) || empty(trim($data['password']))) {
    if ($conn) $conn->close();
    send_json_response(400, ['error' => 'Password is required.']);
}
if (!isset($data['status']) || empty(trim($data['status']))) {
    if ($conn) $conn->close();
    send_json_response(400, ['error' => 'Status is required.']);
}
$username = trim($data['username']);
$email = trim($data['email']);
$password = trim($data['password']);
$status = trim($data['status']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    if ($conn) $conn->close();
    send_json_response(400, ['error' => 'Invalid email format.']);
}
if (strlen($password) < 6) {
    if ($conn) $conn->close();
    send_json_response(400, ['error' => 'Password must be at least 6 characters long.']);
}
$allowed_statuses = ['Читатель', 'Автор', 'Админ'];
if (!in_array($status, $allowed_statuses)) {
    if ($conn) $conn->close();
    send_json_response(400, ['error' => 'Invalid status value. Allowed values: ' . implode(', ', $allowed_statuses)]);
}
$profile_picture_url = isset($data['profile_picture_url']) && !empty(trim($data['profile_picture_url'])) ? trim($data['profile_picture_url']) : null;
$age = isset($data['age']) && is_numeric($data['age']) && $data['age'] >= 0 ? intval($data['age']) : null;
$city = isset($data['city']) && !empty(trim($data['city'])) ? trim($data['city']) : null;
$about_me = isset($data['about_me']) && !empty(trim($data['about_me'])) ? trim($data['about_me']) : null;
$reading_goal = isset($data['reading_goal']) && is_numeric($data['reading_goal']) && $data['reading_goal'] >= 0 ? intval($data['reading_goal']) : 0;
if ($city === '0' || $city === 0) {
    $city = null;
}
if ($profile_picture_url && !filter_var($profile_picture_url, FILTER_VALIDATE_URL)) {
    if (!preg_match('/^\/assets\/(avatars|img)\/.*\.(jpg|jpeg|png|gif)$/i', $profile_picture_url)) {
       if (isset($conn) && $conn) $conn->close();
       send_json_response(400, ['error' => 'Invalid profile picture URL format.']);
    }
}
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO UserProfile (username, email, password_hash, status, profile_picture_url, age, city, about_me, reading_goal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
try {
    $stmt->bind_param("sssssiisi", 
        $username, 
        $email, 
        $password_hash, 
        $status, 
        $profile_picture_url,
        $age,
        $city,
        $about_me,
        $reading_goal
    );
    if ($stmt->execute()) {
        $new_user_id = $conn->insert_id;
        send_json_response(201, ['message' => 'User created successfully', 'id_user' => $new_user_id]);
    } else {
        if ($conn->errno === 1062) { 
            $error_field = 'unknown';
            if (str_contains($stmt->error, 'username')) $error_field = 'username';
            elseif (str_contains($stmt->error, 'email')) $error_field = 'email';
             send_json_response(409, ['error' => ucfirst($error_field) . ' already exists.']);
        } else {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
    }
} catch (Exception $e) {
    error_log("Admin User Create error: " . $e->getMessage());
     send_json_response(500, ['error' => 'Failed to create user profile']);
} finally {
    if ($stmt) {
        $stmt->close();
    }
    if ($conn) {
        $conn->close();
    }
}
?> 