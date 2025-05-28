<?php

session_start();

require_once __DIR__ . '/../clases/User.php';
use PHP\Clases\User;

// Фейковий користувач
$_SESSION['user'] = [
    'Login' => 'huybobra@gmail.com',
    'Name' => 'FimoznikPetya',
    'Password' => 'huesos228',
    'Phone' => '+380935262394',
    'IsSuperUser' => 0,
    'ImagePath' => '/xampp/img/fimozphoto.jpg',
    'Bio' => 'Ебашу пятилеток'
];

// Створення об'єкта User з даних сесії
$user = new User();
$user->Login = $_SESSION['user']['Login'];
$user->Name = $_SESSION['user']['Name'];
$user->Password = $_SESSION['user']['Password'];
$user->Phone = $_SESSION['user']['Phone'];
$user->IsSuperUser = $_SESSION['user']['IsSuperUser'];
$user->ImagePath = $_SESSION['user']['ImagePath'];
$user->Bio = $_SESSION['user']['Bio'];

// Вивід JSON
header('Content-Type: application/json');
echo json_encode([
    'Login' => $user->Login,
    'Name' => $user->Name,
    'Phone' => $user->Phone,
    'ImagePath' => $user->ImagePath,
    'Bio' => $user->Bio
]);
