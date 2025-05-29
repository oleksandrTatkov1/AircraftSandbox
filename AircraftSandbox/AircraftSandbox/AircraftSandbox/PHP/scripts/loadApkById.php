<?php
require_once __DIR__ . '/../clases/ApkInfo.php';
header('Content-Type: application/json');

use PHP\Clases\ApkInfo;

try {
    $id = $_GET['Id'] ?? '';
    if (empty($id)) {
        throw new Exception("Invalid APK ID.");
    }

    $apk = new ApkInfo();
    $apk->loadFromDB($id); // 🔁 тут не потрібна перевірка на is_numeric

    echo json_encode($apk);
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}