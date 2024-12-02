<?php
include 'includes/db.php';

// Получаем ID темы из URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Получаем информацию о теме из базы данных
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    echo "Тема не найдена.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link rel="stylesheet" href="css/article.css">
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>

        <?php if (!empty($post['image'])): ?>
            <img src="<?php echo $post['image']; ?>" alt="Изображение темы" style="max-width: 100%; margin: 20px 0;">
        <?php endif; ?>

        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>

        <?php if (!empty($post['file'])): ?>
            <p><a href="<?php echo $post['file']; ?>" download>Скачать файл</a></p>
        <?php endif; ?>

        <!-- Блок взаимодействия -->
        <div class="interactions">
            <button id="like-button">👍 Лайк</button>
            <textarea id="comment-box" placeholder="Написать комментарий..."></textarea>
            <button id="submit-comment">Отправить</button>
            <button id="copy-link">Копировать ссылку</button>
        </div>
    </div>

    <script>
        // Лайки
        let likes = 0;
        document.getElementById('like-button').addEventListener('click', () => {
            likes++;
            alert(`Лайков: ${likes}`);
        });

        // Комментарии
        document.getElementById('submit-comment').addEventListener('click', () => {
            const comment = document.getElementById('comment-box').value;
            if (comment.trim()) {
                alert(`Ваш комментарий: ${comment}`);
            } else {
                alert("Введите комментарий перед отправкой.");
            }
        });

        // Копирование ссылки
        document.getElementById('copy-link').addEventListener('click', () => {
            const link = window.location.href;
            navigator.clipboard.writeText(link).then(() => {
                alert("Ссылка скопирована!");
            });
        });
    </script>
</body>
</html>
