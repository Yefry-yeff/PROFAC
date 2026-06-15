<div>
    {{-- ════════════════════════════════════════════════════════════════
         REPORTE LIBRO DE VENTA (v2 — Estilo Ventas & Cobros)
         ════════════════════════════════════════════════════════════════ --}}

    @push('styles')
    <style>
    :root {
        --pf-grad:   linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
        --pf-orange: #e67e22;
        --pf-radius: 8px;
        --pf-shadow: 0 2px 8px rgba(0,0,0,.10);
    }
    .rvc-card { border:1px solid #e8d5bf; border-radius:var(--pf-radius); box-shadow:var(--pf-shadow); background:#fff; overflow:visible; }
    .rvc-card-header { background:var(--pf-grad); padding:12px 20px; border-radius:var(--pf-radius) var(--pf-radius) 0 0; display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
    .rvc-card-header h5 { margin:0; color:#fff; font-size:.85rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; display:flex; align-items:center; gap:8px; }
    .rvc-card-body { padding:16px 20px; }
    .btn-rvc-action { background:rgba(255,255,255,.18)!important; color:#fff!important; border:1.5px solid rgba(255,255,255,.5)!important; border-radius:5px!important; font-weight:600!important; font-size:.78rem; padding:5px 14px; transition:background .18s; white-space:nowrap; cursor:pointer; }
    .btn-rvc-action:hover { background:rgba(255,255,255,.30)!important; color:#fff!important; }
    .rvc-stats { display:flex; gap:10px; flex-wrap:wrap; padding:10px 20px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; }
    .rvc-stat-pill { display:flex; align-items:center; gap:7px; background:#fdf6ee; border:1px solid #e8d5bf; border-radius:20px; padding:4px 14px 4px 10px; font-size:.78rem; color:#555; font-weight:500; }
    .rvc-stat-pill .pill-val { font-size:.9rem; font-weight:700; color:var(--pf-orange); }
    .rvc-stat-pill.green { background:#f0fdf4; border-color:#bbf7d0; }
    .rvc-stat-pill.green .pill-val { color:#1a7a4e; }
    .filtros-bar { padding:8px 16px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; display:flex; flex-wrap:wrap; align-items:center; gap:6px; font-size:.78rem; }
    .filtro-badge { display:inline-flex; align-items:center; gap:5px; background:#fff8ee; border:1px solid #f2d49a; border-radius:12px; padding:2px 10px; font-size:.75rem; color:#7d3f00; }
    .filtro-badge .filtro-remove { cursor:pointer; color:#c0622a; font-weight:700; margin-left:3px; }
    .filtro-badge .filtro-remove:hover { color:#e74c3c; }
    #tbl_libro_venta { width:100%!important; }
    #tbl_libro_venta thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; border-bottom:2px solid #f2d49a; white-space:nowrap; padding:8px 10px; vertical-align:middle; }
    #tbl_libro_venta tbody td { font-size:.83rem; vertical-align:middle; padding:8px 10px; }
    #tbl_libro_venta tbody tr:hover>td { background:#fffcf5; }
    .modal-header-rvc { background:var(--pf-grad); color:#fff; border-radius:var(--pf-radius) var(--pf-radius) 0 0; padding:14px 20px; }
    .modal-header-rvc .modal-title { color:#fff; font-size:.95rem; font-weight:700; }
    .modal-header-rvc .close { color:#fff; opacity:.8; text-shadow:none; font-size:1.4rem; }
    .modal-header-rvc .close:hover { opacity:1; }
    .modal-section-label { font-size:.68rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#e67e22; border-bottom:2px solid #fdebd0; padding-bottom:5px; margin-bottom:14px; margin-top:6px; display:flex; align-items:center; gap:5px; }
    #modalFiltrosLV .modal-body { background:#fdfaf6; padding:18px 20px 8px; }
    #modalFiltrosLV .modal-footer { background:#f8f4ef; border-top:1px solid #ead9c8; padding:10px 20px; }
    #modalFiltrosLV .form-group label { font-size:.78rem; font-weight:600; color:#555; margin-bottom:3px; }
    #modalFiltrosLV .form-control { border-color:#ddd; border-radius:5px; font-size:.82rem; }
    #modalFiltrosLV .form-control:focus { border-color:#e67e22; box-shadow:0 0 0 .15rem rgba(230,126,34,.18); }
    .modal-filter-grid { background:#fff; border:1px solid #ead9c8; border-radius:7px; padding:14px 16px 6px; margin-bottom:14px; }
    .date-input-icon { position:relative; }
    .date-input-icon i { position:absolute; left:9px; top:50%; transform:translateY(-50%); color:#aaa; font-size:.78rem; pointer-events:none; }
    .date-input-icon input { padding-left:28px; }
    .select2-container--open { z-index:99999!important; }
    #page-wrapper { padding-left:0!important; padding-right:0!important; }
    .wrapper-content { padding-left:0!important; padding-right:0!important; }
    .wrapper-content>.row { margin-left:0!important; margin-right:0!important; }
    .wrapper-content>.row>[class*="col-"] { padding-left:0!important; padding-right:0!important; }
    .dataTables_wrapper { width:100%!important; }
    </style>
    @endpush

    {{-- ── Page heading ── --}}
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2><i class="fa fa-file-text-o mr-2" style="color:#e67e22"></i>Reporte de Libro de Venta</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Reportes</li>
                <li class="breadcrumb-item active"><strong>Libro de Venta</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="rvc-card">

                    {{-- ── Header con gradiente naranja ── --}}
                    <div class="rvc-card-header">
                        <h5><i class="fa fa-book"></i> Detalle de Ventas por Periodo</h5>
                        <div class="d-flex" style="gap:8px">
                            <button type="button" class="btn-rvc-action" onclick="exportarExcelLV()">
                                <i class="fa fa-file-excel-o mr-1"></i>Excel
                            </button>
                            <button type="button" class="btn-rvc-action" data-toggle="modal" data-target="#modalFiltrosLV">
                                <i class="fa fa-filter mr-1"></i>Filtros
                            </button>
                        </div>
                    </div>

                    {{-- ── KPI pills ── --}}
                    <div class="rvc-stats">
                        <div class="rvc-stat-pill">
                            <i class="fa fa-file-text-o" style="color:var(--pf-orange)"></i>
                            <span class="pill-val" id="kpi_total_vendido">&#8212;</span>
                            <span>Total Vendido</span>
                        </div>
                        <div class="rvc-stat-pill">
                            <i class="fa fa-percent" style="color:var(--pf-orange)"></i>
                            <span class="pill-val" id="kpi_total_isv">&#8212;</span>
                            <span>ISV Total</span>
                        </div>
                        <div class="rvc-stat-pill green">
                            <i class="fa fa-check-circle" style="color:#1a7a4e"></i>
                            <span class="pill-val" id="kpi_total_gravado">&#8212;</span>
                            <span>Gravado</span>
                        </div>
                        <div class="rvc-stat-pill">
                            <i class="fa fa-list" style="color:var(--pf-orange)"></i>
                            <span class="pill-val" id="kpi_total_registros">&#8212;</span>
                            <span>Registros</span>
                        </div>
                    </div>

                    {{-- ── Barra de filtros activos ── --}}
                    <div class="filtros-bar" id="lv_filtros_bar" style="display:none;"></div>

                    {{-- ── Tabla DataTable ── --}}
                    <div class="rvc-card-body">
                        <div style="overflow-x:auto;">
                            <table id="tbl_libro_venta" class="table table-hover table-bordered" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Vendedor</th>
                                        <th>Cliente</th>
                                        <th>Factura</th>
                                        <th>Exonerado</th>
                                        <th>Gravado</th>
                                        <th>Excento</th>
                                        <th>Subtotal</th>
                                        <th>ISV</th>
                                        <th>Total</th>
                                        <th>Fecha Compra</th>
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

    {{-- ══════════════════════════════════════════════════════════════
         Modal de Filtros
         ══════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="modalFiltrosLV" tabindex="-1" role="dialog"
         aria-labelledby="tituloModalLV" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header modal-header-rvc">
                    <h5 class="modal-title" id="tituloModalLV">
                        <i class="fa fa-filter mr-2"></i>Filtros de Búsqueda
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body pb-2">

                    {{-- Fechas de venta --}}
                    <p class="modal-section-label"><i class="fa fa-calendar"></i>Rango de fechas</p>
                    <div class="modal-filter-grid">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <div class="date-input-icon">
                                        <i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="fil_lv_fecha_desde">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <div class="date-input-icon">
                                        <i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="fil_lv_fecha_hasta">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Criterios de búsqueda --}}
                    <p class="modal-section-label"><i class="fa fa-search"></i>Criterios de búsqueda</p>
                    <div class="modal-filter-grid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>N° Factura</label>
                                    <input type="text" class="form-control form-control-sm" id="fil_lv_factura"
                                           placeholder="Buscar por número de factura...">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Cliente</label>
                                    <select id="fil_lv_cliente" class="form-control form-control-sm" style="width:100%">
                                        <option value=""></option>
                                        @foreach($clientes as $cl)
                                            <option value="{{ $cl->id }}">{{ $cl->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vendedor</label>
                                    <select id="fil_lv_vendedor" class="form-control form-control-sm" style="width:100%">
                                        <option value=""></option>
                                        @foreach($vendedores as $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Modo de pago</label>
                                    <select id="fil_lv_modo_pago" class="form-control form-control-sm">
                                        <option value="">&#8212; Todos &#8212;</option>
                                        @foreach($modosPago as $mp)
                                            <option value="{{ $mp->id }}">{{ $mp->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="limpiarFiltrosLV()">
                        <i class="fa fa-eraser mr-1"></i>Limpiar filtros
                    </button>
                    <button type="button" class="btn btn-sm" onclick="aplicarFiltrosLV()"
                        style="background:linear-gradient(135deg,#f39c12,#e05a00);color:#fff;border:none;font-weight:600;padding:6px 20px;border-radius:5px">
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
    <script src="{{ asset('/js/js_proyecto/reportes/libroventarep.js') }}"></script>
    @endpush
</div>
