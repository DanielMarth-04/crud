<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

class grecepcion
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }
    public function guardar($idproforma, $idtrabajador, $idcliente, $costototal, $codigo, $estado)
    {
        try {
            // Verificar conexión
            if (!$this->conn) {
                error_log("Error: No hay conexión a la base de datos");
                throw new Exception("No hay conexión a la base de datos");
            }

            // Generar código
            $nuevoNumero = "00001";
            try {
                $sqlUltimo = "SELECT codigo FROM grecepcion ORDER BY id DESC LIMIT 1";
                $stmtUltimo = $this->conn->query($sqlUltimo);
                if ($stmtUltimo !== false) {
                    $ultimo = $stmtUltimo->fetch(PDO::FETCH_ASSOC);
                    if ($ultimo && isset($ultimo['codigo']) && preg_match('/GR-2025-(\d+)/', $ultimo['codigo'], $matches)) {
                        $nuevoNumero = str_pad($matches[1] + 1, 5, '0', STR_PAD_LEFT);
                    }
                }
            } catch (PDOException $e) {
                // Si la tabla está vacía o no existe, usar el número inicial
                error_log("Advertencia al obtener último código: " . $e->getMessage());
            }
            $codigo = "GR-2025-" . $nuevoNumero;

            // Validar que los valores no sean null antes de insertar
            if (is_null($idproforma) || is_null($idtrabajador) || is_null($idcliente)) {
                $errorMsg = "Valores nulos - idproforma: $idproforma, idpersonal: $idtrabajador, idcliente: $idcliente";
                error_log("Error: " . $errorMsg);
                throw new Exception($errorMsg);
            }

            $sql = "INSERT INTO grecepcion (idproforma, idtrabajador, idcliente, costotal, codigo, estado)
                    VALUES (:idproforma, :idtrabajador, :idcliente, :costotal, :codigo, :estado)";
            $stmt = $this->conn->prepare($sql);
            // Asegurar que costototal sea numérico
            if (empty($costototal) || !is_numeric($costototal)) {
                $costototal = 0;
            }

            $stmt->bindParam(':idproforma', $idproforma, PDO::PARAM_INT);
            $stmt->bindParam(':idtrabajador', $idtrabajador, PDO::PARAM_INT);
            $stmt->bindParam(':idcliente', $idcliente, PDO::PARAM_INT);
            $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
            $stmt->bindParam(':costotal', $costototal);

            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                $errorMsg = "Error al ejecutar INSERT: " . print_r($errorInfo, true);
                error_log($errorMsg);
                throw new Exception("Error SQL: " . ($errorInfo[2] ?? "Error desconocido"));
            }

            // 🔹 Retornar el ID insertado
            $lastId = $this->conn->lastInsertId();
            if ($lastId === false || $lastId == 0) {
                $errorMsg = "Error: lastInsertId retornó false o 0. La inserción puede haber fallado.";
                error_log($errorMsg);
                throw new Exception($errorMsg);
            }
            return $lastId;
        } catch (PDOException $e) {
            $errorMsg = "Error PDO al guardar guía de recepción: " . $e->getMessage();
            error_log($errorMsg);
            error_log("Stack trace: " . $e->getTraceAsString());
            throw new Exception("Error de base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            // Re-lanzar excepciones no-PDO
            throw $e;
        }
    }


    public function guardarDetalleGrecepcion($idrecepcion, $idtipo, $idempleados, $servicio, $codigo, $fecha_ingreso, $estado)
    {
        try {
            // Si no viene fecha del formulario, usar la fecha actual
            if (empty($fecha_ingreso)) {
                date_default_timezone_set('America/Lima');
                $fecha_ingreso = date('Y-m-d H:i:s');
            }

            $sql = "INSERT INTO detgrec (idgrecepcion, idtipo, descripcion, codingr, feching, estado)
        VALUES (:idgrecepcion, :idtipo, :descripcion, :codingr, :feching, :estado)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idgrecepcion', $idrecepcion, PDO::PARAM_INT);
            $stmt->bindParam(':idtipo', $idtipo, PDO::PARAM_INT);
            $stmt->bindParam(':idempleados', $idempleados, PDO::PARAM_INT);
            $stmt->bindParam(':descripcion', $servicio, PDO::PARAM_STR);
            $stmt->bindParam(':codingr', $codigo, PDO::PARAM_STR);
            $stmt->bindParam(':feching', $fecha_ingreso, PDO::PARAM_STR);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);;

            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                error_log("Error al ejecutar INSERT detalle: " . print_r($errorInfo, true));
                return false;
            }
            return true;
        } catch (PDOException $e) {
            error_log("Error al guardar detalle de guía de recepción: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
}
