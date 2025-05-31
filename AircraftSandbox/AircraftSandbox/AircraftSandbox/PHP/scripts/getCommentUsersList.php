<?php
require_once __DIR__ . '/../utils/firebasePublisher.php';
use PHP\Utils\FirebasePublisher;

header('Content-Type: application/json');

try {
    $firebase = new FirebasePublisher();
    $allUserInfo = $firebase->getAll('userInfo');
    $allUsers = $firebase->getAll('users');

    $users = [];
    if (is_array($allUserInfo)) {
        foreach ($allUserInfo as $key => $record) {
            $login = $record['UserLogin'] ?? '';
            if (!empty($login) && !isset($users[$login])) {
                $sanitizedKey = $firebase->sanitizeKey($login);
                $name = $allUsers[$sanitizedKey]['Name'] ?? $login;
                $users[$login] = [
                    'Login' => $login,
                    'Name' => $name
                ];
            }
        }
    }

    echo json_encode(array_values($users));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
