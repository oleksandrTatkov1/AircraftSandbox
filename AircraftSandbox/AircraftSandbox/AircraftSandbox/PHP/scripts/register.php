<?php
// 1) Отключаем варнинги и нотиcы
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ERROR | E_PARSE);

// 2) Импорты – ДОЛЖНЫ стоять до любого кода (кроме declare/namespace)
use PHP\Utils\Validator;
use PHP\Clases\User;

// 3) Подключаем файлы классов
require_once '../../PHP/utils/Validator.php';
require_once '../../PHP/clases/User.php';

// 4) Запускаем сессию и далее идёт логика
session_start();

try {
    // Подключаем БД
    $db = new PDO('sqlite:../../sqlite/users.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Обработка формы
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
        $name     = $_POST['name']         ?? '';
        $login    = $_POST['login']        ?? '';
        $password = $_POST['password']     ?? '';
        $phone    = $_POST['phone']        ?? '';
        $isSuper  = $_POST['is_superuser'] ?? 0;

        // Валидация
        if (!Validator::isEmail($login)) {
            throw new Exception('Невірний формат email у полі "login".');
        }
        if (!Validator::isPhone($phone)) {
            throw new Exception('Невірний формат телефону.');
        }
        if (!Validator::isPassReliable($password)) {
            throw new Exception('Пароль надто простий. Він має містити щонайменше 8 символів, великі та малі літери, цифри й спеціальні символи.');
        }

        // Создаём пользователя
        $user = new User();
        $user->Name        = $name;
        $user->Login       = $login;
        $user->setPassword($password);
        $user->Phone       = $phone;
        $user->IsSuperUser = (int)$isSuper;

        // Сохраняем
        if ($user->saveToDB($db)) {
            echo '✅ Реєстрація успішна!';
        } else {
            echo '❌ Помилка при збереженні в базу!';
        }
    } else {
        echo '❌ Некоректний запит.';
    }
} catch (Exception $e) {
    echo '❌ Помилка: ' . Validator::escapeHtml($e->getMessage());
}
