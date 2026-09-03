<div>
    <style>
        .ofp-th {
            cursor: pointer;
            user-select: none;
            white-space: nowrap;
            font-size: 12px;
            font-weight: 700;
            padding: 10px 12px;
            border-bottom: 2px solid !important;
            transition: background .15s;
            background: #fff3e0;
            color: #e65100;
            border-color: #ffcc80 !important;
        }
        .ofp-th:hover { background: #ffe0b2; }
        .ofp-th.static { cursor: default; }
        .ofp-th.static:hover { background: #fff3e0; }
        .ofp-th .sort-icon { margin-left: 4px; opacity: .4; font-size: 11px; }
        .ofp-th .sort-icon.active { opacity: 1; color: #e65100; }
        .hist-badge {
            display: inline-block;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .estado-pedido { background:#e8f5e9; color:#2e7d32; }
        .estado-ofertas { background:#fff3e0; color:#e65100; }
        .estado-prefactura { background:#e0f7fa; color:#006064; }
        .estado-factura { background:#e8f5e9; color:#1b5e20; }
        .estado-entrega-cobro { background:#ede7f6; color:#4527a0; }
        .estado-sin-flujo { background:#f5f5f5; color:#78909c; }
        .estado-cancelado { background:#fce4ec; color:#b71c1c; }
        .estado-rechazado-creditos { background:#c62828; color:#fff; }
        .ofp-row { transition: background .1s; }
        .ofp-row:hover > td { background: #fff8ee !important; cursor: pointer; }
        .ofp-row-rechazado-creditos > td { background:#ffebee !important; border-color:#ef9a9a !important; }
        .ofp-row-rechazado-creditos:hover > td { background:#ffcdd2 !important; }
    </style>

    <div class="ibox" style="background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 8px 26px rgba(0,0,0,.08);">
    <div class="ibox-title d-flex align-items-center justify-content-between"
         style="background:linear-gradient(135deg,#ef6c00 0%,#f9a825 100%); color:#fff; padding:12px 20px; border:none;">
        <h5 class="m-0" style="color:#fff; font-weight:800;">
            <i class="fa fa-history mr-2"></i> Historial
        </h5>
        <a href="{{ route('flujo.ventas') }}"
           class="btn btn-sm"
           style="border:1px solid rgba(255,255,255,.8); color:#fff; border-radius:8px; font-weight:700; padding:6px 14px;">
            <i class="fa fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <div class="ibox-content" style="padding:22px; background:#fff;">

    @if($mensajeExito)
    <div class="alert alert-success alert-dismissible d-flex align-items-center" role="alert" style="font-size:13px; border-radius:10px;">
        <i class="fa fa-check-circle mr-2"></i> {{ $mensajeExito }}
        <button type="button" class="close ml-auto" wire:click="$set('mensajeExito','')"><span>&times;</span></button>
    </div>
    @endif
    @if($mensajeError)
    <div class="alert alert-danger alert-dismissible d-flex align-items-center" role="alert" style="font-size:13px; border-radius:10px;">
        <i class="fa fa-exclamation-circle mr-2"></i> {{ $mensajeError }}
        <button type="button" class="close ml-auto" wire:click="$set('mensajeError','')"><span>&times;</span></button>
    </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="m-0" style="font-weight:800; color:#37474f;">
            <i class="fa fa-list-alt mr-2" style="color:#e65100;"></i> Historico de Ventas
        </h5>
        <button wire:click="limpiarFiltros"
                class="btn btn-sm btn-warning font-weight-bold"
                style="border-radius:8px; font-size:12px;">
            <i class="fa fa-eraser mr-1"></i> Limpiar Filtros
        </button>
    </div>

    <div class="row mb-3">
        <div class="col-md-3 mb-2">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"
                          style="background:#e65100; color:#fff; border-color:#e65100; border-radius:8px 0 0 8px;">
                        <i class="fa fa-search"></i>
                    </span>
                </div>
                <input type="text"
                       wire:model.debounce.300ms="busquedaOfr"
                       class="form-control"
                       placeholder="Buscar cliente, RTN, # flujo o # documento..."
                       style="border-radius:0 8px 8px 0;">
            </div>
        </div>
        <div class="col-md-2 mb-2">
            <input type="number"
                   wire:model.debounce.300ms="filtroNumero"
                   class="form-control"
                   min="1"
                   placeholder="# Documento"
                   style="border-radius:8px;">
        </div>
        <div class="col-md-2 mb-2">
            <select wire:model="filtroEstado" class="form-control" style="border-radius:8px;">
                <option value="">Todos los estados</option>
                <option value="pedido">Pedido</option>
                <option value="Ofertas">Ofertas</option>
                <option value="prefactura">Pre Factura</option>
                <option value="factura">Factura</option>
                <option value="Entrega Cobro">Entrega / Cobro</option>
                <option value="rechazado_creditos">Rechazado por créditos</option>
                <option value="sin_flujo">Sin flujo</option>
            </select>
        </div>
        <div class="col-md-3 mb-2">
            <select wire:model="filtroTipoVenta" class="form-control" style="border-radius:8px;">
                <option value="">Todos los tipos</option>
                <option value="expo">Expo</option>
                @foreach($tiposVenta as $tipoVenta)
                    <option value="{{ data_get($tipoVenta, 'id') }}">{{ ucfirst(data_get($tipoVenta, 'descripcion')) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 mb-2">
            <input type="date"
                   wire:model="filtroFecha"
                   class="form-control"
                   style="border-radius:8px;">
        </div>
    </div>

    @php
        $ofrTotal    = $totalRegistros;
        $ofrLastPage = max(1, (int) ceil($ofrTotal / $perPage));
        $ofrStart    = ($paginaOfr - 1) * $perPage;
        $ofrSlice    = $registros;

        $estadoMap = [
            'pedido'        => ['#e8f5e9', '#2e7d32', 'Pedido',          'fa-shopping-cart'],
            'Ofertas'       => ['#fff3e0', '#e65100', 'Ofertas',         'fa-tag'],
            'prefactura'    => ['#e0f7fa', '#006064', 'Pre-factura',     'fa-file-o'],
            'factura'       => ['#e8f5e9', '#1b5e20', 'Factura',         'fa-file-text'],
            'Entrega Cobro' => ['#ede7f6', '#4527a0', 'Entrega / Cobro', 'fa-truck'],
            'sin_flujo'     => ['#f5f5f5', '#78909c', 'Sin flujo',       'fa-question-circle'],
            'cancelado'     => ['#fce4ec', '#b71c1c', 'Cancelado',       'fa-ban'],
            'rechazado_creditos' => ['#c62828', '#fff', 'Rechazado por créditos', 'fa-times-circle'],
        ];
    @endphp

    <div class="d-flex flex-wrap align-items-end mb-2" style="gap:10px;">
        <small style="color:#78909c; line-height:28px;">
            <i class="fa fa-list mr-1"></i> {{ $ofrTotal }} registro(s)
        </small>
        @if(!$puedeVerTodoHistorial)
            <small style="color:#78909c; line-height:28px;">
                <i class="fa fa-user mr-1"></i> Solo tus registros
            </small>
        @endif
    </div>

    @if($ofrTotal === 0)
    <div class="text-center py-5">
        <i class="fa fa-inbox fa-3x mb-3 d-block" style="color:#ffe0b2;"></i>
        <p style="color:#78909c; font-size:14px;">No hay registros para mostrar.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-bordered mb-0" style="font-size:13px; border-color:#dee2e6;">
            <thead>
                <tr>
                    <th class="ofp-th" wire:click="sortByOfr('flujo_id')" style="width:80px; text-align:center;">
                        # Flujo
                        <i class="fa {{ $sortColOfr==='flujo_id' ? ($sortDirOfr==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColOfr==='flujo_id' ? 'active' : '' }}"></i>
                    </th>
                    <th class="ofp-th" wire:click="sortByOfr('documento_id')" style="width:95px; text-align:center;">
                        # Documento
                        <i class="fa {{ $sortColOfr==='documento_id' ? ($sortDirOfr==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColOfr==='documento_id' ? 'active' : '' }}"></i>
                    </th>
                    <th class="ofp-th" wire:click="sortByOfr('cliente')">
                        Cliente
                        <i class="fa {{ $sortColOfr==='cliente' ? ($sortDirOfr==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColOfr==='cliente' ? 'active' : '' }}"></i>
                    </th>
                    <th class="ofp-th static" style="width:140px;">RTN</th>
                    <th class="ofp-th static" style="width:150px; text-align:center;">Tipo de venta</th>
                    <th class="ofp-th" wire:click="sortByOfr('estado_flujo')" style="width:145px; text-align:center;">
                        Estado
                        <i class="fa {{ $sortColOfr==='estado_flujo' ? ($sortDirOfr==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColOfr==='estado_flujo' ? 'active' : '' }}"></i>
                    </th>
                    <th class="ofp-th" wire:click="sortByOfr('total_ofertas')" style="width:85px; text-align:center;">
                        Ofertas
                        <i class="fa {{ $sortColOfr==='total_ofertas' ? ($sortDirOfr==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColOfr==='total_ofertas' ? 'active' : '' }}"></i>
                    </th>
                    <th class="ofp-th" wire:click="sortByOfr('created_at')" style="width:100px; text-align:center;">
                        Fecha
                        <i class="fa {{ $sortColOfr==='created_at' ? ($sortDirOfr==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColOfr==='created_at' ? 'active' : '' }}"></i>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($ofrSlice as $ofr)
                @php
                    $o  = (array) $ofr;
                    $ef = $o['estado_flujo'] ?? 'sin_flujo';
                    $ec = $estadoMap[$ef] ?? ['#f5f5f5', '#78909c', ucfirst($ef), 'fa-circle'];
                    $estadoClass = match($ef) {
                        'pedido' => 'estado-pedido',
                        'Ofertas' => 'estado-ofertas',
                        'prefactura' => 'estado-prefactura',
                        'factura' => 'estado-factura',
                        'Entrega Cobro' => 'estado-entrega-cobro',
                        'cancelado' => 'estado-cancelado',
                        'rechazado_creditos' => 'estado-rechazado-creditos',
                        default => 'estado-sin-flujo',
                    };
                    $filaRechazadaCreditos = $ef === 'rechazado_creditos';
                @endphp
                <tr class="ofp-row {{ $filaRechazadaCreditos ? 'ofp-row-rechazado-creditos' : '' }}" wire:click="abrirFlujoDesdeRegistro({{ (int) ($o['flujo_id'] ?? 0) }}, {{ (int) ($o['pedido_id'] ?? 0) }})" title="Ver flujo #{{ $o['flujo_id'] }}">
                    <td class="text-center align-middle" style="padding:8px;">
                        <span style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border-radius:6px; padding:2px 9px; font-weight:800; font-size:13px;">
                            #{{ $o['flujo_id'] }}
                        </span>
                    </td>
                    <td class="text-center align-middle" style="padding:8px; color:#5d4037; font-weight:700;">
                        {{ $o['documento_display'] ?? '—' }}
                    </td>
                    <td class="align-middle" style="padding:8px 12px;">
                        <div style="font-weight:700; color:#2c3e50; line-height:1.3;">{{ $o['cliente'] }}</div>
                    </td>
                    <td class="align-middle" style="color:#546e7a; padding:8px 12px; font-size:12px;">
                        {{ $o['rtn'] ?: '—' }}
                    </td>
                    <td class="text-center align-middle" style="padding:8px 6px;">
                        @forelse(array_filter(array_map('trim', explode(',', $o['tipos_venta'] ?? ''))) as $tipoVenta)
                            <span class="hist-badge" style="margin:1px; background:{{ $tipoVenta === 'Expo' ? '#e8f5e9' : '#e3f2fd' }}; color:{{ $tipoVenta === 'Expo' ? '#2e7d32' : '#1565c0' }};">
                                <i class="fa {{ $tipoVenta === 'Expo' ? 'fa-star' : 'fa-file-text-o' }} mr-1"></i>{{ ucfirst($tipoVenta) }}
                            </span>
                        @empty
                            <span style="color:#b0bec5;">Sin oferta</span>
                        @endforelse
                    </td>
                    <td class="text-center align-middle" style="padding:8px 6px;">
                        <span class="hist-badge {{ $estadoClass }}">
                            <i class="fa {{ $ec[3] }} mr-1"></i>{{ $ec[2] }}
                        </span>
                    </td>
                    <td class="text-center align-middle" style="padding:8px 6px;">
                        <span class="hist-badge" style="background:#fff3e0; color:#e65100;">
                            <i class="fa fa-tag mr-1"></i>{{ $o['total_ofertas'] ?? 0 }}
                        </span>
                    </td>
                    <td class="text-center align-middle" style="color:#78909c; font-size:12px; padding:8px 6px; white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($o['created_at'])->format('d/m/Y') }}
                        <div style="font-size:10px; color:#b0bec5;">{{ \Carbon\Carbon::parse($o['created_at'])->format('H:i') }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($ofrTotal > $perPage)
    <div class="d-flex align-items-center justify-content-between mt-2" style="font-size:12px; color:#78909c;">
        <span>Mostrando {{ min($ofrStart + 1, $ofrTotal) }}-{{ min($ofrStart + $perPage, $ofrTotal) }} de {{ $ofrTotal }}</span>
        <div class="d-flex align-items-center" style="gap:8px;">
            <button wire:click="ofrPrev" @if($paginaOfr <= 1) disabled @endif
                    class="btn btn-sm btn-outline-warning" style="border-radius:8px; font-size:11px;"
                    @if($paginaOfr <= 1) style="opacity:.5; cursor:not-allowed;" @endif>
                <i class="fa fa-chevron-left mr-1"></i> Anterior
            </button>
            <span style="font-weight:700; color:#546e7a;">Pag. {{ $paginaOfr }} / {{ $ofrLastPage }}</span>
            <button wire:click="ofrNext" @if($paginaOfr >= $ofrLastPage) disabled @endif
                    class="btn btn-sm btn-outline-warning" style="border-radius:8px; font-size:11px;"
                    @if($paginaOfr >= $ofrLastPage) style="opacity:.5; cursor:not-allowed;" @endif>
                Siguiente <i class="fa fa-chevron-right ml-1"></i>
            </button>
        </div>
    </div>
    @endif
    @endif

    </div>
    </div>

    {{-- Modal global de flujo (igual que listar-pedidos-para-ofertar) --}}
    <livewire:flujo.modal-flujo-pedido />

    {{-- Deep-link desde notificación: abre el modal una vez que Alpine+Livewire están listos --}}
    @if($autoOpenPedidoId > 0)
    <div
        wire:ignore
        x-data="{ pedidoId: {{ $autoOpenPedidoId }} }"
        x-init="$nextTick(() => Livewire.emit('abrirFlujoPedido', pedidoId))"
    ></div>
    @elseif($autoOpenCotizacionId > 0)
    <div
        wire:ignore
        x-data="{ flujoId: {{ $autoOpenCotizacionId }} }"
        x-init="$nextTick(() => Livewire.emit('abrirFlujoCotizacion', flujoId))"
    ></div>
    @endif
</div>
