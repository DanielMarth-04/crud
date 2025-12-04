<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';
class otrabajo
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }
    public function guardar($idproforma, $idcliente, $idempleado, $fecha, $descripcion, $methodo, $codotr, $estado)
    {
        // Generar código
        $nuevoNumero = "00001";
        try {
            $sqlUltimo = "SELECT codotr FROM otrabajo ORDER BY id DESC LIMIT 1";
            $stmtUltimo = $this->conn->query($sqlUltimo);
            if ($stmtUltimo !== false) {
                $ultimo = $stmtUltimo->fetch(PDO::FETCH_ASSOC);
                if ($ultimo && isset($ultimo['codotr']) && preg_match('/OT-2025-(\d+)/', $ultimo['codotr'], $matches)) {
                    $nuevoNumero = str_pad($matches[1] + 1, 5, '0', STR_PAD_LEFT);
                }
            }
        } catch (PDOException $e) {
            error_log("Advertencia al obtener último código: " . $e->getMessage());
        }

        $codotr = "OT-2025-" . $nuevoNumero;

        $sql = "INSERT INTO otrabajo 
                (idproforma, idcliente, idempleado, fecha, descripcion, methodo, codotr, estado)
                VALUES
                (:idproforma, :idcliente, :idempleado, :fecha, :descripcion, :methodo, :codotr, :estado)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':idproforma', $idproforma, PDO::PARAM_INT);
        $stmt->bindParam(':idcliente', $idcliente, PDO::PARAM_INT);
        $stmt->bindParam(':idempleado', $idempleado, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':methodo', $methodo, PDO::PARAM_STR);
        $stmt->bindParam(':codotr', $codotr, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }
}
