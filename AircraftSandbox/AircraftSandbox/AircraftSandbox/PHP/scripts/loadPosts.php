<?php
require_once '../clases/post.php';
require_once '../clases/user.php';
require_once '../clases/userPosts.php';


$db = new PDO('sqlite:../../sqlite/users.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->query("SELECT * FROM Post ORDER BY Id DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($posts)) {
    echo "Нет постов в базе данных.";
} else {
    foreach ($posts as $row) {
        $post = new Post();
        $post->id = $row['Id'];
        $post->header = $row['Header'];
        $post->imagePath = $row['ImagePath'];
        $post->content = $row['Content'];
        $post->likesCount = $row['LikesCount'];
        $post->dislikesCount = $row['DislikesCount'];
        $post->ownerLogin = $row['ownerLogin'];
        echo $post->createPost();
    }
}

?>
