<?php
require_once __DIR__ . '/../clases/Post.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "ID is required"]);
    exit;
}

$id = (int)$_GET['id'];

try {
    $dbFile = __DIR__ . '/../../sqlite/users.db';
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT * FROM Post WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Post not found"]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "header" => $post['Header'],
        "content" => $post['Content'],
        "imagePath" => $post['ImagePath'],
        "likesCount" => (int)$post['LikesCount'],
        "dislikesCount" => (int)$post['DislikesCount'],
        "ownerLogin" => $post['ownerLogin']
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}