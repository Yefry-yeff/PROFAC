<div>
{{-- ════════════════════════════════════════════════════════════════════
     DASHBOARD DE VENTAS  BI  — 3 Pestañas
     Pestaña 1 : Histórico / Comparativo
     Pestaña 2 : Reporte Semanal
     Pestaña 3 : Analítica Avanzada (Vendedores · Clientes · Productos)
    ════════════════════════════════════════════════════════════════════ --}}

<div class="py-3 container-fluid" id="dashboardVentas">

    {{-- TÍTULO --}}
    <div class="mb-3 d-flex align-items-center">
        <i class="mr-3 fas fa-chart-line fa-2x text-primary"></i>
        <div>
            <h4 class="mb-0 font-weight-bold">Dashboard de Ventas</h4>
            <small class="text-muted">Inteligencia comercial • PROFAC</small>
        </div>
        <div class="ml-auto">
            <button class="btn btn-sm btn-outline-secondary" onclick="dashboardVentas.recargarTodo()">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>

    {{-- TABS --}}
    <ul class="nav nav-tabs nav-tabs-custom" id="dashTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold" id="tab-hist" data-toggle="tab" href="#pane-hist" role="tab">
                <i class="mr-1 fas fa-history"></i>Histórico & Comparativo
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold" id="tab-sem" data-toggle="tab" href="#pane-sem" role="tab">
                <i class="mr-1 fas fa-calendar-week"></i>Reporte Semanal
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold" id="tab-adv" data-toggle="tab" href="#pane-adv" role="tab">
                <i class="mr-1 fas fa-brain"></i>Analítica Avanzada
            </a>
        </li>
    </ul>

    <div class="shadow-sm tab-content card card-body border-top-0 rounded-0 rounded-bottom">

        {{-- ══════════ PESTAÑA 1 ══════════ --}}
        <div class="tab-pane fade show active" id="pane-hist" role="tabpanel">

            {{-- Filtros P1 --}}
            <div class="mb-3 border card card-body bg-light" id="filtros-hist">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="small font-weight-bold">Año(s) <small class="text-muted">(clic para activar)</small></label>
                        <div id="h-anios-pills" class="d-flex flex-wrap py-1" style="gap:4px; min-height:34px; align-items:flex-start;"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold">Vendedor</label>
                        <select class="form-control form-control-sm" id="h-vendedor">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold">Tipo cliente</label>
                        <select class="form-control form-control-sm" id="h-tipo-cliente">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="w-100">
                            <button class="btn btn-primary btn-sm btn-block mb-1" onclick="dashboardVentas.cargarHistorico()">
                                <i class="fas fa-search"></i> Consultar
                            </button>
                            <button class="btn btn-success btn-sm btn-block" onclick="dashboardVentas.exportarExcel('hist')">
                                <i class="fas fa-file-excel"></i> Excel + Gráficas
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KPI Cards P1 --}}
            <div class="mb-3 row" id="kpi-cards">
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-primary h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-primary text-uppercase">Total Vendido</div>
                            <div class="mb-0 h5 font-weight-bold" id="kpi-total">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-success h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-success text-uppercase">Facturas</div>
                            <div class="mb-0 h5 font-weight-bold" id="kpi-facturas">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-info h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-info text-uppercase">Ticket Promedio</div>
                            <div class="mb-0 h5 font-weight-bold" id="kpi-ticket">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-warning h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-warning text-uppercase">Clientes Únicos</div>
                            <div class="mb-0 h5 font-weight-bold" id="kpi-clientes">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-danger h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-danger text-uppercase">Crecimiento %</div>
                            <div class="mb-0 h5 font-weight-bold" id="kpi-crecimiento">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-secondary h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-secondary text-uppercase">Mejor Mes</div>
                            <div class="mb-0 h5 font-weight-bold" id="kpi-mejor-mes">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-primary h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-primary text-uppercase">Mejor Vendedor</div>
                            <div class="mb-0 h5 font-weight-bold" id="kpi-mejor-vend">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-success h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-success text-uppercase">Total Descuentos</div>
                            <div class="mb-0 h5 font-weight-bold" id="kpi-descuentos">—</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gráficas fila 1 --}}
            <div class="mb-3 row">
                <div class="col-md-8">
                    <div class="shadow-sm card">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Evolución Mensual por Año</span>
                        </div>
                        <div class="p-2 card-body">
                            <div id="chart-evolucion" style="min-height:280px"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="shadow-sm card">
                        <div class="py-2 card-header">
                            <span class="font-weight-bold">% Crecimiento Mensual</span>
                        </div>
                        <div class="p-2 card-body">
                            <div id="chart-crecimiento" style="min-height:280px"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gráficas fila 2 --}}
            <div class="mb-3 row">
                <div class="col-md-6">
                    <div class="shadow-sm card">
                        <div class="py-2 card-header">
                            <span class="font-weight-bold">Barras Agrupadas por Mes/Año</span>
                        </div>
                        <div class="p-2 card-body">
                            <div id="chart-barras" style="min-height:260px"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="shadow-sm card">
                        <div class="py-2 card-header">
                            <span class="font-weight-bold">Mapa de Calor (Año × Mes)</span>
                        </div>
                        <div class="p-2 card-body">
                            <div id="chart-heatmap" style="min-height:260px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- /PESTAÑA 1 --}}

        {{-- ══════════ PESTAÑA 2 ══════════ --}}
        <div class="tab-pane fade" id="pane-sem" role="tabpanel">

            {{-- Filtros P2 --}}
            <div class="mb-3 border card card-body bg-light" id="filtros-sem">
                <div class="row g-2">
                    <div class="col-md-2">
                        <label class="small font-weight-bold">Fecha inicio</label>
                        <input type="date" class="form-control form-control-sm" id="s-fi">
                    </div>
                    <div class="col-md-2">
                        <label class="small font-weight-bold">Fecha fin</label>
                        <input type="date" class="form-control form-control-sm" id="s-ff">
                    </div>
                    <div class="col-md-2">
                        <label class="small font-weight-bold">Vendedor</label>
                        <select class="form-control form-control-sm" id="s-vendedor">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small font-weight-bold">Tipo cliente</label>
                        <select class="form-control form-control-sm" id="s-tipo-cliente">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm btn-block" onclick="dashboardVentas.cargarSemanal()">
                            <i class="fas fa-search"></i> Consultar
                        </button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-success btn-sm btn-block" onclick="dashboardVentas.exportarExcel('sem')">
                            <i class="fas fa-file-excel"></i> Excel + Gráficas
                        </button>
                    </div>
                </div>
            </div>

            {{-- KPI Cards P2 --}}
            <div class="mb-3 row" id="kpi-sem">
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-primary h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-primary text-uppercase">Total Período</div>
                            <div class="mb-0 h5 font-weight-bold" id="s-kpi-total">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-success h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-success text-uppercase">Facturas</div>
                            <div class="mb-0 h5 font-weight-bold" id="s-kpi-facturas">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-info h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-info text-uppercase">Ticket Promedio</div>
                            <div class="mb-0 h5 font-weight-bold" id="s-kpi-ticket">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-warning h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-warning text-uppercase">Mejor Día</div>
                            <div class="mb-0 h5 font-weight-bold" id="s-kpi-mejor-dia">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-danger h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-danger text-uppercase">Mejor Vendedor</div>
                            <div class="mb-0 h5 font-weight-bold" id="s-kpi-vend">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-secondary h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-secondary text-uppercase">Mejor Cliente</div>
                            <div class="mb-0 h5 font-weight-bold" id="s-kpi-cliente">—</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gráficas semanal --}}
            <div class="mb-3 row">
                <div class="col-md-5">
                    <div class="shadow-sm card">
                        <div class="py-2 card-header"><span class="font-weight-bold">Ventas por Día de la Semana</span></div>
                        <div class="p-2 card-body"><div id="chart-por-dia" style="min-height:240px"></div></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="shadow-sm card">
                        <div class="py-2 card-header"><span class="font-weight-bold">Tipo de Cliente</span></div>
                        <div class="p-2 card-body"><div id="chart-tipo-cliente-sem" style="min-height:240px"></div></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="shadow-sm card">
                        <div class="py-2 card-header"><span class="font-weight-bold">Top Vendedores (período)</span></div>
                        <div class="p-2 card-body"><div id="chart-ranking-vend-sem" style="min-height:240px"></div></div>
                    </div>
                </div>
            </div>

            {{-- Tabla semanal --}}
            <div class="shadow-sm card">
                <div class="py-2 card-header"><span class="font-weight-bold">Detalle de Facturas</span></div>
                <div class="p-2 card-body table-responsive">
                    <table class="table table-striped table-bordered table-sm" id="tabla-semanal" style="width:100%">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Día</th>
                                <th>Semana</th>
                                <th>Documento</th>
                                <th>Cliente</th>
                                <th>Vendedor</th>
                                <th>Tipo</th>
                                <th>Subtotal</th>
                                <th>ISV</th>
                                <th>Descuento</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- /PESTAÑA 2 --}}

        {{-- ══════════ PESTAÑA 3 ══════════ --}}
        <div class="tab-pane fade" id="pane-adv" role="tabpanel">

            {{-- Filtros P3 --}}
            <div class="mb-3 border card card-body bg-light">
                <div class="row g-2">
                    <div class="col-md-2">
                        <label class="small font-weight-bold">Fecha inicio</label>
                        <input type="date" class="form-control form-control-sm" id="a-fi">
                    </div>
                    <div class="col-md-2">
                        <label class="small font-weight-bold">Fecha fin</label>
                        <input type="date" class="form-control form-control-sm" id="a-ff">
                    </div>
                    <div class="col-md-2">
                        <label class="small font-weight-bold">Vendedor</label>
                        <select class="form-control form-control-sm" id="a-vendedor">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small font-weight-bold">Tipo cliente</label>
                        <select class="form-control form-control-sm" id="a-tipo-cliente">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small font-weight-bold">Categoría</label>
                        <select class="form-control form-control-sm" id="a-categoria">
                            <option value="">Todas</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm btn-block" onclick="dashboardVentas.cargarAnalitica()">
                            <i class="fas fa-search"></i> Consultar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Sub-tabs analítica --}}
            <ul class="mb-3 nav nav-pills" id="adv-pills">
                <li class="nav-item">
                    <a class="nav-link active" id="pill-vend" data-toggle="pill" href="#pill-pane-vend">
                        <i class="mr-1 fas fa-user-tie"></i>Vendedores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pill-cli" data-toggle="pill" href="#pill-pane-cli">
                        <i class="mr-1 fas fa-users"></i>Clientes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pill-prod" data-toggle="pill" href="#pill-pane-prod">
                        <i class="mr-1 fas fa-box"></i>Productos
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                {{-- VENDEDORES --}}
                <div class="tab-pane fade show active" id="pill-pane-vend">
                    <div class="mb-3 row">
                        <div class="col-md-5">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold">Ranking Vendedores (Total)</span></div>
                                <div class="p-2 card-body"><div id="chart-rank-vend" style="min-height:300px"></div></div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold">Participación de mercado (%)</span></div>
                                <div class="p-2 card-body"><div id="chart-part-vend" style="min-height:300px"></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="shadow-sm card">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Tabla Vendedores</span>
                            <button class="btn btn-sm btn-success" onclick="dashboardVentas.exportarExcel('adv')">
                                <i class="fas fa-file-excel"></i> Excel + Gráficas
                            </button>
                        </div>
                        <div class="p-2 card-body table-responsive">
                            <table class="table table-striped table-sm" id="tabla-vendedores" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Vendedor</th>
                                        <th>Facturas</th>
                                        <th>Clientes</th>
                                        <th>Total Ventas</th>
                                        <th>Ticket Promedio</th>
                                        <th>Participación %</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-vendedores"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- CLIENTES --}}
                <div class="tab-pane fade" id="pill-pane-cli">
                    <div class="mb-3 row">
                        <div class="col-md-6">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold">Top 15 Clientes por Facturación</span></div>
                                <div class="p-2 card-body"><div id="chart-top-cli" style="min-height:300px"></div></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold">Clasificación ABC</span></div>
                                <div class="p-2 card-body"><div id="chart-abc-cli" style="min-height:300px"></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="shadow-sm card">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Tabla Clientes</span>
                            <div>
                                <span class="mr-1 badge badge-success">A — Top 70%</span>
                                <span class="mr-1 badge badge-warning">B — 70–90%</span>
                                <span class="mr-1 badge badge-danger">C — 90–100%</span>
                                <button class="ml-2 btn btn-sm btn-success" onclick="dashboardVentas.exportarExcel('adv')">
                                    <i class="fas fa-file-excel"></i> Excel + Gráficas
                                </button>
                            </div>
                        </div>
                        <div class="p-2 card-body table-responsive">
                            <table class="table table-striped table-sm" id="tabla-clientes" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Cliente</th>
                                        <th>Tipo</th>
                                        <th>ABC</th>
                                        <th>Facturas</th>
                                        <th>Total Comprado</th>
                                        <th>Ticket Prom.</th>
                                        <th>Última Compra</th>
                                        <th>Días sin comprar</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-clientes"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- PRODUCTOS --}}
                <div class="tab-pane fade" id="pill-pane-prod">
                    <div class="mb-3 row">
                        <div class="col-md-6">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold">Top 20 Productos por Ingresos</span></div>
                                <div class="p-2 card-body"><div id="chart-top-prod" style="min-height:320px"></div></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold">Pareto 80/20</span></div>
                                <div class="p-2 card-body"><div id="chart-pareto" style="min-height:320px"></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="shadow-sm card">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Tabla Productos</span>
                            <button class="btn btn-sm btn-success" onclick="dashboardVentas.exportarExcel('adv')">
                                <i class="fas fa-file-excel"></i> Excel + Gráficas
                            </button>
                        </div>
                        <div class="p-2 card-body table-responsive">
                            <table class="table table-striped table-sm" id="tabla-productos" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Producto</th>
                                        <th>Categoría</th>
                                        <th>Subcategoría</th>
                                        <th>Unidades</th>
                                        <th>Ingresos</th>
                                        <th>Precio Prom.</th>
                                        <th>Facturas</th>
                                        <th>Pareto %</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-productos"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        {{-- /PESTAÑA 3 --}}

    </div>{{-- /tab-content --}}
