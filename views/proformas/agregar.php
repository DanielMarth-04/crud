<?php
require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../inc/header.php";
require_once __DIR__ . "/../inc/sidebar.php";
require_once __DIR__ . "/../../controllers/proformasController.php";
require_once __DIR__ . "/../../controllers/serviciosController.php";
require_once __DIR__ . "/../../controllers/clienteController.php";
require_once __DIR__ . '/../../controllers/tipoController.php';

$controller = new serviciosController();
$clientes = new clienteController();
$tipocontroller = new tipoController();
$areas = $controller->areas();
$servicios = $controller->servicios();
$cliente = $clientes->listarClienteprof();
$tipos = $tipocontroller->listar();

?>
<div class="content-wrapper">
    <!-- Encabezado -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-file-invoice-dollar text-primary"></i>
                        <span class="text-dark fw-bold">Registrar Nueva Proforma</span>
                    </h1>
                    <p class="text-muted mb-0 mt-1">Complete los datos para crear una nueva proforma de servicio</p>
                </div>
                <div class="col-sm-6 text-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>?views=dashboard">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>?views=proformas/index">Proformas</a></li>
                            <li class="breadcrumb-item active">Nueva</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenido Principal -->
    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xl-10 col-lg-11">

                    <!-- Card Principal -->
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header bg-primary">
                            <h3 class="card-title text-white fw-bold mb-0">
                                <i class="fas fa-clipboard-list me-2"></i>
                                Información de la Proforma
                            </h3>
                        </div>

                        <form method="POST" action="<?php echo APP_URL; ?>controllers/proformasController.php?action=guardar" id="formProforma">
                            <div class="card-body">

                                <!-- Datos Generales -->
                                <div class="row mb-4">
                                    <div class="col-12 mb-3">
                                        <h5 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-user-tie me-2"></i>
                                            Datos del Cliente
                                        </h5>
                                    </div>
                                    <div class="col-lg-8 col-md-10">
                                        <label for="idcliente" class="form-label fw-bold">
                                            Cliente <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light">
                                                <i class="fas fa-building text-primary"></i>
                                            </span>
                                            <select name="idcliente" id="idcliente" class="form-select select2" data-placeholder="Seleccione el cliente">
                                                <option value=""></option>
                                                <?php foreach ($cliente as $c): ?>
                                                    <option value="<?php echo htmlspecialchars($c['id']); ?>">
                                                        <?php echo htmlspecialchars($c['nombres']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle text-info me-1"></i>
                                            Seleccione el cliente para quien se generará la proforma
                                        </div>
                                    </div>
                                </div>

                                <!-- Sección de Servicios -->
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="text-primary border-bottom pb-2 mb-0 flex-grow-1">
                                                <i class="fas fa-cogs me-2"></i>
                                                Servicios de Calibración
                                            </h5>
                                            <span class="badge bg-info fs-6 ms-3" id="contador-servicios">1 servicio</span>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div id="servicios-container">
                                            <!-- Item de Servicio -->
                                            <div class="servicio-item mb-3">
                                                <div class="card border">
                                                    <div class="card-body p-3">
                                                        <div class="row g-3 align-items-end">

                                                            <!-- Servicio -->
                                                            <div class="col-lg-4 col-md-6">
                                                                <label class="form-label fw-semibold small text-muted mb-1">
                                                                    <i class="fas fa-wrench text-primary me-1"></i>
                                                                    Servicio
                                                                </label>
                                                                <select name="idservicio[]" class="form-select select2 servicio-select">
                                                                    <option value="" selected disabled>Seleccione un servicio</option>
                                                                    <?php foreach ($servicios as $s): ?>
                                                                        <option value="<?php echo htmlspecialchars($s['id']); ?>">
                                                                            <?php echo htmlspecialchars($s['servicio']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>

                                                            <!-- Área -->
                                                            <div class="col-lg-2 col-md-6">
                                                                <label class="form-label fw-semibold small text-muted mb-1">
                                                                    <i class="fas fa-map-marker-alt text-success me-1"></i>
                                                                    Área
                                                                </label>
                                                                <select name="idarea[]" class="form-select select2 area-select" data-placeholder="Seleccione área">
                                                                    <option value="" selected disabled>Seleccione área</option>
                                                                    <?php foreach ($areas as $area): ?>
                                                                        <option value="<?php echo htmlspecialchars($area['id']); ?>">
                                                                            <?php echo htmlspecialchars($area['area']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>

                                                            <!-- Tipo -->
                                                            <div class="col-lg-3 col-md-6">
                                                                <label class="form-label fw-semibold small text-muted mb-1">
                                                                    <i class="fas fa-tag text-warning me-1"></i>
                                                                    Tipo
                                                                </label>
                                                                <select name="idtipo[]" class="form-select select2 tipo-select" data-placeholder="Seleccione tipo">
                                                                    <option value="" selected disabled>Seleccione tipo</option>
                                                                    <?php foreach ($tipos as $tipo): ?>
                                                                        <option value="<?php echo htmlspecialchars($tipo['id']); ?>">
                                                                            <?php echo htmlspecialchars($tipo['tipos']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>

                                                            <!-- Precio -->
                                                            <div class="col-lg-2 col-md-5">
                                                                <label class="form-label fw-semibold small text-muted mb-1">
                                                                    <i class="fas fa-money-bill-wave text-success me-1"></i>
                                                                    Precio (S/)
                                                                </label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light">S/</span>
                                                                    <input type="number" name="precio[]" class="form-control precio-input"
                                                                        placeholder="0.00" step="0.01" min="0">
                                                                </div>
                                                            </div>

                                                            <!-- Botón Eliminar -->
                                                            <div class="col-lg-1 col-md-2 text-center">
                                                                <label class="form-label small text-muted mb-1 d-block">
                                                                    &nbsp;
                                                                </label>
                                                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-servicio w-100" title="Eliminar servicio">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Botón Agregar Servicio -->
                                            <div class="text-center mb-3">
                                                <button type="button" id="btnAddServicio" class="btn btn-outline-primary">
                                                    <i class="fas fa-plus-circle me-2"></i>
                                                    Agregar Otro Servicio
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Resumen Total -->
                                    <div class="col-12 mt-4">
                                        <div class="card bg-light border-primary">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <h5 class="mb-0 text-primary fw-bold">
                                                            <i class="fas fa-calculator me-2"></i>
                                                            Total de la Proforma
                                                        </h5>
                                                        <small class="text-muted">Suma de todos los servicios agregados</small>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <h3 class="mb-0 text-success fw-bold">
                                                            S/ <span id="total-proforma">0.00</span>
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Footer del Card -->
                            <div class="card-footer bg-light">
                                <div class="row">
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-success btn-lg px-5 me-2 shadow-sm">
                                            <i class="fas fa-save me-2"></i>
                                            Guardar Proforma
                                        </button>
                                        <a href="<?php echo APP_URL; ?>?views=proformas/index" class="btn btn-outline-secondary btn-lg px-5 shadow-sm">
                                            <i class="fas fa-times me-2"></i>
                                            Cancelar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        const container = document.getElementById('servicios-container');
        const addBtn = document.getElementById('btnAddServicio');


        // Inicializar Select2 en todos los selects al cargar la página
        function inicializarSelect2() {
            if (typeof $ !== 'undefined' && $.fn.select2) {
                
                // Inicializar Select2 para TODOS los selects con clase .select2
                // Incluyendo servicio, área y tipo del primer servicio-item
                $('.select2').each(function() {
                    // Solo inicializar si no está ya inicializado
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        // Obtener placeholder del atributo data-placeholder o del option
                        let placeholder = $(this).data('placeholder');
                        if (!placeholder) {
                            const placeholderOption = $(this).find('option[value=""]').first();
                            placeholder = placeholderOption.length ? placeholderOption.text() : 'Seleccione...';
                        }

                        $(this).select2({
                            theme: 'bootstrap-5',
                            placeholder: placeholder,
                            allowClear: true,
                            width: '100%',
                            language: {
                                noResults: function() {
                                    return "No se encontraron resultados";
                                }
                            }
                        });
                    }
                });
            }
        }

        // Función para actualizar contador de servicios
        function actualizarContador() {
            const total = document.querySelectorAll('.servicio-item').length;
            const contador = document.getElementById('contador-servicios');
            if (contador) {
                contador.textContent = total + (total === 1 ? ' servicio' : ' servicios');
            }
        }

        // Función para recalcular el total
        function recalcularTotal() {
            let total = 0;
            document.querySelectorAll('.precio-input').forEach(input => {
                const valor = parseFloat(input.value) || 0;
                total += valor;
            });
            const totalElement = document.getElementById('total-proforma');
            if (totalElement) {
                totalElement.textContent = total.toFixed(2);
            }
        }

        // Detectar cambios en inputs de precio
        document.addEventListener('input', function(e) {
            if (e.target && e.target.classList.contains('precio-input')) {
                recalcularTotal();
            }
        });

        // Validación antes de enviar el formulario
        const form = document.getElementById('formProforma');
        form.addEventListener('submit', function(e) {
            console.log('[formProforma] Intentando enviar...');
            let errores = [];

            // Validar cliente
            const idcliente = $('#idcliente').val();
            if (!idcliente) {
                errores.push('Seleccione un cliente.');
            }

            // Validar al menos un servicio y que todos los campos estén completos
            const items = document.querySelectorAll('.servicio-item');
            if (items.length === 0) {
                errores.push('Agregue al menos un servicio.');
            }

            items.forEach((item, index) => {
                const servicio = item.querySelector('select[name="idservicio[]"]');
                const area = item.querySelector('select[name="idarea[]"]');
                const tipo = item.querySelector('select[name="idtipo[]"]');
                const precio = item.querySelector('input[name="precio[]"]');

                if (!servicio || !servicio.value) {
                    errores.push(`Seleccione el servicio en la fila ${index + 1}.`);
                }
                if (!area || !area.value) {
                    errores.push(`Seleccione el área en la fila ${index + 1}.`);
                }
                if (!tipo || !tipo.value) {
                    errores.push(`Seleccione el tipo en la fila ${index + 1}.`);
                }
                const valPrecio = parseFloat(precio && precio.value ? precio.value : '0');
                if (!valPrecio || valPrecio <= 0) {
                    errores.push(`Ingrese un precio válido en la fila ${index + 1}.`);
                }
            });

            if (errores.length > 0) {
                e.preventDefault();
                alert(errores.join('\n'));
                console.warn('[formProforma] Envío bloqueado por errores:', errores);
            } else {
                console.log('[formProforma] Validación OK. Enviando...');
            }
        });

        // Agregar nuevo servicio
        // Usar once: true para prevenir múltiples ejecuciones
        addBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation(); // Prevenir otros listeners

            // Deshabilitar el botón temporalmente para prevenir clics múltiples
            if (this.disabled) return;
            const btn = this;
            btn.disabled = true;

            // Obtener el primer servicio-item como plantilla
            const firstItem = container.querySelector('.servicio-item');
            if (!firstItem) {
                console.error('No se encontró el servicio-item');
                btn.disabled = false;
                return;
            }

            // Clonar el elemento - IMPORTANTE: clonar sin Select2 inicializado
            // Primero destruir Select2 del elemento original temporalmente si es necesario
            const selectsOriginales = firstItem.querySelectorAll('select.select2');
            const estadosSelect2 = [];

            if (typeof $ !== 'undefined' && $.fn.select2) {
                selectsOriginales.forEach((select, index) => {
                    if ($(select).hasClass('select2-hidden-accessible')) {
                        estadosSelect2[index] = $(select).val();
                        $(select).select2('destroy');
                    }
                });
            }

            // Ahora clonar el elemento (sin Select2)
            const newItem = firstItem.cloneNode(true);

            // Restaurar Select2 en el elemento original
            if (typeof $ !== 'undefined' && $.fn.select2) {
                selectsOriginales.forEach((select, index) => {
                    if (estadosSelect2[index] !== undefined) {
                        const placeholder = $(select).data('placeholder') ||
                            $(select).find('option[value=""]').first().text() ||
                            'Seleccione...';
                        $(select).select2({
                            theme: 'bootstrap-5',
                            placeholder: placeholder,
                            allowClear: true,
                            width: '100%'
                        });
                        if (estadosSelect2[index]) {
                            $(select).val(estadosSelect2[index]).trigger('change');
                        }
                    }
                });
            }

            // Limpiar IDs duplicados del nuevo elemento
            newItem.querySelectorAll('[id]').forEach(el => el.removeAttribute('id'));

            // Limpiar todos los valores del nuevo elemento
            newItem.querySelectorAll('select').forEach(select => {
                select.selectedIndex = 0;
                select.value = '';
            });

            newItem.querySelectorAll('.precio-input').forEach(input => {
                input.value = '';
            });

            // Encontrar el contenedor del botón "Agregar"
            const addButtonContainer = addBtn.parentElement;

            // Insertar el nuevo item ANTES del contenedor del botón
            container.insertBefore(newItem, addButtonContainer);

            // Inicializar Select2 en TODOS los selects del nuevo elemento DESPUÉS de insertarlo
            if (typeof $ !== 'undefined' && $.fn.select2) {
                // Usar requestAnimationFrame + setTimeout para asegurar que el DOM esté completamente listo
                requestAnimationFrame(function() {
                    setTimeout(function() {
                        $(newItem).find('select.select2').each(function() {
                            // Asegurar que NO esté ya inicializado
                            if ($(this).hasClass('select2-hidden-accessible')) {
                                $(this).select2('destroy');
                            }

                            // Obtener placeholder
                            let placeholder = $(this).data('placeholder');
                            if (!placeholder) {
                                const placeholderOption = $(this).find('option[value=""]').first();
                                placeholder = placeholderOption.length ? placeholderOption.text().trim() : 'Seleccione...';
                            }

                            // Inicializar Select2 con la misma configuración que el original
                            $(this).select2({
                                theme: 'bootstrap-5',
                                placeholder: placeholder,
                                allowClear: true,
                                width: '100%',
                                language: {
                                    noResults: function() {
                                        return "No se encontraron resultados";
                                    }
                                }
                            });
                        });
                    }, 100);
                });
            }

            // Actualizar contador y total
            actualizarContador();
            recalcularTotal();

            // Rehabilitar el botón después de un breve delay
            setTimeout(() => {
                btn.disabled = false;
            }, 500);
        }, {
            once: false,
            capture: true
        });

        // Eliminar servicio
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove-servicio')) {
                const item = e.target.closest('.servicio-item');
                const totalItems = document.querySelectorAll('.servicio-item').length;

                if (totalItems > 0) {
                    // Destruir Select2 antes de eliminar
                    if (typeof $ !== 'undefined' && $.fn.select2) {
                        $(item).find('select.select2').each(function() {
                            if ($(this).hasClass('select2-hidden-accessible')) {
                                $(this).select2('destroy');
                            }
                        });
                    }
                    item.remove();
                    actualizarContador();
                    recalcularTotal();
                } else {
                    // Mostrar alerta si intenta eliminar el último
                    alert('Debe haber al menos un servicio en la proforma');
                }
            }
        });

        // Inicializar cuando el DOM esté listo y jQuery también
        if (typeof $ !== 'undefined') {
            $(document).ready(function() {
                inicializarSelect2();
                actualizarContador();
                recalcularTotal();
            });
        } else {
            // Si jQuery no está disponible inmediatamente, esperar un poco
            setTimeout(function() {
                inicializarSelect2();
                actualizarContador();
                recalcularTotal();
            }, 500);
        }
    });
</script>