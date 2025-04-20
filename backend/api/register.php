<?php
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
setup_cors();
check_request_method('POST');
$input = get_json_input();
if (!isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
    send_json_response(400, ['error' => 'Отсутствуют обязательные поля: username, email, password']);
}
$username = trim($input['username']);
$email = trim($input['email']);
$password = $input['password'];
if (empty($username)) {
    send_json_response(400, ['error' => 'Имя пользователя не может быть пустым']);
}
if (empty($email)) {
    send_json_response(400, ['error' => 'Email не может быть пустым']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json_response(400, ['error' => 'Неверный формат email']);
}
if (empty($password)) {
    send_json_response(400, ['error' => 'Пароль не может быть пустым']);
}
if (mb_strlen($password) < 6) {
    send_json_response(400, ['error' => 'Пароль должен содержать не менее 6 символов']);
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Ошибка подключения к базе данных']);
}
try {
    $stmt_check_email = $conn->prepare("SELECT id_user FROM UserProfile WHERE email = ?");
    if (!$stmt_check_email) throw new Exception("Prepare failed (email check): " . $conn->error);
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();
    if ($stmt_check_email->num_rows > 0) {
        send_json_response(409, ['error' => 'Пользователь с таким email уже существует']);
    }
    $stmt_check_email->close();
    $stmt_check_username = $conn->prepare("SELECT id_user FROM UserProfile WHERE username = ?");
    if (!$stmt_check_username) throw new Exception("Prepare failed (username check): " . $conn->error);
    $stmt_check_username->bind_param("s", $username);
    $stmt_check_username->execute();
    $stmt_check_username->store_result();
    if ($stmt_check_username->num_rows > 0) {
        send_json_response(409, ['error' => 'Имя пользователя уже занято']);
    }
    $stmt_check_username->close();
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    if ($password_hash === false) {
        throw new Exception("Password hashing failed");
    }
    $default_avatar_url = 'assets/img/a9a7392fdbbfdb00d58ea345ca96198f.avif';
    $stmt_insert = $conn->prepare("INSERT INTO UserProfile (username, email, password_hash, profile_picture_url, status) VALUES (?, ?, ?, ?, 'Читатель')");
    if (!$stmt_insert) throw new Exception("Prepare failed (insert): " . $conn->error);
    $stmt_insert->bind_param("ssss", $username, $email, $password_hash, $default_avatar_url);
    
    if ($stmt_insert->execute()) {
        $new_user_id = $conn->insert_id;
        send_json_response(201, [
            'message' => 'Пользователь успешно зарегистрирован',
            'userId' => $new_user_id 
        ]);
    } else {
        throw new Exception("Execute failed (insert): " . $stmt_insert->error);
    }
    $stmt_insert->close();
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    send_json_response(500, ['error' => 'Произошла внутренняя ошибка при регистрации']);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?> 