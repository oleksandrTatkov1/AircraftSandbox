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
        $filename = $safeName;

        $directory = $_SERVER['DOCUMENT_ROOT'] . "/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users";
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $targetPath = "/img/users/$filename.jpg";
        $relativePath = "/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users/$filename.jpg";
        

        $context = stream_context_create([
            "http" => [
                "header" => "User-Agent: PHP"
            ]
        ]);
        $imageData = @file_get_contents($picture, false, $context);
        if ($imageData !== false) {
            file_put_contents($targetPath, $imageData);
            $user->ImagePath = $relativePath;
        } else {
            echo json_encode(['success' => false, 'message' => 'Не удалось загрузить изображение']);
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
