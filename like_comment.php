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

// Запрос для проверки, ставил ли уже пользователь лайк этому комментарию
$query = "SELECT * FROM comment_likes WHERE comment_id = :comment_id AND user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    echo json_encode(['message' => 'Вы уже поставили лайк этому комментарии']);
    exit();
}

// Вставляем лайк в таблицу
$query = "INSERT INTO comment_likes (comment_id, user_id) VALUES (:comment_id, :user_id)";
$stmt = $conn->prepare($query);
$stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

// Увеличиваем счетчик лайков для комментария
$query = "UPDATE comments SET likes = likes + 1 WHERE id = :comment_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
$stmt->execute();

echo json_encode(['message' => 'Лайк поставлен']);
?>
