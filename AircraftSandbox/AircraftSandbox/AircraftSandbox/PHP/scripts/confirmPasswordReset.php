<?php
session_start();

require_once __DIR__ . '/../clases/User.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../utils/validator.php';

use PHP\utils\Validator;
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
    $_SESSION['reset_error'] = "Заповніть усі поля.";
    header("Location: /AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/passwordReset.html");
    exit;
}

if ($newPassword !== $confirmPassword) {
    $_SESSION['reset_error'] = "Паролі не співпадають.";
    header("Location: /AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/passwordReset.html");
    exit;
}

if (!validator::isPassReliable($newPassword)) {
    $_SESSION['reset_error'] = "Пароль має містити щонайменше 8 символів, велику і малу літери, цифру та спецсимвол.";
    header("Location: /AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/passwordReset.html");
    exit;
}

// Отримання коду з Firebase
$firebase = new FirebasePublisher();
$safeKey = $firebase->sanitizeKey($email);
$stored = $firebase->getAll("PasswordResets/$safeKey");

if (!$stored || !isset($stored['code'])) {
    $_SESSION['reset_error'] = "Код не знайдено.";
    header("Location: /AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/passwordReset.html");
    exit;
}

$maxLifetime = 15 * 60; // 15 хвилин
if (!isset($stored['timestamp']) || (time() - $stored['timestamp']) > $maxLifetime) {
    $_SESSION['reset_error'] = "Термін дії коду минув. Спробуйте ще раз.";
    header("Location: /AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/passwordReset.html");
    exit;
}

if ($stored['code'] !== $code) {
    $_SESSION['reset_error'] = "Невірний код.";
    header("Location: /AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/passwordReset.html");
    exit;
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