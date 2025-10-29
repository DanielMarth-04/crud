<?php
require_once __DIR__ . "/../../controllers/proformasController.php";
require_once __DIR__ . "/../../controllers/personalController.php";
require_once __DIR__ . "/../../controllers/clienteController.php";
$controller = new proformasController();
$trabajadores = new personalController();
$clienteController = new clienteController();
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$proformas = $controller->obtenerproformasPorId($id);
$personal = $trabajadores->obtenerPersonalId($id);
$clientes = $clienteController->listarClienteprof($id); // array de clientes
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

                    <form method="POST" action="<?php echo APP_URL; ?>controllers/proformasController.php?action=guardar">
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
                                        <div id="servicios-rows">
                                            <div class="row g-2 mb-2 servicio-row">
                                                <div class="col">
                                                    <input type="text" name="servicio[]" class="form-control" placeholder="Descripcion" required>
                                                </div>
                                                <div class="col">
                                                    <input type="text" name="detalle1[]" class="form-control" placeholder="cod. de ingreso">
                                                </div>
                                                <div class="col">
                                                    <input type="text" name="detalle2[]" class="form-control" placeholder="estado">
                                                </div>
                                                <div class="col">
                                                    <input type="date" name="detalle3[]" class="form-control" placeholder="fech.ingreso">
                                                </div>
                                                <div class="col">
                                                    <input type="date" name="detalle4[]" class="form-control" placeholder="tipo">
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" id="add-servicio" class="btn btn-primary btn-sm mt-2">
                                            <i class="fas fa-plus"></i> Agregar fila
                                        </button>
                                        <small class="text-muted d-block mt-2">
                                            Ejemplo: Calibración de multímetro, mantenimiento de pinza, etc.
                                        </small>
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
</style>
<script>
    document.getElementById('add-servicio').addEventListener('click', function() {
        const container = document.getElementById('servicios-rows');

        // Crear nueva fila
        const newRow = document.createElement('div');
        newRow.classList.add('row', 'g-2', 'mb-2', 'servicio-row');

        // Columna 1: Descripción (required)
        const col1 = document.createElement('div');
        col1.classList.add('col');
        const input1 = document.createElement('input');
        input1.type = 'text';
        input1.name = 'servicio[]';
        input1.classList.add('form-control');
        input1.placeholder = 'Descripcion';
        input1.required = true;
        col1.appendChild(input1);
        newRow.appendChild(col1);

        // Columna 2: Código de ingreso
        const col2 = document.createElement('div');
        col2.classList.add('col');
        const input2 = document.createElement('input');
        input2.type = 'text';
        input2.name = 'detalle1[]';
        input2.classList.add('form-control');
        input2.placeholder = 'cod. de ingreso';
        col2.appendChild(input2);
        newRow.appendChild(col2);

        // Columna 3: Estado
        const col3 = document.createElement('div');
        col3.classList.add('col');
        const input3 = document.createElement('input');
        input3.type = 'text';
        input3.name = 'detalle2[]';
        input3.classList.add('form-control');
        input3.placeholder = 'estado';
        col3.appendChild(input3);
        newRow.appendChild(col3);

        // Columna 4: Fecha de ingreso
        const col4 = document.createElement('div');
        col4.classList.add('col');
        const input4 = document.createElement('input');
        input4.type = 'date';
        input4.name = 'detalle3[]';
        input4.classList.add('form-control');
        input4.placeholder = 'fech.ingreso';
        col4.appendChild(input4);
        newRow.appendChild(col4);

        // Agregar la fila al contenedor
        container.appendChild(newRow);
    });
</script>