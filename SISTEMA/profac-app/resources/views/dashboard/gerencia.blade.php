{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — GERENCIA / ADMINISTRACIÓN
     Roles: 1 (Administrador), 19 (Gerente)
═══════════════════════════════════════════════════════════════ --}}

{{-- ── KPIs ── --}}
<div class="dash-kpi-grid">
    @if($this->enlaceAnalisis('ventas'))
    <a class="dash-kpi orange dash-kpi-action" href="{{ $this->enlaceAnalisis('ventas') }}">
    @else
    <div class="dash-kpi orange">
    @endif
        <div class="dash-kpi-icon"><i class="fa fa-dollar"></i></div>
        <div class="dash-kpi-label">Venta total</div>
        <div class="dash-kpi-value">L. {{ number_format($kpis['venta_total'] ?? 0, 2) }}</div>
        <div class="dash-kpi-sub">
            @if(isset($kpis['variacion']) && $kpis['variacion'] !== null)
                @if($kpis['variacion'] >= 0)
                    <span class="dash-up"><i class="fa fa-arrow-up"></i> +{{ $kpis['variacion'] }}%</span>
                @else
                    <span class="dash-down"><i class="fa fa-arrow-down"></i> {{ $kpis['variacion'] }}%</span>
                @endif
                vs período anterior
            @endif
        </div>
    @if($this->enlaceAnalisis('ventas'))
        <span class="dash-kpi-hint">Ver Dashboard BI Ventas <i class="fa fa-arrow-right" aria-hidden="true"></i></span>
    </a>
    @else
    </div>
    @endif
    @include('dashboard.kpi-saldo')
    @if($this->puedeVerArea('inventario'))
    @include('dashboard.kpi', ['etiqueta' => 'Productos bajos de inventario', 'icono' => 'fa-cubes', 'valor' => number_format($kpisInventario['bajos_stock'] ?? 0), 'ayuda' => 'Existencia actual: 0 a menos de 10 unidades', 'accion' => 'inventario', 'color' => 'orange', 'enlace' => null])
    @endif
    @include('dashboard.kpi', ['etiqueta' => 'Facturas emitidas', 'icono' => 'fa-file-invoice', 'valor' => number_format($kpis['num_facturas'] ?? 0), 'ayuda' => 'Emitidas en el período', 'accion' => 'facturas', 'color' => '', 'enlace' => null])
    @include('dashboard.kpi', ['etiqueta' => 'Clientes activos', 'icono' => 'fa-users', 'valor' => number_format($kpis['clientes_act'] ?? 0), 'ayuda' => 'Compraron desde el inicio del sistema hasta hoy', 'accion' => 'clientes', 'color' => 'green', 'enlace' => null])
    @include('dashboard.kpi', ['etiqueta' => 'Clientes creados', 'icono' => 'fa-user-plus', 'valor' => number_format($kpis['clientes_nuevos'] ?? 0), 'ayuda' => 'Creados desde el inicio del sistema hasta hoy', 'accion' => 'clientes_nuevos', 'color' => 'blue', 'enlace' => null])

    @include('dashboard.kpi-atencion')
    @include('dashboard.kpi-poco-movimiento')
</div>

