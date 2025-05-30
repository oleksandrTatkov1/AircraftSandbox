<?php
require_once __DIR__ . '/../utils/firebasePublisher.php';
use PHP\Utils\FirebasePublisher;

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_GET['login']) || empty($_GET['login'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Не вказано логін користувача.']);
        exit;
    }

    $login = $_GET['login'];
    $firebase = new FirebasePublisher();

    // Перетворюємо login назад у справжній email
    $decodedLogin = str_replace(['_at_', '_dot_'], ['@', '.'], $login);

    // Отримуємо всі пости
    $allPosts = $firebase->getAll("posts");

    $result = [];
    if ($allPosts) {
        foreach ($allPosts as $id => $post) {
            if (isset($post['ownerLogin']) && $post['ownerLogin'] === $decodedLogin) {
                $result[] = [
                    'id' => $id,
                    'title' => $post['header'] ?? '(без назви)'
                ];
            }
        }
    }

    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Помилка сервера: ' . $e->getMessage()]);
}
