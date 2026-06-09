/* ============================================================
   Libro de Cobros – JS (rediseño con filtros dinámicos)
   ============================================================ */

var _lcTable = null;
var _lcFiltros = {};

// ── helpers ──────────────────────────────────────────────────
function _lcFmt(n) {
    return 'L ' + parseFloat(n || 0).toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function _lcBuildUrl() {
    var fi = document.getElementById('lc_fecha_inicio').value;
    var ff = document.getElementById('lc_fecha_final').value;
    if (!fi || !ff) return null;
    var url = '/reporte/Librocobrosrep/consulta/3/' + fi + '/' + ff;
    var params = [];
    var cl = document.getElementById('lc_cliente').value;
    var vd = document.getElementById('lc_vendedor').value;
    var bk = document.getElementById('lc_banco').value;
    var fc = document.getElementById('lc_factura').value.trim();
    if (cl) params.push('cliente_id=' + encodeURIComponent(cl));
    if (vd) params.push('vendedor_id=' + encodeURIComponent(vd));
    if (bk) params.push('banco_id=' + encodeURIComponent(bk));
    if (fc) params.push('factura=' + encodeURIComponent(fc));
    if (params.length) url += '?' + params.join('&');
    return url;
}

function _lcActualizarBadges() {
    var fi = document.getElementById('lc_fecha_inicio').value;
    var ff = document.getElementById('lc_fecha_final').value;
    var bar = document.getElementById('lc_filtros_bar');
    var html = '';
    if (fi || ff) html += '<span class="lc-filtro-badge"><i class="fa fa-calendar-o"></i> ' + (fi||'…') + ' – ' + (ff||'…') + '</span>';
    var clSel = document.getElementById('lc_cliente');
    if (clSel.value) html += '<span class="lc-filtro-badge"><i class="fa fa-user"></i> ' + clSel.options[clSel.selectedIndex].text + '</span>';
    var vdSel = document.getElementById('lc_vendedor');
    if (vdSel.value) html += '<span class="lc-filtro-badge"><i class="fa fa-briefcase"></i> ' + vdSel.options[vdSel.selectedIndex].text + '</span>';
    var bkSel = document.getElementById('lc_banco');
    if (bkSel.value) html += '<span class="lc-filtro-badge"><i class="fa fa-university"></i> ' + bkSel.options[bkSel.selectedIndex].text + '</span>';
    var fc = document.getElementById('lc_factura').value.trim();
    if (fc) html += '<span class="lc-filtro-badge"><i class="fa fa-file-text-o"></i> Factura: ' + fc + '</span>';
    if (html) {
        bar.innerHTML = '<i class="fa fa-info-circle" style="color:#e67e22;margin-right:4px;"></i><strong style="color:#7d3f00;margin-right:6px;">Filtros activos:</strong>' + html;
        bar.style.display = 'flex';
    } else {
        bar.style.display = 'none';
    }
}

function _lcActualizarKpis(data) {
    var rows = Array.isArray(data) ? data : [];
    var totalPagado = 0, totalRet = 0;
    rows.forEach(function(r) {
        totalPagado += parseFloat(r['TOTAL PAGADO'] || 0);
        totalRet    += parseFloat(r['RETENCION']    || 0);
    });
    document.getElementById('lc_kpi_registros').textContent   = rows.length.toLocaleString('es-HN');
    document.getElementById('lc_kpi_total_pagado').textContent = _lcFmt(totalPagado);
    document.getElementById('lc_kpi_retencion').textContent    = _lcFmt(totalRet);
}

// ── funciones públicas ────────────────────────────────────────
function lcBuscar() {
    var fi = document.getElementById('lc_fecha_inicio').value;
    var ff = document.getElementById('lc_fecha_final').value;
    if (!fi || !ff) {
        Swal.fire({ icon: 'warning', title: 'Fechas requeridas', text: 'Por favor seleccione el rango de fechas de pago.', confirmButtonColor: '#e67e22' });
        return;
    }
    var url = _lcBuildUrl();
    if (_lcTable) { _lcTable.destroy(); _lcTable = null; $('#tbl_libro_cobros tbody').empty(); }
    _lcActualizarBadges();
    $('#modalFiltrosLC').modal('hide');

    _lcTable = $('#tbl_libro_cobros').DataTable({
        order: [[0, 'asc']],
        paging: true,
        language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        pageLength: 25,
        responsive: true,
        dom: 'lrtip',
        ajax: {
            url: url,
            dataSrc: function(json) {
                _lcActualizarKpis(json.data || json);
                return json.data || json;
            }
        },
        columns: [
            { data: 'VENDEDOR' },
            { data: 'CLIENTE' },
            { data: 'FACTURA' },
            { data: 'EXONERADO' },
            { data: 'GRAVADO' },
            { data: 'EXCENTO' },
            { data: 'ABONO' },
            { data: 'SUBTOTAL' },
            { data: 'ISV' },
            { data: 'TOTAL' },
            { data: 'RETENCION' },
            { data: 'TOTAL PAGADO' },
            { data: 'FECHA DE COMPRA' },
            { data: 'FECHA DE VENCIMIENTO' },
            { data: 'FECHA DE PAGO' },
            { data: 'BANCO' },
            { data: 'OBSERVACIONES' }
        ]
    });
}

function lcLimpiarFiltros() {
    document.getElementById('lc_fecha_inicio').value = '';
    document.getElementById('lc_fecha_final').value  = '';
    document.getElementById('lc_cliente').value  = '';
    document.getElementById('lc_vendedor').value = '';
    document.getElementById('lc_banco').value    = '';
    document.getElementById('lc_factura').value  = '';
    if (typeof $ !== 'undefined') {
        try { $('#lc_cliente').trigger('change'); } catch(e) {}
        try { $('#lc_vendedor').trigger('change'); } catch(e) {}
        try { $('#lc_banco').trigger('change'); } catch(e) {}
    }
    document.getElementById('lc_filtros_bar').style.display = 'none';
    if (_lcTable) { _lcTable.destroy(); _lcTable = null; $('#tbl_libro_cobros tbody').empty(); }
    document.getElementById('lc_kpi_registros').innerHTML   = '&mdash;';
    document.getElementById('lc_kpi_total_pagado').innerHTML = '&mdash;';
    document.getElementById('lc_kpi_retencion').innerHTML    = '&mdash;';
}

function lcExportarExcel() {
    var fi = document.getElementById('lc_fecha_inicio').value;
    var ff = document.getElementById('lc_fecha_final').value;
    if (!fi || !ff) {
        Swal.fire({ icon: 'warning', title: 'Fechas requeridas', text: 'Primero realice una búsqueda.', confirmButtonColor: '#e67e22' });
        return;
    }
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/Librocobrosrep/exportar-excel/3/' + encodeURIComponent(fi) + '/' + encodeURIComponent(ff);
    var ci = document.createElement('input'); ci.type='hidden'; ci.name='_token'; ci.value=csrfToken; form.appendChild(ci);
    // pass optional filters
    ['cliente_id','vendedor_id','banco_id','factura'].forEach(function(k){
        var vals = {'cliente_id': document.getElementById('lc_cliente').value,
                    'vendedor_id': document.getElementById('lc_vendedor').value,
                    'banco_id': document.getElementById('lc_banco').value,
                    'factura': document.getElementById('lc_factura').value.trim()};
        if (vals[k]) { var inp = document.createElement('input'); inp.type='hidden'; inp.name=k; inp.value=vals[k]; form.appendChild(inp); }
    });
    document.body.appendChild(form); form.submit(); document.body.removeChild(form);
}

function lcExportarPdf() {
    var fi = document.getElementById('lc_fecha_inicio').value;
    var ff = document.getElementById('lc_fecha_final').value;
    if (!fi || !ff) {
        Swal.fire({ icon: 'warning', title: 'Fechas requeridas', text: 'Primero realice una búsqueda.', confirmButtonColor: '#e67e22' });
        return;
    }
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/Librocobrosrep/exportar-pdf/3/' + encodeURIComponent(fi) + '/' + encodeURIComponent(ff);
    var ci = document.createElement('input'); ci.type='hidden'; ci.name='_token'; ci.value=csrfToken; form.appendChild(ci);
    document.body.appendChild(form); form.submit(); document.body.removeChild(form);
}

// ── Init Select2 si está disponible ──────────────────────────
$(function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('#lc_cliente, #lc_vendedor, #lc_banco').select2({ dropdownParent: $('#modalFiltrosLC'), width: '100%' });
    }
});

/* ---- legacy stub: redirect to new lcBuscar ---- */
function carga_libro_cobros() {
    lcBuscar();
}

