<?php
require_once '../../PHP/utils/firebasePublisher.php';

use PHP\Utils\FirebasePublisher;

header('Content-Type: application/json');

$token = $_POST['token'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';

// Перевірка вхідних даних
if (empty($token) || empty($newPassword)) {
    exit(json_encode(["error" => "❌ Токен або новий пароль відсутній."]));
}

$firebase = new FirebasePublisher();
$data = $firebase->getAll("reset_tokens/$token");

// Перевірка наявності токена та дії
if (!$data || ($data['expires'] ?? 0) < time()) {
    exit(json_encode(["error" => "❌ Недійсний або прострочений токен."]));
}

$email = $data['email'];
$safeEmail = str_replace(['@', '.'], ['_at_', '_dot_'], $email);

$firebase->publish("users/$safeEmail/Password", password_hash($newPassword, PASSWORD_DEFAULT));

// Видалення токена після використання
$firebase->publish("reset_tokens/$token", null);

echo json_encode(["success" => "✅ Пароль успішно оновлено."]);
