<?php
require_once __DIR__ . '/../models/proformas.php';
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

class grecepcionController
{

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $idcliente = $_POST['idcliente'] ?? '';
            $idarea = $_POST['idarea'] ?? '';
            $idservicios = $_POST['idservicio'] ?? [];
            $precios = $_POST['precio'] ?? [];
            $estado = 1;

            // Calcular total
            $total = is_array($precios) ? array_sum($precios) : floatval($precios);

            $model = new proformas();
            $idProforma = $model->guardar($idcliente, $idarea, '', $total, $estado);

            if ($idProforma) {
                foreach ($idservicios as $i => $servicioId) {
                    $valor = $precios[$i] ?? 0;
                    $model->guardarDetalleProforma($idProforma, $servicioId, $valor);
                }

                header("Location: ../index.php?views=proformas/index&msg=ok");
                exit();
            } else {
                echo "❌ Error: no se pudo guardar la proforma.";
            }
        }
    }
    public function agregarGuia($idProforma)
    {
        $proformaModel = new proformas();
        $total = $proformaModel->obtenerTotalProforma($idProforma);

        require __DIR__ . '/../views/grecepcion/agregar.php';
    }
}
