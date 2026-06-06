{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — ASESOR COMERCIAL / TELE-ASESOR
     Roles: 2 (Asesor Comercial), 15 (Tele-Asesor Comercial)
═══════════════════════════════════════════════════════════════ --}}

{{-- ── KPIs ── --}}
<div class="dash-kpi-grid">
    <div class="dash-kpi orange">
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
    </div>
    <div class="dash-kpi">
        <div class="dash-kpi-icon"><i class="fa fa-file-text-o"></i></div>
        <div class="dash-kpi-label">Facturas emitidas</div>
        <div class="dash-kpi-value">{{ number_format($kpis['num_facturas'] ?? 0) }}</div>
        <div class="dash-kpi-sub">documentos en el período</div>
    </div>
    <div class="dash-kpi green">
        <div class="dash-kpi-icon"><i class="fa fa-users"></i></div>
        <div class="dash-kpi-label">Clientes activos</div>
        <div class="dash-kpi-value">{{ number_format($kpis['clientes_act'] ?? 0) }}</div>
        <div class="dash-kpi-sub">compraron en el período</div>
    </div>
    <div class="dash-kpi blue">
        <div class="dash-kpi-icon"><i class="fa fa-user-plus"></i></div>
        <div class="dash-kpi-label">Clientes nuevos</div>
        <div class="dash-kpi-value">{{ number_format($kpis['clientes_nuevos'] ?? 0) }}</div>
        <div class="dash-kpi-sub">primera compra este período</div>
    </div>
    <div class="dash-kpi">
        <div class="dash-kpi-icon"><i class="fa fa-line-chart"></i></div>
        <div class="dash-kpi-label">Ticket promedio</div>
        <div class="dash-kpi-value">L. {{ number_format($kpis['ticket_prom'] ?? 0, 2) }}</div>
        <div class="dash-kpi-sub">por factura</div>
    </div>
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
            <div id="chart-tendencia-vendedor" style="min-height:260px;"></div>
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

<script>
function initVendedorCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var tendencia = JSON.parse(span.dataset.tendencia || '{}');
    if (Array.isArray(tendencia)) tendencia = {};
    var dark = window.DASH_DARK;
    var base = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    // Tendencia líneas
    const elT = document.querySelector('#chart-tendencia-vendedor');
    if (elT) {
        elT.innerHTML = '';
        new ApexCharts(elT, Object.assign({}, baseOpts, {
            chart: Object.assign({}, (baseOpts.chart||{}), { type:'area', height:260 }),
            series: [{ name:'Ventas (L.)', data: tendencia.montos || [] }],
            xaxis:  { categories: tendencia.labels || [] },
            yaxis:  { labels: { formatter: v => lpsK(v) } },
            colors: ['#1ab394'],
            fill:   { type:'gradient', gradient:{ shadeIntensity:1, opacityFrom:.4, opacityTo:.05 } },
            stroke: { curve:'smooth', width:2.5 },
            dataLabels: { enabled: false },
        })).render();
    }
}
(function() {
    function run() { if (typeof ApexCharts !== 'undefined') initVendedorCharts(); }
    if (window.DASH_LOADED) { run(); } else { document.addEventListener('livewire:load', run); }
    if (!window._vendedorBound) { window._vendedorBound = true; document.addEventListener('livewire:update', run); }
})();
</script>
