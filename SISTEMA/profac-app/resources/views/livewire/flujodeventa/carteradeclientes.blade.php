<div>
    @push('styles')
    <style>
    :root { --cdc-grad: linear-gradient(135deg,#f39c12 0%,#e05a00 100%); --cdc-accent:#e67e22; --cdc-radius:8px; --cdc-shadow:0 2px 8px rgba(0,0,0,.10); }
    .cdc-card { border:1px solid #e8d5bf; border-radius:var(--cdc-radius); box-shadow:var(--cdc-shadow); background:#fff; overflow:visible; }
    .cdc-card-header { background:var(--cdc-grad); padding:12px 20px; border-radius:var(--cdc-radius) var(--cdc-radius) 0 0; display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
    .cdc-card-header h5 { margin:0; color:#fff; font-size:.85rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; display:flex; align-items:center; gap:8px; }
    .cdc-card-body { padding:16px 20px; }
    .btn-cdc-action { background:rgba(255,255,255,.18)!important; color:#fff!important; border:1.5px solid rgba(255,255,255,.5)!important; border-radius:5px!important; font-weight:600!important; font-size:.78rem; padding:5px 14px; transition:background .18s; white-space:nowrap; cursor:pointer; }
    .btn-cdc-action:hover { background:rgba(255,255,255,.30)!important; color:#fff!important; }
    .btn-cdc-action.active { background:#fff!important; color:var(--cdc-accent)!important; }
    .btn-cdc-primary { background:var(--cdc-grad)!important; color:#fff!important; border:none!important; font-weight:600; padding:6px 20px; border-radius:5px; font-size:.85rem; }
    .btn-cdc-primary:hover { color:#fff!important; opacity:.92; }
    .btn-cdc-primary:disabled { opacity:.5; cursor:not-allowed; }
    .cdc-filtros-bar { padding:12px 16px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; display:flex; flex-wrap:wrap; align-items:end; gap:10px; }
    .cdc-filtros-bar .form-group { margin-bottom:0; min-width:190px; flex:1; }
    .cdc-filtros-bar label { font-size:.72rem; font-weight:700; color:#7d3f00; text-transform:uppercase; letter-spacing:.03em; margin-bottom:3px; }
    .cdc-seleccion-bar { padding:8px 16px; background:#fff8ee; border-bottom:1px solid #f2d49a; display:none; align-items:center; gap:12px; flex-wrap:wrap; }
    .cdc-seleccion-bar.show { display:flex; }
    .cdc-seleccion-count { font-size:.85rem; font-weight:700; color:#7d3f00; }
    #tbl_cdc thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; font-weight:700; letter-spacing:.03em; text-transform:uppercase; border-bottom:2px solid #f2d49a; white-space:nowrap; padding:8px 10px; vertical-align:middle; }
    #tbl_cdc tbody td { font-size:.83rem; vertical-align:middle; padding:8px 10px; }
    #tbl_cdc tbody tr:hover>td { background:#fffcf5; }
    .cdc-chip { display:inline-block; padding:2px 9px; border-radius:12px; font-size:.72rem; font-weight:600; margin:1px 3px 1px 0; }
    .cdc-chip-asesor { background:#fdebd0; color:#8a5000; }
    .cdc-chip-teleasesor { background:#e3f2ff; color:#0d5da6; }
    .btn-cdc-accion { background:none; border:none; color:#7d3f00; font-size:.95rem; padding:2px 6px; cursor:pointer; }
    .btn-cdc-accion:hover { color:var(--cdc-accent); }
    .cdc-group { border:1px solid #e8d5bf; border-radius:6px; margin-bottom:10px; overflow:hidden; }
    .cdc-group-header { background:#fdf4e7; padding:10px 16px; display:flex; align-items:center; justify-content:between; gap:10px; cursor:pointer; }
    .cdc-group-header:hover { background:#fbe9d0; }
    .cdc-group-title { font-weight:700; color:#7d3f00; font-size:.9rem; flex:1; }
    .cdc-group-select { display:inline-flex; align-items:center; gap:5px; margin:0; color:#7d3f00; font-size:.72rem; font-weight:700; cursor:pointer; white-space:nowrap; }
    .cdc-group-select input { margin:0; }
    .cdc-group-badge { background:var(--cdc-grad); color:#fff; border-radius:12px; padding:2px 12px; font-size:.75rem; font-weight:700; }
    .cdc-group-chevron { transition:transform .2s; color:#7d3f00; }
    .cdc-group.open .cdc-group-chevron { transform:rotate(90deg); }
    .cdc-group-body { display:none; padding:8px 16px 14px; }
    .cdc-group.open .cdc-group-body { display:block; }
    .cdc-mini-row { display:flex; align-items:center; gap:10px; padding:8px 6px; border-bottom:1px solid #f3ecdf; font-size:.83rem; flex-wrap:wrap; }
    .cdc-mini-row:last-child { border-bottom:none; }
    .cdc-mini-nombre { font-weight:600; min-width:200px; flex:1; }
    .cdc-agrupado-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:14px; }
    .cdc-agrupado-card { position:relative; display:flex; align-items:center; gap:13px; min-height:104px; padding:15px; border:1px solid #e2c7a6; border-radius:8px; background:#fdfaf6; box-shadow:0 2px 8px rgba(91,55,20,.08); cursor:pointer; transition:transform .16s,box-shadow .16s,border-color .16s; }
    .cdc-agrupado-card:hover { transform:translateY(-2px); border-color:#d79951; box-shadow:0 7px 16px rgba(91,55,20,.14); }
    .cdc-agrupado-card-icon { display:flex; align-items:center; justify-content:center; flex:0 0 46px; width:46px; height:46px; border-radius:7px; background:#f3dcc0; color:#c4690d; font-size:1.25rem; }
    .cdc-agrupado-card-info { flex:1; min-width:0; }
    .cdc-agrupado-card-info strong { display:block; color:#633300; font-size:.95rem; overflow-wrap:anywhere; }
    .cdc-agrupado-card-info small { display:block; margin-top:3px; color:#806e5b; font-size:.74rem; font-weight:700; }
    .cdc-agrupado-subitems { display:block; margin-top:6px; color:#6f6255; font-size:.7rem; line-height:1.35; }
    .cdc-agrupado-select { position:absolute; top:8px; right:9px; display:inline-flex; align-items:center; gap:4px; margin:0; padding:3px 7px; border-radius:4px; background:#fff; color:#7d3f00; font-size:.67rem; font-weight:700; cursor:pointer; }
    .cdc-agrupado-enter { color:#a56a30; margin-top:24px; }
    .cdc-agrupado-head { display:none; align-items:center; gap:10px; margin-bottom:13px; padding-bottom:11px; border-bottom:1px solid #ead9c8; }
    .cdc-agrupado-head-title { flex:1; min-width:0; }
    .cdc-agrupado-head-title strong { display:block; color:#633300; font-size:1.02rem; }
    .cdc-agrupado-head-title small { color:#806e5b; font-weight:700; }
    #tbl_cdc_agrupado_clientes thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; font-weight:700; text-transform:uppercase; white-space:nowrap; vertical-align:middle; }
    #tbl_cdc_agrupado_clientes tbody td { font-size:.83rem; vertical-align:middle; }
    .modal-header-cdc { background:var(--cdc-grad); color:#fff; border-radius:var(--cdc-radius) var(--cdc-radius) 0 0; padding:14px 20px; }
    .modal-header-cdc .modal-title { color:#fff; font-size:.95rem; font-weight:700; }
    .modal-header-cdc .close { color:#fff; opacity:.8; text-shadow:none; font-size:1.4rem; }
    .modal-header-cdc .close:hover { opacity:1; }
    .cdc-modal-section { font-size:.68rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:var(--cdc-accent); border-bottom:2px solid #fdebd0; padding-bottom:5px; margin-bottom:12px; margin-top:10px; }
    .cdc-tipo-box { background:#fdfaf6; border:1px solid #ead9c8; border-radius:7px; padding:14px 16px; margin-bottom:14px; }
    .cdc-tipo-box .form-check-inline { margin-right:14px; }
    .cdc-buscar-agregar { display:flex; gap:8px; align-items:center; margin-bottom:12px; }
    .cdc-buscar-agregar .select2-container { flex:1 1 auto; min-width:0; width:100% !important; }
    .cdc-buscar-agregar .btn { flex:0 0 auto; white-space:nowrap; }
    .cdc-lista-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:6px; }
    .cdc-lista-header span { font-size:.72rem; font-weight:700; color:#7d3f00; text-transform:uppercase; letter-spacing:.03em; }
    .cdc-lista-asignados { display:flex; flex-wrap:wrap; gap:6px; min-height:36px; padding:6px 2px; }
    .cdc-lista-asignados .cdc-vacio { color:#a89686; font-size:.8rem; font-style:italic; padding:6px 4px; }
    .cdc-chip-editable { display:inline-flex; align-items:center; gap:7px; padding:4px 6px 4px 12px; font-size:.78rem; }
    .cdc-chip-remove-icon { cursor:pointer; opacity:.65; font-size:.7rem; padding:3px; }
    .cdc-chip-remove-icon:hover { opacity:1; }
    #tbl_cdc_historial thead th, #tbl_cdc_historial_masivo thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; text-transform:uppercase; }
    #tbl_cdc_zona_detalle thead th, #tbl_cdc_zona_historial thead th, #tbl_cdc_zona_cambios thead th, #tbl_cdc_responsables_decisiones thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; text-transform:uppercase; white-space:nowrap; }
    .cdc-zona-toolbar { display:flex; align-items:center; gap:8px; justify-content:space-between; flex-wrap:wrap; margin-bottom:12px; }
    .cdc-zona-toolbar .input-group { max-width:360px; }
    .cdc-zona-resumen { display:flex; gap:6px; flex-wrap:wrap; }
    .cdc-departamentos-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(360px,1fr)); gap:16px; align-items:start; }
    .cdc-departamento-card { display:flex; align-items:center; gap:14px; width:100%; min-height:94px; padding:16px; border:1px solid #e2c7a6; border-radius:8px; background:#fdfaf6!important; color:inherit; text-align:left; box-shadow:0 2px 8px rgba(91,55,20,.08); cursor:pointer; transition:transform .16s,box-shadow .16s,border-color .16s; }
    .cdc-departamento-card:hover { transform:translateY(-2px); border-color:#d79951; box-shadow:0 7px 16px rgba(91,55,20,.14); }
    .cdc-departamento-card:focus { outline:2px solid #d7832f; outline-offset:2px; }
    .cdc-departamento-folder { display:flex; align-items:center; justify-content:center; flex:0 0 48px; width:48px; height:48px; border-radius:7px; background:#f3dcc0; color:#c4690d; font-size:1.35rem; }
    .cdc-departamento-info { flex:1; min-width:0; }
    .cdc-departamento-info strong { display:block; color:#633300; font-size:1rem; overflow-wrap:anywhere; }
    .cdc-departamento-info small { display:block; margin-top:4px; color:#806e5b; font-size:.75rem; font-weight:700; }
    .cdc-departamento-enter { color:#a56a30; }
    .cdc-departamento-zonas-head { display:none; align-items:center; gap:12px; margin-bottom:14px; padding-bottom:12px; border-bottom:1px solid #ead9c8; }
    .cdc-departamento-zonas-title { flex:1; min-width:0; }
    .cdc-departamento-zonas-title strong { display:block; color:#633300; font-size:1.05rem; overflow-wrap:anywhere; }
    .cdc-departamento-zonas-title small { color:#806e5b; font-weight:700; }
    .cdc-zona-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:12px; }
    .cdc-zona-card { border:1px solid #ead9c8; border-radius:8px; background:#fff; box-shadow:0 2px 7px rgba(91,55,20,.08); cursor:pointer; transition:transform .16s,box-shadow .16s,border-color .16s; overflow:hidden; }
    .cdc-zona-card:hover { transform:translateY(-2px); box-shadow:0 7px 16px rgba(91,55,20,.14); border-color:#e6ad68; }
    .cdc-zona-card-head { padding:13px 14px 10px; display:flex; align-items:flex-start; gap:8px; border-bottom:1px solid #f2e5d6; }
    .cdc-zona-card-title { flex:1; min-width:0; }
    .cdc-zona-card-title strong { display:block; color:#633300; font-size:1rem; overflow-wrap:anywhere; }
    .cdc-zona-card-title small { color:#806e5b; }
    .cdc-zona-card-actions { display:flex; flex:0 0 auto; }
    .cdc-zona-card-body { padding:11px 14px 14px; }
    .cdc-zona-clientes-count { display:inline-flex; align-items:center; gap:5px; margin-top:10px; color:#6b5a48; font-size:.74rem; font-weight:700; }
    .cdc-zona-clientes-count strong { display:inline-flex; align-items:center; justify-content:center; min-width:24px; height:20px; padding:0 7px; border-radius:10px; background:#f3dcc0; color:#7d3f00; }
    .cdc-zona-label { display:block; color:#8a6a49; font-size:.65rem; font-weight:700; text-transform:uppercase; margin:6px 0 3px; }
    .cdc-zona-empty { grid-column:1/-1; text-align:center; color:#8b7a68; padding:38px 12px; border:1px dashed #dfc9ae; border-radius:8px; }
    .cdc-zona-detalle-head { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
    .cdc-zona-detalle-title { flex:1; min-width:210px; }
    .cdc-zona-detalle-title h4 { margin:0; color:#633300; font-size:1.05rem; }
    .cdc-zona-detalle-title small { color:#806e5b; }
    .cdc-zona-detalle-filtro { max-width:310px; }
    #tbl_cdc_zona_detalle td { vertical-align:middle; font-size:.82rem; }
    .cdc-cambio-usuarios { min-width:150px; }
    .cdc-decision-toolbar { display:flex; align-items:end; gap:8px; flex-wrap:wrap; padding:10px 12px; margin-bottom:10px; background:#fff8ee; border:1px solid #f2d49a; border-radius:6px; }
    .cdc-decision-toolbar .form-group { margin:0; min-width:220px; }
    .cdc-decision-toolbar label { display:block; margin-bottom:3px; color:#7d3f00; font-size:.68rem; font-weight:700; text-transform:uppercase; }
    .cdc-operacion-cliente { min-width:190px; font-size:.78rem; }
    #tbl_cdc_responsables_decisiones td { vertical-align:middle; font-size:.8rem; }
    #modalResponsablesZonaCdc .modal-dialog { width:calc(100% - 32px); max-width:1100px; }
    .cdc-zona-miembros { border:1px solid #ead9c8; border-radius:6px; max-height:240px; overflow:auto; }
    .cdc-zona-miembro { display:grid; grid-template-columns:minmax(180px,1fr) 150px 32px; gap:8px; align-items:center; padding:7px 10px; border-bottom:1px solid #f3ecdf; font-size:.8rem; }
    .cdc-zona-miembro:last-child { border-bottom:0; }
    .cdc-zona-vacia { padding:18px; text-align:center; color:#8b7a68; font-size:.82rem; }
    .badge-cdc-insert { background:#d4edda; color:#155724; }
    .badge-cdc-delete { background:#f8d7da; color:#721c24; }
    .select2-container--open { z-index:99999!important; }
    .swal2-container { z-index:99999!important; }
    #modalAsignacionCdc .modal-body, #modalAsignacionMasivaCdc .modal-body { max-height:calc(100vh - 210px); overflow-y:auto; }
    @media (max-width:575px) {
        .cdc-departamentos-grid, .cdc-zona-grid, .cdc-agrupado-grid { grid-template-columns:minmax(0,1fr); }
        .cdc-zona-grid { padding:8px; }
        .cdc-departamento-head { padding:10px; }
    }
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2><i class="fa fa-address-book mr-2" style="color:#e67e22"></i>Cartera de Clientes</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Flujo de Venta</li>
                <li class="breadcrumb-item active"><strong>Cartera de Clientes</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="cdc-card">

                    <div class="cdc-card-header">
                        <h5><i class="fa fa-address-book"></i> Cartera de Clientes</h5>
                        <div class="d-flex" style="gap:6px">
                            <button type="button" class="btn-cdc-action active" id="btn_vista_zonificacion" onclick="cdcCambiarVista('zonificacion')">
                                <i class="fa fa-map mr-1"></i>Zonificación de Clientes
                            </button>
                            <button type="button" class="btn-cdc-action" id="btn_vista_individual" onclick="cdcCambiarVista('individual')">
                                <i class="fa fa-list mr-1"></i>Individual
                            </button>
                            <button type="button" class="btn-cdc-action" id="btn_vista_municipio" onclick="cdcCambiarVista('municipio')">
                                <i class="fa fa-map-marker mr-1"></i>Por Municipio
                            </button>
                            <button type="button" class="btn-cdc-action" id="btn_vista_departamento" onclick="cdcCambiarVista('departamento')">
                                <i class="fa fa-globe mr-1"></i>Por Departamento
                            </button>
                        </div>
                    </div>

                    <div class="cdc-filtros-bar" id="cdc_filtros_clientes" style="display:none;">
                        <div class="form-group">
                            <label>Nombre del cliente</label>
                            <input type="text" id="cdc_fil_nombre" class="form-control form-control-sm" placeholder="Buscar por nombre...">
                        </div>
                        <div class="form-group">
                            <label>Asesor Comercial</label>
                            <select id="cdc_fil_asesor" class="form-control form-control-sm" style="width:100%"></select>
                        </div>
                        <div class="form-group">
                            <label>Tele Asesor</label>
                            <select id="cdc_fil_teleasesor" class="form-control form-control-sm" style="width:100%"></select>
                        </div>
                        <div class="form-group" style="min-width:140px;flex:0 0 140px;">
                            <label>Estado</label>
                            <select id="cdc_fil_estado" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="1">Activo</option>
                                <option value="2">Inactivo</option>
                            </select>
                        </div>
                        <div class="form-group" style="min-width:170px;flex:0 0 170px;">
                            <label>&nbsp;</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="cdc_fil_sin_asignar">
                                <label class="custom-control-label" for="cdc_fil_sin_asignar" style="font-size:.78rem;font-weight:500;text-transform:none;">Solo sin asignar</label>
                            </div>
                        </div>
                        <div class="form-group" style="flex:0 0 auto;">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcAplicarFiltros()"><i class="fa fa-search mr-1"></i>Buscar</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cdcLimpiarFiltros()"><i class="fa fa-eraser mr-1"></i>Limpiar</button>
                                <button type="button" class="btn btn-outline-warning btn-sm" id="cdc_btn_seleccionar_filtrados" onclick="cdcAlternarSeleccionFiltrada()" style="display:none;"><i class="fa fa-check-double mr-1"></i><span>Seleccionar resultados</span></button>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="cdcDescargarExcel()"><i class="fa fa-file-excel mr-1"></i>Excel</button>
                            </div>
                        </div>
                    </div>

                    <div class="cdc-seleccion-bar" id="cdc_seleccion_bar">
                        <span class="cdc-seleccion-count"><i class="fa fa-check-square mr-1"></i><span id="cdc_seleccion_count">0</span> cliente(s) seleccionado(s)</span>
                        <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcAbrirAsignacionMasiva()"><i class="fa fa-user-tag mr-1"></i>Asignar en lote</button>
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="cdcAbrirAgregarZona()"><i class="fa fa-map-marker mr-1"></i>Agregar a Zona</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cdcAbrirHistorialMasivo()"><i class="fa fa-history mr-1"></i>Ver historial</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cdcLimpiarSeleccion()"><i class="fa fa-times mr-1"></i>Quitar selección</button>
                    </div>

                    <div class="cdc-card-body">

                        {{-- Vista Zonificación --}}
                        <div id="cdc_vista_zonificacion">
                            <div id="cdc_zona_cards_wrap">
                                <div class="cdc-zona-toolbar">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="cdc_zona_buscar" class="form-control" placeholder="Buscar zona o departamento...">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="cdcCargarZonas()"><i class="fa fa-search"></i></button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="cdcDescargarExcel()"><i class="fa fa-file-excel mr-1"></i>Excel</button>
                                    <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcNuevaZona()"><i class="fa fa-plus mr-1"></i>Nueva Zona</button>
                                </div>
                                <div id="cdc_departamento_zonas_head" class="cdc-departamento-zonas-head">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cdcVolverDepartamentos()" title="Volver a departamentos"><i class="fa fa-arrow-left"></i></button>
                                    <span class="cdc-departamento-folder"><i class="fa fa-folder-open"></i></span>
                                    <div class="cdc-departamento-zonas-title"><strong id="cdc_departamento_zonas_nombre"></strong><small id="cdc_departamento_zonas_count"></small></div>
                                </div>
                                <div id="cdc_zona_grid" class="cdc-departamentos-grid"></div>
                            </div>

                            <div id="cdc_zona_detalle_wrap" style="display:none;">
                                <div class="cdc-zona-detalle-head">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cdcCerrarDetalleZona()" title="Volver a zonas"><i class="fa fa-arrow-left"></i></button>
                                    <div class="cdc-zona-detalle-title"><h4 id="cdc_zona_detalle_nombre"></h4><small id="cdc_zona_detalle_departamento"></small></div>
                                    <div class="input-group input-group-sm cdc-zona-detalle-filtro">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-search"></i></span></div>
                                        <input type="search" id="cdc_zona_detalle_filtro" class="form-control" placeholder="Buscar cliente...">
                                    </div>
                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="cdcDescargarExcel()"><i class="fa fa-file-excel mr-1"></i>Excel</button>
                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="cdcEditarZona(cdcZonaActivaId)"><i class="fa fa-user-plus mr-1"></i>Administrar zona</button>
                                </div>
                                <div style="overflow-x:auto;">
                                    <table id="tbl_cdc_zona_detalle" class="table table-sm table-bordered table-hover mb-0" style="width:100%;">
                                        <thead><tr><th style="width:30px"><input type="checkbox" id="cdc_zona_chk_all"></th><th>Cliente</th><th>Ubicación</th><th>Asesores Comerciales</th><th>Teleasesores</th><th>Estado</th><th>Acciones</th></tr></thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Vista Individual --}}
                        <div id="cdc_vista_individual" style="display:none;">
                            <div style="overflow-x:auto;">
                                <table id="tbl_cdc" class="table table-hover table-bordered" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th style="width:30px;"><input type="checkbox" id="cdc_chk_all"></th>
                                            <th>Cliente</th>
                                            <th>Ubicación</th>
                                            <th>Asesores Comerciales</th>
                                            <th>Teleasesores</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Vista Agrupada (Municipio/Departamento) --}}
                        <div id="cdc_vista_agrupada" style="display:none;">
                            <div id="cdc_agrupado_head" class="cdc-agrupado-head">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cdcVolverAgrupado()" title="Volver"><i class="fa fa-arrow-left"></i></button>
                                <span class="cdc-departamento-folder"><i id="cdc_agrupado_head_icon" class="fa fa-folder-open"></i></span>
                                <div class="cdc-agrupado-head-title"><strong id="cdc_agrupado_head_nombre"></strong><small id="cdc_agrupado_head_subtitulo"></small></div>
                            </div>
                            <div id="cdc_agrupado_grid" class="cdc-agrupado-grid"></div>
                            <div id="cdc_agrupado_clientes_wrap" style="display:none;overflow-x:auto;">
                                <table id="tbl_cdc_agrupado_clientes" class="table table-hover table-bordered" style="width:100%;">
                                    <thead><tr><th style="width:30px"><input type="checkbox" id="cdc_agrupado_chk_all" title="Seleccionar todos los clientes filtrados"></th><th>Cliente</th><th>Ubicación</th><th>Asesores Comerciales</th><th>Teleasesores</th><th>Estado</th><th>Acciones</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Modal Asignación Individual ══ --}}
    <div class="modal fade" id="modalAsignacionCdc" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-cdc">
                    <h5 class="modal-title"><i class="fa fa-user-tag mr-2"></i>Editar Asignación — <span id="cdc_asig_nombre_cliente"></span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cdc_asig_cliente_id">

                    <p class="cdc-modal-section"><i class="fa fa-briefcase mr-1"></i>Asesores Comerciales</p>
                    <div class="cdc-tipo-box">
                        <div class="cdc-buscar-agregar">
                            <select id="cdc_asig_buscar_asesores" class="form-control"></select>
                            <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcAgregarUsuario('asesores')"><i class="fa fa-plus mr-1"></i>Agregar</button>
                        </div>
                        <div class="cdc-lista-header">
                            <span>Usuarios asignados</span>
                            <button type="button" class="btn btn-outline-danger btn-sm py-0" onclick="cdcEliminarTodos('asesores')"><i class="fa fa-trash mr-1"></i>Eliminar todos</button>
                        </div>
                        <div id="cdc_asig_lista_asesores" class="cdc-lista-asignados"></div>
                    </div>

                    <p class="cdc-modal-section"><i class="fa fa-headset mr-1"></i>Teleasesores</p>
                    <div class="cdc-tipo-box">
                        <div class="cdc-buscar-agregar">
                            <select id="cdc_asig_buscar_teleasesores" class="form-control"></select>
                            <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcAgregarUsuario('teleasesores')"><i class="fa fa-plus mr-1"></i>Agregar</button>
                        </div>
                        <div class="cdc-lista-header">
                            <span>Usuarios asignados</span>
                            <button type="button" class="btn btn-outline-danger btn-sm py-0" onclick="cdcEliminarTodos('teleasesores')"><i class="fa fa-trash mr-1"></i>Eliminar todos</button>
                        </div>
                        <div id="cdc_asig_lista_teleasesores" class="cdc-lista-asignados"></div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcGuardarAsignacion()"><i class="fa fa-save mr-1"></i>Guardar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Modal Asignación Masiva ══ --}}
    <div class="modal fade" id="modalAsignacionMasivaCdc" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-cdc">
                    <h5 class="modal-title"><i class="fa fa-users mr-2"></i>Asignación Masiva (<span id="cdc_asig_masiva_count">0</span> clientes)</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">

                    <p class="cdc-modal-section"><i class="fa fa-briefcase mr-1"></i>Asesores Comerciales</p>
                    <div class="cdc-tipo-box">
                        <div class="form-group">
                            <select id="cdc_masiva_asesores" class="form-control" multiple style="width:100%"></select>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cdc_masiva_modo_asesores" id="cdc_masiva_modo_asesores_sin" value="sin_cambios" checked>
                            <label class="form-check-label" for="cdc_masiva_modo_asesores_sin">No modificar</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cdc_masiva_modo_asesores" id="cdc_masiva_modo_asesores_agregar" value="agregar">
                            <label class="form-check-label" for="cdc_masiva_modo_asesores_agregar">Agregar a los actuales</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cdc_masiva_modo_asesores" id="cdc_masiva_modo_asesores_reemplazar" value="reemplazar">
                            <label class="form-check-label" for="cdc_masiva_modo_asesores_reemplazar">Reemplazar por estos</label>
                        </div>
                    </div>

                    <p class="cdc-modal-section"><i class="fa fa-headset mr-1"></i>Teleasesores</p>
                    <div class="cdc-tipo-box">
                        <div class="form-group">
                            <select id="cdc_masiva_teleasesores" class="form-control" multiple style="width:100%"></select>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cdc_masiva_modo_teleasesores" id="cdc_masiva_modo_tele_sin" value="sin_cambios" checked>
                            <label class="form-check-label" for="cdc_masiva_modo_tele_sin">No modificar</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cdc_masiva_modo_teleasesores" id="cdc_masiva_modo_tele_agregar" value="agregar">
                            <label class="form-check-label" for="cdc_masiva_modo_tele_agregar">Agregar a los actuales</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cdc_masiva_modo_teleasesores" id="cdc_masiva_modo_tele_reemplazar" value="reemplazar">
                            <label class="form-check-label" for="cdc_masiva_modo_tele_reemplazar">Reemplazar por estos</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcGuardarAsignacionMasiva()"><i class="fa fa-save mr-1"></i>Aplicar a todos</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Modal Historial Individual ══ --}}
    <div class="modal fade" id="modalHistorialCdc" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-cdc">
                    <h5 class="modal-title"><i class="fa fa-history mr-2"></i>Historial — <span id="cdc_hist_nombre_cliente"></span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div style="overflow-x:auto;max-height:60vh;">
                        <table id="tbl_cdc_historial" class="table table-sm table-bordered">
                            <thead>
                                <tr><th>Fecha</th><th>Tipo</th><th>Acción</th><th>Persona</th><th>Realizado por</th><th>Comentario</th></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Modal Historial Masivo ══ --}}
    <div class="modal fade" id="modalHistorialMasivoCdc" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-cdc">
                    <h5 class="modal-title"><i class="fa fa-history mr-2"></i>Historial de clientes seleccionados</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div style="overflow-x:auto;max-height:60vh;">
                        <table id="tbl_cdc_historial_masivo" class="table table-sm table-bordered">
                            <thead>
                                <tr><th>Fecha</th><th>Cliente</th><th>Tipo</th><th>Acción</th><th>Persona</th><th>Realizado por</th></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Crear/Editar Zona --}}
    <div class="modal fade" id="modalZonaCdc" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-cdc">
                    <h5 class="modal-title"><i class="fa fa-map mr-2"></i><span id="cdc_zona_modal_titulo">Nueva Zona</span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cdc_zona_id">
                    <div class="form-row">
                        <div class="form-group col-md-5"><label>Departamento *</label><select id="cdc_zona_departamento" class="form-control"></select></div>
                        <div class="form-group col-md-5"><label>Nombre de la zona *</label><input id="cdc_zona_nombre" class="form-control" maxlength="120" placeholder="Ej. Zona Norte"></div>
                        <div class="form-group col-md-2"><label>Estado</label><select id="cdc_zona_activo" class="form-control"><option value="1">Activo</option><option value="0">Inactivo</option></select></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>Asesores Comerciales</label><select id="cdc_zona_asesor" class="form-control" multiple style="width:100%"></select></div>
                        <div class="form-group col-md-6"><label>Teleasesores</label><select id="cdc_zona_teleasesor" class="form-control" multiple style="width:100%"></select></div>
                    </div>
                    <div class="form-group"><label>Observaciones</label><textarea id="cdc_zona_observaciones" class="form-control" rows="2" maxlength="1000"></textarea></div>

                    <div id="cdc_zona_clientes_wrap" style="display:none;">
                        <p class="cdc-modal-section"><i class="fa fa-users mr-1"></i>Clientes de la zona</p>
                        <div class="cdc-buscar-agregar">
                            <select id="cdc_zona_buscar_cliente" class="form-control" style="width:100%"></select>
                            <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcAgregarClienteDesdeZona()"><i class="fa fa-plus mr-1"></i>Agregar</button>
                        </div>
                        <div class="input-group input-group-sm mb-2">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-search"></i></span></div>
                            <input type="search" id="cdc_zona_miembros_filtro" class="form-control" placeholder="Filtrar clientes de la zona..." oninput="cdcRenderMiembrosZona()">
                        </div>
                        <div id="cdc_zona_miembros" class="cdc-zona-miembros"></div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-cdc-primary btn-sm" onclick="cdcGuardarZona()"><i class="fa fa-save mr-1"></i>Guardar Zona</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Agregar selección a Zona --}}
    <div class="modal fade" id="modalAgregarZonaCdc" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-cdc"><h5 class="modal-title"><i class="fa fa-map-marker mr-2"></i>Agregar a Zona</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <p class="text-muted small"><span id="cdc_agregar_zona_count">0</span> cliente(s) seleccionados heredarán los responsables configurados en la zona.</p>
                    <div class="form-group mb-0"><label>Zona destino *</label><select id="cdc_agregar_zona_id" class="form-control"></select></div>
                </div>
                <div class="modal-footer py-2"><button class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button><button class="btn btn-cdc-primary btn-sm" onclick="cdcConfirmarAgregarZona(false)">Confirmar</button></div>
            </div>
        </div>
    </div>

    {{-- Modal Vista Previa de Cambios de Zona --}}
    <div class="modal fade" id="modalCambiosZonaCdc" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-cdc"><h5 class="modal-title"><i class="fa fa-exchange-alt mr-2"></i>Confirmar cambios de asignación</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <p class="small text-muted">Al ingresar a <strong id="cdc_cambios_zona_nombre"></strong>, las asignaciones actuales se reemplazarán por los responsables de esa zona.</p>
                    <div style="overflow:auto;max-height:58vh;"><table id="tbl_cdc_zona_cambios" class="table table-sm table-bordered mb-0"><thead><tr><th>Cliente</th><th>Asesores actuales</th><th>Nuevos asesores</th><th>Teleasesores actuales</th><th>Nuevos teleasesores</th></tr></thead><tbody></tbody></table></div>
                </div>
                <div class="modal-footer py-2"><button class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button><button class="btn btn-cdc-primary btn-sm" id="cdc_confirmar_cambios_zona"><i class="fa fa-check mr-1"></i>Confirmar reemplazo</button></div>
            </div>
        </div>
    </div>

    {{-- Modal Decisiones de Responsables por Cliente --}}
    <div class="modal fade" id="modalResponsablesZonaCdc" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-cdc"><h5 class="modal-title"><i class="fa fa-users-cog mr-2"></i>Aplicar nuevos actores — <span id="cdc_responsables_zona_nombre"></span></h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <p class="small text-muted">Seleccione una operación para cada cliente con actores diferentes. Puede marcar varias filas y asignarles la misma operación sin afectar las demás.</p>
                    <div class="cdc-decision-toolbar">
                        <div class="form-group"><label>Operación para seleccionados</label><select id="cdc_responsables_operacion_masiva" class="form-control form-control-sm"><option value="no_modificar">No modificar</option><option value="reemplazar">Reemplazar por actores de zona</option><option value="agregar">Agregar a los actores actuales</option></select></div>
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="cdcAplicarOperacionResponsablesSeleccionados()"><i class="fa fa-check-double mr-1"></i>Aplicar a seleccionados</button>
                        <span class="small text-muted ml-auto"><strong id="cdc_responsables_total_clientes">0</strong> clientes con diferencias</span>
                    </div>
                    <div style="overflow:auto;max-height:56vh;">
                        <table id="tbl_cdc_responsables_decisiones" class="table table-sm table-bordered mb-0">
                            <thead><tr><th style="width:34px"><input type="checkbox" id="cdc_responsables_chk_all"></th><th>Cliente</th><th>Asesores actuales</th><th>Teleasesores actuales</th><th>Nuevos asesores</th><th>Nuevos teleasesores</th><th>Operación</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer py-2"><button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button><button type="button" class="btn btn-cdc-primary btn-sm" id="cdc_guardar_decisiones_responsables" onclick="cdcConfirmarDecisionesResponsables()"><i class="fa fa-save mr-1"></i>Guardar zona y aplicar</button></div>
            </div>
        </div>
    </div>

    {{-- Modal Historial de Zona --}}
    <div class="modal fade" id="modalHistorialZonaCdc" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content">
            <div class="modal-header modal-header-cdc"><h5 class="modal-title"><i class="fa fa-history mr-2"></i>Bitácora de Zona</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body" style="max-height:65vh;overflow:auto;"><table id="tbl_cdc_zona_historial" class="table table-sm table-bordered"><thead><tr><th>Fecha</th><th>Acción</th><th>Cliente</th><th>Usuario</th><th>Detalle</th></tr></thead><tbody></tbody></table></div>
        </div></div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/js_proyecto/flujodeventa/cartera-de-clientes.js') }}"></script>
<script>
    window.CDC_ROUTES = {
        listar: '{{ route('cartera_clientes.listar') }}',
        listarIds: '{{ route('cartera_clientes.listar_ids') }}',
        exportarExcel: '{{ route('cartera_clientes.exportar_excel') }}',
        agrupado: '{{ route('cartera_clientes.agrupado') }}',
        usuarios: '{{ route('cartera_clientes.usuarios') }}',
        datos: '{{ url('/flujo_de_venta/cartera_de_clientes/datos') }}',
        historial: '{{ url('/flujo_de_venta/cartera_de_clientes/historial') }}',
        historialMasivo: '{{ route('cartera_clientes.historial_masivo') }}',
        asignar: '{{ route('cartera_clientes.asignar') }}',
        asignarMasivo: '{{ route('cartera_clientes.asignar_masivo') }}',
        zonas: '{{ route('cartera_clientes.zonas') }}',
        zonasCatalogos: '{{ route('cartera_clientes.zonas_catalogos') }}',
        zonaDatos: '{{ url('/flujo_de_venta/cartera_de_clientes/zona') }}',
        zonaBuscarClientes: '{{ route('cartera_clientes.zona_clientes_buscar') }}',
        zonaGuardar: '{{ route('cartera_clientes.zona_guardar') }}',
        zonaAsignarClientes: '{{ route('cartera_clientes.zona_asignar_clientes') }}',
        zonaQuitarCliente: '{{ route('cartera_clientes.zona_quitar_cliente') }}',
        zonaHistorial: '{{ url('/flujo_de_venta/cartera_de_clientes/zona-historial') }}',
    };
    $(document).ready(function () {
        cdcInit();
    });
</script>
@endpush

