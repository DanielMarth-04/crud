<?php
require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../views/inc/header.php";
require_once __DIR__ . "/../../views/inc/sidebar.php";
?>

<div class="content-wrapper">

<!-- Encabezado -->
<section class="content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">
    <i class="fas fa-file-import text-primary me-2"></i> ORDENES DE TRABAJO
    </h1>
    <a href="<?php echo APP_URL; ?>?views=otrabajo/agregar" class="btn btn-primary shadow-sm">
      <i class="fas fa-plus-circle me-1"></i> Nueva O.T
    </a>
  </div>
</section>

<!-- Contenido principal -->
<section class="content">
  <div class="container-fluid">

    <div class="card shadow-sm border-0">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <h3 class="card-title text-secondary mb-0">
          <i class="fas fa-list text-info me-1"></i> Lista de Ordenes de Trabajo
        </h3>

        <!-- Buscador -->
        <div class="input-group input-group-sm" style="width: 260px;">
          <input type="text" class="form-control" placeholder="Buscar Guias..." id="buscarProformas">
        </div>
      </div>

      <!-- Tabla -->
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-striped align-middle mb-0">
            <thead class="table-light">
              <tr>

                <th>Nº De Orden</th>
                <th>Nº De Guia</th>
                <th>Cliente</th>
                <th class="text-center" style="width: 120px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($guias)): ?>
                <?php foreach ($guias as $guia): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($guia['codigo']); ?></strong></td>
                    <td><?php echo htmlspecialchars($guia['codpro']); ?></td>
                    <td><?php echo htmlspecialchars($guia['nombres']); ?></td>

                    <td class="text-center">
                      <?php if ($guia['estado'] == 1): ?>
                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Activo</span>
                      <?php else: ?>
                        <span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>Inactivo</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <a href="<?php echo APP_URL; ?>?views=servicios/editar&id=<?php echo $servicio['id'];?>"
                        class="btn btn-sm btn-outline-primary" title="Editar" data-bs-toggle="tooltip">
                        <i class="fas fa-edit"></i>
                      </a>
                      <a
                        href="<?php /*echo APP_URL . 'controllers/grecepcionController.php?action=generar&id=' . urlencode($guia['id']); */?>"
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
              <?php else:?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">
                    <i class="fas fa-info-circle me-2"></i>No hay Guias Registradas.
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
          Total: <strong><?php /*echo count($guia); */?></strong> Guias registradas
        </small>
      </div>
    </div>

  </div>
</section>
</div>