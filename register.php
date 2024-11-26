<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Хэшируем пароль

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");

    try {
        $stmt->execute([$username, $email, $password]);
        echo "Регистрация прошла успешно!";
        header("Location: login.html"); // Перенаправляем на страницу входа
    } catch (PDOException $e) {
        echo "Ошибка регистрации: " . $e->getMessage();
    }
}
?>
