<!-- jQuery -->
<script src="<?php echo APP_URL; ?>assets/plantilla/Adminlte/plugins/jquery/jquery.min.js"></script>

<!-- jQuery UI -->
<script src="<?php echo APP_URL; ?>assets/plantilla/Adminlte/plugins/jquery-ui/jquery-ui.min.js"></script>
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>

<!-- Bootstrap 4 -->
<script src="<?php echo APP_URL; ?>assets/plantilla/Adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- AdminLTE App -->
<script src="<?php echo APP_URL; ?>assets/plantilla/Adminlte/dist/js/adminlte.min.js"></script>

<!-- Inicialización y scripts personalizados -->
<script>
$(document).ready(function () {
  console.log("✅ jQuery y Select2 listos");

  /* ==========================
     🔹 ACTIVAR SELECT2
  ========================== */
  $('#idpersonal').select2({
    theme: 'bootstrap-5',
    placeholder: 'Seleccione al personal...',
    allowClear: true,
    width: '100%',
    language: {
      noResults: function() {
        return "No se encontró personal";
      }
    }
  });

  $('#idproforma').select2({
    theme: 'bootstrap-5',
    placeholder: 'Seleccione la proforma realizada...',
    allowClear: true,
    width: '100%',
    language: {
      noResults: function() {
        return "No se encontró proforma";
      }
    }
  });

  // Animación visual al abrir/cerrar el select
  $('#idpersonal').on('select2:open', function() {
    $('.select2-selection').addClass('border-primary shadow-sm');
  }).on('select2:close', function() {
    $('.select2-selection').removeClass('border-primary shadow-sm');
  });

  /* ==========================
     🔹 LÓGICA DE SERVICIOS
  ========================== */
  $('#btnAddServicio').on('click', function() {
    let first = document.querySelector('.servicio-item');
    if (!first) {
      console.error('No existe .servicio-item');
      return;
    }
    let clone = first.cloneNode(true);
    clone.querySelectorAll('select').forEach(s => s.value = '');
    clone.querySelectorAll('input').forEach(i => i.value = '');
    document.getElementById('servicios-container').appendChild(clone);
    console.log('✅ Clon agregado correctamente');
  });

  // Eliminar servicio
  $(document).on('click', '.btn-remove-servicio', function() {
    const $items = $('.servicio-item');
    if ($items.length > 1) {
      $(this).closest('.servicio-item').remove();
    } else {
      $(this).closest('.servicio-item').find('input, select').val('');
      $(this).closest('.servicio-item').find('select').trigger('change');
      alert('Debe haber al menos un servicio.');
    }
  });
});
</script>


<!-- 🎨 ESTILOS PERSONALIZADOS DE SELECT2 -->
<style>
.select2-container .select2-selection--single {
    height: 42px !important;
    display: flex;
    align-items: center;
    border-radius: 0.5rem !important;
    border: 1px solid #ced4da !important;
    background-color: #fff !important;
    transition: all 0.2s ease;
}
.select2-container--bootstrap-5 .select2-selection--single:focus,
.select2-container--bootstrap-5 .select2-selection--single:active {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,.15);
}
.select2-selection__rendered {
    padding-left: 0.75rem !important;
    line-height: 40px !important;
    font-weight: 500;
}
.select2-selection__arrow {
    height: 40px !important;
}
.select2-dropdown {
    border-radius: 0.5rem !important;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.1);
}

</style>

</body>
</html>
