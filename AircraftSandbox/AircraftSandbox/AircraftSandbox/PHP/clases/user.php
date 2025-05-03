<?php
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
        $stmt = $db->prepare("SELECT * FROM User WHERE Login = :login");
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $this->Login = $user['Login'];
            $this->Name = $user['Name'];
            $this->Password = $user['Password'];
            $this->Phone = $user['Phone'];
            $this->IsSuperUser = $user['IsSuperUser'];
            return true;
        } else {
            return false;
        }
    }

    public function saveToDB($db) {
        $stmt = $db->prepare("INSERT INTO User (Login, Name, Password, Phone, IsSuperUser) 
                              VALUES (:login, :name, :password, :phone, :isSuperUser)");
        $stmt->bindParam(':login', $this->Login);
        $stmt->bindParam(':name', $this->Name);
        $stmt->bindParam(':password', $this->Password);
        $stmt->bindParam(':phone', $this->Phone);
        $stmt->bindParam(':isSuperUser', $this->IsSuperUser);

        return $stmt->execute();
    }

    public function updateToDB($db) {
        $stmt = $db->prepare("UPDATE User SET Name = :name, Password = :password, Phone = :phone, IsSuperUser = :isSuperUser 
                              WHERE Login = :login");
        $stmt->bindParam(':login', $this->Login);
        $stmt->bindParam(':name', $this->Name);
        $stmt->bindParam(':password', $this->Password);
        $stmt->bindParam(':phone', $this->Phone);
        $stmt->bindParam(':isSuperUser', $this->IsSuperUser);

        return $stmt->execute();
    }

    public function deleteFromDB($db) {
        $stmt = $db->prepare("DELETE FROM User WHERE Login = :login");
        $stmt->bindParam(':login', $this->Login);
        return $stmt->execute();
    }

    public function setPassword($plainPassword) {
        $this->Password = password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function verifyPassword($plainPassword) {
        return password_verify($plainPassword, $this->Password);
    }
}
?>
