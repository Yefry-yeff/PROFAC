/**
 * libroventarep.js  —  Reporte Libro de Venta (v2)
 * DataTables con filtros avanzados — Mismo patrón que ventascobros.js
 */

/* ────────────────────────────────────────────────────────────────────
 *  Estado global
 * ──────────────────────────────────────────────────────────────────── */
var lvTable = null;

/* ────────────────────────────────────────────────────────────────────
 *  Lectura de filtros
 * ──────────────────────────────────────────────────────────────────── */
function getFiltrosLV() {
    return {
        cliente:       $('#fil_lv_cliente').val()      || '',
        factura:       $('#fil_lv_factura').val()      || '',
        vendedor:      $('#fil_lv_vendedor').val()     || '',
        modo_pago:     $('#fil_lv_modo_pago').val()    || '',
        fecha_desde:   $('#fil_lv_fecha_desde').val()  || '',
        fecha_hasta:   $('#fil_lv_fecha_hasta').val()  || ''
    };
}

function buildQueryStringLV(f) {
    var parts = [];
    parts.push('tipo=4');
    $.each(f, function(k, v) { if (v) parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(v)); });
    return parts.join('&');
}

/* ────────────────────────────────────────────────────────────────────
 *  Formatters
 * ──────────────────────────────────────────────────────────────────── */
