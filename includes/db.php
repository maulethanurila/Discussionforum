<?php
$host = 'localhost';    // Хост сервера MySQL
$dbname = 'forum';      // Имя базы данных
$user = 'root';         // Имя пользователя MySQL
$pass = '';             // Пароль (оставьте пустым, если не установлен)

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>
