<?php
require_once '../../PHP/utils/firebasePublisher.php';
use PHP\Utils\FirebasePublisher
$token = $_POST['token'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';

$firebase = new FirebasePublisher();
$data = $firebase->getAll("reset_tokens/$token");

if (!$data || $data['expires'] < time()) {
    exit("❌ Недійсний або прострочений токен.");
}

$email = $data['email'];
$safeEmail = str_replace(['@', '.'], ['_at_', '_dot_'], $email);

$firebase->publish("users/$safeEmail/Password", password_hash($newPassword, PASSWORD_DEFAULT));

$firebase->publish("reset_tokens/$token", null);

echo "✅ Пароль оновлено.";
