<?php
require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../controllers/proformasController.php";
require_once __DIR__ . "/../../views/inc/header.php";
require_once __DIR__ . "/../../views/inc/sidebar.php";
$controller = new proformasController();
$proformas = $controller->listar();
?>
<div class="content-wrapper">

  <!-- Encabezado -->
  <section class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <h1 class="m-0 text-dark">
        <i class="fas fa-briefcase text-primary me-2"></i> Gestión de proformas
      </h1>
      <a href="<?php echo APP_URL; ?>?views=proformas/agregar" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus-circle me-1"></i> Nueva proforma
      </a>
    </div>
  </section>

  <!-- Contenido principal -->
  <section class="content">
    <div class="container-fluid">

      <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
          <h3 class="card-title text-secondary mb-0">
            <i class="fas fa-list text-info me-1"></i> Lista de Proformas
          </h3>

          <!-- Buscador -->
          <div class="input-group input-group-sm" style="width: 260px;">
            <input type="text" class="form-control" placeholder="Buscar proformas..." id="buscarProformas">
          </div>
        </div>

        <!-- Tabla -->
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead class="table-light">
                <tr>

                  <th>Nº proforma</th>
                  <th>Cliente</th>
                  <th>Ruc</th>
                  <th>Servicio</th>
                  <th>Fecha</th>
                  <th class="text-center">Estado</th>
                  <th class="text-center" style="width: 120px;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($proformas)): ?>
                  <?php foreach ($proformas as $proforma): ?>
                    <tr>
                      <td><strong><?php echo htmlspecialchars($proforma['codigo']); ?></strong></td>
                      <td><?php echo htmlspecialchars($proforma['cliente']); ?></td>
                      <td><?php echo htmlspecialchars($proforma['dni_ruc']); ?></td>
                      <td><?php echo htmlspecialchars($proforma['servicios']); ?></td>
                      <td><?php echo htmlspecialchars($proforma['fecha']); ?></td>

                      <td class="text-center">
                        <?php if ($proforma['estado'] == 1): ?>
                          <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Activo</span>
                        <?php else: ?>
                          <span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>Inactivo</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <a href="<?php echo APP_URL; ?>?views=servicios/editar&id=<?php /*echo $servicio['id'];*/ ?>"
                          class="btn btn-sm btn-outline-primary" title="Editar" data-bs-toggle="tooltip">
                          <i class="fas fa-edit"></i>
                        </a>
                        <a
                          href="<?php echo APP_URL . 'controllers/proformasController.php?action=generar&id=' . urlencode($proforma['id']); ?>"
                          class="btn btn-sm btn-outline-danger"
                          title="Descargar PDF"
                          target="_blank">
                          <i class="fas fa-file-pdf"></i>
                        </a>
                        <a href="<?php echo APP_URL; ?>controllers/serviciosController.php?action=eliminar&id=<?php /*echo $servicio['id']; */ ?>"
                          class="btn btn-sm btn-outline-danger" title="Eliminar" data-bs-toggle="tooltip">
                          <i class="fas fa-trash-alt"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                      <i class="fas fa-info-circle me-2"></i>No hay Proformas Registradas.
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
            Total: <strong><?php echo count($proformas); ?></strong> Proformas registradas
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