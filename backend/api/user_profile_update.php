<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
setup_cors();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['error' => 'Method Not Allowed. Expected PUT or POST']);
}

$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
if (!$auth_header || !preg_match('/^Bearer\s+(.*?)$/', $auth_header, $matches)) {
    send_json_response(401, ['error' => 'Authorization header missing or invalid']);
}
$jwt = $matches[1];
$admin_user_id = null;
$is_admin = false;
try {
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET_KEY, 'HS256'));
    if (!isset($decoded->data) || !isset($decoded->data->id_user)) {
        throw new Exception('Invalid token payload structure');
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
    send_json_response($code, ['error' => 'Authentication failed', 'details' => $e->getMessage()]);
}

$data = get_json_input();

if (!isset($data['id_user']) || !is_numeric($data['id_user'])) {
    if (isset($conn) && $conn) $conn->close();
     send_json_response(400, ['error' => 'User ID (id_user) is required and must be numeric.']);
}
$id_user_to_update = intval($data['id_user']);

if (!$is_admin && $id_user_to_update !== $admin_user_id) {
    if (isset($conn) && $conn) $conn->close();
    send_json_response(403, ['error' => 'Forbidden: You can only update your own profile.']);
}

if ($id_user_to_update === $admin_user_id && isset($data['status']) && $data['status'] !== 'Админ' && $is_admin) {
     if (isset($conn) && $conn) $conn->close();
    send_json_response(403, ['error' => 'Admin cannot change their own status.']);
}

if (!$is_admin && isset($data['status'])) {
    unset($data['status']);  // Удаляем поле статуса из запроса
}

$update_fields = [];
$params = [];
$types = "";
if (isset($data['username'])) {
    $username = trim($data['username']);
    if (empty($username)) {
        if (isset($conn) && $conn) $conn->close();
         send_json_response(400, ['error' => 'Username cannot be empty.']);
    }
    $update_fields[] = "username = ?";
    $params[] = $username;
    $types .= "s";
}
if (isset($data['email'])) {
    $email = trim($data['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (isset($conn) && $conn) $conn->close();
         send_json_response(400, ['error' => 'Invalid email format.']);
    }
    $update_fields[] = "email = ?";
    $params[] = $email;
    $types .= "s";
}
if (isset($data['profile_picture_url'])) {
    $profile_picture_url = trim($data['profile_picture_url']);
     if (!empty($profile_picture_url) && !filter_var($profile_picture_url, FILTER_VALIDATE_URL)) {
         if (!preg_match('/^\/assets\/(avatars|img)\/.*\.(jpg|jpeg|png|gif)$/i', $profile_picture_url)) {
            if (isset($conn) && $conn) $conn->close();
            send_json_response(400, ['error' => 'Invalid profile picture URL format.']);
         }
     }
    $update_fields[] = "profile_picture_url = ?";
    $params[] = empty($profile_picture_url) ? null : $profile_picture_url;
    $types .= "s";
}
if (isset($data['age'])) {
    $age = $data['age'];
    if ($age !== null && (!is_numeric($age) || $age < 0)) {
        if (isset($conn) && $conn) $conn->close();
        send_json_response(400, ['error' => 'Invalid age format.']);
    }
    $update_fields[] = "age = ?";
    $params[] = ($age === null || $age === '') ? null : intval($age); 
    $types .= "i";
}
if (isset($data['city'])) {
    $city = trim($data['city']);
    $update_fields[] = "city = ?";
    $params[] = empty($city) ? null : $city;
    $types .= "s";
}
if (isset($data['status']) && $is_admin) {
    $status = trim($data['status']);
    $allowed_statuses = ['Читатель', 'Автор', 'Админ']; 
    if (!in_array($status, $allowed_statuses)) {
        if (isset($conn) && $conn) $conn->close();
        send_json_response(400, ['error' => 'Invalid status value. Allowed values: ' . implode(', ', $allowed_statuses)]);
    }
    $update_fields[] = "status = ?";
    $params[] = $status;
    $types .= "s";
}
if (isset($data['about_me'])) {
    $about_me = trim($data['about_me']);
    $update_fields[] = "about_me = ?";
    $params[] = empty($about_me) ? null : $about_me;
    $types .= "s";
}
if (isset($data['reading_goal'])) {
    $reading_goal = $data['reading_goal'];
     if ($reading_goal !== null && (!is_numeric($reading_goal) || $reading_goal < 0)) {
        if (isset($conn) && $conn) $conn->close();
        send_json_response(400, ['error' => 'Invalid reading goal format.']);
    }
    $update_fields[] = "reading_goal = ?";
    $params[] = ($reading_goal === null || $reading_goal === '') ? 0 : intval($reading_goal);
    $types .= "i";
}
if (isset($data['password']) && !empty(trim($data['password']))) {
    $password = trim($data['password']);
    if (strlen($password) < 6) {
        if (isset($conn) && $conn) $conn->close();
        send_json_response(400, ['error' => 'Password must be at least 6 characters long.']);
    }
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $update_fields[] = "password_hash = ?";
    $params[] = $password_hash;
    $types .= "s";
}
if (empty($update_fields)) {
    if (isset($conn) && $conn) $conn->close();
     send_json_response(400, ['error' => 'No fields provided for update.']);
}
$params[] = $id_user_to_update; 
$types .= "i";
$sql = "UPDATE UserProfile SET " . implode(", ", $update_fields) . " WHERE id_user = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    send_json_response(500, ['error' => 'Failed to prepare statement: ' . $conn->error]);
}
try {
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            send_json_response(200, ['message' => 'User profile updated successfully', 'id_user' => $id_user_to_update]);
        } else {
            $check_stmt = $conn->prepare("SELECT id_user FROM UserProfile WHERE id_user = ?");
            $check_stmt->bind_param("i", $id_user_to_update);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            if ($check_result->num_rows === 0) {
                 send_json_response(404, ['error' => 'User not found.', 'id_user' => $id_user_to_update]);
            } else {
                 send_json_response(200, ['message' => 'User profile update requested, but data might be the same or user not found.', 'id_user' => $id_user_to_update]);
            }
             $check_stmt->close();
        }
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
    error_log("Admin Profile update error: " . $e->getMessage());
     send_json_response(500, ['error' => 'Failed to update user profile']);
} finally {
    if ($stmt) {
        $stmt->close();
    }
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?> 