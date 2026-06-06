{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — DEFAULT
     Roles no mapeados específicamente
═══════════════════════════════════════════════════════════════ --}}

<div class="dash-card" style="margin-bottom:20px;">
    <div class="dash-card-header">
        <h5><i class="fa fa-home"></i> Bienvenido, {{ auth()->user()->name }}</h5>
    </div>
    <div class="dash-card-body">
        <p style="color:var(--dash-muted); margin:0;">
            Aquí encontrarás un resumen de las actividades del período seleccionado.
        </p>
    </div>
</div>

<div class="dash-kpi-grid">
    <div class="dash-kpi orange">
        <div class="dash-kpi-icon"><i class="fa fa-dollar"></i></div>
        <div class="dash-kpi-label">Venta total</div>
        <div class="dash-kpi-value">L. {{ number_format($kpis['venta_total'] ?? 0, 2) }}</div>
        <div class="dash-kpi-sub">en el período</div>
    </div>
    <div class="dash-kpi">
        <div class="dash-kpi-icon"><i class="fa fa-file-text-o"></i></div>
        <div class="dash-kpi-label">Facturas</div>
        <div class="dash-kpi-value">{{ number_format($kpis['num_facturas'] ?? 0) }}</div>
        <div class="dash-kpi-sub">emitidas</div>
    </div>
    <div class="dash-kpi green">
        <div class="dash-kpi-icon"><i class="fa fa-users"></i></div>
        <div class="dash-kpi-label">Clientes activos</div>
        <div class="dash-kpi-value">{{ number_format($kpis['clientes_act'] ?? 0) }}</div>
        <div class="dash-kpi-sub">en el período</div>
    </div>
</div>
