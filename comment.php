<?php
header('Content-Type: application/json');
include 'includes/db.php';
session_start(); // Убедитесь, что сессия активна

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Сессия пользователя не найдена.']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id']) && is_numeric($data['id']) && !empty($data['comment'])) {
    $postId = intval($data['id']);
    $comment = htmlspecialchars($data['comment']);

    try {
        // Проверка существования поста
        $stmt = $conn->prepare("SELECT id FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        if ($stmt->rowCount() === 0) {
            echo json_encode(["success" => false, "message" => "Пост не найден."]);
            exit();
        }

        // Вставка комментария в базу данных
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$postId, $_SESSION['user_id'], $comment]);

        // Получаем ID последнего вставленного комментария
        $commentId = $conn->lastInsertId();

        echo json_encode(["success" => true, "message" => "Комментарий добавлен!", "comment_id" => $commentId]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Ошибка базы данных: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Некорректные данные."]);
}
?>
