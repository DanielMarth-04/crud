<?php
require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../inc/header.php";
require_once __DIR__ . "/../inc/sidebar.php";
require_once __DIR__ . "/../../controllers/proformasController.php";
require_once __DIR__ . "/../../controllers/serviciosController.php";
require_once __DIR__ . "/../../controllers/clienteController.php";

$controller = new serviciosController();
$clientes = new clienteController();
$areas = $controller->areas();
$servicios = $controller->servicios();
$cliente = $clientes->listarClienteprof();

?>

<style>
    /* ... (Estilos CSS anteriores sin cambios) ... */
    /* ====== MEJORAS DE DISEÑO Y ESTILOS GENERALES ====== */
    .content-wrapper {
        background-color: #f8f9fa !important;
    }

    .card {
        border-radius: 1.25rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        border: none !important;
        overflow: hidden;
    }

    .card-header {
        border-radius: 1.25rem 1.25rem 0 0 !important;
        padding: 1.5rem 2rem !important;
    }

    .card-header i {
        font-size: 1.3rem;
    }

    .card-body {
        padding: 2.5rem 3rem !important;
    }

    .label {
        font-weight: 700;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    /* Estilos base de inputs y select2 */
    .form-control,
    .form-select,
    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.75rem !important;
        height: calc(2.4rem + 2px) !important;
        display: flex;
        align-items: center;
        border: 1px solid #ced4da;
        transition: all 0.2s ease-in-out;
    }

    .form-control:focus,
    .form-select:focus,
    .select2-container--bootstrap-5 .select2-selection:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.15) !important;
    }

    /* 🔑 AJUSTE CLAVE: Estilos del Input Group (Iconos y Alineación) 🔑 */
    .input-group-text {
        width: auto !important;
        padding-left: 0.8rem !important;
        padding-right: 0.8rem !important;
        justify-content: center !important;
        background: #fff !important;
        border-right: 0;
        border-radius: 0.75rem 0 0 0.75rem !important;
        /* Radio izquierdo */
        height: calc(2.4rem + 2px) !important;
    }

    /* Alineación de Select2 y Inputs dentro de un Input Group */
    .input-group .form-control,
    .input-group .select2-container--bootstrap-5 .select2-selection {
        border-left: 0 !important;
        border-radius: 0 0.75rem 0.75rem 0 !important;
        /* Radio derecho */
        height: calc(2.4rem + 2px) !important;
        padding: 0.375rem 0.75rem;
    }

    .select2-container--bootstrap-5 .select2-selection__rendered {
        padding-left: 0.5rem !important;
        line-height: 2.3rem !important;
    }

    .btn {
        border-radius: 0.75rem;
    }

    .content-header h1 {
        font-weight: 700;
        letter-spacing: 0.3px;
    }
</style>

