<?php
namespace PHP\Clases;

require_once __DIR__ . '/User.php';
require_once __DIR__ . '/../utils/firebasePublisher.php';
use PHP\Utils\FirebasePublisher;
use Exception;

class UserInfo extends User {
    public $id;
    public $UserLogin;
    public $PostId;
    public $Reaction;   // -1 dislike, 0 none, 1 like
    public $CommentId;
    public $CommentText;
    public $CommentDate; // ISO8601 string

    private $firebase;

    public function __construct($authToken = null) {
        parent::__construct($authToken);
        $this->id = null;
        $this->UserLogin = '';
        $this->PostId = '';
        $this->Reaction = 0;
        $this->CommentId = null;
        $this->CommentText = '';
        $this->CommentDate = null;
        $this->firebase = new FirebasePublisher($authToken);
    }

    /**
     * Normalize key for Firebase
     */
    private function sanitizeKey($key) {
        return str_replace(['@', '.', ' '], ['_at_', '_dot_', '_'], (string)$key);
    }

    /**
     * Save relation record to Firebase
     */
    public function saveToDB() {
        if (empty($this->UserLogin) || empty($this->PostId)) {
            throw new Exception("UserLogin and PostId must not be empty.");
        }
        // generate or use existing id
        if (empty($this->id)) {
            $this->id = time();
        }
        $safeKey = $this->sanitizeKey($this->UserLogin) . "_" . $this->sanitizeKey($this->PostId) . "_" . $this->id;
        $path = "userInfo/{$safeKey}";

        $payload = [
            'UserLogin'   => $this->UserLogin,
            'PostId'      => $this->PostId,
            'Reaction'    => $this->Reaction,
            'CommentId'   => $this->CommentId,
            'CommentText' => $this->CommentText,
            'CommentDate' => $this->CommentDate,
        ];
        $this->firebase->publish($path, $payload);
        return true;
    }

    /**
     * Load relation record by composite key
     */
    public function loadFromDB($login) {
        // Викликаємо метод з базового класу
        parent::loadFromDB($login);

        // А тепер можна завантажити пов’язану userInfo інфу за логіном
        $this->loadUserInfoPart($login);
        return true;
    }

    // Додатковий метод для завантаження userInfo-запису
    public function loadUserInfoPart($userLogin, $postId = null, $id = null) {
        if (!$postId || !$id) return false;

        $safeKey = $this->sanitizeKey($userLogin) . "_" . $this->sanitizeKey($postId) . "_" . $id;
        $path = "userInfo/{$safeKey}";

        $data = $this->firebase->getAll($path);
        if (!is_array($data) || empty($data)) {
            return false;
        }

        $this->id = $id;
        $this->UserLogin = $data['UserLogin'] ?? '';
        $this->PostId = $data['PostId'] ?? '';
        $this->Reaction = (int)($data['Reaction'] ?? 0);
        $this->CommentId = $data['CommentId'] ?? null;
        $this->CommentText = $data['CommentText'] ?? '';
        $this->CommentDate = $data['CommentDate'] ?? null;
        return true;
    }

    /**
     * Delete record
     */
    public function deleteFromDB() {
        if (empty($this->UserLogin) || empty($this->PostId) || empty($this->id)) {
            throw new Exception("UserLogin, PostId and id must not be empty for deletion.");
        }
        $safeKey = $this->sanitizeKey($this->UserLogin) . "_" . $this->sanitizeKey($this->PostId) . "_" . $this->id;
        $path = "userInfo/{$safeKey}";
        $this->firebase->publish($path, null);
        return true;
    }

    /**
     * Load all info for a specific post
     * @return UserInfo[]
     */
    public static function loadByPost($authToken, $postId) {
        $firebase = new FirebasePublisher($authToken);
        $all = $firebase->getAll('userInfo');
        $result = [];
        if (!is_array($all)) return [];
        foreach ($all as $key => $data) {
            if (($data['PostId'] ?? '') === $postId) {
                $info = new self();
                $info->id = explode('_', $key)[2] ?? null;
                $info->UserLogin = $data['UserLogin'] ?? '';
                $info->PostId = $data['PostId'];
                $info->Reaction = (int)($data['Reaction'] ?? 0);
                $info->CommentId = $data['CommentId'] ?? null;
                $info->CommentText = $data['CommentText'] ?? '';
                $info->CommentDate = $data['CommentDate'] ?? null;
                $result[] = $info;
            }
        }
        return $result;
    }
}
?>
