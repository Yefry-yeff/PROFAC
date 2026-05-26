/* ============================================================
 *  Reporte de Clientes – DataTables + AJAX
 * ============================================================ */

var tblGeneral    = null;
var tblSincredito = null;
var tblGobierno   = null;

/* ── helpers ─────────────────────────────────────────────── */
function docBadge(val) {
    if (val === 'X')        return '<span class="x-mark"><i class="fa fa-check"></i> Digital</span>';
    if (val === 'FISICO')   return '<span class="fisico-mark"><i class="fa fa-file-text"></i> Físico</span>';
    if (val === 'SOLICITAR')return '<span class="sol-mark">SOLICITAR</span>';
    if (val === 'N/A')      return '<span class="na-mark">N/A</span>';
    return val || '';
}

function xBadge(val) {
    if (val === 'X')        return '<span class="x-mark"><i class="fa fa-check"></i></span>';
    if (val === 'SOLICITAR')return '<span class="sol-mark">SOLICITAR</span>';
    if (val === 'N/A')      return '<span class="na-mark">N/A</span>';
    return val || '';
}

function filVendedor() { return $('#fil_vendedor').val() || 'null'; }
function filEstado()   { return $('#fil_estado').val()   || 'null'; }

/* ── inicializar tablas ───────────────────────────────────── */
function initTablas() {

    /* ── Hoja 1: General ─────────────────────────────────── */
    if (tblGeneral) { tblGeneral.destroy(); }
    tblGeneral = $('#tbl_rep_general').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/reporte/clientes/consulta-general/' + filVendedor() + '/' + filEstado(),
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            { data: 'item',                 defaultContent: '' },
            { data: 'anio_ingreso',         defaultContent: '' },
            { data: 'vendedor',             defaultContent: '' },
            { data: 'cliente',              defaultContent: '' },
            { data: 'codigo',               defaultContent: '' },
            { data: 'solicitud_credito',    defaultContent: '', render: xBadge },
            { data: 'condiciones_credito',  defaultContent: '', render: xBadge },
            { data: 'doc_escritura',        defaultContent: '', render: docBadge },
            { data: 'doc_dni',              defaultContent: '', render: docBadge },
            { data: 'doc_rtn',              defaultContent: '', render: docBadge },
            { data: 'doc_permiso',          defaultContent: '', render: docBadge },
            { data: 'anio_operacion',       defaultContent: '' },
            { data: 'doc_croquis',          defaultContent: '', render: docBadge },
            { data: 'ref_bancarias',        defaultContent: '', render: xBadge },
            { data: 'ref_comerciales',      defaultContent: '', render: xBadge },
            { data: 'ref_referencias',      defaultContent: '' },
            { data: 'ref_tiempo_relacion',  defaultContent: '' },
            { data: 'ref_tiempo_credito',   defaultContent: '' },
            { data: 'ref_limite_credito',   defaultContent: '' },
            { data: 'metodo_pago',          defaultContent: '' },
            { data: 'confirmacion',         defaultContent: '' },
            { data: 'obs_referencias',      defaultContent: '' },
            { data: 'fecha_validacion_ref', defaultContent: '' },
            { data: 'realizo',              defaultContent: '' },
            { data: 'letra_cambio',         defaultContent: '', render: xBadge },
            { data: 'aval_solidario',       defaultContent: '', render: xBadge },
            { data: 'doc_contrato',         defaultContent: '', render: docBadge },
            { data: 'doc_fotos',            defaultContent: '', render: docBadge },
            { data: 'estado_cliente',       defaultContent: '' },
            { data: 'monto_credito',        defaultContent: '' },
            { data: 'plazo_credito',        defaultContent: '' },
            { data: 'observaciones',        defaultContent: '' },
            { data: 'autorizado_gerencia',  defaultContent: '' },
            { data: 'fecha_notif_limite',   defaultContent: '' }
        ],
        language: { url: '/vendor/datatables/spanish.json', processing: 'Cargando...' },
        scrollX: true,
        pageLength: 25
    });

    /* ── Hoja 2: Sin Crédito ─────────────────────────────── */
    if (tblSincredito) { tblSincredito.destroy(); }
    tblSincredito = $('#tbl_rep_sincredito').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/reporte/clientes/consulta-sincredito/' + filVendedor() + '/' + filEstado(),
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            { data: 'item',         defaultContent: '' },
            { data: 'vendedor',     defaultContent: '' },
            { data: 'cliente',      defaultContent: '' },
            { data: 'codigo',       defaultContent: '' },
            { data: 'estado',       defaultContent: '' },
            { data: 'observaciones',defaultContent: '' }
        ],
        language: { url: '/vendor/datatables/spanish.json', processing: 'Cargando...' },
        scrollX: true,
        pageLength: 25
    });

    /* ── Hoja 3: Gobierno ────────────────────────────────── */
    if (tblGobierno) { tblGobierno.destroy(); }
    tblGobierno = $('#tbl_rep_gobierno').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/reporte/clientes/consulta-gobierno/' + filVendedor() + '/' + filEstado(),
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            { data: 'item',          defaultContent: '' },
            { data: 'vendedor',      defaultContent: '' },
            { data: 'cliente',       defaultContent: '' },
            { data: 'codigo',        defaultContent: '' },
            { data: 'plazo_credito', defaultContent: '' },
            { data: 'estado_cliente',defaultContent: '' }
        ],
        language: { url: '/vendor/datatables/spanish.json', processing: 'Cargando...' },
        scrollX: true,
        pageLength: 25
    });
}

/* ── botón Consultar ─────────────────────────────────────── */
function cargarTablas() {
    initTablas();
}

/* ── exportar PDF ────────────────────────────────────────── */
function exportarPdf() {
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/clientes/exportar-pdf/' + filVendedor() + '/' + filEstado();
    var tok = document.createElement('input');
    tok.type  = 'hidden';
    tok.name  = '_token';
    tok.value = document.getElementById('csrf_token') ? document.getElementById('csrf_token').value : '';
    form.appendChild(tok);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

/* ── exportar Excel ──────────────────────────────────────── */
function exportarExcel() {
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/clientes/exportar-excel/' + filVendedor() + '/' + filEstado();
    var tok = document.createElement('input');
    tok.type  = 'hidden';
    tok.name  = '_token';
    tok.value = document.getElementById('csrf_token') ? document.getElementById('csrf_token').value : '';
    form.appendChild(tok);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

/* ── cargar automáticamente al abrir la página ───────────── */
$(document).ready(function () {
    cargarTablas();
});
