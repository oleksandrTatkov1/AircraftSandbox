<?php
// getNewsList.php
// Возвращает JSON-массив всех записей News с полями id и Description (обрезан до 10 символов)

header('Content-Type: application/json; charset=utf-8');

// Путь к вашей БД
$dbFile = __DIR__ . '/../../sqlite/users.db';

try {
    $db = new PDO("sqlite:" . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->query("SELECT id, Description FROM News ORDER BY id");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Обрезаем описание до 10 символов
    foreach ($rows as &$row) {
        $row['Description'] = mb_substr($row['Description'], 0, 10, 'UTF-8');
    }
    unset($row);

    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
