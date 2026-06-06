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
        <div class="dash-kpi-sub">en el período</div>
    </div>
    <div class="dash-kpi blue">
        <div class="dash-kpi-icon"><i class="fa fa-file-text-o"></i></div>
        <div class="dash-kpi-label">Documentos</div>
        <div class="dash-kpi-value">{{ number_format($kpis['num_facturas'] ?? 0) }}</div>
        <div class="dash-kpi-sub">facturas procesadas</div>
    </div>
    <div class="dash-kpi">
        <div class="dash-kpi-icon"><i class="fa fa-user-plus"></i></div>
        <div class="dash-kpi-label">Clientes nuevos</div>
        <div class="dash-kpi-value">{{ number_format($kpis['clientes_nuevos'] ?? 0) }}</div>
        <div class="dash-kpi-sub">primera entrega</div>
    </div>
</div>

{{-- ── Tendencia mensual ── --}}
<div class="dash-card">
    <div class="dash-card-header">
        <h5><i class="fa fa-bar-chart"></i> Entregas — Últimos 12 meses</h5>
    </div>
    <div class="dash-card-body">
        <div id="chart-log-tendencia" style="min-height:260px;"></div>
    </div>
</div>

<script>
function initLogisticaCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var tendencia = JSON.parse(span.dataset.tendencia || '{}');
    if (Array.isArray(tendencia)) tendencia = {};
    var dark = window.DASH_DARK;
    var base = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    const el = document.querySelector('#chart-log-tendencia');
    if (el) {
        el.innerHTML = '';
        new ApexCharts(el, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'bar', height:260 }),
            series: [
                { name:'Facturas', data: tendencia.facturas || [] },
                { name:'Clientes', data: tendencia.clientes || [] },
            ],
            xaxis:  { categories: tendencia.labels || [] },
            colors: ['#1ab394','#2980b9'],
            plotOptions: { bar: { borderRadius:4, columnWidth:'60%' } },
            dataLabels: { enabled: false },
        })).render();
    }
}
(function() {
    function run() { if (typeof ApexCharts !== 'undefined') initLogisticaCharts(); }
    if (window.DASH_LOADED) { run(); } else { document.addEventListener('livewire:load', run); }
    if (!window._logisticaBound) { window._logisticaBound = true; document.addEventListener('livewire:update', run); }
})();
</script>
