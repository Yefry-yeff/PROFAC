{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — GERENCIA / ADMINISTRACIÓN
     Roles: 1 (Administrador), 19 (Gerente)
═══════════════════════════════════════════════════════════════ --}}

{{-- ── KPIs ── --}}
<div class="dash-kpi-grid">
    <div class="dash-kpi orange">
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
    </div>
    <div class="dash-kpi">
        <div class="dash-kpi-icon"><i class="fa fa-file-text-o"></i></div>
        <div class="dash-kpi-label">Facturas</div>
        <div class="dash-kpi-value">{{ number_format($kpis['num_facturas'] ?? 0) }}</div>
        <div class="dash-kpi-sub">emitidas en el período</div>
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
        <div class="dash-kpi-sub">primera compra</div>
    </div>
    <div class="dash-kpi">
        <div class="dash-kpi-icon"><i class="fa fa-line-chart"></i></div>
        <div class="dash-kpi-label">Ticket promedio</div>
        <div class="dash-kpi-value">L. {{ number_format($kpis['ticket_prom'] ?? 0, 2) }}</div>
        <div class="dash-kpi-sub">por factura</div>
    </div>
    <div class="dash-kpi red">
        <div class="dash-kpi-icon"><i class="fa fa-warning"></i></div>
        <div class="dash-kpi-label">Requieren atención</div>
        <div class="dash-kpi-value">{{ count(array_filter($topClientes, fn($c) => $c['requiere_atencion'])) }}</div>
        <div class="dash-kpi-sub">clientes en riesgo</div>
    </div>
</div>

