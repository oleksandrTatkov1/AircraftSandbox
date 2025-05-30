<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_login'])) {
    echo json_encode([
        'loggedIn' => true,
        'login'    => $_SESSION['user_login']
    ]);
} else {
    echo json_encode([
        'loggedIn' => false,
        'login'    => null
    ]);
}