<div>
    {{-- ══════════════════════════════════════════════════════════════
         PANEL DE CONFIGURACIÓN DE WIDGETS
    ══════════════════════════════════════════════════════════════ --}}
    @if($showConfigPanel)
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox" style="margin-bottom:14px;">
                <div class="ibox-title" style="padding:10px 15px; cursor:pointer;"
                     data-toggle="collapse" data-target="#widgetConfigPanel" aria-expanded="false">
                    <h5 style="margin:0; font-size:13px; color:#676a6c;">
                        <i class="fa fa-sliders" style="margin-right:6px;"></i>
                        Configurar Widgets
                    </h5>
                    <div class="ibox-tools">
                        <a class="collapse-link"><i class="fa fa-chevron-down"></i></a>
                    </div>
                </div>
                <div class="ibox-content collapse" id="widgetConfigPanel" style="padding:12px 15px;">
                    <p class="text-muted" style="font-size:12px; margin-bottom:10px;">
                        Activa o desactiva los widgets que deseas ver en tu dashboard.
                    </p>
                    <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
                        @foreach($widgetConfig as $key => $cfg)
                            @if($this->canSeeWidget($key))
                                <button
                                    wire:click="toggleWidget('{{ $key }}')"
                                    class="btn btn-sm {{ isset($widgetPrefs[$key]) && $widgetPrefs[$key] ? 'btn-primary' : 'btn-default' }}"
                                    style="font-size:12px; border-radius:20px; padding:4px 14px;">
                                    <i class="fa {{ $cfg['icon'] }}" style="margin-right:4px;"></i>
                                    {{ $cfg['title'] }}
                                    @if(isset($widgetPrefs[$key]) && $widgetPrefs[$key])
                                        <i class="fa fa-check" style="margin-left:4px; font-size:10px;"></i>
                                    @endif
                                </button>
                            @endif
                        @endforeach
                        @if(optional(Auth::user()->rol)->nombre === 'Administrador')
                        <a href="{{ url('/usuarios/widgets') }}"
                           class="btn btn-sm btn-warning"
                           style="font-size:12px; border-radius:20px; padding:4px 14px; margin-left:8px;">
                            <i class="fa fa-cogs" style="margin-right:4px;"></i>
                            Administrar Widgets
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════
         WIDGETS — Contenedor único con drag-and-drop por título
    ══════════════════════════════════════════════════════════════ --}}
    <style>
        .drag-handle { cursor: grab !important; user-select: none; }
        .drag-handle:active { cursor: grabbing !important; }
        .sortable-ghost  { opacity: .35; background: #e8f8f5 !important; }
        .sortable-chosen { box-shadow: 0 4px 16px rgba(26,179,148,.25) !important; }
    </style>

    <div class="row" id="dashboard-sortable">

        @foreach($widgetConfig as $key => $cfg)
        @if($this->isVisible($key))
        <div class="sortable-widget {{ $colClasses[$key] ?? 'col-lg-12' }}" data-key="{{ $key }}">

            {{-- ────────────────────────────── usuarios_activos ── --}}
            @if($key === 'usuarios_activos')
            <div class="ibox float-e-margins">
                <div class="ibox-title drag-handle" style="border-top: 3px solid #1ab394;">
                    <span class="label label-success pull-right">Usuarios</span>
                    <h5>
                        <i class="fa fa-arrows" style="color:#ccc; margin-right:6px; font-size:11px;" title="Arrastrar"></i>
                        Usuarios Activos
                    </h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins" style="color:#1ab394; font-size:36px;">
                        {{ $totalUsuariosActivos ?? 0 }}
                    </h1>
                    <small class="text-muted">de {{ $totalUsuarios ?? 0 }} registrados</small>
                    <div class="stat-bar" style="margin-top:8px; height:4px; background:#e7eaec; border-radius:2px;">
                        @php
                            $pct = ($totalUsuarios ?? 0) > 0
                                ? round((($totalUsuariosActivos ?? 0) / $totalUsuarios) * 100)
                                : 0;
                        @endphp
                        <div style="width:{{ $pct }}%; height:100%; background:#1ab394; border-radius:2px;"></div>
                    </div>
                    <small class="text-muted" style="font-size:11px;">{{ $pct }}% activos</small>
                </div>
            </div>

            {{-- ────────────────────────────────── ventas_mes ── --}}
            @elseif($key === 'ventas_mes')
            <div class="row" style="margin:0 -8px;">
                <div class="col-6" style="padding:0 8px;">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title drag-handle" style="border-top: 3px solid #1c84c6;">
                            <span class="label label-info pull-right">{{ now()->format('M Y') }}</span>
                            <h5>
                                <i class="fa fa-arrows" style="color:#ccc; margin-right:6px; font-size:11px;" title="Arrastrar"></i>
                                Facturas del Mes
                            </h5>
                        </div>
                        <div class="ibox-content">
                            <h1 class="no-margins" style="color:#1c84c6; font-size:36px;">
                                {{ number_format($ventasMesCount ?? 0) }}
                            </h1>
                            <small class="text-muted">facturas emitidas</small>
                            <div class="m-t-sm">
                                <small class="text-navy">
                                    <i class="fa fa-level-up"></i>
                                    Total: L. {{ number_format($ventasMesTotal ?? 0, 2) }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6" style="padding:0 8px;">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title drag-handle" style="border-top: 3px solid #23c6c8;">
                            <span class="label pull-right" style="background:#23c6c8;">Monto</span>
                            <h5>
                                <i class="fa fa-arrows" style="color:#ccc; margin-right:6px; font-size:11px;" title="Arrastrar"></i>
                                Ventas del Mes
                            </h5>
                        </div>
                        <div class="ibox-content">
                            <h4 class="no-margins" style="color:#23c6c8; font-size:24px; font-weight:700;">
                                L. {{ number_format($ventasMesTotal ?? 0, 0) }}
                            </h4>
                            <small class="text-muted">ingreso total del mes</small>
                            <div class="m-t-sm">
                                <small class="text-muted">
                                    <i class="fa fa-calendar"></i>
                                    {{ now()->format('F Y') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─────────────────────────────── mejor_vendedor ── --}}
            @elseif($key === 'mejor_vendedor')
            <div class="ibox float-e-margins">
                <div class="ibox-title drag-handle" style="border-top: 3px solid #f8ac59;">
                    <span class="label label-warning pull-right">Top</span>
                    <h5>
                        <i class="fa fa-arrows" style="color:#ccc; margin-right:6px; font-size:11px;" title="Arrastrar"></i>
                        <i class="fa fa-trophy text-warning"></i> Mejor Vendedor del Mes
                    </h5>
                </div>
                <div class="ibox-content">
                    @if(!empty($mejorVendedor))
                    <div class="text-center" style="padding:8px 0;">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center"
                             style="width:52px;height:52px;background:#fff8ed;border:3px solid #f8ac59;">
                            <i class="fa fa-user" style="color:#f8ac59; font-size:20px;"></i>
                        </div>
                        <h4 style="margin-top:8px; font-size:15px; color:#333;">
                            {{ $mejorVendedor->nombre_vendedor ?? 'N/A' }}
                        </h4>
                        <div class="row" style="margin-top:12px;">
                            <div class="col-6 text-center" style="border-right:1px solid #eee;">
                                <h3 style="color:#f8ac59; margin:0; font-weight:700;">{{ $mejorVendedor->cnt ?? 0 }}</h3>
                                <small class="text-muted">facturas</small>
                            </div>
                            <div class="col-6 text-center">
                                <h5 style="color:#f8ac59; margin:0; font-weight:700;">
                                    L. {{ number_format($mejorVendedor->monto ?? 0, 0) }}
                                </h5>
                                <small class="text-muted">monto</small>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center text-muted" style="padding:30px 0;">
                        <i class="fa fa-inbox" style="font-size:28px; opacity:.3;"></i>
                        <p style="margin-top:8px; font-size:13px;">Sin datos este mes</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ──────────────────────────────── mejor_cliente ── --}}
            @elseif($key === 'mejor_cliente')
            <div class="ibox float-e-margins">
                <div class="ibox-title drag-handle" style="border-top: 3px solid #ed5565;">
                    <span class="label label-danger pull-right">Top</span>
                    <h5>
                        <i class="fa fa-arrows" style="color:#ccc; margin-right:6px; font-size:11px;" title="Arrastrar"></i>
                        <i class="fa fa-star text-danger"></i> Cliente Top del Mes
                    </h5>
                </div>
                <div class="ibox-content">
                    @if(!empty($mejorCliente))
                    <div class="text-center" style="padding:12px 0;">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center"
                             style="width:60px;height:60px;background:#fde8ea;border:3px solid #ed5565;">
                            <i class="fa fa-building-o" style="color:#ed5565; font-size:24px;"></i>
                        </div>
                        <h4 style="margin-top:10px; font-size:15px; color:#333;">
                            {{ $mejorCliente->nombre_cliente ?? 'N/A' }}
                        </h4>
                        <div class="row" style="margin-top:16px;">
                            <div class="col-6 text-center" style="border-right:1px solid #eee;">
                                <h3 style="color:#ed5565; margin:0; font-weight:700;">{{ $mejorCliente->cnt ?? 0 }}</h3>
                                <small class="text-muted">compras</small>
                            </div>
                            <div class="col-6 text-center">
                                <h5 style="color:#ed5565; margin:0; font-weight:700;">
                                    L. {{ number_format($mejorCliente->monto ?? 0, 0) }}
                                </h5>
                                <small class="text-muted">monto total</small>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center text-muted" style="padding:30px 0;">
                        <i class="fa fa-inbox" style="font-size:30px; opacity:.3;"></i>
                        <p style="margin-top:8px; font-size:13px;">Sin datos este mes</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ─────────────────────────────── grafico_ventas ── --}}
            @elseif($key === 'grafico_ventas')
            <div class="ibox float-e-margins">
                <div class="ibox-title drag-handle" style="border-top: 3px solid #6f42c1;">
                    <h5>
                        <i class="fa fa-arrows" style="color:#ccc; margin-right:6px; font-size:11px;" title="Arrastrar"></i>
                        <i class="fa fa-line-chart" style="color:#6f42c1; margin-right:6px;"></i>
                        Ventas Últimos 6 Meses
                    </h5>
                    <div class="ibox-tools">
                        <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div wire:ignore id="chart-ventas-mes" style="min-height:280px;"></div>
                </div>
            </div>

            {{-- ─────────────────────────────── ultimas_ventas ── --}}
            @elseif($key === 'ultimas_ventas')
            <div class="ibox float-e-margins">
                <div class="ibox-title drag-handle" style="border-top: 3px solid #23c6c8;">
                    <h5>
                        <i class="fa fa-arrows" style="color:#ccc; margin-right:6px; font-size:11px;" title="Arrastrar"></i>
                        <i class="fa fa-list-alt" style="color:#23c6c8; margin-right:6px;"></i>
                        Últimas Ventas
                    </h5>
                    <div class="ibox-tools">
                        <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </div>
                </div>
                <div class="ibox-content" style="padding:0;">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" style="margin:0; font-size:13px;">
                            <thead style="background:#f5f5f5;">
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>N° Factura</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th style="text-align:right;">Total</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $vendedoresCache = []; @endphp
                                @forelse($ultimasVentas ?? [] as $idx => $f)
                                @php
                                    if ($f->vendedor && !isset($vendedoresCache[$f->vendedor])) {
                                        $u = DB::table('users')->select('name')->find($f->vendedor);
                                        $vendedoresCache[$f->vendedor] = $u ? $u->name : ('ID #'.$f->vendedor);
                                    }
                                @endphp
                                <tr>
                                    <td class="text-muted" style="font-size:11px;">{{ $idx + 1 }}</td>
                                    <td>
                                        <span class="badge badge-light" style="border:1px solid #dee2e6; color:#555;">
                                            {{ $f->numero_factura ?? 'S/N' }}
                                        </span>
                                    </td>
                                    <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                        {{ $f->nombre_cliente ?? 'N/A' }}
                                    </td>
                                    <td>{{ $f->vendedor ? ($vendedoresCache[$f->vendedor] ?? '—') : '—' }}</td>
                                    <td style="text-align:right; font-weight:600; color:#1c84c6;">
                                        L. {{ number_format($f->total, 2) }}
                                    </td>
                                    <td class="text-muted" style="font-size:12px;">
                                        {{ \Carbon\Carbon::parse($f->fecha_emision)->format('d/m/Y') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted" style="padding:20px;">
                                        <i class="fa fa-inbox" style="font-size:20px; opacity:.3;"></i>
                                        Sin facturas registradas
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ─────────────────────────────── usuarios_roles ── --}}
            @elseif($key === 'usuarios_roles')
            <div class="ibox float-e-margins">
                <div class="ibox-title drag-handle" style="border-top: 3px solid #2f4050;">
                    <h5>
                        <i class="fa fa-arrows" style="color:#ccc; margin-right:6px; font-size:11px;" title="Arrastrar"></i>
                        <i class="fa fa-id-card" style="color:#2f4050; margin-right:6px;"></i>
                        Usuarios y Roles
                    </h5>
                    <div class="ibox-tools">
                        <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </div>
                </div>
                <div class="ibox-content" style="padding:0;">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" style="margin:0; font-size:13px;">
                            <thead style="background:#f5f5f5;">
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Registrado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($usuariosRoles ?? [] as $idx => $u)
                                <tr>
                                    <td class="text-muted" style="font-size:11px;">{{ $u->id }}</td>
                                    <td style="font-weight:500;">{{ $u->name }}</td>
                                    <td class="text-muted" style="font-size:12px;">{{ $u->email }}</td>
                                    <td>
                                        @if($u->rol_nombre)
                                        <span class="label" style="background:#1ab394; color:#fff; font-size:11px;">
                                            {{ $u->rol_nombre }}
                                        </span>
                                        @else
                                        <span class="label label-default" style="font-size:11px;">Sin rol</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($u->estado_id == 1)
                                        <span class="label label-success" style="font-size:11px;">Activo</span>
                                        @else
                                        <span class="label label-danger" style="font-size:11px;">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-muted" style="font-size:12px;">
                                        {{ $u->created_at ? \Carbon\Carbon::parse($u->created_at)->format('d/m/Y') : '—' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted" style="padding:20px;">
                                        Sin usuarios registrados
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ───────────────────────────────────── stock_bajo ── --}}
            @elseif($key === 'stock_bajo')
            <div class="ibox float-e-margins">
                <div class="ibox-title drag-handle" style="border-top: 3px solid #e74c3c;">
                    <h5>
                        <i class="fa fa-arrows" style="color:#ccc; margin-right:6px; font-size:11px;" title="Arrastrar"></i>
                        <i class="fa fa-exclamation-triangle" style="color:#e74c3c; margin-right:6px;"></i>
                        Productos con Stock Bajo
                        <small class="text-muted" style="font-size:11px; margin-left:8px;">
                            (stock ≤ {{ $stockMinimo ?? 10 }} unidades)
                        </small>
                    </h5>
                    <div class="ibox-tools">
                        <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </div>
                </div>
                <div class="ibox-content" style="padding:0;">
                    <div class="table-responsive">
                        <table class="table table-hover" style="margin:0; font-size:13px;">
                            <thead style="background:#fdf2f2;">
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Producto</th>
                                    <th>Código</th>
                                    <th style="text-align:center; width:120px;">Stock Actual</th>
                                    <th style="width:140px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productosStockBajo ?? [] as $idx => $p)
                                @php
                                    $stock = (int) $p->stock_actual;
                                    $minS  = $stockMinimo ?? 10;
                                    $pct2  = $minS > 0 ? max(0, min(100, round(($stock / $minS) * 100))) : 0;
                                    $barColor = $stock <= 0 ? '#e74c3c' : ($stock <= ($minS * 0.5) ? '#f8ac59' : '#1ab394');
                                @endphp
                                <tr>
                                    <td class="text-muted" style="font-size:11px;">{{ $idx + 1 }}</td>
                                    <td style="font-weight:500; color:#333;">{{ $p->nombre }}</td>
                                    <td class="text-muted" style="font-size:12px;">{{ $p->codigo_barra ?? '—' }}</td>
                                    <td style="text-align:center;">
                                        <span style="font-size:16px; font-weight:700; color:{{ $barColor }};">
                                            {{ $stock }}
                                        </span>
                                        <div style="height:4px; background:#f0f0f0; border-radius:2px; margin-top:4px;">
                                            <div style="width:{{ $pct2 }}%; height:100%; background:{{ $barColor }}; border-radius:2px;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($stock <= 0)
                                        <span class="label label-danger" style="font-size:11px;">Sin stock</span>
                                        @elseif($stock <= ($minS * 0.5))
                                        <span class="label label-warning" style="font-size:11px;">Crítico</span>
                                        @else
                                        <span class="label" style="background:#f8ac59; color:#fff; font-size:11px;">Bajo</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center" style="padding:20px; color:#1ab394;">
                                        <i class="fa fa-check-circle" style="font-size:20px;"></i>
                                        <p style="margin-top:6px; font-size:13px;">¡Todos los productos tienen suficiente stock!</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @endif

        </div>
        @endif
        @endforeach

    </div>

    {{-- ── SortableJS drag-and-drop ──────────────────────────────── --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
    (function () {
        function initDashboardSortable() {
            var container = document.getElementById('dashboard-sortable');
            if (!container || container.dataset.sortableReady) return;
            container.dataset.sortableReady = '1';
            Sortable.create(container, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function () {
                    var keys = Array.from(
                        container.querySelectorAll('.sortable-widget[data-key]')
                    ).map(function (el) { return el.getAttribute('data-key'); });
                    var lw = container.closest('[wire\\:id]');
                    if (lw) {
                        window.livewire.find(lw.getAttribute('wire:id'))
                            .call('saveWidgetOrder', keys);
                    }
                }
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () { setTimeout(initDashboardSortable, 300); });
        } else {
            setTimeout(initDashboardSortable, 300);
        }
        document.addEventListener('livewire:load', function () { setTimeout(initDashboardSortable, 400); });
    })();
    </script>
    @endpush



    {{-- ══════════════════════════════════════════════════════════════
         SCRIPTS — ApexCharts Initialization
    ══════════════════════════════════════════════════════════════ --}}
    @if($this->isVisible('grafico_ventas'))
    @push('scripts')
    <script>
    (function() {
        var cats   = @json($graficoCategorias ?? []);
        var totals = @json($graficoTotales   ?? []);
        var counts = @json($graficoFacturas  ?? []);

        function initVentasChart() {
            var el = document.getElementById('chart-ventas-mes');
            if (!el || typeof ApexCharts === 'undefined') return;

            var options = {
                chart: {
                    type: 'area',
                    height: 280,
                    toolbar: { show: false },
                    fontFamily: '"open sans","Helvetica Neue",Helvetica,Arial,sans-serif',
                },
                series: [
                    { name: 'Ventas (L.)',  type: 'area', data: totals },
                    { name: 'N° Facturas', type: 'line', data: counts },
                ],
                xaxis: {
                    categories: cats,
                    labels: { style: { fontSize: '12px', colors: '#676a6c' } },
                },
                yaxis: [
                    {
                        title: { text: 'Monto (L.)', style: { color: '#6f42c1' } },
                        labels: {
                            formatter: function(v) {
                                return 'L.' + Number(v).toLocaleString('es-HN', {maximumFractionDigits:0});
                            },
                            style: { colors: '#6f42c1' },
                        },
                    },
                    {
                        opposite: true,
                        title: { text: 'Facturas', style: { color: '#1c84c6' } },
                        labels: {
                            formatter: function(v) { return parseInt(v); },
                            style: { colors: '#1c84c6' },
                        },
                    },
                ],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: [2, 3] },
                fill: {
                    type: ['gradient', 'solid'],
                    gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] },
                },
                colors: ['#6f42c1', '#1c84c6'],
                markers: { size: [4, 5] },
                tooltip: {
                    shared: true,
                    y: [
                        { formatter: function(v) { return 'L. ' + Number(v).toLocaleString('es-HN', {minimumFractionDigits:2}); } },
                        { formatter: function(v) { return parseInt(v) + ' facturas'; } },
                    ],
                },
                legend: { position: 'top', horizontalAlign: 'right' },
                grid: { borderColor: '#f1f1f1' },
            };

            new ApexCharts(el, options).render();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initVentasChart);
        } else {
            setTimeout(initVentasChart, 150);
        }
    })();
    </script>
    @endpush
    @endif

</div>