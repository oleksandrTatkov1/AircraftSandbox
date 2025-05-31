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
            return '<p>Комментарів поки немає.</p>';
        }

        $html = '<div class="comments-container">';
        foreach ($comments as $c) {
            $author = htmlspecialchars($c['UserLogin'], ENT_QUOTES, 'UTF-8');
            $date   = htmlspecialchars($c['date'], ENT_QUOTES, 'UTF-8');
            $text   = nl2br(htmlspecialchars($c['text'], ENT_QUOTES, 'UTF-8'));
            $user = User::searchById($author);
            $userImagePath = htmlspecialchars($user->ImagePath, ENT_QUOTES, 'UTF-8');
    
            // Добавляем расширение только если в ImagePath нет точки (jpg, png)
            if (empty(trim($userImagePath))) {
                $authorImage = "/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users/default-avatar.png";
            } else {
                // Иначе обрезаем всё после символа "?"
                $cleanUserImagePath = explode('?', $userImagePath)[0];
                $authorImage = $cleanUserImagePath;
            }

    
            $html .= <<<HTML
                <div class="comment">
                  <div class="comment__inner">
                    <img class="comment__avatar" src= "{$authorImage}" alt="avatar">
                    <div>
                      <p class="comment__user">$author</p>
                      <p class="comment__text">$text</p>
                    </div>
                  </div>
                </div>

    HTML;
        }
        $html .= '</div>';

        return $html;
    }
    public static function renderAllCommentsByUserId(string $userId): string {
        $comments = self::getAllUserCommentsById($userId); 
        if (empty($comments)) {
            return '<p>Комментарів поки немає.</p>';
        }
    
        $html = '<div class="comments-container">';
        foreach ($comments as $c) {
            $author = htmlspecialchars($c['UserLogin'], ENT_QUOTES, 'UTF-8');
            $date   = htmlspecialchars($c['date'], ENT_QUOTES, 'UTF-8');
            $text   = nl2br(htmlspecialchars($c['text'], ENT_QUOTES, 'UTF-8'));
            $postId = isset($c['PostId']) ? htmlspecialchars($c['PostId'], ENT_QUOTES, 'UTF-8') : ''; // виправлено!
    
            $user = User::searchById($author);
            $userImagePath = htmlspecialchars($user->ImagePath, ENT_QUOTES, 'UTF-8');
            if (empty(trim($userImagePath))) {
                $authorImage = "/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users/default-avatar.png";
            } else {
                $cleanUserImagePath = explode('?', $userImagePath)[0];
                $authorImage = $cleanUserImagePath;
            }
    
            $html .= <<<HTML
            <div class="comment" data-post-id="{$postId}">
                <div class="comment__inner">
                    <img class="comment__avatar" src="{$authorImage}" alt="avatar">
                    <div>
                        <p class="comment__user">{$author}</p>
                        <p class="comment__text">{$text}</p>
                    </div>
                </div>
            </div>
    HTML;
        }
        $html .= '</div>';
    
        return $html;
    }
    
    
    
    public static function getAllUserCommentsById(string $userId): array {
        $inst = new self();  // створюємо екземпляр без токена
        $all = $inst->firebase->getAll('userInfo'); // отримуємо всі записи з userInfo
    
        if (!is_array($all) || empty($all)) {
            return [];
        }
    
        $comments = [];
        foreach ($all as $safeKey => $data) {
            $userLogin = $data['UserLogin'] ?? null;
    
            // Перевіряємо, чи UserLogin збігається з шуканим
            if ($userLogin !== $userId) {
                continue;
            }
    
            // Якщо поле comments існує і це масив
            if (isset($data['comments']) && is_array($data['comments'])) {
                foreach ($data['comments'] as $c) {
                    if (!empty($c['date'])) {
                        $comments[] = [
                            'UserLogin' => $userLogin,
                            'PostId'    => $data['PostId'] ?? null,
                            'id'        => $c['id'] ?? null,
                            'text'      => $c['text'] ?? '',
                            'date'      => $c['date'] ?? null,
                        ];
                    }
                }
            }
        }
    
        // Сортуємо коментарі за датою (старіші на початку)
        usort($comments, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });
    
        return $comments;
    }
    
    public static function renderPostThreadFromPost(Post $post): string {
        // Экранируем поля
        $header       = htmlspecialchars($post->header, ENT_QUOTES, 'UTF-8');
        $imagePath    = htmlspecialchars($post->imagePath, ENT_QUOTES, 'UTF-8');
        $content      = nl2br(htmlspecialchars($post->content, ENT_QUOTES, 'UTF-8'));
        $likesCount   = (int)$post->likesCount;
        $dislikesCount= (int)$post->dislikesCount;
        $postId       = htmlspecialchars($post->id, ENT_QUOTES, 'UTF-8');
        $ownerId      = htmlspecialchars($post->ownerLogin, ENT_QUOTES, 'UTF-8');
        $user = User::searchById($ownerId);

            $userImagePath = htmlspecialchars($user->ImagePath, ENT_QUOTES, 'UTF-8');

            // Если строка пустая (или содержит только пробелы), используем аватар по умолчанию
            if (empty(trim($userImagePath))) {
                $authorImage = "/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users/default-avatar.png";
            } else {
                // Иначе обрезаем всё после символа "?"
                $cleanUserImagePath = explode('?', $userImagePath)[0];
                $authorImage = $cleanUserImagePath;
            }
        $postHtml = <<<HTML
<div class="post-thread__post">
  <div class="user-post">
       <img src="{$authorImage}" alt="Avatar" class="post__avatar">
                <h3 class="post__user">$ownerId</h3>
                <h2 class="post-thread__title" id="post-title">{$header}</h2>
            </div>
   </div>
  <!-- Показываем картинку, только если есть -->
  <img src="{$imagePath}" alt="Post image"
       class="post-thread__image" id="post-image"
       style="display:{$imagePath};">
  <div class="post-thread__actions">
    <button class="post-thread__like" data-action="like" data-id="{$postId}">
      👍 <span class="like-count" id="like-count">{$likesCount}</span>
    </button>
    <button class="post-thread__dislike" data-action="dislike" data-id="{$postId}">
      👎 <span class="dislike-count" id="dislike-count">{$dislikesCount}</span>
    </button>
  </div>
</div>
HTML;

        // Затем — комментарии и форма
        $commentsHtml = <<<HTML
<div class="post-thread__comments">
  <h3 class="post-thread__comments-title">Коментарі</h3>
  <div id="comments-container">
    <div id="comment-form" style="margin-top:20px;">
      <textarea id="comment-text"
                placeholder="Напишіть свій коментар..."
                style="width:100%;padding:10px;border-radius:8px;"></textarea>
      <button id="submit-comment"
              style="margin-top:10px;padding:8px 16px;border-radius:10px; ">
        Надіслати
      </button>
    </div>
    <div id="comments-list"style="margin-bottom:30px;">
      <!-- Першоначально рендеримо на сервері наявні коментарі -->
      {COMMENTS}
    </div>
  </div>
</div>
HTML;

        // Встраиваем список комментариев, полученный из UserInfo
        $allComments = UserInfo::renderAllCommentsByPostId($post->id);
        $commentsHtml = str_replace('{COMMENTS}', $allComments, $commentsHtml);

        // Склеиваем всё в одну секцию
        $full = <<<HTML
<section class="post-thread">z
  {$postHtml}
  {$commentsHtml}
</section>
HTML;

        return $full;
    }
    public function deleteAllCommentsByPostId(string $postId): void {
        $allUserInfo = $this->firebase->getAll('userInfo');
        if (!is_array($allUserInfo) || empty($allUserInfo)) {
            return;
        }
    
        foreach ($allUserInfo as $key => $data) {
            if (isset($data['PostId']) && $data['PostId'] === $postId) {
                $this->firebase->publish("userInfo/{$key}", null);
            }
        }
    }
    
    
}

?>
