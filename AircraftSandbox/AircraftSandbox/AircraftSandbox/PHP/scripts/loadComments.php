<?php
header('Content-Type: application/json; charset=utf-8');

$postId = isset($_GET['postId']) ? (int)$_GET['postId'] : 0;
if ($postId <= 0) {
    echo json_encode([]);
    exit;
}

try {
    $db = new PDO("sqlite:../../sqlite/users.db");
    $stmt = $db->prepare("SELECT UserLogin, CommentText, CommentDate FROM UserInfo WHERE PostId = :postId AND CommentText IS NOT NULL ORDER BY CommentDate ASC");
    $stmt->execute([':postId' => $postId]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Throwable $e) {
    echo json_encode([]);
}