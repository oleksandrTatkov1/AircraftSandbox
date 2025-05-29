<?php
// scripts/updateLikes.php

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../clases/post.php';
require_once __DIR__ . '/../clases/userPosts.php';

use PHP\Clases\Post;
use PHP\Clases\UserInfo;

try {
    // 1) Авторизація
    if (empty($_SESSION['user_login'])) {
        throw new Exception('Unauthorized', 401);
    }
    $currentUser = $_SESSION['user_login'];

    // 2) Параметри
    $postId = trim($_POST['postId']  ?? '');
    $action = trim($_POST['action']  ?? '');
    if ($postId === '' || !in_array($action, ['like','dislike'], true)) {
        throw new Exception('Invalid parameters');
    }

    // 3) Завантажуємо пост
    $post = new Post();
    $post->loadFromDB($postId);

    // 4) Існуюча або нова UserInfo
    $ui = UserInfo::getForUserAndPost(null, $currentUser, $postId);
    if (!$ui) {
        $ui = new UserInfo();
        // задаємо id для ключа: sanitize(user)_sanitize(post)_id
        $ui->UserLogin   = $currentUser;
        $ui->PostId      = $postId;
        // можете генерувати власний id, тут — використовуємо постId
        $ui->id          = $postId;
        $ui->Reaction    = 0;
        $ui->CommentText = '';
        $ui->CommentDate = null;
    }
    $prev = (int)$ui->Reaction;

    // 5) Toggle
    $new = $action === 'like'
         ? ($prev === 1 ? 0 : 1)
         : ($prev === -1 ? 0 : -1);

    // 6) Коригуємо лічильники
    if ($prev === 1   && $new !== 1)  $post->likesCount--;
    if ($prev === -1  && $new !== -1) $post->dislikesCount--;
    if ($new === 1    && $prev !== 1) $post->likesCount++;
    if ($new === -1   && $prev !== -1)$post->dislikesCount++;

    // 7) Зберігаємо
    $post->saveToDB();

    $ui->Reaction    = $new;
    $ui->CommentDate = date(DATE_ISO8601);
    $ui->saveToDB();

    // 8) Відправляємо JSON
    echo json_encode([
        'success'       => true,
        'likesCount'    => $post->likesCount,
        'dislikesCount' => $post->dislikesCount,
        'reaction'      => $new,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code($e->getCode() === 401 ? 401 : 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
