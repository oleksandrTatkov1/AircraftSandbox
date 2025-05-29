<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

    $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        throw new Exception('Неприпустимий формат зображення. Дозволено: ' . implode(', ', $allowedExt));
    }

    $newName = uniqid('news_', true) . '.' . $ext;

    // 🔥 Абсолютний ШЛЯХ до потрібної папки (жорстко прописаний)
    $absoluteDir = $_SERVER['DOCUMENT_ROOT'] . '/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/news/';
    $relativePath = '/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/news/' . $newName;

    // Повний шлях до файла
    $fullPath = $absoluteDir . $newName;

    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0777, true)) {
        throw new Exception("Не вдалося створити директорію для зображень: {$absoluteDir}");
    }

    if (!move_uploaded_file($image['tmp_name'], $fullPath)) {
        throw new Exception('Не вдалося зберегти файл на сервері.');
    }

    // ✅ Firebase повинен зберігати шлях відносно сайту (кореня)
    $firebasePath = '/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/news/' . $newName;

    $uniqueId = uniqid();
    $news = new News($firebasePath, $desc, $sliderId, $uniqueId);
    $news->saveToDB();

    echo '✅ Новина успішно додана!';

} catch (Exception $e) {
    http_response_code(400);
    echo '❌ Помилка: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
}