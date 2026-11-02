@php
    $saldoCentavos = (int) round(($kpisCobros['saldo_pendiente'] ?? 0) * 100);
    $recuperadoCentavos = (int) round(($kpisCobros['total_recuperado'] ?? 0) * 100);
    $tramosCartera = [
        ['clave' => 'al_dia', 'nombre' => 'Al día', 'color' => '#27ae60'],
        ['clave' => '0_30', 'nombre' => '1–30 días', 'color' => '#f1c40f'],
        ['clave' => '31_60', 'nombre' => '31–60 días', 'color' => '#f39c12'],
        ['clave' => '61_90', 'nombre' => '61–90 días', 'color' => '#e67e22'],
        ['clave' => '90_mas', 'nombre' => 'Más de 90 días', 'color' => '#e74c3c'],
    ];
    $clasificadoCentavos = 0;
    $vencidoCentavos = 0;
    foreach ($tramosCartera as &$tramoCartera) {
        $tramoCartera['centavos'] = (int) round(($kpisCobros['antiguedad'][$tramoCartera['clave']] ?? 0) * 100);
        $clasificadoCentavos += $tramoCartera['centavos'];
        if ($tramoCartera['clave'] !== 'al_dia') $vencidoCentavos += $tramoCartera['centavos'];
    }
    unset($tramoCartera);
    $tramosCartera[] = ['clave' => 'sin_clasificar', 'nombre' => 'Sin clasificar', 'color' => '#94a3b8', 'centavos' => max(0, $saldoCentavos - $clasificadoCentavos)];
    $porcentajeVencido = $saldoCentavos > 0 ? round($vencidoCentavos / $saldoCentavos * 100, 2) : null;
    $baseRecuperacion = $saldoCentavos + $recuperadoCentavos;
    $tasaRecuperacion = $baseRecuperacion > 0 ? round($recuperadoCentavos / $baseRecuperacion * 100, 2) : null;
    $puedeAbrirCartera = \App\Http\Controllers\MenuHelper::tieneAcceso('reportes/analisis_de_negocio')
        && \App\Http\Controllers\MenuHelper::tieneAcceso('reportes/cartera_de_cobros');
    $graficosCartera = \Illuminate\Support\Arr::only($kpisCobros, ['saldo_pendiente', 'antiguedad', 'tendencia_cobros']);
