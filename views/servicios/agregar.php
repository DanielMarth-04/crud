<?php require_once __DIR__ . "/../../config/app.php"; ?>
<?php require_once __DIR__ . "/../inc/header.php"; ?>
<?php require_once __DIR__ . "/../inc/footer.php"; ?>
<?php require_once __DIR__ . "/../inc/sidebar.php"; ?>
<?php require_once __DIR__ . "/../inc/sidebar.php"; ?>
<?php require_once __DIR__ . "/../../controllers/serviciosController.php"; ?>

<?php $controller = new serviciosController();
$areas = $controller->areas(); ?>

<style>
    /* Ajuste para Select2 dentro de input-group */
    .select2-fix {
        /* Quitamos display: flex; y flex-grow: 1; de aquí si los pusimos en el HTML */
        flex-grow: 1;
        /* Mantener esto si no funciona solo con el style="flex-grow: 1;" */
    }

    .select2-fix .select2-container {
        width: 100% !important;
    }

    .select2-fix select {
        width: 100% !important;
    }

    /* Mejoras visuales */
    .card {
        border-radius: 1rem;
        overflow: hidden;
    }

    .card-header i {
        font-size: 1.3rem;
    }

    label {
        color: #333;
    }

    .form-control,
    .form-select {
        border-radius: 0.5rem;
    }

    .btn {
        border-radius: 0.5rem;
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
                <i class="fas fa-tools me-2"></i> Registrar nuevo servicio
            </h1>
            
        </div>
        <hr>
    </section>

    <!-- 🧾 FORMULARIO -->
    <section class="content">
        <div class="container-fluid d-flex justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-lg border-0">

                    <!-- HEADER -->
                    <div class="card-header bg-gradient-primary text-white d-flex align-items-center py-3">
                        <i class="fas fa-concierge-bell me-2"></i>
                        <h3 class="card-title mb-0 fs-5">Datos del servicio</h3>
                    </div>

                    <!-- BODY -->
                    <form method="POST" action="<?php echo APP_URL; ?>controllers/serviciosController.php?action=guardar">
                        <div class="card-body bg-light">

                            <div class="row g-4">
                                <!-- Servicio -->
                                <div class="col-md-6">
                                    <label for="nombreServicio" class="form-label fw-semibold">
                                        Nombre del servicio <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">
                                            <i class="fas fa-tag text-primary"></i>
                                        </span>
                                        <input type="text" name="servicio" id="servicio" class="form-control"
                                            placeholder="Laboratorio de ensayo" required>
                                    </div>
                                </div>

                                <!-- Descripción -->
                                <div class="col-md-6">
                                    <label for="descripcion" class="form-label fw-semibold">
                                        Descripción
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">
                                            <i class="fas fa-align-left text-primary"></i>
                                        </span>
                                        <input type="text" name="descripcion" id="descripcion" class="form-control"
                                            placeholder="Calibracion de Manometros">
                                    </div>
                                </div>

                                <!-- Área asignada -->
                                <div class="col-md-6">
                                    <label for="idarea" class="form-label fw-semibold">
                                        Área asignada <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group"> <span class="input-group-text bg-white">
                                            <i class="fas fa-building text-primary"></i>
                                        </span>
                                        <div class="select2-fix" style="flex-grow: 1;">
                                            <select name="idarea" id="idarea" class="form-select select2" required>
                                                <option value="" selected disabled>Seleccione un área</option>
                                                <?php foreach ($areas as $a): ?>
                                                    <option value="<?php echo htmlspecialchars($a['id']); ?>">
                                                        <?php echo htmlspecialchars($a['area']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- FOOTER -->
                        <div class="card-footer bg-white border-top d-flex justify-content-end gap-3 py-3">
                            <button type="submit" class="btn btn-success px-4 shadow-sm">
                                <i class="fas fa-save me-1"></i> Guardar servicio
                            </button>
                            <a href="<?php echo APP_URL; ?>?views=servicios/index" class="btn btn-danger px-4 shadow-sm">
                                <i class="fas fa-times-circle me-1"></i> Cancelar
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>
</div>



<!-- Select2 -->


<!-- Inicialización -->
<script>
    $(document).ready(function() {
        $('#idarea').select2({
            theme: 'bootstrap-5',
            placeholder: 'Seleccione un área',
            allowClear: true,
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                }
            }
        });
    });
</script>