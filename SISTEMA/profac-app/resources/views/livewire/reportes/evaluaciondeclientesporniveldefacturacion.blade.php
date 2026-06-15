@push('styles')
<style>
:root {
    --ecf-grad:     linear-gradient(135deg, #1565C0 0%, #0D47A1 100%);
    --ecf-blue:     #1565C0;
    --ecf-blue-lt:  #E3F2FD;
    --ecf-danger:   #c0392b;
    --ecf-success:  #1a7a4e;
    --ecf-radius:   8px;
    --ecf-shadow:   0 2px 8px rgba(0,0,0,.10);
}
.ecf-card { border:1px solid #c5d8f7; border-radius:var(--ecf-radius); box-shadow:var(--ecf-shadow); background:#fff; }
.ecf-card-header {
    background:var(--ecf-grad); padding:12px 20px;
    border-radius:var(--ecf-radius) var(--ecf-radius) 0 0;
    display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap;
}
.ecf-card-header h5 { margin:0; color:#fff; font-size:.85rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; display:flex; align-items:center; gap:8px; }
.ecf-card-body { padding:16px 20px; }
.btn-ecf-header {
    background:rgba(255,255,255,.18)!important; color:#fff!important;
    border:1.5px solid rgba(255,255,255,.5)!important; border-radius:5px!important;
    font-weight:600!important; font-size:.78rem; padding:5px 14px; transition:background .18s; white-space:nowrap;
}
.btn-ecf-header:hover { background:rgba(255,255,255,.30)!important; color:#fff!important; }
.btn-ecf-excel { background:rgba(39,174,96,.85)!important; border-color:rgba(255,255,255,.4)!important; }
.btn-ecf-excel:hover { background:rgba(39,174,96,1)!important; }
.ecf-stats { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
.ecf-stat-pill {
    display:flex; align-items:center; gap:7px;
    background:var(--ecf-blue-lt); border:1px solid #bbdefb;
    border-radius:20px; padding:4px 14px 4px 10px; font-size:.78rem; color:#555; font-weight:500;
}
.ecf-stat-pill .stat-num { font-size:.9rem; font-weight:700; color:var(--ecf-blue); }
.ecf-stat-pill.danger  { background:#fef2f2; border-color:#fecaca; }
.ecf-stat-pill.danger  .stat-num { color:var(--ecf-danger); }
.ecf-stat-pill.success { background:#f0fdf4; border-color:#bbf7d0; }
.ecf-stat-pill.success .stat-num { color:var(--ecf-success); }
.ecf-stat-pill.warning { background:#fffbeb; border-color:#fde68a; }
.ecf-stat-pill.warning .stat-num { color:#b45309; }
.ecf-filter-card { border:1px solid #e0eaf8; border-radius:var(--ecf-radius); margin-bottom:16px; }
.ecf-filter-header {
    background:#f0f6ff; padding:8px 16px; border-bottom:1px solid #dce8f8;
    border-radius:var(--ecf-radius) var(--ecf-radius) 0 0;
    display:flex; align-items:center; justify-content:space-between;
    font-size:.80rem; font-weight:700; color:var(--ecf-blue); text-transform:uppercase; letter-spacing:.04em;
}
.ecf-filter-body { padding:14px 16px 6px; }
.ecf-filter-body .form-group { margin-bottom:10px; }
.ecf-filter-body label { font-size:.75rem; font-weight:600; color:#555; margin-bottom:3px; }
.ecf-filter-body .form-control { font-size:.82rem; border-color:#c8d8f0; }
.ecf-filter-body .form-control:focus { border-color:var(--ecf-blue); box-shadow:0 0 0 .15rem rgba(21,101,192,.18); }
.ecf-table thead th {
    background:#e8f0fe; color:#0d3c7a; font-size:.72rem; font-weight:700;
    letter-spacing:.04em; text-transform:uppercase; border-bottom:2px solid #b3cdf8;
    white-space:nowrap; padding:8px 10px; vertical-align:middle;
}
.ecf-table tbody td { font-size:.83rem; vertical-align:middle; padding:7px 10px; }
.ecf-table tbody tr:hover { background:#f0f6ff!important; }
.ecf-table tbody tr.row-atencion { background:#fff5f5; }
.ecf-table tbody tr.row-atencion:hover { background:#ffe8e8!important; }
.badge-estado { background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:.73rem; font-weight:600; padding:3px 8px; border-radius:10px; }
.badge-atencion-si { background:#fef2f2; color:var(--ecf-danger); border:1px solid #fecaca; font-size:.73rem; font-weight:700; padding:3px 8px; border-radius:10px; }
.badge-atencion-no { background:#f0fdf4; color:var(--ecf-success); border:1px solid #bbf7d0; font-size:.73rem; font-weight:600; padding:3px 8px; border-radius:10px; }
.badge-saldo { background:#fff8e1; color:#e65100; border:1px solid #ffe082; font-size:.73rem; font-weight:700; padding:3px 8px; border-radius:10px; }
.factura-code { font-family:monospace; font-size:.80rem; color:#1565C0; font-weight:600; }
.sin-historial { color:#adb5bd; font-size:.78rem; font-style:italic; }
.ecf-pagination { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-top:12px; }
.ecf-pagination .btn { font-size:.80rem; padding:4px 10px; border-radius:5px; }
.ecf-pagination .btn-page { min-width:30px; }
.ecf-pagination .btn-page.active { background:var(--ecf-blue); color:#fff; border-color:var(--ecf-blue); }
.ecf-page-info { font-size:.78rem; color:#6c757d; }
.ecf-chart-card { border:1px solid #dce8f8; border-radius:var(--ecf-radius); overflow:hidden; margin-bottom:16px; }
.ecf-chart-header {
    background:#f0f6ff; padding:7px 14px; border-bottom:1px solid #dce8f8;
    font-size:.76rem; font-weight:700; color:var(--ecf-blue); text-transform:uppercase; letter-spacing:.04em;
    display:flex; align-items:center; justify-content:space-between; gap:7px;
}
.ecf-chart-header .ecf-filter-active {
    font-size:.70rem; font-weight:600; background:var(--ecf-blue); color:#fff;
    border-radius:10px; padding:2px 8px; text-transform:none; letter-spacing:0;
}
.ecf-chart-body { padding:8px; min-height:220px; }
.ecf-chart-clickable .apexcharts-series { cursor:pointer; }
@media(max-width:767px){
    .ecf-card-body { padding:10px; }
    .ecf-card-header { padding:10px 12px; }
}
</style>
@endpush

<div data-ecf-root="1">
    {{-- Encabezado --}}
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-12">
            <h2><i class="fa fa-chart-bar mr-2" style="color:#1565C0"></i>Evaluación de Clientes por Nivel de Facturación</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="#">Reportes</a></li>
                <li class="breadcrumb-item active"><strong>Evaluación de Clientes</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- Gráficas fila 1 --}}
        <div class="row">
            <div class="col-md-6">
                <div class="ecf-chart-card">
                    <div class="ecf-chart-header">
                        <span><i class="fa fa-chart-pie mr-1"></i> Requieren Atención</span>
                        @if($filtRequiereAt !== '')
                            <span class="ecf-filter-active">{{ $filtRequiereAt }}</span>
                        @endif
                    </div>
                    <div class="ecf-chart-body ecf-chart-clickable"><div id="chartAtencion"></div></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="ecf-chart-card">
                    <div class="ecf-chart-header">
                        <span><i class="fa fa-chart-pie mr-1"></i> Por Estado</span>
                        @if($filtEstado !== '')
                            <span class="ecf-filter-active">{{ $filtEstado }}</span>
                        @endif
                    </div>
                    <div class="ecf-chart-body ecf-chart-clickable"><div id="chartEstados"></div></div>
                </div>
            </div>
        </div>

        {{-- Gráficas fila 2 --}}
        <div class="row">
            <div class="col-md-6">
                <div class="ecf-chart-card">
                    <div class="ecf-chart-header">
                        <span><i class="fa fa-chart-pie mr-1"></i> Por Vendedor</span>
                        @if($filtVendedor !== '')
                            <span class="ecf-filter-active">activo</span>
                        @endif
                    </div>
                    <div class="ecf-chart-body ecf-chart-clickable"><div id="chartVendedores"></div></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="ecf-chart-card">
                    <div class="ecf-chart-header">
                        <span><i class="fa fa-chart-pie mr-1"></i> Historial de Facturación</span>
                        @if($filtSinHistorial !== '')
                            <span class="ecf-filter-active">{{ $filtSinHistorial === 'sin' ? 'Sin historial' : 'Con facturación' }}</span>
                        @endif
                    </div>
                    <div class="ecf-chart-body ecf-chart-clickable"><div id="chartHistorial"></div></div>
                </div>
            </div>
        </div>

        {{-- Gráfica fila 3: Top vendedores --}}
        <div class="row">
            <div class="col-12">
                <div class="ecf-chart-card">
                    <div class="ecf-chart-header">
                        <span><i class="fa fa-trophy mr-1"></i> Top Vendedores con Más Clientes sin Atención</span>
                        <small class="text-muted font-weight-normal" style="text-transform:none;letter-spacing:0;">Haz clic en una barra para filtrar</small>
                    </div>
                    <div class="ecf-chart-body ecf-chart-clickable" style="min-height:260px;"><div id="chartTopVend"></div></div>
                </div>
            </div>
        </div>

        {{-- Card principal --}}
        <div class="row">
            <div class="col-12">
                <div class="ecf-card">

                    <div class="ecf-card-header">
                        <h5><i class="fa fa-users"></i> Clientes por Nivel de Facturación</h5>
                        <div class="d-flex" style="gap:8px;">
                            <button type="button" wire:click="limpiarFiltros" class="btn btn-ecf-header">
                                <i class="fa fa-times mr-1"></i> Limpiar filtros
                            </button>
                            <button type="button" wire:click="exportarExcel" wire:loading.attr="disabled" class="btn btn-ecf-header btn-ecf-excel">
                                <span wire:loading.remove wire:target="exportarExcel">
                                    <i class="fa fa-file-excel mr-1"></i> Exportar Excel
                                </span>
                                <span wire:loading wire:target="exportarExcel">
                                    <i class="fa fa-spinner fa-spin mr-1"></i> Generando…
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="ecf-card-body">

                        {{-- Stat pills --}}
                        <div class="ecf-stats">
                            <div class="ecf-stat-pill">
                                <i class="fa fa-users" style="font-size:.78rem;color:var(--ecf-blue)"></i>
                                <span>Total</span>
                                <span class="stat-num">{{ $total }}</span>
                            </div>
                            <div class="ecf-stat-pill danger">
                                <i class="fa fa-exclamation-triangle" style="font-size:.78rem;color:var(--ecf-danger)"></i>
                                <span>Requieren Atención</span>
                                <span class="stat-num">{{ $chartData['atencion']['series'][0] }}</span>
                            </div>
                            <div class="ecf-stat-pill success">
                                <i class="fa fa-check-circle" style="font-size:.78rem;color:var(--ecf-success)"></i>
                                <span>Al día</span>
                                <span class="stat-num">{{ $chartData['atencion']['series'][1] }}</span>
                            </div>
                            <div class="ecf-stat-pill warning">
                                <i class="fa fa-history" style="font-size:.78rem;color:#b45309"></i>
                                <span>Sin Historial</span>
                                <span class="stat-num">{{ $chartData['historial']['series'][1] }}</span>
                            </div>
                        </div>

                        {{-- Filtros --}}
                        <div class="ecf-filter-card">
                            <div class="ecf-filter-header">
                                <span><i class="fa fa-filter mr-1"></i> Filtros de búsqueda</span>
                            </div>
                            <div class="ecf-filter-body">
                                <div class="row">
                                    <div class="col-md-1 col-sm-3">
                                        <div class="form-group">
                                            <label>Código</label>
                                            <input type="number" wire:model.lazy="filtCodigo"
                                                   class="form-control form-control-sm" placeholder="ID">
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-5">
                                        <div class="form-group">
                                            <label>Nombre</label>
                                            <input type="text" wire:model.debounce.400ms="filtNombre"
                                                   class="form-control form-control-sm" placeholder="Buscar nombre…">
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4">
                                        <div class="form-group">
                                            <label>Estado</label>
                                            <select wire:model="filtEstado" class="form-control form-control-sm">
                                                <option value="">Todos</option>
                                                @foreach ($estados as $e)
                                                    <option value="{{ $e->descripcion }}">{{ $e->descripcion }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4">
                                        <div class="form-group">
                                            <label>Vendedor</label>
                                            <select wire:model="filtVendedor" class="form-control form-control-sm">
                                                <option value="">Todos</option>
                                                @foreach ($vendedores as $v)
                                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4">
                                        <div class="form-group">
                                            <label>Req. Atención</label>
                                            <select wire:model="filtRequiereAt" class="form-control form-control-sm">
                                                <option value="">Todos</option>
                                                <option value="Sí">Sí</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-1 col-sm-4">
                                        <div class="form-group">
                                            <label>Mostrar</label>
                                            <select wire:model="porPagina" class="form-control form-control-sm">
                                                <option value="5">5</option>
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2 col-sm-6">
                                        <div class="form-group">
                                            <label>Fecha últ. factura desde</label>
                                            <input type="date" wire:model="filtFechaDesde"
                                                   class="form-control form-control-sm">
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <div class="form-group">
                                            <label>Fecha últ. factura hasta</label>
                                            <input type="date" wire:model="filtFechaHasta"
                                                   class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Loading --}}
                        <div wire:loading wire:target="filtCodigo,filtNombre,filtEstado,filtVendedor,filtRequiereAt,filtFechaDesde,filtFechaHasta,filtSinHistorial,paginaAnterior,paginaSiguiente,limpiarFiltros,porPagina,filtrarPorGrafica"
                             class="text-center py-4">
                            <i class="fa fa-spinner fa-spin fa-2x" style="color:var(--ecf-blue)"></i>
                            <p class="mt-2 text-muted small">Cargando datos…</p>
                        </div>

                        {{-- Tabla --}}
                        <div wire:loading.remove wire:target="filtCodigo,filtNombre,filtEstado,filtVendedor,filtRequiereAt,filtFechaDesde,filtFechaHasta,filtSinHistorial,paginaAnterior,paginaSiguiente,limpiarFiltros,porPagina,filtrarPorGrafica">

                            @if ($total === 0)
                                <div class="text-center py-5">
                                    <i class="fa fa-search fa-3x" style="color:#c5d8f7"></i>
                                    <p class="mt-3 text-muted">No se encontraron clientes con los filtros aplicados.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered ecf-table" style="margin-bottom:0">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width:70px;">Código</th>
                                                <th>Nombre</th>
                                                <th style="width:220px;">Correo</th>
                                                <th style="width:130px;">Teléfono</th>
                                                <th style="min-width:220px;">Dirección</th>
                                                <th class="text-center" style="width:100px;">Estado</th>
                                                <th style="width:150px;">Vendedor</th>
                                                <th>N° Última Factura</th>
                                                <th class="text-center" style="width:120px;">Fecha Últ. Factura</th>
                                                <th class="text-right" style="width:140px;">Monto Últ. Factura</th>
                                                <th class="text-right" style="width:140px;">Saldo Pendiente</th>
                                                <th class="text-center" style="width:120px;">Req. Atención</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datosPagina as $row)
                                                <tr class="{{ $row->requiere_atencion === 'Sí' ? 'row-atencion' : '' }}">
                                                    <td class="text-center text-muted small">{{ $row->codigo_cliente }}</td>
                                                    <td><strong>{{ $row->nombre_cliente }}</strong></td>
                                                    <td>{{ $row->correo }}</td>
                                                    <td>{{ $row->telefono }}</td>
                                                    <td>{{ $row->direccion }}</td>
                                                    <td class="text-center">
                                                        <span class="badge-estado">{{ $row->estado }}</span>
                                                    </td>
                                                    <td>{{ $row->vendedor }}</td>
                                                    <td>
                                                        @if ($row->numero_ultima_factura)
                                                            <span class="factura-code">{{ $row->numero_ultima_factura }}</span>
                                                        @else
                                                            <span class="sin-historial">Sin historial</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($row->fecha_ultima_factura)
                                                            {{ \Carbon\Carbon::parse($row->fecha_ultima_factura)->format('d/m/Y') }}
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-right">
                                                        @if ($row->monto_ultima_factura > 0)
                                                            L {{ number_format($row->monto_ultima_factura, 2, '.', ',') }}
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-right">
                                                        @if ($row->saldo_pendiente > 0)
                                                            <span class="badge-saldo">
                                                                L {{ number_format($row->saldo_pendiente, 2, '.', ',') }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted small">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($row->requiere_atencion === 'Sí')
                                                            <span class="badge-atencion-si">
                                                                <i class="fa fa-exclamation-triangle mr-1"></i>Sí
                                                            </span>
                                                        @else
                                                            <span class="badge-atencion-no">
                                                                <i class="fa fa-check mr-1"></i>No
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Paginación --}}
                                <div class="ecf-pagination">
                                    <button type="button" wire:click="paginaAnterior"
                                            @if($paginaActual <= 1) disabled @endif
                                            class="btn btn-default btn-sm">
                                        <i class="fa fa-chevron-left"></i> Anterior
                                    </button>
                                    <div class="d-flex align-items-center flex-wrap" style="gap:4px;">
                                        @for ($p = 1; $p <= $totalPaginas; $p++)
                                            @if ($p === 1 || $p === $totalPaginas || ($p >= $paginaActual - 2 && $p <= $paginaActual + 2))
                                                @if ($p === $paginaActual - 2 && $p > 2)
                                                    <span class="ecf-page-info px-1">…</span>
                                                @endif
                                                <button type="button" wire:click="$set('paginaActual', {{ $p }})"
                                                        class="btn btn-sm btn-page {{ $p === $paginaActual ? 'active' : 'btn-default' }}">
                                                    {{ $p }}
                                                </button>
                                                @if ($p === $paginaActual + 2 && $p < $totalPaginas - 1)
                                                    <span class="ecf-page-info px-1">…</span>
                                                @endif
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="ecf-page-info">
                                        Pág. {{ $paginaActual }} / {{ $totalPaginas }} &nbsp;·&nbsp; {{ $total }} registros
                                    </span>
                                    <button type="button" wire:click="paginaSiguiente({{ $totalPaginas }})"
                                            @if($paginaActual >= $totalPaginas) disabled @endif
                                            class="btn btn-default btn-sm">
                                        Siguiente <i class="fa fa-chevron-right"></i>
                                    </button>
                                </div>
                            @endif

                        </div>{{-- /wire:loading.remove --}}
                    </div>{{-- /ecf-card-body --}}
                </div>{{-- /ecf-card --}}
            </div>
        </div>

    </div>{{-- /wrapper-content --}}

    {{-- Datos de gráficas (Livewire actualiza en cada render) --}}
    <script type="application/json" id="ecfChartData">@json($chartData)</script>

</div>{{-- root --}}

@push('scripts')
<script>
(function () {
    'use strict';

    var charts = {};
    var C_ATENCION   = ['#e74c3c','#27ae60'];
    var C_ESTADOS    = ['#1565C0','#27ae60','#e67e22','#8e44ad','#1abc9c','#e74c3c','#f39c12','#2980b9'];
    var C_VENDEDORES = ['#2980b9','#16a085','#8e44ad','#d35400','#27ae60','#c0392b','#2c3e50','#f39c12'];
    var C_HISTORIAL  = ['#27ae60','#bdc3c7'];
    var C_TOPVEND    = ['#e74c3c','#e67e22','#f39c12','#27ae60','#1565C0','#8e44ad','#2980b9','#16a085','#c0392b','#2c3e50'];

    function getLivewireComponent() {
        var el = document.querySelector('[data-ecf-root]');
        return (el && window.livewire) ? window.livewire.find(el.getAttribute('wire:id')) : null;
    }

    function callLivewire(method) {
        var args = Array.prototype.slice.call(arguments, 1);
        var comp = getLivewireComponent();
        if (comp) comp.call.apply(comp, [method].concat(args));
    }

    function makeClickHandler(tipo) {
        return {
            dataPointSelection: function(event, ctx, config) {
                var label = config.w.globals.labels[config.dataPointIndex];
                if (label !== undefined) callLivewire('filtrarPorGrafica', tipo, label);
            }
        };
    }

    function buildPie(series, labels, colors, tipo) {
        return {
            series: series, labels: labels, colors: colors,
            chart: { type: 'pie', height: 220, fontFamily: 'inherit', events: makeClickHandler(tipo) },
            legend: { position: 'bottom', fontSize: '12px' },
            dataLabels: { formatter: function(val, opts) {
                return opts.w.globals.seriesTotals[opts.seriesIndex] + ' (' + Math.round(val) + '%)';
            }},
            tooltip: { y: { formatter: function(v) { return v + ' clientes'; } } },
            states: { hover: { filter: { type: 'darken', value: 0.15 } } },
            plotOptions: { pie: { expandOnClick: true } },
            noData: { text: 'Sin datos', style: { fontSize: '14px', color: '#aaa' } },
        };
    }

    function buildBar(series, labels, colors) {
        return {
            series: [{ name: 'Sin atención', data: series }],
            chart: {
                type: 'bar', height: Math.max(200, labels.length * 38 + 60),
                fontFamily: 'inherit',
                events: {
                    dataPointSelection: function(event, ctx, config) {
                        var label = config.w.globals.labels[config.dataPointIndex];
                        if (label !== undefined) callLivewire('filtrarPorGrafica', 'vendedor', label);
                    }
                }
            },
            plotOptions: { bar: { horizontal: true, barHeight: '65%', distributed: true } },
            colors: colors,
            xaxis: {
                categories: labels,
                labels: { style: { fontSize: '12px' } },
                title: { text: 'N° de clientes sin atención' }
            },
            yaxis: { labels: { style: { fontSize: '12px' } } },
            legend: { show: false },
            dataLabels: { enabled: true, formatter: function(v) { return v; }, style: { fontSize: '12px' } },
            tooltip: { y: { formatter: function(v) { return v + ' clientes'; } } },
            noData: { text: 'Sin datos', style: { fontSize: '14px', color: '#aaa' } },
        };
    }

    function safeDestroy(key) {
        if (charts[key]) { try { charts[key].destroy(); } catch(e){} charts[key] = null; }
    }

    function sumArr(a) { return a.reduce(function(x,y){ return x+y; }, 0); }

    function renderPie(key, elId, series, labels, colors, tipo) {
        safeDestroy(key);
        var el = document.getElementById(elId);
        if (!el) return;
        el.innerHTML = '';
        if (!series || sumArr(series) === 0) {
            el.innerHTML = '<p class="text-center text-muted" style="padding-top:70px;font-size:.82rem;">Sin datos</p>';
            return;
        }
        charts[key] = new ApexCharts(el, buildPie(series, labels, colors, tipo));
        charts[key].render();
    }

    function renderBar(key, elId, series, labels, colors) {
        safeDestroy(key);
        var el = document.getElementById(elId);
        if (!el) return;
        el.innerHTML = '';
        if (!series || sumArr(series) === 0) {
            el.innerHTML = '<p class="text-center text-muted" style="padding-top:80px;font-size:.82rem;">Sin datos — no hay clientes sin atención con los filtros actuales</p>';
            return;
        }
        charts[key] = new ApexCharts(el, buildBar(series, labels, colors));
        charts[key].render();
    }

    function initCharts(data) {
        if (!data) return;
        renderPie('atencion',   'chartAtencion',   data.atencion.series,         data.atencion.labels,         C_ATENCION,   'atencion');
        renderPie('estados',    'chartEstados',    data.estados.series,           data.estados.labels,           C_ESTADOS,    'estado');
        renderPie('vendedores', 'chartVendedores', data.vendedores.series,        data.vendedores.labels,        C_VENDEDORES, 'vendedor');
        renderPie('historial',  'chartHistorial',  data.historial.series,         data.historial.labels,         C_HISTORIAL,  'historial');
        renderBar('topVend',    'chartTopVend',    data.topVendedoresAt.series,   data.topVendedoresAt.labels,   C_TOPVEND);
    }

    function getChartData() {
        var el = document.getElementById('ecfChartData');
        if (!el) return null;
        try { return JSON.parse(el.textContent || el.innerText); } catch(e) { return null; }
    }

    document.addEventListener('DOMContentLoaded', function() { initCharts(getChartData()); });

    document.addEventListener('livewire:load', function() {
        Livewire.hook('message.processed', function() { initCharts(getChartData()); });
    });
})();
</script>
@endpush
