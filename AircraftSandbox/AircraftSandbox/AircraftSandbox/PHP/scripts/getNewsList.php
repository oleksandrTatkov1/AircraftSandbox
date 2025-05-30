<?php
// getNewsList.php — Firebase версія
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../clases/News.php';
use PHP\Clases\News;
use PHP\Utils\FirebasePublisher;

try {
    $firebase = new FirebasePublisher();
    $allNews = $firebase->getAll('news');

    if (!$allNews || !is_array($allNews)) {
        throw new Exception("Новини не знайдено.");
    }

    $result = [];
    foreach ($allNews as $id => $news) {
        $desc = isset($news['description']) ? mb_substr($news['description'], 0, 10, 'UTF-8') : '';
        $result[] = [
            'id' => $id,
            'Description' => $desc
        ];
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}