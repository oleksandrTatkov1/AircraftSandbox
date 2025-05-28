<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Подключение файлов
require_once '../../PHP/clases/User.php';
require_once '../../PHP/utils/validator.php';
use PHP\Utils\Validator;
use PHP\Clases\User;
// Запускаем сессию
session_start();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
        $name     = $_POST['name']         ?? '';
        $login    = $_POST['login']        ?? '';
        $password = $_POST['password']     ?? '';
        $phone    = $_POST['phone']        ?? '';
        $isSuper  = $_POST['is_superuser'] ?? 0;
        $Bio      = $_POST['bio']          ?? '';
        $ImagePath = $_POST['image_path']  ?? '';
        
        if (!Validator::isEmail($login)) {
            throw new Exception('Невірний формат email у полі "login".');
        }
        if (!Validator::isPhone($phone)) {
            throw new Exception('Невірний формат телефону.');
        }
        if (!Validator::isPassReliable($password)) {
            throw new Exception('Пароль надто простий. Він має містити щонайменше 8 символів, великі та малі літери, цифри й спеціальні символи.');
        }

        $user = new User(); 
        $user->Name        = $name;
        $user->Login       = $login;
        $user->setPassword($password);
        $user->Phone       = $phone;
        $user->IsSuperUser = (int)$isSuper;
        $user->Bio = $Bio;
        $user->ImagePath = $ImagePath;
        if ($user->saveToDB()) {
            echo '✅ Реєстрація успішна!';
        } else {
            echo '❌ Помилка при збереженні в Firebase!';
        }
    } else {
        echo '❌ Некоректний запит.';
    }
} catch (Exception $e) {
    echo '❌ Помилка: ' . Validator::escapeHtml($e->getMessage());
}
