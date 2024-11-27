<?php
// Подключение к базе данных
include 'includes/db.php';

session_start();

// Проверяем, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Хэшируем пароль

    try {
        // Проверяем, есть ли пользователь с таким именем или email
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $_SESSION['error'] = "Пользователь с таким именем или email уже существует.";
            header("Location: register.html");
            exit();
        }

        // Добавляем нового пользователя
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);

        $_SESSION['success'] = "Регистрация прошла успешно! Вы можете войти.";
        header("Location: login.html");
        exit();
    } catch (PDOException $e) {
        // Ошибка при работе с базой данных
        $_SESSION['error'] = "Ошибка базы данных: " . $e->getMessage();
        header("Location: register.html");
        exit();
    }
} else {
    // Если доступ не через POST, перенаправляем на форму регистрации
    header("Location: register.html");
    exit();
}
?>
