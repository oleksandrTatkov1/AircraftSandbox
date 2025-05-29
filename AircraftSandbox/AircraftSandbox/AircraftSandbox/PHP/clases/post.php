<?php
namespace PHP\Clases;
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/../utils/firebasePublisher.php';
require_once __DIR__ . '/userPosts.php';
use PHP\Utils\FirebasePublisher;
use PHP\Clases\User;
use PHP\Clases\UserInfo;
use Exception;

class Post {
    public $id;
    public $header;
    public $imagePath;
    public $content;
    public $likesCount;
    public $dislikesCount;

    private $firebase;

    public function __construct($authToken = null) {
        $this->id = null;
        $this->header = '';
        $this->imagePath = '';
        $this->content = '';
        $this->likesCount = 0;
        $this->dislikesCount = 0;

        $this->firebase = new FirebasePublisher($authToken);
    }

    /**
     * Normalize a key for Firebase paths
     */
    private function sanitizeKey($key) {
        return str_replace(['@', '.', ' '], ['_at_', '_dot_', '_'], (string)$key);
    }

    /**
     * Load a post by its ID
     */
    public function loadFromDB($id) {
        if (!is_numeric($id)) {
            throw new Exception("Invalid post ID.");
        }

        $safeId = $this->sanitizeKey($id);
        $path = "posts/{$safeId}";

        $data = $this->firebase->getAll($path);
        if (!is_array($data) || empty($data)) {
            throw new Exception("Post not found.");
        }

        $this->id            = (int)$id;
        $this->header        = $data['header'] ?? '';
        $this->imagePath     = $data['imagePath'] ?? '';
        $this->content       = $data['content'] ?? '';
        $this->likesCount    = (int)($data['likesCount'] ?? 0);
        $this->dislikesCount = (int)($data['dislikesCount'] ?? 0);

        return true;
    }

    

    /**
     * Alias for saveToDB (Firebase overwrites existing data)
     */
    public function updateToDB() {
        return $this->saveToDB();
    }

    /**
     * Delete the post
     */
    public function deleteFromDB() {
        if (empty($this->id)) {
            throw new Exception("Post ID must not be empty for deletion.");
        }

        $safeId = $this->sanitizeKey($this->id);
        $path = "posts/{$safeId}";

        $this->firebase->publish($path, null);
        return true;
    }

    /**

     * Save (or create) the current post under its ID
     * Also update UserInfo to record ownership
     */
    public function saveToDB() {
        if (empty($this->id) || empty($this->ownerLogin)) {
            throw new Exception("Post ID and ownerLogin must not be empty.");
        }

        $safeId = $this->sanitizeKey($this->id);
        $path = "posts/{$safeId}";

        $payload = [
            'header'        => $this->header,
            'imagePath'     => $this->imagePath,
            'content'       => $this->content,
            'likesCount'    => $this->likesCount,
            'dislikesCount' => $this->dislikesCount,
            'ownerLogin'    => $this->ownerLogin,
        ];

        // Save post data
        $this->firebase->publish($path, $payload);

        // Update UserInfo: add this post to user's post list
        $userInfo = new UserInfo();
        $userInfo->Login = $this->ownerLogin;
        // assume UserInfo::addPost handles publishing relation
        $userInfo->addPost($this);

        return true;
    }

    /**
     * Render the post HTML; load author record into authorInfo
     */
    public function createPost() {
        // Load author info for later use
        $userInfo = new UserInfo();
        $userInfo->loadFromDB($this->ownerLogin);
        $this->authorInfo = $userInfo;

        $escapedHeader    = htmlspecialchars($this->header, ENT_QUOTES, 'UTF-8');
        $escapedContent   = nl2br(htmlspecialchars($this->content, ENT_QUOTES, 'UTF-8'));
        $escapedImagePath = htmlspecialchars($this->imagePath, ENT_QUOTES, 'UTF-8');
        $postId           = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $likes            = (int)$this->likesCount;
        $dislikes         = (int)$this->dislikesCount;
        return"
        <div class=\"post scroll-section\" id=\"post-{$postId}\">
          <div class=\"post__header\">
            <img 
              src=\"{$userInfo->UserLogin}\" 
              alt=\"Avatar of {$userInfo->ImagePath}\" 
              class=\"post__avatar\"
              onerror=\"this.onerror=null;this.src='{$userInfo->UserLogin}'\"
            >
            <div>
              <h3 class=\"post__user\">{$userInfo->UserLogin}</h3>
              <p class=\"post__title\">{$escapedHeader}</p>
            </div>
            <div class=\"post__menu\">
              <svg class=\"post__menu-icon\" xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\">
                <circle cx=\"5\" cy=\"12\" r=\"1\" />
                <circle cx=\"12\" cy=\"12\" r=\"1\" />
                <circle cx=\"19\" cy=\"12\" r=\"1\" />
              </svg>
             <ul class=\"post__dropdown\">
                <li class=\"post__dropdown-item\">
                    <a href=\"https://t.me/SkyTechDev_bot\">Поскаржитись</a>
                </li>
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
