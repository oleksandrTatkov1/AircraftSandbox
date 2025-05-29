<?php
require_once __DIR__ . '/../clases/ApkInfo.php';
require_once __DIR__ . '/../utils/firebasePublisher.php';

use PHP\Clases\ApkInfo;
use PHP\Utils\FirebasePublisher;

try {
    $firebase = new FirebasePublisher();
    $allData = $firebase->getAll("apkInfo");

    if (!$allData) exit;

    foreach ($allData as $id => $apkData) {
        echo ApkInfo::renderCardById($firebase, $id);
    }
} catch (Throwable $e) {
    exit;
}