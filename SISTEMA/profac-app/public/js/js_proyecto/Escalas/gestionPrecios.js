// === Habilitar/Deshabilitar botón "Descargar" (MISMA lógica actual)
function toggleDescargarCompleto() {
  const tipoCategoria = $('#tipoCategoria').val();
  const tipoFiltro = $('#tipoFiltro').val();
  const lista = $('#listaTipoFiltro').val();
  const catPrecios = $('#listaTipoFiltroCatPrecios').val();

  const habilitado = !!(tipoCategoria && tipoFiltro && lista && catPrecios);
  $('#btnDescargar').prop('disabled', !habilitado);
}

$(document).ready(function () {
  // Axios headers básicos (seguro y no invasivo)
  if (typeof axios !== 'undefined') {
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrf) axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
  }

  // === Tabla
  listarCategorias();

  // === Select2 - Filtros superiores
  $('#tipoCategoria').select2({
    theme: 'bootstrap4',
    placeholder: '🧾 Tipo de categoría',
    width: 'resolve'
  });

  $('#tipoFiltro').select2({
    theme: 'bootstrap4',
    placeholder: '📂 Tipo de filtro',
    width: 'resolve'
  });

  $('#listaTipoFiltro').select2({
    theme: 'bootstrap4',
    placeholder: 'Seleccione una opción',
    width: 'resolve'
  });

  // === Cargar opciones de #listaTipoFiltro según #tipoFiltro
  $('#tipoFiltro').on('change', function () {
    let tipo = $(this).val();
    let $listaTipo = $('#listaTipoFiltro');

    $listaTipo.val(null).trigger('change');
    $listaTipo.empty();

    if (!tipo) {
      toggleDescargarActual();
      return;
    }

    let url = tipo == '1' ? '/filtros/marca' : '/filtros/categoria';

    $.ajax({
      url: url,
      type: 'GET',
      dataType: 'json',
      success: function (data) {
        $listaTipo.append(new Option('Seleccione', '', false, false));
        data.forEach(function (item) {
          $listaTipo.append(new Option(item.nombre, item.id, false, false));
        });
        $listaTipo.trigger('change');
        toggleDescargarActual(); // mantener lógica actual del botón
      },
      error: function () {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el listado.' });
      }
    });
  });

  // === Select2 - Categoría de precios (ajax)
  $('#listaTipoFiltroCatPrecios').select2({
    theme: 'bootstrap4',
    placeholder: 'Seleccione Categoría de precio',
    allowClear: true,
    ajax: {
      url: '/filtros/categoria/precios',
      dataType: 'json',
      delay: 250,
      processResults: function (data) {
        return {
          results: data.map(function (item) {
            return { id: item.id, text: item.nombre };
          })
        };
      },
      cache: true
    }
  });

  // === Select2 dentro del modal
  $('#categoria_cliente_id').select2({
    theme: 'bootstrap4',
    placeholder: 'Seleccione una categoría...',
    allowClear: true,
    minimumResultsForSearch: 0,
    dropdownParent: $('#modalCategoriasPrecios')
  });
$('#categoria_cliente_id').val(id).trigger('change');
  // === Resetear form al cerrar modal
  $('#modalCategoriasPrecios').on('hidden.bs.modal', function () {
    $('#CreacionCatPrecios')[0].reset();
    $('#categoria_cliente_id').val(null).trigger('change');
  });

  // === Listeners para mantener la lógica actual del botón
  $('#listaTipoFiltro, #tipoCategoria').on('change', toggleDescargarActual);
    toggleDescargarActual(); // estado inicial
  });
$('#tipoCategoria, #tipoFiltro, #listaTipoFiltro, #listaTipoFiltroCatPrecios')
  .on('change', toggleDescargarCompleto);

// Estado inicial al cargar la página
toggleDescargarCompleto();
// === Submit del modal (crear categoría de precios)
$(document).on('submit', '#CreacionCatPrecios', function (event) {
  event.preventDefault();
  registrarCategoriaPrecios();
});

function registrarCategoriaPrecios() {
  const $btn = $('#btn_guardar_categoria');
  $btn.prop('disabled', true);

  var data = new FormData($('#CreacionCatPrecios').get(0));

  axios.post('/guardar/categoria/precios', data)
    .then(response => {
      let data = response.data;
      $('#modalCategoriasPrecios').modal('hide');
      $('#CreacionCatPrecios').parsley().reset();
      $('#CreacionCatPrecios')[0].reset();
      $('#tbl_listaCategoria').DataTable().ajax.reload();

      Swal.fire({
        icon: data.icon,
        title: data.title,
        text: data.text
      });

      // foco en primer input
      $('#nombre_cat_precio').focus();
    })
    .catch(err => {
      console.error(err);
      let data = err.response?.data || { icon: 'error', title: 'Error', text: 'Ha ocurrido un error.' };
      $('#modalCategoriasPrecios').modal('hide');

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

// === DataTable
function listarCategorias() {
  $('#tbl_listaCategoria').DataTable({
    destroy: true,
    order: [0, 'desc'],
    language: { "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json" },
    pageLength: 5,
    responsive: true,
    deferRender: true,
    ajax: {
      url: "/listar/categoria/precios",
      // Datatables::of(...)->make(true) devuelve {data:[...]}
      dataSrc: 'data',
      error: function () {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la tabla.' });
      }
    },
    columns: [
      { data: 'id' },
      { data: 'categoria' },
      { data: 'estado' },
      { data: 'categoriaCliente' },
      { data: 'porc_a' },
      { data: 'porc_b' },
      { data: 'porc_c' },
      { data: 'porc_d' },
      { data: 'creacion' },
      { data: 'registro' },
      { data: 'opciones' }
    ]
  });
}

// === Desactivar categoría (se mantiene GET por compatibilidad)
function desactivarCategoria(idCategoria) {
  axios.get('/desactivar/categoria/precios/' + idCategoria)
    .then(response => {
      let data = response.data;
      Swal.fire({
        icon: data.icon,
        title: data.title,
        text: data.text
      });
      $('#tbl_listaCategoria').DataTable().ajax.reload();
    })
    .catch(err => {
      console.error(err);
      let data = err.response?.data || { icon: 'error', title: 'Error', text: 'No se pudo desactivar.' };
      Swal.fire({
        icon: data.icon,
        title: data.title,
        text: data.text
      });
    });
}
