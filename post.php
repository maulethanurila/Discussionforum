<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Получаем ID текущего пользователя

// Подключение к базе данных
require 'includes/db.php'; // Подключение к базе с использованием PDO

// Получаем публикации текущего пользователя
$query = "SELECT id, title, content FROM posts WHERE user_id = :user_id ORDER BY id DESC";
$stmt = $conn->prepare($query);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои публикации</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: #f6f5f5;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        header {
            background: linear-gradient(90deg, #2c8d48, #3db774);
            padding: 20px;
            width: 100%;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        header h1 {
            margin: 0;
            font-size: 32px;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
        }
        .post-container {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .post-container h3 {
            color: #2c8d48;
        }
        .no-posts {
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>

<header>
    <h1>Мои публикации</h1>
</header>

<div class="container">
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <div class="post-container">
                <h3><?= htmlspecialchars($post['title']); ?></h3>
                <p><?= htmlspecialchars($post['content']); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-posts">У вас нет публикаций.</p>
    <?php endif; ?>
</div>

</body>
</html>
