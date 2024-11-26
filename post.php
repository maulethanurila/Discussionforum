<?php
include 'includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image = ''; // Заглушка для изображения

    // Обработка загруженного изображения
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $image = $upload_dir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, image) VALUES (?, ?, ?, ?)");

    try {
        $stmt->execute([$user_id, $title, $content, $image]);
        echo "Публикация успешно добавлена!";
    } catch (PDOException $e) {
        echo "Ошибка публикации: " . $e->getMessage();
    }
} else {
    echo "Необходимо войти в систему.";
}
?>
