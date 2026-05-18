@push('styles')
<style>
:root {
    --rrhh-purple : #7c3aed;
    --rrhh-blue   : #2563eb;
    --rrhh-green  : #059669;
    --rrhh-amber  : #d97706;
}
.rrhh-header {
    background: linear-gradient(135deg, #1e1b4b 0%, #4338ca 60%, #7c3aed 100%);
    border-radius: 14px;
    padding: 22px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 8px 24px rgba(99,102,241,.35);
    color: #fff;
}
.rrhh-icon-wrap {
    width: 52px; height: 52px;
    background: rgba(255,255,255,.18);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
}
.rrhh-badge-periodo {
    background: rgba(255,255,255,.18);
    border-radius: 8px;
    padding: 7px 16px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .3px;
    display: none;
}
.kpi-card {
    border: none;
    border-radius: 14px;
    padding: 22px 20px;
    box-shadow: 0 4px 16px rgba(0,0,0,.08);
    position: relative;
    overflow: hidden;
    transition: transform .2s;
}
.kpi-card:hover { transform: translateY(-3px); }
.kpi-card::after {
    content: '';
    position: absolute; right: -18px; bottom: -18px;
    width: 80px; height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,.15);
}
.kpi-card .kpi-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    background: rgba(255,255,255,.22);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: #fff;
    margin-bottom: 14px;
}
.kpi-card .kpi-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; opacity: .82; margin-bottom: 4px; }
.kpi-card .kpi-value { font-size: 26px; font-weight: 800; line-height: 1; }
.kpi-card .kpi-sub   { font-size: 11px; opacity: .7; margin-top: 4px; }
.kpi-purple { background: linear-gradient(135deg,#7c3aed,#5b21b6); color:#fff; }
.kpi-blue   { background: linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; }
.kpi-green  { background: linear-gradient(135deg,#059669,#047857); color:#fff; }
.kpi-amber  { background: linear-gradient(135deg,#d97706,#b45309); color:#fff; }
.filter-panel {
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
}
.filter-panel .fp-title {
    font-size: 12px; font-weight: 800; text-transform: uppercase;
    letter-spacing: .5px; color: #7c3aed; margin-bottom: 16px;
    display: flex; align-items: center; gap: 7px;
}
.filter-label {
    font-size: 11px; font-weight: 700; color: #475569;
    text-transform: uppercase; letter-spacing: .4px;
    margin-bottom: 5px; display: block;
}
.fp-input {
    height: 38px; border-radius: 8px; border: 1.5px solid #cbd5e1;
    font-size: 13px; padding: 0 10px; width: 100%;
    transition: border-color .2s, box-shadow .2s;
}
.fp-input:focus { outline: none; border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.15); }
.select2-container--default .select2-selection--single {
    height: 38px !important; border-radius: 8px !important;
    border: 1.5px solid #cbd5e1 !important;
    display: flex !important; align-items: center !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important; padding-left: 10px !important; font-size: 13px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px !important; }
.btn-generar {
    background: linear-gradient(135deg,#7c3aed,#5b21b6);
    color: #fff; border: none; border-radius: 8px;
    height: 38px; padding: 0 20px; font-weight: 700; font-size: 13px;
    cursor: pointer; transition: opacity .2s, transform .15s;
    display: inline-flex; align-items: center; gap: 7px;
}
.btn-generar:hover { opacity: .9; transform: translateY(-1px); color:#fff; }
.btn-limpiar {
    background: #f1f5f9; color: #475569;
    border: 1.5px solid #cbd5e1; border-radius: 8px;
    height: 38px; padding: 0 16px; font-weight: 600; font-size: 13px;
    cursor: pointer; transition: background .2s;
    display: inline-flex; align-items: center; gap: 7px;
}
.btn-limpiar:hover { background: #e2e8f0; }
.rrhh-tabs-wrapper {
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
    overflow: hidden;
}
.rrhh-nav-tabs {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    padding: 0 8px;
    display: flex; gap: 2px; flex-wrap: wrap;
}
.rrhh-nav-tabs .nav-link {
    font-size: 12px; font-weight: 700;
    color: #64748b; border: none; border-radius: 8px 8px 0 0;
    padding: 10px 16px; margin-top: 6px;
    display: flex; align-items: center; gap: 6px;
    transition: background .15s, color .15s;
    text-decoration: none;
}
.rrhh-nav-tabs .nav-link:hover  { background: #ede9fe; color: #7c3aed; }
.rrhh-nav-tabs .nav-link.active { background: #7c3aed; color: #fff; box-shadow: 0 2px 8px rgba(124,58,237,.3); }
.rrhh-tab-content { padding: 20px; }
.dataTables_wrapper .dataTables_filter input {
    border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 5px 12px; font-size: 13px;
}
table.dataTable thead th {
    background: #f1f5f9; color: #334155;
    font-size: 11px; font-weight: 800;
    text-transform: uppercase; letter-spacing: .5px;
    border-bottom: 2px solid #e2e8f0; white-space: nowrap;
}
table.dataTable tbody tr:hover td { background: #faf5ff; }
table.dataTable tbody td { font-size: 13px; vertical-align: middle; }
.badge-rol { display: inline-block; border-radius: 6px; padding: 3px 9px; font-size: 11px; font-weight: 700; }
.badge-rol-tv  { background: #ede9fe; color: #5b21b6; }
.badge-rol-ac  { background: #dbeafe; color: #1d4ed8; }
.badge-rol-adm { background: #fef3c7; color: #92400e; }
.badge-rol-def { background: #f1f5f9; color: #475569; }
.monto-com { font-weight: 800; color: #059669; font-size: 14px; }
.rank-medal { font-size: 18px; }
.tab-toolbar {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 14px; flex-wrap: wrap; gap: 8px;
}
.tab-toolbar .tab-title {
    font-size: 14px; font-weight: 800; color: #1e293b;
    display: flex; align-items: center; gap: 8px;
}
.btn-export {
    background: #f0fdf4; color: #059669;
    border: 1.5px solid #86efac; border-radius: 8px;
    font-size: 12px; font-weight: 700;
    padding: 6px 14px; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
    transition: background .2s; text-decoration: none;
}
.btn-export:hover { background: #dcfce7; color: #059669; }
.empty-state { text-align: center; padding: 50px 20px; color: #94a3b8; }
.empty-state i { font-size: 44px; margin-bottom: 12px; display: block; }
.empty-state p { font-size: 14px; margin: 0; }
.select2-container { z-index: 999 !important; width: 100% !important; }
.select2-dropdown  { z-index: 3050 !important; }
</style>
@endpush

<div>{{-- Livewire single root element --}}

{{-- HEADER --}}
<div class="rrhh-header mb-4">
    <div style="display:flex;align-items:center;gap:16px;">
        <div class="rrhh-icon-wrap">
            <i class="fa fa-chart-bar" style="font-size:22px;"></i>
        </div>
        <div>
            <h4 style="margin:0;font-weight:800;font-size:18px;">Reportería de Comisiones</h4>
            <p style="margin:0;font-size:12px;opacity:.8;">Módulo de Recursos Humanos — Análisis integral de comisiones de ventas</p>
        </div>
    </div>
    <div class="rrhh-badge-periodo" id="badgePeriodo">
        <i class="fa fa-calendar mr-1"></i><span id="textPeriodo"></span>
    </div>
</div>

{{-- KPI CARDS --}}
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="kpi-card kpi-purple">
            <div class="kpi-icon"><i class="fa fa-money-bill-wave"></i></div>
            <div class="kpi-label">Total Comisiones</div>
            <div class="kpi-value" id="kpiComision">—</div>
            <div class="kpi-sub">Lempiras en el período</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="kpi-card kpi-blue">
            <div class="kpi-icon"><i class="fa fa-users"></i></div>
            <div class="kpi-label">Empleados Activos</div>
            <div class="kpi-value" id="kpiEmpleados">—</div>
            <div class="kpi-sub">Con comisiones generadas</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="kpi-card kpi-green">
            <div class="kpi-icon"><i class="fa fa-file-invoice"></i></div>
            <div class="kpi-label">Facturas Procesadas</div>
            <div class="kpi-value" id="kpiFacturas">—</div>
            <div class="kpi-sub">Facturas que generaron comisión</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="kpi-card kpi-amber">
            <div class="kpi-icon"><i class="fa fa-chart-line"></i></div>
            <div class="kpi-label">Promedio / Empleado</div>
            <div class="kpi-value" id="kpiPromedio">—</div>
            <div class="kpi-sub">Comisión promedio del período</div>
        </div>
    </div>
</div>

{{-- PANEL DE FILTROS --}}
<div class="filter-panel mb-4">
    <div class="fp-title"><i class="fa fa-sliders-h"></i> Filtros de Búsqueda</div>
    <div class="row align-items-end">
        <div class="col-md-2 col-sm-6 mb-2">
            <label class="filter-label"><i class="fa fa-calendar-alt mr-1"></i>Fecha Inicio</label>
            <input type="date" id="fpFechaInicio" class="fp-input">
        </div>
        <div class="col-md-2 col-sm-6 mb-2">
            <label class="filter-label"><i class="fa fa-calendar-check mr-1"></i>Fecha Fin</label>
            <input type="date" id="fpFechaFin" class="fp-input">
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <label class="filter-label"><i class="fa fa-user mr-1"></i>Empleado <small style="font-weight:400;text-transform:none;">(opcional)</small></label>
            <select id="fpEmpleado" class="form-control" style="width:100%;"></select>
        </div>
        <div class="col-md-2 col-sm-6 mb-2">
            <label class="filter-label"><i class="fa fa-tag mr-1"></i>Rol <small style="font-weight:400;text-transform:none;">(opcional)</small></label>
            <select id="fpRol" class="form-control" style="width:100%;"></select>
        </div>
        <div class="col-md-3 col-sm-12 mb-2">
            <label class="filter-label">&nbsp;</label>
            <div style="display:flex;gap:8px;">
                <button class="btn-generar" id="btnGenerar">
                    <i class="fa fa-search"></i> Generar Reporte
                </button>
                <button class="btn-limpiar" id="btnLimpiar">
                    <i class="fa fa-times"></i> Limpiar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- TABS --}}
<div class="rrhh-tabs-wrapper">
    <ul class="rrhh-nav-tabs nav" id="rrhhTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#tab-nomina" role="tab">
                <i class="fa fa-clipboard-list"></i> Nómina
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-detalle" role="tab">
                <i class="fa fa-user-check"></i> Detalle Empleado
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-ranking" role="tab">
                <i class="fa fa-trophy"></i> Ranking
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-rol" role="tab">
                <i class="fa fa-users-cog"></i> Por Rol
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-facturas" role="tab">
                <i class="fa fa-file-invoice-dollar"></i> Por Factura
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-productos" role="tab">
                <i class="fa fa-boxes"></i> Por Producto
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-comparativo" role="tab">
                <i class="fa fa-chart-area"></i> Comparativo Mensual
            </a>
        </li>
    </ul>

    <div class="tab-content rrhh-tab-content" id="rrhhTabContent">

        {{-- TAB 1: NÓMINA --}}
        <div class="tab-pane fade show active" id="tab-nomina" role="tabpanel">
            <div class="tab-toolbar">
                <div class="tab-title">
                    <i class="fa fa-clipboard-list" style="color:#7c3aed;"></i>
                    Reporte de Nómina de Comisiones
                </div>
                <div style="display:flex;gap:8px;">
                    <button class="btn-export" onclick="exportarExcel('nomina')">
                        <i class="fa fa-file-excel"></i> Exportar Excel
                    </button>
                    <button class="btn-export" style="background:#eff6ff;color:#1d4ed8;border-color:#93c5fd;" onclick="imprimirTabla('tab-nomina')">
                        <i class="fa fa-print"></i> Imprimir
                    </button>
                </div>
            </div>
            <div id="nominaInfo" style="display:none;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:12px 16px;font-size:13px;color:#166534;margin-bottom:14px;">
                <i class="fa fa-info-circle mr-2"></i>
                Consolidado por <strong>empleado / rol / mes</strong> — base para cálculo de nómina.
                Total del período: <strong id="nominaTotal">—</strong>
            </div>
            <div id="nominaEmptyState" class="empty-state">
                <i class="fa fa-clipboard-list"></i>
                <p>Seleccione un período y presione <strong>Generar Reporte</strong></p>
            </div>
            <div id="nominaTableWrap" style="display:none;">
                <table id="dtNomina" class="table table-hover table-sm w-100">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Empleado</th>
                            <th>Rol</th>
                            <th>Mes</th>
                            <th class="text-center">Facturas Generadas</th>
                            <th class="text-right">Comisión Total (L.)</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr style="font-weight:800;background:#f8fafc;">
                            <td colspan="5" class="text-right" style="padding-right:12px;">TOTAL PERÍODO:</td>
                            <td class="text-right monto-com" id="nominaFooterTotal">—</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- TAB 2: DETALLE EMPLEADO --}}
        <div class="tab-pane fade" id="tab-detalle" role="tabpanel">
            <div class="tab-toolbar">
                <div class="tab-title">
                    <i class="fa fa-user-check" style="color:#2563eb;"></i>
                    Detalle de Comisiones por Empleado
                </div>
                <button class="btn-export" onclick="exportarExcel('empleado')">
                    <i class="fa fa-file-excel"></i> Exportar Excel
                </button>
            </div>
            <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;padding:12px 16px;font-size:13px;color:#1d4ed8;margin-bottom:14px;">
                <i class="fa fa-info-circle mr-2"></i>
                Seleccione un <strong>empleado específico</strong> en el filtro superior para ver el detalle producto por producto.
            </div>
            <div id="detalleEmptyState" class="empty-state">
                <i class="fa fa-user-check"></i>
                <p>Seleccione un empleado en el filtro superior y presione <strong>Generar Reporte</strong></p>
            </div>
            <div id="detalleTableWrap" style="display:none;">
                <table id="dtDetalle" class="table table-hover table-sm w-100">
                    <thead>
                        <tr>
                            <th>Fecha Cierre</th>
                            <th>Factura</th>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-right">Monto Comisión (L.)</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- TAB 3: RANKING --}}
        <div class="tab-pane fade" id="tab-ranking" role="tabpanel">
            <div class="tab-toolbar">
                <div class="tab-title">
                    <i class="fa fa-trophy" style="color:#d97706;"></i>
                    Ranking de Empleados — Mejor Comisión
                </div>
                <button class="btn-export" onclick="exportarExcel('ranking')">
                    <i class="fa fa-file-excel"></i> Exportar Excel
                </button>
            </div>
            <div id="rankingEmptyState" class="empty-state">
                <i class="fa fa-trophy"></i>
                <p>Seleccione un período y presione <strong>Generar Reporte</strong></p>
            </div>
            <div id="rankingTableWrap" style="display:none;">
                <table id="dtRanking" class="table table-hover table-sm w-100">
                    <thead>
                        <tr>
                            <th style="width:50px;text-align:center;">Pos.</th>
                            <th>Empleado</th>
                            <th>Rol</th>
                            <th class="text-center">Meses Activos</th>
                            <th class="text-right">Mejor Mes (L.)</th>
                            <th class="text-right">Promedio / Mes (L.)</th>
                            <th class="text-right">Total Comisión (L.)</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- TAB 4: POR ROL --}}
        <div class="tab-pane fade" id="tab-rol" role="tabpanel">
            <div class="tab-toolbar">
                <div class="tab-title">
                    <i class="fa fa-users-cog" style="color:#7c3aed;"></i>
                    Comisiones por Rol
                </div>
                <button class="btn-export" onclick="exportarExcel('rol')">
                    <i class="fa fa-file-excel"></i> Exportar Excel
                </button>
            </div>
            <div id="rolEmptyState" class="empty-state">
                <i class="fa fa-users-cog"></i>
                <p>Seleccione un período y presione <strong>Generar Reporte</strong></p>
            </div>
            <div id="rolTableWrap" style="display:none;">
                <table id="dtRol" class="table table-hover table-sm w-100">
                    <thead>
                        <tr>
                            <th>Rol</th>
                            <th>Empleado</th>
                            <th class="text-right">Total Comisiones (L.)</th>
                            <th class="text-center">Facturas</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- TAB 5: POR FACTURA --}}
        <div class="tab-pane fade" id="tab-facturas" role="tabpanel">
            <div class="tab-toolbar">
                <div class="tab-title">
                    <i class="fa fa-file-invoice-dollar" style="color:#059669;"></i>
                    Auditoría — Comisiones por Factura
                </div>
                <button class="btn-export" onclick="exportarExcel('facturas')">
                    <i class="fa fa-file-excel"></i> Exportar Excel
                </button>
            </div>
            <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:12px 16px;font-size:13px;color:#166534;margin-bottom:14px;">
                <i class="fa fa-shield-alt mr-2"></i>
                Trazabilidad completa: cada factura cerrada con su empleado, monto de venta y comisión generada.
            </div>
            <div id="facturasEmptyState" class="empty-state">
                <i class="fa fa-file-invoice-dollar"></i>
                <p>Seleccione un período y presione <strong>Generar Reporte</strong></p>
            </div>
            <div id="facturasTableWrap" style="display:none;">
                <table id="dtFacturas" class="table table-hover table-sm w-100">
                    <thead>
                        <tr>
                            <th>Factura / CAI</th>
                            <th>Cliente</th>
                            <th>Empleado</th>
                            <th class="text-right">Total Venta (L.)</th>
                            <th class="text-right">Comisión (L.)</th>
                            <th class="text-center">% Efectivo</th>
                            <th class="text-center">Fecha Cierre</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- TAB 6: POR PRODUCTO --}}
        <div class="tab-pane fade" id="tab-productos" role="tabpanel">
            <div class="tab-toolbar">
                <div class="tab-title">
                    <i class="fa fa-boxes" style="color:#7c3aed;"></i>
                    Comisiones por Producto
                </div>
                <button class="btn-export" onclick="exportarExcel('productos')">
                    <i class="fa fa-file-excel"></i> Exportar Excel
                </button>
            </div>
            <div id="productosEmptyState" class="empty-state">
                <i class="fa fa-boxes"></i>
                <p>Seleccione un período y presione <strong>Generar Reporte</strong></p>
            </div>
            <div id="productosTableWrap" style="display:none;">
                <table id="dtProductos" class="table table-hover table-sm w-100">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Código</th>
                            <th class="text-center">Unidades Vendidas</th>
                            <th class="text-right">Total Comisión (L.)</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- TAB 7: COMPARATIVO MENSUAL --}}
        <div class="tab-pane fade" id="tab-comparativo" role="tabpanel">
            <div class="tab-toolbar">
                <div class="tab-title">
                    <i class="fa fa-chart-area" style="color:#2563eb;"></i>
                    Comparativo Mensual de Comisiones
                </div>
                <button class="btn-export" onclick="exportarExcel('comparativo')">
                    <i class="fa fa-file-excel"></i> Exportar Excel
                </button>
            </div>
            <div id="comparativoEmptyState" class="empty-state">
                <i class="fa fa-chart-area"></i>
                <p>Seleccione un rango de meses y presione <strong>Generar Reporte</strong></p>
            </div>
            <div id="comparativoTableWrap" style="display:none;">
                <table id="dtComparativo" class="table table-hover table-sm w-100">
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th class="text-center">Empleados Activos</th>
                            <th class="text-center">Roles</th>
                            <th class="text-right">Mayor Comisión Individual (L.)</th>
                            <th class="text-right">Menor Comisión Individual (L.)</th>
                            <th class="text-right">Total Comisiones (L.)</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</div>
</div>{{-- /Livewire root --}}

@push('scripts')
<script src="{{ asset('js/js_proyecto/comisiones/Escalado/reportesComisionesGenerales.js') }}"></script>
@endpush
