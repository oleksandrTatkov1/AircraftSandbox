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

    $firebase = new FirebasePublisher();
    $safeEmail = $firebase->sanitizeKey($login);

    // Видаляємо сам пост
    $path = "posts/$postId";
    $firebase->publish($path, null);

    // Видаляємо всі коментарі пов'язані з цим постом і користувачем
    $userInfo = new UserInfo();
    $userInfo->deleteCommentsByPostIdAndOwner($postId, $login);

    echo "✅ Пост ID $postId та всі пов'язані коментарі успішно видалено!";
} catch (Exception $e) {
    http_response_code(500);
    echo "❌ Помилка сервера: " . $e->getMessage();
}