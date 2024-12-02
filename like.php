<?php
header('Content-Type: application/json'); // Ответ в формате JSON
include 'includes/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $postId = intval($_GET['id']); // Преобразуем ID в число

    try {
        // Проверяем, существует ли пост
        $stmt = $conn->prepare("SELECT id FROM posts WHERE id = ?");
        $stmt->execute([$postId]);

        if ($stmt->rowCount() > 0) {
            // Увеличиваем счетчик лайков
            $updateStmt = $conn->prepare("UPDATE posts SET likes = likes + 1 WHERE id = ?");
            $updateStmt->execute([$postId]);

            echo json_encode(["success" => true, "message" => "Лайк добавлен!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Пост не найден."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Ошибка базы данных: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Неверный запрос."]);
}
