<?php

require_once __DIR__ . '/../models/proformas.php';
require_once __DIR__ . '/../lib/fpdf/fpdf.php';
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
        $this->Cell(120, 10, $this->safe_text('FR/LC-OP-002 '), 1, 1, 'C');
        $this->SetXY(36, 20);
        $this->Cell(120, 10, $this->safe_text('PROFORMA DE SERVICIO DE CALIBRACIÓN'), 1, 1, 'C');

        // Resto de la cabecera (Revisado, Aprobado, Versión, Fecha)
        $this->SetFont('Arial', 'B', 7);
        $this->SetXY(156, 10);
        $this->Cell(20, 10, 'Revisado: CC', 1, 1, 'C');
        $this->SetXY(156, 20);
        $this->Cell(20, 10, 'Aprobado: GG', 1, 1, 'C');
        $this->SetXY(176, 10);
        $this->Cell(20, 10, 'Ver: 05', 1, 1, 'C');
        $this->SetXY(176, 20);
        $this->Cell(20, 10, '2022-10-06', 1, 1, 'C');

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
class proformasController
{
    public function listar()
    {
        $detProf = [];
        $model = new proformas();
        return $model->obtenerProformas();
    }
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $idcliente   = $_POST['idcliente'] ?? '';
            $idareas     = $_POST['idarea'] ?? [];
            $idservicios = $_POST['idservicio'] ?? [];
            $idtipos     = $_POST['idtipo'] ?? [];
            $precios     = $_POST['precio'] ?? [];
            $estado      = 1;

            // 🔹 Asegurar que todos sean arrays
            $idareas     = (array)$idareas;
            $idservicios = (array)$idservicios;
            $idtipos     = (array)$idtipos;
            $precios     = (array)$precios;

            // 🔹 Calcular total general
            $total = array_sum(array_map('floatval', $precios));

            $model = new proformas();
            $idProforma = $model->guardar($idcliente, '', $total, $estado);

