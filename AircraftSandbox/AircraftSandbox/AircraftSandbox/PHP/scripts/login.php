<?php
// 1) Отключаем варнинги и нотиcы сразу
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE);

// 2) Объявляем пространства имён до любого кода
use PHP\Utils\Validator;
use PHP\Clases\User;

// 3) Подключаем файлы-классы
require_once '../../PHP/utils/Validator.php';
require_once '../../PHP/clases/User.php';
require_once '../../PHP/clases/guard.php';
// 4) Теперь — запускаем сессию и дальше исполняем код
session_start();

try {
    // Подключаем БД
    $db = new \PDO('sqlite:../../sqlite/users.db');
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login    = $_POST['login']    ?? '';
        $password = $_POST['password'] ?? '';

        if (!Validator::isEmail($login)) {
            echo '❌ Некоректний формат email.';
            exit;
        }

        $user = new User();
        if ($user->loadFromDB($db, $login) && $user->verifyPassword($password)) {
            $_SESSION['user_login'] = $user->Login;

            // ✅ Используем объект User, а не несуществующий $userData
            $logger = new GuardLogger([
                'login' => $user->Login,
                'phone' => $user->Phone ?? '',   // если есть такое поле
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
