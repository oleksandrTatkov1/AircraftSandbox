<?php
session_start(); // ⬅️ запуск сесії

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once '../../PHP/clases/User.php';
    $db = new PDO('sqlite:../../sqlite/users.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = new User();
        if ($user->loadFromDB($db, $login)) {
            if ($user->Password === $password) {
                // ✅ Зберігаємо дані користувача в сесію
                $_SESSION['user_login'] = $user->Login;
                $_SESSION['user_name'] = $user->Name;
                $_SESSION['user_phone'] = $user->Phone;
                $_SESSION['is_superuser'] = $user->IsSuperUser;

                echo "SUCCESS";
            } else {
                echo "❌ Невірний пароль.";
            }
        } else {
            echo "❌ Користувача не знайдено.";
        }
    }
} catch (PDOException $e) {
    echo "❌ Помилка бази даних: " . $e->getMessage();
} catch (Exception $e) {
    echo "❌ Загальна помилка: " . $e->getMessage();
}
?>
