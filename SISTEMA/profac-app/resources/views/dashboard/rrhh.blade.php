{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — RRHH
     Rol: 8 (RRHH)
     Enfoque: Productividad del equipo de ventas
═══════════════════════════════════════════════════════════════ --}}

{{-- ── KPIs generales del equipo ── --}}
<div class="dash-kpi-grid">
    <div class="dash-kpi orange">
        <div class="dash-kpi-icon"><i class="fa fa-file-text-o"></i></div>
        <div class="dash-kpi-label">Facturas del equipo</div>
        <div class="dash-kpi-value">{{ number_format($kpis['num_facturas'] ?? 0) }}</div>
        <div class="dash-kpi-sub">emitidas en el período</div>
    </div>
    <div class="dash-kpi blue">
        <div class="dash-kpi-icon"><i class="fa fa-users"></i></div>
        <div class="dash-kpi-label">Vendedores activos</div>
        <div class="dash-kpi-value">{{ count($productividad) }}</div>
        <div class="dash-kpi-sub">con ventas en el período</div>
    </div>
    <div class="dash-kpi green">
        <div class="dash-kpi-icon"><i class="fa fa-file-text-o"></i></div>
        <div class="dash-kpi-label">Total facturas</div>
        <div class="dash-kpi-value">{{ number_format($kpis['num_facturas'] ?? 0) }}</div>
        <div class="dash-kpi-sub">emitidas en el período</div>
    </div>
    <div class="dash-kpi">
        <div class="dash-kpi-icon"><i class="fa fa-line-chart"></i></div>
        <div class="dash-kpi-label">Ticket promedio</div>
        <div class="dash-kpi-value">L. {{ number_format($kpis['ticket_prom'] ?? 0, 2) }}</div>
        <div class="dash-kpi-sub">por factura</div>
    </div>
</div>

{{-- ── Productividad por vendedor ── --}}
<div class="dash-card" style="margin-bottom:20px;">
    <div class="dash-card-header blue">
        <h5><i class="fa fa-bar-chart"></i> Productividad del Equipo — {{ $periodoLabel }}</h5>
    </div>
    <div class="dash-card-body">
        <div id="chart-rrhh-productividad" style="min-height:280px; margin-bottom:20px;"></div>
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

<script>
function initRrhhCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var productividad = JSON.parse(span.dataset.productividad || '[]');
    if (!Array.isArray(productividad)) productividad = [];
    var dark = window.DASH_DARK;
    var base = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    const el = document.querySelector('#chart-rrhh-productividad');
    if (el && productividad.length) {
        el.innerHTML = '';
        new ApexCharts(el, Object.assign({}, base, {
            chart: Object.assign({}, (base.chart||{}), { type:'bar', height:280 }),
            series: [
                { name:'Ventas (L.)',  data: productividad.map(p => p.venta_total) },
                { name:'Facturas',     data: productividad.map(p => p.num_facturas) },
                { name:'Clientes',     data: productividad.map(p => p.clientes_atendidos) },
            ],
            xaxis:  { categories: productividad.map(p => p.vendedor) },
            yaxis:  [
                { title:{ text:'L.' }, labels:{ formatter: v => lpsK(v) } },
                { opposite:true, title:{ text:'Cant.' } },
                { show: false },
            ],
            colors: ['#1ab394','#f39c12','#2980b9'],
            plotOptions: { bar: { borderRadius:4, columnWidth:'70%' } },
            dataLabels: { enabled: false },
        })).render();
    }
}
(function() {
    function run() { if (typeof ApexCharts !== 'undefined') initRrhhCharts(); }
    if (window.DASH_LOADED) { run(); } else { document.addEventListener('livewire:load', run); }
    if (!window._rrhhBound) { window._rrhhBound = true; document.addEventListener('livewire:update', run); }
})();
</script>
