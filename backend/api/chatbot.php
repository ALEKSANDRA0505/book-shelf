<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    exit(0);
}
require_once '../includes/db_connection.php';
$conn = get_db_connection();
if (!$conn) {
    $response = [
        'status' => 'error',
        'message' => 'Ошибка подключения к базе данных. Пожалуйста, попробуйте позже.'
    ];
    http_response_code(500);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
$response = ['status' => 'error', 'message' => 'Неизвестная ошибка'];
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = isset($input['message']) ? trim($input['message']) : '';
if (empty($userMessage)) {
    $response['message'] = 'Сообщение не может быть пустым.';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
$userMessageLower = mb_strtolower($userMessage, 'UTF-8');
$parts = explode(' ' , $userMessageLower, 2);
$command = $parts[0];
$argument = isset($parts[1]) ? trim($parts[1]) : null;
mysqli_set_charset($conn, "utf8mb4");
try {
    switch ($command) {
        case 'помощь':
            $response = [
                'status' => 'success',
                'message' => "Доступные команды:\n" .
                             " - помощь: Показать это сообщение\n" .
                             " - найди название <текст>: Поиск книг по названию\n" .
                             " - найди писателя <текст>: Поиск книг по писателю\n" .
                             " - инфо книга <точное название>: Информация о книге\n" .
                             " - посоветуй книгу: Случайная книга\n" .
                             " - посоветуй по жанру <жанр>: Случайная книга по жанру"
            ];
            break;
        case 'найди':
            if (isset($parts[1])) {
                 $subParts = explode(' ', $parts[1], 2);
                 $subCommand = $subParts[0];
                 $subArgument = isset($subParts[1]) ? trim($subParts[1]) : null;
                 if ($subCommand === 'название' && $subArgument) {
                     $sql = "SELECT b.id_book, b.title, GROUP_CONCAT(DISTINCT w.name SEPARATOR ', ') AS authors 
                             FROM Book b 
                             LEFT JOIN BookWriter bw ON b.id_book = bw.id_book 
                             LEFT JOIN Writer w ON bw.id_writer = w.id_writer 
                             WHERE LOWER(b.title) LIKE ?
                             GROUP BY b.id_book, b.title";
                     $stmt = $conn->prepare($sql);
                     $searchTerm = "%" . $subArgument . "%";
                     $stmt->bind_param("s", $searchTerm);
                     $stmt->execute();
                     $result = $stmt->get_result();
                     $books = $result->fetch_all(MYSQLI_ASSOC);
                     $stmt->close();
                     if (!empty($books)) {
                         $message = "Найденные книги по названию '$subArgument':\n";
                         foreach ($books as $book) {
                             $authorText = $book['authors'] ? htmlspecialchars($book['authors'], ENT_QUOTES, 'UTF-8') : 'Неизвестен';
                             $message .= " - " . htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') . " (Писатель: " . $authorText . ", ID: " . $book['id_book'] . ")\n";
                         }
                         $response = ['status' => 'success', 'message' => $message];
                     } else {
                         $response = ['status' => 'success', 'message' => "Книги по названию '$subArgument' не найдены."];
                     }
                 } elseif ($subCommand === 'писателя' && $subArgument) {
                     $sql = "SELECT b.id_book, b.title 
                             FROM Book b 
                             JOIN BookWriter bw ON b.id_book = bw.id_book 
                             JOIN Writer w ON bw.id_writer = w.id_writer 
                             WHERE LOWER(w.name) LIKE ?";
                     $stmt = $conn->prepare($sql);
                     $searchTerm = "%" . $subArgument . "%";
                     $stmt->bind_param("s", $searchTerm);
                     $stmt->execute();
                     $result = $stmt->get_result();
                     $books = $result->fetch_all(MYSQLI_ASSOC);
                     $stmt->close();
                     if (!empty($books)) {
                         $message = "Найденные книги писателя '$subArgument':\n";
                         foreach ($books as $book) {
                              $message .= " - " . htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') . " (ID: " . $book['id_book'] . ")\n";
                         }
                         $response = ['status' => 'success', 'message' => $message];
                     } else {
                         $response = ['status' => 'success', 'message' => "Книги писателя '$subArgument' не найдены."];
                     }
                 } else {
                      $response['message'] = "Неверный формат команды 'найди'. Используйте 'найди название <текст>' или 'найди писателя <текст>'.";
                 }
            } else {
                 $response['message'] = "Укажите, что искать: 'найди название <текст>' или 'найди писателя <текст>'.";
            }
            break;
        case 'инфо':
             if (isset($parts[1])) {
                  $subParts = explode(' ', $parts[1], 2);
                  $subCommand = $subParts[0];
                  $subArgument = isset($subParts[1]) ? trim($subParts[1]) : null;
                  if ($subCommand === 'книга' && $subArgument) {
                      $stmtBook = $conn->prepare("SELECT id_book, title, description FROM Book WHERE LOWER(title) = ? LIMIT 1");
                      $stmtBook->bind_param("s", $subArgument);
                      $stmtBook->execute();
                      $resultBook = $stmtBook->get_result();
                      $book = $resultBook->fetch_assoc();
                      $stmtBook->close();
                      if ($book) {
                          $bookId = $book['id_book'];
                          $stmtAuthors = $conn->prepare("SELECT w.name FROM Writer w JOIN BookWriter bw ON w.id_writer = bw.id_writer WHERE bw.id_book = ?");
                          $stmtAuthors->bind_param("i", $bookId);
                          $stmtAuthors->execute();
                          $resultAuthors = $stmtAuthors->get_result();
                          $authors = $resultAuthors->fetch_all(MYSQLI_ASSOC);
                          $stmtAuthors->close();
                          $authorNames = !empty($authors) ? implode(', ', array_column($authors, 'name')) : 'Неизвестен';
                          $stmtGenres = $conn->prepare("SELECT g.name FROM Genre g JOIN BookGenre bg ON g.id_genre = bg.id_genre WHERE bg.id_book = ?");
                          $stmtGenres->bind_param("i", $bookId);
                          $stmtGenres->execute();
                          $resultGenres = $stmtGenres->get_result();
                          $genres = $resultGenres->fetch_all(MYSQLI_ASSOC);
                          $stmtGenres->close();
                          $genreNames = !empty($genres) ? implode(', ', array_column($genres, 'name')) : 'Неизвестен';
                          $message = "Информация о книге '" . htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') . "':\n" .
                                     " - Писатель(и): " . htmlspecialchars($authorNames, ENT_QUOTES, 'UTF-8') . "\n" .
                                     " - Жанр(ы): " . htmlspecialchars($genreNames, ENT_QUOTES, 'UTF-8') . "\n" .
                                     " - Описание: " . htmlspecialchars($book['description'] ?? 'Нет описания', ENT_QUOTES, 'UTF-8');
                          $response = ['status' => 'success', 'message' => $message];
                      } else {
                          $response = ['status' => 'success', 'message' => "Книга с точным названием '$subArgument' не найдена."];
                      }
                  } else {
                       $response['message'] = "Неверный формат команды 'инфо'. Используйте 'инфо книга <точное название>'.";
                  }
             } else {
                  $response['message'] = "Укажите название книги: 'инфо книга <точное название>'.";
             }
             break;
        case 'посоветуй':
             if ($argument === 'книгу') {
                 $resultBook = $conn->query("SELECT id_book, title, description FROM Book ORDER BY RAND() LIMIT 1");
                 $book = $resultBook->fetch_assoc();
                 if ($book) {
                     $bookId = $book['id_book'];
                     $stmtAuthors = $conn->prepare("SELECT w.name FROM Writer w JOIN BookWriter bw ON w.id_writer = bw.id_writer WHERE bw.id_book = ?");
                     $stmtAuthors->bind_param("i", $bookId);
                     $stmtAuthors->execute();
                     $resultAuthors = $stmtAuthors->get_result();
                     $authors = $resultAuthors->fetch_all(MYSQLI_ASSOC);
                     $stmtAuthors->close();
                     $authorNames = !empty($authors) ? implode(', ', array_column($authors, 'name')) : 'Неизвестен';
                     $stmtGenres = $conn->prepare("SELECT g.name FROM Genre g JOIN BookGenre bg ON g.id_genre = bg.id_genre WHERE bg.id_book = ?");
                     $stmtGenres->bind_param("i", $bookId);
                     $stmtGenres->execute();
                     $resultGenres = $stmtGenres->get_result();
                     $genres = $resultGenres->fetch_all(MYSQLI_ASSOC);
                     $stmtGenres->close();
                     $genreNames = !empty($genres) ? implode(', ', array_column($genres, 'name')) : 'Неизвестен';
                     $message = "Рекомендую книгу:\n" .
                                " - Название: " . htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') . "\n" .
                                " - Автор(ы): " . htmlspecialchars($authorNames, ENT_QUOTES, 'UTF-8') . "\n" .
                                " - Жанр(ы): " . htmlspecialchars($genreNames, ENT_QUOTES, 'UTF-8') . "\n" .
                                " - Описание: " . htmlspecialchars($book['description'] ?? 'Нет описания', ENT_QUOTES, 'UTF-8');
                     $response = ['status' => 'success', 'message' => $message];
                 } else {
                     $response = ['status' => 'success', 'message' => "В базе пока нет книг для рекомендации."];
                 }
            } elseif ($argument !== null && str_starts_with($argument, 'по жанру ')) {
                $genreArgument = trim(mb_substr($argument, mb_strlen('по жанру ')));
                if (!empty($genreArgument)) {
                    $stmtBook = $conn->prepare("SELECT b.id_book, b.title, b.description 
                                                FROM Book b 
                                                JOIN BookGenre bg ON b.id_book = bg.id_book 
                                                JOIN Genre g ON bg.id_genre = g.id_genre 
                                                WHERE LOWER(g.name) = ? 
                                                ORDER BY RAND() 
                                                LIMIT 1");
                     $stmtBook->bind_param("s", $genreArgument);
                     $stmtBook->execute();
                     $resultBook = $stmtBook->get_result();
                     $book = $resultBook->fetch_assoc();
                     $stmtBook->close();
                    if ($book) {
                        $bookId = $book['id_book'];
                        $stmtAuthors = $conn->prepare("SELECT w.name FROM Writer w JOIN BookWriter bw ON w.id_writer = bw.id_writer WHERE bw.id_book = ?");
                        $stmtAuthors->bind_param("i", $bookId);
                        $stmtAuthors->execute();
                        $resultAuthors = $stmtAuthors->get_result();
                        $authors = $resultAuthors->fetch_all(MYSQLI_ASSOC);
                        $stmtAuthors->close();
                        $authorNames = !empty($authors) ? implode(', ', array_column($authors, 'name')) : 'Неизвестен';
                        $stmtGenres = $conn->prepare("SELECT g.name FROM Genre g JOIN BookGenre bg ON g.id_genre = bg.id_genre WHERE bg.id_book = ?");
                        $stmtGenres->bind_param("i", $bookId);
                        $stmtGenres->execute();
                        $resultGenres = $stmtGenres->get_result();
                        $genres = $resultGenres->fetch_all(MYSQLI_ASSOC);
                        $stmtGenres->close();
                        $genreNames = !empty($genres) ? implode(', ', array_column($genres, 'name')) : 'Неизвестен';
                        $message = "Рекомендую книгу в жанре '" . htmlspecialchars($genreArgument, ENT_QUOTES, 'UTF-8') . "':\n" .
                                    " - Название: " . htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') . "\n" .
                                    " - Автор(ы): " . htmlspecialchars($authorNames, ENT_QUOTES, 'UTF-8') . "\n" .
                                    " - Жанр(ы): " . htmlspecialchars($genreNames, ENT_QUOTES, 'UTF-8') . "\n" .
                                    " - Описание: " . htmlspecialchars($book['description'] ?? 'Нет описания', ENT_QUOTES, 'UTF-8');
                        $response = ['status' => 'success', 'message' => $message];
                    } else {
                        $response = ['status' => 'success', 'message' => "Книг в жанре '" . htmlspecialchars($genreArgument, ENT_QUOTES, 'UTF-8') . "' не найдено."];
                    }
                } else {
                    $response['message'] = "Укажите название жанра после 'посоветуй по жанру'.";
                }
            } else {
                $response['message'] = "Неверный формат команды 'посоветуй'. Используйте 'посоветуй книгу' или 'посоветуй по жанру <жанр>'.";
            }
            break;
        default:
            $response['message'] = "Неизвестная команда '$command'. Введите 'помощь' для списка команд.";
            break;
    }
} catch (Exception $e) {
    error_log("Chatbot Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    $response['status'] = 'error';
    $response['message'] = 'Произошла внутренняя ошибка сервера.';
    
    if (http_response_code() === 200) {
        http_response_code(500);
    }
} finally {
     if (isset($conn) && $conn instanceof mysqli) {
         $conn->close();
     }
}
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?> 