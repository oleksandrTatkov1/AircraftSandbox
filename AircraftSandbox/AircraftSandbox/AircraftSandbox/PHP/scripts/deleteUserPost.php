<?php
require_once __DIR__ . '/../utils/firebasePublisher.php';
use PHP\Utils\FirebasePublisher;

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

    $path = "posts/$postId";
    $firebase->publish($path, null);  // null видаляє запис у Firebase

    echo "✅ Пост ID $postId успішно видалено!";
} catch (Exception $e) {
    http_response_code(500);
    echo "❌ Помилка сервера: " . $e->getMessage();
}
