<?php
// deleteNews.php для Firebase
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../clases/News.php';
use PHP\Clases\News;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Неправильний метод запиту');
    }

    if (empty($_POST['id'])) {
        throw new Exception('Некоректний ID');
    }
    $id = $_POST['id'];

    // Завантажуємо і видаляємо новину
    $news = new News();
    $news->loadFromDB($id);
    $news->deleteFromDB();

    echo "✅ Новина з ID $id успішно видалена!";
} catch (Exception $e) {
    http_response_code(400);
    echo '❌ Помилка: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
}