
// ================================================================
// reporteEscalas.js — PROFAC · Reportes de Escalas de Precios
// ================================================================

// --- Estado global ---
var tablaPrecios       = null;
var _coberturaLoaded   = false;
var _sincatLoaded      = false;
var _sinprecioLoaded   = false;
var _comisionesLoaded  = false;
var _comparativoProdId = null;

$(document).ready(function () {

    // CSRF para axios
    if (typeof axios !== 'undefined') {
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        var csrf = document.querySelector('meta[name="csrf-token"]');
        if (csrf) axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf.content;
    }

    // -- Inicializar Select2 --
    var s2opts = { theme: 'bootstrap4', width: 'resolve' };
    $('#filtro-cat-cliente').select2($.extend({}, s2opts, { placeholder: 'Todas las categorías' }));
    $('#filtro-cat-precio').select2($.extend({}, s2opts, { placeholder: 'Todas las cat. de precio' }));
    $('#tipoFiltro').select2($.extend({}, s2opts, { placeholder: 'Sin filtro adicional' }));
    $('#listaTipoFiltro').select2($.extend({}, s2opts, { placeholder: 'Seleccione...' }));
    $('#filtro-resumen-cat-cliente').select2($.extend({}, s2opts, { placeholder: 'Todas' }));
    $('#filtro-comision-cat-cliente').select2($.extend({}, s2opts, { placeholder: 'Todas las categorías' }));
    $('#filtro-comision-rol').select2($.extend({}, s2opts, { placeholder: 'Todos los roles' }));

    // -- Comparativo: Select2 con búsqueda AJAX --
    $('#select-produto-comparativo').select2({
        theme: 'bootstrap4',
        placeholder: 'Escriba al menos 2 letras para buscar...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: '/filtros/produtos',
            dataType: 'json',
            delay: 300,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) {
                return { results: data.map(function (r) { return { id: r.id, text: r.nombre }; }) };
            },
            cache: true
        },
        width: '100%'
    });

    // -- Cargar opciones de cat-cliente (compartido entre tabs) --
    cargarOpcionesCatCliente();

    // -- Cargar opciones de roles para tab comisiones --
    cargarOpcionesRoles();

    // -- Inicializar DataTable de precios (Tab 1) --
    inicializarTablaPrecios();

    // -- Eventos de filtros del Tab 1 --
    $('#filtro-cat-cliente').on('change', function () {
        cargarCatPrecioSegunCliente($(this).val());
        tablaPrecios && tablaPrecios.ajax.reload(null, false);
    });

    $('#filtro-cat-precio').on('change', function () {
        tablaPrecios && tablaPrecios.ajax.reload(null, false);
    });

    $('#tipoFiltro').on('change', function () {
        var tipo = $(this).val();
        $('#listaTipoFiltro').val(null).trigger('change');
        if (tipo) {
            $('#wrapper-lista-filtro').slideDown(180);
            $('#label-lista-filtro').html('<i class="fa fa-list"></i> ' + (tipo == '1' ? 'Marca' : 'Categoría de Producto'));
            cargarListaFiltro(tipo);
        } else {
            $('#wrapper-lista-filtro').slideUp(180);
            tablaPrecios && tablaPrecios.ajax.reload(null, false);
        }
    });

    $('#listaTipoFiltro').on('change', function () {
        tablaPrecios && tablaPrecios.ajax.reload(null, false);
    });

    // -- Lazy loading de tabs por demanda --
    $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        if      (target === '#tab-cobertura'   && !_coberturaLoaded)  cargarCobertura();
        else if (target === '#tab-sincat'       && !_sincatLoaded)     cargarSinCat();
        else if (target === '#tab-sinprecio'    && !_sinprecioLoaded)  cargarSinPrecio();
        else if (target === '#tab-comisiones'   && !_comisionesLoaded) cargarComisiones();
    });

    // -- Tab Resumen: cargar al inicio --
    cargarResumen();
});

// ================================================================
// HELPERS
// ================================================================
function cargarOpcionesCatCliente() {
    $.get('/filtros/categoria/cliente', function (data) {
        var opciones = '';
        data.forEach(function (r) { opciones += '<option value="' + r.id + '">' + r.nombre + '</option>'; });
        $('#filtro-cat-cliente, #filtro-resumen-cat-cliente, #filtro-comision-cat-cliente').each(function () {
            $(this).append(opciones);
        });
    });
}

