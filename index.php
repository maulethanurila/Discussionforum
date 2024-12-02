<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Подключаем базу данных
include 'includes/db.php';

// Запрос на получение всех тем
$stmt = $conn->query("SELECT id, title, content, image, video FROM posts ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <header>
        <h1>Добро пожаловать!</h1>
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
    </header>

    <main class="container">
        <section id="hot-topics">
            <h2>Горячие темы</h2>
            <a href="add_hot_topic.php" class="button">Добавить новую тему</a>
            
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="post-container">
                    <div class="media-container">
                        <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>" alt="Изображение" class="media">
                        <?php endif; ?>
                        <?php if (!empty($row['video']) && file_exists($row['video'])): ?>
                            <video class="media" width="300" height="auto" autoplay muted loop>
                                <source src="<?= htmlspecialchars($row['video']) ?>" type="video/mp4">
                                Ваш браузер не поддерживает видео
                            </video>
                        <?php endif; ?>
                    </div>

                    <div class="text-container">
                        <h2><?= htmlspecialchars($row['title']) ?></h2>
                        <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                        
                        <div class="post-actions">
                            <button class="like-button" data-post-id="<?= $row['id'] ?>">Лайк</button>
                            <button class="comment-button" data-post-id="<?= $row['id'] ?>">Комментарии</button>
                            <button class="share-button" data-post-id="<?= $row['id'] ?>">Поделиться</button>
                        </div>

                        <!-- Форма для добавления комментариев -->
                        <div class="comment-form" id="comment-form-<?= $row['id'] ?>">
                            <textarea id="comment-text-<?= $row['id'] ?>" placeholder="Напишите комментарий..." rows="4"></textarea>
                            <button class="submit-comment" data-post-id="<?= $row['id'] ?>">Отправить</button>
                        </div>

                        <div class="comments-list" id="comments-list-<?= $row['id'] ?>">
                            <!-- Здесь будут отображаться комментарии -->
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Ваша компания. Все права защищены.</p>
    </footer>

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

    // Лайк поста
    document.querySelectorAll('.like-button').forEach(button => {
        button.addEventListener('click', (event) => {
            const postId = button.getAttribute('data-post-id');
            fetch(`like.php?id=${postId}`)
                .then(response => response.json())
                .then(data => {
                    alert(data.message || 'Пост понравился!');
                })
                .catch(err => console.error('Ошибка:', err));
        });
    });

    // Отправка комментариев
    document.querySelectorAll('.submit-comment').forEach(button => {
        button.addEventListener('click', () => {
            const postId = button.getAttribute('data-post-id');
            const commentText = document.getElementById(`comment-text-${postId}`).value;

            if (commentText.trim() === "") {
                alert("Пожалуйста, напишите комментарий.");
                return;
            }

            fetch(`add_comment.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}&comment=${encodeURIComponent(commentText)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`comment-text-${postId}`).value = ""; // Очистка поля ввода
                    loadComments(postId); // Перезагрузка комментариев
                } else {
                    alert("Ошибка при добавлении комментария.");
                }
            })
            .catch(err => console.error('Ошибка:', err));
        });
    });

    // Функция для загрузки комментариев
    function loadComments(postId) {
        fetch(`get_comments.php?post_id=${postId}`)
            .then(response => {
                // Проверим, если это не JSON
                if (!response.ok) {
                    throw new Error('Ошибка загрузки комментариев');
                }
                return response.text(); // Читаем как текст для диагностики
            })
            .then(data => {
                try {
                    // Пытаемся парсить данные как JSON
                    const jsonData = JSON.parse(data);
                    const commentsList = document.getElementById(`comments-list-${postId}`);
                    commentsList.innerHTML = ""; // Очистить текущие комментарии

                    if (jsonData.comments && jsonData.comments.length > 0) {
                        jsonData.comments.forEach(comment => {
                            const commentItem = document.createElement("div");
                            commentItem.classList.add("comment-item");
                            commentItem.innerHTML = `
                                <p>${comment.text}</p>
                                <button class="like-comment" data-comment-id="${comment.id}">Лайк</button>
                                <button class="dislike-comment" data-comment-id="${comment.id}">Дизлайк</button>
                            `;
                            commentsList.appendChild(commentItem);
                        });
                    } else {
                        commentsList.innerHTML = "<p>Нет комментариев</p>";
                    }
                } catch (error) {
                    console.error("Ошибка при парсинге JSON:", error);
                    console.log("Ответ от сервера:", data); // Логируем оригинальный ответ
                    alert("Ошибка при загрузке комментариев. Ответ сервера: " + data);
                }
            })
            .catch(err => {
                console.error('Ошибка:', err);
                alert('Ошибка при загрузке комментариев. Проверьте соединение или сервер.');
            });
    }

    // Загрузка комментариев при загрузке страницы
    document.querySelectorAll('.post-container').forEach(post => {
        const postId = post.querySelector('.comment-button').getAttribute('data-post-id');
        loadComments(postId);
    });
    </script>
</body>
</html>
