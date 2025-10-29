<?php
require_once __DIR__ . '/../config/database.php';
class servicios
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function obtenerServicios()
    {
        $sql = "SELECT s.id,s.servicio,s.descripcion,a.area, s.estado FROM servicios s INNER JOIN area a ON s.idarea = a.id ORDER BY s.id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerServiciosPorId($id)
    {
        $sql = "SELECT s.*, a.area 
                FROM servicios s 
                INNER JOIN area a ON s.idarea = a.id 
                WHERE s.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function obtenerAreas()
    {
        $sql = "SELECT id, area FROM area ORDER BY area ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerServiciosProf()
    {
        $sql = "SELECT id, servicio FROM servicios ORDER BY servicio ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function guardar($idarea, $servicio, $descripcion, $estado)
    {
        try {
            $sql = "INSERT INTO servicios (idarea, servicio, descripcion, estado)
                    VALUES (:idarea, :servicio, :descripcion, :estado)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idarea', $idarea);
            $stmt->bindParam(':servicio', $servicio);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':estado', $estado);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error al guardar: " . $e->getMessage();
            return false;
        }
    }
    public function editarServicios($id, $servicio, $descripcion, $idarea, $estado)
    {
        try {
            $sql = "UPDATE servicios SET idarea = :idarea, servicio = :servicio, descripcion = :descripcion, estado = :estado WHERE id = :id";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idarea', $idarea, PDO::PARAM_INT);
            $stmt->bindParam(':servicio', $servicio);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // 👈 ESTE FALTABA

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al editar cliente: " . $e->getMessage());
            return false;
        }
    }

}
