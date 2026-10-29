<div class="compras-reporte">
    @push('styles')
    <style>
        .compras-reporte .compras-header { background:#fff; padding:22px 28px; color:#676a6c; border-bottom:1px solid #e7eaec; box-shadow:0 1px 3px rgba(0,0,0,.04); }
        .compras-reporte .compras-header h2 { color:#343a40; margin:0 0 5px; font-weight:700; }
        .compras-reporte .compras-header p { margin:0; color:#889198; font-size:.82rem; }
        .compras-reporte .reporte-selector { width:auto; min-width:210px; height:40px; padding:0 36px 0 13px; color:#69421f; background-color:#fff; border:1px solid #ead9c8; border-radius:7px; font-size:.84rem; font-weight:600; box-shadow:0 2px 6px rgba(125,63,0,.08); cursor:pointer; transition:border-color .18s, box-shadow .18s; }
        .compras-reporte .reporte-selector:hover,.compras-reporte .reporte-selector:focus { border-color:#e67e22; box-shadow:0 0 0 .18rem rgba(230,126,34,.16); outline:0; }
        .compras-reporte .reporte-create-button { display:inline-flex; align-items:center; gap:7px; padding:10px 16px; color:#fff!important; background:linear-gradient(135deg,#f39c12 0%,#e05a00 100%); border:0; border-radius:7px; font-size:.82rem; font-weight:700; box-shadow:0 3px 8px rgba(224,90,0,.22); transition:transform .18s, box-shadow .18s, filter .18s; }
        .compras-reporte .reporte-create-button:hover,.compras-reporte .reporte-create-button:focus { color:#fff!important; filter:brightness(.96); transform:translateY(-1px); box-shadow:0 5px 12px rgba(224,90,0,.28); }
        .compras-reporte .compras-panel { padding:0; background:#fff; border:1px solid #e8d5bf; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.10); overflow:hidden; }
        .compras-reporte .compras-actions { display:flex; align-items:center; justify-content:flex-end; gap:8px; flex-wrap:wrap; padding:12px 20px; background:linear-gradient(135deg,#f39c12 0%,#e05a00 100%); }
        .compras-reporte .btn-compras-action { background:rgba(255,255,255,.18)!important; color:#fff!important; border:1.5px solid rgba(255,255,255,.5)!important; border-radius:5px!important; font-weight:600!important; font-size:.78rem; padding:5px 14px; transition:background .18s; white-space:nowrap; cursor:pointer; }
        .compras-reporte .btn-compras-action:hover { background:rgba(255,255,255,.30)!important; color:#fff!important; }
        .compras-reporte .compras-filtros-bar { display:none; padding:8px 16px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; align-items:center; gap:6px; flex-wrap:wrap; font-size:.78rem; }
        .compras-reporte .compras-filtro-badge { display:inline-flex; align-items:center; gap:5px; background:#fff8ee; border:1px solid #f2d49a; border-radius:12px; padding:2px 10px; font-size:.75rem; color:#7d3f00; }
        .compras-reporte .compras-tabla { padding:16px 20px 20px; }
        .compras-reporte table thead th { background:#f3f6f7; color:#52636a; border-bottom:2px solid #dce4e6; }
        .compras-reporte table td { vertical-align:middle; }
        #modalFiltrosCompras .modal-header { background:linear-gradient(135deg,#f39c12 0%,#e05a00 100%); color:#fff; border-radius:8px 8px 0 0; padding:14px 20px; }
        #modalFiltrosCompras .modal-title { color:#fff; font-size:.95rem; font-weight:700; }
        #modalFiltrosCompras .close { color:#fff; opacity:.8; text-shadow:none; font-size:1.4rem; }
        #modalFiltrosCompras .modal-body { background:#fdfaf6; padding:18px 20px 8px; }
        #modalFiltrosCompras .modal-footer { background:#f8f4ef; border-top:1px solid #ead9c8; padding:10px 20px; }
        #modalFiltrosCompras .form-group label { font-size:.78rem; font-weight:600; color:#555; margin-bottom:3px; }
        #modalFiltrosCompras .form-control { border-color:#ddd; border-radius:5px; font-size:.82rem; }
        #modalFiltrosCompras .form-control:focus { border-color:#e67e22; box-shadow:0 0 0 .15rem rgba(230,126,34,.18); }
        #modalFiltrosCompras .select2-container { width:100% !important; }
        .compras-reporte .modal-section-label { font-size:.68rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#e67e22; border-bottom:2px solid #fdebd0; padding-bottom:5px; margin-bottom:14px; margin-top:6px; display:flex; align-items:center; gap:5px; }
        .compras-reporte .modal-filter-grid { background:#fff; border:1px solid #ead9c8; border-radius:7px; padding:14px 16px 6px; margin-bottom:14px; }
        .compras-reporte .date-input-icon { position:relative; }
        .compras-reporte .date-input-icon i { position:absolute; left:9px; top:50%; transform:translateY(-50%); color:#aaa; font-size:.78rem; pointer-events:none; }
        .compras-reporte .date-input-icon input { padding-left:28px; }
    </style>
    @endpush
    <div class="compras-header d-flex justify-content-between align-items-center flex-wrap">
        <div><h2><i class="fa fa-shopping-cart mr-2"></i>Reporte de compras</h2><p>Inventario / Compras</p></div>
        <div class="d-flex align-items-center flex-wrap" style="gap:8px">
            <label for="selectorReporteInventario" class="sr-only">Seleccionar reporte</label>
            <select id="selectorReporteInventario" class="form-control reporte-selector" onchange="if (this.value) window.location.href = this.value">
                <option value="{{ url('/producto/listar/compras') }}" selected>Reporte de compras</option>
                <option value="{{ route('gastos') }}">Reporte de gastos</option>
            </select>
            <a href="{{ url('/producto/compra') }}" class="btn reporte-create-button"><i class="fa fa-plus"></i>Nueva compra</a>
        </div>
    </div>
    <div class="wrapper wrapper-content">
        <div class="compras-panel">
            <div class="compras-actions">
                <a id="exportarComprasExcel" class="btn-compras-action"
                    href="{{ url('/compras/excel_mes/'.date('Y-m')) }}"><i class="fa fa-file-excel-o mr-1"></i>Excel</a>
                <button type="button" class="btn-compras-action" data-toggle="modal" data-target="#modalFiltrosCompras"><i class="fa fa-filter mr-1"></i>Filtros</button>
            </div>
            <div id="resumenFiltrosCompras" class="compras-filtros-bar" aria-live="polite"></div>
            <div id="errorReporteCompras" class="alert alert-danger d-none" role="alert"></div>
            <div class="compras-tabla">
                <div class="table-responsive">
                    <table id="tbl_listar_compras" class="table table-striped table-bordered table-hover">
                        <thead><tr>
                            <th>Código interno</th><th>N° Compra</th><th>N° Factura</th><th>Emisión</th>
                            <th>Fecha de vencimiento</th><th>Total de compra</th><th>Retención</th>
                            <th>Proveedor</th><th>Registrado por</th><th>Fecha de registro</th><th>Anular</th><th>Opciones</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalFiltrosCompras" tabindex="-1" role="dialog" aria-labelledby="tituloFiltrosCompras" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <form id="filtrosComprasForm" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tituloFiltrosCompras"><i class="fa fa-filter mr-2"></i>Filtros de Búsqueda</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body pb-2">
                        <div id="errorFiltrosCompras" class="alert alert-danger d-none" role="alert"></div>
                        <p class="modal-section-label"><i class="fa fa-calendar"></i>Rango de fechas</p>
                        <div class="modal-filter-grid">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="comprasDesde">Desde</label>
                                        <div class="date-input-icon"><i class="fa fa-calendar-o"></i>
                                            <input id="comprasDesde" type="date" class="form-control form-control-sm" value="{{ now()->startOfMonth()->toDateString() }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="comprasHasta">Hasta</label>
                                        <div class="date-input-icon"><i class="fa fa-calendar-o"></i>
                                            <input id="comprasHasta" type="date" class="form-control form-control-sm" value="{{ now()->endOfMonth()->toDateString() }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="modal-section-label"><i class="fa fa-search"></i>Criterios de búsqueda</p>
                        <div class="modal-filter-grid">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="comprasFactura">Número de factura</label>
                                        <input id="comprasFactura" type="text" class="form-control form-control-sm" maxlength="255">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="comprasProveedor">Proveedor</label>
                                        <select id="comprasProveedor" class="form-control form-control-sm">
                                            <option value="">— Todos —</option>
                                            @foreach ($proveedores as $proveedor)<option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button id="limpiarFiltrosCompras" type="button" class="btn btn-outline-secondary btn-sm"><i class="fa fa-eraser mr-1"></i>Limpiar filtros</button>
                        <button type="submit" class="btn btn-sm" style="background:linear-gradient(135deg,#f39c12,#e05a00);color:#fff;border:none;font-weight:600;padding:6px 20px;border-radius:5px"><i class="fa fa-search mr-1"></i>Buscar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="{{ asset('js/js_proyecto/inventario/listar-compras.js') }}?v={{ filemtime(public_path('js/js_proyecto/inventario/listar-compras.js')) }}"></script>
    @endpush
</div>
