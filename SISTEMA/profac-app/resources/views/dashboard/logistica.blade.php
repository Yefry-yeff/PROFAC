{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — LOGÍSTICA
     Roles: 7, 10 (Picking), 11, 16, 17
═══════════════════════════════════════════════════════════════ --}}

{{-- ── KPIs Básicos ── --}}
<div class="dash-kpi-grid">
    <div class="dash-kpi orange">
        <div class="dash-kpi-icon"><i class="fa fa-truck"></i></div>
        <div class="dash-kpi-label">Entregas en el período</div>
        <div class="dash-kpi-value">{{ number_format($kpis['num_facturas'] ?? 0) }}</div>
        <div class="dash-kpi-sub">facturas/pedidos</div>
    </div>
    <div class="dash-kpi green">
        <div class="dash-kpi-icon"><i class="fa fa-check-square-o"></i></div>
        <div class="dash-kpi-label">Clientes atendidos</div>
        <div class="dash-kpi-value">{{ number_format($kpis['clientes_act'] ?? 0) }}</div>
        <div class="dash-kpi-sub">Desde el inicio del sistema hasta hoy</div>
    </div>
    <div class="dash-kpi blue">
        <div class="dash-kpi-icon"><i class="fa fa-file-text-o"></i></div>
        <div class="dash-kpi-label">Documentos</div>
        <div class="dash-kpi-value">{{ number_format($kpis['num_facturas'] ?? 0) }}</div>
        <div class="dash-kpi-sub">facturas procesadas</div>
    </div>
    @include('dashboard.kpi', ['etiqueta' => 'Clientes creados', 'icono' => 'fa-user-plus', 'valor' => number_format($kpis['clientes_nuevos'] ?? 0), 'ayuda' => 'Creados desde el inicio del sistema hasta hoy', 'accion' => 'clientes_nuevos', 'color' => 'blue', 'enlace' => null])
    @include('dashboard.kpi-poco-movimiento')
</div>

{{-- ── Tendencia mensual ── --}}
<div class="dash-card">
    <div class="dash-card-header">
        <h5><i class="fa fa-bar-chart"></i> Entregas — Últimos 12 meses</h5>
    </div>
    <div class="dash-card-body">
        <div id="chart-log-tendencia" wire:ignore style="min-height:260px;"></div>
    </div>
</div>
