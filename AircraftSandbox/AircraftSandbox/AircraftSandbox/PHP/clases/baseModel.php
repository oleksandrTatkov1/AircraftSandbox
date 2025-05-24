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
}
?>