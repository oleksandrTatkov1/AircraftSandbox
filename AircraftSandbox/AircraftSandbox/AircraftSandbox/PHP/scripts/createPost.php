<?php
// scripts/createPost.php

require_once __DIR__ . '/../Clases/Post.php';

use PHP\Clases\Post;

header('Content-Type: text/html; charset=utf-8');
session_start();
if (empty($_SESSION['user_login'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$login = $_SESSION['user_login'];
$header       = trim($_POST['header'] ?? '');
$content      = trim($_POST['content'] ?? '');
$likesCount   = intval($_POST['likesCount'] ?? 0);
$dislikesCount= intval($_POST['dislikesCount'] ?? 0);

// Handle optional image upload
$imagePath = '';
if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/posts';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $filename = basename($_FILES['image']['name']);
    $target = $uploadDir . $filename;
    move_uploaded_file($_FILES['image']['tmp_name'], $target);
    $imagePath = '/posts/' . $filename;
}

// Use timestamp as unique ID (or use any other generator)
$postId = (string) time();

$post = new Post();
$post->id = $postId;
$post->header = $header;
$post->content = $content;
$post->imagePath = $imagePath;
$post->likesCount = $likesCount;
$post->dislikesCount = $dislikesCount;
$post->ownerLogin = $login;
$post->saveToDB();

// Return the HTML for the newly created post
echo $post->createPost();
?>
