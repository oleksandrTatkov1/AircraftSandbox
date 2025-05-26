<?php
namespace PHP\Clases;

class BaseModel {
    protected PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    protected function transaction(callable $fn) {
        $this->db->beginTransaction();
        try {
            $result = $fn();
            $this->db->commit();
            return $result;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function init(): void
    {
        try {
            $this->db->beginTransaction();

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS User (
                  Login TEXT PRIMARY KEY,
                  Name TEXT,
                  Password TEXT,
                  Phone TEXT,
                  IsSuperUser INTEGER
                );
            ");
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS Post (
                  Id INTEGER PRIMARY KEY AUTOINCREMENT,
                  Header TEXT,
                  ImagePath TEXT,
                  Content TEXT,
                  LikesCount INTEGER,
                  DislikesCount INTEGER,
                  ownerLogin TEXT,
                  FOREIGN KEY(ownerLogin) REFERENCES User(Login)
                );
            ");

            $this->db->exec("INSERT OR IGNORE INTO User (Login,Name,Password,Phone,IsSuperUser)
                             VALUES ('admin','Admin','', '0', 1);");
            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            exit('Не вдалося ініціалізувати БД: ' . htmlspecialchars($e->getMessage()));
        }
    }
}
?>