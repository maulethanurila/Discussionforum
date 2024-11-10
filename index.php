<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <h1>Список обсуждений</h1>
    <a href="add_discussion.php">Добавить обсуждение</a>
    <ul>
        <?php
        $result = $conn->query("SELECT * FROM discussions ORDER BY created_at DESC");
        while ($row = $result->fetch_assoc()) {
            echo "<li><a href='discussion.php?id=" . $row['id'] . "'>" . $row['title'] . "</a></li>";
        }
        ?>
    </ul>
</body>
</html>
