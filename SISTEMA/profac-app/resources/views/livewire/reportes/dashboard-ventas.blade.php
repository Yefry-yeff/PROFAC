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
                        <div wire:ignore>
                        <select class="form-control form-control-sm" id="h-vendedor">
                            <option value="">Todos</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                        </div>
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
                            <button class="btn btn-success btn-sm btn-block" onclick="dashboardVentas.exportarExcel()">
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
                            <div class="mb-1 text-xs font-weight-bold text-info text-uppercase">Venta Promedio</div>
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

            {{-- FILA 2: Evolución mensual — full width --}}
            <div class="mb-3 row">
                <div class="col-12">
                    <div class="shadow-sm card">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fas fa-chart-line mr-1"></i> Evolución Mensual por Año</span>
                            <small class="text-muted font-italic" id="evol-anios-badge"></small>
                        </div>
                        <div class="p-2 card-body">
                            <div id="chart-evolucion" style="min-height:420px"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FILA 3: % Crecimiento mensual — full width --}}
            <div class="mb-3 row">
                <div class="col-12">
                    <div class="shadow-sm card">
                        <div class="py-2 card-header d-flex align-items-center">
                            <span class="font-weight-bold"><i class="fas fa-percentage mr-1"></i> % Crecimiento Mensual</span>
                            <small class="text-muted ml-2">(diferencia absoluta en L. por mes)</small>
                        </div>
                        <div class="p-2 card-body">
                            <div id="chart-crecimiento" style="min-height:340px"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FILA 4: Barras agrupadas + Heatmap --}}
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
                        <div wire:ignore>
                        <select class="form-control form-control-sm" id="s-vendedor">
                            <option value="">Todos</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                        </div>
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
                        <button class="btn btn-success btn-sm btn-block" onclick="dashboardVentas.exportarExcel()">
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
                            <div class="mb-1 text-xs font-weight-bold text-info text-uppercase">Venta Promedio</div>
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

            {{-- Barra de filtros activos P2 --}}
            <div id="sem-active-filters" class="mb-2 bi-filter-bar d-none">
                <div class="d-flex flex-wrap align-items-center" style="gap:6px">
                    <i class="fas fa-filter text-primary mr-1"></i>
                    <small class="font-weight-bold text-muted mr-1">Filtro activo:</small>
                    <span id="filter-badge-dia"  class="badge badge-pill bi-badge" style="display:none;cursor:pointer"></span>
                    <span id="filter-badge-vend" class="badge badge-pill bi-badge" style="display:none;cursor:pointer"></span>
                    <button class="btn btn-xs btn-outline-danger ml-1" onclick="dashboardVentas.limpiarFiltrosSem()">
                        <i class="fas fa-times mr-1"></i>Limpiar filtros
                    </button>
                    <small class="text-muted ml-2"><i class="fas fa-info-circle"></i> Haz clic en cualquier barra o segmento para filtrar</small>
                </div>
            </div>

            {{-- FILA 1: Por día + Tipo de cliente --}}
            <div class="mb-3 row">
                <div class="col-md-6">
                    <div class="shadow-sm card bi-clickable-chart">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fas fa-calendar-day mr-1"></i> Ventas por Día de la Semana</span>
                            <small class="text-muted">Clic para filtrar</small>
                        </div>
                        <div class="p-2 card-body"><div id="chart-por-dia" style="min-height:300px"></div></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="shadow-sm card bi-clickable-chart">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fas fa-users mr-1"></i> Tipo de Cliente</span>
                            <small class="text-muted">Clic para filtrar</small>
                        </div>
                        <div class="p-2 card-body"><div id="chart-tipo-cliente-sem" style="min-height:300px"></div></div>
                    </div>
                </div>
            </div>

            {{-- FILA 2: Top vendedores (full width) --}}
            <div class="mb-3 row">
                <div class="col-12">
                    <div class="shadow-sm card bi-clickable-chart">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fas fa-medal mr-1"></i> Top Vendedores del Período</span>
                            <small class="text-muted">Clic para filtrar por vendedor</small>
                        </div>
                        <div class="p-2 card-body"><div id="chart-ranking-vend-sem" style="min-height:300px"></div></div>
                    </div>
                </div>
            </div>

            {{-- FILA 3: Crecimiento de vendedores (full width) --}}
            <div class="mb-3 row">
                <div class="col-12">
                    <div class="shadow-sm card bi-clickable-chart">
                        <div class="py-2 px-3 card-header d-flex flex-wrap justify-content-between align-items-center" style="gap:8px">
                            <span class="font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Crecimiento por Vendedor</span>
                            <div class="d-flex align-items-center flex-wrap" style="gap:6px">
                                <small class="text-muted mr-1">Comparar con:</small>
                                <input type="date" class="form-control form-control-sm" id="crec-fi" style="width:140px">
                                <small class="text-muted">al</small>
                                <input type="date" class="form-control form-control-sm" id="crec-ff" style="width:140px">
                                <button class="btn btn-sm btn-outline-primary" onclick="dashboardVentas.recalcularCrecimiento()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <small class="text-muted font-italic" id="crec-vend-periodo-label">vs. período anterior</small>
                            </div>
                        </div>
                        <div class="p-2 card-body"><div id="chart-crec-vend-sem" style="min-height:300px"></div></div>
                    </div>
                </div>
            </div>

            {{-- FILA 4: Top 5 clientes (full width) --}}
            <div class="mb-3 row">
                <div class="col-12">
                    <div class="shadow-sm card">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fas fa-star mr-1 text-warning"></i> Top 5 Clientes del Período</span>
                            <small class="text-muted" id="top-cli-sem-label">Todos los vendedores</small>
                        </div>
                        <div class="p-2 card-body"><div id="chart-top-cli-sem" style="min-height:280px"></div></div>
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
                <div class="row g-2 mb-2">
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
                        <div wire:ignore>
                        <select class="form-control form-control-sm" id="a-vendedor">
                            <option value="">Todos</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                        </div>
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
                    <div class="col-md-2">
                        <label class="small font-weight-bold">Marca</label>
                        <select class="form-control form-control-sm" id="a-marca">
                            <option value="">Todas</option>
                        </select>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-12 d-flex justify-content-end" style="gap:6px">
                        <button class="btn btn-outline-secondary btn-sm" onclick="dashboardVentas.limpiarFiltrosAdv()">
                            <i class="fas fa-times"></i> Limpiar
                        </button>
                        <button class="btn btn-primary btn-sm px-4" onclick="dashboardVentas.cargarAnalitica()">
                            <i class="fas fa-search"></i> Consultar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Barra filtros activos P3 --}}
            <div id="adv-active-filters" class="mb-2 bi-filter-bar d-none">
                <div class="d-flex flex-wrap align-items-center" style="gap:6px">
                    <i class="fas fa-filter text-primary mr-1"></i>
                    <small class="font-weight-bold text-muted mr-1">Filtro activo:</small>
                    <span id="adv-filter-badge-vend" class="badge badge-pill bi-badge" style="display:none"></span>
                    <button class="btn btn-xs btn-outline-danger ml-1" onclick="dashboardVentas.limpiarFiltrosAdv()">
                        <i class="fas fa-times mr-1"></i>Limpiar filtros
                    </button>
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
                <li class="nav-item">
                    <a class="nav-link" id="pill-comp" data-toggle="pill" href="#pill-pane-comp">
                        <i class="mr-1 fas fa-balance-scale"></i>Comparar Vendedores
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
                            <button class="btn btn-sm btn-success" onclick="dashboardVentas.exportarExcel()">
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
                                        <th>Venta Promedio</th>
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
                                <button class="ml-2 btn btn-sm btn-success" onclick="dashboardVentas.exportarExcel()">
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
                                        <th>Venta Prom.</th>
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

                    {{-- Marcas --}}
                    <div class="mb-3 row">
                        <div class="col-md-6">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-tag mr-1 text-warning"></i>Top Marcas por Ingresos</span></div>
                                <div class="p-2 card-body"><div id="chart-top-marcas" style="min-height:300px"></div></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-chart-pie mr-1 text-warning"></i>Participación por Marca (%)</span></div>
                                <div class="p-2 card-body"><div id="chart-part-marcas" style="min-height:300px"></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="shadow-sm card">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Tabla Productos</span>
                            <button class="btn btn-sm btn-success" onclick="dashboardVentas.exportarExcel()">
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

                    {{-- Tabla Marcas --}}
                    <div class="mt-3 shadow-sm card">
                        <div class="py-2 card-header">
                            <span class="font-weight-bold"><i class="fas fa-tag mr-1 text-warning"></i>Tabla por Marca</span>
                        </div>
                        <div class="p-2 card-body table-responsive">
                            <table class="table table-striped table-sm" id="tabla-marcas" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Marca</th>
                                        <th>Productos</th>
                                        <th>Unidades</th>
                                        <th>Ingresos</th>
                                        <th>Precio Prom.</th>
                                        <th>Facturas</th>
                                        <th>Participación %</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-marcas"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- COMPARAR VENDEDORES --}}
                <div class="tab-pane fade" id="pill-pane-comp">
                    {{-- Filtros comparación --}}
                    <div class="mb-3 border card card-body bg-light">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="small font-weight-bold">Fecha inicio</label>
                                <input type="date" class="form-control form-control-sm" id="cmp-fi">
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold">Fecha fin</label>
                                <input type="date" class="form-control form-control-sm" id="cmp-ff">
                            </div>
                            <div class="col-md-6">
                                <label class="small font-weight-bold">Seleccionar vendedores a comparar</label>
                                <div id="cmp-vend-checks" class="d-flex flex-wrap py-1 border rounded bg-white px-2" style="gap:8px; min-height:38px; max-height:100px; overflow-y:auto;"></div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary btn-sm btn-block" onclick="dashboardVentas.cargarComparacion()">
                                    <i class="fas fa-exchange-alt"></i> Comparar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- KPI cards comparación --}}
                    <div class="mb-3 row" id="cmp-kpi-cards"></div>

                    {{-- Gráficas comparación --}}
                    <div class="mb-3 row">
                        <div class="col-12">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-chart-line mr-1"></i> Evolución Mensual por Vendedor</span></div>
                                <div class="p-2 card-body"><div id="chart-cmp-evolucion" style="min-height:380px"></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-md-6">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Total del Período</span></div>
                                <div class="p-2 card-body"><div id="chart-cmp-total" style="min-height:300px"></div></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-chart-pie mr-1"></i> Participación de Mercado</span></div>
                                <div class="p-2 card-body"><div id="chart-cmp-part" style="min-height:300px"></div></div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabla comparación --}}
                    <div class="shadow-sm card">
                        <div class="py-2 card-header"><span class="font-weight-bold">Resumen por Vendedor</span></div>
                        <div class="p-2 card-body table-responsive">
                            <table class="table table-bordered table-sm" id="tabla-comparacion" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Vendedor</th>
                                        <th>Facturas</th>
                                        <th>Clientes</th>
                                        <th>Total Ventas</th>
                                        <th>Ticket Prom.</th>
                                        <th>Participación %</th>
                                        <th>Mejor Mes</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-comparacion"></tbody>
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
.nav-tabs-custom { border-bottom: 2px solid #EC401B; }
.nav-tabs-custom .nav-link { border: 1px solid transparent; border-radius: 4px 4px 0 0; color: #5a5c69; }
.nav-tabs-custom .nav-link.active { background: #fff; border-color: #EC401B #EC401B #fff; color: #EC401B; }

.kpi-card { border-radius: 6px; border: 1px solid #e3e6f0; }
.border-left-primary  { border-left: 4px solid #EC401B !important; }
.border-left-success  { border-left: 4px solid #1cc88a !important; }
.border-left-info     { border-left: 4px solid #36b9cc !important; }
.border-left-warning  { border-left: 4px solid #f6c23e !important; }
.border-left-danger   { border-left: 4px solid #e74a3b !important; }
.border-left-secondary{ border-left: 4px solid #858796 !important; }
.text-xs { font-size: .7rem; }

#dashboardVentas .card-header { background: #f8f9fc; font-size: .85rem; }

/* Year toggle pills */
.year-pill { border-radius: 20px !important; font-size: .75rem !important; padding: 2px 10px !important; }

/* Orange gradient buttons */
#dashboardVentas .btn-primary {
    background: linear-gradient(135deg, #EC401B 0%, #F15533 100%) !important;
    border-color: #d4390f !important;
}
#dashboardVentas .btn-primary:hover {
    background: linear-gradient(135deg, #d4390f 0%, #EC401B 100%) !important;
}
/* Active year pill — orange */
.year-pill.btn-primary { background: linear-gradient(135deg, #EC401B 0%, #F15533 100%) !important; border-color: #d4390f !important; }
.year-pill.btn-outline-primary { color: #EC401B !important; border-color: #EC401B !important; }
.year-pill.btn-outline-primary:hover { background: rgba(236,64,27,.1) !important; }

/* BI skeleton shimmer */
@keyframes bi-shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
.skeleton-block {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: bi-shimmer 1.4s infinite;
    border-radius: 4px;
    display: block;
    margin-bottom: 8px;
}
/* Clickable chart cards */
.bi-clickable-chart { transition: box-shadow .2s ease; }
.bi-clickable-chart:hover { box-shadow: 0 4px 16px rgba(236,64,27,.18) !important; }
/* Vendor comparison checkboxes */
.cmp-vend-label { display:flex; align-items:center; gap:5px; cursor:pointer; font-size:.82rem; padding:3px 8px; border-radius:4px; border:1px solid #dee2e6; background:#fff; transition:all .15s; }
.cmp-vend-label:hover { border-color:#EC401B; background:rgba(236,64,27,.06); }
.cmp-vend-check:checked + .cmp-vend-label { border-color:#EC401B; background:rgba(236,64,27,.12); font-weight:600; color:#EC401B; }
.cmp-vend-check { display:none; }
#tabla-vendedores thead th, #tabla-clientes thead th, #tabla-productos thead th,
#tabla-marcas thead th, #tabla-comparacion thead th,
#tabla-semanal thead th {
    background-color: #343a40 !important;
    color: #fff !important;
    border-color: #454d55 !important;
}
/* Active filter bar */
.bi-filter-bar {
    background: linear-gradient(90deg, rgba(236,64,27,.06) 0%, rgba(241,85,51,.04) 100%);
    border: 1px solid rgba(236,64,27,.25);
    border-radius: 6px;
    padding: 6px 14px;
}
.bi-badge {
    background: linear-gradient(135deg,#EC401B,#F15533) !important;
    color: #fff !important;
    font-size: .73rem;
    padding: 4px 10px;
    cursor: pointer;
}
.bi-badge:hover { opacity: .8; }
.btn-xs { padding: 2px 8px; font-size: .72rem; }
/* ApexCharts pointer cursor on clickable elements */
.bi-clickable-chart .apexcharts-bar-area,
.bi-clickable-chart .apexcharts-pie-slice,
.bi-clickable-chart .apexcharts-donut-slice { cursor: pointer !important; }
</style>

{{-- ======================= SCRIPTS ======================= --}}
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
<script>
    window._profacAuthUser = '{{ addslashes(auth()->user()->name ?? "Usuario") }}';
</script>
<script src="{{ asset('js/js_proyecto/reportes/dashboard-ventas.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        dashboardVentas.init();
    });
</script>

</div>
