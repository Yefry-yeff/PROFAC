/* =====================================================================
   Reporte de Ventas y Cobros – JS
   ===================================================================== */

var tblVentasCobros = null;

/* ─── helpers de filtros ─── */
function getVendedor() { return $('#fil_vendedor').val() || 'null'; }
function getCliente()  { return $('#fil_cliente').val()  || 'null'; }
function getMes()      { return $('#fil_mes').val()      || 'null'; }
function getAnio()     { return $('#fil_anio').val()     || 'null'; }

/* ─── formato moneda lempiras ─── */
function fmtLps(v) {
    var n = parseFloat(v);
    return (n && n !== 0) ? 'L ' + n.toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
}

/* ─── badge estado crédito ─── */
function renderEstado(val) {
    var map = {
        'Vencida'   : '<span class="badge" style="background:#e74c3c;color:#fff">Vencida</span>',
        'Cancelada' : '<span class="badge" style="background:#27ae60;color:#fff">Cancelada</span>',
        'Contado'   : '<span class="badge" style="background:#2980b9;color:#fff">Contado</span>',
        'Vigente'   : '<span class="badge" style="background:#1ab394;color:#fff">Vigente</span>',
    };
    return map[val] || val || '';
}

/* ─── cargar tabla ─── */
function cargarTabla() {
    if (tblVentasCobros) {
        tblVentasCobros.destroy();
        tblVentasCobros = null;
    }

    tblVentasCobros = $('#tbl_ventas_cobros').DataTable({
        processing  : true,
        serverSide  : true,
        order       : [[18, 'desc']],   /* fecha_venta desc */
        scrollX     : true,
        language    : { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        pageLength  : 25,
        dom         : 'lTfgitp',
        ajax: {
            url  : '/reporte/ventas-cobros/consulta/' + getVendedor() + '/' + getCliente() + '/' + getMes() + '/' + getAnio(),
            type : 'GET',
            error: function (xhr) {
                Swal.fire('Error', 'No se pudo cargar el reporte.', 'error');
            }
        },
        columns: [
            /* #0 */  { data: 'item',              className: 'text-center' },
            /* #1 */  { data: 'mes',               className: 'text-center' },
            /* #2 */  { data: 'vendedor',           className: 'text-left'  },
            /* #3 */  { data: 'cliente',            className: 'text-left'  },
            /* #4 */  { data: 'factura',            className: 'text-center' },
            /* #5 */  { data: 'observacion',        className: 'text-left'  },
            /* #6 */  { data: 'orden_compra',       className: 'text-center' },
            /* #7 */  { data: 'modo_pago',          className: 'text-center' },
            /* #8 */  { data: 'estado_f01',         className: 'text-center' },
            /* #9 */  { data: 'exonerado',          className: 'text-right',  render: function(v) { return fmtLps(v); } },
            /* #10 */ { data: 'gravado',            className: 'text-right',  render: function(v) { return fmtLps(v); } },
            /* #11 */ { data: 'exento',             className: 'text-right',  render: function(v) { return fmtLps(v); } },
            /* #12 */ { data: 'abonos',             className: 'text-right',  render: function(v) { return fmtLps(v); } },
            /* #13 */ { data: 'sub_total',          className: 'text-right',  render: function(v) { return fmtLps(v); } },
            /* #14 */ { data: 'isv',                className: 'text-right',  render: function(v) { return fmtLps(v); } },
            /* #15 */ { data: 'total',              className: 'text-right',  render: function(v) { return fmtLps(v); } },
            /* #16 */ { data: 'saldo_pendiente',    className: 'text-right readonly-col', render: function(v) { return fmtLps(v); } },
            /* #17 */ { data: 'monto_pagado',       className: 'text-right',  render: function(v) { return fmtLps(v); } },
            /* #18 */ { data: 'fecha_venta',        className: 'text-center' },
            /* #19 */ { data: 'fecha_vencimiento',  className: 'text-center' },
            /* #20 */ { data: 'dias_vencidos',      className: 'text-center readonly-col', render: function(v) { return v + ' días'; } },
            /* #21 */ { data: 'creditos_vencidos',  className: 'text-center',  render: renderEstado },
            /* #22 */ { data: 'fecha_pago',         className: 'text-center' },
            /* #23 */ { data: 'forma_pago',         className: 'text-left'  },
            /* #24 */ { data: 'cuenta_banco',       className: 'text-left'  },
            /* #25 */ { data: 'fecha_entrega',      className: 'text-center' },
            /* #26 */ { data: 'recibo',             className: 'text-center' },
        ],
        /* Colorear filas según estado */
        createdRow: function(row, data) {
            if (data.creditos_vencidos === 'Vencida')   { $(row).css('background', '#fdf2f0'); }
            if (data.creditos_vencidos === 'Cancelada') { $(row).css('background', '#f0faf3'); }
        }
    });
}

/* ─── Exportar PDF ─── */
function exportarPdf() {
    var token = document.querySelector('meta[name="csrf-token"]').content;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/ventas-cobros/exportar-pdf/' + getVendedor() + '/' + getCliente() + '/' + getMes() + '/' + getAnio();
    var t = document.createElement('input'); t.type = 'hidden'; t.name = '_token'; t.value = token;
    form.appendChild(t);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

/* ─── Exportar Excel ─── */
function exportarExcel() {
    var token = document.querySelector('meta[name="csrf-token"]').content;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/ventas-cobros/exportar-excel/' + getVendedor() + '/' + getCliente() + '/' + getMes() + '/' + getAnio();
    var t = document.createElement('input'); t.type = 'hidden'; t.name = '_token'; t.value = token;
    form.appendChild(t);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

/* ─── Inicializar ─── */
$(document).ready(function () {
    cargarTabla();
});
