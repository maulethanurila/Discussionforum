<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

// Получаем ID поста
$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

if ($postId > 0) {
    try {
        // Запрос на получение комментариев для указанного поста
        $stmt = $conn->prepare("SELECT c.id, c.comment, u.username FROM comments c
                               JOIN users u ON c.user_id = u.id
                               WHERE c.post_id = :post_id ORDER BY c.id DESC");
        $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
        $stmt->execute();

        $comments = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comments[] = [
                'id' => $row['id'],
                'text' => htmlspecialchars($row['comment']),
                'username' => htmlspecialchars($row['username']) // Имя пользователя
            ];
        }

        // Отправляем успешный ответ с комментариями
        echo json_encode([
            'success' => true,
            'comments' => $comments
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка базы данных: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Неверный ID поста.'
    ]);
}
?>
