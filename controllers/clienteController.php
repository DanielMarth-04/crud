
<?php
require_once __DIR__ . '/../models/clientes.php';

class clienteController
{
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            
            // Capturar los datos del formulario
            $nombreRz  = $_POST['nombreRz'] ?? '';
            $DniRuc    = ($_POST['DniRuc'] ?? '');
            $email     = $_POST['email'] ?? '';
            $telefono  = ($_POST['telefono'] ?? '');
            $contacto = $_POST['contacto'] ?? '';
            $direccion = $_POST['direccion'] ?? '';
            $estado    = '1';

            // Llamar al modelo
            $model = new clientes();
            $resultado = $model->guardar($nombreRz, $DniRuc, $email, $telefono, $contacto, $direccion, $estado);

            if ($resultado) {
                // Redirigir si se guarda correctamente
                header("Location: ../index.php?views=clientes/index&msg=ok");
                exit();
            } else {
                echo " Error: no se pudo guardar el cliente.";
            }
        }
    }
    public function listar()
    {
        $model = new clientes();
        return $model->obtenerClientes();
    }
    public function listarClienteprof()
    {
        $model = new clientes();
        return $model->obtenerClientesProf();
    }
    public function obtenerClientePorId($id)
    {
        $model = new clientes();
        return $model->obtenerClientePorId($id);
    }
    public function editar()
    {
        // Muestra debug solo si necesitas probar
        // echo "<pre>DEBUG: Entrando al método editar()</pre>";

        // Validar si el formulario viene por POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../index.php?views=clientes/index&error=invalid_request");
            exit();
        }

        // Capturar datos del formulario
        $id         = $_POST['id'] ?? null;
        $nombreRz   = $_POST['nombreRz'] ?? '';
        $DniRuc   = $_POST['DniRuc'] ?? '';
        $email      = $_POST['email'] ?? '';
        $telefono   = $_POST['telefono'] ?? '';
        $contacto = $_POST['contacto'] ?? '';
        $direccion  = $_POST['direccion'] ?? '';
        $estado     = $_POST['estado'] ?? '1';

        // Validar que venga un ID
        if (!$id) {
            header("Location: ../index.php?views=clientes/index&error=noid");
            exit();
        }

        // Llamar al modelo
        $model = new clientes();
        $resultado = $model->editarClientes($id, $nombreRz, $DniRuc, $email, $telefono, $contacto, $direccion, $estado);

        // Redirección según el resultado
        if ($resultado) {
            header("Location: ../index.php?views=clientes/index&msg=ok");
            exit();
        } else {
            header("Location: ../index.php?views=clientes/index&error=updatefail");
            exit();
        }
    }

    public function eliminar($id)
    {
        $model = new clientes();
        $resultado = $model->eliminar($id);

        if ($resultado) {
            // Redirige al listado con mensaje de éxito
            header("Location: ../index.php?views=clientes/index&msg=deleted");
            exit();
        } else {
            // Redirige al listado con mensaje de error
            header("Location: ../index.php?views=clientes/index&error=deletefail");
            exit();
        }
    }

}
// --- Router del controlador ---
if (isset($_GET['action'])) {
    $controller = new clienteController();

    switch ($_GET['action']) {
        case 'guardar':
            $controller->guardar();
            break;

        case 'editar':
            $controller->editar();
            break;

        case 'eliminar':
            if (isset($_GET['id'])) {
                $controller->eliminar($_GET['id']);
            }
            break;
    }
}
