{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — AUX CONTABLE
     Rol: 6 (Aux Contable)
     KPIs generales + info básica de cobros
═══════════════════════════════════════════════════════════════ --}}

<div class="dash-kpi-grid">
    @include('dashboard.kpi', ['etiqueta' => 'Facturas emitidas', 'icono' => 'fa-file-invoice', 'valor' => number_format($kpis['num_facturas'] ?? 0), 'ayuda' => 'Emitidas en el período', 'accion' => 'facturas', 'color' => '', 'enlace' => null])
    @include('dashboard.kpi', ['etiqueta' => 'Facturas emitidas', 'icono' => 'fa-file-invoice', 'valor' => number_format($kpis['num_facturas'] ?? 0), 'ayuda' => 'Emitidas en el período', 'accion' => 'facturas', 'color' => '', 'enlace' => null])
    @include('dashboard.kpi-saldo')
    <div class="dash-kpi green">
        <div class="dash-kpi-icon"><i class="fa fa-check-circle"></i></div>
        <div class="dash-kpi-label">Recuperado</div>
        <div class="dash-kpi-value">L. {{ number_format($kpisCobros['total_recuperado'] ?? 0, 2) }}</div>
        <div class="dash-kpi-sub">cobrado</div>
    </div>
    <div class="dash-kpi orange">
        <div class="dash-kpi-icon"><i class="fa fa-exclamation-triangle"></i></div>
        <div class="dash-kpi-label">Facturas vencidas</div>
        <div class="dash-kpi-value">{{ number_format($kpisCobros['facturas_vencidas'] ?? 0) }}</div>
        <div class="dash-kpi-sub">documentos vencidos</div>
    </div>
    <div class="dash-kpi blue">
        <div class="dash-kpi-icon"><i class="fa fa-user-times"></i></div>
        <div class="dash-kpi-label">Clientes morosos</div>
        <div class="dash-kpi-value">{{ number_format($kpisCobros['clientes_morosos'] ?? 0) }}</div>
        <div class="dash-kpi-sub">con saldo vencido</div>
    </div>
    @include('dashboard.kpi-poco-movimiento')
</div>

<div class="dash-grid-2">
    <div class="dash-card">
        <div class="dash-card-header">
            <h5><i class="fa fa-area-chart"></i> Tendencia — 12 meses</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-auxcnt-tendencia" wire:ignore style="min-height:240px;"></div>
        </div>
    </div>
    <div class="dash-card">
        <div class="dash-card-header red">
            <h5><i class="fa fa-pie-chart"></i> Antigüedad de Saldos</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-auxcnt-antiguedad" wire:ignore style="min-height:240px;"></div>
        </div>
    </div>
</div>
