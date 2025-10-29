<?php
require_once __DIR__ . '/../config/database.php';

class clientes
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function guardar($nombreRz,$DniRuc, $email, $telefono, $contacto, $direccion, $estado)
    {
        try {
            $sql = "INSERT INTO clientes (nombres, DniRuc, correo, telefono, contacto, direccion, estado)
                    VALUES (:nombres, :DniRuc, :correo, :telefono, :contacto, :direccion, :estado)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombres', $nombreRz);
            $stmt->bindParam(':DniRuc', $DniRuc);
            $stmt->bindParam(':correo', $email);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':contacto', $contacto);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':estado', $estado);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error al guardar: " . $e->getMessage();
            return false;
        }
    }
    public function obtenerClientes()
    {
        $sql = "SELECT * FROM clientes ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerClientesProf()
    {
        $sql = "SELECT id, nombres FROM clientes ORDER BY nombres ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerClientePorId($id)
    {
        $sql = "SELECT * FROM clientes WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function editarClientes($id, $nombreRz, $DniRuc, $email, $telefono, $contacto, $direccion, $estado)
    {
        try {
            $sql = "UPDATE clientes SET nombres = :nombreRz, DniRuc = :DniRuc, correo = :email, telefono = :telefono, contacto = :contacto, direccion = :direccion, estado = :estado WHERE id = :id";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombreRz', $nombreRz);
            $stmt->bindParam(':DniRuc', $DniRuc, PDO::PARAM_INT);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':contacto', $contacto);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // 👈 ESTE FALTABA

            return $stmt->execute();
        }catch (PDOException $e) {
            // 🚨 CAMBIO CRÍTICO: Muestra el error y detiene la ejecución 🚨
            echo "<h2 style='color: red;'>ERROR DE ACTUALIZACIÓN SQL:</h2>";
            echo "Mensaje de PDO: " . $e->getMessage();
            echo "<br>Código SQLSTATE: " . $e->getCode();
            exit(); // <-- Esto detiene la redirección
            // ----------------------------------------------------
        }
    }

    public function eliminar($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM clientes WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
