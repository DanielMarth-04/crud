<?php
require_once __DIR__ . "/../../controllers/personalController.php";
require_once __DIR__ . '/../../controllers/tipoController.php';
require_once __DIR__ . '/../../controllers/grecepcionController.php';
$trabajadores = new personalController();
$controller = new tipoController();
$grecepcion = new grecepcionController();
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$personal = $trabajadores->obtenerPersonalId($id);
$guias = $grecepcion->obtenerguias($id);
// Validar que venga el ID por GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ERROR: No se recibió ID de guía.");
}

$id = $_GET['id'];

// Obtener los datos
$guia = $grecepcion->obtenerguias($id);

// Si no se encontró la guía
if (!$guia) {
    die("ERROR: No existe la guía con ID: $id");
}
$tipos = $controller->listar(); // array de clientes

?>

<div class="content-wrapper">

    <!-- ENCABEZADO -->
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="mb-0 text-primary fw-bold">
                <i class="fas fa-clipboard-list me-2"></i> Registrar Orden de Trabajo
            </h1>
        </div>
        <hr class="mt-3 mb-4">
    </section>

    <section class="content">
        <div class="container-fluid">

            <!-- CARD GENERAL -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice me-2"></i> Datos Generales
                    </h5>
                </div>

                <div class="card-body">

                    <form id="form-guia-recepcion" method="POST"
                        action="<?php echo APP_URL; ?>controllers/otrabajoController.php?action=guardar">

                        <!-- FILA 1 -->
                        <div class="row mb-4">
                            <!-- Nº PROFORMA -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Nº Proforma <span class="text-danger">*</span></label>
                                <input type="hidden" name="idproforma" value="<?php echo $guias['cabecera']['id']; ?>">
                                <input type="text" class="form-control" value="<?php echo $guia['cabecera']['codpro']; ?>" readonly>
                            </div>

                            <!-- CLIENTE -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Cliente <span class="text-danger">*</span></label>
                                <input type="text" class="form-control"
                                    value="<?php echo $guia['cabecera']['nombres']; ?>" readonly>
                                <input type="hidden" name="idcliente">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">fecha <span class="text-danger">*</span></label>
                                <input type="date" class="form-control"
                                    value="">
                            </div>

                            <!-- PERSONAL -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Personal Responsable <span class="text-danger">*</span></label>
                                <select name="idtrabajador" id="idtrabajador" class="form-control" required>
                                    <option value="">Seleccione al personal</option>
                                    <?php foreach ($personal as $ps): ?>
                                        <option value="<?php echo $ps['id']; ?>">
                                            <?php echo $ps['nombres'] . ' || ' . $ps['cargo']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- DESCRIPCIÓN -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Descripción</label>
                                <select class="form-control select2" name="descripcion">
                                    <option value="">Seleccione descripción…</option>
                                    <option>SERVICIO DE CALIBRACIÓN ACREDITADO POR INACAL - DA</option>
                                    <option>SERVICIO DE CALIBRACIÓN CONFORME A NTP ISO/IEC 17025</option>
                                    <option>SERVICIO DE CALIBRACIÓN IN SITU ACREDITADO POR INACAL - DA</option>
                                    <option>INTERLABORATORIO</option>
                                    <option>INTRALABORATORIO</option>
                                    <option>SERVICIO DE PRUEBA</option>
                                </select>
                            </div>
                        </div>

                        <!-- FILA 2 → Segunda lista de descripciones -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Método</label>
                                <select class="form-control select2" name="metodo">
                                    <option value="">Seleccione método…</option>
                                    <option>MÉTODO DE COMPARACIÓN DIRECTA, SEGÚN EL PC-026 "PROCEDIMIENTO PARA LA CALIBRACIÓN DE HIGRÓMETROS Y TERMÓMETROS AMBIENTALES" (1era ed., 2019)</option>
                                    <option>MÉTODO DE COMPARACIÓN DIRECTA, SEGÚN EL PC-017 "PROCEDIMIENTO PARA LA CALIBRACIÓN DE TERMÓMETROS DIGITALES" (2da ed., 2012)</option>
                                    <option>MÉTODO DE COMPARACIÓN DIRECTA, SEGÚN EL PROCEDIMIENTO PARA LA CALIBRACIÓN DE TERMÓMETROS CON INDICACIÓN ANALÓGICA (BASADO EN LA NORMA "THERMOMETER, DIRECT READING AND REMOTE READING" ASME B40.200 REV. 2013)</option>
                                </select>
                            </div>
                        </div>

                        <!-- SERVICIOS -->
                        <div class="card border">
                            <div class="card-header bg-light fw-bold">
                                <i class="fas fa-tools me-2"></i> Servicios
                            </div>
                            <div class="card-body">

                                <table class="table table-bordered table-striped text-center">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th>Descripción</th>
                                            <th>Cod. Ingreso</th>
                                            <th>Estado</th>
                                            <th>Fecha Ingreso</th>
                                            <th>Tipo</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>

                                    <tbody id="tabla-servicios">

                                        <?php foreach ($guia['detalle'] as $det): ?>
                                            <tr class="servicio-row">
                                                <td><input type="text" name="servicio[]" value="<?php echo $det['descripcion']; ?>" class="form-control" required></td>
                                                <td><input type="text" name="codigo_det[]" value="<?php echo $det['codingr']; ?>" class="form-control"></td>
                                                <td><input type="text" name="estado_det[]" value="<?php echo $det['estado']; ?>" class="form-control"></td>
                                                <td><input type="text" name="fecha_ingreso[]" value="<?php echo $det['feching']; ?>" class="form-control"></td>

                                                <td>
                                                    <select name="idtipo[]" class="form-control">
                                                        <?php foreach ($tipos as $tipo): ?>
                                                            <option value="<?php echo $tipo['id']; ?>"><?php echo $tipo['tipos']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>

                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-servicio">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>

                                    </tbody>
                                </table>

                            </div>
                        </div>

                    </form>
                </div>

                <!-- FOOTER -->
                <div class="card-footer text-end bg-light">
                    <button type="submit" form="form-guia-recepcion" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar
                    </button>

                    <a href="<?php echo APP_URL; ?>?views=proformas/index"
                        class="btn btn-danger ms-2">
                        <i class="fas fa-times-circle"></i> Cancelar
                    </a>
                </div>

            </div>
        </div>
    </section>

