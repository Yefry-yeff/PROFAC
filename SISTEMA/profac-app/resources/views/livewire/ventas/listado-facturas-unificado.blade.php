<div>
    @push('styles')
    <style>
    /* -- Variables PROFAC -- */
    :root {
        --pf-grad:     linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
        --pf-orange:   #e67e22;
        --pf-radius:   8px;
        --pf-shadow:   0 2px 8px rgba(0,0,0,.10);
    }
    /* -- Card facturas -- */
    .fact-card {
        border: 1px solid #e8d5bf;
        border-radius: var(--pf-radius);
        box-shadow: var(--pf-shadow);
        background: #fff;
        overflow: visible;
    }
    .fact-card-header {
        background: var(--pf-grad);
        padding: 12px 20px;
        border-radius: var(--pf-radius) var(--pf-radius) 0 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }
    .fact-card-header h5 {
        margin: 0; color: #fff;
        font-size: .85rem; font-weight: 700;
        letter-spacing: .05em; text-transform: uppercase;
        display: flex; align-items: center; gap: 8px;
    }
    .fact-card-body { padding: 12px 16px; }
    /* -- Botón filtros -- */
    .btn-fact-filter {
        background: rgba(255,255,255,.18) !important;
        color: #fff !important;
        border: 1.5px solid rgba(255,255,255,.5) !important;
        border-radius: 5px !important;
        font-weight: 600 !important;
        font-size: .78rem; padding: 5px 14px;
        transition: background .18s; white-space: nowrap; cursor: pointer;
    }
    .btn-fact-filter:hover { background: rgba(255,255,255,.30) !important; }
    /* -- Barra de filtros activos -- */
    .filtros-bar {
        padding: 8px 16px;
        background: #fdfaf5;
        border-bottom: 1px solid #e8d5bf;
        display: flex; flex-wrap: wrap;
        align-items: center; gap: 6px;
        font-size: .78rem;
    }
    .filtro-badge {
        display: inline-flex; align-items: center; gap: 5px;
        background: #fff8ee; border: 1px solid #f2d49a;
        border-radius: 12px; padding: 2px 10px;
        font-size: .75rem; color: #7d3f00;
    }
    .filtro-badge .filtro-remove {
        cursor: pointer; color: #c0622a;
        font-weight: 700; margin-left: 3px;
    }
    .filtro-badge .filtro-remove:hover { color: #e74c3c; }
    /* -- Tabla compacta -- */
    #tbl_listar_compras { width: 100% !important; }
    #tbl_listar_compras thead th {
        background: #fdf4e7; color: #7d3f00;
        font-size: .70rem; font-weight: 700;
        letter-spacing: .04em; text-transform: uppercase;
        border-bottom: 2px solid #f2d49a;
        white-space: nowrap; padding: 7px 8px; vertical-align: middle;
    }
    #tbl_listar_compras tbody td {
        font-size: .80rem; vertical-align: middle; padding: 6px 8px;
    }
    #tbl_listar_compras tbody tr:hover { background: #fffcf5; }
    .factura-link {
        color: #e67e22;
        font-weight: 600;
        text-decoration: underline;
        cursor: pointer;
    }
    .factura-link:hover { color: #c76305; }
    .td-cai-trunc {
        display: inline-block;
        max-width: 90px; overflow: hidden;
        text-overflow: ellipsis; white-space: nowrap;
        vertical-align: bottom;
    }
    /* -- Modal filtros -- */
    .modal-header-fact {
        background: var(--pf-grad); color: #fff;
        border-radius: var(--pf-radius) var(--pf-radius) 0 0;
        padding: 14px 20px;
    }
    .modal-header-fact .modal-title { color: #fff; font-size: .95rem; font-weight: 700; }
    .modal-header-fact .close { color: #fff; opacity: .8; text-shadow: none; font-size: 1.4rem; }
    .modal-header-fact .close:hover { opacity: 1; }
    .modal-section-label {
        font-size: .68rem; font-weight: 700;
        letter-spacing: .07em; text-transform: uppercase;
        color: #e67e22; border-bottom: 2px solid #fdebd0;
        padding-bottom: 5px; margin-bottom: 14px; margin-top: 6px;
        display: flex; align-items: center; gap: 5px;
    }
    .modal-section-label i { color: #e67e22; }
    #modalFiltros .modal-body { background: #fdfaf6; padding: 18px 20px 8px; }
    #modalFiltros .modal-footer { background: #f8f4ef; border-top: 1px solid #ead9c8; padding: 10px 20px; }
    #modalFiltros .form-group label { font-size: .78rem; font-weight: 600; color: #555; margin-bottom: 3px; }
    #modalFiltros .form-control, #modalFiltros .form-control-sm {
        border-color: #ddd; border-radius: 5px; font-size: .82rem;
    }
    #modalFiltros .form-control:focus {
        border-color: #e67e22;
        box-shadow: 0 0 0 .15rem rgba(230,126,34,.18);
    }
    #modalDetalleFactura .modal-body {
        background: #fffdf9;
        padding: 16px 18px;
    }
    #modalDetalleFactura .modal-dialog.modal-dialog-centered {
        min-height: calc(100% - 7rem);
        align-items: flex-start;
        margin-top: 68px;
    }
    #modalDetalleFactura .table thead th {
        background: #fdf4e7;
        color: #7d3f00;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        border-bottom: 2px solid #f2d49a;
    }
    #modalDetalleFactura .table tbody td {
        font-size: .82rem;
        vertical-align: middle;
    }
    .detalle-tools {
        display: flex;
        gap: 8px;
        align-items: center;
        justify-content: flex-end;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }
    .detalle-tools .form-control {
        max-width: 260px;
        height: 32px;
        font-size: .80rem;
    }
    .modal-filter-grid {
        background: #fff; border: 1px solid #ead9c8;
        border-radius: 7px; padding: 14px 16px 6px;
        margin-bottom: 14px;
    }
    /* -- Tipo buttons en modal -- */
    .tipo-filter-btn {
        font-size: .78rem; font-weight: 600;
        padding: 5px 16px; border-radius: 20px !important;
        border: 1.5px solid #dee2e6;
        background: #fff; color: #555;
        transition: all .15s; cursor: pointer; outline: none;
    }
    .tipo-filter-btn.active {
        background: var(--pf-grad) !important;
        color: #fff !important;
        border-color: transparent !important;
        box-shadow: 0 2px 6px rgba(230,126,34,.3) !important;
    }
    .tipo-filter-btn:hover:not(.active) {
        background: #fff8f0; border-color: #e67e22; color: #c0622a;
    }
    .date-input-icon { position: relative; }
    .date-input-icon i {
        position: absolute; left: 9px; top: 50%; transform: translateY(-50%);
        color: #aaa; font-size: .78rem; pointer-events: none;
    }
    .date-input-icon input { padding-left: 28px; }
    /* -- Select2 sobre el modal -- */
    .select2-container--open {
        z-index: 99999 !important;
    }
    @media (max-width: 575px) {
        .modal-dialog.modal-lg { max-width: calc(100vw - 1rem); }
        .fact-card-body { padding: 8px; }
        #modalDetalleFactura .modal-dialog.modal-dialog-centered { margin-top: 56px; }
    }
    </style>
    @endpush

    {{-- Loading Overlay (oculto hasta que se apliquen filtros) --}}
    <div id="tbl_loading_overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.78); z-index:9000; text-align:center; padding-top:18%; display:none;">
        <i class="fa fa-spinner fa-spin fa-3x" style="color:#1ab394;"></i>
        <p class="mt-3" style="color:#555; font-size:1rem;">Cargando datos...</p>
    </div>

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2><i class="fa fa-file-text mr-2" style="color:#e67e22"></i>Listado de Facturas</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong id="breadcrumbTipo">{{ $nombreTipo }}</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        <div class="row">
            <div class="col-lg-12">
                <div class="fact-card">

                    <div class="fact-card-header">
                        <h5><i class="fa fa-file-text"></i> Listado de Facturas</h5>
                        <div class="d-flex" style="gap:8px;">
                            @if($tipoVenta === 'estatal')
                            <button type="button" class="btn-fact-filter" onclick="exportarFacturasDetallePdf()">
                                <i class="fa fa-file-pdf-o mr-1"></i>PDF detalle
                            </button>
                            @endif
                            <button type="button" class="btn-fact-filter" onclick="exportarFacturasExcel()">
                                <i class="fa fa-file-excel-o mr-1"></i>Excel
                            </button>
                            <button type="button" class="btn-fact-filter" data-toggle="modal" data-target="#modalFiltros">
                                <i class="fa fa-filter mr-1"></i>Filtros
                            </button>
                        </div>
                    </div>

                    {{-- Barra de filtros activos --}}
                    <div class="filtros-bar" id="filtrosBar" style="display:none;"></div>

                    {{-- Placeholder: visible hasta que se apliquen filtros --}}
                    <div id="fact-placeholder" class="text-center py-5" style="color:#aaa">
                        <i class="fa fa-filter" style="font-size:2.5rem; color:#e67e22; opacity:.45"></i>
                        <p class="mt-3 mb-0" style="font-size:1rem; font-weight:600">Aplique filtros para cargar los resultados</p>
                        <p class="small">Haga clic en <strong>Filtros</strong> para definir los criterios de búsqueda.</p>
                    </div>

                    <div class="fact-card-body" id="fact-table-wrapper" style="display:none">
                        <table id="tbl_listar_compras" class="table table-bordered table-hover">
                            <thead>
                                <tr></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- ==================== Modal Detalle Productos/Escala ==================== -->
    <div class="modal fade" id="modalDetalleFactura" tabindex="-1" role="dialog" aria-labelledby="tituloModalDetalleFactura" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-fact">
                    <h5 class="modal-title" id="tituloModalDetalleFactura">
                        <i class="fa fa-list-alt mr-2"></i>Detalle de productos por escala
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap:8px">
                        <small class="text-muted">Factura: <strong id="detalleFacturaNumero">-</strong></small>
                    </div>

                    <div id="detalleFacturaLoading" class="text-center py-3" style="display:none">
                        <i class="fa fa-spinner fa-spin"></i> Cargando detalle...
                    </div>

                    <div class="detalle-tools">
                        <input type="text" id="detalleFacturaBuscar" class="form-control form-control-sm" placeholder="Buscar producto o escala...">
                        <button type="button" id="btnDetalleFacturaExcel" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel-o mr-1"></i>Excel
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0" id="tblDetalleFacturaProductos">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-right">Cantidad</th>
                                    <th>Escala</th>
                                    <th class="text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Seleccione una factura para ver el detalle.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== Modal de Filtros ==================== -->
    <div class="modal fade" id="modalFiltros" tabindex="-1" role="dialog" aria-labelledby="tituloModalFiltros" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-fact">
                    <h5 class="modal-title" id="tituloModalFiltros">
                        <i class="fa fa-filter mr-2"></i>Filtros de Búsqueda
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-2">

                    {{-- ── Tipo de factura ── --}}
                    <p class="modal-section-label"><i class="fa fa-tag"></i>Tipo de factura</p>
                    <div class="modal-filter-grid mb-3">
                        <div class="d-flex flex-wrap" style="gap:8px">
                            <button type="button" class="tipo-filter-btn {{ $tipoVenta == 'todos' ? 'active' : '' }}" data-tipo="todos">
                                <i class="fa fa-circle mr-1" style="font-size:.6rem"></i>Todos
                            </button>
                            <button type="button" class="tipo-filter-btn {{ $tipoVenta == 'estatal'     ? 'active' : '' }}" data-tipo="estatal">
                                <i class="fa fa-circle mr-1" style="font-size:.6rem"></i>Clientes A
                            </button>
                            <button type="button" class="tipo-filter-btn {{ $tipoVenta == 'corporativo' ? 'active' : '' }}" data-tipo="corporativo">
                                <i class="fa fa-circle mr-1" style="font-size:.6rem"></i>Clientes B
                            </button>
                            <button type="button" class="tipo-filter-btn {{ $tipoVenta == 'exonerado'   ? 'active' : '' }}" data-tipo="exonerado">
                                <i class="fa fa-circle mr-1" style="font-size:.6rem"></i>Exoneradas
                            </button>
                        </div>
                    </div>

                    {{-- ── Rango de fechas ── --}}
                    <p class="modal-section-label"><i class="fa fa-calendar"></i>Rango de fechas</p>
                    <div class="modal-filter-grid">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <div class="date-input-icon">
                                        <i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="filtroDesde">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <div class="date-input-icon">
                                        <i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="filtroHasta">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Criterios de búsqueda ── --}}
                    <p class="modal-section-label"><i class="fa fa-search"></i>Criterios de búsqueda</p>
                    <div class="modal-filter-grid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>N° Factura</label>
                                    <input type="text" class="form-control form-control-sm" id="filtroCai"
                                           placeholder="Ej: 000-001-01-00041992 o solo el número 41992">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Cliente</label>
                                    <select id="filtroCliente" class="form-control" style="width:100%">
                                        <option></option>
                                    </select>
                                </div>
                            </div>
                            @if(!$esVendedor)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vendedor</label>
                                    <select id="filtroVendedor" class="form-control" style="width:100%">
                                        <option></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Facturador</label>
                                    <select id="filtroFacturador" class="form-control" style="width:100%">
                                        <option></option>
                                    </select>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="limpiarFiltros()">
                        <i class="fa fa-eraser mr-1"></i>Limpiar filtros
                    </button>
                    <button type="button" class="btn btn-sm" onclick="aplicarFiltros()"
                        style="background:linear-gradient(135deg,#f39c12,#e05a00);color:#fff;border:none;font-weight:600;padding:6px 20px;border-radius:5px">
                        <i class="fa fa-search mr-1"></i>Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // ── Configuración por tipo ───────────────────────────────────────
            var configPorTipo = {
                corporativo: {
                    listar: '/lista/facturas/corporativo',
                    listarVendedor: '/lista/facturas/corporativo/vendedor',
                    anular: '/factura/corporativo/anular',
                    excelTitle: 'Facturas_corporativo',
                    label: 'Clientes B', badgeClass: 'badge-primary'
                },
                estatal: {
                    listar: '/lista/facturas/estatal',
                    listarVendedor: '/listado/ventas/estatal/vendedor',
                    anular: '/factura/estatal/anular',
                    excelTitle: 'Facturas_estatal',
                    label: 'Clientes A', badgeClass: 'badge-warning'
                },
                exonerado: {
                    listar: '/exonerado/listas/facturas',
                    listarVendedor: '/exonerado/listas/facturas',
                    anular: '/factura/corporativo/anular',
                    excelTitle: 'Facturas_exonerado',
                    label: 'Exoneradas', badgeClass: 'badge-success'
                },
                todos: {
                    listar: '/lista/facturas/todos',
                    listarVendedor: '/lista/facturas/todos',
                    anular: '/factura/corporativo/anular',
                    excelTitle: 'Todas_Facturas',
                    label: 'Todos', badgeClass: 'badge-secondary'
                }
            };

            var tipoVenta  = @json($tipoVenta);
            var esVendedor = @json($esVendedor);
            var config     = configPorTipo[tipoVenta] || configPorTipo['corporativo'];
            var usuarioDescargaExcel = @json(optional(Auth::user())->name ?? 'Sistema');

            var urlHistoryAdmin    = { corporativo: '/facturas/corporativo', estatal: '/facturas/estatal', exonerado: '/exonerado/ventas/lista', todos: '/facturas/todos' };
            var urlHistoryVendedor = { corporativo: '/facturas/corporativo/vendedor', estatal: '/ventas/estatal/vendedor', exonerado: '/exonerado/ventas/lista', todos: '/facturas/todos' };

            // ── Filtros activos ──────────────────────────────────────────────
            var filtros = { tipo: tipoVenta, cai: '', cliente: '', vendedor: '', facturador: '', desde: '', hasta: '' };

            // ── Construcción dinámica de columnas ────────────────────────────
            var tipoVentaBadge = { 1: { label: 'Cliente B', badgeClass: 'badge-primary' }, 2: { label: 'Cliente A', badgeClass: 'badge-warning' }, 3: { label: 'Exonerada', badgeClass: 'badge-success' } };

            function buildColumnas(tipo, esVend) {
                var cols = [];
                // Badge: dinámico por fila cuando tipo=todos, estático en otro caso
                if (tipo === 'todos') {
                    cols.push({
                        data: 'tipo_venta_id', orderable: true, searchable: false,
                        render: function(d, type, row) {
                            if (type !== 'display') return d;
                            var t = tipoVentaBadge[d] || { label: row.tipo_label || d || '-', badgeClass: 'badge-secondary' };
                            return '<span class="badge ' + t.badgeClass + '">' + t.label + '</span>';
                        }
                    });
                } else {
                    cols.push({
                        data: null, orderable: false, searchable: false,
                        render: function(d, type, row) {
                            var cfg = configPorTipo[tipo] || {};
                            return '<span class="badge ' + (cfg.badgeClass || 'badge-secondary') + '">' + (cfg.label || tipo) + '</span>';
                        }
                    });
                }
                if (esVend) cols.push({ data: 'id', searchable: true });
                if (!esVend) {
                    cols.push({
                        data: 'cai', searchable: true,
                        render: function(d, type, row) {
                            if (type !== 'display') return d;
                            var label = d || '';
                            if (!row || !row.id) return label;
                            return '<a href="javascript:void(0)" class="factura-link" data-factura-id="' + row.id + '" data-factura-cai="' + label + '">' + label + '</a>';
                        }
                    });
                }
                cols.push({ data: 'fecha_emision', searchable: false });
                cols.push({ data: 'nombre', searchable: true });
                cols.push({ data: 'descripcion', searchable: true });
                cols.push({ data: 'gravado',   searchable: false });
                cols.push({ data: 'exento',    searchable: false });
                cols.push({ data: 'exonerado', searchable: false });
                cols.push({ data: 'sub_total', searchable: false });
                cols.push({ data: 'isv',       searchable: false });
                cols.push({ data: 'total',     searchable: false });
                cols.push({ data: 'estado_cobro', searchable: false, orderable: false });
                if (!esVend) {
                    cols.push({ data: 'vendedor', searchable: true });
                } else {
                    cols.push({ data: 'creado_por', searchable: true });
                }
                cols.push({ data: 'opciones', orderable: false, searchable: false });
                return cols;
            }

            function buildTheadHtml(tipo, esVend) {
                var headers = ['Tipo'];
                if (esVend) headers.push('Cód.');
                if (!esVend) headers.push('N° Factura');
                headers.push('Fecha', 'Cliente', 'Tipo Pago', 'Gravado', 'Exento', 'Exonerado', 'Subtotal', 'ISV', 'Total', 'Estado', 'Vendedor', 'Opciones');
                var html = '<tr>';
                headers.forEach(function(h) { html += '<th>' + h + '</th>'; });
                html += '</tr>';
                return html;
            }

            // ── Inicializar DataTable ────────────────────────────────────────
            function initDataTable(tipo, esVend) {
                var currentConfig = configPorTipo[tipo] || configPorTipo['corporativo'];
                var columnas = buildColumnas(tipo, esVend);
                var ajaxUrl  = esVend ? currentConfig.listarVendedor : currentConfig.listar;
                $('#tbl_listar_compras thead').html(buildTheadHtml(tipo, esVend));
                return $('#tbl_listar_compras').DataTable({
                    "language": { "url": "/js/plugins/dataTables/i18n/Spanish.json" },
                    "order": [[2, 'desc']],
                    pageLength: 10,
                    "processing": true,
                    "serverSide": true,
                    responsive: true,
                    autoWidth: false,
                    scrollX: false,
                    dom: 'lTfgitp',
                    buttons: [],
                    "ajax": {
                        url: ajaxUrl,
                        data: function(d) {
                            d.filtroCai        = filtros.cai;
                            d.filtroCliente    = filtros.cliente;
                            d.filtroVendedor   = filtros.vendedor;
                            d.filtroFacturador = filtros.facturador;
                            d.filtroDesde      = filtros.desde;
                            d.filtroHasta      = filtros.hasta;
                        }
                    },
                    "columns": columnas,
                    "initComplete": function() {
                        document.getElementById('tbl_loading_overlay').style.display = 'none';
                    }
                });
            }

            // ── Exportar Excel con formato ────────────────────────────────────
            function _fuSetCookie(n, v, s) {
                var e = ''; if (typeof s === 'number') { var d = new Date(); d.setTime(d.getTime() + s * 1000); e = '; expires=' + d.toUTCString(); }
                document.cookie = n + '=' + encodeURIComponent(v) + e + '; path=/';
            }
            function _fuGetCookie(name) {
                var p = name + '=', parts = document.cookie ? document.cookie.split(';') : [];
                for (var i = 0; i < parts.length; i++) { var c = parts[i].trim(); if (c.indexOf(p) === 0) return decodeURIComponent(c.substring(p.length)); }
                return '';
            }

            function exportarFacturasExcel() {
                var tok           = $('meta[name="csrf-token"]').attr('content');
                var downloadToken = 'fuexl_' + Date.now() + '_' + Math.floor(Math.random() * 1000000);
                var cookieName    = 'fu_excel_download_token';
                _fuSetCookie(cookieName, '', -1);

                var form = $('<form method="POST"></form>').attr('action', '/lista/facturas/exportar-excel');
                var fields = {
                    _token: tok, download_token: downloadToken,
                    tipo:          filtros.tipo       || tipoVenta,
                    filtroCai:     filtros.cai        || '',
                    filtroCliente: filtros.cliente    || '',
                    filtroVendedor:filtros.vendedor   || '',
                    filtroDesde:   filtros.desde      || '',
                    filtroHasta:   filtros.hasta      || ''
                };
                $.each(fields, function(k, v) { form.append($('<input type="hidden">').attr('name', k).val(v)); });
                $('body').append(form);

                Swal.fire({
                    title: 'Generando Excel',
                    html: 'Preparando reporte...<br><small>Este proceso puede tardar varios momentos.</small>',
                    allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false,
                    didOpen: function() { Swal.showLoading(); }
                });

                var startedAt = Date.now();
                var timer = setInterval(function() {
                    if (_fuGetCookie(cookieName) === downloadToken) {
                        clearInterval(timer); _fuSetCookie(cookieName, '', -1); Swal.close();
                    } else if (Date.now() - startedAt > 10 * 60 * 1000) {
                        clearInterval(timer);
                        Swal.fire({ icon: 'warning', title: 'Demora en descarga', text: 'La generación sigue en proceso. Intenta nuevamente.' });
                    }
                }, 400);

                form.trigger('submit');
                setTimeout(function() { form.remove(); }, 1500);
            }

            function exportarFacturasDetallePdf() {
                var form = $('<form method="POST" target="_blank"></form>')
                    .attr('action', '/lista/facturas/estatal/exportar-pdf-detalle');
                var fields = {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    tipo: filtros.tipo || tipoVenta,
                    filtroCai: filtros.cai || '',
                    filtroCliente: filtros.cliente || '',
                    filtroVendedor: filtros.vendedor || '',
                    filtroFacturador: filtros.facturador || '',
                    filtroDesde: filtros.desde || '',
                    filtroHasta: filtros.hasta || ''
                };
                $.each(fields, function(key, value) {
                    form.append($('<input type="hidden">').attr('name', key).val(value));
                });
                $('body').append(form);
                form.trigger('submit');
                setTimeout(function() { form.remove(); }, 1500);
            }

            // ── Aplicar / limpiar filtros ────────────────────────────────────
            function aplicarFiltros() {
                var activeBtn = document.querySelector('.tipo-filter-btn.active');
                var nuevoTipo = activeBtn ? activeBtn.dataset.tipo : tipoVenta;

                filtros.tipo       = nuevoTipo;
                filtros.cai        = document.getElementById('filtroCai').value.trim();
                filtros.cliente    = $('#filtroCliente').val() || '';
                filtros.vendedor   = $('#filtroVendedor').val() || '';
                filtros.facturador = $('#filtroFacturador').val() || '';
                filtros.desde      = document.getElementById('filtroDesde').value || '';
                filtros.hasta      = document.getElementById('filtroHasta').value || '';

                $('#modalFiltros').modal('hide');
                document.getElementById('fact-placeholder').style.display = 'none';
                document.getElementById('fact-table-wrapper').style.display = '';
                document.getElementById('tbl_loading_overlay').style.display = '';

                tipoVenta = nuevoTipo;
                config    = configPorTipo[nuevoTipo] || configPorTipo['corporativo'];

                var el = document.getElementById('breadcrumbTipo');
                if (el) el.textContent = config.label;

                var historyUrls = esVendedor ? urlHistoryVendedor : urlHistoryAdmin;
                if (historyUrls[nuevoTipo]) history.pushState({ tipo: nuevoTipo }, '', historyUrls[nuevoTipo]);

                if ($.fn.DataTable.isDataTable('#tbl_listar_compras')) {
                    $('#tbl_listar_compras').DataTable().destroy();
                    $('#tbl_listar_compras tbody').empty();
                }
                initDataTable(nuevoTipo, esVendedor);
                actualizarFiltrosBar();
            }

            function limpiarFiltros() {
                document.getElementById('filtroCai').value = '';
                document.getElementById('filtroDesde').value = '';
                document.getElementById('filtroHasta').value = '';
                $('#filtroCliente').val(null).trigger('change');
                $('#filtroVendedor').val(null).trigger('change');
                $('#filtroFacturador').val(null).trigger('change');
                document.querySelectorAll('.tipo-filter-btn').forEach(function(b) { b.classList.remove('active'); });
                var def = document.querySelector('.tipo-filter-btn[data-tipo="' + tipoVenta + '"]');
                if (def) def.classList.add('active');
            }

            // ── Barra de filtros activos ─────────────────────────────────────
            function actualizarFiltrosBar() {
                var bar = document.getElementById('filtrosBar');
                var cfg = configPorTipo[filtros.tipo] || {};
                var parts = [];
                parts.push('<span class="badge ' + (cfg.badgeClass || 'badge-secondary') + '" style="font-size:.75rem;padding:4px 10px">' +
                           '<i class="fa fa-tag mr-1"></i>' + (cfg.label || filtros.tipo) + '</span>');
                var labels = { cai: 'N° Factura', cliente: 'Cliente', vendedor: 'Vendedor', facturador: 'Facturador', desde: 'Desde', hasta: 'Hasta' };
                Object.keys(labels).forEach(function(key) {
                    if (filtros[key]) {
                        parts.push('<span class="filtro-badge"><i class="fa fa-filter" style="font-size:.68rem"></i>' +
                                   labels[key] + ': <strong>' + filtros[key] + '</strong>' +
                                   '<span class="filtro-remove" onclick="quitarFiltro(\'' + key + '\')" title="Quitar">✕</span></span>');
                    }
                });
                if (parts.length > 0) {
                    bar.innerHTML = '<small class="text-muted mr-2"><i class="fa fa-info-circle mr-1"></i>Mostrando:</small>' + parts.join('');
                    bar.style.display = 'flex';
                } else {
                    bar.style.display = 'none';
                }
            }

            function quitarFiltro(key) {
                filtros[key] = '';
                var select2Keys = ['cliente', 'vendedor', 'facturador'];
                var dateKeys    = ['desde', 'hasta'];
                var ids = { cai: 'filtroCai', cliente: 'filtroCliente', vendedor: 'filtroVendedor', facturador: 'filtroFacturador', desde: 'filtroDesde', hasta: 'filtroHasta' };
                if (ids[key]) {
                    if (select2Keys.indexOf(key) >= 0) {
                        $('#' + ids[key]).val(null).trigger('change');
                    } else {
                        document.getElementById(ids[key]).value = '';
                    }
                }
                if ($.fn.DataTable.isDataTable('#tbl_listar_compras')) {
                    $('#tbl_listar_compras').DataTable().ajax.reload();
                }
                actualizarFiltrosBar();
            }

            // ── Anular factura ───────────────────────────────────────────────
            function anularVentaConfirmar(idFactura) {
                Swal.fire({
                    title: '¿Está seguro de anular esta factura?',
                    html: '<p>Una vez anulada la factura el producto será devuelto al inventario.</p>' +
                          '<textarea rows="4" placeholder="Es obligatorio describir el motivo." required id="comentario" class="form-group form-control"></textarea>',
                    showDenyButton: true,
                    showCancelButton: false,
                    confirmButtonText: 'Sí, Anular Factura',
                    denyButtonText: 'Cancelar',
                    confirmButtonColor: '#19A689',
                    denyButtonColor: '#676A6C',
                }).then((result) => {
                    let motivo = document.getElementById("comentario").value;
                    if (result.isConfirmed && motivo) { anularVenta(idFactura, motivo); }
                });
            }

            function anularVenta(idFactura, motivo) {
                axios.post(config.anular, { 'idFactura': idFactura, 'motivo': motivo })
                    .then(response => {
                        let data = response.data;
                        Swal.fire({ icon: data.icon, title: data.title, html: data.text });
                        $('#tbl_listar_compras').DataTable().ajax.reload();
                    })
                    .catch(() => {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Ha ocurrido un error al anular la factura.' });
                    });
            }

            // ── Inicialización ───────────────────────────────────────────────
            $(document).ready(function() {
                var detalleFacturaTable = null;
                var detalleFacturaActual = '';

                function fechaHoraDescarga() {
                    var now = new Date();
                    return now.toLocaleDateString('es-HN', { day: '2-digit', month: '2-digit', year: 'numeric' }) +
                        ' ' + now.toLocaleTimeString('es-HN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                }

                function normalizarNumeroExcel(valor) {
                    if (valor === null || valor === undefined) return 0;
                    var limpio = String(valor)
                        .replace(/L\.|HNL|,/gi, '')
                        .replace(/\s+/g, '')
                        .trim();
                    var n = parseFloat(limpio);
                    return isNaN(n) ? 0 : n;
                }

                function formatoNumero(n) {
                    var num = Number(n || 0);
                    return num.toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                function renderDetalleFactura(productos) {
                    var rows = [];
                    (productos || []).forEach(function(item) {
                        rows.push({
                            producto: item.producto || '-',
                            cantidad: Number(item.cantidad || 0),
                            escala: item.escala || 'Sin escala',
                            monto: Number(item.monto || 0)
                        });
                    });

                    if (!detalleFacturaTable) {
                        detalleFacturaTable = $('#tblDetalleFacturaProductos').DataTable({
                            data: rows,
                            paging: true,
                            pageLength: 10,
                            ordering: true,
                            info: true,
                            searching: true,
                            dom: 'Brtip',
                            language: { "url": "/js/plugins/dataTables/i18n/Spanish.json" },
                            buttons: [{
                                extend: 'excelHtml5',
                                title: function() {
                                    return '';
                                },
                                filename: function() {
                                    var num = (detalleFacturaActual || 'factura').replace(/[^0-9A-Za-z_-]/g, '_');
                                    return 'Distribuciones_Valencia_Detalle_' + num;
                                },
                                messageTop: function() {
                                    return '';
                                },
                                exportOptions: {
                                    format: {
                                        body: function(data, row, column) {
                                            if (column === 1 || column === 3) {
                                                return normalizarNumeroExcel(data);
                                            }
                                            return $('<div>').html(data).text();
                                        }
                                    }
                                },
                                customize: function(xlsx) {
                                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                    var $sheet = $(sheet);
                                    var styles = xlsx.xl['styles.xml'];
                                    var $styles = $(styles);

                                    function escapeXml(text) {
                                        return String(text || '')
                                            .replace(/&/g, '&amp;')
                                            .replace(/</g, '&lt;')
                                            .replace(/>/g, '&gt;')
                                            .replace(/"/g, '&quot;')
                                            .replace(/'/g, '&apos;');
                                    }

                                    function colFromRef(ref) {
                                        return String(ref || '').replace(/[0-9]/g, '');
                                    }

                                    var sheetData = $sheet.find('sheetData');
                                    var horaDescarga = fechaHoraDescarga();
                                    var encabezado1 = 'Distribuciones Valencia';
                                    var encabezado2 = 'Reporte de productos factura numero: ' + (detalleFacturaActual || '-');
                                    var encabezado3 = 'Hora de descarga: ' + horaDescarga + '  |  Descargado por: ' + (usuarioDescargaExcel || 'Sistema');

                                    // Desplazar filas existentes 4 posiciones para insertar encabezados visibles.
                                    sheetData.find('row').each(function() {
                                        var $row = $(this);
                                        var oldRow = parseInt($row.attr('r') || '0', 10);
                                        var newRow = oldRow + 4;
                                        $row.attr('r', String(newRow));

                                        $row.find('c').each(function() {
                                            var $cell = $(this);
                                            var oldRef = $cell.attr('r') || '';
                                            var col = colFromRef(oldRef);
                                            if (col) {
                                                $cell.attr('r', col + newRow);
                                            }
                                        });
                                    });

                                    // Insertar los 3 headers solicitados y una fila separadora.
                                    var filasHeader = '' +
                                        '<row r="1"><c r="A1" t="inlineStr"><is><t>' + escapeXml(encabezado1) + '</t></is></c></row>' +
                                        '<row r="2"><c r="A2" t="inlineStr"><is><t>' + escapeXml(encabezado2) + '</t></is></c></row>' +
                                        '<row r="3"><c r="A3" t="inlineStr"><is><t>' + escapeXml(encabezado3) + '</t></is></c></row>' +
                                        '<row r="4"><c r="A4" t="inlineStr"><is><t></t></is></c></row>';
                                    sheetData.prepend(filasHeader);

                                    // Extender el rango del worksheet (dimension) para incluir las nuevas filas.
                                    var $dimension = $sheet.find('dimension');
                                    if ($dimension.length) {
                                        var ref = $dimension.attr('ref') || 'A1:D1';
                                        var parts = ref.split(':');
                                        if (parts.length === 2) {
                                            var endCol = colFromRef(parts[1]) || 'D';
                                            var endRow = parseInt(String(parts[1]).replace(/[^0-9]/g, '') || '1', 10) + 4;
                                            $dimension.attr('ref', 'A1:' + endCol + endRow);
                                        }
                                    }

                                    // Crear formato moneda en Lempiras para la columna MONTO.
                                    var $numFmts = $styles.find('numFmts');
                                    if (!$numFmts.length) {
                                        $styles.find('styleSheet').prepend('<numFmts count="0"></numFmts>');
                                        $numFmts = $styles.find('numFmts');
                                    }
                                    var existeNumFmt = $numFmts.find('numFmt[numFmtId="300"]');
                                    if (!existeNumFmt.length) {
                                        $numFmts.append('<numFmt numFmtId="300" formatCode="&quot;L &quot;#,##0.00"/>');
                                        $numFmts.attr('count', parseInt($numFmts.attr('count') || '0', 10) + 1);
                                    }

                                    var $cellXfs = $styles.find('cellXfs');
                                    var xfCount = parseInt($cellXfs.attr('count') || '0', 10);

                                    var estiloTextoEditable = xfCount;
                                    $cellXfs.append('<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyProtection="1"><protection locked="0"/></xf>');
                                    xfCount += 1;

                                    var estiloNumeroEditable = xfCount;
                                    $cellXfs.append('<xf numFmtId="4" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyProtection="1"><protection locked="0"/></xf>');
                                    xfCount += 1;

                                    var estiloMonedaLEditable = xfCount;
                                    $cellXfs.append('<xf numFmtId="300" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyProtection="1"><protection locked="0"/></xf>');
                                    xfCount += 1;

                                    $cellXfs.attr('count', xfCount);

                                    if ($sheet.find('sheetProtection').length === 0) {
                                        $sheet.find('worksheet').append('<sheetProtection sheet="1" objects="1" scenarios="1" selectLockedCells="1" selectUnlockedCells="1"/>');
                                    }

                                    // Solo los headers (filas 1..3) quedan bloqueados; tabla editable desde fila 5.
                                    sheetData.find('row').each(function() {
                                        var $row = $(this);
                                        var rowNum = parseInt($row.attr('r') || '0', 10);
                                        if (rowNum >= 5) {
                                            $row.find('c').each(function() {
                                                var $cell = $(this);
                                                var ref = $cell.attr('r') || '';
                                                var col = colFromRef(ref);

                                                if (rowNum >= 6 && col === 'B') {
                                                    $cell.attr('s', String(estiloNumeroEditable));
                                                } else if (rowNum >= 6 && col === 'D') {
                                                    $cell.attr('s', String(estiloMonedaLEditable));
                                                } else {
                                                    $cell.attr('s', String(estiloTextoEditable));
                                                }
                                            });
                                        }
                                    });
                                },
                                className: 'd-none'
                            }],
                            columns: [
                                { data: 'producto' },
                                { data: 'cantidad', className: 'text-right', render: function(d, t) { return t === 'display' ? formatoNumero(d) : d; } },
                                { data: 'escala' },
                                { data: 'monto', className: 'text-right', render: function(d, t) { return t === 'display' ? ('L. ' + formatoNumero(d)) : d; } }
                            ]
                        });
                    } else {
                        detalleFacturaTable.clear().rows.add(rows).draw();
                    }

                    if (rows.length === 0) {
                        detalleFacturaTable.clear().draw();
                    }

                    $('#detalleFacturaBuscar').val('');
                    detalleFacturaTable.search('').draw();
                }

                function abrirDetalleFactura(idFactura, numeroFactura) {
                    var loading = document.getElementById('detalleFacturaLoading');
                    var numero  = document.getElementById('detalleFacturaNumero');
                    detalleFacturaActual = numeroFactura || ('#' + idFactura);

                    if (numero) numero.textContent = detalleFacturaActual;
                    if (detalleFacturaTable) {
                        detalleFacturaTable.clear().draw();
                    }
                    if (loading) loading.style.display = '';

                    $('#modalDetalleFactura').modal('show');

                    axios.get('/factura/detalle-productos-escala/' + idFactura)
                        .then(function(response) {
                            var data = response.data || {};
                            renderDetalleFactura(data.productos || []);
                        })
                        .catch(function() {
                            if (detalleFacturaTable) {
                                detalleFacturaTable.clear().draw();
                            } else {
                                var tbody = document.querySelector('#tblDetalleFacturaProductos tbody');
                                if (tbody) {
                                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">No fue posible cargar el detalle de la factura.</td></tr>';
                                }
                            }
                        })
                        .finally(function() {
                            if (loading) loading.style.display = 'none';
                        });
                }

                $(document).on('click', '.factura-link', function() {
                    var idFactura = this.getAttribute('data-factura-id');
                    var numeroFac = this.getAttribute('data-factura-cai');
                    if (!idFactura) return;
                    abrirDetalleFactura(idFactura, numeroFac);
                });

                $('#detalleFacturaBuscar').on('keyup', function() {
                    if (!detalleFacturaTable) return;
                    detalleFacturaTable.search(this.value || '').draw();
                });

                $('#btnDetalleFacturaExcel').on('click', function() {
                    if (!detalleFacturaTable) return;
                    detalleFacturaTable.button(0).trigger();
                });

                // Handlers de los botones tipo en el modal
                document.querySelectorAll('.tipo-filter-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.tipo-filter-btn').forEach(function(b) { b.classList.remove('active'); });
                        this.classList.add('active');
                    });
                });

                // Select2 autocomplete para cliente / vendedor / facturador
                function s2opts(url, placeholder) {
                    return {
                        ajax: {
                            url: url,
                            dataType: 'json',
                            delay: 300,
                            data: function(params) { return { q: params.term || '' }; },
                            processResults: function(data) { return { results: data.results }; },
                            cache: true
                        },
                        placeholder: placeholder,
                        allowClear: true,
                        minimumInputLength: 2,
                        language: {
                            inputTooShort: function() { return 'Ingrese al menos 2 caracteres...'; },
                            searching:     function() { return 'Buscando...'; },
                            noResults:     function() { return 'Sin resultados'; }
                        },
                        width: '100%',
                        dropdownParent: $('body')
                    };
                }
                $('#filtroCliente').select2(s2opts('/filtros/facturas/clientes', 'Buscar cliente...'));
                $('#filtroVendedor').select2(s2opts('/filtros/facturas/usuarios', 'Buscar vendedor...'));
                $('#filtroFacturador').select2(s2opts('/filtros/facturas/usuarios', 'Buscar facturador...'));

                // Fix: Bootstrap modal roba el foco del campo de búsqueda de Select2
                $(document).on('select2:open', function() {
                    $(document).off('focusin.modal');
                    var campo = document.querySelector('.select2-container--open .select2-search__field');
                    if (campo) campo.focus();
                });

                // Abrir modal de filtros automáticamente al cargar (sin inicializar tabla)
                setTimeout(function() { $('#modalFiltros').modal('show'); }, 400);
            });
        </script>
    @endpush

    <?php
        date_default_timezone_set('America/Tegucigalpa');
        $act_fecha=date("Y-m-d");
        $act_hora=date("H:i:s");
        $mes=date("m");
        $year=date("Y");
        $datetim=$act_fecha." ".$act_hora;
    ?>
    <script>
        function mostrarHora() {
            var el = document.getElementById("hora");
            if (!el) return;
            var fecha = new Date();
            var hora = fecha.getHours();
            var minutos = fecha.getMinutes();
            var segundos = fecha.getSeconds();
            hora = (hora < 10) ? "0" + hora : hora;
            minutos = (minutos < 10) ? "0" + minutos : minutos;
            segundos = (segundos < 10) ? "0" + segundos : segundos;
            el.innerHTML = hora + ":" + minutos + ":" + segundos;
        }
        setInterval(mostrarHora, 1000);
    </script>
</div>
