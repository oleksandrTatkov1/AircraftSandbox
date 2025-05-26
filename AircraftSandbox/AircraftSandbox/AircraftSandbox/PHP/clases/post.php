<?php
namespace PHP\Clases;
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
        if (!is_numeric($id)) {
            throw new Exception("Invalid post ID.");
        }

        $stmt = $db->prepare("SELECT * FROM Post WHERE id = :id");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement for loading post.");
        }

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute statement for loading post.");
        }

        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$post) {
            throw new Exception("Post not found.");
        }

        $this->id = $post['id'];
        $this->header = $post['header'];
        $this->imagePath = $post['imagePath'];
        $this->content = $post['content'];
        $this->likesCount = $post['likesCount'];
        $this->dislikesCount = $post['dislikesCount'];
        $this->ownerLogin = $post['ownerLogin'];

        return true;
    }

    public function saveToDB($db) {
        $stmt = $db->prepare("INSERT INTO Post (header, imagePath, content, likesCount, dislikesCount, ownerLogin) 
                              VALUES (:header, :imagePath, :content, :likesCount, :dislikesCount, :ownerLogin)");
        if (!$stmt) {
            throw new Exception("Failed to prepare insert statement.");
        }

        $stmt->bindParam(':header', $this->header);
        $stmt->bindParam(':imagePath', $this->imagePath);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':likesCount', $this->likesCount);
        $stmt->bindParam(':dislikesCount', $this->dislikesCount);
        $stmt->bindParam(':ownerLogin', $this->ownerLogin);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert post into database.");
        }
        $this->id = (int)$db->lastInsertId();
        return true;
    }

    public function updateToDB($db) {
        if ($this->id <= 0) {
            throw new Exception("Invalid post ID for update.");
        }

        $stmt = $db->prepare("UPDATE Post SET header = :header, imagePath = :imagePath, content = :content, 
                              likesCount = :likesCount, dislikesCount = :dislikesCount, ownerLogin = :ownerLogin 
                              WHERE id = :id");
        if (!$stmt) {
            throw new Exception("Failed to prepare update statement.");
        }

        $stmt->bindParam(':header', $this->header);
        $stmt->bindParam(':imagePath', $this->imagePath);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':likesCount', $this->likesCount);
        $stmt->bindParam(':dislikesCount', $this->dislikesCount);
        $stmt->bindParam(':ownerLogin', $this->ownerLogin);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update post.");
        }

        return true;
    }

    public function deleteFromDB($db) {
        if ($this->id <= 0) {
            throw new Exception("Invalid post ID for deletion.");
        }

        $stmt = $db->prepare("DELETE FROM Post WHERE id = :id");
        if (!$stmt) {
            throw new Exception("Failed to prepare delete statement.");
        }

        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete post.");
        }

        return true;
    }

    public function createPost() {
      $escapedLogin     = htmlspecialchars($this->ownerLogin, ENT_QUOTES, 'UTF-8');
      $escapedHeader    = htmlspecialchars($this->header, ENT_QUOTES, 'UTF-8');
      $escapedContent   = nl2br(htmlspecialchars($this->content, ENT_QUOTES, 'UTF-8'));
      $escapedImagePath = htmlspecialchars($this->imagePath, ENT_QUOTES, 'UTF-8');
      $postId           = (int)$this->id;
      $likes            = (int)$this->likesCount;
      $dislikes         = (int)$this->dislikesCount;

      // Пути к аватаркам относительно страницы, например:
      $userAvatarUrl   = "../img/users/{$escapedLogin}.png";
      $defaultAvatarUrl = "../img/users/default-avatar.png";

      return "
        <div class=\"post scroll-section\" id=\"post-{$postId}\">
          <div class=\"post__header\">
            <img 
              src=\"{$userAvatarUrl}\" 
              alt=\"Avatar of {$escapedLogin}\" 
              class=\"post__avatar\"
              onerror=\"this.onerror=null;this.src='{$defaultAvatarUrl}'\"
            >
            <div>
              <h3 class=\"post__user\">{$escapedLogin}</h3>
              <p class=\"post__title\">{$escapedHeader}</p>
            </div>
            <div class=\"post__menu\">
              <svg class=\"post__menu-icon\" xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\">
                <circle cx=\"5\" cy=\"12\" r=\"1\" />
                <circle cx=\"12\" cy=\"12\" r=\"1\" />
                <circle cx=\"19\" cy=\"12\" r=\"1\" />
              </svg>
              <ul class=\"post__dropdown\">
                <li class=\"post__dropdown-item\">Поскаржитись</li>
              </ul>
            </div>                    
          </div>

          <div class=\"post__image\">
            <img src=\"{$escapedImagePath}\" alt=\"Post image\">
          </div>

          <div class=\"post__text\">
            {$escapedContent}
          </div>

          <div class=\"post__actions\">
            <button class=\"post__like\" data-action=\"like\" data-id=\"{$postId}\">
              👍 <span class=\"post__count like-count\">{$likes}</span>
            </button>
            <button class=\"post__dislike\" data-action=\"dislike\" data-id=\"{$postId}\">
              👎 <span class=\"post__count dislike-count\">{$dislikes}</span>
            </button>
          </div>
        </div>";
    }
  }
?>
