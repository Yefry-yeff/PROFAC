/* ============================================================
   Libro de Cobros – Conciliación Bancaria
   ============================================================ */

var _lcTable = null;

// ── helpers ──────────────────────────────────────────────────
function _lcFmt(n) {
    return 'L ' + parseFloat(n || 0).toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/** Devuelve {inicio: 'YYYY-MM-DD', fin: 'YYYY-MM-DD'} del mes anterior */
function _lcUltimoMes() {
    var hoy  = new Date();
    var y    = hoy.getMonth() === 0 ? hoy.getFullYear() - 1 : hoy.getFullYear();
    var m    = hoy.getMonth() === 0 ? 11 : hoy.getMonth() - 1;   // 0-based
    var ini  = new Date(y, m, 1);
    var fin  = new Date(y, m + 1, 0);
    var pad  = function(n){ return String(n).padStart(2,'0'); };
    return {
        inicio : y + '-' + pad(m + 1) + '-01',
        fin    : y + '-' + pad(m + 1) + '-' + pad(fin.getDate())
    };
}

function _lcBuildUrl() {
    var fi = document.getElementById('lc_fecha_inicio').value;
    var ff = document.getElementById('lc_fecha_final').value;
    if (!fi || !ff) return null;
    var url    = '/reporte/Librocobrosrep/consulta/3/' + fi + '/' + ff;
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
    var fi  = document.getElementById('lc_fecha_inicio').value;
    var ff  = document.getElementById('lc_fecha_final').value;
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
    var rows       = Array.isArray(data) ? data : [];
    var totalCob   = 0, totalPagadas = 0;
    rows.forEach(function(r) {
        totalCob += parseFloat(r.monto_cobrado || 0);
        if (r.estado_factura === 'PAGADA') totalPagadas++;
    });
    document.getElementById('lc_kpi_registros').textContent    = rows.length.toLocaleString('es-HN');
    document.getElementById('lc_kpi_total_pagado').textContent = _lcFmt(totalCob);
    document.getElementById('lc_kpi_completas').textContent    = totalPagadas.toLocaleString('es-HN');
}

// ── Render helpers para celdas ────────────────────────────────
function _lcRenderEstado(val) {
    if (val === 'PAGADA') {
        return '<span style="display:inline-block;padding:2px 10px;border-radius:12px;background:#d1fae5;color:#065f46;font-size:.72rem;font-weight:700;">&#10003; PAGADA</span>';
    }
    return '<span style="display:inline-block;padding:2px 10px;border-radius:12px;background:#fef3c7;color:#92400e;font-size:.72rem;font-weight:700;">&#9679; PARCIAL</span>';
}

/** Para columnas de detalle de factura: solo mostrar si la factura está PAGADA */
function _lcRenderDetalle(val, type, row) {
    if (type !== 'display') return val;
    if (row.estado_factura !== 'PAGADA') {
        return '<span style="color:#d1d5db;font-size:.75rem;">—</span>';
    }
    return '<span style="color:#1e3a5f;font-weight:600;">' + _lcFmt(val) + '</span>';
}

function _lcRenderMonto(val) {
    return '<span style="font-weight:700;color:#1a7a4e;">' + _lcFmt(val) + '</span>';
}

function _lcRenderSaldo(val, type, row) {
    if (type !== 'display') return val;
    var v = parseFloat(val || 0);
    if (v <= 0.01) return '<span style="color:#d1d5db;font-size:.75rem;">—</span>';
    return '<span style="color:#dc2626;font-weight:600;">' + _lcFmt(v) + '</span>';
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
                var rows = json.data || json;
                _lcActualizarKpis(rows);
                return rows;
            }
        },
        columns: [
            { data: 'fecha_pago',       title: 'Fecha Pago',       width: '90px' },
            { data: 'cliente',          title: 'Cliente' },
            { data: 'vendedor',         title: 'Vendedor' },
            { data: 'factura',          title: 'N° Factura',       width: '130px' },
            { data: 'monto_cobrado',    title: 'Monto Cobrado',    width: '110px',
              render: function(v){ return _lcRenderMonto(v); } },
            { data: 'estado_factura',   title: 'Estado',           width: '95px',
              render: function(v){ return _lcRenderEstado(v); } },
            { data: 'saldo_pendiente',  title: 'Saldo Pendiente',  width: '110px',
              render: _lcRenderSaldo },
            { data: 'banco',            title: 'Banco' },
            { data: 'cuenta_banco',     title: 'Cuenta',           width: '110px' },
            { data: 'observaciones',    title: 'Observaciones' },
            // ── Detalle de factura — solo visible cuando PAGADA ──
            { data: 'exonerado',        title: 'Exonerado',        width: '95px',
              render: _lcRenderDetalle },
            { data: 'gravado',          title: 'Gravado',          width: '95px',
              render: _lcRenderDetalle },
            { data: 'excento',          title: 'Exento',           width: '95px',
              render: _lcRenderDetalle },
            { data: 'subtotal',         title: 'Sub Total',        width: '95px',
              render: _lcRenderDetalle },
            { data: 'isv',              title: 'ISV',              width: '80px',
              render: _lcRenderDetalle },
            { data: 'total_factura',    title: 'Total Factura',    width: '110px',
              render: _lcRenderDetalle }
        ],
        rowCallback: function(row, data) {
            if (data.estado_factura === 'PAGADA') {
                $(row).find('td').slice(10)   // cols 11+ (detail)
                      .css('background-color', '#f0fdf4');
            }
        },
        createdRow: function(row, data) {
            if (data.estado_factura === 'PAGADA') {
                $(row).addClass('lc-row-pagada');
            } else {
                $(row).addClass('lc-row-parcial');
            }
        }
    });
}

