<?php
require_once __DIR__ . '/../clases/ApkInfo.php';
use PHP\Clases\ApkInfo;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Метод не підтримується.");
    }

    if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception("Некоректний ID.");
    }

    $id = (int)$_POST['id'];
    $db = new PDO("sqlite:" . __DIR__ . '/../../sqlite/users.db');

    $apk = new ApkInfo();
    $apk->loadFromDB($db, $id);
    $apk->deleteFromDB($db);

    echo "✅ Картку з ID $id успішно видалено.";
} catch (Throwable $e) {
    echo "❌ Помилка: " . $e->getMessage();
}