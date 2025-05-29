<?php
// scripts/updateLikes.php

require_once __DIR__ . '/../Clases/Post.php';

use PHP\Clases\Post;

header('Content-Type: application/json');

// Read parameters
$postId = $_POST['postId'] ?? '';
$action = $_POST['action'] ?? '';

try {
    if (!in_array($action, ['like', 'dislike'], true)) {
        throw new Exception('Invalid action');
    }

    $post = new Post();
    $post->loadFromDB($postId);

    if ($action === 'like') {
        $post->likesCount++;
    } else {
        $post->dislikesCount++;
    }

    $post->saveToDB();

    echo json_encode([
        'success'       => true,
        'likesCount'    => $post->likesCount,
        'dislikesCount' => $post->dislikesCount,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
?>
