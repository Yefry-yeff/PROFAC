/**
 * ventascobros.js  —  Reporte Financiero Detallado por Factura  (v2)
 * DataTables serverSide + child-row expediente financiero
 */

/* ────────────────────────────────────────────────────────────────────
 *  Estado global
 * ──────────────────────────────────────────────────────────────────── */
var rfdTable = null;
var rfdOpenRows = {};   // { facturaId: DataTable row object }

/* ────────────────────────────────────────────────────────────────────
 *  Lectura de filtros
 * ──────────────────────────────────────────────────────────────────── */
function getFiltros() {
    return {
        cliente:          $('#fil_cliente').val()          || '',
        factura:          $('#fil_factura').val()          || '',
        vendedor:         $('#fil_vendedor').val()         || '',
        modo_pago:        $('#fil_modo_pago').val()        || '',
        estado_f01:       $('#fil_estado_f01').val()       || '',
        fecha_desde:      $('#fil_fecha_desde').val()      || '',
        fecha_hasta:      $('#fil_fecha_hasta').val()      || '',
        fecha_pago_desde: $('#fil_fecha_pago_desde').val() || '',
        fecha_pago_hasta: $('#fil_fecha_pago_hasta').val() || '',
        estado_cobro:     $('#fil_estado_cobro').val()     || '',
        banco:            $('#fil_banco').val()            || '',
        cuenta:           $('#fil_cuenta').val()           || ''
    };
}

function buildQueryString(f) {
    var parts = [];
    $.each(f, function(k, v) { if (v) parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(v)); });
    return parts.join('&');
}

/* ────────────────────────────────────────────────────────────────────
 *  Formatters
 * ──────────────────────────────────────────────────────────────────── */
