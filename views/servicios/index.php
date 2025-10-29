<?php
require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../controllers/serviciosController.php";
require_once __DIR__ . "/../../views/inc/header.php";
require_once __DIR__ . "/../../views/inc/sidebar.php";
$controller = new serviciosController();
$servicios = $controller->listar();
?>

<div class="content-wrapper">

  <!-- Encabezado -->
  <section class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <h1 class="m-0 text-dark">
        <i class="fas fa-briefcase text-primary me-2"></i> Gestión de Servicios
      </h1>
      <a href="<?php echo APP_URL; ?>?views=servicios/agregar" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus-circle me-1"></i> Nuevo Servicio
      </a>
    </div>
  </section>

  <!-- Contenido principal -->
  <section class="content">
    <div class="container-fluid">

      <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
          <h3 class="card-title text-secondary mb-0">
            <i class="fas fa-list text-info me-1"></i> Lista de Servicios
          </h3>

          <!-- Buscador -->
          <div class="input-group input-group-sm" style="width: 260px;">
            <input type="text" class="form-control" placeholder="Buscar servicio..." id="buscarServicio">
          </div>
        </div>

        <!-- Tabla -->
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead class="table-light">
                <tr>
                  
                  <th>Servicio</th>
                  <th>Descripción</th>
                  <th>Área</th>
                  <th class="text-center">Estado</th>
                  <th class="text-center" style="width: 120px;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($servicios)): ?>
                  <?php foreach ($servicios as $servicio): ?>
                    <tr>
                      <td><strong><?php echo htmlspecialchars($servicio['servicio']); ?></strong></td>
                      <td><?php echo htmlspecialchars($servicio['descripcion']); ?></td>
                      <td><?php echo htmlspecialchars($servicio['area']); ?></td>
                      <td class="text-center">
                        <?php if ($servicio['estado'] == 1): ?>
                          <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Activo</span>
                        <?php else: ?>
                          <span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>Inactivo</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <a href="<?php echo APP_URL; ?>?views=servicios/editar&id=<?php echo $servicio['id']; ?>"
                          class="btn btn-sm btn-outline-primary" title="Editar" data-bs-toggle="tooltip">
                          <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?php echo APP_URL; ?>controllers/serviciosController.php?action=eliminar&id=<?php echo $servicio['id'];  ?>"
                          class="btn btn-sm btn-outline-danger" title="Eliminar" data-bs-toggle="tooltip">
                          <i class="fas fa-trash-alt"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                      <i class="fas fa-info-circle me-2"></i>No hay servicios registrados.
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pie de tabla -->
        <div class="card-footer bg-white text-end">
          <small class="text-muted">
            Total: <strong><?php echo count($servicios); ?></strong> servicios registrados
          </small>
        </div>
      </div>

    </div>
  </section>
</div>
<!-- Scripts de Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
  crossorigin="anonymous"></script>

<!-- Activar tooltips -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function(tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
  });
</script>