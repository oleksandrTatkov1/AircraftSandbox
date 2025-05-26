<?php
namespace PHP\utils;
use PDO;
use Exception;
class Validator
{
    public static function isEmail(string $email): bool
    {
        return (bool) preg_match(
            '/^[\w\.\-]+@[\w\-]+\.[A-Za-z]{2,6}$/',
            $email
        );
    }
    public static function isPhone(string $phone): bool
    {
        return (bool) preg_match(
            '/^(?:\+?380|0)\d{9}$/',
            $phone
        );
    }
    public static function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    
    public static function isPassReliable(string $password): bool {
        return strlen($password) >= 8 &&
            preg_match('/[A-Z]/', $password) &&     // Велика літера
            preg_match('/[a-z]/', $password) &&     // Маленька літера
            preg_match('/[0-9]/', $password) &&     // Цифра
            preg_match('/[\W_]/', $password);       // Спецсимвол (не літера/цифра)
    }

}
