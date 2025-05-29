<?php
// PHP/Clases/UserInfo.php
namespace PHP\Clases;

require_once __DIR__ . '/User.php';
require_once __DIR__ . '/../utils/firebasePublisher.php';
use PHP\Utils\FirebasePublisher;
use Exception;

class UserInfo extends User {
    public $id;
    public $UserLogin;
    public $PostId;
    public $Reaction;   
    public $CommentId;
    public $CommentText;
    public $CommentDate;

    public $firebase;

    public function __construct($authToken = null) {
        parent::__construct($authToken);
        $this->id           = null;
        $this->UserLogin    = '';
        $this->PostId       = '';
        $this->Reaction     = 0;
        $this->CommentId    = null;
        $this->CommentText  = '';
        $this->CommentDate  = null;
        $this->firebase     = new FirebasePublisher($authToken);
    }

    private function sanitizeKey(string $key): string {
        return str_replace(
            ['@', '.', ' '],
            ['_at_', '_dot_', '_'],
            $key
        );
    }

    public function saveToDB(): bool {
        if (empty($this->UserLogin) || empty($this->PostId) ) {
            throw new Exception("UserLogin, PostId and id must not be empty.");
        }
        $u = $this->sanitizeKey($this->UserLogin);
        $p = $this->sanitizeKey($this->PostId);
        $safeKey = "{$u}_{$p}";
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
 * Опционально — получить единственную связь конкретного пользователя с постом
 */
    public static function getForUserAndPost($authToken, string $userLogin, string $postId): ?self {
        $inst = new self($authToken);
        $u = $inst->sanitizeKey($userLogin);
        $p = $inst->sanitizeKey($postId);
        $key = "{$u}_{$p}";
        $path = "userInfo/{$key}";

        $data = $inst->firebase->getAll($path);
        if (!is_array($data) || empty($data)) {
            return null;
        }

        // Заполняем поля, id возьмём из postId
        $inst->id           = $postId;
        $inst->UserLogin    = $data['UserLogin']   ?? $userLogin;
        $inst->PostId       = $data['PostId']      ?? $postId;
        $inst->Reaction     = (int)($data['Reaction'] ?? 0);
        $inst->CommentId    = $data['CommentId']   ?? null;
        $inst->CommentText  = $data['CommentText'] ?? '';
        $inst->CommentDate  = $data['CommentDate'] ?? null;

        return $inst;
    }

}
