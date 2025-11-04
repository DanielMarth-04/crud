<?php
require_once __DIR__ . "/../../controllers/proformasController.php";
require_once __DIR__ . "/../../controllers/personalController.php";
require_once __DIR__ . "/../../controllers/clienteController.php";
require_once __DIR__ . '/../../controllers/tipoController.php';
$controller = new proformasController();
$trabajadores = new personalController();
$clienteController = new clienteController();
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$proformas = $controller->obtenerproformasPorId($id);
$personal = $trabajadores->obtenerPersonalId($id);
$clientes = $clienteController->listarClienteprof($id);
$controller = new tipoController();
$tipos = $controller->listar(); // array de clientes
?>
<div class="content-wrapper">

    <!-- ENCABEZADO -->
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="mb-0 text-primary fw-bold">
                <i class="fas fa-clipboard-list me-2"></i> Registrar Guía de Recepción
            </h1>
        </div>
        <hr class="mt-3 mb-4">
    </section>

    <!-- FORMULARIO -->
    <section class="content">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td align="center">
                    <table width="90%" cellpadding="0" cellspacing="0" border="1" style="border-collapse: collapse; background-color: #ffffff;">
                        <!-- HEADER -->
                        <tr>
                            <td bgcolor="#007bff" style="padding: 15px; color: #ffffff;">
                                <strong><i class="fas fa-file-invoice"></i> Datos Generales</strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 20px;">
                                <form id="form-guia-recepcion" method="POST" action="<?php echo APP_URL; ?>controllers/guiasRecepcionController.php?action=guardar">
                                    <table width="100%" cellpadding="10" cellspacing="0" border="0">
                                        <!-- Nº PROFORMA -->
                                        <tr>
                                            <td width="20%" align="right" valign="top" style="padding-right: 15px;">
                                                <label for="idproforma"><strong>Nº Proforma <span style="color: red;">*</span></strong></label>
                                            </td>
                                            <td width="30%" valign="top">
                                                <select name="idproforma" id="idproforma" style="width: 100%; padding: 8px; border: 1px solid #ccc;" required>
                                                    <option value="">Seleccione Nº Proforma</option>
                                                    <?php foreach ($proformas as $p): ?>
                                                        <option value="<?php echo htmlspecialchars($p['id']); ?>">
                                                            <?php echo htmlspecialchars($p['codigo']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <!-- CLIENTE -->
                                            <td width="20%" align="right" valign="top" style="padding-right: 15px;">
                                                <label for="cliente"><strong>Cliente <span style="color: red;">*</span></strong></label>
                                            </td>
                                            <td width="30%" valign="top">
                                                <select name="cliente" id="cliente" style="width: 100%; padding: 8px; border: 1px solid #ccc;" required>
                                                    <option value="">Seleccione un cliente</option>
                                                    <?php foreach ($clientes as $c): ?>
                                                        <option value="<?php echo htmlspecialchars($c['id']); ?>">
                                                            <?php echo htmlspecialchars($c['nombres']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        </tr>

                                        <!-- PERSONAL RESPONSABLE -->
                                        <tr>
                                            <td width="20%" align="right" valign="top" style="padding-right: 15px;">
                                                <label for="idpersonal"><strong>Personal responsable <span style="color: red;">*</span></strong></label>
                                            </td>
                                            <td width="30%" valign="top">
                                                <select name="idpersonal" id="idpersonal" style="width: 100%; padding: 8px; border: 1px solid #ccc;" required>
                                                    <option value="" selected disabled>Seleccione al personal</option>
                                                    <?php foreach ($personal as $ps): ?>
                                                        <option value="<?php echo htmlspecialchars($ps['id']); ?>">
                                                            <?php echo htmlspecialchars($ps['nombres'] . '  || ' . $ps['cargo']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <br><small style="color: #666;">Seleccione quién recibe el equipo</small>
                                            </td>
                                            <!-- COSTO TOTAL -->
                                            <td width="20%" align="right" valign="top" style="padding-right: 15px;">
                                                <label for="costo_total"><strong>Costo Total</strong></label>
                                            </td>
                                            <td width="30%" valign="top">
                                                <input type="text" name="costo_total" id="costo_total" style="width: 100%; padding: 8px; border: 1px solid #ccc; text-align: right;" placeholder="0.00">
                                            </td>
                                        </tr>

                                        <!-- SERVICIOS -->
                                        <tr>
                                            <td colspan="4" style="padding-top: 20px;">
                                                <table width="100%" cellpadding="0" cellspacing="0" border="1" style="border-collapse: collapse; background-color: #f8f9fa;">
                                                    <tr>
                                                        <td style="padding: 15px;">
                                                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                                <tr>
                                                                    <td colspan="6" style="padding-bottom: 10px;">
                                                                        <strong>Servicios <span style="color: red;">*</span></strong>
                                                                    </td>
                                                                </tr>
                                                                <!-- Encabezado de tabla de servicios -->
                                                                <tr bgcolor="#e9ecef" style="font-weight: bold; text-align: center;">
                                                                    <td width="25%" style="padding: 8px; border: 1px solid #dee2e6;">Descripción</td>
                                                                    <td width="15%" style="padding: 8px; border: 1px solid #dee2e6;">Cod. Ingreso</td>
                                                                    <td width="15%" style="padding: 8px; border: 1px solid #dee2e6;">Estado</td>
                                                                    <td width="15%" style="padding: 8px; border: 1px solid #dee2e6;">Fecha Ingreso</td>
                                                                    <td width="20%" style="padding: 8px; border: 1px solid #dee2e6;">Tipo</td>
                                                                    <td width="10%" style="padding: 8px; border: 1px solid #dee2e6;">Acción</td>
                                                                </tr>
                                                                <!-- Contenedor dinámico -->
                                                                <tr class="servicio-row" id="servicios-first-row">
                                                                    <td style="padding: 5px; border: 1px solid #dee2e6;">
                                                                        <input type="text" name="servicio[]" style="width: 100%; padding: 5px; border: 1px solid #ccc;" placeholder="Descripción del servicio" required>
                                                                    </td>
                                                                    <td style="padding: 5px; border: 1px solid #dee2e6;">
                                                                        <input type="text" name="detalle1[]" style="width: 100%; padding: 5px; border: 1px solid #ccc;" placeholder="Cod. ingreso">
                                                                    </td>
                                                                    <td style="padding: 5px; border: 1px solid #dee2e6;">
                                                                        <input type="text" name="detalle2[]" style="width: 100%; padding: 5px; border: 1px solid #ccc;" placeholder="Estado">
                                                                    </td>
                                                                    <td style="padding: 5px; border: 1px solid #dee2e6;">
                                                                        <input type="date" name="detalle3[]" style="width: 100%; padding: 5px; border: 1px solid #ccc;">
                                                                    </td>
                                                                    <td style="padding: 5px; border: 1px solid #dee2e6;">
                                                                        <select name="idtipo[]" style="width: 100%; padding: 5px; border: 1px solid #ccc;">
                                                                            <?php foreach ($tipos as $tipo): ?>
                                                                                <option value="<?php echo htmlspecialchars($tipo['id']); ?>">
                                                                                    <?php echo htmlspecialchars($tipo['tipos']); ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </td>
                                                                    <td style="padding: 5px; border: 1px solid #dee2e6; text-align: center;">
                                                                        <button type="button" class="remove-servicio" style="padding: 5px 10px; background-color: #dc3545; color: white; border: none; cursor: pointer;" title="Eliminar fila">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                                <!-- Nuevas filas se agregarán aquí después de la primera fila -->
                                                                <tr>
                                                                    <td colspan="6" style="padding: 10px; border-top: 1px solid #dee2e6;">
                                                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                                            <tr>
                                                                                <td>
                                                                                    <button type="button" id="add-servicio" style="padding: 8px 15px; background-color: #007bff; color: white; border: none; cursor: pointer;">
                                                                                        <i class="fas fa-plus"></i> Agregar servicio
                                                                                    </button>
                                                                                </td>
                                                                                <td align="right">
                                                                                    <small style="color: #666;">Ejemplo: Calibración, mantenimiento, inspección, etc.</small>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                            </td>
                        </tr>

                        <!-- FOOTER -->
                        <tr>
                            <td align="right" style="padding: 15px; border-top: 1px solid #dee2e6; background-color: #f8f9fa;">
                                <button type="submit" form="form-guia-recepcion" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; cursor: pointer; margin-right: 10px;">
                                    <i class="fas fa-save"></i> Guardar Guía
                                </button>
                                <a href="<?php echo APP_URL; ?>?views=proformas/index" style="padding: 10px 20px; background-color: #dc3545; color: white; text-decoration: none; display: inline-block;">
                                    <i class="fas fa-times-circle"></i> Cancelar
                                </a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </section>
</div>
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
                <input type="text" name="detalle1[]" style="width: 100%; padding: 5px; border: 1px solid #ccc;" placeholder="Cod. ingreso">
            </td>
            <td style="padding: 5px; border: 1px solid #dee2e6;">
                <input type="text" name="detalle2[]" style="width: 100%; padding: 5px; border: 1px solid #ccc;" placeholder="Estado">
            </td>
            <td style="padding: 5px; border: 1px solid #dee2e6;">
                <input type="date" name="detalle3[]" style="width: 100%; padding: 5px; border: 1px solid #ccc;">
            </td>
            <td style="padding: 5px; border: 1px solid #dee2e6;">
                <select name="idtipo[]" style="width: 100%; padding: 5px; border: 1px solid #ccc;">
                    <?php foreach ($tipos as $tipo): ?>
                        <option value="<?php echo htmlspecialchars($tipo['id']); ?>">
                            <?php echo htmlspecialchars($tipo['tipos']); ?>
                        </option>
                    <?php endforeach; ?>
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