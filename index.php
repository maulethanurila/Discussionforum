<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Подключаем базу данных
include 'includes/db.php';

// Получение параметров фильтра и поиска
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'new';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Базовый SQL-запрос
$sql = "SELECT id, title, content, image, video, likes FROM posts";

// Добавляем условия поиска
if (!empty($search)) {
    $sql .= " WHERE title LIKE :search OR content LIKE :search";
}

// Добавляем сортировку
switch ($filter) {
    case 'old':
        $orderBy = " ORDER BY id ASC"; // Старые
        break;
    case 'popular':
        $orderBy = " ORDER BY likes DESC"; // Популярные
        break;
    case 'new':
    default:
        $orderBy = " ORDER BY id DESC"; // Новые
        break;
}
$sql .= $orderBy;

// Подготовка и выполнение запроса
$stmt = $conn->prepare($sql);

// Если есть поиск, привязываем параметр поиска
if (!empty($search)) {
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}

$stmt->execute();
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
                <a href="post.php">Публикации</a>
                <a href="logout.php">Выйти</a>
            </div>
        </div>
    </header>

    <main class="container">
        <section id="hot-topics">
            <h2>Горячие темы</h2>
            <div id="filter-container">
                <label for="filter-select">Сортировать по:</label>
                <select id="filter-select">
                    <option value="new" <?= $filter == 'new' ? 'selected' : '' ?>>Новые</option>
                    <option value="old" <?= $filter == 'old' ? 'selected' : '' ?>>Старые</option>
                    <option value="popular" <?= $filter == 'popular' ? 'selected' : '' ?>>Популярные</option>
                </select>
            </div>

            <form method="get" id="search-container">
                <input id="search-input" type="text" name="search" placeholder="Поиск..." value="<?= htmlspecialchars($search) ?>">
                <button id="search-button" type="submit">Найти</button>
            </form>

            <a href="add_hot_topic.php" class="button">Добавить новую тему</a>

            <?php if ($stmt->rowCount() === 0): ?>
                <p>Ничего не найдено по вашему запросу.</p>
            <?php else: ?>
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

                            <p>Лайков: <?= $row['likes'] ?></p>

                            <div class="comment-form" id="comment-form-<?= $row['id'] ?>" style="display:none;">
                                <textarea id="comment-text-<?= $row['id'] ?>" placeholder="Напишите комментарий..." rows="4"></textarea>
                                <button class="submit-comment" data-post-id="<?= $row['id'] ?>">Отправить</button>
                            </div>

                            <div class="comments-list" id="comments-list-<?= $row['id'] ?>">
                                <!-- Комментарии загружаются через JS -->
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Ваша компания. Все права защищены.</p>
    </footer>

    <script>
    // Логика для выпадающего меню
    const menuButton = document.getElementById('menu-button');
    const menuDropdown = document.getElementById('menu-dropdown');

    menuButton.addEventListener('click', (e) => {
        e.stopPropagation();
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
                .then(data => alert(data.message || 'Пост понравился!'))
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

            console.log("Отправка комментария:", { postId, commentText }); // Логируем данные перед отправкой

            fetch(`add_comment.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}&comment=${encodeURIComponent(commentText)}`
            })
            .then(response => response.json())
            .then(data => {
                console.log("Ответ от сервера:", data); // Логируем ответ от сервера
                if (data.success) {
                    document.getElementById(`comment-text-${postId}`).value = "";
                    loadComments(postId);
                } else {
                    alert("Ошибка при добавлении комментария.");
                }
            })
            .catch(err => {
                console.error('Ошибка при отправке комментария:', err);
                alert("Ошибка при отправке комментария.");
            });
        });
    });

    // Загрузка комментариев
    function loadComments(postId) {
        fetch(`get_comments.php?post_id=${postId}`)
            .then(response => response.json())
            .then(data => {
                const commentsList = document.getElementById(`comments-list-${postId}`);
                commentsList.innerHTML = "";  // Очистка текущего списка комментариев

                if (data.success && data.comments && data.comments.length > 0) {
                    data.comments.forEach(comment => {
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
                    commentsList.innerHTML = "<p>Нет комментариев.</p>";
                }
            })
            .catch(err => {
                console.error('Ошибка при загрузке комментариев:', err);
                alert("Ошибка при загрузке комментариев.");
            });
    }

    // Загружаем комментарии при загрузке страницы
    document.querySelectorAll('.post-container').forEach(post => {
        const postId = post.querySelector('.comment-button').getAttribute('data-post-id');
        loadComments(postId);
    });

    // Показываем/скрываем форму для комментариев
    document.querySelectorAll('.comment-button').forEach(button => {
        button.addEventListener('click', (event) => {
            const postId = button.getAttribute('data-post-id');
            const commentForm = document.getElementById(`comment-form-${postId}`);
            commentForm.style.display = (commentForm.style.display === 'none' || commentForm.style.display === '') ? 'block' : 'none';
        });
    });

    // Обработчик для фильтрации
    document.getElementById('filter-select').addEventListener('change', function () {
        const filter = this.value;
        window.location.href = `index.php?filter=${filter}&search=${encodeURIComponent(document.getElementById('search-input').value)}`;
    });

    // Обработчик для поиска
    document.getElementById('search-button').addEventListener('click', function () {
        const searchValue = document.getElementById('search-input').value.trim();
        const filterValue = document.getElementById('filter-select').value;
        window.location.href = `index.php?search=${encodeURIComponent(searchValue)}&filter=${filterValue}`;
    });
    // Добавить обработчик для кнопки "Поделиться"
document.querySelectorAll('.share-button').forEach(button => {
    button.addEventListener('click', () => {
        const postId = button.getAttribute('data-post-id');
        const postUrl = `${window.location.origin}/post.php?id=${postId}`;
        navigator.clipboard.writeText(postUrl)
            .then(() => alert('Ссылка скопирована!'))
            .catch(err => console.error('Ошибка копирования:', err));
    });
});

    </script>
</body>
</html>
