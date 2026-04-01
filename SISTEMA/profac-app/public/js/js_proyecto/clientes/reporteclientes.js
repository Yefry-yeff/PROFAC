/* =====================================================================
   Reporte de Clientes – JS
   ===================================================================== */

var tblGeneral    = null;
var tblSinCredito = null;
var tblGobierno   = null;

/* ─── helpers ─── */
function getVendedor() { return $('#fil_vendedor').val() || 'null'; }
function getEstado()   { return $('#fil_estado').val()   || 'null'; }

function renderDocBadge(val) {
    if (val === 'X')        return '<span class="badge badge-success">X</span>';
    if (val === 'SOLICITAR') return '<span class="badge badge-danger" style="font-size:.75rem">SOLICITAR</span>';
    if (val === 'N/A')      return '<span class="text-muted">N/A</span>';
    return val || '';
}

/* ─── cargar las tres tablas ─── */
function cargarTablas() {
    cargarGeneral();
    cargarSinCredito();
    cargarGobierno();
}

/* ── Hoja 1: General ─────────────────────────────────────────────── */
function cargarGeneral() {
    if (tblGeneral) { tblGeneral.destroy(); }

    tblGeneral = $('#tbl_rep_general').DataTable({
        processing: true,
        serverSide: true,
        order: [[3, 'asc']],
        scrollX: true,
        language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        pageLength: 15,
        dom: 'lTfgitp',
        ajax: {
            url: '/reporte/clientes/consulta-general/' + getVendedor() + '/' + getEstado(),
            type: 'GET',
            error: function(xhr) {
                Swal.fire('Error', 'No se pudo cargar el reporte de clientes.', 'error');
            }
        },
        columns: [
            { data: 'item',               className: 'text-center' },
            { data: 'anio_ingreso',        className: 'text-center' },
            { data: 'vendedor' },
            { data: 'cliente' },
            { data: 'codigo',              className: 'text-center' },
            { data: 'solicitud_credito',   className: 'text-center', render: renderDocBadge },
            { data: 'condiciones_credito', className: 'text-center', render: renderDocBadge },
            { data: 'doc_escritura',       className: 'text-center', render: renderDocBadge },
            { data: 'doc_dni',             className: 'text-center', render: renderDocBadge },
            { data: 'doc_rtn',             className: 'text-center', render: renderDocBadge },
            { data: 'doc_permiso',         className: 'text-center', render: renderDocBadge },
            { data: 'anio_operacion',      className: 'text-center' },
            { data: 'doc_croquis',         className: 'text-center', render: renderDocBadge },
            { data: 'ref_bancarias',       className: 'text-center', render: renderDocBadge },
            { data: 'ref_comerciales',     className: 'text-center', render: renderDocBadge },
            { data: 'ref_referencias' },
            { data: 'ref_tiempo_relacion' },
            { data: 'ref_tiempo_credito',  className: 'text-center' },
            { data: 'ref_limite_credito',  className: 'text-right', render: function(v) { return v && v != 0 ? 'L ' + parseFloat(v).toLocaleString('es-HN', {minimumFractionDigits:2}) : ''; } },
            { data: 'metodo_pago' },
            { data: 'confirmacion' },
            { data: 'obs_referencias' },
            { data: 'fecha_validacion_ref', className: 'text-center' },
            { data: 'realizo' },
            { data: 'letra_cambio',        className: 'text-center', render: renderDocBadge },
            { data: 'aval_solidario',      className: 'text-center', render: renderDocBadge },
            { data: 'doc_contrato',        className: 'text-center', render: renderDocBadge },
            { data: 'doc_fotos',           className: 'text-center', render: renderDocBadge },
            { data: 'estado_cliente',      className: 'text-center' },
            { data: 'monto_credito',       className: 'text-right', render: function(v) { var n=parseFloat(v); return n>0 ? 'L ' + n.toLocaleString('es-HN',{minimumFractionDigits:2}) : ''; } },
            { data: 'plazo_credito',       className: 'text-center', render: function(v) { return v && v>0 ? v + ' días' : ''; } },
            { data: 'observaciones' },
            { data: 'autorizado_gerencia' },
            { data: 'fecha_notif_limite',  className: 'text-center' }
        ]
    });
}

/* ── Hoja 2: Sin Crédito ─────────────────────────────────────────── */
function cargarSinCredito() {
    if (tblSinCredito) { tblSinCredito.destroy(); }

    tblSinCredito = $('#tbl_rep_sincredito').DataTable({
        processing: true,
        serverSide: true,
        order: [[2, 'asc']],
        scrollX: true,
        language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        pageLength: 15,
        dom: 'lTfgitp',
        ajax: {
            url: '/reporte/clientes/consulta-sincredito/' + getVendedor() + '/' + getEstado(),
            type: 'GET'
        },
        columns: [
            { data: 'item',         className: 'text-center' },
            { data: 'vendedor' },
            { data: 'cliente' },
            { data: 'codigo',       className: 'text-center' },
            { data: 'estado',       className: 'text-center' },
            { data: 'observaciones' }
        ]
    });
}

/* ── Hoja 3: Gobierno ───────────────────────────────────────── */
function cargarGobierno() {
    if (tblGobierno) { tblGobierno.destroy(); }

    tblGobierno = $('#tbl_rep_gobierno').DataTable({
        processing: true,
        serverSide: true,
        order: [[2, 'asc']],
        scrollX: true,
        language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        pageLength: 15,
        dom: 'lTfgitp',
        ajax: {
            url: '/reporte/clientes/consulta-gobierno/' + getVendedor() + '/' + getEstado(),
            type: 'GET'
        },
        columns: [
            { data: 'item',          className: 'text-center' },
            { data: 'vendedor' },
            { data: 'cliente' },
            { data: 'codigo',        className: 'text-center' },
            { data: 'plazo_credito', className: 'text-center', render: function(v) { return v && v>0 ? v + ' días' : ''; } },
            { data: 'estado_cliente', className: 'text-center' }
        ]
    });
}

/* ─── Exportar PDF ─────────────────────────────────────────────────── */
function exportarPdf() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/clientes/exportar-pdf/' + getVendedor() + '/' + getEstado();
    var t = document.createElement('input'); t.type='hidden'; t.name='_token'; t.value=csrfToken;
    form.appendChild(t);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

/* ─── Exportar Excel ───────────────────────────────────────────────── */
function exportarExcel() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/clientes/exportar-excel/' + getVendedor() + '/' + getEstado();
    var t = document.createElement('input'); t.type='hidden'; t.name='_token'; t.value=csrfToken;
    form.appendChild(t);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

/* ─── Inicializar al cargar ─────────────────────────────────────────── */
$(document).ready(function () {
    cargarTablas();
});
