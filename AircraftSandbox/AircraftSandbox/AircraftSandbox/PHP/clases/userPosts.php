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

    public static function getAllPostCommentsById(string $postId): array {
        $inst = new self();  // создаём экземпляр без токена
        $all = $inst->firebase->getAll('userInfo'); // получаем все записи из userInfo

        if (!is_array($all) || empty($all)) {
            return [];
        }

        $comments = [];
        foreach ($all as $safeKey => $data) {
            // Проверяем, совпадает ли PostId
            if (($data['PostId'] ?? null) !== $postId) {
                continue;
            }

            $userLogin = $data['UserLogin'] ?? null;

            // Если поле comments существует и это массив
            if (isset($data['comments']) && is_array($data['comments'])) {
                foreach ($data['comments'] as $c) {
                    if (!empty($c['date'])) {
                        $comments[] = [
                            'UserLogin' => $userLogin,
                            'PostId'    => $postId,
                            'id'        => $c['id']   ?? null,
                            'text'      => $c['text'] ?? '',
                            'date'      => $c['date'] ?? null,
                        ];
                    }
                }
            }
        }

        // Сортируем комментарии по дате (старые в начале)
        usort($comments, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return $comments;
    }


    /**
     * Получить связь конкретного пользователя с постом (с реакцией и комментарием).
     *
     * @param string $userLogin — логин пользователя
     * @param string $postId    — идентификатор поста
     * @return self|null
     */
    public static function getForUserAndPost(string $userLogin, string $postId): ?self {
        $inst = new self();  // без токена
        $u = $inst->sanitizeKey($userLogin);
        $p = $inst->sanitizeKey($postId);
        $key = "{$u}_{$p}";
        $path = "userInfo/{$key}";

        $data = $inst->firebase->getAll($path);
        if (!is_array($data) || empty($data)) {
            return null;
        }

        $inst->id        = $postId;
        $inst->UserLogin = $data['UserLogin'] ?? $userLogin;
        $inst->PostId    = $data['PostId']  ?? $postId;
        $inst->Reaction  = (int)($data['Reaction'] ?? 0);

        if (isset($data['comments']) && is_array($data['comments'])) {
            $inst->comments = $data['comments']; // просто беремо масив як є
        } else {
            $inst->comments = []; // ініціалізуємо як порожній масив
        }


        return $inst;
    }

    /**
     * Сформировать HTML-список комментариев для заданного поста.
     *
     * @param string $postId — идентификатор поста
     * @return string       — HTML-код <ul>…</ul> или заглушка
     */
    public static function renderAllCommentsByPostId(string $postId): string {
        $comments = self::getAllPostCommentsById($postId);
        if (empty($comments)) {
            return '<p>Комментариев пока нет.</p>';
        }

        $html = '<div class="comments-container">';
        foreach ($comments as $c) {
            $author = htmlspecialchars($c['UserLogin'], ENT_QUOTES, 'UTF-8');
            $date   = htmlspecialchars($c['date'], ENT_QUOTES, 'UTF-8');
            $text   = nl2br(htmlspecialchars($c['text'], ENT_QUOTES, 'UTF-8'));

            // Формування шляху до аватара
            $authorImage = "/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users/{$author}.jpg";

            $html .= <<<HTML
    <div class="comment-card">
        <div class="comment-header">
            <img 
                src="{$authorImage}" 
                alt="Avatar of {$author}" 
                class="comment-avatar"
                onerror="this.onerror=null;this.src='/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users/default-avatar.png'">
            <div class="comment-info">
                <span class="comment-author">{$author}</span>
                <span class="comment-date">{$date}</span>
            </div>
        </div>
        <div class="comment-body">
            {$text}
        </div>
    </div>
    HTML;
        }
        $html .= '</div>';

        return $html;
    }

}
?>
