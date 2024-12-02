<?php
// Подключение к базе данных
include 'includes/db.php';

// Получаем ID поста из запроса
$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

if ($post_id > 0) {
    // Запрос для получения комментариев с данными о пользователе
    $stmt = $conn->prepare("
        SELECT c.id, c.comment, c.created_at, u.username 
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = :post_id
        ORDER BY c.created_at DESC
    ");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Возвращаем комментарии и информацию о пользователе в формате JSON
    echo json_encode(['comments' => $comments]);
} else {
    echo json_encode(['comments' => []]);
}
?>