            if ($idProforma) {

                foreach ($idservicios as $i => $servicioId) {
                    if (empty($servicioId)) continue;

                    $idtipo  = isset($idtipos[$i]) ? intval($idtipos[$i]) : null;
                    $idarea  = isset($idareas[$i]) ? intval($idareas[$i]) : null;
                    $valor   = isset($precios[$i]) ? floatval($precios[$i]) : 0;

                    // 🔹 Validar campos requeridos antes de guardar
                    if ($idtipo && $idarea && $servicioId) {
                        $ok = $model->guardarDetalleProforma($idProforma, $servicioId, $idtipo, $idarea, $valor);
                        if (!$ok) {
                            error_log("Error al guardar detalle: Servicio=$servicioId, Tipo=$idtipo, Área=$idarea, Valor=$valor");
                        }
                    } else {
                        error_log("⚠️ Detalle omitido por datos incompletos: index $i");
                    }
                }

                header("Location: ../index.php?views=proformas/index&msg=ok");
                exit();
            } else {
                echo "❌ Error: no se pudo guardar la proforma.";
            }
        }
    }
    public function obtenerproformasPorId($id)
    {
        $model = new proformas();
        return $model->obtenerProformasPorId($id);
    }


    public function generar($id)
    {

        ob_clean();
        // --- Limpia cualquier salida previa para evitar el error de FPDF ---
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $model = new proformas();
        $proforma = $model->obtenerPorId($id);
        $cabecera = $proforma['cabecera'];
        $detalle = $proforma['detalle'];

        if (!$cabecera) {
            // Si la proforma no existe, informa al usuario SIN contaminar la salida
            while (ob_get_level() > 0) {
                ob_end_clean(); // Limpia antes de mostrar el error
            }
            header('Content-Type: text/plain; charset=utf-8');
            exit('Error: Proforma no encontrada con ID: ' . $id);
        }

        // --- Calcular totales desde el array $detalle ---
        $total = 0;
        foreach ($detalle as $fila) {
            if (isset($fila['valor'])) {
                $total += floatval($fila['valor']);
            }
        }

        $subtotal = $total / 1.18;
        $igv = $total - $subtotal;

        // --- Crear PDF ---
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);



        // Fecha actual formateada
        /*$formatter = new IntlDateFormatter('es_PE', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'America/Lima', IntlDateFormatter::GREGORIAN, "d 'de' MMMM 'de' y");
        $fecha = ucfirst($formatter->format(new DateTime()));
        $pdf->SetFont('Arial', 'BI', 10);
        $pdf->SetXY(30, 30); // Usamos SetXY para colocar la fecha
        $pdf->Cell(25, 10, $pdf->safe_text("Pimentel, $fecha"), 0, 1, 'C');*/
        // Reemplaza con una fecha simple y segura
        date_default_timezone_set('America/Lima');
        $fecha_simple = date('d/m/Y');
        $pdf->SetFont('Arial', 'BI', 10);
        $pdf->SetXY(30, 40);
        $pdf->Cell(25, 10, $pdf->safe_text("Pimentel, $fecha_simple"), 0, 1, 'C');

        $pdf->SetFont('Arial', 'BI', 9);
        $pdf->SetXY(100, 40);
        $pdf->Cell(25, 10, $pdf->safe_text("PROFORMA N°:"), 0, 1, 'C');
        $pdf->SetFont('Arial', 'BI', 9);
        $pdf->SetXY(100, 40);
        $pdf->Cell(75, 10, $pdf->safe_text($cabecera['codigo']), 0, 1, 'C');

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

        $pdf->Ln(8);
        $pdf->MultiCell(180, 5, mb_convert_encoding(
            'Tenemos el agrado de dirigirnos a usted con el fin de alcanzarle, de acuerdo a sus requerimientos, nuestra proforma de servicio de calibración de los siguientes items:',
            'ISO-8859-1',
            'UTF-8'
        ));
        $pdf->MultiCell(180, 5, $pdf->safe_text('Tenemos el agrado de dirigirnos a usted con el fin de alcanzarle, de acuerdo a sus requerimientos, nuestra proforma de servicio de calibración de los siguientes items:'));

        // --- Tabla encabezado ---
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(190, 231, 250);
        $pdf->Cell(20, 8, 'ITEM', 1, 0, 'C', true);
        $pdf->Cell(90, 8, 'DESCRIPCION', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'LUGAR', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'VALOR NETO S/.', 1, 1, 'C', true);


        // --- Detalle de servicios ---
        $pdf->SetFont('Arial', '', 8);
        $item = 1;
        foreach ($detalle as $fila) {
            $descripcion = isset($fila['descripcion']) ? $pdf->safe_text($fila['descripcion']) : '';
            $area = isset($fila['area']) ? $pdf->safe_text($fila['area']) : '';
            $valor = isset($fila['valor']) ? number_format($fila['valor'], 2) : '0.00';

            $pdf->Cell(20, 6, $item, 1, 0, 'C');
            $pdf->Cell(90, 6, $descripcion, 1, 0, 'L');
            $pdf->Cell(40, 6, $area, 1, 0, 'C');
            $pdf->Cell(40, 6, $valor, 1, 1, 'C');

            $item++;
        }

        // 🔹 Nuevo: toma la posición actual
        $y = $pdf->GetY();
        $pdf->Ln(2); // un poco de espacio

        // --- Totales dinámicos ---
        // Se posicionan correctamente después de la tabla, sin importar su altura.
        // --- Totales alineados a la derecha ---
        $pdf->SetFont('Arial', 'B', 8);

        // Calculamos posición de la caja de totales alineada con columnas LUGAR (40) y VALOR (40)
        // Márgenes definidos previamente: SetMargins(15, 15, 15)
        $anchoCeldaEtiqueta = 40; // coincide con ancho de columna LUGAR
        $anchoCeldaValor = 40;    // coincide con ancho de columna VALOR NETO
        // Usamos GetX() (posición actual) como margen izquierdo efectivo
        $margenIzquierdo = $pdf->GetX();
        $anchoColItem = 20;
        $anchoColDescripcion = 90;
        $xInicio = $margenIzquierdo + $anchoColItem + $anchoColDescripcion; // inicio exacto de columna LUGAR

        // Gravado
        $pdf->SetXY($xInicio, $y);
        $pdf->Cell($anchoCeldaEtiqueta, 8, 'Gravado', 1, 0, 'C');
        $pdf->Cell($anchoCeldaValor, 8, number_format($subtotal, 2), 1, 1, 'R');

        // IGV
        $pdf->SetX($xInicio);
        $pdf->Cell($anchoCeldaEtiqueta, 8, 'IGV 18%', 1, 0, 'C');
        $pdf->Cell($anchoCeldaValor, 8, number_format($igv, 2), 1, 1, 'R');

        // Valor Total (resaltado)
        $pdf->SetX($xInicio);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell($anchoCeldaEtiqueta, 8, 'VALOR TOTAL', 1, 0, 'C', true);
        $pdf->Cell($anchoCeldaValor, 8, number_format($total, 2), 1, 1, 'R', true);


        $pdf->Ln(2);
        $y_actual = $pdf->GetY();

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY(15, $y_actual - 15);
        $pdf->cell(85, 5, 'Costo en soles', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(15, $pdf->GetY());
        $pdf->MultiCell(85, 4, $pdf->safe_text('Los servicios se ejecutaran en dia de labores de 9:00 h a 18:00 h de lunes a viernes; sabdo de 9:00 h a 13:00 h.'), 1, 'J');
        $pdf->SetFont('Arial', 'BI', 8);
        $pdf->SetXY(15, $pdf->GetY() + 2);
        $pdf->MultiCell(185, 4, $pdf->safe_text('*El pago solo se debe de realizar por tranferencia o efectivo, cualquier otro medio de pago sera rechazado.'), 0, 'J');
        $pdf->MultiCell(185, 4, $pdf->safe_text('*El tiempo de vigencia de la presente proforma es de 7 días hábiles. De lo contrario la proforma ya no es válida y debe solicitar otra, de requerir el servicio.'), 0, 'J');
        $pdf->MultiCell(185, 4, $pdf->safe_text('*El tiempo de almacenamiento gratuito sera de 10 dias habiles . Luego tendra un costo adicional por dia de almacenamiento.'), 0, 'J');
        $pdf->MultiCell(185, 4, $pdf->safe_text('*El tiempo de entrega de los ítems a calibrar es de 3 días hábiles.'), 0, 'J');
        $pdf->Ln(3);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(185, 4, $pdf->safe_text('1.- Datos para la emisión del certificado.'), 0, 1, 'J');
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetX(20);
        $pdf->MultiCell(170, 4, $pdf->safe_text('Se utilizarán los mismos datos del solicitante de la presente proforma (razón social y dirección) para la emisión del respectivo certificado de calibración. En caso de algún cambio o corrección puede solicitarlo por cualquiera de nuestros medios de comunicación.'), 0, 'J');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(185, 4, $pdf->safe_text('2.- Forma de pago'), 0, 1, 'J');
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetX(20);

        $pdf->MultiCell(170, 4, $pdf->safe_text('El pago del 100% de esta proforma indica la aceptación de la misma. El pago debe realizarse antes de ingresar sus ítems a las instalaciones de S&H Ingenieros y puede realizarlo por los siguientes canales:'), 0, 'J');

        $pdf->Ln(2);
        $pdf->SetX(20);
        $pdf->Cell(185, 4, $pdf->safe_text('a) En las oficinas de S&H INGENIEROS.'), 0, 1, 'J');
        $pdf->SetX(20);
        $pdf->Cell(185, 4, $pdf->safe_text('b) Deposito a cuenta de la empresa: NUMEROS DE CUENTA S&H INGENIEROS:'), 0, 1, 'J');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(0, 51, 102); // azul oscuro
        $pdf->SetX(15);
        $pdf->Cell(60, 8, $pdf->safe_text('BANCO DE CREDITO:'), 1, 0, 'C');
        $x_bcp = $pdf->GetX();
        $y_bcp = $pdf->GetY();
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(125, 4, $pdf->safe_text("CUENTA CORRIENTE SOLES: 305-2311877-0-99\nCÓDIGO DE CUENTA INTERBANCARIA: 00230500231187709911"), 1, 'L');

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(245, 39, 39); // azul oscuro
        $pdf->SetX(15);
        $pdf->Cell(60, 8, $pdf->safe_text('SCOTIABANK'), 1, 0, 'C');
        $x_scotia = $pdf->GetX();
        $y_scotia = $pdf->GetY();
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(125, 4, $pdf->safe_text("CUENTA CORRIENTE SOLES: 000-2782910\nCUENTA DOLARES: 000-4632989"), 1, 'L');

        $pdf->SetTextColor(0, 51, 102);
        $pdf->SetX(15);
        $pdf->Cell(60, 8, $pdf->safe_text('BANCO CONTINENTAL'), 1, 0, 'C');
        $x_bbva = $pdf->GetX();
        $y_bbva = $pdf->GetY();
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(125, 4, $pdf->safe_text("CUENTA DE AHORROS DOLARES: 0011-0287-0200217197-09\nCUENTA CORRIENTE SOLES: 0011-0287-0100034559"), 1, 'L');

        $pdf->SetTextColor(0, 0, 0); // Reset text color

        $pdf->AddPage();
        // Añadimos una nueva página para el resto de la información

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY(15, 40);
        $pdf->Cell(185, 4,  $pdf->safe_text('3.- Horarios de atención, coordinación de recepción y devolución de ítems:'), 0, 1, 'J');
        $pdf->SetX(20);
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(170, 4,  $pdf->safe_text('Los equipos o instrumentos de medición serán recepcionados y entregados en las instalaciones de S&H Ingenieros (Predio los arenales Sub-Lote B-1C, distrito de Pimentel, provincia de Chiclayo).'), 0, 'J');
        $pdf->SetX(20);
        $pdf->Cell(185, 4,  $pdf->safe_text('- Lunes a viernes de 09:00 h a 18:00 h.'), 0, 1, 'J');
        $pdf->SetX(20);
        $pdf->Cell(185, 4,  $pdf->safe_text('- Sábados de 9:00 h a 13:00 h'), 0, 1, 'J');
        $pdf->SetX(20);
        $pdf->Cell(185, 4,  $pdf->safe_text('- Atención al cliente: 972458381'), 0, 1, 'J');
        $pdf->SetX(20);
        $pdf->Cell(15, 4, $pdf->safe_text('- Correo: '), 0, 0, 'J');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(100, 4, $pdf->safe_text('laboratorio@sh.com.pe'), 0, 1, 'L');
        $pdf->Ln(2);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(185, 4,  $pdf->safe_text('4.- Condiciones de servicio:'), 0, 1, 'J');
        $pdf->SetX(20);
        $pdf->Cell(185, 4,  $pdf->safe_text('a) Plazo estimado de ejecución:'), 0, 1, 'J');
        $pdf->SetX(25);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(170, 4, $pdf->safe_text('Dependerá de la programación del servicio, cantidad de equipos a calibrar y la carga de los inspectores de calibración.
El plazo estimado para higrometros y termometros ambientales es de 3 a 5 dias laborales, en caso solicite un servicio
adicional el tiempo puede ser mayor.'), 0, 'J');
        $pdf->SetX(20);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(185, 4,  $pdf->safe_text('b)  Condiciones para la calibración de higrometros y termometros ambientales:'), 0, 1, 'J');
        $pdf->SetX(25);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(170, 4, $pdf->safe_text('El servicio de calibración de manómetros cuenta con trazabilidad a INACAL y seguimos los estándares de calidad de la
NTP ISO 17025
El cliente debe enviar la orden de compra u orden de servicio y/o aceptación del servicio (confirmación telefónica, vía
correo electrónico u otros) y/o constancia de pago para proceder a ejecutar el servicio.
Cualquier diferencia se deberá resolver antes de empezar las actividades de calibración.
El ítem a calibrar deberá:
1. Encontrarse limpio, debe estar libre de polvo y este debe enviarse en buenas condiciones de operación y disponer
de todos los accesorios necesarios para su correcto uso.
2. Contar con baterías debidamente cargadas.
3. Los higrometros y/o termometros ambientales con sensor interno deben encontrarse limpios y con rendijas
despejadas, libres de cualquier agente (etiqueta, cinta adhesiva, esponja, etc).
4. Contar con pantalla digital que indique la lectura del sensor.
5. La sonda no debe tener ningún cable expuesto.
6. Estar debidamente identificado (Número de serie y/o código asignado por el cliente), caso contrario S&H Ingenieros
S.R.L. asignará código al instrumento.
En caso ítem a calibrar no cumpla con las condiciones indicadas anteriormente, será rechazado y podrá ser devuelto
inmediatamente, en coordinación con el cliente, dependiendo del caso.
S&H Ingenieros proporcionará acceso razonable a las áreas pertinentes para presenciar actividades del servicio,
cuando:
- El cliente solicite o necesite aclarar sus solicitudes.
- Preparar, embalar y enviar el ítem a calibrar.
- No se permite filmar ni tomar fotos al proceso de calibración.
adicional el tiempo puede ser mayor.'), 0, 'J');
        $pdf->SetX(20);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(185, 4,  $pdf->safe_text('c) Revelación de la información:'), 0, 1, 'J');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetX(20);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetX(25);
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(170, 4, $pdf->safe_text('- Los resultados en la inspección serán confidenciales. En caso de ser requeridos por ley ante un organismo
supervisor, al cliente se le notificara vía correo electrónico y/o mediante documento remitido a su domicilio,
adjuntando el oficio de la entidad que lo requiera (si fuera el caso).
- El certificado se enviará de manera física y en caso el cliente lo requiera de manera digital. El plazo estimado de
entrega es de 1 a 2 días laborables.
- El cliente tiene 30 días desde la fecha de emisión del certificado para solicitar sin costo la corrección o modificación
del certificado, después de ese tiempo el cliente asumirá los costos incurridos en la verificación de la validez de la calibración.'), 0, 'J');
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(185, 4,  $pdf->safe_text('5.- Suspensión del servicio:'), 0, 1, 'J');
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetX(20);
        $pdf->MultiCell(170, 4, $pdf->safe_text('En caso el cliente decida suspender el servicio durante o antes de su ejecución por cualquier desviación solicitada por el mismo,
S&H Ingenieros S.R.L. procederá a facturar el servicio de acuerdo con la proforma aceptada sin devolución del dinero. Si desea
una reprogramación el cliente deberá cubrir los gastos adicionales.
En caso el cliente solicite una reprogramación del servicio, tiene 4 días hábiles ANTES de la fecha programada del
servicio, de lo contrario el cliente deberá cubrir los gastos adicionales por reprogramación.'), 0, 'J');
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(185, 4,  $pdf->safe_text('6.- Quejas'), 0, 1, 'J');
        $pdf->Ln(2);
        $pdf->SetX(15);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetX(20);
        $pdf->MultiCell(170, 4, $pdf->safe_text('El cliente puede presentar sus quejas sobre los servicios ofrecidos por el laboratorio ingresando al siguiente link: https://sh.com.pe/sh/inicio/complaint/ y estas serán atendidas de acuerdo con los procedimientos establecidos por S&H Ingenieros SRL.'), 0, 'J');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->AddPage();
        $pdf->SetXY(10, 35);
        $pdf->Cell(185, 4,  $pdf->safe_text('7.- Declaración de confidencialidad:'), 0, 1, 'J');
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetX(20);
        $pdf->MultiCell(170, 4, $pdf->safe_text('Para asegurarnos de que se cumpla el Principio de confidencialidad en especial con la protección de la información que
recibimos, es esencial mantener la confidencialidad de los asuntos de la organización, dando cumplimiento a "ser prudentes en
el uso y protección de la información adquirida en el transcurso del trabajo y a no utilizar la información para lucro personal o de
alguna manera que fuera contraria en detrimento de los objetivos legítimos y éticos de la organización."
La información sobre el cliente, obtenida de fuentes distintas al cliente (por ejemplo, una persona que realiza una queja, de
autorizaciones reguladoras) se trata como información que recibimos, es esencial mantener la confidencialidad de los asuntos
de la organización.
He leído, entiendo y cumplo el lineamiento dado sobre confidencialidad, en relación con los asuntos de la organización.'), 0, 'J');

        $pdf->SetFillColor(255, 255, 255);
        // Colocar contenido de forma relativa, evitando solapes
        $espacioDespues = 5;
        $yActual = $pdf->GetY() + $espacioDespues;
        // Si queda poco espacio en la página, saltamos a una nueva
        if ($yActual > 260) {
            $pdf->AddPage();
            $yActual = 20;
        }
        $pdf->SetY($yActual);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(185, 4,  $pdf->safe_text('ADVERTENCIA:'), 0, 1, 'J');
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(185, 4, $pdf->safe_text('S&H Ingenieros se encarga de recoger y entregar el instrumento a calibrar desde y hacia la agencia de transportes determinada por el cliente. Sin embargo, no nos hacemos responsables por cualquier desperfecto que pueda presentar el medidor durante el transporte.'), 0, 'J');

        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(185, 4,  $pdf->safe_text('RECOMENDACIONES PARA EL ENVÍO DE SU INSTRUMENTO DE TEMPERATURA Y HUMEDAD:'), 0, 1, 'J');
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(185, 4, $pdf->safe_text('Recomendamos que embale adecuadamente su instrumento antes de la entrega, utilizando materiales de protección adecuados. Esto ayudará a prevenir posibles daños, como abolladuras en el cuerpo del instrumento, durante el transporte. S&H Ingenieros no se hace responsable de los daños causados por un embalaje inadecuado. Le sugerimos que asegure firmemente el instrumento en un estuche resistente o caja acolchada para garantizar su seguridad durante el transporte.'), 0, 'J');

        // --- Limpiar buffers antes de enviar el PDF ---
        // --- Limpiar buffers antes de enviar el PDF ---
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // --- Enviar el PDF al navegador ---
        $nombreArchivo = 'PROFORMA_' . $pdf->safe_text($cabecera['codigo']) . '.pdf';

        // Forzar descarga del archivo
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nombreArchivo . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        $pdf->Output('I', $nombreArchivo);
    }
    /*public function generar2()
    {
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        // Logo con borde
        $x = 10;
        $y = 11;
        $w = 25;
        $h = 18;
        $pdf->Rect($x - 1, $y - 1, $w + 2, $h + 2);
        $pdf->Image(__DIR__ . '/../assets/img/logo.png', $x, $y, $w, $h);

        // Cabecera del documento
        $pdf->SetXY(36, 10);
        $pdf->Cell(120, 10, $pdf->safe_text('FR/CB-OP-004'), 1, 1, 'C');
        $pdf->SetXY(36, 20);
        $pdf->Cell(120, 10, $pdf->safe_text('REPORTE DE CAMPO DE VERIFICACIÓN'), 1, 1, 'C');
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(156, 10); // Estas celdas no necesitan conversión
        $pdf->Cell(20, 10, 'Revisado: CC', 1, 1, 'C');
        $pdf->SetXY(156, 20);
        $pdf->Cell(20, 10, 'Aprobado: GG', 1, 1, 'C');
        $pdf->SetXY(176, 10);
        $pdf->Cell(20, 10, 'Ver: 12', 1, 1, 'C');
        $pdf->SetXY(176, 20);
        $pdf->Cell(20, 10, '31-08-2023', 1, 1, 'C');
        $pdf->Ln(2);
        $pdf->SetXY(9, 32);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->MultiCell(15, 3, $pdf->safe_text('N° Reporte de Campo'), 1, 'C', false);
        $pdf->SetXY(24, 32);
        $pdf->SetFont('Arial', '', 6);
        $pdf->MultiCell(25, 6, $pdf->safe_text('RC-00000001'), 1, 'C', false);
        $pdf->SetXY(49, 32);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->MultiCell(15, 3, $pdf->safe_text('Fecha de Inspección:'), 1, 'C', false);
        $pdf->SetXY(64, 32);
        $pdf->SetFont('Arial', '', 6);
        $pdf->MultiCell(25, 6, $pdf->safe_text('6-11-2025'), 1, 'C', false);
        $pdf->SetXY(89, 32);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->MultiCell(15, 3, $pdf->safe_text('Proforma N°:'), 1, 'C', false);
        $pdf->SetXY(104, 32);
        $pdf->SetFont('Arial', '', 6);
        $pdf->MultiCell(25, 6, $pdf->safe_text('PF-000001'), 1, 'C', false);
        $pdf->SetXY(129, 32);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->MultiCell(10, 3, $pdf->safe_text('Hora de Inicio'), 1, 'C', false);
        $pdf->SetXY(139, 32);
        $pdf->SetFont('Arial', '', 6);
        $pdf->MultiCell(10, 6, $pdf->safe_text('h'), 1, 'C', false);
        $pdf->SetXY(149, 32);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->MultiCell(10, 3, $pdf->safe_text('Hora de termino'), 1, 'C', false);
        $pdf->SetXY(159, 32);
        $pdf->SetFont('Arial', '', 6);
        $pdf->MultiCell(10, 6, $pdf->safe_text('h'), 1, 'C', false);
        $pdf->SetXY(169, 32);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->MultiCell(10, 6, $pdf->safe_text('SEDE'), 1, 'C', false);
        $pdf->SetXY(179, 32);
        $pdf->SetFont('Arial', '', 6);
        $pdf->MultiCell(17, 6, $pdf->safe_text('CHICLAYO'), 1, 'C', false);
        $pdf->Ln(2);
        $pdf->SetXY(9, 38);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(72, 4, $pdf->safe_text('1.1.	IDENTIFICACION: PLACA DEL VEHICULO TANQUE:'), 1, 1, 'L', false);
        $pdf->SetXY(81, 38);
        $pdf->SetFont('Arial', '', 6);
        $pdf->MultiCell(25, 4, $pdf->safe_text('5950AC'), 1, 'C', false);
        $pdf->SetXY(106, 38);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(60, 4, $pdf->safe_text('1.1.	PLACA REMOLCADOR'), 1, 1, 'L', false);
        $pdf->SetXY(166, 38);
        $pdf->SetFont('Arial', '', 6);
        $pdf->MultiCell(30, 4, $pdf->safe_text('5950AC'), 1, 'C', false);
        $pdf->SetXY(166, 38);
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(9, 50);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text('5'), 1, 1, 'C', false);
        $pdf->SetXY(14, 50);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(25, 4, $pdf->safe_text('N° Compartimientos'), 1, 1, 'C', false);
        $pdf->SetXY(39, 50);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(45, 4, $pdf->safe_text('5'), 1, 1, 'C', false);
        $pdf->SetXY(84, 42);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text('2'), 1, 1, 'C', false);
        $pdf->SetXY(9, 46);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text('3'), 1, 1, 'C', false);
        $pdf->SetXY(14, 46);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(25, 4, $pdf->safe_text('N° Ejes del tanque'), 1, 1, 'C', false);
        $pdf->SetXY(39, 46);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(45, 4, $pdf->safe_text('6'), 1, 1, 'C', false);
        $pdf->SetXY(89, 42);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(27, 4, $pdf->safe_text('serie del tanque '), 1, 1, 'C', false);
        $pdf->SetXY(116, 42);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(44, 4, $pdf->safe_text('TQ000001'), 1, 1, 'C', false);
        $pdf->SetXY(160, 42);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(36, 4, $pdf->safe_text('Camion remolque'), 1, 1, 'C', false);

        $pdf->SetXY(9, 42);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text('1'), 1, 1, 'C', false);
        $pdf->SetXY(14, 42);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(25, 4, $pdf->safe_text('Marca del tanque'), 1, 1, 'C', false);
        $pdf->SetXY(39, 42);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(45, 4, $pdf->safe_text('TOYOTA'), 1, 1, 'C', false);
        $pdf->SetXY(84, 46);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text('4'), 1, 1, 'C', false);
        $pdf->SetXY(89, 46);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(27, 4, $pdf->safe_text('Capacidad del tanque '), 1, 1, 'C', false);
        $pdf->SetXY(116, 46);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(44, 4, $pdf->safe_text('50 galones'), 1, 1, 'C', false);
        $pdf->SetXY(160, 46);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(36, 4, $pdf->safe_text('Semirremolque tanque'), 1, 1, 'C', false);
        $pdf->SetXY(84, 50);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text('6'), 1, 1, 'C', false);
        $pdf->SetXY(89, 50);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(27, 4, $pdf->safe_text('Año de fabricación'), 1, 1, 'C', false);
        $pdf->SetXY(116, 50);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(44, 4, $pdf->safe_text('2025'), 1, 1, 'C', false);
        $pdf->SetXY(160, 50);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(36, 4, $pdf->safe_text('Vagón tanque'), 1, 1, 'C', false);
        $pdf->Ln(2);
        $pdf->SetXY(8, 54);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(44, 4, $pdf->safe_text('2.	INSPECCION VISUAL:'), 0, 1, 'L', false);
        $pdf->SetXY(9, 58);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text('1'), 1, 1, 'C', false);
        $pdf->SetXY(14, 58);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(80, 4, $pdf->safe_text('Contómetro incorporado con acople mediante la válvula API'), 1, 1, 'C', false);
        $pdf->SetXY(94, 58);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text(''), 1, 1, 'C', false);
        $pdf->SetXY(9, 62);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text('3'), 1, 1, 'C', false);
        $pdf->SetXY(14, 62);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(80, 4, $pdf->safe_text('Contómetro incorporado que inicia en el tanque'), 1, 1, 'C', false);
        $pdf->SetXY(94, 62);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text(''), 1, 1, 'C', false);
        $pdf->SetXY(9, 66);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text('5'), 1, 1, 'C', false);
        $pdf->SetXY(14, 66);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(80, 4, $pdf->safe_text('Contómetro incorporado que inicia en la tubería de descarga'), 1, 1, 'C', false);
        $pdf->SetXY(94, 66);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text(''), 1, 1, 'C', false);
        $pdf->SetXY(9, 70);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text('7'), 1, 1, 'C', false);
        $pdf->SetXY(14, 70);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(80, 4, $pdf->safe_text('Contómetro incorporado que inicia en la tubería de descarga'), 1, 1, 'C', false);
        $pdf->SetXY(94, 70);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text(''), 1, 1, 'C', false);
        //------------------------------------------------------
        $pdf->SetXY(99, 58);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text('2'), 1, 1, 'C', false);
        $pdf->SetXY(99, 58);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(92, 4, $pdf->safe_text('Sombrero se desplaza por la rosca'), 1, 1, 'C', false);
        $pdf->SetXY(191, 58);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text(''), 1, 1, 'C', false);
        $pdf->SetXY(99, 62);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text('4'), 1, 1, 'C', false);
        $pdf->SetXY(99, 62);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(92, 4, $pdf->safe_text('Contómetro incorporado que inicia en el tanque'), 1, 1, 'C', false);
        $pdf->SetXY(191, 62);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text(''), 1, 1, 'C', false);
        $pdf->SetXY(99, 66);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text('6'), 1, 1, 'C', false);
        $pdf->SetXY(104, 66);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(87, 4, $pdf->safe_text('Contómetro incorporado que inicia en la tubería de descarga'), 1, 1, 'C', false);
        $pdf->SetXY(191, 66);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text(''), 1, 1, 'C', false);
        $pdf->SetXY(99, 70);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text('8'), 1, 1, 'C', false);
        $pdf->SetXY(104, 70);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(87, 4, $pdf->safe_text('Contómetro incorporado que inicia en la tubería de descarga'), 1, 1, 'C', false);
        $pdf->SetXY(191, 70);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(5, 4, $pdf->safe_text(''), 1, 1, 'C', false);
        $pdf->SetXY(9, 74);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell(187, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(10, 74);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(44, 4, $pdf->safe_text('Observaciones:'), 0, 1, 'L', false);
        // ---------------------------------------------
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(8, 78);
        $pdf->Cell(44, 6, $pdf->safe_text('3.TIPO DE VERIFICACIÓN:'), 0, 1, 'L', false);
        $pdf->SetXY(55, 80);
        $pdf->MultiCell(5, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(60, 79);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(44, 6, $pdf->safe_text('INICIAL'), 0, 1, 'L', false);
        $pdf->SetXY(94, 80);
        $pdf->MultiCell(5, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(100, 79);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(44, 6, $pdf->safe_text('PERIODICA'), 0, 1, 'L', false);
        $pdf->SetXY(140, 80);
        $pdf->MultiCell(5, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(145, 79);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(44, 6, $pdf->safe_text('EXTRAORDINARIA'), 0, 1, 'L', false);
        $pdf->SetXY(10, 85);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(187, 4, $pdf->safe_text('INDICAR MOTIVO SOLO SI FUERA CUBICACION INICIAL'), 1, 1, 'C', false);
        $pdf->SetXY(10, 89);
        $pdf->MultiCell(4, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(15, 89);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(10, 89);
        $pdf->MultiCell(4, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(14, 89);
        $pdf->SetFont('Arial', '', 6.5);
        $pdf->Cell(40, 4, $pdf->safe_text('1° verificación con la NMP 023:2021'), 1, 1, 'C', false);
        $pdf->SetFont('Arial', '', 6.5);
        $pdf->SetXY(14, 93);
        $pdf->Cell(40, 4, $pdf->safe_text('Transferencia de chasis a otro.'), 1, 1, 'C', false);
        $pdf->SetXY(10, 93);
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(4, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(10, 97);
        $pdf->MultiCell(4, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(14, 97);
        $pdf->SetFont('Arial', '', 6.5);
        $pdf->Cell(40, 4, $pdf->safe_text('Violación de precinto:'), 1, 1, 'C', false);
        //-------------------segunda columna ------------------
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(54, 89);
        $pdf->MultiCell(4, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(58, 89);
        $pdf->SetFont('Arial', '', 6.5);
        $pdf->Cell(71, 4, $pdf->safe_text('Modificación que altera características tec. Certificado inicial.'), 1, 1, 'L', false);
        $pdf->SetXY(54, 93);
        $pdf->MultiCell(4, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(58, 93);
        $pdf->Cell(71, 4, $pdf->safe_text('Deformación del tanque debido a pruebas hidrostáticas o el uso.'), 1, 1, 'C', false);
        $pdf->SetXY(54, 97);
        $pdf->MultiCell(4, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetFont('Arial', '', 6.5);
        $pdf->SetXY(58, 97);
        $pdf->Cell(71, 4, $pdf->safe_text('Modificación que altera las características técnicas del tanque.'), 1, 1, 'C', false);


        //--------------tercera columna-----------------
        $pdf->SetXY(129, 89);
        $pdf->MultiCell(4, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(133, 89);
        $pdf->SetFont('Arial', '', 6.5);
        $pdf->Cell(64, 4, $pdf->safe_text('Modificaciones que alteran las características metrológicas.'), 1, 1, 'L', false);
        $pdf->SetXY(129, 93);
        $pdf->MultiCell(4, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(133, 93);
        $pdf->Cell(64, 4, $pdf->safe_text('Adulteración o falsificación en el certificado.'), 1, 1, 'C', false);
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(129, 97);
        $pdf->MultiCell(4, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(133, 97);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(64, 4, $pdf->safe_text('A solicitud:'), 1, 1, 'C', false);

        //----------------------------------------------------//
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(9, 102);
        $pdf->Cell(44, 2, $pdf->safe_text('4.	CONDICIONES GENERALES:'), 0, 1, 'L', false);
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(9, 105);
        $pdf->MultiCell(8, 12, $pdf->safe_text('4.1'), 1, 0, 'C', false);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "B", 6, "70,111,166");
        $pdf->SetXY(17, 105);
        $pdf->WriteTag(30, 3, "<p><b>Tanque desgasificado</b>(8.3.3 NMP 023-2021)</p>              <a>Colocar el valor %LEL cuando sea NC</a>", 1, "C");
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(47, 105);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('        C1'), 1, 0, 'R', false);
        $pdf->SetXY(47, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(54.5, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(47, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(54.5, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(47, 114.1);
        $pdf->MultiCell(15, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        //------------2--------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(62, 105);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('        C2'), 1, 0, 'R', false);
        $pdf->SetXY(62, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(69.5, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(62, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(69.5, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(62, 114.1);
        $pdf->MultiCell(15, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        //---------------3-------------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(77, 105);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('        C3'), 1, 0, 'R', false);
        $pdf->SetXY(77, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(84.5, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(77, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(84.5, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(77, 114.1);
        $pdf->MultiCell(15, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        //-----------------4-------------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(92, 105);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('        C4'), 1, 0, 'R', false);
        $pdf->SetXY(92, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(99.5, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(92, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(99.5, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(92, 114.1);
        $pdf->MultiCell(15, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        //-----------------5-------------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(107, 105);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('        C5'), 1, 0, 'R', false);
        $pdf->SetXY(107, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(114.5, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(114.5, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(107, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(107, 114.1);
        $pdf->MultiCell(15, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        //--------------------6------------------------

        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(122, 105);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('        C6'), 1, 0, 'R', false);
        $pdf->SetXY(122, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(129.5, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(129.5, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(122, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(122, 114.1);
        $pdf->MultiCell(15, 3, $pdf->safe_text(''), 1, 0, 'R', false);

        //--------------7------------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(137, 105);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('        C7'), 1, 0, 'R', false);
        $pdf->SetXY(137, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(144.5, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(137, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(144.5, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(137, 114.1);
        $pdf->MultiCell(15, 3, $pdf->safe_text(''), 1, 0, 'R', false);

        //--------------8------------------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(152, 105);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('        C8'), 1, 0, 'R', false);
        $pdf->SetXY(152, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(159.5, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(152, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(159.5, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(152, 114.1);
        $pdf->MultiCell(15, 3, $pdf->safe_text(''), 1, 0, 'R', false);

        //--------------9----------------------------

        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(167, 105);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('        C9'), 1, 0, 'R', false);
        $pdf->SetXY(167, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(174.5, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(167, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(174.5, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(167, 114.1);
        $pdf->MultiCell(15, 3, $pdf->safe_text(''), 1, 0, 'R', false);

        //-----------------10---------------------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(182, 105);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('        C10'), 1, 0, 'R', false);
        $pdf->SetXY(182, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(189.5, 108);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(182, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(189.5, 111);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(182, 114.1);
        $pdf->MultiCell(15, 3, $pdf->safe_text(''), 1, 0, 'R', false);

        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(9, 117);
        $pdf->MultiCell(188, 6, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(8, 117);
        $pdf->Cell(44, 2, $pdf->safe_text('Observaciones:'), 0, 1, 'L', false);
        //----------fin-----------------------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(9, 124);
        $pdf->MultiCell(8, 9, $pdf->safe_text('4.2'), 1, 0, 'C', false);

        $pdf->SetStyle("p", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "", 6, "0,0,0");
        $pdf->SetXY(17, 124);
        $pdf->WriteTag(30, 4.4, $pdf->safe_text("<p>Revisión de mamparas</p><b>(8.5.3.2 NMP 023-2021)   NA</b>"), 1, "C");
        //----1-----
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(47, 124);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('    C1 – C2'), 1, 0, 'R', false);
        $pdf->SetXY(47, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(54.5, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(47, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(54.5, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        //------3-----
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(62, 124);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('    C2 - C3'), 1, 0, 'R', false);
        $pdf->SetXY(62, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(69.5, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(62, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(69.5, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        //------4-----------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(77, 124);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('    C3 - C4'), 1, 0, 'R', false);
        $pdf->SetXY(77, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(84.5, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(77, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(84.5, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        //------5----------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(92, 124);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('    C4 - C5'), 1, 0, 'R', false);
        $pdf->SetXY(92, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(99.5, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(92, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(99.5, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        //------6----------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(107, 124);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('    C5 - C6'), 1, 0, 'R', false);
        $pdf->SetXY(107, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(114.5, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(107, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(114.5, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        //----7--------------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(122, 124);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('    C6 - C7'), 1, 0, 'R', false);
        $pdf->SetXY(122, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(129.5, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(122, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(129.5, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        //----8--------------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(137, 124);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('    C7 - C8'), 1, 0, 'R', false);
        $pdf->SetXY(137, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(144.5, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(137, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(144.5, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        //----9--------------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(152, 124);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('    C8 - C9'), 1, 0, 'R', false);
        $pdf->SetXY(152, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(159.5, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(152, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(159.5, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        //----10--------------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(167, 124);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(15, 3, $pdf->safe_text('    C9 - C10'), 1, 0, 'R', false);
        $pdf->SetXY(167, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(174.5, 127);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(167, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);
        $pdf->SetXY(174.5, 130);
        $pdf->MultiCell(7.5, 3, $pdf->safe_text(''), 1, 0, 'R', false);


        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(9, 133);
        $pdf->MultiCell(173, 6, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(8, 133);
        $pdf->Cell(44, 2, $pdf->safe_text('Observaciones:'), 0, 1, 'L', false);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(9, 140);
        $pdf->MultiCell(65, 6, $pdf->safe_text('4.3.	INSPECCION INTERNA DEL COMPARTIMIENTO'), 1, 0, 'C', false);
        //-------------------c1----------------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(74, 140);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(10, 3, $pdf->safe_text('     C1'), 1, 0, 'R', false);
        $pdf->SetXY(74, 143);
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell(5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(79, 143);
        $pdf->MultiCell(5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);


        //-------------------c2----------------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(84, 140);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(10, 3, $pdf->safe_text('     C2'), 1, 0, 'R', false);
        $pdf->SetXY(89, 143);
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell(5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(84, 143);
        $pdf->MultiCell(5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        //-------------------c3------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(94, 140);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(10, 3, $pdf->safe_text('     C3'), 1, 0, 'R', false);
        $pdf->SetXY(94, 143);
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell(5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(99, 143);
        $pdf->MultiCell(5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        //-------------------c4----------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(104, 140);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(10, 3, $pdf->safe_text('     C4'), 1, 0, 'R', false);
        $pdf->SetXY(104, 143);
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell(5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(109, 143);
        $pdf->MultiCell(5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        //-------------------c5----------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(114, 140);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(10, 3, $pdf->safe_text('     C5'), 1, 0, 'R', false);
        $pdf->SetXY(114, 143);
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell(5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(119, 143);
        $pdf->MultiCell(5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        //-------------------c6----------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(124, 140);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(10, 3, $pdf->safe_text('     C6'), 1, 0, 'R', false);
        $pdf->SetXY(124, 143);
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell(5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(129, 143);
        $pdf->MultiCell(5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        //-------------------c7----------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(134, 140);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(10, 3, $pdf->safe_text('     C7'), 1, 0, 'R', false);
        $pdf->SetXY(134, 143);
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell(5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(139, 143);
        $pdf->MultiCell(5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        //-------------------c8----------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(144, 140);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(10, 3, $pdf->safe_text('     C8'), 1, 0, 'R', false);
        $pdf->SetXY(144, 143);
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell(5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(149, 143);
        $pdf->MultiCell(5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        //-------------------c9----------------
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(154, 140);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(10, 3, $pdf->safe_text('     C9'), 1, 0, 'R', false);
        $pdf->SetXY(154, 143);
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell(5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(159, 143);
        $pdf->MultiCell(5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        //-------------------c10----------------
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(164, 140);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(10, 3, $pdf->safe_text('     C10'), 1, 0, 'R', false);
        $pdf->SetXY(164, 143);
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell(5, 3, $pdf->safe_text('C'), 1, 0, 'R', false);
        $pdf->SetXY(169, 143);
        $pdf->MultiCell(5, 3, $pdf->safe_text('NC'), 1, 0, 'R', false);
        $pdf->SetXY(9, 146);
        $pdf->MultiCell(7, 15.3, $pdf->safe_text('4.3.1'), 1, 0, 'C', false);
        $pdf->SetXY(16, 146);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 6, "70,111,166");
        $pdf->WriteTag(58, 2.5, $pdf->safe_text('<p>Tanque fabricado antes de la vigencia de la NMP023:2021 Presenta elementos internos fijos (SI:  / NO ) que no se puedan desmontar que no retengan aire ni liquido: Serpentines     calentadores    Tub. Recup. - Vapor     Tub. Refuerzo tubular    Tub. No tubular      Otro  :                                                                                <a>(5.2.2.12 NMP 023:2021)</a></p>'), 1, 0, 'C', false);
        $pdf->SetXY(74, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(79, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(84, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(94, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(99, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(104, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(109, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(114, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(119, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(124, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(129, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(134, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(144, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(154, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(159, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(164, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(169, 146);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);


        //-----------------2col--------------------------------------------
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(9, 161);
        $pdf->MultiCell(7, 15.1, $pdf->safe_text('4.3.2'), 1, 0, 'C', false);
        $pdf->SetXY(16, 161);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(58, 2.1, $pdf->safe_text('<p>El tanque posee un serpentín de calentamiento que fue instalado antes de la vigencia de NMP 023:2021 (SI:  / NO ), el cliente presenta declaración jurada donde se indique fecha de instalación y que el elemento interno no es utilizado para productos blancos y se encuentra deshabilitado e inoperativo.                                                                                <a>(5.2.2.12 NMP 023:2021)</a></p>'), 1, 0, 'C', false);
        $pdf->SetXY(74, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(79, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(84, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(94, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(99, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(104, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(109, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(114, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(119, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(124, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(129, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(134, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(144, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(154, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(159, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(164, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(169, 161);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);

        //-----------------3col--------------------------------------------
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(9, 176);
        $pdf->MultiCell(7, 15.1, $pdf->safe_text('4.3.3'), 1, 0, 'C', false);
        $pdf->SetXY(16, 176);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(58, 2.5, $pdf->safe_text('<p>El certificado de verificación Inicial indica elementos internos fijos (SI:  / NO ), El cliente indica que (SI:  / NO ) se ha modificado elementos internos fijos declarados en el certificado de verificación inicial. Este resultado debe indicarse en el certificado posterior.                                                                                <a>8.5 NMP 023:2021)</a>No Aplica para Verificación Inicial.</p>'), 1, 0, 'C', false);
        $pdf->SetXY(74, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(79, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(84, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(94, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(99, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(104, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(109, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(114, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(119, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(124, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(129, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(134, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(144, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(154, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(159, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(164, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(169, 176);
        $pdf->MultiCell(5, 15, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(9, 191);
        $pdf->MultiCell(7, 6.2, $pdf->safe_text('4.3.4'), 1, 0, 'C', false);
        $pdf->SetXY(16, 191);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(58, 2, $pdf->safe_text('<p>No presenta dentro del compartimiento cualquier objeto otro cuerpo cuyo retiro o cambio podría modificar la capacidad del tanqu <a>(5.2.2.11 NMP 023:2021)</a></p>'), 1, 0, 'C', false);
        $pdf->SetXY(74, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(79, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(84, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(94, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(99, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(104, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(109, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(114, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(119, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(124, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(129, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(134, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(144, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(154, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(159, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(164, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(169, 191);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        //----------------6col--------------------------------------------
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(9, 197.2);
        $pdf->MultiCell(7, 10.2, $pdf->safe_text('4.3.5'), 1, 0, 'C', false);
        $pdf->SetXY(16, 197.2);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(58, 2, $pdf->safe_text('<p>Debe tener una forma tal que no se retenga aire durante el llenado ni líquido durante el vaciado, Ninguna estructura interna deberá dificultar el llenado o vaciado completo ni crear espacios ocultos o permita la formación de bolsas de aire en el compartimiento. <a>(5.2.2.6 NMP 023:2021)</a></p>'), 1, 0, 'C', false);
        $pdf->SetXY(74, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(79, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(84, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(94, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(99, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(104, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(109, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(114, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(119, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(124, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(129, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(134, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(144, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(154, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(159, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(164, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(169, 197.2);
        $pdf->MultiCell(5, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        //----------------------7col--------------------------------
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(9, 207.5);
        $pdf->MultiCell(7, 8.2, $pdf->safe_text('4.3.6'), 1, 0, 'C', false);
        $pdf->SetXY(16, 207.5);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(58, 2, $pdf->safe_text('<p>Los rompeolas deben tener no menos de 3 aberturas, una inferior, una superior y la tercera a lo largo de su plano horizontal con diámetro tal que permita la inspección del compartimiento. <a>(5.2.2.6 NMP 023:2021)</a></p>'), 1, 0, 'C', false);
        $pdf->SetXY(74, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(79, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(84, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(94, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(99, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(104, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(109, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(114, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(119, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(124, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(129, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(134, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(144, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(154, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(159, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(164, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(169, 207.5);
        $pdf->MultiCell(5, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        //-----------------------8col----------------------------------
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(9, 215.5);
        $pdf->MultiCell(7, 6.2, $pdf->safe_text('4.3.7'), 1, 0, 'C', false);
        $pdf->SetXY(16, 215.5);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(58, 2, $pdf->safe_text('<p>No se deben utilizar caños, molduras o tubos de ventilación y válvulas para cumplir con los requisitos antes mencionados <a>(5.2.2.7 NMP 023:2021)</a></p>'), 1, 0, 'C', false);
        $pdf->SetXY(74, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(79, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(84, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(94, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(99, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(104, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(109, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(114, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(119, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(124, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(129, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(134, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(144, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(154, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(159, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(164, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(169, 215.5);
        $pdf->MultiCell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        //-------------------------------9col---------------------------
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(9, 221.7);
        $pdf->MultiCell(7, 12.2, $pdf->safe_text('4.3.8'), 1, 0, 'C', false);
        $pdf->SetXY(16, 221.7);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(58, 2, $pdf->safe_text('<p>El indicador de nivel (flecha) este instalado dentro del domo.  Su eje vertical debería estar centrado en el compartimiento de forma longitudinal y transversal. Cualquier desviación en cada dirección (izquierda, derecha, adelante o atrás) no sobrepase del 10%  de la longitud del compartimiento o 15 cm  , el que sea menor <a>(5.4.1.2 NMP 023:2021)</a></p>'), 1, 0, 'C', false);
        $pdf->SetXY(74, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(79, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(84, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(94, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(99, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(104, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(109, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(114, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(119, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(124, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(129, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(134, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(144, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(154, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(159, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(164, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(169, 221.7);
        $pdf->MultiCell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);

        //-------------------------------9col---------------------------
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(9, 233.8);
        $pdf->MultiCell(7, 4.3, $pdf->safe_text('4.3.9'), 1, 0, 'C', false);
        $pdf->SetXY(16, 233.8);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(58, 2, $pdf->safe_text('<p>El dispositivo de indicación de nivel (flecha) cumple con el diseño<a>(5.4.1.2 NMP 023:2021)</a></p>'), 1, 0, 'C', false);
        $pdf->SetXY(74, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(79, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(84, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(94, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(99, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(104, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(109, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(114, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(119, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(124, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(129, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(134, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(144, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(154, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(159, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(164, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(169, 233.8);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);

        //-------------------------------11col---------------------------
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(9, 238);
        $pdf->MultiCell(7, 4.3, $pdf->safe_text('4.3.10'), 1, 0, 'C', false);
        $pdf->SetXY(16, 238);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(58, 2, $pdf->safe_text('<p>El dispositivo de indicación de nivel (flecha) debe garantizar una lectura segura, fácil e inequívoca<a>(5.4.1.1 NMP 023:2021)</a></p>'), 1, 0, 'C', false);
        $pdf->SetXY(74, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(79, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(84, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(94, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(99, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(104, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(109, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(114, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(119, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(124, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(129, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(134, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(144, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(154, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(159, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(164, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(169, 238);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        //-------------------------------12col---------------------------
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(9, 242.3);
        $pdf->MultiCell(7, 10.3, $pdf->safe_text('4.3.11'), 1, 0, 'C', false);
        $pdf->SetXY(16, 242.3);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(58, 2, $pdf->safe_text('<p>Revisa que la mesa de medición sea una plancha metálica plana, sin estrías, horizontal, no desmontable y que no tenga ninguna condición que pueda alterar las medidas de la cinta de sondaje (hundimiento, orificios, inclinación, etc.) <a>(5.4.2.2 NMP 023:2021)</a></p>'), 1, 0, 'C', false);
        $pdf->SetXY(74, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(79, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(84, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(94, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(99, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(104, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(109, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(114, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(119, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(124, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(129, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(134, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(144, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(154, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(159, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(164, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(169, 242.3);
        $pdf->MultiCell(5, 10.3, $pdf->safe_text(''), 1, 0, 'C', false);
        //-------------------------------12col---------------------------
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(9, 252.5);
        $pdf->MultiCell(7, 6.2, $pdf->safe_text('4.3.12'), 1, 0, 'C', false);
        $pdf->SetXY(16, 252.5);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(58, 2, $pdf->safe_text('<p>Las dimensiones de la mesa de medición de 150 mm x 150 mm debería ser suficiente para cumplir su propósito <a>(5.4.2.2 NMP 023:2021)</a></p>'), 1, 0, 'C', false);
        $pdf->SetXY(74, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(79, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(84, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(94, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(99, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(104, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(109, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(114, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(119, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(124, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(129, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(134, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(144, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(154, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(159, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(164, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(169, 252.5);
        $pdf->MultiCell(5, 6.3, $pdf->safe_text(''), 1, 0, 'C', false);
        //-------------------------------12col---------------------------
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(9, 258.7);
        $pdf->MultiCell(7, 4.2, $pdf->safe_text('4.3.13'), 1, 0, 'C', false);
        $pdf->SetXY(16, 258.7);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(58, 2, $pdf->safe_text('<p>El espesor de la mesa de medición debe ser entre 4 mm y 6 mm <a>(5.4.2.2 NMP 023:2021)</a></p>'), 1, 0, 'C', false);
        $pdf->SetXY(74, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(79, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(84, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(94, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(99, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(104, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(109, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(114, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(119, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(124, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(129, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(134, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(144, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(154, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(159, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(164, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(169, 258.7);
        $pdf->MultiCell(5, 4.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(9, 263);
        $pdf->MultiCell(165, 6, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(10, 262);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(44, 4, $pdf->safe_text('Observaciones:'), 0, 1, 'L', false);
        $pdf->SetXY(10, 266);
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(44, 4, $pdf->safe_text('LOS REQUISITOS DEL CAPITULO 5 NO APLICAN PARA VERIFICACION POSTERIOR.'), 0, 1, 'L', false);

        //-----------------------medicion de mesa ------------------
        $pdf->SetXY(9, 270);
        $pdf->MultiCell(26, 4, $pdf->safe_text('MEDICIÓN DE MESA '), 1, 0, 'C', false);
        $pdf->SetXY(35, 270);
        $pdf->Cell(13, 4, $pdf->safe_text('1'), 1, 0, 'C', false);
        $pdf->SetXY(48, 270);
        $pdf->Cell(13, 4, $pdf->safe_text('2'), 1, 0, 'C', false);
        $pdf->SetXY(61, 270);
        $pdf->Cell(13, 4, $pdf->safe_text('3'), 1, 0, 'C', false);
        $pdf->SetXY(74, 270);
        $pdf->Cell(13, 4, $pdf->safe_text('4'), 1, 0, 'C', false);
        $pdf->SetXY(87, 270);
        $pdf->Cell(13, 4, $pdf->safe_text('5'), 1, 0, 'C', false);
        $pdf->SetXY(100, 270);
        $pdf->Cell(13, 4, $pdf->safe_text('6'), 1, 0, 'C', false);
        $pdf->SetXY(113, 270);
        $pdf->Cell(13, 4, $pdf->safe_text('7'), 1, 0, 'C', false);
        $pdf->SetXY(126, 270);
        $pdf->Cell(13, 4, $pdf->safe_text('8'), 1, 0, 'C', false);
        $pdf->SetXY(139, 270);
        $pdf->Cell(13, 4, $pdf->safe_text('9'), 1, 0, 'C', false);
        $pdf->SetXY(152, 270);
        $pdf->Cell(13, 4, $pdf->safe_text('10'), 1, 0, 'C', false);
        //-----------------------2da fila ------------------
        $pdf->SetXY(9, 274);
        $pdf->MultiCell(26, 4, $pdf->safe_text('Longitud cm             NA '), 1, 0, 'C', false);
        $pdf->SetXY(35, 274);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(48, 274);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(61, 274);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(74, 274);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(87, 274);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(100, 274);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(113, 274);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(126, 274);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 274);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(152, 274);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        //-----------------------3ra fila ------------------
        $pdf->SetXY(9, 278);
        $pdf->MultiCell(26, 4, $pdf->safe_text('Espesor mm         NA'), 1, 0, 'C', false);
        $pdf->SetXY(35, 278);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(48, 278);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(61, 278);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(74, 278);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(87, 278);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(100, 278);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(113, 278);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(126, 278);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(139, 278);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(152, 278);
        $pdf->Cell(13, 4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->AddPage();
        $pdf->SetXY(9, 40);
        $pdf->Cell(62, 6, $pdf->safe_text('4.4	INSPECCION EXTERNA DEL COMPARTIMIENTO'), 1, 0, 'L', false);
        //------c1------------
        $pdf->SetXY(71, 40);
        $pdf->Cell(12, 3, $pdf->safe_text('C1'), 1, 0, 'C', false);
        $pdf->SetXY(71, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('C'), 1, 0, 'C', false);
        $pdf->SetXY(77, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('NC'), 1, 0, 'C', false);

        //---------c2--------------
        $pdf->SetXY(83, 40);
        $pdf->Cell(12, 3, $pdf->safe_text('C2'), 1, 0, 'C', false);
        $pdf->SetXY(83, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('C'), 1, 0, 'C', false);
        $pdf->SetXY(89, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('NC'), 1, 0, 'C', false);
        //--------c3-------------------
        $pdf->SetXY(95, 40);
        $pdf->Cell(12, 3, $pdf->safe_text('C3'), 1, 0, 'C', false);
        $pdf->SetXY(95, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('C'), 1, 0, 'C', false);
        $pdf->SetXY(101, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('NC'), 1, 0, 'C', false);
        //--------c4-------------------
        $pdf->SetXY(107, 40);
        $pdf->Cell(12, 3, $pdf->safe_text('C4'), 1, 0, 'C', false);
        $pdf->SetXY(107, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('C'), 1, 0, 'C', false);
        $pdf->SetXY(113, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('NC'), 1, 0, 'C', false);
        //--------c5-------------------
        $pdf->SetXY(119, 40);
        $pdf->Cell(12, 3, $pdf->safe_text('C5'), 1, 0, 'C', false);
        $pdf->SetXY(119, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('C'), 1, 0, 'C', false);
        $pdf->SetXY(125, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('NC'), 1, 0, 'C', false);
        //--------c6-------------------
        $pdf->SetXY(131, 40);
        $pdf->Cell(12, 3, $pdf->safe_text('C6'), 1, 0, 'C', false);
        $pdf->SetXY(131, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('C'), 1, 0, 'C', false);
        $pdf->SetXY(137, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('NC'), 1, 0, 'C', false);
        //--------c7-------------------
        $pdf->SetXY(143, 40);
        $pdf->Cell(12, 3, $pdf->safe_text('C7'), 1, 0, 'C', false);
        $pdf->SetXY(143, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('C'), 1, 0, 'C', false);
        $pdf->SetXY(149, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('NC'), 1, 0, 'C', false);
        //--------c8-------------------
        $pdf->SetXY(155, 40);
        $pdf->Cell(12, 3, $pdf->safe_text('C8'), 1, 0, 'C', false);
        $pdf->SetXY(155, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('C'), 1, 0, 'C', false);
        $pdf->SetXY(161, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('NC'), 1, 0, 'C', false);
        //--------c9-------------------
        $pdf->SetXY(167, 40);
        $pdf->Cell(12, 3, $pdf->safe_text('C9'), 1, 0, 'C', false);
        $pdf->SetXY(167, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('C'), 1, 0, 'C', false);
        $pdf->SetXY(173, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('NC'), 1, 0, 'C', false);
        //--------c10-------------------
        $pdf->SetXY(179, 40);
        $pdf->Cell(12, 3, $pdf->safe_text('C10'), 1, 0, 'C', false);
        $pdf->SetXY(179, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('C'), 1, 0, 'C', false);
        $pdf->SetXY(185, 43);
        $pdf->Cell(6, 3, $pdf->safe_text('NC'), 1, 0, 'C', false);
        //----------datos 1fila---------------------
        $pdf->SetXY(9, 46);
        $pdf->Cell(7, 10.2, $pdf->safe_text('4.4.1'), 1, 0, 'C', false);
        $pdf->SetXY(16, 46);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(55, 2, utf8_decode("<p>Debe asegurar la descarga completa y rápida por gravedad del líquido contenido en el tanque.El dispositivo de descarga debe estar conectado a la parte más baja del cuerpo del tanque. (8.3.3 NMP 023-2021)Colocar el valor %LEL cuando sea NC<a> (5.3.1.1 NMP 023:2021)</a></p>"), 1, "J");
        $pdf->SetXY(71, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(77, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(83, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(95, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(101, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(107, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(113, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(119, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(125, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(131, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(137, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(143, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(155, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(161, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(167, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(173, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(179, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(185, 46);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);

        //------------datos 2fila-----------
        $pdf->SetXY(9, 56.2);
        $pdf->Cell(7, 8.2, $pdf->safe_text('4.4.2'), 1, 0, 'C', false);
        $pdf->SetXY(16, 56.2);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(55, 2, utf8_decode("<p>El tanque es de construcción especial para aeropuertos (SI: ☐ / NO ☐).  Se permite la presencia de un dispositivo para recolectar el agua y las impurezas depositadas por el líquido contenido.<a>(5.3.1.2 NMP 023:2021)</a></p>"), 1, "J");
        $pdf->SetXY(9, 56.2);



        $pdf->SetXY(71, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(77, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(83, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(95, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(101, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(107, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(113, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(119, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(125, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(131, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(137, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(143, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(155, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(161, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(167, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(173, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(179, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(185, 56.2);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        //------------datos 3fila-----------
        $pdf->SetXY(9, 64.4);
        $pdf->Cell(7, 8.2, $pdf->safe_text('4.4.3'), 1, 0, 'C', false);
        $pdf->SetXY(16, 64.4);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(55, 2, utf8_decode("<p>Cada compartimiento debe tener una tubería de descarga independiente. Las tuberías deben identificarse claramente con el número correspondiente al compartimiento al que pertenecen <a>(5.3.1.4 NMP 023:2021)</a></p>"), 1, "J");
        $pdf->SetXY(9, 64.4);

        $pdf->SetXY(71, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(77, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(83, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(95, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(101, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(107, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(113, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(119, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(125, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(131, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(137, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(143, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(155, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(161, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(167, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(173, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(179, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(185, 64.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        //------------datos 4fila-----------
        $pdf->SetXY(9, 72.6);
        $pdf->Cell(7, 10.2, $pdf->safe_text('4.4.4'), 1, 0, 'C', false);
        $pdf->SetXY(16, 72.6);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(55, 2, utf8_decode("<p>Las válvulas de cierre deben ser fácilmente accesibles y colocarse en la parte trasera o en el lado apropiado del tanque. Las tuberías de descarga, válvulas y sus conexiones no deben presentar fugas.<a>                               (5.3.1.6 NMP 023:2021)</a></p>"), 1, "J");
        $pdf->SetXY(9, 72.6);

        $pdf->SetXY(71, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(77, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(83, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(95, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(101, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(107, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(113, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(119, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(125, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(131, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(137, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(143, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(155, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(161, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(167, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(173, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(179, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(185, 72.6);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        //------------datos 5fila-----------
        $pdf->SetXY(9, 82.8);
        $pdf->Cell(7, 8.2, $pdf->safe_text('4.4.5'), 1, 0, 'C', false);
        $pdf->SetXY(16, 82.8);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(55, 2, utf8_decode("<p>Cada compartimiento debe estar provisto de un dispositivo de cierre (manual o automático) separado en cada línea de descarga.<a>                                                              (5.3.1.7 NMP 023:2021)</a></p>"), 1, "J");
        $pdf->SetXY(9, 82.8);

        $pdf->SetXY(71, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(77, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(83, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(95, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(101, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(107, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(113, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(119, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(125, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(131, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(137, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(143, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(155, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(161, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(167, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(173, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(179, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(185, 82.7);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        //------------datos 6fila-----------
        $pdf->SetXY(9, 90.9);
        $pdf->Cell(7, 8.2, $pdf->safe_text('4.4.6'), 1, 0, 'C', false);
        $pdf->SetXY(16, 90.9);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(55, 2, utf8_decode("<p>Cerca de la parte más baja de cada línea, se pueden instalar detectores de líquidos o mirillas (SI:  / NO )., para verificar la vaciedad. <a>                                                              (5.3.1.8 NMP 023:2021)</a></p>"), 1, "J");
        $pdf->SetXY(9, 88.7);

        $pdf->SetXY(71, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(77, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(83, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(95, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(101, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(107, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(113, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(119, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(125, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(131, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(137, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(143, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(155, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(161, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(167, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(173, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(179, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(185, 90.9);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);


        //------------datos 7fila-----------
        $pdf->SetXY(9, 99.2);
        $pdf->Cell(7, 6.2, $pdf->safe_text('4.4.7'), 1, 0, 'C', false);
        $pdf->SetXY(16, 99.2);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(55, 2, utf8_decode("<p>La tubería no debe ser flexible y debe estar instalada rígidamente. (5.3.1.9 NMP 023:2021)<a>                                                              (5.3.1.9 NMP 023:2021)</a></p>"), 1, "J");
        $pdf->SetXY(9, 99.2);

        $pdf->SetXY(71, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(77, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(83, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(95, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(101, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(107, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(113, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(119, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(125, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(131, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(137, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(143, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(155, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(161, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(167, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(173, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(179, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(185, 99.2);
        $pdf->Cell(6, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);


        //------------datos 8fila-----------
        $pdf->SetXY(9, 105.4);
        $pdf->Cell(7, 8.2, $pdf->safe_text('4.4.8'), 1, 0, 'C', false);
        $pdf->SetXY(16, 105.4);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(55, 2, utf8_decode("<p>Las líneas y dispositivos de control cuya manipulación podría falsear el resultado de medición, deben ser protegidos contra manipulaciones imprudentes <a>                                                              (5.3.1.10 NMP 023:2021)</a></p>"), 1, "J");
        $pdf->SetXY(71, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(77, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(83, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(95, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(101, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(107, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(113, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(119, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(125, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(131, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(137, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(143, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(155, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(161, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(167, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(173, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(179, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(185, 105.4);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        //------------datos 9fila-----------
        $pdf->SetXY(9, 113.6);
        $pdf->Cell(7, 8.2, $pdf->safe_text('4.4.9'), 1, 0, 'C', false);
        $pdf->SetXY(16, 113.6);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(55, 2, iconv('UTF-8', 'ISO-8859-1', "<p>El tubo de descarga es lo más corto posible y tiene una pendiente suficiente hacia la válvula de cierre.Se recomienda una pendiente de por lo menos 2°. <a href='#'>                       (5.3.1.3 NMP 023)</a></p>"), 1, "J");
        $pdf->SetXY(71, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(77, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(83, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(95, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(101, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(107, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(113, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(119, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(125, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(131, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(137, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(143, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(155, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(161, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(167, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(173, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(179, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(185, 113.6);
        $pdf->Cell(6, 8.2, $pdf->safe_text(''), 1, 0, 'C', false);
        //------------datos 10fila-----------
        $pdf->SetXY(9, 121.9);
        $pdf->Cell(7, 10.2, $pdf->safe_text('4.4.10'), 1, 0, 'C', false);
        $pdf->SetXY(16, 121.9);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(55, 2, iconv('UTF-8', 'ISO-8859-1', "<p>Las válvulas de emergencia y las válvulas de descarga deben estar en buenas condiciones, Las tapas de las válvulas de descarga deben tener orificios para ser precintados y/o sellados.<a href='#'>                        (5.1.6.3 NMP 023:2021)</a></p>"), 1, "J");
        $pdf->SetXY(71, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(77, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(83, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(89, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(95, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(101, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(107, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(113, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(119, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(125, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(131, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(137, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(143, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(149, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(155, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(161, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(167, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(173, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(179, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(185, 121.9);
        $pdf->Cell(6, 10.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(9, 132);
        $pdf->Cell(182, 8, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(10, 132);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(44, 4, $pdf->safe_text('Observaciones:'), 0, 1, 'L', false);
        $pdf->SetXY(10, 136);
        $pdf->SetFont('Arial', 'B', 5);
        $pdf->Cell(44, 4, $pdf->safe_text('LOS REQUISITOS DEL CAPITULO 5 NO APLICAN PARA VERIFICACION POSTERIOR.'), 0, 1, 'L', false);
        $pdf->SetXY(10, 144);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 5);
        $pdf->Cell(85, 4, $pdf->safe_text('4.5  INSPECCION DEL TANQUE'), 1, 1, 'L', false);
        $pdf->SetXY(95, 144);
        $pdf->Cell(5, 4, $pdf->safe_text('C'), 1, 0, 'C', false);
        $pdf->SetXY(100, 144);
        $pdf->Cell(5, 4, $pdf->safe_text('NC'), 1, 0, 'C', false);
        $pdf->SetXY(105, 144);
        $pdf->SetFont('Arial', 'B', 5);
        $pdf->Cell(85, 4, $pdf->safe_text('  INSPECCION DEL TANQUE'), 1, 1, 'L', false);
        $pdf->SetXY(10, 148);
        $pdf->Cell(10, 6.2, $pdf->safe_text('4.5.1'), 1, 0, 'C', false);
        $pdf->SetXY(20, 148);;
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(75, 3, iconv('UTF-8', 'ISO-8859-1', "<p>Si el tanque está dividido en compartimientos, cada uno debe ser considerado como un tanque separado. <a href='#'>                                          (3.2.1 NMP 023-2021)</a></p>"), 1, "J");
        $pdf->SetXY(95, 148);
        $pdf->Cell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(100, 148);
        $pdf->Cell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(10, 154.3);
        $pdf->Cell(10, 5.9, $pdf->safe_text('4.5.2'), 1, 0, 'C', false);
        $pdf->SetXY(20, 154.2);;
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(75, 2.9, iconv('UTF-8', 'ISO-8859-1', "<p>Cada tanque debe estar compuesto de un cuerpo y dispositivos de descarga.<a href='#'>                                          (3.2.2 NMP 023-2021)</a></p>"), 1, "J");
        $pdf->SetXY(95, 154);
        $pdf->Cell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(100, 154);
        $pdf->Cell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(10, 160.2);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(10, 12, $pdf->safe_text('4.5.3'), 1, 0, 'C', false);
        $pdf->SetXY(20, 160.4);;
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(75, 2.9, iconv('UTF-8', 'ISO-8859-1', "<p>Los compartimientos deben ser identificados en orden numérico ascendente, a partir del compartimiento más próximo a la cabina del vehículo y sus respectivas capacidades nominales deben indicarse. <a href='#'>                                          (5.1.6.2 NMP 023:2021)</a></p>"), 1, "J");
        $pdf->SetXY(95, 160);
        $pdf->Cell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(100, 160);
        $pdf->Cell(5, 12.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(10, 172);
        $pdf->Cell(10, 6, $pdf->safe_text('4.5.4'), 1, 0, 'C', false);
        $pdf->SetXY(20, 172.1);;
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(75, 2.9, iconv('UTF-8', 'ISO-8859-1', "<p>La superficie del tanque no presenta abolladuras, perforaciones u otros que ocasionen fugas<a href='#'>                                          (5.1.6.3 NMP 023:2021)</a></p>"), 1, "J");
        $pdf->SetXY(95, 172.5);
        $pdf->Cell(5, 5.7, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(100, 172.5);
        $pdf->Cell(5, 5.7, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(10, 178);
        $pdf->Cell(10, 8.9, $pdf->safe_text('4.5.6'), 1, 0, 'C', false);
        $pdf->SetXY(20, 178);;
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(75, 2.9, iconv('UTF-8', 'ISO-8859-1', "<p>El material del tanque tiene un coeficiente de dilatación lineal inferior a 33 x 10-6 °C-1 o el coeficiente de dilatación cubica es menor que 99 x 10-6 °C-1<a href='#'>                                          (5.2.2.5 NMP 023)</a></p>"), 1, "J");

        $pdf->SetXY(105, 148);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(8, 6.2, $pdf->safe_text('4.5.7'), 1, 0, 'C', false);
        $pdf->SetXY(113, 148);;
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(67.5, 2, iconv('UTF-8', 'ISO-8859-1', "<p>El material del tanque tiene un coeficiente de dilatación lineal inferior a 33 x 10-6 °C-1 o el coeficiente de dilatación cubica es menor que 99 x 10-6 °C-1<a href='#'>                                          (5.2.2.5 NMP 023)</a></p>"), 1, "J");
        $pdf->SetXY(180.5, 148);
        $pdf->Cell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(185.5, 148);
        $pdf->Cell(4.5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(105, 154);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(8, 6.2, $pdf->safe_text('4.5.8'), 1, 0, 'C', false);
        $pdf->SetXY(113, 154);;
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(67.5, 3, iconv('UTF-8', 'ISO-8859-1', "<p>El domo debe estar montado en la parte superior del cuerpo, al cual debe estar soldado. <a href='#'>                                          (5.5.5 NMP 023)</a></p>"), 1, "J");
        $pdf->SetXY(180.5, 154);
        $pdf->Cell(5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(185.5, 154);
        $pdf->Cell(4.5, 6.2, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(105, 160);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(8, 12.4, $pdf->safe_text('4.5.9'), 1, 0, 'C', false);
        $pdf->SetXY(113, 160);;
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(67.5, 6.1, iconv('UTF-8', 'ISO-8859-1', "<p> de dilatación cubica es menor que 99 x 10-6 °C-1<a href='#'>                                          (5.2.2.5 NMP 023)</a></p>"), 1, "J");
        $pdf->SetXY(180.5, 160.1);
        $pdf->Cell(5, 12.4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(185.5, 160.1);
        $pdf->Cell(4.5, 12.4, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetXY(105, 172.3);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(8, 6, $pdf->safe_text('4.5.10'), 1, 0, 'C', false);
        $pdf->SetXY(113, 172.3);;
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("a", "Arial", "", 5, "70,111,166");
        $pdf->WriteTag(67.5, 2.8, iconv('UTF-8', 'ISO-8859-1', "<p>El domo puede tener forma cilíndrica o paralelepipédica, con paredes laterales verticales <a href='#'>                                          (5.2.2.5 NMP 023)</a></p>"), 1, "J");
        $pdf->SetXY(180.5, 172.3);
        $pdf->Cell(5, 6, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(185.5, 172.3);
        $pdf->Cell(4.5, 6, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(95, 178);
        $pdf->Cell(95.1, 9, $pdf->safe_text('Material:                                                    // Placa fab.       // Cert. fab.       // Dec. Jurada   '), 1, 0, 'C', false);
        $pdf->SetXY(10, 187);
        $pdf->Cell(180, 9, $pdf->safe_text('LOS REQUISITOS DEL CAPITULO 5 NO APLICAN PARA VERIFICACION POSTERIOR.'), 1, 0, 'C', false);
        $pdf->SetXY(10, 187);
        $pdf->Cell(44, 4, $pdf->safe_text('Observaciones:'), 0, 1, 'L', false);

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(9, 198);
        $pdf->Cell(44, 2, $pdf->safe_text('5.	DIMENSIONES Y PRESIONES DE LOS NEUMATICOS:'), 0, 1, 'L', false);
        $pdf->SetXY(10, 202);
        $pdf->Cell(35, 45, $pdf->safe_text(''), 1, 0, 'C', false);
        $x = 15;
        $y = 203;
        $w = 25;
        $h = 18;
        $pdf->Image(__DIR__ . '/../assets/img/dimensiones.png', $x, $y, $w, $h);

        
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(13, 225);
        $pdf->SetStyle("p", "Arial", "", 6, "0,0,0");
        $pdf->SetStyle("b", "Arial", "B", 6, "0,0,0");
        $pdf->SetStyle("c", "Arial", "B", 6, "70,111,166");
        $pdf->WriteTag(30, 3, "<p><b>Neumaticos (presiones y dimensiones recomendados por el fabricante)</b> <c>(5.1.6.1 NMP 023-2021)</c></p>", 0, "C");
        $pdf->SetXY(45, 202);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(17, 3, $pdf->safe_text('Medidas'), 1, 0, 'C', false);
        $pdf->SetXY(45, 205);
        $pdf->Cell(4, 3, $pdf->safe_text('A'), 1, 0, 'C', false);
        $pdf->SetXY(45, 208);
        $pdf->Cell(4, 3, $pdf->safe_text('B'), 1, 0, 'C', false);
        $pdf->SetXY(45, 211);
        $pdf->Cell(4, 3, $pdf->safe_text('C'), 1, 0, 'C', false);
        $pdf->SetXY(45, 214);
        $pdf->Cell(4, 3, $pdf->safe_text('D'), 1, 0, 'C', false);
        $pdf->SetXY(45, 217);
        $pdf->Cell(4, 3, $pdf->safe_text('E'), 1, 0, 'C', false);
        $pdf->SetXY(45, 220);
        $pdf->Cell(4, 3, $pdf->safe_text('F'), 1, 0, 'C', false);
        $pdf->SetXY(45, 223);
        $pdf->Cell(4, 3, $pdf->safe_text('G'), 1, 0, 'C', false);
        $pdf->SetXY(45, 226);
        $pdf->Cell(4, 3, $pdf->safe_text('H'), 1, 0, 'C', false);
        $pdf->SetXY(45, 229);
        $pdf->Cell(4, 3, $pdf->safe_text('I'), 1, 0, 'C', false);
        $pdf->SetXY(45, 232);
        $pdf->Cell(4, 3, $pdf->safe_text('J'), 1, 0, 'C', false);
        $pdf->SetXY(45, 235);
        $pdf->Cell(4, 3, $pdf->safe_text('K'), 1, 0, 'C', false);
        $pdf->SetXY(45, 238);
        $pdf->Cell(4, 3, $pdf->safe_text('L'), 1, 0, 'C', false);
        $pdf->SetXY(45, 241);
        $pdf->Cell(4, 3, $pdf->safe_text('M'), 1, 0, 'C', false);
        $pdf->SetXY(45, 244);
        $pdf->Cell(4, 3, $pdf->safe_text('N'), 1, 0, 'C', false);

        $pdf->SetXY(49, 205);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(13, 3, $pdf->safe_text('11R22.5'), 1, 0, 'C', false);
        $pdf->SetXY(49, 208);
        $pdf->Cell(13, 3, $pdf->safe_text('12R22.5'), 1, 0, 'C', false);
        $pdf->SetXY(49, 211);
        $pdf->Cell(13, 3, $pdf->safe_text('65R22.5'), 1, 0, 'C', false);
        $pdf->SetXY(49, 214);
        $pdf->Cell(13, 3, $pdf->safe_text('80R22.5'), 1, 0, 'C', false);
        $pdf->SetXY(49, 217);
        $pdf->Cell(13, 3, $pdf->safe_text('8.25R20'), 1, 0, 'C', false);
        $pdf->SetXY(49, 220);
        $pdf->Cell(13, 3, $pdf->safe_text('10.00R20'), 1, 0, 'C', false);
        $pdf->SetXY(49, 223);
        $pdf->Cell(13, 3, $pdf->safe_text('12.00R20'), 1, 0, 'C', false);
        $pdf->SetXY(49, 226);
        $pdf->Cell(13, 3, $pdf->safe_text('11R22.5'), 1, 0, 'C', false);
        $pdf->SetXY(49, 229);
        $pdf->Cell(13, 3, $pdf->safe_text('12R22.5'), 1, 0, 'C', false);
        $pdf->SetXY(49, 232);
        $pdf->Cell(13, 3, $pdf->safe_text('65R22.5'), 1, 0, 'C', false);
        $pdf->SetXY(49, 235);
        $pdf->Cell(13, 3, $pdf->safe_text('80R22.5'), 1, 0, 'C', false);
        $pdf->SetXY(49, 238);
        $pdf->Cell(13, 3, $pdf->safe_text('8.25R20'), 1, 0, 'C', false);
        $pdf->SetXY(49, 241);
        $pdf->Cell(13, 3, $pdf->safe_text('10.00R20'), 1, 0, 'C', false);
        $pdf->SetXY(49, 244);
        $pdf->Cell(13, 3, $pdf->safe_text('12.00R20'), 1, 0, 'C', false);
        $pdf->SetXY(62, 202);
        $pdf->Cell(88, 3, $pdf->safe_text('Datos del fabricante'), 1, 0, 'C', false);
        $pdf->SetXY(62, 205);
        $pdf->Cell(25, 6, $pdf->safe_text('Marca'), 1, 0, 'C', false);
        $pdf->SetXY(62, 211);
        $pdf->Cell(5, 3, $pdf->safe_text('1'), 1, 0, 'C', false);
        $pdf->SetXY(62, 214);
        $pdf->Cell(5, 3, $pdf->safe_text('2'), 1, 0, 'C', false);
        $pdf->SetXY(62, 217);
        $pdf->Cell(5, 3, $pdf->safe_text('3'), 1, 0, 'C', false);
        $pdf->SetXY(62, 220);
        $pdf->Cell(5, 3, $pdf->safe_text('4'), 1, 0, 'C', false);
        $pdf->SetXY(62, 223);
        $pdf->Cell(5, 3, $pdf->safe_text('5'), 1, 0, 'C', false);
        $pdf->SetXY(62, 226);
        $pdf->Cell(5, 3, $pdf->safe_text('6'), 1, 0, 'C', false);
        $pdf->SetXY(62, 229);
        $pdf->Cell(5, 3, $pdf->safe_text('7'), 1, 0, 'C', false);
        $pdf->SetXY(62, 232);
        $pdf->Cell(5, 3, $pdf->safe_text('8'), 1, 0, 'C', false);
        $pdf->SetXY(62, 235);
        $pdf->Cell(5, 3, $pdf->safe_text('9'), 1, 0, 'C', false);
        $pdf->SetXY(62, 238);
        $pdf->Cell(5, 3, $pdf->safe_text('10'), 1, 0, 'C', false);
        $pdf->SetXY(62, 241);
        $pdf->Cell(5, 3, $pdf->safe_text('11'), 1, 0, 'C', false);
        $pdf->SetXY(62, 244);
        $pdf->Cell(5, 3, $pdf->safe_text('12'), 1, 0, 'C', false);

        $pdf->SetXY(67, 211);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(67, 214);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(67, 217);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(67, 220);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(67, 223);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(67, 226);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(67, 229);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(67, 232);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(67, 235);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(67, 238);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(67, 241);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(67, 244);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(87, 205);
        $pdf->Cell(23, 6, $pdf->safe_text('Clasificación Modelo'), 1, 0, 'C', false);
        $pdf->SetXY(87, 211);
        $pdf->Cell(23, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(87, 214);
        $pdf->Cell(23, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(87, 217);
        $pdf->Cell(23, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(87, 220);
        $pdf->Cell(23, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(87, 223);
        $pdf->Cell(23, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(87, 226);
        $pdf->Cell(23, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(87, 229);
        $pdf->Cell(23, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(87, 232);
        $pdf->Cell(23, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(87, 235);
        $pdf->Cell(23, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(87, 238);
        $pdf->Cell(23, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(87, 241);
        $pdf->Cell(23, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(87, 244);
        $pdf->Cell(23, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(110, 205);
        $pdf->Cell(20, 6, $pdf->safe_text('Dimensión(cm)'), 1, 0, 'C', false);
        $pdf->SetXY(110, 211);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(110, 214);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(110, 217);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(110, 220);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(110, 223);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(110, 226);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(110, 229);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(110, 232);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(110, 235);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(110, 238);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(110, 241);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(110, 244);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(130, 205);
        $pdf->Cell(20, 6, $pdf->safe_text('Presión(psi)'), 1, 0, 'C', false);
        $pdf->SetXY(130, 211);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(130, 214);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(130, 217);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(130, 220);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(130, 223);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(130, 226);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(130, 229);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(130, 232);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(130, 235);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(130, 238);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(130, 241);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(130, 244);
        $pdf->Cell(20, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(150, 202);
        $pdf->Cell(24.2, 3, $pdf->safe_text('VACÍO'), 1, 0, 'C', false);
        $pdf->SetXY(150, 205);
        $pdf->SetFont('Arial', 'B', 5);
        $pdf->Cell(12, 6, $pdf->safe_text('Dimensión(cm)'), 1, 0, 'C', false);
        $pdf->SetXY(162.2, 205);
        $pdf->SetFont('Arial', 'B', 5);
        $pdf->Cell(12, 6, $pdf->safe_text('Presión(psi)'), 1, 0, 'C', false);
        $pdf->SetXY(150, 211);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(150, 214);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(150, 217);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(150, 220);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(150, 223);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(150, 226);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(150, 229);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(150, 232);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(150, 235);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(150, 238);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(150, 241);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(150, 244);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);

        $pdf->SetXY(162.2, 211);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(162.2, 214);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(162.2, 217);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(162.2, 220);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(162.2, 223);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(162.2, 226);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(162.2, 229);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(162.2, 232);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(162.2, 235);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(162.2, 238);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(162.2, 241);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(162.2, 244);
        $pdf->Cell(12, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(174, 202);
        $pdf->Cell(15, 3, $pdf->safe_text('LLENO'), 1, 0, 'C', false);
        $pdf->SetXY(174, 205);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 5);
        $pdf->Cell(15, 6, $pdf->safe_text('Presión(psi)'), 1, 0, 'C', false);
        $pdf->SetXY(174, 211);
        $pdf->Cell(15, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(174, 214);
        $pdf->Cell(15, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(174, 217);
        $pdf->Cell(15, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(174, 220);
        $pdf->Cell(15, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(174, 223);
        $pdf->Cell(15, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(174, 226);
        $pdf->Cell(15, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(174, 229);
        $pdf->Cell(15, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(174, 232);
        $pdf->Cell(15, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(174, 235);
        $pdf->Cell(15, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(174, 238);
        $pdf->Cell(15, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(174, 241);
        $pdf->Cell(15, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(174, 244);
        $pdf->Cell(15, 3, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(10, 247);
        $pdf->Cell(179, 7, $pdf->safe_text(''), 1, 0, 'C', false);
        $pdf->SetXY(15, 245);
        $pdf->Cell(5, 7, $pdf->safe_text('Observaciones:'), 0, 0, 'C', false);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(9, 258);
        $pdf->Cell(44, 2, $pdf->safe_text('6.	MEDIDAS DEL TANQUE Y DE LAS EXTREMIDADES'), 0, 1, 'L', false);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(119, 258);
        $pdf->Cell(44, 2, $pdf->safe_text('Cuenta con Base Tipo S                  SI               NO          '), 0, 1, 'L', false);
        ob_end_clean(); // limpia el buffer de salida
        $pdf->Output('I', 'Reporte de campo.pdf');
    }*/
}

// --- Dispatcher mínimo cuando se accede directamente a este archivo ---
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $action = $_GET['action'] ?? '';
    if ($action === 'generar') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $controller = new proformasController();
            $controller->generar($id);
            exit;
        }
        header('Content-Type: text/plain; charset=utf-8');
        http_response_code(400);
        echo 'Error: ID inválido.';
        exit;
    }
    if ($action === 'guardar') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new proformasController();
            $controller->guardar(); // guardar no recibe ID; toma datos de $_POST
            exit;
        }
        header('Content-Type: text/plain; charset=utf-8');
        http_response_code(405);
        echo 'Método no permitido. Use POST para guardar.';
        exit;
    }
    if ($_POST['action'] == 'obtenerTotal') {
        require_once __DIR__ . '/../models/proformas.php';
        $model = new proformas();
        $total = $model->obtenerTotalProforma($_POST['id']);
        echo json_encode($total);
        exit;
    }
}