</div>

<?php
$opcionesTipos = "";
foreach ($tipos as $tipo) {
    $opcionesTipos .= '<option value="' . $tipo['id'] . '">' . $tipo['tipos'] . '</option>';
}
?>
<script>
    document.getElementById('add-servicio').addEventListener('click', function() {
        const firstRow = document.getElementById('servicios-first-row');
        const parentTable = firstRow.parentElement;

        const newRow = document.createElement('tr');
        newRow.className = 'servicio-row';
        newRow.innerHTML = `
    <td style="padding: 5px; border: 1px solid #dee2e6;">
        <input type="text" name="servicio[]" style="width: 100%; padding: 5px; border: 1px solid #ccc;" placeholder="Descripción del servicio" required>
    </td>
    <td style="padding: 5px; border: 1px solid #dee2e6;">
        <input type="text" name="codigo_det[]" style="width: 100%; padding: 5px; border: 1px solid #ccc;" placeholder="Cod. ingreso">
    </td>
    <td style="padding: 5px; border: 1px solid #dee2e6;">
        <input type="text" name="estado_det[]" style="width: 100%; padding: 5px; border: 1px solid #ccc;" placeholder="Estado">
    </td>
    <td style="padding: 5px; border: 1px solid #dee2e6;">
        <input type="date" name="fecha_ingreso[]" style="width: 100%; padding: 5px; border: 1px solid #ccc;">
    </td>
    <td style="padding: 5px; border: 1px solid #dee2e6;">
        <select name="idtipo[]" style="width: 100%; padding: 5px; border: 1px solid #ccc;">
            <?php echo $opcionesTipos; ?>
        </select>
    </td>
    <td style="padding: 5px; border: 1px solid #dee2e6; text-align: center;">
        <button type="button" class="remove-servicio" style="padding: 5px 10px; background-color: #dc3545; color: white; border: none; cursor: pointer;" title="Eliminar fila">
            <i class="fas fa-trash-alt"></i>
        </button>
    </td>
`;

        // Encontrar la última fila de servicio antes de la fila de botones
        const allRows = parentTable.querySelectorAll('.servicio-row');
        const lastServicioRow = allRows[allRows.length - 1];
        const nextRow = lastServicioRow.nextElementSibling;

        if (nextRow) {
            parentTable.insertBefore(newRow, nextRow);
        } else {
            parentTable.appendChild(newRow);
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-servicio')) {
            const row = e.target.closest('.servicio-row');
            // No permitir eliminar la primera fila si es la única
            const allRows = document.querySelectorAll('.servicio-row');
            if (allRows.length > 1 || row.id !== 'servicios-first-row') {
                row.remove();
            }
        }
    });
</script>