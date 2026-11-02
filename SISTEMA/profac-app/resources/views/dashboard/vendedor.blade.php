{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — ASESOR COMERCIAL / TELE-ASESOR
     Roles: 2 (Asesor Comercial), 15 (Tele-Asesor Comercial)
═══════════════════════════════════════════════════════════════ --}}

{{-- ── KPIs ── --}}
<div class="dash-kpi-grid">
    @if($this->enlaceAnalisis('ventas'))
    <a class="dash-kpi orange dash-kpi-action" href="{{ $this->enlaceAnalisis('ventas') }}">
    @else
    <div class="dash-kpi orange">
    @endif
        <div class="dash-kpi-icon"><i class="fa fa-dollar"></i></div>
        <div class="dash-kpi-label">Venta del mes</div>
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
    @include('dashboard.kpi', ['etiqueta' => 'Facturas emitidas', 'icono' => 'fa-file-invoice', 'valor' => number_format($kpis['num_facturas'] ?? 0), 'ayuda' => 'Emitidas en el período', 'accion' => 'facturas', 'color' => '', 'enlace' => null])
    @include('dashboard.kpi', ['etiqueta' => 'Clientes activos', 'icono' => 'fa-users', 'valor' => number_format($kpis['clientes_act'] ?? 0), 'ayuda' => 'Compraron desde el inicio del sistema hasta hoy', 'accion' => 'clientes', 'color' => 'green', 'enlace' => null])
    @include('dashboard.kpi', ['etiqueta' => 'Clientes creados', 'icono' => 'fa-user-plus', 'valor' => number_format($kpis['clientes_nuevos'] ?? 0), 'ayuda' => 'Creados desde el inicio del sistema hasta hoy', 'accion' => 'clientes_nuevos', 'color' => 'blue', 'enlace' => null])

    @include('dashboard.kpi-poco-movimiento')
</div>

{{-- ── Alertas: clientes que requieren atención ── --}}
@php $alertas = array_filter($topClientes, fn($c) => $c['requiere_atencion']); @endphp
@if(count($alertas))
<div class="dash-card" style="margin-bottom:20px;">
    <div class="dash-card-header red">
        <h5><i class="fa fa-exclamation-triangle"></i> Clientes que Requieren Atención ({{ count($alertas) }})</h5>
    </div>
    <div class="dash-card-body" style="padding:14px 16px;">
        @foreach(array_slice($alertas, 0, 5) as $c)
        <div class="dash-alert dash-alert-danger">
            <i class="fa fa-exclamation-circle" style="margin-top:2px;"></i>
            <div>
                <strong>{{ $c['nombre'] }}</strong>
                — Última compra: {{ $c['ultima_compra'] ?? 'N/A' }}
                @if($c['variacion'] !== null)
                    | Var: <span class="dash-down">{{ $c['variacion'] }}%</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Grid: Tendencia + Top Clientes ── --}}
<div class="dash-grid-2">

    {{-- Tendencia mensual --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <h5><i class="fa fa-line-chart"></i> Tendencia de Ventas — Últimos 12 meses</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-tendencia-vendedor" wire:ignore style="min-height:260px;"></div>
        </div>
    </div>

    {{-- Top Clientes --}}
    <div class="dash-card">
        <div class="dash-card-header orange">
            <h5><i class="fa fa-trophy"></i> Top Clientes — {{ $periodoLabel }}</h5>
        </div>
        <div class="dash-card-body" style="padding:0;">
            <div style="overflow-x:auto; max-height:320px; overflow-y:auto;">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Var.</th>
                            <th>Atención</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topClientes as $i => $c)
                        <tr>
                            <td><strong>{{ $i+1 }}</strong></td>
                            <td>{{ $c['nombre'] }}</td>
                            <td>L. {{ number_format($c['total_actual'],2) }}</td>
                            <td>
                                @if($c['variacion'] !== null)
                                    @if($c['variacion'] >= 0)
                                        <span class="dash-up">↑ {{ $c['variacion'] }}%</span>
                                    @else
                                        <span class="dash-down">↓ {{ $c['variacion'] }}%</span>
                                    @endif
                                @else
                                    <span style="color:var(--dash-muted);">—</span>
                                @endif
                            </td>
                            <td>
                                @if($c['requiere_atencion'])
                                    <span class="dash-badge dash-badge-danger">SI</span>
                                @else
                                    <span class="dash-badge dash-badge-success">NO</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center; color:var(--dash-muted); padding:24px;">Sin datos en el período</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ── Comparativa vs mes anterior ── --}}
<div class="dash-card">
    <div class="dash-card-header blue">
        <h5><i class="fa fa-exchange"></i> Comparativa vs Período Anterior</h5>
    </div>
    <div class="dash-card-body" style="padding:0;">
        <div style="overflow-x:auto;">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Período actual</th>
                        <th>Período anterior</th>
                        <th>Variación</th>
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
                                    <span class="dash-up">↑ L. {{ number_format($c['mes_actual'] - $c['mes_anterior'],2) }}</span>
                                @else
                                    <span class="dash-down">↓ L. {{ number_format(abs($c['mes_actual'] - $c['mes_anterior']),2) }}</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($c['variacion'] !== null)
                                @if($c['variacion'] >= 0)
                                    <span class="dash-up">+{{ $c['variacion'] }}%</span>
                                @else
                                    <span class="dash-down">{{ $c['variacion'] }}%</span>
                                @endif
                            @else
                                <span style="color:var(--dash-muted);">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center; color:var(--dash-muted); padding:24px;">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
