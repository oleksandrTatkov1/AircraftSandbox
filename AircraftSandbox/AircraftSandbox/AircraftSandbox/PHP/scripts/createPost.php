<?php
declare(strict_types=1);

// подавляем варнинги/нотиcы
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ERROR | E_PARSE);

// подключаем класс Post
require_once __DIR__ . '/../../PHP/clases/Post.php';

session_start();

try {
    // соединяемся с БД
    $dbFile = __DIR__ . '/../../sqlite/users.db';
    if (!file_exists($dbFile) || !is_readable($dbFile)) {
        exit;
    }
    $db = new \PDO("sqlite:$dbFile");
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    // парсим входящие поля
    $header = $_POST['header']  ?? '';
    $content = $_POST['content'] ?? '';
    $owner = $_SESSION['user_login'] ?? 'guest';

    // загрузка файла (если есть)
    $uploadDir    = realpath(__DIR__ . '/../../img/posts') . DIRECTORY_SEPARATOR;
    $imageBaseURL = '../AircraftSandbox/img/posts/';
    $imagePath    = $imageBaseURL . 'defaultimage.jpg';
    if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fn = basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fn)) {
            $imagePath = $imageBaseURL . $fn;
        }
    }

    // создаём объект и сохраняем
    $post = new \PHP\Clases\Post();
    $post->header        = $header;
    $post->content       = $content;
    $post->likesCount    = 0;
    $post->dislikesCount = 0;
    $post->imagePath     = $imagePath;
    $post->ownerLogin    = $owner;

    $post->saveToDB($db);

    // подхватываем ID и сразу возвращаем HTML
    echo $post->createPost();

} catch (\Exception $e) {
    // ничего не выводим спустя AJAX-запроса
    exit;
}
