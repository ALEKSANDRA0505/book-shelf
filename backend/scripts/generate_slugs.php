<?php
    require_once __DIR__ . '/../includes/db_connection.php';
    require_once __DIR__ . '/../includes/api_helpers.php';
    echo "<!DOCTYPE html><html><head><title>Populate Genre Slugs</title><meta charset='utf-8'></head><body>";
    echo "<h1>Заполнение слагов для жанров...</h1>";
    $conn = get_db_connection();
    if (!$conn) {
        echo "<p style='color: red;'>Ошибка: Не удалось подключиться к базе данных.</p>";
        echo "</body></html>";
        exit;
    }
    echo "<p>Соединение с БД установлено.</p>";
    $sql_select = "SELECT id_genre, name FROM Genre";
    $result = $conn->query($sql_select);
    if (!$result) {
        echo "<p style='color: red;'>Ошибка при выборе жанров: " . $conn->error . "</p>";
        $conn->close();
        echo "</body></html>";
        exit;
    }
    $genres_to_update = $result->fetch_all(MYSQLI_ASSOC);
    $updated_count = 0;
    $error_count = 0;
    if (count($genres_to_update) === 0) {
        echo "<p>Не найдено жанров с незаполненным слагом. Все уже обработано.</p>";
    } else {
        echo "<p>Найдено " . count($genres_to_update) . " жанров для обновления слага.</p>";
        echo "<ul>";
        $sql_update = "UPDATE Genre SET slug = ? WHERE id_genre = ?";
        $stmt_update = $conn->prepare($sql_update);
        if (!$stmt_update) {
            echo "<p style='color: red;'>Ошибка при подготовке запроса на обновление: " . $conn->error . "</p>";
            $conn->close();
            echo "</body></html>";
            exit;
        }
        foreach ($genres_to_update as $genre) {
            $id_genre = $genre['id_genre'];
            $name = $genre['name'];
            echo "<li>Обработка жанра: ID=" . htmlspecialchars($id_genre) . ", Name='" . htmlspecialchars($name) . "'...";
            try {
                $slug = generate_slug($name, $conn, 'Genre', 'slug', $id_genre);
                $stmt_update->bind_param('si', $slug, $id_genre);
                if ($stmt_update->execute()) {
                    if ($stmt_update->affected_rows > 0) {
                        echo " <span style='color: green;'>Успешно обновлен slug: '" . htmlspecialchars($slug) . "'</span></li>";
                        $updated_count++;
                    } else {
                         echo " <span style='color: orange;'>Запись не обновлена (возможно, слаг уже был таким?).</span></li>";
                    }
                } else {
                    throw new Exception($stmt_update->error);
                }
            } catch (Exception $e) {
                echo " <span style='color: red;'>Ошибка обновления: " . htmlspecialchars($e->getMessage()) . "</span></li>";
                $error_count++;
            }
        }
        echo "</ul>";
        $stmt_update->close();
        echo "<hr>";
        echo "<p><strong>Итог:</strong></p>";
        echo "<p>Успешно обновлено: " . $updated_count . "</p>";
        if ($error_count > 0) {
             echo "<p style='color: red;'>Ошибок при обновлении: " . $error_count . "</p>";
        } else {
             echo "<p>Ошибок: 0</p>";
        }
    }
    $conn->close();
    echo "<p>Соединение с БД закрыто.</p>";
    echo "<p>Готово!</p>";
    echo "</body></html>";
    ?>