<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <h1>Добавить новую дискуссию</h1>
    <form method="POST" action="save_discussion.php">
        <input type="text" name="title" required placeholder="Заголовок">
        <textarea name="content" required placeholder="Текст обсуждения"></textarea>
        <button type="submit">Добавить</button>
    </form>
</body>
</html>
