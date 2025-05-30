<?php
// loadNews.php — Firebase версія
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../clases/News.php';
use PHP\Clases\News;
use PHP\Utils\FirebasePublisher;

$bySlider = [];

try {
    $firebase = new FirebasePublisher();
    $allNews = $firebase->getAll('news');

    if (!$allNews || !is_array($allNews)) {
        throw new Exception("Новини не знайдено.");
    }

    foreach ($allNews as $id => $r) {
        $news = new News(
            $r['imagePath'] ?? '',
            $r['description'] ?? '',
            $r['sliderId'] ?? null,
            $id
        );

        $sid = intval($news->sliderId);
        if ($sid < 1) continue;

        $bySlider[$sid][] = $news->renderNewsItem();
    }

    foreach ($bySlider as $sid => $items) {
        echo '<div data-slider-id="' . $sid . '">';
        echo implode('', $items);
        echo '</div>';
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo '<div data-slider-id="error">'
       . 'Помилка завантаження новин: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES)
       . '</div>';
}