function cargarOpcionesRoles() {
    $.get('/comision/roles/lista', function (data) {
        var arr = Array.isArray(data) ? data : (data.data || []);
        var $s = $('#filtro-comision-rol');
        arr.forEach(function (r) { $s.append(new Option(r.name || r.nombre, r.id)); });
    });
}

function cargarCatPrecioSegunCliente(catClienteId) {
    var url = '/filtros/categoria/precios/por-cliente' + (catClienteId ? '?cat_cliente_ids=' + catClienteId : '');
    var $s = $('#filtro-cat-precio');
    $s.empty().append('<option value="">Todas las cat. de precio</option>');
    $.get(url, function (data) {
        data.forEach(function (r) { $s.append(new Option(r.nombre, r.id)); });
        $s.trigger('change.select2');
    });
}

function cargarListaFiltro(tipo) {
    var url = tipo == '1' ? '/filtros/marca' : '/filtros/categoria';
    var $s = $('#listaTipoFiltro');
    $s.empty().append('<option value="">Seleccione...</option>');
    $.get(url, function (data) {
        data.forEach(function (r) { $s.append(new Option(r.nombre, r.id)); });
        $s.trigger('change.select2');
    });
}

function fmtEstado(estado) {
    return estado === 'Activo'
        ? '<span class="badge-activo">Activo</span>'
        : '<span class="badge-inactivo">Inactivo</span>';
}

function fmtFecha(f) {
    return (f && f.length >= 10) ? f.substring(0, 10) : '—';
}

// ================================================================
// TAB 1: Precios por Producto
// ================================================================
function inicializarTablaPrecios() {
    tablaPrecios = $('#tbl_precios_prod').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        language: {
            url: '/js/plugins/dataTables/i18n/Spanish.json',
            processing: '<div style="padding:16px;color:#b07c3c;"><i class="fa fa-spinner fa-spin fa-lg mr-2"></i>Cargando...</div>'
        },
        ajax: {
            url: '/escalas/productos/filtrados',
            type: 'GET',
            data: function (d) {
                d.cat_cliente_ids  = $('#filtro-cat-cliente').val()  || '';
                d.cat_precio_ids   = $('#filtro-cat-precio').val()   || '';
                d.tipoFiltro       = $('#tipoFiltro').val()          || '';
                d.lista_filtro_ids = $('#listaTipoFiltro').val()     || '';
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los productos.' });
            }
        },
        columns: [
            { data: 'id',                  width: '50px' },
            { data: 'categoria_cliente' },
            { data: 'codigo',              width: '110px' },
            { data: 'producto' },
            { data: 'marca' },
            { data: 'categoria' },
            { data: 'escala_precio' },
            { data: 'precio_A_formatted',  className: 'text-right' },
            { data: 'precio_B_formatted',  className: 'text-right' },
            { data: 'precio_C_formatted',  className: 'text-right' },
            { data: 'precio_D_formatted',  className: 'text-right' }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        dom: '<"row align-items-center mb-2"<"col-sm-6"l><"col-sm-6"f>>rtip'
    });
}

function exportarPreciosProd() {
    var params = $.param({
        cat_cliente_id:  $('#filtro-cat-cliente').val()  || '',
        cat_precio_id:   $('#filtro-cat-precio').val()   || '',
        tipoFiltro:      $('#tipoFiltro').val()          || '',
        listaTipoFiltro: $('#listaTipoFiltro').val()     || ''
    });
    window.location.href = '/descargar/productos/filtros?' + params;
}

