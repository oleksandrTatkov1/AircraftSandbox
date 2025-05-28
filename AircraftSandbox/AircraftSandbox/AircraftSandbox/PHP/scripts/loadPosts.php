<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ERROR | E_PARSE);

require_once __DIR__ . '/../clases/Post.php';
session_start();

try {
    $dbFile = __DIR__ . '/../../sqlite/users.db';
    if (!file_exists($dbFile) || !is_readable($dbFile)) {
        exit;
    }
    $db = new \PDO("sqlite:$dbFile");
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    $stmt = $db->query('SELECT * FROM Post ORDER BY id DESC');
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    if (!$rows) exit;

    foreach ($rows as $row) {
        $id            = (int)$row['Id'];
        $header        = htmlspecialchars($row['Header']);
        $imagePath     = htmlspecialchars($row['ImagePath']);
        $content       = nl2br(htmlspecialchars($row['Content']));
        $likesCount    = (int)$row['LikesCount'];
        $dislikesCount = (int)$row['DislikesCount'];
        $ownerLogin    = htmlspecialchars($row['ownerLogin']);

        echo <<<HTML
    <div class="post scroll-section from-left" data-id="$id">
        <div class="post__header">
            <img class="post__avatar" src="img/avatars/default.png" alt="User avatar">
            <div>
                <p class="post__user">$ownerLogin</p>
                <p class="post__title">$header</p>
            </div>
        </div>
        <div class="post__image"><img src="$imagePath" alt="Post image"></div>
        <p class="post__text">$content</p>
        <div class="post__footer">
            <button class="post__like" data-id="$id" data-action="like">👍 <span class="like-count">$likesCount</span></button>
            <button class="post__dislike" data-id="$id" data-action="dislike">👎 <span class="dislike-count">$dislikesCount</span></button>
        </div>
    </div>
HTML;
    }

} catch (\Exception $e) {
    exit;
}