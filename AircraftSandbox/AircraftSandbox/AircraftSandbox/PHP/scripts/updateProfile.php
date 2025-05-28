<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../clases/User.php';
use PHP\Clases\User;

if (!isset($_SESSION['user_login'])) {
    echo json_encode(['success' => false, 'message' => 'Користувач не авторизований']);
    exit;
}

$login = $_SESSION['user_login'];
$user = new User();

if (!$user->loadFromDB($login)) {
    echo json_encode(['success' => false, 'message' => 'Користувача не знайдено']);
    exit;
}

$user->Name = $_POST['name'] ?? $user->Name;
$user->Bio  = $_POST['bio'] ?? $user->Bio;

// Фото (опціонально)
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../../img/users/';
    $fileName = basename($_FILES['photo']['name']);
    $targetPath = $uploadDir . $fileName;

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath);

    // Відносний шлях до фото
    $user->ImagePath = '/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users/' . $fileName;
}

$user->updateToDB();

echo json_encode(['success' => true]);
