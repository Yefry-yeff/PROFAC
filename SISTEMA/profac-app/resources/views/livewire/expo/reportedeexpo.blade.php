<div>
    @push('styles')
    <style>
        .expo-report-page { --ink:#243b46; --muted:#6d7f88; --line:#dfe7eb; --blue:#245b78; }
        .expo-report-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; }
        .expo-report-heading h2 { margin-bottom:4px; color:var(--ink); font-size:24px; }
        .expo-report-heading p { margin:0; color:var(--muted); font-size:12px; }
        .expo-report-back { display:inline-flex; align-items:center; gap:6px; border:1px solid #ccd8de; border-radius:5px; background:#fff; color:#405965; font-size:11px; font-weight:700; }
        .expo-report-card { overflow:hidden; border:1px solid var(--line); border-radius:7px; background:#fff; box-shadow:0 4px 18px rgba(35,55,65,.07); }
        .expo-report-card-head { display:flex; align-items:center; justify-content:space-between; gap:15px; padding:14px 17px; background:var(--blue); color:#fff; }
        .expo-report-card-head h5 { margin:0 0 2px; color:#fff; font-size:15px; font-weight:800; }
        .expo-report-card-head small { color:rgba(255,255,255,.82); }
        .expo-report-state { display:inline-flex; align-items:center; border-radius:12px; padding:4px 8px; background:rgba(255,255,255,.16); font-size:9px; font-weight:800; text-transform:uppercase; }
        .expo-report-content { padding:16px; }
        .expo-report-summary { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:9px; margin-bottom:14px; }
        .expo-report-metric { padding:11px 12px; border:1px solid var(--line); border-radius:6px; background:#f8fafb; }
        .expo-report-metric span { display:block; color:#71828b; font-size:9px; font-weight:800; text-transform:uppercase; }
        .expo-report-metric strong { display:block; margin-top:2px; color:var(--ink); font-size:19px; }
        .expo-report-toolbar { display:grid; grid-template-columns:minmax(260px,1fr) auto; align-items:center; gap:12px; margin-bottom:11px; }
        .expo-report-search { position:relative; }
        .expo-report-search i { position:absolute; top:10px; left:11px; color:#91a0a7; }
        .expo-report-search input { min-height:35px; padding-left:34px; border:1px solid #d7e0e5; border-radius:5px; font-size:12px; }
        .expo-report-table-wrap { max-height:calc(100vh - 390px); min-height:220px; overflow:auto; border:1px solid var(--line); border-radius:6px; }
        .expo-report-table { min-width:1120px; margin:0; font-size:11px; }
        .expo-report-table th { position:sticky; top:0; z-index:2; padding:8px!important; border-bottom:1px solid #d7e1e6!important; background:#edf3f6; color:#526b77; font-size:9px; text-transform:uppercase; white-space:nowrap; }
        .expo-report-table td { padding:8px!important; vertical-align:top!important; border-color:#edf1f3!important; color:#344b56; }
        .expo-report-table tbody tr:hover { background:#f8fbfc; }
        .expo-report-status { display:inline-flex; align-items:center; border-radius:12px; padding:3px 7px; font-size:8px; font-weight:800; text-transform:uppercase; white-space:nowrap; }
        .expo-report-status.pending { background:#eceff1; color:#546e7a; }
        .expo-report-status.partial { background:#fff3df; color:#a15b00; }
        .expo-report-status.done { background:#e5f4e9; color:#27733c; }
        .expo-report-status.liquidation { background:#e8f1fb; color:#27689a; }
        .expo-report-actors { max-width:210px; white-space:normal; line-height:1.4; }
        .expo-report-invoices { max-width:210px; color:#607d8b!important; white-space:normal; line-height:1.4; }
        .expo-report-empty { padding:45px 20px!important; color:#84949c!important; text-align:center; }
        .expo-picker-title { margin:0 0 4px; color:var(--ink); font-size:15px; font-weight:800; }
        .expo-picker-copy { margin:0 0 14px; color:var(--muted); font-size:11px; }
        .expo-picker-table { min-width:760px; margin:0; font-size:11px; }
        .expo-picker-table th { padding:8px 10px!important; background:#edf3f6; color:#526b77; font-size:9px; text-transform:uppercase; }
        .expo-picker-table td { padding:9px 10px!important; vertical-align:middle!important; border-color:#edf1f3!important; }
        .expo-picker-name { color:var(--ink); font-size:12px; font-weight:800; }
        .expo-picker-select { display:inline-flex; align-items:center; gap:6px; border:0; border-radius:5px; background:#245b78; color:#fff; font-size:10px; font-weight:700; }
        .expo-report-head-actions { display:flex; align-items:center; gap:8px; }
        .expo-report-change { border:1px solid rgba(255,255,255,.55); border-radius:5px; background:transparent; color:#fff; font-size:10px; font-weight:700; }
        @media (max-width:767px) {
            .expo-report-heading { flex-direction:column; }
            .expo-report-summary { grid-template-columns:repeat(2,minmax(0,1fr)); }
            .expo-report-toolbar { grid-template-columns:1fr; }
            .expo-report-content { padding:12px; }
            .expo-report-table-wrap { max-height:none; }
        }
    </style>
    @endpush

    <div class="expo-report-page">
        <div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-lg-12 expo-report-heading">
                <div>
                    <h2><i class="fa fa-list-alt mr-2" style="color:#245b78;"></i>Reporte de flujos de Expo</h2>
                    <p>Seguimiento de facturación y responsables comerciales.</p>
                    <ol class="breadcrumb mt-2 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/flujo_de_venta/expo') }}">Expo</a></li>
                        <li class="breadcrumb-item active"><strong>Reporte de flujos</strong></li>
                    </ol>
                </div>
                <a href="{{ url('/flujo_de_venta/expo') }}" class="btn btn-sm expo-report-back"><i class="fa fa-arrow-left"></i>Volver a Expos</a>
            </div>
        </div>

        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="expo-report-card">
                @if(empty($expo))
                    <div class="expo-report-card-head">
                        <div>
                            <h5>Seleccionar Expo parametrizada</h5>
                            <small>Busque la configuración que desea consultar.</small>
                        </div>
                    </div>
                    <div class="expo-report-content">
                        <h3 class="expo-picker-title">Expos disponibles</h3>
                        <p class="expo-picker-copy">Seleccione una Expo para generar el reporte de todos sus flujos y responsables.</p>
                        <div class="expo-report-toolbar">
                            <div class="expo-report-search">
                                <i class="fa fa-search"></i>
                                <input type="search" wire:model.debounce.300ms="busquedaExpo" class="form-control" placeholder="Buscar por nombre, estado o número de Expo">
                            </div>
                            <span class="text-muted small">{{ $expos->count() }} Expo(s)</span>
                        </div>
                        <div class="table-responsive" style="border:1px solid #dfe7eb;border-radius:6px;">
                            <table class="table table-hover expo-picker-table">
                                <thead><tr><th>Expo</th><th>Estado</th><th>Vigencia</th><th class="text-center">Flujos</th><th class="text-right">Acción</th></tr></thead>
                                <tbody>
                                @forelse($expos as $expoDisponible)
                                    <tr>
                                        <td><span class="expo-picker-name">{{ $expoDisponible->nombre }}</span><br><small class="text-muted">Expo #{{ $expoDisponible->id }}</small></td>
                                        <td><span class="expo-report-status {{ $expoDisponible->estado === 'Activo' ? 'done' : 'pending' }}">{{ $expoDisponible->estado }}</span></td>
                                        <td>{{ date('d/m/Y H:i', strtotime($expoDisponible->fecha_inicio)) }}<br><small class="text-muted">Hasta {{ $expoDisponible->fecha_fin ? date('d/m/Y H:i', strtotime($expoDisponible->fecha_fin)) : 'sin fecha final' }}</small></td>
                                        <td class="text-center"><strong>{{ $expoDisponible->total_flujos }}</strong></td>
                                        <td class="text-right"><button type="button" wire:click="seleccionarExpo({{ $expoDisponible->id }})" wire:loading.attr="disabled" class="btn btn-sm expo-picker-select"><i class="fa fa-bar-chart"></i>Ver reporte</button></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="expo-report-empty">No se encontraron Expos con ese criterio.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="expo-report-card-head">
                        <div>
                            <h5>{{ $expo['nombre'] }}</h5>
                            <small>Expo #{{ $expo['id'] }} · {{ date('d/m/Y H:i', strtotime($expo['fecha_inicio'])) }} a {{ $expo['fecha_fin'] ? date('d/m/Y H:i', strtotime($expo['fecha_fin'])) : 'sin fecha final' }}</small>
                        </div>
                        <div class="expo-report-head-actions">
                            <span class="expo-report-state">{{ $expo['estado'] }}</span>
                            <button type="button" wire:click="cambiarExpo" class="btn btn-sm expo-report-change"><i class="fa fa-exchange mr-1"></i>Cambiar Expo</button>
                        </div>
                    </div>
                    <div class="expo-report-content">
                    <div class="expo-report-summary">
                        <div class="expo-report-metric"><span>Total de flujos</span><strong>{{ $totalFlujos }}</strong></div>
                        <div class="expo-report-metric"><span>Sin facturar</span><strong>{{ $sinFacturar }}</strong></div>
                        <div class="expo-report-metric"><span>Factura parcial</span><strong>{{ $facturaParcial }}</strong></div>
                        <div class="expo-report-metric"><span>En liquidación o finalizados</span><strong>{{ $finalizados }}</strong></div>
                    </div>

                    <div class="expo-report-toolbar">
                        <div class="expo-report-search">
                            <i class="fa fa-search"></i>
                            <input type="search" wire:model.debounce.300ms="filtro" class="form-control" placeholder="Buscar flujo, oferta, cliente, estado o responsable">
                        </div>
                        <span class="text-muted small">{{ $flujosFiltrados->count() }} de {{ $totalFlujos }} flujo(s)</span>
                    </div>

                    <div class="expo-report-table-wrap">
                        <table class="table table-hover expo-report-table">
                            <thead><tr><th>Flujo</th><th>Oferta</th><th>Cliente</th><th>Estado</th><th>Facturas</th><th>Asesor comercial</th><th>Teleasesor</th><th>Gestor comercial</th><th>Fecha</th></tr></thead>
                            <tbody>
                            @forelse($flujosFiltrados as $flujo)
                                @php
                                    $claseEstado = match ($flujo['estado']) {
                                        'PENDIENTE_FACTURACION' => 'pending',
                                        'FACTURACION_PARCIAL' => 'partial',
                                        'PENDIENTE_LIQUIDACION' => 'liquidation',
                                        default => 'done',
                                    };
                                @endphp
                                <tr>
                                    <td><strong>#{{ $flujo['flujo_id'] ?: 'Sin asignar' }}</strong></td>
                                    <td>#{{ $flujo['cotizacion_id'] }}</td>
                                    <td>{{ $flujo['cliente'] }}</td>
                                    <td><span class="expo-report-status {{ $claseEstado }}">{{ $flujo['estado_etiqueta'] }}</span></td>
                                    <td class="expo-report-invoices">
                                        <strong>{{ $flujo['total_facturas'] }}</strong>
                                        @if(!empty($flujo['facturas']))<br>{{ implode(', ', $flujo['facturas']) }}@endif
                                    </td>
                                    <td class="expo-report-actors">{{ $flujo['asesores'] }}</td>
                                    <td class="expo-report-actors">{{ $flujo['teleasesores'] }}</td>
                                    <td class="expo-report-actors">{{ $flujo['gestores'] }}</td>
                                    <td style="white-space:nowrap;">{{ date('d/m/Y H:i', strtotime($flujo['fecha'])) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="expo-report-empty">{{ $totalFlujos ? 'No hay resultados para este filtro.' : 'Esta Expo no tiene flujos registrados.' }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>