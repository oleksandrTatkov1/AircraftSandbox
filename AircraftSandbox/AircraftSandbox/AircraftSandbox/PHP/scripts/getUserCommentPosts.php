<?php
require_once __DIR__ . '/../utils/firebasePublisher.php';
use PHP\Utils\FirebasePublisher;

header('Content-Type: application/json');

if (!isset($_GET['login'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing login parameter']);
    exit;
}

$login = $_GET['login'];

try {
    $firebase = new FirebasePublisher();
    $allUserInfo = $firebase->getAll('userInfo');
    $allPosts = $firebase->getAll('posts');

    $posts = [];
    if (is_array($allUserInfo)) {
        foreach ($allUserInfo as $key => $record) {
            if (($record['UserLogin'] ?? '') === $login && !empty($record['PostId'])) {
                $postId = $record['PostId'];
                $postTitle = $allPosts[$postId]['header'] ?? '(Без назви)';
                $posts[$postId] = [
                    'PostId' => $postId,
                    'Title' => $postTitle
                ];
            }
        }
    }

    echo json_encode(array_values($posts));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
