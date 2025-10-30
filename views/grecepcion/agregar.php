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
        <div class="container-fluid d-flex justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-lg border-0 rounded-4 animate__animated animate__fadeIn">

                    <!-- HEADER -->
                    <div class="card-header bg-gradient-primary text-white py-3 rounded-top-4 d-flex align-items-center">
                        <i class="fas fa-file-invoice me-2 fs-5"></i>
                        <h3 class="card-title mb-0 fs-5">Datos Generales</h3>
                    </div>

                    <form method="POST" action="<?php echo APP_URL; ?>controllers/guiasRecepcionController.php?action=guardar">
                        <div class="card-body bg-light rounded-bottom-4">
                            <div class="row g-4">

                                <!-- Nº PROFORMA -->
                                <div class="col-md-6">
                                    <label for="idproforma" class="form-label fw-semibold">
                                        Nº Proforma <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-lg shadow-sm">
                                        <span class="input-group-text bg-white">
                                            <i class="fas fa-hashtag text-primary"></i>
                                        </span>
                                        <select name="idproforma" id="idproforma" class="form-select select2" required>
                                            <option value="">Seleccione Nº Proforma</option>
                                            <?php foreach ($proformas as $p): ?>
                                                <option value="<?php echo htmlspecialchars($p['id']); ?>">
                                                    <?php echo htmlspecialchars($p['codigo']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- CLIENTE -->
                                <div class="col-md-6">
                                    <label for="cliente" class="form-label fw-semibold">
                                        Cliente <span class="text-danger">*</span>
                                    </label>
                                    <select name="cliente" id="cliente" class="form-control select2" required>
                                        <option value="">Seleccione un cliente</option>
                                        <?php foreach ($clientes as $c): ?>
                                            <option value="<?php echo htmlspecialchars($c['id']); ?>">
                                                <?php echo htmlspecialchars($c['nombres']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- PERSONAL RESPONSABLE -->
                                <div class="col-md-6">
                                    <label for="idpersonal" class="form-label fw-semibold">
                                        Personal responsable <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-lg shadow-sm">
                                        <span class="input-group-text bg-white">
                                            <i class="fas fa-user-cog text-primary"></i>
                                        </span>
                                        <select name="idpersonal" id="idpersonal" class="form-select select2" required>
                                            <option value="" selected disabled>Seleccione al personal</option>
                                            <?php foreach ($personal as $ps): ?>
                                                <option value="<?php echo htmlspecialchars($ps['id']); ?>">
                                                    <?php echo htmlspecialchars($ps['nombres'] . '  || ' . $ps['cargo']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <small class="text-muted">Seleccione quién recibe el equipo o está a cargo de la guía.</small>
                                </div>

                                <!-- COSTO TOTAL -->
                                <div class="col-md-6">
                                    <label for="costo_total" class="form-label fw-semibold">
                                        Costo Total
                                    </label>
                                    <div class="input-group input-group-lg shadow-sm">
                                        <span class="input-group-text bg-white">
                                            <i class="fas fa-calculator text-primary"></i>
                                        </span>
                                        <input type="text" name="costo_total" id="costo_total"
                                            class="form-control text-end fw-semibold"
                                            placeholder="0.00" readonly>
                                    </div>
                                </div>
                                <!-- SERVICIOS -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        Servicios <span class="text-danger">*</span>
                                    </label>

                                    <div id="servicios-container" class="border rounded-3 p-3 bg-white shadow-sm">

                                        <!-- Encabezado -->
                                        <div class="row fw-semibold text-secondary mb-2 text-center d-none d-md-flex">
                                            <div class="col-3">Descripción</div>
                                            <div class="col-2">Cod. Ingreso</div>
                                            <div class="col-2">Estado</div>
                                            <div class="col-2">Fecha Ingreso</div>
                                            <div class="col-2">Tipo</div>
                                            <div class="col-1"></div>
                                        </div>

                                        <!-- Contenedor dinámico -->
                                        <div id="servicios-rows">
                                            <div class="row g-2 mb-2 servicio-row align-items-center">
                                                <div class="col-3">
                                                    <input type="text" name="servicio[]" class="form-control form-control-sm" placeholder="Descripción del servicio" required>
                                                </div>
                                                <div class="col-2">
                                                    <input type="text" name="detalle1[]" class="form-control form-control-sm" placeholder="Cod. ingreso">
                                                </div>
                                                <div class="col-2">
                                                    <input type="text" name="detalle2[]" class="form-control form-control-sm" placeholder="Estado">
                                                </div>
                                                <div class="col-2">
                                                    <input type="date" name="detalle3[]" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-2">
                                                    <select name="detalle4[]" class="form-select form-select-sm">
                                                        <?php foreach ($tipos as $tipo): ?>
                                                            <option value="<?php echo htmlspecialchars($tipo['id']); ?>">
                                                                <?php echo htmlspecialchars($tipo['tipos']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-1 text-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-servicio p-0 d-flex align-items-center justify-content-center" style="height:31px; width:31px;" title="Eliminar fila">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Botón agregar -->
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <button type="button" id="add-servicio" class="btn btn-outline-primary btn-sm px-3 shadow-sm">
                                                <i class="fas fa-plus me-1"></i> Agregar servicio
                                            </button>
                                            <small class="text-muted">Ejemplo: Calibración, mantenimiento, inspección, etc.</small>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>

                        <!-- FOOTER -->
                        <div class="card-footer bg-white border-top d-flex justify-content-end gap-3 py-3">
                            <button type="submit" class="btn btn-success px-5 shadow-sm hover-grow">
                                <i class="fas fa-save me-2"></i> Guardar Guía
                            </button>
                            <a href="<?php echo APP_URL; ?>?views=proformas/index" class="btn btn-outline-danger px-5 shadow-sm hover-grow">
                                <i class="fas fa-times-circle me-2"></i> Cancelar
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>
</div>

<style>
    /* Animación de entrada */
    .animate__fadeIn {
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Botones con efecto hover */
    .hover-grow:hover {
        transform: scale(1.05);
        transition: all 0.2s ease-in-out;
    }

    /* Select2 personalizado */
    .select2-container .select2-selection--single {
        height: 46px !important;
        display: flex;
        align-items: center;
        border-radius: 0.5rem !important;
        border-color: #dee2e6 !important;
        background-color: #fff;
    }

    .select2-selection__rendered {
        line-height: 46px !important;
        padding-left: 0.75rem !important;
        font-weight: 500;
    }

    .select2-selection__arrow {
        height: 46px !important;
    }

    /* Textareas e inputs uniformes */
    .input-group .form-control,
    textarea.form-control {
        border-radius: 0.5rem !important;
        font-weight: 500;
    }

    .input-group .form-control:focus,
    textarea.form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, .15);
    }

    textarea.form-control {
        resize: none;
    }

    /* Tarjeta principal */
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .1);
    }

    .servicio-row input,
    .servicio-row select {
        min-height: 31px;
    }

    .servicio-row .btn {
        border-radius: 6px;
        transition: 0.2s ease-in-out;
    }

    .servicio-row .btn:hover {
        transform: scale(1.1);
    }

    /* Corrección de layout para el select de Tipo y el botón eliminar */
    .servicio-row select.form-select {
        width: 100%;
        max-width: 100%;
    }

    .servicio-row .col-1 {
        flex: 0 0 48px;
        /* ancho fijo para el botón */
        max-width: 48px;
    }

    .servicio-row .col-1 .btn {
        width: 100%;
        height: 31px;
    }
</style>
<script>
    document.getElementById('add-servicio').addEventListener('click', function() {
        const container = document.getElementById('servicios-rows');

        const newRow = document.createElement('div');
        newRow.classList.add('row', 'g-2', 'mb-2', 'servicio-row', 'align-items-center');
        newRow.innerHTML = `
        <div class="col-3">
            <input type="text" name="servicio[]" class="form-control form-control-sm" placeholder="Descripción del servicio" required>
        </div>
        <div class="col-2">
            <input type="text" name="detalle1[]" class="form-control form-control-sm" placeholder="Cod. ingreso">
        </div>
        <div class="col-2">
            <input type="text" name="detalle2[]" class="form-control form-control-sm" placeholder="Estado">
        </div>
        <div class="col-2">
            <input type="date" name="detalle3[]" class="form-control form-control-sm">
        </div>
<div class="col-2">
    <select name="detalle4[]" class="form-select form-select-sm rounded-3 shadow-sm border-secondary-subtle">
        <?php foreach ($tipos as $tipo): ?>
            <option value="<?php echo htmlspecialchars($tipo['id']); ?>">
                <?php echo htmlspecialchars($tipo['tipos']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
        <div class="col-1 text-center">
            <button type="button" class="btn btn-outline-danger btn-sm remove-servicio p-0 d-flex align-items-center justify-content-center" style="height:31px; width:31px;" title="Eliminar fila">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    `;

        container.appendChild(newRow);
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-servicio')) {
            e.target.closest('.servicio-row').remove();
        }
    });
</script>