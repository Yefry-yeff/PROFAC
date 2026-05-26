{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — COBROS & CARTERA
     Rol: 4 (Gestor de Cobros)
═══════════════════════════════════════════════════════════════ --}}

{{-- ── KPIs Cartera & Cobros ── --}}
<div class="dash-kpi-grid">
    <div class="dash-kpi red">
        <div class="dash-kpi-icon"><i class="fa fa-money"></i></div>
        <div class="dash-kpi-label">Cartera activa</div>
        <div class="dash-kpi-value">L. {{ number_format($kpisCobros['saldo_pendiente'] ?? 0, 2) }}</div>
        <div class="dash-kpi-sub">total por cobrar</div>
    </div>
    <div class="dash-kpi red">
        <div class="dash-kpi-icon"><i class="fa fa-exclamation-triangle"></i></div>
        <div class="dash-kpi-label">Facturas vencidas</div>
        <div class="dash-kpi-value">{{ number_format($kpisCobros['facturas_vencidas'] ?? 0) }}</div>
        <div class="dash-kpi-sub">de {{ number_format($kpisCobros['facturas_pendientes'] ?? 0) }} pendientes</div>
    </div>
    <div class="dash-kpi green">
        <div class="dash-kpi-icon"><i class="fa fa-check-circle"></i></div>
        <div class="dash-kpi-label">Recuperado</div>
        <div class="dash-kpi-value">L. {{ number_format($kpisCobros['total_recuperado'] ?? 0, 2) }}</div>
        <div class="dash-kpi-sub">
            @if(isset($kpisCobros['var_recuperado']) && $kpisCobros['var_recuperado'] !== null)
                @if($kpisCobros['var_recuperado'] >= 0)
                    <span class="dash-up">&#8593; +{{ $kpisCobros['var_recuperado'] }}%</span>
                @else
                    <span class="dash-down">&#8595; {{ $kpisCobros['var_recuperado'] }}%</span>
                @endif
                vs mes anterior
            @else
                cobrado en el periodo
            @endif
        </div>
    </div>
    <div class="dash-kpi orange">
        <div class="dash-kpi-icon"><i class="fa fa-user-times"></i></div>
        <div class="dash-kpi-label">Clientes morosos</div>
        <div class="dash-kpi-value">{{ number_format($kpisCobros['clientes_morosos'] ?? 0) }}</div>
        <div class="dash-kpi-sub">con saldo vencido</div>
    </div>
</div>

