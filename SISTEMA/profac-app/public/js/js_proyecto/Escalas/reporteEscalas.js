


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

// Variable global para almacenar la instancia de DataTable
let tablaProductos = null;

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
  
  // === Inicialización de la tabla de productos con lazy loading
  inicializarTablaProductos();

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
    
  // === Actualizar tabla cuando cambian los filtros
  $('#tipoFiltro, #listaTipoFiltro, #listaTipoFiltroCatPrecios').on('change', function() {
    actualizarTablaProductos();
  });
  
  // === Interceptar submit del formulario de descarga para asegurar que usa los filtros actuales
  $('#formExportFiltrado').on('submit', function(e) {
    // Asegurar que los valores actuales de los filtros se envíen
    $('#tipoFiltro').attr('name', 'tipoFiltro');
    $('#listaTipoFiltro').attr('name', 'listaTipoFiltro');
    $('#listaTipoFiltroCatPrecios').attr('name', 'listaTipoFiltroCatPrecios');
  });
  });

$('#tipoCategoria, #tipoFiltro, #listaTipoFiltro, #listaTipoFiltroCatPrecios')
  .on('change', toggleDescargarCompleto);

// Estado inicial al cargar la página (seguridad extra si el DOM ready no alcanzó)
toggleDescargarCompleto();


function listarCategorias() {
  $('#tbl_listaCategoria').DataTable({
    destroy: true,
    order: [0, 'desc'],
    language: { "url": "/js/plugins/dataTables/i18n/Spanish.json" },
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

// === Inicializar tabla de productos con lazy loading (server-side processing)
function inicializarTablaProductos() {
  tablaProductos = $('#tbl_productos').DataTable({
    processing: true,
    serverSide: true, // Activar procesamiento del lado del servidor (lazy loading)
    deferRender: true,
    language: {
      "url": "/js/plugins/dataTables/i18n/Spanish.json",
      "processing": '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Cargando...</span>'
    },
    ajax: {
      url: '/escalas/productos/filtrados',
      type: 'GET',
      data: function(d) {
        // Enviar parámetros de filtro
        d.tipoFiltro = $('#tipoFiltro').val();
        d.listaTipoFiltro = $('#listaTipoFiltro').val();
        d.listaTipoFiltroCatPrecios = $('#listaTipoFiltroCatPrecios').val();
        
        // Debug: ver qué valores se están enviando
        console.log('Filtros enviados:', {
          tipoFiltro: d.tipoFiltro,
          listaTipoFiltro: d.listaTipoFiltro,
          listaTipoFiltroCatPrecios: d.listaTipoFiltroCatPrecios
        });
      },
      error: function(xhr, error, thrown) {
        console.error('Error al cargar productos:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo cargar la tabla de productos.'
        });
      }
    },
    columns: [
      { data: 'id', width: '50px' },
      { data: 'categoria_cliente', width: '150px' },
      { data: 'codigo', width: '100px' },
      { data: 'producto' },
      { data: 'marca' },
      { data: 'categoria' },
      { data: 'escala_precio' },
      { data: 'precio_A_formatted', className: 'text-right' },
      { data: 'precio_B_formatted', className: 'text-right' },
      { data: 'precio_C_formatted', className: 'text-right' },
      { data: 'precio_D_formatted', className: 'text-right' }
    ],
    order: [[0, 'desc']],
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
    responsive: true,
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
    drawCallback: function() {
      // Callback después de cada dibujado de la tabla
      console.log('Tabla de productos actualizada');
    }
  });
}

// === Actualizar tabla de productos cuando cambian los filtros
function actualizarTablaProductos() {
  if (tablaProductos) {
    // Recargar datos con los nuevos filtros
    tablaProductos.ajax.reload(null, false); // false para mantener la paginación actual
  }
}





// Índices sugeridos a nivel de base de datos para mejorar performance en consultas frecuentes:
// CREATE INDEX idx_ppc_cat_prod ON precios_producto_carga (categoria_precios_id, producto_id);
// CREATE INDEX idx_ppc_estado   ON precios_producto_carga (estado_id);
