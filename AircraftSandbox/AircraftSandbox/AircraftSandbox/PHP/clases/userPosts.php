<?php
require_once 'User.php';
require_once 'Post.php';

class UserInfo extends User {
    public $posts = []; // список постів цього юзера

    public function addPost($db, Post $post) {
        $post->ownerLogin = $this->Login;
        return $post->saveToDB($db);
    }

    public function deletePost($db, $postId) {
        $stmt = $db->prepare("DELETE FROM Post WHERE id = :id AND ownerLogin = :login");
        $stmt->bindParam(':id', $postId);
        $stmt->bindParam(':login', $this->Login);
        return $stmt->execute();
    }

    public function loadUserPosts($db) {
        $stmt = $db->prepare("SELECT * FROM Post WHERE ownerLogin = :login");
        $stmt->bindParam(':login', $this->Login);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->posts = [];
        foreach ($results as $row) {
            $post = new Post();
            $post->id = $row['id'];
            $post->header = $row['header'];
            $post->imagePath = $row['imagePath'];
            $post->content = $row['content'];
            $post->likesCount = $row['likesCount'];
            $post->dislikesCount = $row['dislikesCount'];
            $post->ownerLogin = $row['ownerLogin'];
            $this->posts[] = $post;
        }

        return count($this->posts);
    }

    public function showAllPostsHTML() {
        $html = '';
        foreach ($this->posts as $post) {
            $html .= $post->createPost();
        }
        return $html;
    }
}

?>
