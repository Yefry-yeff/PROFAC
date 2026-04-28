<div>
<style>
    .ofr-th {
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
        background: #fff3e0;
        font-size: 12px;
        font-weight: 700;
        color: #bf360c;
        padding: 10px 12px;
        border-bottom: 2px solid #ffcc80 !important;
        transition: background .15s;
    }
    .ofr-th:hover { background: #ffe0b2; }
    .ofr-th.sin-sort { cursor: default; }
    .ofr-th .sort-icon { margin-left: 4px; opacity: .35; font-size: 11px; }
    .ofr-th .sort-icon.active { opacity: 1; color: #e65100; }
    .ofr-badge {
        display: inline-block;
        border-radius: 20px;
        padding: 3px 11px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }
    .ofr-row { transition: background .1s; }
    .ofr-row:hover > td { background: #fff8f0 !important; cursor: pointer; }
    .sin-th {
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
        background: #fce4ec;
        font-size: 12px;
        font-weight: 700;
        color: #880e4f;
        padding: 10px 12px;
        border-bottom: 2px solid #f48fb1 !important;
        transition: background .15s;
    }
    .sin-th:hover { background: #f8bbd0; }
    .sin-th.sin-sort { cursor: default; }
    .sin-th .sort-icon { margin-left: 4px; opacity: .35; font-size: 11px; }
    .sin-th .sort-icon.active { opacity: 1; color: #c2185b; }
    .sin-row { transition: background .1s; }
    .sin-row:hover > td { background: #fdf2f7 !important; cursor: pointer; }
</style>

    {{-- ── Flash ──────────────────────────────────────────────────────────── --}}
    @if($mensajeExito)
    <div class="alert alert-success alert-dismissible mb-3"
         style="border-radius:10px; font-size:13px; padding:10px 16px; border:none; background:#e8f5e9; color:#2e7d32;">
        <i class="fa fa-check-circle mr-2"></i>{{ $mensajeExito }}
        <button type="button" wire:click="$set('mensajeExito','')" class="close" style="top:6px;">&times;</button>
    </div>
    @endif
    @if($mensajeError)
    <div class="alert alert-danger alert-dismissible mb-3"
         style="border-radius:10px; font-size:13px; padding:10px 16px; border:none; background:#fce4ec; color:#b71c1c;">
        <i class="fa fa-exclamation-circle mr-2"></i>{{ $mensajeError }}
        <button type="button" wire:click="$set('mensajeError','')" class="close" style="top:6px;">&times;</button>
    </div>
    @endif

    {{-- ── Barra título + acción ──────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <span style="font-weight:700; color:#e65100; font-size:14px;">
            <i class="fa fa-file-text-o mr-1"></i> Pedidos y Ofertas
        </span>
        <button type="button" wire:click="nuevaOfertaSinPedido"
                style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                       border-radius:8px; padding:7px 16px; font-size:13px; font-weight:700; cursor:pointer;
                       box-shadow:0 2px 8px rgba(230,81,0,.28);">
            <i class="fa fa-plus mr-1"></i> Nueva Oferta sin Pedido
        </button>
    </div>

    {{-- ── Filtros ─────────────────────────────────────────────────────────── --}}
    <div class="d-flex flex-wrap align-items-end gap-2 mb-4">
        <div class="input-group" style="max-width:320px;">
            <div class="input-group-prepend">
                <span class="input-group-text"
                      style="background:#e65100; color:#fff; border-color:#e65100; border-radius:8px 0 0 8px;">
                    <i class="fa fa-search"></i>
                </span>
            </div>
            <input type="text" wire:model.debounce.300ms="busqueda"
                   class="form-control"
                   placeholder="Buscar por cliente, RTN o # pedido…"
                   style="border-radius:0 8px 8px 0;">
        </div>

        <select wire:model="filtroEstado"
                class="form-control shadow-sm"
                style="max-width:180px; border-radius:8px; font-size:13px;">
            <option value="">Todos los estados</option>
            <option value="pedido">Pedido</option>
            <option value="Ofertas">Oferta</option>
            <option value="prefactura">Pre-factura</option>
        </select>

        <div wire:loading style="line-height:34px;">
            <i class="fa fa-spinner fa-spin text-warning ml-1"></i>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- SECCIÓN 1: PEDIDOS CON HISTORICO DE PEDIDO                         --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div class="mb-2 d-flex align-items-center">
        <span style="font-weight:700; color:#e65100; font-size:13px;">
            <i class="fa fa-shopping-cart mr-1"></i> Pedidos listos para ofertar
        </span>
        <span class="ofr-badge ml-2" style="background:#fff3e0; color:#e65100;">
            {{ count($pedidos) }}
        </span>
    </div>

    @if(count($pedidos) === 0)
    <div class="text-center py-4 mb-4"
         style="background:#fff8f0; border-radius:12px; border:1px dashed #ffcc80;">
        <i class="fa fa-inbox fa-2x mb-2 d-block" style="color:#ffb74d;"></i>
        <p style="color:#78909c; font-size:13px; margin:0;">No hay pedidos con los filtros aplicados.</p>
    </div>
    @else
    <div class="table-responsive mb-4">
        <table class="table table-bordered mb-0" style="font-size:13px; border-color:#dee2e6;">
            <thead>
                <tr>
                    <th class="ofr-th" wire:click="sortBy('id')" style="width:65px; text-align:center;">
                        #
                        <i class="fa {{ $sortCol==='id' ? ($sortDir==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                      sort-icon {{ $sortCol==='id' ? 'active' : '' }}"></i>
                    </th>
                    <th class="ofr-th" wire:click="sortBy('cliente')">
                        Cliente
                        <i class="fa {{ $sortCol==='cliente' ? ($sortDir==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                      sort-icon {{ $sortCol==='cliente' ? 'active' : '' }}"></i>
                    </th>
                    <th class="ofr-th sin-sort" style="width:140px; cursor:default;">RTN</th>
                    <th class="ofr-th" wire:click="sortBy('total_productos')"
                        style="width:70px; text-align:center;">
                        Ítems
                        <i class="fa {{ $sortCol==='total_productos' ? ($sortDir==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                      sort-icon {{ $sortCol==='total_productos' ? 'active' : '' }}"></i>
                    </th>
                    <th class="ofr-th" wire:click="sortBy('total_ofertas')"
                        style="width:85px; text-align:center;">
                        Ofertas
                        <i class="fa {{ $sortCol==='total_ofertas' ? ($sortDir==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                      sort-icon {{ $sortCol==='total_ofertas' ? 'active' : '' }}"></i>
                    </th>
                    <th class="ofr-th" wire:click="sortBy('estado_flujo')"
                        style="width:140px; text-align:center;">
                        Estado
                        <i class="fa {{ $sortCol==='estado_flujo' ? ($sortDir==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                      sort-icon {{ $sortCol==='estado_flujo' ? 'active' : '' }}"></i>
                    </th>
                    <th class="ofr-th" wire:click="sortBy('created_at')"
                        style="width:100px; text-align:center;">
                        Fecha
                        <i class="fa {{ $sortCol==='created_at' ? ($sortDir==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                      sort-icon {{ $sortCol==='created_at' ? 'active' : '' }}"></i>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $ped)
                @php
                    $p = (array) $ped;
                    $ef = $p['estado_flujo'] ?? 'pedido';
                    $estadoMap = [
                        'pedido'        => ['#e8f5e9', '#2e7d32', 'Pedido',         'fa-shopping-cart'],
                        'Ofertas'       => ['#fff3e0', '#e65100', 'Oferta',          'fa-tag'],
                        'prefactura'    => ['#e0f7fa', '#006064', 'Pre-factura',     'fa-file-o'],
                        'factura'       => ['#e8f5e9', '#1b5e20', 'Factura',         'fa-file-text'],
                        'Entrega Cobro' => ['#ede7f6', '#4527a0', 'Entrega / Cobro', 'fa-truck'],
                        'cancelado'     => ['#fce4ec', '#b71c1c', 'Cancelado',       'fa-ban'],
                    ];
                    $ec = $estadoMap[$ef] ?? ['#f5f5f5', '#78909c', ucfirst($ef), 'fa-circle'];
                @endphp
                <tr class="ofr-row"
                    wire:click="abrirModalPedido({{ $p['id'] }})"
                    title="Ver flujo del pedido #{{ $p['id'] }}">

                    <td class="text-center align-middle" style="padding:8px 6px;">
                        <span style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff;
                                     border-radius:6px; padding:2px 9px; font-weight:800; font-size:13px;">
                            #{{ $p['id'] }}
                        </span>
                    </td>

                    <td class="align-middle" style="padding:8px 12px;">
                        <div style="font-weight:700; color:#2c3e50; line-height:1.3;">{{ $p['cliente'] }}</div>
                        @if($p['observaciones'])
                        <div style="font-size:11px; color:#90a4ae; line-height:1.2; margin-top:2px;">
                            {{ Str::limit($p['observaciones'], 55) }}
                        </div>
                        @endif
                    </td>

                    <td class="align-middle" style="color:#546e7a; font-size:12px; padding:8px 12px;">
                        {{ $p['rtn'] ?: '—' }}
                    </td>

                    <td class="text-center align-middle" style="padding:8px 6px;">
                        <span class="ofr-badge" style="background:#e8eaf6; color:#3949ab;">
                            {{ $p['total_productos'] }}
                        </span>
                    </td>

                    <td class="text-center align-middle" style="padding:8px 6px;">
                        @if($p['total_ofertas'] > 0)
                            <span class="ofr-badge" style="background:#fff3e0; color:#e65100;">
                                <i class="fa fa-tag mr-1"></i>{{ $p['total_ofertas'] }}
                                @if($p['ofertas_ganadoras'] > 0)
                                    <i class="fa fa-trophy ml-1 text-warning" title="Tiene ganadora"></i>
                                @endif
                            </span>
                        @else
                            <span style="color:#cfd8dc;">—</span>
                        @endif
                    </td>

                    <td class="text-center align-middle" style="padding:8px 6px;">
                        <span class="ofr-badge"
                              style="background:{{ $ec[0] }}; color:{{ $ec[1] }};
                                     border:1px solid {{ $ec[1] }}33;">
                            <i class="fa {{ $ec[3] }} mr-1"></i>{{ $ec[2] }}
                        </span>
                    </td>

                    <td class="text-center align-middle"
                        style="color:#78909c; font-size:12px; white-space:nowrap; padding:8px 6px;">
                        {{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}
                        <div style="font-size:10px; color:#b0bec5;">
                            {{ \Carbon\Carbon::parse($p['created_at'])->format('H:i') }}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- SECCIÓN 2: OFERTAS SIN PEDIDO                                       --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    @if($filtroEstado !== 'pedido')
    <div style="border-top: 2px dashed #f48fb1; padding-top:18px; margin-top:8px;">
        <div class="mb-2 d-flex align-items-center">
            <span style="font-weight:700; color:#c2185b; font-size:13px;">
                <i class="fa fa-file-text-o mr-1"></i> Ofertas sin pedido (iniciaron directamente desde oferta)
            </span>
            <span class="ofr-badge ml-2" style="background:#fce4ec; color:#c2185b;">
                {{ count($cotizacionesSinPedido) }}
            </span>
        </div>

        @if(count($cotizacionesSinPedido) === 0)
        <div class="text-center py-3"
             style="background:#fdf2f7; border-radius:12px; border:1px dashed #f48fb1;">
            <i class="fa fa-inbox fa-2x mb-2 d-block" style="color:#f48fb1;"></i>
            <p style="color:#78909c; font-size:13px; margin:0;">No hay ofertas sin pedido.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-bordered mb-0" style="font-size:13px; border-color:#dee2e6;">
                <thead>
                    <tr>
                        <th class="sin-th" wire:click="sortBySin('id')" style="width:65px; text-align:center;">
                            #
                            <i class="fa {{ $sortColSin==='id' ? ($sortDirSin==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                          sort-icon {{ $sortColSin==='id' ? 'active' : '' }}"></i>
                        </th>
                        <th class="sin-th" wire:click="sortBySin('nombre_cliente')">
                            Cliente
                            <i class="fa {{ $sortColSin==='nombre_cliente' ? ($sortDirSin==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                          sort-icon {{ $sortColSin==='nombre_cliente' ? 'active' : '' }}"></i>
                        </th>
                        <th class="sin-th sin-sort" style="width:140px; cursor:default;">RTN</th>
                        <th class="sin-th" wire:click="sortBySin('total_productos')"
                            style="width:70px; text-align:center;">
                            Ítems
                            <i class="fa {{ $sortColSin==='total_productos' ? ($sortDirSin==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                          sort-icon {{ $sortColSin==='total_productos' ? 'active' : '' }}"></i>
                        </th>
                        <th class="sin-th sin-sort" style="width:100px; text-align:right; cursor:default;">Total</th>
                        <th class="sin-th" wire:click="sortBySin('estado_flujo')"
                            style="width:150px; text-align:center;">
                            Estado
                            <i class="fa {{ $sortColSin==='estado_flujo' ? ($sortDirSin==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                          sort-icon {{ $sortColSin==='estado_flujo' ? 'active' : '' }}"></i>
                        </th>
                        <th class="sin-th" wire:click="sortBySin('created_at')"
                            style="width:100px; text-align:center;">
                            Fecha
                            <i class="fa {{ $sortColSin==='created_at' ? ($sortDirSin==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                          sort-icon {{ $sortColSin==='created_at' ? 'active' : '' }}"></i>
                        </th>
                        <th class="sin-th sin-sort" style="width:130px; cursor:default; text-align:center;">
                            Acción
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cotizacionesSinPedido as $cot)
                    @php
                        $c = (array) $cot;
                        $sinEf = $c['estado_flujo'] ?? 'Ofertas';
                        $sinMap = [
                            'pedido'        => ['#e8f5e9', '#2e7d32', 'Pedido',         'fa-shopping-cart'],
                            'Ofertas'       => ['#fff3e0', '#e65100', 'Oferta',          'fa-tag'],
                            'prefactura'    => ['#e0f7fa', '#006064', 'Pre-factura',     'fa-file-o'],
                            'factura'       => ['#e8f5e9', '#1b5e20', 'Factura',         'fa-file-text'],
                            'Entrega Cobro' => ['#ede7f6', '#4527a0', 'Entrega / Cobro', 'fa-truck'],
                        ];
                        $sc = $sinMap[$sinEf] ?? ['#f5f5f5', '#78909c', ucfirst($sinEf), 'fa-circle'];
                        $esGanadora = (bool) ($c['es_ganadora'] ?? false);
                    @endphp
                    <tr class="sin-row"
                        wire:click="abrirModalOferta({{ $c['id'] }})"
                        title="Ver flujo de la oferta #{{ $c['id'] }}">

                        <td class="text-center align-middle" style="padding:8px 6px;">
                            <span style="background:linear-gradient(135deg,#c2185b,#e91e63); color:#fff;
                                         border-radius:6px; padding:2px 9px; font-weight:800; font-size:13px;">
                                #{{ $c['id'] }}
                            </span>
                        </td>

                        <td class="align-middle" style="padding:8px 12px;">
                            <div style="font-weight:700; color:#2c3e50;">{{ $c['nombre_cliente'] ?: '—' }}</div>
                        </td>

                        <td class="align-middle" style="color:#546e7a; font-size:12px; padding:8px 12px;">
                            {{ $c['RTN'] ?: '—' }}
                        </td>

                        <td class="text-center align-middle" style="padding:8px 6px;">
                            <span class="ofr-badge" style="background:#e8eaf6; color:#3949ab;">
                                {{ $c['total_productos'] }}
                            </span>
                        </td>

                        <td class="text-right align-middle" style="padding:8px 10px;">
                            <strong style="color:#2e7d32; font-size:13px;">L {{ $c['total'] }}</strong>
                        </td>

                        <td class="text-center align-middle" style="padding:8px 6px;">
                            <span class="ofr-badge"
                                  style="background:{{ $sc[0] }}; color:{{ $sc[1] }};
                                         border:1px solid {{ $sc[1] }}33;">
                                <i class="fa {{ $sc[3] }} mr-1"></i>{{ $sc[2] }}
                            </span>
                        </td>

                        <td class="text-center align-middle"
                            style="color:#78909c; font-size:12px; white-space:nowrap; padding:8px 6px;">
                            {{ \Carbon\Carbon::parse($c['created_at'])->format('d/m/Y') }}
                            <div style="font-size:10px; color:#b0bec5;">
                                {{ \Carbon\Carbon::parse($c['created_at'])->format('H:i') }}
                            </div>
                        </td>

                        <td class="text-center align-middle" style="padding:8px 6px;"
                            wire:click.stop>
                            @if($esGanadora)
                                <button type="button"
                                        wire:click="anularGanadora({{ $c['flujo_id'] }}, {{ $c['id'] }})"
                                        class="btn btn-sm"
                                        style="background:#fce4ec; color:#c2185b; border:1.5px solid #f48fb1;
                                               border-radius:7px; font-size:11px; font-weight:700; padding:4px 10px;"
                                        title="Anular como ganadora">
                                    <i class="fa fa-trophy mr-1 text-warning"></i> Anular
                                </button>
                            @else
                                <button type="button"
                                        wire:click="marcarGanadora({{ $c['flujo_id'] }}, {{ $c['id'] }})"
                                        class="btn btn-sm"
                                        style="background:#e8f5e9; color:#2e7d32; border:1.5px solid #a5d6a7;
                                               border-radius:7px; font-size:11px; font-weight:700; padding:4px 10px;"
                                        title="Marcar como ganadora → prefactura">
                                    <i class="fa fa-trophy mr-1"></i> Ganadora
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

</div>