@endphp
<section class="cr-summary" aria-labelledby="cr-title"
    wire:key="cartera-resumen-{{ md5(json_encode([$graficosCartera, $darkMode ?? false])) }}"
    x-data="carteraResumen(@js($graficosCartera), @js($darkMode ?? false))">
    <style>
        .cr-summary { margin:24px 0; padding:24px; border:1px solid var(--dash-border); border-top:4px solid var(--dash-orange); border-radius:18px; background:var(--dash-card-bg); color:var(--dash-text); box-shadow:var(--dash-shadow); }
        .cr-summary .cr-heading { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:18px; margin-bottom:22px; }
        .cr-summary .cr-eyebrow { color:var(--dash-orange); font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:1.2px; margin-bottom:6px; }
        .cr-summary h3 { font-size:22px; font-weight:800; margin:0 0 7px; color:var(--dash-text); }
        .cr-summary p { color:var(--dash-muted); font-size:13px; margin:0; line-height:1.6; }
        .cr-summary .cr-link { display:inline-flex; align-items:center; justify-content:center; gap:10px; padding:12px 17px; border-radius:10px; color:#fff; background:#b9570b; font-weight:700; font-size:13px; text-decoration:none; transition:background .15s; }
        .cr-summary .cr-link:hover { background:#974508; }
        .cr-summary .cr-link:focus-visible { outline:3px solid var(--dash-orange); outline-offset:3px; }
        .cr-summary .cr-metrics { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin-bottom:22px; }
        .cr-summary .cr-metric { border:1px solid var(--dash-border); border-radius:12px; padding:18px; background:var(--dash-bg); min-width:0; }
        .cr-summary .cr-label { display:flex; align-items:center; gap:8px; color:var(--dash-muted); font-weight:700; font-size:12px; margin-bottom:14px; }
        .cr-summary .cr-label i { color:var(--dash-orange); }
        .cr-summary .cr-value { font-size:clamp(20px,1.8vw,28px); font-weight:800; line-height:1.3; font-variant-numeric:tabular-nums; overflow-wrap:anywhere; }
        .cr-summary .cr-caption { color:var(--dash-muted); font-size:12px; margin-top:8px; line-height:1.6; }
        .cr-summary .cr-danger { color:var(--dash-red); }
        .cr-summary .cr-success { color:var(--dash-green); }
        .cr-summary .cr-panels { display:grid; grid-template-columns:minmax(0,1.15fr) minmax(0,1fr); gap:18px; }
        .cr-summary .cr-panel { border:1px solid var(--dash-border); border-radius:12px; padding:20px; min-width:0; }
        .cr-summary h4 { font-size:15px; font-weight:800; color:var(--dash-text); margin:0 0 5px; }
        .cr-summary .cr-period { display:inline-block; padding:3px 8px; border:1px solid var(--dash-border); border-radius:6px; color:var(--dash-muted); font-size:11px; margin-top:10px; }
        .cr-summary .cr-aging { display:grid; grid-template-columns:minmax(150px,.8fr) minmax(0,1fr); gap:12px; align-items:center; margin-top:12px; }
        .cr-summary .cr-aging > div { min-width:0; }
        .cr-summary .cr-bands { display:grid; gap:12px; }
        .cr-summary .cr-band-head { display:flex; justify-content:space-between; align-items:center; gap:8px; font-size:12px; }
        .cr-summary .cr-band-name { display:flex; align-items:center; gap:6px; }
        .cr-summary .cr-dot { width:8px; height:8px; flex-shrink:0; border-radius:50%; background:var(--cr-color); }
        .cr-summary .cr-band-amount { color:var(--dash-muted); font-size:11px; margin:3px 0 5px; font-variant-numeric:tabular-nums; }
        .cr-summary .cr-track { height:5px; border-radius:8px; overflow:hidden; background:var(--dash-bg); }
        .cr-summary .cr-fill { height:100%; border-radius:8px; background:var(--cr-color); }
        .cr-summary .cr-footnote { margin-top:18px; padding:12px 15px; background:var(--dash-bg); border:1px solid var(--dash-border); border-radius:9px; font-size:12px; color:var(--dash-muted); line-height:1.7; }
        .cr-summary .cr-empty { padding:55px 10px; text-align:center; color:var(--dash-muted); }
        @media(min-width:1200px) and (max-width:1450px) { .cr-summary .cr-aging { grid-template-columns:minmax(0,1fr); } }
        @media(max-width:1100px) { .cr-summary .cr-metrics { grid-template-columns:repeat(2,minmax(0,1fr)); } .cr-summary .cr-panels { grid-template-columns:minmax(0,1fr); } }
        @media(max-width:575px) { .cr-summary { padding:16px; } .cr-summary .cr-metrics,.cr-summary .cr-aging { grid-template-columns:minmax(0,1fr); } .cr-summary .cr-link { width:100%; } .cr-summary .cr-panel { padding:14px; } }
    </style>
    <header class="cr-heading">
        <div>
            <div class="cr-eyebrow">Control de cartera</div>
            <h3 id="cr-title">Cartera y cobros</h3>
            <p>Cuánto falta por cobrar, cuánto está vencido y cuánto hemos recuperado.</p>
        </div>
        @if($puedeAbrirCartera)
            <a class="cr-link" href="{{ url('reportes/analisis_de_negocio') }}?modulo=cartera">
                Ver Cartera de Cobros <i class="fa fa-arrow-right" aria-hidden="true"></i>
            </a>
        @endif
    </header>
    <div class="cr-metrics">
        <article class="cr-metric">
            <div class="cr-label"><i class="fa fa-money" aria-hidden="true"></i> Saldo pendiente actual</div>
            <div class="cr-value">L. {{ number_format($saldoCentavos / 100, 2) }}</div>
            <div class="cr-caption">{{ number_format($kpisCobros['facturas_pendientes'] ?? 0) }} facturas por cobrar</div>
        </article>
        <article class="cr-metric">
            <div class="cr-label"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Saldo vencido actual</div>
            <div class="cr-value cr-danger">L. {{ number_format($vencidoCentavos / 100, 2) }}</div>
            <div class="cr-caption"><strong>{{ $porcentajeVencido === null ? 'Sin base' : number_format($porcentajeVencido, 2) . '%' }}</strong> del saldo pendiente<br>{{ number_format($kpisCobros['facturas_vencidas'] ?? 0) }} facturas · {{ number_format($kpisCobros['clientes_morosos'] ?? 0) }} clientes en mora</div>
        </article>
        <article class="cr-metric">
            <div class="cr-label"><i class="fa fa-check-circle" aria-hidden="true"></i> Recuperado en el período</div>
            <div class="cr-value cr-success">L. {{ number_format($recuperadoCentavos / 100, 2) }}</div>
            <div class="cr-caption">{{ $periodoLabel }}
                @if(($kpisCobros['var_recuperado'] ?? null) !== null)
                    <br><strong class="{{ $kpisCobros['var_recuperado'] >= 0 ? 'cr-success' : 'cr-danger' }}">{{ $kpisCobros['var_recuperado'] > 0 ? '+' : '' }}{{ number_format($kpisCobros['var_recuperado'], 1) }}%</strong> vs período anterior
                @else
                    <br>Sin base de comparación anterior
                @endif
            </div>
        </article>
        <article class="cr-metric">
            <div class="cr-label"><i class="fa fa-pie-chart" aria-hidden="true"></i> Recuperación de cartera</div>
            <div class="cr-value">{{ $tasaRecuperacion === null ? 'Sin base' : number_format($tasaRecuperacion, 2) . '%' }}</div>
            <div class="cr-caption">Cobrado del período ÷ (cobrado del período + saldo pendiente actual). No es una meta.</div>
        </article>
    </div>
    <div x-show="error" x-text="error" class="alert alert-danger" role="alert" style="display:none;"></div>
    <div class="cr-panels">
        <article class="cr-panel">
            <h4>¿Cómo evoluciona la recaudación?</h4>
            <p>Abonos por mes en los últimos 6 meses. Pasa el cursor para ver el monto y su participación.</p>
            <div id="chart-ger-tendencia-cobros" x-ref="mensual" wire:ignore style="min-height:275px;"></div>
            <div class="cr-period">El mes actual está en curso · No representa un mes completo</div>
        </article>
        <article class="cr-panel">
            <h4>¿Dónde se concentra la deuda?</h4>
            <p>Distribución del saldo actual por días de atraso, no por fecha de emisión.</p>
            @if($saldoCentavos > 0)
                <div class="cr-aging">
                    <div id="chart-ger-antiguedad" x-ref="antiguedad" wire:ignore style="min-height:230px;"></div>
                    <div class="cr-bands">
                        @foreach($tramosCartera as $tramoCartera)
                            @if($tramoCartera['clave'] !== 'sin_clasificar' || $tramoCartera['centavos'] > 0)
                                @php $participacionTramo = round($tramoCartera['centavos'] / $saldoCentavos * 100, 2); @endphp
                                <div style="--cr-color:{{ $tramoCartera['color'] }};">
                                    <div class="cr-band-head"><span class="cr-band-name"><span class="cr-dot" aria-hidden="true"></span>{{ $tramoCartera['nombre'] }}</span><strong>{{ number_format($participacionTramo, 2) }}%</strong></div>
                                    <div class="cr-band-amount">L. {{ number_format($tramoCartera['centavos'] / 100, 2) }}</div>
                                    <div class="cr-track" aria-hidden="true"><div class="cr-fill" style="width:{{ min(100, $participacionTramo) }}%;"></div></div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                <div class="cr-empty"><i class="fa fa-check-circle cr-success" aria-hidden="true"></i> No hay saldo pendiente por cobrar.</div>
            @endif
        </article>
    </div>
    <div class="cr-footnote"><i class="fa fa-info-circle" aria-hidden="true"></i> <strong>Cómo leer este resumen:</strong> el saldo y la mora corresponden al estado actual de la cartera; el recuperado corresponde a {{ $periodoLabel }}. Para consultar clientes, responsables, facturas y descargar Excel, abre Cartera de Cobros en Análisis de negocio.</div>
</section>
@push('scripts')
    <script src="{{ asset('js/js_proyecto/reportes/cartera-resumen.js') }}"></script>
@endpush
