<?php
require_once __DIR__ . '/../Clases/userPosts.php';
use PHP\Clases\UserInfo;
session_start();

// Перевірка авторизації
if (!isset($_SESSION['user_login'])) {
    http_response_code(401); // Unauthorized
    echo "Користувач не авторизований.";
    exit;
}

$userLogin = $_SESSION['user_login'];  // або $_SESSION['user_login'] — дивлячись що ти зараз використовуєш

// Виклик рендера
$html = UserInfo::renderAllCommentsByUserId($userLogin); // заміни YourClass на ім'я класу, де метод

// Вивід
echo $html;
?>
