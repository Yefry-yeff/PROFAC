/* ==========================================================================
   reporteEscalas.js  —  Reportes de Precios y Escalas
   6 tabs: Precios, Cobertura, Sin Cat., Sin Precio, Comparativo, Resumen
========================================================================== */

// Instancias de DataTable
let dtPrecios      = null;
let dtCobertura    = null;
let dtSinCat       = null;
let dtSinPrecio    = null;
let dtComparativo  = null;
let dtResumen      = null;

// Producto seleccionado para comparativo
let currentProdutoId     = null;
let currentProdutoNombre = null;

// Control de peticiones
let _reloadTimer  = null;   // debounce timer
let _filtersLocked = false; // evita doble-click durante carga

/* Debounce: espera 600ms después del último cambio antes de recargar */
function scheduleReloadPrecios() {
  if (_reloadTimer) clearTimeout(_reloadTimer);
  _reloadTimer = setTimeout(function () {
    if (dtPrecios) {
      setFiltersLock(true);
      dtPrecios.ajax.reload(function () { setFiltersLock(false); }, true);
    }
  }, 600);
}

/* Bloquea/desbloquea los controles de filtro durante la carga */
function setFiltersLock(lock) {
  _filtersLocked = lock;
  $('#filtro-cat-cliente, #filtro-cat-precio, #tipoFiltro, #listaTipoFiltro')
    .prop('disabled', lock);
  if (lock) {
    $('#btn-export-precios').prop('disabled', true)
      .html('<i class="fa fa-spinner fa-spin mr-1"></i> Cargando...');
  } else {
    $('#btn-export-precios').prop('disabled', false)
      .html('<i class="fa fa-file-excel-o mr-1"></i> Exportar a Excel');
  }
}

