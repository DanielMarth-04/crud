<?php
require_once __DIR__ . '/../config/database.php';

class Tipo {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function obtenerTipos() {
        $query = "SELECT id AS id, tipo AS tipos
              FROM tipo
              WHERE tipo IN ('Instrumentos', 'Otros')
              ORDER BY tipo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}