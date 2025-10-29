<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

class personal{
    private $conn;

    public function __construct(){
        $db = new Database();
        $this->conn = $db->connect();
    }
    public function obtenerPersonal($id)
    {
        $sql = "SELECT id, nombres,cargo FROM empleados ORDER BY id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}