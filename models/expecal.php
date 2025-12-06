<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

class expcal
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Guarda un expediente en la tabla expcal
     * Espera un array con:
     *  - idtipo
     *  - idgrecepcion
     *  - codigo
     */
    public function guardar($data)
    {
        try {
            $sql = "INSERT INTO expcal (idtipo, idgrecepcion, codigo, fcreacion)
                    VALUES (:idtipo, :idgrecepcion, :codigo, NOW())";

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':idtipo', $data['idtipo'], PDO::PARAM_INT);
            $stmt->bindParam(':idgrecepcion', $data['idgrecepcion'], PDO::PARAM_INT);
            $stmt->bindParam(':codigo', $data['codigo'], PDO::PARAM_STR);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error guardando expediente: " . $e->getMessage());
            return false;
        }
    }
}
