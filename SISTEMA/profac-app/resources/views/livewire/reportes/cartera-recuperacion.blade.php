<section class="cb-panel mb-3">
    <h3 class="font-weight-bold">Recuperación y comparación de cartera</h3>
    <p class="cb-muted">Compara la recaudación de dos meses o consulta rangos detallados. Recuperado = abonos activos aplicados a deuda, sin comisiones bancarias, notas de crédito ni pagos anulados. Incluye facturas ya liquidadas.</p>
    <div class="btn-group mb-3" role="group" aria-label="Modo de comparación">
        <button type="button" class="btn {{ $modoPeriodo === 'mensual' ? 'btn-warning' : 'btn-outline-secondary' }}"
            wire:click="$set('modoPeriodo', 'mensual')" aria-pressed="{{ $modoPeriodo === 'mensual' ? 'true' : 'false' }}">Comparar por mes</button>
        <button type="button" class="btn {{ $modoPeriodo === 'detalle' ? 'btn-warning' : 'btn-outline-secondary' }}"
            wire:click="$set('modoPeriodo', 'detalle')" aria-pressed="{{ $modoPeriodo === 'detalle' ? 'true' : 'false' }}">Detalle por fechas</button>
    </div>
    @error('modoPeriodo')<p class="text-danger" role="alert">{{ $message }}</p>@enderror
    <div class="row">
        @if($modoPeriodo === 'mensual')
            @foreach(['mesA' => 'Mes A · recaudación', 'mesB' => 'Mes B · comparación'] as $campo => $nombre)
                <div class="col-md-6 mb-3">
                    <label for="cb-{{ $campo }}">{{ $nombre }}</label>
                    <input id="cb-{{ $campo }}" type="month" class="form-control" wire:model.live="{{ $campo }}" max="{{ now()->format('Y-m') }}">
                    @error($campo)<span class="text-danger" role="alert">{{ $message }}</span>@enderror
                </div>
            @endforeach
            <div class="col-12"><p class="cb-muted">Meses completos. El mes en curso se muestra únicamente hasta hoy; los períodos y fechas efectivos aparecen en los indicadores y en Excel.</p></div>
        @else
            @foreach(['inicio' => 'Período A · desde', 'fin' => 'Período A · hasta', 'compararInicio' => 'Período B · desde', 'compararFin' => 'Período B · hasta'] as $campo => $nombre)
                <div class="col-lg-3 col-md-6 mb-3">
                    <label for="cb-{{ $campo }}">{{ $nombre }}</label>
                    <input id="cb-{{ $campo }}" type="date" class="form-control" wire:model.live="{{ $campo }}" max="{{ now()->toDateString() }}">
                    @error($campo)<span class="text-danger" role="alert">{{ $message }}</span>@enderror
                </div>
            @endforeach
        @endif
        <div class="col-md-6 mb-3">
            @include('livewire.reportes.cartera-filtro', ['campo' => 'usuario', 'nombre' => 'Usuario que registró el abono', 'opciones' => $recuperacion['usuarios']])
        </div>
    </div>
    <p class="cb-muted">
        <strong>Recuperación % = cobrado del período / (cobrado del período + saldo pendiente actual) × 100.</strong>
        Ambos períodos usan el mismo saldo actual; no es una tasa sobre cartera inicial histórica.
        Los filtros de vencimiento/mora se evalúan al día de hoy. Al seleccionar un registrador, el saldo corresponde a las facturas donde registró abonos en A o B.
        Un registrador no equivale necesariamente al cobrador físico. Los saldos por registrador pueden coincidir si varios usuarios abonaron la misma factura; no deben sumarse.
    </p>
    @if(!$recuperacion['valida'])
        <div class="alert alert-warning">Corrige los rangos de fechas para consultar o descargar la recuperación.</div>
    @else
        <div class="row">
            @foreach($recuperacion['comparacion'] as $periodo)
                <div class="col-md-6 mb-3"><div class="cb-kpi">
                    <span class="font-weight-bold">{{ $modoPeriodo === 'mensual' ? 'Recaudación mes' : 'Recuperación período' }} {{ $periodo['periodo'] }} · {{ $periodo['inicio'] }} a {{ $periodo['fin'] }}</span>
                    <strong>{{ $dinero($periodo['cobrado']) }} · {{ $periodo['porcentaje'] === null ? 'Sin base' : $periodo['porcentaje'] . '%' }}</strong>
                    <small>{{ $periodo['abonos'] }} abonos · Saldo pendiente actual {{ $dinero($periodo['saldo']) }}</small>
                </div></div>
            @endforeach
        </div>
        @php
            $a = $recuperacion['comparacion'][0]; $b = $recuperacion['comparacion'][1];
            $maximoCobro = max(1, $a['cobrado'], $b['cobrado']);
        @endphp
        <p><strong>Diferencia A − B:</strong> {{ $dinero($a['cobrado'] - $b['cobrado']) }} ·
            Variación {{ $b['cobrado'] ? number_format(($a['cobrado'] - $b['cobrado']) * 100 / $b['cobrado'], 2) . '%' : 'Sin base de comparación' }}.
            Diferencia de recuperación: {{ $a['porcentaje'] !== null && $b['porcentaje'] !== null ? number_format($a['porcentaje'] - $b['porcentaje'], 2) . ' puntos porcentuales' : 'Sin base de comparación' }}.
            Los rangos pueden tener distinta duración; compara sus fechas antes de interpretar el crecimiento.
        </p>
        <div class="row">
            <div class="col-lg-6">
                <h4>Comparación de cobros A / B</h4>
                <svg class="cb-chart" viewBox="0 0 500 240" role="img" aria-label="Comparación de cobros de los períodos A y B">
                    <line x1="140" y1="185" x2="480" y2="185" stroke="#ccc"/>
                    @foreach($recuperacion['comparacion'] as $indice => $periodo)
                        <text x="5" y="{{ 65 + $indice * 85 }}">{{ $modoPeriodo === 'mensual' ? substr($periodo['inicio'], 0, 7) : 'Período ' . $periodo['periodo'] }}</text>
                        <rect x="140" y="{{ 30 + $indice * 85 }}" width="{{ 330 * $periodo['cobrado'] / $maximoCobro }}" height="45" fill="{{ $indice === 0 ? '#f39c12' : '#1ab394' }}"/>
                        <text x="140" y="{{ 95 + $indice * 85 }}">{{ $dinero($periodo['cobrado']) }}</text>
                    @endforeach
                </svg>
                <button class="btn btn-sm btn-outline-secondary" wire:click="descargar('comparacion')">Excel · comparación</button>
                <div class="table-responsive cb-table-scroll">
                    <table class="table table-sm cb-detail">
                        <thead><tr><th>Período</th><th>Cobrado</th><th>Saldo actual</th><th>Recuperación</th></tr></thead>
                        <tbody>@foreach($recuperacion['comparacion'] as $p)<tr><td>{{ $p['periodo'] }}{{ $modoPeriodo === 'mensual' ? ' · ' . substr($p['inicio'], 0, 7) : '' }}</td><td>{{ $dinero($p['cobrado']) }}</td><td>{{ $dinero($p['saldo']) }}</td><td>{{ $p['porcentaje'] === null ? 'Sin base' : $p['porcentaje'] . '%' }}</td></tr>@endforeach</tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-6">
                <h4>Evolución mensual de lo recuperado · A</h4>
                <button class="btn btn-sm btn-outline-secondary" wire:click="descargar('evolucion')">Excel · evolución</button>
                @php $maximoMes = max(1, $recuperacion['evolucion']->max('cobrado')); @endphp
                <div class="cb-scroll">
                    <svg style="width:100%; min-height:100px;" viewBox="0 0 500 {{ max(90, $recuperacion['evolucion']->count() * 48) }}" role="img" aria-label="Evolución mensual de cobros del período A">
                        @foreach($recuperacion['evolucion'] as $indice => $p)
                            <text x="0" y="{{ $indice * 48 + 20 }}">{{ $p['mes'] }}</text>
                            <rect x="90" y="{{ $indice * 48 + 3 }}" width="{{ 210 * $p['cobrado'] / $maximoMes }}" height="25" fill="#e67e22"/>
                            <text x="310" y="{{ $indice * 48 + 20 }}">{{ $dinero($p['cobrado']) }}</text>
                        @endforeach
                    </svg>
                    @if($recuperacion['evolucion']->isEmpty())<p>No hay abonos en el período A.</p>@endif
                </div>
            </div>
        </div>
        <div class="row mt-4">
            @foreach(['gestor' => 'Gestores de entrega', 'teleasesor' => 'Teleasesores', 'usuario' => 'Usuarios que registran abonos'] as $dimension => $tituloGrafico)
                @php
                    $gruposGrafico = $recuperacion['grupos'][$dimension];
                    $escalaGrafico = max(1, $gruposGrafico->max('actual'), $gruposGrafico->max('anterior'));
                @endphp
                <div class="col-xl-4 mb-3">
                    <div class="cb-panel cb-recovery-chart" id="cb-grafico-{{ $dimension }}">
                        <h4 class="font-weight-bold">Recaudación por {{ mb_strtolower($tituloGrafico) }}</h4>
                        <p class="cb-muted">Todos los registros. Clic en una persona para filtrar; clic nuevamente para quitar la selección.</p>
                        <div class="small mb-2">
                            <span class="cb-series-key" style="background:#f39c12"></span> A · {{ $a['inicio'] }} a {{ $a['fin'] }}<br>
                            <span class="cb-series-key" style="background:#1ab394"></span> B · {{ $b['inicio'] }} a {{ $b['fin'] }}
                        </div>
                        <p class="cb-muted">Porcentajes de participación sobre la recaudación total de cada período, no tasas de recuperación.</p>
                        <button type="button" class="btn btn-sm btn-outline-secondary mb-2" wire:click="descargar('recuperacion-{{ $dimension }}')">Excel · datos del gráfico</button>
                        <div class="cb-scroll">
                            @forelse($gruposGrafico as $g)
                                @php
                                    $participacionA = $a['cobrado'] > 0 ? number_format($g['actual'] * 100 / $a['cobrado'], 2) . '%' : 'Sin base';
                                    $participacionB = $b['cobrado'] > 0 ? number_format($g['anterior'] * 100 / $b['cobrado'], 2) . '%' : 'Sin base';
                                @endphp
                                <button type="button" class="cb-bar cb-recovery-person {{ $this->$dimension === $g['id'] ? 'cb-active' : '' }}"
                                    wire:key="grafico-{{ $dimension }}-{{ $g['id'] }}"
                                    wire:click="seleccionar('{{ $dimension }}', '{{ $g['id'] }}')"
                                    aria-pressed="{{ $this->$dimension === $g['id'] ? 'true' : 'false' }}">
                                    <strong class="d-block">{{ $g['nombre'] }}</strong>
                                    <svg class="cb-recovery-bars" viewBox="0 0 300 44" preserveAspectRatio="none" aria-hidden="true">
                                        <rect x="0" y="3" width="300" height="14" rx="3" fill="#edf1f7"/>
                                        <rect x="0" y="3" width="{{ $g['actual'] * 300 / $escalaGrafico }}" height="14" rx="3" fill="#f39c12"/>
                                        <rect x="0" y="25" width="300" height="14" rx="3" fill="#edf1f7"/>
                                        <rect x="0" y="25" width="{{ $g['anterior'] * 300 / $escalaGrafico }}" height="14" rx="3" fill="#1ab394"/>
                                    </svg>
                                    <span class="small d-block">A: {{ $dinero($g['actual']) }} · {{ $participacionA }}</span>
                                    <span class="small d-block">B: {{ $dinero($g['anterior']) }} · {{ $participacionB }}</span>
                                    <span class="cb-person-tooltip" role="tooltip">
                                        <strong>{{ $g['nombre'] }}</strong><br>
                                        A: {{ $dinero($g['actual']) }} · {{ $participacionA }}<br>
                                        B: {{ $dinero($g['anterior']) }} · {{ $participacionB }}<br>
                                        Diferencia A − B: {{ $dinero($g['diferencia']) }}
                                    </span>
                                </button>
                            @empty
                                <p class="cb-muted">Sin registros para los períodos y filtros seleccionados.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @foreach(['cliente' => 'Clientes', 'asesor' => 'Asesores comerciales', 'teleasesor' => 'Teleasesores', 'gestor' => 'Gestores de entrega', 'usuario' => 'Usuarios que registraron abonos'] as $campo => $nombre)
            <div class="mt-4">
                <div class="d-flex justify-content-between flex-wrap">
                    <h4 class="font-weight-bold">Recuperación por {{ mb_strtolower($nombre) }}</h4>
                    <button class="btn btn-sm btn-outline-secondary" wire:click="descargar('recuperacion-{{ $campo }}')">Excel · tabla completa</button>
                </div>
                <p class="cb-muted">{{ $recuperacion['grupos'][$campo]->count() }} registros · Sin top ni recortes. Clic en el nombre para filtrar todo el tablero.</p>
                <div class="table-responsive cb-table-scroll">
                    <table class="table table-hover table-sm cb-detail" id="cb-recuperacion-{{ $campo }}">
                        <thead><tr><th>{{ $nombre }}</th><th>Cobrado A @if($modoPeriodo === 'mensual')<small class="d-block">{{ $mesA }}</small>@endif</th><th>Cobrado B @if($modoPeriodo === 'mensual')<small class="d-block">{{ $mesB }}</small>@endif</th><th>Diferencia</th><th>Saldo actual</th><th>Recuperación A</th><th>Recuperación B</th></tr></thead>
                        <tbody>
                            @forelse($recuperacion['grupos'][$campo] as $g)
                                <tr>
                                    <td><button class="cb-link {{ $this->$campo === $g['id'] ? 'font-weight-bold' : '' }}" wire:click="seleccionar('{{ $campo }}', '{{ $g['id'] }}')">{{ $g['nombre'] }}</button></td>
                                    <td class="text-nowrap">{{ $dinero($g['actual']) }}</td><td class="text-nowrap">{{ $dinero($g['anterior']) }}</td>
                                    <td class="text-nowrap">{{ $dinero($g['diferencia']) }}</td><td class="text-nowrap">{{ $dinero($g['saldo']) }}</td>
                                    <td>{{ $g['porcentaje'] === null ? 'Sin base' : $g['porcentaje'] . '%' }}</td>
                                    <td>{{ $g['porcentajeB'] === null ? 'Sin base' : $g['porcentajeB'] . '%' }}</td>
                                </tr>
                            @empty<tr><td colspan="7">Sin registros para esta selección.</td></tr>@endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
        <details class="mt-4">
            <summary class="font-weight-bold">Ver todos los abonos recuperados (A y B)</summary>
            <button class="btn btn-sm btn-outline-secondary my-2" wire:click="descargar('abonos')">Excel · todos los abonos</button>
            <div class="table-responsive cb-table-scroll">
                <table class="table table-hover table-sm cb-detail">
                    <thead><tr><th>Período</th><th>Abono</th><th>Fecha</th><th>Cliente</th><th>Factura</th><th>Aplicado</th><th>Asesor</th><th>Teleasesor</th><th>Gestor</th><th>Registrado por</th></tr></thead>
                    <tbody>
                        @foreach(['A' => 'actual', 'B' => 'anterior'] as $periodo => $clave)
                            @foreach($recuperacion[$clave] as $p)
                                <tr><td>{{ $periodo }}</td><td>#{{ $p->id }}</td><td>{{ $p->fecha }}</td>
                                    <td><button class="cb-link" wire:click="seleccionar('cliente', '{{ $p->factura->cliente_id }}')">{{ $p->factura->cliente }}</button></td>
                                    <td>{{ $p->factura->cai }}</td><td class="text-nowrap">{{ $dinero($p->centavos) }}</td>
                                    @foreach(['asesor', 'teleasesor', 'gestor'] as $campo)<td><button class="cb-link" wire:click="seleccionar('{{ $campo }}', '{{ $p->factura->{$campo . '_id'} }}')">{{ $p->factura->$campo }}</button></td>@endforeach
                                    <td><button class="cb-link" wire:click="seleccionar('usuario', '{{ $p->usuario_id }}')">{{ $p->usuario }}</button></td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="cb-muted">Si los rangos se superponen, un abono aparece en ambos períodos deliberadamente; no se suman A y B.</p>
        </details>
    @endif
</section>
