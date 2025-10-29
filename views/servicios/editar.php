<?php
require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../models/servicios.php";
require_once __DIR__ . "/../../controllers/serviciosController.php";
require_once __DIR__ . "/../inc/header.php";
require_once __DIR__ . "/../inc/sidebar.php";


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$controller = new serviciosController();
$servicio = $controller->obtenerServiciosPorId($id);
$areas = $controller->areas();
?>

<div class="content-wrapper">
    <!-- Encabezado -->
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="mb-0 text-primary">
                <i class="fas fa-tools mr-2"></i> Editar Servicio
            </h1>
        </div>
    </section>

    <!-- Contenido -->
    <section class="content">
        <div class="container-fluid d-flex justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-gradient-primary text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-edit"></i> Editar Servicio
                        </h3>
                    </div>

                    <form method="POST" 
                          action="<?php echo APP_URL; ?>controllers/serviciosController.php?action=editar">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($servicio['id']); ?>">
                        <input type="hidden" name="estado" value="<?php echo htmlspecialchars($servicio['estado'] ?? '1'); ?>">

                        <div class="card-body">
                            <div class="row">
                                <!-- Nombre del servicio -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="servicio">Servicio</label>
                                        <input type="text" name="servicio" id="servicio" class="form-control" required
                                               value="<?php echo htmlspecialchars($servicio['servicio']); ?>">
                                    </div>
                                </div>

                                <!-- Descripción -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="descripcion">Descripción</label>
                                        <input type="text" name="descripcion" id="descripcion" class="form-control"
                                               value="<?php echo htmlspecialchars($servicio['descripcion']); ?>">
                                    </div>
                                </div>

                                <!-- Área (Select dinámico) -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="idarea">Área</label>
                                        <select name="idarea" id="idarea" class="form-control" required>
                                            <option value="">Seleccione un área</option>
                                            <?php foreach ($areas as $area): ?>
                                                <option value="<?php echo $area['id']; ?>"
                                                    <?php echo ($area['id'] == $servicio['idarea']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($area['area']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light text-right">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-sync-alt"></i> Actualizar
                            </button>
                            <a href="<?php echo APP_URL; ?>?views=servicios/index" class="btn btn-warning px-4">
                                <i class="fas fa-times-circle mr-1"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
