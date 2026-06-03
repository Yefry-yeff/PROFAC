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
                        <label class="small font-weight-bold">Asesor Comercial</label>
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
                    <div class="card kpi-card border-left-info h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-info text-uppercase">Facturación sin ISV</div>
                            <div class="mb-0 h5 font-weight-bold" id="kpi-sin-isv">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-primary h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-primary text-uppercase">Facturación con ISV</div>
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
                            <div class="mb-1 text-xs font-weight-bold text-primary text-uppercase">Mejor Asesor Comercial</div>
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
                        <label class="small font-weight-bold">Asesor Comercial</label>
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
                    <div class="card kpi-card border-left-info h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-info text-uppercase">Facturación sin ISV</div>
                            <div class="mb-0 h5 font-weight-bold" id="s-kpi-sin-isv">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-6 col-md-3">
                    <div class="card kpi-card border-left-primary h-100">
                        <div class="px-3 py-2 card-body">
                            <div class="mb-1 text-xs font-weight-bold text-primary text-uppercase">Facturación con ISV</div>
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
                            <div class="mb-1 text-xs font-weight-bold text-danger text-uppercase">Mejor Asesor Comercial</div>
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
                            <span class="font-weight-bold"><i class="fas fa-medal mr-1"></i> Top Asesores Comerciales del Período</span>
                            <small class="text-muted">Clic para filtrar por asesor comercial</small>
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
                            <span class="font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Crecimiento por Asesor Comercial</span>
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
                            <small class="text-muted" id="top-cli-sem-label">Todos los asesores comerciales</small>
                        </div>
                        <div class="p-2 card-body"><div id="chart-top-cli-sem" style="min-height:280px"></div></div>
                    </div>
                </div>
            </div>

            {{-- Tabla semanal --}}
            <div class="shadow-sm card">
                <div class="py-2 card-header d-flex align-items-center justify-content-between">
                    <span class="font-weight-bold">Detalle de Facturas</span>
                    <button class="btn btn-sm btn-success" onclick="dashboardVentas.exportarDetalleSemanal()">
                        <i class="fas fa-file-excel mr-1"></i>Excel
                    </button>
                </div>
                <div class="p-2 card-body table-responsive">
                    <table class="table table-striped table-bordered table-sm" id="tabla-semanal" style="width:100%">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Día</th>
                                <th>Semana</th>
                                <th>Documento</th>
                                <th>Cliente</th>
                                <th>Asesor Comercial</th>
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

            {{-- Sub-tabs analítica --}}
            <ul class="mb-3 nav nav-pills" id="adv-pills">
                <li class="nav-item">
                    <a class="nav-link active" id="pill-vend" data-toggle="pill" href="#pill-pane-vend">
                        <i class="mr-1 fas fa-user-tie"></i>Asesores Comerciales
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pill-cli" data-toggle="pill" href="#pill-pane-cli">
                        <i class="mr-1 fas fa-users"></i>Clientes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pill-marc" data-toggle="pill" href="#pill-pane-marc">
                        <i class="mr-1 fas fa-tag"></i>Marcas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pill-prod" data-toggle="pill" href="#pill-pane-prod">
                        <i class="mr-1 fas fa-box"></i>Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pill-comp" data-toggle="pill" href="#pill-pane-comp">
                        <i class="mr-1 fas fa-balance-scale"></i>Comparar Asesores Comerciales
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pill-tla" data-toggle="pill" href="#pill-pane-tla">
                        <i class="mr-1 fas fa-headset"></i>Tele-Asesor
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                {{-- VENDEDORES --}}
                <div class="tab-pane fade show active" id="pill-pane-vend">
                    <div class="mb-3 row">
                        <div class="col-md-5">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold">Ranking Asesores Comerciales (Total)</span></div>
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
                            <span class="font-weight-bold">Tabla Asesores Comerciales</span>
                            <button class="btn btn-sm btn-success" onclick="dashboardVentas.exportarExcel()">
                                <i class="fas fa-file-excel"></i> Excel + Gráficas
                            </button>
                        </div>
                        <div class="p-2 card-body table-responsive">
                            <table class="table table-striped table-sm" id="tabla-vendedores" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Asesor Comercial</th>
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

                    {{-- Filtros locales de Clientes --}}
                    <div class="mb-3 border card card-body bg-light py-2" id="cli-filtros">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="small font-weight-bold">Fecha inicio</label>
                                <input type="date" class="form-control form-control-sm" id="cli-fi">
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold">Fecha fin</label>
                                <input type="date" class="form-control form-control-sm" id="cli-ff">
                            </div>
                            <div class="col-md-4">
                                <label class="small font-weight-bold"><i class="fas fa-user mr-1 text-info"></i>Cliente</label>
                                <select class="form-control form-control-sm" id="cli-cliente">
                                    <option value="">Todos los clientes</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small font-weight-bold"><i class="fas fa-box mr-1 text-warning"></i>Producto</label>
                                <select class="form-control form-control-sm" id="cli-producto">
                                    <option value="">Todos los productos</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-2 row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="small font-weight-bold"><i class="fas fa-tag mr-1 text-secondary"></i>Marca</label>
                                <select class="form-control form-control-sm" id="cli-marca">
                                    <option value="">Todas las marcas</option>
                                </select>
                            </div>
                            <div class="col-md-8 d-flex justify-content-end" style="gap:6px">
                                <button class="btn btn-primary btn-sm px-4" onclick="dashboardVentas.cargarCli()">
                                    <i class="fas fa-search"></i> Consultar
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" title="Limpiar filtros" onclick="dashboardVentas.limpiarFiltroCli()">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                            </div>
                        </div>
                        <div id="cli-filtros-activos" class="mt-2 d-none">
                            <small class="text-muted"><i class="fas fa-filter mr-1"></i>Filtrando por:</small>
                            <span id="cli-badge-cliente" class="badge badge-info mr-1" style="display:none"></span>
                            <span id="cli-badge-producto" class="badge badge-warning mr-1" style="display:none"></span>
                            <span id="cli-badge-marca" class="badge badge-secondary mr-1" style="display:none"></span>
                        </div>
                    </div>

                    {{-- Fila 1: Top 15 clientes + Top 5 productos --}}
                    <div class="mb-3 row">
                        <div class="col-md-7">
                            <div class="shadow-sm card h-100">
                                <div class="py-2 card-header d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold"><i class="fas fa-trophy mr-1 text-warning"></i>Top 15 Clientes por Facturación</span>
                                    <small class="text-muted"><i class="fas fa-mouse-pointer mr-1"></i>Clic para filtrar</small>
                                </div>
                                <div class="p-2 card-body"><div id="chart-top-cli" style="min-height:320px"></div></div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="shadow-sm card h-100">
                                <div class="py-2 card-header">
                                    <span class="font-weight-bold"><i class="fas fa-box-open mr-1 text-primary"></i><span id="cli-prod-titulo">Top 5 Productos más Vendidos</span></span>
                                    <small class="text-muted d-block"><i class="fas fa-mouse-pointer mr-1"></i>Clic para filtrar por producto</small>
                                </div>
                                <div class="p-2 card-body"><div id="chart-top-prod-cli" style="min-height:320px"></div></div>
                            </div>
                        </div>
                    </div>

                    {{-- Fila 2: Frecuencia de compra + Evolución mensual --}}
                    <div class="mb-3 row">
                        <div class="col-md-5">
                            <div class="shadow-sm card h-100">
                                <div class="py-2 card-header d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold"><i class="fas fa-repeat mr-1 text-success"></i>Frecuencia de Compra por Cliente</span>
                                    <small class="text-muted"><i class="fas fa-mouse-pointer mr-1"></i>Clic para filtrar</small>
                                </div>
                                <div class="p-2 card-body"><div id="chart-freq-cli" style="min-height:300px"></div></div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="shadow-sm card h-100">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-chart-area mr-1 text-primary"></i>Evolución Mensual — Top Clientes</span></div>
                                <div class="p-2 card-body"><div id="chart-evol-cli" style="min-height:300px"></div></div>
                            </div>
                        </div>
                    </div>

                    {{-- Fila 3: Estado clientes + Evolución Cantidad --}}
                    <div class="mb-3 row">
                        <div class="col-md-4">
                            <div class="shadow-sm card h-100">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-heartbeat mr-1 text-danger"></i>Estado de Clientes</span></div>
                                <div class="p-2 card-body"><div id="chart-estado-cli" style="min-height:260px"></div></div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="shadow-sm card h-100">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-boxes mr-1 text-warning"></i>Evolución de Compra (Cantidad de Productos)</span></div>
                                <div class="p-2 card-body"><div id="chart-evol-cant-cli" style="min-height:260px"></div></div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabla Clientes --}}
                    <div class="shadow-sm card mb-3">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fas fa-users mr-1"></i>Resumen de Clientes</span>
                            <button class="btn btn-sm btn-success" onclick="dashboardVentas.exportarTablaExcel('tabla-clientes','Clientes')">
                                <i class="fas fa-file-excel"></i> Exportar Excel
                            </button>
                        </div>
                        <div class="p-2 card-body table-responsive">
                            <table class="table table-striped table-sm" id="tabla-clientes" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Mes</th>
                                        <th>Cliente</th>
                                        <th>Tipo</th>
                                        <th>Facturas</th>
                                        <th>Total Comprado</th>
                                        <th>Unidades</th>
                                        <th>Última Compra</th>
                                        <th>Días sin comprar</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-clientes"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Tabla Productos x Cliente --}}
                    <div class="shadow-sm card">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fas fa-table mr-1 text-info"></i>Detalle Productos por Cliente</span>
                            <button class="btn btn-sm btn-success" onclick="dashboardVentas.exportarTablaExcel('tabla-prod-cli','Productos_x_Cliente')">
                                <i class="fas fa-file-excel"></i> Exportar Excel
                            </button>
                        </div>
                        <div class="p-2 card-body table-responsive">
                            <table class="table table-striped table-sm" id="tabla-prod-cli" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Mes</th>
                                        <th>Cliente</th>
                                        <th>Tipo</th>
                                        <th>Producto</th>
                                        <th>Marca</th>
                                        <th>Categoría</th>
                                        <th>Facturas</th>
                                        <th>Unidades</th>
                                        <th>Total Comprado</th>
                                        <th>Últ. Compra</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-prod-cli"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Tabla Detalle por Factura --}}
                    <div class="shadow-sm card mt-3">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fas fa-file-invoice-dollar mr-1 text-success"></i>Detalle por Factura</span>
                            <button class="btn btn-sm btn-success" onclick="dashboardVentas.exportarTablaExcel('tabla-facturas-cli','Facturas_x_Cliente')">
                                <i class="fas fa-file-excel"></i> Exportar Excel
                            </button>
                        </div>
                        <div class="p-2 card-body table-responsive">
                            <table class="table table-striped table-sm" id="tabla-facturas-cli" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th>N° Factura</th>
                                        <th>Cliente</th>
                                        <th>Asesor Comercial</th>
                                        <th>Tele Asesor</th>
                                        <th class="text-right">Subtotal (Sin ISV)</th>
                                        <th class="text-right">ISV</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-facturas-cli"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- MARCAS --}}
                <div class="tab-pane fade" id="pill-pane-marc">

                    {{-- Filtros Marcas --}}
                    <div class="mb-3 border card card-body bg-light py-2" id="marc-filtros">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="small font-weight-bold">Fecha inicio</label>
                                <input type="date" class="form-control form-control-sm" id="marc-fi">
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold">Fecha fin</label>
                                <input type="date" class="form-control form-control-sm" id="marc-ff">
                            </div>
                            <div class="col-md-4">
                                <label class="small font-weight-bold"><i class="fas fa-user mr-1 text-info"></i>Cliente</label>
                                <select class="form-control form-control-sm" id="marc-cliente">
                                    <option value="">Todos los clientes</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small font-weight-bold"><i class="fas fa-box mr-1 text-warning"></i>Producto</label>
                                <select class="form-control form-control-sm" id="marc-producto">
                                    <option value="">Todos los productos</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-2 row g-2 align-items-end">
                            <div class="col-md-12 d-flex justify-content-end" style="gap:6px">
                                <button class="btn btn-primary btn-sm px-4" onclick="dashboardVentas.cargarMarcas()">
                                    <i class="fas fa-search"></i> Consultar
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="dashboardVentas.limpiarFiltroMarc()">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Fila 1: Top marcas bar + donut participación --}}
                    <div class="mb-3 row">
                        <div class="col-md-7">
                            <div class="shadow-sm card h-100">
                                <div class="py-2 card-header d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold"><i class="fas fa-trophy mr-1 text-warning"></i>Marcas por Facturación (L.)</span>
                                    <small class="text-muted"><i class="fas fa-mouse-pointer mr-1"></i>Clic en barra para ver detalle</small>
                                </div>
                                <div class="p-2 card-body"><div id="chart-marc-bar" style="min-height:340px"></div></div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="shadow-sm card h-100">
                                <div class="py-2 card-header">
                                    <span class="font-weight-bold"><i class="fas fa-chart-pie mr-1 text-info"></i>Participación por Marca (%)</span>
                                </div>
                                <div class="p-2 card-body"><div id="chart-marc-donut" style="min-height:340px"></div></div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabla Marcas --}}
                    <div class="shadow-sm card">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fas fa-table mr-1 text-warning"></i>Detalle por Marca</span>
                            <button class="btn btn-sm btn-success" onclick="dashboardVentas.exportarTablaExcel('tabla-marcas-cli','Marcas')">
                                <i class="fas fa-file-excel"></i> Exportar Excel
                            </button>
                        </div>
                        <div class="p-2 card-body table-responsive">
                            <table class="table table-striped table-sm" id="tabla-marcas-cli" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Marca</th>
                                        <th>Clientes</th>
                                        <th>Facturas</th>
                                        <th>Productos</th>
                                        <th>Unidades</th>
                                        <th>Total Vendido</th>
                                        <th>Participación %</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-marcas-cli"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- PRODUCTOS --}}
                <div class="tab-pane fade" id="pill-pane-prod">
                    <div class="mb-3 border card card-body bg-light">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="small font-weight-bold">Fecha desde</label>
                                <input type="date" class="form-control form-control-sm" id="prod-fi">
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold">Fecha hasta</label>
                                <input type="date" class="form-control form-control-sm" id="prod-ff">
                            </div>
                            <div class="col-md-6">
                                <label class="small font-weight-bold"><i class="fas fa-box mr-1 text-primary"></i>Producto</label>
                                <select class="form-control form-control-sm" id="prod-filtro-producto">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-2 row g-2 align-items-end">
                            <div class="col-md-12 d-flex justify-content-end" style="gap:6px">
                                <button class="btn btn-outline-secondary btn-sm" onclick="dashboardVentas.limpiarDashboardProductos()">
                                    <i class="fas fa-times"></i> Limpiar filtros
                                </button>
                                <button class="btn btn-primary btn-sm px-4" onclick="dashboardVentas.cargarDashboardProductos()">
                                    <i class="fas fa-search"></i> Consultar
                                </button>
                                <button class="btn btn-success btn-sm" onclick="dashboardVentas.exportarVistaProductosExcel()">
                                    <i class="fas fa-file-excel"></i> Descargar vista completa
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 text-muted" style="font-size:.8rem;">
                            <strong>Escala seleccionada:</strong> <span id="prod-escala">Sin aplicar</span>
                        </div>
                    </div>

                    <div class="mb-2 row">
                        <div class="col-12"><h6 class="font-weight-bold text-primary mb-2">Resumen General</h6></div>
                        <div class="col-md-2"><div class="kpi-card p-3 border-left-info"><div class="text-xs text-uppercase text-muted">Cantidad vendida</div><div class="h6 mb-0" id="prod-kpi-cantidad">0</div></div></div>
                        <div class="col-md-2"><div class="kpi-card p-3 border-left-success"><div class="text-xs text-uppercase text-muted">Nro facturas</div><div class="h6 mb-0" id="prod-kpi-facturas">0</div></div></div>
                        <div class="col-md-2"><div class="kpi-card p-3 border-left-warning"><div class="text-xs text-uppercase text-muted">Cantidad de clientes</div><div class="h6 mb-0" id="prod-kpi-clientes">0</div></div></div>
                        <div class="col-md-2"><div class="kpi-card p-3 border-left-secondary"><div class="text-xs text-uppercase text-muted">Costo total</div><div class="h6 mb-0" id="prod-kpi-costo-total">L. 0.00</div></div></div>
                        <div class="col-md-2"><div class="kpi-card p-3 border-left-primary"><div class="text-xs text-uppercase text-muted">Venta total</div><div class="h6 mb-0" id="prod-kpi-venta-total">L. 0.00</div></div></div>
                        <div class="col-md-2"><div class="kpi-card p-3 border-left-danger"><div class="text-xs text-uppercase text-muted">Utilidad Bruta</div><div class="h6 mb-0" id="prod-kpi-utilidad-bruta">L. 0.00</div></div></div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-12">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-box mr-1"></i>Resumen del Producto Seleccionado</span></div>
                                <div class="p-2 card-body">
                                    <div class="row">
                                        <div class="col-md-2"><small class="text-muted d-block">Producto</small><strong id="prod-res-nombre">-</strong></div>
                                        <div class="col-md-2"><small class="text-muted d-block">Código</small><strong id="prod-res-codigo">-</strong></div>
                                        <div class="col-md-2"><small class="text-muted d-block">Marca</small><strong id="prod-res-marca">-</strong></div>
                                        <div class="col-md-2"><small class="text-muted d-block">Categoría</small><strong id="prod-res-categoria">-</strong></div>
                                        <div class="col-md-2"><small class="text-muted d-block">Precio costo</small><strong id="prod-res-precio-costo">L. 0.00</strong></div>
                                        <div class="col-md-2"><small class="text-muted d-block">Existencia</small><strong id="prod-res-existencia">0</strong></div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-2"><small class="text-muted d-block">Total vendido</small><strong id="prod-res-total">L. 0.00</strong></div>
                                        <div class="col-md-2"><small class="text-muted d-block">Unidades</small><strong id="prod-res-unidades">0</strong></div>
                                        <div class="col-md-3"><small class="text-muted d-block">Clientes que compraron</small><strong id="prod-res-clientes">0</strong></div>
                                        <div class="col-md-3"><small class="text-muted d-block">Última venta</small><strong id="prod-res-ultima">-</strong></div>
                                        <div class="col-md-3"><small class="text-muted d-block">Promedio mensual</small><strong id="prod-res-promedio">L. 0.00</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-12">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold">Evolución de Ventas (Día/Semana/Mes)</span></div>
                                <div class="p-2 card-body"><div id="chart-prod-evolucion" style="min-height:340px"></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-md-6">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-star mr-1 text-warning"></i>Top Clientes de este Producto</span></div>
                                <div class="p-2 card-body"><div id="chart-prod-top-clientes" style="min-height:320px"></div></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-user-tie mr-1 text-primary"></i>Top Asesores Comerciales que mueven este producto</span></div>
                                <div class="p-2 card-body"><div id="chart-prod-top-vendedores" style="min-height:320px"></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 shadow-sm card">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Últimas Facturas de este Producto</span>
                            <div>
                                <button class="btn btn-sm btn-success" onclick="dashboardVentas.exportarTablaProductosExcel('tabla-prod-fact-det','ultimas-facturas-producto.xlsx','Facturas Producto')">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="dashboardVentas.exportarFacturasProductoPDF()">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                            </div>
                        </div>
                        <div class="p-2 card-body table-responsive">
                            <table class="table table-striped table-sm" id="tabla-prod-fact-det" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Número factura</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Escala</th>
                                        <th>Asesor Comercial</th>
                                        <th>Producto</th>
                                        <th class="text-right">Cantidad</th>
                                        <th class="text-right">Precio base venta</th>
                                        <th class="text-right">Precio unitario</th>
                                        <th class="text-right">Descuento</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-right">Costo total</th>
                                        <th class="text-right">Utilidad bruta</th>
                                        <th class="text-right">Total factura</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-prod-fact-det"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-12">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold"><i class="fas fa-history mr-1"></i>Histórico de clientes que compraron este producto</span>
                                    <button class="btn btn-sm btn-success" onclick="dashboardVentas.exportarTablaProductosExcel('tabla-prod-ranking-cli','historico-clientes-producto.xlsx','Historico Clientes')">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </button>
                                </div>
                                <div class="p-2 card-body table-responsive">
                                    <table class="table table-striped table-sm" id="tabla-prod-ranking-cli" style="width:100%">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Cliente</th>
                                                <th class="text-right">Compras</th>
                                                <th class="text-right">Monto</th>
                                                <th class="text-right">Unidades</th>
                                                <th>Última compra</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-prod-ranking-cli"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Indicadores de Clientes --}}
                    <div class="mb-3 row">
                        <div class="col-md-4">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold">Indicadores de Clientes</span></div>
                                <div class="p-3 card-body">
                                    <p class="mb-2"><small class="text-muted d-block">Cliente que más compra</small><strong id="prod-ind-mas-compra">-</strong></p>
                                    <p class="mb-2"><small class="text-muted d-block">Cliente con mayor frecuencia</small><strong id="prod-ind-frecuencia">-</strong></p>
                                    <p class="mb-0"><small class="text-muted d-block">Cliente con mayor volumen</small><strong id="prod-ind-volumen">-</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 mb-3 shadow-sm card">
                        <div class="py-2 card-header d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Tabla de Producto Completa</span>
                            <button class="btn btn-sm btn-success" onclick="dashboardVentas.exportarTablaProductosExcel('tabla-productos','tabla-producto-completa.xlsx','Tabla Producto Completa')">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </div>
                        <div class="p-2 card-body table-responsive">
                            <table class="table table-striped table-sm" id="tabla-productos" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Número factura</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Escala</th>
                                        <th>Asesor Comercial</th>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th class="text-right">Cantidad</th>
                                        <th class="text-right">Precio base venta</th>
                                        <th class="text-right">Precio unitario factura</th>
                                        <th class="text-right">Venta factura</th>
                                        <th class="text-right">Costo total</th>
                                        <th class="text-right">Utilidad bruta</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-productos"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- COMPARAR VENDEDORES --}}
                <div class="tab-pane fade" id="pill-pane-comp">
                    {{-- Filtros comparación --}}
                    <div class="mb-3 border card card-body bg-light">
                        <div class="row g-2 mb-2">
                            <div class="col-md-2">
                                <label class="small font-weight-bold">Fecha inicio</label>
                                <input type="date" class="form-control form-control-sm" id="cmp-fi">
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold">Fecha fin</label>
                                <input type="date" class="form-control form-control-sm" id="cmp-ff">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary btn-sm btn-block" onclick="dashboardVentas.cargarComparacion()">
                                    <i class="fas fa-exchange-alt"></i> Comparar
                                </button>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-12">
                                <label class="small font-weight-bold">Seleccionar asesores comerciales a comparar</label>
                                <div id="cmp-vend-checks" class="d-flex flex-wrap py-1 border rounded bg-white px-2" style="gap:8px; min-height:50px; max-height:130px; overflow-y:auto;"></div>
                            </div>
                        </div>
                    </div>

                    {{-- KPI cards comparación --}}
                    <div class="mb-3 row" id="cmp-kpi-cards"></div>

                    {{-- Gráficas comparación --}}
                    <div class="mb-3 row">
                        <div class="col-12">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-chart-line mr-1"></i> Evolución Mensual por Asesor Comercial</span></div>
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

                    {{-- Resumen escalas por vendedor (tabs) --}}
                    <div class="shadow-sm card">
                        <div class="py-2 card-header"><span class="font-weight-bold">Resumen por Asesor Comercial — Escalas de Precio</span></div>
                        <div class="card-body p-0">
                            <div id="cmp-esc-empty" class="p-3 text-muted text-center small" style="display:none">
                                Sin datos. Seleccione asesores comerciales y presione Comparar.
                            </div>
                            <ul class="nav nav-tabs border-bottom px-3 pt-2" id="cmp-esc-tabs"></ul>
                            <div class="tab-content px-3 pb-3" id="cmp-esc-content"></div>
                        </div>
                    </div>
                </div>

                {{-- TELE-ASESOR --}}
                <div class="tab-pane fade" id="pill-pane-tla">
                    {{-- Filtros --}}
                    <div class="mb-3 border card card-body bg-light">
                        <div class="row g-2 mb-2">
                            <div class="col-md-2">
                                <label class="small font-weight-bold">Fecha inicio</label>
                                <input type="date" class="form-control form-control-sm" id="tla-fi">
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold">Fecha fin</label>
                                <input type="date" class="form-control form-control-sm" id="tla-ff">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary btn-sm btn-block" onclick="dashboardVentas.cargarComparacionTla()">
                                    <i class="fas fa-headset"></i> Comparar
                                </button>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-12">
                                <label class="small font-weight-bold">Seleccionar tele-asesores a comparar</label>
                                <div id="tla-vend-checks" class="d-flex flex-wrap py-1 border rounded bg-white px-2" style="gap:8px; min-height:50px; max-height:130px; overflow-y:auto;"></div>
                            </div>
                        </div>
                    </div>

                    {{-- KPI cards --}}
                    <div class="mb-3 row" id="tla-kpi-cards"></div>

                    {{-- Gráficas --}}
                    <div class="mb-3 row">
                        <div class="col-12">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-chart-line mr-1"></i> Evolución Mensual por Tele-Asesor</span></div>
                                <div class="p-2 card-body"><div id="chart-tla-evolucion" style="min-height:380px"></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-md-6">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Total del Período</span></div>
                                <div class="p-2 card-body"><div id="chart-tla-total" style="min-height:300px"></div></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="shadow-sm card">
                                <div class="py-2 card-header"><span class="font-weight-bold"><i class="fas fa-chart-pie mr-1"></i> Participación de Mercado</span></div>
                                <div class="p-2 card-body"><div id="chart-tla-part" style="min-height:300px"></div></div>
                            </div>
                        </div>
                    </div>

                    {{-- Resumen escalas por tele-asesor (tabs) --}}
                    <div class="shadow-sm card">
                        <div class="py-2 card-header"><span class="font-weight-bold">Resumen por Tele-Asesor — Escalas de Precio</span></div>
                        <div class="card-body p-0">
                            <div id="tla-esc-empty" class="p-3 text-muted text-center small" style="display:none">
                                Sin datos. Seleccione tele-asesores y presione Comparar.
                            </div>
                            <ul class="nav nav-tabs border-bottom px-3 pt-2" id="tla-esc-tabs"></ul>
                            <div class="tab-content px-3 pb-3" id="tla-esc-content"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        {{-- /PESTAÑA 3 --}}

    </div>{{-- /tab-content --}}
