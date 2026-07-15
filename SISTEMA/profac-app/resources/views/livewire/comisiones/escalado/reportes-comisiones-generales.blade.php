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

.nom-detalle-btn {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
}
.nom-detalle-btn:hover {
    background: #dbeafe;
    color: #1e40af;
}

.modal-nomina-detalle {
    width: calc(100vw - 140px) !important;
    max-width: 1120px !important;
    margin: 1.2rem auto !important;
}

#modalDetalleNomina .modal-dialog {
    width: calc(100vw - 140px) !important;
    max-width: 1120px !important;
}

@media (max-width: 1199.98px) {
    .modal-nomina-detalle,
    #modalDetalleNomina .modal-dialog {
        width: calc(100vw - 56px) !important;
        max-width: calc(100vw - 56px) !important;
    }
}

@media (max-width: 991.98px) {
    .modal-nomina-detalle,
    #modalDetalleNomina .modal-dialog {
        width: calc(100vw - 24px) !important;
        max-width: calc(100vw - 24px) !important;
        margin: .5rem auto !important;
    }
}

#modalDetalleNomina .modal-content {
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    box-shadow: 0 14px 30px rgba(15, 23, 42, .20);
}

#modalDetalleNomina .modal-body {
    max-height: 68vh;
    overflow-y: auto;
    background: #f8fafc;
    padding: 10px 12px 12px;
}

#modalDetalleNomina .table-responsive {
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
}

#dtNominaDetalle thead th {
    white-space: nowrap;
    background: #f1f5f9;
    color: #334155;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .35px;
    border-bottom: 1px solid #dbe3ee;
}

#dtNominaDetalle td {
    font-size: 12.5px;
    vertical-align: middle;
    color: #334155;
}

#dtNominaDetalle td.resumen-productos-col {
    min-width: 180px;
    max-width: 220px;
    vertical-align: middle;
}

#modalProductosFactura .modal-dialog {
    width: calc(100vw - 120px) !important;
    max-width: 1100px !important;
}

#modalProductosFactura .modal-content {
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #dbe3ee;
    box-shadow: 0 18px 36px rgba(15, 23, 42, .22);
}

#modalProductosFactura .modal-header {
    background: #0f172a;
    color: #fff;
    padding: 10px 14px;
}

#modalProductosFactura .modal-body {
    background: #f8fafc;
    padding: 12px;
}

#dtProductosFactura thead th {
    white-space: nowrap;
    background: #eef2f7;
    color: #334155;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
}

#dtProductosFactura td {
    font-size: 12px;
    color: #334155;
    vertical-align: middle;
}

#dtNominaDetalle tbody tr:nth-child(even) td {
    background: #fcfdff;
}

#dtNominaDetalle tbody tr:hover td {
    background: #f8fafc !important;
}

#modalDetalleNomina .modal-header {
    border-bottom: 1px solid rgba(255,255,255,.15);
    padding: 10px 14px;
}

#modalDetalleNomina .modal-title {
    font-size: 13px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 8px;
}

#modalDetalleNomina .close {
    opacity: .92;
    text-shadow: none;
}

#modalDetalleNomina .close:hover {
    opacity: 1;
}

.mdn-btn-excel {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
    font-weight: 700;
    font-size: 11px;
    border-radius: 8px;
    padding: 5px 9px;
}

.mdn-btn-excel:hover {
    background: #bbf7d0;
    color: #14532d;
}

#modalDetalleNomina .dataTables_wrapper .dataTables_length,
#modalDetalleNomina .dataTables_wrapper .dataTables_filter {
    margin: 2px 0 8px;
}

#modalDetalleNomina .dataTables_wrapper .dataTables_length label,
#modalDetalleNomina .dataTables_wrapper .dataTables_filter label {
    font-size: 11px;
    color: #64748b;
    font-weight: 700;
}

#modalDetalleNomina .dataTables_wrapper .dataTables_length select,
#modalDetalleNomina .dataTables_wrapper .dataTables_filter input {
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    height: 30px;
    padding: 0 10px;
    background: #fff;
    color: #334155;
}

