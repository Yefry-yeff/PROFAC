<div class="compras-reporte">
    @push('styles')
    <style>
        .compras-reporte .compras-header { background:linear-gradient(135deg,#f39c12,#e05a00); padding:22px 28px; color:#fff; }
        .compras-reporte .compras-header h2 { color:#fff; margin:0 0 5px; font-weight:700; }
        .compras-reporte .compras-header p { margin:0; }
        .compras-reporte .compras-header .btn { color:#52636a; }
        .compras-reporte .compras-panel { padding:20px; background:#fff; border:1px solid #e3e8ea; border-radius:8px; box-shadow:0 2px 10px rgba(38,50,56,.05); }
        .compras-reporte .compras-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:18px; }
        .compras-reporte .compras-excel { background:#1e8449; border-color:#1e8449; color:#fff; }
        .compras-reporte .compras-buscar { background:#e8751a; border-color:#e8751a; color:#fff; }
        .compras-reporte table thead th { background:#f3f6f7; color:#52636a; border-bottom:2px solid #dce4e6; }
        .compras-reporte table td { vertical-align:middle; }
        #modalFiltrosCompras .modal-header { background:linear-gradient(135deg,#f39c12,#e05a00); }
        #modalFiltrosCompras .modal-title,#modalFiltrosCompras .close { color:#fff; }
        #modalFiltrosCompras .modal-body { background:#fdfaf6; }
        #modalFiltrosCompras .select2-container { width:100% !important; }
    </style>
    @endpush
    <div class="compras-header d-flex justify-content-between align-items-center flex-wrap">
        <div><h2><i class="fa fa-shopping-cart mr-2"></i>Reporte de compras</h2><p>Inventario / Compras</p></div>
        <a href="{{ url('/producto/compra') }}" class="btn btn-white"><i class="fa fa-plus mr-1"></i>Nueva compra</a>
    </div>
    <div class="wrapper wrapper-content">
        <div class="compras-panel">
            <div class="compras-actions">
                <button type="button" class="btn btn-white" data-toggle="modal" data-target="#modalFiltrosCompras"><i class="fa fa-filter mr-1"></i>Filtros</button>
                <span id="resumenFiltrosCompras" class="text-muted" aria-live="polite"></span>
                <a id="exportarComprasExcel" class="btn compras-excel ml-auto"
                    href="{{ url('/compras/excel_mes/'.date('Y-m')) }}"><i class="fa fa-file-excel mr-1"></i>Exportar Excel</a>
            </div>
            <div id="errorReporteCompras" class="alert alert-danger d-none" role="alert"></div>
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
        <div class="modal fade" id="modalFiltrosCompras" tabindex="-1" role="dialog" aria-labelledby="tituloFiltrosCompras" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <form id="filtrosComprasForm" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tituloFiltrosCompras"><i class="fa fa-filter mr-2"></i>Filtros de búsqueda</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div id="errorFiltrosCompras" class="alert alert-danger d-none" role="alert"></div>
                        <p class="font-weight-bold">Fecha de emisión</p>
                        <div class="row">
                            <div class="col-md-6 form-group"><label for="comprasDesde">Desde</label>
                                <input id="comprasDesde" type="date" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}" required></div>
                            <div class="col-md-6 form-group"><label for="comprasHasta">Hasta</label>
                                <input id="comprasHasta" type="date" class="form-control" value="{{ now()->endOfMonth()->toDateString() }}" required></div>
                            <div class="col-md-6 form-group"><label for="comprasFactura">Número de factura</label>
                                <input id="comprasFactura" type="text" class="form-control" maxlength="255"></div>
                            <div class="col-md-6 form-group"><label for="comprasProveedor">Nombre de proveedor</label>
                                <select id="comprasProveedor" class="form-control">
                                    <option value="">Todos los proveedores</option>
                                    @foreach ($proveedores as $proveedor)<option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="limpiarFiltrosCompras" type="button" class="btn btn-white"><i class="fa fa-eraser mr-1"></i>Limpiar</button>
                        <button type="submit" class="btn compras-buscar"><i class="fa fa-search mr-1"></i>Buscar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="{{ asset('js/js_proyecto/inventario/listar-compras.js') }}?v={{ filemtime(public_path('js/js_proyecto/inventario/listar-compras.js')) }}"></script>
    @endpush
</div>
