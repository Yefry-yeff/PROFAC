<div class="fa-page">
    @push('styles')
    <style>
        .fa-page { --ink:#24313d; --muted:#71808d; --line:#dfe5e9; --soft:#f4f6f7; --orange:#e67e22; --green:#218c5a; --amber:#d89b16; --red:#c9413a; color:var(--ink); padding-bottom:28px; }
        .fa-heading { align-items:flex-end; display:flex; justify-content:space-between; margin-bottom:16px; padding:18px 4px 0; }
        .fa-heading h2 { font-size:1.35rem; font-weight:700; letter-spacing:0; margin:0; }
        .fa-heading p { color:var(--muted); font-size:.78rem; margin:4px 0 0; }
        .fa-heading-actions { align-items:flex-end; display:flex; flex-direction:column; gap:7px; }
        .fa-quality { background:#fff8e8; border:1px solid #edcf8b; color:#735619; font-size:.72rem; padding:7px 11px; }
        .fa-filters { background:#fff; border:1px solid var(--line); border-left:4px solid var(--orange); box-shadow:0 2px 8px rgba(24,39,52,.05); margin-bottom:14px; padding:14px; }
        .fa-filter-grid { display:grid; gap:10px; grid-template-columns:repeat(6,minmax(120px,1fr)); }
        .fa-field label { color:#596874; display:block; font-size:.64rem; font-weight:700; margin-bottom:3px; text-transform:uppercase; }
        .fa-field .form-control { border-color:#d9e0e4; border-radius:4px; font-size:.76rem; height:33px; padding:5px 8px; }
        .fa-filter-actions { align-items:center; border-top:1px solid #edf0f2; display:flex; gap:8px; justify-content:flex-end; margin-top:11px; padding-top:11px; }
        .fa-btn { align-items:center; border:0; border-radius:4px; display:inline-flex; font-size:.74rem; font-weight:700; gap:6px; min-height:32px; padding:6px 13px; }
        .fa-btn-primary { background:var(--orange); color:#fff; }
        .fa-btn-primary:hover { background:#cf6d15; color:#fff; }
        .fa-btn-muted { background:#edf1f3; color:#4f5c66; }
        .fa-kpis { display:grid; gap:9px; grid-template-columns:repeat(6,minmax(0,1fr)); margin-bottom:14px; }
        .fa-kpi { background:#fff; border:1px solid var(--line); border-radius:5px; min-height:95px; padding:12px; position:relative; }
        .fa-kpi:before { background:#7c8b96; content:""; height:3px; left:0; position:absolute; right:0; top:0; }
        .fa-kpi.green:before { background:var(--green); } .fa-kpi.orange:before { background:var(--orange); }
        .fa-kpi.red:before { background:var(--red); } .fa-kpi.amber:before { background:var(--amber); }
        .fa-kpi-label { color:var(--muted); font-size:.63rem; font-weight:700; line-height:1.3; text-transform:uppercase; }
        .fa-kpi-value { font-size:1.22rem; font-weight:700; line-height:1.15; margin-top:8px; overflow-wrap:anywhere; }
        .fa-kpi-foot { color:#8a969f; font-size:.63rem; margin-top:4px; }
        .fa-grid-2 { display:grid; gap:12px; grid-template-columns:minmax(0,1.35fr) minmax(320px,.65fr); margin-bottom:12px; }
        .fa-grid-even { display:grid; gap:12px; grid-template-columns:repeat(2,minmax(0,1fr)); margin-bottom:12px; }
        .fa-panel { background:#fff; border:1px solid var(--line); border-radius:5px; min-width:0; }
        .fa-panel-head { align-items:center; border-bottom:1px solid #edf0f2; display:flex; justify-content:space-between; min-height:44px; padding:9px 13px; }
        .fa-panel-head h3 { font-size:.78rem; font-weight:700; margin:0; }
        .fa-panel-head small { color:var(--muted); font-size:.65rem; }
        .fa-chart { height:285px; padding:6px 8px 2px; }
        .fa-bottleneck-main { background:#fff7f4; border-bottom:1px solid #f1d4cc; padding:13px; }
        .fa-bottleneck-main strong { color:#a93832; display:block; font-size:.9rem; }
        .fa-bottleneck-main span { color:#735c58; font-size:.7rem; }
        .fa-rank { list-style:none; margin:0; padding:5px 13px 11px; }
        .fa-rank li { align-items:center; border-bottom:1px solid #eef1f2; display:grid; font-size:.7rem; gap:8px; grid-template-columns:22px 1fr auto; padding:8px 0; }
        .fa-rank li:last-child { border-bottom:0; }
        .fa-rank-num { background:#edf1f3; border-radius:50%; display:grid; height:20px; place-items:center; width:20px; }
        .fa-table-wrap { overflow:auto; }
        .fa-table { margin:0; min-width:760px; width:100%; }
        .fa-table th { background:#f4f6f7; border-bottom:1px solid #d8e0e4; color:#596874; font-size:.62rem; padding:8px 9px; position:sticky; text-transform:uppercase; top:0; white-space:nowrap; z-index:1; }
        .fa-table td { border-top:1px solid #edf0f2; font-size:.7rem; padding:8px 9px; vertical-align:middle; }
        .fa-table tbody tr:hover { background:#fffaf5; }
        .fa-link { background:transparent; border:0; color:#16669b; cursor:pointer; font:inherit; font-weight:700; padding:0; text-align:left; }
        .fa-link:hover { color:#c65f0c; text-decoration:underline; }
        .fa-pill { border-radius:10px; display:inline-block; font-size:.61rem; font-weight:700; padding:3px 7px; white-space:nowrap; }
        .fa-pill.normal { background:#dff3e8; color:#176c45; } .fa-pill.advertencia { background:#fff1c9; color:#86620a; }
        .fa-pill.critico { background:#fae0de; color:#9e302b; } .fa-pill.neutral { background:#e9eef1; color:#596874; }
        .fa-loading { align-items:center; background:rgba(255,255,255,.82); inset:0; justify-content:center; position:fixed; z-index:5000; }
        .fa-empty { color:#87939c; font-size:.76rem; padding:28px; text-align:center; }
        .fa-modal .modal-dialog { max-width:1200px; width:calc(100vw - 32px); }
        .fa-modal .modal-header { background:#24313d; color:#fff; }
        .fa-modal .modal-body { max-height:76vh; overflow-y:auto; padding:0; }
        .fa-modal-summary { background:#f4f6f7; border-bottom:1px solid var(--line); display:grid; gap:10px; grid-template-columns:repeat(5,1fr); padding:12px 16px; }
        .fa-summary-item span { color:var(--muted); display:block; font-size:.61rem; font-weight:700; text-transform:uppercase; }
        .fa-summary-item strong { display:block; font-size:.77rem; margin-top:2px; }
        .fa-modal .nav-tabs { padding:9px 14px 0; }
        .fa-modal .nav-link { color:#64727d; font-size:.72rem; font-weight:700; padding:7px 12px; }
        .fa-modal .tab-content { padding:16px; }
        .fa-timeline { margin:0; padding:0 0 0 13px; position:relative; }
        .fa-timeline:before { background:#dce3e7; bottom:10px; content:""; left:23px; position:absolute; top:10px; width:2px; }
        .fa-event { display:grid; gap:10px; grid-template-columns:34px 1fr; padding-bottom:12px; position:relative; }
        .fa-event-icon { align-items:center; background:#fff; border:2px solid #82909a; border-radius:50%; display:flex; height:22px; justify-content:center; margin-top:7px; position:relative; width:22px; z-index:1; }
        .fa-event.normal .fa-event-icon { border-color:var(--green); color:var(--green); }
        .fa-event.advertencia .fa-event-icon { border-color:var(--amber); color:var(--amber); }
        .fa-event.critico .fa-event-icon { border-color:var(--red); color:var(--red); }
        .fa-event-body { border:1px solid var(--line); border-left:3px solid #82909a; padding:10px 12px; }
        .fa-event.normal .fa-event-body { border-left-color:var(--green); } .fa-event.advertencia .fa-event-body { border-left-color:var(--amber); }
        .fa-event.critico .fa-event-body { border-left-color:var(--red); }
        .fa-event-top { align-items:center; display:flex; gap:8px; justify-content:space-between; }
        .fa-event-title { font-size:.8rem; font-weight:700; }
        .fa-event-time { font-size:.76rem; font-weight:700; white-space:nowrap; }
        .fa-event-meta { color:#65737e; display:flex; flex-wrap:wrap; font-size:.66rem; gap:10px; margin-top:4px; }
        .fa-event-note { background:#f7f9fa; color:#53616c; font-size:.68rem; margin-top:7px; padding:6px 8px; }
        .fa-doc-section { border-bottom:1px solid var(--line); margin-bottom:14px; padding-bottom:14px; }
        .fa-doc-section h4 { font-size:.75rem; font-weight:700; margin:0 0 8px; }
        @media (max-width:1200px) { .fa-filter-grid { grid-template-columns:repeat(4,1fr); } .fa-kpis { grid-template-columns:repeat(3,1fr); } }
        @media (max-width:800px) { .fa-filter-grid { grid-template-columns:repeat(2,1fr); } .fa-kpis { grid-template-columns:repeat(2,1fr); } .fa-grid-2,.fa-grid-even { grid-template-columns:1fr; } .fa-modal-summary { grid-template-columns:repeat(2,1fr); } }
        @media (max-width:480px) { .fa-filter-grid,.fa-kpis { grid-template-columns:1fr; } .fa-heading { align-items:flex-start; flex-direction:column; gap:8px; } }
    </style>
    @endpush

    <div wire:loading.flex wire:target="aplicarFiltros,limpiarFiltros,verFlujo" class="fa-loading">
        <div class="text-center"><div class="spinner-border text-warning" role="status"></div><div class="small mt-2">Procesando trazabilidad...</div></div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <header class="fa-heading">
            <div>
                <h2><i class="fa fa-line-chart mr-2 text-warning"></i>Analítica Avanzada del Flujo</h2>
                <p>Vida completa de la venta: oferta, revisiones, prefactura, factura, entrega y cobro.</p>
            </div>
            <div class="fa-heading-actions">
                <button type="button" class="fa-btn fa-btn-muted" data-toggle="modal" data-target="#modalUmbralesFlujo"><i class="fa fa-sliders"></i>Configurar semáforo</button>
                <div class="fa-quality"><i class="fa fa-database mr-1"></i>{{ number_format($calidadDatos['eventos'] ?? 0) }} eventos históricos analizados</div>
            </div>
        </header>

        <section class="fa-filters" aria-label="Filtros del dashboard">
            <div class="fa-filter-grid">
                <div class="fa-field"><label>Desde</label><input type="date" class="form-control" wire:model.defer="fechaDesde"></div>
                <div class="fa-field"><label>Hasta</label><input type="date" class="form-control" wire:model.defer="fechaHasta"></div>
                <div class="fa-field"><label>Cliente / RTN</label><input type="text" class="form-control" wire:model.defer="filtroCliente" placeholder="Nombre o RTN"></div>
                <div class="fa-field"><label>Etapa actual</label><select class="form-control" wire:model.defer="filtroEtapa"><option value="">Todas</option>@foreach($catalogos['etapas'] as $item)<option value="{{ $item['id'] }}">{{ $item['nombre'] }}</option>@endforeach</select></div>
                <div class="fa-field"><label>Estado</label><select class="form-control" wire:model.defer="filtroEstado"><option value="">Todos</option><option value="1">Activo</option><option value="7">Finalizado</option></select></div>
                <div class="fa-field"><label>Tipo de venta</label><select class="form-control" wire:model.defer="filtroTipoVenta"><option value="">Todos</option>@foreach($catalogos['tipos_venta'] as $item)<option value="{{ $item['id'] }}">{{ $item['nombre'] }}</option>@endforeach</select></div>
                <div class="fa-field"><label>Asesor comercial</label><select class="form-control" wire:model.defer="filtroAsesor"><option value="">Todos</option>@foreach($catalogos['usuarios'] as $item)<option value="{{ $item['id'] }}">{{ $item['nombre'] }}</option>@endforeach</select></div>
                <div class="fa-field"><label>Teleasesor / creador</label><select class="form-control" wire:model.defer="filtroTeleasesor"><option value="">Todos</option>@foreach($catalogos['usuarios'] as $item)<option value="{{ $item['id'] }}">{{ $item['nombre'] }}</option>@endforeach</select></div>
                <div class="fa-field"><label>Usuario participante</label><select class="form-control" wire:model.defer="filtroUsuario"><option value="">Todos</option>@foreach($catalogos['usuarios'] as $item)<option value="{{ $item['id'] }}">{{ $item['nombre'] }}</option>@endforeach</select></div>
                <div class="fa-field"><label>Oferta</label><input type="number" min="1" class="form-control" wire:model.defer="filtroOferta" placeholder="ID oferta"></div>
                <div class="fa-field"><label>Prefactura</label><input type="number" min="1" class="form-control" wire:model.defer="filtroPrefactura" placeholder="ID prefactura"></div>
                <div class="fa-field"><label>Factura</label><input type="number" min="1" class="form-control" wire:model.defer="filtroFactura" placeholder="ID factura"></div>
                <div class="fa-field"><label>Producto</label><input type="text" class="form-control" wire:model.defer="filtroProducto" placeholder="ID, código o nombre"></div>
                <div class="fa-field"><label>Marca</label><select class="form-control" wire:model.defer="filtroMarca"><option value="">Todas</option>@foreach($catalogos['marcas'] as $item)<option value="{{ $item['id'] }}">{{ $item['nombre'] }}</option>@endforeach</select></div>
                <div class="fa-field"><label>Categoría</label><select class="form-control" wire:model.defer="filtroCategoria"><option value="">Todas</option>@foreach($catalogos['categorias'] as $item)<option value="{{ $item['id'] }}">{{ $item['nombre'] }}</option>@endforeach</select></div>
                <div class="fa-field"><label>Equipo de entrega</label><select class="form-control" wire:model.defer="filtroEquipo"><option value="">Todos</option>@foreach($catalogos['equipos'] as $item)<option value="{{ $item['id'] }}">{{ $item['nombre'] }}</option>@endforeach</select></div>
            </div>
            @error('fechaDesde')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            @error('fechaHasta')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            <div class="fa-filter-actions">
                <button type="button" class="fa-btn fa-btn-muted" wire:click="limpiarFiltros"><i class="fa fa-eraser"></i>Limpiar</button>
                <button type="button" class="fa-btn fa-btn-primary" wire:click="aplicarFiltros"><i class="fa fa-filter"></i>Aplicar filtros</button>
            </div>
        </section>

        <section class="fa-kpis" aria-label="Indicadores principales">
            <div class="fa-kpi orange"><div class="fa-kpi-label">Flujos analizados</div><div class="fa-kpi-value">{{ number_format($kpis['total'] ?? 0) }}</div><div class="fa-kpi-foot">Rango seleccionado</div></div>
            <div class="fa-kpi green"><div class="fa-kpi-label">Completados</div><div class="fa-kpi-value">{{ number_format($kpis['completados'] ?? 0) }}</div><div class="fa-kpi-foot">Ciclo cerrado</div></div>
            <div class="fa-kpi red"><div class="fa-kpi-label">Actualmente abiertos</div><div class="fa-kpi-value">{{ number_format($kpis['abiertos'] ?? 0) }}</div><div class="fa-kpi-foot">Aún en proceso</div></div>
            <div class="fa-kpi"><div class="fa-kpi-label">Promedio ciclo completo</div><div class="fa-kpi-value">{{ $kpis['promedio_total'] ?? 'Sin datos' }}</div><div class="fa-kpi-foot">Oferta hasta cierre</div></div>
            <div class="fa-kpi"><div class="fa-kpi-label">Promedio hasta factura</div><div class="fa-kpi-value">{{ $kpis['promedio_factura'] ?? 'Sin datos' }}</div><div class="fa-kpi-foot">Tiempo comercial + operativo</div></div>
            <div class="fa-kpi"><div class="fa-kpi-label">Promedio hasta entrega</div><div class="fa-kpi-value">{{ $kpis['promedio_entrega'] ?? 'Sin datos' }}</div><div class="fa-kpi-foot">Oferta a logística</div></div>
            <div class="fa-kpi"><div class="fa-kpi-label">Promedio hasta cobro</div><div class="fa-kpi-value">{{ $kpis['promedio_cobro'] ?? 'Sin datos' }}</div><div class="fa-kpi-foot">Oferta a hito de cobro</div></div>
            <div class="fa-kpi amber"><div class="fa-kpi-label">Reprocesos</div><div class="fa-kpi-value">{{ number_format($kpis['reprocesos'] ?? 0) }}</div><div class="fa-kpi-foot">Regresos a una etapa</div></div>
            <div class="fa-kpi red"><div class="fa-kpi-label">Devoluciones / rechazos</div><div class="fa-kpi-value">{{ number_format($kpis['devoluciones'] ?? 0) }}</div><div class="fa-kpi-foot">Detectados en observaciones</div></div>
            <div class="fa-kpi amber"><div class="fa-kpi-label">Solicitudes de código</div><div class="fa-kpi-value">{{ number_format($kpis['solicitudes_codigo'] ?? 0) }}</div><div class="fa-kpi-foot">{{ number_format($kpis['flujos_con_codigo'] ?? 0) }} flujos afectados</div></div>
            <div class="fa-kpi"><div class="fa-kpi-label">Espera promedio por código</div><div class="fa-kpi-value">{{ $kpis['espera_codigo'] ?? 'Sin datos' }}</div><div class="fa-kpi-foot">Solicitudes resueltas</div></div>
            <div class="fa-kpi"><div class="fa-kpi-label">Rápido / lento</div><div class="fa-kpi-value" style="font-size:.88rem;">{{ $kpis['flujo_rapido'] ?? 'Sin datos' }} / {{ $kpis['flujo_lento'] ?? 'Sin datos' }}</div><div class="fa-kpi-foot">Flujos completados</div></div>
        </section>

        <div class="fa-grid-2">
            <section class="fa-panel">
                <div class="fa-panel-head"><h3><i class="fa fa-clock-o mr-1"></i>Tiempo promedio por etapa</h3><small>Clic en una barra para filtrar</small></div>
                <div id="chart-tiempo-etapa" class="fa-chart" wire:ignore></div>
            </section>
            <section class="fa-panel">
                <div class="fa-panel-head"><h3><i class="fa fa-exclamation-triangle mr-1"></i>Cuellos de botella</h3><small>Mayor duración promedio</small></div>
                @if(count($cuellosBotella))
                    <div class="fa-bottleneck-main"><strong>Principal: {{ $cuellosBotella[0]['etapa'] }}</strong><span>{{ $cuellosBotella[0]['promedio'] }} promedio · {{ $cuellosBotella[0]['retrasados'] }} procesos críticos · {{ $cuellosBotella[0]['cumplimiento'] }}% dentro del objetivo</span></div>
                    <ol class="fa-rank">@foreach($cuellosBotella as $index => $item)<li><span class="fa-rank-num">{{ $index + 1 }}</span><span>{{ $item['etapa'] }}<br><small class="text-muted">{{ $item['procesos'] }} procesos</small></span><strong>{{ $item['promedio'] }}</strong></li>@endforeach</ol>
                @else<div class="fa-empty">No hay eventos para analizar.</div>@endif
            </section>
        </div>

        <div class="fa-grid-even">
            <section class="fa-panel"><div class="fa-panel-head"><h3><i class="fa fa-calendar mr-1"></i>Evolución del tiempo promedio</h3><small>Por fecha de inicio</small></div><div id="chart-evolucion" class="fa-chart" wire:ignore></div></section>
            <section class="fa-panel"><div class="fa-panel-head"><h3><i class="fa fa-retweet mr-1"></i>Reprocesos por etapa</h3><small>Visitas posteriores a la primera</small></div><div id="chart-reprocesos" class="fa-chart" wire:ignore></div></section>
        </div>

        <div class="fa-grid-even">
            <section class="fa-panel"><div class="fa-panel-head"><h3><i class="fa fa-pie-chart mr-1"></i>Distribución del ciclo</h3><small>Peso del tiempo promedio por etapa</small></div><div id="chart-distribucion" class="fa-chart" wire:ignore></div></section>
            <section class="fa-panel"><div class="fa-panel-head"><h3><i class="fa fa-key mr-1"></i>Facturación bloqueada por códigos</h3><small>Flujos afectados en el rango</small></div><div id="chart-codigos" class="fa-chart" wire:ignore></div></section>
        </div>

        <section class="fa-panel mb-3">
            <div class="fa-panel-head"><h3><i class="fa fa-building-o mr-1"></i>Analítica por departamento / etapa</h3><small>Promedio, extremos, mediana y cumplimiento</small></div>
            <div class="fa-table-wrap"><table class="fa-table"><thead><tr><th>Área</th><th>Procesos</th><th>Promedio</th><th>Mínimo</th><th>Máximo</th><th>Mediana</th><th>Críticos</th><th>Cumplimiento</th><th>Devoluciones</th><th>Reprocesos</th></tr></thead><tbody>
            @forelse($metricasEtapa as $item)<tr><td><strong>{{ $item['etapa'] }}</strong></td><td>{{ number_format($item['procesos']) }}</td><td>{{ $item['promedio'] }}</td><td>{{ $item['minimo'] }}</td><td>{{ $item['maximo'] }}</td><td>{{ $item['mediana'] }}</td><td>{{ $item['retrasados'] }}</td><td>{{ $item['cumplimiento'] }}%</td><td>{{ $item['devoluciones'] }}</td><td>{{ $item['reprocesos'] }}</td></tr>@empty<tr><td colspan="10" class="fa-empty">Sin datos en el rango seleccionado.</td></tr>@endforelse
            </tbody></table></div>
        </section>

        <div class="fa-grid-even">
            <section class="fa-panel">
                <div class="fa-panel-head"><h3><i class="fa fa-users mr-1"></i>Analítica por usuario</h3><small>Responsable registrado en cada evento</small></div>
                <div class="fa-table-wrap" style="max-height:390px;"><table class="fa-table"><thead><tr><th>Usuario</th><th>Área</th><th>Procesos</th><th>Promedio</th><th>Más rápido</th><th>Más lento</th><th>Dev.</th><th>Rep.</th></tr></thead><tbody>
                @forelse($metricasUsuario as $item)<tr><td><strong>{{ $item['usuario'] }}</strong></td><td>{{ $item['area'] }}</td><td>{{ $item['procesos'] }}</td><td>{{ $item['promedio'] }}</td><td>{{ $item['rapido'] }}</td><td>{{ $item['lento'] }}</td><td>{{ $item['devoluciones'] }}</td><td>{{ $item['reprocesos'] }}</td></tr>@empty<tr><td colspan="8" class="fa-empty">Sin actividad registrada.</td></tr>@endforelse
                </tbody></table></div>
            </section>
            <section class="fa-panel">
                <div class="fa-panel-head"><h3><i class="fa fa-bar-chart mr-1"></i>Volumen procesado por etapa</h3><small>Flujos que alcanzaron cada área</small></div>
                <div id="chart-volumen" class="fa-chart" wire:ignore></div>
            </section>
        </div>

        <section class="fa-panel">
            <div class="fa-panel-head"><h3><i class="fa fa-list-alt mr-1"></i>Operaciones que explican los indicadores</h3><small>100 flujos con mayor duración · clic para abrir trazabilidad</small></div>
            <div class="fa-table-wrap" style="max-height:560px;"><table class="fa-table"><thead><tr><th>Flujo</th><th>Referencia</th><th>Cliente</th><th>Etapa actual</th><th>Inicio</th><th>Última actividad</th><th>Duración</th><th>Reprocesos</th><th>Semáforo</th></tr></thead><tbody>
            @forelse($flujos as $flujo)<tr><td><button type="button" class="fa-link" wire:click="verFlujo({{ $flujo['id'] }})">#{{ $flujo['id'] }}</button></td><td>{{ $flujo['identificacion'] ?: '—' }}</td><td><strong>{{ $flujo['cliente'] }}</strong><br><small class="text-muted">{{ $flujo['rtn'] }}</small></td><td>{{ $flujo['etapa'] }}</td><td>{{ $flujo['inicio'] }}</td><td>{{ $flujo['ultima_actividad'] }}</td><td><strong>{{ $flujo['duracion'] }}</strong></td><td>{{ $flujo['reprocesos'] }}</td><td><span class="fa-pill {{ $flujo['semaforo'] }}">{{ ucfirst($flujo['semaforo']) }}</span></td></tr>@empty<tr><td colspan="9" class="fa-empty">No existen flujos para los filtros aplicados.</td></tr>@endforelse
            </tbody></table></div>
            <div class="fa-quality border-0"><i class="fa fa-info-circle mr-1"></i>{{ $calidadDatos['metodo'] ?? '' }}</div>
        </section>
    </div>

    <div class="modal fade" id="modalUmbralesFlujo" tabindex="-1" role="dialog" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content">
            <div class="modal-header"><div><h5 class="modal-title">Semáforo operativo por etapa</h5><small class="text-muted">Verde hasta el límite normal; amarillo hasta advertencia; rojo al superarlo.</small></div><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body"><div class="fa-table-wrap"><table class="fa-table"><thead><tr><th>Etapa</th><th>Normal hasta (min)</th><th>Advertencia hasta (min)</th><th>Crítico</th></tr></thead><tbody>
                @foreach($umbralesEditables as $tipo => $limites)<tr><td><strong>{{ $limites['nombre'] }}</strong></td><td><input type="number" min="0" class="form-control form-control-sm" wire:model.defer="umbralesEditables.{{ $tipo }}.normal"></td><td><input type="number" min="1" class="form-control form-control-sm" wire:model.defer="umbralesEditables.{{ $tipo }}.advertencia"></td><td>&gt; {{ $limites['advertencia'] }} min</td></tr>@endforeach
            </tbody></table></div></div>
            <div class="modal-footer"><button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cancelar</button><button type="button" class="fa-btn fa-btn-primary" wire:click="guardarUmbrales"><i class="fa fa-save"></i>Guardar límites</button></div>
        </div></div>
    </div>

    <div class="modal fade fa-modal" id="modalDetalleFlujoAnalytics" tabindex="-1" role="dialog" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document"><div class="modal-content">
            @if($detalleFlujo)
            <div class="modal-header"><div><small style="color:#bdc8cf;">Trazabilidad completa</small><h5 class="modal-title mb-0">Flujo #{{ $detalleFlujo['flujo']['id'] }} · {{ $detalleFlujo['flujo']['nombre'] }}</h5></div><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <div class="fa-modal-summary">
                    <div class="fa-summary-item"><span>Referencia</span><strong>{{ $detalleFlujo['flujo']['identificacion'] ?: 'Sin referencia' }}</strong></div>
                    <div class="fa-summary-item"><span>Etapa actual</span><strong>{{ $detalleFlujo['flujo']['etapa_actual'] ?: 'Sin etapa' }}</strong></div>
                    <div class="fa-summary-item"><span>Creado por</span><strong>{{ $detalleFlujo['flujo']['creado_por'] ?: 'Sin registro' }}</strong></div>
                    <div class="fa-summary-item"><span>Tiempo operativo</span><strong>{{ $detalleFlujo['duracion_operativa'] }}</strong></div>
                    <div class="fa-summary-item"><span>Tiempo total observado</span><strong>{{ $detalleFlujo['duracion_total'] }}</strong></div>
                </div>
                <ul class="nav nav-tabs" role="tablist"><li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#fa-tab-linea">Línea de tiempo</a></li><li class="nav-item"><a class="nav-link" data-toggle="tab" href="#fa-tab-documentos">Documentos y facturas</a></li><li class="nav-item"><a class="nav-link" data-toggle="tab" href="#fa-tab-logistica">Entrega y cobro</a></li><li class="nav-item"><a class="nav-link" data-toggle="tab" href="#fa-tab-codigos">Solicitudes de código</a></li><li class="nav-item"><a class="nav-link" data-toggle="tab" href="#fa-tab-auditoria">Auditoría</a></li></ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="fa-tab-linea"><div class="fa-timeline">
                        @forelse($detalleFlujo['segmentos'] as $evento)<div class="fa-event {{ $evento['semaforo'] }}"><div class="fa-event-icon"><i class="fa {{ $evento['icono'] }}" style="font-size:.6rem;"></i></div><div class="fa-event-body"><div class="fa-event-top"><div class="fa-event-title">{{ $evento['etapa'] }} @if($evento['visita'] > 1)<span class="fa-pill neutral">Visita {{ $evento['visita'] }}</span>@endif</div><div class="fa-event-time">{{ $evento['duracion'] }}</div></div><div class="fa-event-meta"><span><i class="fa fa-sign-in"></i> {{ $evento['entrada'] }}</span><span><i class="fa fa-sign-out"></i> {{ $evento['salida'] ?: 'En curso' }}</span><span><i class="fa fa-user"></i> {{ $evento['usuario'] }}</span><span>{{ $evento['estado'] }}</span><span class="fa-pill {{ $evento['semaforo'] }}">{{ ucfirst($evento['semaforo']) }}</span></div><div class="fa-event-note">{{ $evento['observacion'] }} · Dato {{ $evento['calidad'] }}</div></div></div>@empty<div class="fa-empty">Este flujo no tiene eventos históricos.</div>@endforelse
                    </div></div>
                    <div class="tab-pane fade" id="fa-tab-documentos">
                        <div class="fa-doc-section"><h4>Ofertas ({{ count($detalleFlujo['ofertas']) }})</h4><div class="fa-table-wrap"><table class="fa-table"><thead><tr><th>ID</th><th>Creación</th><th>Último cambio</th><th>Teleasesor / creador</th><th>Asesor</th><th>Productos</th><th>Descuento</th><th>Total</th></tr></thead><tbody>@forelse($detalleFlujo['ofertas'] as $item)<tr><td>#{{ $item['id'] }}</td><td>{{ $item['created_at'] }}</td><td>{{ $item['updated_at'] }}</td><td>{{ $item['usuario'] ?: '—' }}</td><td>{{ $item['asesor'] ?: '—' }}</td><td>{{ $item['productos'] }}</td><td>L. {{ number_format($item['monto_descuento'],2) }} ({{ $item['porc_descuento'] }}%)</td><td>L. {{ number_format($item['total'],2) }}</td></tr>@empty<tr><td colspan="8">Sin ofertas vinculadas.</td></tr>@endforelse</tbody></table></div></div>
                        <div class="fa-doc-section"><h4>Prefacturas ({{ count($detalleFlujo['prefacturas']) }})</h4><div class="fa-table-wrap"><table class="fa-table"><thead><tr><th>ID</th><th>Estado</th><th>Creación</th><th>Último cambio</th><th>Usuario</th><th>Total</th></tr></thead><tbody>@forelse($detalleFlujo['prefacturas'] as $item)<tr><td>#{{ $item['id'] }}</td><td>{{ $item['estado'] }}</td><td>{{ $item['created_at'] }}</td><td>{{ $item['updated_at'] }}</td><td>{{ $item['usuario'] ?: '—' }}</td><td>L. {{ number_format($item['total'],2) }}</td></tr>@empty<tr><td colspan="6">Sin prefacturas vinculadas.</td></tr>@endforelse</tbody></table></div></div>
                        <div class="fa-doc-section"><h4>Facturas ({{ count($detalleFlujo['facturas']) }})</h4><div class="fa-table-wrap"><table class="fa-table"><thead><tr><th>ID</th><th>Número</th><th>Emisión</th><th>Vencimiento</th><th>Usuario</th><th>Total</th><th>Saldo</th><th>Primer pago</th><th>Último pago</th><th>Pagos</th><th>Días a pago total</th><th>Clasificación</th></tr></thead><tbody>@forelse($detalleFlujo['facturas'] as $item)<tr><td>#{{ $item['id'] }}</td><td>{{ $item['numero_factura'] }}</td><td>{{ $item['fecha_emision'] }}</td><td>{{ $item['fecha_vencimiento'] }}</td><td>{{ $item['usuario'] ?: '—' }}</td><td>L. {{ number_format($item['total'],2) }}</td><td>L. {{ number_format($item['saldo'] ?? $item['total'],2) }}</td><td>{{ $item['primer_pago'] ?: 'Pendiente' }}</td><td>{{ $item['ultimo_pago'] ?: 'Pendiente' }}</td><td>{{ $item['cantidad_pagos'] }}</td><td>{{ $item['dias_pago_total'] === null ? 'Pendiente' : $item['dias_pago_total'].' días' }}</td><td><span class="fa-pill {{ str_contains(strtolower($item['clasificacion_cobro']), 'crítico') ? 'critico' : (str_contains(strtolower($item['clasificacion_cobro']), 'atrasado') ? 'advertencia' : 'normal') }}">{{ $item['clasificacion_cobro'] }}</span></td></tr>@empty<tr><td colspan="12">Sin facturas vinculadas.</td></tr>@endforelse</tbody></table></div></div>
                    </div>
                    <div class="tab-pane fade" id="fa-tab-logistica">
                        <div class="fa-doc-section"><h4>Logística y entregas</h4><div class="fa-table-wrap"><table class="fa-table"><thead><tr><th>Factura</th><th>Equipo</th><th>Programada</th><th>Salida</th><th>Llegada</th><th>Entrega real</th><th>Estado</th><th>Observaciones</th></tr></thead><tbody>@forelse($detalleFlujo['entregas'] as $item)<tr><td>#{{ $item['factura_id'] }}</td><td>{{ $item['nombre_equipo'] ?: 'Sin asignar' }}</td><td>{{ $item['fecha_programada'] ?: '—' }}</td><td>{{ $item['hora_salida'] ?: '—' }}</td><td>{{ $item['hora_llegada'] ?: '—' }}</td><td>{{ $item['fecha_entrega_real'] ?: 'Pendiente' }}</td><td>{{ $item['estado_entrega'] }}</td><td>{{ $item['motivo_anulacion'] ?: ($item['observaciones'] ?: '—') }}</td></tr>@empty<tr><td colspan="8">Sin distribución logística registrada.</td></tr>@endforelse</tbody></table></div></div>
                        <div class="fa-doc-section"><h4>Pagos registrados</h4><div class="fa-table-wrap"><table class="fa-table"><thead><tr><th>Factura</th><th>Fecha pago</th><th>Fecha/hora registro</th><th>Recibo</th><th>Usuario</th><th>Monto</th><th>Comentario</th></tr></thead><tbody>@forelse($detalleFlujo['pagos'] as $item)<tr><td>#{{ $item['factura_id'] }}</td><td>{{ $item['fecha_pago'] }}</td><td>{{ $item['created_at'] }}</td><td>{{ $item['numero_recibo'] ?: '—' }}</td><td>{{ $item['usuario'] ?: '—' }}</td><td>L. {{ number_format($item['monto_abonado'],2) }}</td><td>{{ $item['comentario'] ?: '—' }}</td></tr>@empty<tr><td colspan="7">Sin pagos vinculados.</td></tr>@endforelse</tbody></table></div></div>
                    </div>
                    <div class="tab-pane fade" id="fa-tab-codigos"><div class="fa-doc-section"><h4>Autorizaciones y solicitudes relacionadas al flujo</h4><div class="fa-table-wrap"><table class="fa-table"><thead><tr><th>Solicitud</th><th>Tipo</th><th>Solicitante</th><th>Fecha/hora</th><th>Resolución</th><th>Espera</th><th>Estado</th></tr></thead><tbody>@forelse($detalleFlujo['codigos'] as $item)<tr><td>#{{ $item['id'] }}</td><td>{{ $item['tipo_tramite'] }}</td><td>{{ $item['usuario'] ?: '—' }}</td><td>{{ $item['created_at'] }}</td><td>{{ $item['fecha_utilizacion'] ?: 'Pendiente' }}</td><td>{{ $item['espera'] }}</td><td>{{ $item['estado_codigo_id'] == 2 ? 'Utilizado' : 'Pendiente / no utilizado' }}</td></tr>@empty<tr><td colspan="7">Este flujo no solicitó códigos.</td></tr>@endforelse</tbody></table></div></div></div>
                    <div class="tab-pane fade" id="fa-tab-auditoria"><div class="fa-doc-section"><h4>Bitácora inmutable ({{ count($detalleFlujo['auditoria']) }} eventos)</h4><div class="fa-table-wrap"><table class="fa-table"><thead><tr><th>Evento</th><th>Fecha/hora</th><th>Etapa</th><th>Entidad</th><th>Acción</th><th>Estado anterior</th><th>Estado nuevo</th><th>Usuario</th><th>Observación</th></tr></thead><tbody>@forelse($detalleFlujo['auditoria'] as $item)<tr><td>#{{ $item['id'] }}</td><td>{{ $item['created_at'] }}</td><td>{{ $item['etapa'] ?: '—' }}</td><td>{{ $item['entidad_tipo'] }} #{{ $item['entidad_id'] }}</td><td>{{ $item['accion'] }}</td><td>{{ $item['estado_anterior'] ?: '—' }}</td><td>{{ $item['estado_nuevo'] ?: '—' }}</td><td>{{ $item['usuario'] ?: '—' }}</td><td>{{ $item['observacion'] ?: '—' }}</td></tr>@empty<tr><td colspan="9">No hay auditoría estructurada para este flujo.</td></tr>@endforelse</tbody></table></div></div></div>
                </div>
            </div>
            <div class="modal-footer"><small class="text-muted mr-auto">Los eventos históricos nunca se alteran desde esta vista.</small><button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cerrar</button></div>
            @endif
        </div></div>
    </div>

    @push('scripts')
    <script>
    (function () {
        var charts = {};
        var initialData = {
            etapas: @json($metricasEtapa),
            evolucion: @json($evolucion),
            kpis: @json($kpis),
            reprocesos: @json(array_map(fn ($item) => ['etapa' => $item['etapa'], 'cantidad' => $item['reprocesos']], $metricasEtapa))
        };
        function minutos(valor) {
            valor = Number(valor || 0);
            if (valor < 60) return valor + ' min';
            if (valor < 1440) return Math.floor(valor / 60) + ' h ' + (valor % 60) + ' min';
            return Math.floor(valor / 1440) + ' d ' + Math.floor((valor % 1440) / 60) + ' h';
        }
        function reemplazarChart(id, opciones) {
            if (!window.ApexCharts || !document.getElementById(id)) return;
            if (charts[id]) charts[id].destroy();
            charts[id] = new ApexCharts(document.getElementById(id), opciones);
            charts[id].render();
        }
        function renderCharts(data) {
            var etapas = data.etapas || [];
            var base = { chart:{toolbar:{show:false},fontFamily:'inherit'}, dataLabels:{enabled:false}, grid:{borderColor:'#edf0f2'}, noData:{text:'Sin datos'}, tooltip:{theme:'light'} };
            reemplazarChart('chart-tiempo-etapa', $.extend(true, {}, base, {
                chart:{type:'bar',height:275,events:{dataPointSelection:function(e,cfg,opts){var etapa=etapas[opts.dataPointIndex];if(etapa){@this.set('filtroEtapa',String(etapa.tipo_tramite_id));@this.call('aplicarFiltros');}}}},
                series:[{name:'Promedio',data:etapas.map(function(x){return x.promedio_minutos;})}], colors:['#e67e22'],
                plotOptions:{bar:{horizontal:true,borderRadius:2,barHeight:'55%'}}, xaxis:{categories:etapas.map(function(x){return x.etapa;}),labels:{formatter:minutos}}, tooltip:{y:{formatter:minutos}}
            }));
            reemplazarChart('chart-evolucion', $.extend(true, {}, base, {
                chart:{type:'area',height:275}, series:[{name:'Tiempo promedio',data:(data.evolucion||[]).map(function(x){return x.promedio_minutos;})}],
                colors:['#218c5a'], stroke:{width:2,curve:'smooth'}, fill:{type:'solid',opacity:.12}, xaxis:{categories:(data.evolucion||[]).map(function(x){return x.fecha;})}, yaxis:{labels:{formatter:minutos}}, tooltip:{y:{formatter:minutos}}
            }));
            reemplazarChart('chart-reprocesos', $.extend(true, {}, base, {
                chart:{type:'bar',height:275}, series:[{name:'Reprocesos',data:(data.reprocesos||[]).map(function(x){return x.cantidad;})}], colors:['#c9413a'],
                plotOptions:{bar:{borderRadius:2,columnWidth:'48%'}}, xaxis:{categories:(data.reprocesos||[]).map(function(x){return x.etapa;}),labels:{rotate:-35}}
            }));
            reemplazarChart('chart-volumen', $.extend(true, {}, base, {
                chart:{type:'bar',height:275}, series:[{name:'Procesos',data:etapas.map(function(x){return x.procesos;})}], colors:['#527a95'],
                plotOptions:{bar:{borderRadius:2,columnWidth:'45%'}}, xaxis:{categories:etapas.map(function(x){return x.etapa;}),labels:{rotate:-35}}
            }));
            reemplazarChart('chart-distribucion', $.extend(true, {}, base, {
                chart:{type:'donut',height:275}, series:etapas.map(function(x){return x.promedio_minutos;}), labels:etapas.map(function(x){return x.etapa;}),
                colors:['#e67e22','#2e86ab','#218c5a','#d89b16','#82589f','#c9413a','#527a95','#7f8c8d'], legend:{position:'bottom',fontSize:'11px'}, tooltip:{y:{formatter:minutos}}
            }));
            var total=Number((data.kpis||{}).total||0), afectados=Number((data.kpis||{}).flujos_con_codigo||0);
            reemplazarChart('chart-codigos', $.extend(true, {}, base, {
                chart:{type:'donut',height:275}, series:[afectados,Math.max(0,total-afectados)], labels:['Con solicitud','Sin solicitud'], colors:['#c9413a','#dfe5e9'],
                legend:{position:'bottom'}, plotOptions:{pie:{donut:{size:'68%',labels:{show:true,total:{show:true,label:'Afectados',formatter:function(){return total?Math.round(afectados/total*100)+'%':'0%';}}}}}}
            }));
        }
        document.addEventListener('DOMContentLoaded', function () { renderCharts(initialData); });
        window.addEventListener('flujo-analytics-updated', function (event) { setTimeout(function(){ renderCharts(event.detail); }, 50); });
        window.addEventListener('flujo-analytics-detail-ready', function () { setTimeout(function(){ $('#modalDetalleFlujoAnalytics').modal('show'); }, 50); });
        window.addEventListener('flujo-thresholds-saved', function () { $('#modalUmbralesFlujo').modal('hide'); if(window.toastr) toastr.success('Semáforos actualizados'); });
        window.addEventListener('flujo-analytics-error', function (event) { if(window.Swal) Swal.fire({icon:'error',title:'No disponible',text:event.detail.mensaje}); });
    })();
    </script>
    @endpush
</div>