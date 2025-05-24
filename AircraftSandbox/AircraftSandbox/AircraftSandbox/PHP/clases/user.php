<?php
namespace PHP\Clases;
use PDO;
use Exception;
class User {
    public $Login;
    public $Name;
    public $Password;
    public $Phone;
    public $IsSuperUser;

    public function __construct() {
        $this->Login = '';
        $this->Name = '';
        $this->Password = '';
        $this->Phone = '';
        $this->IsSuperUser = 0;
    }

    public function loadFromDB($db, $login) {
        if (empty($login)) {
            throw new Exception("Login must not be empty.");
        }

        $stmt = $db->prepare("SELECT * FROM User WHERE Login = :login");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement.");
        }

        $stmt->bindParam(':login', $login);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute statement.");
        }

        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($user) {
            $this->Login = $user['Login'];
            $this->Name = $user['Name'];
            $this->Password = $user['Password'];
            $this->Phone = $user['Phone'];
            $this->IsSuperUser = $user['IsSuperUser'];
            return true;
        } else {
            throw new Exception("User not found.");
        }
    }

    public function saveToDB($db) {
        if (empty($this->Login) || empty($this->Password)) {
            throw new Exception("Login and Password must not be empty.");
        }

        $stmt = $db->prepare("INSERT INTO User (Login, Name, Password, Phone, IsSuperUser) 
                              VALUES (:login, :name, :password, :phone, :isSuperUser)");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement.");
        }

        $stmt->bindParam(':login', $this->Login);
        $stmt->bindParam(':name', $this->Name);
        $stmt->bindParam(':password', $this->Password);
        $stmt->bindParam(':phone', $this->Phone);
        $stmt->bindParam(':isSuperUser', $this->IsSuperUser, \PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new Exception("Failed to save user to database.");
        }

        return true;
    }

    public function updateToDB($db) {
        if (empty($this->Login)) {
            throw new Exception("Login must not be empty.");
        }

        $stmt = $db->prepare("UPDATE User 
                              SET Name = :name, Password = :password, Phone = :phone, IsSuperUser = :isSuperUser 
                              WHERE Login = :login");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement.");
        }

        $stmt->bindParam(':login', $this->Login);
        $stmt->bindParam(':name', $this->Name);
        $stmt->bindParam(':password', $this->Password);
        $stmt->bindParam(':phone', $this->Phone);
        $stmt->bindParam(':isSuperUser', $this->IsSuperUser, \PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update user.");
        }

        return true;
    }

    public function deleteFromDB($db) {
        if (empty($this->Login)) {
            throw new Exception("Login must not be empty.");
        }

        $stmt = $db->prepare("DELETE FROM User WHERE Login = :login");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement.");
        }

        $stmt->bindParam(':login', $this->Login);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete user.");
        }

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
?>
