<?php
require_once __DIR__ . '/../models/servicios.php';
class serviciosController
{
    public function listar()
    {
        $model = new servicios();
        return $model->obtenerServicios();
    }
    public function areas()
    {
        $model = new servicios();
        return $model->obtenerAreas();
    }
    public function servicios()
    {
        $model = new servicios();
        return $model->obtenerServiciosProf();
    }
    public function guardar()
    {
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $idarea = $_POST['idarea'] ?? '';
            $servicio  = $_POST['servicio'] ?? '';
            $descripcion     = $_POST['descripcion'] ?? '';
            $estado    = '1';

            // Llamar al modelo
            $model = new servicios();
            $resultado = $model->guardar($idarea, $servicio, $descripcion, $estado);

            if ($resultado) {
                // Redirigir si se guarda correctamente
                header("Location: ../index.php?views=servicios/index&msg=ok");
                exit();
            } else {
                echo " Error: no se pudo guardar el servicio.";
            }
        }
    }
    public function obtenerServiciosPorId($id)
    {
        $model = new servicios();
        return $model->obtenerServiciosPorId($id);
    }
    public function editar()
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../index.php?views=servicios/index&error=invalid_request");
            exit();
        }

        // Capturar datos del formulario
        $id         = $_POST['id'] ?? null;
        $servicio   = $_POST['servicio'] ?? '';
        $descripcion      = $_POST['descripcion'] ?? '';
        $idarea   = $_POST['idarea'] ?? '';
        $estado     = $_POST['estado'] ?? '1';

        // Validar que venga un ID
        if (!$id) {
            header("Location: ../index.php?views=servicios/index&error=noid");
            exit();
        }

        // Llamar al modelo
        $model = new servicios();
        $resultado = $model->editarServicios($id, $servicio, $descripcion, $idarea, $estado);

        // Redirección según el resultado
        if ($resultado) {
            header("Location: ../index.php?views=servicios/index&msg=ok");
            exit();
        } else {
            header("Location: ../index.php?views=servicios/index&error=updatefail");
            exit();
        }
    }
}
// --- Router del controlador ---
if (isset($_GET['action'])) {
    $controller = new serviciosController();

    switch ($_GET['action']) {
        case 'guardar':
            $controller->guardar();
            break;

        case 'editar':
            $controller->editar();
            break;

        /*case 'eliminar':
            if (isset($_GET['id'])) {
                $controller->eliminar($_GET['id']);
            }
            break;*/
    }
}
