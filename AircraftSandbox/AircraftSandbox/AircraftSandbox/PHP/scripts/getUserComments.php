<?php
require_once __DIR__ . '/../utils/firebasePublisher.php';
use PHP\Utils\FirebasePublisher;

header('Content-Type: application/json');

if (!isset($_GET['login']) || !isset($_GET['postId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing login or postId parameter']);
    exit;
}

$login = $_GET['login'];
$postId = $_GET['postId'];

try {
    $firebase = new FirebasePublisher();
    $safeKey = $firebase->sanitizeKey($login) . '_' . $firebase->sanitizeKey($postId);
    $data = $firebase->getAll("userInfo/{$safeKey}");

    $comments = [];
    if (isset($data['comments']) && is_array($data['comments'])) {
        foreach ($data['comments'] as $c) {
            $comments[] = [
                'id' => $c['id'] ?? '',
                'text' => $c['text'] ?? ''
            ];
        }
    }

    echo json_encode($comments);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
