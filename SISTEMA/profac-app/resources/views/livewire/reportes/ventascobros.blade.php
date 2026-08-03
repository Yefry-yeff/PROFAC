<div>
    {{-- ════════════════════════════════════════════════════════════════
         REPORTE FINANCIERO DETALLADO POR FACTURA  (v3 — PROFAC Style)
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
    /* KPI pills */
    .rvc-stats { display:flex; gap:10px; flex-wrap:wrap; padding:10px 20px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; }
    .rvc-stat-pill { display:flex; align-items:center; gap:7px; background:#fdf6ee; border:1px solid #e8d5bf; border-radius:20px; padding:4px 14px 4px 10px; font-size:.78rem; color:#555; font-weight:500; }
    .rvc-stat-pill .pill-val { font-size:.9rem; font-weight:700; color:var(--pf-orange); }
    .rvc-stat-pill.green { background:#f0fdf4; border-color:#bbf7d0; }
    .rvc-stat-pill.green .pill-val { color:#1a7a4e; }
    .rvc-stat-pill.red { background:#fef2f2; border-color:#fecaca; }
    .rvc-stat-pill.red .pill-val { color:#b91c1c; }
    .rvc-stat-pill .pill-sub { font-size:.70rem; color:#9ca3af; margin-left:2px; }
    /* filtros bar */
    .filtros-bar { padding:8px 16px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; display:flex; flex-wrap:wrap; align-items:center; gap:6px; font-size:.78rem; }
    .filtro-badge { display:inline-flex; align-items:center; gap:5px; background:#fff8ee; border:1px solid #f2d49a; border-radius:12px; padding:2px 10px; font-size:.75rem; color:#7d3f00; }
    .filtro-badge .filtro-remove { cursor:pointer; color:#c0622a; font-weight:700; margin-left:3px; }
    .filtro-badge .filtro-remove:hover { color:#e74c3c; }
    /* tabla */
    #tbl_rfd { width:100%!important; }
    #tbl_rfd thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; border-bottom:2px solid #f2d49a; white-space:nowrap; padding:8px 10px; vertical-align:middle; }
    #tbl_rfd tbody td { font-size:.83rem; vertical-align:middle; padding:8px 10px; }
    #tbl_rfd tbody tr:hover>td { background:#fffcf5; }
    /* toggle btn */
    .rfd-toggle-btn { width:28px; height:28px; border-radius:50%; border:none; background:#e67e22; color:#fff; cursor:pointer; font-size:13px; display:inline-flex; align-items:center; justify-content:center; transition:transform .2s,background .2s; }
    .rfd-toggle-btn.open { background:#374151; transform:rotate(45deg); }
    /* estado badges */
    .rfd-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; white-space:nowrap; }
    .badge-pagada       { background:#dcfce7; color:#166534; }
    .badge-pendiente    { background:#fef9c3; color:#854d0e; }
    .badge-parcial      { background:#dbeafe; color:#1e40af; }
    .badge-vencida      { background:#fee2e2; color:#991b1b; }
    .badge-vencida-crit { background:#fecaca; color:#7f1d1d; animation:rfd-pulse 1.4s ease-in-out infinite; }
    .badge-contado      { background:#ede9fe; color:#4c1d95; }
    .badge-anulado      { background:#f3f4f6; color:#6b7280; text-decoration:line-through; }
    @keyframes rfd-pulse { 0%,100%{ box-shadow:0 0 0 0 rgba(220,38,38,.4); } 50%{ box-shadow:0 0 0 6px rgba(220,38,38,0); } }
    tr.rfd-row-anulado td { opacity:.55; text-decoration:line-through; }
    /* modal */
    .modal-header-rvc { background:var(--pf-grad); color:#fff; border-radius:var(--pf-radius) var(--pf-radius) 0 0; padding:14px 20px; }
    .modal-header-rvc .modal-title { color:#fff; font-size:.95rem; font-weight:700; }
    .modal-header-rvc .close { color:#fff; opacity:.8; text-shadow:none; font-size:1.4rem; }
    .modal-header-rvc .close:hover { opacity:1; }
    .modal-section-label { font-size:.68rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#e67e22; border-bottom:2px solid #fdebd0; padding-bottom:5px; margin-bottom:14px; margin-top:6px; display:flex; align-items:center; gap:5px; }
    #modalFiltrosRVC .modal-body { background:#fdfaf6; padding:18px 20px 8px; }
    #modalFiltrosRVC .modal-footer { background:#f8f4ef; border-top:1px solid #ead9c8; padding:10px 20px; }
    #modalFiltrosRVC .form-group label { font-size:.78rem; font-weight:600; color:#555; margin-bottom:3px; }
    #modalFiltrosRVC .form-control { border-color:#ddd; border-radius:5px; font-size:.82rem; }
    #modalFiltrosRVC .form-control:focus { border-color:#e67e22; box-shadow:0 0 0 .15rem rgba(230,126,34,.18); }
    .modal-filter-grid { background:#fff; border:1px solid #ead9c8; border-radius:7px; padding:14px 16px 6px; margin-bottom:14px; }
    .date-input-icon { position:relative; }
    .date-input-icon i { position:absolute; left:9px; top:50%; transform:translateY(-50%); color:#aaa; font-size:.78rem; pointer-events:none; }
    .date-input-icon input { padding-left:28px; }
    .select2-container--open { z-index:99999!important; }
    /* expediente child row */
    .rfd-exp-wrapper { background:#f8fafc; border-top:2px solid #fde8b0; padding:20px 24px; }
    .rfd-exp-header-card { background:var(--pf-grad); color:#fff; border-radius:var(--pf-radius); padding:16px 20px; margin-bottom:16px; }
    .rfd-exp-header-card h4 { margin:0 0 12px; font-size:15px; font-weight:700; border-bottom:1px solid rgba(255,255,255,.2); padding-bottom:10px; }
    .rfd-exp-meta-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:8px 20px; }
    .rfd-meta-item .rfd-meta-lbl { font-size:10px; opacity:.7; text-transform:uppercase; letter-spacing:.5px; }
    .rfd-meta-item .rfd-meta-val { font-size:13px; font-weight:600; }
    .rfd-fin-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px; }
    @media(max-width:768px){ .rfd-fin-grid{ grid-template-columns:1fr; } }
    .rfd-fin-box { background:#fff; border-radius:var(--pf-radius); border:1px solid #e5e7eb; padding:14px 16px; }
    .rfd-fin-box h5 { margin:0 0 10px; font-size:12px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; }
    .rfd-fin-row { display:flex; justify-content:space-between; align-items:center; padding:5px 0; border-bottom:1px solid #f3f4f6; font-size:13px; }
    .rfd-fin-row:last-child { border-bottom:none; }
    .rfd-fin-row .lbl { color:#6b7280; } .rfd-fin-row .val { font-weight:700; color:#111827; }
    .rfd-fin-row.total .lbl,.rfd-fin-row.total .val { font-size:15px; font-weight:800; color:#e67e22; }
    .rfd-fin-row.saldo-0   .val { color:#0e9f6e; }
    .rfd-fin-row.saldo-venc .val { color:#e02424; }
    .rfd-cartera-box { background:#fff; border-radius:var(--pf-radius); border:1px solid #e5e7eb; padding:14px 16px; margin-bottom:16px; }
    .rfd-cartera-box h5 { margin:0 0 10px; font-size:12px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; }
    .rfd-cartera-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(170px,1fr)); gap:8px; }
    .rfd-cartera-item { background:#f8fafc; border-radius:8px; border:1px solid #e5e7eb; padding:8px 12px; }
    .rfd-cartera-item .ci-lbl { font-size:10px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.4px; }
    .rfd-cartera-item .ci-val { font-size:14px; font-weight:800; color:#111827; margin-top:2px; }
    .rfd-timeline-hdr { font-size:12px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; margin-bottom:12px; }
    .rfd-timeline { list-style:none; padding:0; margin:0; position:relative; }
    .rfd-timeline::before { content:''; position:absolute; left:20px; top:0; bottom:0; width:2px; background:#e5e7eb; }
    .rfd-tl-item { display:flex; gap:16px; margin-bottom:16px; position:relative; }
    .rfd-tl-dot { flex-shrink:0; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:15px; color:#fff; z-index:1; box-shadow:0 2px 6px rgba(0,0,0,.15); }
    .rfd-tl-body { background:#fff; border-radius:var(--pf-radius); border:1px solid #e5e7eb; padding:12px 16px; flex:1; box-shadow:0 1px 3px rgba(0,0,0,.06); }
    .rfd-tl-body .tl-tipo { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; }
    .rfd-tl-body .tl-doc  { font-size:13px; font-weight:700; color:#111827; }
    .rfd-tl-body .tl-fecha { font-size:11px; color:#9ca3af; margin-left:8px; }
    .rfd-tl-body .tl-desc { font-size:12px; color:#6b7280; margin-top:3px; }
    .rfd-tl-body .tl-meta { display:flex; flex-wrap:wrap; gap:6px 16px; margin-top:8px; }
    .rfd-tl-body .tl-meta span { font-size:11px; color:#374151; }
    .rfd-tl-body .tl-meta span strong { font-weight:700; }
    .rfd-tl-monto { font-size:15px; font-weight:800; margin-left:auto; flex-shrink:0; align-self:center; }
    .rfd-tl-saldo { font-size:11px; font-weight:600; color:#6b7280; margin-left:8px; }
    .rfd-dot-venta        { background:#e67e22; }
    .rfd-dot-entrega      { background:#0e9f6e; }
    .rfd-dot-abono        { background:#d97706; }
    .rfd-dot-pago         { background:#7c3aed; }
    .rfd-dot-nota_credito { background:#e02424; }
    .rfd-dot-nota_debito  { background:#b45309; }
    .rfd-dot-vale         { background:#e67e22; }
    /* layout overrides */
    #page-wrapper { padding-left:0!important; padding-right:0!important; }
    .wrapper-content { padding-left:0!important; padding-right:0!important; }
    .wrapper-content>.row { margin-left:0!important; margin-right:0!important; }
    .wrapper-content>.row>[class*="col-"] { padding-left:0!important; padding-right:0!important; }
    .dataTables_wrapper { width:100%!important; }
    @media(max-width:767px){
        .rvc-card-body { padding:10px; }
        .rvc-card-header { padding:10px 12px; }
        .modal-dialog.modal-lg { max-width:calc(100vw - 1rem); }
    }
    </style>
    @endpush

    {{-- ── Page heading ── --}}
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2><i class="fa fa-file-text-o mr-2" style="color:#e67e22"></i>Reporte Financiero &mdash; Ventas &amp; Cobros</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Reportes</li>
                <li class="breadcrumb-item active"><strong>Ventas &amp; Cobros</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="rvc-card">

                    {{-- ── Header con gradiente naranja ── --}}
                    <div class="rvc-card-header">
                        <h5><i class="fa fa-file-text-o"></i> Reporte Financiero Detallado por Factura</h5>
                        <div class="d-flex" style="gap:8px">
                            <button type="button" class="btn-rvc-action" onclick="exportarExcel()">
                                <i class="fa fa-file-excel-o mr-1"></i>Excel
                            </button>
                            <button type="button" class="btn-rvc-action" onclick="exportarPdf()">
                                <i class="fa fa-file-pdf-o mr-1"></i>PDF
                            </button>
                            <button type="button" class="btn-rvc-action" data-toggle="modal" data-target="#modalFiltrosRVC">
                                <i class="fa fa-filter mr-1"></i>Filtros
                            </button>
                        </div>
                    </div>

                    {{-- ── KPI pills ── --}}
                    <div class="rvc-stats">
                        <div class="rvc-stat-pill">
                            <i class="fa fa-file-text-o" style="color:var(--pf-orange)"></i>
                            <span class="pill-val" id="kpi_facturado">&#8212;</span>
                            <span>Facturado</span>
                            <span class="pill-sub" id="kpi_total_facturas"></span>
                        </div>
                        <div class="rvc-stat-pill green">
                            <i class="fa fa-check-circle" style="color:#1a7a4e"></i>
                            <span class="pill-val" id="kpi_cobrado">&#8212;</span>
                            <span>Cobrado</span>
                            <span class="pill-sub" id="kpi_fac_pagadas"></span>
                        </div>
                        <div class="rvc-stat-pill">
                            <i class="fa fa-level-up" style="color:var(--pf-orange)"></i>
                            <span class="pill-val" id="kpi_pendiente">&#8212;</span>
                            <span>Aumento Factura</span>
                            <span class="pill-sub" id="kpi_sub_aumento"></span>
                        </div>
                        <div class="rvc-stat-pill red">
                            <i class="fa fa-level-down" style="color:#b91c1c"></i>
                            <span class="pill-val" id="kpi_vencido">&#8212;</span>
                            <span>Disminuyo Factura</span>
                            <span class="pill-sub" id="kpi_sub_disminucion"></span>
                        </div>
                        <div class="rvc-stat-pill">
                            <i class="fa fa-clock-o" style="color:var(--pf-orange)"></i>
                            <span class="pill-val" id="kpi_saldo_pendiente">&#8212;</span>
                            <span>Saldo Pendiente</span>
                            <span class="pill-sub" id="kpi_fac_pendientes"></span>
                        </div>
                    </div>

                    {{-- ── Barra de filtros activos ── --}}
                    <div class="filtros-bar" id="rvc_filtros_bar" style="display:none;"></div>

                    {{-- ── Tabla DataTable ── --}}
                    <div class="rvc-card-body">
                        <div style="overflow-x:auto;">
                            <table id="tbl_rfd" class="table table-hover table-bordered" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th style="width:40px;"></th>
                                        <th>Factura / CAI</th>
                                        <th>Cliente</th>
                                        <th>Asesor Comercial</th>
                                        <th>Teleasesor</th>
                                        <th>Fecha Venta</th>
                                        <th>Modo Pago</th>
                                        <th>Total Facturado</th>
                                        <th>Total Pagado</th>
                                        <th>Saldo Pendiente</th>
                                        <th>Estado</th>
                                        <th>D&#237;as Venc.</th>
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
         Modal de Filtros (estilo listado-facturas)
         ══════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="modalFiltrosRVC" tabindex="-1" role="dialog"
         aria-labelledby="tituloModalRVC" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header modal-header-rvc">
                    <h5 class="modal-title" id="tituloModalRVC">
                        <i class="fa fa-filter mr-2"></i>Filtros de B&#250;squeda
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body pb-2">

                    {{-- Fechas de venta --}}
                    <p class="modal-section-label"><i class="fa fa-calendar"></i>Rango de fechas de venta</p>
                    <div class="modal-filter-grid">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <div class="date-input-icon">
                                        <i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="fil_fecha_desde">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <div class="date-input-icon">
                                        <i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="fil_fecha_hasta">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Criterios de búsqueda --}}
                    <p class="modal-section-label"><i class="fa fa-search"></i>Criterios de b&#250;squeda</p>
                    <div class="modal-filter-grid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>N&#176; Factura / CAI</label>
                                    <input type="text" class="form-control form-control-sm" id="fil_factura"
                                           placeholder="Ej: 000-001-01-00041992 o solo el n&#250;mero">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Cliente</label>
                                    <select id="fil_cliente" class="form-control" style="width:100%">
                                        <option value=""></option>
                                        @foreach($clientes as $cl)
                                            <option value="{{ $cl->id }}">{{ $cl->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Asesor Comercial</label>
                                    <select id="fil_vendedor" class="form-control" style="width:100%">
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
                                    <select id="fil_modo_pago" class="form-control form-control-sm">
                                        <option value="">&#8212; Todos &#8212;</option>
                                        @foreach($modosPago as $mp)
                                            <option value="{{ $mp->id }}">{{ $mp->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Estado --}}
                    <p class="modal-section-label"><i class="fa fa-tag"></i>Estado</p>
                    <div class="modal-filter-grid">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Estado cobro</label>
                                    <select id="fil_estado_cobro" class="form-control form-control-sm">
                                        <option value="">&#8212; Todos &#8212;</option>
                                        <option value="Anuladas">Anuladas</option>
                                        <option value="Contado">Contado</option>
                                        <option value="Pagada">Pagada</option>
                                        <option value="Parcialmente Pagada">Parcialmente Pagada</option>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Vencida">Vencida</option>
                                        <option value="Vencida Cr&#237;tica">Vencida Cr&#237;tica</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pagos / Abonos --}}
                    <p class="modal-section-label"><i class="fa fa-money"></i>Pagos / Abonos</p>
                    <div class="modal-filter-grid">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha pago desde</label>
                                    <div class="date-input-icon">
                                        <i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="fil_fecha_pago_desde">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha pago hasta</label>
                                    <div class="date-input-icon">
                                        <i class="fa fa-calendar-o"></i>
                                        <input type="date" class="form-control form-control-sm" id="fil_fecha_pago_hasta">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Banco</label>
                                    <select id="fil_banco" class="form-control form-control-sm">
                                        <option value="">&#8212; Todos &#8212;</option>
                                        @foreach($bancos as $b)
                                            <option value="{{ $b->id }}">{{ $b->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>N&#176;. de cuenta</label>
                                    <input type="text" class="form-control form-control-sm" id="fil_cuenta"
                                           placeholder="Buscar cuenta...">
                                </div>
                            </div>
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
    <script src="/js/js_proyecto/reportes/ventascobros.js"></script>
    @endpush
</div>