/* ────────────────────────────────────────────────────────────────────
 *  LIBRO DE COBROS - Sistema de Filtros
 *  Patron igual a libroventarep.js
 * ──────────────────────────────────────────────────────────────────── */

var lcTable = null;

function fmtLpsLC(n) {
    return 'L ' + parseFloat(n || 0).toLocaleString('es-HN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function escHtmlLC(s) {
    return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function fmtFechaLC(d) {
    if (!d) return '—';
    d = String(d).split(' ')[0];
    var p = d.split('-');
    return p[2] + '/' + p[1] + '/' + p[0];
}

function getFiltrosLC() {
    return {
        cliente: $('#fil_lc_cliente').val() || '',
        factura: $('#fil_lc_factura').val() || '',
        vendedor: $('#fil_lc_vendedor').val() || '',
        banco: $('#fil_lc_banco').val() || '',
        fecha_desde: $('#fil_lc_fecha_desde').val() || '',
        fecha_hasta: $('#fil_lc_fecha_hasta').val() || ''
    };
}

function buildQueryStringLC(f) {
    var qs = 'tipo=3';
    $.each(f, function(k, v) {
        if (v && v !== '') {
            qs += '&' + k + '=' + encodeURIComponent(v);
        }
    });
    return qs;
}

function cargarTablaLC() {
    if (lcTable) {
        lcTable.destroy();
    }
    $('#tbl_libro_cobros tbody').empty();

    var qs = buildQueryStringLC(getFiltrosLC());

    lcTable = $('#tbl_libro_cobros').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        language: {
            url: '/js/plugins/dataTables/i18n/Spanish.json',
            processing: '<i class="fa fa-spinner fa-spin"></i> Cargando...'
        },
        ajax: {
            url: '/reporte/Librocobrosrep/datos?' + qs,
            type: 'GET',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataSrc: function(json) {
                setKpisLC(
                    json.kpi_cobros,
                    json.kpi_total_cobrado,
                    json.kpi_completas
                );
                return json.data;
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar el reporte: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : xhr.status)
                });
            }
        },
        columns: [
            { data: 'fecha_venta', render: function(d) { return fmtFechaLC(d); } },
            { data: 'fecha_vencimiento', render: function(d) { return fmtFechaLC(d); } },
            { data: 'fecha_pago', render: function(d) { return fmtFechaLC(d); } },
            { data: 'cliente' },
            { data: 'vendedor' },
            { data: 'factura', render: function(d) { return '<strong>' + escHtmlLC(d) + '</strong>'; } },
            { data: 'monto_cobrado', className: 'text-right', render: function(d) { return fmtLpsLC(d); } },
            { data: 'estado_factura', render: function(d) { return '<span class="badge ' + (d === 'PAGADA' ? 'badge-success' : 'badge-warning') + '">' + d + '</span>'; } },
            { data: 'banco' },
            { data: 'cuenta_banco' },
            { data: 'observaciones' },
            { data: 'exonerado', className: 'text-right', render: function(d) { return fmtLpsLC(d); } },
            { data: 'gravado', className: 'text-right', render: function(d) { return fmtLpsLC(d); } },
            { data: 'excento', className: 'text-right', render: function(d) { return fmtLpsLC(d); } },
            { data: 'subtotal', className: 'text-right', render: function(d) { return fmtLpsLC(d); } },
            { data: 'isv', className: 'text-right', render: function(d) { return fmtLpsLC(d); } },
            { data: 'total_factura', className: 'text-right', render: function(d) { return '<strong>' + fmtLpsLC(d) + '</strong>'; } }
        ],
        order: [[2, 'asc']],
        pageLength: 25,
        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
        initComplete: function() {
            mostrarFiltrosActivosLC();
        }
    });
}

function setKpisLC(totalCobros, totalCobrado, facturasPagadas) {
    $('#lc_kpi_registros').text(parseInt(totalCobros || 0, 10));
    $('#lc_kpi_total_pagado').text(fmtLpsLC(totalCobrado || 0));
    $('#lc_kpi_completas').text(parseInt(facturasPagadas || 0, 10));
}

