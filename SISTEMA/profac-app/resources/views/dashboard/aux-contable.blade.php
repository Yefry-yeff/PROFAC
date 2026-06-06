{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — AUX CONTABLE
     Rol: 6 (Aux Contable)
     KPIs generales + info básica de cobros
═══════════════════════════════════════════════════════════════ --}}

<div class="dash-kpi-grid">
    <div class="dash-kpi orange">
        <div class="dash-kpi-icon"><i class="fa fa-file-text-o"></i></div>
        <div class="dash-kpi-label">Facturas</div>
        <div class="dash-kpi-value">{{ number_format($kpis['num_facturas'] ?? 0) }}</div>
        <div class="dash-kpi-sub">emitidas en el período</div>
    </div>
    <div class="dash-kpi">
        <div class="dash-kpi-icon"><i class="fa fa-file-text-o"></i></div>
        <div class="dash-kpi-label">Facturas</div>
        <div class="dash-kpi-value">{{ number_format($kpis['num_facturas'] ?? 0) }}</div>
        <div class="dash-kpi-sub">emitidas</div>
    </div>
    <div class="dash-kpi red">
        <div class="dash-kpi-icon"><i class="fa fa-clock-o"></i></div>
        <div class="dash-kpi-label">Saldo pendiente</div>
        <div class="dash-kpi-value">L. {{ number_format($kpisCobros['saldo_pendiente'] ?? 0, 2) }}</div>
        <div class="dash-kpi-sub">por cobrar</div>
    </div>
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
</div>

<div class="dash-grid-2">
    <div class="dash-card">
        <div class="dash-card-header">
            <h5><i class="fa fa-area-chart"></i> Tendencia — 12 meses</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-auxcnt-tendencia" style="min-height:240px;"></div>
        </div>
    </div>
    <div class="dash-card">
        <div class="dash-card-header red">
            <h5><i class="fa fa-pie-chart"></i> Antigüedad de Saldos</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-auxcnt-antiguedad" style="min-height:240px;"></div>
        </div>
    </div>
</div>

<script>
function initAuxCntCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var tendencia   = JSON.parse(span.dataset.tendencia || '{}');
    var kpisCobros  = JSON.parse(span.dataset.cobros    || '{}');
    if (Array.isArray(tendencia))  tendencia = {};
    if (Array.isArray(kpisCobros)) kpisCobros = {};
    var dark = window.DASH_DARK;
    var base = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    const elT = document.querySelector('#chart-auxcnt-tendencia');
    if (elT) {
        elT.innerHTML = '';
        new ApexCharts(elT, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'area', height:240 }),
            series: [{ name:'Ventas (L.)', data: tendencia.montos || [] }],
            xaxis:  { categories: tendencia.labels || [] },
            yaxis:  { labels: { formatter: v => lpsK(v) } },
            colors: ['#1ab394'],
            stroke: { curve:'smooth', width:2.5 },
            fill:   { type:'gradient', gradient:{ opacityFrom:.35, opacityTo:.05 } },
            dataLabels: { enabled: false },
        })).render();
    }

    const elA = document.querySelector('#chart-auxcnt-antiguedad');
    if (elA && kpisCobros.antiguedad) {
        elA.innerHTML = '';
        new ApexCharts(elA, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'donut', height:240 }),
            series: [
                kpisCobros.antiguedad['0_30']   || 0,
                kpisCobros.antiguedad['31_60']  || 0,
                kpisCobros.antiguedad['61_90']  || 0,
                kpisCobros.antiguedad['90_mas'] || 0,
            ],
            labels: ['0-30 días','31-60 días','61-90 días','> 90 días'],
            colors: ['#27ae60','#f39c12','#e67e22','#e74c3c'],
            legend: { position:'bottom' },
        })).render();
    }
}
(function() {
    function run() { if (typeof ApexCharts !== 'undefined') initAuxCntCharts(); }
    if (window.DASH_LOADED) { run(); } else { document.addEventListener('livewire:load', run); }
    if (!window._auxCntBound) { window._auxCntBound = true; document.addEventListener('livewire:update', run); }
})();
</script>
