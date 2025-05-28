<?php
namespace PHP\Clases;

require_once __DIR__ . '/../utils/FirebasePublisher.php';
use PHP\Clases\FirebasePublisher;
use Exception;

class User {
    public $Login;
    public $Name;
    public $Password;
    public $Phone;
    public $IsSuperUser;

    private $firebase;

    public function __construct($authToken = null) {
        $this->Login = '';
        $this->Name = '';
        $this->Password = '';
        $this->Phone = '';
        $this->IsSuperUser = 0;
        $this->firebase = new FirebasePublisher($authToken);
    }

   public function loadFromDB($login) {
        // Используем метод sanitizeKey из User, а не из FirebasePublisher
        $safeEmail = $this->sanitizeKey($login);

        $path = "users/$safeEmail";

        $data = $this->firebase->getAll($path);

        if (is_array($data) && !empty($data)) {
            $this->Login       = $login;
            $this->Name        = $data['Name'] ?? null;
            $this->Password    = $data['Password'] ?? null;
            $this->Phone       = $data['Phone'] ?? null;
            $this->IsSuperUser = (int)($data['IsSuperUser'] ?? 0);
            return true;
        }
        return false;
    }


    private function sanitizeKey($key) {
        return str_replace(['@', '.'], ['_at_', '_dot_'], $key);
    }

    public function saveToDB() {
        if (empty($this->Login) || empty($this->Password)) {
            throw new Exception("Login and Password must not be empty.");
        }

        $key = $this->sanitizeKey($this->Login);

        $data = [
            'Name' => $this->Name,
            'Password' => $this->Password,
            'Phone' => $this->Phone,
            'IsSuperUser' => $this->IsSuperUser
        ];

        $this->firebase->publish("users/$key", $data);
        return true;
    }


    public function updateToDB() {
        return $this->saveToDB(); // Для Firebase publish() обновит или создаст запись
    }

    public function deleteFromDB() {
        if (empty($this->Login)) {
            throw new Exception("Login must not be empty.");
        }

        $this->firebase->publish("users/{$this->Login}", null); // Удаление в Firebase через null
        return true;
    }

    public function setPassword($plainPassword) {
        if (empty($plainPassword)) {
            throw new Exception("Password must not be empty.");
        }

        $this->Password = password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function verifyPassword($plainPassword) {
        if (empty($plainPassword)) {
            throw new Exception("Password must not be empty.");
        }

        return password_verify($plainPassword, $this->Password);
    }
}
