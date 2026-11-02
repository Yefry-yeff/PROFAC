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
    @if($this->enlaceAnalisis('ventas'))
    <a class="dash-kpi orange dash-kpi-action" href="{{ $this->enlaceAnalisis('ventas') }}">
    @else
    <div class="dash-kpi orange">
    @endif
        <div class="dash-kpi-icon"><i class="fa fa-dollar"></i></div>
        <div class="dash-kpi-label">Venta total</div>
        <div class="dash-kpi-value">L. {{ number_format($kpis['venta_total'] ?? 0, 2) }}</div>
        <div class="dash-kpi-sub">en el período</div>
    @if($this->enlaceAnalisis('ventas'))
        <span class="dash-kpi-hint">Ver Dashboard BI Ventas <i class="fa fa-arrow-right" aria-hidden="true"></i></span>
    </a>
    @else
    </div>
    @endif
    @include('dashboard.kpi', ['etiqueta' => 'Facturas emitidas', 'icono' => 'fa-file-invoice', 'valor' => number_format($kpis['num_facturas'] ?? 0), 'ayuda' => 'Emitidas en el período', 'accion' => 'facturas', 'color' => '', 'enlace' => null])
    @include('dashboard.kpi', ['etiqueta' => 'Clientes activos', 'icono' => 'fa-users', 'valor' => number_format($kpis['clientes_act'] ?? 0), 'ayuda' => 'Compraron desde el inicio del sistema hasta hoy', 'accion' => 'clientes', 'color' => 'green', 'enlace' => null])
    @include('dashboard.kpi-poco-movimiento')
</div>
