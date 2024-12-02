<?php
if (isset($_GET['id'])) {
    $postId = intval($_GET['id']);
    $url = "http://yourwebsite.com/article.php?id=" . $postId;
    echo "Поделитесь этой ссылкой: <a href='$url'>$url</a>";
} else {
    echo "Тема не найдена.";
}
?>
