<?php
// scripts/addComment.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../../PHP/clases/userPosts.php';

use PHP\Clases\UserInfo;

if (empty($_SESSION['user_login'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Ви не авторизовані']);
    exit;
}

$userLogin = $_SESSION['user_login'];
$postId    = trim($_POST['postId'] ?? '');
$comment   = trim($_POST['comment'] ?? '');

if ($postId === '' || $comment === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Некоректні дані']);
    exit;
}

try {
    // Попытка загрузить существующую связь UserInfo для данного пользователя и поста
    $ui = UserInfo::getForUserAndPost(null, $userLogin, $postId);

    if (!$ui) {
        // Если записи нет — создаём новую связь
        $ui = new UserInfo();
        $ui->UserLogin = $userLogin;
        $ui->PostId    = $postId;
        $ui->Reaction  = 0;
        // Инициализируем comments как пустой массив
        $ui->comments  = [];
    } else {
        // Если запись уже имеется, убеждаемся, что comments является массивом
        if (!is_array($ui->comments)) {
            $ui->comments = [];
        }
    }

    // Создаём новый комментарий с случайным ID, текстом и текущей датой
    $newComment = [
        'id'   => uniqid('', true),      // генерирует уникальный идентификатор
        'text' => $comment,
        'date' => date(DATE_ISO8601)
    ];

    // Добавляем новый комментарий в массив comments
    $ui->comments[] = $newComment;

    // Сохраняем обновлённую связь в базу данных через FirebasePublisher
    $ui->saveToDB();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
