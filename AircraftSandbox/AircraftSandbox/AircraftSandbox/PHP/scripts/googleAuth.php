<?php
require_once  '../../vendor/autoload.php';
require_once  '../../PHP/Clases/User.php';

use Google\Client;
use PHP\Clases\User;

// 1. Отримання та перевірка ID токена
$data = json_decode(file_get_contents("php://input"), true);
$idToken = $data['credential'] ?? null;

if (!$idToken) {
    echo json_encode(['success' => false, 'message' => 'Missing token']);
    exit;
}

$client = new Client(['client_id' => '326026153218-gghrefo6968gvh462pogkun45dfn9ti1.apps.googleusercontent.com']);

try {
    $payload = $client->verifyIdToken($idToken);
    if (!$payload) {
        throw new Exception("Невірний токен");
    }

    $email = $payload['email'];
    $name = $payload['name'] ?? '';
    $picture = $payload['picture'] ?? '';
    
    $user = User::searchById($email);
    if (!$user) {
        $user = new User();
        $user->Login = $email;
        $user->Name = $name;
        $user->Phone = '';
        $user->IsSuperUser = 0;
        $user->Bio = 'Google user';

        // 2. Завантажити аватар
        if ($picture) {
            $imageData = file_get_contents($picture);
            $filename = basename(parse_url($picture, PHP_URL_PATH));
            $safeName = str_replace(['@', '.'], ['_at_', '_dot_'], $email);
            $targetPath = "AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users/{$safeName}_{$filename}";
            file_put_contents($targetPath, $imageData);

            $user->ImagePath = "AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users/{$safeName}_{$filename}";
        }

        // Генерація випадкового пароля для сумісності з існуючою системою
        $user->setPassword(bin2hex(random_bytes(8)));
        $user->saveToDB();
        
    }

    // Тут ви можете зберегти сесію
    session_start();
    $_SESSION['user_login'] = $user->Login;

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