// ================================================================
// TAB 2: Cobertura
// ================================================================
function cargarCobertura() {
    $('#loading-cobertura').show();
    $('#wrapper-cobertura').hide();
    $.get('/reportes/escalas/cobertura', function (data) {
        var total      = data.length;
        var sinCob     = data.filter(function (r) { return !r.total_cat_precios || r.total_cat_precios == 0; }).length;
        var totalProds = data.reduce(function (s, r) { return s + (parseInt(r.total_productos) || 0); }, 0);
        $('#stat-total-cat').text(total);
        $('#stat-sin-cobertura').text(sinCob);
        $('#stat-con-cobertura').text(total - sinCob);
        $('#stat-total-prods-cob').text(totalProds);
        $('#stats-cobertura').show();

        var html = '';
        data.forEach(function (r) {
            var cob = (parseInt(r.cat_activas) > 0)
                ? '<span class="badge-activo">Con precios</span>'
                : '<span class="badge-inactivo">Sin precios</span>';
            html += '<tr>'
                + '<td>' + r.id + '</td>'
                + '<td>' + (r.nombre_categoria || '—') + '</td>'
                + '<td class="text-center">' + fmtEstado(r.estado) + '</td>'
                + '<td class="text-center">' + (r.total_cat_precios || 0) + '</td>'
                + '<td class="text-center">' + (r.cat_activas || 0) + '</td>'
                + '<td class="text-center">' + (r.total_productos || 0) + '</td>'
                + '<td class="text-center">' + cob + '</td>'
                + '<td class="text-center">' + fmtFecha(r.created_at) + '</td>'
                + '</tr>';
        });
        $('#tbody-cobertura').html(html || '<tr><td colspan="8" class="text-center text-muted py-3">Sin datos</td></tr>');

        if ($.fn.DataTable.isDataTable('#tbl_cobertura')) $('#tbl_cobertura').DataTable().destroy();
        $('#tbl_cobertura').DataTable({
            language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
            order: [[3, 'desc']], pageLength: 15, responsive: true
        });
        $('#loading-cobertura').hide();
        $('#wrapper-cobertura').show();
        _coberturaLoaded = true;
    }).fail(function () {
        $('#loading-cobertura').hide();
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el reporte de cobertura.' });
    });
}

function exportarCobertura() { window.location.href = '/exportar/cobertura-categorias'; }

// ================================================================
// TAB 3: Categorías sin Cat. Precio
// ================================================================
function cargarSinCat() {
    $('#loading-sincat').show();
    $('#wrapper-sincat, #empty-sincat').hide();
    $.get('/reportes/escalas/sin-precios-cat', function (data) {
        $('#badge-sincat').text(data.length || '0');
        if (!data.length) {
            $('#loading-sincat').hide();
            $('#empty-sincat').show();
            _sincatLoaded = true;
            return;
        }
        var html = '';
        data.forEach(function (r) {
            html += '<tr>'
                + '<td>' + r.id + '</td>'
                + '<td>' + (r.nombre_categoria || '—') + '</td>'
                + '<td>' + (r.descripcion_categoria || '—') + '</td>'
                + '<td class="text-center">' + fmtEstado(r.estado) + '</td>'
                + '<td class="text-center">' + fmtFecha(r.created_at) + '</td>'
                + '</tr>';
        });
        $('#tbody-sincat').html(html);
        if ($.fn.DataTable.isDataTable('#tbl_sincat')) $('#tbl_sincat').DataTable().destroy();
        $('#tbl_sincat').DataTable({ language: { url: '/js/plugins/dataTables/i18n/Spanish.json' }, pageLength: 15, responsive: true });
        $('#loading-sincat').hide();
        $('#wrapper-sincat').show();
        _sincatLoaded = true;
    }).fail(function () {
        $('#loading-sincat').hide();
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el reporte.' });
    });
}

function exportarSinCat() { window.location.href = '/exportar/cat-sin-precios'; }

// ================================================================
// TAB 4: Productos sin precios
// ================================================================
function cargarSinPrecio() {
    $('#loading-sinprecio').show();
    $('#wrapper-sinprecio, #empty-sinprecio').hide();
    $.get('/reportes/escalas/sin-precios-prod', function (data) {
        $('#badge-sinprecio').text(data.length || '0');
        if (!data.length) {
            $('#loading-sinprecio').hide();
            $('#empty-sinprecio').show();
            _sinprecioLoaded = true;
            return;
        }
        var html = '';
        data.forEach(function (r) {
            html += '<tr><td>' + r.id + '</td><td>' + (r.codigo_barra || '—') + '</td><td>' + (r.nombre || '—') + '</td></tr>';
        });
        $('#tbody-sinprecio').html(html);
        if ($.fn.DataTable.isDataTable('#tbl_sinprecio')) $('#tbl_sinprecio').DataTable().destroy();
        $('#tbl_sinprecio').DataTable({ language: { url: '/js/plugins/dataTables/i18n/Spanish.json' }, pageLength: 25, responsive: true });
        $('#loading-sinprecio').hide();
        $('#wrapper-sinprecio').show();
        _sinprecioLoaded = true;
    }).fail(function () {
        $('#loading-sinprecio').hide();
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el reporte.' });
    });
}

