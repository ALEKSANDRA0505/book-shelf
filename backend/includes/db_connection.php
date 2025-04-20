<?php

require_once __DIR__ . '/../config/database.php';
/**
 * Устанавливает соединение с базой данных MySQLi.
 *
 * @return mysqli|false Возвращает объект mysqli в случае успеха или false в случае ошибки.
 */
function get_db_connection(): mysqli|false
{
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            error_log("MySQL Connection Error: (" . $conn->connect_errno . ") " . $conn->connect_error);
            
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'error',
                'message' => 'Не удалось подключиться к базе данных',
                'debug_info' => "MySQL Error: (" . $conn->connect_errno . ") " . $conn->connect_error
            ]);
            return false;
        }
        if (!$conn->set_charset("utf8mb4")) {
            error_log("Error loading character set utf8mb4: " . $conn->error);
            
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'error',
                'message' => 'Ошибка при установке кодировки соединения',
                'debug_info' => "Character set error: " . $conn->error
            ]);
            
            $conn->close();
            return false;
        }
    }
    return $conn;
}
?> 