<div>
    {{-- ── Barra título + acción ─────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <span style="font-weight:700; color:#e65100; font-size:14px;">
            <i class="fa fa-clipboard-list mr-1"></i> Pedidos disponibles para ofertar
        </span>
        <button type="button" wire:click="nuevaOfertaSinPedido"
                style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                       border-radius:8px; padding:7px 16px; font-size:13px; font-weight:700; cursor:pointer;
                       box-shadow:0 2px 8px rgba(230,81,0,.28);">
            <i class="fa fa-plus mr-1"></i> Nueva Oferta sin Pedido
        </button>
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
            <option value="pre_factura">Pre-factura</option>
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
        <p style="color:#78909c; font-size:14px;">No hay pedidos activos para ofertar.</p>
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
                    // Usar estado del flujo si existe; si no, usar campo de la tabla pedido
                    $estadoFlujo = $p['estado_flujo'] ?? null;
                    $estadoLabel = $estadoFlujo ?: $p['estado'];
                    $estadoMap = [
                        'pendiente'   => ['#e3f2fd', '#1565c0', 'Pendiente'],
                        'activo'      => ['#e8f5e9', '#2e7d32', 'Activo'],
                        'pre_factura' => ['#fff8e1', '#f57f17', 'Pre-factura'],
                        'cancelado'   => ['#fce4ec', '#b71c1c', 'Cancelado'],
                        'pedido'      => ['#e3f2fd', '#1565c0', 'Pedido'],
                        'oferta'      => ['#fff3e0', '#e65100', 'Oferta'],
                        'ofertas'     => ['#fff3e0', '#e65100', 'Oferta'],
                        'prefactura'  => ['#f3e5f5', '#6a1b9a', 'Prefactura'],
                        'factura'     => ['#e8f5e9', '#1b5e20', 'Factura'],
                        'entrega cobro' => ['#e0f7fa', '#00695c', 'Entrega/Cobro'],
                    ];
                    $ec = $estadoMap[strtolower($estadoLabel)] ?? ['#f5f5f5', '#546e7a', ucfirst(str_replace('_',' ',$estadoLabel))];
                @endphp
                <tr style="cursor:pointer;" wire:click="abrirModalPedido({{ $p['id'] }})" title="Ver opciones del pedido #{{ $p['id'] }}">
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

    <div wire:loading class="text-center py-3">
        <i class="fa fa-spinner fa-spin" style="color:#00897b; font-size:20px;"></i>
    </div>

    {{-- ── Cotizaciones sin pedido ─────────────────────────────────────────── --}}
    @if(count($cotizacionesSinPedido) > 0)
    <div class="mt-4" style="border-top:2px dashed #ffe0b2; padding-top:14px;">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span style="font-weight:700; color:#e65100; font-size:13px;">
                <i class="fa fa-file-text-o mr-1"></i> Ofertas sin pedido
                <span style="background:#fff3e0; color:#e65100; border-radius:20px; padding:2px 10px; font-size:11px; font-weight:700; margin-left:6px;">{{ count($cotizacionesSinPedido) }}</span>
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover" style="font-size:13px;">
                <thead style="background:#fff3e0;">
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>RTN</th>
                        <th style="text-align:center;">Prods.</th>
                        <th style="text-align:right;">Total</th>
                        <th style="text-align:center;">Estado</th>
                        <th>Registrado</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cotizacionesSinPedido as $cot)
                    @php $c = (array) $cot; @endphp
                    <tr>
                        <td>
                            <span style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff;
                                         border-radius:6px; padding:3px 10px; font-weight:800; font-size:13px;">
                                #{{ $c['id'] }}
                            </span>
                        </td>
                        <td style="font-weight:600; color:#2c3e50;">{{ $c['nombre_cliente'] ?: '—' }}</td>
                        <td style="color:#546e7a;">{{ $c['RTN'] ?: '—' }}</td>
                        <td class="text-center">
                            <span style="background:#e8eaf6; color:#3949ab; border-radius:20px; padding:2px 10px; font-size:11px; font-weight:700;">
                                {{ $c['total_productos'] }}
                            </span>
                        </td>
                        <td class="text-right">
                            <strong style="color:#2e7d32;">L {{ number_format($c['total'], 2) }}</strong>
                        </td>
                        <td class="text-center">
                            @if($c['es_ganadora'])
                                <span style="background:#e8f5e9; color:#2e7d32; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700;">
                                    <i class="fa fa-trophy mr-1"></i> Ganadora
                                </span>
                            @else
                                <span style="background:#e3f2fd; color:#1565c0; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700;">
                                    <i class="fa fa-clock-o mr-1"></i> Activa
                                </span>
                            @endif
                        </td>
                        <td style="font-size:12px; color:#546e7a;">{{ $c['registrado_por'] ?: '—' }}</td>
                        <td style="font-size:11px; color:#78909c;">
                            {{ \Carbon\Carbon::parse($c['created_at'])->format('d/m/Y') }}
                        </td>
                        <td>
                            <a href="/proforma/cotizacion/2?id={{ $c['id'] }}&from=flujo"
                               style="background:#fff3e0; color:#e65100; border:1.5px solid #f9a826;
                                      border-radius:7px; padding:4px 12px; font-size:12px; font-weight:700;
                                      text-decoration:none; white-space:nowrap;">
                                <i class="fa fa-pencil mr-1"></i> Ver
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
