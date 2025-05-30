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
    public $comments;   // Объединяем комментарий в один ассоциативный массив

    public $firebase;

    public function __construct($authToken = null) {
        parent::__construct($authToken);
        $this->id        = null;
        $this->UserLogin = '';
        $this->PostId    = '';
        $this->Reaction  = 0;
        $this->firebase = new FirebasePublisher($authToken);
    }

    private function sanitizeKey(string $key): string {
        return str_replace(
            ['@', '.', ' '],
            ['_at_', '_dot_', '_'],
            $key
        );
    }

    public function saveToDB(): bool {
        if (empty($this->UserLogin) || empty($this->PostId)) {
            throw new Exception("UserLogin, PostId and id must not be empty.");
        }
        $u = $this->sanitizeKey($this->UserLogin);
        $p = $this->sanitizeKey($this->PostId);
        $safeKey = "{$u}_{$p}";
        $path = "userInfo/{$safeKey}";

        // Собираем данные для отправки, включая comments в виде единого массива
        $payload = [
            'UserLogin' => $this->UserLogin,
            'PostId'    => $this->PostId,
            'Reaction'  => $this->Reaction,
            'comments'  => $this->comments
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

        // Заполняем свойства объекта
        $inst->id        = $postId;
        $inst->UserLogin = $data['UserLogin'] ?? $userLogin;
        $inst->PostId    = $data['PostId'] ?? $postId;
        $inst->Reaction  = (int)($data['Reaction'] ?? 0);

        // Если используются новые ключи, то читаем их из массива comments
        if (isset($data['comments']) && is_array($data['comments'])) {
            $inst->comments = [
                'id'   => $data['comments']['id']   ?? null,
                'text' => $data['comments']['text'] ?? '',
                'date' => $data['comments']['date'] ?? null
            ];
        } else {
            // Если данные ещё в старом формате (по отдельности)
            $inst->comments = [
                'id'   => $data['CommentId']   ?? null,
                'text' => $data['CommentText'] ?? '',
                'date' => $data['CommentDate'] ?? null
            ];
        }

        return $inst;
    }
}
?>
