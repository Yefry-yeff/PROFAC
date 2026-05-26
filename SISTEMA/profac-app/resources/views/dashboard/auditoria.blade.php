{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — AUDITORÍA
     Rol: 18 — Inventario + Cartera de cobros (sin ventas totales)
═══════════════════════════════════════════════════════════════ --}}

@include('dashboard.inventario-kpis', ['kpisInventario' => $kpisInventario])

{{-- ── Separador ── --}}
<div style="margin:24px 0 12px; display:flex; align-items:center; gap:12px;">
    <span style="font-weight:700; font-size:15px; color:var(--dash-text);">
        <i class="fa fa-money" style="color:#e74c3c;"></i> Cartera de Cobros
    </span>
    <div style="flex:1; height:1px; background:var(--dash-border);"></div>
</div>

{{-- ── KPIs Cobros ── --}}
<div class="dash-kpi-grid" style="margin-bottom:20px;">
    <div class="dash-kpi red">
        <div class="dash-kpi-icon"><i class="fa fa-clock-o"></i></div>
        <div class="dash-kpi-label">Cartera activa</div>
        <div class="dash-kpi-value">L. {{ number_format($kpisCobros['saldo_pendiente'] ?? 0, 2) }}</div>
        <div class="dash-kpi-sub">saldo pendiente</div>
    </div>
    <div class="dash-kpi orange">
        <div class="dash-kpi-icon"><i class="fa fa-exclamation-triangle"></i></div>
        <div class="dash-kpi-label">Facturas vencidas</div>
        <div class="dash-kpi-value">{{ number_format($kpisCobros['facturas_vencidas'] ?? 0) }}</div>
        <div class="dash-kpi-sub">documentos</div>
    </div>
    <div class="dash-kpi green">
        <div class="dash-kpi-icon"><i class="fa fa-check-circle"></i></div>
        <div class="dash-kpi-label">Recuperado</div>
        <div class="dash-kpi-value">L. {{ number_format($kpisCobros['total_recuperado'] ?? 0, 2) }}</div>
        <div class="dash-kpi-sub">en el período</div>
    </div>
    <div class="dash-kpi blue">
        <div class="dash-kpi-icon"><i class="fa fa-user-times"></i></div>
        <div class="dash-kpi-label">Clientes morosos</div>
        <div class="dash-kpi-value">{{ number_format($kpisCobros['clientes_morosos'] ?? 0) }}</div>
        <div class="dash-kpi-sub">con saldo vencido</div>
    </div>
</div>

{{-- ── Grid: Antigüedad + Top deudores ── --}}
<div class="dash-grid-2">

    <div class="dash-card">
        <div class="dash-card-header red">
            <h5><i class="fa fa-pie-chart"></i> Antigüedad de Cartera</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-aud-antiguedad" style="min-height:260px;"></div>
        </div>
    </div>

    <div class="dash-card">
        <div class="dash-card-header orange">
            <h5><i class="fa fa-list-ol"></i> Top Deudores</h5>
        </div>
        <div class="dash-card-body" style="padding:0;">
            <div style="overflow-x:auto; max-height:280px; overflow-y:auto;">
                <table class="dash-table">
                    <thead>
                        <tr><th>#</th><th>Cliente</th><th>Saldo</th></tr>
                    </thead>
                    <tbody>
                        @forelse($kpisCobros['top_deudores'] ?? [] as $i => $d)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $d['cliente'] }}</td>
                            <td>L. {{ number_format($d['saldo'],2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align:center; color:var(--dash-muted); padding:20px;">Sin datos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
function initAuditoriaCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var cobros = JSON.parse(span.dataset.cobros    || '{}');
    if (Array.isArray(cobros)) cobros = {};
    var dark   = window.DASH_DARK;
    var base   = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    // Antigüedad donut
    const elA = document.querySelector('#chart-aud-antiguedad');
    if (elA && cobros.antiguedad) {
        elA.innerHTML = '';
        const ag = cobros.antiguedad;
        new ApexCharts(elA, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'donut', height:260 }),
            series: [ag.al_dia||0, ag['0_30']||0, ag['31_60']||0, ag['61_90']||0, ag['90_mas']||0],
            labels: ['Al día','0-30 días','31-60 días','61-90 días','+90 días'],
            colors: ['#1ab394','#f39c12','#e67e22','#e74c3c','#8e44ad'],
            legend: { position:'bottom' },
            tooltip: { y: { formatter: v => lps(v) } },
            dataLabels: { enabled: true, formatter: v => v.toFixed(1)+'%' },
        })).render();
    }
}
(function() {
    function run() { if (typeof ApexCharts !== 'undefined') initAuditoriaCharts(); }
    if (window.DASH_LOADED) { run(); } else { document.addEventListener('livewire:load', run); }
    if (!window._auditoriaBound) { window._auditoriaBound = true; document.addEventListener('livewire:update', run); }
})();
</script>
