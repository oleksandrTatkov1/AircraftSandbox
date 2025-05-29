<?php
require_once __DIR__ . '/../clases/ApkInfo.php';
require_once __DIR__ . '/../utils/firebasePublisher.php';

use PHP\Utils\FirebasePublisher;

try {
    $firebase = new FirebasePublisher();
    $allData = $firebase->getAll("apkInfo");

    $rows = [];
    foreach ($allData as $id => $apk) {
        $rows[] = [
            "Id" => $id,
            "Description" => $apk['description'] ?? ''
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($rows);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}