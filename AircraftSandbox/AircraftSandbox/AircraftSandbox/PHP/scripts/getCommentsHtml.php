<?php
// getCommentsHtml.php
require_once __DIR__ . '/../clases/userPosts.php';
session_start();
header('Content-Type: text/html; charset=utf-8');

if (empty($_GET['post'])) {
    http_response_code(400);
    exit('No post id');
}

$token = $_SESSION['user_login'] ?? null;
if (!$token) {
    http_response_code(401);
    exit('Not authorized');
}

$postId = $_GET['post'];

echo \PHP\Clases\UserInfo::renderAllCommentsByPostId($postId);
