<?php
// scripts/createPost.php

namespace PHP\Scripts;

// автозавантаження через composer чи вручну
require_once __DIR__ . '/../clases/post.php';
require_once __DIR__ . '/../clases/userPosts.php';

use PHP\Clases\Post;
use PHP\Clases\UserInfo;

session_start();

// 1) Авторизація
if (empty($_SESSION['user_login'])) {
    http_response_code(401);
    exit('Unauthorized');
}
$currentUser = $_SESSION['user_login'];

// 2) Зчитуємо поля форми
$header        = trim($_POST['header'] ?? '');
$content       = trim($_POST['content'] ?? '');
$imagePath     = ''; // за замовчуванням
$likesCount    = 0;
$dislikesCount = 0;

// Обробка завантаження зображення (за бажанням)
if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/posts/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $filename = basename($_FILES['image']['name']);
    $target   = $uploadDir . $filename;
    $tmpPath  = $_FILES['image']['tmp_name'];

    // Путь, где может лежать оригинальный файл (если tmp не существует)
    $originalSearchPaths = [
        $_SERVER['DOCUMENT_ROOT'] . '/' . $filename, // корень сайта
        __DIR__ . '/' . $filename                     // рядом со скриптом
        // добавь сюда пути, где могут лежать загруженные заранее файлы
    ];

    // Попробуем сначала переместить из временного места
    if (is_uploaded_file($tmpPath) && move_uploaded_file($tmpPath, $target)) {
        $imagePath = '/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/posts/' . $filename;
    } else {
        // Файл не переместился — ищем оригинальный файл
        foreach ($originalSearchPaths as $src) {
            if (file_exists($src)) {
                if (copy($src, $target)) {
                    $imagePath = '/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/posts/' . $filename;
                    break;
                }
            }
        }
    }
}



// 3) Генеруємо унікальний ID поста
$postId = (string) time();  // або будь-який власний генератор

// 4) Створюємо і зберігаємо Post
$post = new Post();
$post->id          = $postId;
$post->header      = $header;
$post->content     = $content;
$post->imagePath   = $imagePath;
$post->likesCount  = $likesCount;
$post->dislikesCount = $dislikesCount;
$post->saveToDB();  // тут має публікуватись тільки “posts/{$postId}`

// 5) Створюємо UserInfo-запис для зв’язку “юзер ↔ пост”
$userInfo = new UserInfo();
$userInfo->id = $currentUser;
$userInfo->UserLogin   = $currentUser;
$userInfo->PostId      = $postId;
$userInfo->Reaction    = 0;                  // без реакції
$userInfo->CommentText = '';                 // без коментаря
$userInfo->CommentDate = date(DATE_ISO8601); // зараз у ISO
$userInfo->saveToDB();  // публікує “userInfo/{$userLogin}_{$postId}_{$id}`

// 6) Відповідь — HTML лише нового поста
echo $post->createPost($currentUser);
