<?php
session_start();

if (!isset($_SESSION['uid'])) {
    die("Користувач не авторизований.");
}

$name = $_POST['name'] ?? '';
$_SESSION['name'] = $name;

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $filename = uniqid() . '_' . basename($_FILES['photo']['name']);
    $targetPath = $uploadDir . $filename;
    move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath);
    $_SESSION['photo'] = 'uploads/' . $filename;
}

header('Location: profile.php');
exit;
?>
