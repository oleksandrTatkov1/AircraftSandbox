<?php
require_once __DIR__ . '/../clases/ApkInfo.php';
use PHP\Clases\ApkInfo;

try {
    $dbFile = __DIR__ . '/../../sqlite/users.db';
    $db = new PDO("sqlite:$dbFile");

    $result = $db->query("SELECT Id, Description FROM ApkInfo ORDER BY Id");
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($rows);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}