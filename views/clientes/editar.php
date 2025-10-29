<?php
require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../models/clientes.php";
require_once __DIR__ . "/../../controllers/clienteController.php";
require_once __DIR__ . "/../inc/header.php";
require_once __DIR__ . "/../inc/sidebar.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: " . APP_URL . "?views=clientes/index&error=noid");
    exit();
}

$controller = new clienteController();
$cliente = $controller->obtenerClientePorId($id);

if (!$cliente) {
    header("Location: " . APP_URL . "?views=clientes/index&error=notfound");
    exit();
}
?>

<div class="content-wrapper">
  <!-- Encabezado -->
  <section class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center mb-3">
      <h1 class="text-primary mb-0">
        <i class="fas fa-user-edit me-2"></i> Editar Cliente
      </h1>
    </div>
  </section>

  <!-- Contenido -->
  <section class="content">
    <div class="container-fluid d-flex justify-content-center">
      <div class="col-lg-8 col-md-10">
        <div class="card shadow-sm border-0 rounded-3">
          <div class="card-header bg-gradient-primary text-white">
            <h3 class="card-title mb-0">
              <i class="fas fa-edit me-2"></i> Actualización de Datos del Cliente
            </h3>
          </div>

          <form method="POST" action="<?php echo APP_URL; ?>controllers/clienteController.php?action=editar">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($cliente['id']); ?>">
            <input type="hidden" name="estado" value="<?php echo htmlspecialchars($cliente['estado'] ?? '1'); ?>">

            <div class="card-body">
              <div class="row g-3">

                <!-- DATOS GENERALES -->
                <div class="col-12">
                  <h5 class="text-secondary border-bottom pb-2 mb-3">
                    <i class="fas fa-id-card me-2"></i> Datos Generales
                  </h5>
                </div>

                <div class="col-md-6">
                  <label for="nombreRz" class="form-label fw-semibold">Nombres o Razón Social</label>
                  <input type="text" name="nombreRz" id="nombreRz" class="form-control"
                    placeholder="Ingrese nombres o razón social" required
                    value="<?php echo htmlspecialchars($cliente['nombres']); ?>">
                </div>

                <div class="col-md-6">
                  <label for="DniRuc" class="form-label fw-semibold">DNI o RUC</label>
                  <input type="text" name="DniRuc" id="DniRuc" class="form-control"
                    placeholder="Ingrese DNI o RUC" required
                    value="<?php echo htmlspecialchars($cliente['DniRuc']); ?>">
                </div>

                <!-- CONTACTO -->
                <div class="col-12 mt-3">
                  <h5 class="text-secondary border-bottom pb-2 mb-3">
                    <i class="fas fa-address-book me-2"></i> Información de Contacto
                  </h5>
                </div>

                <div class="col-md-6">
                  <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
                  <input type="email" name="email" id="email" class="form-control"
                    placeholder="correo@ejemplo.com"
                    value="<?php echo htmlspecialchars($cliente['correo']); ?>">
                </div>

                <div class="col-md-6">
                  <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                  <input type="text" name="telefono" id="telefono" class="form-control"
                    placeholder="Ingrese teléfono"
                    value="<?php echo htmlspecialchars($cliente['telefono']); ?>">
                </div>

                <div class="col-md-6">
                  <label for="contacto" class="form-label fw-semibold">Persona de Contacto</label>
                  <input type="text" name="contacto" id="contacto" class="form-control"
                    placeholder="Ingrese persona de contacto"
                    value="<?php echo htmlspecialchars($cliente['contacto']); ?>">
                </div>

                <!-- DIRECCIÓN -->
                <div class="col-12 mt-3">
                  <h5 class="text-secondary border-bottom pb-2 mb-3">
                    <i class="fas fa-map-marker-alt me-2"></i> Dirección
                  </h5>
                </div>

                <div class="col-md-12">
                  <label for="direccion" class="form-label fw-semibold">Dirección Completa</label>
                  <input type="text" name="direccion" id="direccion" class="form-control"
                    placeholder="Ingrese la dirección del cliente"
                    value="<?php echo htmlspecialchars($cliente['direccion']); ?>">
                </div>
              </div>
            </div>

            <!-- BOTONES -->
            <div class="card-footer bg-light d-flex justify-content-end gap-2">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-1"></i> Guardar Cambios
              </button>
              <a href="<?php echo APP_URL; ?>?views=clientes/index" class="btn btn-warning">
                <i class="fas fa-times-circle me-1"></i> Cancelar
              </a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </section>
</div>
