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

/** Establece el rango de fechas según el preset indicado */
function lcSetFechas(preset) {
    var pad = function(n){ return String(n).padStart(2,'0'); };
    var fmt = function(d){ return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()); };
    var hoy = new Date();
    var ini, fin;
    switch (preset) {
        case 'hoy':
            ini = fin = fmt(hoy);
            break;
        case 'semana': {
            var dow = hoy.getDay() === 0 ? 6 : hoy.getDay() - 1; // lunes=0
            var lun = new Date(hoy); lun.setDate(hoy.getDate() - dow);
            var dom = new Date(lun); dom.setDate(lun.getDate() + 6);
            ini = fmt(lun); fin = fmt(dom);
            break;
        }
        case 'mes':
            ini = fmt(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
            fin = fmt(new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0));
            break;
        case 'mes_ant': {
            var ma = _lcUltimoMes();
            ini = ma.inicio; fin = ma.fin;
            break;
        }
        case 'trim': {
            var tm = Math.floor(hoy.getMonth() / 3) * 3;
            ini = fmt(new Date(hoy.getFullYear(), tm, 1));
            fin = fmt(new Date(hoy.getFullYear(), tm + 3, 0));
            break;
        }
        default:
            return;
    }
    document.getElementById('lc_fecha_inicio').value = ini;
    document.getElementById('lc_fecha_final').value  = fin !== undefined ? fin : ini;
    // Resaltar botón activo
    document.querySelectorAll('.lc-ds-btn').forEach(function(b){ b.classList.remove('active'); });
    document.querySelectorAll('.lc-ds-btn').forEach(function(b){
        if (b.getAttribute('onclick') === "lcSetFechas('" + preset + "')") b.classList.add('active');
    });
}

function _lcBuildUrl() {
    var fi = document.getElementById('lc_fecha_inicio').value;
    var ff = document.getElementById('lc_fecha_final').value;
    var url;
    if (fi && ff) {
        url = '/reporte/Librocobrosrep/consulta/3/' + fi + '/' + ff;
    } else {
        url = '/reporte/Librocobrosrep/consulta/3';
    }
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
    var esc = function(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); };
    var addBadge = function(key, icon, label, elId) {
        var el = document.getElementById(elId);
        if (!el || !el.value) return;
        var display = el.tagName === 'SELECT'
            ? el.options[el.selectedIndex].text
            : el.value;
        html += '<span class="lc-filtro-badge">' +
                '<i class="fa ' + icon + '"></i>&nbsp;<strong>' + label + ':</strong>&nbsp;' + esc(display) +
                '<span class="lc-filtro-badge-rm fr" data-el="' + elId + '">&times;</span>' +
                '</span>';
    };
    if (fi) html += '<span class="lc-filtro-badge"><i class="fa fa-calendar-o"></i>&nbsp;<strong>Desde:</strong>&nbsp;' + esc(fi) + '<span class="lc-filtro-badge-rm fr" data-el="lc_fecha_inicio">&times;</span></span>';
    if (ff) html += '<span class="lc-filtro-badge"><i class="fa fa-calendar-o"></i>&nbsp;<strong>Hasta:</strong>&nbsp;' + esc(ff) + '<span class="lc-filtro-badge-rm fr" data-el="lc_fecha_final">&times;</span></span>';
    addBadge('cliente',   'fa-user',       'Cliente',  'lc_cliente');
    addBadge('vendedor',  'fa-briefcase',  'Vendedor', 'lc_vendedor');
    addBadge('banco',     'fa-university', 'Banco',    'lc_banco');
    addBadge('factura',   'fa-file-text-o','Factura',  'lc_factura');
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

/** Para columnas de detalle de factura:
 *  - PAGADA (último abono que cancela): muestra valor completo de la factura
 *  - PARCIAL: todas las columnas de detalle vacías EXCEPTO subtotal = monto_cobrado
 */
function _lcRenderDetalle(val, type, row) {
    if (type !== 'display') return val;
    if (row.estado_factura === 'PAGADA') {
        return '<span style="color:#1e3a5f;font-weight:600;">' + _lcFmt(val) + '</span>';
    }
    return '<span style="color:#d1d5db;font-size:.75rem;">—</span>';
}

/** Subtotal/TotalFactura: si la factura no tiene ninguna PAGADA, mostrar monto_cobrado en PARCIAL */
function _lcRenderSubtotal(val, type, row) {
    if (type !== 'display') return val;
    if (row.estado_factura === 'PAGADA') {
        return '<span style="color:#1e3a5f;font-weight:600;">' + _lcFmt(val) + '</span>';
    }
    // Solo abonos (sin pago final): mostrar monto cobrado en lugar de —
    if (!row.factura_tiene_pagada || row.factura_tiene_pagada == 0) {
        return '<span style="color:#92400e;font-weight:600;">' + _lcFmt(row.monto_cobrado) + '</span>';
    }
    return '<span style="color:#d1d5db;font-size:.75rem;">—</span>';
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
              render: _lcRenderSubtotal },
            { data: 'isv',              title: 'ISV',              width: '80px',
              render: _lcRenderDetalle },
            { data: 'total_factura',    title: 'Total Factura',    width: '110px',
              render: _lcRenderSubtotal }
        ],
        rowCallback: function(row, data) {
            if (data.estado_factura === 'PAGADA') {
                $(row).find('td').slice(9)   // cols 10+ (detail)
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
    document.getElementById('lc_fecha_inicio').value = '';
    document.getElementById('lc_fecha_final').value  = '';
    document.getElementById('lc_cliente').value  = '';
    document.getElementById('lc_vendedor').value = '';
    document.getElementById('lc_banco').value    = '';
    document.getElementById('lc_factura').value  = '';
    if (typeof $ !== 'undefined') {
        try { $('#lc_cliente').trigger('change'); }  catch(e) {}
        try { $('#lc_vendedor').trigger('change'); } catch(e) {}
        try { $('#lc_banco').trigger('change'); }    catch(e) {}
    }
    // Quitar resaltado de botones de acceso rápido
    document.querySelectorAll('.lc-ds-btn').forEach(function(b){ b.classList.remove('active'); });
    document.getElementById('lc_filtros_bar').style.display = 'none';
    lcBuscar();
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
    // Establecer último mes (igual que ventascobros)
    lcSetFechas('mes_ant');
    // Auto-buscar con fechas por defecto
    lcBuscar();
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

