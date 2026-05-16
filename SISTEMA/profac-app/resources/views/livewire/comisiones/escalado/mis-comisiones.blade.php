<style>
/* ==================== DASHBOARD COMISIONES ==================== */
.mc-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
    border-radius: 16px;
    padding: 32px 36px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.mc-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 240px; height: 240px;
    background: radial-gradient(circle, rgba(16,185,129,.18) 0%, transparent 70%);
    pointer-events: none;
}
.mc-hero::after {
    content: '';
    position: absolute;
    bottom: -40px; left: -40px;
    width: 180px; height: 180px;
    background: radial-gradient(circle, rgba(59,130,246,.15) 0%, transparent 70%);
    pointer-events: none;
}
.mc-avatar {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981, #3b82f6);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; font-weight: 800; color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 16px rgba(16,185,129,.4);
}
.mc-hero-name { font-size: 1.45rem; font-weight: 700; color: #f8fafc; margin-bottom: 2px; }
.mc-hero-sub  { font-size: .82rem; color: #94a3b8; }
.mc-hero-total-label { font-size: .75rem; color: #64748b; text-transform: uppercase; letter-spacing: .6px; }
.mc-hero-total-val   { font-size: 2rem; font-weight: 800; color: #10b981; line-height: 1.1; }
/* KPI Cards */
.mc-kpi {
    background: #fff;
    border-radius: 14px;
    padding: 18px 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    border-left: 4px solid transparent;
    transition: transform .2s, box-shadow .2s;
    height: 100%;
}
.mc-kpi:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
.mc-kpi.kpi-green  { border-left-color: #10b981; }
.mc-kpi.kpi-blue   { border-left-color: #3b82f6; }
.mc-kpi.kpi-amber  { border-left-color: #f59e0b; }
.mc-kpi.kpi-purple { border-left-color: #8b5cf6; }
.mc-kpi.kpi-rose   { border-left-color: #f43f5e; }
.mc-kpi.kpi-teal   { border-left-color: #14b8a6; }
.mc-kpi-icon {
    width: 42px; height: 42px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; margin-bottom: 12px;
}
.mc-kpi-icon.bg-green  { background: #d1fae5; color: #059669; }
.mc-kpi-icon.bg-blue   { background: #dbeafe; color: #2563eb; }
.mc-kpi-icon.bg-amber  { background: #fef3c7; color: #d97706; }
.mc-kpi-icon.bg-purple { background: #ede9fe; color: #7c3aed; }
.mc-kpi-icon.bg-rose   { background: #ffe4e6; color: #e11d48; }
.mc-kpi-icon.bg-teal   { background: #ccfbf1; color: #0d9488; }
.mc-kpi-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .5px; color: #64748b; margin-bottom: 4px; }
.mc-kpi-val   { font-size: 1.35rem; font-weight: 800; color: #0f172a; line-height: 1; margin-bottom: 2px; }
.mc-kpi-sub   { font-size: .73rem; color: #94a3b8; }
/* Section card */
.mc-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    margin-bottom: 22px;
    overflow: hidden;
}
.mc-card-header {
    padding: 16px 22px;
    background: linear-gradient(90deg, #0f172a, #1e3a5f);
    display: flex; align-items: center; justify-content: space-between;
}
.mc-card-header h6 { color: #f1f5f9; font-size: .92rem; font-weight: 700; margin: 0; }
.mc-card-body { padding: 20px 22px; }
/* Table */
.mc-table { font-size: .84rem; width: 100%; }
.mc-table thead th {
    background: #f8fafc;
    font-size: .7rem; text-transform: uppercase; letter-spacing: .4px;
    color: #475569; font-weight: 700;
    padding: 10px 12px; border-bottom: 2px solid #e2e8f0;
}
.mc-table tbody td { padding: 10px 12px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
.mc-table tbody tr:hover { background: #f8fafc; }
.mc-table tbody tr:last-child td { border-bottom: none; }
/* Fila de cabecera de grupo (mes) */
.mc-group-row td {
    background: linear-gradient(90deg, #f0f7ff, #f8fafc);
    padding: 9px 14px !important;
    border-top: 2px solid #bfdbfe !important;
    border-bottom: 1px solid #dbeafe !important;
    color: #1e3a5f;
    pointer-events: none;
    cursor: default !important;
}
.mc-group-row:hover td { background: linear-gradient(90deg, #e0f2fe, #f0f7ff) !important; }
/* Period filter pills */
.mc-period-pill {
    display: inline-block; padding: 4px 14px; border-radius: 20px;
    font-size: .75rem; font-weight: 600; cursor: pointer;
    border: 1.5px solid #cbd5e1; color: #475569;
    transition: all .15s; background: transparent;
    margin-left: 4px;
}
.mc-period-pill.active, .mc-period-pill:hover {
    background: #10b981; color: #fff; border-color: #10b981;
}
/* Progress bar top productos */
.mc-prog-bar {
    height: 6px; border-radius: 3px;
    background: #e2e8f0; overflow: hidden; margin-top: 4px;
}
.mc-prog-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, #10b981, #3b82f6); transition: width .6s ease; }
/* Modal overlay */
.mc-modal-backdrop {
    position: fixed; inset: 0; background: rgba(0,0,0,.55);
    z-index: 1050; display: none; align-items: center; justify-content: center;
}
.mc-modal-backdrop.show { display: flex; }
.mc-modal-box {
    background: #fff; border-radius: 16px; width: 92%; max-width: 860px;
    max-height: 88vh; overflow: hidden; display: flex; flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
}
.mc-modal-header {
    background: linear-gradient(90deg, #0f172a, #1e3a5f);
    padding: 16px 22px; display: flex; align-items: center; justify-content: space-between;
}
.mc-modal-header h6 { color: #f1f5f9; margin: 0; font-weight: 700; }
.mc-modal-close { background: none; border: none; color: #94a3b8; font-size: 1.3rem; cursor: pointer; line-height:1; }
.mc-modal-close:hover { color: #fff; }
.mc-modal-body { padding: 20px 22px; overflow-y: auto; }
@media (max-width: 767px) {
    .mc-hero { padding: 20px 16px; }
    .mc-hero-total-val { font-size: 1.5rem; }
    .mc-kpi-val { font-size: 1.1rem; }
}
</style>

<div>

{{-- HERO BANNER --}}
<div class="mc-hero">
    <div class="d-flex align-items-start justify-content-between flex-wrap" style="gap:16px;">
        <div class="d-flex align-items-center" style="gap:16px;">
            <div class="mc-avatar">{{ strtoupper(substr($info->name, 0, 2)) }}</div>
            <div>
                <div class="mc-hero-name">{{ $info->name }}</div>
                <div class="mc-hero-sub">
                    <i class="fa fa-shield-alt mr-1" style="color:#3b82f6;"></i>{{ $info->rol }}
                    &nbsp;&bull;&nbsp;
                    <i class="fa fa-id-badge mr-1" style="color:#10b981;"></i>ID {{ $info->id }}
                    &nbsp;&bull;&nbsp;
                    <i class="fa fa-calendar-check mr-1" style="color:#f59e0b;"></i>
                    {{ $kpis->meses_activos }} {{ $kpis->meses_activos == 1 ? 'mes activo' : 'meses activos' }}
                </div>
            </div>
        </div>
        <div class="text-right">
            <div class="mc-hero-total-label"><i class="fa fa-history mr-1"></i>Total historico</div>
            <div class="mc-hero-total-val">L {{ number_format($kpis->total_historico, 2) }}</div>
            <div class="mc-hero-sub" style="font-size:.7rem;">{{ $kpis->facturas_totales }} facturas en total</div>
        </div>
    </div>
</div>

{{-- KPI CARDS --}}
@php
    $promedio  = $kpis->meses_activos > 0 ? round($kpis->total_historico / $kpis->meses_activos, 2) : 0;
    $variacion = null;
    if(count($historicoMeses) >= 2) {
        $ultimo    = $historicoMeses[0]->comision_acumulada ?? 0;
        $penultimo = $historicoMeses[1]->comision_acumulada ?? 0;
        $variacion = $penultimo > 0 ? round((($ultimo - $penultimo) / $penultimo) * 100, 1) : null;
    }
@endphp

<div class="row mb-3">
    <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="mc-kpi kpi-green h-100">
            <div class="mc-kpi-icon bg-green"><i class="fa fa-calendar-alt"></i></div>
            <div class="mc-kpi-label">Mes actual</div>
            <div class="mc-kpi-val">L {{ number_format($kpis->total_mes_actual, 2) }}</div>
            <div class="mc-kpi-sub">{{ $kpis->facturas_mes_actual }} facturas</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="mc-kpi kpi-amber h-100">
            <div class="mc-kpi-icon bg-amber"><i class="fa fa-trophy"></i></div>
            <div class="mc-kpi-label">Mejor mes</div>
            <div class="mc-kpi-val">L {{ $mejorMes ? number_format($mejorMes->comision_acumulada, 2) : '0.00' }}</div>
            <div class="mc-kpi-sub">{{ $mejorMes ? \Carbon\Carbon::parse($mejorMes->mes_comision)->isoFormat('MMM YYYY') : '---' }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="mc-kpi kpi-blue h-100">
            <div class="mc-kpi-icon bg-blue"><i class="fa fa-chart-line"></i></div>
            <div class="mc-kpi-label">Ano {{ date('Y') }}</div>
            <div class="mc-kpi-val">L {{ number_format($kpis->total_anio_actual, 2) }}</div>
            <div class="mc-kpi-sub">acumulado ano</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="mc-kpi kpi-purple h-100">
            <div class="mc-kpi-icon bg-purple"><i class="fa fa-file-invoice-dollar"></i></div>
            <div class="mc-kpi-label">Facturas</div>
            <div class="mc-kpi-val">{{ $kpis->facturas_totales }}</div>
            <div class="mc-kpi-sub">total historicas</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="mc-kpi kpi-teal h-100">
            <div class="mc-kpi-icon bg-teal"><i class="fa fa-equals"></i></div>
            <div class="mc-kpi-label">Promedio mensual</div>
            <div class="mc-kpi-val">L {{ number_format($promedio, 2) }}</div>
            <div class="mc-kpi-sub">entre {{ $kpis->meses_activos }} meses</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="mc-kpi kpi-rose h-100">
            <div class="mc-kpi-icon bg-rose"><i class="fa fa-exchange-alt"></i></div>
            <div class="mc-kpi-label">Variacion</div>
            @if($variacion !== null)
                <div class="mc-kpi-val {{ $variacion >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:1.2rem;">
                    {{ $variacion >= 0 ? '+' : '' }}{{ $variacion }}%
                </div>
                <div class="mc-kpi-sub">vs mes anterior</div>
            @else
                <div class="mc-kpi-val" style="font-size:1.1rem;color:#94a3b8;">---</div>
                <div class="mc-kpi-sub">sin datos</div>
            @endif
        </div>
    </div>
</div>

{{-- GRAFICA + TOP PRODUCTOS --}}
<div class="row mb-2">
    <div class="col-lg-7 mb-3">
        <div class="mc-card h-100">
            <div class="mc-card-header">
                <h6><i class="fa fa-chart-area mr-2" style="color:#10b981;"></i>Historico de Comisiones</h6>
                <span style="font-size:.72rem;color:#94a3b8;" id="chart-periodo-label"></span>
            </div>
            <div class="mc-card-body" style="padding-bottom:14px;">
                <canvas id="mcChartHistorico" height="130"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5 mb-3">
        <div class="mc-card h-100">
            <div class="mc-card-header" style="flex-wrap:wrap;gap:8px;">
                <h6><i class="fa fa-box-open mr-2" style="color:#f59e0b;"></i>Top Productos</h6>
                <div>
                    <span class="mc-period-pill active" onclick="topPeriodo(this,'todo')">Todo</span>
                    <span class="mc-period-pill" onclick="topPeriodo(this,'anio')">{{ date('Y') }}</span>
                    <span class="mc-period-pill" onclick="topPeriodo(this,'mes')">Este mes</span>
                </div>
            </div>
            <div class="mc-card-body" id="top-productos-body" style="padding-top:10px;max-height:320px;overflow-y:auto;">
                <div class="text-center py-4"><i class="fa fa-spinner fa-spin text-muted"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- HISTORIAL MENSUAL --}}
<div class="mc-card">
    <div class="mc-card-header">
        <h6><i class="fa fa-table mr-2" style="color:#3b82f6;"></i>Historial Mensual de Comisiones</h6>
        <small style="color:#64748b;">Haz clic en una fila para ver el detalle de facturas</small>
    </div>
    <div class="mc-card-body p-0">
        <div class="table-responsive">
            <table id="tbl_comisiones_empleado" class="table mc-table mb-0">
                <thead>
                    <tr>
                        <th style="display:none;"></th>{{-- periodo (oculto, para orden) --}}
                        <th><i class="fa fa-user-tag mr-1" style="color:#3b82f6;"></i>Rol</th>
                        <th class="text-right"><i class="fa fa-coins mr-1" style="color:#059669;"></i>Comisión</th>
                        <th class="text-center"><i class="fa fa-receipt mr-1" style="color:#f59e0b;"></i>Facturas</th>
                        <th><i class="fa fa-clock mr-1" style="color:#94a3b8;"></i>Última Actualización</th>
                        <th class="text-center"><i class="fa fa-tag mr-1" style="color:#8b5cf6;"></i>Estado</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL DETALLE MES --}}
<div class="mc-modal-backdrop" id="mcModalDetalle">
    <div class="mc-modal-box">
        <div class="mc-modal-header">
            <h6 id="mcModalTitle"><i class="fa fa-receipt mr-2"></i>Facturas del mes</h6>
            <button class="mc-modal-close" onclick="cerrarModal()"><i class="fa fa-times"></i></button>
        </div>
        <div class="mc-modal-body">
            <div class="table-responsive">
                <table id="tbl_detalle_mes" class="table mc-table mb-0">
                    <thead>
                        <tr>
                            <th>#Factura</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Rol</th>
                            <th>Monto Comision</th>
                            <th>Productos</th>
                            <th>Unidades</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/js_proyecto/comisiones/Escalado/misComisiones.js') }}"></script>
@endpush