/* ──────────────────────────────────────────────────────────────────────────
   BOOTSTRAP
────────────────────────────────────────────────────────────────────────── */
$(document).ready(function () {
  // CSRF para Axios
  if (typeof axios !== 'undefined') {
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrf) axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
  }

  // === Tab 1: Precios por producto ===
  initTabPrecios();

  // === Tabs 2-4 y 6: cargar al mostrarse por primera vez ===
  let coberturaLoaded = false, sincatLoaded = false, sinprecioLoaded = false, resumenLoaded = false;

  $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
    const tab = $(e.target).attr('href');
    if (tab === '#tab-cobertura' && !coberturaLoaded)  { coberturaLoaded = true;  cargarCobertura();  }
    if (tab === '#tab-sincat'    && !sincatLoaded)      { sincatLoaded    = true;  cargarSinCat();     }
    if (tab === '#tab-sinprecio' && !sinprecioLoaded)   { sinprecioLoaded = true;  cargarSinPrecio();  }
    if (tab === '#tab-resumen'   && !resumenLoaded)     { resumenLoaded   = true;  cargarResumen();    }
  });

  // === Cargar contadores de alerta en badges (siempre al inicio) ===
  cargarBadgesAlerta();

  // === Select2 para los filtros del Tab 1 ===

  // ── Cat. de Cliente (single, carga total) ──────────────────────────
  $.ajax({
    url: '/filtros/categoria/cliente', dataType: 'json',
    success: function (data) {
      const $sel = $('#filtro-cat-cliente');
      data.forEach(function (item) { $sel.append(new Option(item.nombre, item.id)); });
      $sel.select2({ theme: 'bootstrap4',
        placeholder: 'Todas las categorías de cliente',
        allowClear: false, width: 'resolve'
      });
    }
  });

  // ── Cat. de Precio (single, carga total, cascading desde cliente) ──
  function cargarCatPrecio(catClienteId) {
    const $sel = $('#filtro-cat-precio');
    const seleccionado = $sel.val() || '';
    $sel.empty().append(new Option('Todas las categorías de precio', ''));
    $.ajax({
      url: '/filtros/categoria/precios/por-cliente',
      data: { cat_cliente_ids: catClienteId || '' },
      dataType: 'json',
      success: function (data) {
        data.forEach(function (item) { $sel.append(new Option(item.nombre, item.id)); });
        const idsDisponibles = data.map(function (i) { return String(i.id); });
        const mantener = idsDisponibles.includes(String(seleccionado)) ? seleccionado : '';
        $sel.val(mantener).trigger('change.select2');
        scheduleReloadPrecios();
      }
    });
  }
  $('#filtro-cat-precio').select2({ theme: 'bootstrap4',
    placeholder: 'Todas las categorías de precio',
    allowClear: false, width: 'resolve'
  });
  cargarCatPrecio('');

  // Al cambiar Cat. Cliente → cascading de Cat. Precio
  $('#filtro-cat-cliente').on('change', function () {
    if (_filtersLocked) return;
    cargarCatPrecio($(this).val() || '');
  });
  $('#filtro-cat-precio').on('change', function () {
    if (_filtersLocked) return;
    scheduleReloadPrecios();
  });

  // ── Filtrar por (tipo único) ────────────────────────────────────
  $('#tipoFiltro').select2({ theme: 'bootstrap4', placeholder: 'Sin filtro adicional',
    allowClear: true, width: 'resolve'
  });

  // ── Lista de valores (multi, AJAX paginado) ────────────────────
  function initListaFiltro(tipo) {
    const $lista = $('#listaTipoFiltro');
    const $wrap  = $('#wrapper-lista-filtro');
    if ($lista.data('select2')) { $lista.select2('destroy'); }
    $lista.empty().val(null);

    if (!tipo) { $wrap.hide(); scheduleReloadPrecios(); return; }

    const url   = tipo == '1' ? '/filtros/marca/buscar' : '/filtros/categoria/buscar';
    const label = tipo == '1' ? '<i class="fa fa-trademark"></i> Marca'
                               : '<i class="fa fa-th-large"></i> Categoría Producto';
    $('#label-lista-filtro').html(label);
    $wrap.show();

    $lista.select2({
      theme: 'bootstrap4', multiple: true,
      placeholder: tipo == '1' ? 'Todas las marcas' : 'Todas las categorías',
      allowClear: true, width: 'resolve',
      closeOnSelect: false,
      ajax: {
        url: url, dataType: 'json', delay: 250,
        data: function (params) { return { q: params.term || '', page: params.page || 1 }; },
        processResults: function (data) { return { results: data.results, pagination: data.pagination }; },
        cache: true
      }
    });

    $lista.on('change', function () {
      if (_filtersLocked) return;
      scheduleReloadPrecios();
    });
  }

  $('#tipoFiltro').on('change', function () {
    if (_filtersLocked) return;
    initListaFiltro($(this).val());
  });
  $('#select-produto-comparativo').select2({
    theme: 'bootstrap4',
    placeholder: 'Escriba al menos 2 caracteres...',
    allowClear: true,
    minimumInputLength: 2,
    width: 'resolve',
    ajax: {
      url: '/filtros/produtos',
      dataType: 'json',
      delay: 300,
      data: function (params) { return { q: params.term }; },
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

  // Cuando se selecciona un producto en el comparativo
  $('#select-produto-comparativo').on('change', function () {
    currentProdutoId     = $(this).val();
    currentProdutoNombre = $(this).find(':selected').text();
    if (currentProdutoId) cargarComparativo();
  });

  // === Select2 para filtro resumen cat cliente ===
  $.ajax({
    url: '/filtros/categoria/cliente',
    success: function (data) {
      const $sel = $('#filtro-resumen-cat-cliente');
      data.forEach(function (item) {
        $sel.append(new Option(item.nombre, item.id));
      });
    }
  });
  $('#filtro-resumen-cat-cliente').select2({ theme: 'bootstrap4', placeholder: 'Todas', allowClear: true, width: 'resolve' });

});

/* ──────────────────────────────────────────────────────────────────────────
   HELPERS
────────────────────────────────────────────────────────────────────────── */
function badgeEstado(estado) {
  const cls = estado === 'Activo' ? 'badge-activo' : 'badge-inactivo';
  return '<span class="' + cls + '">' + estado + '</span>';
}
function fmt(val) {
  return val !== null && val !== undefined ? 'L. ' + parseFloat(val).toFixed(2) : '—';
}
function fmtPct(val) {
  return val !== null && val !== undefined && val !== '' ? parseFloat(val).toFixed(2) + '%' : '—';
}

/* ──────────────────────────────────────────────────────────────────────────
   BADGES DE ALERTA
────────────────────────────────────────────────────────────────────────── */
function cargarBadgesAlerta() {
  $.getJSON('/reportes/escalas/sin-precios-cat', function (data) {
    const n = data.length;
    const $b = $('#badge-sincat');
    $b.text(n);
    if (n > 0) $b.css({ background: 'rgba(231,76,60,.85)', color: '#fff' });
  });
  $.getJSON('/reportes/escalas/sin-precios-prod', function (data) {
    const n = data.length;
    const $b = $('#badge-sinprecio');
    $b.text(n);
    if (n > 0) $b.css({ background: 'rgba(231,76,60,.85)', color: '#fff' });
  });
}

/* ──────────────────────────────────────────────────────────────────────────
   TAB 1 — Precios por Producto (server-side)
────────────────────────────────────────────────────────────────────────── */
function initTabPrecios() {
  dtPrecios = $('#tbl_precios_prod').DataTable({
    processing: true,
    serverSide: true,
    language: { url: '/js/plugins/dataTables/i18n/Spanish.json', processing: '<i class="fa fa-spinner fa-spin fa-2x text-warning"></i>' },
    ajax: {
      url: '/escalas/productos/filtrados',
      type: 'GET',
      data: function (d) {
        d.cat_cliente_ids  = $('#filtro-cat-cliente').val() || '';
        d.cat_precio_ids   = $('#filtro-cat-precio').val()  || '';
        d.tipoFiltro       = $('#tipoFiltro').val();
        d.lista_filtro_ids = ($('#listaTipoFiltro').val()     || []).join(',');
      },
      error: function (xhr) {
        setFiltersLock(false);
        if (xhr.status === 429) {
          Swal.fire({ icon: 'warning', title: 'Demasiadas consultas',
            text: 'Estás realizando cambios muy rápido. Espera un momento antes de seguir filtrando.',
            confirmButtonColor: '#e67e22', timer: 4000, timerProgressBar: true });
        }
      }
    },
    columns: [
      { data: 'id',               width: '50px' },
      { data: 'categoria_cliente' },
      { data: 'codigo',           width: '100px' },
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
    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
    dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip'
  });
}

function exportarPreciosProd() {
  const $btn = $('#btn-export-precios');
  const orig = $btn.html();
  $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Generando...');
  const params = new URLSearchParams({
    cat_cliente_ids:  $('#filtro-cat-cliente').val()  || '',
    cat_precio_ids:   $('#filtro-cat-precio').val()   || '',
    tipoFiltro:       $('#tipoFiltro').val() || '',
    lista_filtro_ids: ($('#listaTipoFiltro').val()     || []).join(',')
  });
  const a = document.createElement('a');
  a.href = '/descargar/productos/filtros?' + params.toString();
  a.download = '';
  document.body.appendChild(a); a.click(); document.body.removeChild(a);
  setTimeout(function () { $btn.prop('disabled', false).html(orig); }, 3000);
}

/* ──────────────────────────────────────────────────────────────────────────
   TAB 2 — Cobertura
────────────────────────────────────────────────────────────────────────── */
function cargarCobertura() {
  $('#loading-cobertura').show();
  $('#wrapper-cobertura').hide();
  $('#stats-cobertura').hide();

  $.getJSON('/reportes/escalas/cobertura', function (data) {
    $('#loading-cobertura').hide();

    // Stats
    const total = data.length;
    const sinCob = data.filter(function (r) { return parseInt(r.total_cat_precios) === 0; }).length;
    const totalProds = data.reduce(function (s, r) { return s + parseInt(r.total_productos || 0); }, 0);
    $('#stat-total-cat').text(total);
    $('#stat-sin-cobertura').text(sinCob);
    $('#stat-con-cobertura').text(total - sinCob);
    $('#stat-total-prods-cob').text(totalProds.toLocaleString());
    $('#stats-cobertura').show();

    // Filas
    const $tbody = $('#tbody-cobertura').empty();
    data.forEach(function (r) {
      const catTotal = parseInt(r.total_cat_precios) || 0;
      const cobBadge = catTotal === 0
        ? '<span class="badge-cero">Sin cobertura</span>'
        : '<span class="badge-activo">' + catTotal + ' cat.</span>';
      $tbody.append(
        '<tr>' +
        '<td>' + r.id + '</td>' +
        '<td><strong>' + escHtml(r.nombre_categoria) + '</strong></td>' +
        '<td class="text-center">' + badgeEstado(r.estado) + '</td>' +
        '<td class="text-center">' + catTotal + '</td>' +
        '<td class="text-center">' + (r.cat_activas || 0) + '</td>' +
        '<td class="text-center"><strong>' + (r.total_productos || 0) + '</strong></td>' +
        '<td class="text-center">' + cobBadge + '</td>' +
        '<td class="text-center"><small>' + (r.created_at || '—') + '</small></td>' +
        '</tr>'
      );
    });

    // DataTable
    if (dtCobertura) { dtCobertura.destroy(); dtCobertura = null; }
    dtCobertura = $('#tbl_cobertura').DataTable({
      language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
      pageLength: 10,
      order: [[3, 'asc']],
      dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip'
    });

    $('#wrapper-cobertura').show();
  }).fail(function () {
    $('#loading-cobertura').hide();
    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el reporte de cobertura.' });
  });
}

function exportarCobertura() {
  descargarExcel('/exportar/cobertura-categorias');
}

/* ──────────────────────────────────────────────────────────────────────────
   TAB 3 — Sin categorías de precio
────────────────────────────────────────────────────────────────────────── */
function cargarSinCat() {
  $('#loading-sincat').show();
  $('#wrapper-sincat').hide();
  $('#empty-sincat').hide();

  $.getJSON('/reportes/escalas/sin-precios-cat', function (data) {
    $('#loading-sincat').hide();

    if (data.length === 0) {
      $('#empty-sincat').show();
      return;
    }

    const $tbody = $('#tbody-sincat').empty();
    data.forEach(function (r) {
      $tbody.append(
        '<tr>' +
        '<td>' + r.id + '</td>' +
        '<td><strong>' + escHtml(r.nombre_categoria) + '</strong></td>' +
        '<td>' + escHtml(r.descripcion_categoria || '') + '</td>' +
        '<td class="text-center">' + badgeEstado(r.estado) + '</td>' +
        '<td class="text-center"><small>' + (r.created_at || '—') + '</small></td>' +
        '</tr>'
      );
    });

    if (dtSinCat) { dtSinCat.destroy(); dtSinCat = null; }
    dtSinCat = $('#tbl_sincat').DataTable({
      language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
      pageLength: 10,
      order: [[0, 'desc']],
      dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip'
    });

    $('#wrapper-sincat').show();
  }).fail(function () {
    $('#loading-sincat').hide();
    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el reporte.' });
  });
}

function exportarSinCat() {
  descargarExcel('/exportar/cat-sin-precios');
}

/* ──────────────────────────────────────────────────────────────────────────
   TAB 4 — Productos sin precios
────────────────────────────────────────────────────────────────────────── */
function cargarSinPrecio() {
  $('#loading-sinprecio').show();
  $('#wrapper-sinprecio').hide();
  $('#empty-sinprecio').hide();

  $.getJSON('/reportes/escalas/sin-precios-prod', function (data) {
    $('#loading-sinprecio').hide();

    if (data.length === 0) {
      $('#empty-sinprecio').show();
      return;
    }

    const $tbody = $('#tbody-sinprecio').empty();
    data.forEach(function (r) {
      $tbody.append(
        '<tr>' +
        '<td>' + r.id + '</td>' +
        '<td>' + escHtml(r.codigo_barra || '—') + '</td>' +
        '<td>' + escHtml(r.nombre) + '</td>' +
        '</tr>'
      );
    });

    if (dtSinPrecio) { dtSinPrecio.destroy(); dtSinPrecio = null; }
    dtSinPrecio = $('#tbl_sinprecio').DataTable({
      language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
      pageLength: 10,
      order: [[0, 'desc']],
      dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip'
    });

    $('#wrapper-sinprecio').show();
  }).fail(function () {
    $('#loading-sinprecio').hide();
    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el reporte.' });
  });
}

function exportarSinPrecio() {
  descargarExcel('/exportar/productos-sin-precios');
}

/* ──────────────────────────────────────────────────────────────────────────
   TAB 5 — Comparativo por producto
────────────────────────────────────────────────────────────────────────── */
function cargarComparativo() {
  currentProdutoId = $('#select-produto-comparativo').val();
  if (!currentProdutoId) {
    Swal.fire({ icon: 'warning', title: 'Seleccione un producto', text: 'Debe buscar y seleccionar un producto primero.' });
    return;
  }

  $('#placeholder-comparativo').hide();
  $('#loading-comparativo').show();
  $('#wrapper-comparativo').hide();

  $.getJSON('/reportes/escalas/comparativo?produto_id=' + currentProdutoId, function (data) {
    $('#loading-comparativo').hide();

    if (data.length === 0) {
      $('#placeholder-comparativo').html('<i class="fa fa-inbox"></i>Este producto no tiene precios configurados en ninguna categoría.').show();
      $('#btn-export-comparativo').prop('disabled', true);
      return;
    }

    const $tbody = $('#tbody-comparativo').empty();
    data.forEach(function (r) {
      $tbody.append(
        '<tr>' +
        '<td>' + escHtml(r.categoria_cliente) + '</td>' +
        '<td>' + escHtml(r.categoria_precio) + '</td>' +
        '<td class="text-center">' + fmtPct(r.porc_precio_a) + '</td>' +
        '<td class="text-center">' + fmtPct(r.porc_precio_b) + '</td>' +
        '<td class="text-center">' + fmtPct(r.porc_precio_c) + '</td>' +
        '<td class="text-center">' + fmtPct(r.porc_precio_d) + '</td>' +
        '<td class="text-right">' + fmt(r.precio_base_venta) + '</td>' +
        '<td class="text-right"><strong>' + fmt(r.precio_a) + '</strong></td>' +
        '<td class="text-right">' + fmt(r.precio_b) + '</td>' +
        '<td class="text-right">' + fmt(r.precio_c) + '</td>' +
        '<td class="text-right">' + fmt(r.precio_d) + '</td>' +
        '<td class="text-center">' + badgeEstado(r.estado) + '</td>' +
        '</tr>'
      );
    });

    if (dtComparativo) { dtComparativo.destroy(); dtComparativo = null; }
    dtComparativo = $('#tbl_comparativo').DataTable({
      language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
      pageLength: 25,
      order: [[0, 'asc']],
      dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip'
    });

    $('#wrapper-comparativo').show();
    $('#btn-export-comparativo').prop('disabled', false);
    $('#info-comparativo').html('<i class="fa fa-info-circle mr-1"></i>' + data.length + ' configuraciones encontradas para el producto seleccionado.');
  }).fail(function () {
    $('#loading-comparativo').hide();
    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el comparativo.' });
  });
}

function exportarComparativo() {
  if (!currentProdutoId) return;
  descargarExcel('/exportar/comparativo-produto?produto_id=' + currentProdutoId);
}

/* ──────────────────────────────────────────────────────────────────────────
   TAB 6 — Resumen categorías de precio
────────────────────────────────────────────────────────────────────────── */
function cargarResumen() {
  $('#loading-resumen').show();
  $('#wrapper-resumen').hide();

  if (dtResumen) { dtResumen.destroy(); dtResumen = null; }

  const catClienteId = $('#filtro-resumen-cat-cliente').val() || '';
  const estadoId     = $('#filtro-resumen-estado').val();

  const params = new URLSearchParams();
  if (catClienteId) params.append('cat_cliente_id', catClienteId);
  if (estadoId !== '') params.append('estado_id', estadoId);

  $.getJSON('/reportes/escalas/resumen-cat-precio?' + params.toString(), function (data) {
    $('#loading-resumen').hide();

    const $tbody = $('#tbody-resumen').empty();
    data.forEach(function (r) {
      $tbody.append(
        '<tr>' +
        '<td>' + r.id + '</td>' +
        '<td><strong>' + escHtml(r.categoria_precio) + '</strong></td>' +
        '<td>' + escHtml(r.categoria_cliente) + '</td>' +
        '<td class="text-center">' + fmtPct(r.porc_precio_a) + '</td>' +
        '<td class="text-center">' + fmtPct(r.porc_precio_b) + '</td>' +
        '<td class="text-center">' + fmtPct(r.porc_precio_c) + '</td>' +
        '<td class="text-center">' + fmtPct(r.porc_precio_d) + '</td>' +
        '<td class="text-center">' + badgeEstado(r.estado) + '</td>' +
        '<td class="text-center"><strong>' + (r.total_productos || 0) + '</strong></td>' +
        '<td class="text-center"><small>' + (r.fecha_ultima_actualizacion || '—') + '</small></td>' +
        '</tr>'
      );
    });

    dtResumen = $('#tbl_resumen').DataTable({
      language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
      pageLength: 15,
      order: [[1, 'asc']],
      dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip'
    });

    $('#wrapper-resumen').show();
  }).fail(function () {
    $('#loading-resumen').hide();
    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el resumen.' });
  });
}

function exportarResumen() {
  const catClienteId = $('#filtro-resumen-cat-cliente').val() || '';
  const estadoId     = $('#filtro-resumen-estado').val();
  const params = new URLSearchParams();
  if (catClienteId) params.append('cat_cliente_id', catClienteId);
  if (estadoId !== '') params.append('estado_id', estadoId);
  descargarExcel('/exportar/resumen-cat-precio?' + params.toString());
}

/* ──────────────────────────────────────────────────────────────────────────
   UTILIDADES
────────────────────────────────────────────────────────────────────────── */
function descargarExcel(url) {
  const a = document.createElement('a');
  a.href = url;
  a.download = '';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
}

function escHtml(text) {
  if (!text) return '';
  return String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}