function mostrarFiltrosActivosLC() {
    var f = getFiltrosLC();
    var bar = $('#lc_filtros_bar');
    var html = '';

    var defs = [
        { key: 'fecha_desde', icon: 'fa-calendar', label: 'Desde:', fmt: function(v) { return v; } },
        { key: 'fecha_hasta', icon: 'fa-calendar', label: 'Hasta:', fmt: function(v) { return v; } },
        { key: 'cliente', icon: 'fa-user', label: 'Cliente:', fmt: function(v) { var t = $('#fil_lc_cliente option[value="' + v + '"]').text(); return t || v; } },
        { key: 'vendedor', icon: 'fa-briefcase', label: 'Vendedor:', fmt: function(v) { var t = $('#fil_lc_vendedor option[value="' + v + '"]').text(); return t || v; } },
        { key: 'banco', icon: 'fa-bank', label: 'Banco:', fmt: function(v) { var t = $('#fil_lc_banco option[value="' + v + '"]').text(); return t || v; } },
        { key: 'factura', icon: 'fa-file', label: 'Factura:', fmt: function(v) { return v; } }
    ];

    $.each(defs, function(i, def) {
        if (f[def.key]) {
            html += '<span class="lc-filtro-badge" data-el="' + def.key + '">' +
                '<i class="fa ' + def.icon + '"></i> ' + def.label + ' <strong>' + escHtmlLC(def.fmt(f[def.key])) + '</strong>' +
                '<span class="fr" onclick="limpiarFiltroLC(this)">×</span>' +
                '</span> ';
        }
    });

    if (html) {
        bar.html(html).show();
    } else {
        bar.empty().hide();
    }
}

function limpiarFiltroLC(el) {
    var filtro = $(el).closest('.lc-filtro-badge').data('el');
    var ids = {
        cliente: '#fil_lc_cliente',
        vendedor: '#fil_lc_vendedor',
        banco: '#fil_lc_banco',
        factura: '#fil_lc_factura',
        fecha_desde: '#fil_lc_fecha_desde',
        fecha_hasta: '#fil_lc_fecha_hasta'
    };

    if (ids[filtro]) {
        $(ids[filtro]).val('').trigger('change');
    }

    aplicarFiltrosLC();
}

function limpiarFiltrosLC() {
    $('#fil_lc_cliente').val('').trigger('change');
    $('#fil_lc_vendedor').val('').trigger('change');
    $('#fil_lc_banco').val('').trigger('change');
    $('#fil_lc_factura').val('');
    setDefaultDatesLC();
    aplicarFiltrosLC();
}

function setDefaultDatesLC() {
    var now = new Date();
    var year = now.getFullYear();
    var month = now.getMonth();
    var m = (month === 0) ? 11 : month - 1;
    var y = (month === 0) ? year - 1 : year;
    var fin = new Date(y, m + 1, 0);
    var pad = function(n) { return String(n).padStart(2, '0'); };

    $('#fil_lc_fecha_desde').val(y + '-' + pad(m + 1) + '-01');
    $('#fil_lc_fecha_hasta').val(y + '-' + pad(m + 1) + '-' + pad(fin.getDate()));
}

function aplicarFiltrosLC() {
    $('#modalFiltrosLC').modal('hide');
    cargarTablaLC();
}

function _exportarFormLC(url) {
    var f = getFiltrosLC();
    var tok = $('meta[name="csrf-token"]').attr('content');
    var form = $('<form method="POST"></form>').attr('action', url);
    var fields = {
        _token: tok,
        cliente: f.cliente || '',
        cliente_id: f.cliente || '',
        vendedor: f.vendedor || '',
        vendedor_id: f.vendedor || '',
        banco: f.banco || '',
        banco_id: f.banco || '',
        factura: f.factura || '',
        fecha_desde: f.fecha_desde || '',
        fecha_hasta: f.fecha_hasta || ''
    };

    $.each(fields, function(k, v) {
        form.append($('<input type="hidden">').attr('name', k).val(v));
    });

    $('body').append(form);
    form.submit();
    form.remove();
}

