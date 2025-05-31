<?php
// scripts/getPostHtml.php

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../clases/post.php';
require_once __DIR__ . '/../clases/userPosts.php';
use PHP\Clases\Post;
use PHP\Clases\UserInfo;
if (empty($_GET['post'])) {
    http_response_code(400);
    exit('<p>Missing post ID</p>');
}

$postId = $_GET['post'];

try {
    $post = new Post();
    $post->loadFromDB($postId);
    // возвращаем именно HTML разметку поста
    echo UserInfo::renderPostThreadFromPost($post);
} catch (Exception $e) {
    http_response_code(404);
    echo '<p>Post not found</p>';
}
