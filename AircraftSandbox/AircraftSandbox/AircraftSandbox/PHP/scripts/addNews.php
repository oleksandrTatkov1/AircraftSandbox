<?php
// addNews.php — без BOM и пустых строк до <?php
// Отключаем любые варнинги/нотисы
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ERROR | E_PARSE);

// Подключаем (если нужно) класс-валидатор для экранирования
// require_once __DIR__ . '/../../PHP/utils/Validator.php';

// Подключаем PDO
try {
    $dbFile = __DIR__ . '/../../sqlite/users.db';
    $db = new PDO("sqlite:" . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    http_response_code(500);
    exit('❌ Помилка: не вдалося підключитися до БД.');
}

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

    // Обрабатываем файл
    $image = $_FILES['newsImage'];
    if ($image['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Не вдалося завантажити зображення. Код помилки: ' . $image['error']);
    }

    // Проверка расширения (опционально)
    $allowedExt = ['jpg','jpeg','png','gif','webp'];
    $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        throw new Exception('Неприпустимий формат зображення. Дозволено: ' . implode(', ', $allowedExt));
    }

    // Генерируем уникальное имя и путь
    $newName      = uniqid('news_', true) . '.' . $ext;
    $relativePath = '/img/news/' . $newName;
    $fullPath     = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $relativePath;

    // Создаём папку, если нет
    $dir = dirname($fullPath);
    if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
        throw new Exception('Не вдалося створити директорію для зображень.');
    }

    // Перемещаем файл
    if (!move_uploaded_file($image['tmp_name'], $fullPath)) {
        throw new Exception('Не вдалося зберегти файл на сервері.');
    }

    // Вставляем в БД
    $stmt = $db->prepare("
        INSERT INTO News (ImagePath, Description, SliderId)
        VALUES (:path, :desc, :sid)
    ");
    $stmt->bindValue(':path', $relativePath, PDO::PARAM_STR);
    $stmt->bindValue(':desc', $desc,       PDO::PARAM_STR);
    $stmt->bindValue(':sid',  $sliderId,   PDO::PARAM_INT);
    $stmt->execute();

    echo '✅ Новина успішно додана!';

} catch (Exception $e) {
    // В случае ошибки выводим сообщение
    http_response_code(400);
    echo '❌ Помилка: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
}
