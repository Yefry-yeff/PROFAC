

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

  if (!$('#modalSeleccionFiltrosProductos').length) {
    $('#listaTipoFiltro').select2({
      theme: 'bootstrap4',
      placeholder: 'Seleccione una opción',
      width: 'resolve'
    });
  }

  // === Cargar opciones dinámicas de #listaTipoFiltro según el valor de #tipoFiltro
  // Si el usuario elige filtrar por Marca (1) o Categoría (2), se consulta el endpoint correspondiente
  // y se pobla el select con los resultados.
  $('#tipoFiltro').on('change', function () {
    if ($('#modalSeleccionFiltrosProductos').length) return;
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
    allowClear: true
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
      { data: 'total_cat' },
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

/* ===================================================================
   MODAL: VER CATEGORÍAS DE PRECIO POR CATEGORÍA CLIENTE
   =================================================================== */

let _catClienteIdActivo  = null;
let _catClienteNomActivo = null;

function verCategoriasPrecio(id, nombre) {
  _catClienteIdActivo  = id;
  _catClienteNomActivo = nombre;

  $('#subtitleVerCatPrecios').text('Categoría cliente: ' + nombre);
  $('#loadingVerCatPrecios').show();
  $('#wrapperVerCatPrecios').hide();
  $('#emptyCatPrecios').hide();
  $('#modalVerCatPrecios').modal('show');

  axios.get('/listar/categorias/precios/por-cliente/' + id)
    .then(function (response) {
      var cats = response.data.categorias || [];
      var $tbody = $('#tbody_catPrecios_lista');
      $tbody.empty();

      if (!cats.length) {
        $('#loadingVerCatPrecios').hide();
        $('#wrapperVerCatPrecios').show();
        $('#emptyCatPrecios').show();
        return;
      }

      cats.forEach(function (cat) {
        var estado = cat.estado_id == 1
          ? '<span class="badge badge-success" style="font-size:.73rem;padding:3px 8px;">Activo</span>'
          : '<span class="badge badge-secondary" style="font-size:.73rem;padding:3px 8px;">Inactivo</span>';

        var ultAct = cat.fecha_ultima_actualizacion
          ? String(cat.fecha_ultima_actualizacion).substring(0, 10)
          : (cat.created_at ? String(cat.created_at).substring(0, 10) : '—');

        var actualizador  = cat.nombre_actualizador || '—';
        var comisiones    = cat.comisiones || [];
        var numCom        = comisiones.length;
        var comisionesB64 = btoa(unescape(encodeURIComponent(JSON.stringify(comisiones))));
        var nomEsc        = (cat.nombre || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");

        var btnComision = '<button class="btn-edit-cat" style="font-size:.7rem;" '
          + 'onclick="abrirModalComisiones(' + cat.id + ',\'' + nomEsc + '\',\'' + comisionesB64 + '\')">'
          + '<i class="fa fa-percent mr-1"></i>'
          + (numCom > 0 ? numCom + ' rol' + (numCom > 1 ? 'es' : '') : 'Sin comisiones')
          + '</button>';

var estadoBtn = cat.estado_id == 1
          ? '<li class="dropdown-divider"></li>'
            + '<li><a class="dropdown-item item-deact" onclick="desactivarCatPrecioEnModal(' + cat.id + ')">'  
            + '<i class="fa fa-ban mr-1"></i>Desactivar</a></li>'
          : '<li class="dropdown-divider"></li>'
            + '<li><a class="dropdown-item" style="color:#27ae60;" onclick="reactivarCatPrecioEnModal(' + cat.id + ')">'  
            + '<i class="fa fa-check-circle mr-1"></i>Reactivar</a></li>';

        var comentEsc = (cat.comentario || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");

        var acciones = '<div class="cat-action-dropdown dropdown">'
          + '<button class="dropdown-toggle" data-toggle="dropdown" data-boundary="viewport">'
          + '<i class="fa fa-ellipsis-v mr-1"></i>Acc.</button>'
          + '<ul class="dropdown-menu dropdown-menu-right">'
          + '<li><a class="dropdown-item item-edit" onclick="editarCatPrecioInline('
          + cat.id + ',\'' + nomEsc + '\',\'' + comentEsc + '\','
          + (cat.porc_precio_a || 0) + ',' + (cat.porc_precio_b || 0) + ','
          + (cat.porc_precio_c || 0) + ',' + (cat.porc_precio_d || 0) + ')">'
          + '<i class="fa fa-pencil mr-1"></i>Editar</a></li>'
          + '<li><a class="dropdown-item item-excel" onclick="descargarPreciosPorCategoria(' + id + ',' + cat.id + ')">'
          + '<i class="fa fa-file-excel-o mr-1"></i>Excel precios</a></li>'
          + estadoBtn
          + '</ul></div>';

        $tbody.append(
          '<tr id="fila-cat-' + cat.id + '">'
          + '<td class="col-hide-xs">' + cat.id + '</td>'
          + '<td id="td-nombre-' + cat.id + '">' + (cat.nombre || '') + '</td>'
          + '<td class="text-center" id="td-a-' + cat.id + '">' + (cat.porc_precio_a || 0) + '%</td>'
          + '<td class="text-center col-hide-xs" id="td-b-' + cat.id + '">' + (cat.porc_precio_b || 0) + '%</td>'
          + '<td class="text-center col-hide-xs" id="td-c-' + cat.id + '">' + (cat.porc_precio_c || 0) + '%</td>'
          + '<td class="text-center col-hide-xs" id="td-d-' + cat.id + '">' + (cat.porc_precio_d || 0) + '%</td>'
          + '<td class="text-center">' + btnComision + '</td>'
          + '<td class="text-center">' + estado + '</td>'
          + '<td class="text-center col-hide-sm">' + ultAct + '</td>'
          + '<td class="text-center col-hide-sm">' + actualizador + '</td>'
          + '<td class="text-center">' + acciones + '</td>'
          + '</tr>'
        );
      });

      $('#loadingVerCatPrecios').hide();
      $('#wrapperVerCatPrecios').show();
    })
    .catch(function (err) {
      console.error(err);
      $('#modalVerCatPrecios').modal('hide');
      Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las categorías de precio.' });
    });
}

function descargarPreciosPorCliente() {
  if (!_catClienteIdActivo) return;
  window.location.href = '/exportar/precios/por-cliente/' + _catClienteIdActivo;
}

function descargarPreciosPorCategoria(clienteId, catPrecioId) {
  window.location.href = '/exportar/precios/por-categoria/' + clienteId + '/' + catPrecioId;
}

function desactivarCatPrecioEnModal(catPrecioId) {
  Swal.fire({
    icon: 'warning',
    title: '¿Desactivar categoría?',
    text: 'Se inactivarán también todos sus precios de producto. Esta categoría ya no aparecerá en cotizaciones ni facturaciones.',
    showCancelButton: true,
    confirmButtonText: 'Sí, desactivar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#e74c3c',
    customClass: { container: 'swal-sobre-modal' }
  }).then(function (result) {
    if (!result.isConfirmed) return;
    axios.get('/desactivar/categoria/precios/' + catPrecioId)
      .then(function (res) {
        Swal.fire({
          icon: res.data.icon, title: res.data.title, text: res.data.text,
          customClass: { container: 'swal-sobre-modal' }
        }).then(function () {
          verCategoriasPrecio(_catClienteIdActivo, _catClienteNomActivo);
          $('#tbl_listaCategoria').DataTable().ajax.reload(null, false);
        });
      })
      .catch(function (err) {
        var d = err.response && err.response.data ? err.response.data : {};
        Swal.fire({ icon: 'error', title: 'Error', text: d.text || 'No se pudo desactivar.',
          customClass: { container: 'swal-sobre-modal' } });
      });
  });
}

function reactivarCatPrecioEnModal(catPrecioId) {
  Swal.fire({
    icon: 'question',
    title: '¿Reactivar categoría?',
    text: 'La categoría volverá a estar activa. Recuerda que sus precios de producto quedaron inactivos; deberás re-importarlos para que aparezca en cotizaciones.',
    showCancelButton: true,
    confirmButtonText: 'Sí, reactivar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#27ae60',
    customClass: { container: 'swal-sobre-modal' }
  }).then(function (result) {
    if (!result.isConfirmed) return;
    axios.get('/reactivar/categoria/precios/' + catPrecioId)
      .then(function (res) {
        Swal.fire({
          icon: res.data.icon, title: res.data.title, text: res.data.text,
          customClass: { container: 'swal-sobre-modal' }
        }).then(function () {
          verCategoriasPrecio(_catClienteIdActivo, _catClienteNomActivo);
          $('#tbl_listaCategoria').DataTable().ajax.reload(null, false);
        });
      })
      .catch(function (err) {
        var d = err.response && err.response.data ? err.response.data : {};
        Swal.fire({ icon: 'error', title: 'Error', text: d.text || 'No se pudo reactivar.',
          customClass: { container: 'swal-sobre-modal' } });
      });
  });
}

function editarCatPrecioInline(id, nombre, comentario, a, b, c, d) {
  var nomEsc = (_catClienteNomActivo || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");

  // Inputs con clase ei-input para poder deshabilitarlos en bloque durante el guardado
  $('#td-nombre-' + id).html('<input class="form-control form-control-sm ei-input" id="ei-nombre-' + id + '" value="' + nombre + '" style="min-width:130px;">');
  $('#td-a-' + id).html('<input type="number" class="form-control form-control-sm text-center ei-input" id="ei-a-' + id + '" value="' + a + '" style="width:64px;margin:auto;" min="0" max="100" step="0.01">');
  $('#td-b-' + id).html('<input type="number" class="form-control form-control-sm text-center ei-input" id="ei-b-' + id + '" value="' + b + '" style="width:64px;margin:auto;" min="0" max="100" step="0.01">');
  $('#td-c-' + id).html('<input type="number" class="form-control form-control-sm text-center ei-input" id="ei-c-' + id + '" value="' + c + '" style="width:64px;margin:auto;" min="0" max="100" step="0.01">');
  $('#td-d-' + id).html('<input type="number" class="form-control form-control-sm text-center ei-input" id="ei-d-' + id + '" value="' + d + '" style="width:64px;margin:auto;" min="0" max="100" step="0.01">');

  $('#fila-cat-' + id).css('background', '#fffbf0');

  $('#fila-cat-' + id + ' td:last-child').html(
    '<div style="display:flex;gap:5px;justify-content:center;align-items:center;">'
    + '<button id="btn-save-' + id + '" class="btn-save-cat" onclick="guardarCatPrecioInline(' + id + ')" title="Guardar cambios"'
    + ' style="display:inline-flex;align-items:center;gap:5px;padding:5px 14px;font-size:.78rem;">'
    + '<i class="fa fa-check"></i><span id="btn-save-lbl-' + id + '">Guardar</span></button>'
    + '<button id="btn-cancel-' + id + '" class="btn btn-sm" onclick="verCategoriasPrecio(' + _catClienteIdActivo + ',\'' + nomEsc + '\')" title="Cancelar"'
    + ' style="background:#f0f0f0;border:1px solid #ccc;color:#555;border-radius:6px;padding:5px 10px;font-size:.78rem;">'
    + '<i class="fa fa-times"></i></button>'
    + '</div>'
  );
}

function guardarCatPrecioInline(id) {
  var nombre = $('#ei-nombre-' + id).val();
  if (!nombre || !nombre.trim()) {
    Swal.fire({ icon: 'warning', title: 'Nombre requerido', text: 'El nombre no puede estar vacío.',
      customClass: { container: 'swal-sobre-modal' } });
    return;
  }

  var payload = {
    id:            id,
    nombre:        nombre.trim(),
    porc_precio_a: parseFloat($('#ei-a-' + id).val()) || 0,
    porc_precio_b: parseFloat($('#ei-b-' + id).val()) || 0,
    porc_precio_c: parseFloat($('#ei-c-' + id).val()) || 0,
    porc_precio_d: parseFloat($('#ei-d-' + id).val()) || 0
  };

  // ── Estado de carga ──
  var $btnSave   = $('#btn-save-' + id);
  var $btnCancel = $('#btn-cancel-' + id);
  $btnSave.prop('disabled', true)
    .html('<i class="fa fa-spinner fa-spin"></i><span style="margin-left:5px;">Guardando...</span>');
  $btnCancel.prop('disabled', true);
  $('#fila-cat-' + id + ' .ei-input').prop('disabled', true);

  axios.post('/actualizar/categoria/precios', payload)
    .then(function (res) {
      var d     = res.data;
      var prods = d.productos_actualizados
        ? ' Precios recalculados: <strong>' + d.productos_actualizados + '</strong> producto(s).'
        : '';

      // Fila flash verde
      $('#fila-cat-' + id).css('background', '#eafaf1');

      Swal.fire({
        icon:              d.icon || 'success',
        title:             d.title || '¡Actualizado!',
        html:              (d.text || 'Categoría guardada.') + (prods ? '<br><small class="text-muted">' + prods + '</small>' : ''),
        timer:             2200,
        timerProgressBar:  true,
        showConfirmButton: false,
        customClass:       { container: 'swal-sobre-modal', popup: 'swal2-toast-mini' }
      }).then(function () {
        verCategoriasPrecio(_catClienteIdActivo, _catClienteNomActivo);
        $('#tbl_listaCategoria').DataTable().ajax.reload(null, false);
      });
    })
    .catch(function (err) {
      var d = err.response && err.response.data ? err.response.data : {};
      // Restaurar botones en caso de error
      $btnSave.prop('disabled', false)
        .html('<i class="fa fa-check"></i><span style="margin-left:5px;">Guardar</span>');
      $btnCancel.prop('disabled', false);
      $('#fila-cat-' + id + ' .ei-input').prop('disabled', false);
      $('#fila-cat-' + id).css('background', '#fff5f5');
      Swal.fire({ icon: 'error', title: 'Error', text: d.text || 'No se pudo guardar.',
        customClass: { container: 'swal-sobre-modal' } });
    });
}

/* ===================================================================
   OVERLAY DE COMISIONES POR ROL (mc-popup)
   =================================================================== */

var _mcCatPrecioId    = null;
var _mcComisionesData = [];
var _mcEditing        = false;

function abrirModalComisiones(catPrecioId, catNombre, comisionesB64) {
  _mcCatPrecioId = catPrecioId;
  try {
    _mcComisionesData = JSON.parse(decodeURIComponent(escape(atob(comisionesB64))));
  } catch (e) {
    _mcComisionesData = [];
  }
  _mcEditing = false;

  $('#mc-modal-subtitle').text(catNombre);
  $('#mc-buscador').val('');
  $('#mc-btn-editar').show();
  $('#mc-btn-aplicar').hide();

  renderMCTable(_mcComisionesData, false);
  $('#mcOverlay').fadeIn(180);
}

function cerrarMCIfBg(event) {
  if (event.target === document.getElementById('mcOverlay')) {
    cerrarModalComisiones();
  }
}

function cerrarModalComisiones() {
  _mcEditing = false;
  $('#mcOverlay').fadeOut(180);
}

function filtrarModalComisiones(value) {
  var q = (value || '').toLowerCase().trim();
  var lista = q
    ? _mcComisionesData.filter(function (c) { return c.rol_nombre.toLowerCase().indexOf(q) !== -1; })
    : _mcComisionesData;
  renderMCTable(lista, _mcEditing);
}

function renderMCTable(comisiones, editing) {
  var $tbody    = $('#mc-tbody');
  var $noResult = $('#mc-no-result');
  $tbody.empty();

  if (!comisiones.length) {
    $noResult.show();
    return;
  }
  $noResult.hide();

  comisiones.forEach(function (c) {
    if (editing) {
      $tbody.append(
        '<tr><td>' + c.rol_nombre + '</td>'
        + '<td class="text-center"><input type="number" class="form-control form-control-sm text-center mc-input"'
        + ' value="' + c.porcentaje_comision + '" min="0" max="100" step="0.01"'
        + ' data-escala="' + c.escala_id + '" style="width:80px;margin:auto;"></td></tr>'
      );
    } else {
      $tbody.append('<tr><td>' + c.rol_nombre + '</td><td class="text-center">' + c.porcentaje_comision + '%</td></tr>');
    }
  });
}

function activarEdicionMC() {
  _mcEditing = true;
  var q = ($('#mc-buscador').val() || '').toLowerCase().trim();
  var lista = q
    ? _mcComisionesData.filter(function (c) { return c.rol_nombre.toLowerCase().indexOf(q) !== -1; })
    : _mcComisionesData;
  renderMCTable(lista, true);
  $('#mc-btn-editar').hide();
  $('#mc-btn-aplicar').show();
}

function aplicarComisionesModal() {
  var comisiones = [];
  $('#mc-tbody .mc-input').each(function () {
    comisiones.push({
      escala_id:            $(this).data('escala'),
      porcentaje_comision:  parseFloat($(this).val()) || 0
    });
  });

  axios.post('/actualizar/comision/cat-precio', {
    cat_precio_id: _mcCatPrecioId,
    comisiones:    comisiones
  })
    .then(function (res) {
      var d = res.data;
      cerrarModalComisiones();
      Swal.fire({
        icon: d.icon, title: d.title, text: d.text,
        customClass: { container: 'swal-sobre-modal' }
      });
      comisiones.forEach(function (c) {
        var item = _mcComisionesData.find(function (x) { return x.escala_id == c.escala_id; });
        if (item) item.porcentaje_comision = c.porcentaje_comision;
      });
    })
    .catch(function (err) {
      var d = err.response && err.response.data ? err.response.data : {};
      Swal.fire({ icon: 'error', title: d.title || 'Error', text: d.text || 'No se pudo actualizar.',
        customClass: { container: 'swal-sobre-modal' } });
    });
}
