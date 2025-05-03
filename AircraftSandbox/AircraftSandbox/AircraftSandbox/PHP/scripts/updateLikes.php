<?php
header('Content-Type: application/json');
if (!isset($_POST['postId'], $_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$postId = (int)$_POST['postId'];
$action = $_POST['action'];

$db = new PDO('sqlite:../../sqlite/users.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($action === 'like') {
    $db->exec("UPDATE Post SET LikesCount = LikesCount + 1 WHERE Id = $postId");
} elseif ($action === 'dislike') {
    $db->exec("UPDATE Post SET DislikesCount = DislikesCount + 1 WHERE Id = $postId");
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Вернуть обновлённые значения
$stmt = $db->query("SELECT LikesCount, DislikesCount FROM Post WHERE Id = $postId");
$data = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'likesCount' => $data['LikesCount'],
    'dislikesCount' => $data['DislikesCount']
]);
?>