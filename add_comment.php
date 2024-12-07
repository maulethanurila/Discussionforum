<?php
// Подключение к базе данных
include 'includes/db.php';

// Проверка, был ли отправлен запрос
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из POST-запроса
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    if ($post_id > 0 && !empty($comment)) {
        try {
            // Вставка комментария в базу данных
            $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (:post_id, :user_id, :comment)");
            $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);  // Получаем user_id из сессии
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->execute();

            // Возвращаем успешный ответ
            echo json_encode([
                'success' => true,
                'message' => 'Комментарий добавлен успешно.',
            ]);
        } catch (PDOException $e) {
            // Обработка ошибок базы данных
            echo json_encode([
                'success' => false,
                'message' => 'Ошибка при добавлении комментария: ' . $e->getMessage(),
            ]);
        }
    } else {
        // Если данные не переданы
        echo json_encode([
            'success' => false,
            'message' => 'Пожалуйста, заполните все поля.',
        ]);
    }
} else {
    // Если запрос не POST
    echo json_encode([
        'success' => false,
        'message' => 'Неверный метод запроса.',
    ]);
}

exit();
?>