function exportarSinPrecio() { window.location.href = '/exportar/productos-sin-precios'; }

// ================================================================
// TAB 5: Comparativo por Producto
// ================================================================
function cargarComparativo() {
    var prodId = $('#select-produto-comparativo').val();
    if (!prodId) {
        Swal.fire({ icon: 'warning', title: 'Seleccione un producto', text: 'Escriba y elija un producto de la lista.' });
        return;
    }
    _comparativoProdId = prodId;
    $('#placeholder-comparativo').hide();
    $('#loading-comparativo').show();
    $('#wrapper-comparativo').hide();

    $.get('/reportes/escalas/comparativo?produto_id=' + prodId, function (data) {
        if (!data.length) {
            $('#loading-comparativo').hide();
            $('#placeholder-comparativo').show().html('<i class="fa fa-info-circle mr-1"></i>No se encontraron precios para este producto.');
            $('#btn-export-comparativo').prop('disabled', true);
            return;
        }
        var html = '';
        data.forEach(function (r) {
            html += '<tr>'
                + '<td>' + (r.categoria_cliente || '—') + '</td>'
                + '<td>' + (r.categoria_precio  || '—') + '</td>'
                + '<td class="text-center">' + (r.porc_precio_a || 0) + '%</td>'
                + '<td class="text-center">' + (r.porc_precio_b || 0) + '%</td>'
                + '<td class="text-center">' + (r.porc_precio_c || 0) + '%</td>'
                + '<td class="text-center">' + (r.porc_precio_d || 0) + '%</td>'
                + '<td class="text-right">L. ' + parseFloat(r.precio_base_venta || 0).toFixed(2) + '</td>'
                + '<td class="text-right">L. ' + parseFloat(r.precio_a || 0).toFixed(2) + '</td>'
                + '<td class="text-right">L. ' + parseFloat(r.precio_b || 0).toFixed(2) + '</td>'
                + '<td class="text-right">L. ' + parseFloat(r.precio_c || 0).toFixed(2) + '</td>'
                + '<td class="text-right">L. ' + parseFloat(r.precio_d || 0).toFixed(2) + '</td>'
                + '<td class="text-center">' + fmtEstado(r.estado) + '</td>'
                + '</tr>';
        });
        $('#tbody-comparativo').html(html);
        if ($.fn.DataTable.isDataTable('#tbl_comparativo')) $('#tbl_comparativo').DataTable().destroy();
        $('#tbl_comparativo').DataTable({
            language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
            pageLength: 25, order: [[0, 'asc']], responsive: true
        });
        $('#loading-comparativo').hide();
        $('#wrapper-comparativo').show();
        $('#btn-export-comparativo').prop('disabled', false);
        $('#info-comparativo').html('<i class="fa fa-info-circle mr-1"></i>Mostrando ' + data.length + ' categoría(s).');
    }).fail(function () {
        $('#loading-comparativo').hide();
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el comparativo.' });
    });
}

function exportarComparativo() {
    if (!_comparativoProdId) return;
    window.location.href = '/exportar/comparativo-produto?produto_id=' + _comparativoProdId;
}

// ================================================================
// TAB 6: Resumen de Categorías de Precio
// ================================================================
function cargarResumen() {
    var catClienteId = $('#filtro-resumen-cat-cliente').val() || '';
    var estadoId     = $('#filtro-resumen-estado').val()      || '';
    $('#loading-resumen').show();
    $('#wrapper-resumen').hide();

    $.get('/reportes/escalas/resumen-cat-precio?cat_cliente_id=' + catClienteId + '&estado_id=' + estadoId, function (data) {
        var html = '';
        data.forEach(function (r) {
            html += '<tr>'
                + '<td>' + r.id + '</td>'
                + '<td>' + (r.categoria_precio  || '—') + '</td>'
                + '<td>' + (r.categoria_cliente || '—') + '</td>'
                + '<td class="text-center">' + (r.porc_precio_a || 0) + '%</td>'
                + '<td class="text-center">' + (r.porc_precio_b || 0) + '%</td>'
                + '<td class="text-center">' + (r.porc_precio_c || 0) + '%</td>'
                + '<td class="text-center">' + (r.porc_precio_d || 0) + '%</td>'
                + '<td class="text-center">' + fmtEstado(r.estado) + '</td>'
                + '<td class="text-center">' + (r.total_productos || 0) + '</td>'
                + '<td class="text-center">' + fmtFecha(r.fecha_ultima_actualizacion) + '</td>'
                + '</tr>';
        });
        $('#tbody-resumen').html(html || '<tr><td colspan="10" class="text-center text-muted py-3">Sin datos para los filtros seleccionados.</td></tr>');
        if ($.fn.DataTable.isDataTable('#tbl_resumen')) $('#tbl_resumen').DataTable().destroy();
        $('#tbl_resumen').DataTable({
            language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
            order: [[2, 'asc'], [1, 'asc']], pageLength: 25, responsive: true
        });
        $('#loading-resumen').hide();
        $('#wrapper-resumen').show();
    }).fail(function () {
        $('#loading-resumen').hide();
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el resumen.' });
    });
}

