<?php
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
set_exception_handler(function ($exception) {
    error_log("Uncaught exception: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Allow-Credentials: true");
    }
    echo json_encode([
        'error' => 'Internal Server Error',

    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
});
require_once __DIR__ . '/../vendor/autoload.php';
/**
 * Устанавливает заголовки CORS для разрешения запросов от Angular (или другого фронтенда).
 * Вам может потребоваться настроить 'Access-Control-Allow-Origin' более строго для продакшена.
 */
function setup_cors() {
    header("Access-Control-Allow-Origin: http://bookshelf.nngasu.tw1.ru"); 
    
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    
    header("Access-Control-Allow-Credentials: true");
    
    header("Access-Control-Max-Age: 86400");
    
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}
/**
 * Отправляет JSON-ответ клиенту.
 *
 * @param int $status_code HTTP статус код.
 * @param mixed $data Данные для отправки (массив или объект).
 */
function send_json_response(int $status_code, mixed $data) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}
/**
 * Проверяет, соответствует ли метод HTTP-запроса ожидаемому.
 * Если нет, отправляет ошибку 405 Method Not Allowed.
 *
 * @param string $expected_method Ожидаемый метод (например, 'GET', 'POST').
 */
function check_request_method(string $expected_method) {
    if ($_SERVER['REQUEST_METHOD'] !== $expected_method) {
        send_json_response(405, ['error' => 'Method Not Allowed. Expected ' . $expected_method]);
    }
}
/**
 * Получает JSON данные из тела POST/PUT запроса.
 *
 * @return array|null Возвращает ассоциативный массив данных или null при ошибке.
 */
function get_json_input(): ?array {
    $input = file_get_contents('php://input');
    if (!$input) {
        return null;
    }
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        send_json_response(400, ['error' => 'Invalid JSON input: ' . json_last_error_msg()]);
        return null;
    }
    return $data;
}
/**
 * Генерирует URL-дружественный слаг из строки.
 *
 * @param string $text Исходная строка (например, название жанра).
 * @param mysqli $conn Объект соединения с БД для проверки уникальности (опционально).
 * @param string $table Имя таблицы для проверки уникальности (по умолчанию 'Genre').
 * @param string $column Имя колонки слага для проверки уникальности (по умолчанию 'slug').
 * @param int $current_id ID текущей записи для исключения при проверке уникальности (при обновлении).
 * @return string Сгенерированный слаг.
 */
function generate_slug(string $text, mysqli $conn = null, string $table = 'Genre', string $column = 'slug', int $current_id = 0): string {
    $rus = [
        'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о',
        'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
        'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О',
        'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'
    ];
    $lat = [
        'a', 'b', 'v', 'g', 'd', 'e', 'yo', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o',
        'p', 'r', 's', 't', 'u', 'f', 'kh', 'ts', 'ch', 'sh', 'shch', '', 'y', '', 'e', 'yu', 'ya',
        'A', 'B', 'V', 'G', 'D', 'E', 'Yo', 'Zh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O',
        'P', 'R', 'S', 'T', 'U', 'F', 'Kh', 'Ts', 'Ch', 'Sh', 'Shch', '', 'Y', '', 'E', 'Yu', 'Ya'
    ];
    $text = str_replace($rus, $lat, $text);
    $slug = strtolower($text);
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    if (empty($slug)) {
        $slug = 'n-a-' . time();
    }
    if ($conn) {
        $original_slug = $slug;
        $counter = 1;
        while (true) {
            $sql = "SELECT COUNT(*) FROM `$table` WHERE `$column` = ?";
            $params = [$slug];
            $types = 's';
            if ($current_id > 0) {
                 $sql .= " AND id_" . strtolower(rtrim($table, 's')) . " != ?";
                 $params[] = $current_id;
                 $types .= 'i';
            }
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $stmt->close();
                if ($count == 0) {
                    break;
                } else {
                    $slug = $original_slug . '-' . $counter;
                    $counter++;
                }
            } else {
                error_log("Failed to prepare uniqueness check for slug: " . $conn->error);
                 break;
            }
        }
    }
    return $slug;
}
/**
 * Извлекает JWT из заголовка Authorization, декодирует его и возвращает ID пользователя.
 *
 * @return int|null ID пользователя или null, если токен недействителен или отсутствует.
 */
function get_user_id_from_token(): ?int {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$auth_header) {
        return null;
    }
    if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $jwt = $matches[1];
    } else {
        return null;
    }
    if (!$jwt) {
        return null;
    }
    try {
        $payload = Firebase\JWT\JWT::decode($jwt, new Firebase\JWT\Key(JWT_SECRET_KEY, 'HS256'));
        
        if (isset($payload->data->id_user) && is_numeric($payload->data->id_user)) {
            return (int)$payload->data->id_user;
        } else {
            error_log("JWT decode error: id_user not found or invalid in payload");
            return null;
        }
    } catch (Firebase\JWT\ExpiredException $e) {
        error_log("JWT decode error: Expired token - " . $e->getMessage());
        return null;
    } catch (Firebase\JWT\SignatureInvalidException $e) {
         error_log("JWT decode error: Invalid signature - " . $e->getMessage());
        return null;
    } catch (Exception $e) {
        error_log("JWT decode error: " . $e->getMessage());
        return null;
    }
}
?> 