<div class="py-3 container-fluid" id="reporteExpoBI">
    <style>
        #reporteExpoBI .bi-row-selectable { cursor: pointer; }
        #reporteExpoBI .bi-row-selectable:hover { background: #e8f2fb !important; box-shadow: inset 3px 0 #2878a9; }
        #reporteExpoBI .bi-modal-summary { display: grid; grid-template-columns: repeat(4, minmax(140px, 1fr)); gap: 10px; }
        #reporteExpoBI .bi-summary-item { padding: 10px; border: 1px solid #dfe5ec; border-radius: 4px; background: #f8fafc; }
        #reporteExpoBI .bi-summary-item small { display: block; color: #64748b; text-transform: uppercase; }
        #reporteExpoBI .bi-summary-item strong { display: block; margin-top: 3px; font-size: 16px; }
        #reporteExpoBI .bi-profit { color: #08783e; }
        #reporteExpoBI .bi-loss { color: #b42318; }
        #reporteExpoBI #tabla-expo-ofertas td.bi-profit { color: #08783e !important; }
        #reporteExpoBI #tabla-expo-ofertas td.bi-loss { color: #b42318 !important; }
        #reporteExpoBI .modal { z-index: 4010 !important; }
        .modal-backdrop { z-index: 4005 !important; }
        #reporteExpoBI .modal-bi-wide { max-width: 96vw; }
        #reporteExpoBI .modal-bi-wide .modal-body { max-height: 78vh; overflow-y: auto; }
        #reporteExpoBI .select2-container { width: 100% !important; }
        @media (max-width: 767px) {
            #reporteExpoBI .bi-modal-summary { grid-template-columns: repeat(2, minmax(120px, 1fr)); }
            #reporteExpoBI .modal-bi-wide { max-width: 100%; margin: 0; }
        }
    </style>

    <div class="mb-3 d-flex align-items-center">
        <i class="mr-3 fas fa-chart-pie fa-2x text-primary"></i>
        <div>
            <h4 class="mb-0 font-weight-bold">Reporte BI de Expo</h4>
            <small class="text-muted">Análisis comercial dinámico • solo consulta, no altera Expo/Oferta/Prefactura/Facturación</small>
        </div>
        <div class="ml-auto">
            <button class="btn btn-sm btn-outline-secondary" onclick="reporteExpo.recargarTodo()">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="mb-3 border card card-body bg-light" wire:ignore>
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="small font-weight-bold">Expo</label>
                <select class="form-control form-control-sm" id="expo-selector" onchange="reporteExpo.aplicarFiltroDesdeForm(); reporteExpo.cargarCatalogoFiltros();">
                    @foreach ($expos as $expo)
                        <option value="{{ $expo->id }}" @selected($expo->id == $expoActivoId)>
                            {{ $expo->nombre }} @if($expo->estado === 'Activo') (Activa) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Marca</label>
                <select class="form-control form-control-sm" id="expo-f-marca">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Escala</label>
                <select class="form-control form-control-sm" id="expo-f-escala">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Asesor</label>
                <select class="form-control form-control-sm" id="expo-f-vendedor">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Teleasesor</label>
                <select class="form-control form-control-sm" id="expo-f-teleasesor" multiple data-placeholder="Todos">
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Estado oferta</label>
                <select class="form-control form-control-sm" id="expo-f-estado">
                    <option value="">Todos</option>
                    <option value="PENDIENTE_FACTURACION">Sin facturar</option>
                    <option value="FACTURACION_PARCIAL">Factura parcial</option>
                    <option value="PENDIENTE_LIQUIDACION">Pendiente liquidar</option>
                    <option value="LIQUIDADA">Liquidada</option>
                </select>
            </div>
        </div>
        <div class="row g-2 align-items-end mt-2">
            <div class="col-md-2">
                <label class="small font-weight-bold">Desde</label>
                <input type="date" class="form-control form-control-sm" id="expo-f-desde">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">Hasta</label>
                <input type="date" class="form-control form-control-sm" id="expo-f-hasta">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-sm" onclick="reporteExpo.aplicarFiltroDesdeForm()">
                    <i class="fas fa-search"></i> Consultar
                </button>
            </div>
            <div class="col-md-5 text-right" id="expo-filtros-activos"></div>
        </div>
        <div class="row g-2 align-items-end mt-2">
            <div class="col-md-2">
                <label class="small font-weight-bold d-block">Ver resultados por</label>
                <div class="btn-group btn-group-toggle w-100" data-toggle="buttons" aria-label="Base de rentabilidad">
                    <label class="btn btn-sm btn-outline-primary active">
                        <input type="radio" name="expo-f-rentabilidad-base" value="ofertas" onchange="reporteExpo.aplicarFiltroDesdeForm()" checked> Ofertas
                    </label>
                    <label class="btn btn-sm btn-outline-primary">
                        <input type="radio" name="expo-f-rentabilidad-base" value="facturas" onchange="reporteExpo.aplicarFiltroDesdeForm()"> Facturas
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="mb-3 row">
        <div class="mb-2 col-6 col-md-3">
            <div class="card h-100 border-left-secondary">
                <div class="px-3 py-2 card-body">
                    <div class="mb-1 text-xs font-weight-bold text-secondary text-uppercase">Clientes Únicos</div>
                    <div class="mb-0 h5 font-weight-bold" id="kpi-clientes">—</div>
                </div>
            </div>
        </div>
        <div class="mb-2 col-6 col-md-3">
            <div class="card h-100 border-left-warning">
                <div class="px-3 py-2 card-body">
                    <div class="mb-1 text-xs font-weight-bold text-warning text-uppercase">Ofertas Validadas</div>
                    <div class="mb-0 h5 font-weight-bold" id="kpi-ofertas">—</div>
                </div>
            </div>
        </div>
        <div class="mb-2 col-6 col-md-3">
            <div class="card h-100 border-left-secondary">
                <div class="px-3 py-2 card-body">
                    <div class="mb-1 text-xs font-weight-bold text-secondary text-uppercase">Facturas</div>
                    <div class="mb-0 h5 font-weight-bold" id="kpi-facturas">—</div>
                </div>
            </div>
        </div>
        <div class="mb-2 col-6 col-md-3">
            <div class="card h-100 border-left-primary">
                <div class="px-3 py-2 card-body">
                    <div class="mb-1 text-xs font-weight-bold text-primary text-uppercase">Total Ofertado</div>
                    <div class="mb-0 h5 font-weight-bold" id="kpi-ofertado">—</div>
                </div>
            </div>
        </div>
        <div class="mb-2 col-6 col-md-3">
            <div class="card h-100 border-left-warning">
                <div class="px-3 py-2 card-body">
                    <div class="mb-1 text-xs font-weight-bold text-warning text-uppercase">Descuento Oferta</div>
                    <div class="mb-0 h5 font-weight-bold" id="kpi-descuento">—</div>
                </div>
            </div>
        </div>
        <div class="mb-2 col-6 col-md-3">
            <div class="card h-100 border-left-primary">
                <div class="px-3 py-2 card-body">
                    <div class="mb-1 text-xs font-weight-bold text-primary text-uppercase">Total Oferta sin ISV</div>
                    <div class="mb-0 h5 font-weight-bold" id="kpi-oferta-sin-isv">—</div>
                </div>
            </div>
        </div>
        <div class="mb-2 col-6 col-md-3">
            <div class="card h-100 border-left-success">
                <div class="px-3 py-2 card-body">
                    <div class="mb-1 text-xs font-weight-bold text-success text-uppercase">Total Oferta con ISV</div>
                    <div class="mb-0 h5 font-weight-bold" id="kpi-oferta-con-isv">—</div>
                </div>
            </div>
        </div>
        <div class="mb-2 col-6 col-md-3">
            <div class="card h-100 border-left-dark">
                <div class="px-3 py-2 card-body">
                    <div class="mb-1 text-xs font-weight-bold text-dark text-uppercase">Costo Oferta</div>
                    <div class="mb-0 h5 font-weight-bold" id="kpi-costo">—</div>
                </div>
            </div>
        </div>
        <div class="mb-2 col-6 col-md-3">
            <div class="card h-100 border-left-danger">
                <div class="px-3 py-2 card-body">
                    <div class="mb-1 text-xs font-weight-bold text-danger text-uppercase">Utilidad</div>
                    <div class="mb-0 h5 font-weight-bold" id="kpi-utilidad">—</div>
                </div>
            </div>
        </div>
        <div class="mb-2 col-6 col-md-3">
            <div class="card h-100 border-left-danger">
                <div class="px-3 py-2 card-body">
                    <div class="mb-1 text-xs font-weight-bold text-danger text-uppercase">Margen</div>
                    <div class="mb-0 h5 font-weight-bold" id="kpi-margen">—</div>
                </div>
            </div>
        </div>
        <div class="mb-2 col-6 col-md-3">
            <div class="card h-100 border-left-info">
                <div class="px-3 py-2 card-body">
                    <div class="mb-1 text-xs font-weight-bold text-info text-uppercase">% Avance Facturación</div>
                    <div class="mb-0 h5 font-weight-bold" id="kpi-avance">—</div>
                </div>
            </div>
        </div>
    </div>

    {{-- GRÁFICAS --}}
    <div class="row">
        <div class="mb-3 col-lg-6">
            <div class="card card-body h-100">
                <h6 class="font-weight-bold">Estado de las Ofertas <small class="text-muted">(clic para filtrar)</small></h6>
                <div id="chart-estado"></div>
            </div>
        </div>
        <div class="mb-3 col-lg-6">
            <div class="card card-body h-100">
                <h6 class="font-weight-bold">Evolución acumulada diaria: Oferta sin ISV vs Facturado</h6>
                <div id="chart-evolucion-expo"></div>
            </div>
        </div>
        <div class="mb-3 col-lg-6">
            <div class="card card-body h-100">
                <h6 class="font-weight-bold">Ventas por Marca <small class="text-muted">(clic para filtrar)</small></h6>
                <div id="chart-marca"></div>
            </div>
        </div>
        <div class="mb-3 col-lg-6">
            <div class="card card-body h-100">
                <h6 class="font-weight-bold">Ventas por Asesor <small class="text-muted">(clic para filtrar)</small></h6>
                <div id="chart-asesor"></div>
            </div>
        </div>
        <div class="mb-3 col-lg-12">
            <div class="card card-body h-100">
                <h6 class="font-weight-bold">Rendimiento por Teleasesor <small class="text-muted">ventas netas, utilidad y conversión; clic para filtrar</small></h6>
                <div id="chart-teleasesor"></div>
            </div>
        </div>
    </div>

    {{-- TABLA: OFERTAS --}}
    <div class="mb-3 card card-body">
        <div class="d-flex align-items-center mb-2">
            <h6 class="mb-0 font-weight-bold">Ofertas de la Expo (deduplicadas)</h6>
            <button class="ml-auto btn btn-sm btn-success" onclick="reporteExpo.exportarOfertas()">
                <i class="fas fa-file-excel"></i> Excel
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered" id="tabla-expo-ofertas" style="width:100%">
                <thead>
                    <tr>
                        <th>Oferta #</th>
                        <th>Flujo</th>
                        <th>Cliente</th>
                        <th>Asesor</th>
                        <th>Teleasesor</th>
                        <th>Fecha</th>
                        <th>Facturación</th>
                        <th class="text-right">Ofertado</th>
                        <th class="text-right">Facturado</th>
                        <th class="text-right">Margen Oferta %</th>
                        <th class="text-right">Ganancia</th>
                        <th class="text-right">Avance %</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- TABLA: PRODUCTOS --}}
    <div class="mb-3 card card-body">
        <div class="d-flex align-items-center mb-2">
            <h6 class="mb-0 font-weight-bold" id="titulo-analitica-productos">Analítica de Productos por Ofertas</h6>
            <button class="ml-auto btn btn-sm btn-success" onclick="reporteExpo.exportarProductos()">
                <i class="fas fa-file-excel"></i> Excel
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered" id="tabla-expo-productos" style="width:100%">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Categoría</th>
                        <th class="text-right">Ofertas</th>
                        <th class="text-right" id="th-productos-cantidad-base">Cant. Ofertada</th>
                        <th class="text-right" id="th-productos-venta-base">Venta Oferta</th>
                        <th class="text-right" id="th-productos-descuento-base">Descuento Oferta</th>
                        <th class="text-right" id="th-productos-costo-base">Costo Oferta</th>
                        <th class="text-right" id="th-productos-utilidad-base">Utilidad Oferta</th>
                        <th class="text-right" id="th-productos-margen-base">Margen Oferta %</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- MODAL REUTILIZABLE: OFERTA --}}
    <div class="modal fade" id="modal-oferta-expo" tabindex="-1" role="dialog" aria-hidden="true" wire:ignore>
        <div class="modal-dialog modal-bi-wide" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <small class="text-muted">Detalle transaccional</small>
                        <h5 class="modal-title mb-0" id="modal-oferta-titulo">Oferta</h5>
                    </div>
                    <div class="ml-auto mr-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="reporteExpo.abrirBuscadorProductos('oferta')" title="Buscar producto"><i class="fas fa-search"></i> Producto</button>
                        <button type="button" class="btn btn-sm btn-success" onclick="reporteExpo.exportarOfertaSeleccionada()" title="Descargar oferta en Excel"><i class="fas fa-file-excel"></i> Excel</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="reporteExpo.imprimirOfertaSeleccionada()" title="Imprimir oferta"><i class="fas fa-print"></i> Imprimir</button>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-oferta-detalle" role="tab">Detalle de Oferta</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-oferta-facturas" role="tab">Facturas <span class="badge badge-light" id="modal-oferta-num-facturas">0</span></a></li>
                    </ul>
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="tab-oferta-detalle" role="tabpanel">
                            <div class="row small" id="modal-oferta-general"></div>
                            <div class="bi-modal-summary my-3" id="modal-oferta-resumen"></div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover" id="modal-oferta-productos">
                                    <thead class="thead-light"><tr>
                                        <th>Código / Producto</th><th>Marca</th><th>Categoría</th><th>Escala</th>
                                        <th class="text-right">Cant.</th><th class="text-right">Precio base</th>
                                        <th class="text-right">Precio antes desc.</th><th class="text-right">Descuento</th>
                                        <th class="text-right">Precio final</th><th class="text-right">Subtotal</th>
                                        <th class="text-right">ISV</th><th class="text-right">Total</th>
                                        <th class="text-right">Costo</th><th class="text-right">Utilidad / Margen</th>
                                    </tr></thead><tbody></tbody><tfoot></tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-oferta-facturas" role="tabpanel">
                            <div class="bi-modal-summary mb-3" id="modal-facturas-comparacion"></div>
                            <div id="modal-oferta-facturas-contenido"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: ANALÍTICA DE PRODUCTO --}}
    <div class="modal fade" id="modal-producto-expo" tabindex="-1" role="dialog" aria-hidden="true" wire:ignore>
        <div class="modal-dialog modal-bi-wide" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div><small class="text-muted">Analítica del Producto</small><h5 class="modal-title mb-0" id="modal-producto-titulo">Producto</h5></div>
                    <button type="button" class="ml-auto mr-3 btn btn-sm btn-outline-primary" onclick="reporteExpo.abrirBuscadorProductos('producto')" title="Buscar otro producto"><i class="fas fa-search"></i> Producto</button>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="bi-modal-summary mb-3" id="modal-producto-resumen"></div>
                    <h6 class="font-weight-bold" id="titulo-modal-producto-detalle">Ofertas donde apareció</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover" id="modal-producto-ofertas">
                            <thead class="thead-light"><tr>
                                <th>Oferta / Flujo</th><th>Fecha</th><th>Cliente</th><th>Asesor</th><th>Teleasesor</th>
                                <th>Escala</th><th class="text-right" id="th-modal-producto-cantidad">Cant. Ofertada</th><th class="text-right">Precio base</th>
                                <th class="text-right">Precio ofertado</th><th class="text-right">Descuento</th>
                                <th class="text-right" id="th-modal-producto-precio">Precio oferta</th><th class="text-right" id="th-modal-producto-subtotal">Subtotal oferta</th>
                                <th class="text-right" id="th-modal-producto-total">ISV / Total oferta</th><th class="text-right" id="th-modal-producto-costo">Costo oferta</th>
                                <th class="text-right" id="th-modal-producto-utilidad">Utilidad / Margen oferta</th><th>Facturación</th>
                            </tr></thead><tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-buscador-producto
    id-modal="buscadorProductoReporteExpo"
    callback="seleccionarProductoReporteExpo"
    url-buscar="/reporte/expo/buscar-productos"
    extra-params-callback="parametrosBuscadorProductoReporteExpo"
    :con-stock-default="false"
    :use-top-preview="false"
/>

<script src="{{ asset('js/js_proyecto/reportes/reporte-expo.js') }}"></script>
<script>
    window.parametrosBuscadorProductoReporteExpo = function () {
        return reporteExpo.parametrosBuscadorProductos();
    };
    window.seleccionarProductoReporteExpo = function (producto) {
        reporteExpo.seleccionarProductoBuscador(producto);
    };
    document.addEventListener('DOMContentLoaded', function () {
        reporteExpo.init(document.getElementById('expo-selector').value);
    });
</script>
