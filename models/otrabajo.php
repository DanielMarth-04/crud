<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

class otrabajo
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    /**
     * Guarda cabecera de orden de trabajo
     */
    public function guardar($idproforma, $idcliente, $idempleado, $fecha, $descripcion, $methodo, $codotr, $estado)
    {
        // ================================
        //  1. Generar Código OT dinámico
        // ================================
        $codotr = $this->generarCodigo();

        // ================================
        //  2. Insertar datos
        // ================================
        $sql = "INSERT INTO otrabajo 
                (idproforma, idcliente, idempleado, fecha, descripcion, methodo, codotr, estado)
                VALUES
                (:idproforma, :idcliente, :idempleado, :fecha, :descripcion, :methodo, :codotr, :estado)";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ':idproforma' => $idproforma,
            ':idcliente'  => $idcliente,
            ':idempleado' => $idempleado,
            ':fecha'      => $fecha,
            ':descripcion' => $descripcion,
            ':methodo'    => $methodo,
            ':codotr'     => $codotr,
            ':estado'     => $estado
        ]);

        return $this->conn->lastInsertId();
    }
    public function guardarDetalleotrabajo($idtipo, $idotrabajo, $servicio, $cod_ingre, $fingreso)
    {
        date_default_timezone_set('America/Lima');
    
        // Ajustar fingreso
        if (!empty($fingreso) && strlen($fingreso) == 10) {
            $fingreso .= ' ' . date('H:i:s');
        } elseif (empty($fingreso)) {
            $fingreso = date('Y-m-d H:i:s');
        }
    
        // Código expediente
        $codigoExp = "EXP-" . date("Y") . "-" . str_pad($cod_ingre, 5, "0", STR_PAD_LEFT);
        $fcreacion = date('Y-m-d H:i:s');
    
        // ============================
        // INSERT EXPEDIENTE
        // ============================
        $sqlExp = "
            INSERT INTO expcal (idtipo, iddetotra, codigo, fcreacion)
            VALUES (:idtipo, :iddetotra, :codigo, :fcreacion)
        ";
    
        $stmtExp = $this->conn->prepare($sqlExp);
        $stmtExp->execute([
            ':idtipo'     => $idtipo,
            ':iddetotra'  => $idotrabajo,  // AHORA SI COINCIDE
            ':codigo'     => $codigoExp,
            ':fcreacion'  => $fcreacion
        ]);
    
        // Obtener ID
        $idExp = $this->conn->lastInsertId();
        if (!$idExp) {
            throw new Exception("No se obtuvo el ID del Expediente");
        }
    
        // ============================
        // INSERT DETALLE OT
        // ============================
        $sql = "
            INSERT INTO detotra (idotrabajo, idexp, servicio, codingre, fingreso)
            VALUES (:idotrabajo, :idexp, :servicio, :codingre, :fingreso)
        ";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':idotrabajo' => $idotrabajo,
            ':idexp'      => $idExp,
            ':servicio'   => $servicio,
            ':codingre'   => $cod_ingre,
            ':fingreso'   => $fingreso
        ]);
    
        return true;
    }
    public function obtenerotrabajo()
    {
        $sql = "SELECT 
                o.id,
                p.codigo as codpro,
                o.codotr,
                c.nombres,
                o.estado,
                o.fecha
                From
                otrabajo o
                INNER JOIN clientes c ON o.idcliente = c.id  
                INNER JOIN proforma p ON o.idproforma = p.id
                GROUP BY o.id
                ORDER BY o.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    /**
     * Generar código correlativo OT-2025-xxxxx
     */
    private function generarCodigo()
    {
        try {
            $sql = "SELECT codotr FROM otrabajo ORDER BY id DESC LIMIT 1";
            $stmt = $this->conn->query($sql);

            if ($stmt) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && preg_match('/OT-2025-(\d+)/', $row['codotr'], $m)) {
                    $num = str_pad($m[1] + 1, 5, '0', STR_PAD_LEFT);
                } else {
                    $num = "00001";
                }
            } else {
                $num = "00001";
            }
        } catch (PDOException $e) {
            error_log("⚠ Error generando código OT: " . $e->getMessage());
            $num = "00001";
        }

        return "OT-2025-" . $num;
    }
}
