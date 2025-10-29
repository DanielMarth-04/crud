<?php require_once __DIR__ . "/../../config/app.php"; ?>
<?php require_once __DIR__ . "/../inc/header.php"; ?>
<?php require_once __DIR__ . "/../inc/sidebar.php"; ?>

<div class="content-wrapper">
  <!-- Encabezado -->
  <section class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center mb-3">
      <h1 class="mb-0 text-primary">
        <i class="fas fa-user-plus me-2"></i> Agregar Cliente
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
              <i class="fas fa-address-card me-2"></i> Registrar Cliente
            </h3>
          </div>

          <form method="POST" action="<?php echo APP_URL; ?>controllers/clienteController.php?action=guardar">
            <div class="card-body">

              <div class="row g-3"> <!-- g-3 agrega espacio entre columnas -->

                <div class="col-md-6">
                  <label for="nombreRz" class="form-label">Nombres completos o Razón Social</label>
                  <input type="text" name="nombreRz" id="nombreRz" class="form-control" placeholder="Ingrese nombres completos o razón social" required>
                </div>

                <div class="col-md-6">
                  <label for="DniRuc" class="form-label">RUC o DNI</label>
                  <input type="number" name="DniRuc" id="DniRuc" class="form-control" placeholder="Ingrese RUC o DNI" required>
                </div>

                <div class="col-md-6">
                  <label for="email" class="form-label">Correo electrónico</label>
                  <input type="email" name="email" id="email" class="form-control" placeholder="correo@ejemplo.com">
                </div>

                <div class="col-md-6">
                  <label for="telefono" class="form-label">Teléfono</label>
                  <input type="text" name="telefono" id="telefono" class="form-control" placeholder="Ingrese teléfono">
                </div>

                <div class="col-md-6">
                  <label for="contacto" class="form-label">Contacto</label>
                  <input type="text" name="contacto" id="contacto" class="form-control" placeholder="Nombre del contacto">
                </div>

                <div class="col-md-6">
                  <label for="direccion" class="form-label">Dirección</label>
                  <input type="text" name="direccion" id="direccion" class="form-control" placeholder="Ingrese la dirección">
                </div>

              </div>
            </div>

            <div class="card-footer bg-light d-flex justify-content-end gap-2">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-1"></i> Guardar Cliente
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
