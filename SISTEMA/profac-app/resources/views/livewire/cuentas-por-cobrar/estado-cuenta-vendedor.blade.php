<div>
@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   Estado de Cuenta — Estilos
═══════════════════════════════════════════════════════ */
@keyframes ec-fadeInUp {
    from { opacity:0; transform:translateY(16px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes ec-fadeIn {
    from { opacity:0; } to { opacity:1; }
}
@keyframes ec-badge-pop {
    0%   { transform:scale(.7); opacity:0; }
    70%  { transform:scale(1.15); }
    100% { transform:scale(1); opacity:1; }
}

/* ── HERO BANNER ── */
.ec-hero {
    background: linear-gradient(135deg, #1a202c 0%, #2d3748 60%, #4a5568 100%);
    border-radius: 16px;
    padding: 28px 32px;
    margin: 24px 0 0;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 8px 32px rgba(0,0,0,.18);
    animation: ec-fadeInUp .45s ease both;
    position: relative;
    overflow: hidden;
}
.ec-hero::before {
    content:'';
    position: absolute; top:-40px; right:-40px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: rgba(243,156,18,.12);
    pointer-events: none;
}
.ec-hero::after {
    content:'';
    position: absolute; bottom:-50px; right:80px;
    width: 120px; height: 120px;
    border-radius: 50%;
    background: rgba(243,156,18,.07);
    pointer-events: none;
}
.ec-hero-icon {
    width: 56px; height: 56px;
    border-radius: 14px;
    background: rgba(243,156,18,.2);
    border: 2px solid rgba(243,156,18,.4);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; color: #f39c12;
    flex-shrink: 0;
}
.ec-hero-body h3 {
    margin: 0; font-size: 18px; font-weight: 800;
    letter-spacing: .3px;
}
.ec-hero-body p {
    margin: 4px 0 0; font-size: 12px;
    color: rgba(255,255,255,.65);
}
.ec-badge-readonly {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 20px;
    font-size: 10px; font-weight: 700;
    padding: 3px 10px;
    color: rgba(255,255,255,.85);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-top: 8px;
}

/* ── SEARCH CARD ── */
.ec-search-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 6px 28px rgba(0,0,0,.08);
    padding: 24px 28px;
    margin: 18px 0 0;
    border: 1px solid #edf2f7;
    animation: ec-fadeInUp .5s .1s ease both;
}
.ec-search-title {
    font-size: 12px;
    font-weight: 700;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: .6px;
    margin-bottom: 14px;
    display: flex; align-items: center; gap: 8px;
}
.ec-search-title i { color: #f39c12; }

.ec-search-row {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}
.ec-select-wrap { flex: 0 0 50%; max-width: 50%; }
.ec-select-wrap label {
    font-size: 11px; font-weight: 700;
    color: #718096; text-transform: uppercase;
    letter-spacing: .4px; margin-bottom: 6px; display: block;
}
.ec-select-wrap .form-control:focus {
    border-color: #f39c12;
    box-shadow: 0 0 0 3px rgba(243,156,18,.15);
}

/* ── BUTTONS ── */
.ec-btn-search {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 10px;
    padding: 10px 22px;
    font-size: 13px; font-weight: 700;
    cursor: pointer; white-space: nowrap;
    display: inline-flex !important;
    align-items: center; gap: 8px;
    height: 42px;
    box-shadow: 0 4px 14px rgba(243,156,18,.35);
    transition: transform .15s, box-shadow .15s;
}
.ec-btn-search:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(243,156,18,.45);
    color: #fff !important;
}
.ec-btn-pdf-main {
    background: #fff !important;
    color: #e53e3e !important;
    border: 1.5px solid #e53e3e !important;
    border-radius: 10px;
    padding: 9px 18px;
    font-size: 13px; font-weight: 700;
    cursor: pointer; white-space: nowrap;
    display: inline-flex !important;
    align-items: center; gap: 8px;
    height: 42px;
    transition: background .15s, color .15s, transform .15s;
}
.ec-btn-pdf-main:hover {
    background: #e53e3e !important;
    color: #fff !important;
    transform: translateY(-1px);
}
.ec-btn-group { display: flex; gap: 8px; align-items: center; }

/* ── STATS CARDS ── */
.ec-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 14px;
    margin-top: 18px;
    animation: ec-fadeInUp .5s .2s ease both;
}
.ec-stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 16px 18px;
    border-left: 4px solid #f39c12;
    box-shadow: 0 3px 14px rgba(0,0,0,.06);
    transition: transform .15s, box-shadow .15s;
}
.ec-stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.1); }
.ec-stat-card.blue  { border-left-color: #4299e1; }
.ec-stat-card.green { border-left-color: #48bb78; }
.ec-stat-card.red   { border-left-color: #e53e3e; }
.ec-stat-label {
    font-size: 10px; font-weight: 700; color: #a0aec0;
    text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px;
}
.ec-stat-value {
    font-size: 20px; font-weight: 800; color: #2d3748;
    font-variant-numeric: tabular-nums;
}
.ec-stat-value.red { color: #e53e3e; }

/* ── TABLE PANEL ── */
.ec-panel {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0,0,0,.07);
    border: 1px solid #e2e8f0;
    padding: 24px;
    margin-top: 18px;
    animation: ec-fadeInUp .45s .25s ease both;
}
.ec-panel-header {
    display: flex; align-items: center; justify-content: space-between;
    margin: -24px -24px 20px -24px;
    padding: 14px 20px;
    background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
    border-radius: 14px 14px 0 0;
    color: #fff;
}
.ec-panel-header h6 {
    margin: 0; font-size: 14px; font-weight: 800;
    display: flex; align-items: center; gap: 8px;
}
.ec-panel-header .ec-panel-badge {
    background: rgba(255,255,255,.15);
    border-radius: 20px; padding: 2px 10px;
    font-size: 10px; font-weight: 700;
}

/* DataTable overrides */
.ec-panel .dataTables_wrapper .dataTables_filter input,
.ec-panel .dataTables_wrapper .dataTables_length select {
    border: 1.5px solid #dde2ec; border-radius: 8px;
    padding: 5px 10px; font-size: 12px;
    transition: border-color .2s, box-shadow .2s;
}
.ec-panel .dataTables_wrapper .dataTables_filter input:focus {
    border-color: #f39c12; outline: none;
    box-shadow: 0 0 0 3px rgba(243,156,18,.12);
}
.ec-panel table.dataTable thead th {
    background: #f5f7fb; color: #4a5568;
    font-size: 11px; font-weight: 800;
    text-transform: uppercase; letter-spacing: .4px;
    border-top: none !important;
    border-bottom: 2px solid #e2e8f0 !important;
    white-space: nowrap; padding: 10px 12px;
}
.ec-panel table.dataTable tbody tr { transition: background .18s; }
.ec-panel table.dataTable tbody tr:nth-child(even) { background: #f7f9fc !important; }
.ec-panel table.dataTable tbody tr:nth-child(odd)  { background: #ffffff !important; }
.ec-panel table.dataTable tbody tr:hover {
    background: #fff8ec !important;
    box-shadow: 0 2px 10px rgba(243,156,18,.1);
    position: relative; z-index: 1;
}
.ec-panel table.dataTable td {
    font-size: 12.5px; color: #2d3748;
    padding: 9px 12px; vertical-align: middle;
}

/* ── BUTTONS EN TABLA ── */
.ec-btn-detalle {
    font-size: 11px !important; font-weight: 700 !important;
    border-radius: 7px !important; padding: 4px 10px !important;
    white-space: nowrap;
}
.ec-tbl-pdf { font-size: 11px !important; border-radius: 7px !important; padding: 4px 9px !important; }

/* ── EMPTY STATE ── */
.ec-empty { text-align: center; padding: 52px 20px; color: #a0aec0; }
.ec-empty i { font-size: 56px; display: block; margin-bottom: 14px; opacity: .25; }
.ec-empty p  { font-size: 14px; }

/* ── Select2 ── */
.ec-search-card .select2-container { width: 100% !important; }
.ec-search-card .select2-container .select2-selection--single {
    height: 40px !important; border: 1.5px solid #dde2ec !important;
    border-radius: 9px !important; background: #fff !important;
    display: flex; align-items: center;
    transition: border-color .18s, box-shadow .18s;
}
.ec-search-card .select2-container--open .select2-selection--single {
    border-color: #f39c12 !important;
    box-shadow: 0 0 0 3px rgba(243,156,18,.15) !important;
}
.ec-search-card .select2-selection__rendered {
    color: #2d3748 !important; font-size: 13px !important;
    line-height: 38px !important; padding-left: 13px !important;
}
.ec-search-card .select2-selection__arrow { height: 38px !important; right: 8px !important; }
.ec-select2-drop.select2-dropdown {
    border: 1.5px solid #f39c12 !important; border-radius: 9px !important;
    box-shadow: 0 8px 32px rgba(243,156,18,.2) !important;
    font-size: 13px !important; z-index: 99999 !important; overflow: hidden;
}
.ec-select2-drop .select2-results__option--highlighted {
    background: linear-gradient(135deg, #f39c12, #e67e22) !important; color: #fff !important;
}
</style>
@endpush

{{-- ===== PAGE HEADER ===== --}}
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><i class="fa-solid fa-magnifying-glass-dollar text-warning"></i> Estado de Cuenta — Consulta</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item">Cuentas por Cobrar</li>
            <li class="breadcrumb-item active"><strong>Estado de Cuenta</strong></li>
        </ol>
    </div>
</div>

{{-- ===== CONTENT ===== --}}
<div class="wrapper wrapper-content pb-4">

    {{-- Hero banner --}}
    <div class="ec-hero">
        <div class="ec-hero-icon">
            <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
        <div class="ec-hero-body">
            <h3>Estado de Cuenta de Clientes</h3>
            <p>Consulta el saldo y detalle de facturas pendientes por cliente. Solo lectura.</p>
            <span class="ec-badge-readonly">
                <i class="fa-solid fa-eye"></i> Módulo de consulta
            </span>
        </div>
    </div>

    {{-- Search card --}}
    <div class="ec-search-card">
        <div class="ec-search-title">
            <i class="fa-solid fa-magnifying-glass"></i> Seleccionar Cliente
        </div>
        <div class="ec-search-row">
            <div class="ec-select-wrap">
                <label><i class="fa fa-building-o mr-1"></i> Cliente</label>
                <select id="ecCliente" name="ecCliente" class="form-control">
                    <option value="" disabled selected>— Escriba para buscar —</option>
                </select>
            </div>
            <div class="ec-btn-group">
                <button type="button" class="ec-btn-search" onclick="ecCargarDatos()">
                    <i class="fa fa-search"></i> Consultar
                </button>
                <button type="button" class="ec-btn-pdf-main d-none" id="ecBtnPdf" onclick="ecVerPdf()">
                    <i class="fa fa-file-pdf-o"></i> Estado de Cuenta PDF
                </button>
            </div>
        </div>
    </div>

    {{-- Stats cards (hidden until client selected) --}}
    <div class="ec-stats-row d-none" id="ecStats">
        <div class="ec-stat-card">
            <div class="ec-stat-label"><i class="fa fa-list mr-1"></i> Facturas Pendientes</div>
            <div class="ec-stat-value" id="ecStatFacturas">—</div>
        </div>
        <div class="ec-stat-card blue">
            <div class="ec-stat-label"><i class="fa fa-dollar mr-1"></i> Total Cargo</div>
            <div class="ec-stat-value" id="ecStatCargo">—</div>
        </div>
        <div class="ec-stat-card red">
            <div class="ec-stat-label"><i class="fa-solid fa-scale-unbalanced-flip mr-1"></i> Saldo Total</div>
            <div class="ec-stat-value red" id="ecStatSaldo">—</div>
        </div>
        <div class="ec-stat-card green">
            <div class="ec-stat-label"><i class="fa fa-check-circle mr-1"></i> Total Abonado</div>
            <div class="ec-stat-value" id="ecStatAbonado">—</div>
        </div>
    </div>

    {{-- Tabs panel (oculto hasta seleccionar cliente) --}}
    <div id="ecTabsContainer" style="display:none; margin-top:18px;">

        {{-- Nav tabs --}}
        <ul class="nav nav-tabs" id="ecTabs" role="tablist" style="border-bottom:2px solid #e2e8f0;">
            <li class="nav-item">
                <a class="nav-link active" id="tab-saldos-link" data-toggle="tab" href="#tab-saldos" role="tab">
                    <i class="fa fa-file-invoice-dollar mr-1"></i> Saldos por Factura
                    <span class="badge badge-warning ml-1" id="ecBadgeSaldos">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-movimientos-link" data-toggle="tab" href="#tab-movimientos" role="tab">
                    <i class="fa fa-exchange mr-1"></i> Movimientos
                    <span class="badge badge-secondary ml-1" id="ecBadgeMovimientos">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-abonos-link" data-toggle="tab" href="#tab-abonos" role="tab">
                    <i class="fa fa-credit-card mr-1"></i> Créditos y Abonos
                    <span class="badge badge-success ml-1" id="ecBadgeAbonos">0</span>
                </a>
            </li>
        </ul>

        <div class="tab-content" id="ecTabContent">

            {{-- TAB 1: Saldos por Factura --}}
            <div class="tab-pane fade show active" id="tab-saldos" role="tabpanel">
                <div class="ec-panel" style="border-radius:0 0 14px 14px; margin-top:0;">
                    <div class="ec-panel-header" style="border-radius:0;">
                        <h6>
                            <i class="fa-solid fa-table-list"></i>
                            Registros de Saldos por Factura
                            <span class="ec-panel-badge" id="ecClienteNombre"></span>
                        </h6>
                        <small style="color:rgba(255,255,255,.55); font-size:11px;">
                            <i class="fa-solid fa-eye mr-1"></i> Solo lectura
                        </small>
                    </div>
                    <table id="ecTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th><th>Factura</th><th>Fecha Emisión</th>
                                <th>Cargo</th><th>Abonos</th><th>Notas Cred.</th>
                                <th>Notas Déb.</th><th>Retención ISV</th>
                                <th>Saldo</th><th>Estado</th><th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- TAB 2: Movimientos --}}
            <div class="tab-pane fade" id="tab-movimientos" role="tabpanel">
                <div class="ec-panel" style="border-radius:0 0 14px 14px; margin-top:0;">
                    <div class="ec-panel-header" style="border-radius:0;">
                        <h6>
                            <i class="fa fa-exchange"></i> Movimientos
                            <span class="ec-panel-badge" id="ecMovClienteNombre"></span>
                        </h6>
                        <button class="btn btn-sm btn-success" onclick="ecExportarMovimientos()">
                            <i class="fa fa-file-excel-o mr-1"></i> Exportar Excel
                        </button>
                    </div>
                    <table id="ecTableMovimientos" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th><th>Cod. Pago</th><th>Correlativo</th>
                                <th>Monto</th><th>Tipo Movimiento</th><th>Comentario</th>
                                <th>Usuario</th><th>Fecha Registro</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- TAB 3: Créditos y Abonos --}}
            <div class="tab-pane fade" id="tab-abonos" role="tabpanel">
                <div class="ec-panel" style="border-radius:0 0 14px 14px; margin-top:0;">
                    <div class="ec-panel-header" style="border-radius:0;">
                        <h6>
                            <i class="fa fa-credit-card"></i> Créditos y Abonos
                            <span class="ec-panel-badge" id="ecAbonosClienteNombre"></span>
                        </h6>
                        <button class="btn btn-sm btn-success" onclick="ecExportarAbonos()">
                            <i class="fa fa-file-excel-o mr-1"></i> Exportar Excel
                        </button>
                    </div>
                    <table id="ecTableAbonos" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th><th>Cod. Pago</th><th>Correlativo</th>
                                <th>Monto</th><th>Comentario</th>
                                <th>Usuario</th><th>Fecha Depósito</th><th>Fecha Registro</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Empty state (initial) --}}
    <div class="ec-empty" id="ecEmptyState">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        <p>Seleccione un cliente para ver su estado de cuenta.</p>
    </div>

</div>

@push('scripts')
<script>
$(function () {

    // ── Select2 inicialización ─────────────────────────────────────────────
    $('#ecCliente').select2({
        placeholder: '— Escriba para buscar —',
        allowClear: true,
        minimumInputLength: 1,
        dropdownCssClass: 'ec-select2-drop',
        language: {
            inputTooShort:  function () { return 'Escriba al menos 1 caracter para buscar'; },
            noResults:      function () { return 'No se encontraron resultados'; },
            searching:      function () { return 'Buscando...'; },
            errorLoading:   function () { return 'Error al cargar resultados'; }
        },
        ajax: {
            url: '/estado_cuenta/vendedor/clientes',
            dataType: 'json',
            delay: 300,
            data: function (params) { return { search: params.term }; },
            processResults: function (data) { return { results: data.results }; },
            cache: true
        }
    });

    var dtLang = {
        processing:  '<div class="spinner-border spinner-border-sm text-warning" role="status"></div>',
        emptyTable:  'No hay registros.',
        zeroRecords: 'No se encontraron registros.',
        search:      'Buscar:', lengthMenu: 'Mostrar _MENU_ registros',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        paginate: { first:'«', last:'»', next:'›', previous:'‹' }
    };

    // ── DataTable Saldos ───────────────────────────────────────────────────
    window.ecDt = $('#ecTable').DataTable({
        serverSide: false, autoWidth: false, language: dtLang,
        columns: [
            { data: 'codigoPago',    title: '#',            width: '50px' },
            { data: 'codigoFactura', title: 'Factura' },
            { data: 'fechaFactura',  title: 'Fecha Emisión' },
            { data: 'cargo',         title: 'Cargo',         render: fmtMoneda },
            { data: 'abonosCargo',   title: 'Abonos',        render: fmtMoneda },
            { data: 'notasCredito',  title: 'Notas Cred.',   render: fmtMoneda },
            { data: 'notasDebito',   title: 'Notas Déb.',    render: fmtMoneda },
            { data: 'isv',           title: 'Retención ISV', render: fmtMoneda },
            { data: 'saldoBadge',    title: 'Saldo',         orderable: false },
            { data: 'estadoBadge',   title: 'Estado',        orderable: false },
            { data: 'acciones',      title: 'Acciones',      orderable: false, searchable: false }
        ],
        order: [[0, 'desc']], pageLength: 15
    });

    // ── DataTable Movimientos ──────────────────────────────────────────────
    window.ecDtMov = $('#ecTableMovimientos').DataTable({
        serverSide: false, autoWidth: false, language: dtLang,
        columns: [
            { data: 'codigoMovimiento', title: '#',               width: '50px' },
            { data: 'codigoPago',       title: 'Cod. Pago',       width: '80px' },
            { data: 'correlativo',      title: 'Correlativo' },
            { data: 'monto',            title: 'Monto',           className: 'text-right' },
            { data: 'tipo_movimiento',  title: 'Tipo Movimiento' },
            { data: 'comentario',       title: 'Comentario' },
            { data: 'userRegistro',     title: 'Usuario' },
            { data: 'fechaRegistro',    title: 'Fecha Registro' }
        ],
        order: [[0, 'desc']], pageLength: 15
    });

    // ── DataTable Abonos ───────────────────────────────────────────────────
    window.ecDtAbonos = $('#ecTableAbonos').DataTable({
        serverSide: false, autoWidth: false, language: dtLang,
        columns: [
            { data: 'codigoAbono',     title: '#',           width: '50px' },
            { data: 'codigoPago',      title: 'Cod. Pago',   width: '80px' },
            { data: 'correlativo',     title: 'Correlativo' },
            { data: 'monto',           title: 'Monto',           className: 'text-right' },
            { data: 'comentarioabono', title: 'Comentario' },
            { data: 'userRegistro',    title: 'Usuario' },
            { data: 'fechaDeposito',   title: 'Fecha Depósito' },
            { data: 'fechaRegistro',   title: 'Fecha Registro' }
        ],
        order: [[0, 'desc']], pageLength: 15
    });
});

// ── Formatear moneda ───────────────────────────────────────────────────────
function fmtMoneda(data) {
    if (data === null || data === undefined) return 'L. 0.00';
    return 'L. ' + parseFloat(data).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// ── Cargar datos del cliente (los 3 tabs) ─────────────────────────────────
function ecCargarDatos() {
    var clienteId  = $('#ecCliente').val();
    var clienteTxt = $('#ecCliente option:selected').text();

    if (!clienteId) {
        Swal.fire({ icon: 'warning', title: 'Seleccione un cliente', timer: 1800, showConfirmButton: false });
        return;
    }

    $('#ecEmptyState').hide();
    $('#ecTabsContainer').show();
    $('#ecStats').removeClass('d-none');
    $('#ecBtnPdf').removeClass('d-none');
    $('#ecClienteNombre, #ecMovClienteNombre, #ecAbonosClienteNombre').text(clienteTxt);
    window._ecClienteId = clienteId;

    // --- Saldos ---
    axios.get('/estado_cuenta/vendedor/listar/' + clienteId)
        .then(function (response) {
            var data = (response.data && response.data.data) ? response.data.data : [];
            window.ecDt.clear().rows.add(data).draw();
            $('#ecBadgeSaldos').text(data.length);

            var totalSaldo = data.reduce(function (s, r) { return s + parseFloat(r.saldo       || 0); }, 0);
            var totalCargo = data.reduce(function (s, r) { return s + parseFloat(r.cargo       || 0); }, 0);
            var totalAbono = data.reduce(function (s, r) { return s + parseFloat(r.abonosCargo || 0); }, 0);
            $('#ecStatFacturas').text(data.length);
            $('#ecStatCargo').text('L. ' + totalCargo.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#ecStatSaldo').text('L. ' + totalSaldo.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#ecStatAbonado').text('L. ' + totalAbono.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        })
        .catch(function () {
            Swal.fire({ icon: 'error', title: 'Error al cargar saldos', timer: 2000, showConfirmButton: false });
        });

    // --- Movimientos ---
    axios.get('/estado_cuenta/vendedor/movimientos/' + clienteId)
        .then(function (response) {
            var data = (response.data && response.data.data) ? response.data.data : [];
            window.ecDtMov.clear().rows.add(data).draw();
            $('#ecBadgeMovimientos').text(data.length);
        })
        .catch(function () { /* silencioso */ });

    // --- Abonos ---
    axios.get('/estado_cuenta/vendedor/abonos/' + clienteId)
        .then(function (response) {
            var data = (response.data && response.data.data) ? response.data.data : [];
            window.ecDtAbonos.clear().rows.add(data).draw();
            $('#ecBadgeAbonos').text(data.length);
        })
        .catch(function () { /* silencioso */ });
}

// ── PDF ───────────────────────────────────────────────────────────────────
function ecVerPdf() {
    var clienteId = $('#ecCliente').val();
    if (!clienteId) return;
    window.open('/estado_cuenta/vendedor/pdf/' + clienteId, '_blank');
}

// ── Exportar Excel Movimientos ────────────────────────────────────────────
function ecExportarMovimientos() {
    if (!window._ecClienteId) { Swal.fire({ icon: 'warning', title: 'Seleccione un cliente primero', timer: 1800, showConfirmButton: false }); return; }
    window.location.href = '/estado_cuenta/vendedor/exportar/movimientos/' + window._ecClienteId;
}

// ── Exportar Excel Abonos ─────────────────────────────────────────────────
function ecExportarAbonos() {
    if (!window._ecClienteId) { Swal.fire({ icon: 'warning', title: 'Seleccione un cliente primero', timer: 1800, showConfirmButton: false }); return; }
    window.location.href = '/estado_cuenta/vendedor/exportar/abonos/' + window._ecClienteId;
}
</script>
@endpush
</div>
