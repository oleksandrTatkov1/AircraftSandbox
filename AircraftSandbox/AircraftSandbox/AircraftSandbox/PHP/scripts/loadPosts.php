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
        $p = new \PHP\Clases\Post();
        // здесь именно те ключи, как в таблице:
        $p->id            = (int)$row['Id'];
        $p->header        = $row['Header'];
        $p->imagePath     = $row['ImagePath'];
        $p->content       = $row['Content'];
        $p->likesCount    = (int)$row['LikesCount'];
        $p->dislikesCount = (int)$row['DislikesCount'];
        $p->ownerLogin    = $row['ownerLogin'];
        echo $p->createPost();
    }

} catch (\Exception $e) {
    // молча выходим
    exit;
}
