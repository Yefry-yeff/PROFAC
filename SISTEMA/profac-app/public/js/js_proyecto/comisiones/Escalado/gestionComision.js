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
    if (!$('#param_comision_id').val()) {
        cargarCategoriasClienteEnModal();
        cargarRolesEnModal();
    }
});


$('#modalParamComision').on('hidden.bs.modal', function () {
  const $sel = $('#categoria_cliente_id');
  $sel.empty().append('<option value="">Seleccione una categoría...</option>');
});


/*Registro de parametro de comisión */
function registrarParametroComision() {
  const $btn = $('#btn_guardar_parametro_comision');
  const cat = $('#categoria_cliente_id').val();
  const idParam = $('#param_comision_id').val(); // si viene lleno, es edición

  if (!cat) {
    Swal.fire({ icon:'warning', title:'Falta categoría', text:'Seleccione una categoría de cliente.' });
    return;
  }

  var data = new FormData($('#paramComisionForm').get(0));

  let url = '';
  if (idParam) {
    // Editar
    url = '/actualizar/parametro/comision/' + idParam;
  } else {
    // Crear
    url = '/guardar/parametro/comision';
  }

  $btn.prop('disabled', true);

  axios.post(url, data)
    .then(response => {
      let data = response.data;
      // Cerrar modal y limpiar estado del formulario/validaciones
      $('#modalParamComision').modal('hide');
      $('#paramComisionForm').parsley().reset();
      $('#paramComisionForm')[0].reset();
      $('#param_comision_id').val(''); // limpiar ID

      // Refrescar DataTable
      $('#tbl_listaParametroComision').DataTable().ajax.reload(null, false);

      // Notificación al usuario
      Swal.fire({
        icon: data.icon,
        title: data.title,
        text: data.text
      });
    })
    .catch(err => {
      console.error(err);
      let data = err.response?.data || { icon: 'error', title: 'Error', text: 'Ha ocurrido un error.' };

      Swal.fire({
        icon: data.icon,
        title: data.title,
        text: data.text
      });
    })
    .finally(() => {
      $btn.prop('disabled', false);
    });
}
$('#modalParamComision').on('hidden.bs.modal', function () {
  const $sel = $('#categoria_cliente_id');
  $sel.empty().append('<option value="">Seleccione una categoría...</option>');

  $('#paramComisionForm').parsley().reset();
  $('#paramComisionForm')[0].reset();
  $('#param_comision_id').val('');
  $('#modalParamComision .modal-title').text('Parametrización de Comisión'); // volver a título original
});


$(document).on('submit', '#paramComisionForm', function (event) {
  event.preventDefault();
  registrarParametroComision();
});

listaParametroComision();

/*Listando los registros */
function listaParametroComision() {
  $('#tbl_listaParametroComision').DataTable({
    destroy: true,
    order: [0, 'desc'],
    language: { "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json" },
    pageLength: 5,
    responsive: true,
    deferRender: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [
            {
                extend: 'excel',
                title: 'Parametrizacion_comisiones'
            }
        ],
    ajax: {
      url: "/listar/parametros/comision",
      // Datatables::of(...)->make(true) devuelve {data:[...]}
      dataSrc: 'data',
      error: function () {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la tabla.' });
      }
    },
    columns: [
        { data: 'id' },
        { data: 'nombre' },
        { data: 'porcentaje_comision' },
        /* { data: 'rango_inicial' },
        { data: 'rango_final' }, */
        { data: 'rol' },
        { data: 'cliente_cat_escala' },
        { data: 'userRegistro' },
        { data: 'fechaRegistro' },
        { data: 'estado' },
        { data: 'opciones' }
    ]
  });
}

function desactivarCategoria(id, rol) {

    Swal.fire({
        title: "¿Estás seguro que deseas desactivar este parámetro?",
        html: `
            <b>Advertencia:</b><br>
            Al desactivar esta configuración,
            <span class="text-danger font-weight-bold">
                todos los usuarios asociados al rol ${rol} dejarán de recibir cualquier cálculo de comisiones.
            </span>
        `,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, desactivar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d"
    }).then((result) => {

        if (result.isConfirmed) {
            axios.post('/desactivar/parametro-comision/' + id)
                .then(response => {
                    Swal.fire({
                        icon: response.data.icon,
                        title: response.data.title,
                        text: response.data.text,
                    });

                    $('#tbl_listaParametroComision').DataTable().ajax.reload(null, false);
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "No se pudo desactivar el parámetro."
                    });
                });
        }

    });
}

function editarParametro(id) {

    $('#paramComisionForm').parsley().reset();

    axios.get('/parametro-comision/' + id)
        .then(response => {
            const p = response.data;

            // ID oculto
            $('#param_comision_id').val(p.id);

            // Campos normales
            $('#nombre_comescala').val(p.nombre);
            $('#descripcion_comescala').val(p.descripcion);
            $('#porcentaje_comision').val(p.porcentaje_comision);
            $('#rango_inicial_comescala').val(p.rango_inicial);
            $('#rango_final_comescala').val(p.rango_final);

            // Cargar selects con preselección
            cargarCategoriasClienteEnModal(p.cliente_categoria_escala_id);
            cargarRolesEnModal(p.rol_id);

            // Cambiar título del modal
            $('#modalParamComision .modal-title').text('Editar Parámetro de Comisión');

            // Mostrar modal
            $('#modalParamComision').modal('show');
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar la información.'
            });
        });
}