function fmtLps(v) {
    var n = parseFloat(v) || 0;
    if (Math.abs(n) < 0.005) n = 0;
    return 'L ' + n.toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function fmtLpsAbs(v) { return fmtLps(Math.abs(parseFloat(v) || 0)); }
function fmtLpsSaldo(v) {
    var n = parseFloat(v) || 0;
    if (Math.abs(n) < 0.005) n = 0;
    if (n <= 0.01) return '<span style="color:#0e9f6e;font-weight:800;">' + fmtLps(0) + '</span>';
    return '<span style="color:#e02424;font-weight:800;">' + fmtLps(n) + '</span>';
}
function fmtFecha(v) {
    if (!v) return '<span style="color:#9ca3af;">—</span>';
    var d = new Date(v);
    if (isNaN(d)) return v;
    return d.toLocaleDateString('es-HN', { day:'2-digit', month:'2-digit', year:'numeric' });
}
function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function esFacturaAnulada(row) {
    var estadoF01 = String((row && row.estado_f01) || '').toUpperCase();
    return estadoF01.indexOf('ANULAD') === 0 || String((row && row.estado_cobro_v2) || '') === 'Anuladas';
}

/* ────────────────────────────────────────────────────────────────────
 *  Badges de estado
 * ──────────────────────────────────────────────────────────────────── */
function renderBadgeEstado(v) {
    var map = {
        'Anuladas':            ['badge-anulado',      'fa-ban',           'Anuladas'],
        'Pagada':               ['badge-pagada',      'fa-check-circle',  'Pagada'],
        'Contado':              ['badge-contado',      'fa-money',         'Contado'],
        'Parcialmente Pagada':  ['badge-parcial',      'fa-adjust',        'Parcial'],
        'Pendiente':            ['badge-pendiente',    'fa-clock-o',       'Pendiente'],
        'Vencida':              ['badge-vencida',      'fa-exclamation-triangle', 'Vencida'],
        'Vencida Cr\u00edtica': ['badge-vencida-crit', 'fa-fire',          'Venc. Cr\u00edtica'],
        'Anulado':              ['badge-anulado',      'fa-ban',           'Anulado']
    };
    var cfg = map[v] || ['badge-pendiente', 'fa-question', v];
    return '<span class="rfd-badge ' + cfg[0] + '"><i class="fa ' + cfg[1] + '"></i> ' + cfg[2] + '</span>';
}

/* ────────────────────────────────────────────────────────────────────
 *  DataTable principal
 * ──────────────────────────────────────────────────────────────────── */
function cargarTabla() {
    if (rfdTable) { rfdTable.destroy(); rfdOpenRows = {}; }
    $('#tbl_rfd tbody').empty();

    var qs = buildQueryString(getFiltros());

    rfdTable = $('#tbl_rfd').DataTable({
        processing:    true,
        serverSide:    true,
        responsive:    false,
        language:      { url: '/js/plugins/dataTables/i18n/Spanish.json', processing: '<i class="fa fa-spinner fa-spin"></i> Cargando...' },
        ajax: {
            url:  '/reporte/ventas-cobros/datos?' + qs,
            type: 'GET',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            error: function(xhr) {
                Swal.fire({ icon:'error', title:'Error', text: 'Error al cargar el reporte: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : xhr.status) });
            }
        },
        columns: [
            /* 0 – toggle */
            {
                data: 'factura_id', orderable: false, searchable: false,
                render: function(d) {
                    return '<button class="rfd-toggle-btn" data-id="' + d + '" title="Ver expediente"><i class="fa fa-plus"></i></button>';
                }
            },
            /* 1 – factura */
            { data: 'numero_secuencia_cai', render: function(d) { return '<strong>' + escHtml(d) + '</strong>'; } },
            /* 2 – cliente */
            { data: 'cliente' },
            /* 3 – vendedor */
            { data: 'vendedor' },
            /* 4 – fecha venta */
            { data: 'fecha_venta', render: function(d, type, row) { return esFacturaAnulada(row) ? '' : fmtFecha(d); } },
            /* 5 – modo pago */
            { data: 'modo_pago' },
            /* 6 – total */
            { data: 'total', className: 'text-right', render: function(d, type, row) { return esFacturaAnulada(row) ? '' : fmtLps(d); } },
            /* 7 – monto pagado */
            { data: 'monto_pagado', className: 'text-right', render: function(d) { return fmtLps(d); } },
            /* 8 – saldo */
            { data: 'saldo_pendiente', className: 'text-right', render: function(d, type, row) { return esFacturaAnulada(row) ? '' : fmtLpsSaldo(d); } },
            /* 9 – estado */
            { data: 'estado_cobro_v2', render: function(d) { return renderBadgeEstado(d); } },
            /* 10 – dias vencidos */
            {
                data: 'dias_vencidos', className: 'text-center',
                render: function(d, type, row) {
                    if (esFacturaAnulada(row)) return '';
                    if (d === null || d === undefined) return '<span style="color:#9ca3af;">—</span>';
                    var n = parseInt(d, 10);
                    if (isNaN(n) || n <= 0) return '<span style="color:#9ca3af;">—</span>';
                    var cls = n > 60 ? 'color:#7f1d1d;font-weight:800;' : 'color:#991b1b;font-weight:700;';
                    return '<span style="' + cls + '">' + n + ' d</span>';
                }
            }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
        initComplete: function() {
            cargarKpis();
        },
        createdRow: function(row, data) {
            $(row).attr('data-factura-id', data.factura_id);
            var ef = (data.estado_f01 || '').toUpperCase();
            if (ef === 'ANULADO' || ef === 'ANULADA') $(row).addClass('rfd-row-anulado');
        }
    });
}

/* ────────────────────────────────────────────────────────────────────
 *  KPIs
 * ──────────────────────────────────────────────────────────────────── */
function cargarKpis() {
    var qs = buildQueryString(getFiltros());
    $.ajax({
        url:  '/reporte/ventas-cobros/kpis?' + qs,
        type: 'GET',
        success: function(resp) {
            if (!resp.success) return;
            var k = resp.kpis;
            $('#kpi_facturado').text(fmtLps(k.total_facturado));
            $('#kpi_cobrado').text(fmtLps(k.total_cobrado));
            $('#kpi_pendiente').text(fmtLps(k.total_aumento_factura));
            $('#kpi_vencido').text(fmtLps(k.total_disminucion_factura));
            $('#kpi_saldo_pendiente').text(fmtLps(k.total_pendiente));
            $('#kpi_total_facturas').text(k.total_facturas + ' facturas');
            $('#kpi_fac_pagadas').text(k.facturas_pagadas + ' pagadas');
            $('#kpi_fac_pendientes').text(k.facturas_pendientes + ' pendientes');
            $('#kpi_sub_aumento').text('');
            $('#kpi_sub_disminucion').text('');
        }
    });
}

/* ────────────────────────────────────────────────────────────────────
 *  Click en toggle — carga expediente
 * ──────────────────────────────────────────────────────────────────── */
$(document).on('click', '.rfd-toggle-btn', function() {
    var btn      = $(this);
    var factId   = btn.data('id');
    var tr       = btn.closest('tr');
    var dtRow    = rfdTable.row(tr);

    if (rfdOpenRows[factId]) {
        /* cerrar */
        dtRow.child.hide();
        btn.removeClass('open');
        btn.find('i').removeClass('fa-minus').addClass('fa-plus');
        delete rfdOpenRows[factId];
        return;
    }

    /* abrir — fetch expediente */
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.ajax({
        url:  '/reporte/ventas-cobros/expediente/' + factId,
        type: 'GET',
        success: function(resp) {
            if (!resp.success) {
                Swal.fire({ icon:'error', title:'Error', text: resp.mensaje || 'Error al cargar el expediente.' });
                btn.prop('disabled', false).html('<i class="fa fa-plus"></i>');
                return;
            }
            var html = renderExpediente(resp);
            dtRow.child(html).show();
            rfdOpenRows[factId] = dtRow;
            btn.prop('disabled', false).addClass('open').html('<i class="fa fa-minus"></i>');
        },
        error: function(xhr) {
            Swal.fire({ icon:'error', title:'Error de servidor', text: xhr.status + ': ' + xhr.statusText });
            btn.prop('disabled', false).html('<i class="fa fa-plus"></i>');
        }
    });
});

/* ────────────────────────────────────────────────────────────────────
 *  Render del expediente completo
 * ──────────────────────────────────────────────────────────────────── */
function renderExpediente(resp) {
    var c  = resp.cabecera;
    var ms = resp.movimientos;
    var esAnulada = esFacturaAnulada(c);

    var saldoClass = parseFloat(c.total_factura) <= 0.01 ? '' :
                     (parseFloat(resp.saldo_final) <= 0.01 ? 'saldo-0' : (parseFloat(c.dias_vencidos) > 0 ? 'saldo-venc' : ''));

    /* ── Cabecera ── */
    var html = '<div class="rfd-exp-wrapper">';
    html += '<div class="rfd-exp-header-card">';
    html += '<h4><i class="fa fa-folder-open-o" style="margin-right:8px;"></i>Expediente Financiero — ' + escHtml(c.numero_secuencia_cai) + '</h4>';
    html += '<div class="rfd-exp-meta-grid">';
    html += metaItem('Cliente',        c.cliente);
    html += metaItem('Vendedor',       c.vendedor);
    html += metaItem('Fecha Emisión',  esAnulada ? '' : fmtFecha(c.fecha_venta));
    html += metaItem('Vencimiento',    esAnulada ? '' : fmtFecha(c.fecha_vencimiento));
    html += metaItem('Días Crédito',   c.credito == 0 ? 'Contado' : (c.dias_credito + ' días'));
    html += metaItem('Modo Pago',      c.modo_pago);
    html += '<div class="rfd-meta-item">'
          + '<div class="rfd-meta-lbl">ESTADO F-01</div>'
          + '<div class="rfd-meta-val" style="display:flex;align-items:center;gap:6px;">'
          + '<span id="rfd-f01-val-' + c.factura_id + '">' + escHtml(c.flujo_forma_f01 || 'N/A') + '</span>'
          + (c.tiene_flujo
              ? ' <button onclick="rfdEditarF01(' + c.factura_id + ', this)" '
                + 'style="background:rgba(255,255,255,.25);border:1px solid rgba(255,255,255,.5);color:#fff;'
                + 'border-radius:4px;padding:1px 7px;font-size:11px;cursor:pointer;" title="Editar F-01">'
                + '<i class="fa fa-pencil"></i></button>'
              : ' <span style="font-size:10px;opacity:.65;cursor:help;" title="Esta factura no tiene flujo asociado — no se puede editar"><i class="fa fa-lock"></i></span>')
          + '</div></div>';
    html += metaItem('Orden Compra',   c.orden_compra || '—');
    html += metaItem('Fecha Entrega',  c.fecha_entrega ? fmtFecha(c.fecha_entrega) : 'Sin entrega');
    html += '</div></div>';

    /* ── Montos fiscales + Estado financiero ── */
    html += '<div class="rfd-fin-grid">';

    /* Montos fiscales */
    html += '<div class="rfd-fin-box"><h5><i class="fa fa-calculator" style="margin-right:5px;"></i>Montos Fiscales</h5>';
    html += finRow('Gravado',    esAnulada ? '' : fmtLps(c.gravado));
    html += finRow('Exento',     esAnulada ? '' : fmtLps(c.exento));
    html += finRow('Exonerado',  esAnulada ? '' : fmtLps(c.exonerado));
    html += finRow('Sub-Total',  esAnulada ? '' : fmtLps(c.sub_total));
    html += finRow('ISV (15%)',  esAnulada ? '' : fmtLps(c.isv));
    html += '<div class="rfd-fin-row total"><span class="lbl">TOTAL FACTURA</span><span class="val">' + (esAnulada ? '' : fmtLps(c.total_factura)) + '</span></div>';
    html += '</div>';

    /* Estado financiero */
    var totalAbonos      = 0;
    var totalNotasDebito = 0;
    var totalNotasCredito= 0;
    var totalRetencion   = 0;
    $.each(ms, function(i, mov) {
        var monto = parseFloat(mov.monto) || 0;
        if (mov.tipo === 'ABONO' || mov.tipo === 'PAGO') totalAbonos      += monto;
        if (mov.tipo === 'NOTA_DEBITO')                  totalNotasDebito += monto;
        if (mov.tipo === 'NOTA_CREDITO')                 totalNotasCredito+= monto;
        if (mov.tipo === 'RETENCION')                    totalRetencion   += monto;
    });

    html += '<div class="rfd-fin-box"><h5><i class="fa fa-line-chart" style="margin-right:5px;"></i>Estado Financiero Actual</h5>';
    html += '<div class="rfd-fin-row"><span class="lbl">Total Facturado</span><span class="val" style="color:#111827;font-weight:600;">' + (esAnulada ? '' : fmtLps(c.total_factura)) + '</span></div>';
    if (!esAnulada && totalNotasDebito > 0) {
        html += '<div class="rfd-fin-row"><span class="lbl" style="padding-left:12px;color:#6b7280;">↳ Notas de Débito</span><span class="val" style="color:#b45309;font-weight:600;">+ ' + fmtLps(totalNotasDebito) + '</span></div>';
    }
    if (!esAnulada && totalNotasCredito > 0) {
        html += '<div class="rfd-fin-row"><span class="lbl" style="padding-left:12px;color:#6b7280;">↳ Notas de Crédito</span><span class="val" style="color:#e02424;font-weight:600;">- ' + fmtLps(totalNotasCredito) + '</span></div>';
    }
    if (!esAnulada && totalRetencion > 0) {
        html += '<div class="rfd-fin-row"><span class="lbl" style="padding-left:12px;color:#6b7280;">↳ Retención ISV</span><span class="val" style="color:#0369a1;font-weight:600;">- ' + fmtLps(totalRetencion) + '</span></div>';
    }
    html += '<div class="rfd-fin-row"><span class="lbl">Total Abonado / Pagado</span><span class="val" style="color:#0e9f6e;font-weight:600;">' + fmtLps(totalAbonos) + '</span></div>';
    var saldoCalculado = (parseFloat(c.total_factura) || 0) + totalNotasDebito - totalNotasCredito - totalRetencion - totalAbonos;
    if (Math.abs(saldoCalculado) < 0.005 || saldoCalculado < 0) saldoCalculado = 0;
    var saldoClassCalc = saldoCalculado <= 0.01 ? 'saldo-0' : (parseInt(c.dias_vencidos) > 0 ? 'saldo-venc' : '');
    html += '<div class="rfd-fin-row ' + saldoClassCalc + '"><span class="lbl">Saldo Pendiente</span><span class="val">' + (esAnulada ? '' : fmtLps(saldoCalculado)) + '</span></div>';
    html += finRow('D\u00edas Vencidos',
        esAnulada
            ? ''
            :
        (parseInt(c.dias_vencidos) > 0)
            ? '<span style="color:#e02424;font-weight:700;">' + c.dias_vencidos + ' d\u00edas</span>'
            : '<span style="color:#0e9f6e;font-weight:600;">Al d\u00eda</span>');
    html += '</div>';
    html += '</div>';

    /* ── Cartera ── */
    html += '<div class="rfd-cartera-box">';
    html += '<h5><i class="fa fa-briefcase" style="margin-right:5px;"></i>Datos de Cartera</h5>';
    html += '<div class="rfd-cartera-grid">';
    html += carteraItem('Estado Cobro',    renderBadgeEstado(estadoCobro(c, resp.saldo_final)));
    html += carteraItem('Estado Crédito',  c.credito == 0 ? '<span class="rfd-badge badge-contado">Contado</span>' : '<span class="rfd-badge badge-pendiente">Crédito</span>');
    html += carteraItem('Observación',     c.observacion || '—');
    html += carteraItem('Días Crédito',    c.credito == 0 ? 'Contado' : c.dias_credito + ' días');
    html += carteraItem('Movimientos',     ms.length + ' registros');
    html += '</div></div>';

    /* ── Timeline ── */
    html += '<div class="rfd-timeline-hdr"><i class="fa fa-history" style="margin-right:6px;"></i>Línea de Tiempo del Ciclo de Vida</div>';
    html += '<ul class="rfd-timeline">';
    $.each(ms, function(i, mov) {
        if (mov.tipo === 'ENTREGA' || mov.tipo === 'VALE') return;
        html += renderMovimiento(mov);
    });
    html += '</ul>';

    html += '</div>'; /* /rfd-exp-wrapper */
    return html;
}

/* ────────────────────────────────────────────────────────────────────
 *  Cálculo local de estado cobro (para el expediente)
 * ──────────────────────────────────────────────────────────────────── */
function estadoCobro(c, saldoFinal) {
    var estadoF01 = String(c.estado_f01 || '').toUpperCase();
    if (estadoF01.indexOf('ANULAD') === 0) return 'Anuladas';
    if (c.credito == 0)             return 'Contado';
    if (parseFloat(saldoFinal) <= 0.01) return 'Pagada';
    if (c.dias_vencidos > 60)       return 'Vencida Cr\u00edtica';
    if (c.dias_vencidos > 0)        return 'Vencida';
    return 'Pendiente';
}

/* ────────────────────────────────────────────────────────────────────
 *  Render de un movimiento (timeline item)
 * ──────────────────────────────────────────────────────────────────── */
function renderMovimiento(mov) {
    var tipo  = (mov.tipo || '').toUpperCase();
    var monto = parseFloat(mov.monto) || 0;
    var esCargo  = tipo === 'VENTA' || tipo === 'NOTA_DEBITO';
    var esAbono  = !esCargo && tipo !== 'ENTREGA' && tipo !== 'VALE';

    var dotClass   = 'rfd-dot-' + tipo.toLowerCase();
    var tipoColor  = tipoColorMap(tipo);
    var icon       = tipoIcon(tipo);
    var label      = tipoLabel(tipo);
    var montoHtml  = '';

    if (tipo !== 'ENTREGA' && tipo !== 'VALE') {
        var sign = esCargo ? '+' : '-';
        var montoColor = (tipo === 'NOTA_DEBITO') ? '#b45309' : (esCargo ? '#111827' : '#0e9f6e');
        montoHtml = '<span class="rfd-tl-monto" style="color:' + montoColor + ';">' + sign + ' ' + fmtLpsAbs(monto) + '</span>';
    }

    var saldoHtml = '';
    if (mov.saldo_resultante !== null && mov.saldo_resultante !== undefined) {
        saldoHtml = '<span class="rfd-tl-saldo">Saldo: ' + fmtLps(mov.saldo_resultante) + '</span>';
    }

    var docHtml = escHtml(mov.documento || '');
    var descHtml = mov.descripcion ? ('<div class="tl-desc">' + escHtml(mov.descripcion) + '</div>') : '';

    /* meta pills */
    var metas = [];
    if (mov.responsable)  metas.push('<span><strong>Por:</strong> ' + escHtml(mov.responsable) + '</span>');
    if (mov.forma_pago)   metas.push('<span><strong>Forma:</strong> ' + escHtml(mov.forma_pago) + '</span>');
    if (mov.banco_nombre) metas.push('<span><strong>Banco:</strong> ' + escHtml(mov.banco_nombre) + '</span>');
    if (mov.banco_cuenta) metas.push('<span><strong>Cuenta:</strong> ' + escHtml(mov.banco_cuenta) + '</span>');
    if (mov.recibo && tipo !== 'VENTA') metas.push('<span><strong>Recibo:</strong> ' + escHtml(mov.recibo) + '</span>');

    var metaHtml = metas.length ? '<div class="tl-meta">' + metas.join('') + '</div>' : '';

    return '<li class="rfd-tl-item">' +
        '<div class="rfd-tl-dot ' + dotClass + '"><i class="fa ' + icon + '"></i></div>' +
        '<div class="rfd-tl-body" style="border-left:3px solid ' + tipoColor + ';">' +
            '<div style="display:flex;align-items:flex-start;justify-content:space-between;">' +
                '<div>' +
                    '<div class="tl-tipo" style="color:' + tipoColor + ';">' + label + '</div>' +
                    '<span class="tl-doc">' + docHtml + '</span>' +
                    '<span class="tl-fecha">' + fmtFecha(mov.fecha) + '</span>' +
                    (saldoHtml ? saldoHtml : '') +
                '</div>' +
                montoHtml +
            '</div>' +
            descHtml +
            metaHtml +
        '</div>' +
    '</li>';
}

/* ────────────────────────────────────────────────────────────────────
 *  Helpers de render
 * ──────────────────────────────────────────────────────────────────── */
function metaItem(lbl, val) {
    return '<div class="rfd-meta-item"><div class="rfd-meta-lbl">' + lbl + '</div><div class="rfd-meta-val">' + (val || '—') + '</div></div>';
}
function finRow(lbl, val) {
    return '<div class="rfd-fin-row"><span class="lbl">' + lbl + '</span><span class="val">' + val + '</span></div>';
}
function carteraItem(lbl, val) {
    return '<div class="rfd-cartera-item"><div class="ci-lbl">' + lbl + '</div><div class="ci-val">' + (val || '—') + '</div></div>';
}
function tipoIcon(t) {
    var m = { VENTA:'fa-file-text-o', ENTREGA:'fa-truck', ABONO:'fa-money', PAGO:'fa-credit-card', NOTA_CREDITO:'fa-minus-circle', NOTA_DEBITO:'fa-plus-circle', VALE:'fa-ticket', RETENCION:'fa-percent' };
    return m[t] || 'fa-circle-o';
}
function tipoLabel(t) {
    var m = { VENTA:'Venta', ENTREGA:'Entrega', ABONO:'Abono Crédito', PAGO:'Pago Contado', NOTA_CREDITO:'Nota de Crédito', NOTA_DEBITO:'Nota de Débito', VALE:'Vale de Entrega', RETENCION:'Retención ISV' };
    return m[t] || t;
}
function tipoColorMap(t) {
    var m = { VENTA:'#1a56db', ENTREGA:'#0e9f6e', ABONO:'#d97706', PAGO:'#7c3aed', NOTA_CREDITO:'#e02424', NOTA_DEBITO:'#b45309', VALE:'#e67e22', RETENCION:'#0369a1' };
    return m[t] || '#6b7280';
}

/* ────────────────────────────────────────────────────────────────────
 *  Edición inline del Estado F-01
 * ──────────────────────────────────────────────────────────────────── */
function rfdEditarF01(facturaId, btn) {
    var spanId  = '#rfd-f01-val-' + facturaId;
    var valAct  = $(spanId).text().trim();
    if (valAct === 'N/A') valAct = '';

    Swal.fire({
        title: 'Editar Estado F-01',
        input: 'text',
        inputValue: valAct,
        inputPlaceholder: 'Ej: F-01-2026-00123',
        inputAttributes: { maxlength: 100 },
        showCancelButton: true,
        confirmButtonText: '<i class="fa fa-save"></i> Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e67e22',
        inputLabel: 'Número de forma F-01',
        showLoaderOnConfirm: true,
        preConfirm: function(valor) {
            var tok = $('meta[name="csrf-token"]').attr('content');
            return $.ajax({
                url: '/reporte/ventas-cobros/actualizar-f01/' + facturaId,
                method: 'POST',
                data: { _token: tok, valor: valor },
            }).fail(function(xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.mensaje
                    ? xhr.responseJSON.mensaje
                    : 'Error al guardar.';
                Swal.showValidationMessage(msg);
            });
        },
        allowOutsideClick: function() { return !Swal.isLoading(); }
    }).then(function(result) {
        if (result.isConfirmed && result.value && result.value.success) {
            var nuevo = result.value.valor || 'N/A';
            $(spanId).text(nuevo);
            Swal.fire({
                icon: 'success',
                title: 'Actualizado',
                text: 'Estado F-01 guardado: ' + nuevo,
                timer: 2000,
                showConfirmButton: false,
            });
        }
    });
}

/* ────────────────────────────────────────────────────────────────────
 *  Exportar PDF / Excel (rutas legacy)
 * ──────────────────────────────────────────────────────────────────── */
function _exportarForm(url) {
    var f    = getFiltros();
    var tok  = $('meta[name="csrf-token"]').attr('content');
    var form = $('<form method="POST"></form>').attr('action', url);
    var fields = {
        _token:            tok,
        vendedor:          f.vendedor          || '',
        cliente:           f.cliente           || '',
        factura:           f.factura           || '',
        fecha_desde:       f.fecha_desde       || '',
        fecha_hasta:       f.fecha_hasta       || '',
        fecha_pago_desde:  f.fecha_pago_desde  || '',
        fecha_pago_hasta:  f.fecha_pago_hasta  || '',
        estado_cobro:      f.estado_cobro      || '',
        estado_f01:        f.estado_f01        || '',
        modo_pago:         f.modo_pago         || '',
        banco:             f.banco             || '',
        cuenta:            f.cuenta            || ''
    };
    $.each(fields, function(k, v) {
        form.append($('<input type="hidden">').attr('name', k).val(v));
    });
    $('body').append(form);
    form.submit();
    form.remove();
}
function exportarPdf() {
    var f   = getFiltros();
    var tok = $('meta[name="csrf-token"]').attr('content');
    var downloadToken = 'vcpdf_' + Date.now() + '_' + Math.floor(Math.random() * 1000000);
    var cookieName = 'vc_pdf_download_token';
    document.cookie = cookieName + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';

    var form = $('<form method="POST"></form>').attr('action', '/reporte/ventas-cobros/exportar-pdf/null/null/null/null');
    var fields = {
        _token: tok, download_token: downloadToken,
        vendedor: f.vendedor || '', cliente: f.cliente || '',
        factura: f.factura || '', fecha_desde: f.fecha_desde || '',
        fecha_hasta: f.fecha_hasta || '', fecha_pago_desde: f.fecha_pago_desde || '',
        fecha_pago_hasta: f.fecha_pago_hasta || '', estado_cobro: f.estado_cobro || '',
        estado_f01: f.estado_f01 || '', modo_pago: f.modo_pago || '',
        banco: f.banco || '', cuenta: f.cuenta || ''
    };
    $.each(fields, function(k, v) { form.append($('<input type="hidden">').attr('name', k).val(v)); });
    $('body').append(form);

    Swal.fire({
        title: 'Generando PDF',
        html: 'Preparando documento...<br><small>Este proceso puede tardar varios minutos.</small>',
        allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false,
        didOpen: function() { Swal.showLoading(); }
    });

    var startedAt = Date.now();
    var timer = setInterval(function() {
        var prefix = cookieName + '=', val = '';
        document.cookie.split(';').forEach(function(c) {
            var t = c.trim();
            if (t.indexOf(prefix) === 0) val = decodeURIComponent(t.substring(prefix.length));
        });
        if (val === downloadToken) {
            clearInterval(timer);
            document.cookie = cookieName + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
            Swal.close();
        } else if (Date.now() - startedAt > 15 * 60 * 1000) {
            clearInterval(timer);
            Swal.fire({ icon: 'warning', title: 'Demora en descarga', text: 'La generación sigue en proceso. Intenta nuevamente.' });
        }
    }, 400);

    form.trigger('submit');
    setTimeout(function() { form.remove(); }, 1500);
}
function _vcProgressHtml(pct, msg) {
    var safe = Math.max(3, Math.min(100, parseInt(pct, 10) || 3));
    var label = msg || ('Procesando... ' + safe + '%');
    return '<div style="margin:10px 0 4px;">' +
        '<div style="background:#f0e6d3;border-radius:8px;height:18px;overflow:hidden;">' +
        '<div id="vc-pbar" style="height:100%;width:' + safe + '%;background:linear-gradient(90deg,#f39c12,#e05a00);' +
        'border-radius:8px;transition:width .4s ease;"></div>' +
        '</div>' +
        '<div id="vc-pbar-label" style="font-size:.78rem;color:#7d3f00;margin-top:5px;text-align:center;">' + label + '</div>' +
        '</div>' +
        '<small style="color:#999;">El reporte se procesa en segundo plano.</small>';
}

function _vcProgressUpdate(pct, msg) {
    var safe = Math.max(3, Math.min(100, parseInt(pct, 10) || 3));
    var label = msg || ('Procesando... ' + safe + '%');
    var bar = document.getElementById('vc-pbar');
    var lbl = document.getElementById('vc-pbar-label');
    if (bar) bar.style.width = safe + '%';
    if (lbl) lbl.textContent = label;
}

function exportarExcel() {
    var f   = getFiltros();
    var tok = $('meta[name="csrf-token"]').attr('content');

    Swal.fire({
        title: 'Generando Excel',
        html: _vcProgressHtml(3, 'Iniciando...'),
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false
    });

    $.ajax({
        url: '/reporte/ventas-cobros/exportar-excel-async/null/null/null/null',
        type: 'POST',
        dataType: 'json',
        data: {
            _token: tok,
            vendedor:          f.vendedor          || '',
            cliente:           f.cliente           || '',
            factura:           f.factura           || '',
            fecha_desde:       f.fecha_desde       || '',
            fecha_hasta:       f.fecha_hasta       || '',
            fecha_pago_desde:  f.fecha_pago_desde  || '',
            fecha_pago_hasta:  f.fecha_pago_hasta  || '',
            estado_cobro:      f.estado_cobro      || '',
            estado_f01:        f.estado_f01        || '',
            modo_pago:         f.modo_pago         || '',
            banco:             f.banco             || '',
            cuenta:            f.cuenta            || ''
        },
        success: function(resp) {
            if (!resp || !resp.success || !resp.token) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo iniciar la exportación.' });
                return;
            }
            _pollExportExcel(resp.token);
        },
        error: function(xhr) {
            var msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.mensaje))
                ? (xhr.responseJSON.message || xhr.responseJSON.mensaje)
                : 'No se pudo iniciar la exportación.';
            Swal.fire({ icon: 'error', title: 'Error', text: msg });
        }
    });
}

function _pollExportExcel(token) {
    var startedAt = Date.now();
    var lastPct   = 3;
    var pollId = setInterval(function() {
        $.ajax({
            url: '/reporte/ventas-cobros/exportar-excel-estado/' + encodeURIComponent(token),
            type: 'GET',
            dataType: 'json',
            success: function(resp) {
                if (!resp || !resp.success) {
                    return;
                }

                var st  = resp.status || 'queued';
                var pct = parseInt(resp.progress || 0, 10);
                if (isNaN(pct)) pct = 0;
                var msg = resp.message || '';

                if (st === 'queued') {
                    _vcProgressUpdate(Math.max(lastPct, 5), msg || 'En cola...');
                    return;
                }

                if (st === 'processing') {
                    // Avanza la barra al valor reportado o simula pequeño avance
                    lastPct = Math.max(lastPct + 1, pct);
                    lastPct = Math.min(lastPct, 95);
                    _vcProgressUpdate(lastPct, msg || ('Procesando... ' + lastPct + '%'));
                    return;
                }

                if (st === 'ready') {
                    clearInterval(pollId);
                    _vcProgressUpdate(100, '¡Listo! Descargando...');
                    setTimeout(function() {
                        Swal.close();
                        window.location.href = '/reporte/ventas-cobros/exportar-excel-descargar/' + encodeURIComponent(token);
                    }, 500);
                    return;
                }

                if (st === 'failed') {
                    clearInterval(pollId);
                    Swal.fire({
                        icon: 'error',
                        title: 'Exportación fallida',
                        text: resp.message || 'No se pudo generar el archivo.'
                    });
                }
            },
            error: function() {
                // tolerar fallos transitorios de red durante el polling
            }
        });

        // Timeout de polling en cliente (15 min)
        if (Date.now() - startedAt > 15 * 60 * 1000) {
            clearInterval(pollId);
            Swal.fire({
                icon: 'warning',
                title: 'Demora en exportación',
                text: 'La generación sigue en proceso. Intenta nuevamente en unos minutos.'
            });
        }
    }, 2500);
}

/* ────────────────────────────────────────────────────────────────────
 *  Fechas por defecto — último mes calendario
 * ──────────────────────────────────────────────────────────────────── */
function setDefaultDates() {
    var now  = new Date();
    var y    = now.getMonth() === 0 ? now.getFullYear() - 1 : now.getFullYear();
    var m    = now.getMonth() === 0 ? 11 : now.getMonth() - 1;  // 0-based
    function fmt(d) {
        return d.getFullYear() + '-' +
               String(d.getMonth() + 1).padStart(2, '0') + '-' +
               String(d.getDate()).padStart(2, '0');
    }
    $('#fil_fecha_desde').val(fmt(new Date(y, m, 1)));
    $('#fil_fecha_hasta').val(fmt(new Date(y, m + 1, 0)));
}

/* ────────────────────────────────────────────────────────────────────
 *  Barra de filtros activos (badges)
 * ──────────────────────────────────────────────────────────────────── */
function actualizarBadges() {
    var f = getFiltros();
    var defs = [
        { key: 'cliente',          el: '#fil_cliente',          lbl: 'Cliente',      isSelect: true },
        { key: 'factura',          el: '#fil_factura',          lbl: 'Factura',      isSelect: false },
        { key: 'vendedor',         el: '#fil_vendedor',         lbl: 'Vendedor',     isSelect: true },
        { key: 'modo_pago',        el: '#fil_modo_pago',        lbl: 'Pago',         isSelect: true },
        { key: 'estado_f01',       el: '#fil_estado_f01',       lbl: 'F-01',         isSelect: true },
        { key: 'fecha_desde',      el: '#fil_fecha_desde',      lbl: 'Desde',        isSelect: false },
        { key: 'fecha_hasta',      el: '#fil_fecha_hasta',      lbl: 'Hasta',        isSelect: false },
        { key: 'estado_cobro',     el: '#fil_estado_cobro',     lbl: 'Estado',       isSelect: true },
        { key: 'fecha_pago_desde', el: '#fil_fecha_pago_desde', lbl: 'P.Desde',      isSelect: false },
        { key: 'fecha_pago_hasta', el: '#fil_fecha_pago_hasta', lbl: 'P.Hasta',      isSelect: false },
        { key: 'banco',            el: '#fil_banco',            lbl: 'Banco',        isSelect: true },
        { key: 'cuenta',           el: '#fil_cuenta',           lbl: 'Cuenta',       isSelect: false }
    ];
    var bar = $('#rvc_filtros_bar').empty();
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
            '<span class="filtro-badge">' +
            '<strong>' + d.lbl + ':</strong>&nbsp;' + escHtml(displayVal) +
            '<span class="filtro-remove" data-key="' + d.key + '" data-el="' + d.el + '" data-select="' + (d.isSelect ? '1' : '0') + '">&times;</span>' +
            '</span>'
        );
    });
    bar.toggle(has);
}

