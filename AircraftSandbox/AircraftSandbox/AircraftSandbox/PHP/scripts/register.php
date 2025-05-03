<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once '../../PHP/clases/User.php';

try {
    $db = new PDO('sqlite:../../sqlite/users.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['send'])) {
        $user = new User();
        $user->Name = $_POST['name'] ?? '';
        $user->Login = $_POST['login'] ?? '';
        $user->Password = $_POST['password'] ?? '';
        $user->Phone = $_POST['phone'] ?? '';
        $user->IsSuperUser = $_POST['is_superuser'] ?? 0;

        if ($user->saveToDB($db)) {
            echo "✅ Реєстрація успішна!";
        } else {
            echo "❌ Помилка при збереженні в базу!";
        }
    } else {
        echo "❌ Некоректний запит.";
    }
} catch (Exception $e) {
    echo "❌ Виняток: " . $e->getMessage();
}
?>
