<div class="container-fluid py-3 cartera-bi" x-data="{ pestana: 'cartera' }">
    @php
        $dinero = fn ($centavos) => 'L ' . number_format($centavos / 100, 2);
        $porcentaje = fn ($centavos) => $metricas['total'] > 0 ? round($centavos * 100 / $metricas['total'], 1) : 0;
    @endphp
    <style>
        .cartera-bi { color:#26354b; }
        .cartera-bi .cb-hero { background:linear-gradient(120deg,#f39c12,#e67e22); color:white; border-radius:14px; padding:24px; }
        .cartera-bi .cb-panel { background:white; border:1px solid #e1e7ef; border-radius:12px; padding:18px; height:100%; box-shadow:0 3px 12px #172e5008; }
        .cartera-bi .cb-kpi { width:100%; text-align:left; border:1px solid #e1e7ef; border-top:4px solid #f39c12; border-radius:10px; padding:16px; background:white; height:100%; color:inherit; }
        .cartera-bi .cb-kpi strong { display:block; font-size:22px; margin:6px 0; }
        .cartera-bi .cb-kpi small { display:block; color:#65758a; }
        .cartera-bi button.cb-kpi:hover,.cartera-bi .cb-bar:hover { background:#fff3e0; }
        .cartera-bi .cb-active { outline:2px solid #f39c12; outline-offset:1px; }
        .cartera-bi .cb-bar { width:100%; display:block; border:0; border-radius:7px; background:transparent; color:inherit; padding:9px; text-align:left; margin-bottom:5px; }
        .cartera-bi .cb-track { background:#edf1f7; border-radius:5px; height:9px; overflow:hidden; margin-top:6px; }
        .cartera-bi .cb-fill { height:100%; border-radius:5px; background:linear-gradient(90deg,#f39c12,#e67e22); }
        .cartera-bi .cb-chip { border:1px solid #f6ce8e; border-radius:18px; padding:5px 12px; color:#985614; background:#fff3e0; margin:3px; }
        .cartera-bi .cb-detail th { white-space:nowrap; background:#fff3e0; }
        .cartera-bi .cb-table-scroll { max-height:420px; overflow:auto; position:relative; }
        .cartera-bi .cb-table-scroll table { border-collapse:separate; border-spacing:0; margin-bottom:0; }
        .cartera-bi .cb-table-scroll thead th { position:sticky; top:0; z-index:2; box-shadow:0 2px 0 #e1e7ef; }
        .cartera-bi .cb-detail td { vertical-align:middle; }
        .cartera-bi .cb-muted { color:#65758a; font-size:12px; }
        .cartera-bi .cb-scroll { max-height:360px; overflow-y:auto; }
        .cartera-bi button:focus-visible { outline:3px solid #f39c12; }
        .cartera-bi .cb-link { border:0; background:none; color:#a65a0e; text-align:left; padding:0; }
        .cartera-bi .cb-chart { width:100%; height:240px; }
        .cartera-bi .cb-chart text { font-size:12px; fill:#26354b; }
        .cartera-bi .cb-chart [wire\:click] { cursor:pointer; }
        .cartera-bi .cb-segment { transition:stroke-width .18s ease,opacity .18s ease; }
        .cartera-bi .cb-chart-center { pointer-events:none; }
        .cartera-bi .cb-chart .cb-center-name { font-size:10px; }
        .cartera-bi .cb-chart-wrap { position:relative; }
        .cartera-bi .cb-chart-tooltip { position:absolute; bottom:4px; left:50%; transform:translateX(-50%); max-width:100%; width:max-content; padding:8px 14px; border-radius:8px; background:#26354b; color:white; box-shadow:0 3px 12px #172e5026; pointer-events:none; z-index:3; text-align:center; font-size:12px; }
        .cartera-bi .cb-chart-tooltip strong { display:block; }
        .cb-loading { position:fixed; inset:0; z-index:2100; align-items:center; justify-content:center; background:#ffffffb8; cursor:wait; }
        .cb-loading .cb-loading-card { display:flex; flex-direction:column; align-items:center; gap:14px; max-width:calc(100vw - 32px); padding:28px 36px; border-radius:12px; background:white; color:#26354b; box-shadow:0 6px 30px #172e5026; text-align:center; }
        .cb-loading .cb-spinner { width:48px; height:48px; border:5px solid #fce5c5; border-top-color:#e67e22; border-radius:50%; animation:cb-spin .8s linear infinite; }
        .cb-loading .cb-muted { color:#65758a; font-size:12px; }
        @keyframes cb-spin { to { transform:rotate(360deg); } }
        .cartera-bi [x-cloak] { display:none !important; }
        .cartera-bi .cb-tabs { gap:8px; border-bottom:2px solid #f39c12; padding-bottom:8px; }
        .cartera-bi .cb-tab { border:1px solid #e1e7ef; background:white; border-radius:8px; padding:12px 20px; color:inherit; }
        .cartera-bi .cb-tab-active { background:#e67e22; color:white; border-color:#e67e22; }
        .cartera-bi .cb-filter { position:relative; }
        .cartera-bi .cb-filter-toggle { display:flex; justify-content:space-between; align-items:center; min-width:0; }
        .cartera-bi .cb-filter-menu { position:absolute; top:100%; right:0; width:100%; min-width:240px; max-width:calc(100vw - 50px); z-index:20; background:white; border:1px solid #e1e7ef; border-radius:8px; padding:10px; box-shadow:0 8px 24px #172e5026; }
        .cartera-bi .cb-filter-options { max-height:240px; overflow:auto; }
        .cartera-bi .cb-filter-option { display:block; width:100%; text-align:left; background:white; border:0; border-radius:4px; padding:8px; color:inherit; }
        .cartera-bi .cb-filter-option:hover,.cartera-bi .cb-selected { background:#fff3e0; }
    </style>

    <div class="cb-hero mb-3 d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h2 class="font-weight-bold mb-1"><i class="fa fa-pie-chart mr-2"></i>Cartera de cobros · BI</h2>
            <div>Saldos pendientes, riesgo y responsables de la recuperación</div>
            <small>Cartera actual al {{ now()->format('d/m/Y H:i') }} · Clic en las barras para cruzar todos los indicadores</small>
        </div>
        <div class="mt-2">
            <button type="button" class="btn btn-outline-light" wire:click="$refresh" wire:loading.attr="disabled"><i class="fa fa-refresh"></i> Actualizar</button>
            <button type="button" class="btn btn-light" wire:click="limpiar">Limpiar filtros</button>
            <button type="button" class="btn btn-dark" wire:click="descargar" wire:loading.attr="disabled"><i class="fa fa-file-excel-o"></i> Excel completo</button>
        </div>
    </div>

    <div class="cb-panel mb-3">
        <div class="row">
            <div class="col-lg-4 mb-2">
                <label for="cb-buscar" class="small font-weight-bold">Cliente, ID, factura o secuencia CAI</label>
                <input id="cb-buscar" type="search" class="form-control" wire:model.live.debounce.350ms="buscar" placeholder="Buscar en toda la cartera">
            </div>
            @foreach(['cliente' => 'Cliente', 'asesor' => 'Asesor comercial', 'teleasesor' => 'Teleasesor', 'gestor' => 'Gestor de entrega'] as $campo => $nombre)
                <div class="col-lg-2 col-md-6 mb-2">
                    @include('livewire.reportes.cartera-filtro', ['opciones' => $catalogos[$campo]])
                </div>
            @endforeach
        </div>
        <div class="cb-muted">Busca por nombre o ID en los filtros. Se combinan y se conservan al cambiar de pestaña. Usa × para quitar uno o “Limpiar filtros” para restaurar toda la cartera.</div>
        <div class="mt-2">
            @foreach(['cliente' => 'Cliente', 'asesor' => 'Asesor', 'teleasesor' => 'Teleasesor', 'gestor' => 'Gestor', 'tramo' => 'Antigüedad de mora', 'estado' => 'Estado', 'mes' => 'Vencimiento', 'usuario' => 'Usuario registrador'] as $campo => $nombre)
                @if($this->$campo !== '')
                    <button class="cb-chip" type="button" wire:click="seleccionar('{{ $campo }}', '')">
                        {{ $nombre }}:
                        {{ isset($catalogos[$campo]) ? ($catalogos[$campo][$this->$campo] ?? $this->$campo) : ($campo === 'tramo' ? (\App\Livewire\Reportes\CarteraDeCobros::TRAMOS[$this->$campo] ?? $this->$campo) : ($campo === 'usuario' ? ($recuperacion['usuarios'][$this->$campo] ?? $this->$campo) : $this->$campo)) }} ×
                    </button>
                @endif
            @endforeach
        </div>
    </div>

    <div class="d-flex flex-wrap cb-tabs mb-3" role="tablist" aria-label="Secciones de cartera">
        <button type="button" id="cb-tab-cartera" class="cb-tab" :class="{ 'cb-tab-active': pestana === 'cartera' }"
            role="tab" :aria-selected="pestana === 'cartera'" aria-controls="cb-panel-cartera" @click="pestana = 'cartera'">
            <i class="fa fa-pie-chart mr-1" aria-hidden="true"></i> Cartera pendiente
        </button>
        <button type="button" id="cb-tab-recuperacion" class="cb-tab" :class="{ 'cb-tab-active': pestana === 'recuperacion' }"
            role="tab" :aria-selected="pestana === 'recuperacion'" aria-controls="cb-panel-recuperacion" @click="pestana = 'recuperacion'">
            <i class="fa fa-line-chart mr-1" aria-hidden="true"></i> Recuperación y recaudación
        </button>
    </div>

    <div id="cb-panel-recuperacion" role="tabpanel" aria-labelledby="cb-tab-recuperacion" x-show="pestana === 'recuperacion'" x-cloak>
        @include('livewire.reportes.cartera-recuperacion')
    </div>
    <div id="cb-panel-cartera" role="tabpanel" aria-labelledby="cb-tab-cartera" x-show="pestana === 'cartera'">
    <div class="row mb-2">
        @foreach([
            ['total', 'Deuda total', '', 'Saldo abierto de la selección'],
            ['vencido', 'Deuda vencida', 'vencida', 'Vencimiento anterior a hoy'],
            ['corriente', 'Por vencer', 'corriente', 'Incluye lo que vence hoy'],
            ['critica', 'Riesgo alto · más de 90 días', 'critica', 'Saldo con más de 90 días de mora'],
        ] as [$clave, $nombre, $filtro, $ayuda])
            <div class="col-xl-3 col-md-6 mb-3">
                <button type="button" class="cb-kpi {{ $estado === $filtro && $filtro !== '' ? 'cb-active' : '' }}" wire:click="seleccionar('estado', '{{ $filtro }}')">
                    <span class="text-uppercase small font-weight-bold">{{ $nombre }}</span>
                    <strong>{{ $dinero($metricas[$clave]) }}</strong>
                    <small>{{ $porcentaje($metricas[$clave]) }}% de la selección · {{ $ayuda }}</small>
                </button>
            </div>
        @endforeach
        <div class="col-xl-3 col-md-6 mb-3"><div class="cb-kpi">
            <span class="small font-weight-bold text-uppercase">Exposición</span>
            <strong>{{ number_format($metricas['clientes']) }} clientes</strong>
            <small>{{ number_format($metricas['facturas']) }} facturas abiertas · Promedio/factura {{ $dinero($metricas['facturas'] ? round($metricas['total'] / $metricas['facturas']) : 0) }}</small>
        </div></div>
        <div class="col-xl-3 col-md-6 mb-3">
            <button type="button" class="cb-kpi" @if($antigua) wire:click="seleccionar('cliente', '{{ $antigua->cliente_id }}')" @else disabled @endif>
                <span class="small font-weight-bold text-uppercase">Deuda más antigua · emisión</span>
                <strong>{{ $antigua ? number_format($antigua->antiguedad) . ' días' : 'Sin datos' }}</strong>
                <small>{{ $antigua ? $antigua->cliente . ' · ' . ($antigua->cai ?: $antigua->numero_secuencia_cai) : 'No hay facturas con fecha de emisión' }}</small>
            </button>
        </div>
        <div class="col-xl-3 col-md-6 mb-3"><div class="cb-kpi">
            <span class="small font-weight-bold text-uppercase">Mora ponderada por saldo</span>
            <strong>{{ number_format($metricas['mora'], 1) }} días</strong>
            <small>Promedio de días de mora ponderado por saldo vencido</small>
        </div></div>
        <div class="col-xl-3 col-md-6 mb-3">
            <button type="button" class="cb-kpi {{ $estado === 'sin-fecha' ? 'cb-active' : '' }}" wire:click="seleccionar('estado', 'sin-fecha')">
                <span class="small font-weight-bold text-uppercase">Sin fecha de vencimiento</span>
                <strong>{{ $dinero($metricas['sinFecha']) }}</strong>
                <small>Revisar datos · No se clasifica como corriente ni vencida</small>
            </button>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-5 mb-3">
            <div class="cb-panel">
                <h4 class="font-weight-bold">Antigüedad de saldos</h4>
                <button class="btn btn-sm btn-outline-secondary" wire:click="descargar('antiguedad')">Excel</button>
                <p class="cb-muted">Días transcurridos desde el vencimiento. Pasa el cursor para ver monto y porcentaje; clic para filtrar.</p>
                @php $acumulado = 0; $colores = ['#1ab394', '#f8c471', '#f39c12', '#e67e22', '#d35400', '#b03a2e', '#95a5a6']; @endphp
                @if($tramo !== '')
                    <button type="button" class="cb-chip mb-2" wire:click="seleccionar('tramo', '')">
                        {{ \App\Livewire\Reportes\CarteraDeCobros::TRAMOS[$tramo] }} · Quitar selección ×
                    </button>
                @endif
                <div class="cb-chart-wrap" wire:key="antiguedad-{{ md5($tramos->toJson()) }}-{{ $tramo }}"
                    x-data="{ detalle: null }" @mouseleave="detalle = null" @keydown.escape="detalle = null">
                <svg class="cb-chart" viewBox="0 0 300 240" role="group" aria-label="Distribución de cartera por antigüedad">
                    <circle cx="150" cy="110" r="75" fill="none" stroke="#edf1f7" stroke-width="32"/>
                    @foreach($tramos->values() as $indice => $grupo)
                        @php $parte = $metricas['total'] ? $grupo['saldo'] / $metricas['total'] * 100 : 0; @endphp
                        <circle class="cb-segment" cx="150" cy="110" r="75" fill="none" stroke="{{ $colores[$indice] }}" stroke-width="32" pathLength="100"
                            :stroke-width="detalle?.tramo === @js($grupo['id']) ? 42 : 32"
                            :opacity="detalle && detalle.tramo !== @js($grupo['id']) ? 0.35 : 1"
                            style="{{ $parte > 0 ? '' : 'pointer-events:none;' }}"
                            stroke-dasharray="{{ $parte }} {{ 100 - $parte }}" stroke-dashoffset="{{ -$acumulado }}" transform="rotate(-90 150 110)"
                            data-nombre="{{ $grupo['nombre'] }}" data-monto="{{ $dinero($grupo['saldo']) }}" data-porcentaje="{{ number_format($parte, 2) }}%"
                            data-facturas="{{ $grupo['facturas'] }}" data-tramo="{{ $grupo['id'] }}"
                            @mouseenter="detalle = $el.dataset" @mouseleave="detalle = null"
                            @focus="detalle = $el.dataset" @blur="detalle = null" @click="detalle = null"
                            @keydown.enter.prevent="$el.dispatchEvent(new MouseEvent('click', { bubbles: true }))"
                            @keydown.space.prevent="$el.dispatchEvent(new MouseEvent('click', { bubbles: true }))"
                            tabindex="{{ $parte > 0 ? '0' : '-1' }}" role="button"
                            aria-pressed="{{ $tramo === $grupo['id'] ? 'true' : 'false' }}"
                            aria-label="{{ $grupo['nombre'] }}: {{ $dinero($grupo['saldo']) }} ({{ number_format($parte, 2) }}%). {{ $grupo['facturas'] }} facturas. Filtrar."
                            aria-describedby="cb-antiguedad-tooltip"
                            wire:click="seleccionar('tramo', '{{ $grupo['id'] }}')"></circle>
                        @php $acumulado += $parte; @endphp
                    @endforeach
                    <g class="cb-chart-center">
                        <text class="cb-center-name" x="150" y="93" text-anchor="middle" x-text="detalle ? detalle.nombre : 'Cartera actual'">Cartera actual</text>
                        <text x="150" y="113" text-anchor="middle" font-weight="bold" x-text="detalle ? detalle.monto : @js($dinero($metricas['total']))">{{ $dinero($metricas['total']) }}</text>
                        <text class="cb-center-name" x="150" y="133" text-anchor="middle" x-text="detalle ? detalle.porcentaje + ' · ' + detalle.facturas + ' facturas' : 'Clic en un tramo para filtrar'"></text>
                    </g>
                </svg>
                <div id="cb-antiguedad-tooltip" class="cb-chart-tooltip" role="tooltip" x-show="detalle" x-cloak>
                    <strong x-text="detalle?.nombre"></strong>
                    <span x-text="detalle ? detalle.monto + ' · ' + detalle.porcentaje : ''"></span>
                    <span class="d-block" x-text="detalle ? detalle.facturas + ' facturas' : ''"></span>
                </div>
                </div>
                @foreach($tramos as $grupo)
                    <button type="button" class="cb-bar {{ $tramo === $grupo['id'] ? 'cb-active' : '' }}" wire:click="seleccionar('tramo', '{{ $grupo['id'] }}')">
                        <div class="d-flex justify-content-between"><span>{{ $grupo['nombre'] }}</span><strong>{{ $dinero($grupo['saldo']) }}</strong></div>
                        <div class="cb-track"><div class="cb-fill" style="width:{{ $porcentaje($grupo['saldo']) }}%"></div></div>
                        <small class="cb-muted">{{ $grupo['facturas'] }} facturas · {{ $porcentaje($grupo['saldo']) }}%</small>
                    </button>
                @endforeach
            </div>
        </div>
        @foreach(['saldo' => 'Clientes con mayor deuda', 'antiguedad' => 'Clientes con deuda más antigua'] as $criterio => $nombre)
            @php
                $ranking = $criterio === 'saldo' ? $clientes : $clientes->sortByDesc('antiguedad');
                $maximo = max(1, (int) $ranking->max($criterio));
            @endphp
            <div class="col-lg-{{ $criterio === 'saldo' ? '4' : '3' }} mb-3">
                <div class="cb-panel">
                    <h4 class="font-weight-bold">{{ $nombre }}</h4>
                    <button class="btn btn-sm btn-outline-secondary" wire:click="descargar('clientes')">Excel · todos los clientes</button>
                    <p class="cb-muted">{{ $ranking->count() }} clientes · Información completa, sin límite</p>
                    <div class="cb-scroll">
                        @forelse($ranking as $grupo)
                            <button type="button" class="cb-bar {{ $cliente === $grupo['id'] ? 'cb-active' : '' }}" wire:click="seleccionar('cliente', '{{ $grupo['id'] }}')">
                                <span class="d-block font-weight-bold">{{ $grupo['nombre'] }}</span>
                                <div class="cb-track"><div class="cb-fill" style="width:{{ round($grupo[$criterio] * 100 / $maximo, 1) }}%"></div></div>
                                <small>{{ $dinero($grupo['saldo']) }} · {{ $grupo['facturas'] }} facturas</small>
                                <small class="d-block cb-muted">{{ $criterio === 'saldo' ? $porcentaje($grupo['saldo']) . '% del saldo' : $grupo['antiguedad'] . ' días de antigüedad' }} · Mora máxima {{ max(0, $grupo['dias'] ?? 0) }} días</small>
                            </button>
                        @empty
                            <p class="text-muted">Sin clientes en esta selección.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        @foreach($responsables as $campo => $panel)
            @php $maximo = max(1, (int) $panel['filas']->max('saldo')); @endphp
            <div class="col-lg-4 mb-3">
                <div class="cb-panel">
                    <h4 class="font-weight-bold">Deuda por {{ mb_strtolower($panel['nombre']) }}</h4>
                    <button class="btn btn-sm btn-outline-secondary" wire:click="descargar('{{ $campo }}')">Excel</button>
                    <p class="cb-muted">{{ $panel['filas']->count() }} responsables · Listado completo</p>
                    <div class="cb-scroll">
                        @forelse($panel['filas'] as $grupo)
                            <button type="button" class="cb-bar {{ $this->$campo === $grupo['id'] ? 'cb-active' : '' }}" wire:click="seleccionar('{{ $campo }}', '{{ $grupo['id'] }}')">
                                <div class="d-flex justify-content-between"><span>{{ $grupo['nombre'] }}</span><strong>{{ $dinero($grupo['saldo']) }}</strong></div>
                                <div class="cb-track"><div class="cb-fill" style="width:{{ round($grupo['saldo'] * 100 / $maximo, 1) }}%"></div></div>
                                <small class="cb-muted">Vencido {{ $dinero($grupo['vencido']) }} · {{ $grupo['facturas'] }} facturas · {{ $porcentaje($grupo['saldo']) }}% del saldo</small>
                            </button>
                        @empty
                            <p class="text-muted">Sin responsables en esta selección.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="cb-panel mb-3">
        <h4 class="font-weight-bold">Distribución por mes de vencimiento</h4>
        <button class="btn btn-sm btn-outline-secondary" wire:click="descargar('meses')">Excel</button>
        <div class="row cb-scroll">
            @forelse($meses as $grupo)
                <div class="col-lg-3 col-md-4 col-6">
                    <button type="button" class="cb-bar {{ $mes === $grupo['id'] ? 'cb-active' : '' }}" wire:click="seleccionar('mes', '{{ $grupo['id'] }}')">
                        <strong>{{ $grupo['id'] === 'sin-fecha' ? 'Sin vencimiento' : $grupo['id'] }}</strong>
                        <div>{{ $dinero($grupo['saldo']) }}</div>
                        <div class="cb-track"><div class="cb-fill" style="width:{{ $porcentaje($grupo['saldo']) }}%"></div></div>
                        <small class="cb-muted">{{ $grupo['facturas'] }} facturas</small>
                    </button>
                </div>
            @empty
                <div class="col text-muted">Sin movimientos para los filtros seleccionados.</div>
            @endforelse
        </div>
    </div>

    <div class="cb-panel mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div><h4 class="font-weight-bold mb-1">Detalle de cartera pendiente</h4><small>{{ $detalle->total() }} facturas · Las mismas selecciones de los gráficos</small></div>
            <button class="btn btn-sm btn-outline-secondary" wire:click="descargar('detalle')">Excel · todas las páginas</button>
            <div>
                <label for="cb-orden" class="small">Ordenar por</label>
                <select id="cb-orden" class="form-control form-control-sm" wire:model.live="orden">
                    <option value="saldo">Mayor saldo</option><option value="mora">Mayor mora</option><option value="antiguedad">Emisión más antigua</option>
                </select>
            </div>
        </div>
        <div class="table-responsive cb-table-scroll">
            <table class="table table-hover table-sm cb-detail">
                <thead><tr><th>Cliente</th><th>Factura / CAI</th><th>Emisión</th><th>Vencimiento</th><th class="text-right">Saldo</th><th>Mora</th><th>Antigüedad</th><th>Asesor</th><th>Teleasesor</th><th>Gestor de entrega</th></tr></thead>
                <tbody>
                    @forelse($detalle as $fila)
                        <tr wire:key="cartera-factura-{{ $fila->id }}">
                            <td><button class="cb-link" wire:click="seleccionar('cliente', '{{ $fila->cliente_id }}')">{{ $fila->cliente }}</button><small class="d-block text-muted">#{{ $fila->cliente_id }}</small></td>
                            <td>{{ $fila->cai ?: $fila->numero_secuencia_cai }}<small class="d-block text-muted">ID {{ $fila->id }}</small></td>
                            <td>{{ $fila->fecha_emision ?: 'Sin fecha' }}</td>
                            <td>{{ $fila->fecha_vencimiento ?: 'Sin fecha' }}</td>
                            <td class="text-right font-weight-bold text-nowrap">{{ $dinero($fila->centavos) }}</td>
                            <td><button class="cb-link" wire:click="seleccionar('tramo', '{{ $fila->tramo }}')">{{ $fila->dias === null ? 'Sin vencimiento' : ($fila->dias > 0 ? $fila->dias . ' días' : 'Por vencer') }}</button></td>
                            <td>{{ $fila->antiguedad !== null ? $fila->antiguedad . ' días' : 'Sin fecha' }}</td>
                            @foreach(['asesor', 'teleasesor', 'gestor'] as $campo)
                                <td><button class="cb-link" wire:click="seleccionar('{{ $campo }}', '{{ $fila->{$campo . '_id'} }}')">{{ $fila->$campo }}</button></td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center py-4">No hay deuda pendiente con los filtros seleccionados. Limpia los filtros para consultar toda la cartera.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr><th colspan="4">Total de la selección (todas las páginas)</th><th class="text-right text-nowrap">{{ $dinero($metricas['total']) }}</th><th colspan="5"></th></tr></tfoot>
            </table>
        </div>
        {{ $detalle->links() }}
    </div>
    <div class="cb-muted mb-3">
        <strong>Reglas de lectura:</strong> facturas activas y cuentas abiertas con saldo positivo; no incluye anuladas, cerradas ni saldos a favor.
        Se conserva el saldo neto registrado (abonos, notas y movimientos); las comisiones bancarias no se suman a la deuda.
        Los cambios de cargo se consideran sin alterar los registros. Las facturas aún no sincronizadas en Aplicación de Pagos se incluyen por su cargo vigente.
        Este es un corte actual, no una reconstrucción histórica. Mora ponderada = suma(saldo vencido × días de mora) / saldo vencido.
    </div>
    </div>
    @teleport('body')
    <div wire:loading.flex class="cb-loading" role="status" aria-live="polite" aria-label="Cargando información">
        <div class="cb-loading-card">
            <span class="cb-spinner" aria-hidden="true"></span>
            <strong>Cargando información…</strong>
            <span class="cb-muted">Aplicando filtros y actualizando el tablero.</span>
        </div>
        @endteleport
    </div>
</div>
