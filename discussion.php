<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php
    $id = $_GET['id'];
    $discussion = $conn->query("SELECT * FROM discussions WHERE id = $id")->fetch_assoc();
    ?>
    <h1><?php echo $discussion['title']; ?></h1>
    <p><?php echo $discussion['content']; ?></p>
    <h2>Комментарии:</h2>
    <ul>
        <?php
        $comments = $conn->query("SELECT * FROM comments WHERE discussion_id = $id ORDER BY created_at DESC");
        while ($comment = $comments->fetch_assoc()) {
            echo "<li>" . $comment['content'] . "</li>";
        }
        ?>
    </ul>
    <form method="POST" action="add_comment.php">
        <input type="hidden" name="discussion_id" value="<?php echo $id; ?>">
        <textarea name="content" required></textarea>
        <button type="submit">Добавить комментарий</button>
    </form>
</body>
</html>
