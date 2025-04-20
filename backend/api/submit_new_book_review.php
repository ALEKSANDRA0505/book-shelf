<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
setup_cors();
check_request_method('POST');
$user_id = get_user_id_from_token();
if (!$user_id) {
    send_json_response(401, ['error' => 'Ошибка аутентификации: Требуется токен доступа']);
}
$input = get_json_input();
$required_fields = ['title', 'author', 'rating', 'genre_ids', 'comment']; 
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || 
        (is_string($input[$field]) && trim($input[$field]) === '' && $field !== 'comment') || 
        ($field === 'rating' && (!is_numeric($input[$field]) || $input[$field] < 1 || $input[$field] > 5)) || 
        ($field === 'genre_ids' && (!is_array($input[$field]) || empty($input[$field])))
    ) {
        if ($field === 'genre_ids') {
             send_json_response(400, ['error' => "Отсутствует или неверно заполнено обязательное поле: {$field} (должен быть непустой массив ID)"]);
        } else {
             send_json_response(400, ['error' => "Отсутствует или неверно заполнено обязательное поле: {$field}"]);
        }
    }
    if ($field === 'genre_ids') {
        foreach ($input[$field] as $gid) {
            if (!is_numeric($gid)) {
                send_json_response(400, ['error' => "Поле genre_ids содержит нечисловые значения."]);
            }
        }
    }
}
$title = trim($input['title']);
$author = trim($input['author']);
$rating = (int)$input['rating'];
$genre_ids = array_map('intval', $input['genre_ids']);
$comment = isset($input['comment']) ? trim($input['comment']) : null;
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Ошибка подключения к базе данных']);
}
$conn->begin_transaction();
try {
    $writer_id = null;
    $stmt_find_writer = $conn->prepare("SELECT id_writer FROM Writer WHERE name = ?");
    if (!$stmt_find_writer) throw new Exception("Prepare failed (find writer): " . $conn->error);
    $stmt_find_writer->bind_param("s", $author);
    if (!$stmt_find_writer->execute()) throw new Exception("Execute failed (find writer): " . $stmt_find_writer->error);
    $result_writer = $stmt_find_writer->get_result();
    if ($result_writer->num_rows > 0) {
        $writer_id = $result_writer->fetch_assoc()['id_writer'];
    } else {
        $stmt_create_writer = $conn->prepare("INSERT INTO Writer (name) VALUES (?)");
        if (!$stmt_create_writer) throw new Exception("Prepare failed (create writer): " . $conn->error);
        $stmt_create_writer->bind_param("s", $author);
        if (!$stmt_create_writer->execute()) {
             if ($conn->errno == 1062) {
                 $stmt_find_writer->execute();
                 $result_writer = $stmt_find_writer->get_result();
                 $writer_id = $result_writer->fetch_assoc()['id_writer'];
                 $stmt_create_writer->close();
             } else {
                 throw new Exception("Execute failed (create writer): " . $stmt_create_writer->error);
             }
        } else {
            $writer_id = $conn->insert_id;
            $stmt_create_writer->close();
        }
    }
    $stmt_find_writer->close();
    if (!$writer_id) {
        throw new Exception("Не удалось определить ID писателя.", 500);
    }
    $stmt_create_book = $conn->prepare("INSERT INTO Book (title) VALUES (?)");
    if (!$stmt_create_book) throw new Exception("Prepare failed (create book): " . $conn->error);
    $stmt_create_book->bind_param("s", $title);
    if (!$stmt_create_book->execute()) {
        if ($conn->errno == 1062) {
             throw new Exception("Книга с таким названием уже существует.", 409);
        }
        throw new Exception("Execute failed (create book): " . $stmt_create_book->error);
    }
    $new_book_id = $conn->insert_id;
    $stmt_create_book->close();
    $stmt_link_writer = $conn->prepare("INSERT INTO BookWriter (id_book, id_writer) VALUES (?, ?)");
    if (!$stmt_link_writer) throw new Exception("Prepare failed (link writer): " . $conn->error);
    $stmt_link_writer->bind_param("ii", $new_book_id, $writer_id);
    if (!$stmt_link_writer->execute()) {
        if ($conn->errno == 1062) {
             error_log("Duplicate entry error ignored for BookWriter: book={$new_book_id}, writer={$writer_id}");
        } else {
            throw new Exception("Execute failed (link writer): " . $stmt_link_writer->error);
        }
    }
    $stmt_link_writer->close();
    $stmt_create_review = $conn->prepare("INSERT INTO Review (id_user, id_book, rating, review_text) VALUES (?, ?, ?, ?)");
    if (!$stmt_create_review) throw new Exception("Prepare failed (create review): " . $conn->error);
    $stmt_create_review->bind_param("iiis", $user_id, $new_book_id, $rating, $comment);
    if (!$stmt_create_review->execute()) {
        throw new Exception("Execute failed (create review): " . $stmt_create_review->error);
    }
    $new_review_id = $conn->insert_id;
    $stmt_create_review->close();
    $stmt_add_genre = $conn->prepare("INSERT INTO BookGenre (id_book, id_genre) VALUES (?, ?)");
    if (!$stmt_add_genre) throw new Exception("Prepare failed (add genre): " . $conn->error);
    
    foreach ($genre_ids as $genre_id) {
        $stmt_add_genre->bind_param("ii", $new_book_id, $genre_id);
        if (!$stmt_add_genre->execute()) {
            if ($conn->errno == 1452) { 
                throw new Exception("Ошибка добавления жанра: Жанр с ID {$genre_id} не найден.", 400);
            }
            throw new Exception("Execute failed (add genre for ID {$genre_id}): " . $stmt_add_genre->error);
        }
    }
    $stmt_add_genre->close();
    $conn->commit();
    send_json_response(201, [
        'message' => 'Новая книга и рецензия успешно добавлены.',
        'id_book' => $new_book_id,
        'id_review' => $new_review_id
    ]);
} catch (Exception $e) {
    $conn->rollback();
    error_log("Submit new book review error: " . $e->getMessage());
    $response_code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
    send_json_response($response_code, ['error' => $e->getMessage()]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?> 