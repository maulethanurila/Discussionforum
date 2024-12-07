<?php
header('Content-Type: application/json');
include 'includes/db.php';
session_start(); // Убедитесь, что сессия активна

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Сессия пользователя не найдена.']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id']) && is_numeric($data['id']) && !empty($data['comment'])) {
    $postId = intval($data['id']);
    $comment = htmlspecialchars($data['comment']);

    try {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$postId, $_SESSION['user_id'], $comment]);

        echo json_encode(["success" => true, "message" => "Комментарий добавлен!"]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Ошибка базы данных: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Некорректные данные."]);
}
?>
