
<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ERROR | E_PARSE);

session_start();
header('Content-Type: application/json');


echo json_encode([
    'authorized' => isset($_SESSION['user_login']) // или другое поле, которое вы устанавливаете при входе
]);
