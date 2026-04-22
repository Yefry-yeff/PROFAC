

// ID de categoría cliente a pre-seleccionar al abrir el modal (null = ninguno)
let pendingClienteCatId = null;

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
      // Si se abrió el modal desde una fila, pre-seleccionar esa categoría
      if (pendingClienteCatId) {
        $sel.val(pendingClienteCatId).trigger('change');
        pendingClienteCatId = null;
      } else {
        $sel.trigger('change');
      }
    })
    .fail(() => {
      Swal.fire({ icon:'error', title:'Error', text:'No se pudo cargar Categoría de Cliente.' });
    });
}

// Cargar SIEMPRE al abrir el modal
$('#modalCategoriasPrecios').on('shown.bs.modal', function () {
  cargarCategoriasClienteEnModal();
});

// Limpiar al cerrar
$('#modalCategoriasPrecios').on('hidden.bs.modal', function () {
  const $sel = $('#categoria_cliente_id');
  $sel.empty().append('<option value="">Seleccione una categoría...</option>');
});

// === Habilitar/Deshabilitar botón "Descargar" (MISMA lógica actual)
// Controla el estado del botón "Descargar" en función de que todos los filtros requeridos
// tengan un valor seleccionado. Si falta alguno, deshabilita el botón para evitar acciones inválidas.
function toggleDescargarCompleto() {
  const tipoPlantilla = $('#tipoPlantilla').val();
  const tipoCategoria = $('#tipoCategoria').val();
  const tipoFiltro    = $('#tipoFiltro').val();
  const lista         = $('#listaTipoFiltro').val();
  const catCliente    = $('#catClienteSelect').val();
  const catPrecios    = $('#listaTipoFiltroCatPrecios').val();

  let habilitado = false;
  if (tipoPlantilla === 'categoria') {
    habilitado = !!(tipoCategoria && tipoFiltro && lista && catCliente && catPrecios);
  } else if (tipoPlantilla === 'general') {
    habilitado = !!(tipoCategoria && tipoFiltro && lista);
  }
  $('#btnDescargar').prop('disabled', !habilitado);
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

  // === Inicialización de Select2 para todos los filtros del formulario de plantilla
  // Tema bootstrap4 + placeholder limpio + ancho 100% para homogeneidad.
  $('#tipoPlantilla').select2({
    theme: 'bootstrap4',
    placeholder: 'Seleccionar tipo de plantilla',
    allowClear: false,
    width: '100%'
  });

  $('#tipoCategoria').select2({
    theme: 'bootstrap4',
    placeholder: 'Tipo de categoría',
    allowClear: false,
    width: '100%'
  });

  $('#tipoFiltro').select2({
    theme: 'bootstrap4',
    placeholder: 'Filtrar por',
    allowClear: false,
    width: '100%'
  });

  $('#listaTipoFiltro').select2({
    theme: 'bootstrap4',
    placeholder: 'Seleccione una opción',
    allowClear: false,
    width: '100%',
    minimumResultsForSearch: 6
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

  // === Select2 - Categoría de cliente (carga estática + filtro en cliente)
  // Se inicializa primero el Select2 vacío, luego se puebla con todas las categorías.
  // Así Select2 hace el filtrado en el cliente al escribir — mucho más rápido y fiable.
  $('#catClienteSelect').select2({
    theme: 'bootstrap4',
    placeholder: 'Categoría de cliente',
    allowClear: false,
    width: '100%',
    minimumResultsForSearch: 0
  });

  // Cargar todas las categorías de cliente de una sola vez
  $.ajax({
    url: '/filtros/categoria/cliente',
    type: 'GET',
    dataType: 'json',
    success: function(data) {
      data.forEach(function(item) {
        $('#catClienteSelect').append(new Option(item.nombre, item.id, false, false));
      });
    }
  });

  // === Select2 - Categoría de precios (se puebla dinámicamente por JS)
  $('#listaTipoFiltroCatPrecios').select2({
    theme: 'bootstrap4',
    placeholder: 'Categoría de precio',
    allowClear: false,
    width: '100%',
    minimumResultsForSearch: 5
  });

  // === Select2 dentro del modal (categoría de cliente)
  // dropdownParent: $('body') → el dropdown se renderiza en body, fuera del overflow del modal.
  // z-index forzado vía inline style en el evento open (gana sobre cualquier CSS).
  $('#categoria_cliente_id').select2({
    theme: 'bootstrap4',
    placeholder: 'Buscar categoría...',
    allowClear: true,
    dropdownParent: $('body'),
    width: '100%',
    language: {
      noResults: function() { return 'No se encontraron resultados'; },
      searching: function() { return 'Buscando...'; }
    }
  });

  // Fix Bootstrap 4 + Select2: Bootstrap tiene un listener 'focusin' en document
  // que fuerza el foco de vuelta al modal cada vez que algo fuera de él lo toma.
  // removeAttr('tabindex') no es suficiente — hay que detener ese handler.
  $(document).on('focusin', function (e) {
    if ($(e.target).closest('.select2-container').length) {
      e.stopImmediatePropagation();
    }
  });

  $(document).on('select2:open', '#categoria_cliente_id', function () {
    setTimeout(function () {
      document.querySelectorAll('body > .select2-container').forEach(function (el) {
        el.style.setProperty('z-index', '99999', 'important');
      });
      var searchField = document.querySelector('body > .select2-container--open .select2-search__field');
      if (searchField) searchField.focus();
    }, 10);
  });

  // === Resetear formulario al cerrar el modal
  $('#modalCategoriasPrecios').on('hidden.bs.modal', function () {
    $('#CreacionCatPrecios')[0].reset();
    $('#categoria_cliente_id').val(null).trigger('change');
  });

  // === Listeners para mantener la lógica actual del botón "Descargar"
  // Recalcula el estado del botón al cambiar filtros críticos.
  $('#listaTipoFiltro, #tipoCategoria').on('change', toggleDescargarCompleto);
    toggleDescargarCompleto(); // estado inicial
  });

// Listeners globales fuera del DOM ready para cobertura total de cambios.
$('#tipoCategoria, #tipoFiltro, #listaTipoFiltro, #catClienteSelect, #listaTipoFiltroCatPrecios')
  .on('change', toggleDescargarCompleto);

// Estado inicial al cargar la página (seguridad extra si el DOM ready no alcanzó)
toggleDescargarCompleto();

// === Submit del modal (crear categoría de precios)
// Intercepta el submit nativo para manejarlo por AJAX.
$(document).on('submit', '#CreacionCatPrecios', function (event) {
  event.preventDefault();
  registrarCategoriaPrecios();
});

// === Lógica de creación de categoría de precios
// Envía el formulario del modal al backend y maneja la respuesta con feedback visual.
function registrarCategoriaPrecios() {
  const $btn = $('#btn_guardar_categoria');
  const cat = $('#categoria_cliente_id').val();
  const porcA = $('#porc_precio_a').val();

  if (!cat) {
    Swal.fire({ icon:'warning', title:'Falta categoría', text:'Seleccione una categoría de cliente.' });
    return;
  }

  if (porcA === '' || porcA === null) {
    Swal.fire({ icon:'warning', title:'Campo requerido', text:'El % Precio Venta es obligatorio.' });
    $('#porc_precio_a').focus();
    return;
  }

  var data = new FormData($('#CreacionCatPrecios').get(0));

  const htmlOriginal = $btn.html();
  $btn.prop('disabled', true)
      .html('<i class="fa fa-spinner fa-spin mr-1"></i>Guardando...');

  axios.post('/guardar/categoria/precios', data)
    .then(response => {
      let data = response.data;
      // Cerrar modal y limpiar estado del formulario/validaciones
      $('#modalCategoriasPrecios').modal('hide');
      $('#CreacionCatPrecios').parsley().reset();
      $('#CreacionCatPrecios')[0].reset();
      // Refrescar DataTable principal
      $('#tbl_listaCategoria').DataTable().ajax.reload();

      // Notificación al usuario
      Swal.fire({
        icon: data.icon,
        title: data.title,
        text: data.text
      });

      // Devolver foco al primer campo del modal (mejora de UX)
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
      $btn.prop('disabled', false).html(htmlOriginal);
    });
}

// === DataTable principal de categorías de precios
// Consume el endpoint /listar/categoria/precios y pinta columnas predefinidas.
// Maneja errores de red y configura idioma, paginación y responsividad.
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
      { data: 'total_cat' },
      { data: 'creacion' },
      { data: 'registro' },
      { data: 'opciones' }
    ]
  });

  // Click en fila: abrir modal con la categoría cliente pre-seleccionada
  // Se ignoran clics sobre el botón de Acciones para no interferir con el dropdown
  $('#tbl_listaCategoria tbody').off('click.rowopen').on('click.rowopen', 'tr', function (e) {
    if ($(e.target).closest('.btn-group, .dropdown-menu, .dropdown-item').length) return;
    const rowData = $('#tbl_listaCategoria').DataTable().row(this).data();
    if (!rowData) return;
    pendingClienteCatId = rowData.id;
    $('#modalCategoriasPrecios').modal('show');
  });
}

