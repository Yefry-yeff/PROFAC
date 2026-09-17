<div>
    <style>
        /* ── Estilos heredados de listar-historial-pedidos ─────────── */
        .ofp-th {
            cursor: pointer;
            user-select: none;
            white-space: nowrap;
            font-size: 12px;
            font-weight: 700;
            padding: 10px 12px;
            border-bottom: 2px solid !important;
            transition: background .15s;
        }
        /* Pestana pedidos: azul; Pestana ofertas: naranja */
        .ofp-th.ped { background: #e3f2fd; color: #1565c0; border-color: #90caf9 !important; }
        .ofp-th.ped:hover { background: #bbdefb; }
        .ofp-th.ofr { background: #fff3e0; color: #e65100; border-color: #ffcc80 !important; }
        .ofp-th.ofr:hover { background: #ffe0b2; }
        .ofp-th .sort-icon { margin-left: 4px; opacity: .4; font-size: 11px; }
        .ofp-th .sort-icon.active { opacity: 1; }
        .ofp-th.ped .sort-icon.active { color: #1a73e8; }
        .ofp-th.ofr .sort-icon.active { color: #e65100; }
        .hist-badge {
            display: inline-block;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .ofp-row { transition: background .1s; }
        .ofp-row:hover > td { background: #f0f7ff !important; cursor: pointer; }

        /* Pestanas Bootstrap */
        .ofp-tab-nav { border-bottom: 2px solid #dee2e6; margin-bottom: 16px; }
        .ofp-tab-btn {
            background: none; border: none; padding: 10px 22px;
            font-size: 13px; font-weight: 700; color: #78909c;
            border-bottom: 3px solid transparent; margin-bottom: -2px;
            transition: color .15s, border-color .15s;
            cursor: pointer;
        }
        .ofp-tab-btn:hover { color: #1565c0; }
        .ofp-tab-btn.active-ped { color: #1565c0; border-bottom-color: #1565c0; }
        .ofp-tab-btn.active-ofr { color: #e65100; border-bottom-color: #e65100; }
        .ofp-tab-btn.active-temp { color: #00897b; border-bottom-color: #00897b; }
        .ofp-tab-btn .ofp-count {
            display: inline-block; border-radius: 20px;
            padding: 1px 8px; font-size: 10px; margin-left: 5px; font-weight: 700;
        }
    </style>

    {{-- ── Mensajes flash ─────────────────────────────────────────── --}}
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

    {{-- ── Cabecera ────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="m-0" style="font-weight:800; color:#37474f;">
            <i class="fa fa-file-text-o mr-2" style="color:#e65100;"></i> Pedidos y Ofertas
        </h5>
        <div class="d-flex" style="gap:8px;">
            @if($expoActiva)
                <button wire:click="nuevaOfertaExpo"
                        class="btn btn-sm btn-success font-weight-bold"
                        style="border-radius:8px; font-size:12px;">
                    <i class="fa fa-plus mr-1"></i> Oferta de Expo
                </button>
            @endif
            <button wire:click="nuevaOfertaSinPedido"
                    class="btn btn-sm btn-warning font-weight-bold"
                    style="border-radius:8px; font-size:12px;">
                <i class="fa fa-plus mr-1"></i> Nueva Oferta
            </button>
        </div>
    </div>

    {{-- ── Pestanas ─────────────────────────────────────────────────── --}}
    <div class="ofp-tab-nav">
        <button wire:click="$set('tab','pedidos')"
                class="ofp-tab-btn {{ $tab === 'pedidos' ? 'active-ped' : '' }}">
            <i class="fa fa-shopping-cart mr-1"></i>
            Pedidos sin ofertas
            <span class="ofp-count" style="{{ $tab === 'pedidos' ? 'background:#1565c0; color:#fff;' : 'background:#e3f2fd; color:#1565c0;' }}">
                {{ count($pedidos) }}
            </span>
        </button>
        <button wire:click="$set('tab','ofertas')"
                class="ofp-tab-btn {{ $tab === 'ofertas' ? 'active-ofr' : '' }}">
            <i class="fa fa-tag mr-1"></i>
            Ofertas
            <span class="ofp-count" style="{{ $tab === 'ofertas' ? 'background:#e65100; color:#fff;' : 'background:#fff3e0; color:#e65100;' }}">
                {{ count($ofertas) }}
            </span>
        </button>
        <button wire:click="$set('tab','temporales')"
                class="ofp-tab-btn {{ $tab === 'temporales' ? 'active-temp' : '' }}">
            <i class="fa fa-clock-o mr-1"></i>Temporales
        </button>

    </div>

    {{-- ════════════════════════════════════════════════════════════════
         PESTANA 1: PEDIDOS LISTOS PARA OFERTAR
    ════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'pedidos')
    @php
        $pedTotal    = count($pedidos);
        $pedLastPage = max(1, (int) ceil($pedTotal / $perPage));
        $pedStart    = ($paginaPed - 1) * $perPage;
        $pedSlice    = array_slice($pedidos, $pedStart, $perPage);
    @endphp

        {{-- Buscador --}}
        <div class="d-flex flex-wrap align-items-end mb-3" style="gap:10px;">
            <div class="input-group" style="max-width:320px;">
                <div class="input-group-prepend">
                    <span class="input-group-text"
                          style="background:#1565c0; color:#fff; border-color:#1565c0; border-radius:8px 0 0 8px;">
                        <i class="fa fa-search"></i>
                    </span>
                </div>
                <input type="text"
                       wire:model.debounce.300ms="busquedaPed"
                       class="form-control"
                       placeholder="Buscar por cliente, RTN o # pedido…"
                       style="border-radius:0 8px 8px 0;">
            </div>
            <small style="color:#78909c; line-height:36px;">
                <i class="fa fa-list mr-1"></i> {{ $pedTotal }} registro(s)
            </small>
        </div>

        {{-- Tabla --}}
        @if($pedTotal === 0)
        <div class="text-center py-5">
            <i class="fa fa-inbox fa-3x mb-3 d-block" style="color:#b2dfdb;"></i>
            <p style="color:#78909c; font-size:14px;">No hay pedidos pendientes de oferta.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-bordered mb-0" style="font-size:13px; border-color:#dee2e6;">
                <thead>
                    <tr>
                        <th class="ofp-th ped" wire:click="sortByPed('id')" style="width:80px; text-align:center;">
                            # Flujo
                            <i class="fa {{ $sortColPed==='id' ? ($sortDirPed==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColPed==='id' ? 'active' : '' }}"></i>
                        </th>
                        <th class="ofp-th ped" wire:click="sortByPed('cliente')">
                            Cliente
                            <i class="fa {{ $sortColPed==='cliente' ? ($sortDirPed==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColPed==='cliente' ? 'active' : '' }}"></i>
                        </th>
                        <th class="ofp-th ped" style="width:140px; cursor:default;">RTN</th>
                        <th class="ofp-th ped" wire:click="sortByPed('total_productos')" style="width:70px; text-align:center;">
                            Ítems
                            <i class="fa {{ $sortColPed==='total_productos' ? ($sortDirPed==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColPed==='total_productos' ? 'active' : '' }}"></i>
                        </th>
                        <th class="ofp-th ped" wire:click="sortByPed('total_ofertas')" style="width:85px; text-align:center;">
                            Ofertas
                            <i class="fa {{ $sortColPed==='total_ofertas' ? ($sortDirPed==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColPed==='total_ofertas' ? 'active' : '' }}"></i>
                        </th>
                        <th class="ofp-th ped" wire:click="sortByPed('created_at')" style="width:100px; text-align:center;">
                            Fecha
                            <i class="fa {{ $sortColPed==='created_at' ? ($sortDirPed==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColPed==='created_at' ? 'active' : '' }}"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedSlice as $ped)
                    @php $p = (array) $ped; @endphp
                    <tr class="ofp-row" wire:click="abrirModalPedido({{ $p['id'] }})"
                        title="Ver flujo #{{ $p['flujo_id'] }} — Pedido #{{ $p['id'] }}">
                        <td class="text-center align-middle" style="padding:8px;">
                            <span style="background:linear-gradient(135deg,#1565c0,#1a73e8); color:#fff; border-radius:6px; padding:2px 9px; font-weight:800; font-size:13px;">
                                #{{ $p['flujo_id'] }}
                            </span>
                            <div style="font-size:10px; color:#b0bec5; margin-top:2px;">Ped. #{{ $p['id'] }}</div>
                        </td>
                        <td class="align-middle" style="padding:8px 12px;">
                            <div style="font-weight:700; color:#2c3e50; line-height:1.3;">{{ $p['cliente'] }}</div>
                            @if(!empty($p['observaciones']))
                            <div style="font-size:11px; color:#90a4ae; line-height:1.2; margin-top:2px;">
                                {{ \Illuminate\Support\Str::limit($p['observaciones'], 55) }}
                            </div>
                            @endif
                        </td>
                        <td class="align-middle" style="color:#546e7a; padding:8px 12px; font-size:12px;">
                            {{ $p['rtn'] ?: '—' }}
                        </td>
                        <td class="text-center align-middle" style="padding:8px 6px;">
                            <span class="hist-badge" style="background:#e8eaf6; color:#3949ab;">
                                {{ $p['total_productos'] }}
                            </span>
                        </td>
                        <td class="text-center align-middle" style="padding:8px 6px;">
                            @if($p['total_ofertas'] > 0)
                                <span class="hist-badge" style="background:#fff3e0; color:#e65100;">
                                    <i class="fa fa-tag mr-1"></i>{{ $p['total_ofertas'] }}
                                </span>
                            @else
                                <span style="color:#cfd8dc;">—</span>
                            @endif
                        </td>
                        <td class="text-center align-middle" style="color:#78909c; font-size:12px; padding:8px 6px; white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}
                            <div style="font-size:10px; color:#b0bec5;">{{ \Carbon\Carbon::parse($p['created_at'])->format('H:i') }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginacion pedidos --}}
        @if($pedTotal > $perPage)
        <div class="d-flex align-items-center justify-content-between mt-2" style="font-size:12px; color:#78909c;">
            <span>Mostrando {{ min($pedStart + 1, $pedTotal) }}–{{ min($pedStart + $perPage, $pedTotal) }} de {{ $pedTotal }}</span>
            <div class="d-flex align-items-center" style="gap:8px;">
                <button wire:click="pedPrev" @if($paginaPed <= 1) disabled @endif
                        class="btn btn-sm btn-outline-primary" style="border-radius:8px; font-size:11px;"
                        @if($paginaPed <= 1) style="opacity:.5; cursor:not-allowed;" @endif>
                    <i class="fa fa-chevron-left mr-1"></i> Anterior
                </button>
                <span style="font-weight:700; color:#546e7a;">Pág. {{ $paginaPed }} / {{ $pedLastPage }}</span>
                <button wire:click="pedNext" @if($paginaPed >= $pedLastPage) disabled @endif
                        class="btn btn-sm btn-outline-primary" style="border-radius:8px; font-size:11px;"
                        @if($paginaPed >= $pedLastPage) style="opacity:.5; cursor:not-allowed;" @endif>
                    Siguiente <i class="fa fa-chevron-right ml-1"></i>
                </button>
            </div>
        </div>
        @endif
        @endif
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         PESTANA 2: OFERTAS
    ════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'ofertas')
    @php
        $ofrTotal    = count($ofertas);
        $ofrLastPage = max(1, (int) ceil($ofrTotal / $perPage));
        $ofrStart    = ($paginaOfr - 1) * $perPage;
        $ofrSlice    = array_slice($ofertas, $ofrStart, $perPage);

        $estadoMap = [
            'pedido'        => ['#e8f5e9', '#2e7d32', 'Pedido',          'fa-shopping-cart'],
            'Ofertas'       => ['#fff3e0', '#e65100', 'Ofertas',          'fa-tag'],
            'prefactura'    => ['#e0f7fa', '#006064', 'Pre-factura',      'fa-file-o'],
            'factura'       => ['#e8f5e9', '#1b5e20', 'Factura',          'fa-file-text'],
            'Entrega Cobro' => ['#ede7f6', '#4527a0', 'Entrega / Cobro',  'fa-truck'],
            'cancelado'     => ['#fce4ec', '#b71c1c', 'Cancelado',        'fa-ban'],
        ];
    @endphp

        {{-- Buscador --}}
        <div class="d-flex flex-wrap align-items-end mb-3" style="gap:10px;">
            <div class="input-group" style="max-width:320px;">
                <div class="input-group-prepend">
                    <span class="input-group-text"
                          style="background:#e65100; color:#fff; border-color:#e65100; border-radius:8px 0 0 8px;">
                        <i class="fa fa-search"></i>
                    </span>
                </div>
                <input type="text"
                       wire:model.debounce.300ms="busquedaOfr"
                       class="form-control"
                       placeholder="Buscar por cliente, RTN o # flujo…"
                       style="border-radius:0 8px 8px 0;">
            </div>
            <select wire:model="filtroTipoVenta" class="form-control" style="max-width:220px; border-radius:8px;">
                <option value="">Todos los tipos</option>
                <option value="expo">Expo</option>
                @foreach($tiposVenta as $tipoVenta)
                    <option value="{{ data_get($tipoVenta, 'id') }}">{{ ucfirst(data_get($tipoVenta, 'descripcion')) }}</option>
                @endforeach
            </select>
            <small style="color:#78909c; line-height:36px;">
                <i class="fa fa-list mr-1"></i> {{ $ofrTotal }} registro(s)
            </small>
        </div>

        {{-- Tabla --}}
        @if($ofrTotal === 0)
        <div class="text-center py-5">
            <i class="fa fa-inbox fa-3x mb-3 d-block" style="color:#ffe0b2;"></i>
            <p style="color:#78909c; font-size:14px;">No hay ofertas registradas.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-bordered mb-0" style="font-size:13px; border-color:#dee2e6;">
                <thead>
                    <tr>
                        <th class="ofp-th ofr" wire:click="sortByOfr('flujo_id')" style="width:80px; text-align:center;">
                            # Flujo
                            <i class="fa {{ $sortColOfr==='flujo_id' ? ($sortDirOfr==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColOfr==='flujo_id' ? 'active' : '' }}"></i>
                        </th>
                        <th class="ofp-th ofr" wire:click="sortByOfr('cliente')">
                            Cliente
                            <i class="fa {{ $sortColOfr==='cliente' ? ($sortDirOfr==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColOfr==='cliente' ? 'active' : '' }}"></i>
                        </th>
                        <th class="ofp-th ofr" style="width:140px; cursor:default;">RTN</th>
                        <th class="ofp-th ofr" style="width:150px; cursor:default; text-align:center;">Tipo de venta</th>
                        <th class="ofp-th ofr" wire:click="sortByOfr('estado_flujo')" style="width:145px; text-align:center;">
                            Estado
                            <i class="fa {{ $sortColOfr==='estado_flujo' ? ($sortDirOfr==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColOfr==='estado_flujo' ? 'active' : '' }}"></i>
                        </th>
                        <th class="ofp-th ofr" wire:click="sortByOfr('total_ofertas')" style="width:85px; text-align:center;">
                            Ofertas
                            <i class="fa {{ $sortColOfr==='total_ofertas' ? ($sortDirOfr==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColOfr==='total_ofertas' ? 'active' : '' }}"></i>
                        </th>
                        <th class="ofp-th ofr" wire:click="sortByOfr('created_at')" style="width:100px; text-align:center;">
                            Fecha
                            <i class="fa {{ $sortColOfr==='created_at' ? ($sortDirOfr==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }} sort-icon {{ $sortColOfr==='created_at' ? 'active' : '' }}"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ofrSlice as $ofr)
                    @php
                        $o  = (array) $ofr;
                        $ef = $o['estado_flujo'] ?? '';
                        $ec = $estadoMap[$ef] ?? ['#f5f5f5', '#78909c', ucfirst($ef), 'fa-circle'];
                    @endphp
                    @php
                        $rowClick = $o['origen'] === 'pedido'
                            ? 'abrirModalPedido('.$o['tramite_id'].')'
                            : 'abrirModalCotizacion('.$o['flujo_id'].')';
                    @endphp
                    <tr class="ofp-row" wire:click="{{ $rowClick }}"
                        title="Ver flujo #{{ $o['flujo_id'] }}">
                        <td class="text-center align-middle" style="padding:8px;">
                            <span style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border-radius:6px; padding:2px 9px; font-weight:800; font-size:13px;">
                                #{{ $o['flujo_id'] }}
                            </span>
                            <div style="font-size:10px; color:#b0bec5; margin-top:2px;">{{ ucfirst($o['origen']) }} #{{ $o['tramite_id'] }}</div>
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
                            <span class="hist-badge"
                                  style="background:{{ $ec[0] }}; color:{{ $ec[1] }}; border:1px solid {{ $ec[1] }}33;">
                                <i class="fa {{ $ec[3] }} mr-1"></i>{{ $ec[2] }}
                            </span>
                            @if($o['tiene_ganadora'] > 0)
                            <span class="hist-badge ml-1" style="background:#fff8e1; color:#f57f17; border:1px solid #ffe08233;">
                                <i class="fa fa-trophy mr-1"></i>Ganadora
                            </span>
                            @endif
                        </td>
                        <td class="text-center align-middle" style="padding:8px 6px;">
                            <span class="hist-badge" style="background:#fff3e0; color:#e65100;">
                                <i class="fa fa-tag mr-1"></i>{{ $o['total_ofertas'] }}
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

        {{-- Paginacion ofertas --}}
        @if($ofrTotal > $perPage)
        <div class="d-flex align-items-center justify-content-between mt-2" style="font-size:12px; color:#78909c;">
            <span>Mostrando {{ min($ofrStart + 1, $ofrTotal) }}–{{ min($ofrStart + $perPage, $ofrTotal) }} de {{ $ofrTotal }}</span>
            <div class="d-flex align-items-center" style="gap:8px;">
                <button wire:click="ofrPrev" @if($paginaOfr <= 1) disabled @endif
                        class="btn btn-sm btn-outline-warning" style="border-radius:8px; font-size:11px;"
                        @if($paginaOfr <= 1) style="opacity:.5; cursor:not-allowed;" @endif>
                    <i class="fa fa-chevron-left mr-1"></i> Anterior
                </button>
                <span style="font-weight:700; color:#546e7a;">Pág. {{ $paginaOfr }} / {{ $ofrLastPage }}</span>
                <button wire:click="ofrNext" @if($paginaOfr >= $ofrLastPage) disabled @endif
                        class="btn btn-sm btn-outline-warning" style="border-radius:8px; font-size:11px;"
                        @if($paginaOfr >= $ofrLastPage) style="opacity:.5; cursor:not-allowed;" @endif>
                    Siguiente <i class="fa fa-chevron-right ml-1"></i>
                </button>
            </div>
        </div>
        @endif
        @endif
    @elseif($tab === 'temporales')
        <livewire:flujo.temporales-venta tipo="oferta" />
    @endif

</div>
