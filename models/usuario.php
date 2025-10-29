<?php
require_once __DIR__ . '/../config/database.php';

class usuarioModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function login($usuario, $password)
    {
        $sql = "SELECT u.*, r.roles 
                FROM usuarios u 
                INNER JOIN roles r ON u.idrol = r.id
                WHERE u.usuario = :usuario AND u.estado = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":usuario", $usuario);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['contrasenia'])) {
            return $user;
        }

        return false;
    }
}
