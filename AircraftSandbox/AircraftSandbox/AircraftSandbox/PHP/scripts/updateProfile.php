<?php
session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/../clases/User.php';
require_once __DIR__ . '/../utils/firebasePublisher.php';

use PHP\Clases\User;
use PHP\Utils\FirebasePublisher;

// Перевірка авторизації
if (!isset($_SESSION['user_login'])) {
    echo json_encode(['success' => false, 'message' => 'Користувач не авторизований.']);
    exit;
}

$login = $_SESSION['user_login'];
$firebase = new FirebasePublisher();
$user = new User();
$user->Login = $login;

// Завантаження наявних даних користувача
if (!$user->loadFromDB($login)) {
    echo json_encode(['success' => false, 'message' => 'Не вдалося завантажити користувача з бази.']);
    exit;
}

// Оновлення імені та біо
if (isset($_POST['name'])) {
    $user->Name = trim($_POST['name']);
}
if (isset($_POST['bio'])) {
    $user->Bio = trim($_POST['bio']);
}

// Завантаження фото
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $filename = str_replace(['@', '.'], '', $login) . '.' . $extension;
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
        $relativePath = '/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users/' . $filename;
        $user->ImagePath = $relativePath . '?t=' . time();
    }
}

// Збереження у Firebase
try {
    $user->updateToDB();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}