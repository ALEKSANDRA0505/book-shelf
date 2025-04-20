<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/api_helpers.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

$jwt_secret_key = getenv('JWT_SECRET_KEY') ?: '8d3d8fa623320894928a3a092c442f6b0fbbb231319a6f08000e71fc723772fd8069c1e4fe99750d695a2685a031643c0dc09fb6278994b3703e9e58c9f87217d031cc3cca900ba6bcda95464c38a06cdfa233c82e38337afe92db9c3a72bb83dc8f35e2dca3b23e730c08f614f5220530b66cb466ebd8f0671b8bfb7f4e672207e4f7618e2ff645fb28a242851ba6c4496b270378f10f35429e97f8c8750e899468aa194c8cd40106656b39ec6564edf7571a21f271a2d9533f943941dd0c0a8c272199a51f61637735dd9294c53ac6bbd66c13424c901a40b4bab44bfa4ffda892e2cb3d596048106bf1b2b19b1a2b7432f611b8650d6512494f4a644fba76';
/**
 * Проверяет наличие и валидность JWT токена в заголовке Authorization.
 * Если токен валиден, возвращает payload (данные пользователя).
 * Если токен отсутствует или невалиден, отправляет ошибку 401/403 и завершает скрипт.
 *
 * @return array Данные пользователя из JWT payload.
 */
function require_authentication(): array {
    global $jwt_secret_key;
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$auth_header && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    if (!$auth_header) {
        send_json_response(401, ['error' => 'Authorization header missing']);
    }
    if (!preg_match('/^Bearer\s+(.*)$/i', $auth_header, $matches)) {
        send_json_response(401, ['error' => 'Invalid Authorization header format']);
    }
    $token = $matches[1];
    if (!$token) {
         send_json_response(401, ['error' => 'Token not found in Authorization header']);
    }
    try {
        $decoded = JWT::decode($token, new Key($jwt_secret_key, 'HS256'));
        return (array) $decoded->data;
    } catch (ExpiredException $e) {
        send_json_response(401, ['error' => 'Token has expired']);
    } catch (SignatureInvalidException $e) {
        send_json_response(401, ['error' => 'Token signature is invalid']);
    } catch (Exception $e) {
        send_json_response(401, ['error' => 'Invalid token: ' . $e->getMessage()]);
    }
}
?> 