#modalDetalleNomina .dataTables_wrapper .dataTables_info {
    font-size: 11px;
    color: #64748b;
    font-weight: 600;
}

#modalDetalleNomina .dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 7px !important;
    border: 1px solid #dbe3ee !important;
    background: #fff !important;
    color: #334155 !important;
    margin: 0 2px;
    min-width: 30px;
    height: 30px;
    line-height: 16px;
    font-size: 12px;
}

#modalDetalleNomina .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #0f766e !important;
    color: #fff !important;
    border-color: #0f766e !important;
}

#modalDetalleNomina .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #f8fafc !important;
    color: #0f172a !important;
}

.mdn-subtitle {
    color: #c7d2fe;
    font-size: 10px;
    font-weight: 600;
    margin-top: 2px;
}
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

{{-- TABS --}}
<div class="rrhh-tabs-wrapper">
    <ul class="rrhh-nav-tabs nav" id="rrhhTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#tab-nomina" role="tab">
                <i class="fa fa-clipboard-list"></i> Nómina
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-conciliadas" role="tab">
                <i class="fa fa-check-circle"></i> Comisiones Conciliadas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-proyecciones" role="tab">
                <i class="fa fa-chart-line"></i> Proyecciones
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-revision-facturas" role="tab">
                <i class="fa fa-search"></i> Revisión de Facturas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-factura-actor" role="tab">
                <i class="fa fa-users"></i> Factura por Actor
            </a>
        </li>
    </ul>

    <div class="tab-content rrhh-tab-content" id="rrhhTabContent">

        {{-- TAB 1: NÓMINA --}}
        <div class="tab-pane fade show active" id="tab-nomina" role="tabpanel">
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
                Consolidado por <strong>empleado / mes</strong> utilizando acreditaciones reales ya generadas.
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
                            <th>Roles</th>
                            <th>Mes</th>
                            <th class="text-center">Facturas Comisionadas</th>
                            <th class="text-right">Comisión Total (L.)</th>
                            <th class="text-center" style="width:110px;">Detalle</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr style="font-weight:800;background:#f8fafc;">
                            <td colspan="6" class="text-right" style="padding-right:12px;">TOTAL PERÍODO:</td>
                            <td class="text-right monto-com" id="nominaFooterTotal">—</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-conciliadas" role="tabpanel">
            <div class="filter-panel mb-4">
                <div class="fp-title"><i class="fa fa-check-circle"></i> Filtro de Comisiones Conciliadas</div>
                <div class="row align-items-end">
                    <div class="col-md-4 col-sm-12 mb-2">
                        <label class="filter-label"><i class="fa fa-calendar mr-1"></i>Período Conciliado</label>
                        <select id="ccPeriodo" class="form-control fp-input" style="width:100%;">
                            <option value="">Seleccione un período conciliado</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-2">
                        <label class="filter-label">&nbsp;</label>
                        <div style="display:flex;gap:8px;">
                            <button class="btn-generar" id="btnCcGenerar">
                                <i class="fa fa-search"></i> Cargar Período
                            </button>
                            <button class="btn-limpiar" id="btnCcLimpiar">
                                <i class="fa fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-toolbar">
                <div class="tab-title">
                    <i class="fa fa-check-circle" style="color:#059669;"></i>
                    Resumen de Comisiones Conciliadas
                </div>
                <div>
                    <button class="btn-generar" id="btnCcExcelMasivo" style="padding:8px 14px;">
                        <i class="fa fa-file-excel-o"></i> Descargar Excel Masivo
                    </button>
                </div>
            </div>

            <div id="ccEmptyState" class="empty-state">
                <i class="fa fa-check-circle"></i>
                <p>Seleccione un <strong>período conciliado</strong> para ver el resumen.</p>
            </div>

            <div id="ccResumenWrap" style="display:none;">
                <div id="ccKpis" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:14px;margin-bottom:20px;">
                    <div class="kpi-card kpi-purple">
                        <div class="kpi-icon"><i class="fa fa-wallet"></i></div>
                        <div class="kpi-label">Total Bruto</div>
                        <div class="kpi-value" id="ccTotalBruto">L. 0.00</div>
                        <div class="kpi-sub">Comisión conciliada del período</div>
                    </div>
                    <div class="kpi-card kpi-amber">
                        <div class="kpi-icon"><i class="fa fa-file-invoice-dollar"></i></div>
                        <div class="kpi-label">Retención Fuente</div>
                        <div class="kpi-value" id="ccTotalRetencion">L. 0.00</div>
                        <div class="kpi-sub">Descuentos aplicados al cierre</div>
                    </div>
                    <div class="kpi-card kpi-green">
                        <div class="kpi-icon"><i class="fa fa-money-bill-wave"></i></div>
                        <div class="kpi-label">Total Neto</div>
                        <div class="kpi-value" id="ccTotalNeto">L. 0.00</div>
                        <div class="kpi-sub">Monto final conciliado</div>
                    </div>
                    <div class="kpi-card kpi-blue">
                        <div class="kpi-icon"><i class="fa fa-users"></i></div>
                        <div class="kpi-label">Empleados</div>
                        <div class="kpi-value" id="ccTotalEmpleados">0</div>
                        <div class="kpi-sub"><span id="ccTotalFacturas">0</span> facturas conciliadas</div>
                    </div>
                </div>

                <div style="overflow-x:auto;border:1px solid #e2e8f0;border-radius:10px;background:#fff;">
                    <table id="dtConciliadas" class="table table-hover table-sm w-100 mb-0">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Rol Asignado</th>
                                <th class="text-center">Facturas Reales</th>
                                <th class="text-right">Total Comisión Conciliada</th>
                                <th>Fecha Conciliación</th>
                                <th>Conciliado Por</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="ccTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-proyecciones" role="tabpanel">
            <div class="filter-panel mb-4">
                <div class="fp-title"><i class="fa fa-chart-line"></i> Filtros de Proyección</div>
                <div class="row align-items-end">
                    <div class="col-md-2 col-sm-6 mb-2" id="proyFiltrosRangoInicioWrap">
                        <label class="filter-label"><i class="fa fa-calendar-alt mr-1"></i>Fecha Inicio</label>
                        <input type="date" id="proyFechaInicio" class="fp-input">
                    </div>

                    <div class="col-md-2 col-sm-6 mb-2" id="proyFiltrosRangoFinWrap">
                        <label class="filter-label"><i class="fa fa-calendar-check mr-1"></i>Fecha Fin</label>
                        <input type="date" id="proyFechaFin" class="fp-input">
                    </div>

                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="filter-label"><i class="fa fa-user mr-1"></i>Usuario Activo</label>
                        <select id="proyUsuario" class="form-control" style="width:100%;"></select>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="filter-label"><i class="fa fa-tag mr-1"></i>Rol Para Calcular</label>
                        <select id="proyRol" class="form-control" style="width:100%;"></select>
                    </div>

                    <div class="col-md-2 col-sm-12 mb-2">
                        <label class="filter-label">&nbsp;</label>
                        <div style="display:flex;gap:8px;">
                            <button class="btn-generar" id="btnProyGenerar">
                                <i class="fa fa-search"></i> Generar Proyección
                            </button>
                            <button class="btn-limpiar" id="btnProyLimpiar">
                                <i class="fa fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="proyInfo" style="display:none;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;padding:12px 16px;font-size:13px;color:#1e3a8a;margin-bottom:14px;">
                <div style="display:flex;gap:18px;flex-wrap:wrap;align-items:center;">
                    <span><i class="fa fa-file-invoice mr-1"></i>Facturas proyectadas: <strong id="proyFacturas">0</strong></span>
                    <span><i class="fa fa-list mr-1"></i>Líneas de producto: <strong id="proyRegistros">0</strong></span>
                    <span><i class="fa fa-calculator mr-1"></i>Base unitaria total: <strong id="proyBaseUnitaria">L. 0.00</strong></span>
                    <span><i class="fa fa-coins mr-1"></i>Base comisionable total: <strong id="proyBaseComisionable">L. 0.00</strong></span>
                    <span><i class="fa fa-money-bill-wave mr-1"></i>Comisión total: <strong id="proyComisionTotal">L. 0.00</strong></span>
                    <span><i class="fa fa-exclamation-triangle mr-1"></i>Excluidas: <strong id="proyExcluidas">0</strong></span>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;">
                    <button class="btn-export" type="button" onclick="exportarProyeccionesExcel('proyectadas')">
                        <i class="fa fa-file-excel-o"></i> Descargar Excel Proyectadas
                    </button>
                    <button class="btn-export" type="button" style="background:#fff1f2;color:#9f1239;border-color:#fecdd3;" onclick="exportarProyeccionesExcel('excluidas')">
                        <i class="fa fa-file-excel-o"></i> Descargar Excel Excluidas
                    </button>
                    <button class="btn-export" type="button" style="background:#fff7ed;color:#9a3412;border-color:#fdba74;" onclick="exportarProyeccionesNomina()">
                        <i class="fa fa-file-excel-o"></i> Descargar Nómina Proyectada
                    </button>
                    <small style="display:flex;align-items:center;color:#334155;">Desplace horizontalmente para ver todas las columnas.</small>
                </div>
            </div>

            <div id="proyEmptyState" class="empty-state">
                <i class="fa fa-chart-line"></i>
                <p>Seleccione rango y usuario para generar la <strong>Proyección Unificada</strong></p>
            </div>

            <div id="proyTableWrap" style="display:none;">
                <div style="overflow-x:auto;">
                    <table id="dtProyecciones" class="table table-hover table-sm w-100">
                        <thead>
                            <tr>
                                <th>Fecha Pago</th>
                                <th>Fecha Creación Factura</th>
                                <th>Factura</th>
                                <th style="min-width:340px;">Producto</th>
                                <th style="min-width:220px;">Cliente</th>
                                <th>Escala Cliente</th>
                                <th class="text-center" style="min-width:110px;">Escala</th>
                                <th class="text-right">Cantidad</th>
                                <th>Rol Comisión</th>
                                <th>Usuario</th>
                                <th class="text-right">Base Comisionable Unitaria</th>
                                <th class="text-right">Base Comisionable</th>
                                <th class="text-right">% Promedio</th>
                                <th class="text-right">Comisión Proyectada</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div id="proyExcluidasWrap" style="display:none;margin-top:18px;">
                <div class="tab-toolbar" style="margin-bottom:10px;">
                    <div class="tab-title" style="color:#991b1b;">
                        <i class="fa fa-exclamation-triangle" style="color:#dc2626;"></i>
                        Facturas para comisión por politica anterior
                    </div>
                    <button class="btn-export" type="button" style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;" onclick="redirigirCalculoPoliticaAnterior()">
                        <i class="fa fa-calculator"></i> Calcular
                    </button>
                </div>
                <div style="overflow-x:auto;border:1px solid #fecaca;border-radius:10px;background:#fff;">
                    <table id="dtProyeccionesExcluidas" class="table table-hover table-sm w-100 mb-0">
                        <thead>
                            <tr>
                                <th>Fecha Pago</th>
                                <th>Fecha Creación Factura</th>
                                <th>Factura</th>
                                <th style="min-width:280px;">Producto</th>
                                <th>Cliente</th>
                                <th>Categoría Precio</th>
                                <th>Rol Comisión</th>
                                <th>Usuario</th>
                                <th>Razón No Comisionable</th>
                                <th>Detalle Técnico</th>
                            </tr>
                        </thead>
                        <tbody id="proyExcluidasBody"></tbody>
                    </table>
                </div>
            </div>

            {{-- Sección inline Política Anterior --}}
            <div id="seccionPoliticaAnterior" style="display:none;margin-top:24px;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.08);">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;background:linear-gradient(135deg,#1e3a5f,#2563eb);color:#fff;">
                    <div style="font-size:15px;font-weight:700;">
                        <i class="fa fa-calculator mr-2"></i> Comisión por Política Anterior
                    </div>
                    <button type="button" style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:6px;padding:4px 12px;font-size:12px;cursor:pointer;" onclick="$('#seccionPoliticaAnterior').hide();$('#iframePoliticaAnterior').attr('src','');">
                        <i class="fa fa-times mr-1"></i> Cerrar
                    </button>
                </div>
                <iframe id="iframePoliticaAnterior" src="" style="width:100%;height:90vh;border:1px solid #e2e8f0;border-radius:0 0 12px 12px;display:block;"></iframe>
            </div>

        </div>

        <div class="tab-pane fade" id="tab-revision-facturas" role="tabpanel">
            <div class="filter-panel mb-4">
                <div class="fp-title"><i class="fa fa-search"></i> Filtros de Revisión de Facturas</div>
                <div class="row align-items-end">
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="filter-label"><i class="fa fa-calendar-alt mr-1"></i>Fecha Pago Inicio</label>
                        <input type="date" id="revFechaInicio" class="fp-input">
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="filter-label"><i class="fa fa-calendar-check mr-1"></i>Fecha Pago Fin</label>
                        <input type="date" id="revFechaFin" class="fp-input">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="filter-label"><i class="fa fa-user mr-1"></i>Usuario Activo</label>
                        <select id="revUsuario" class="form-control" style="width:100%;"></select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="filter-label"><i class="fa fa-tag mr-1"></i>Rol Comisionable Activo</label>
                        <select id="revRol" class="form-control" style="width:100%;"></select>
                    </div>
                    <div class="col-md-2 col-sm-12 mb-2">
                        <label class="filter-label">&nbsp;</label>
                        <div style="display:flex;gap:8px;">
                            <button class="btn-generar" id="btnRevGenerar">
                                <i class="fa fa-search"></i> Generar
                            </button>
                            <button class="btn-limpiar" id="btnRevLimpiar">
                                <i class="fa fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="revInfo" style="display:none;background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:10px;padding:12px 16px;font-size:13px;color:#0f172a;margin-bottom:14px;">
                <div style="display:flex;gap:18px;flex-wrap:wrap;align-items:center;">
                    <span><i class="fa fa-file-invoice mr-1"></i>Facturas: <strong id="revFacturas">0</strong></span>
                    <span><i class="fa fa-list mr-1"></i>Registros Factura: <strong id="revRegistrosFactura">0</strong></span>
                    <span><i class="fa fa-cubes mr-1"></i>Registros Producto: <strong id="revRegistrosProducto">0</strong></span>
                    <span><i class="fa fa-money mr-1"></i>Monto Abonado Total: <strong id="revMontoAbonado">L. 0.00</strong></span>
                </div>
            </div>

            <div id="revEmptyState" class="empty-state">
                <i class="fa fa-search"></i>
                <p>Seleccione filtros y presione <strong>Generar</strong> para revisar facturas con <strong>estado_cerrado = 0</strong> y <strong>saldo = 0</strong>.</p>
            </div>

            <div id="revFacturaWrap" style="display:none;">
                <div class="tab-toolbar" style="margin-bottom:10px;">
                    <div class="tab-title">
                        <i class="fa fa-file-invoice" style="color:#0284c7;"></i>
                        Reporte Factura por Factura
                    </div>
                    <button class="btn-export" type="button" onclick="exportarRevisionFacturasExcel('facturas')">
                        <i class="fa fa-file-excel-o"></i> Descargar Excel Facturas
                    </button>
                </div>
                <div style="overflow-x:auto;border:1px solid #e2e8f0;border-radius:10px;background:#fff;">
                    <table id="dtRevFacturas" class="table table-hover table-sm w-100 mb-0">
                        <thead>
                            <tr>
                                <th>Fecha Pago</th>
                                <th>Fecha Creación Factura</th>
                                <th>Factura</th>
                                <th>Aplicación Pago ID</th>
                                <th>Cliente</th>
                                <th>Escala Cliente</th>
                                <th>Capacidad</th>
                                <th>Rol</th>
                                <th>Usuario</th>
                                <th class="text-right">Saldo</th>
                                <th class="text-right">Abonado</th>
                                <th class="text-center"># Abonos</th>
                                <th>Último Abono</th>
                                <th class="text-right">SubTotal Factura</th>
                                <th class="text-right">Total Factura</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div id="revProductoWrap" style="display:none;margin-top:18px;">
                <div class="tab-toolbar" style="margin-bottom:10px;">
                    <div class="tab-title">
                        <i class="fa fa-cubes" style="color:#059669;"></i>
                        Reporte Producto por Producto
                    </div>
                    <button class="btn-export" type="button" onclick="exportarRevisionFacturasExcel('productos')">
                        <i class="fa fa-file-excel-o"></i> Descargar Excel Productos
                    </button>
                </div>
                <div style="overflow-x:auto;border:1px solid #d1fae5;border-radius:10px;background:#fff;">
                    <table id="dtRevProductos" class="table table-hover table-sm w-100 mb-0">
                        <thead>
                            <tr>
                                <th>Fecha Pago</th>
                                <th>Factura</th>
                                <th>Cliente</th>
                                <th>Capacidad</th>
                                <th>Rol</th>
                                <th>Usuario</th>
                                <th>Producto</th>
                                <th>Categoría Precio</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Precio Unidad</th>
                                <th class="text-right">Precio Seleccionado</th>
                                <th class="text-right">Base Unitaria</th>
                                <th class="text-right">Base Precio Seleccionado</th>
                                <th class="text-right">% Comisión</th>
                                <th class="text-right">Comisión Proyectada</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 5: FACTURA POR ACTOR --}}
        <div class="tab-pane fade" id="tab-factura-actor" role="tabpanel">
            <div class="filter-panel mb-4">
                <div class="fp-title"><i class="fa fa-users"></i> Filtros — Factura por Actor</div>
                <div class="row align-items-end">
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="filter-label"><i class="fa fa-calendar-alt mr-1"></i>Desde</label>
                        <input type="date" id="faDesde" class="fp-input">
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="filter-label"><i class="fa fa-calendar-check mr-1"></i>Hasta</label>
                        <input type="date" id="faHasta" class="fp-input">
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="filter-label"><i class="fa fa-user-tie mr-1"></i>Asesor Comercial</label>
                        <select id="faAsesor" class="form-control" style="width:100%;"></select>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="filter-label"><i class="fa fa-headset mr-1"></i>Tele Asesor</label>
                        <select id="faTeleasesor" class="form-control" style="width:100%;"></select>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="filter-label"><i class="fa fa-truck mr-1"></i>Gestor de Entregas</label>
                        <select id="faGestor" class="form-control" style="width:100%;"></select>
                    </div>
                    <div class="col-md-2 col-sm-12 mb-2">
                        <label class="filter-label">&nbsp;</label>
                        <div style="display:flex;gap:8px;">
                            <button class="btn-generar" id="btnFaGenerar">
                                <i class="fa fa-search"></i> Buscar
                            </button>
                            <button class="btn-limpiar" id="btnFaLimpiar">
                                <i class="fa fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="faKpis" style="display:none;background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:10px;padding:12px 16px;font-size:13px;color:#0f172a;margin-bottom:14px;">
                <div style="display:flex;gap:18px;flex-wrap:wrap;align-items:center;">
                    <span><i class="fa fa-file-invoice mr-1"></i>Facturas: <strong id="faTotalFacturas">0</strong></span>
                    <span><i class="fa fa-money mr-1"></i>Subtotal: <strong id="faTotalSubtotal">L. 0.00</strong></span>
                    <span><i class="fa fa-percent mr-1"></i>ISV: <strong id="faTotalIsv">L. 0.00</strong></span>
                    <span><i class="fa fa-check-circle mr-1" style="color:#059669;"></i>Total: <strong id="faTotalTotal">L. 0.00</strong></span>
                </div>
            </div>

            <div id="faEmptyState" class="empty-state">
                <i class="fa fa-users"></i>
                <p>Seleccione un período y presione <strong>Buscar</strong> para ver las facturas cerradas por actor.</p>
            </div>

            <div id="faTableWrap" style="display:none;">
                <div class="tab-toolbar" style="margin-bottom:10px;">
                    <div class="tab-title">
                        <i class="fa fa-file-invoice" style="color:#7c3aed;"></i>
                        Facturas Cerradas por Actor
                    </div>
                    <button class="btn-export" type="button" onclick="exportarFacturaActorExcel()">
                        <i class="fa fa-file-excel-o"></i> Descargar Excel
                    </button>
                </div>
                <div style="overflow-x:auto;border:1px solid #e2e8f0;border-radius:10px;background:#fff;">
                    <table id="dtFacturaActor" class="table table-hover table-sm w-100 mb-0">
                        <thead>
                            <tr>
                                <th>N° Factura (CAI)</th>
                                <th>Asesor Comercial</th>
                                <th>Tele Asesor</th>
                                <th>Gestor de Entregas</th>
                                <th>Fecha Creación</th>
                                <th>Fecha Último Pago</th>
                                <th>Tipo Factura</th>
                                <th>Política</th>
                                <th class="text-right">Subtotal</th>
                                <th class="text-right">ISV</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODAL DETALLE NÓMINA --}}
