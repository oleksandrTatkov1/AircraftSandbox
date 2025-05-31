<?php
require_once __DIR__ . '/../utils/firebasePublisher.php';
use PHP\Utils\FirebasePublisher;

header('Content-Type: text/plain; charset=utf-8');

if (!isset($_POST['login']) || !isset($_POST['postId']) || !isset($_POST['commentId'])) {
    http_response_code(400);
    echo "Помилка: відсутні обов'язкові параметри.";
    exit;
}

$login = $_POST['login'];
$postId = $_POST['postId'];
$commentId = $_POST['commentId'];

try {
    $firebase = new FirebasePublisher();
    $safeKey = $firebase->sanitizeKey($login) . '_' . $firebase->sanitizeKey($postId);
    $path = "userInfo/{$safeKey}/comments";

    $data = $firebase->getAll($path);

    if (is_array($data)) {
        foreach ($data as $index => $comment) {
            if (isset($comment['id']) && $comment['id'] === $commentId) {
                unset($data[$index]);
            }
        }

        // Зберігаємо оновлений список
        $firebase->publish($path, array_values($data));
    }

    echo "✅ Коментар успішно видалено.";
} catch (Exception $e) {
    http_response_code(500);
    echo "❌ Помилка сервера: " . $e->getMessage();
}
?>
