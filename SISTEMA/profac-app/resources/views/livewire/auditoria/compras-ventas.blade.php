<div>
    @push('styles')
    <style>
        .acv-card { border: 1px solid #e8d5bf; border-radius: 8px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.10); }
        .acv-header { padding: 11px 16px; color: #fff; background: linear-gradient(135deg,#f39c12,#e05a00); border-radius: 8px 8px 0 0; }
        .acv-header h5 { margin: 0; font-size: .85rem; font-weight: 700; text-transform: uppercase; }
        .acv-body { padding: 15px; }
        .acv-kpi { border-left: 4px solid #e67e22; padding: 10px 14px; background: #fff8ee; border-radius: 5px; }
        .acv-kpi small { display: block; color: #7d5b38; text-transform: uppercase; font-weight: 700; }
        .acv-kpi strong { font-size: 1.15rem; color: #7d3f00; }
        #tbl_auditoria_cv th { white-space: nowrap; font-size: .69rem; text-transform: uppercase; background: #fdf4e7; color: #7d3f00; }
        #tbl_auditoria_cv td { white-space: nowrap; font-size: .78rem; }
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-12">
            <h2><i class="fa fa-balance-scale mr-2" style="color:#e67e22"></i>Auditoría de Compras y Ventas</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Auditoría Compras y Ventas</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="alert alert-info">
            <strong>Alcance actual:</strong> exclusivamente líneas de compra y venta de los
            <strong>{{ count(\App\Services\Auditoria\ConsultaComprasVentas::PRODUCTOS_AUDITADOS) }} productos seleccionados</strong>.
            No se incluyen otros productos de las facturas.
            La clasificación usa el tipo de venta: <strong>corporativo = B</strong> y <strong>estatal = A</strong>.
            Cada compra se consolida por documento y producto; las ventas permanecen desglosadas por factura y lote.
            Los movimientos se presentan en secuencia cronológica, del más antiguo al más reciente.
        </div>
        <div class="acv-card mb-3">
            <div class="acv-header d-flex justify-content-between align-items-center">
                <h5><i class="fa fa-filter mr-1"></i> Filtros</h5>
                <div>
                    <button id="btnAcvSincronizar" class="btn btn-sm btn-light"><i class="fa fa-refresh mr-1"></i>Sincronizar</button>
                    <button id="btnAcvExportar" class="btn btn-sm btn-light"><i class="fa fa-file-excel-o mr-1"></i>CSV completo (Excel)</button>
                </div>
            </div>
            <div class="acv-body">
                <div class="row">
                    <div class="col-md-2"><label>Desde</label><input id="acvDesde" type="date" class="form-control form-control-sm"></div>
                    <div class="col-md-2"><label>Hasta</label><input id="acvHasta" type="date" class="form-control form-control-sm"></div>
                    <div class="col-md-2"><label>Movimiento</label><select id="acvTipo" class="form-control form-control-sm"><option value="">Todos</option><option>COMPRA</option><option>VENTA</option></select></div>
                    <div class="col-md-2"><label>Clasificación</label><select id="acvClasificacion" class="form-control form-control-sm"><option value="">Todas</option><option>A</option><option>B</option></select></div>
                    <div class="col-md-2"><label>Estado</label><select id="acvEstado" class="form-control form-control-sm"><option value="">Todos</option><option>ACTIVO</option><option>ANULADO</option></select></div>
                    <div class="col-md-2"><label>Lote</label><input id="acvLote" class="form-control form-control-sm" placeholder="ID lote"></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-5"><label>Producto</label><input id="acvProducto" class="form-control form-control-sm" placeholder="ID o nombre"></div>
                    <div class="col-md-5"><label>Documento</label><input id="acvDocumento" class="form-control form-control-sm" placeholder="Factura ID, compra ID, número o CAI"></div>
                    <div class="col-md-2 d-flex align-items-end"><button id="btnAcvBuscar" class="btn btn-primary btn-sm btn-block"><i class="fa fa-search mr-1"></i>Consultar</button></div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3"><div class="acv-kpi"><small>Compras (documentos)</small><strong id="acvKpiCompras">0</strong></div></div>
            <div class="col-md-3"><div class="acv-kpi"><small>Ventas (facturas)</small><strong id="acvKpiVentas">0</strong></div></div>
            <div class="col-md-3"><div class="acv-kpi"><small>Líneas de compra consolidadas</small><strong id="acvKpiLotesCompras">0</strong></div></div>
            <div class="col-md-3"><div class="acv-kpi"><small>Lotes de venta</small><strong id="acvKpiLotesVentas">0</strong></div></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3"><div class="acv-kpi"><small>Total de filas</small><strong id="acvKpiFilas">0</strong></div></div>
            <div class="col-md-3"><div class="acv-kpi"><small>Unidades de entrada base</small><strong id="acvKpiEntradas">0</strong></div></div>
            <div class="col-md-3"><div class="acv-kpi"><small>Unidades de salida activa</small><strong id="acvKpiSalidas">0</strong></div></div>
            <div class="col-md-3"><div class="acv-kpi"><small>Lotes/filas anulados</small><strong id="acvKpiAnulados">0</strong></div></div>
        </div>

        <div class="acv-card">
            <div class="acv-header"><h5><i class="fa fa-table mr-1"></i> Movimientos por producto y lote</h5></div>
            <div class="acv-body">
                <div class="table-responsive">
                    <table id="tbl_auditoria_cv" class="table table-bordered table-hover w-100">
                        <thead><tr>
                            <th>Secuencia</th><th>Movimiento</th><th>Fecha movimiento</th><th>Ingreso compra</th>
                            <th>Creación factura</th><th>Emisión factura</th><th>Factura ID</th>
                            <th>Compra ID</th><th>Documento</th><th>CAI</th><th>A/B</th><th>Estado</th>
                            <th>Producto ID</th><th>Producto</th><th>Lote</th><th>Bodega</th><th>Sección</th>
                            <th>Unidad compra</th><th>Factor compra</th><th>Unidad venta</th><th>Factor venta</th>
                            <th>Cant. documento</th><th>Cant. base</th><th>Movimiento base</th>
                            <th>Cliente</th><th>Proveedor</th><th>Usuario</th>
                        </tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/js_proyecto/auditoria/comprasVentas.js') }}?v={{ @filemtime(public_path('js/js_proyecto/auditoria/comprasVentas.js')) }}"></script>
    @endpush
</div>
