<?php
include 'includes/db.php';

try {
    $query = $conn->query("SELECT * FROM users");
    $users = $query->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($users);
    echo "</pre>";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>
