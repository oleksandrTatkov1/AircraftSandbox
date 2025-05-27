<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$db = new PDO(__DIR__ . '/../../sqlite/users.db');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM News WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo "✅ Запис $id видалено.";
} else {
    echo "❌ Невірний запит.";
}
