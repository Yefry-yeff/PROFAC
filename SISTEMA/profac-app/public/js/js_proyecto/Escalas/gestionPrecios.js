

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

// Cargar SIEMPRE al abrir el modal
$('#modalCategoriasPrecios').on('shown.bs.modal', function () {
  cargarCategoriasClienteEnModal();
});

// (opcional) limpiar al cerrar
$('#modalCategoriasPrecios').on('hidden.bs.modal', function () {
  const $sel = $('#categoria_cliente_id');
  $sel.empty().append('<option value="">Seleccione una categoría...</option>');
});

// === Habilitar/Deshabilitar botón "Descargar" (MISMA lógica actual)
// Controla el estado del botón "Descargar" en función de que todos los filtros requeridos
// tengan un valor seleccionado. Si falta alguno, deshabilita el botón para evitar acciones inválidas.
function toggleDescargarCompleto() {
  const tipoCategoria = $('#tipoCategoria').val();
  const tipoFiltro = $('#tipoFiltro').val();
  const lista = $('#listaTipoFiltro').val();
  const catPrecios = $('#listaTipoFiltroCatPrecios').val();

  const habilitado = !!(tipoCategoria && tipoFiltro && lista && catPrecios);
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

  // === Select2 dentro del modal (categoría de cliente)
  // Se especifica dropdownParent para asegurar el correcto renderizado dentro del modal.
  /* $('#categoria_cliente_id').select2({
    theme: 'bootstrap4',
    placeholder: 'Seleccione una categoría...',
    allowClear: true,
    minimumResultsForSearch: 0,
    dropdownParent: $('#modalCategoriasPrecios')
  }); */

  // === Resetear formulario al cerrar el modal
  // Evita que queden valores anteriores al reabrir el modal.
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
$('#tipoCategoria, #tipoFiltro, #listaTipoFiltro, #listaTipoFiltroCatPrecios')
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

  if (!cat) {
    Swal.fire({ icon:'warning', title:'Falta categoría', text:'Seleccione una categoría de cliente.' });
    return;
  }

  var data = new FormData($('#CreacionCatPrecios').get(0));

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
      $btn.prop('disabled', false);
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
// Llama al endpoint de desactivación y refresca la tabla al completar.
// Notifica al usuario del resultado (éxito o error).
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
  const categoriaPrecioId= $('#listaTipoFiltroCatPrecios').val();

  if (!(tipoCategoria && tipoFiltro && valorFiltro && categoriaPrecioId)) {
    return Swal.fire({ icon:'warning', title:'Faltan filtros', text:'Completá los 4 filtros antes de procesar.' });
  }

  const fd = new FormData();
  fd.append('archivo_excel', file);
  fd.append('tipoCategoria', tipoCategoria);
  fd.append('tipoFiltro', tipoFiltro);
  fd.append('valorFiltro', valorFiltro);
  fd.append('categoriaPrecioId', categoriaPrecioId);

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
<<<<<<< HEAD
=======

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

  // Botón compacto → abre mini modal de comisiones
  var cnt = (c.comisiones || []).length;
  var comisionesHtml = cnt > 0
    ? '<button class="btn-mc-ver" onclick="abrirModalComisiones(' + c.id + ', false); event.stopPropagation();"><i class="fa fa-percent"></i> ' + cnt + ' roles</button>'
    : '<span class="btn-mc-ver-sin"><i class="fa fa-percent"></i> Sin config.</span>';

  // Serializar comisiones para data-attribute
  var comisionesData = JSON.stringify(c.comisiones || []).replace(/"/g, '&quot;');

  return `<tr id="row_cat_${c.id}"
              data-id="${c.id}"
              data-nombre="${escapeHtml(c.nombre)}"
              data-a="${c.porc_precio_a}"
              data-b="${c.porc_precio_b || ''}"
              data-c="${c.porc_precio_c || ''}"
              data-d="${c.porc_precio_d || ''}"
              data-comisiones="${comisionesData}">
    <td class="col-hide-xs">${c.id}</td>
    <td>${escapeHtml(c.nombre)}</td>
    <td class="text-center">${c.porc_precio_a}%</td>
    <td class="text-center col-hide-xs">${c.porc_precio_b ? c.porc_precio_b + '%' : '<span class="text-muted">\u2014</span>'}</td>
    <td class="text-center col-hide-xs">${c.porc_precio_c ? c.porc_precio_c + '%' : '<span class="text-muted">\u2014</span>'}</td>
    <td class="text-center col-hide-xs">${c.porc_precio_d ? c.porc_precio_d + '%' : '<span class="text-muted">\u2014</span>'}</td>
    <td class="text-center" style="max-width:180px;">${comisionesHtml}</td>
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

/* ════════════════════════════════════════════════════════
   MINI MODAL % COMISIONES POR ROL
   ════════════════════════════════════════════════════════ */
window._pfComisionesEditadas = {};
var _pfMCCatId = null;

function abrirModalComisiones(catId, modoEdicion) {
  var $row = $('#row_cat_' + catId);
  var nombre    = $row.data('nombre') || ('Categoría ' + catId);
  var comisiones = $row.data('comisiones') || [];

  _pfMCCatId = catId;

  // Si ya hay ediciones pendientes para este cat, usarlas
  var items = (window._pfComisionesEditadas[catId] && window._pfComisionesEditadas[catId].length > 0)
    ? window._pfComisionesEditadas[catId]
    : comisiones;

  // Header
  $('#mc-modal-subtitle').html(
    '<strong>' + escapeHtml(nombre) + '</strong>'
    + ' <span style="opacity:.7;margin-left:6px;">' + items.length + ' rol' + (items.length !== 1 ? 'es' : '') + ' configurado' + (items.length !== 1 ? 's' : '') + '</span>'
  );

  // Limpiar buscador y tabla
  $('#mc-buscador').val('');
  $('#mc-no-result').hide();
  var $tbody = $('#mc-tbody');
  $tbody.empty();

  if (items.length === 0) {
    $tbody.append('<tr><td colspan="2" class="text-center py-3" style="color:#999;font-size:.82rem;">Sin comisiones configuradas para esta categoría.</td></tr>');
    $('#mc-btn-aplicar').hide();
  } else {
    items.forEach(function (cm) {
      var porc = parseFloat(cm.porcentaje_comision);
      var celdaPorc = modoEdicion
        ? '<div style="display:inline-flex;align-items:center;gap:6px;">'
            + '<input type="number" class="mc-pct-input" '
            + 'data-escala-id="' + cm.escala_id + '" '
            + 'value="' + porc + '" min="0" max="100" step="0.01">'
            + '<span style="font-size:.72rem;color:#777;">%</span></div>'
        : '<span class="mc-pct-badge">' + porc + '%</span>';

      $tbody.append(
        '<tr data-rol="' + escapeHtml((cm.rol_nombre || '').toLowerCase()) + '" data-escala-id="' + cm.escala_id + '">'
        + '<td style="font-size:.82rem;">' + escapeHtml(cm.rol_nombre) + '</td>'
        + '<td class="text-center">' + celdaPorc + '</td>'
        + '</tr>'
      );
    });
    $('#mc-btn-aplicar').toggle(modoEdicion);
    $('#mc-btn-editar').toggle(!modoEdicion);
  }

  // Abrir overlay custom (siempre encima de todo)
  document.getElementById('mcOverlay').style.display = 'flex';
  // Bloquear el focus-trap de Bootstrap para permitir edición en el overlay
  $(document).off('focusin.modal').on('focusin.mcOverlay', function (e) {
    if ($('#mcOverlay').is(':visible') && !$(e.target).closest('#mcOverlay').length) {
      e.stopImmediatePropagation();
    }
  });
  setTimeout(function () { document.getElementById('mc-buscador').focus(); }, 80);
}

function cerrarModalComisiones() {
  document.getElementById('mcOverlay').style.display = 'none';
  // Restaurar el focus-trap de Bootstrap
  $(document).off('focusin.mcOverlay');
}
function cerrarMCIfBg(e) {
  if (e.target === document.getElementById('mcOverlay')) cerrarModalComisiones();
}

function activarEdicionMC() {
  // Reemplaza cada badge por un input numérico editable
  $('#mc-tbody tr[data-rol]').each(function () {
    var $td = $(this).find('td:last-child');
    var badge = $td.find('.mc-pct-badge');
    if (badge.length) {
      var val = badge.text().replace('%', '').trim();
      var escalId = $(this).data('escala-id');
      $td.html(
        '<div style="display:inline-flex;align-items:center;gap:6px;">'
        + '<input type="number" class="mc-pct-input" data-escala-id="' + escalId + '" '
        + 'value="' + val + '" min="0" max="100" step="0.01">'
        + '<span style="font-size:.72rem;color:#777;">%</span></div>'
      );
    }
  });
  $('#mc-btn-editar').hide();
  $('#mc-btn-aplicar').show();
  // Foco en el primer input
  $('#mc-tbody .mc-pct-input').first().focus().select();
}

function filtrarModalComisiones(val) {
  var query = val.toLowerCase().trim();
  var visible = 0;
  $('#mc-tbody tr[data-rol]').each(function () {
    var rol = $(this).data('rol') || '';
    var show = !query || rol.indexOf(query) !== -1;
    $(this).toggle(show);
    if (show) visible++;
  });
  $('#mc-no-result').toggle(visible === 0 && query.length > 0);
}

function aplicarComisionesModal() {
  if (!_pfMCCatId) return;

  // Recoger valores actuales de los inputs
  var items = [];
  $('#mc-tbody tr[data-rol]').each(function () {
    var $input = $(this).find('.mc-pct-input');
    if ($input.length) {
      var escalId = parseInt($input.data('escala-id'));
      var pct     = parseFloat($input.val());
      var rolNom  = $(this).find('td:first-child').text().trim();
      if (escalId && !isNaN(pct)) {
        items.push({ escala_id: escalId, porcentaje_comision: pct, rol_nombre: rolNom });
      }
    }
  });

  if (items.length === 0) {
    cerrarModalComisiones();
    $('#modalVerCatPrecios').addClass('pf-hiding').modal('hide');
    Swal.fire({ icon: 'warning', title: 'Sin cambios', text: 'No hay comisiones para aplicar.', timer: 1800, showConfirmButton: false });
    return;
  }

  // Comparar con valores originales para detectar cambios reales
  var originales = ($('#row_cat_' + _pfMCCatId).data('comisiones') || []);
  var origMap = {};
  originales.forEach(function (o) { origMap[o.escala_id] = parseFloat(o.porcentaje_comision); });

  var cambios = items.filter(function (it) {
    return origMap[it.escala_id] === undefined || origMap[it.escala_id] !== it.porcentaje_comision;
  });

  // Construir lista HTML de cambios para el confirm
  var listHtml = cambios.length > 0
    ? '<div style="max-height:200px;overflow-y:auto;margin-top:8px;">'
      + '<table style="width:100%;font-size:.82rem;border-collapse:collapse;">'
      + '<thead><tr style="background:#f0f7f1;">'
      + '<th style="padding:5px 8px;text-align:left;border-bottom:1px solid #c8e6c9;">Rol</th>'
      + '<th style="padding:5px 8px;text-align:center;border-bottom:1px solid #c8e6c9;">Antes</th>'
      + '<th style="padding:5px 8px;text-align:center;border-bottom:1px solid #c8e6c9;">Ahora</th>'
      + '</tr></thead><tbody>'
      + cambios.map(function (it) {
          var antes = origMap[it.escala_id] !== undefined ? origMap[it.escala_id] + '%' : '—';
          return '<tr><td style="padding:4px 8px;">' + escapeHtml(it.rol_nombre) + '</td>'
            + '<td style="padding:4px 8px;text-align:center;color:#999;">' + antes + '</td>'
            + '<td style="padding:4px 8px;text-align:center;font-weight:700;color:#1b5e20;">' + it.porcentaje_comision + '%</td></tr>';
        }).join('')
      + '</tbody></table></div>'
    : '<p style="color:#888;font-size:.83rem;margin-top:6px;">No se detectaron cambios respecto a los valores actuales.</p>';

  // Guardar antes de cerrar
  var catIdGuardar = _pfMCCatId;
  var itemsParaGuardar = items;
  var cambiosParaGuardar = cambios;

  var titulo = cambiosParaGuardar.length > 0
    ? 'Confirmar ' + cambiosParaGuardar.length + ' cambio' + (cambiosParaGuardar.length !== 1 ? 's' : '')
    : 'Sin cambios detectados';

  // Cerrar overlay y modal COMPLETAMENTE antes de Swal
  cerrarModalComisiones();
  var $bsModal = $('#modalVerCatPrecios');
  if ($bsModal.hasClass('show')) {
    $bsModal.one('hidden.bs.modal', function () {
      _mostrarSwalComisiones(titulo, listHtml, cambiosParaGuardar, catIdGuardar, itemsParaGuardar);
    });
    $bsModal.addClass('pf-hiding').modal('hide');
  } else {
    _mostrarSwalComisiones(titulo, listHtml, cambiosParaGuardar, catIdGuardar, itemsParaGuardar);
  }
}

function _mostrarSwalComisiones(titulo, listHtml, cambiosParaGuardar, catIdGuardar, itemsParaGuardar) {
  Swal.fire({
    icon: cambiosParaGuardar.length > 0 ? 'question' : 'info',
    title: titulo,
    html: '<p style="font-size:.85rem;margin:0;">Se modificará el % de comisión para los siguientes roles:</p>' + listHtml,
    showCancelButton: cambiosParaGuardar.length > 0,
    confirmButtonText: cambiosParaGuardar.length > 0 ? '<i class="fa fa-check mr-1"></i>Sí, aplicar' : 'Cerrar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#27ae60',
    cancelButtonColor: '#6c757d',
    customClass: { htmlContainer: 'text-left' }
  }).then(function (result) {
    if (!result.isConfirmed || cambiosParaGuardar.length === 0) return;

    // Enviar al servidor
    axios.post('/actualizar/comision/cat-precio', {
      cat_precio_id: catIdGuardar,
      comisiones: itemsParaGuardar.map(function (it) {
        return { escala_id: it.escala_id, porcentaje_comision: it.porcentaje_comision };
      })
    }).then(function (response) {
      // Actualizar data-comisiones en la fila
      var $row = $('#row_cat_' + catIdGuardar);
      var comisionesActuales = $row.data('comisiones') || [];
      var mapNuevos = {};
      itemsParaGuardar.forEach(function (it) { mapNuevos[it.escala_id] = it.porcentaje_comision; });
      comisionesActuales.forEach(function (c) {
        if (mapNuevos[c.escala_id] !== undefined) c.porcentaje_comision = mapNuevos[c.escala_id];
      });
      $row.data('comisiones', comisionesActuales);

      Swal.fire({
        icon: 'success',
        title: '¡Comisiones actualizadas!',
        html: '<b>' + cambiosParaGuardar.length + ' cambio' + (cambiosParaGuardar.length !== 1 ? 's' : '') + '</b> guardado' + (cambiosParaGuardar.length !== 1 ? 's' : '') + ' correctamente.',
        confirmButtonText: 'OK',
        confirmButtonColor: '#27ae60',
        timer: 3500,
        timerProgressBar: true
      });
    }).catch(function (err) {
      console.error(err);
      Swal.fire({ icon: 'error', title: 'Error al guardar', text: 'No se pudieron guardar los cambios. Inténtalo de nuevo.' });
    });
  });
}

function activarEdicionFila(id) {
  const $row = $('#row_cat_' + id);
  const nombre    = $row.data('nombre');
  const a         = $row.data('a');
  const b         = $row.data('b');
  const c         = $row.data('c');
  const d         = $row.data('d');
  const comisiones = $row.data('comisiones') || [];

  // Botón para abrir mini modal en modo edición
  var cntEdit = comisiones.length;
  var comisionInputsHtml = cntEdit > 0
    ? '<button class="btn-mc-ver-edit" onclick="abrirModalComisiones(' + id + ', true); event.stopPropagation();"><i class="fa fa-pencil"></i> Comisiones (' + cntEdit + ')</button>'
    : '<span class="btn-mc-ver-sin"><i class="fa fa-percent"></i> Sin config.</span>';

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
    <td style="min-width:140px;">${comisionInputsHtml}</td>
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

  // Recoger comisiones editadas en el mini modal (almacenadas en memoria temporal)
  var comisionItems = window._pfComisionesEditadas ? (window._pfComisionesEditadas[id] || []) : [];
  if (window._pfComisionesEditadas) delete window._pfComisionesEditadas[id];

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

  // Guardar precios y comisiones en paralelo
  var promPrecios = axios.post('/actualizar/categoria/precios', payload);
  var promComisiones = comisionItems.length > 0
    ? axios.post('/actualizar/comision/cat-precio', { cat_precio_id: id, comisiones: comisionItems })
    : Promise.resolve(null);

  axios.all([promPrecios, promComisiones])
    .then(axios.spread(function (resPrecios) {
      const data = resPrecios.data;
      const count = data.productos_actualizados ?? 0;
      const htmlMsg = 'Categoría actualizada correctamente.' +
        '<br><span class="badge badge-success mt-2" style="font-size:.82rem;padding:4px 10px;">' +
        '<i class="fa fa-refresh mr-1"></i>' + count + ' producto' + (count !== 1 ? 's' : '') + ' recalculado' + (count !== 1 ? 's' : '') +
        '</span>' +
        (comisionItems.length > 0 ? '<br><span class="badge badge-info mt-1" style="font-size:.78rem;padding:4px 10px;"><i class="fa fa-percent mr-1"></i>Comisiones actualizadas</span>' : '');
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
    }))
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
>>>>>>> origin/Union_Flujo_comisiones
