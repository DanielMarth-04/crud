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
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
      <h1 class="m-0 text-dark">
        <i class="fas fa-briefcase text-primary me-2"></i> Gestión de Proformas
      </h1>
      <div class="d-flex gap-2 mt-2 mt-md-0">
        <a href="<?php echo APP_URL; ?>?views=proformas/agregar" class="btn btn-primary shadow-sm">
          <i class="fas fa-plus-circle me-1"></i> Proforma
        </a>
        <button id="btnGenerarGuia" class="btn btn-success shadow-sm">
          <i class="fas fa-file-alt me-1"></i> Generar Guía
        </button>
      </div>
      <div class="input-group input-group-sm mt-2" style="width: 280px;">
        <span class="input-group-text bg-light border-0"><i class="fas fa-hashtag text-muted"></i></span>
        <input type="text" id="idsSeleccionados" class="form-control border-0 shadow-none" placeholder="IDs seleccionados..." readonly>
      </div>
    </div>
  </section>

  <!-- Contenido principal -->
  <section class="content">
    <div class="container-fluid">

      <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap">
          <h3 class="card-title text-secondary mb-0">
            <i class="fas fa-list text-info me-1"></i> Lista de Proformas
          </h3>

          <!-- Buscador -->
          <div class="input-group input-group-sm mt-2 mt-md-0" style="width: 280px;">
            <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" class="form-control border-0 shadow-none" placeholder="Buscar proformas..." id="buscarProformas">
          </div>
        </div>

        <!-- Tabla -->
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light text-center align-middle">
                <tr>
                  <th>Nº Proforma</th>
                  <th>Cliente</th>
                  <th>RUC / DNI</th>
                  <th>Servicio</th>
                  <th>Fecha</th>
                  <th>Estado</th>
                  <th style="width: 140px;">Acciones</th>
                </tr>
              </thead>
              <tbody class="text-center">
                <?php if (!empty($proformas)): ?>
                  <?php foreach ($proformas as $proforma): ?>
                    <tr>
                      
                      <td><strong><?php echo htmlspecialchars($proforma['codigo']); ?></strong></td>
                      <td><?php echo htmlspecialchars($proforma['cliente']); ?></td>
                      <td><?php echo htmlspecialchars($proforma['dni_ruc']); ?></td>
                      <td><?php echo htmlspecialchars($proforma['servicios']); ?></td>
                      <td><?php echo htmlspecialchars($proforma['fecha']); ?></td>
                      <td>
                        <?php if ($proforma['estado'] == 1): ?>
                          <span class="badge bg-success px-2 py-1"><i class="fas fa-check me-1"></i> Activo</span>
                        <?php else: ?>
                          <span class="badge bg-secondary px-2 py-1"><i class="fas fa-ban me-1"></i> Inactivo</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="btn-group">
                          <a href="<?php echo APP_URL; ?>?views=proformas/editar&id=<?php echo $proforma['id']; ?>"
                            class="btn btn-sm btn-outline-primary" title="Editar" data-bs-toggle="tooltip">
                            <i class="fas fa-edit"></i>
                          </a>
                          <a href="<?php echo APP_URL . 'controllers/proformasController.php?action=generar&id=' . urlencode($proforma['id']); ?>"
                            class="btn btn-sm btn-outline-danger" title="Descargar PDF" target="_blank">
                            <i class="fas fa-file-pdf"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                      <i class="fas fa-info-circle me-2"></i>No hay Proformas Registradas.
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pie -->
        <div class="card-footer bg-white text-end">
          <small class="text-muted">
            Total: <strong><?php echo count($proformas); ?></strong> Proformas registradas
          </small>
        </div>
      </div>
    </div>
  </section>
</div>
<script>
</script>