<div class="content-wrapper">
    <!-- 🧭 ENCABEZADO -->
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="mb-0 text-primary">
                <i class="fas fa-tools me-2"></i> Registrar Nueva Proforma
            </h1>
        </div>
        <hr class="mt-3 mb-4">
    </section>

    <!-- 🧾 FORMULARIO -->
    <section class="content">
        <div class="container-fluid d-flex justify-content-center">
            <div class="col-lg-6 col-md-10">
                <div class="card shadow-lg border-0">
                    <!-- HEADER -->
                    <div class="card-header bg-gradient-primary text-white d-flex align-items-center py-3">
                        <i class="fas fa-file-invoice me-2"></i>
                        <h3 class="card-title mb-0 fs-5">Datos de la Proforma</h3>
                    </div>

                    <!-- BODY -->
                    <form method="POST" action="<?php echo APP_URL; ?>controllers/proformasController.php?action=guardar">
                        <div class="card-body bg-light">
                            <div class="row g-4">

                                <!-- CLIENTE -->
                                <div class="col-md-6">
                                    <label for="idcliente" class="form-label">Cliente <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user text-primary"></i>
                                        </span>
                                        <select name="idcliente" id="idcliente" class="form-select select2" required>
                                            <option value="" selected disabled>Seleccione el cliente</option>
                                            <?php foreach ($cliente as $c): ?>
                                                <option value="<?php echo htmlspecialchars($c['id']); ?>">
                                                    <?php echo htmlspecialchars($c['nombres']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- SERVICIO -->
                                <!-- SERVICIOS -->
                                <div class="col-md-12">
                                    <label class="form-label">Servicios <span class="text-danger">*</span></label>

                                    <!-- Contenedor dinámico -->
                                    <div id="servicios-container">
                                        <div class="row g-3 servicio-item align-items-end mb-2">
                                            <div class="col-md-8">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-concierge-bell text-primary"></i>
                                                    </span>
                                                    <select name="idservicio[]" class="form-select select2 servicio-select" required>
                                                        <option value="" selected disabled>Seleccione un servicio</option>
                                                        <?php foreach ($servicios as $s): ?>
                                                            <option value="<?php echo htmlspecialchars($s['id']); ?>">
                                                                <?php echo htmlspecialchars($s['servicio']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-dollar-sign text-primary"></i>
                                                    </span>
                                                    <input type="number" name="precio[]" class="form-control" placeholder="Costo" step="0.01" required>
                                                </div>
                                            </div>
                                            <div class="col-md-1 text-center">
                                                <button type="button" class="btn btn-outline-danger btn-remove-servicio">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botón para añadir más servicios -->
                                    <div class="text-end mt-2">
                                        <button type="button" id="btnAddServicio" class="btn btn-outline-primary">
                                            <i class="fas fa-plus"></i> Añadir servicio
                                        </button>
                                    </div>
                                </div>

                                <!-- ÁREA (Lugar del servicio) - ¡AHORA 100% ANCHO! -->
                                <div class="col-md-12">
                                    <label for="idarea" class="form-label">Lugar del servicio <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-building text-primary"></i>
                                        </span>
                                        <select name="idarea" id="idarea" class="form-select select2" required>
                                            <option value="" selected disabled>Seleccione un lugar</option>
                                            <?php foreach ($areas as $a): ?>
                                                <option value="<?php echo htmlspecialchars($a['id']); ?>">
                                                    <?php echo htmlspecialchars($a['area']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- COSTO -->
                                <div class="col-md-4">
                                    <label for="costo" class="form-label">Costo del servicio</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-dollar-sign text-primary"></i>
                                        </span>
                                        <input type="text" name="costo_total" id="costo_total" class="form-control" placeholder="50.00" value="">

                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- FOOTER -->
                        <div class="card-footer bg-white border-top d-flex justify-content-end gap-3 py-4">
                            <button type="submit" class="btn btn-success px-5 shadow-lg">
                                <i class="fas fa-save me-1"></i> Guardar Proforma
                            </button>
                            <a href="<?php echo APP_URL; ?>?views=proformas/index" class="btn btn-outline-danger px-5 shadow-lg">
                                <i class="fas fa-times-circle me-1"></i> Cancelar
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {

    // 🔹 Función para recalcular el total
    function recalcularTotal() {
        let total = 0;
        document.querySelectorAll('input[name="precio[]"]').forEach(input => {
            let valor = parseFloat(input.value) || 0;
            total += valor;
        });
        document.getElementById('costo_total').value = total.toFixed(2);
    }

    // 🔹 Detectar cambios en cualquier input de precio existente
    document.addEventListener('input', function (e) {
        if (e.target && e.target.name === 'precio[]') {
            recalcularTotal();
        }
    });

    // 🔹 Detectar cuando se agreguen nuevos servicios dinámicamente
    const container = document.getElementById('servicios-container');
    const addBtn = document.getElementById('btnAddServicio');

    addBtn.addEventListener('click', function () {
        // Clona el primer bloque
        const firstItem = container.querySelector('.servicio-item');
        const newItem = firstItem.cloneNode(true);

        // Limpia los valores
        newItem.querySelector('select').value = '';
        newItem.querySelector('input[name="precio[]"]').value = '';

        // Agrega el nuevo bloque
        container.appendChild(newItem);

        // Actualiza select2 si lo usas
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $(newItem).find('select.select2').select2({
                theme: 'bootstrap-5'
            });
        }

        // Volver a calcular
        recalcularTotal();
    });

    // 🔹 Permitir eliminar servicios dinámicos
    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-servicio')) {
            const item = e.target.closest('.servicio-item');
            const totalItems = document.querySelectorAll('.servicio-item').length;
            if (totalItems > 1) {
                item.remove();
                recalcularTotal();
            }
        }
    });

    // 🔹 Calcula el total al cargar la página
    recalcularTotal();
});
</script>



