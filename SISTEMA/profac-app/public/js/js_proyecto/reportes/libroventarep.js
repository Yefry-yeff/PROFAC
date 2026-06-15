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
            cargarKpisLV();
            mostrarFiltrosActivosLV();
        }
    });
}

/* ────────────────────────────────────────────────────────────────────
 *  KPIs
 * ──────────────────────────────────────────────────────────────────── */
function cargarKpisLV() {
    if (!lvTable) return;
    var totalVendido = 0;
    var totalISV = 0;
    var totalGravado = 0;
    var totalRegistros = lvTable.data().length;

    lvTable.data().each(function(row) {
        totalVendido += parseFloat(row.TOTAL) || 0;
        totalISV += parseFloat(row.ISV) || 0;
        totalGravado += parseFloat(row.GRAVADO) || 0;
    });

    $('#kpi_total_vendido').text(fmtLpsLV(totalVendido));
    $('#kpi_total_isv').text(fmtLpsLV(totalISV));
    $('#kpi_total_gravado').text(fmtLpsLV(totalGravado));
    $('#kpi_total_registros').text(totalRegistros);
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
function exportarExcelLV() {
    var f = getFiltrosLV();
    var fechaDesde = f.fecha_desde || '1900-01-01';
    var fechaHasta = f.fecha_hasta || new Date().toISOString().split('T')[0];

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/Libroventarep/exportar-excel/4/' + encodeURIComponent(fechaDesde) + '/' + encodeURIComponent(fechaHasta);

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



