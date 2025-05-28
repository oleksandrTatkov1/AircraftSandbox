<?php
// php/api/sessionProfile.php
session_start();

// Фейковий користувач (імітація після авторизації)
$_SESSION['user'] = [
    'Login' => 'glory@aviation.com',
    'Name' => 'FimoznikPetya',
    'Password' = 'huesos228',
    'Phone' = '+380935262394',
    'IsSuperUser' = 0,
    'ImagePath' => '"/xampp/img/fimozphoto.jpg"',
    'Bio' => 'Ебашу пятилеток'
];

// Видача у форматі JSON
header('Content-Type: application/json');
echo json_encode($_SESSION['user']);
