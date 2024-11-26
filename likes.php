<?php
include 'includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'], $_POST['post_id'])) {
    $user_id = $_SESSION['user_id'];
    $post_id = intval($_POST['post_id']);

    // Проверяем, был ли уже поставлен лайк
    $stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$user_id, $post_id]);

    if ($stmt->rowCount() === 0) {
        // Если лайка ещё нет, добавляем его
        $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $post_id]);
        echo "Лайк добавлен!";
    } else {
        echo "Вы уже поставили лайк.";
    }
} else {
    echo "Ошибка. Необходима авторизация.";
}
?>
