<?php
require_once __DIR__ . '/../models/grecepcion.php';
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

class grecepcionController
{

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // 🔹 Variables principales (únicas)
            $idproforma   = $_POST['idproforma'] ?? null;
            $idtrabajador   = $_POST['idtrabajador'] ?? null;
            $idcliente   = $_POST['idcliente'] ?? null;
            $costototal  = $_POST['costo_total'] ?? 0;
            $codigo       = null; // Se genera automáticamente en el modelo
            $estado       = 1;

            // 🔹 Detalles (arrays)
            $servicios      = $_POST['servicio'] ?? [];
            $codigos        = $_POST['codigo'] ?? []; // Array de códigos de servicios
            $fechas_ingreso = $_POST['fecha_ingreso'] ?? [];
            $idtipos        = $_POST['idtipo'] ?? [];

            // Validación básica
            if (empty($idproforma) || empty($idcliente) || empty($idtrabajador)) {
                header("Location: ../index.php?views=grecepcion/agregar&msg=error&error=Datos incompletos");
                exit();
            }

            // Validar que los valores sean numéricos
            $idproforma = filter_var($idproforma, FILTER_VALIDATE_INT);
            $idtrabajador = filter_var($idtrabajador, FILTER_VALIDATE_INT);
            $idcliente = filter_var($idcliente, FILTER_VALIDATE_INT);
            $costototal = filter_var($costototal, FILTER_VALIDATE_FLOAT) ?: 0;

            if ($idproforma === false || $idtrabajador === false || $idcliente === false) {
                error_log("Error: Valores inválidos - idproforma: " . $_POST['idproforma'] . ", idtrabajador: " . $_POST['idtrabajador'] . ", idcliente: " . $_POST['idcliente']);
                header("Location: ../index.php?views=grecepcion/agregar&msg=error&error=Datos inválidos. Por favor verifique los campos.");
                exit();
            }

            $model = new grecepcion();
            
            // Depuración: capturar el error específico
            try {
                $idGrecepcion = $model->guardar($idproforma, $idtrabajador, $idcliente, $costototal, $codigo, $estado);
            } catch (Exception $e) {
                error_log("Excepción al guardar: " . $e->getMessage());
                $errorMsg = urlencode("Error: " . $e->getMessage());
                header("Location: ../index.php?views=grecepcion/agregar&msg=error&error=$errorMsg");
                exit();
            }

            if ($idGrecepcion && $idGrecepcion > 0) {
                foreach ($servicios as $i => $servicio) {
                    if (empty($servicio)) continue;

                    $codigo = $codigos[$i] ?? '';
                    $fecha_ingreso = $fechas_ingreso[$i] ?? '';
                    $idtipo = $idtipos[$i] ?? null;
                    error_log("📩 Datos recibidos para detalle: " . print_r([
                        'idrecepcion' => $idGrecepcion,
                        'idtipo' => $idtipo,
                        'servicio' => $servicio,
                        'codigo' => $codigo,
                        'fecha_ingreso' => $fecha_ingreso,
                        'estado' => $estado
                    ], true));
                    // Validación básica
                    if ($idtipo && $servicio) {
                        $ok = $model->guardarDetalleGrecepcion( $idGrecepcion, $idtipo, $servicio, $codigo, $fecha_ingreso, $estado);

                        if (!$ok) {
                            error_log("❌ Error al guardar detalle: servicio=$servicio, tipo=$idtipo");
                        }
                    } else {
                        error_log("⚠️ Detalle omitido por datos incompletos: index $i");
                    }
                }

                header("Location: ../index.php?views=grecepcion/index&msg=ok");
                exit();
            } else {
                // Obtener el último error de PHP para mostrar más detalles
                $lastError = error_get_last();
                $errorMsg = "No se pudo guardar la guía de recepción";
                if ($lastError && isset($lastError['message'])) {
                    $errorMsg .= ". Error: " . $lastError['message'];
                }
                error_log("Error al guardar - idGrecepcion retornado: " . var_export($idGrecepcion, true));
                header("Location: ../index.php?views=grecepcion/agregar&msg=error&error=" . urlencode($errorMsg));
                exit();
            }
        }
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
