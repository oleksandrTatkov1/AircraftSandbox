<?php
session_start();

require_once __DIR__ . '/../clases/User.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHP\Clases\User;
use PHP\Utils\FirebasePublisher;

// Перевірка форми
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Недопустимий метод запиту.");
}

if (!isset($_SESSION['reset_email'])) {
    exit("Сесія закінчена або email відсутній.");
}

$email = $_SESSION['reset_email'];
$code = $_POST['code'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Перевірка
if (empty($code) || empty($newPassword) || empty($confirmPassword)) {
    exit("Заповніть усі поля.");
}

if ($newPassword !== $confirmPassword) {
    exit("Паролі не співпадають.");
}

// Отримання коду з Firebase
$firebase = new FirebasePublisher();
$safeKey = $firebase->sanitizeKey($email);
$stored = $firebase->getAll("PasswordResets/$safeKey");

if (!$stored || !isset($stored['code']) || $stored['code'] !== $code) {
    exit("Невірний або прострочений код.");
}

// Оновлення пароля
$user = new User();
if (!$user->loadFromDB($email)) {
    exit("Користувач не знайдений.");
}

try {
    $user->setPassword($newPassword);
    $user->updateToDB();

    // Видалення коду з Firebase
    $firebase->publish("PasswordResets/$safeKey", null);

    // Очистка сесії
    unset($_SESSION['reset_email']);

    header("Location: /AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/registerAlpha/register.html");
    exit;

} catch (Exception $e) {
    exit("Помилка оновлення пароля: " . $e->getMessage());
}