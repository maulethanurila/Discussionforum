<?php
// Подключение к базе данных
include 'includes/db.php';

// Устанавливаем заголовки для JSON
header('Content-Type: application/json');

// Получаем ID поста из запроса
$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

if ($post_id > 0) {
    try {
        // Получение комментариев с именами и аватарками пользователей
        $stmt = $conn->prepare("
            SELECT c.id, c.comment, c.created_at, 
                   u.username, 
                   COALESCE(u.profile_image, 'uploads/profile_images/default_avatar.jpg') AS profile_image
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.post_id = :post_id
            ORDER BY c.created_at ASC
        ");
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->execute();

        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Отправка результата в формате JSON
        echo json_encode([
            'success' => true,
            'comments' => $comments,
        ]);
    } catch (PDOException $e) {
        // Обработка ошибки базы данных
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка базы данных: ' . $e->getMessage(),
        ]);
    }
} else {
    // Если post_id не передан или равен 0
    echo json_encode([
        'success' => false,
        'message' => 'Неверный идентификатор поста.',
    ]);
}

exit();
?>