function lcLimpiarFiltros() {
    var mes = _lcUltimoMes();
    document.getElementById('lc_fecha_inicio').value = mes.inicio;
    document.getElementById('lc_fecha_final').value  = mes.fin;
    document.getElementById('lc_cliente').value  = '';
    document.getElementById('lc_vendedor').value = '';
    document.getElementById('lc_banco').value    = '';
    document.getElementById('lc_factura').value  = '';
    if (typeof $ !== 'undefined') {
        try { $('#lc_cliente').trigger('change'); }  catch(e) {}
        try { $('#lc_vendedor').trigger('change'); } catch(e) {}
        try { $('#lc_banco').trigger('change'); }    catch(e) {}
    }
    document.getElementById('lc_filtros_bar').style.display = 'none';
    if (_lcTable) { _lcTable.destroy(); _lcTable = null; $('#tbl_libro_cobros tbody').empty(); }
    document.getElementById('lc_kpi_registros').innerHTML    = '&mdash;';
    document.getElementById('lc_kpi_total_pagado').innerHTML = '&mdash;';
    document.getElementById('lc_kpi_completas').innerHTML    = '&mdash;';
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
    ['cliente_id','vendedor_id','banco_id','factura'].forEach(function(k){
        var vals = {
            'cliente_id' : document.getElementById('lc_cliente').value,
            'vendedor_id': document.getElementById('lc_vendedor').value,
            'banco_id'   : document.getElementById('lc_banco').value,
            'factura'    : document.getElementById('lc_factura').value.trim()
        };
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

// ── Init: último mes por defecto + auto-buscar ────────────────
$(function() {
    // Select2
    if (typeof $.fn.select2 !== 'undefined') {
        $('#lc_cliente, #lc_vendedor, #lc_banco').select2({ dropdownParent: $('#modalFiltrosLC'), width: '100%' });
    }
    // Establecer último mes
    var mes = _lcUltimoMes();
    document.getElementById('lc_fecha_inicio').value = mes.inicio;
    document.getElementById('lc_fecha_final').value  = mes.fin;
    // Auto-buscar
    lcBuscar();
});

/* ---- legacy stub ---- */
function carga_libro_cobros() { lcBuscar(); }

