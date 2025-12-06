<?php
require_once __DIR__ . '/../models/otrabajo.php';
require_once __DIR__ . '/../models/expecal.php';

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

        // ==========================
        // 📌 OBTENER VARIABLES
        // ==========================
        $idproforma    = $_POST['idproforma']   ?? null;
        $idcliente     = $_POST['idcliente']    ?? null;
        $idtrabajador  = $_POST['idtrabajador'] ?? null;
        $fecha         = $_POST['fecha']        ?? null;
        $descripcion   = $_POST['descripcion']  ?? null;
        $methodo       = $_POST['methodo']      ?? null;
        $idguia        = $_POST['idguia']       ?? null;

        $servicios     = $_POST['servicio']     ?? [];
        $codigos       = $_POST['codigo_det']   ?? [];
        $tipos         = $_POST['idtipo']       ?? [];

        // ==========================
        // ✔ VALIDAR CAMPOS OBLIGATORIOS
        // ==========================
        $faltantes = [];

        $required = [
            'Proforma'              => $idproforma,
            'Cliente'               => $idcliente,
            'Personal Responsable'  => $idtrabajador,
            'Fecha'                 => $fecha,
            'Descripción'           => $descripcion,
            'Método'                => $methodo,
            'Guía'                  => $idguia
        ];

        foreach ($required as $campo => $valor) {
            if (empty($valor)) $faltantes[] = $campo;
        }

        if ($faltantes) {
            $msg = "Faltan los siguientes campos: " . implode(", ", $faltantes);
            return $this->redirectError($msg);
        }

        // Convertir a enteros
        $idproforma   = intval($idproforma);
        $idcliente    = intval($idcliente);
        $idtrabajador = intval($idtrabajador);
        $idguia       = intval($idguia);

        // ==========================
        // ✔ GUARDAR CABECERA
        // ==========================
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
                1
            );
        } catch (Exception $e) {
            return $this->redirectError("Error al guardar cabecera: " . $e->getMessage());
        }

        if (!$idotrabajo) {
            return $this->redirectError("No se pudo registrar la orden de trabajo.");
        }

        // ==========================
        // ✔ GUARDAR DETALLES
        // ==========================
        $total = count($servicios);
        if ($total == 0) {
            return $this->redirectError("Debe agregar al menos un servicio.");
        }

        $errores = [];
        $guardados = 0;

        for ($i = 0; $i < $total; $i++) {

            $servicio   = trim($servicios[$i] ?? '');
            $codigo_ingre = trim($codigos[$i]   ?? '');
            $idtipo        = intval($tipos[$i]   ?? 0);
            $fingreso      = $_POST['fingreso'][$i] ?? "";

            if (!$servicio || $idotrabajo <= 0) {
                $errores[] = "Dato inválido en el servicio #" . ($i + 1);
                continue;
            }

            try {
                $model->guardarDetalleotrabajo(
                    $idtipo,
                    $idotrabajo,
                    $servicio,
                    $codigo_ingre,
                    $fingreso 
                );
                $guardados++;
            } catch (Exception $e) {
                $errores[] = "Error en servicio #" . ($i + 1) . ": " . $e->getMessage();
            }
        }

        // ==========================
        // ✔ RESULTADOS
        // ==========================
        if ($errores) {
            return $this->redirectError("Errores: " . implode(". ", $errores));
        }

        if ($guardados == 0) {
            return $this->redirectError("No se pudo registrar ningún detalle.");
        }

        // ✓ TODO OK
        header("Location: ../index.php?views=Otrabajo/index&msg=success");
    }
    public function listar()
    {
        $model = new otrabajo();
        return $model->obtenerotrabajo();
    }

    // ==========================
    // 📌 FUNCIÓN AUXILIAR LIMPIA
    // ==========================
    private function redirectError($msg)
    {
        header("Location: ../index.php?views=Otrabajo/agregar&msg=error&error=" . urlencode($msg));
        return;
    }
}

// Ejecutar según acción
$controller = new otrabajoController();
$action     = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'guardar':
        $controller->guardar();
        break;

    default:
        echo "⚠ Acción no reconocida.";
        break;
}