{{-- ── Alertas: proximas a vencer ── --}}
@if(!empty($kpisCobros['proximas_vencer']) && count($kpisCobros['proximas_vencer']))
<div class="dash-card" style="margin-bottom:20px;">
    <div class="dash-card-header orange">
        <h5><i class="fa fa-bell"></i> Facturas por Vencer — Proximos 7 dias ({{ count($kpisCobros['proximas_vencer']) }})</h5>
    </div>
    <div class="dash-card-body" style="padding:0;">
        <div style="overflow-x:auto;">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>CAI / Factura</th>
                        <th>Cliente</th>
                        <th>Saldo</th>
                        <th>Vence</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kpisCobros['proximas_vencer'] as $f)
                    <tr>
                        <td><span class="dash-badge dash-badge-warning">{{ $f['numero'] ?? '' }}</span></td>
                        <td>{{ $f['cliente'] ?? '' }}</td>
                        <td><strong>L. {{ number_format($f['monto'] ?? 0, 2) }}</strong></td>
                        <td>{{ $f['fecha_vencimiento'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ── Grid: Abonos diarios + Tendencia 6m ── --}}
<div class="dash-grid-2" style="margin-bottom:20px;">
    <div class="dash-card">
        <div class="dash-card-header" style="background:linear-gradient(135deg,#27ae60,#1e8449);">
            <h5><i class="fa fa-bar-chart"></i> Abonos Diarios — {{ $periodoLabel }}</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-cobros-diarios" style="min-height:240px;"></div>
        </div>
    </div>
    <div class="dash-card">
        <div class="dash-card-header" style="background:linear-gradient(135deg,#2980b9,#1a6fa5);">
            <h5><i class="fa fa-line-chart"></i> Tendencia de Abonos — 6 meses</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-cobros-tendencia" style="min-height:240px;"></div>
        </div>
    </div>
</div>

{{-- ── Grid: Antiguedad + Top Deudores ── --}}
<div class="dash-grid-2" style="margin-bottom:20px;">
    <div class="dash-card">
        <div class="dash-card-header red">
            <h5><i class="fa fa-pie-chart"></i> Antiguedad de Saldos (L.)</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-cobros-antiguedad" style="min-height:260px;"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;">
                @php $ant = $kpisCobros['antiguedad'] ?? []; @endphp
                @foreach([
                    ['Al dia',      $ant['al_dia']  ?? 0, '#27ae60'],
                    ['0-30 dias',   $ant['0_30']    ?? 0, '#2ecc71'],
                    ['31-60 dias',  $ant['31_60']   ?? 0, '#f39c12'],
                    ['61-90 dias',  $ant['61_90']   ?? 0, '#e67e22'],
                    ['> 90 dias',   $ant['90_mas']  ?? 0, '#e74c3c'],
                ] as [$label, $val, $color])
                <div style="display:flex;align-items:center;gap:8px;font-size:12px;">
                    <div style="width:10px;height:10px;border-radius:50%;background:{{ $color }};flex-shrink:0;"></div>
                    <div>
                        <div style="font-weight:600;color:var(--dash-text);">{{ $label }}</div>
                        <div style="color:var(--dash-muted);">L. {{ number_format($val, 2) }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="dash-card">
        <div class="dash-card-header red">
            <h5><i class="fa fa-list-ol"></i> Top Deudores</h5>
        </div>
        <div class="dash-card-body" style="padding:0;">
            <div style="overflow-x:auto;max-height:380px;overflow-y:auto;">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Saldo</th>
                            <th>Facturas</th>
                            <th>Dias</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($kpisCobros['top_deudores'] ?? []) as $i => $d)
                        <tr>
                            <td><strong>{{ $i+1 }}</strong></td>
                            <td>{{ $d['cliente'] ?? '' }}</td>
                            <td><span class="dash-down">L. {{ number_format($d['saldo'] ?? 0, 2) }}</span></td>
                            <td>{{ $d['facturas_vencidas'] ?? 0 }}</td>
                            <td>
                                @if(($d['dias_vencido'] ?? 0) > 90)
                                    <span class="dash-badge dash-badge-danger">{{ $d['dias_vencido'] }} d</span>
                                @elseif(($d['dias_vencido'] ?? 0) > 60)
                                    <span class="dash-badge dash-badge-warning">{{ $d['dias_vencido'] }} d</span>
                                @else
                                    {{ $d['dias_vencido'] ?? 0 }} d
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--dash-muted);padding:24px;">Sin deudores</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ── Top 10 Facturas mas altas por cobrar ── --}}
<div class="dash-card" style="margin-bottom:20px;">
    <div class="dash-card-header" style="background:linear-gradient(135deg,#8e44ad,#6c3483);">
        <h5><i class="fa fa-sort-amount-desc"></i> Top 10 Facturas mas Altas por Cobrar <small style="font-weight:400;opacity:.8;">— {{ $periodoLabel }}</small></h5>
    </div>
    <div class="dash-card-body" style="padding:0;">
        <div style="overflow-x:auto;">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>CAI / N. Factura</th>
                        <th>Cliente</th>
                        <th>Total Factura</th>
                        <th>Saldo Pendiente</th>
                        <th>Fecha Emision</th>
                        <th>Fecha Venc.</th>
                        <th>Dias Vencido</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($kpisCobros['top_facturas_cobrar'] ?? []) as $i => $tf)
                    <tr>
                        <td><strong>{{ $i+1 }}</strong></td>
                        <td>
                            @if($tf['numero'] ?? null)
                                <span class="dash-badge dash-badge-info" style="background:#8e44ad;color:#fff;">{{ $tf['numero'] }}</span>
                            @else
                                <span style="color:var(--dash-muted);">—</span>
                            @endif
                        </td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $tf['cliente'] ?? '' }}">
                            {{ $tf['cliente'] ?? '—' }}
                        </td>
                        <td>L. {{ number_format($tf['total_factura'] ?? 0, 2) }}</td>
                        <td><strong class="dash-down">L. {{ number_format($tf['saldo'] ?? 0, 2) }}</strong></td>
                        <td>{{ $tf['fecha_emision'] ?? '—' }}</td>
                        <td>
                            @if($tf['fecha_vencimiento'] ?? null)
                                {{ $tf['fecha_vencimiento'] }}
                            @else
                                <span style="color:var(--dash-muted);">—</span>
                            @endif
                        </td>
                        <td>
                            @php $dv = $tf['dias_vencido'] ?? 0; @endphp
                            @if($dv > 90)
                                <span class="dash-badge dash-badge-danger">{{ $dv }} dias</span>
                            @elseif($dv > 60)
                                <span class="dash-badge dash-badge-warning">{{ $dv }} dias</span>
                            @elseif($dv > 0)
                                <span class="dash-badge" style="background:#f39c12;color:#fff;">{{ $dv }} dias</span>
                            @else
                                <span style="color:var(--dash-muted);">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;color:var(--dash-muted);padding:32px;">
                            <i class="fa fa-check-circle" style="font-size:24px;margin-bottom:8px;display:block;color:#27ae60;"></i>
                            Sin facturas pendientes en el periodo seleccionado
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function initCobrosCharts() {
    var span = document.getElementById('dash-data');
    if (!span || typeof ApexCharts === 'undefined') return;
    var kc   = JSON.parse(span.dataset.cobros || '{}');
    if (Array.isArray(kc)) kc = {};
    var dark = window.DASH_DARK;
    var base = typeof dashBaseOpts !== 'undefined' ? dashBaseOpts(dark) : {};

    // Abonos diarios (barras)
    const elD = document.querySelector('#chart-cobros-diarios');
    if (elD) {
        elD.innerHTML = '';
        new ApexCharts(elD, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'bar', height:240 }),
            series: [{ name:'Abonado (L.)', data: (kc.cobros_diarios && kc.cobros_diarios.montos) ? kc.cobros_diarios.montos : [] }],
            xaxis:  { categories: (kc.cobros_diarios && kc.cobros_diarios.fechas) ? kc.cobros_diarios.fechas : [], labels:{ rotate:-45, style:{fontSize:'10px'} } },
            yaxis:  { labels: { formatter: v => lpsK(v) } },
            tooltip: { y: { formatter: v => lps(v) } },
            colors: ['#27ae60'],
            plotOptions: { bar: { borderRadius:3 } },
            dataLabels: { enabled: false },
        })).render();
    }

    // Tendencia 6 meses (area)
    const elT = document.querySelector('#chart-cobros-tendencia');
    if (elT) {
        elT.innerHTML = '';
        new ApexCharts(elT, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'area', height:240 }),
            series: [{ name:'Abonos (L.)', data: (kc.tendencia_cobros && kc.tendencia_cobros.montos) ? kc.tendencia_cobros.montos : [] }],
            xaxis:  { categories: (kc.tendencia_cobros && kc.tendencia_cobros.labels) ? kc.tendencia_cobros.labels : [] },
            yaxis:  { labels: { formatter: v => lpsK(v) } },
            tooltip: { y: { formatter: v => lps(v) } },
            colors: ['#2980b9'],
            stroke: { curve:'smooth', width:2.5 },
            fill:   { type:'gradient', gradient:{ opacityFrom:.35, opacityTo:.05 } },
            dataLabels: { enabled: false },
        })).render();
    }

    // Antiguedad (donut)
    const elA = document.querySelector('#chart-cobros-antiguedad');
    if (elA && kc.antiguedad) {
        elA.innerHTML = '';
        new ApexCharts(elA, Object.assign({}, base, {
            chart:  Object.assign({}, (base.chart||{}), { type:'donut', height:260 }),
            series: [
                kc.antiguedad['al_dia']  || 0,
                kc.antiguedad['0_30']   || 0,
                kc.antiguedad['31_60']  || 0,
                kc.antiguedad['61_90']  || 0,
                kc.antiguedad['90_mas'] || 0,
            ],
            labels: ['Al dia','0-30 dias','31-60 dias','61-90 dias','> 90 dias'],
            colors: ['#27ae60','#2ecc71','#f39c12','#e67e22','#e74c3c'],
            legend: { position:'bottom' },
            tooltip: { y: { formatter: v => lps(v) } },
            dataLabels: { enabled: true, formatter: v => v.toFixed(1)+'%' },
            plotOptions: { pie: { donut: { size:'65%' } } },
        })).render();
    }
}
(function() {
    function run() { if (typeof ApexCharts !== 'undefined') initCobrosCharts(); }
    if (window.DASH_LOADED) { run(); } else { document.addEventListener('livewire:load', run); }
    if (!window._cobrosBound) { window._cobrosBound = true; document.addEventListener('livewire:update', run); }
})();
</script>
