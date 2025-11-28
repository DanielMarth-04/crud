<?php
require_once __DIR__ . '/../models/grecepcion.php';
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class PDF extends FPDF
{
    // Función auxiliar para convertir texto a ISO-8859-1 de forma segura
    function safe_text($text, $default = '')
    {
        $converted = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
        return $converted === false ? $default : $converted;
    }
    function Header()
    {
        $this->SetXY(16, 10);
        $this->Cell(20, 20, '', 1); // Celda con borde

        $this->Image(__DIR__ . '/../assets/img/logo.png', 17, 11, 18);

        $this->SetXY(36, 10);
        $this->SetFont('Arial', 'B', 10); // Asegurarse de reestablecer la fuente si la usas
        $this->Cell(120, 10, $this->safe_text('FR/LC-OP-003  '), 1, 1, 'C');
        $this->SetXY(36, 20);
        $this->Cell(120, 10, $this->safe_text('GUÍA DE RECEPCIÓN '), 1, 1, 'C');

        // Resto de la cabecera (Revisado, Aprobado, Versión, Fecha)
        $this->SetFont('Arial', 'B', 7);
        $this->SetXY(156, 10);
        $this->Cell(20, 10, 'Revisado: CC', 1, 1, 'C');
        $this->SetXY(156, 20);
        $this->Cell(20, 10, 'Aprobado: GG', 1, 1, 'C');
        $this->SetXY(176, 10);
        $this->Cell(20, 10, 'Ver: 04', 1, 1, 'C');
        $this->SetXY(176, 20);
        $this->Cell(20, 10, '2024-02-15', 1, 1, 'C');

        // Puedes dejar un espacio o línea de separación después de la cabecera
        $this->SetY(10);
    }

    function Footer()
    {
        // Configuración de fuente
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(0, 0, 0);

        // Posición: 20 mm desde el fondo
        $this->SetY(-12);

        // Texto: Página X de Y
        $this->Cell(
            0,
            10,
            $this->safe_text('Página ' . $this->PageNo() . ' de {nb}'),
            0,
            0,
            'C'
        );

        // Línea justo debajo del texto
        $this->SetDrawColor(0, 0, 0);
        $this->Line(10, $this->GetY() + 6, 200, $this->GetY() + 6);

        // --- Texto informativo usando métodos estándar de FPDF ---
        $this->SetY(-6);
        $this->SetFont('Arial', 'B', 5);
        $this->Cell(0, 3, $this->safe_text('Atención al cliente: 972458381 | Email: laboratorio@sh.com.pe | web: https://www.sh.com.pe'), 0, 1, 'C');

        $this->SetFont('Arial', 'B', 5);
        $this->SetTextColor(0, 0, 0);
        // Usamos GetX para centrar el texto de la dirección
        $this->SetX((210 - $this->GetStringWidth('S&H INGENIEROS: predio los arenales sub lote B-1C, Pimentel-Lambayeque-Peru.')) / 2);
        $this->Cell(0, 3, $this->safe_text('S&H INGENIEROS: predio los arenales sub lote B-1C, Pimentel-Lambayeque-Peru.'), 0, 0, 'L');
    }
}
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

                $servicio      = trim($servicios[$i]);
                $codigo_det    = $codigos[$i] ?? '';
                $estado_det    = $estados[$i] ?? 'recepcionado';
                $fecha_ingreso = $fechas[$i] ?? date('Y-m-d');
                $idtipo        = $tipos[$i] ?? null;


                if (empty($servicio) || empty($idtipo)) continue;

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
    public function listar()
    {
        $model = new grecepcion();
        return $model->obtenerGuias();
    }
    public function generar($id)
    {
        ob_clean();
        // --- Limpia cualquier salida previa para evitar el error de FPDF ---
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $model = new grecepcion();
        $proforma = $model->obtenerPorId($id);
        $cabecera = $proforma['cabecera'];
        $detalle = $proforma['detalle'];

        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY(120, 30);
        $pdf->Cell(25, 10, $pdf->safe_text("PROFORMA N°:"), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetXY(120, 30);
        $pdf->Cell(75, 10, $pdf->safe_text($cabecera['codguia']), 0, 1, 'C');

        // Datos del cliente
        $pdf->SetFont('Arial', '', 10);
        $pdf->Ln(10);
        $pdf->Cell(40, 8, 'Cliente:');
        $pdf->Cell(100, 8, $pdf->safe_text($cabecera['nombres']), 0, 1);
        $pdf->Cell(40, 8, 'RUC / DNI:');
        $pdf->Cell(100, 8, $cabecera['DniRuc'], 0, 1);
        $pdf->Cell(40, 8, 'Direccion:');
        $pdf->Cell(100, 8, $pdf->safe_text($cabecera['direccion'] ?? ''), 0, 1);
        $pdf->Cell(40, 8, 'Contacto');
        $pdf->Cell(100, 8, $pdf->safe_text($cabecera['contacto'] ?? ''), 0, 1);
        $pdf->Cell(40, 8, 'Telefono:');
        $pdf->Cell(100, 8, $cabecera['telefono'] ?? '', 0, 1);
        $pdf->Cell(40, 8, 'Correo:');
        $pdf->Cell(100, 8, $pdf->safe_text($cabecera['correo'] ?? ''), 0, 1);

        // --- Tabla encabezado ---
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(190, 231, 250);
        $pdf->SetXY(15,105);
        $pdf->Cell(10, 8, 'ITEM', 1, 0, 'C', true);
        $pdf->SetXY(25,105);
        $pdf->Cell(30, 8, 'DESCRIPCION', 1, 0, 'C', true);
        $pdf->SetXY(55,105);
        $pdf->Cell(30, 8, 'CODIGO INGRESO', 1, 0, 'C', true);
        $pdf->SetXY(85,105);
        $pdf->Cell(30, 8, 'TIPO', 1, 0, 'C', true);
        $pdf->SetXY(115,105);
        $pdf->Cell(30, 8, 'ESTADO', 1, 1, 'C', true);
        $pdf->SetXY(145,105);
        $pdf->Cell(30, 8, 'FECHA INGRESO', 1, 1, 'C', true);

        // --- Detalle de servicios ---
        $pdf->SetFont('Arial', '', 8);
        $item = 1;
        foreach ($detalle as $fila) {
            $descripcion = isset($fila['descripcion']) ? $pdf->safe_text($fila['descripcion']) : '';
            $coding = isset($fila['codingr']) ? $pdf->safe_text($fila['codingr']) : '';
            $tipo = isset($fila['tipo']) ? $pdf->safe_text($fila['tipo']) : '';
            $estado = isset($fila['estado']) ? $pdf->safe_text($fila['estado'], 2) : '0.00';
            $fechaing = isset($fila['feching']) ? $pdf->safe_text($fila['feching'], 2) : '';

            $pdf->Cell(10, 6, $item, 1, 0, 'C');
            $pdf->Cell(30, 6, $descripcion, 1, 0, 'L');
            $pdf->Cell(30, 6, $coding, 1, 0, 'C');
            $pdf->Cell(30, 6, $tipo, 1, 0, 'C');
            $pdf->Cell(30, 6, $estado, 1, 0, 'C');
            $pdf->Cell(30, 6, $fechaing, 1, 1, 'C');

            $item++;
        }

        $pdf->Output('I', 'Fecha de Ingreso');
    }
}
$controller = new grecepcionController();
$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch ($action) {

    case 'guardar':
        $controller->guardar();
        break;

    case 'listar':
        $controller->listar();
        break;
    case 'generar':
        if ($action === 'generar')
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $controller = new grecepcionController();
            $controller->generar($id);
            break;
        }
    default:
        echo "⚠️ Acción no reconocida.";
        break;
}
