<?php
session_start();
include 'includes/db.php';
header('Content-Type: application/json');

// Получаем данные из POST-запроса
$postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$comment = isset($_POST['comment']) ? $_POST['comment'] : '';

if ($postId > 0 && !empty($comment)) {
    try {
        // Вставка комментария в базу данных
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$postId, $_SESSION['user_id'], htmlspecialchars($comment)]);

        // Получаем ID последнего вставленного комментария
        $commentId = $conn->lastInsertId();

        // Получаем имя пользователя
        $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Комментарий добавлен!',
            'comment_id' => $commentId,
            'username' => $user['username'] // Добавляем имя пользователя
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
        'message' => 'Некорректные данные.'
    ]);
}
?>