function fmtLpsLV(v) {
    var n = parseFloat(v) || 0;
    return 'L ' + n.toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function fmtFechaLV(v) {
    if (!v) return '<span style="color:#9ca3af;">—</span>';
    var d = new Date(v);
    if (isNaN(d)) return v;
    return d.toLocaleDateString('es-HN', { day:'2-digit', month:'2-digit', year:'numeric' });
}
function escHtmlLV(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ────────────────────────────────────────────────────────────────────
 *  DataTable principal
 * ──────────────────────────────────────────────────────────────────── */
function cargarTablaLV() {
    if (lvTable) { lvTable.destroy(); }
    $('#tbl_libro_venta tbody').empty();

    var qs = buildQueryStringLV(getFiltrosLV());

    lvTable = $('#tbl_libro_venta').DataTable({
        processing:    true,
        serverSide:    true,
        responsive:    false,
        language:      { url: '/js/plugins/dataTables/i18n/Spanish.json', processing: '<i class="fa fa-spinner fa-spin"></i> Cargando...' },
        ajax: {
            url:  '/reporte/Libroventarep/datos?' + qs,
            type: 'GET',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataSrc: function(json) {
                setKpisLV(
                    json.kpi_total_vendido,
                    json.kpi_total_isv,
                    json.kpi_total_gravado,
                    json.kpi_total_registros
                );
                return json.data;
            },
            error: function(xhr) {
                Swal.fire({ icon:'error', title:'Error', text: 'Error al cargar el reporte: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : xhr.status) });
            }
        },
        columns: [
            { data: 'VENDEDOR' },
            { data: 'CLIENTE' },
            { data: 'FACTURA', render: function(d) { return '<strong>' + escHtmlLV(d) + '</strong>'; } },
            { data: 'EXONERADO', className: 'text-right', render: function(d) { return fmtLpsLV(d); } },
            { data: 'GRAVADO', className: 'text-right', render: function(d) { return fmtLpsLV(d); } },
            { data: 'EXCENTO', className: 'text-right', render: function(d) { return fmtLpsLV(d); } },
            { data: 'SUBTOTAL', className: 'text-right', render: function(d) { return fmtLpsLV(d); } },
            { data: 'ISV', className: 'text-right', render: function(d) { return fmtLpsLV(d); } },
            { data: 'TOTAL', className: 'text-right', render: function(d) { return '<strong>' + fmtLpsLV(d) + '</strong>'; } },
            { data: 'FECHA COMPRA', render: function(d) { return fmtFechaLV(d); } }
        ],
        order: [[2, 'desc']],
        pageLength: 25,
        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
        initComplete: function() {
            mostrarFiltrosActivosLV();
        }
    });
}

/* ────────────────────────────────────────────────────────────────────
 *  KPIs
 * ──────────────────────────────────────────────────────────────────── */
function setKpisLV(totalVendido, totalISV, totalGravado, totalRegistros) {
    $('#kpi_total_vendido').text(fmtLpsLV(totalVendido || 0));
    $('#kpi_total_isv').text(fmtLpsLV(totalISV || 0));
    $('#kpi_total_gravado').text(fmtLpsLV(totalGravado || 0));
    $('#kpi_total_registros').text(parseInt(totalRegistros || 0, 10));
}

/* ────────────────────────────────────────────────────────────────────
 *  Barra de filtros activos (badges)
 * ──────────────────────────────────────────────────────────────────── */
function mostrarFiltrosActivosLV() {
    var f = getFiltrosLV();
    var defs = [
        { key: 'cliente',     el: '#fil_lv_cliente',     lbl: 'Cliente',      isSelect: true },
        { key: 'factura',     el: '#fil_lv_factura',     lbl: 'Factura',      isSelect: false },
        { key: 'vendedor',    el: '#fil_lv_vendedor',    lbl: 'Vendedor',     isSelect: true },
        { key: 'modo_pago',   el: '#fil_lv_modo_pago',   lbl: 'Pago',         isSelect: true },
        { key: 'fecha_desde', el: '#fil_lv_fecha_desde', lbl: 'Desde',        isSelect: false },
        { key: 'fecha_hasta', el: '#fil_lv_fecha_hasta', lbl: 'Hasta',        isSelect: false }
    ];
    var bar = $('#lv_filtros_bar').empty();
    var has = false;
    $.each(defs, function(i, d) {
        var val = f[d.key];
        if (!val) return;
        has = true;
        var displayVal = val;
        if (d.isSelect) {
            var opt = $(d.el).find('option[value="' + val + '"]');
            if (opt.length) displayVal = opt.text();
        }
        bar.append(
            '<div class="filtro-badge">' +
            '<strong>' + d.lbl + ':</strong> ' + escHtmlLV(displayVal) +
            ' <span class="filtro-remove" data-el="' + d.el + '" title="Limpiar este filtro">×</span>' +
            '</div>'
        );
    });
    if (has) { bar.show(); } else { bar.hide(); }
}

function limpiarFiltroLV(el) {
    if ($.fn.select2) {
        $(el).val('').trigger('change');
    } else {
        $(el).val('');
    }
    aplicarFiltrosLV();
}

/* ────────────────────────────────────────────────────────────────────
 *  Limpiar todos los filtros (con fechas por defecto)
 * ──────────────────────────────────────────────────────────────────── */
function limpiarFiltrosLV() {
    if ($.fn.select2) {
        $('#fil_lv_cliente, #fil_lv_vendedor').val('').trigger('change');
    } else {
        $('#fil_lv_cliente, #fil_lv_vendedor').val('');
    }
    $('#fil_lv_modo_pago').val('');
    $('#fil_lv_factura').val('');
    setDefaultDatesLV();
    aplicarFiltrosLV();
}

/* ────────────────────────────────────────────────────────────────────
 *  Fechas por defecto — último mes calendario
 * ──────────────────────────────────────────────────────────────────── */
function setDefaultDatesLV() {
    var now  = new Date();
    var y    = now.getMonth() === 0 ? now.getFullYear() - 1 : now.getFullYear();
    var m    = now.getMonth() === 0 ? 11 : now.getMonth() - 1;  // 0-based
    function fmt(d) {
        return d.getFullYear() + '-' +
               String(d.getMonth() + 1).padStart(2, '0') + '-' +
               String(d.getDate()).padStart(2, '0');
    }
    $('#fil_lv_fecha_desde').val(fmt(new Date(y, m, 1)));
    $('#fil_lv_fecha_hasta').val(fmt(new Date(y, m + 1, 0)));
}

/* ────────────────────────────────────────────────────────────────────
 *  Aplicar filtros
 * ──────────────────────────────────────────────────────────────────── */
function aplicarFiltrosLV() {
    $('#modalFiltrosLV').modal('hide');
    cargarTablaLV();
}

/* ────────────────────────────────────────────────────────────────────
 *  Exportar a Excel
 * ──────────────────────────────────────────────────────────────────── */
function _exportarConCargaLV(url, title, detail) {
    var frameName = 'lv_export_frame_' + Date.now();
    var $iframe = $('<iframe>', { name: frameName, style: 'display:none;' });

    var f = getFiltrosLV();
    var tok = $('meta[name="csrf-token"]').attr('content');
    var form = $('<form method="POST"></form>').attr('action', url).attr('target', frameName);
    var fields = {
        _token: tok,
        cliente: f.cliente || '',
        cliente_id: f.cliente || '',
        vendedor: f.vendedor || '',
        vendedor_id: f.vendedor || '',
        modo_pago: f.modo_pago || '',
        factura: f.factura || '',
        fecha_desde: f.fecha_desde || '',
        fecha_hasta: f.fecha_hasta || ''
    };

    $.each(fields, function(k, v) {
        form.append($('<input type="hidden">').attr('name', k).val(v));
    });

    $('body').append($iframe);
    $('body').append(form);

    Swal.fire({
        title: title || 'Generando archivo',
        html: (detail || 'Preparando reporte...') + '<br><small>Este proceso puede tardar varios minutos.</small>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: function() { Swal.showLoading(); }
    });

    var closed = false;
    var safeClose = function() {
        if (closed) return;
        closed = true;
        Swal.close();
        form.remove();
        setTimeout(function() { $iframe.remove(); }, 800);
    };

    $iframe.on('load', function() {
        safeClose();
    });

    setTimeout(function() {
        safeClose();
    }, 12000);

    form.trigger('submit');
}

function _setCookieLV(name, value, seconds) {
    var expires = '';
    if (typeof seconds === 'number') {
        var d = new Date();
        d.setTime(d.getTime() + (seconds * 1000));
        expires = '; expires=' + d.toUTCString();
    }
    document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/';
}

function _getCookieLV(name) {
    var prefix = name + '=';
    var parts = document.cookie ? document.cookie.split(';') : [];
    for (var i = 0; i < parts.length; i++) {
        var c = parts[i].trim();
        if (c.indexOf(prefix) === 0) {
            return decodeURIComponent(c.substring(prefix.length));
        }
    }
    return '';
}

function _exportarPdfConEsperaLV(url, title, detail) {
    var f = getFiltrosLV();
    var tok = $('meta[name="csrf-token"]').attr('content');
    var downloadToken = 'lvpdf_' + Date.now() + '_' + Math.floor(Math.random() * 1000000);
    var cookieName = 'lv_pdf_download_token';

    // limpiar token previo para evitar falsos positivos en el polling
    _setCookieLV(cookieName, '', -1);

    var form = $('<form method="POST"></form>').attr('action', url);
    var fields = {
        _token: tok,
        download_token: downloadToken,
        cliente: f.cliente || '',
        cliente_id: f.cliente || '',
        vendedor: f.vendedor || '',
        vendedor_id: f.vendedor || '',
        modo_pago: f.modo_pago || '',
        factura: f.factura || '',
        fecha_desde: f.fecha_desde || '',
        fecha_hasta: f.fecha_hasta || ''
    };

    $.each(fields, function(k, v) {
        form.append($('<input type="hidden">').attr('name', k).val(v));
    });

    $('body').append(form);

    Swal.fire({
        title: title || 'Generando PDF',
        html: (detail || 'Preparando documento...') + '<br><small>Este proceso puede tardar varios minutos.</small>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: function() { Swal.showLoading(); }
    });

    var startedAt = Date.now();
    var timer = setInterval(function() {
        if (_getCookieLV(cookieName) === downloadToken) {
            clearInterval(timer);
            _setCookieLV(cookieName, '', -1);
            Swal.close();
        } else if (Date.now() - startedAt > (15 * 60 * 1000)) {
            clearInterval(timer);
            Swal.fire({
                icon: 'warning',
                title: 'Demora en descarga',
                text: 'La generación del PDF sigue en proceso. Intenta nuevamente en unos minutos.'
            });
        }
    }, 400);

    form.trigger('submit');
    setTimeout(function() { form.remove(); }, 1500);
}

function exportarExcelLV() {
    var f = getFiltrosLV();
    var fechaDesde = f.fecha_desde || '1900-01-01';
    var fechaHasta = f.fecha_hasta || new Date().toISOString().split('T')[0];
    var tok = $('meta[name="csrf-token"]').attr('content');
    var downloadToken = 'lvxls_' + Date.now() + '_' + Math.floor(Math.random() * 1000000);
    var cookieName = 'lv_excel_download_token';

    _setCookieLV(cookieName, '', -1);

    var url = '/reporte/Libroventarep/exportar-excel/4/' + encodeURIComponent(fechaDesde) + '/' + encodeURIComponent(fechaHasta);
    var form = $('<form method="POST"></form>').attr('action', url);
    var fields = {
        _token: tok,
        download_token: downloadToken,
        cliente: f.cliente || '',
        cliente_id: f.cliente || '',
        vendedor: f.vendedor || '',
        vendedor_id: f.vendedor || '',
        modo_pago: f.modo_pago || '',
        factura: f.factura || '',
        fecha_desde: fechaDesde,
        fecha_hasta: fechaHasta
    };

    $.each(fields, function(k, v) {
        form.append($('<input type="hidden">').attr('name', k).val(v));
    });

    $('body').append(form);

    Swal.fire({
        title: 'Generando Excel',
        html: 'Preparando reporte...<br><small>Este proceso puede tardar varios minutos.</small>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: function() { Swal.showLoading(); }
    });

    var startedAt = Date.now();
    var timer = setInterval(function() {
        if (_getCookieLV(cookieName) === downloadToken) {
            clearInterval(timer);
            _setCookieLV(cookieName, '', -1);
            Swal.close();
        } else if (Date.now() - startedAt > (15 * 60 * 1000)) {
            clearInterval(timer);
            Swal.fire({
                icon: 'warning',
                title: 'Demora en descarga',
                text: 'La generación del Excel sigue en proceso. Intenta nuevamente en unos minutos.'
            });
        }
    }, 400);

    form.trigger('submit');
    setTimeout(function() { form.remove(); }, 1500);
}

function exportarPdfLV() {
    var f = getFiltrosLV();
    var fechaDesde = f.fecha_desde || '1900-01-01';
    var fechaHasta = f.fecha_hasta || new Date().toISOString().split('T')[0];
    _exportarPdfConEsperaLV(
        '/reporte/Libroventarep/exportar-pdf/4/' + encodeURIComponent(fechaDesde) + '/' + encodeURIComponent(fechaHasta),
        'Generando PDF',
        'Preparando documento...'
    );
}

/* ────────────────────────────────────────────────────────────────────
 *  Document ready
 * ──────────────────────────────────────────────────────────────────── */
$(document).ready(function() {
    // Inicializar Select2 para los selects
    if ($.fn.select2) {
        $('#fil_lv_cliente').select2({
            placeholder: '— Todos —', 
            allowClear: true,
            dropdownParent: $('#modalFiltrosLV')
        });
        $('#fil_lv_vendedor').select2({
            placeholder: '— Todos —', 
            allowClear: true,
            dropdownParent: $('#modalFiltrosLV')
        });
    }

    // Establecer fechas por defecto = último mes
    setDefaultDatesLV();

    // Carga inicial con filtros por defecto
    mostrarFiltrosActivosLV();
    cargarTablaLV();

    // Enter en factura aplica filtros
    $(document).on('keypress', '#fil_lv_factura', function(e) {
        if (e.which === 13) aplicarFiltrosLV();
    });

    // Quitar filtro individual desde la barra de badges
    $(document).on('click', '.filtro-remove', function() {
        var el = $(this).data('el');
        limpiarFiltroLV(el);
    });

    // Detectar cambios en los inputs date
    $(document).on('change', '#fil_lv_fecha_desde, #fil_lv_fecha_hasta', function() {
        // Si ambas fechas están vacías, cargar tabla igualmente (sin validación)
        // Si solo una está vacía, permitir
    });
});



