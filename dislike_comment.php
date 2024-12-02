<?php
session_start();

// Подключаем базу данных
include 'includes/db.php';

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Пользователь не авторизован']);
    exit();
}

// Проверяем наличие параметра 'id' в запросе
if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Не указан комментарий']);
    exit();
}

$comment_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Запрос для проверки, ставил ли уже пользователь дизлайк этому комментарию
$query = "SELECT * FROM comment_dislikes WHERE comment_id = :comment_id AND user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    echo json_encode(['message' => 'Вы уже поставили дизлайк этому комментарии']);
    exit();
}

// Вставляем дизлайк в таблицу
$query = "INSERT INTO comment_dislikes (comment_id, user_id) VALUES (:comment_id, :user_id)";
$stmt = $conn->prepare($query);
$stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

// Увеличиваем счетчик дизлайков для комментария
$query = "UPDATE comments SET dislikes = dislikes + 1 WHERE id = :comment_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
$stmt->execute();

echo json_encode(['message' => 'Дизлайк поставлен']);
?>
