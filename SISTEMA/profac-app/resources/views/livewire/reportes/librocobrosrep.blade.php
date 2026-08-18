<div>
    @push('styles')
    <style>
    :root {
        --lc-grad:   linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
        --lc-orange: #e67e22;
        --lc-radius: 8px;
        --lc-shadow: 0 2px 8px rgba(0,0,0,.10);
    }
    .lc-card { border:1px solid #e8d5bf; border-radius:var(--lc-radius); box-shadow:var(--lc-shadow); background:#fff; }
    .lc-card-header { background:var(--lc-grad); padding:12px 20px; border-radius:var(--lc-radius) var(--lc-radius) 0 0;
        display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
    .lc-card-header h5 { margin:0; color:#fff; font-size:.85rem; font-weight:700; letter-spacing:.05em;
        text-transform:uppercase; display:flex; align-items:center; gap:8px; }
    .lc-card-body { padding:16px 20px; }
    .btn-lc-action { background:rgba(255,255,255,.18)!important; color:#fff!important;
        border:1.5px solid rgba(255,255,255,.5)!important; border-radius:5px!important;
        font-weight:600!important; font-size:.78rem; padding:5px 14px; cursor:pointer; white-space:nowrap; }
    .btn-lc-action:hover { background:rgba(255,255,255,.30)!important; }
    .lc-stats { display:flex; gap:10px; flex-wrap:wrap; padding:10px 20px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; }
    .lc-stat-pill { display:flex; align-items:center; gap:7px; background:#fdf6ee; border:1px solid #e8d5bf;
        border-radius:20px; padding:4px 14px 4px 10px; font-size:.78rem; color:#555; font-weight:500; }
    .lc-stat-pill .pill-val { font-size:.9rem; font-weight:700; color:var(--lc-orange); }
    .lc-stat-pill.green { background:#f0fdf4; border-color:#bbf7d0; }
    .lc-stat-pill.green .pill-val { color:#1a7a4e; }
    .lc-filtros-bar { padding:8px 16px; background:#fdfaf5; border-bottom:1px solid #e8d5bf;
        display:flex; flex-wrap:wrap; align-items:center; gap:6px; font-size:.78rem; }
    .lc-filtro-badge { display:inline-flex; align-items:center; gap:5px; background:#fff8ee;
        border:1px solid #f2d49a; border-radius:12px; padding:2px 10px; font-size:.75rem; color:#7d3f00; }
    .lc-filtro-badge .fr { cursor:pointer; color:#c0622a; font-weight:700; margin-left:3px; }
    #tbl_libro_cobros thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; font-weight:700;
        letter-spacing:.04em; text-transform:uppercase; border-bottom:2px solid #f2d49a; white-space:nowrap;
        padding:8px 10px; vertical-align:middle; }
    #tbl_libro_cobros tbody td { font-size:.83rem; vertical-align:middle; padding:8px 10px; }
    #tbl_libro_cobros tbody tr:hover>td { background:#fffcf5; }
    .dataTables_wrapper { width:100%!important; }
    .modal-header-lc { background:var(--lc-grad); color:#fff; border-radius:var(--lc-radius) var(--lc-radius) 0 0; padding:14px 20px; }
    .modal-header-lc .modal-title { color:#fff; font-size:.95rem; font-weight:700; }
    .modal-header-lc .close { color:#fff; opacity:.8; text-shadow:none; font-size:1.4rem; }
    .modal-header-lc .close:hover { opacity:1; }
    .select2-container--open { z-index: 99999 !important; }
    .select2-dropdown { z-index: 99999 !important; }
    .lc-section-label { font-size:.68rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase;
        color:#e67e22; border-bottom:2px solid #fdebd0; padding-bottom:5px; margin-bottom:14px; margin-top:6px;
        display:flex; align-items:center; gap:5px; }
    #modalFiltrosLC .modal-body { background:#fdfaf6; padding:18px 20px 8px; }
    #modalFiltrosLC .modal-footer { background:#f8f4ef; border-top:1px solid #ead9c8; padding:10px 20px; }
    #modalFiltrosLC .form-group label { font-size:.78rem; font-weight:600; color:#555; margin-bottom:3px; }
    #modalFiltrosLC .form-control { border-color:#ddd; border-radius:5px; font-size:.82rem; }
    #modalFiltrosLC .form-control:focus { border-color:#e67e22; box-shadow:0 0 0 .15rem rgba(230,126,34,.18); }
    .lc-filter-grid { background:#fff; border:1px solid #ead9c8; border-radius:7px; padding:14px 16px 6px; margin-bottom:14px; }
    .date-icon-lc { position:relative; }
    .date-icon-lc i { position:absolute; left:9px; top:50%; transform:translateY(-50%); color:#aaa; font-size:.78rem; pointer-events:none; }
    .date-icon-lc input { padding-left:28px; }
    /* Date shortcut buttons */
    .lc-date-shortcuts { display:flex; gap:5px; flex-wrap:wrap; margin-bottom:10px; }
    .lc-ds-btn { font-size:.72rem; padding:3px 10px; border-radius:12px; border:1px solid #e8d5bf;
        background:#fff; color:#7d3f00; cursor:pointer; font-weight:600; transition:background .15s; }
    .lc-ds-btn:hover, .lc-ds-btn.active { background:linear-gradient(135deg,#f39c12,#e05a00); color:#fff; border-color:transparent; }
    #tbl_libro_cobros tbody tr.lc-row-pagada td:nth-child(n+10) { background:#f0fdf4!important; }
    #tbl_libro_cobros tbody tr.lc-row-parcial td:nth-child(n+10) { background:#fafafa; }
    /* Separador visual entre datos del cobro y detalle de factura */
    #tbl_libro_cobros thead th:nth-child(13),
    #tbl_libro_cobros tbody td:nth-child(13) { border-left:3px solid #f2d49a!important; }
    </style>
    @endpush

    {{-- Page heading --}}
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2><i class="fa fa-book mr-2" style="color:#e67e22"></i>Libro de Cobros</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Reportes</li>
                <li class="breadcrumb-item active"><strong>Libro de Cobros</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="lc-card">

                    {{-- Header naranja --}}
                    <div class="lc-card-header">
                        <h5><i class="fa fa-book"></i> Libro de Cobros</h5>
                        <div class="d-flex" style="gap:8px;">
                            <button type="button" class="btn-lc-action" onclick="lcExportarExcel()">
                                <i class="fa fa-file-excel-o mr-1"></i>Excel
                            </button>
                            <button type="button" class="btn-lc-action" onclick="lcExportarPdf()">
                                <i class="fa fa-file-pdf-o mr-1"></i>PDF
                            </button>
                            <button type="button" class="btn-lc-action" data-toggle="modal" data-target="#modalFiltrosLC">
                                <i class="fa fa-filter mr-1"></i>Filtros
                            </button>
                        </div>
                    </div>

                    {{-- KPI pills --}}
                    <div class="lc-stats">
                        <div class="lc-stat-pill">
                            <i class="fa fa-list" style="color:var(--lc-orange)"></i>
                            <span class="pill-val" id="lc_kpi_registros">&mdash;</span>
                            <span>Cobros</span>
                        </div>
                        <div class="lc-stat-pill green">
                            <i class="fa fa-money" style="color:#1a7a4e"></i>
                            <span class="pill-val" id="lc_kpi_total_pagado">&mdash;</span>
                            <span>Total Cobrado</span>
                        </div>
                        <div class="lc-stat-pill">
                            <i class="fa fa-check-circle" style="color:var(--lc-orange)"></i>
                            <span class="pill-val" id="lc_kpi_completas">&mdash;</span>
                            <span>Facturas Completas</span>
                        </div>
                    </div>

                    {{-- Barra de filtros activos --}}
                    <div class="lc-filtros-bar" id="lc_filtros_bar" style="display:none;"></div>

                    {{-- Tabla --}}
                    <div class="lc-card-body">
                        <div style="overflow-x:auto;">
                            <table id="tbl_libro_cobros" class="table table-hover table-bordered" style="width:100%;">
                                <thead>
                                    <tr>
                                        {{-- Datos del cobro --}}
                                        <th>Fecha Venta</th>
                                        <th>Fecha Vcto.</th>
                                        <th>Fecha Pago</th>
                                        <th>Cliente</th>
                                        <th>Asesor Comercial</th>
                                        <th>Teleasesor</th>
                                        <th>N&deg; Factura</th>
                                        <th>Monto Movimiento</th>
                                        <th>Estado</th>
                                        <th>Banco</th>
                                        <th>Cuenta</th>
                                        <th>Observaciones</th>
                                        {{-- Detalle de factura (visible cuando PAGADA) --}}
                                        <th title="Detalle de factura — visible cuando está pagada">Exonerado</th>
                                        <th>Gravado</th>
                                        <th>Exento</th>
                                        <th>Sub Total</th>
                                        <th>ISV</th>
                                        <th>Total Factura</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Modal Filtros --}}
    <div class="modal fade" id="modalFiltrosLC" tabindex="-1" role="dialog"
         aria-labelledby="tituloModalLC" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-lc">
                    <h5 class="modal-title" id="tituloModalLC">
                        <i class="fa fa-filter mr-2"></i>Filtros de B&uacute;squeda
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-2">
                    <p class="lc-section-label"><i class="fa fa-calendar"></i>Rango de fechas de pago</p>
                    <div class="lc-filter-grid">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <div class="date-icon-lc"><i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="fil_lc_fecha_desde">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <div class="date-icon-lc"><i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="fil_lc_fecha_hasta">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="lc-section-label"><i class="fa fa-search"></i>Criterios de búsqueda</p>
                    <div class="lc-filter-grid">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cliente</label>
                                    <select id="fil_lc_cliente" class="form-control" style="width:100%;">
                                        <option value="">&mdash; Todos &mdash;</option>
                                        @foreach($clientes as $cl)
                                            <option value="{{ $cl->id }}">{{ $cl->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Asesor Comercial</label>
                                    <select id="fil_lc_vendedor" class="form-control" style="width:100%;">
                                        <option value="">&mdash; Todos &mdash;</option>
                                        @foreach($vendedores as $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Banco</label>
                                    <select id="fil_lc_banco" class="form-control form-control-sm" style="width:100%;">
                                        <option value="">&mdash; Todos &mdash;</option>
                                        @foreach($bancos as $b)
                                            <option value="{{ $b->id }}">{{ $b->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>N° Factura</label>
                                    <input type="text" class="form-control form-control-sm" id="fil_lc_factura"
                                           placeholder="Ej: 2026-00123">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="limpiarFiltrosLC()">
                        <i class="fa fa-eraser mr-1"></i>Limpiar
                    </button>
                    <button type="button" class="btn btn-sm" id="btn_buscar_lc" onclick="aplicarFiltrosLC()"
                        style="background:linear-gradient(135deg,#f39c12,#e05a00);color:#fff;border:none;font-weight:600;padding:6px 20px;border-radius:5px;">
                        <i class="fa fa-search mr-1"></i>Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="{{ asset('/js/js_proyecto/reportes/librocobrosrep.js') }}"></script>
    @endpush
</div>
