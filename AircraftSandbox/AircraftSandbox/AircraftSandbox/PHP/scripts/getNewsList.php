<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
    $db = new PDO(__DIR__ . '/../../sqlite/users.db');

    $stmt = $db->query("SELECT id, Description FROM News");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($news);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Помилка сервера: " . $e->getMessage()]);
}
