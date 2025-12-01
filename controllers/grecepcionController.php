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
        $this->Cell(0, 3, $this->safe_text('Atención al cliente: 972458381 / Email: laboratorio@sh.com.pe / Web: https://www.sh.com.pe/
S&H INGENIEROS: Predio los arenales sub-lote B-1C, Pimentel - Lambayeque - Perú'), 0, 1, 'C');

        $this->SetFont('Arial', 'B', 5);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 3, $this->safe_text('BCP CTA: 3052311877099 || BCP CCI: 00230500231187709911 || YAPE: 988 432 896'), 0, 0, 'C');
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
        $pdf->SetXY(15, 105);
        $pdf->Cell(10, 8, 'ITEM', 1, 0, 'C', true);
        $pdf->SetXY(25, 105);
        $pdf->Cell(30, 8, 'DESCRIPCION', 1, 0, 'C', true);
        $pdf->SetXY(55, 105);
        $pdf->Cell(30, 8, 'CODIGO INGRESO', 1, 0, 'C', true);
        $pdf->SetXY(85, 105);
        $pdf->Cell(30, 8, 'TIPO', 1, 0, 'C', true);
        $pdf->SetXY(115, 105);
        $pdf->Cell(30, 8, 'ESTADO', 1, 1, 'C', true);
        $pdf->SetXY(145, 105);
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
        $pdf->SetXY(15, 125);
        $pdf->SetFillColor(190, 231, 250);
        $pdf->cell(30, 5, 'OBS. ENTRADA', 1, 1, 'C', true);
        $pdf->SetXY(45, 125);
        $pdf->cell(130, 5, '', 1, 1, 'C');

        $pdf->SetXY(25, 135);
        $pdf->SetFillColor(190, 231, 250);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->cell(20, 5, 'M. DE PAGO', 1, 1, 'C', true);
        $pdf->SetXY(45, 135);
        $pdf->cell(20, 5, '', 1, 1, 'C');

        $pdf->SetXY(65, 135);
        $pdf->SetFillColor(190, 231, 250);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->cell(20, 5, 'BANCO', 1, 1, 'C', true);
        $pdf->SetXY(85, 135);
        $pdf->cell(20, 5, '', 1, 1, 'C');

        $pdf->SetXY(105, 135);
        $pdf->SetFillColor(190, 231, 250);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(30, 5, iconv('UTF-8', 'ISO-8859-1', '# DE OPERACIÓN'), 1, 1, 'C', true);
        $pdf->SetXY(135, 135);
        $pdf->cell(30, 5, '', 1, 1, 'C');

        $pdf->SetXY(25, 140);
        $pdf->SetFillColor(190, 231, 250);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->cell(20, 5, 'TOTAL', 1, 1, 'C', true);
        // Valor a la derecha del TOTAL
        $pdf->SetXY(45, 140);  // ← Ajusta X y Y para que quede alineado
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(40, 5, $pdf->safe_text($cabecera['costotal'] ?? ''), 1, 0, 'L');


        $pdf->SetXY(65, 140);
        $pdf->SetFillColor(190, 231, 250);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(20, 5, iconv('UTF-8', 'ISO-8859-1', 'PAGÓ '), 1, 1, 'C', true);
        $pdf->SetXY(85, 140);
        $pdf->cell(20, 5, '', 1, 1, 'C');

        $pdf->SetXY(105, 140);
        $pdf->SetFillColor(190, 231, 250);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(30, 5, iconv('UTF-8', 'ISO-8859-1', '# DE FACTURA'), 1, 1, 'C', true);
        $pdf->SetXY(135, 140);
        $pdf->cell(30, 5, '', 1, 1, 'C');

        $pdf->SetXY(45, 150);
        $pdf->cell(30, 5, 'NOTA: Para el recojo de los items presentar este documento.', 0, 1, 'C');

        $pdf->SetXY(25, 160);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Multicell(140, 5, 'El item ha sido verificado en sus condiciones de operacion aparente.
Cualquier defecto se comunicara inmediatamente al cliente.
Una vez ingresado se procedera con la calibracion si el item cumple con los requisitos.', 1, 0,false, 'C');

$pdf->SetXY(20, 250);
$pdf->Line(20, 250, 80, 250); // (x1, y1, x2, y2)

$pdf->SetXY(30, 245);  // ← Ajusta X y Y para que quede alineado
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 5, $pdf->safe_text($cabecera['nomemple'] ?? ''), 0, 0, 'L');

$pdf->SetXY(30, 252);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(30, 5, iconv('UTF-8', 'ISO-8859-1', 'Responsable de recepción'), 0, 0, 'C', false);

$pdf->SetXY(30, 260);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(30, 5, iconv('UTF-8', 'ISO-8859-1', 'S&H INGENIEROS S.R.L.'), 0, 0, 'C', false);

// Línea de firma (de 140 a 200)
$pdf->Line(140, 250, 200, 250);

// Texto centrado debajo de la línea
$pdf->SetXY(140, 252);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(60, 5, iconv('UTF-8', 'ISO-8859-1', 'Responsable del Cliente'), 0, 0, 'C');
$pdf->SetXY(140, 260);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(30, 5, iconv('UTF-8', 'ISO-8859-1', 'Nombre:'), 0, 0, 'C', false);
$pdf->SetXY(140, 275);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(30, 5, iconv('UTF-8', 'ISO-8859-1', 'DNI:'), 0, 0, 'C', false);

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
