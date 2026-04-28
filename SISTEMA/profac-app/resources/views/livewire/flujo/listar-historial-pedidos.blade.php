<div>
    <style>
        .hist-th {
            cursor: pointer;
            user-select: none;
            white-space: nowrap;
            background: #e3f2fd;
            font-size: 12px;
            font-weight: 700;
            color: #1565c0;
            padding: 10px 12px;
            border-bottom: 2px solid #90caf9 !important;
            transition: background .15s;
        }
        .hist-th:hover { background: #bbdefb; }
        .hist-th .sort-icon { margin-left: 4px; opacity: .4; font-size: 11px; }
        .hist-th .sort-icon.active { opacity: 1; color: #1a73e8; }
        .hist-badge {
            display: inline-block;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .hist-row { transition: background .1s; }
        .hist-row:hover > td { background: #f0f7ff !important; cursor: pointer; }
    </style>

    {{-- ── Filtros ─────────────────────────────────────────────────────────── --}}
    <div class="d-flex flex-wrap align-items-end gap-2 mb-3">

        {{-- Búsqueda --}}
        <div class="input-group" style="max-width:320px;">
            <div class="input-group-prepend">
                <span class="input-group-text"
                      style="background:#1a73e8; color:#fff; border-color:#1a73e8; border-radius:8px 0 0 8px;">
                    <i class="fa fa-search"></i>
                </span>
            </div>
            <input type="text"
                   wire:model.debounce.300ms="busqueda"
                   class="form-control"
                   placeholder="Buscar por cliente, RTN o # pedido…"
                   style="border-radius:0 8px 8px 0;">
        </div>

        {{-- Filtro de estado --}}
        <select wire:model="filtroEstado"
                class="form-control shadow-sm"
                style="max-width:190px; border-radius:8px; font-size:13px;">
            <option value="">Todos los estados</option>
            <option value="pedido">Pedido</option>
            <option value="Ofertas">Ofertas</option>
            <option value="prefactura">Pre-factura</option>
            <option value="factura">Factura</option>
            <option value="Entrega Cobro">Entrega / Cobro</option>
            <option value="cancelado">Cancelado</option>
        </select>

        {{-- Spinner --}}
        <div wire:loading style="line-height:34px;">
            <i class="fa fa-spinner fa-spin text-primary ml-2"></i>
        </div>
    </div>

    {{-- ── Conteo ───────────────────────────────────────────────────────────── --}}
    <div class="mb-2 d-flex align-items-center">
        <small style="color:#78909c;">
            <i class="fa fa-list mr-1"></i>
            {{ count($pedidos) }} registro(s)
        </small>
        @if(!$esAdmin)
        &nbsp;
        <span class="badge badge-warning ml-1" style="font-size:10px; vertical-align:middle;">
            <i class="fa fa-user"></i> Solo tus pedidos
        </span>
        @endif
    </div>

    {{-- ── Tabla ────────────────────────────────────────────────────────────── --}}
    @if(count($pedidos) === 0)
        <div class="text-center py-5">
            <i class="fa fa-inbox fa-3x mb-3 d-block" style="color:#b2dfdb;"></i>
            <p style="color:#78909c; font-size:14px;">No se encontraron pedidos con los filtros aplicados.</p>
        </div>
    @else
    <div class="table-responsive">
        <table class="table table-bordered mb-0" style="font-size:13px; border-color:#dee2e6;">
            <thead>
                <tr>
                    {{-- # --}}
                    <th class="hist-th" wire:click="sortBy('id')"
                        style="width:60px; text-align:center;">
                        #
                        <i class="fa {{ $sortCol==='id' ? ($sortDir==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                      sort-icon {{ $sortCol==='id' ? 'active' : '' }}"></i>
                    </th>
                    {{-- Cliente --}}
                    <th class="hist-th" wire:click="sortBy('cliente')">
                        Cliente
                        <i class="fa {{ $sortCol==='cliente' ? ($sortDir==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                      sort-icon {{ $sortCol==='cliente' ? 'active' : '' }}"></i>
                    </th>
                    {{-- RTN --}}
                    <th class="hist-th" style="width:145px; cursor:default;">RTN</th>
                    {{-- Ítems --}}
                    <th class="hist-th" wire:click="sortBy('total_productos')"
                        style="width:75px; text-align:center;">
                        Ítems
                        <i class="fa {{ $sortCol==='total_productos' ? ($sortDir==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                      sort-icon {{ $sortCol==='total_productos' ? 'active' : '' }}"></i>
                    </th>
                    {{-- Ofertas --}}
                    <th class="hist-th" wire:click="sortBy('total_ofertas')"
                        style="width:85px; text-align:center;">
                        Ofertas
                        <i class="fa {{ $sortCol==='total_ofertas' ? ($sortDir==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                      sort-icon {{ $sortCol==='total_ofertas' ? 'active' : '' }}"></i>
                    </th>
                    {{-- Estado --}}
                    <th class="hist-th" wire:click="sortBy('estado_flujo')"
                        style="width:145px; text-align:center;">
                        Estado
                        <i class="fa {{ $sortCol==='estado_flujo' ? ($sortDir==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                      sort-icon {{ $sortCol==='estado_flujo' ? 'active' : '' }}"></i>
                    </th>
                    {{-- Fecha --}}
                    <th class="hist-th" wire:click="sortBy('created_at')"
                        style="width:105px; text-align:center;">
                        Fecha
                        <i class="fa {{ $sortCol==='created_at' ? ($sortDir==='asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}
                                      sort-icon {{ $sortCol==='created_at' ? 'active' : '' }}"></i>
                    </th>
                    @if($esAdmin)
                    <th class="hist-th" style="width:130px; cursor:default;">Registrado por</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $ped)
                @php
                    $p = (array) $ped;

                    // ── Estado → [bg, color, label, icon] ─────────────────────────
                    $estadoMap = [
                        'pedido'        => ['#e8f5e9', '#2e7d32', 'Pedido',          'fa-shopping-cart'],
                        'Ofertas'       => ['#fff3e0', '#e65100', 'Ofertas',          'fa-tag'],
                        'prefactura'    => ['#e0f7fa', '#006064', 'Pre-factura',      'fa-file-o'],
                        'factura'       => ['#e8f5e9', '#1b5e20', 'Factura',          'fa-file-text'],
                        'Entrega Cobro' => ['#ede7f6', '#4527a0', 'Entrega / Cobro',  'fa-truck'],
                        'cancelado'     => ['#fce4ec', '#b71c1c', 'Cancelado',        'fa-ban'],
                        'sin_flujo'     => ['#f5f5f5', '#78909c', 'Sin flujo',        'fa-question-circle'],
                    ];
                    $ef = $p['estado_flujo'] ?? 'sin_flujo';
                    $ec = $estadoMap[$ef] ?? ['#f5f5f5', '#78909c', ucfirst($ef), 'fa-circle'];
                @endphp
                <tr class="hist-row"
                    wire:click="abrirModalPedido({{ $p['id'] }})"
                    title="Ver flujo del pedido #{{ $p['id'] }}">

                    {{-- # --}}
                    <td class="text-center align-middle" style="padding:8px 8px;">
                        <span style="background:linear-gradient(135deg,#1565c0,#1a73e8);
                                     color:#fff; border-radius:6px; padding:2px 9px;
                                     font-weight:800; font-size:13px;">
                            #{{ $p['id'] }}
                        </span>
                    </td>

                    {{-- Cliente --}}
                    <td class="align-middle" style="padding:8px 12px;">
                        <div style="font-weight:700; color:#2c3e50; line-height:1.3;">
                            {{ $p['cliente'] }}
                        </div>
                        @if($p['observaciones'])
                        <div style="font-size:11px; color:#90a4ae; line-height:1.2; margin-top:2px;">
                            {{ Str::limit($p['observaciones'], 55) }}
                        </div>
                        @endif
                    </td>

                    {{-- RTN --}}
                    <td class="align-middle" style="color:#546e7a; padding:8px 12px; font-size:12px;">
                        {{ $p['rtn'] ?: '—' }}
                    </td>

                    {{-- Ítems --}}
                    <td class="text-center align-middle" style="padding:8px 6px;">
                        <span class="hist-badge" style="background:#e8eaf6; color:#3949ab;">
                            {{ $p['total_productos'] }}
                        </span>
                    </td>

                    {{-- Ofertas --}}
                    <td class="text-center align-middle" style="padding:8px 6px;">
                        @if($p['total_ofertas'] > 0)
                            <span class="hist-badge" style="background:#fff3e0; color:#e65100;">
                                <i class="fa fa-tag mr-1"></i>{{ $p['total_ofertas'] }}
                            </span>
                        @else
                            <span style="color:#cfd8dc;">—</span>
                        @endif
                    </td>

                    {{-- Estado --}}
                    <td class="text-center align-middle" style="padding:8px 6px;">
                        <span class="hist-badge"
                              style="background:{{ $ec[0] }}; color:{{ $ec[1] }};
                                     border:1px solid {{ $ec[1] }}33;">
                            <i class="fa {{ $ec[3] }} mr-1"></i>{{ $ec[2] }}
                        </span>
                    </td>

                    {{-- Fecha --}}
                    <td class="text-center align-middle"
                        style="color:#78909c; font-size:12px; padding:8px 6px; white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}
                        <div style="font-size:10px; color:#b0bec5;">
                            {{ \Carbon\Carbon::parse($p['created_at'])->format('H:i') }}
                        </div>
                    </td>

                    @if($esAdmin)
                    {{-- Registrado por --}}
                    <td class="align-middle" style="font-size:11px; color:#546e7a; padding:8px 10px;">
                        <i class="fa fa-user-circle mr-1 text-muted"></i>{{ $p['registrado_por'] ?? '—' }}
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
