<?php
require_once __DIR__ . '/../clases/ApkInfo.php';
header('Content-Type: application/json');

try {
    $id = isset($_GET['Id']) ? (int)$_GET['Id'] : 0; // <<< ВЕЛИКА LITERA тут
    if ($id <= 0) throw new Exception("Invalid ID");

    $dbFile = __DIR__ . '/../../sqlite/users.db';
    $db = new PDO("sqlite:$dbFile");
    $apk = new \PHP\Clases\ApkInfo();
    $apk->loadFromDB($db, $id);

    echo json_encode($apk);
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}