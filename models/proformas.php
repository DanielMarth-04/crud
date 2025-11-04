<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

class proformas
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }
    public function obtenerProformas()
    {
        $sql = "SELECT 
        p.id,
        p.codigo,
        p.fecha,
        p.estado,
        c.DniRuc AS dni_ruc,
        c.nombres AS cliente,
        c.contacto,
        GROUP_CONCAT(s.servicio SEPARATOR ', ') AS servicios
    FROM proforma p
    INNER JOIN clientes c ON p.idcliente = c.id
    INNER JOIN detalleproforma d ON p.id = d.idproforma
    INNER JOIN servicios s ON d.idservicio = s.id
    GROUP BY p.id
    ORDER BY p.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerServiciosPorProforma($idproforma)
    {
        try {
            $sql = "SELECT 
                        d.id, 
                        s.servicio, 
                        d.valor
                    FROM detalleproforma d
                    INNER JOIN servicios s ON d.idservicio = s.id
                    WHERE d.idproforma = :idproforma";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idproforma', $idproforma, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al obtener servicios por proforma: " . $e->getMessage();
            return [];
        }
    }
    public function obtenerTotalProforma($idProforma)
    {
        $sql = "SELECT SUM(valor) AS total 
            FROM detalleproforma 
            WHERE idproforma = :idProforma";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idProforma', $idProforma, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function obtenerProformasPorId($id = null)
    {
        $sql = "SELECT id, codigo FROM proforma ORDER BY codigo ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function guardar($idcliente, $codigo, $precio, $estado)
    {
        try {
            // Generar código
            $sqlUltimo = "SELECT codigo FROM proforma ORDER BY id DESC LIMIT 1";
            $stmtUltimo = $this->conn->query($sqlUltimo);
            $ultimo = $stmtUltimo->fetch(PDO::FETCH_ASSOC);

            if ($ultimo && preg_match('/PF-2025-(\d+)/', $ultimo['codigo'], $matches)) {
                $nuevoNumero = str_pad($matches[1] + 1, 5, '0', STR_PAD_LEFT);
            } else {
                $nuevoNumero = "00001";
            }
            $codigo = "PF-2025-" . $nuevoNumero;
            date_default_timezone_set('America/Lima');
            $fecha = date('Y-m-d H:i:s');

            $sql = "INSERT INTO proforma (idcliente, codigo, precio, fecha, estado)
                    VALUES (:idcliente, :codigo, :precio, :fecha, :estado)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idcliente', $idcliente);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':estado', $estado);
            $stmt->execute();

            // 🔹 Retornar el ID insertado
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            echo "Error al guardar proforma: " . $e->getMessage();
            return false;
        }
    }


    public function guardarDetalleProforma($idproforma, $idservicio, $idtipo, $idarea, $valor)
    {
        try {
            $sql = "INSERT INTO detalleproforma (idproforma, idservicio, idtipo, idarea, valor)
                    VALUES (:idproforma, :idservicio, :idtipo, :idarea, :valor)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idproforma', $idproforma);
            $stmt->bindParam(':idservicio', $idservicio);
            $stmt->bindParam(':idtipo', $idtipo);
            $stmt->bindParam(':idarea', $idarea);
            $stmt->bindParam(':valor', $valor);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            die("Error al guardar detalle: " . $e->getMessage());
        }
    }

    public function obtenerPorId($id)
    {
        // ✅ Cabecera: ya no unimos con area, solo cliente
        $sqlCabecera = "SELECT 
                            p.id,
                            p.codigo,
                            p.fecha,
                            p.precio AS total,
                            p.estado,
                            c.DniRuc,
                            c.nombres,
                            c.contacto,
                            c.direccion,
                            c.telefono,
                            c.correo
                        FROM proforma p
                        INNER JOIN clientes c ON p.idcliente = c.id
                        WHERE p.id = ?";
        $stmt = $this->conn->prepare($sqlCabecera);
        $stmt->execute([$id]);
        $cabecera = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // ✅ Detalle: el área se obtiene desde detalleproforma
        $sqlDetalle = "SELECT 
                            s.servicio AS descripcion,
                            a.area,
                            d.valor
                        FROM detalleproforma d
                        INNER JOIN servicios s ON d.idservicio = s.id
                        INNER JOIN area a ON d.idarea = a.id
                        WHERE d.idproforma = ?";
        $stmt2 = $this->conn->prepare($sqlDetalle);
        $stmt2->execute([$id]);
        $detalle = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
        return [
            'cabecera' => $cabecera,
            'detalle' => $detalle
        ];
    }
}   
