<?php
include 'includes/db.php';

// Получаем горячие темы (сортируем по количеству лайков)
$query = $conn->query("
    SELECT posts.id, posts.title, posts.content, posts.image, users.username, COUNT(likes.id) AS like_count
    FROM posts
    LEFT JOIN users ON posts.user_id = users.id
    LEFT JOIN likes ON posts.id = likes.post_id
    GROUP BY posts.id
    ORDER BY like_count DESC
    LIMIT 5
");

$posts = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($posts as $post): ?>
    <div class="post">
        <h2><?= htmlspecialchars($post['title']) ?></h2>
        <p><?= htmlspecialchars($post['content']) ?></p>
        <?php if (!empty($post['image'])): ?>
            <img src="<?= htmlspecialchars($post['image']) ?>" alt="Изображение">
        <?php endif; ?>
        <p>Автор: <?= htmlspecialchars($post['username']) ?></p>
        <p>Лайков: <?= $post['like_count'] ?></p>
    </div>
<?php endforeach; ?>
