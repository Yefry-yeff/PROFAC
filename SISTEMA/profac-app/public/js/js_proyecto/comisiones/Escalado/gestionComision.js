function cargarCategoriasClienteEnModal(selected = null) {
  const $sel = $('#categoria_cliente_id');
  const url  = $sel.data('url');

  $sel.empty().append('<option value="">Seleccione una categoría...</option>');

  $.getJSON(url)
    .done(res => {
      (res.categorias || []).forEach(c => {
        $sel.append(`<option value="${c.id}">${c.nombre_categoria}</option>`);
      });

      // SI VIENES EDITANDO → marcar la opción correcta
      if (selected) {
        $sel.val(selected);
      }

    })
    .fail(() => {
      Swal.fire({
        icon:'error',
        title:'Error',
        text:'No se pudo cargar Categoría de Cliente.'
      });
    });
}


function cargarRolesEnModal(selected = null) {
  const $sel = $('#rol_id');
  const url  = $sel.data('url');

  $sel.empty().append('<option value="">Seleccione un rol...</option>');

  $.getJSON(url)
    .done(res => {
      (res.roles || []).forEach(c => {
        $sel.append(`<option value="${c.id}">${c.nombre}</option>`);
      });

      if (selected) {
        $sel.val(selected);
      }

    })
    .fail(() => {
      Swal.fire({
        icon:'error',
        title:'Error',
        text:'No se pudo cargar roles de usuario.'
      });
    });
}


$('#modalParamComision').on('shown.bs.modal', function () {
    // Cargar opciones siempre (editar ahora usa #modalEditarPct)
    cargarCategoriasClienteEnModal();
    cargarRolesEnModal();
});


/*Registro de parametro de comisión — recoge filas de la tabla dinámica */
function registrarParametroComision() {
  var $btn  = $('#btn_guardar_parametro_comision');
  var cat   = $('#categoria_cliente_id').val();
  var rol   = $('#rol_id').val();
  var nombre = $('#nombre_comescala').val().trim();

  if (!cat || !rol) {
    Swal.fire({ icon:'warning', title:'Faltan datos', text:'Seleccione rol y categoría de cliente.' });
    return;
  }
  if (!nombre) {
    Swal.fire({ icon:'warning', title:'Faltan datos', text:'Ingrese un título para la configuración.' });
    return;
  }

  // Recoger filas con % ingresado
  var filas = [];
  $('#tbody_categorias_precio tr').each(function() {
    var catPreId = $(this).data('cat-id');
    var pct      = $(this).find('.pct-input').val();
    if (catPreId && pct !== '' && parseFloat(pct) > 0) {
      filas.push({ categoria_precios_id: catPreId, porcentaje: pct });
    }
  });

  if (!filas.length) {
    Swal.fire({ icon:'warning', title:'Sin porcentajes', text:'Ingrese al menos un porcentaje mayor a 0.' });
    return;
  }

  var payload = new FormData();
  payload.append('nombre_comescala',    nombre);
  payload.append('categoria_cliente_id', cat);
  payload.append('rol_id',              rol);
  filas.forEach(function(f, i) {
    payload.append('filas[' + i + '][categoria_precios_id]', f.categoria_precios_id);
    payload.append('filas[' + i + '][porcentaje]',           f.porcentaje);
  });

  $btn.prop('disabled', true);

  axios.post('/guardar/parametro/comision', payload)
    .then(function(res) {
      $('#modalParamComision').modal('hide');
      $('#tbl_listaParametroComision').DataTable().ajax.reload(null, false);
      Swal.fire({ icon: res.data.icon, title: res.data.title, text: res.data.text });
    })
    .catch(function(err) {
      var d = err.response?.data || { icon:'error', title:'Error', text:'Ocurrió un error.' };
      Swal.fire({ icon: d.icon, title: d.title, text: d.text });
    })
    .finally(function() { $btn.prop('disabled', false); });
}

$(document).on('submit', '#paramComisionForm', function(event) {
  event.preventDefault();
  registrarParametroComision();
});

listaParametroComision();

/*Listando los registros */
function listaParametroComision() {
  $('#tbl_listaParametroComision').DataTable({
    destroy: true,
    order: [0, 'desc'],
    language: { "url": "/js/plugins/dataTables/i18n/Spanish.json" },
    pageLength: 10,
    responsive: true,
    deferRender: true,
    dom: '<"html5buttons"B>lTfgitp',
    buttons: [{ extend: 'excel', title: 'Parametrizacion_comisiones' }],
    ajax: {
      url: "/listar/parametros/comision",
      dataSrc: 'data',
      error: function() {
        Swal.fire({ icon:'error', title:'Error', text:'No se pudo cargar la tabla.' });
      }
    },
    columns: [
        { data: 'id' },
        { data: 'nombre' },
        { data: 'porcentaje_comision', render: function(d) { return d + '%'; } },
        { data: 'rol' },
        { data: 'cliente_cat_escala' },
        { data: 'categoria_precio' },
        { data: 'userRegistro' },
        { data: 'fechaRegistro' },
        { data: 'estado', orderable: false },
        { data: 'opciones', orderable: false }
    ]
  });
}

function desactivarCategoria(id, rol) {
    Swal.fire({
        title: '¿Desactivar este parámetro?',
        html: '<b>Advertencia:</b><br>Al desactivar, <span class="text-danger font-weight-bold">el rol ' + rol + ' dejará de recibir comisión por esta categoría de precio.</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d'
    }).then(function(result) {
        if (result.isConfirmed) {
            axios.post('/desactivar/parametro-comision/' + id)
                .then(function(res) {
                    Swal.fire({ icon: res.data.icon, title: res.data.title, text: res.data.text });
                    $('#tbl_listaParametroComision').DataTable().ajax.reload(null, false);
                })
                .catch(function() {
                    Swal.fire({ icon:'error', title:'Error', text:'No se pudo desactivar el parámetro.' });
                });
        }
    });
}

