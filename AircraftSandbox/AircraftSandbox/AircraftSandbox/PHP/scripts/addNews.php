<?php
// addNews.php — для Firebase
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ERROR | E_PARSE);

require_once __DIR__ . '/../clases/News.php';
use PHP\Clases\News;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Неправильний метод запиту');
    }

    if (empty($_FILES['newsImage']) || !isset($_POST['newsDesc']) || !isset($_POST['newsSliderId'])) {
        throw new Exception('Заповніть усі поля: зображення, опис, індекс слайдера.');
    }

    $desc     = trim($_POST['newsDesc']);
    $sliderId = intval($_POST['newsSliderId']);

    if ($sliderId < 1) {
        throw new Exception('Невірний індекс слайдера.');
    }

    $image = $_FILES['newsImage'];
    if ($image['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Не вдалося завантажити зображення. Код помилки: ' . $image['error']);
    }

    $allowedExt = ['jpg','jpeg','png','gif','webp'];
    $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        throw new Exception('Неприпустимий формат зображення. Дозволено: ' . implode(', ', $allowedExt));
    }

    // 💡 Використовуємо оригінальне ім’я файлу
    $cleanName = basename($image['name']);
    $relativePath = '/img/news/' . $cleanName;
    $fullPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $relativePath;

    $dir = dirname($fullPath);
    if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
        throw new Exception('Не вдалося створити директорію для зображень.');
    }

    // Якщо вже існує файл з таким ім’ям — перезапишеться
    if (!move_uploaded_file($image['tmp_name'], $fullPath)) {
        throw new Exception('Не вдалося зберегти файл на сервері.');
    }

    $uniqueId = uniqid(); // Унікальний ID новини

    $news = new News($relativePath, $desc, $sliderId, $uniqueId);
    $news->saveToDB();

    echo '✅ Новина успішно додана!';

} catch (Exception $e) {
    http_response_code(400);
    echo '❌ Помилка: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
}