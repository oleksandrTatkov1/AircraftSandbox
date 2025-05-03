<?php
session_start();
require_once '../../PHP/clases/Post.php';

$db = new PDO('sqlite:../../sqlite/users.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$uploadDir = realpath(__DIR__ . '/../../img/posts') . DIRECTORY_SEPARATOR;

$imageBaseURL = '../AircraftSandbox/img/posts/';
$imagePath = $imageBaseURL . 'defaultimage.jpg';

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $filename = basename($_FILES['image']['name']);
    $targetFile = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $imagePath = $imageBaseURL . $filename; 
    }
}
if (!isset($_SESSION['user_login'])) {
    error_log("❗ НЕМАЄ user_login в сесії!");
} else {
    error_log("✅ Сесія знайдена, логін: " . $_SESSION['user_login']);
}

$post = new Post();
$post->header = $_POST['header'] ?? 'Новий пост';
$post->content = $_POST['content'] ?? '';
$post->likesCount = 0;
$post->dislikesCount = 0;
$post->imagePath = $imagePath;
$post->ownerLogin = $_SESSION['user_login'] ?? 'guest';

if ($post->saveToDB($db)) {
    echo $post->createPost();
} else {
    echo "❌ Помилка збереження поста.";
}

?>
