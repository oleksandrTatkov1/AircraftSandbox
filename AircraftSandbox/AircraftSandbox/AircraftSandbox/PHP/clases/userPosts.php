<?php
require_once 'User.php';
require_once 'Post.php';

class UserInfo extends User {
    public $posts = []; // список постів цього юзера

    public function addPost($db, Post $post) {
        if (empty($this->Login)) {
            throw new Exception("User login is not set.");
        }

        $post->ownerLogin = $this->Login;
        if (!$post->saveToDB($db)) {
            throw new Exception("Failed to save post.");
        }

        return true;
    }

    public function deletePost($db, $postId) {
        if (empty($this->Login)) {
            throw new Exception("User login is not set.");
        }

        if (!is_numeric($postId)) {
            throw new Exception("Invalid post ID.");
        }

        $stmt = $db->prepare("DELETE FROM Post WHERE id = :id AND ownerLogin = :login");
        if (!$stmt) {
            throw new Exception("Failed to prepare delete statement.");
        }

        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
        $stmt->bindParam(':login', $this->Login);

        if (!$stmt->execute()) {
            throw new Exception("Failed to delete post.");
        }

        if ($stmt->rowCount() === 0) {
            throw new Exception("No post found or not authorized.");
        }

        return true;
    }

    public function loadUserPosts($db) {
        if (empty($this->Login)) {
            throw new Exception("User login is not set.");
        }

        $stmt = $db->prepare("SELECT * FROM Post WHERE ownerLogin = :login");
        if (!$stmt) {
            throw new Exception("Failed to prepare select statement.");
        }

        $stmt->bindParam(':login', $this->Login);

        if (!$stmt->execute()) {
            throw new Exception("Failed to load posts.");
        }

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
            if (!method_exists($post, 'createPost')) {
                throw new Exception("Post object missing createPost method.");
            }
            $html .= $post->createPost();
        }
        return $html;
    }
}
?>
