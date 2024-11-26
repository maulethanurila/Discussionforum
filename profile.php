<?php
session_start();
include 'includes/db.php'; // Подключение к базе данных

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Запрос данных пользователя
    $query = $conn->prepare("SELECT username, email, profile_image FROM users WHERE id = ?");
    $query->execute([$user_id]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    // Если пользователь не найден
    if (!$user) {
        throw new Exception("Пользователь с ID $user_id не найден.");
    }
} catch (Exception $e) {
    // Обрабатываем ошибку
    die("Ошибка: " . $e->getMessage());
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Обновление имени пользователя
    if (!empty($_POST['username'])) {
        $new_username = trim($_POST['username']);
        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->execute([$new_username, $user_id]);
        $_SESSION['success'] = "Имя пользователя обновлено!";
        $user['username'] = $new_username; // Обновляем данные в массиве
    }

    // Обновление пароля
    if (!empty($_POST['password'])) {
        $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_password, $user_id]);
        $_SESSION['success'] = "Пароль успешно обновлен!";
    }

    // Загрузка фото профиля
    if (!empty($_FILES['profile_image']['name'])) {
        $image_name = $_FILES['profile_image']['name'];
        $image_tmp = $_FILES['profile_image']['tmp_name'];
        $upload_dir = "uploads/profile_images/";
        $image_path = $upload_dir . uniqid() . "_" . $image_name;

        // Перемещаем файл в папку uploads
        if (move_uploaded_file($image_tmp, $image_path)) {
            $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->execute([$image_path, $user_id]);
            $_SESSION['success'] = "Фото профиля обновлено!";
            $user['profile_image'] = $image_path; // Обновляем данные в массиве
        } else {
            $_SESSION['error'] = "Ошибка загрузки файла.";
        }
    }

    header("Location: profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой профиль</title>
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
<div class="profile-container">
    <div class="profile-header">
        <!-- Фото профиля -->
        <div class="profile-image-container">
            <?php if (!empty($user['profile_image'])): ?>
                <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Фото профиля" class="profile-image">
            <?php else: ?>
                <img src="default-profile.png" alt="Фото профиля" class="profile-image">
            <?php endif; ?>
        </div>

        <!-- Информация пользователя -->
        <div class="profile-info">
            <h2><?= htmlspecialchars($user['username']) ?></h2>
            <p><?= htmlspecialchars($user['email']) ?></p>
        </div>
    </div>

    <!-- Форма редактирования -->
    <div class="profile-edit">
        <form method="POST" enctype="multipart/form-data">
            <label for="username">Имя пользователя:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

            <label for="profile_image">Фото профиля:</label>
            <input type="file" id="profile_image" name="profile_image">

            <label for="password">Новый пароль:</label>
            <input type="password" id="password" name="password" placeholder="Введите новый пароль">

            <button type="submit">Сохранить изменения</button>
        </form>
    </div>
</div>
</body>
</html>