/* ────────────────────────────────────────────────────────────────────
 *  Aplicar filtros (desde modal) y limpiar
 * ──────────────────────────────────────────────────────────────────── */
function aplicarFiltros() {
    $('#modalFiltrosRVC').modal('hide');
    actualizarBadges();
    cargarTabla();
}

function limpiarFiltros() {
    if ($.fn.select2) {
        $('#fil_cliente, #fil_vendedor').val('').trigger('change');
    } else {
        $('#fil_cliente, #fil_vendedor').val('');
    }
    $('#fil_modo_pago, #fil_estado_f01, #fil_estado_cobro, #fil_banco').val('');
    $('#fil_factura, #fil_cuenta, #fil_fecha_pago_desde, #fil_fecha_pago_hasta').val('');
    setDefaultDates();
}

/* ────────────────────────────────────────────────────────────────────
 *  Document ready
 * ──────────────────────────────────────────────────────────────────── */
$(document).ready(function() {
    /* Select2 en cliente y vendedor si está disponible */
    if ($.fn.select2) {
        $('#fil_cliente').select2({
            placeholder: '— Todos —', allowClear: true,
            dropdownParent: $('#modalFiltrosRVC')
        });
        $('#fil_vendedor').select2({
            placeholder: '— Todos —', allowClear: true,
            dropdownParent: $('#modalFiltrosRVC')
        });
    }

    /* Fechas por defecto = último mes */
    setDefaultDates();

    /* Carga inicial con filtros por defecto */
    actualizarBadges();
    cargarTabla();

    /* Enter en factura aplica filtros */
    $(document).on('keypress', '#fil_factura', function(e) {
        if (e.which === 13) aplicarFiltros();
    });

    /* Quitar filtro individual desde la barra de badges */
    $(document).on('click', '.filtro-remove', function() {
        var el  = $(this).data('el');
        var sel = $(this).data('select');
        if (sel === 1 || sel === '1') {
            $(el).val('');
            if ($.fn.select2 && $(el).hasClass('select2-hidden-accessible')) {
                $(el).val('').trigger('change');
            }
        } else {
            $(el).val('');
        }
        actualizarBadges();
        cargarTabla();
    });
});