<div class="modal fade" id="modalDetalleNomina" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-nomina-detalle" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e1b4b;color:#fff;">
                <div>
                    <h5 class="modal-title" id="mdnTitulo">
                        <i class="fa fa-list-alt"></i>Detalle de Comisiones
                    </h5>
                    <div class="mdn-subtitle">Trazabilidad de cálculo por factura comisionada</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <button type="button" class="btn btn-sm mdn-btn-excel" onclick="exportarDetalleNominaExcel()">
                        <i class="fa fa-file-excel mr-1"></i>Exportar Excel
                    </button>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="dtNominaDetalle" class="table table-hover table-sm w-100">
                        <thead>
                            <tr>
                                <th>Factura</th>
                                <th>Cliente</th>
                                <th>Fecha Cierre</th>
                                <th>Rol Comisionado</th>
                                <th class="text-right">Comisión Original</th>
                                <th class="text-right">Retención Aplicada</th>
                                <th class="text-right">Comisión Final</th>
                                <th class="text-right">Base Comisionable</th>
                                <th>Fuente Base Comisionable</th>
                                <th>Resumen por Producto / Escala</th>
                                <th class="text-center">Estado</th>
                                <th>Observaciones de Reversa</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProductosFactura" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="mpfTitulo">Detalle de Productos</h5>
                    <div style="font-size:11px;opacity:.8;" id="mpfSubtitulo">Desglose real por factura comisionada</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <button type="button" class="btn btn-sm mdn-btn-excel" onclick="exportarProductosFacturaExcel()">
                        <i class="fa fa-file-excel mr-1"></i>Exportar Excel
                    </button>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="dtProductosFactura" class="table table-sm table-hover w-100 mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría Cliente Escala</th>
                                <th>Categoría Precio Vendida</th>
                                <th class="text-right">%</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Precio Unitario</th>
                                <th class="text-right">Precio Escala</th>
                                <th class="text-right">Base Comisionable</th>
                                <th>Fuente Base</th>
                                <th class="text-right">Comisión</th>
                            </tr>
                        </thead>
                        <tbody id="mpfBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>{{-- /Livewire root --}}

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="{{ asset('js/js_proyecto/comisiones/Escalado/reportesComisionesGenerales.js') }}"></script>
@endpush
