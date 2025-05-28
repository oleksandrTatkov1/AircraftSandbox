<?php
// 1) Отключаем варнинги и нотиcы сразу
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE);

// 2) Пространства имён
use PHP\Utils\Validator;
use PHP\Clases\User;

// 3) Подключаем файлы-классы
require_once '../../PHP/utils/Validator.php';
require_once '../../PHP/clases/User.php';
require_once '../../PHP/clases/guard.php';

// 4) Запускаем сессию
session_start();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login    = $_POST['login']    ?? '';
        $password = $_POST['password'] ?? '';

        if (!Validator::isEmail($login)) {
            echo '❌ Некоректний формат email.';
            exit;
        }

        $user = new User();

        // Загружаем данные пользователя из Firebase по email (логину)
        if ($user->loadFromDB($login) && $user->verifyPassword($password)) {
            $_SESSION['user_login'] = $user->Login;

            $logger = new GuardLogger([
                'login' => $user->Login,
                'phone' => $user->Phone ?? '',
                'ip'    => $_SERVER['REMOTE_ADDR']
            ]);
            $logger->writeLogToFile();

            echo 'SUCCESS';
            exit;
        }
        else {
            echo '❌ Невірний логін або пароль.';
            exit;
        }
    }
} catch (\Exception $e) {
    echo '❌ Помилка: ' . Validator::escapeHtml($e->getMessage());
    exit;
}
