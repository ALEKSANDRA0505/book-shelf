<?php
use Firebase\JWT\JWT;
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
setup_cors();
check_request_method('POST');
$input = get_json_input();
if (!isset($input['email']) || !isset($input['password'])) {
    send_json_response(400, ['error' => 'Отсутствуют обязательные поля: email, password']);
}
$email = trim($input['email']);
$password = $input['password'];
if (empty($email) || empty($password)) {
    send_json_response(400, ['error' => 'Email и пароль не могут быть пустыми']);
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Ошибка подключения к базе данных']);
}
try {
    $stmt = $conn->prepare("SELECT id_user, username, email, password_hash FROM UserProfile WHERE email = ?");
    if (!$stmt) throw new Exception("Prepare failed (find user): " . $conn->error);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            $issuer_claim = "http://bookshelf.nngasu.tw1.ru";
            $audience_claim = "http://bookshelf.nngasu.tw1.ru";
            $issuedat_claim = time();
            $expire_claim = $issuedat_claim + JWT_EXPIRATION_TIME;
            $payload = [
                'iss' => $issuer_claim,
                'aud' => $audience_claim,
                'iat' => $issuedat_claim,
                'exp' => $expire_claim,
                'data' => [
                    'id_user' => $user['id_user']
                ]
            ];
            $jwt = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');
            $user_data_for_response = [
                'id_user' => $user['id_user'],
                'username' => $user['username'],
                'email' => $user['email']
            ];
            send_json_response(200, [
                'message' => 'Вход выполнен успешно',
                'token' => $jwt,
                'user' => $user_data_for_response,
                'expiresIn' => JWT_EXPIRATION_TIME
            ]);
        } else {
            send_json_response(401, ['error' => 'Неверный email или пароль']);
        }
    } else {
        send_json_response(401, ['error' => 'Неверный email или пароль']);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    send_json_response(500, ['error' => 'Произошла внутренняя ошибка при входе']);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?> 