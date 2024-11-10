<?php
include 'db.php';
$title = $_POST['title'];
$content = $_POST['content'];
$conn->query("INSERT INTO discussions (title, content) VALUES ('$title', '$content')");
header("Location: index.php");
?>