</div>{{-- /container-fluid --}}

{{-- ======================= ESTILOS ======================= --}}
<style>
.nav-tabs-custom { border-bottom: 2px solid #4e73df; }
.nav-tabs-custom .nav-link { border: 1px solid transparent; border-radius: 4px 4px 0 0; color: #5a5c69; }
.nav-tabs-custom .nav-link.active { background: #fff; border-color: #4e73df #4e73df #fff; color: #4e73df; }

.kpi-card { border-radius: 6px; border: 1px solid #e3e6f0; }
.border-left-primary  { border-left: 4px solid #4e73df !important; }
.border-left-success  { border-left: 4px solid #1cc88a !important; }
.border-left-info     { border-left: 4px solid #36b9cc !important; }
.border-left-warning  { border-left: 4px solid #f6c23e !important; }
.border-left-danger   { border-left: 4px solid #e74a3b !important; }
.border-left-secondary{ border-left: 4px solid #858796 !important; }
.text-xs { font-size: .7rem; }

#dashboardVentas .card-header { background: #f8f9fc; font-size: .85rem; }

/* Ensure DT headers always show in dark */
#tabla-vendedores thead th, #tabla-clientes thead th, #tabla-productos thead th {
    background-color: #343a40 !important;
    color: #fff !important;
    border-color: #454d55 !important;
}
/* Year toggle pills */
.year-pill { border-radius: 20px !important; font-size: .75rem !important; padding: 2px 10px !important; }
</style>

{{-- ======================= SCRIPTS ======================= --}}
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
<script src="{{ asset('js/js_proyecto/reportes/dashboard-ventas.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        dashboardVentas.init();
    });
</script>

</div>