</div>{{-- /container-fluid --}}

{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 1: Facturas por vendedor/escala (Comparar Vendedores)
═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-cmp-facturas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-cmp-positioned" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#343a40; color:#fff;">
                <h5 class="mb-0 modal-title" id="modal-cmp-facturas-title"><i class="fas fa-file-invoice mr-2"></i>Detalle de Facturas</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                {{-- Resumen rápido --}}
                <div class="px-3 py-2 d-flex flex-wrap" id="modal-cmp-fact-kpis" style="gap:12px; background:#f8f9fc; border-bottom:1px solid #e3e6f0;"></div>
                {{-- Tabla --}}
                <div class="table-responsive px-3 pt-2" style="max-height:52vh; overflow-y:auto;">
                    <table class="table table-sm table-bordered table-hover mb-0" id="tabla-cmp-facturas" style="width:100%">
                        <thead class="thead-dark" style="position:sticky;top:0;z-index:1;">
                            <tr>
                                <th>Documento</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Cat. Cliente</th>
                                <th>Tipo Cliente</th>
                                <th class="text-right">Líneas</th>
                                <th class="text-right">Sin ISV</th>
                                <th class="text-right">ISV</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-cmp-facturas"></tbody>
                    </table>
                </div>
                {{-- Paginación --}}
                <div class="px-3 py-2 d-flex align-items-center justify-content-between border-top" id="cmp-fact-pagination" style="background:#f8f9fc; display:none!important;">
                    <small class="text-muted" id="cmp-fact-pag-info"></small>
                    <ul class="pagination pagination-sm mb-0" id="cmp-fact-pag-links"></ul>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-success btn-sm" id="btn-cmp-fact-excel">
                    <i class="fas fa-file-excel mr-1"></i>Exportar Excel
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 2: Productos de una factura (Comparar Vendedores)
═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-cmp-productos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-cmp-positioned" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#EC401B; color:#fff;">
                <h5 class="mb-0 modal-title" id="modal-cmp-prod-title"><i class="fas fa-boxes mr-2"></i>Productos de la Factura</h5>
                <button type="button" class="close text-white" id="btn-cmp-prod-x"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                {{-- Info de cabecera --}}
                <div class="px-3 py-2 d-flex flex-wrap" id="modal-cmp-prod-header" style="gap:16px; background:#fff8f6; border-bottom:1px solid #fde0d8; font-size:.83rem;"></div>
                {{-- Tabla --}}
                <div class="table-responsive px-3 pt-2" style="max-height:52vh; overflow-y:auto;">
                    <table class="table table-sm table-bordered mb-0" id="tabla-cmp-productos" style="width:100%">
                        <thead class="thead-dark" style="position:sticky;top:0;z-index:1;">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Escala de Precio</th>
                                <th>Cat. Cliente</th>
                                <th>Tipo Cliente</th>
                                <th class="text-right">Precio Unit.</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Subtotal sin ISV</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-cmp-productos"></tbody>
                        <tfoot>
                            <tr class="font-weight-bold" style="background:#fff3f0">
                                <td colspan="7" class="text-right">TOTAL sin ISV:</td>
                                <td class="text-right" id="tfoot-cmp-total"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-cmp-prod-back">
                    <i class="fas fa-arrow-left mr-1"></i>Volver a Facturas
                </button>
                <button type="button" class="btn btn-success btn-sm" id="btn-cmp-prod-excel">
                    <i class="fas fa-file-excel mr-1"></i>Exportar Excel
                </button>
                <a id="btn-cmp-ver-factura" href="#" target="_blank" class="btn btn-sm btn-primary">
                    <i class="fas fa-print mr-1"></i>Ver Factura
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 1 TLA: Facturas por tele-asesor/escala
═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-tla-facturas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-cmp-positioned" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#343a40; color:#fff;">
                <h5 class="mb-0 modal-title" id="modal-tla-facturas-title"><i class="fas fa-file-invoice mr-2"></i>Detalle de Facturas</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <div class="px-3 py-2 d-flex flex-wrap" id="modal-tla-fact-kpis" style="gap:12px; background:#f8f9fc; border-bottom:1px solid #e3e6f0;"></div>
                <div class="table-responsive px-3 pt-2" style="max-height:52vh; overflow-y:auto;">
                    <table class="table table-sm table-bordered table-hover mb-0" id="tabla-tla-facturas" style="width:100%">
                        <thead class="thead-dark" style="position:sticky;top:0;z-index:1;">
                            <tr>
                                <th>Documento</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Cat. Cliente</th>
                                <th>Tipo Cliente</th>
                                <th class="text-right">Líneas</th>
                                <th class="text-right">Sin ISV</th>
                                <th class="text-right">ISV</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-tla-facturas"></tbody>
                    </table>
                </div>
                <div class="px-3 py-2 d-flex align-items-center justify-content-between border-top" id="tla-fact-pagination" style="background:#f8f9fc; display:none!important;">
                    <small class="text-muted" id="tla-fact-pag-info"></small>
                    <ul class="pagination pagination-sm mb-0" id="tla-fact-pag-links"></ul>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-success btn-sm" id="btn-tla-fact-excel">
                    <i class="fas fa-file-excel mr-1"></i>Exportar Excel
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 2 TLA: Productos de una factura (Tele-Asesor)
═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-tla-productos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-cmp-positioned" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#EC401B; color:#fff;">
                <h5 class="mb-0 modal-title" id="modal-tla-prod-title"><i class="fas fa-boxes mr-2"></i>Productos de la Factura</h5>
                <button type="button" class="close text-white" id="btn-tla-prod-x"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <div class="px-3 py-2 d-flex flex-wrap" id="modal-tla-prod-header" style="gap:16px; background:#fff8f6; border-bottom:1px solid #fde0d8; font-size:.83rem;"></div>
                <div class="table-responsive px-3 pt-2" style="max-height:52vh; overflow-y:auto;">
                    <table class="table table-sm table-bordered mb-0" id="tabla-tla-productos" style="width:100%">
                        <thead class="thead-dark" style="position:sticky;top:0;z-index:1;">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Escala de Precio</th>
                                <th>Cat. Cliente</th>
                                <th>Tipo Cliente</th>
                                <th class="text-right">Precio Unit.</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Subtotal sin ISV</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-tla-productos"></tbody>
                        <tfoot>
                            <tr class="font-weight-bold" style="background:#fff3f0">
                                <td colspan="7" class="text-right">TOTAL sin ISV:</td>
                                <td class="text-right" id="tfoot-tla-total"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-tla-prod-back">
                    <i class="fas fa-arrow-left mr-1"></i>Volver a Facturas
                </button>
                <button type="button" class="btn btn-success btn-sm" id="btn-tla-prod-excel">
                    <i class="fas fa-file-excel mr-1"></i>Exportar Excel
                </button>
                <a id="btn-tla-ver-factura" href="#" target="_blank" class="btn btn-sm btn-primary">
                    <i class="fas fa-print mr-1"></i>Ver Factura
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ======================= ESTILOS ======================= --}}
<style>
/* Modal comparar vendedores: posicionado debajo del navbar y con espacio del sidebar */
.modal-cmp-positioned {
    margin-top: 75px;          /* debajo del navbar (65px) + 10px espacio */
    margin-left: calc(220px + 20px); /* sidebar (220px) + margen */
    margin-right: 20px;
    max-width: calc(100vw - 220px - 50px);
    width: 100%;
}
@media (max-width: 768px) {
    .modal-cmp-positioned {
        margin-left: 10px;
        margin-right: 10px;
        max-width: calc(100vw - 20px);
    }
}
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
#tabla-marcas thead th, #tabla-categorias thead th,
#tabla-prod-alta-rotacion thead th, #tabla-prod-baja-rotacion thead th,
#tabla-comparacion thead th,
#tabla-semanal thead th,
#tabla-prod-fact-det thead th,
#tabla-prod-ranking-cli thead th,
#tabla-prod-cli thead th,
#tabla-marcas-cli thead th,
#tabla-facturas-cli thead th {
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
    (function () {
        function initDashboardVentasSafe() {
            var root = document.getElementById('dashboardVentas');
            if (!root) return;
            if (root.dataset.biInit === '1') return;
            if (!window.dashboardVentas || typeof window.dashboardVentas.init !== 'function') return;

            root.dataset.biInit = '1';
            window.dashboardVentas.init();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDashboardVentasSafe);
        } else {
            initDashboardVentasSafe();
        }

        document.addEventListener('livewire:load', initDashboardVentasSafe);
        document.addEventListener('livewire:navigated', initDashboardVentasSafe);
    })();
</script>

</div>