{{-- ── Grid principal: Tendencia 12 m + Productividad vendedores ── --}}
<div class="dash-grid-2" style="margin-bottom:20px;">

    {{-- Tendencia 12 meses --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <h5><i class="fa fa-area-chart"></i> Tendencia — Últimos 12 meses</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-ger-tendencia" wire:ignore style="min-height:270px;"></div>
        </div>
    </div>

    {{-- Participación de mercado (pie) --}}
    <div class="dash-card">
        <div class="dash-card-header orange">
            <h5><i class="fa fa-pie-chart"></i> Participación por Vendedor</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-ger-participacion" wire:ignore style="min-height:270px;"></div>
        </div>
    </div>

</div>

{{-- ── Productividad vendedores ── --}}
<div class="dash-card" style="margin-bottom:20px;">
    <div class="dash-card-header blue">
        <h5><i class="fa fa-bar-chart"></i> Productividad por Vendedor — {{ $periodoLabel }}</h5>
    </div>
    <div class="dash-card-body">
        <div id="chart-ger-productividad" wire:ignore style="min-height:260px; margin-bottom:16px;"></div>
        <div style="overflow-x:auto;">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Vendedor</th>
                        <th>Venta Total</th>
                        <th>Facturas</th>
                        <th>Clientes</th>
                        <th>Ticket Prom.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productividad as $i => $p)
                    <tr>
                        <td><strong>{{ $i+1 }}</strong></td>
                        <td>{{ $p['vendedor'] }}</td>
                        <td>L. {{ number_format($p['venta_total'],2) }}</td>
                        <td>{{ number_format($p['num_facturas']) }}</td>
                        <td>{{ number_format($p['clientes_atendidos']) }}</td>
                        <td>L. {{ $p['num_facturas'] > 0 ? number_format($p['venta_total']/$p['num_facturas'],2) : '0.00' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center; color:var(--dash-muted); padding:24px;">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Top Clientes + Comparativa ── --}}
<div class="dash-grid-2">

    <div class="dash-card">
        <div class="dash-card-header orange">
            <h5><i class="fa fa-trophy"></i> Top Clientes — {{ $periodoLabel }}</h5>
        </div>
        <div class="dash-card-body" style="padding:0;">
            <div style="overflow-x:auto; max-height:360px; overflow-y:auto;">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Part.</th>
                            <th>Var.</th>
                            <th>Alerta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topClientes as $i => $c)
                        <tr>
                            <td><strong>{{ $i+1 }}</strong></td>
                            <td>{{ $c['nombre'] }}</td>
                            <td>L. {{ number_format($c['total_actual'],2) }}</td>
                            <td>
                                {{ $c['participacion'] }}%
                                <div class="dash-progress"><div class="dash-progress-fill" style="width:{{ min(100,$c['participacion']) }}%;"></div></div>
                            </td>
                            <td>
                                @if($c['variacion'] !== null)
                                    @if($c['variacion'] >= 0)
                                        <span class="dash-up">↑ {{ $c['variacion'] }}%</span>
                                    @else
                                        <span class="dash-down">↓ {{ $c['variacion'] }}%</span>
                                    @endif
                                @else <span style="color:var(--dash-muted);">—</span>
                                @endif
                            </td>
                            <td>
                                @if($c['requiere_atencion'])
                                    <span class="dash-badge dash-badge-danger">ALERTA</span>
                                @else
                                    <span class="dash-badge dash-badge-success">OK</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center; color:var(--dash-muted); padding:24px;">Sin datos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dash-card">
        <div class="dash-card-header blue">
            <h5><i class="fa fa-exchange"></i> Comparativa vs Período Anterior</h5>
        </div>
        <div class="dash-card-body" style="padding:0;">
            <div style="overflow-x:auto; max-height:360px; overflow-y:auto;">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Actual</th>
                            <th>Anterior</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($comparativa as $c)
                        <tr>
                            <td>{{ $c['cliente'] }}</td>
                            <td>L. {{ number_format($c['mes_actual'],2) }}</td>
                            <td>L. {{ number_format($c['mes_anterior'],2) }}</td>
                            <td>
                                @if($c['variacion'] !== null)
                                    @if($c['variacion'] >= 0)
                                        <span class="dash-up">+{{ $c['variacion'] }}%</span>
                                    @else
                                        <span class="dash-down">{{ $c['variacion'] }}%</span>
                                    @endif
                                @else <span style="color:var(--dash-muted);">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center; color:var(--dash-muted); padding:24px;">Sin datos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@include('dashboard.cartera-resumen')

{{-- ── Sección Inventario (solo administrador ve todo) ── --}}
@if(!empty($kpisInventario))
<div style="margin:28px 0 12px; display:flex; align-items:center; gap:12px;">
    <span style="font-weight:700; font-size:15px; color:var(--dash-text);">
        <i class="fa fa-cubes" style="color:#2980b9;"></i> Inventario
    </span>
    <div style="flex:1; height:1px; background:var(--dash-border);"></div>
</div>

@include('dashboard.inventario-kpis', ['kpisInventario' => $kpisInventario])
@endif
