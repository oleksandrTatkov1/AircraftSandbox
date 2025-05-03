<?php
class Post {
    public $id;
    public $header;
    public $imagePath;
    public $content;
    public $likesCount;
    public $dislikesCount;
    public $ownerLogin;
    public function __construct() {
        $this->id = 0;
        $this->header = '';
        $this->imagePath = '';
        $this->content = '';
        $this->likesCount = 0;
        $this->dislikesCount = 0;
        $this->ownerLogin = '';
    }

    public function loadFromDB($db, $id) {
        $stmt = $db->prepare("SELECT * FROM Post WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post) {
            $this->id = $post['id'];
            $this->header = $post['header'];
            $this->imagePath = $post['imagePath'];
            $this->content = $post['content'];
            $this->likesCount = $post['likesCount'];
            $this->dislikesCount = $post['dislikesCount'];
            $this->ownerLogin = $post['ownerLogin'];
            return true;
        } else {
            return false;
        }
    }

    public function saveToDB($db) {
        $stmt = $db->prepare("INSERT INTO Post (header, imagePath, content, likesCount, dislikesCount, ownerLogin) 
                              VALUES (:header, :imagePath, :content, :likesCount, :dislikesCount, :ownerLogin)");
        $stmt->bindParam(':header', $this->header);
        $stmt->bindParam(':imagePath', $this->imagePath);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':likesCount', $this->likesCount);
        $stmt->bindParam(':dislikesCount', $this->dislikesCount);
        $stmt->bindParam(':ownerLogin', $this->ownerLogin);
        return $stmt->execute();
    }

    public function updateToDB($db) {
        $stmt = $db->prepare("UPDATE Post SET header = :header, imagePath = :imagePath, content = :content, 
                              likesCount = :likesCount, dislikesCount = :dislikesCount, ownerLogin = :ownerLogin WHERE id = :id");
        $stmt->bindParam(':header', $this->header);
        $stmt->bindParam(':imagePath', $this->imagePath);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':likesCount', $this->likesCount);
        $stmt->bindParam(':dislikesCount', $this->dislikesCount);
        $stmt->bindParam(':ownerLogin', $this->ownerLogin);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function deleteFromDB($db) {
        $stmt = $db->prepare("DELETE FROM Post WHERE id = :id");
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function createPost() {
        $escapedLogin = htmlspecialchars($this->ownerLogin);
        $escapedHeader = htmlspecialchars($this->header);
        $escapedContent = nl2br(htmlspecialchars($this->content));
        $escapedImagePath = htmlspecialchars($this->imagePath);
        $postId = (int)$this->id;
        $likes = (int)$this->likesCount;
        $dislikes = (int)$this->dislikesCount;
    
        return '
        <div class="post scroll-section" id="post-' . $postId . '">
            <div class="post__header">
                <img src="/AircraftSandbox/img/users/' . $escapedLogin . '.png" alt="Avatar" class="post__avatar">
                <div>
                    <h3 class="post__user">' . $escapedLogin . '</h3>
                    <p class="post__title">' . $escapedHeader . '</p>
                </div>
                <div class="post__menu">
                    <svg class="post__menu-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="5" cy="12" r="1" />
                        <circle cx="12" cy="12" r="1" />
                        <circle cx="19" cy="12" r="1" />
                    </svg>
                    <ul class="post__dropdown">
                        <li class="post__dropdown-item">Поскаржитись</li>
                    </ul>
                </div>                    
            </div>
    
            <div class="post__image">
                <img src="' . $escapedImagePath . '" alt="Post image">
            </div>
    
            <div class="post__text">
                ' . $escapedContent . '
            </div>
    
            <div class="post__actions">
                <button class="post__like" data-action="like" data-id="' . $postId . '">
                    👍 <span class="post__count like-count">' . $likes . '</span>
                </button>
                <button class="post__dislike" data-action="dislike" data-id="' . $postId . '">
                    👎 <span class="post__count dislike-count">' . $dislikes . '</span>
                </button>
            </div>
        </div>';
    }
       
}
?>
