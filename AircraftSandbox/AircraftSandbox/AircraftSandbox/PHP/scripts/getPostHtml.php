<?php
// scripts/getPostHtml.php

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../clases/post.php';

use PHP\Clases\Post;

if (empty($_GET['post'])) {
    http_response_code(400);
    exit('<p>Missing post ID</p>');
}

$postId = $_GET['post'];

try {
    $post = new Post();
    $post->loadFromDB($postId);
    // возвращаем именно HTML разметку поста
    echo $post->createPost();
} catch (Exception $e) {
    http_response_code(404);
    echo '<p>Post not found</p>';
}
