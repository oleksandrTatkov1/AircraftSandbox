<?php
// scripts/getPost.php

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../clases/post.php';

use PHP\Clases\Post;

if (empty($_GET['post'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Missing post parameter'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$postId = $_GET['post'];

try {
    $post = new Post();
    $post->loadFromDB($postId);

    echo json_encode([
        'success'       => true,
        'id'            => $post->id,
        'header'        => $post->header,
        'content'       => $post->content,
        'imagePath'     => $post->imagePath,
        'likesCount'    => $post->likesCount,
        'dislikesCount' => $post->dislikesCount,
        'ownerLogin'    => $post->ownerLogin
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
