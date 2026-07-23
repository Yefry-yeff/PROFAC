@push('styles')
<style>
/* ===== DASHBOARD LOGÍSTICA ===== */
.dl-page { font-family: 'Source Sans Pro', sans-serif; }

.dl-header {
    background: linear-gradient(135deg, #0f766e 0%, #0d9488 60%, #14b8a6 100%);
    padding: 1.5rem 1.75rem;
    border-radius: 14px 14px 0 0;
}
.dl-header h4 { color: #fff; font-weight: 700; margin: 0; font-size: 1.25rem; }
.dl-header small { color: rgba(255,255,255,.75); font-size: .82rem; }
.dl-hero-icon {
    width: 52px; height: 52px;
    background: rgba(255,255,255,.18);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    margin-right: 1rem; font-size: 1.5rem; color: #fff; flex-shrink: 0;
}

.dl-main-card {
    border: none;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,.08);
    margin-bottom: 1.5rem;
}

/* KPI Cards */
.dl-kpi { border-left: 4px solid; border-radius: 8px; transition: transform .15s; }
.dl-kpi:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.1); }
.dl-kpi .kpi-val { font-size: 1.6rem; font-weight: 700; line-height: 1.2; }
.dl-kpi .kpi-lbl { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }

/* Tabs */
.nav-tabs-teal .nav-link { color: #555; font-weight: 600; border: none; border-bottom: 3px solid transparent; }
.nav-tabs-teal .nav-link.active { color: #0d9488; border-bottom-color: #0d9488; background: transparent; }
.nav-tabs-teal .nav-link:hover:not(.active) { border-bottom-color: #a7f3d0; color: #0d9488; }

/* Filter bar */
.dl-filter-bar { background: #f8fffe; border: 1px solid #b2dfdb; border-radius: 8px; padding: .85rem 1rem; }

/* Loader teal */
.text-teal { color: #0d9488 !important; }

/* Progress slim */
.progress { border-radius: 20px; }
</style>
@endpush

<div class="dl-page">

    {{-- ── CARD PRINCIPAL ── --}}
    <div class="dl-main-card">

        {{-- Header --}}
        <div class="dl-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="dl-hero-icon"><i class="fas fa-truck"></i></div>
                <div>
                    <h4>Analítica Logística</h4>
                    <small>Rendimiento de entregas · PROFAC</small>
                </div>
            </div>
            <div>
                <button class="btn btn-sm btn-outline-light" onclick="dashLogistica.consultar()">
                    <i class="fas fa-sync-alt mr-1"></i> Actualizar
                </button>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="px-3 pt-3">
            <div class="dl-filter-bar">
                <div class="row align-items-end">
                    <div class="col-6 col-md-2 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Fecha inicio</label>
                        <input type="date" class="form-control form-control-sm" id="l-fi">
                    </div>
                    <div class="col-6 col-md-2 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Fecha fin</label>
                        <input type="date" class="form-control form-control-sm" id="l-ff">
                    </div>
                    <div class="col-6 col-md-3 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Equipo de entrega</label>
                        <select class="form-control form-control-sm" id="l-equipo">
                            <option value="">Todos los equipos</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Estado distribución</label>
                        <select class="form-control form-control-sm" id="l-estado">
                            <option value="">Todos</option>
                            <option value="1">Pendiente</option>
                            <option value="2">En Proceso</option>
                            <option value="3">Completada</option>
                            <option value="4">Cancelada</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2 mb-2 mb-md-0" id="wrap-estado-factura" style="display:none">
                    <label class="small font-weight-bold mb-1">Estado factura</label>
                        <select class="form-control form-control-sm" id="l-estado-fact">
                            <option value="">Todos</option>
                            <option value="entregado">Entregado</option>
                            <option value="parcial">Parcial</option>
                            <option value="sin_entrega">Sin Entregar</option>
                            <option value="anulada">Anulada</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2 mb-2 mb-md-0">
                        <button class="btn btn-sm btn-block" onclick="dashLogistica.consultar()"
                            style="background: linear-gradient(135deg,#0f766e,#14b8a6); color:#fff; border:none; border-radius:8px;">
                            <i class="fas fa-search mr-1"></i> Consultar
                        </button>
                    </div>
                    <div class="col-6 col-md-1">
                        <button class="btn btn-sm btn-outline-success btn-block" onclick="dashLogistica.exportarCSV()"
                            title="Exportar CSV">
                            <i class="fas fa-file-csv"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="px-3 pt-3">
            <h6 class="text-uppercase font-weight-bold small mb-2" style="color:#0d9488; letter-spacing:.5px">
                <i class="fas fa-file-invoice-dollar mr-1"></i>Facturación
            </h6>
            <div class="row">
                <div class="col-6 col-md-3 col-lg mb-3">
                    <div class="card dl-kpi h-100" style="border-left-color:#36b9cc">
                        <div class="px-3 py-2 card-body">
                            <div class="kpi-lbl text-info">Total Facturas</div>
                            <div class="kpi-val text-info" id="kpi-fact-gen">—</div>
                            <div class="small text-muted mt-1" id="kpi-fact-gen-sub">generadas del período</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg mb-3">
                    <div class="card dl-kpi h-100" style="border-left-color:#f6c23e"
                         title="Facturas ya asignadas a una distribución activa que aún no se han entregado (sin_entrega/parcial), emitidas dentro del período filtrado">
                        <div class="px-3 py-2 card-body">
                            <div class="kpi-lbl text-warning">Pendientes Asignadas</div>
                            <div class="kpi-val text-warning" id="kpi-pend-asignadas">—</div>
                            <div class="small text-muted mt-1">en distribución, sin entregar</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg mb-3">
                    <div class="card dl-kpi h-100" style="border-left-color:#dc6803"
                         title="Facturas que no han sido agregadas a ninguna distribución activa, emitidas dentro del período filtrado">
                        <div class="px-3 py-2 card-body">
                            <div class="kpi-lbl" style="color:#dc6803">Pendientes sin Asignar</div>
                            <div class="kpi-val" style="color:#dc6803" id="kpi-pend-sin-asignar">—</div>
                            <div class="small text-muted mt-1">sin distribución asignada</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg mb-3">
                    <div class="card dl-kpi h-100" style="border-left-color:#0d9488"
                         title="(Total de facturas generadas del período − Pendientes reales) / Total de facturas generadas">
                        <div class="px-3 py-2 card-body">
                            <div class="kpi-lbl" style="color:#0d9488">Efectividad</div>
                            <div class="kpi-val" style="color:#0d9488" id="kpi-efect">—</div>
                            <div class="small text-muted mt-1" id="kpi-efect-sub">generadas vs. pendientes reales</div>
                        </div>
                    </div>
                </div>
            </div>

            <h6 class="text-uppercase font-weight-bold small mb-2 mt-1" style="color:#0d9488; letter-spacing:.5px">
                <i class="fas fa-truck mr-1"></i>Distribución
            </h6>
            <div class="row">
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="card dl-kpi h-100" style="border-left-color:#0d9488">
                        <div class="px-3 py-2 card-body">
                            <div class="kpi-lbl" style="color:#0d9488">Distribuciones</div>
                            <div class="kpi-val" id="kpi-dist">—</div>
                            <div class="small text-muted mt-1">del período</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="card dl-kpi h-100" style="border-left-color:#36b9cc">
                        <div class="px-3 py-2 card-body">
                            <div class="kpi-lbl text-info">Total Facturas</div>
                            <div class="kpi-val text-info" id="kpi-fact">—</div>
                            <div class="small text-muted mt-1">asignadas al período</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="card dl-kpi h-100" style="border-left-color:#1cc88a">
                        <div class="px-3 py-2 card-body">
                            <div class="kpi-lbl text-success">Entregadas</div>
                            <div class="kpi-val text-success" id="kpi-entr">—</div>
                            <div class="small text-muted mt-1">confirmadas</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="card dl-kpi h-100" style="border-left-color:#e74a3b">
                        <div class="px-3 py-2 card-body">
                            <div class="kpi-lbl text-danger">Anuladas</div>
                            <div class="kpi-val text-danger" id="kpi-anul">—</div>
                            <div class="small text-muted mt-1">facturas anuladas</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="card dl-kpi h-100" style="border-left-color:#1cc88a">
                        <div class="px-3 py-2 card-body">
                            <div class="kpi-lbl text-success">Completadas</div>
                            <div class="kpi-val text-success" id="kpi-comp">—</div>
                            <div class="small text-muted mt-1">distribuciones finalizadas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="px-3 pb-0 pt-1">
            <ul class="nav nav-tabs nav-tabs-teal" id="logTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-resumen" data-toggle="tab" href="#pane-resumen" role="tab">
                        <i class="fas fa-chart-bar mr-1"></i>Resumen
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-detalle" data-toggle="tab" href="#pane-detalle" role="tab">
                        <i class="fas fa-table mr-1"></i>Por Distribución
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-facturas" data-toggle="tab" href="#pane-facturas" role="tab">
                        <i class="fas fa-file-invoice mr-1"></i>Por Factura
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-equipos" data-toggle="tab" href="#pane-equipos" role="tab">
                        <i class="fas fa-users-cog mr-1"></i>Por Equipo
                    </a>
                </li>
            </ul>
        </div>

        {{-- Tab content --}}
        <div class="tab-content px-3 pb-3 pt-3">

            {{-- ── PESTAÑA RESUMEN ── --}}
            <div class="tab-pane fade show active" id="pane-resumen" role="tabpanel">

                {{-- Chip de filtro activo (clic en gráficos) --}}
                <div id="resumen-filtro-chip" class="alert alert-info d-flex justify-content-between align-items-center py-2 mb-3" style="display:none">
                    <span id="resumen-filtro-txt"></span>
                    <button class="btn btn-sm btn-outline-secondary" onclick="dashLogistica.limpiarFiltroClick()">
                        <i class="fas fa-times mr-1"></i> Quitar filtro
                    </button>
                </div>

                {{-- Fila 1: Evolución --}}
                <div class="mb-3 row">
                    <div class="col-12">
                        <div class="shadow-sm card">
                            <div class="py-2 px-3 card-header d-flex align-items-center">
                                <span class="font-weight-bold">
                                    <i class="fas fa-chart-area mr-1 text-teal"></i>
                                    Evolución de Entregas por Día
                                </span>
                            </div>
                            <div class="p-2 card-body">
                                <div id="chart-evolucion" style="min-height:320px">
                                    <div class="text-center py-5">
                                        <i class="fas fa-spinner fa-spin fa-2x text-teal"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Fila 2: Por equipo + Estados --}}
                <div class="mb-3 row">
                    <div class="col-md-7 mb-3 mb-md-0">
                        <div class="shadow-sm card h-100">
                            <div class="py-2 px-3 card-header">
                                <span class="font-weight-bold">
                                    <i class="fas fa-users mr-1 text-teal"></i>
                                    Rendimiento por Equipo
                                </span>
                            </div>
                            <div class="p-2 card-body">
                                <div id="chart-equipos" style="min-height:300px">
                                    <div class="text-center py-5">
                                        <i class="fas fa-spinner fa-spin fa-2x text-teal"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="shadow-sm card h-100">
                            <div class="py-2 px-3 card-header">
                                <span class="font-weight-bold">
                                    <i class="fas fa-chart-pie mr-1 text-teal"></i>
                                    Estados de Facturas
                                </span>
                            </div>
                            <div class="p-2 card-body d-flex align-items-center justify-content-center">
                                <div id="chart-estados" style="min-height:300px; width:100%">
                                    <div class="text-center py-5">
                                        <i class="fas fa-spinner fa-spin fa-2x text-teal"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Fila 3: Detalle de facturas (pendientes / entregadas / anuladas) --}}
                <div class="mb-3 row">
                    <div class="col-12">
                        <div class="shadow-sm card">
                                <div class="py-2 px-3 card-header d-flex align-items-center justify-content-between">
                                <span class="font-weight-bold">
                                    <i class="fas fa-list-ul mr-1 text-teal"></i>
                                    Detalle de Facturas (Pendientes / Entregadas / Anuladas)
                                </span>
                                <button class="btn btn-sm btn-success" onclick="dashLogistica.exportarExcelResumenDetalle()"
                                        title="Exportar Excel">
                                    <i class="fas fa-file-excel mr-1"></i> Exportar Excel
                                </button>
                            </div>
                            <div class="p-2 card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover w-100" id="tabla-resumen-detalle">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Factura</th>
                                                <th>Cliente</th>
                                                <th>Equipo</th>
                                                <th>Fecha Prog.</th>
                                                <th class="text-center">Estado</th>
                                                <th>Fecha Entrega</th>
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
            {{-- /pane-resumen --}}


            {{-- ── PESTAÑA DETALLE ── --}}
            <div class="tab-pane fade" id="pane-detalle" role="tabpanel">
                <div class="d-flex justify-content-end mb-2">
                    <button class="btn btn-sm btn-success" onclick="dashLogistica.exportarExcelDistribucion()"
                            title="Exportar Excel">
                        <i class="fas fa-file-excel mr-1"></i> Exportar Excel
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover w-100" id="tabla-logistica">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Equipo</th>
                                <th>Creador</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Entregadas</th>
                                <th class="text-center">Pendientes</th>
                                <th class="text-center">Anuladas</th>
                                <th class="text-center">Efectividad</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            {{-- /pane-detalle --}}

            {{-- ── PESTAÑA POR FACTURA ── --}}
            <div class="tab-pane fade" id="pane-facturas" role="tabpanel">

                {{-- Leyenda de estado --}}
                <div class="mb-2 d-flex flex-wrap align-items-center justify-content-between">
                    <div class="d-flex flex-wrap align-items-center" style="gap:6px">
                        <span class="badge badge-pill" style="background:#1cc88a;color:#fff">Entregado</span>
                        <span class="badge badge-pill" style="background:#f6c23e;color:#333">Parcial</span>
                        <span class="badge badge-pill" style="background:#858796;color:#fff">Sin Entregar</span>
                        <span class="badge badge-pill" style="background:#e74a3b;color:#fff">Anulada</span>
                        <small class="text-muted ml-2">* Las columnas de motivo solo aplican a facturas anuladas o confirmadas</small>
                    </div>
                    <button class="btn btn-sm btn-success" onclick="dashLogistica.exportarExcelFacturas()"
                            title="Exportar Excel">
                        <i class="fas fa-file-excel mr-1"></i> Exportar Excel
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover w-100" id="tabla-facturas">
                        <thead class="thead-light">
                            <tr>
                                <th>Distribución</th>
                                <th>Fecha Prog.</th>
                                <th>Hora Salida</th>
                                <th>Factura</th>
                                <th>Cliente</th>
                                <th class="text-right">Total L.</th>
                                <th>Equipo</th>
                                <th class="text-center">Estado</th>
                                <th>Fecha Entrega</th>
                                <th>Motivo Anulación</th>
                                <th>Motivo Confirmación</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            {{-- /pane-facturas --}}

            {{-- ── PESTAÑA POR EQUIPO ── --}}
            <div class="tab-pane fade" id="pane-equipos" role="tabpanel">
                <div class="d-flex justify-content-end mb-2">
                    <button class="btn btn-sm btn-success" onclick="dashLogistica.exportarExcelEquipos()"
                            title="Exportar Excel detallado por factura">
                        <i class="fas fa-file-excel mr-1"></i> Exportar Excel Detallado
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover w-100" id="tabla-equipos">
                        <thead class="thead-light">
                            <tr>
                                <th>Equipo</th>
                                <th>Fecha</th>
                                <th>Hora Salida</th>
                                <th>Hora Última Entrega</th>
                                <th>Hora Llegada</th>
                                <th>Miembros / % Comisión</th>
                                <th class="text-center">Facturas Entregadas</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            {{-- /pane-equipos --}}

        </div>
    </div>
    {{-- /dl-main-card --}}

</div>

@push('scripts')
<script src="{{ asset('js/js_proyecto/reportes/dashboard-logistica.js') }}"></script>
@endpush

<!-- Modal: Detalle de entregas por equipo -->
<div class="modal fade" id="modalDetalleEquipo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="text-white modal-header" style="background: linear-gradient(135deg,#0f766e,#14b8a6);">
                <h5 class="modal-title">
                    <i class="fas fa-users-cog"></i> Entregas del equipo: <span id="detEquipoNombre"></span>
                </h5>
                <button type="button" class="text-white close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Factura</th>
                                <th>Cliente</th>
                                <th>Dirección de Entrega</th>
                                <th class="text-center">Hora de Entrega</th>
                                <th class="text-center">Hallazgo</th>
                            </tr>
                        </thead>
                        <tbody id="detEquipoTablaBody">
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

