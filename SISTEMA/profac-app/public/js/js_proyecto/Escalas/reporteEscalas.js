


// === Habilitar/Deshabilitar botón "Descargar" (MISMA lógica actual)
// Controla el estado del botón "Descargar" en función de que todos los filtros requeridos
// tengan un valor seleccionado. Si falta alguno, deshabilita el botón para evitar acciones inválidas.
function toggleDescargarCompleto() {
  const tipoCategoria = $('#tipoCategoria').val();
  const tipoFiltro = $('#tipoFiltro').val();
  const lista = $('#listaTipoFiltro').val();
  const catPrecios = $('#listaTipoFiltroCatPrecios').val();

  const habilitado = !!(tipoCategoria && tipoFiltro && lista && catPrecios);
  //$('#btnDescargar').prop('disabled', !habilitado);
}

$(document).ready(function () {
  // Configuración básica para Axios:
  // - Define header X-Requested-With para solicitudes AJAX.
  // - Inyecta token CSRF (si existe en <meta>) para proteger contra ataques CSRF.
  if (typeof axios !== 'undefined') {
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrf) axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
  }

  // === Inicialización de la tabla principal de categorías (DataTable)
  listarCategorias();

  // === Inicialización de Select2 para los filtros superiores
  // Mejora UX con tema bootstrap4 y placeholders.
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

  // === Cargar opciones dinámicas de #listaTipoFiltro según el valor de #tipoFiltro
  // Si el usuario elige filtrar por Marca (1) o Categoría (2), se consulta el endpoint correspondiente
  // y se pobla el select con los resultados.
  $('#tipoFiltro').on('change', function () {
    let tipo = $(this).val();
    let $listaTipo = $('#listaTipoFiltro');

    // Limpieza del select dependiente
    $listaTipo.val(null).trigger('change');
    $listaTipo.empty();

    // Si no hay tipo definido, sólo recalcula estado del botón y sale.
    if (!tipo) {
      toggleDescargarCompleto();
      return;
    }

    // Selección del endpoint según tipo de filtro
    let url = tipo == '1' ? '/filtros/marca' : '/filtros/categoria';

    // Solicitud AJAX para poblar el select
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
        toggleDescargarCompleto(); // mantener lógica actual del botón
      },
      error: function () {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el listado.' });
      }
    });
  });

  // === Select2 - Categoría de precios (con carga AJAX)
  // Trae categorías de precio desde el servidor con búsqueda diferida (delay).
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

  $('#listaTipoFiltro, #tipoCategoria').on('change', toggleDescargarCompleto);
    toggleDescargarCompleto(); // estado inicial
  });

$('#tipoCategoria, #tipoFiltro, #listaTipoFiltro, #listaTipoFiltroCatPrecios')
  .on('change', toggleDescargarCompleto);

// Estado inicial al cargar la página (seguridad extra si el DOM ready no alcanzó)
toggleDescargarCompleto();


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





// Índices sugeridos a nivel de base de datos para mejorar performance en consultas frecuentes:
// CREATE INDEX idx_ppc_cat_prod ON precios_producto_carga (categoria_precios_id, producto_id);
// CREATE INDEX idx_ppc_estado   ON precios_producto_carga (estado_id);
