<?php
// deleteNews.php
// Удаляет запись News по переданному POST-id и возвращает текстовый ответ

// Отключаем вывод варнингов
ini_set('display_errors', 0);
error_reporting(0);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Неправильний метод запиту');
    }

    if (empty($_POST['id']) || !ctype_digit($_POST['id'])) {
        throw new Exception('Некоректний ID');
    }
    $id = (int)$_POST['id'];

    $dbFile = __DIR__ . '/../../sqlite/users.db';
    $db = new PDO("sqlite:" . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Проверяем, что такая запись есть
    $check = $db->prepare("SELECT COUNT(*) FROM News WHERE id = :id");
    $check->execute([':id' => $id]);
    if ($check->fetchColumn() == 0) {
        throw new Exception("Запис з ID $id не знайдено");
    }

    // Удаляем
    $del = $db->prepare("DELETE FROM News WHERE id = :id");
    $del->execute([':id' => $id]);

    echo "✅ Новина з ID $id успішно видалена!";
} catch (Exception $e) {
    http_response_code(400);
    echo '❌ Помилка: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
}
