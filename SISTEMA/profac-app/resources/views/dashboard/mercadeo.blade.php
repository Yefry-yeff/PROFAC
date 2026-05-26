{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — GERENTE DE MARCA / MERCADEO
     Rol: 9 — Enfoque en inventario
═══════════════════════════════════════════════════════════════ --}}

@include('dashboard.inventario-kpis', ['kpisInventario' => $kpisInventario])

{{-- ── Grid: Distribución stock + Cobertura imágenes ── --}}
<div class="dash-grid-2" style="margin-bottom:20px;">

    <div class="dash-card">
        <div class="dash-card-header orange">
            <h5><i class="fa fa-bar-chart"></i> Estado del Stock — Top Críticos</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-mkt-stock" style="min-height:270px;"></div>
        </div>
    </div>

    <div class="dash-card">
        <div class="dash-card-header blue">
            <h5><i class="fa fa-pie-chart"></i> Cobertura de Imágenes</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-mkt-imagenes" style="min-height:270px;"></div>
        </div>
    </div>

</div>

<script>
function initMktCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var inv  = JSON.parse(span.dataset.inventario || '{}');
    if (Array.isArray(inv)) inv = {};
    var dark = window.DASH_DARK;
    var base = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    const elS = document.querySelector('#chart-mkt-stock');
    if (elS && inv.top_bajos_stock && inv.top_bajos_stock.length) {
        elS.innerHTML = '';
        const nombres = inv.top_bajos_stock.map(p => p.nombre.length > 25 ? p.nombre.substring(0,25)+'\u2026' : p.nombre);
        const stocks  = inv.top_bajos_stock.map(p => p.stock);
        new ApexCharts(elS, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'bar', height:270 }),
            series: [{ name: 'Unidades', data: stocks }],
            xaxis:  { categories: nombres, labels: { style: { fontSize: '10px' } } },
            yaxis:  { title: { text: 'Unidades' } },
            colors: ['#e74c3c'],
            plotOptions: { bar: { borderRadius: 3, columnWidth: '55%' } },
            dataLabels: { enabled: true, style: { fontSize: '10px' } },
        })).render();
    }

    const elI = document.querySelector('#chart-mkt-imagenes');
    if (elI) {
        elI.innerHTML = '';
        const total  = inv.total_productos || 0;
        const sinImg = inv.sin_imagen      || 0;
        const conImg = Math.max(0, total - sinImg);
        new ApexCharts(elI, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'donut', height:270 }),
            series: [conImg, sinImg],
            labels: ['Con imagen', 'Sin imagen'],
            colors: ['#1ab394', '#f39c12'],
            legend: { position: 'bottom' },
            dataLabels: { enabled: true, formatter: v => v.toFixed(1) + '%' },
            tooltip: { y: { formatter: v => v + ' productos' } },
        })).render();
    }
}
(function() {
    function run() { if (typeof ApexCharts !== 'undefined') initMktCharts(); }
    if (window.DASH_LOADED) { run(); } else { document.addEventListener('livewire:load', run); }
    if (!window._mktBound) { window._mktBound = true; document.addEventListener('livewire:update', run); }
})();
</script>
