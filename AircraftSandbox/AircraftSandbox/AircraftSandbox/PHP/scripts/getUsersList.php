<?php
require_once __DIR__ . '/../utils/firebasePublisher.php';
use PHP\Utils\FirebasePublisher;

header('Content-Type: application/json; charset=utf-8');

try {
    $firebase = new FirebasePublisher();
    $users = $firebase->getAll('users');

    if (!$users) {
        echo json_encode([]);
        exit;
    }

    $result = [];
    foreach ($users as $login => $data) {
        $result[] = [
            'Login' => $login,                   // для value
            'Name' => $data['Name'] ?? $login    // для відображення (показуємо ім’я або логін)
        ];
    }

    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Помилка сервера: ' . $e->getMessage()]);
}
