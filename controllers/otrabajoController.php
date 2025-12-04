<?php
require_once __DIR__ . '/../models/otrabajo.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class otrabajoController
{
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo "Método no permitido";
            return;
        }
        // --- cabecera ---
        $idproforma    = $_POST['idproforma']    ?? null;
        $idcliente     = $_POST['idcliente']     ?? null;
        $idtrabajador  = $_POST['idtrabajador']  ?? null;
        $fecha         = $_POST['fecha']         ?? null;
        $descripcion   = $_POST['descripcion']   ?? null;
        $methodo       = $_POST['methodo']       ?? null;
        $idguia        = $_POST['idguia']        ?? null;  

        //detalles
        $idotrabajo = $_POST['idotrabajo'];
        $servicios = $_POST['servicio'];
        $codigos = $_POST['cod_det'];

        // --- Validación de campos ---
        if (
            !$idproforma || !$idcliente || !$idtrabajador ||
            !$fecha || !$descripcion || !$methodo || !$idguia
        ) {
            header("Location: ../index.php?views=Otrabajo/agregar&msg=error&error=Datos incompletos");
            return;
        }

        // --- validar fecha ---
        if (!strtotime($fecha)) {
            header("Location: ../index.php?views=Otrabajo/agregar&msg=error&error=Fecha inválida");
            return;
        }

        // Convertir a números
        $idproforma   = intval($idproforma);
        $idcliente    = intval($idcliente);
        $idtrabajador = intval($idtrabajador);
        $idguia       = intval($idguia);

        $model = new otrabajo();

        try {
            $idotrabajo = $model->guardar(
                $idproforma,
                $idcliente,
                $idtrabajador,
                $fecha,
                $descripcion,
                $methodo,
                $idguia,  
                1         // estado
            );
        } catch (Exception $e) {
            error_log("Error guardando cabecera: " . $e->getMessage());
            header("Location: ../index.php?views=Otrabajo/agregar&msg=error");
            return;
        }

        if (!$idotrabajo) {
            error_log("No se generó ID de recepción.");
            echo "Error guardando en orden.";
            return;
        }

        header("Location: ../index.php?views=Otrabajo/index");
        return;
    }
}

// =======================
// Manejo de acciones
// =======================
$controller = new otrabajoController();

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {

    case 'guardar':
        $controller->guardar();
        break;

    default:
        echo "⚠️ Acción no reconocida.";
        break;
}
