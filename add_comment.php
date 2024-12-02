<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован.']);
    exit();
}

// Подключаем базу данных
include 'includes/db.php';

// Получаем данные из запроса
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Комментарий не может быть пустым.']);
    exit();
}

// Проверяем, существует ли пост с таким ID
$stmt = $conn->prepare("SELECT id FROM posts WHERE id = :post_id");
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    echo json_encode(['success' => false, 'message' => 'Пост не найден.']);
    exit();
}

// Добавляем комментарий в базу данных
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (:post_id, :user_id, :comment, NOW())");
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Комментарий успешно добавлен.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении комментария.']);
}
?>
