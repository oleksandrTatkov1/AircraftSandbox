<?php
require_once __DIR__ . '/../utils/firebasePublisher.php';
require_once __DIR__ . '/../clases/User.php';
require_once __DIR__ . '/../clases/userPosts.php';


use PHP\Utils\FirebasePublisher;
use PHP\Clases\UserInfo;

header('Content-Type: text/plain; charset=utf-8');

try {
    if (!isset($_POST['login']) || !isset($_POST['postId'])) {
        http_response_code(400);
        echo "Помилка: не вказані обов'язкові дані.";
        exit;
    }

    $login = $_POST['login'];
    $postId = $_POST['postId'];

    // Удаляем сам пост
    $firebase = new FirebasePublisher();
    $firebase->publish("posts/$postId", null);

    // Удаляем все комментарии связанные с этим постом
    $userInfo = new UserInfo();
    $userInfo->deleteAllCommentsByPostId($postId);

    echo "✅ Пост ID $postId та всі пов'язані коментарі успішно видалено!";
} catch (Exception $e) {
    http_response_code(500);
    echo "❌ Помилка сервера: " . $e->getMessage();
}