function _exportarConCargaLC(url, title, detail) {
    var frameName = 'lc_export_frame_' + Date.now();
    var $iframe = $('<iframe>', {
        name: frameName,
        style: 'display:none;'
    });

    var f = getFiltrosLC();
    var tok = $('meta[name="csrf-token"]').attr('content');
    var form = $('<form method="POST"></form>').attr('action', url).attr('target', frameName);
    var fields = {
        _token: tok,
        cliente: f.cliente || '',
        cliente_id: f.cliente || '',
        vendedor: f.vendedor || '',
        vendedor_id: f.vendedor || '',
        banco: f.banco || '',
        banco_id: f.banco || '',
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

function _setCookieLC(name, value, seconds) {
    var expires = '';
    if (typeof seconds === 'number') {
        var d = new Date();
        d.setTime(d.getTime() + (seconds * 1000));
        expires = '; expires=' + d.toUTCString();
    }
    document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/';
}

function _getCookieLC(name) {
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

function _exportarPdfConEsperaLC(url, title, detail) {
    var f = getFiltrosLC();
    var tok = $('meta[name="csrf-token"]').attr('content');
    var downloadToken = 'lcpdf_' + Date.now() + '_' + Math.floor(Math.random() * 1000000);
    var cookieName = 'lc_pdf_download_token';

    // limpiar token previo para evitar falsos positivos en el polling
    _setCookieLC(cookieName, '', -1);

    var form = $('<form method="POST"></form>').attr('action', url);
    var fields = {
        _token: tok,
        download_token: downloadToken,
        cliente: f.cliente || '',
        cliente_id: f.cliente || '',
        vendedor: f.vendedor || '',
        vendedor_id: f.vendedor || '',
        banco: f.banco || '',
        banco_id: f.banco || '',
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
        if (_getCookieLC(cookieName) === downloadToken) {
            clearInterval(timer);
            _setCookieLC(cookieName, '', -1);
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

function exportarExcelLC() {
    var f = getFiltrosLC();
    var fechaDesde = f.fecha_desde || '1900-01-01';
    var fechaHasta = f.fecha_hasta || new Date().toISOString().split('T')[0];
    var tok = $('meta[name="csrf-token"]').attr('content');
    var downloadToken = 'lcxls_' + Date.now() + '_' + Math.floor(Math.random() * 1000000);
    var cookieName = 'lc_excel_download_token';

    _setCookieLC(cookieName, '', -1);

    var url = '/reporte/Librocobrosrep/exportar-excel/3/' + encodeURIComponent(fechaDesde) + '/' + encodeURIComponent(fechaHasta);
    var form = $('<form method="POST"></form>').attr('action', url);
    var fields = {
        _token: tok,
        download_token: downloadToken,
        cliente: f.cliente || '',
        cliente_id: f.cliente || '',
        vendedor: f.vendedor || '',
        vendedor_id: f.vendedor || '',
        banco: f.banco || '',
        banco_id: f.banco || '',
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
        if (_getCookieLC(cookieName) === downloadToken) {
            clearInterval(timer);
            _setCookieLC(cookieName, '', -1);
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

function exportarPdfLC() {
    var f = getFiltrosLC();
    var fechaDesde = f.fecha_desde || '1900-01-01';
    var fechaHasta = f.fecha_hasta || new Date().toISOString().split('T')[0];
    _exportarPdfConEsperaLC(
        '/reporte/Librocobrosrep/exportar-pdf/3/' + encodeURIComponent(fechaDesde) + '/' + encodeURIComponent(fechaHasta),
        'Generando PDF',
        'Preparando documento...'
    );
}

/* Compatibilidad con botones existentes en la vista */
function lcExportarExcel() { exportarExcelLC(); }
function lcExportarPdf() { exportarPdfLC(); }

$(document).ready(function() {
    $('#fil_lc_cliente').select2({ placeholder: '— Todos —', allowClear: true, dropdownParent: $('#modalFiltrosLC') });
    $('#fil_lc_vendedor').select2({ placeholder: '— Todos —', allowClear: true, dropdownParent: $('#modalFiltrosLC') });
    $('#fil_lc_banco').select2({ placeholder: '— Todos —', allowClear: true, dropdownParent: $('#modalFiltrosLC') });

    setDefaultDatesLC();
    mostrarFiltrosActivosLC();
    cargarTablaLC();

    $('#fil_lc_factura').on('keypress', function(e) {
        if (e.which === 13) {
            aplicarFiltrosLC();
            return false;
        }
    });

    $(document).on('click', '.lc-filtro-badge .fr', function(e) {
        e.preventDefault();
        limpiarFiltroLC(this);
    });

    $('#btn_buscar_lc').on('click', function() {
        aplicarFiltrosLC();
    });
});
