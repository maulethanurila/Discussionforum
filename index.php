<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <div class="menu-container">
            <button id="menu-button">☰ Меню</button>
            <div id="menu-dropdown">
                <a href="profile.php">Мой профиль</a>
                <a href="likes.php">Лайки</a>
                <a href="comments.php">Комментарии</a>
                <a href="posts.php">Публикации</a>
                <a href="logout.php">Выйти</a>
            </div>
        </div>
        <h1>Добро пожаловать!</h1>
    </header>

    <main class="container">
        <section id="hot-topics">
            <h2>Горячие темы</h2>
            <?php include 'includes/load_hot_topics.php'; ?>
            <a href="add_hot_topic.php" class="button">Добавить новую тему</a>
        </section>
    </main>
</body>
<script>
    // Логика для выпадающего меню
    const menuButton = document.getElementById('menu-button');
    const menuDropdown = document.getElementById('menu-dropdown');

    menuButton.addEventListener('click', () => {
        menuDropdown.style.display = menuDropdown.style.display === 'block' ? 'none' : 'block';
    });

    window.addEventListener('click', (e) => {
        if (!menuButton.contains(e.target) && !menuDropdown.contains(e.target)) {
            menuDropdown.style.display = 'none';
        }
    });
</script>
</html>

