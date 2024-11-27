<?php
include 'includes/db.php';

// Проверяем, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Директория для загрузки файлов
    $uploadDir = 'uploads/';
    $imagePath = null;
    $filePath = null;

    // Обрабатываем загрузку фото
    if (!empty($_FILES['image']['name'])) {
        $imageName = basename($_FILES['image']['name']);
        $imagePath = $uploadDir . time() . '_' . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            echo "Фото загружено: $imagePath<br>";
        } else {
            echo "Ошибка загрузки фото.<br>";
        }
    }

    // Обрабатываем загрузку файла
    if (!empty($_FILES['file']['name'])) {
        $fileName = basename($_FILES['file']['name']);
        $filePath = $uploadDir . time() . '_' . $fileName;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
            echo "Файл загружен: $filePath<br>";
        } else {
            echo "Ошибка загрузки файла.<br>";
        }
    }

    // Вставляем данные в базу данных
    try {
        $stmt = $conn->prepare("
            INSERT INTO posts (title, content, image, file, user_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $content, $imagePath, $filePath, 1]); // user_id = 1 (замените на текущего пользователя)

        echo "Горячая тема добавлена успешно!";
    } catch (PDOException $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить горячую тему</title>
    <link rel="stylesheet" href="css/auth-styles.css">
</head>
<body>
    <div class="container">
        <h1>Добавить горячую тему</h1>
        <form method="post" action="add_hot_topic.php" enctype="multipart/form-data">
            <label for="title">Заголовок</label>
            <input type="text" id="title" name="title" required>
            
            <label for="content">Описание</label>
            <textarea id="content" name="content" rows="5" required></textarea>
            
            <label for="image">Добавить изображение</label>
            <input type="file" id="image" name="image" accept="image/*">
            
            <label for="file">Добавить файл</label>
            <input type="file" id="file" name="file">
            
            <button type="submit">Добавить тему</button>
        </form>
    </div>
</body>
</html>

