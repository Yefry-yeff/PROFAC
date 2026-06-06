<div>
@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════════
   Dashboard Empresarial — Estilos
═══════════════════════════════════════════════════════════════════ */
@keyframes db-fadeInUp {
    from { opacity:0; transform:translateY(18px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes db-countUp {
    from { opacity:0; transform:scale(.85); }
    to   { opacity:1; transform:scale(1); }
}

/* ── Sección ── */
.db-section { margin-bottom: 36px; animation: db-fadeInUp .45s ease both; }
.db-section-header {
    border-radius: 14px 14px 0 0;
    padding: 18px 24px;
    background: linear-gradient(135deg, #f39c12, #e67e22);
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px;
}
.db-section-header h4 {
    margin: 0; color: #fff; font-size: 15px; font-weight: 700;
    letter-spacing: .3px;
    display: flex; align-items: center; gap: 10px;
}
.db-section-header .db-period-badge {
    background: rgba(255,255,255,.22);
    color: #fff; font-size: 11px; font-weight: 600;
    padding: 3px 12px; border-radius: 20px;
    letter-spacing: .4px;
}
.db-section-body {
    background: #fff;
    border: 1px solid #f0f0f0;
    border-top: none;
    border-radius: 0 0 14px 14px;
    padding: 22px 20px;
    box-shadow: 0 6px 28px rgba(0,0,0,.07);
}

/* ── KPI card ── */
.db-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}
.db-kpi {
    background: #fafbfc;
    border: 1px solid #edf2f7;
    border-radius: 12px;
    padding: 16px 18px;
    transition: box-shadow .2s, transform .2s;
    cursor: default;
}
.db-kpi:hover {
    box-shadow: 0 6px 20px rgba(243,156,18,.15);
    transform: translateY(-2px);
}
.db-kpi-label {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .6px; color: #a0aec0; margin-bottom: 8px;
    display: flex; align-items: center; gap: 6px;
}
.db-kpi-label i { color: #f39c12; font-size: 12px; }
.db-kpi-value {
    font-size: 24px; font-weight: 700; color: #2d3748;
    line-height: 1; animation: db-countUp .5s ease both;
}
.db-kpi-value.orange { color: #e67e22; }
.db-kpi-value.green  { color: #27ae60; }
.db-kpi-value.red    { color: #e74c3c; }
.db-kpi-value.blue   { color: #2980b9; }
.db-kpi-sub {
    font-size: 11px; color: #718096; margin-top: 5px;
    display: flex; align-items: center; gap: 4px;
}
.db-kpi-sub .up   { color: #27ae60; }
.db-kpi-sub .down { color: #e74c3c; }

/* ── Chart wrapper ── */
.db-chart-wrap {
    border-radius: 10px;
    background: #fafbfc;
    border: 1px solid #edf2f7;
    padding: 16px 12px 8px;
    margin-top: 4px;
}
.db-chart-title {
    font-size: 12px; font-weight: 700; color: #4a5568;
    text-transform: uppercase; letter-spacing: .5px;
    margin-bottom: 12px; padding-left: 4px;
    display: flex; align-items: center; gap: 6px;
}
.db-chart-title::before {
    content: '';
    width: 3px; height: 14px;
    background: linear-gradient(135deg, #f39c12, #e67e22);
    border-radius: 2px; display: inline-block;
}

/* ── Ranking ── */
.db-rank-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.db-rank-table th {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: #a0aec0; padding: 6px 10px;
    border-bottom: 2px solid #edf2f7; background: #fafbfc;
}
.db-rank-table td { padding: 9px 10px; border-bottom: 1px solid #f7f7f7; color: #2d3748; }
.db-rank-table tr:last-child td { border-bottom: none; }
.db-rank-badge {
    width: 24px; height: 24px; border-radius: 50%;
    background: linear-gradient(135deg, #f39c12, #e67e22);
    color: #fff; font-size: 11px; font-weight: 700;
    display: inline-flex; align-items: center; justify-content: center;
}
.db-rank-badge.pos-1 { background: linear-gradient(135deg, #f6d365, #fda085); }
.db-rank-badge.pos-2 { background: linear-gradient(135deg, #a8c0ff, #3f2b96); }
.db-rank-badge.pos-3 { background: linear-gradient(135deg, #cd9a63, #a0522d); }
.db-empty { text-align: center; padding: 24px; color: #a0aec0; font-size: 13px; }
.db-empty i { display: block; font-size: 28px; margin-bottom: 8px; opacity:.3; }

/* ── Divider ── */
.db-divider {
    height: 1px; background: #edf2f7; margin: 20px 0;
}

/* ── Barra progreso ── */
.db-progress { height: 5px; background: #edf2f7; border-radius: 3px; margin-top: 7px; }
.db-progress-fill { height: 100%; border-radius: 3px;
    background: linear-gradient(135deg, #f39c12, #e67e22); }
</style>
@endpush

{{-- ══════════════════════════════════════════════════════════════════
     DASHBOARD EMPRESARIAL
══════════════════════════════════════════════════════════════════ --}}

{{-- ════════════════════════════════════════════════════════════════
     SECCIÓN 1 — FACTURACIÓN
════════════════════════════════════════════════════════════════ --}}
<div class="db-section" style="animation-delay:.1s;">
    <div class="db-section-header">
        <h4><i class="fa fa-file-text-o"></i> Facturación</h4>
        <span class="db-period-badge">{{ $periodoLabel }}</span>
    </div>
    <div class="db-section-body">

        {{-- KPIs --}}
        <div class="db-kpi-grid">
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-file-text-o"></i> Facturas emitidas</div>
                <div class="db-kpi-value orange">{{ number_format($fact['count']) }}</div>
                <div class="db-kpi-sub">documentos este mes</div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-dollar"></i> Total facturado</div>
                <div class="db-kpi-value orange">L. {{ number_format($fact['total'], 2) }}</div>
                <div class="db-kpi-sub">
                    @if($fact['variacion'] !== null)
                        @if($fact['variacion'] >= 0)
                            <span class="up"><i class="fa fa-arrow-up"></i> {{ $fact['variacion'] }}%</span>
                        @else
                            <span class="down"><i class="fa fa-arrow-down"></i> {{ abs($fact['variacion']) }}%</span>
                        @endif
                        vs mes anterior
                    @else
                        vs mes anterior
                    @endif
                </div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-line-chart"></i> Ticket promedio</div>
                <div class="db-kpi-value">L. {{ number_format($fact['ticket'], 2) }}</div>
                <div class="db-kpi-sub">por factura</div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-credit-card"></i> Crédito</div>
                <div class="db-kpi-value blue">{{ number_format($fact['credito']) }}</div>
                @php $pctCred = $fact['count'] > 0 ? round(($fact['credito'] / $fact['count']) * 100) : 0; @endphp
                <div class="db-kpi-sub">{{ $pctCred }}% del total</div>
                <div class="db-progress"><div class="db-progress-fill" style="width:{{ $pctCred }}%"></div></div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-money"></i> Contado</div>
                <div class="db-kpi-value green">{{ number_format($fact['contado']) }}</div>
                @php $pctCont = $fact['count'] > 0 ? round(($fact['contado'] / $fact['count']) * 100) : 0; @endphp
                <div class="db-kpi-sub">{{ $pctCont }}% del total</div>
                <div class="db-progress"><div class="db-progress-fill" style="width:{{ $pctCont }}%; background:linear-gradient(135deg,#27ae60,#2ecc71);"></div></div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-lock"></i> Facturas cerradas</div>
                <div class="db-kpi-value green">{{ number_format($fact['cerradasCount']) }}</div>
                <div class="db-kpi-sub">L. {{ number_format($fact['cerradasMonto'], 2) }} recuperados</div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-clock-o"></i> Saldo pendiente</div>
                <div class="db-kpi-value red">{{ number_format($fact['pendientes']) }}</div>
                <div class="db-kpi-sub">facturas con saldo activo</div>
            </div>
        </div>

        {{-- Gráfico Facturación 6 meses --}}
        <div class="db-chart-wrap">
            <div class="db-chart-title">Evolución últimos 6 meses</div>
            <div id="chart-facturacion"></div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════
     SECCIÓN 2 — VENDEDORES
════════════════════════════════════════════════════════════════ --}}
<div class="db-section" style="animation-delay:.2s;">
    <div class="db-section-header">
        <h4><i class="fa fa-users"></i> Vendedores</h4>
        <span class="db-period-badge">{{ $periodoLabel }}</span>
    </div>
    <div class="db-section-body">

        {{-- KPIs --}}
        <div class="db-kpi-grid">
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-file-o"></i> Cotizaciones</div>
                <div class="db-kpi-value orange">{{ number_format($vend['cotCount']) }}</div>
                <div class="db-kpi-sub">L. {{ number_format($vend['cotTotal'], 2) }} total</div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-shopping-cart"></i> Pedidos creados</div>
                <div class="db-kpi-value blue">{{ number_format($vend['pedCount']) }}</div>
                <div class="db-kpi-sub">flujos este mes</div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-check-circle"></i> Pedidos facturados</div>
                <div class="db-kpi-value green">{{ number_format($vend['pedFacturados']) }}</div>
                <div class="db-kpi-sub">convertidos a factura</div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-exchange"></i> Fact. ligadas a pedido</div>
                <div class="db-kpi-value">{{ number_format($vend['facturasConPedido']) }}</div>
                <div class="db-kpi-sub">este mes</div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-percent"></i> Tasa conversión</div>
                <div class="db-kpi-value orange">{{ $vend['tasaConversion'] }}%</div>
                <div class="db-kpi-sub">cot. → factura</div>
                <div class="db-progress"><div class="db-progress-fill" style="width:{{ min($vend['tasaConversion'], 100) }}%"></div></div>
            </div>
        </div>

        <div class="row">
            {{-- Gráfico Cotizaciones vs Pedidos --}}
            <div class="col-md-7">
                <div class="db-chart-wrap">
                    <div class="db-chart-title">Cotizaciones vs Pedidos — últimos 6 meses</div>
                    <div id="chart-vendedores"></div>
                </div>
            </div>
            {{-- Ranking vendedores --}}
            <div class="col-md-5">
                <div class="db-chart-wrap" style="height:100%; min-height:200px;">
                    <div class="db-chart-title">Top 5 Vendedores (por monto)</div>
                    @if(count($rankingVend) > 0)
                    <table class="db-rank-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Vendedor</th>
                                <th>Fact.</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rankingVend as $i => $v)
                            <tr>
                                <td><span class="db-rank-badge pos-{{ $i+1 }}">{{ $i+1 }}</span></td>
                                <td style="font-weight:600;">{{ $v->nombre }}</td>
                                <td>{{ $v->facturas }}</td>
                                <td style="color:#e67e22; font-weight:600;">L. {{ number_format($v->monto, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="db-empty"><i class="fa fa-trophy"></i> Sin datos este mes</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════
     SECCIÓN 3 — COBROS
════════════════════════════════════════════════════════════════ --}}
<div class="db-section" style="animation-delay:.3s;">
    <div class="db-section-header">
        <h4><i class="fa fa-bank"></i> Cobros &amp; Cartera</h4>
        <span class="db-period-badge">{{ $periodoLabel }}</span>
    </div>
    <div class="db-section-body">

        {{-- KPIs --}}
        <div class="db-kpi-grid">
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-briefcase"></i> Cartera activa</div>
                <div class="db-kpi-value red">L. {{ number_format($cobros['carteraTotal'], 2) }}</div>
                <div class="db-kpi-sub">saldo total pendiente</div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-money"></i> Cobros del mes</div>
                <div class="db-kpi-value green">L. {{ number_format($cobros['abonosMonto'], 2) }}</div>
                <div class="db-kpi-sub">
                    @if($cobros['varCobros'] !== null)
                        @if($cobros['varCobros'] >= 0)
                            <span class="up"><i class="fa fa-arrow-up"></i> {{ $cobros['varCobros'] }}%</span>
                        @else
                            <span class="down"><i class="fa fa-arrow-down"></i> {{ abs($cobros['varCobros']) }}%</span>
                        @endif
                        vs mes anterior
                    @else
                        vs mes anterior
                    @endif
                </div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-list-ol"></i> Abonos registrados</div>
                <div class="db-kpi-value orange">{{ number_format($cobros['abonosCount']) }}</div>
                <div class="db-kpi-sub">transacciones este mes</div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-lock"></i> Facturas cerradas</div>
                <div class="db-kpi-value green">{{ number_format($cobros['cerradasMes']) }}</div>
                <div class="db-kpi-sub">saldo en cero este mes</div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-clock-o"></i> Pendientes de cobro</div>
                <div class="db-kpi-value blue">{{ number_format($cobros['pendientes']) }}</div>
                <div class="db-kpi-sub">facturas con saldo &gt; 0</div>
            </div>
            <div class="db-kpi">
                <div class="db-kpi-label"><i class="fa fa-exclamation-triangle"></i> En mora</div>
                <div class="db-kpi-value red">{{ number_format($cobros['enMora']) }}</div>
                <div class="db-kpi-sub">vencidas con saldo</div>
            </div>
        </div>

        {{-- Gráfico cobros 6 meses --}}
        <div class="db-chart-wrap">
            <div class="db-chart-title">Cobros registrados — últimos 6 meses</div>
            <div id="chart-cobros"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    // ── Paleta compartida ──────────────────────────────────────────
    var orange = '#f39c12';
    var orangeD = '#e67e22';
    var green   = '#27ae60';
    var blue    = '#2980b9';
    var gray    = '#edf2f7';

    var fontFamily = "'Source Sans Pro', 'Open Sans', Arial, sans-serif";

    var baseOpts = {
        chart: { toolbar: { show: false }, fontFamily: fontFamily, animations: { enabled: true, speed: 500 } },
        tooltip: { theme: 'light' },
        grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
        dataLabels: { enabled: false },
    };

    // ── 1. Gráfico Facturación ─────────────────────────────────────
    var factCats   = {!! json_encode($graficoFact['categorias']) !!};
    var factMontos = {!! json_encode($graficoFact['montos']) !!};
    var factCants  = {!! json_encode($graficoFact['cantidades']) !!};

    new ApexCharts(document.querySelector('#chart-facturacion'), Object.assign({}, baseOpts, {
        chart: Object.assign({}, baseOpts.chart, { type: 'line', height: 280, id: 'fact' }),
        series: [
            { name: 'Total (L.)', type: 'bar',  data: factMontos },
            { name: 'Facturas',   type: 'line', data: factCants  }
        ],
        xaxis: { categories: factCats, labels: { style: { fontSize: '11px' } } },
        yaxis: [
            { title: { text: 'Monto (L.)', style: { fontSize: '11px', fontWeight: 600 } },
              labels: { formatter: function(v){ return 'L. ' + (v/1000).toFixed(0) + 'k'; } } },
            { opposite: true, title: { text: 'Facturas', style: { fontSize: '11px', fontWeight: 600 } },
              labels: { formatter: function(v){ return v.toFixed(0); } } }
        ],
        colors: [orange, blue],
        fill: {
            type: ['gradient', 'solid'],
            gradient: { shade: 'light', type: 'vertical', gradientToColors: [orangeD], stops: [0, 100] }
        },
        stroke: { width: [0, 3], curve: 'smooth' },
        markers: { size: [0, 5] },
        legend: { position: 'top', fontSize: '12px' },
        plotOptions: { bar: { borderRadius: 5, columnWidth: '55%' } },
    })).render();

    // ── 2. Gráfico Vendedores ──────────────────────────────────────
    var vendCats = {!! json_encode($graficoVend['categorias']) !!};
    var vendCot  = {!! json_encode($graficoVend['cotizaciones']) !!};
    var vendPed  = {!! json_encode($graficoVend['pedidos']) !!};

    new ApexCharts(document.querySelector('#chart-vendedores'), Object.assign({}, baseOpts, {
        chart: Object.assign({}, baseOpts.chart, { type: 'bar', height: 240, id: 'vend' }),
        series: [
            { name: 'Cotizaciones', data: vendCot },
            { name: 'Pedidos',      data: vendPed }
        ],
        xaxis: { categories: vendCats, labels: { style: { fontSize: '11px' } } },
        yaxis: { labels: { formatter: function(v){ return v.toFixed(0); } } },
        colors: [orange, blue],
        fill: {
            type: 'gradient',
            gradient: { shade: 'light', type: 'vertical', stops: [0, 100],
                gradientToColors: [orangeD, '#1a5276'] }
        },
        plotOptions: { bar: { borderRadius: 5, columnWidth: '65%', grouped: true } },
        legend: { position: 'top', fontSize: '12px' },
    })).render();

    // ── 3. Gráfico Cobros ─────────────────────────────────────────
    var cobroCats = {!! json_encode($graficoCobros['categorias']) !!};
    var cobroMon  = {!! json_encode($graficoCobros['montos']) !!};
    var cobroCant = {!! json_encode($graficoCobros['cantidades']) !!};

    new ApexCharts(document.querySelector('#chart-cobros'), Object.assign({}, baseOpts, {
        chart: Object.assign({}, baseOpts.chart, { type: 'area', height: 260, id: 'cobros' }),
        series: [
            { name: 'Cobrado (L.)', data: cobroMon },
            { name: 'Abonos',       data: cobroCant }
        ],
        xaxis: { categories: cobroCats, labels: { style: { fontSize: '11px' } } },
        yaxis: [
            { title: { text: 'Cobrado (L.)', style: { fontSize: '11px', fontWeight: 600 } },
              labels: { formatter: function(v){ return 'L. ' + (v/1000).toFixed(0) + 'k'; } } },
            { opposite: true, title: { text: 'N° Abonos', style: { fontSize: '11px', fontWeight: 600 } },
              labels: { formatter: function(v){ return v.toFixed(0); } } }
        ],
        colors: [green, orange],
        fill: {
            type: 'gradient',
            gradient: { shade: 'light', type: 'vertical', stops: [0, 90],
                gradientToColors: ['#a8e6cf', orangeD], opacityFrom: .55, opacityTo: .05 }
        },
        stroke: { width: [2.5, 2], curve: 'smooth' },
        markers: { size: 4 },
        legend: { position: 'top', fontSize: '12px' },
    })).render();
})();
</script>
@endpush

</div>
