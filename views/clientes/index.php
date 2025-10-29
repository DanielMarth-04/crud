<?php 
require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../controllers/clienteController.php";
require_once __DIR__ . "/../../views/inc/header.php";
require_once __DIR__ . "/../../views/inc/sidebar.php"; 
$controller = new clienteController();
$clientes = $controller->listar();
?>

<!-- Contenido principal -->
<div class="content-wrapper">
  <!-- Encabezado -->
  <section class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <h1 class="m-0 text-dark">
        <i class="fas fa-users mr-2 text-primary"></i> Gestión de Clientes
      </h1>
      <a href="<?php echo APP_URL; ?>?views=clientes/agregar" class="btn btn-primary">
        <i class="fas fa-user-plus mr-1"></i> Agregar Cliente
      </a>
    </div>
  </section>

  <!-- Contenido principal -->
  <section class="content">
    <div class="container-fluid">

      <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h3 class="card-title text-secondary m-0">
            <i class="fas fa-list mr-1"></i> Lista de Clientes
          </h3>
          <div class="input-group input-group-sm" style="width: 250px;">
            <input type="text" class="form-control" placeholder="Buscar cliente...">
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
            </div>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead class="thead-light">
                <tr>
                  
                  <th>Nombre / Razón Social</th>
                  <th>Ruc</th>
                  <th>Correo</th>
                  <th>Teléfono</th>
                  <th>Contacto</th>
                  <th>Dirección</th>
                  <th>Estado</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead> 
              <tbody>
                <?php if (!empty($clientes)): ?>
                  <?php foreach ($clientes as $cliente): ?>
                    <tr>
                      
                      <td><?php echo htmlspecialchars($cliente['nombres']); ?></td>
                      <td><?php echo htmlspecialchars($cliente['DniRuc']); ?></td>
                      <td><?php echo htmlspecialchars($cliente['correo']); ?></td>
                      <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                      <td><?php echo htmlspecialchars($cliente['contacto']); ?></td>
                      <td><?php echo htmlspecialchars($cliente['direccion']); ?></td>
                      <td>
                        <span class="badge <?php echo $cliente['estado'] == 1 ? 'bg-success' : 'bg-secondary'; ?>">
                          <?php echo $cliente['estado'] == 1 ? 'Activo' : 'Inactivo'; ?>
                        </span>
                      </td>
                      <td class="text-center">
                        <a href="<?php echo APP_URL; ?>?views=clientes/editar&id=<?php echo $cliente['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                        <a href="<?php echo APP_URL; ?>controllers/clienteController.php?action=eliminar&id=<?php echo $cliente['id']; ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="text-center text-muted py-3">No hay clientes registrados</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card-footer text-right">
          <small class="text-muted">Total: <?php echo count($clientes); ?> clientes</small>
        </div>
      </div>

    </div>
  </section>
</div>
