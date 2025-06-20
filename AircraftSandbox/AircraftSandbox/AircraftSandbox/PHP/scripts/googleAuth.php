<?php
require_once '../../vendor/autoload.php';
require_once '../../PHP/Clases/User.php';

use Google\Client;
use PHP\Clases\User;

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

        $safeName = str_replace(['@', '.'], ['', ''], $email);
        $filename = $safeName . ".jpg";

        // Путь на сервере (абсолютный физический путь к папке)
        $directory = realpath(__DIR__ . '/../..') . '/img/users';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $targetPath = "$directory/$filename";

        // Путь, который будет использоваться на сайте (относительный URL для клиента)
        $relativePath = '/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users/' . $filename;

        // Загрузка изображения
        $context = stream_context_create([
            "http" => [
                "header" => "User-Agent: PHP"
            ]
        ]);
        $imageData = @file_get_contents($picture, false, $context);
        if ($imageData !== false) {
            $saved = file_put_contents($targetPath, $imageData);
            if ($saved === false) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Не вдалося зберегти зображення у: ' . $targetPath,
                    'step' => 'file_put_contents'
                ]);
                exit;
            }
            $user->ImagePath = $relativePath;
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Не вдалося завантажити зображення з URL: ' . $picture,
                'step' => 'file_get_contents'
            ]);
            exit;
        }



        $user->setPassword(bin2hex(random_bytes(8)));
        $user->saveToDB();
    }

    session_start();
    $_SESSION['user_login'] = $user->Login;

    echo json_encode(['success' => true, 'redirect' => 'index.html']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
