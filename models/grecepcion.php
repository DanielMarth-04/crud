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


    public function guardarDetalleGrecepcion($idGrecepcion, $idtipo, $descripcion, $codingr, $feching, $estado)
    {
        date_default_timezone_set('America/Lima');
        // Si el usuario envió solo la fecha
        if (!empty($feching)) {
            // Si viene sin hora → le agregamos la hora actual
            if (strlen($feching) == 10) { // Formato YYYY-MM-DD
                $feching = $feching . ' ' . date('H:i:s');
            }
        } else {
            // Si no enviaron nada → fecha y hora actual
            $feching = date('Y-m-d H:i:s');
        }
        $sql = "INSERT INTO detgrec (idgrecepcion, idtipo, descripcion, codingr, feching, estado)
                VALUES (:idgrecepcion, :idtipo, :descripcion, :codingr, :feching, :estado)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idgrecepcion', $idGrecepcion, PDO::PARAM_INT);
        $stmt->bindParam(':idtipo', $idtipo, PDO::PARAM_INT);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':codingr', $codingr, PDO::PARAM_STR);
        $stmt->bindParam(':feching', $feching, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);

        return $stmt->execute();
    }
    public function obtenerGuias()
    {
        $sql = "SELECT 
                g.id,
                g.codigo,
                p.codigo as codpro,
                c.nombres,
                g.estado
                From
                grecepcion g
                INNER JOIN clientes c ON g.idcliente = c.id  
                INNER JOIN proforma p ON g.idproforma = p.id
                GROUP BY g.id
                ORDER BY g.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        // ✅ Cabecera: ya no unimos con area, solo cliente
        $sqlCabecera = "SELECT 
                            g.id,
                            g.idcliente,
                            g.codigo,
                            g.costotal,
                            p.codigo AS codguia,
                            e.nombres AS nomemple,
                            c.DniRuc,
                            c.nombres,
                            c.contacto,
                            c.direccion,
                            c.telefono,
                            c.correo
                        FROM grecepcion g
                        INNER JOIN clientes c ON g.idcliente = c.id
                        INNER JOIN proforma p ON g.idproforma = p.id
                        INNER JOIN empleados e ON g.idtrabajador = e.id
                        WHERE g.id = ?";
        $stmt = $this->conn->prepare($sqlCabecera);
        $stmt->execute([$id]);
        $cabecera = $stmt->fetch(PDO::FETCH_ASSOC);

        // ✅ Detalle: el área se obtiene desde detalleproforma
        $sqlDetalle = "SELECT 
                            d.descripcion,
                            t.tipo,
                            d.codingr,
                            d.estado,
                            d.feching
                        FROM detgrec d
                        INNER JOIN tipo t ON d.idtipo = t.id      
                        WHERE d.idgrecepcion = ?";
        $stmt2 = $this->conn->prepare($sqlDetalle);
        $stmt2->execute([$id]);
        $detalle = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return [
            'cabecera' => $cabecera,
            'detalle' => $detalle
        ];
    }
}
