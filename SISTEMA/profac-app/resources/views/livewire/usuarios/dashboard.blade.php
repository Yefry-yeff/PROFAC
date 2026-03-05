<div>
    {{-- ══════════════════════════════════════════════════════════════
         PANEL DE CONFIGURACIÓN DE WIDGETS
    ══════════════════════════════════════════════════════════════ --}}
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
                    <div class="d-flex flex-wrap" style="gap:8px;">
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         FILA 1 — STAT CARDS
    ══════════════════════════════════════════════════════════════ --}}
    <div class="row">

        {{-- Usuarios Activos --}}
        @if($this->isVisible('usuarios_activos'))
        <div class="col-lg-3 col-md-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title" style="border-top: 3px solid #1ab394;">
                    <span class="label label-success pull-right">Usuarios</span>
                    <h5>Usuarios Activos</h5>
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
        </div>
        @endif

        {{-- Total Facturas del Mes --}}
        @if($this->isVisible('ventas_mes'))
        <div class="col-lg-3 col-md-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title" style="border-top: 3px solid #1c84c6;">
                    <span class="label label-info pull-right">
                        {{ now()->format('M Y') }}
                    </span>
                    <h5>Facturas del Mes</h5>
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

        <div class="col-lg-3 col-md-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title" style="border-top: 3px solid #23c6c8;">
                    <span class="label pull-right" style="background:#23c6c8;">Monto</span>
                    <h5>Ventas del Mes</h5>
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
        @endif

        {{-- Mejor Vendedor mini card --}}
        @if($this->isVisible('mejor_vendedor') && !empty($mejorVendedor))
        <div class="col-lg-3 col-md-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title" style="border-top: 3px solid #f8ac59;">
                    <span class="label label-warning pull-right">Top</span>
                    <h5><i class="fa fa-trophy text-warning"></i> Mejor Vendedor</h5>
                </div>
                <div class="ibox-content">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:42px;height:42px;background:#fff8ed;border:2px solid #f8ac59;flex-shrink:0;">
                            <i class="fa fa-user" style="color:#f8ac59;"></i>
                        </div>
                        <div style="margin-left:10px; overflow:hidden;">
                            <div style="font-weight:700; font-size:13px; color:#333; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $mejorVendedor->nombre_vendedor ?? 'N/A' }}
                            </div>
                            <div style="font-size:12px; color:#888;">
                                {{ $mejorVendedor->cnt ?? 0 }} facturas &bull;
                                L. {{ number_format($mejorVendedor->monto ?? 0, 0) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ══════════════════════════════════════════════════════════════
         FILA 2 — GRÁFICO VENTAS + MEJOR CLIENTE
    ══════════════════════════════════════════════════════════════ --}}
    @if($this->isVisible('grafico_ventas') || $this->isVisible('mejor_cliente'))
    <div class="row">

        {{-- Gráfico Ventas --}}
        @if($this->isVisible('grafico_ventas'))
        <div class="{{ $this->isVisible('mejor_cliente') ? 'col-lg-8' : 'col-lg-12' }}">
            <div class="ibox float-e-margins">
                <div class="ibox-title" style="border-top: 3px solid #6f42c1;">
                    <h5><i class="fa fa-line-chart" style="color:#6f42c1; margin-right:6px;"></i> Ventas Últimos 6 Meses</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div wire:ignore id="chart-ventas-mes" style="min-height:280px;"></div>
                </div>
            </div>
        </div>
        @endif

        {{-- Mejor Cliente --}}
        @if($this->isVisible('mejor_cliente'))
        <div class="{{ $this->isVisible('grafico_ventas') ? 'col-lg-4' : 'col-lg-4 col-md-6' }}">
            <div class="ibox float-e-margins">
                <div class="ibox-title" style="border-top: 3px solid #ed5565;">
                    <span class="label label-danger pull-right">Top</span>
                    <h5><i class="fa fa-star text-danger"></i> Cliente Top del Mes</h5>
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
                                <h3 style="color:#ed5565; margin:0; font-weight:700;">
                                    {{ $mejorCliente->cnt ?? 0 }}
                                </h3>
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

            {{-- Mejor Vendedor side card --}}
            @if($this->isVisible('mejor_vendedor'))
            <div class="ibox float-e-margins">
                <div class="ibox-title" style="border-top: 3px solid #f8ac59;">
                    <span class="label label-warning pull-right">Top</span>
                    <h5><i class="fa fa-trophy text-warning"></i> Mejor Vendedor del Mes</h5>
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
                                <h3 style="color:#f8ac59; margin:0; font-weight:700;">
                                    {{ $mejorVendedor->cnt ?? 0 }}
                                </h3>
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
                    <div class="text-center text-muted" style="padding:20px 0;">
                        <i class="fa fa-inbox" style="font-size:28px; opacity:.3;"></i>
                        <p style="margin-top:8px; font-size:13px;">Sin datos este mes</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>
        @endif

    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════
         FILA 3 — ÚLTIMAS VENTAS
    ══════════════════════════════════════════════════════════════ --}}
    @if($this->isVisible('ultimas_ventas'))
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title" style="border-top: 3px solid #23c6c8;">
                    <h5>
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
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════
         FILA 4 — USUARIOS Y ROLES
    ══════════════════════════════════════════════════════════════ --}}
    @if($this->isVisible('usuarios_roles'))
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title" style="border-top: 3px solid #2f4050;">
                    <h5>
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
        </div>
    </div>
    @endif

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
