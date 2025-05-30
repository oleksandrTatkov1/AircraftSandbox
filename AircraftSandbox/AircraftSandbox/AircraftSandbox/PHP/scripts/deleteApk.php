<?php
require_once __DIR__ . '/../clases/ApkInfo.php';
use PHP\Clases\ApkInfo;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Метод не підтримується.");
    }

    if (empty($_POST['id'])) {
        throw new Exception("Некоректний ID.");
    }

    $apk = new ApkInfo();
    $apk->id = $_POST['id'];
    $apk->deleteFromDB(); 

    echo "✅ Картку з ID {$_POST['id']} успішно видалено.";
} catch (Throwable $e) {
    echo "❌ Помилка: " . $e->getMessage();
}