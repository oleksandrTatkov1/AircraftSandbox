<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ERROR | E_PARSE);

require_once __DIR__ . '/../clases/ApkInfo.php'; // підключення класу
session_start();

try {
    $dbFile = __DIR__ . '/../../sqlite/users.db';
    if (!file_exists($dbFile) || !is_readable($dbFile)) {
        exit;
    }

    $db = new \PDO("sqlite:$dbFile");
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    $stmt = $db->query('SELECT Id FROM ApkInfo ORDER BY Id DESC');
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    if (!$rows) exit;

    foreach ($rows as $row) {
        echo \PHP\Clases\ApkInfo::renderCardById($db, (int)$row['Id']);
    }

} catch (\Exception $e) {
    exit;
}