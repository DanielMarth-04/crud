<?php
require_once __DIR__ . '/../models/grecepcion.php';
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class grecepcionController
{

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // 🔹 Variables principales
            $idproforma    = $_POST['idproforma'] ?? null;
            $idtrabajador  = $_POST['idtrabajador'] ?? null;
            $idcliente     = $_POST['idcliente'] ?? null;
            $costototal    = $_POST['costo_total'] ?? 0;

            // 🔹 Arrays de detalles
            $servicios   = $_POST['servicio'];
            $codigos     = $_POST['codigo_det'];
            $estados     = $_POST['estado_det'];
            $fechas      = $_POST['fecha_ingreso'];
            $tipos       = $_POST['idtipo'];

            // Validación
            if (empty($idproforma) || empty($idcliente) || empty($idtrabajador)) {
                header("Location: ../index.php?views=grecepcion/agregar&msg=error&error=Datos incompletos");
                exit();
            }

            // Convertir a enteros
            $idproforma   = intval($idproforma);
            $idcliente    = intval($idcliente);
            $idtrabajador = intval($idtrabajador);

            $model = new grecepcion();

            // 🔹 GUARDAR CABECERA
            try {
                $idGrecepcion = $model->guardar(
                    $idproforma,
                    $idtrabajador,
                    $idcliente,
                    $costototal,
                    null,   // código generado automáticamente
                    1       // estado activo
                );
            } catch (Exception $e) {
                error_log("Error guardando cabecera: " . $e->getMessage());
                header("Location: ../index.php?views=grecepcion/agregar&msg=error");
                exit();
            }

            if (!$idGrecepcion) {
                error_log("No se generó ID de recepción.");
                exit("Error guardando guía.");
            }

            // 🔹 GUARDAR DETALLES
            for ($i = 0; $i < count($servicios); $i++) {

                if (trim($servicios[$i]) == "") continue;

                $servicio      = $servicios[$i];
                $codigo_det    = $codigos[$i] ?? '';
                $estado_det    = $estados[$i] ?? 'recepcionado';
                $fecha_ingreso = $fechas[$i] ?? date('Y-m-d');
                $idtipo        = $tipos[$i] ?? null;

                // Evitar inserciones vacías
                if (!$idtipo) continue;

                $ok = $model->guardarDetalleGrecepcion(
                    $idGrecepcion,
                    $idtipo,
                    $servicio,
                    $codigo_det,
                    $fecha_ingreso,
                    $estado_det
                );

                if (!$ok) {
                    error_log("❌ Error guardando detalle fila $i: " . print_r($_POST, true));
                }
            }

            header("Location: ../index.php?views=grecepcion/index&msg=ok");
            exit();
        }

        echo "Método no permitido";
        exit;
    }
}
$controller = new grecepcionController();
$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch ($action) {

    case 'guardar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->guardar();
        } else {
            header('Content-Type: text/plain; charset=utf-8');
            http_response_code(405);
            echo 'Método no permitido. Use POST para guardar.';
        }
        break;

    default:
        header('Content-Type: text/plain; charset=utf-8');
        echo "⚠️ Acción no reconocida o no especificada.";
        break;
}
