<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_login'])) {
    echo json_encode(['authorized' => false, 'message' => 'Не авторизовано']);
    exit;
}

require_once __DIR__ . '/../clases/User.php';
use PHP\Clases\User;

$user = new User();
$login = $_SESSION['user_login'];

if ($user->loadFromDB($login)) {
    if ((int)$user->IsSuperUser === 1) {
        echo json_encode(['authorized' => true]);
    } else {
        echo json_encode(['authorized' => false, 'message' => 'Недостатньо прав']);
    }
} else {
    echo json_encode(['authorized' => false, 'message' => 'Користувача не знайдено']);
}
