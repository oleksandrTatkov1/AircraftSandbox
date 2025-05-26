<?php
// loadNews.php — без BOM и пустых строк до <?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../clases/News.php';
use PHP\Clases\News;

$dbFile = __DIR__ . '/../../sqlite/users.db';
$bySlider = [];

try {
    // Подключаемся к базе
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем все записи сразу
    $stmt = $pdo->query("SELECT * FROM News");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        // Инициализируем объект по тому же конструктору, что и save/load
        $news = new News(
            $r['ImagePath'],
            $r['Description'],
            $r['SliderId'],
            $r['id']
        );

        // Приводим sliderId к целому
        $sid = intval($news->sliderId);
        if ($sid < 1) {
            // пропускаем невалидные или пустые
            continue;
        }

        // Рендерим и группируем
        $bySlider[$sid][] = $news->renderNewsItem();
    }

    // Отдаём AJAX-у чистые блоки
    foreach ($bySlider as $sid => $items) {
        echo '<div data-slider-id="' . $sid . '">';
        echo implode('', $items);
        echo '</div>';
    }

} catch (Throwable $e) {
    // При ошибке возвращаем 500 и информируем JS
    http_response_code(500);
    echo '<div data-slider-id="error">'
       . 'Ошибка загрузки новостей: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES)
       . '</div>';
}
