<?php
require_once __DIR__ . '/../config/database.php';
/**
 * Устанавливает соединение с базой данных MySQLi.
 * Исправленная версия, которая не устанавливает заголовки при ошибке.
 *
 * @return mysqli|null Возвращает объект mysqli в случае успеха или null в случае ошибки.
 */
function get_db_connection_fixed(): ?mysqli
{
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($conn->connect_error) {
                error_log("MySQL Connection Error: (" . $conn->connect_errno . ") " . $conn->connect_error);
                return null;
            }
            if (!$conn->set_charset("utf8mb4")) {
                error_log("Error loading character set utf8mb4: " . $conn->error);
                $conn->close();
                return null;
            }
        } catch (Exception $e) {
            error_log("Exception in get_db_connection: " . $e->getMessage());
            return null;
        }
    }
    return $conn;
}
/**
 * Оригинальная функция для обратной совместимости.
 * Просто вызывает новую исправленную функцию.
 */
function get_db_connection(): ?mysqli
{
    return get_db_connection_fixed();
} 