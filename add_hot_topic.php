<?php
session_start();
// Проверка, что пользователь авторизован
if (!isset($_SESSION['user_id'])) {
    die("Ошибка: Пользователь не авторизован.");
}

// Получаем ID пользователя из сессии
$user_id = $_SESSION['user_id'];

include 'includes/db.php';

// Функция для изменения размера изображения
function resizeImage($sourcePath, $destinationPath, $maxWidth, $maxHeight) {
    list($width, $height, $type) = getimagesize($sourcePath);
    $ratio = $width / $height;

    if ($width > $maxWidth || $height > $maxHeight) {
        if ($ratio > 1) {
            $newWidth = $maxWidth;
            $newHeight = $maxWidth / $ratio;
        } else {
            $newHeight = $maxHeight;
            $newWidth = $maxHeight * $ratio;
        }

        $image_p = imagecreatetruecolor($newWidth, $newHeight);

        switch ($type) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($image_p, $destinationPath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($image_p, $destinationPath);
                break;
            case IMAGETYPE_GIF:
                imagegif($image_p, $destinationPath);
                break;
        }

        return true;
    } else {
        return copy($sourcePath, $destinationPath);
    }
}

// Удаление темы
if (isset($_GET['delete'])) {
    $postId = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT image, file, video FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($post) {
        // Удаляем файлы
        if (!empty($post['image']) && file_exists($post['image'])) {
            unlink($post['image']);
        }
        if (!empty($post['file']) && file_exists($post['file'])) {
            unlink($post['file']);
        }
        if (!empty($post['video']) && file_exists($post['video'])) {
            unlink($post['video']);
        }

        // Удаляем запись из базы данных
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$postId]);

        echo "Тема успешно удалена!";
    } else {
        echo "Тема не найдена!";
    }
}

// Добавление новой темы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Директория для загрузки файлов
    $uploadDir = 'uploads/';
    $imagePath = null;
    $filePath = null;
    $videoPath = null;

    // Обрабатываем загрузку фото
    if (!empty($_FILES['image']['name'])) {
        $imageName = basename($_FILES['image']['name']);
        $tempImagePath = $uploadDir . time() . '_' . $imageName;
        $imagePath = $uploadDir . 'resized_' . time() . '_' . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $tempImagePath)) {
            // Изменение размера изображения
            if (!resizeImage($tempImagePath, $imagePath, 800, 600)) {
                $imagePath = null; // Если произошла ошибка, не сохраняем путь
            }
            unlink($tempImagePath); // Удаляем оригинал
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

    // Обрабатываем загрузку видео
    if (!empty($_FILES['video']['name'])) {
        $videoName = basename($_FILES['video']['name']);
        $videoPath = $uploadDir . time() . '_' . $videoName;

        if (move_uploaded_file($_FILES['video']['tmp_name'], $videoPath)) {
            echo "Видео загружено: $videoPath<br>";
        } else {
            echo "Ошибка загрузки видео.<br>";
        }
    }
 
    // Вставляем данные в базу данных
    try {
        $stmt = $conn->prepare("
            INSERT INTO posts (title, content, image, file, video, user_id)
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $content, $imagePath, $filePath, $videoPath, $_SESSION['user_id']]);

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
            
            <label for="video">Добавить видео</label>
            <input type="file" id="video" name="video" accept="video/*">

            <button type="submit">Добавить тему</button>
        </form>
        
        <h2>Список горячих тем</h2>
        <?php
        $stmt = $conn->query("SELECT id, title FROM posts ORDER BY id DESC");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<div>';
            echo '<p>' . htmlspecialchars($row['title']) . '</p>';
            echo '<a href="add_hot_topic.php?delete=' . $row['id'] . '" onclick="return confirm(\'Удалить эту тему?\');">Удалить</a>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
