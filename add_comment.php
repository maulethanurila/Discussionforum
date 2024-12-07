<?php
session_start();

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован.']);
    exit();
}

include 'includes/db.php';

// Получаем данные из запроса
$post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

// Проверка на корректность данных
if (empty($post_id) || empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные.']);
    exit();
}

// Подготовка SQL-запроса
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (:post_id, :user_id, :comment)");
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT); // Используем ID пользователя из сессии
$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);

// Выполнение запроса
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Комментарий добавлен.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении комментария.']);
}
?>
