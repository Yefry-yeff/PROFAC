/* ────────────────────────────────────────────────────────────────────
 *  LIBRO DE COBROS - Sistema de Filtros
 *  Basado en patrón ventascobros.js / libroventarep.js
 * ──────────────────────────────────────────────────────────────────── */

var lcTable = null;

/* Helpers ─────────────────────────────────────────────────────────────── */
function fmtLpsLC(n) { return 'L ' + parseFloat(n || 0).toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
function escHtmlLC(s) { return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }
function fmtFechaLC(d) { if (!d) return '—'; d = String(d).split(' ')[0]; var p = d.split('-'); return p[2] + '/' + p[1] + '/' + p[0]; }

/* ────────────────────────────────────────────────────────────────────
 *  Funciones de Filtros
 * ──────────────────────────────────────────────────────────────────── */
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

/* ────────────────────────────────────────────────────────────────────
 *  DataTable principal
 * ──────────────────────────────────────────────────────────────────── */
function cargarTablaLC() {
    if (lcTable) { lcTable.destroy(); }
    $('#tbl_libro_cobros tbody').empty();

    var qs = buildQueryStringLC(getFiltrosLC());

    lcTable = $('#tbl_libro_cobros').DataTable({
        processing:    true,
        serverSide:    true,
        responsive:    false,
        language:      { url: '/js/plugins/dataTables/i18n/Spanish.json', processing: '<i class="fa fa-spinner fa-spin"></i> Cargando...' },
        ajax: {
            url:  '/reporte/Librocobrosrep/datos?' + qs,
            type: 'GET',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            error: function(xhr) {
                Swal.fire({ icon:'error', title:'Error', text: 'Error al cargar el reporte: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : xhr.status) });
            }
        },
        columns: [
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
        order: [[0, 'desc']],
        pageLength: 25,
        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
        initComplete: function() {
            cargarKpisLC();
            mostrarFiltrosActivosLC();
        }
    });
}

/* ────────────────────────────────────────────────────────────────────
 *  KPI Pills
 * ──────────────────────────────────────────────────────────────────── */
function cargarKpisLC() {
    var totalCobros = 0, totalCobrado = 0, facturasPagadas = 0;
    var estadosVistos = {};

    $.each(lcTable.data(), function(i, row) {
        totalCobros++;
        totalCobrado += parseFloat(row.monto_cobrado || 0);
        
        var est = row.estado_factura;
        if (est === 'PAGADA') {
            if (!estadosVistos[est]) {
                facturasPagadas++;
                estadosVistos[est] = true;
            }
        }
    });

    $('#lc_kpi_registros').text(totalCobros);
    $('#lc_kpi_total_pagado').text(fmtLpsLC(totalCobrado));
    $('#lc_kpi_completas').text(facturasPagadas);
}

/* ────────────────────────────────────────────────────────────────────
 *  Filtros Activos (Badges)
 * ──────────────────────────────────────────────────────────────────── */
function mostrarFiltrosActivosLC() {
    var f = getFiltrosLC();
    var bar = $('#lc_filtros_bar');
    var html = '';
    var esc = function(s) { return escHtmlLC(s); };

    var defs = [
        { key: 'fecha_desde', el: '#fil_lc_fecha_desde', icon: 'fa-calendar', label: 'Desde:', fmt: function(v) { return v; } },
        { key: 'fecha_hasta', el: '#fil_lc_fecha_hasta', icon: 'fa-calendar', label: 'Hasta:', fmt: function(v) { return v; } },
        { key: 'cliente', el: '#fil_lc_cliente', icon: 'fa-user', label: 'Cliente:', fmt: function(v) { var t = $('#fil_lc_cliente option[value="' + v + '"]').text(); return t || v; } },
        { key: 'vendedor', el: '#fil_lc_vendedor', icon: 'fa-briefcase', label: 'Vendedor:', fmt: function(v) { var t = $('#fil_lc_vendedor option[value="' + v + '"]').text(); return t || v; } },
        { key: 'banco', el: '#fil_lc_banco', icon: 'fa-bank', label: 'Banco:', fmt: function(v) { var t = $('#fil_lc_banco option[value="' + v + '"]').text(); return t || v; } },
        { key: 'factura', el: '#fil_lc_factura', icon: 'fa-file', label: 'Factura:', fmt: function(v) { return v; } }
    ];

    $.each(defs, function(i, def) {
        if (f[def.key]) {
            html += '<span class="lc-filtro-badge" data-el="' + def.key + '">' +
                    '<i class="fa ' + def.icon + '"></i> ' + def.label + ' <strong>' + esc(def.fmt(f[def.key])) + '</strong>' +
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

/* ────────────────────────────────────────────────────────────────────
 *  Limpiar Filtros (Individual y Todos)
 * ──────────────────────────────────────────────────────────────────── */
function limpiarFiltroLC(el) {
    var filtro = $(el).closest('.lc-filtro-badge').data('el');
    var ids = {
        'cliente': '#fil_lc_cliente',
        'vendedor': '#fil_lc_vendedor',
        'banco': '#fil_lc_banco',
        'factura': '#fil_lc_factura',
        'fecha_desde': '#fil_lc_fecha_desde',
        'fecha_hasta': '#fil_lc_fecha_hasta'
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

/* ────────────────────────────────────────────────────────────────────
 *  Fechas por Defecto (Último Mes)
 * ──────────────────────────────────────────────────────────────────── */
function setDefaultDatesLC() {
    var now = new Date();
    var year = now.getFullYear();
    var month = now.getMonth();
    
    // Mes anterior
    var m = (month === 0) ? 11 : month - 1;
    var y = (month === 0) ? year - 1 : year;
    
    // Primer día del mes anterior
    var inicio = new Date(y, m, 1);
    // Último día del mes anterior
    var fin = new Date(y, m + 1, 0);
    
    var pad = function(n) { return String(n).padStart(2, '0'); };
    
    var fechaDesde = y + '-' + pad(m + 1) + '-01';
    var fechaHasta = y + '-' + pad(m + 1) + '-' + pad(fin.getDate());
    
    $('#fil_lc_fecha_desde').val(fechaDesde);
    $('#fil_lc_fecha_hasta').val(fechaHasta);
}

/* ────────────────────────────────────────────────────────────────────
 *  Aplicar Filtros
 * ──────────────────────────────────────────────────────────────────── */
function aplicarFiltrosLC() {
    $('#modalFiltrosLC').modal('hide');
    cargarTablaLC();
}

/* ────────────────────────────────────────────────────────────────────
 *  Exportar a Excel
 * ──────────────────────────────────────────────────────────────────── */
function exportarExcelLC() {
    var f = getFiltrosLC();
    var fechaDesde = f.fecha_desde || '1900-01-01';
    var fechaHasta = f.fecha_hasta || new Date().toISOString().split('T')[0];

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/Librocobrosrep/exportar-excel/3/' + encodeURIComponent(fechaDesde) + '/' + encodeURIComponent(fechaHasta);

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        var csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfMeta.getAttribute('content');
        form.appendChild(csrfInput);
    }

    // Agregar filtros como campos ocultos
    $.each(f, function(k, v) {
        if (v) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = k;
            input.value = v;
            form.appendChild(input);
        }
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

/* ────────────────────────────────────────────────────────────────────
 *  Inicialización (document.ready)
 * ──────────────────────────────────────────────────────────────────── */
$(document).ready(function() {
    // Inicializar Select2 para Cliente, Vendedor y Banco
    $('#fil_lc_cliente').select2({
        placeholder: '— Todos —',
        allowClear: true,
        dropdownParent: $('#modalFiltrosLC')
    });
    $('#fil_lc_vendedor').select2({
        placeholder: '— Todos —',
        allowClear: true,
        dropdownParent: $('#modalFiltrosLC')
    });
    $('#fil_lc_banco').select2({
        placeholder: '— Todos —',
        allowClear: true,
        dropdownParent: $('#modalFiltrosLC')
    });

    // Establecer fechas por defecto (último mes)
    setDefaultDatesLC();

    // Cargar tabla inicial con filtros activos
    mostrarFiltrosActivosLC();
    cargarTablaLC();

    // Event handlers
    // Tecla Enter en busqueda de factura
    $('#fil_lc_factura').on('keypress', function(e) {
        if (e.which === 13) {
            aplicarFiltrosLC();
            return false;
        }
    });

    // Click en × de badges
    $(document).on('click', '.lc-filtro-badge .fr', function(e) {
        e.preventDefault();
        limpiarFiltroLC(this);
    });

    // Botón "Buscar" en modal
    $('#btn_buscar_lc').on('click', function() {
        aplicarFiltrosLC();
    });
});
    // Enter en campo factura aplica búsqueda
    $(document).on('keypress', '#lc_factura', function(e) {
        if (e.which === 13) lcBuscar();
    });
    // Quitar filtro individual desde la barra de badges
    $(document).on('click', '.lc-filtro-badge-rm', function() {
        var elId = $(this).data('el');
        var el   = document.getElementById(elId);
        if (!el) return;
        el.value = '';
        if ($(el).hasClass('select2-hidden-accessible')) {
            $(el).val('').trigger('change');
        }
        lcBuscar();
    });
});

/* ---- legacy stub ---- */
function carga_libro_cobros() { lcBuscar(); }