function exportarResumen() {
    var catClienteId = $('#filtro-resumen-cat-cliente').val() || '';
    var estadoId     = $('#filtro-resumen-estado').val()      || '';
    window.location.href = '/exportar/resumen-cat-precio?cat_cliente_id=' + catClienteId + '&estado_id=' + estadoId;
}

// ================================================================
// TAB 7: Comisiones Asignadas
// ================================================================
function cargarComisiones() {
    var catClienteId = $('#filtro-comision-cat-cliente').val() || '';
    var rolId        = $('#filtro-comision-rol').val()         || '';
    var estadoId     = $('#filtro-comision-estado').val()      || '';
    $('#loading-comisiones').show();
    $('#wrapper-comisiones').hide();

    var url = '/reportes/escalas/comisiones?cat_cliente_id=' + catClienteId
            + '&rol_id=' + rolId + '&estado_id=' + estadoId;

    $.get(url, function (data) {
        var total   = data.length;
        var activos = data.filter(function (r) { return r.estado === 'Activo'; }).length;
        var rolesSet = {};
        var sumPct   = 0;
        data.forEach(function (r) { rolesSet[r.rol] = 1; sumPct += parseFloat(r.porcentaje_comision || 0); });
        var prom = total ? (sumPct / total).toFixed(2) : '0.00';

        $('#stat-com-total').text(total);
        $('#stat-com-activos').text(activos);
        $('#stat-com-roles').text(Object.keys(rolesSet).length);
        $('#stat-com-prom').text(prom + '%');
        $('#stats-comisiones').show();

        var html = '';
        data.forEach(function (r) {
            html += '<tr>'
                + '<td>' + r.id + '</td>'
                + '<td>' + (r.categoria_cliente || '—') + '</td>'
                + '<td>' + (r.categoria_precio  || '—') + '</td>'
                + '<td>' + (r.rol || '—') + '</td>'
                + '<td class="text-center"><strong class="text-success">' + parseFloat(r.porcentaje_comision || 0).toFixed(2) + '%</strong></td>'
                + '<td class="text-center">' + (r.porc_precio_a != null ? r.porc_precio_a + '%' : '—') + '</td>'
                + '<td class="text-center">' + (r.porc_precio_b != null ? r.porc_precio_b + '%' : '—') + '</td>'
                + '<td class="text-center">' + (r.porc_precio_c != null ? r.porc_precio_c + '%' : '—') + '</td>'
                + '<td class="text-center">' + (r.porc_precio_d != null ? r.porc_precio_d + '%' : '—') + '</td>'
                + '<td class="text-center">' + fmtEstado(r.estado) + '</td>'
                + '</tr>';
        });
        $('#tbody-comisiones').html(html || '<tr><td colspan="10" class="text-center text-muted py-3">Sin registros para los filtros aplicados.</td></tr>');
        if ($.fn.DataTable.isDataTable('#tbl_comisiones')) $('#tbl_comisiones').DataTable().destroy();
        $('#tbl_comisiones').DataTable({
            language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
            order: [[1, 'asc'], [2, 'asc']], pageLength: 25, responsive: true
        });
        $('#loading-comisiones').hide();
        $('#wrapper-comisiones').show();
        _comisionesLoaded = true;
    }).fail(function () {
        $('#loading-comisiones').hide();
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar las comisiones.' });
    });
}

// Permite recargar forzando (botón "Aplicar filtros")
function recargarComisiones() {
    _comisionesLoaded = false;
    cargarComisiones();
}

