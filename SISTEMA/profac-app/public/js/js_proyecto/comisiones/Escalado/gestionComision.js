function cargarCategoriasClienteEnModal() {
  const $sel = $('#categoria_cliente_id');
  const url  = $sel.data('url');

  // limpiar opciones y dejar placeholder
  $sel.empty().append('<option value="">Seleccione una categoría...</option>');

  $.getJSON(url)
    .done(res => {
      (res.categorias || []).forEach(c => {
        $sel.append(`<option value="${c.id}">${c.nombre_categoria}</option>`);
      });
    })
    .fail(() => {
      Swal.fire({ icon:'error', title:'Error', text:'No se pudo cargar Categoría de Cliente.' });
    });
}

function cargarRolesEnModal() {
  const $sel = $('#rol_id');
  const url  = $sel.data('url');

  // limpiar opciones y dejar placeholder
  $sel.empty().append('<option value="">Seleccione un rol...</option>');

  $.getJSON(url)
    .done(res => {
      (res.roles || []).forEach(c => {
        $sel.append(`<option value="${c.id}">${c.nombre}</option>`);
      });
    })
    .fail(() => {
      Swal.fire({ icon:'error', title:'Error', text:'No se pudo cargar roles de usuario.' });
    });
}
$('#modalParamComision').on('shown.bs.modal', function () {
  cargarCategoriasClienteEnModal();
  cargarRolesEnModal();
});

$('#modalParamComision').on('hidden.bs.modal', function () {
  const $sel = $('#categoria_cliente_id');
  $sel.empty().append('<option value="">Seleccione una categoría...</option>');
});