{{-- ── Grid principal: Tendencia 12 m + Productividad vendedores ── --}}
<div class="dash-grid-2" style="margin-bottom:20px;">

    {{-- Tendencia 12 meses --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <h5><i class="fa fa-area-chart"></i> Tendencia — Últimos 12 meses</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-ger-tendencia" style="min-height:270px;"></div>
        </div>
    </div>

    {{-- Participación de mercado (pie) --}}
    <div class="dash-card">
        <div class="dash-card-header orange">
            <h5><i class="fa fa-pie-chart"></i> Participación por Vendedor</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-ger-participacion" style="min-height:270px;"></div>
        </div>
    </div>

</div>

{{-- ── Productividad vendedores ── --}}
<div class="dash-card" style="margin-bottom:20px;">
    <div class="dash-card-header blue">
        <h5><i class="fa fa-bar-chart"></i> Productividad por Vendedor — {{ $periodoLabel }}</h5>
    </div>
    <div class="dash-card-body">
        <div id="chart-ger-productividad" style="min-height:260px; margin-bottom:16px;"></div>
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

{{-- ── Cartera & Cobros ── --}}
<div class="dash-card" style="margin-top:20px;">
    <div class="dash-card-header red">
        <h5><i class="fa fa-bank"></i> Cartera &amp; Cobros — Estado Actual</h5>
    </div>
    <div class="dash-card-body">
        <div class="dash-kpi-grid" style="margin-bottom:20px;">
            <div class="dash-kpi red">
                <div class="dash-kpi-icon"><i class="fa fa-money"></i></div>
                <div class="dash-kpi-label">Cartera activa</div>
                <div class="dash-kpi-value">L. {{ number_format($kpisCobros['saldo_pendiente'] ?? 0, 2) }}</div>
                <div class="dash-kpi-sub">{{ number_format($kpisCobros['facturas_pendientes'] ?? 0) }} facturas pendientes</div>
            </div>
            <div class="dash-kpi red">
                <div class="dash-kpi-icon"><i class="fa fa-exclamation-triangle"></i></div>
                <div class="dash-kpi-label">Facturas vencidas</div>
                <div class="dash-kpi-value">{{ number_format($kpisCobros['facturas_vencidas'] ?? 0) }}</div>
                <div class="dash-kpi-sub">{{ number_format($kpisCobros['clientes_morosos'] ?? 0) }} clientes morosos</div>
            </div>
            <div class="dash-kpi green">
                <div class="dash-kpi-icon"><i class="fa fa-check-circle"></i></div>
                <div class="dash-kpi-label">Recuperado</div>
                <div class="dash-kpi-value">L. {{ number_format($kpisCobros['total_recuperado'] ?? 0, 2) }}</div>
                <div class="dash-kpi-sub">
                    @if(isset($kpisCobros['var_recuperado']) && $kpisCobros['var_recuperado'] !== null)
                        @if($kpisCobros['var_recuperado'] >= 0)
                            <span class="dash-up">↑ +{{ $kpisCobros['var_recuperado'] }}%</span>
                        @else
                            <span class="dash-down">↓ {{ $kpisCobros['var_recuperado'] }}%</span>
                        @endif
                        vs mes anterior
                    @else
                        cobrado en el período
                    @endif
                </div>
            </div>
        </div>
        <div class="dash-grid-2">
            <div>
                <div style="font-size:12px;font-weight:700;color:var(--dash-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">
                    <i class="fa fa-line-chart"></i> Tendencia Abonos 6 meses
                </div>
                <div id="chart-ger-tendencia-cobros" style="min-height:200px;"></div>
            </div>
            <div>
                <div style="font-size:12px;font-weight:700;color:var(--dash-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">
                    <i class="fa fa-pie-chart"></i> Antigüedad de Saldos
                </div>
                <div id="chart-ger-antiguedad" style="min-height:200px;"></div>
            </div>
        </div>
    </div>
</div>

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

<script>
function initGerenciaCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var tendencia     = JSON.parse(span.dataset.tendencia     || '{}');
    var productividad = JSON.parse(span.dataset.productividad || '[]');
    var participacion = JSON.parse(span.dataset.participacion || '{}');
    var kc            = JSON.parse(span.dataset.cobros        || '{}');
    if (Array.isArray(kc))           kc = {};
    if (Array.isArray(tendencia))    tendencia = {};
    if (!Array.isArray(productividad)) productividad = [];
    var dark = window.DASH_DARK;
    var base = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    // ── Tendencia 12 meses ──
    const elTend = document.querySelector('#chart-ger-tendencia');
    if (elTend) {
        elTend.innerHTML = '';
        new ApexCharts(elTend, Object.assign({}, base, {
            chart: Object.assign({}, (base.chart||{}), { type:'area', height:270 }),
            series: [
                { name:'Ventas (L.)', data: tendencia.montos || [] },
                { name:'Facturas',    data: tendencia.facturas || [] },
            ],
            xaxis:  { categories: tendencia.labels || [] },
            yaxis:  [
                { title: { text: 'L.' }, labels: { formatter: v => lpsK(v) } },
                { opposite: true, title: { text: 'Facturas' } },
            ],
            colors: ['#1ab394', '#f39c12'],
            stroke: { curve:'smooth', width:[2.5,2] },
            fill:   { type:['gradient','solid'], gradient:{ opacityFrom:.35, opacityTo:.05 }, opacity:[1,0] },
            dataLabels: { enabled: false },
        })).render();
    }

    // ── Productividad barras ──
    const elProd = document.querySelector('#chart-ger-productividad');
    if (elProd && productividad.length) {
        elProd.innerHTML = '';
        new ApexCharts(elProd, Object.assign({}, base, {
            chart: Object.assign({}, (base.chart||{}), { type:'bar', height:260 }),
            series: [{ name:'Ventas (L.)', data: productividad.map(p => p.venta_total) }],
            xaxis:  { categories: productividad.map(p => p.vendedor) },
            yaxis:  { labels: { formatter: v => lpsK(v) } },
            colors: ['#1ab394'],
            plotOptions: { bar: { borderRadius:6, horizontal:false } },
            dataLabels: { enabled: false },
        })).render();
    }

    // ── Participación pie ──
    const elPart = document.querySelector('#chart-ger-participacion');
    if (elPart && participacion.series && participacion.series.length) {
        elPart.innerHTML = '';
        new ApexCharts(elPart, Object.assign({}, base, {
            chart: Object.assign({}, (base.chart||{}), { type:'donut', height:270 }),
            series: participacion.series || [],
            labels: participacion.labels || [],
            colors: ['#1ab394','#f39c12','#e74c3c','#2980b9','#8e44ad','#27ae60','#e67e22','#c0392b'],
            legend: { position:'bottom' },
            tooltip: { y: { formatter: v => lps(v) } },
            dataLabels: { enabled: true, formatter: (v) => v.toFixed(1) + '%' },
        })).render();
    }

    // ── Tendencia cobros ──
    const elTC = document.querySelector('#chart-ger-tendencia-cobros');
    if (elTC && kc && kc.tendencia_cobros) {
        elTC.innerHTML = '';
        new ApexCharts(elTC, Object.assign({}, base, {
            chart: Object.assign({}, (base.chart||{}), { type:'area', height:200, toolbar:{show:false} }),
            series: [{ name:'Abonos (L.)', data: kc.tendencia_cobros.montos || [] }],
            xaxis:  { categories: kc.tendencia_cobros.labels || [] },
            yaxis:  { labels: { formatter: v => lpsK(v) } },
            colors: ['#27ae60'],
            stroke: { curve:'smooth', width:2 },
            fill:   { type:'gradient', gradient:{ opacityFrom:.3, opacityTo:.02 } },
            dataLabels: { enabled: false },
        })).render();
    }

    // ── Antigüedad ──
    const elAnt = document.querySelector('#chart-ger-antiguedad');
    if (elAnt && kc && kc.antiguedad) {
        elAnt.innerHTML = '';
        new ApexCharts(elAnt, Object.assign({}, base, {
            chart: Object.assign({}, (base.chart||{}), { type:'donut', height:200, toolbar:{show:false} }),
            series: [ kc.antiguedad['0_30']||0, kc.antiguedad['31_60']||0, kc.antiguedad['61_90']||0, kc.antiguedad['90_mas']||0 ],
            labels: ['0-30 d','31-60 d','61-90 d','> 90 d'],
            colors: ['#27ae60','#f39c12','#e67e22','#e74c3c'],
            legend: { position:'bottom', fontSize:'11px' },
            dataLabels: { enabled: false },
        })).render();
    }
}
(function() {
    function run() { if (typeof ApexCharts !== 'undefined') initGerenciaCharts(); }
    if (window.DASH_LOADED) { run(); } else { document.addEventListener('livewire:load', run); }
    if (!window._gerenciaBound) { window._gerenciaBound = true; document.addEventListener('livewire:update', run); }
})();
</script>
