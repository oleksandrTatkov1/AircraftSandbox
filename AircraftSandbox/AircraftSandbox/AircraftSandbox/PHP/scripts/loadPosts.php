<?php
// scripts/loadPosts.php

require_once __DIR__ . '/../Clases/Post.php';
require_once __DIR__ . '/../Clases/userPosts.php';
require_once __DIR__ . '/../Utils/firebasePublisher.php';

use PHP\Clases\Post;
use PHP\Clases\UserInfo;
use PHP\Utils\FirebasePublisher;

session_start();
header('Content-Type: text/html; charset=utf-8');

$firebase = new FirebasePublisher();
$allPosts = $firebase->getAll('posts');

if (!is_array($allPosts) || empty($allPosts)) {
    echo '';
    exit;
}

$html = '';
foreach ($allPosts as $key => $data) {
    $post = new Post();
    $post->id            = $key;
    $post->header        = $data['header'] ?? '';
    $post->content       = $data['content'] ?? '';
    $post->imagePath     = $data['imagePath'] ?? '';
    $post->likesCount    = intval($data['likesCount'] ?? 0);
    $post->dislikesCount = intval($data['dislikesCount'] ?? 0);
    $post->ownerLogin    = $data['ownerLogin'];
    
    $html .= $post->createPost();
}

echo $html;
