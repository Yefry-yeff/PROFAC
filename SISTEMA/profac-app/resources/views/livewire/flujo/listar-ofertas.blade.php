<div>
<style>
    /* ── Stat cards ─────────────────────────────────────────────────────── */
    .ofc-stat {
        border-radius: 16px;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 4px 18px rgba(0,0,0,.07);
        color: #fff;
        transition: transform .2s, box-shadow .2s;
    }
    .ofc-stat:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.13); }
    .ofc-stat .stat-icon { font-size: 32px; opacity: .75; }
    .ofc-stat .stat-num  { font-size: 28px; font-weight: 800; line-height: 1; }
    .ofc-stat .stat-lbl  { font-size: 12px; opacity: .88; text-transform: uppercase; letter-spacing: .6px; margin-top: 3px; }

    /* ── Panel buscar pedido ─────────────────────────────────────────────── */
    .pedido-search-panel {
        background: linear-gradient(135deg,#e8f5e9,#f1f8e9);
        border: 1px solid #c8e6c9;
        border-radius: 14px;
        padding: 24px 28px 20px;
        margin-bottom: 24px;
    }
    .pedido-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 10px;
        background: #fff;
        border: 1px solid #e8f5e9;
        margin-bottom: 8px;
        transition: box-shadow .15s, border-color .15s;
    }
    .pedido-row:hover { box-shadow: 0 4px 14px rgba(0,0,0,.08); border-color: #a5d6a7; }

    /* ── Estado badges ───────────────────────────────────────────────────── */
    .estado-badge {
        display: inline-block;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 12px;
        white-space: nowrap;
    }
    .estado-ganadora  { background: #e8f5e9; color: #2e7d32; }
    .estado-activa    { background: #e3f2fd; color: #1565c0; }
    .estado-cancelada { background: #fce4ec; color: #b71c1c; }

    /* ── Tabla ───────────────────────────────────────────────────────────── */
    .ofertas-table th {
        background: linear-gradient(135deg,#00695c,#00897b);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
        border: none !important;
        padding: 10px 12px;
    }
    .ofertas-table tbody tr { transition: background .12s; }
    .ofertas-table tbody tr:hover { background: #f0fdf4 !important; }
    .ofertas-table td { vertical-align: middle !important; font-size: 13px; }

    /* ── Filtros ─────────────────────────────────────────────────────────── */
    .filter-label {
        font-size: 11px;
        font-weight: 700;
        color: #546e7a;
        letter-spacing: .5px;
        text-transform: uppercase;
        margin-bottom: 5px;
        display: block;
    }
</style>

    {{-- ===== ENCABEZADO ===== --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="fa fa-file-text-o" style="color:#00897b;"></i> Ofertas</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('flujo.ventas') }}">Ventas</a></li>
                <li class="breadcrumb-item active"><strong>Ofertas</strong></li>
            </ol>
        </div>
        <div class="col-lg-2 d-flex align-items-center justify-content-end">
            <a href="{{ route('flujo.ventas') }}" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- ===== ESTADÍSTICAS ===== --}}
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="ofc-stat" style="background:linear-gradient(135deg,#37474f,#546e7a);">
                    <div class="stat-icon"><i class="fa fa-file-text-o"></i></div>
                    <div>
                        <div class="stat-num">{{ $statsTotal }}</div>
                        <div class="stat-lbl">Total Ofertas</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="ofc-stat" style="background:linear-gradient(135deg,#2e7d32,#43a047);">
                    <div class="stat-icon"><i class="fa fa-trophy"></i></div>
                    <div>
                        <div class="stat-num">{{ $statsGanadoras }}</div>
                        <div class="stat-lbl">Ganadoras</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="ofc-stat" style="background:linear-gradient(135deg,#1565c0,#1e88e5);">
                    <div class="stat-icon"><i class="fa fa-clock-o"></i></div>
                    <div>
                        <div class="stat-num">{{ $statsActivas }}</div>
                        <div class="stat-lbl">Activas</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="ofc-stat" style="background:linear-gradient(135deg,#b71c1c,#e53935);">
                    <div class="stat-icon"><i class="fa fa-ban"></i></div>
                    <div>
                        <div class="stat-num">{{ $statsCanceladas }}</div>
                        <div class="stat-lbl">Canceladas</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== PANEL: BUSCAR PEDIDO PARA OFERTAR ===== --}}
        <div class="mb-4">
            <button type="button"
                    wire:click="togglePanelPedido"
                    style="background:linear-gradient(135deg,#00897b,#00bcd4); color:#fff;
                           border:none; border-radius:10px; padding:10px 22px; font-weight:700;
                           font-size:13px; box-shadow:0 3px 12px rgba(0,137,123,.3);
                           display:inline-flex; align-items:center; gap:8px; cursor:pointer;
                           transition:transform .15s, box-shadow .15s;"
                    onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 18px rgba(0,137,123,.4)';"
                    onmouseout="this.style.transform='';this.style.boxShadow='0 3px 12px rgba(0,137,123,.3)';">
                <i class="fa fa-{{ $showPanelPedido ? 'chevron-up' : 'search' }}"></i>
                {{ $showPanelPedido ? 'Cerrar búsqueda' : 'Buscar Pedido para Ofertar' }}
            </button>
        </div>

        @if($showPanelPedido)
        <div class="pedido-search-panel">
            <div class="mb-3">
                <h5 style="color:#2e7d32; font-weight:800; margin:0 0 4px;">
                    <i class="fa fa-search mr-2"></i>Buscar Pedido
                </h5>
                <p style="color:#546e7a; font-size:13px; margin:0;">
                    Busca por número de pedido o nombre de cliente.
                    <strong>Puedes crear múltiples ofertas por pedido.</strong>
                </p>
            </div>

            <div class="row align-items-end mb-3">
                <div class="col-md-6">
                    <label class="filter-label"><i class="fa fa-search mr-1"></i> Nº de pedido o cliente</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background:#00897b; color:#fff; border-color:#00897b;">
                                <i class="fa fa-search"></i>
                            </span>
                        </div>
                        <input type="text"
                               wire:model.debounce.350ms="busquedaPedido"
                               class="form-control"
                               placeholder="Ej: 142  o  Distribuciones Valencia..."
                               autocomplete="off"
                               style="font-size:14px; border-color:#a5d6a7;">
                    </div>
                    @if(strlen(trim($busquedaPedido)) > 0 && strlen(trim($busquedaPedido)) < 2)
                        <small class="text-muted mt-1 d-block">Escribe al menos 2 caracteres</small>
                    @endif
                </div>
            </div>

            @if(count($pedidosEncontrados) > 0)
                <div style="max-height:340px; overflow-y:auto;">
                    @foreach($pedidosEncontrados as $ped)
                    @php $p = (array)$ped; @endphp
                    <div class="pedido-row" style="cursor:pointer;" wire:click="abrirModalPedido({{ $p['id'] }})"
                         onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.08)';this.style.borderColor='#a5d6a7';"
                         onmouseout="this.style.boxShadow='';this.style.borderColor='#e8f5e9';">
                        <div style="flex-shrink:0;">
                            <span style="background:linear-gradient(135deg,#1a73e8,#5ea3f5); color:#fff;
                                         border-radius:10px; padding:4px 12px; font-weight:800; font-size:14px;">
                                #{{ $p['id'] }}
                            </span>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:700; color:#2c3e50; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $p['cliente'] }}
                            </div>
                            <div style="font-size:11px; color:#90a4ae;">
                                RTN: {{ $p['rtn'] ?: '—' }}
                                &nbsp;·&nbsp;
                                {{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}
                                &nbsp;·&nbsp;
                                <em>{{ $p['registrado_por'] ?: '—' }}</em>
                            </div>
                        </div>
                        <div style="flex-shrink:0;">
                            @php
                                $estMap = ['pendiente'=>['#e3f2fd','#1565c0'],'pre_factura'=>['#fff8e1','#f57f17'],'activo'=>['#e8f5e9','#2e7d32'],'cancelado'=>['#fce4ec','#b71c1c']];
                                $col = $estMap[$p['estado']] ?? ['#f5f5f5','#546e7a'];
                            @endphp
                            <span style="background:{{ $col[0] }}; color:{{ $col[1] }}; border-radius:20px;
                                         padding:3px 12px; font-size:11px; font-weight:700;">
                                {{ ucfirst(str_replace('_',' ', $p['estado'])) }}
                            </span>
                        </div>
                        <div style="flex-shrink:0; text-align:center; min-width:80px;">
                            <div style="font-size:11px; color:#78909c;">Ofertas</div>
                            <div style="font-weight:700; color:{{ $p['total_ofertas'] > 0 ? '#00897b' : '#90a4ae' }};">
                                {{ $p['total_ofertas'] }}
                                @if($p['has_ganadora'] > 0)
                                    <i class="fa fa-trophy text-warning ml-1" title="Tiene oferta ganadora"></i>
                                @endif
                            </div>
                        </div>
                        <div style="flex-shrink:0;">
                            @if($p['has_ganadora'] > 0)
                                <span style="background:#fff8e1; color:#f57f17; border-radius:20px;
                                             padding:6px 14px; font-size:11px; font-weight:700;
                                             display:inline-flex; align-items:center; gap:5px;">
                                    <i class="fa fa-trophy"></i> Ganadora
                                </span>
                            @endif
                            <span style="background:linear-gradient(135deg,#546e7a,#78909c); color:#fff;
                                         border-radius:20px; padding:6px 14px; font-size:11px; font-weight:700;
                                         display:inline-flex; align-items:center; gap:5px; margin-left:4px;">
                                <i class="fa fa-ellipsis-h"></i> Opciones
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @elseif(strlen(trim($busquedaPedido)) >= 2)
                <div class="text-center py-4">
                    <i class="fa fa-search fa-2x mb-2" style="color:#c8e6c9; display:block;"></i>
                    <p style="color:#78909c; font-size:13px; margin:0;">
                        No se encontraron pedidos activos con ese criterio.
                    </p>
                </div>
            @endif
        </div>
        @endif

        {{-- ===== TABLA DE OFERTAS ===== --}}
        <div class="ibox" style="border-radius:14px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.06);">
            <div class="ibox-title d-flex align-items-center justify-content-between"
                 style="background:linear-gradient(135deg,#00695c 0%,#00897b 100%); border:none; padding:14px 22px;">
                <h5 class="m-0" style="color:#fff; font-size:15px;">
                    <i class="fa fa-list mr-2"></i>Registro de Ofertas
                </h5>
                <span style="background:rgba(255,255,255,.18); color:#fff; border-radius:20px;
                             padding:3px 14px; font-size:13px; font-weight:700;">
                    {{ $totalOfertas }} resultado(s)
                </span>
            </div>

            <div class="ibox-content" style="padding:22px;">

                {{-- Filtros --}}
                <div class="row mb-4" style="align-items:flex-end;">
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="filter-label"><i class="fa fa-user mr-1"></i> Cliente / RTN</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fa fa-user text-muted"></i></span>
                            </div>
                            <input type="text"
                                   wire:model.debounce.300ms="busquedaCliente"
                                   class="form-control border-left-0"
                                   placeholder="Nombre o RTN..."
                                   style="border-radius:0 8px 8px 0;">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 mb-2">
                        <label class="filter-label"><i class="fa fa-hashtag mr-1"></i> Pedido #</label>
                        <input type="number" wire:model.debounce.400ms="filtroPedido"
                               class="form-control" placeholder="ID..." style="border-radius:8px;">
                    </div>
                    <div class="col-lg-2 col-md-3 mb-2">
                        <label class="filter-label"><i class="fa fa-tag mr-1"></i> Estado</label>
                        <select wire:model="filtroEstado" class="form-control" style="border-radius:8px;">
                            <option value="">Todos</option>
                            <option value="activa">Activa</option>
                            <option value="ganadora">Ganadora</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 mb-2">
                        <label class="filter-label"><i class="fa fa-calendar mr-1"></i> Fecha</label>
                        <input type="date" wire:model="filtroFecha"
                               class="form-control" style="border-radius:8px;">
                    </div>
                    <div class="col-lg-2 col-md-3 mb-2">
                        <label class="filter-label">&nbsp;</label>
                        <button type="button" wire:click="limpiarFiltros"
                                class="btn btn-default btn-block" style="border-radius:8px;">
                            <i class="fa fa-times mr-1 text-muted"></i> Limpiar
                        </button>
                    </div>
                    <div class="col-lg-1 mb-2 d-flex align-items-end">
                        <small class="text-muted" style="white-space:nowrap;">
                            Pág {{ $pagina }}/{{ $totalPaginas ?: 1 }}
                        </small>
                    </div>
                </div>

                {{-- Tabla --}}
                <div class="table-responsive">
                    <table class="table ofertas-table" style="border-collapse:separate; border-spacing:0;">
                        <thead>
                            <tr>
                                <th style="width:70px;">#</th>
                                <th style="width:80px;">Pedido</th>
                                <th>Cliente</th>
                                <th style="width:105px;">Estado</th>
                                <th style="width:65px; text-align:center;">Prods.</th>
                                <th style="width:115px; text-align:right;">Total</th>
                                <th style="width:80px; text-align:center;">Desc.</th>
                                <th>Registrado</th>
                                <th style="width:100px;">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ofertas as $oferta)
                            <tr>
                                <td>
                                    <span style="background:linear-gradient(135deg,#00695c,#00897b); color:#fff;
                                                 border-radius:20px; padding:3px 10px; font-size:12px; font-weight:700;">
                                        #{{ $oferta->id }}
                                    </span>
                                </td>
                                <td>
                                    @if($oferta->pedido_id)
                                        <a href="/flujo/pedido/editar/{{ $oferta->pedido_id }}"
                                           style="background:#e3f2fd; color:#1565c0; border-radius:20px;
                                                  padding:3px 10px; font-size:12px; font-weight:700; text-decoration:none;"
                                           title="Ver pedido #{{ $oferta->pedido_id }}">
                                            <i class="fa fa-shopping-cart" style="font-size:10px;"></i> #{{ $oferta->pedido_id }}
                                        </a>
                                    @else
                                        <span class="text-muted" style="font-size:12px;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#2c3e50; max-width:180px;
                                                white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"
                                         title="{{ $oferta->nombre_cliente }}">
                                        {{ $oferta->nombre_cliente ?: '—' }}
                                    </div>
                                    @if($oferta->RTN)
                                        <small style="color:#90a4ae;">RTN: {{ $oferta->RTN }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $est = $oferta->estado ?? 'activa';
                                        $ico = match($est) { 'ganadora'=>'fa-trophy','cancelada'=>'fa-ban',default=>'fa-clock-o' };
                                    @endphp
                                    <span class="estado-badge estado-{{ $est }}">
                                        <i class="fa {{ $ico }} mr-1" style="font-size:10px;"></i>
                                        {{ ucfirst($est) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span style="background:#eceff1; color:#546e7a; border-radius:20px;
                                                 padding:3px 10px; font-size:12px; font-weight:700;">
                                        {{ $oferta->total_productos }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <strong style="color:#2e7d32;">L {{ $oferta->total }}</strong>
                                </td>
                                <td class="text-center">
                                    @if($oferta->porc_descuento > 0)
                                        <span style="background:#fff3e0; color:#e65100; border-radius:20px;
                                                     padding:3px 10px; font-size:11px; font-weight:700;">
                                            {{ $oferta->porc_descuento }}%
                                        </span>
                                    @else
                                        <span style="color:#cfd8dc;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-size:12px; color:#546e7a;">
                                        <i class="fa fa-user-circle text-muted mr-1"></i>{{ $oferta->registrado_por ?: '—' }}
                                    </div>
                                    @if($oferta->vendedor && $oferta->vendedor !== $oferta->registrado_por)
                                        <small style="color:#90a4ae; font-size:11px;">Vend: {{ $oferta->vendedor }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-size:12px; color:#546e7a; white-space:nowrap;">
                                        <i class="fa fa-calendar-o mr-1" style="color:#a5d6a7;"></i>
                                        {{ \Carbon\Carbon::parse($oferta->created_at)->format('d/m/Y') }}
                                    </div>
                                    <small style="color:#b0bec5; font-size:10px;">
                                        {{ \Carbon\Carbon::parse($oferta->created_at)->format('H:i') }}
                                    </small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="fa fa-file-text-o fa-3x mb-3" style="color:#b2dfdb; display:block;"></i>
                                    <h6 style="color:#78909c; font-weight:700;">Sin resultados</h6>
                                    <p style="color:#90a4ae; font-size:13px; margin:0;">
                                        No se encontraron ofertas con los filtros aplicados.
                                    </p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($totalPaginas > 1)
                <div class="d-flex justify-content-center mt-4">
                    <div style="display:inline-flex; gap:4px; align-items:center;">
                        <button wire:click="paginaAnterior" class="btn btn-sm btn-default"
                                style="border-radius:8px; {{ $pagina <= 1 ? 'opacity:.4;' : '' }}"
                                {{ $pagina <= 1 ? 'disabled' : '' }}>
                            <i class="fa fa-chevron-left"></i>
                        </button>
                        @for ($p = max(1,$pagina-2); $p <= min($totalPaginas,$pagina+2); $p++)
                            <button wire:click="irPagina({{ $p }})" class="btn btn-sm"
                                    style="border-radius:8px; min-width:34px;
                                           {{ $p==$pagina ? 'background:linear-gradient(135deg,#00695c,#00897b);color:#fff;border:none;font-weight:700;' : 'background:#fff;color:#546e7a;border:1px solid #e0e0e0;' }}">
                                {{ $p }}
                            </button>
                        @endfor
                        <button wire:click="paginaSiguiente" class="btn btn-sm btn-default"
                                style="border-radius:8px; {{ $pagina>=$totalPaginas ? 'opacity:.4;' : '' }}"
                                {{ $pagina>=$totalPaginas ? 'disabled' : '' }}>
                            <i class="fa fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                @endif

            </div>
        </div>

    </div>

    {{-- ===== MODAL DETALLE / ACCIONES DE PEDIDO ===== --}}
    @if($showModalPedido && $pedidoSeleccionado)
    <div style="position:fixed; inset:0; z-index:1060; display:flex; align-items:center; justify-content:center; padding:20px;
                background:rgba(15,15,35,.58); backdrop-filter:blur(3px); -webkit-backdrop-filter:blur(3px);">
        {{-- Backdrop --}}
        <div style="position:absolute; inset:0;" wire:click="cerrarModalPedido"></div>

        {{-- Panel --}}
        <div style="position:relative; background:#fff; border-radius:18px; width:100%; max-width:580px;
                    max-height:92vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.28);
                    margin:16px; z-index:1;">

            {{-- Header --}}
            <div style="background:linear-gradient(135deg,#1565c0,#1e88e5); padding:20px 24px;
                        border-radius:18px 18px 0 0; display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h5 style="color:#fff; margin:0; font-weight:800; font-size:16px;">
                        <i class="fa fa-shopping-cart mr-2"></i>Pedido #{{ $pedidoSeleccionado['id'] }}
                    </h5>
                    <div style="color:rgba(255,255,255,.8); font-size:12px; margin-top:3px;">
                        {{ $pedidoSeleccionado['cliente'] }}
                        &nbsp;·&nbsp;
                        {{ \Carbon\Carbon::parse($pedidoSeleccionado['created_at'])->format('d/m/Y') }}
                    </div>
                </div>
                <button type="button" wire:click="cerrarModalPedido"
                        style="background:rgba(255,255,255,.2); border:none; color:#fff; border-radius:50%;
                               width:32px; height:32px; font-size:16px; cursor:pointer;
                               display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <div style="padding:24px;">

                {{-- Mensajes --}}
                @if($mensajeExito)
                <div style="background:#e8f5e9; border:1px solid #c8e6c9; border-radius:10px;
                            padding:10px 14px; color:#2e7d32; font-weight:600; font-size:13px;
                            margin-bottom:16px; display:flex; gap:8px; align-items:center;">
                    <i class="fa fa-check-circle fa-lg"></i> {{ $mensajeExito }}
                </div>
                @endif
                @if($mensajeError)
                <div style="background:#fce4ec; border:1px solid #f8bbd0; border-radius:10px;
                            padding:10px 14px; color:#b71c1c; font-weight:600; font-size:13px;
                            margin-bottom:16px; display:flex; gap:8px; align-items:center;">
                    <i class="fa fa-exclamation-circle fa-lg"></i> {{ $mensajeError }}
                </div>
                @endif

                {{-- Info --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
                    <div style="background:#f8f9fa; border-radius:10px; padding:12px 14px;">
                        <div style="font-size:11px; color:#90a4ae; font-weight:700; text-transform:uppercase; margin-bottom:4px;">Cliente</div>
                        <div style="font-weight:700; color:#2c3e50; font-size:13px;">{{ $pedidoSeleccionado['cliente'] }}</div>
                        <div style="font-size:11px; color:#90a4ae;">{{ $pedidoSeleccionado['rtn'] ?: '—' }}</div>
                    </div>
                    <div style="background:#f8f9fa; border-radius:10px; padding:12px 14px;">
                        <div style="font-size:11px; color:#90a4ae; font-weight:700; text-transform:uppercase; margin-bottom:4px;">Estado flujo</div>
                        @php
                            $flujoEst = $pedidoSeleccionado['estatus_flujo'] ?? null;
                            $estMapMod = [
                                'pedido'       => ['#e3f2fd','#1565c0','fa-shopping-cart'],
                                'Ofertas'      => ['#fff3e0','#e65100','fa-tag'],
                                'prefactura'   => ['#f3e5f5','#6a1b9a','fa-file-o'],
                                'factura'      => ['#e8f5e9','#1b5e20','fa-file-text'],
                                'Entrega Cobro'=> ['#e0f7fa','#00695c','fa-truck'],
                                'cancelado'    => ['#fce4ec','#b71c1c','fa-ban'],
                            ];
                            $colMod = $estMapMod[$flujoEst] ?? ['#f5f5f5','#546e7a','fa-question-circle'];
                        @endphp
                        <span style="background:{{ $colMod[0] }}; color:{{ $colMod[1] }};
                                     border-radius:20px; padding:3px 12px; font-size:12px; font-weight:700;">
                            <i class="fa {{ $colMod[2] }} mr-1"></i>{{ $flujoEst ?: ucfirst(str_replace('_', ' ', $pedidoSeleccionado['estado'])) }}
                        </span>
                    </div>
                    <div style="background:#f8f9fa; border-radius:10px; padding:12px 14px;">
                        <div style="font-size:11px; color:#90a4ae; font-weight:700; text-transform:uppercase; margin-bottom:4px;">Registrado por</div>
                        <div style="font-size:13px; color:#546e7a;">{{ $pedidoSeleccionado['registrado_por'] ?: '—' }}</div>
                    </div>
                    <div style="background:#f8f9fa; border-radius:10px; padding:12px 14px;">
                        <div style="font-size:11px; color:#90a4ae; font-weight:700; text-transform:uppercase; margin-bottom:4px;">Fecha</div>
                        <div style="font-size:13px; color:#546e7a;">
                            {{ \Carbon\Carbon::parse($pedidoSeleccionado['created_at'])->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>

                {{-- Productos --}}
                @if(count($pedidoDetalles) > 0)
                <div style="margin-bottom:20px;">
                    <h6 style="font-weight:800; color:#37474f; margin-bottom:10px; font-size:12px;
                                text-transform:uppercase; letter-spacing:.4px;">
                        <i class="fa fa-list mr-1 text-muted"></i>Productos ({{ count($pedidoDetalles) }})
                    </h6>
                    <div style="max-height:180px; overflow-y:auto; border:1px solid #eceff1; border-radius:10px;">
                        @foreach($pedidoDetalles as $det)
                        @php $d = (array)$det; @endphp
                        <div style="display:flex; justify-content:space-between; align-items:center;
                                    padding:8px 12px; font-size:12px;
                                    {{ !$loop->last ? 'border-bottom:1px solid #f5f5f5;' : '' }}">
                            <span style="color:#37474f; font-weight:600;">{{ $d['nombre_producto'] ?: '—' }}</span>
                            <span style="background:#eceff1; color:#546e7a; border-radius:20px;
                                         padding:2px 10px; font-weight:700; white-space:nowrap;">
                                ×{{ $d['cantidad'] }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div style="text-align:center; padding:14px; background:#f8f9fa; border-radius:10px; margin-bottom:20px;">
                    <i class="fa fa-inbox text-muted d-block fa-lg mb-1"></i>
                    <small style="color:#90a4ae;">Sin productos registrados</small>
                </div>
                @endif

                {{-- Conteo de ofertas --}}
                <div style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap;">
                    <div style="background:#e3f2fd; color:#1565c0; border-radius:10px;
                                padding:7px 14px; font-size:12px; font-weight:700;">
                        <i class="fa fa-file-text-o mr-1"></i>{{ $pedidoSeleccionado['total_ofertas'] }} oferta(s)
                    </div>
                    @if($pedidoSeleccionado['has_ganadora'] > 0)
                    <div style="background:#fff8e1; color:#f57f17; border-radius:10px;
                                padding:7px 14px; font-size:12px; font-weight:700;">
                        <i class="fa fa-trophy mr-1"></i>Tiene oferta ganadora
                    </div>
                    @endif
                </div>

                {{-- Acciones / Confirmación --}}
                @if($confirmAccion === 'anular')
                <div style="background:#fce4ec; border:2px solid #ef9a9a; border-radius:12px;
                            padding:18px; text-align:center;">
                    <i class="fa fa-exclamation-triangle fa-2x mb-2" style="color:#c62828; display:block;"></i>
                    <p style="font-weight:700; color:#c62828; margin:0 0 4px; font-size:14px;">¿Confirmar anulación?</p>
                    <p style="font-size:12px; color:#e57373; margin:0 0 12px;">
                        El pedido #{{ $pedidoSeleccionado['id'] }} quedará cancelado y su flujo se actualizará.
                    </p>
                    <textarea wire:model.defer="motivoAnulacion"
                              placeholder="Motivo de anulación (obligatorio)..."
                              rows="2"
                              style="width:100%; border-radius:8px; border:1px solid #ef9a9a;
                                     padding:8px 12px; font-size:12px; resize:vertical;
                                     margin-bottom:12px; outline:none;"
                    ></textarea>
                    @if($mensajeError)
                    <div style="color:#c62828; font-size:12px; font-weight:700; margin-bottom:10px;">
                        <i class="fa fa-exclamation-circle mr-1"></i>{{ $mensajeError }}
                    </div>
                    @endif
                    <div style="display:flex; gap:8px; justify-content:center;">
                        <button type="button" wire:click="cancelarConfirmacion"
                                style="background:#f5f5f5; color:#546e7a; border:1px solid #e0e0e0;
                                       border-radius:8px; padding:8px 22px; font-weight:700; cursor:pointer;">
                            <i class="fa fa-arrow-left mr-1"></i>Volver
                        </button>
                        <button type="button" wire:click="anularPedido"
                                style="background:#c62828; color:#fff; border:none;
                                       border-radius:8px; padding:8px 22px; font-weight:700; cursor:pointer;">
                            <i class="fa fa-ban mr-1"></i>Sí, Anular
                        </button>
                    </div>
                </div>

                @elseif($confirmAccion === 'duplicar')
                <div style="background:#e3f2fd; border:2px solid #90caf9; border-radius:12px;
                            padding:18px; text-align:center;">
                    <i class="fa fa-copy fa-2x mb-2" style="color:#1565c0; display:block;"></i>
                    <p style="font-weight:700; color:#1565c0; margin:0 0 4px; font-size:14px;">¿Duplicar este pedido?</p>
                    <p style="font-size:12px; color:#1e88e5; margin:0 0 16px;">
                        Se creará un nuevo pedido con los mismos productos y un flujo nuevo independiente.
                    </p>
                    <div style="display:flex; gap:8px; justify-content:center;">
                        <button type="button" wire:click="cancelarConfirmacion"
                                style="background:#f5f5f5; color:#546e7a; border:1px solid #e0e0e0;
                                       border-radius:8px; padding:8px 22px; font-weight:700; cursor:pointer;">
                            <i class="fa fa-arrow-left mr-1"></i>Volver
                        </button>
                        <button type="button" wire:click="duplicarPedido"
                                style="background:#1565c0; color:#fff; border:none;
                                       border-radius:8px; padding:8px 22px; font-weight:700; cursor:pointer;">
                            <i class="fa fa-copy mr-1"></i>Sí, Duplicar
                        </button>
                    </div>
                </div>

                @else
                {{-- Menú de 3 opciones --}}
                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
                    {{-- Anular --}}
                    @if($pedidoSeleccionado['estado'] !== 'cancelado')
                    <button type="button" wire:click="confirmarAccion('anular')"
                            style="background:linear-gradient(135deg,#c62828,#e53935); color:#fff;
                                   border:none; border-radius:14px; padding:16px 8px; cursor:pointer;
                                   font-weight:700; font-size:13px; display:flex; flex-direction:column;
                                   align-items:center; gap:8px; transition:transform .15s, box-shadow .15s;"
                            onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(198,40,40,.4)';"
                            onmouseout="this.style.transform='';this.style.boxShadow='';">
                        <i class="fa fa-ban fa-lg"></i>
                        Anular Pedido
                    </button>
                    @else
                    <div style="background:#f5f5f5; border-radius:14px; padding:16px 8px;
                                font-size:12px; color:#90a4ae; display:flex; flex-direction:column;
                                align-items:center; gap:8px; text-align:center;">
                        <i class="fa fa-ban fa-lg"></i>
                        Ya cancelado
                    </div>
                    @endif

                    {{-- Duplicar --}}
                    <button type="button" wire:click="confirmarAccion('duplicar')"
                            style="background:linear-gradient(135deg,#1565c0,#1e88e5); color:#fff;
                                   border:none; border-radius:14px; padding:16px 8px; cursor:pointer;
                                   font-weight:700; font-size:13px; display:flex; flex-direction:column;
                                   align-items:center; gap:8px; transition:transform .15s, box-shadow .15s;"
                            onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(21,101,192,.4)';"
                            onmouseout="this.style.transform='';this.style.boxShadow='';">
                        <i class="fa fa-copy fa-lg"></i>
                        Duplicar Pedido
                    </button>

                    {{-- Agregar Oferta --}}
                    @if($pedidoSeleccionado['has_ganadora'] == 0 && $pedidoSeleccionado['estado'] !== 'cancelado')
                    <a href="{{ route('flujo.oferta') }}?pedidoId={{ $pedidoSeleccionado['id'] }}"
                       style="background:linear-gradient(135deg,#00897b,#26a69a); color:#fff;
                              border-radius:14px; padding:16px 8px; cursor:pointer;
                              font-weight:700; font-size:13px; display:flex; flex-direction:column;
                              align-items:center; gap:8px; text-decoration:none;
                              transition:transform .15s, box-shadow .15s;"
                       onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(0,137,123,.4)';"
                       onmouseout="this.style.transform='';this.style.boxShadow='';">
                        <i class="fa fa-plus fa-lg"></i>
                        Agregar Oferta
                    </a>
                    @else
                    <div style="background:#f5f5f5; border-radius:14px; padding:16px 8px;
                                font-size:12px; color:#90a4ae; display:flex; flex-direction:column;
                                align-items:center; gap:8px; text-align:center;">
                        <i class="fa fa-lock fa-lg"></i>
                        Ganadora<br>definida
                    </div>
                    @endif
                </div>
                @endif

            </div>
        </div>
    </div>
    @endif

    <script>
        window.addEventListener('abrir-nueva-pestana', function(e) {
            window.open(e.detail.url, '_blank');
        });
    </script>
</div>

