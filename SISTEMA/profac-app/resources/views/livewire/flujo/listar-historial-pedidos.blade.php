<div>
    {{-- ── Barra título ────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <span style="font-weight:700; color:#1565c0; font-size:14px;">
            <i class="fa fa-history mr-1"></i> Historial de Pedidos
        </span>
    </div>

    {{-- ── Filtros ─────────────────────────────────────────────────────── --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="input-group" style="max-width:340px;">
            <div class="input-group-prepend">
                <span class="input-group-text" style="background:#1a73e8; color:#fff; border-color:#1a73e8; border-radius:8px 0 0 8px;">
                    <i class="fa fa-search"></i>
                </span>
            </div>
            <input type="text" wire:model.debounce.300ms="busqueda"
                   class="form-control" placeholder="Buscar por cliente, RTN o # pedido…"
                   style="border-radius:0 8px 8px 0;">
        </div>

        <select wire:model="filtroEstado" class="form-control" style="max-width:160px; border-radius:8px;">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="activo">Activo</option>
            <option value="pedido">Pedido</option>
            <option value="pre_factura">Pre-factura</option>
            <option value="cancelado">Cancelado</option>
        </select>
    </div>

    {{-- ── Conteo ──────────────────────────────────────────────────────── --}}
    <div class="mb-2" style="font-size:12px; color:#78909c;">
        <i class="fa fa-list mr-1"></i> {{ count($pedidos) }} pedido(s) encontrado(s)
    </div>

    {{-- ── Tabla ───────────────────────────────────────────────────────── --}}
    @if(count($pedidos) === 0)
    <div class="text-center py-5">
        <i class="fa fa-inbox fa-3x mb-3" style="color:#b2dfdb; display:block;"></i>
        <p style="color:#78909c; font-size:14px;">No hay pedidos registrados.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover" style="font-size:13px;">
            <thead style="background:#e3f2fd;">
                <tr>
                    <th style="border-radius:8px 0 0 0;">#</th>
                    <th>Cliente</th>
                    <th>RTN</th>
                    <th>Ítems</th>
                    <th>Ofertas</th>
                    <th>Estado</th>
                    <th style="border-radius:0 8px 0 0;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $ped)
                @php
                    $p = (array) $ped;
                    $estadoMap = [
                        'pendiente'   => ['#e3f2fd', '#1565c0', 'Pendiente'],
                        'activo'      => ['#e8f5e9', '#2e7d32', 'Activo'],
                        'pedido'      => ['#e8f5e9', '#2e7d32', 'Pedido'],
                        'pre_factura' => ['#fff8e1', '#f57f17', 'Pre-factura'],
                        'cancelado'   => ['#fce4ec', '#b71c1c', 'Cancelado'],
                    ];
                    // Si tiene ofertas activas, mostrar "Oferta" independientemente del campo estado
                    if ($p['estado'] !== 'cancelado' && $p['total_ofertas'] > 0 && $p['ofertas_ganadoras'] == 0) {
                        $ec = ['#fff3e0', '#e65100', 'Oferta'];
                    } elseif ($p['estado'] !== 'cancelado' && $p['ofertas_ganadoras'] > 0) {
                        $ec = ['#fff8e1', '#f57f17', 'Pre-factura'];
                    } else {
                        $ec = $estadoMap[$p['estado']] ?? ['#f5f5f5', '#546e7a', ucfirst($p['estado'])];
                    }
                @endphp
                <tr style="cursor:pointer; {{ $p['estado'] === 'cancelado' ? 'opacity:.72;' : '' }}"
                    wire:click="abrirModalPedido({{ $p['id'] }})"
                    title="Ver opciones del pedido #{{ $p['id'] }}">
                    <td>
                        <span style="background:linear-gradient(135deg,#1565c0,#1a73e8); color:#fff;
                                     border-radius:6px; padding:3px 10px; font-weight:800; font-size:13px;">
                            #{{ $p['id'] }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:700; color:#2c3e50;">{{ $p['cliente'] }}</div>
                        @if($p['observaciones'])
                        <div style="font-size:11px; color:#90a4ae;">{{ Str::limit($p['observaciones'], 60) }}</div>
                        @endif
                    </td>
                    <td style="color:#546e7a;">{{ $p['rtn'] ?: '—' }}</td>
                    <td>
                        <span style="background:#e8eaf6; color:#3949ab; border-radius:20px; padding:2px 10px; font-size:11px; font-weight:700;">
                            {{ $p['total_productos'] }} ítem(s)
                        </span>
                    </td>
                    <td>
                        @if($p['total_ofertas'] > 0)
                        <span style="font-weight:700; color:#00897b;">{{ $p['total_ofertas'] }}</span>
                        @if($p['ofertas_ganadoras'] > 0)
                            <i class="fa fa-trophy text-warning ml-1" title="Tiene oferta ganadora"></i>
                        @endif
                        @else
                        <span style="color:#b0bec5;">0</span>
                        @endif
                    </td>
                    <td>
                        <span style="background:{{ $ec[0] }}; color:{{ $ec[1] }};
                                     border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700;">
                            {{ $ec[2] }}
                        </span>
                    </td>
                    <td style="color:#78909c; font-size:11px;">
                        {{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

    {{-- ── Loading ──────────────────────────────────────────────────────── --}}
    <div wire:loading class="text-center py-3">
        <i class="fa fa-spinner fa-spin" style="color:#1a73e8; font-size:20px;"></i>
    </div>
</div>
