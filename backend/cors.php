<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
require_once __DIR__ . '/config/database.php';
$database = new Database();
$conn = $database->getConnection();
header('Content-Type: application/json');
if ($conn) {
    echo json_encode([
        'status' => 'success', 
        'message' => 'Подключение к БД успешно установлено',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Не удалось подключиться к БД',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?> 