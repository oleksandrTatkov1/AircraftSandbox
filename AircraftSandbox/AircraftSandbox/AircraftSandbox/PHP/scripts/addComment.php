<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_login'])) {
    echo json_encode(['success' => false, 'message' => 'Ви не авторизовані']);
    exit;
}

$user = $_SESSION['user_login'];
$postId = isset($_POST['postId']) ? (int)$_POST['postId'] : 0;
$text = trim($_POST['comment'] ?? '');

if ($postId <= 0 || $text === '') {
    echo json_encode(['success' => false, 'message' => 'Некоректні дані']);
    exit;
}

try {
    $db = new PDO("sqlite:../../sqlite/users.db");
    $stmt = $db->prepare("
        INSERT INTO UserInfo (UserLogin, PostId, CommentText, CommentDate)
        VALUES (:user, :post, :text, :date)
    ");
    $stmt->execute([
        ':user' => $user,
        ':post' => $postId,
        ':text' => $text,
        ':date' => date('Y-m-d H:i:s')
    ]);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}