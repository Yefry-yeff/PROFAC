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
      toggleDescargarCompleto();
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
        toggleDescargarCompleto(); // mantener lógica actual del botón
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
  // === Resetear form al cerrar modal
  $('#modalCategoriasPrecios').on('hidden.bs.modal', function () {
    $('#CreacionCatPrecios')[0].reset();
    $('#categoria_cliente_id').val(null).trigger('change');
  });

  // === Listeners para mantener la lógica actual del botón
  $('#listaTipoFiltro, #tipoCategoria').on('change', toggleDescargarCompleto);
    toggleDescargarCompleto(); // estado inicial
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
/*===================================================================================================================================*/
/*===================================================================================================================================*/
/*===================================================================================================================================*/
/* Subida de Excel de precios de productos */
// Mostrar nombre del archivo
// Mostrar nombre del archivo elegido
// Mostrar nombre del archivo
// ================================
//  Estado global de la vista previa
// ================================
window.excelPreview = {
  rows: [],      // Array de objetos (filas)
  headers: []    // Encabezados detectados
};

// ======================================
//  Utilidad: destruir DataTable si existe
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

  // Construir thead
  const theadHtml = '<tr>' + headers.map(h => `<th>${h}</th>`).join('') + '</tr>';
  $('#previewExcel thead').html(theadHtml);

  // Definir columnas para DataTables
  const columns = headers.map(h => ({ title: h, data: h }));

  // Inicializar DataTable
  $('#previewExcel').DataTable({
    destroy: true,
    data: rows,
    columns: columns,
    pageLength: 25,
    responsive: true,
    language: { url: "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json" },
    deferRender: true
  });

  // Habilitar botones
  $('#btnProcesar').prop('disabled', false);
  $('#btnLimpiarVista').prop('disabled', false);
}

// ======================================
//  Mostrar nombre del archivo elegido
// ======================================
$(document).on('change', '#archivo_excel', function () {
  const name = this.files?.[0]?.name || 'Elegí un archivo...';
  $(this).next('.custom-file-label').text(name);
});

// =====================================================
//  Submit: leer Excel y mostrar vista previa (no envía)
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

      // Tomamos la PRIMERA hoja para la vista previa (se puede extender a más)
      const firstSheet = workbook.SheetNames[0];
      const worksheet = workbook.Sheets[firstSheet];

      // Convertimos a JSON (defval:null para no perder celdas vacías)
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

      // Guardar y renderizar
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
    Swal.fire({ icon: d.icon || 'success', title: d.title || 'Listo', text: d.text || 'Procesado.' });
    $('#tbl_listaCategoria').DataTable().ajax.reload();
  } catch (err) {
    const d = err.response?.data || {};
    console.error('Error:', d);
    Swal.fire({ icon: d.icon || 'error', title: d.title || 'Error', text: d.text || 'No se pudo procesar.' });
  } finally {
    $btn.prop('disabled', false).text('Procesar');
  }
});


//Indice en base de datos
// CREATE INDEX idx_ppc_cat_prod ON precios_producto_carga (categoria_precios_id, producto_id);
//CREATE INDEX idx_ppc_estado ON precios_producto_carga (estado_id);

