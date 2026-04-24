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
                    <th>Fecha</th>
                    <th style="border-radius:0 8px 0 0;">Acción</th>
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
                    $ec = $estadoMap[$p['estado']] ?? ['#f5f5f5', '#546e7a', ucfirst($p['estado'])];
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
                    <td onclick="event.stopPropagation()">
                        <button type="button" wire:click.stop="abrirModalPedido({{ $p['id'] }})"
                                style="background:linear-gradient(135deg,#546e7a,#78909c); color:#fff; border:none;
                                       border-radius:8px; padding:6px 14px; font-size:12px; font-weight:700;
                                       cursor:pointer; white-space:nowrap; box-shadow:0 2px 6px rgba(0,0,0,.15);">
                            <i class="fa fa-ellipsis-h mr-1"></i> Opciones
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ── Loading ──────────────────────────────────────────────────────── --}}
    <div wire:loading class="text-center py-3">
        <i class="fa fa-spinner fa-spin" style="color:#1a73e8; font-size:20px;"></i>
    </div>

    {{-- ===== MODAL DETALLE / ACCIONES DE PEDIDO ===== --}}
    @if($showModalPedido && $pedidoSeleccionado)
    <div style="position:fixed; inset:0; z-index:9999;">
        {{-- Backdrop --}}
        <div style="position:absolute; inset:0; background:rgba(0,0,0,.55);" wire:click="cerrarModalPedido"></div>

        {{-- Panel --}}
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
                    background:#fff; border-radius:16px; width:calc(100% - 32px); max-width:480px;
                    max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.30); z-index:1;">

            {{-- Header --}}
            <div style="background:linear-gradient(135deg,#1565c0,#1e88e5); padding:14px 18px;
                        border-radius:16px 16px 0 0; display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h5 style="color:#fff; margin:0; font-weight:800; font-size:14px;">
                        <i class="fa fa-shopping-cart mr-2"></i>Pedido #{{ $pedidoSeleccionado['id'] }}
                    </h5>
                    <div style="color:rgba(255,255,255,.8); font-size:11px; margin-top:2px;">
                        {{ $pedidoSeleccionado['cliente'] }}
                        &nbsp;·&nbsp;
                        {{ \Carbon\Carbon::parse($pedidoSeleccionado['created_at'])->format('d/m/Y') }}
                    </div>
                </div>
                <button type="button" wire:click="cerrarModalPedido"
                        style="background:rgba(255,255,255,.2); border:none; color:#fff; border-radius:50%;
                               width:28px; height:28px; font-size:14px; cursor:pointer;
                               display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <div style="padding:16px 18px 18px;">

                {{-- Mensajes --}}
                @if($mensajeExito)
                <div style="background:#e8f5e9; border:1px solid #c8e6c9; border-radius:8px;
                            padding:8px 12px; color:#2e7d32; font-weight:600; font-size:12px;
                            margin-bottom:12px; display:flex; gap:7px; align-items:center;">
                    <i class="fa fa-check-circle"></i> {{ $mensajeExito }}
                </div>
                @endif
                @if($mensajeError && $confirmAccion !== 'anular')
                <div style="background:#fce4ec; border:1px solid #f8bbd0; border-radius:8px;
                            padding:8px 12px; color:#b71c1c; font-weight:600; font-size:12px;
                            margin-bottom:12px; display:flex; gap:7px; align-items:center;">
                    <i class="fa fa-exclamation-circle"></i> {{ $mensajeError }}
                </div>
                @endif

                {{-- Info en 2 columnas compactas --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px;">
                    <div style="background:#f8f9fa; border-radius:8px; padding:9px 12px;">
                        <div style="font-size:10px; color:#90a4ae; font-weight:700; text-transform:uppercase; margin-bottom:2px;">Cliente</div>
                        <div style="font-weight:700; color:#2c3e50; font-size:12px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $pedidoSeleccionado['cliente'] }}</div>
                        <div style="font-size:10px; color:#90a4ae;">{{ $pedidoSeleccionado['rtn'] ?: '—' }}</div>
                    </div>
                    <div style="background:#f8f9fa; border-radius:8px; padding:9px 12px;">
                        <div style="font-size:10px; color:#90a4ae; font-weight:700; text-transform:uppercase; margin-bottom:4px;">Estado</div>
                        @php
                            $estMapM = [
                                'pendiente'   => ['#e3f2fd','#1565c0'],
                                'pre_factura' => ['#fff8e1','#f57f17'],
                                'activo'      => ['#e8f5e9','#2e7d32'],
                                'pedido'      => ['#e8f5e9','#2e7d32'],
                                'cancelado'   => ['#fce4ec','#b71c1c'],
                            ];
                            $colM = $estMapM[$pedidoSeleccionado['estado']] ?? ['#f5f5f5','#546e7a'];
                        @endphp
                        <span style="background:{{ $colM[0] }}; color:{{ $colM[1] }};
                                     border-radius:20px; padding:2px 10px; font-size:11px; font-weight:700;">
                            {{ ucfirst(str_replace('_', ' ', $pedidoSeleccionado['estado'])) }}
                        </span>
                    </div>
                </div>

                {{-- Productos --}}
                @if(count($pedidoDetalles) > 0)
                <div style="margin-bottom:14px;">
                    <div style="font-size:10px; color:#90a4ae; font-weight:700; text-transform:uppercase;
                                margin-bottom:6px; letter-spacing:.4px;">
                        <i class="fa fa-list mr-1"></i>Productos ({{ count($pedidoDetalles) }})
                        &nbsp;·&nbsp; <span style="color:#546e7a;">{{ $pedidoSeleccionado['total_ofertas'] }} oferta(s)</span>
                        @if($pedidoSeleccionado['has_ganadora'] > 0)
                            &nbsp;<i class="fa fa-trophy text-warning" title="Tiene oferta ganadora"></i>
                        @endif
                    </div>
                    <div style="max-height:140px; overflow-y:auto; border:1px solid #eceff1; border-radius:8px;">
                        @foreach($pedidoDetalles as $det)
                        @php $d = (array)$det; @endphp
                        <div style="display:flex; justify-content:space-between; align-items:center;
                                    padding:6px 10px; font-size:12px;
                                    {{ !$loop->last ? 'border-bottom:1px solid #f5f5f5;' : '' }}">
                            <span style="color:#37474f; font-weight:600;">{{ $d['nombre_producto'] ?: '—' }}</span>
                            <span style="background:#eceff1; color:#546e7a; border-radius:20px;
                                         padding:1px 8px; font-weight:700; white-space:nowrap; font-size:11px;">
                                ×{{ $d['cantidad'] }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div style="text-align:center; padding:10px; background:#f8f9fa; border-radius:8px; margin-bottom:14px;">
                    <small style="color:#90a4ae;"><i class="fa fa-inbox mr-1"></i>Sin productos registrados</small>
                </div>
                @endif

                {{-- Acciones / Confirmación --}}
                @if($confirmAccion === 'anular')
                <div style="background:#fce4ec; border:2px solid #ef9a9a; border-radius:10px;
                            padding:14px; text-align:center;">
                    <i class="fa fa-exclamation-triangle fa-lg mb-1" style="color:#c62828; display:block;"></i>
                    <p style="font-weight:700; color:#c62828; margin:0 0 3px; font-size:13px;">¿Confirmar anulación?</p>
                    <p style="font-size:11px; color:#e57373; margin:0 0 10px;">
                        El pedido #{{ $pedidoSeleccionado['id'] }} quedará cancelado.
                    </p>
                    <textarea wire:model.defer="motivoAnulacion"
                              placeholder="Motivo de anulación (obligatorio)..."
                              rows="2"
                              style="width:100%; border-radius:7px; border:1px solid #ef9a9a;
                                     padding:7px 10px; font-size:12px; resize:vertical;
                                     margin-bottom:10px; outline:none;"
                    ></textarea>
                    @if($mensajeError)
                    <div style="color:#c62828; font-size:11px; font-weight:700; margin-bottom:8px;">
                        <i class="fa fa-exclamation-circle mr-1"></i>{{ $mensajeError }}
                    </div>
                    @endif
                    <div style="display:flex; gap:8px; justify-content:center;">
                        <button type="button" wire:click="cancelarConfirmacion"
                                style="background:#f5f5f5; color:#546e7a; border:1px solid #e0e0e0;
                                       border-radius:7px; padding:7px 18px; font-weight:700; font-size:12px; cursor:pointer;">
                            <i class="fa fa-arrow-left mr-1"></i>Volver
                        </button>
                        <button type="button" wire:click="anularPedido"
                                style="background:#c62828; color:#fff; border:none;
                                       border-radius:7px; padding:7px 18px; font-weight:700; font-size:12px; cursor:pointer;">
                            <i class="fa fa-ban mr-1"></i>Sí, Anular
                        </button>
                    </div>
                </div>

                @elseif($confirmAccion === 'duplicar')
                <div style="background:#e3f2fd; border:2px solid #90caf9; border-radius:10px;
                            padding:14px; text-align:center;">
                    <i class="fa fa-copy fa-lg mb-1" style="color:#1565c0; display:block;"></i>
                    <p style="font-weight:700; color:#1565c0; margin:0 0 3px; font-size:13px;">¿Duplicar este pedido?</p>
                    <p style="font-size:11px; color:#1e88e5; margin:0 0 12px;">
                        Se abrirá la pantalla de pedidos con los productos pre-cargados. Deberás elegir el cliente.
                    </p>
                    <div style="display:flex; gap:8px; justify-content:center;">
                        <button type="button" wire:click="cancelarConfirmacion"
                                style="background:#f5f5f5; color:#546e7a; border:1px solid #e0e0e0;
                                       border-radius:7px; padding:7px 18px; font-weight:700; font-size:12px; cursor:pointer;">
                            <i class="fa fa-arrow-left mr-1"></i>Volver
                        </button>
                        <button type="button" wire:click="duplicarPedido"
                                style="background:#1565c0; color:#fff; border:none;
                                       border-radius:7px; padding:7px 18px; font-weight:700; font-size:12px; cursor:pointer;">
                            <i class="fa fa-copy mr-1"></i>Sí, Continuar
                        </button>
                    </div>
                </div>

                @else
                {{-- Menú de opciones --}}
                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px;">

                    {{-- Imprimir --}}
                    <a href="/flujo/pedido/imprimir/{{ $pedidoSeleccionado['id'] }}" target="_blank"
                       style="background:linear-gradient(135deg,#00897b,#26a69a); color:#fff;
                              border-radius:12px; padding:14px 6px; cursor:pointer;
                              font-weight:700; font-size:12px; display:flex; flex-direction:column;
                              align-items:center; gap:6px; text-decoration:none; transition:transform .15s;"
                       onmouseover="this.style.transform='translateY(-2px)';"
                       onmouseout="this.style.transform='';">
                        <i class="fa fa-print fa-lg"></i>
                        Imprimir
                    </a>

                    {{-- Anular --}}
                    @if($pedidoSeleccionado['estado'] !== 'cancelado')
                    <button type="button" wire:click="confirmarAccion('anular')"
                            style="background:linear-gradient(135deg,#c62828,#e53935); color:#fff;
                                   border:none; border-radius:12px; padding:14px 6px; cursor:pointer;
                                   font-weight:700; font-size:12px; display:flex; flex-direction:column;
                                   align-items:center; gap:6px; transition:transform .15s;"
                            onmouseover="this.style.transform='translateY(-2px)';"
                            onmouseout="this.style.transform='';">
                        <i class="fa fa-ban fa-lg"></i>
                        Anular
                    </button>
                    @else
                    <div style="background:#f5f5f5; border-radius:12px; padding:14px 6px;
                                font-size:11px; color:#90a4ae; display:flex; flex-direction:column;
                                align-items:center; gap:6px; text-align:center;">
                        <i class="fa fa-ban fa-lg"></i>Ya cancelado
                    </div>
                    @endif

                    {{-- Duplicar --}}
                    <button type="button" wire:click="confirmarAccion('duplicar')"
                            style="background:linear-gradient(135deg,#1565c0,#1e88e5); color:#fff;
                                   border:none; border-radius:12px; padding:14px 6px; cursor:pointer;
                                   font-weight:700; font-size:12px; display:flex; flex-direction:column;
                                   align-items:center; gap:6px; transition:transform .15s;"
                            onmouseover="this.style.transform='translateY(-2px)';"
                            onmouseout="this.style.transform='';">
                        <i class="fa fa-copy fa-lg"></i>
                        Duplicar
                    </button>

                    {{-- Agregar Oferta --}}
                    @if($pedidoSeleccionado['has_ganadora'] == 0 && $pedidoSeleccionado['estado'] !== 'cancelado')
                    <button type="button" wire:click="nuevaOferta({{ $pedidoSeleccionado['id'] }})"
                            style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff;
                                   border:none; border-radius:12px; padding:14px 6px; cursor:pointer;
                                   font-weight:700; font-size:12px; display:flex; flex-direction:column;
                                   align-items:center; gap:6px; transition:transform .15s;"
                            onmouseover="this.style.transform='translateY(-2px)';"
                            onmouseout="this.style.transform='';">
                        <i class="fa fa-plus fa-lg"></i>
                        Ag. Oferta
                    </button>
                    @else
                    <div style="background:#f5f5f5; border-radius:12px; padding:14px 6px;
                                font-size:11px; color:#90a4ae; display:flex; flex-direction:column;
                                align-items:center; gap:6px; text-align:center;">
                        <i class="fa fa-lock fa-lg"></i>
                        {{ $pedidoSeleccionado['estado'] === 'cancelado' ? 'Cancelado' : 'Ganadora' }}
                    </div>
                    @endif

                </div>
                @endif

            </div>
        </div>
    </div>
    @endif

</div>

<script>
    window.addEventListener('abrir-nueva-pestana', function(e) {
        window.open(e.detail.url, '_blank');
    });
</script>