// === Desactivar categoría (se mantiene GET por compatibilidad)
// Llama al endpoint de desactivación y refresca la tabla al completar.
// Notifica al usuario del resultado (éxito o error).
function desactivarCategoria(idCategoria) {
  axios.get('/desactivar/categoria/cliente/' + idCategoria)
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

/*===================================================================================================================================*/
/*===================================================================================================================================*/
/*===================================================================================================================================*/
/* Subida de Excel de precios de productos */

// ================================
//  Estado global de la vista previa
//  (se usa para compartir datos entre funciones de preview)
// ================================
window.excelPreview = {
  rows: [],      // Array de objetos (filas del Excel parseado)
  headers: []    // Encabezados detectados automáticamente
};

// ======================================
//  Utilidad: destruir DataTable si existe
//  (evita fugas de memoria y conflictos de inicialización)
// ======================================
function destroyPreviewTable() {
  if ($.fn.DataTable.isDataTable('#previewExcel')) {
    $('#previewExcel').DataTable().clear().destroy();
  }
  $('#previewExcel thead').empty();
  $('#previewExcel tbody').empty();
}

// ======================================
//  Renderiza la vista previa con DataTables
//  - Construye encabezados desde las llaves del primer registro
//  - Inicializa DataTable con los datos parseados
//  - Habilita/deshabilita botones según corresponda
// ======================================
function renderPreviewTable(rows) {
  destroyPreviewTable();

  if (!rows || !rows.length) {
    $('#btnProcesar').prop('disabled', true);
    $('#btnLimpiarVista').prop('disabled', true);
    return;
  }

  // Encabezados a partir de las keys de la primera fila
  const headers = Object.keys(rows[0] || {});
  window.excelPreview.headers = headers;

  // Construir thead dinámico
  const theadHtml = '<tr>' + headers.map(h => `<th>${h}</th>`).join('') + '</tr>';
  $('#previewExcel thead').html(theadHtml);

  // Definir columnas para DataTables (data binding por key)
  const columns = headers.map(h => ({ title: h, data: h }));

  // Inicializar DataTable para la vista previa
  $('#previewExcel').DataTable({
    destroy: true,
    data: rows,
    columns: columns,
    pageLength: 25,
    responsive: true,
    language: { url: "/js/plugins/dataTables/i18n/Spanish.json" },
    deferRender: true
  });

  // Habilitar acciones asociadas a la vista previa
  $('#btnProcesar').prop('disabled', false);
  $('#btnLimpiarVista').prop('disabled', false);
}

// ======================================
//  Mostrar nombre del archivo elegido
//  - Actualiza la etiqueta del input file para feedback inmediato al usuario
// ======================================
$(document).on('change', '#archivo_excel', function () {
  const name = this.files?.[0]?.name || 'Elegí un archivo...';
  $(this).next('.custom-file-label').text(name);
});

// =====================================================
//  Submit: leer Excel y mostrar vista previa (no envía al backend)
//  - Valida tamaño y existencia del archivo
//  - Usa FileReader + XLSX para parsear la primera hoja
//  - Limita a 10k filas por rendimiento (opcional)
// =====================================================
$(document).on('submit', '#formSubirExcel', function (e) {
  e.preventDefault();

  const file = $('#archivo_excel')[0].files[0];
  if (!file) {
    return Swal.fire({ icon: 'warning', title: 'Archivo requerido', text: 'Seleccioná un archivo Excel.' });
  }
  if (file.size > 10 * 1024 * 1024) {
    return Swal.fire({ icon: 'warning', title: 'Archivo muy grande', text: 'Máximo 10 MB.' });
  }

  const reader = new FileReader();

  reader.onload = function (event) {
    try {
      const data = new Uint8Array(event.target.result);
      const workbook = XLSX.read(data, { type: 'array' });

      // Tomamos la PRIMERA hoja para la vista previa
      const firstSheet = workbook.SheetNames[0];
      const worksheet = workbook.Sheets[firstSheet];

      // Convertimos a JSON manteniendo celdas vacías (defval:null)
      let jsonData = XLSX.utils.sheet_to_json(worksheet, { defval: null });

      // Limitar a 10k filas por rendimiento (opcional)
      const MAX_ROWS = 10000;
      if (jsonData.length > MAX_ROWS) {
        jsonData = jsonData.slice(0, MAX_ROWS);
        Swal.fire({
          icon: 'info',
          title: 'Vista previa truncada',
          text: `Se muestran las primeras ${MAX_ROWS} filas por rendimiento.`
        });
      }

      // Guardar en estado global y renderizar tabla de vista previa
      window.excelPreview.rows = jsonData;
      renderPreviewTable(jsonData);

      Swal.fire({ icon: 'success', title: 'Excel cargado', text: 'Revisá la vista previa antes de procesar.' });
    } catch (err) {
      console.error(err);
      Swal.fire({ icon: 'error', title: 'Error de lectura', text: 'No se pudo leer el archivo. Verificá el formato.' });
    }
  };

  reader.onerror = function (error) {
    console.error(error);
    Swal.fire({ icon: 'error', title: 'Error de lectura', text: 'No se pudo leer el archivo.' });
  };

  reader.readAsArrayBuffer(file);
});

// ======================================
//  Botón: limpiar vista previa
//  - Restablece estado interno y UI
// ======================================
$('#btnLimpiarVista').on('click', function () {
  window.excelPreview.rows = [];
  window.excelPreview.headers = [];
  destroyPreviewTable();
  $('#btnProcesar').prop('disabled', true);
  $('#btnLimpiarVista').prop('disabled', true);
  $('#formSubirExcel')[0].reset();
  $('.custom-file-label[for="archivo_excel"]').text('Elegí un archivo...');
});

// ======================================
//  Botón: procesar (envía al backend)
//  - Valida filtros requeridos
//  - Envía archivo + metadatos vía FormData a /procesar-excel-precios
//  - Muestra feedback y refresca tabla principal
// ======================================
$('#btnProcesar').on('click', async function () {
  const file = $('#archivo_excel')[0].files[0];
  if (!file) return Swal.fire({icon:'warning',title:'Archivo requerido',text:'Seleccioná un Excel.'});

  const tipoCategoria    = $('#tipoCategoria').val();
  const tipoFiltro       = $('#tipoFiltro').val();
  const valorFiltro      = $('#listaTipoFiltro').val();
  const catClienteId     = $('#catClienteSelect').val();
  const categoriaPrecioId= $('#listaTipoFiltroCatPrecios').val();

  if (!(tipoCategoria && tipoFiltro && valorFiltro && categoriaPrecioId)) {
    return Swal.fire({ icon:'warning', title:'Faltan filtros', text:'Completá los filtros antes de procesar.' });
  }

  const fd = new FormData();
  fd.append('archivo_excel', file);
  fd.append('tipoCategoria', tipoCategoria);
  fd.append('tipoFiltro', tipoFiltro);
  fd.append('valorFiltro', valorFiltro);
  fd.append('categoriaPrecioId', categoriaPrecioId);
  if (catClienteId) fd.append('catClienteId', catClienteId);

  const $btn = $(this).prop('disabled', true).text('Procesando...');
  try {
    const res = await axios.post('/procesar-excel-precios', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
    const d = res.data || {};
    console.log('Stats import:', d.debug || {});

    $('#tbl_listaCategoria').DataTable().ajax.reload();
    Swal.fire({ icon: d.icon || 'success', title: d.title || 'Listo', text: d.text || 'Procesado.' }).then(() => location.reload());

  } catch (err) {
    const d = err.response?.data || {};
    console.error('Error:', d);
    Swal.fire({ icon: d.icon || 'error', title: d.title || 'Error', text: d.text || 'No se pudo procesar.' });
  } finally {
    $btn.prop('disabled', false).text('Procesar');
  }
});


// Índices sugeridos a nivel de base de datos para mejorar performance en consultas frecuentes:
// CREATE INDEX idx_ppc_cat_prod ON precios_producto_carga (categoria_precios_id, producto_id);
// CREATE INDEX idx_ppc_estado   ON precios_producto_carga (estado_id);

/*===================================================================================================================================*/
/* MODAL VER / EDITAR CATEGORÍAS DE PRECIO                                                                                           */
/*===================================================================================================================================*/

// ID de categoría cliente actualmente en el modal de lista
let currentClienteCatId   = null;
let currentClienteCatNombre = null;

function verCategoriasPrecio(clienteCatId, nombreCat) {
  currentClienteCatId      = clienteCatId;
  currentClienteCatNombre  = nombreCat;
  $('#subtitleVerCatPrecios').text('Categoría cliente: ' + nombreCat);
  $('#loadingVerCatPrecios').show();
  $('#wrapperVerCatPrecios').hide();
  $('#emptyCatPrecios').hide();
  $('#modalVerCatPrecios').removeClass('pf-hiding').modal('show');
  reloadCatPrecios();
}

// Animación de salida del modal
$(document).on('hide.bs.modal', '#modalVerCatPrecios', function (e) {
  const $modal = $(this);
  if ($modal.hasClass('pf-hiding')) return; // ya animando, dejar cerrar
  e.preventDefault();
  $modal.addClass('pf-hiding');
  setTimeout(function () {
    $modal.modal('hide');
  }, 180);
});

function reloadCatPrecios() {
  if (!currentClienteCatId) return;
  $('#loadingVerCatPrecios').show();
  $('#wrapperVerCatPrecios').hide();

  $.getJSON('/listar/categorias/precios/por-cliente/' + currentClienteCatId)
    .done(function (res) {
      const cats = res.categorias || [];
      const $tbody = $('#tbody_catPrecios_lista').empty();

      if (cats.length === 0) {
        $('#emptyCatPrecios').show();
      } else {
        $('#emptyCatPrecios').hide();
        cats.forEach(function (c) { $tbody.append(buildCatRow(c)); });
      }

      $('#loadingVerCatPrecios').hide();
      $('#wrapperVerCatPrecios').show();
    })
    .fail(function () {
      $('#loadingVerCatPrecios').hide();
      Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las categorías de precio.' });
    });
}

function descargarPreciosPorCliente() {
  if (!currentClienteCatId) return;

  const $btn = $('#btnExportarPreciosCat');
  const originalHtml = $btn.html();
  $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Generando...');

  const url = '/exportar/precios/por-cliente/' + currentClienteCatId;

  // Descarga directa vía enlace oculto
  const a = document.createElement('a');
  a.href = url;
  a.download = '';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);

  setTimeout(function () {
    $btn.prop('disabled', false).html(originalHtml);
  }, 3000);
}

function descargarPreciosPorCategoria(categoriaPrecioId) {
  if (!currentClienteCatId || !categoriaPrecioId) return;

  const url = '/exportar/precios/por-categoria/' + currentClienteCatId + '/' + categoriaPrecioId;

  const a = document.createElement('a');
  a.href = url;
  a.download = '';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
}

function buildCatRow(c) {
  const estado = c.estado_id == 1
    ? '<span class="badge badge-success" style="font-size:.72rem;padding:3px 8px;">ACTIVO</span>'
    : '<span class="badge badge-danger" style="font-size:.72rem;padding:3px 8px;">INACTIVO</span>';

  const acciones = c.estado_id == 1
    ? `<div class="dropdown cat-action-dropdown" style="display:inline-block;">
         <button class="dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
           <i class="fa fa-cog"></i> Acciones
         </button>
         <div class="dropdown-menu dropdown-menu-right">
           <a class="dropdown-item item-edit" href="#" onclick="activarEdicionFila(${c.id}); return false;">
             <i class="fa fa-pencil fa-fw"></i> Editar
           </a>
           <a class="dropdown-item item-excel" href="#" onclick="descargarPreciosPorCategoria(${c.id}); return false;">
             <i class="fa fa-file-excel-o fa-fw"></i> Exportar Excel
           </a>
           <div class="dropdown-divider"></div>
           <a class="dropdown-item item-deact" href="#" onclick="desactivarCatPrecioLista(${c.id}); return false;">
             <i class="fa fa-times fa-fw"></i> Desactivar
           </a>
         </div>
       </div>`
    : '<span class="text-muted small">—</span>';

  const fechaAct = c.fecha_ultima_actualizacion
    ? '<span style="font-size:.75rem;">' + c.fecha_ultima_actualizacion + '</span>'
    : '<span class="text-muted">\u2014</span>';
  const usuarioAct = c.nombre_actualizador
    ? '<span style="font-size:.75rem;"><i class="fa fa-user mr-1 text-secondary"></i>' + escapeHtml(c.nombre_actualizador) + '</span>'
    : '<span class="text-muted">\u2014</span>';

  return `<tr id="row_cat_${c.id}"
              data-id="${c.id}"
              data-nombre="${escapeHtml(c.nombre)}"
              data-a="${c.porc_precio_a}"
              data-b="${c.porc_precio_b || ''}"
              data-c="${c.porc_precio_c || ''}"
              data-d="${c.porc_precio_d || ''}">
    <td class="col-hide-xs">${c.id}</td>
    <td>${escapeHtml(c.nombre)}</td>
    <td class="text-center">${c.porc_precio_a}%</td>
    <td class="text-center col-hide-xs">${c.porc_precio_b ? c.porc_precio_b + '%' : '<span class="text-muted">\u2014</span>'}</td>
    <td class="text-center col-hide-xs">${c.porc_precio_c ? c.porc_precio_c + '%' : '<span class="text-muted">\u2014</span>'}</td>
    <td class="text-center col-hide-xs">${c.porc_precio_d ? c.porc_precio_d + '%' : '<span class="text-muted">\u2014</span>'}</td>
    <td class="text-center">${estado}</td>
    <td class="text-center col-hide-sm">${fechaAct}</td>
    <td class="text-center col-hide-sm">${usuarioAct}</td>
    <td class="text-center">${acciones}</td>
  </tr>`;
}

function escapeHtml(text) {
  if (!text) return '';
  return String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function activarEdicionFila(id) {
  const $row = $('#row_cat_' + id);
  const nombre = $row.data('nombre');
  const a = $row.data('a');
  const b = $row.data('b');
  const c = $row.data('c');
  const d = $row.data('d');

  $row.html(`
    <td class="col-hide-xs">${id}</td>
    <td>
      <input type="text" class="form-control edit-cat-input" id="edit_nombre_${id}"
             value="${escapeHtml(nombre)}" maxlength="100" required style="min-width:110px;">
    </td>
    <td>
      <input type="number" class="form-control edit-cat-input text-center" id="edit_a_${id}"
             value="${a}" min="0" max="100" step="0.01" required style="width:52px;">
    </td>
    <td class="col-hide-xs">
      <input type="number" class="form-control edit-cat-input text-center" id="edit_b_${id}"
             value="${b}" min="0" max="100" step="0.01" style="width:52px;">
    </td>
    <td class="col-hide-xs">
      <input type="number" class="form-control edit-cat-input text-center" id="edit_c_${id}"
             value="${c}" min="0" max="100" step="0.01" style="width:52px;">
    </td>
    <td class="col-hide-xs">
      <input type="number" class="form-control edit-cat-input text-center" id="edit_d_${id}"
             value="${d}" min="0" max="100" step="0.01" style="width:52px;">
    </td>
    <td></td>
    <td class="col-hide-sm"></td>
    <td class="text-center col-hide-sm"></td>
    <td class="text-center" style="white-space:nowrap;">
      <button class="btn-save-cat mr-1" onclick="guardarEdicionCat(${id})">
        <i class="fa fa-check mr-1"></i>Guardar
      </button>
      <button class="btn-cancel-cat" onclick="reloadCatPrecios()">
        <i class="fa fa-times mr-1"></i>Cancelar
      </button>
    </td>
  `);

  // Foco en el nombre para edición inmediata
  document.getElementById('edit_nombre_' + id).focus();
}

function guardarEdicionCat(id) {
  const nombre = $('#edit_nombre_' + id).val().trim();
  const a = $('#edit_a_' + id).val();

  if (!nombre) {
    Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre no puede estar vacío.' });
    return;
  }
  if (a === '' || a === null || a === undefined) {
    Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El % Precio A es obligatorio.' });
    return;
  }

  const payload = {
    id:           id,
    nombre:       nombre,
    porc_precio_a: a,
    porc_precio_b: $('#edit_b_' + id).val() || 0,
    porc_precio_c: $('#edit_c_' + id).val() || 0,
    porc_precio_d: $('#edit_d_' + id).val() || 0
  };

  // Spinner en el botón Guardar
  const $btnGuardar = $('button[onclick="guardarEdicionCat(' + id + ')"]');
  const htmlOriginal = $btnGuardar.html();
  $btnGuardar.prop('disabled', true)
             .html('<i class="fa fa-spinner fa-spin mr-1"></i>Guardando...');

  axios.post('/actualizar/categoria/precios', payload)
    .then(function (res) {
      const data = res.data;
      const count = data.productos_actualizados ?? 0;
      const htmlMsg = 'Categoría actualizada correctamente.' +
        '<br><span class="badge badge-success mt-2" style="font-size:.82rem;padding:4px 10px;">' +
        '<i class="fa fa-refresh mr-1"></i>' + count + ' producto' + (count !== 1 ? 's' : '') + ' recalculado' + (count !== 1 ? 's' : '') +
        '</span>';
      Swal.fire({
        icon: data.icon,
        title: data.title,
        html: htmlMsg,
        confirmButtonText: 'OK',
        confirmButtonColor: '#27ae60',
        customClass: { container: 'swal-sobre-modal' }
      });
      reloadCatPrecios();
      // Actualizar el contador en la tabla principal sin reiniciarla
      $('#tbl_listaCategoria').DataTable().ajax.reload(null, false);
    })
    .catch(function (err) {
      $btnGuardar.prop('disabled', false).html(htmlOriginal);
      const data = err.response?.data || { icon: 'error', title: 'Error', text: 'No se pudo actualizar.' };
      Swal.fire({ icon: data.icon, title: data.title, text: data.text });
    });
}

function desactivarCatPrecioLista(id) {
  Swal.fire({
    title: '¿Desactivar categoría de precio?',
    text: 'Se inactivarán todos los precios de productos asociados a esta categoría.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sí, desactivar',
    cancelButtonText: 'Cancelar'
  }).then(function (result) {
    if (result.isConfirmed) {
      axios.get('/desactivar/categoria/precios/' + id)
        .then(function (res) {
          const data = res.data;
          Swal.fire({ icon: data.icon, title: data.title, text: data.text, timer: 2200, showConfirmButton: false });
          reloadCatPrecios();
          $('#tbl_listaCategoria').DataTable().ajax.reload(null, false);
        })
        .catch(function (err) {
          const data = err.response?.data || { icon: 'error', title: 'Error', text: 'No se pudo desactivar.' };
          Swal.fire({ icon: data.icon, title: data.title, text: data.text });
        });
    }
  });
}
