<?php
// Подключение к базе данных
include 'includes/db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        // Проверяем, есть ли пользователь с таким именем
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Сохраняем пользователя в сессии
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Перенаправляем на главную страницу
            header("Location: index.php");
            exit();
        } else {
            echo "Неправильное имя пользователя или пароль.";
        }
    } catch (PDOException $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}
?>
