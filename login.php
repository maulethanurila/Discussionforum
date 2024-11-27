<?php
// Подключение к базе данных
include 'includes/db.php';

session_start();

// Проверяем, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        // Проверяем, существует ли пользователь с таким именем
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Сохраняем информацию о пользователе в сессии
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Перенаправляем на главную страницу
            header("Location: index.php");
            exit();
        } else {
            // Неправильные имя пользователя или пароль
            $_SESSION['error'] = "Неправильное имя пользователя или пароль.";
            header("Location: login.html");
            exit();
        }
    } catch (PDOException $e) {
        // Ошибка при работе с базой данных
        $_SESSION['error'] = "Ошибка базы данных: " . $e->getMessage();
        header("Location: login.html");
        exit();
    }
} else {
    // Если доступ не через POST, перенаправляем на форму входа
    header("Location: login.html");
    exit();
}
?>
