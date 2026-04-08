<div>
    {{-- ===== ENCABEZADO ===== --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="fa fa-list-alt text-primary"></i> Historial de Pedidos</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item">Flujo</li>
                <li class="breadcrumb-item active"><strong>Historial de Pedidos</strong></li>
            </ol>
        </div>
        <div class="col-lg-2 d-flex align-items-center justify-content-end">
            <a href="/flujo/pedido" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> &nbsp;Nuevo Pedido
            </a>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- ===== ALERTAS ===== --}}
        @if ($mensajeExito)
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" wire:click="$set('mensajeExito', '')"><span>&times;</span></button>
                <i class="fa fa-check-circle"></i> <strong>¡Éxito!</strong> {{ $mensajeExito }}
            </div>
        @endif
        @if ($mensajeError)
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" wire:click="$set('mensajeError', '')"><span>&times;</span></button>
                <i class="fa fa-exclamation-triangle"></i> {{ $mensajeError }}
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    {{-- ── Encabezado del ibox ── --}}
                    <div class="ibox-title"
                         style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%);
                                color:#fff; border-radius:4px 4px 0 0;">
                        <h5 class="m-0" style="color:#fff;">
                            <i class="fa fa-list-alt"></i> &nbsp;Pedidos Registrados
                        </h5>
                    </div>

                    <div class="ibox-content" style="padding:24px;">

                        {{-- ===== FILTROS ===== --}}
                        <div class="row mb-4" style="align-items:flex-end;">

                            {{-- Búsqueda de cliente --}}
                            <div class="col-lg-5 col-md-6 mb-2">
                                <label class="font-weight-bold" style="font-size:12px; color:#555;">
                                    <i class="fa fa-search text-primary"></i> &nbsp;Buscar por cliente
                                </label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0">
                                            <i class="fa fa-user text-muted"></i>
                                        </span>
                                    </div>
                                    <input
                                        type="text"
                                        wire:model.debounce.300ms="busquedaCliente"
                                        class="form-control border-left-0"
                                        placeholder="Nombre o RTN del cliente..."
                                        style="border-radius:0 8px 8px 0;"
                                    >
                                </div>
                            </div>

                            {{-- Filtro estado --}}
                            <div class="col-lg-2 col-md-3 mb-2">
                                <label class="font-weight-bold" style="font-size:12px; color:#555;">
                                    <i class="fa fa-filter text-primary"></i> &nbsp;Estado
                                </label>
                                <select wire:model="filtroEstado" class="form-control shadow-sm"
                                        style="border-radius:8px;">
                                    <option value="">Todos</option>
                                    <option value="pedido">Pedido</option>
                                    <option value="pre_factura">Pre Factura</option>
                                    <option value="cotizado">Factura</option>
                                    <option value="facturado">Cobro</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>

                            {{-- Filtro fecha --}}
                            <div class="col-lg-3 col-md-3 mb-2">
                                <label class="font-weight-bold" style="font-size:12px; color:#555;">
                                    <i class="fa fa-calendar text-primary"></i> &nbsp;Fecha exacta
                                </label>
                                <input
                                    type="date"
                                    wire:model="filtroFecha"
                                    class="form-control shadow-sm"
                                    style="border-radius:8px;"
                                >
                            </div>

                            {{-- Botón limpiar --}}
                            <div class="col-lg-2 col-md-3 mb-2">
                                <label class="font-weight-bold" style="font-size:12px; color:#555;">&nbsp;</label>
                                <button
                                    type="button"
                                    wire:click="limpiarFiltros"
                                    class="btn btn-default btn-block shadow-sm"
                                    style="border-radius:8px; width:100%;"
                                    title="Limpiar filtros"
                                >
                                    <i class="fa fa-times-circle text-muted"></i> &nbsp;Limpiar
                                </button>
                            </div>
                        </div>

                        {{-- Contador de resultados --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i>
                                {{ $totalPedidos }} pedido(s) encontrado(s)
                            </small>
                            <small class="text-muted">
                                Página {{ $pagina }} de {{ $totalPaginas ?: 1 }}
                            </small>
                        </div>

                        {{-- ===== TABLA ===== --}}
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" style="font-size:14px;">
                                <thead>
                                    <tr style="background:#f5f7fb;">
                                        <th style="width:50px;">#</th>
                                        <th>Cliente</th>
                                        <th style="width:75px; text-align:center;">Prods.</th>
                                        <th style="width:105px; text-align:center;">Estado</th>
                                        <th>Registrado</th>
                                        <th style="width:140px;">Fecha</th>
                                        <th style="width:90px; text-align:center;">Flujo</th>
                                        <th style="width:175px; text-align:center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pedidos as $pedido)
                                        <tr>
                                            {{-- ID --}}
                                            <td class="font-weight-bold text-primary">
                                                #{{ $pedido->id }}
                                            </td>

                                            {{-- Cliente --}}
                                            <td>
                                                <span class="font-weight-bold">{{ $pedido->cliente }}</span>
                                                @if ($pedido->rtn)
                                                    <br><small class="text-muted">RTN: {{ $pedido->rtn }}</small>
                                                @endif
                                            </td>

                                            {{-- Cantidad de productos --}}
                                            <td class="text-center">
                                                <span class="badge badge-info"
                                                      style="font-size:12px; padding:4px 8px; border-radius:12px;">
                                                    {{ $pedido->total_productos }}
                                                </span>
                                            </td>

                                            {{-- Estado --}}
                                            <td class="text-center">
                                                @php
                                                    // Determinar el paso visual igual que el mapa del flujo
                                                    if ($pedido->estado === 'cancelado') {
                                                        $eLabel = 'Cancelado';
                                                        $eIcon  = 'fa-ban';
                                                        $eBg    = '#e74c3c';
                                                    } elseif ($pedido->estado === 'facturado') {
                                                        $eLabel = 'Cobro';
                                                        $eIcon  = 'fa-money';
                                                        $eBg    = '#6c5ce7';
                                                    } elseif ($pedido->has_ganadora > 0 || $pedido->estado === 'pre_factura' || $pedido->estado === 'cotizado') {
                                                        $eLabel = 'Pre Factura';
                                                        $eIcon  = 'fa-file-o';
                                                        $eBg    = '#00b894';
                                                    } elseif ($pedido->has_ofertas > 0) {
                                                        $eLabel = 'Ofertas';
                                                        $eIcon  = 'fa-tag';
                                                        $eBg    = '#f39c12';
                                                    } else {
                                                        $eLabel = 'Pedido';
                                                        $eIcon  = 'fa-shopping-cart';
                                                        $eBg    = '#1ab394';
                                                    }
                                                @endphp
                                                <span style="display:inline-block; background:{{ $eBg }};
                                                             color:#fff; border-radius:20px; font-size:11px;
                                                             font-weight:700; padding:3px 10px; white-space:nowrap;">
                                                    <i class="fa {{ $eIcon }}"></i> {{ $eLabel }}
                                                </span>
                                            </td>

                                            {{-- Registrado por --}}
                                            <td>
                                                <i class="fa fa-user-circle text-muted"></i>
                                                &nbsp;{{ $pedido->registrado_por }}
                                            </td>

                                            {{-- Fecha --}}
                                            <td>
                                                <i class="fa fa-calendar-o text-muted"></i>
                                                &nbsp;{{ \Carbon\Carbon::parse($pedido->created_at)->format('d/m/Y H:i') }}
                                            </td>

                                            {{-- Flujo --}}
                                            <td class="text-center">
                                                <button
                                                    type="button"
                                                    wire:click="verFlujo({{ $pedido->id }})"
                                                    class="btn btn-xs"
                                                    title="Ver flujo del pedido"
                                                    style="background:linear-gradient(135deg,#6c5ce7,#a855f7); color:#fff;
                                                           border:none; border-radius:8px; padding:4px 10px;
                                                           font-size:11px; font-weight:600; white-space:nowrap;
                                                           box-shadow:0 2px 8px rgba(108,92,231,.3);"
                                                >
                                                    <i class="fa fa-map-o"></i> Flujo
                                                </button>
                                            </td>

                                            {{-- Acciones --}}
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    {{-- Imprimir --}}
                                                    <a href="/flujo/pedido/imprimir/{{ $pedido->id }}"
                                                       target="_blank"
                                                       class="btn btn-xs btn-default"
                                                       title="Imprimir pedido"
                                                       style="border-radius:6px 0 0 6px;">
                                                        <i class="fa fa-print text-info"></i>
                                                    </a>
                                                    {{-- Exportar Excel --}}
                                                    <a href="/flujo/pedido/exportar/{{ $pedido->id }}"
                                                       class="btn btn-xs btn-default"
                                                       title="Exportar Excel">
                                                        <i class="fa fa-file-excel-o text-success"></i>
                                                    </a>
                                                    {{-- Editar --}}
                                                    <a href="/flujo/pedido/editar/{{ $pedido->id }}"
                                                       class="btn btn-xs btn-warning"
                                                       title="Editar pedido"
                                                       style="color:#fff;">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    {{-- Crear Oferta --}}
                                                    @if ($pedido->estado !== 'cancelado' && $pedido->estado !== 'cotizado' && $pedido->estado !== 'pre_factura')
                                                        <a href="/flujo/oferta/crear/{{ $pedido->id }}"
                                                           class="btn btn-xs btn-primary"
                                                           title="Crear oferta para este pedido"
                                                           style="color:#fff;">
                                                            <i class="fa fa-tag"></i>
                                                        </a>
                                                    @else
                                                        <button type="button" class="btn btn-xs btn-default"
                                                                disabled style="cursor:not-allowed;opacity:.45;"
                                                                title="{{ $pedido->estado === 'cotizado' ? 'Ya existe una oferta ganadora' : ($pedido->estado === 'pre_factura' ? 'Ya existe una oferta ganadora' : 'Pedido cancelado') }}">
                                                            <i class="fa fa-tag text-muted"></i>
                                                        </button>
                                                    @endif
                                                    {{-- Cancelar (solo si no está cancelado) --}}
                                                    @if ($pedido->estado !== 'cancelado')
                                                        <button
                                                            type="button"
                                                            wire:click="confirmarAnular({{ $pedido->id }})"
                                                            class="btn btn-xs btn-default"
                                                            title="Cancelar pedido"
                                                            style="border-radius:0 6px 6px 0;">
                                                            <i class="fa fa-ban text-danger"></i>
                                                        </button>
                                                    @else
                                                        <span class="btn btn-xs btn-default disabled"
                                                              style="border-radius:0 6px 6px 0; opacity:.35;">
                                                            <i class="fa fa-ban text-danger"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                                                No se encontraron pedidos con los filtros aplicados.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- ===== PAGINACIÓN ===== --}}
                        @if ($totalPaginas > 1)
                            <div class="d-flex justify-content-center mt-3">
                                <nav>
                                    <ul class="pagination pagination-sm">
                                        <li class="page-item {{ $pagina <= 1 ? 'disabled' : '' }}">
                                            <button
                                                class="page-link"
                                                wire:click="paginaAnterior"
                                                {{ $pagina <= 1 ? 'disabled' : '' }}
                                            >&laquo;</button>
                                        </li>

                                        @for ($p = max(1, $pagina - 2); $p <= min($totalPaginas, $pagina + 2); $p++)
                                            <li class="page-item {{ $p == $pagina ? 'active' : '' }}">
                                                <button class="page-link" wire:click="irPagina({{ $p }})">
                                                    {{ $p }}
                                                </button>
                                            </li>
                                        @endfor

                                        <li class="page-item {{ $pagina >= $totalPaginas ? 'disabled' : '' }}">
                                            <button
                                                class="page-link"
                                                wire:click="paginaSiguiente"
                                                {{ $pagina >= $totalPaginas ? 'disabled' : '' }}
                                            >&raquo;</button>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        @endif

                    </div>{{-- /ibox-content --}}
                </div>{{-- /ibox --}}
            </div>
        </div>

    </div>{{-- /wrapper-content --}}

    {{-- ===== MODAL DE CONFIRMACIÓN: ANULAR ===== --}}
    @if ($showModalAnular)
    <div
        class="modal fade show"
        style="display:block; background:rgba(0,0,0,.55); z-index:1050;"
        tabindex="-1"
        role="dialog"
    >
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius:12px; border:none; box-shadow:0 10px 40px rgba(0,0,0,.25);">
                <div class="modal-header" style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%); border:none;">
                    <h5 class="modal-title text-white m-0">
                        <i class="fa fa-ban"></i> &nbsp;Cancelar Pedido
                    </h5>
                    <button type="button" class="close text-white" wire:click="cancelarAnular" style="opacity:1;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="fa fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                    <p class="mb-0">
                        ¿Está seguro que desea cancelar el pedido <strong>#{{ $pedidoAnularId }}</strong>?
                        <br><small class="text-muted">Esta acción no puede deshacerse.</small>
                    </p>
                </div>
                <div class="modal-footer" style="border:none; justify-content:center;">
                    <button type="button" wire:click="cancelarAnular" class="btn btn-default">
                        <i class="fa fa-times"></i> No, volver
                    </button>
                    <button type="button" wire:click="anularPedido" class="btn btn-danger">
                        <i class="fa fa-ban"></i> &nbsp;Sí, Cancelar pedido
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== MODAL DE FLUJO DEL PEDIDO ===== --}}
    @if ($showModalFlujo && $pedidoFlujoData)
    @php
        $fEstado      = $pedidoFlujoData['estado'] ?? 'pedido';
        $fCancelado   = ($fEstado === 'cancelado');
        $fOfertas     = $pedidoFlujoData['ofertas'] ?? [];
        $tieneOfertas  = count($fOfertas) > 0;
        $tieneGanadora = collect($fOfertas)->contains(fn($o) => ((array)$o)['estado'] === 'ganadora');
        $yaGanadoraExists = $tieneGanadora;
        $fPaso = match(true) {
            $fCancelado                                                              => 0,
            $fEstado === 'facturado'                                                 => 6,
            $fEstado === 'cotizado' || $fEstado === 'pre_factura' || $tieneGanadora => 3,
            $tieneOfertas                                                            => 2,
            default                                                                  => 1,
        };
        $fPasos = [
            1 => ['icon' => 'fa-shopping-cart', 'title' => 'Pedido',       'color' => '#1a7efb', 'glow' => 'rgba(26,126,251,.45)'],
            2 => ['icon' => 'fa-tag',           'title' => 'Ofertas',      'color' => '#f39c12', 'glow' => 'rgba(243,156,18,.45)'],
            3 => ['icon' => 'fa-file-o',        'title' => 'Pre Factura',  'color' => '#00b894', 'glow' => 'rgba(0,184,148,.45)'],
            4 => ['icon' => 'fa-file-text',     'title' => 'Factura',      'color' => '#1ab394', 'glow' => 'rgba(26,179,148,.45)'],
            5 => ['icon' => 'fa-truck',         'title' => 'Entregas',     'color' => '#e67e22', 'glow' => 'rgba(230,126,34,.45)'],
            6 => ['icon' => 'fa-money',         'title' => 'Cobro',        'color' => '#6c5ce7', 'glow' => 'rgba(108,92,231,.45)'],
        ];
    @endphp
    <div class="modal fade show"
         style="display:block; background:rgba(10,10,30,.65); z-index:1060;"
         tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" style="margin-top:60px;" role="document">
            <div class="modal-content" style="border-radius:18px; border:none; overflow:hidden;
                        box-shadow:0 20px 60px rgba(0,0,0,.35);">

                {{-- Header --}}
                <div class="modal-header" style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%);
                            border:none; padding:18px 24px;">
                    <div>
                        <h5 class="modal-title m-0" style="color:#fff; font-size:17px; font-weight:700;">
                            <i class="fa fa-map-o mr-2"></i>Flujo del Pedido
                            <span style="background:rgba(255,255,255,.2); border-radius:20px;
                                         padding:2px 12px; font-size:14px; margin-left:6px;">
                                #{{ $pedidoFlujoData['id'] }}
                            </span>
                        </h5>
                        <small style="color:rgba(255,255,255,.8); font-size:12px;">
                            <i class="fa fa-user mr-1"></i>{{ $pedidoFlujoData['cliente'] }}
                            &nbsp;&bull;&nbsp;
                            <i class="fa fa-calendar mr-1"></i>
                            {{ \Carbon\Carbon::parse($pedidoFlujoData['created_at'])->format('d/m/Y H:i') }}
                        </small>
                    </div>
                    <button type="button" wire:click="cerrarFlujo"
                            class="close" style="color:#fff; opacity:1; font-size:22px; margin-top:-8px;">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body" style="padding:16px 28px 28px; background:#f8f9fc;">

                    @if ($fCancelado)
                    {{-- Estado cancelado: banner rojo --}}
                    <div style="text-align:center; padding:20px 0 10px;">
                        <div style="display:inline-block; background:linear-gradient(135deg,#e74c3c,#c0392b);
                                    border-radius:50%; width:80px; height:80px; line-height:80px;
                                    font-size:36px; color:#fff; margin-bottom:16px;
                                    box-shadow:0 8px 24px rgba(231,76,60,.45);
                                    animation:cancelShake .5s ease;">
                            <i class="fa fa-ban"></i>
                        </div>
                        <h4 style="color:#e74c3c; font-weight:700; margin:0 0 6px;">Pedido Cancelado</h4>
                        <p class="text-muted" style="font-size:13px; margin:0;">Este pedido fue cancelado y no continuará en el flujo.</p>
                    </div>
                    @else
                    {{-- Pipeline de estados --}}
                    <div style="display:flex; align-items:center; justify-content:center;
                                gap:0; flex-wrap:nowrap; overflow-x:auto;
                                padding: 20px 8px 12px;">

                        @foreach($fPasos as $paso => $info)

                        @php
                            $completado  = (!$fCancelado && $paso < $fPaso);
                            $activo      = (!$fCancelado && $paso === $fPaso);
                            $pendiente   = (!$fCancelado && $paso > $fPaso);
                            $delay       = ($paso - 1) * 100;

                            $labelColor  = $completado ? '#1ab394' : ($activo ? '#1a7efb' : '#aab');
                        @endphp

                        {{-- Step card --}}
                        <div style="display:flex; flex-direction:column; align-items:center; min-width:110px;
                                    animation:stepIn .5s cubic-bezier(.34,1.56,.64,1) {{ $delay }}ms both;">

                            {{-- Circle --}}
                            @if($completado)
                            <div style="width:64px; height:64px; border-radius:50%;
                                        background:linear-gradient(135deg,#1ab394,#0fa37a);
                                        color:#fff; margin-bottom:10px;
                                        box-shadow:0 6px 20px rgba(26,179,148,.45);
                                        display:flex; align-items:center; justify-content:center;
                                        font-size:24px; flex-shrink:0;">
                                <i class="fa fa-check" style="animation:checkPop .4s cubic-bezier(.34,1.56,.64,1) {{ $delay + 200 }}ms both;"></i>
                            </div>
                            @elseif($activo)
                            {{-- Estado actual: check azul con anillo distintivo --}}
                            <div style="width:64px; height:64px; border-radius:50%;
                                        background:linear-gradient(135deg,#1a7efb,#0d6efd);
                                        color:#fff; margin-bottom:10px;
                                        box-shadow:0 6px 24px rgba(26,126,251,.5), 0 0 0 5px rgba(26,126,251,.2), 0 0 0 10px rgba(26,126,251,.08);
                                        display:flex; align-items:center; justify-content:center;
                                        font-size:24px; flex-shrink:0;">
                                <i class="fa fa-check" style="animation:checkPop .4s cubic-bezier(.34,1.56,.64,1) {{ $delay + 200 }}ms both;"></i>
                            </div>
                            @else
                            <div style="width:64px; height:64px; border-radius:50%;
                                        background:#e8eaf0; color:#c0c2cc; margin-bottom:10px;
                                        display:flex; align-items:center; justify-content:center;
                                        font-size:24px; flex-shrink:0;">
                                <i class="fa {{ $info['icon'] }}"></i>
                            </div>
                            @endif

                            {{-- Label --}}
                            <div style="text-align:center;">
                                <div style="font-size:13px; font-weight:700; color:{{ $labelColor }};">
                                    {{ $info['title'] }}
                                </div>
                                <div style="font-size:11px; color:{{ $labelColor }}; opacity:{{ $pendiente ? '.5' : '1' }};">
                                    @if($completado)
                                        <i class="fa fa-check-circle"></i> Completado
                                    @elseif($activo)
                                        <i class="fa fa-map-marker" style="animation:dotBlink 1s ease-in-out infinite;"></i> Estado actual
                                    @else
                                        <i class="fa fa-clock-o"></i> Pendiente
                                    @endif
                                </div>
                                @if($activo && $paso === 3)
                                <div style="font-size:10px; color:#f39c12; margin-top:4px; font-weight:700;
                                            background:rgba(243,156,18,.12); border-radius:8px; padding:2px 7px;">
                                    <i class="fa fa-trophy"></i> Oferta ganadora
                                </div>
                                @endif
                            </div>

                        </div>{{-- /step --}}

                        {{-- Connector (between steps) --}}
                        @if($paso < 6)
                        @php $connDelay = $delay + 80; @endphp
                        <div style="flex:1; min-width:24px; max-width:48px; height:4px; border-radius:4px;
                                    margin-bottom:28px; position:relative; overflow:hidden; background:#e0e3ee;">
                            @if($completado)
                            <div style="position:absolute; top:0; left:0; width:100%; height:100%;
                                        background:linear-gradient(90deg,#1ab394,#1a7efb);
                                        animation:connFill .6s ease {{ $connDelay }}ms both;
                                        border-radius:4px;"></div>
                            @endif
                        </div>
                        @endif

                        @endforeach

                    </div>
                    @endif

                    {{-- Info footer --}}
                    <div style="margin-top:24px; padding:14px 18px; background:#fff;
                                border-radius:12px; border:1px solid #e8eaf0;
                                display:flex; gap:20px; flex-wrap:wrap; font-size:12px; color:#666;">
                        <span><i class="fa fa-hashtag text-primary mr-1"></i>
                            <strong>Pedido #{{ $pedidoFlujoData['id'] }}</strong>
                        </span>
                        <span><i class="fa fa-user text-info mr-1"></i>
                            {{ $pedidoFlujoData['cliente'] }}
                        </span>
                        <span><i class="fa fa-user-circle-o text-muted mr-1"></i>
                            Por: {{ $pedidoFlujoData['registrado_por'] }}
                        </span>
                        <span><i class="fa fa-calendar text-muted mr-1"></i>
                            {{ \Carbon\Carbon::parse($pedidoFlujoData['created_at'])->format('d/m/Y H:i') }}
                        </span>
                    </div>

                    {{-- Panel dinámico de ofertas --}}
                    <div style="margin-top:14px; border-radius:12px; overflow:hidden; border:1px solid #ede9f7;">
                        <div style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%);
                                    padding:10px 18px;">
                            <span style="color:#fff; font-size:13px; font-weight:700;">
                                <i class="fa fa-tag mr-1"></i>
                                Ofertas asociadas
                            </span>
                        </div>
                        <div style="background:#fff; padding:12px 18px; max-height:200px; overflow-y:auto;">
                            @if (count($fOfertas) === 0)
                                <div class="text-center py-3 text-muted" style="font-size:12px;">
                                    <i class="fa fa-inbox fa-lg d-block mb-1" style="opacity:.3;"></i>
                                    Sin ofertas aún para este pedido.
                                </div>
                            @else
                                <table style="width:100%; font-size:11px; border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#f8f9fc; color:#888;">
                                            <th style="padding:4px 8px; text-align:left;">Oferta</th>
                                            <th style="padding:4px 8px; text-align:left;">Cliente</th>
                                            <th style="padding:4px 8px; text-align:right;">Total</th>
                                            <th style="padding:4px 8px; text-align:center;">Fecha</th>
                                            <th style="padding:4px 8px; text-align:center;">Estado</th>
                                            <th style="padding:4px 8px; text-align:center;">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($fOfertas as $fo)
                                        @php
                                            $foArr     = (array) $fo;
                                            $foEstado  = $foArr['estado'] ?? 'activa';
                                        @endphp
                                        <tr style="border-bottom:1px solid #f0f0f0; opacity:{{ $foEstado === 'cancelada' ? '.5' : '1' }};">
                                            <td style="padding:5px 8px; font-weight:700; color:{{ $foEstado === 'ganadora' ? '#1ab394' : ($foEstado === 'cancelada' ? '#aaa' : '#f39c12') }};">
                                                #{{ $foArr['id'] }}
                                                @if($foEstado === 'ganadora')
                                                    <i class="fa fa-trophy ml-1" style="color:#f39c12;"></i>
                                                @endif
                                            </td>
                                            <td style="padding:5px 8px; color:#555; text-decoration:{{ $foEstado === 'cancelada' ? 'line-through' : 'none' }};">
                                                {{ $foArr['nombre_cliente'] }}
                                            </td>
                                            <td style="padding:5px 8px; text-align:right; font-weight:700; color:#1ab394;">
                                                L. {{ number_format($foArr['total'], 2) }}
                                            </td>
                                            <td style="padding:5px 8px; text-align:center; color:#888;">
                                                {{ \Carbon\Carbon::parse($foArr['created_at'])->format('d/m/Y') }}
                                            </td>
                                            <td style="padding:5px 8px; text-align:center;">
                                                @if($foEstado === 'ganadora')
                                                    <span style="background:#d4edda; color:#155724; border-radius:10px; padding:2px 8px; font-size:10px; font-weight:700;">
                                                        <i class="fa fa-trophy"></i> Ganadora
                                                    </span>
                                                @elseif($foEstado === 'cancelada')
                                                    <span style="background:#f8d7da; color:#721c24; border-radius:10px; padding:2px 8px; font-size:10px; font-weight:700;">
                                                        <i class="fa fa-ban"></i> Cancelada
                                                    </span>
                                                @else
                                                    <span style="background:#e8f0fe; color:#1a7efb; border-radius:10px; padding:2px 8px; font-size:10px; font-weight:700;">
                                                        <i class="fa fa-circle"></i> Activa
                                                    </span>
                                                @endif
                                            </td>
                                            <td style="padding:5px 8px; text-align:center;">
                                                <div style="display:flex; gap:4px; justify-content:center; align-items:center;">
                                                    <a href="/oferta/imprimir/{{ $foArr['id'] }}" target="_blank"
                                                       title="Imprimir oferta"
                                                       style="color:#1a7efb; font-size:13px;">
                                                        <i class="fa fa-print"></i>
                                                    </a>
                                                    @if($foEstado === 'activa' && !$yaGanadoraExists)
                                                        <button type="button"
                                                                wire:click="confirmarGanadora({{ $foArr['id'] }}, {{ $pedidoFlujoData['id'] }})"
                                                                title="Seleccionar como oferta ganadora"
                                                                style="background:linear-gradient(135deg,#f39c12,#e67e22); color:#fff;
                                                                       border:none; border-radius:8px; padding:2px 8px;
                                                                       font-size:10px; font-weight:700; cursor:pointer;">
                                                            <i class="fa fa-trophy mr-1"></i> Ganadora
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>

                </div>{{-- /modal-body --}}

                <div class="modal-footer" style="border:none; padding:12px 24px 20px; background:#f8f9fc;">
                    <button type="button" wire:click="cerrarFlujo" class="btn btn-default"
                            style="border-radius:20px; padding:6px 22px;">
                        <i class="fa fa-times mr-1"></i> Cerrar
                    </button>
                    <a href="/flujo/pedido/editar/{{ $pedidoFlujoData['id'] }}"
                       class="btn btn-warning" style="border-radius:20px; padding:6px 22px; color:#fff;">
                        <i class="fa fa-pencil mr-1"></i> Editar pedido
                    </a>
                    @if (!$fCancelado && !$yaGanadoraExists)
                        <a href="/flujo/oferta/crear/{{ $pedidoFlujoData['id'] }}"
                           class="btn btn-primary" style="border-radius:20px; padding:6px 22px; color:#fff;">
                            <i class="fa fa-tag mr-1"></i> Crear oferta
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </div>
    @endif

    {{-- ===== MODAL CONFIRMACIÓN: OFERTA GANADORA ===== --}}
    @if ($showModalGanadora)
    <div class="modal fade show"
         style="display:block; background:rgba(0,0,0,.65); z-index:1080;"
         tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius:12px; border:none; box-shadow:0 10px 40px rgba(0,0,0,.35);">
                <div class="modal-header" style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%); border:none;">
                    <h5 class="modal-title text-white m-0">
                        <i class="fa fa-trophy"></i> &nbsp;Seleccionar Oferta Ganadora
                    </h5>
                    <button type="button" class="close text-white" wire:click="cancelarSeleccionGanadora" style="opacity:1;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="fa fa-trophy fa-2x mb-2" style="color:#f39c12;"></i>
                    <p class="mb-1">
                        ¿Confirmar la <strong>Oferta #{{ $ofertaGanadoraId }}</strong> como ganadora?
                    </p>
                    <small class="text-muted">
                        Las demás ofertas de este pedido quedarán <strong>canceladas</strong> y el pedido avanzará a la etapa de <strong>Pre Factura</strong>.
                    </small>
                </div>
                <div class="modal-footer" style="border:none; justify-content:center;">
                    <button type="button" wire:click="cancelarSeleccionGanadora" class="btn btn-default">
                        <i class="fa fa-times"></i> No, volver
                    </button>
                    <button type="button" wire:click="seleccionarGanadora" class="btn btn-warning" style="color:#fff;">
                        <i class="fa fa-trophy"></i> &nbsp;Sí, confirmar ganadora
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== CSS ANIMACIONES FLUJO ===== --}}
    <style>
        @keyframes stepIn {
            from { opacity:0; transform:translateY(20px) scale(.85); }
            to   { opacity:1; transform:translateY(0) scale(1); }
        }
        @keyframes stepPulse {
            0%,100% { box-shadow:0 6px 24px var(--sp-glow,rgba(26,126,251,.4)), 0 0 0 6px rgba(26,126,251,.08); transform:scale(1); }
            50%      { box-shadow:0 6px 32px var(--sp-glow,rgba(26,126,251,.55)), 0 0 0 12px rgba(26,126,251,.06); transform:scale(1.07); }
        }
        @keyframes checkPop {
            from { opacity:0; transform:scale(0) rotate(-45deg); }
            to   { opacity:1; transform:scale(1) rotate(0deg); }
        }
        @keyframes connFill {
            from { width:0; }
            to   { width:100%; }
        }
        @keyframes dotBlink {
            0%,100% { opacity:1; } 50% { opacity:.3; }
        }
        @keyframes cancelShake {
            0%,100% { transform:rotate(0); }
            20%,60% { transform:rotate(-10deg) scale(1.05); }
            40%,80% { transform:rotate(10deg) scale(1.05); }
        }
    </style>

</div>
