<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../clases/User.php';
use PHP\Clases\User;

// 🔐 Перевірка, чи користувач авторизований
if (!isset($_SESSION['user_login'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Користувач не авторизований']);
    exit;
}

// 📨 Отримання логіну
$login = $_SESSION['user_login'];
$user = new User();

// 📥 Завантаження з Firebase
if (!$user->loadFromDB($login)) {
    http_response_code(404);
    echo json_encode(['error' => 'Користувач не знайдений']);
    exit;
}

// ✅ Вивід даних
echo json_encode([
    'Login' => $user->Login,
    'Name' => $user->Name ?? '',
    'Phone' => $user->Phone ?? '',
    'ImagePath' => $user->ImagePath ?: '/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users/default-avatar.png',
    'Bio' => $user->Bio ?? ''
]);
