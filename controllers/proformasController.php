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

    function Footer()
    {
        // Configuración de fuente
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(0, 0, 0);

        // Posición: 20 mm desde el fondo
        $this->SetY(-25);

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
        $this->Line(10, $this->GetY() + 8, 200, $this->GetY() + 8);

        // --- Texto informativo usando métodos estándar de FPDF ---
        $this->SetY(-18);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(0, 5, $this->safe_text('Atención al cliente: 972458381 | Email: laboratorio@sh.com.pe | web: https://www.sh.com.pe'), 0, 1, 'C');

        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor(0, 0, 0);
        // Usamos GetX para centrar el texto de la dirección
        $this->SetX((210 - $this->GetStringWidth('S&H INGENIEROS: predio los arenales sub lote B-1C, Pimentel-Lambayeque-Peru.')) / 2);
        $this->Cell(0, 5, $this->safe_text('S&H INGENIEROS: predio los arenales sub lote B-1C, Pimentel-Lambayeque-Peru.'), 0, 0, 'L');
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

        // Logo con borde
        $x = 10;
        $y = 11;
        $w = 25;
        $h = 18;
        $pdf->Rect($x - 1, $y - 1, $w + 2, $h + 2);
        $pdf->Image(__DIR__ . '/../assets/img/logo.png', $x, $y, $w, $h);

        // Cabecera del documento
        $pdf->SetXY(36, 10);
        $pdf->Cell(120, 10, $pdf->safe_text('FR/LC-OP-002'), 1, 1, 'C');
        $pdf->SetXY(36, 20);
        $pdf->Cell(120, 10, $pdf->safe_text('PROFORMA DE SERVICIO DE CALIBRACION'), 1, 1, 'C');
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(156, 10); // Estas celdas no necesitan conversión
        $pdf->Cell(20, 10, 'Revisado: CC', 1, 1, 'C');
        $pdf->SetXY(156, 20);
        $pdf->Cell(20, 10, 'Aprobado: GG', 1, 1, 'C');
        $pdf->SetXY(176, 10);
        $pdf->Cell(20, 10, 'Ver: 05', 1, 1, 'C');
        $pdf->SetXY(176, 20);
        $pdf->Cell(20, 10, '2022-10-06', 1, 1, 'C');

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
        $pdf->SetXY(30, 30);
        $pdf->Cell(25, 10, $pdf->safe_text("Pimentel, $fecha_simple"), 0, 1, 'C');

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

        $pdf->AddPage(); // Añadimos una nueva página para el resto de la información

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY(15, 20);
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
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 8);
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
