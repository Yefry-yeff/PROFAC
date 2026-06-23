<div>
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{--                  REVISIÓN DE INVENTARIO                          --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}

    {{-- Page header --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="mr-2 fa fa-search text-primary"></i>Revisión de Inventario</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong>Revisión de Inventario</strong>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- ── Mensajes globales ───────────────────────────────────────── --}}
        @if ($mensajeExito && !$flujoId)
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mr-1 fa fa-check-circle"></i> {{ $mensajeExito }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif
        @if ($mensajeError && !$flujoId)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mr-1 fa fa-exclamation-triangle"></i> {{ $mensajeError }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- VISTA DETALLE (cuando hay un flujo seleccionado)              --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if ($flujoId)

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox" style="border-radius:14px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.09);">
                    {{-- Header detalle --}}
                    <div class="ibox-title d-flex align-items-center justify-content-between"
                         style="background:linear-gradient(135deg,{{ $devuelto ? '#e67e22' : '#1a7efb' }},{{ $devuelto ? '#d35400' : '#0d6efd' }}); border:none; padding:14px 22px;">
                        <div>
                            <h5 style="color:#fff; margin:0; font-weight:700; font-size:15px;">
                                <i class="mr-2 fa fa-search"></i>
                                Revisando Flujo #{{ $flujoId }}
                            </h5>
                            @if($flujoData)
                            <small style="color:rgba(255,255,255,.88); font-size:11px; display:block; margin-top:3px;">
                                Cliente: <strong>{{ $flujoData['cliente'] ?? '—' }}</strong>
                                <span style="opacity:.65;">|</span>
                                Vendedor: <strong>{{ $flujoData['vendedor_nombre'] ?? '—' }}</strong>
                            </small>
                            @endif
                            @if($flujoData && $flujoData['pedido_id'])
                            <small style="color:rgba(255,255,255,.8); font-size:11px;">
                                Pedido #{{ $flujoData['pedido_id'] }}
                                @if($flujoData['pedido_fecha'])
                                    · {{ \Carbon\Carbon::parse($flujoData['pedido_fecha'])->format('d/m/Y') }}
                                @endif
                            </small>
                            @endif
                        </div>
                        <button type="button" wire:click="cerrarDetalle"
                                style="background:rgba(255,255,255,.2); color:#fff; border:none;
                                       border-radius:8px; padding:6px 14px; font-size:12px; font-weight:700; cursor:pointer;">
                            <i class="mr-1 fa fa-arrow-left"></i> Volver a bandeja
                        </button>
                    </div>

                    <div class="ibox-content" style="padding:22px 26px;">

                        {{-- Mensajes de detalle --}}
                        @if ($mensajeExito)
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <i class="mr-1 fa fa-check-circle"></i> {{ $mensajeExito }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                        @endif
                        @if ($mensajeError)
                        <div class="alert alert-danger" role="alert">
                            <i class="mr-1 fa fa-exclamation-triangle"></i> {{ $mensajeError }}
                        </div>
                        @endif

                        {{-- Alerta de stock insuficiente (solo para flujos en revisión, no devueltos) --}}
                        @if (!$devuelto && count($stockErrors) > 0)
                        <div style="background:#fff3e0; border:1px solid #ffcc80; border-radius:12px;
                                    padding:14px 18px; margin-bottom:18px;">
                            <h6 style="color:#e65100; font-weight:700; margin-bottom:8px;">
                                <i class="mr-1 fa fa-exclamation-triangle"></i>
                                {{ count($stockErrors) }} producto(s) sin stock suficiente
                            </h6>
                            <table style="width:100%; font-size:12px; border-collapse:collapse;">
                                <thead>
                                    <tr style="background:#fbe9e7;">
                                        <th style="padding:5px 10px; text-align:left; color:#bf360c;">Producto</th>
                                        <th style="padding:5px 10px; text-align:center; color:#bf360c;">Solicitado</th>
                                        <th style="padding:5px 10px; text-align:center; color:#bf360c;">Disponible</th>
                                        <th style="padding:5px 10px; text-align:center; color:#1565c0;">Global</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stockErrors as $se)
                                    <tr style="border-bottom:1px solid #fce4cc;">
                                        <td style="padding:5px 10px; color:#2c3e50;">{{ $se['producto'] }}</td>
                                        <td style="padding:5px 10px; text-align:center; font-weight:700; color:#e65100;">{{ $se['solicitado'] }}</td>
                                        <td style="padding:5px 10px; text-align:center; font-weight:700; color:#b71c1c;">{{ $se['disponible'] }}</td>
                                        <td style="padding:5px 10px; text-align:center; font-weight:700; color:#1565c0;">{{ $se['disponible_global'] ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <p style="font-size:12px; color:#e65100; margin-top:8px; margin-bottom:0;">
                                <i class="fa fa-info-circle mr-1"></i>
                                Puede agregar notas de reemplazo por producto y luego <strong>Devolver a Oferta</strong>
                                con las observaciones para que el vendedor actualice la oferta.
                            </p>
                        </div>
                        @else
                        @if(!$devuelto && count($productos) > 0)
                        <div style="background:#e8f5e9; border:1px solid #a5d6a7; border-radius:10px;
                                    padding:10px 16px; margin-bottom:16px;">
                            <span style="color:#2e7d32; font-weight:700; font-size:13px;">
                                <i class="mr-1 fa fa-check-circle"></i>
                                Todos los productos tienen stock disponible. Puede pasar a Prefactura.
                            </span>
                        </div>
                        <div style="background:#e3f2fd; border:1px solid #90caf9; border-radius:10px;
                                    padding:10px 16px; margin-bottom:16px;">
                            <span style="color:#1565c0; font-weight:700; font-size:13px;">
                                <i class="mr-1 fa fa-check-square-o"></i>
                                Marque todos los productos como revisados para habilitar las acciones.
                            </span>
                        </div>
                        @endif
                        @endif

                        {{-- Tabla de productos --}}
                        <div style="border-radius:12px; overflow:hidden; border:1px solid #e8eaf0; margin-bottom:20px;">
                            <div style="background:linear-gradient(135deg,#546e7a,#37474f); padding:10px 16px;">
                                <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:10px;">
                                    <span style="color:#fff; font-size:13px; font-weight:700;">
                                        <i class="mr-1 fa fa-list-ul"></i>
                                        Productos de la oferta
                                        <span style="background:rgba(255,255,255,.2); border-radius:20px;
                                                     padding:1px 10px; font-size:11px; margin-left:6px;">
                                            {{ count($this->productosFiltrados) }} / {{ count($productos) }}
                                        </span>
                                    </span>
                                    <button type="button"
                                            wire:click="limpiarFiltrosTabla"
                                            class="btn btn-sm btn-light"
                                            style="border-radius:8px; font-size:12px; font-weight:700;">
                                        <i class="fa fa-eraser mr-1"></i> Limpiar filtros
                                    </button>
                                </div>
                            </div>
                            <div style="background:#f8fafc; border-bottom:1px solid #e8eaf0; padding:12px 14px;">
                                <div class="row" style="row-gap:10px;">
                                    <div class="col-md-4">
                                        <label class="mb-1" style="font-size:12px; font-weight:700; color:#334155;">Producto</label>
                                        <input type="text"
                                               wire:model.debounce.350ms="filtroProducto"
                                               class="form-control form-control-sm"
                                               placeholder="Buscar por nombre..."
                                               style="border-radius:8px;">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="mb-1" style="font-size:12px; font-weight:700; color:#334155;">Bodega</label>
                                        <input type="text"
                                               wire:model.debounce.350ms="filtroBodega"
                                               class="form-control form-control-sm"
                                               placeholder="Buscar por bodega..."
                                               style="border-radius:8px;">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="mb-1" style="font-size:12px; font-weight:700; color:#334155;">Estado</label>
                                        <select wire:model="filtroEstado" class="form-control form-control-sm" style="border-radius:8px;">
                                            <option value="">Todos</option>
                                            <option value="ok">OK</option>
                                            <option value="sin_existencia">Sin existencia</option>
                                            <option value="sin_stock">Sin stock</option>
                                            <option value="sin_control">Sin control</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="mb-1" style="font-size:12px; font-weight:700; color:#334155;">Revisado</label>
                                        <select wire:model="filtroRevisado" class="form-control form-control-sm" style="border-radius:8px;">
                                            <option value="">Todos</option>
                                            <option value="si">Marcados</option>
                                            <option value="no">Pendientes</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            @if(count($productos) === 0)
                            <div style="padding:24px; text-align:center; color:#aaa;">
                                <i class="fa fa-inbox d-block" style="font-size:28px; margin-bottom:8px; opacity:.4;"></i>
                                Sin productos registrados.
                            </div>
                            @else
                            <div style="overflow-x:auto;">
                                <table class="table table-hover table-sm mb-0" style="font-size:13px; margin:0;">
                                    <thead style="background:#f8f9fc;">
                                        <tr>
                                            <th style="padding:10px 14px; color:#555; font-weight:700;">#</th>
                                            <th style="padding:10px 14px; color:#555; font-weight:700;">Producto</th>
                                            <th style="padding:10px 14px; color:#555; font-weight:700;">Bodega</th>
                                            <th style="padding:10px 10px; color:#555; font-weight:700; white-space:nowrap;">Unidad</th>
                                            <th style="padding:10px 14px; text-align:center; color:#555; font-weight:700;">Cantidad</th>
                                            <th style="padding:10px 14px; text-align:center; color:#e65100; font-weight:700;">Reserva</th>
                                            <th style="padding:10px 14px; text-align:center; color:#555; font-weight:700;">Cant. en Bodega</th>
                                            <th style="padding:10px 14px; text-align:center; color:#1565c0; font-weight:700;">Cant. Disponible</th>
                                            <th style="padding:10px 14px; text-align:center; color:#2e7d32; font-weight:700;">Revisado</th>
                                            <th style="padding:10px 14px; text-align:center; color:#555; font-weight:700;">Estado</th>
                                            <th style="padding:10px 14px; color:#555; font-weight:700;">Nota / Reemplazo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($this->productosFiltrados) === 0)
                                        <tr>
                                            <td colspan="11" style="padding:20px; text-align:center; color:#78909c; background:#fff;">
                                                <i class="fa fa-filter d-block" style="font-size:24px; margin-bottom:6px; opacity:.55;"></i>
                                                No hay productos que coincidan con los filtros.
                                            </td>
                                        </tr>
                                        @else
                                            @foreach ($this->productosFiltrados as $prod)
                                            <tr style="border-bottom:1px solid #f0f0f0;
                                                       {{ $prod['falta_stock'] ? 'background:#fff8f5;' : '' }}
                                                       {{ !empty($productosRevisados[$prod['idx']]) ? 'box-shadow: inset 4px 0 0 #2e7d32;' : '' }}">
                                                <td style="padding:8px 14px; color:#888;">{{ $loop->iteration }}</td>
                                                <td style="padding:8px 14px; color:#2c3e50; font-weight:600;">
                                                    {{ $prod['nombre_producto'] }}
                                                </td>
                                                <td style="padding:8px 14px; color:#607d8b; font-size:12px;">
                                                    {{ $prod['nombre_bodega'] ?? '—' }}
                                                </td>
                                                <td style="padding:8px 10px; color:#607d8b; font-size:12px; white-space:nowrap;">
                                                    <span style="background:#f1f5f9; color:#334155; border-radius:999px; padding:1px 8px; font-weight:600; display:inline-block; font-size:11px; line-height:1.2;">
                                                        {{ $prod['unidad_medida'] ?? '—' }}
                                                    </span>
                                                </td>
                                                <td style="padding:8px 14px; text-align:center;">
                                                    <span style="background:#e3f2fd; color:#1565c0; border-radius:12px;
                                                                 padding:2px 10px; font-weight:700; font-size:13px;">
                                                        {{ (int)$prod['cantidad'] }}
                                                    </span>
                                                </td>
                                                {{-- Reserva (clicable si hay reservas) --}}
                                                <td style="padding:8px 14px; text-align:center;">
                                                    @if ($prod['reservado'] !== null)
                                                        @if ((float)$prod['reservado'] > 0)
                                                            <button type="button"
                                                                    onclick="abrirModalReservasInv(this)"
                                                                    data-nombre="{{ e($prod['nombre_producto']) }}"
                                                                    data-reservas='@json($prod['reservas_detalle'])'
                                                                    style="background:#fff3e0; color:#e65100; border:1px solid #ffcc80;
                                                                           border-radius:12px; padding:2px 12px; font-weight:700;
                                                                           font-size:13px; cursor:pointer;"
                                                                    title="Ver flujos con reserva">
                                                                <i class="fa fa-lock mr-1" style="font-size:11px;"></i>{{ (int)$prod['reservado'] }}
                                                            </button>
                                                        @else
                                                            <span style="background:#f1f5f9; color:#90a4ae; border-radius:12px; padding:2px 10px; font-size:13px;">0</span>
                                                        @endif
                                                    @else
                                                        <span style="color:#aaa; font-size:12px;">—</span>
                                                    @endif
                                                </td>
                                                {{-- Cant. en Bodega (rawStock) --}}
                                                <td style="padding:8px 14px; text-align:center;">
                                                    @if ($prod['rawStock'] !== null)
                                                        <span style="background:#f3e5f5; color:#6a1b9a;
                                                                     border-radius:12px; padding:2px 10px; font-weight:700; font-size:13px;">
                                                            {{ (int)$prod['rawStock'] }}
                                                        </span>
                                                    @else
                                                        <span style="color:#aaa; font-size:12px;">—</span>
                                                    @endif
                                                </td>
                                                {{-- Cant. Disponible = rawStock - reservado --}}
                                                <td style="padding:8px 14px; text-align:center;">
                                                    @if ($prod['disponible'] !== null)
                                                        <span style="background:{{ $prod['falta_stock'] ? '#fce4ec' : '#e8f5e9' }};
                                                                     color:{{ $prod['falta_stock'] ? '#b71c1c' : '#2e7d32' }};
                                                                     border-radius:12px; padding:2px 10px; font-weight:700; font-size:13px;">
                                                            {{ (int)$prod['disponible'] }}
                                                        </span>
                                                    @else
                                                        <span style="color:#aaa; font-size:12px;">—</span>
                                                    @endif
                                                </td>
                                                <td style="padding:8px 14px; text-align:center;">
                                                    <div class="custom-control custom-checkbox d-inline-flex align-items-center">
                                                        <input type="checkbox"
                                                               class="custom-control-input"
                                                               id="rev_{{ $prod['idx'] }}"
                                                               wire:model="productosRevisados.{{ $prod['idx'] }}"
                                                               {{ ($devuelto || $soloVisualizacion) ? 'disabled' : '' }}>
                                                        <label class="custom-control-label" for="rev_{{ $prod['idx'] }}"></label>
                                                    </div>
                                                </td>
                                                <td style="padding:8px 14px; text-align:center;">
                                                    @if ($prod['sin_existencia'] ?? false)
                                                        <span style="background:#ffebee; color:#c62828; border-radius:8px;
                                                                     padding:3px 10px; font-size:11px; font-weight:700;">
                                                            <i class="fa fa-ban mr-1"></i>Sin existencia
                                                        </span>
                                                    @elseif ($prod['falta_stock'])
                                                        <span style="background:#fce4ec; color:#b71c1c; border-radius:8px;
                                                                     padding:3px 10px; font-size:11px; font-weight:700;">
                                                            <i class="fa fa-exclamation-triangle mr-1"></i>Sin stock
                                                        </span>
                                                    @elseif ($prod['disponible'] !== null)
                                                        <span style="background:#e8f5e9; color:#2e7d32; border-radius:8px;
                                                                     padding:3px 10px; font-size:11px; font-weight:700;">
                                                            <i class="fa fa-check mr-1"></i>OK
                                                        </span>
                                                    @else
                                                        <span style="background:#f3e5f5; color:#6a1b9a; border-radius:8px;
                                                                     padding:3px 10px; font-size:11px;">
                                                            <i class="fa fa-info-circle mr-1"></i>Sin control
                                                        </span>
                                                    @endif
                                                </td>
                                                <td style="padding:6px 14px;">
                                                    <input type="text"
                                                           wire:model.lazy="obsProducto.{{ $prod['idx'] }}"
                                                           placeholder="{{ $prod['falta_stock'] ? 'Ej: reemplazar con Producto X...' : 'Observación opcional...' }}"
                                                           class="form-control form-control-sm"
                                                           {{ ($devuelto || $soloVisualizacion) ? 'readonly' : '' }}
                                                           style="font-size:12px; border-radius:6px;
                                                                  border-color: {{ $prod['falta_stock'] ? '#f9a825' : '#ddd' }};
                                                                  {{ ($devuelto || $soloVisualizacion) ? 'background:#f8f8f8; cursor:default;' : '' }}">
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>

                        {{-- Panel de acciones --}}
                        @if ($soloVisualizacion)
                        <div style="background:#e3f2fd; border:1px solid #90caf9; border-radius:12px; padding:16px 20px;">
                            <div style="display:flex; align-items:flex-start; gap:14px;">
                                <i class="fa fa-eye" style="color:#1565c0; font-size:22px; flex-shrink:0; margin-top:2px;"></i>
                                <div>
                                    <strong style="color:#1565c0; font-size:13px;">
                                        Vista de Prefactura en modo solo lectura.
                                    </strong>
                                    <div style="color:#607d8b; font-size:12px; margin-top:3px;">
                                        Este detalle se muestra solo para consulta. No se permite editar notas ni ejecutar acciones.
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif ($devuelto)
                        <div style="background:#fff3e0; border:1px solid #ffd54f; border-radius:12px; padding:16px 20px;">
                            <div style="display:flex; align-items:flex-start; gap:14px; margin-bottom:{{ $motivoDevolucionGuardado ? '14px' : '0' }};">
                                <i class="fa fa-undo" style="color:#e65100; font-size:22px; flex-shrink:0; margin-top:2px;"></i>
                                <div>
                                    <strong style="color:#e65100; font-size:13px;">
                                        Revisión completada — Flujo devuelto a Oferta.
                                    </strong>
                                    <div style="color:#888; font-size:12px; margin-top:3px;">
                                        Las observaciones y notas de reemplazo quedaron registradas en el historial.
                                        No se puede editar, devolver ni pasar a Prefactura.
                                    </div>
                                </div>
                            </div>
                            @if ($motivoDevolucionGuardado)
                            <div style="background:#fff8e1; border:1px solid #ffe082; border-radius:8px; padding:10px 14px;">
                                <div style="font-size:11px; font-weight:700; color:#f57f17; text-transform:uppercase;
                                            letter-spacing:.5px; margin-bottom:6px;">
                                    <i class="fa fa-comment mr-1"></i>Motivo de devolución
                                </div>
                                <div style="font-size:13px; color:#4e342e; line-height:1.6; white-space:pre-wrap;">{{ $motivoDevolucionGuardado }}</div>
                            </div>
                            @endif
                        </div>
                        @elseif ($confirmAccion === null)
                        @php
                            $puedePasarPrefactura = count($stockErrors) === 0
                                && count($productos) > 0
                                && collect($obsProducto)->filter()->isEmpty()
                                && $this->todosProductosRevisados()
                                && ! $this->tieneProductosSinExistencia();

                            $puedeDevolverOferta = $this->todosProductosRevisados();

                            $motivoBloqueoPrefactura = ! $this->todosProductosRevisados()
                                ? 'Debe marcar todos los productos como revisados'
                                : (collect($obsProducto)->filter()->isNotEmpty()
                                    ? 'Elimine las notas/reemplazos antes de pasar a Prefactura'
                                    : ($this->tieneProductosSinExistencia()
                                        ? 'Hay productos marcados como sin existencia'
                                        : 'Hay productos sin stock suficiente'));
                        @endphp
                        <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                            {{-- Pasar a Prefactura --}}
                            <button type="button"
                                    wire:key="btn-prefactura-{{ $flujoId }}-{{ $puedePasarPrefactura ? 'on' : 'off' }}"
                                    wire:click.prevent="confirmarPrefactura"
                                    wire:loading.attr="disabled"
                                    wire:target="confirmarPrefactura"
                                    {{ $puedePasarPrefactura ? '' : 'disabled' }}
                                    title="{{ $puedePasarPrefactura ? '' : $motivoBloqueoPrefactura }}"
                                    style="background:{{ $puedePasarPrefactura ? 'linear-gradient(135deg,#1ab394,#0fa37a)' : '#ccc' }};
                                           color:#fff; border:none; border-radius:10px; padding:9px 22px;
                                           font-size:13px; font-weight:700;
                                           cursor:{{ $puedePasarPrefactura ? 'pointer' : 'not-allowed' }};
                                           box-shadow:{{ $puedePasarPrefactura ? '0 3px 10px rgba(26,179,148,.4)' : 'none' }};">
                                <i class="mr-1 fa fa-{{ $puedePasarPrefactura ? 'file-o' : 'ban' }}"></i> Pasar a Prefactura
                            </button>

                            {{-- Devolver a Oferta --}}
                                <button type="button"
                                    wire:key="btn-devolver-{{ $flujoId }}-{{ $puedeDevolverOferta ? 'on' : 'off' }}"
                                    wire:click.prevent="confirmarDevolucion"
                                    wire:loading.attr="disabled"
                                    wire:target="confirmarDevolucion"
                                    {{ $puedeDevolverOferta ? '' : 'disabled' }}
                                    style="background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff;
                                           border:none; border-radius:10px; padding:9px 22px;
                                       font-size:13px; font-weight:700; cursor:{{ $puedeDevolverOferta ? 'pointer' : 'not-allowed' }};
                                           box-shadow:0 3px 10px rgba(231,76,60,.35);">
                                <i class="mr-1 fa fa-reply"></i> Devolver a Oferta
                            </button>
                        </div>

                        {{-- Confirm: Pasar a Prefactura --}}
                        @elseif ($confirmAccion === 'prefactura')
                        <div style="background:#e8f5e9; border:1px solid #a5d6a7; border-radius:12px; padding:16px;">
                            <p style="font-size:14px; color:#2e7d32; font-weight:700; margin-bottom:10px;">
                                <i class="mr-1 fa fa-check-circle"></i>
                                ¿Confirmar el paso a Prefactura para el Flujo #{{ $flujoId }}?
                            </p>
                            <p style="font-size:12px; color:#555; margin-bottom:14px;">
                                Se creará la prefactura basada en la oferta ganadora y el flujo avanzará al siguiente paso.
                            </p>
                            @if ($mensajeError)
                            <div style="background:#fce4ec; border-radius:8px; padding:7px 12px;
                                        font-size:12px; color:#b71c1c; margin-bottom:10px;">
                                <i class="mr-1 fa fa-exclamation-triangle"></i>{{ $mensajeError }}
                            </div>
                            @endif
                            <div style="display:flex; gap:10px;">
                                <button type="button" wire:click="pasarAPrefactura"
                                        wire:loading.attr="disabled"
                                        style="background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff;
                                               border:none; border-radius:8px; padding:8px 20px;
                                               font-size:13px; font-weight:700; cursor:pointer;">
                                    <span wire:loading.remove wire:target="pasarAPrefactura">
                                        <i class="mr-1 fa fa-check"></i> Confirmar
                                    </span>
                                    <span wire:loading wire:target="pasarAPrefactura">
                                        <i class="fa fa-spinner fa-spin mr-1"></i> Procesando...
                                    </span>
                                </button>
                                <button type="button" wire:click="cancelarAccion"
                                        style="background:#f0f0f0; color:#555; border:none;
                                               border-radius:8px; padding:8px 20px; font-size:13px; cursor:pointer;">
                                    Cancelar
                                </button>
                            </div>
                        </div>

                        {{-- Confirm: Devolver a Oferta --}}
                        @elseif ($confirmAccion === 'devolver')
                        <div style="background:#fff3e0; border:1px solid #ffcc80; border-radius:12px; padding:16px;">
                            <p style="font-size:14px; color:#e65100; font-weight:700; margin-bottom:10px;">
                                <i class="mr-1 fa fa-reply"></i>
                                Devolver a Oferta — Flujo #{{ $flujoId }}
                            </p>
                            <p style="font-size:12px; color:#555; margin-bottom:10px;">
                                El flujo regresará al estado de Oferta. El vendedor podrá revisar y crear una nueva oferta con las correcciones indicadas.
                                Las notas por producto también se incluirán en el registro.
                            </p>
                            @if ($mensajeError)
                            <div style="background:#fce4ec; border-radius:8px; padding:7px 12px;
                                        font-size:12px; color:#b71c1c; margin-bottom:10px;">
                                <i class="mr-1 fa fa-exclamation-triangle"></i>{{ $mensajeError }}
                            </div>
                            @endif
                            <textarea wire:model.defer="motivoDevolucion"
                                      rows="3"
                                      placeholder="Motivo de devolución a Oferta (obligatorio)…"
                                      class="form-control"
                                      style="border-radius:8px; font-size:13px; margin-bottom:12px; resize:none;
                                             border-color:#f9a825;"></textarea>
                            <div style="display:flex; gap:10px;">
                                <button type="button" wire:click="devolverAOferta"
                                        wire:loading.attr="disabled"
                                        style="background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff;
                                               border:none; border-radius:8px; padding:8px 20px;
                                               font-size:13px; font-weight:700; cursor:pointer;">
                                    <span wire:loading.remove wire:target="devolverAOferta">
                                        <i class="mr-1 fa fa-reply"></i> Confirmar devolución
                                    </span>
                                    <span wire:loading wire:target="devolverAOferta">
                                        <i class="fa fa-spinner fa-spin mr-1"></i> Procesando...
                                    </span>
                                </button>
                                <button type="button" wire:click="cancelarAccion"
                                        style="background:#f0f0f0; color:#555; border:none;
                                               border-radius:8px; padding:8px 20px; font-size:13px; cursor:pointer;">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                        @endif

                    </div>{{-- /ibox-content detalle --}}
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- BANDEJA (cuando no hay flujo seleccionado)                    --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @else

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox" style="border-radius:14px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.07);">

                    {{-- Cabecera con título, búsqueda y pestañas --}}
                    <div class="ibox-title" style="background:linear-gradient(135deg,#1a7efb,#0d6efd); border:none; padding:0;">

                        {{-- Fila superior: título + búsqueda + refresh --}}
                        <div style="display:flex; align-items:center; justify-content:space-between;
                                    padding:12px 20px; border-bottom:1px solid rgba(255,255,255,.15);">
                            <h5 style="color:#fff; margin:0; font-weight:700;">
                                <i class="mr-2 fa fa-list-alt"></i>Bandeja — Revisión de Inventario
                            </h5>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <div class="input-group input-group-sm" style="width:240px;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"
                                              style="background:rgba(255,255,255,.15); border:none; color:#fff;">
                                            <i class="fa fa-search"></i>
                                        </span>
                                    </div>
                                    <input type="text" wire:model.debounce.400ms="busqueda"
                                           class="form-control form-control-sm"
                                           placeholder="Buscar flujo o cliente..."
                                           style="background:rgba(255,255,255,.15); border:none; color:#fff;
                                                  border-radius:0 6px 6px 0;"
                                           autocomplete="off">
                                </div>
                                <button type="button" wire:click="cargar"
                                        style="background:rgba(255,255,255,.2); color:#fff; border:none;
                                               border-radius:8px; padding:5px 12px; font-size:12px; cursor:pointer;">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Pestañas --}}
                        <div style="display:flex;">
                            @foreach ([
                                'llegando'   => ['label' => 'Llegando',        'icon' => 'fa-inbox',       'count' => $totalLlegando],
                                'devueltos'  => ['label' => 'Devueltos',       'icon' => 'fa-reply',       'count' => $totalDevueltos],
                                'prefactura' => ['label' => 'En Prefactura',   'icon' => 'fa-file-text-o', 'count' => $totalPrefactura],
                            ] as $tabKey => $tabInfo)
                            <button type="button" wire:click="cambiarTab('{{ $tabKey }}')"
                                    style="flex:1; padding:10px 14px; border:none; cursor:pointer; font-size:13px;
                                           font-weight:700; transition:background .15s;
                                           background:{{ $tabActiva === $tabKey ? 'rgba(255,255,255,.18)' : 'transparent' }};
                                           color:{{ $tabActiva === $tabKey ? '#fff' : 'rgba(255,255,255,.6)' }};
                                           border-bottom:{{ $tabActiva === $tabKey ? '3px solid #fff' : '3px solid transparent' }};">
                                <i class="fa {{ $tabInfo['icon'] }} mr-1"></i>{{ $tabInfo['label'] }}
                                <span style="background:rgba(255,255,255,{{ $tabActiva === $tabKey ? '.25' : '.12' }});
                                             color:#fff; border-radius:20px; padding:1px 8px;
                                             font-size:11px; margin-left:4px;">
                                    {{ $tabInfo['count'] }}
                                </span>
                            </button>
                            @endforeach
                        </div>
                    </div>{{-- /ibox-title --}}

                    <div class="ibox-content" style="padding:0;">

                        {{-- ══ Pestaña: Llegando ══ --}}
                        @if ($tabActiva === 'llegando')
                        @if ($totalLlegando === 0)
                        <div style="padding:40px; text-align:center; color:#aaa;">
                            <i class="fa fa-inbox d-block" style="font-size:40px; margin-bottom:12px; opacity:.3;"></i>
                            @if ($configuracionActiva)
                                <p style="font-size:14px; margin:0;">No hay ofertas pendientes de revisión.</p>
                                <p style="font-size:12px; color:#bbb; margin-top:4px;">
                                    Cuando una oferta sea seleccionada como ganadora, aparecerá aquí.
                                </p>
                            @else
                                <p style="font-size:14px; margin:0; color:#e67e22; font-weight:600;">
                                    <i class="fa fa-toggle-off mr-1"></i>La revisión de inventario está desactivada.
                                </p>
                                <p style="font-size:12px; color:#bbb; margin-top:4px;">
                                    Actívela para que las ofertas ganadoras pasen por este paso antes de Prefactura.
                                </p>
                            @endif
                        </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-hover" style="font-size:13px; margin:0;">
                                <thead style="background:#f8f9fc;">
                                    <tr>
                                        <th style="padding:10px 16px; color:#555; font-weight:700;">Flujo</th>
                                        <th style="padding:10px 16px; color:#555; font-weight:700;">Cliente</th>
                                        <th style="padding:10px 16px; text-align:center; color:#555; font-weight:700;">Oferta</th>
                                        <th style="padding:10px 16px; text-align:center; color:#555; font-weight:700;">Productos</th>
                                        <th style="padding:10px 16px; color:#555; font-weight:700;">Ingresó</th>
                                        <th style="padding:10px 16px; text-align:center; color:#555; font-weight:700;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bandejaRegistros as $reg)
                                    <tr style="border-bottom:1px solid #f0f0f0;">
                                        <td style="padding:10px 16px;">
                                            <span style="background:#e3f2fd; color:#1565c0; border-radius:8px;
                                                         padding:3px 10px; font-weight:700; font-size:12px;">
                                                #{{ $reg['flujo_id'] }}
                                            </span>
                                        </td>
                                        <td style="padding:10px 16px; color:#2c3e50; font-weight:600;">
                                            {{ $reg['cliente'] }}
                                            @if(!empty($reg['rtn']))
                                            <div style="font-size:11px; color:#888;">RTN: {{ $reg['rtn'] }}</div>
                                            @endif
                                        </td>
                                        <td style="padding:10px 16px; text-align:center;">
                                            @if($reg['cotizacion_id'])
                                            <span style="background:#fff3e0; color:#e65100; border-radius:8px;
                                                         padding:3px 10px; font-weight:700; font-size:12px;">
                                                <i class="fa fa-trophy mr-1"></i>#{{ $reg['cotizacion_id'] }}
                                            </span>
                                            @else
                                            <span style="color:#aaa; font-size:11px;">—</span>
                                            @endif
                                        </td>
                                        <td style="padding:10px 16px; text-align:center;">
                                            <span style="background:#f3e5f5; color:#6a1b9a; border-radius:8px;
                                                         padding:3px 10px; font-weight:700; font-size:12px;">
                                                {{ $reg['total_productos'] }}
                                            </span>
                                        </td>
                                        <td style="padding:10px 16px; font-size:12px; color:#666;">
                                            {{ \Carbon\Carbon::parse($reg['fecha_revision'])->format('d/m/Y H:i') }}
                                        </td>
                                        <td style="padding:10px 16px; text-align:center;">
                                            <button type="button"
                                                    wire:click="seleccionarFlujo({{ $reg['flujo_id'] }})"
                                                    style="background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                                           border:none; border-radius:8px; padding:5px 14px;
                                                           font-size:12px; font-weight:700; cursor:pointer;">
                                                <i class="mr-1 fa fa-eye"></i> Revisar
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- Paginación Llegando --}}
                        @if ($totalLlegando > $porPagina)
                        @php $totalPagsL = (int) ceil($totalLlegando / $porPagina); @endphp
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 16px; border-top:1px solid #f0f0f0; background:#f8f9fc;">
                            <span style="font-size:12px; color:#64748b;">
                                Mostrando {{ ($paginaLlegando-1)*$porPagina+1 }}–{{ min($paginaLlegando*$porPagina, $totalLlegando) }} de {{ $totalLlegando }}
                            </span>
                            <div style="display:flex; gap:4px; align-items:center;">
                                <button wire:click="cambiarPagina('llegando', {{ max(1,$paginaLlegando-1) }})" {{ $paginaLlegando<=1 ? 'disabled' : '' }}
                                        style="border:1px solid #e2e8f0; background:#fff; color:#374151; border-radius:6px; padding:4px 10px; font-size:13px; cursor:pointer; {{ $paginaLlegando<=1 ? 'opacity:.4;cursor:not-allowed;' : '' }}">&#8249;</button>
                                @for ($pg = max(1,$paginaLlegando-2); $pg <= min($totalPagsL,$paginaLlegando+2); $pg++)
                                <button wire:click="cambiarPagina('llegando', {{ $pg }})"
                                        style="border:1px solid {{ $paginaLlegando===$pg ? '#1a7efb' : '#e2e8f0' }}; background:{{ $paginaLlegando===$pg ? '#1a7efb' : '#fff' }}; color:{{ $paginaLlegando===$pg ? '#fff' : '#374151' }}; border-radius:6px; padding:4px 10px; font-size:12px; font-weight:{{ $paginaLlegando===$pg ? '700' : '400' }}; cursor:pointer;">
                                    {{ $pg }}
                                </button>
                                @endfor
                                <button wire:click="cambiarPagina('llegando', {{ min($totalPagsL,$paginaLlegando+1) }})" {{ $paginaLlegando>=$totalPagsL ? 'disabled' : '' }}
                                        style="border:1px solid #e2e8f0; background:#fff; color:#374151; border-radius:6px; padding:4px 10px; font-size:13px; cursor:pointer; {{ $paginaLlegando>=$totalPagsL ? 'opacity:.4;cursor:not-allowed;' : '' }}">&#8250;</button>
                            </div>
                        </div>
                        @endif
                        @endif

                        {{-- ══ Pestaña: Devueltos ══ --}}
                        @elseif ($tabActiva === 'devueltos')
                        @if ($totalDevueltos === 0)
                        <div style="padding:40px; text-align:center; color:#aaa;">
                            <i class="fa fa-reply d-block" style="font-size:40px; margin-bottom:12px; opacity:.3;"></i>
                            <p style="font-size:14px; margin:0;">No hay flujos devueltos a Oferta todavía.</p>
                        </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-hover" style="font-size:13px; margin:0;">
                                <thead style="background:#fff8f0;">
                                    <tr>
                                        <th style="padding:10px 16px; color:#555; font-weight:700;">Flujo</th>
                                        <th style="padding:10px 16px; color:#555; font-weight:700;">Cliente</th>
                                        <th style="padding:10px 16px; text-align:center; color:#555; font-weight:700;">Oferta</th>
                                        <th style="padding:10px 16px; text-align:center; color:#555; font-weight:700;">Productos</th>
                                        <th style="padding:10px 16px; color:#555; font-weight:700;">Devuelto el</th>
                                        <th style="padding:10px 16px; color:#555; font-weight:700;">Motivo</th>
                                        <th style="padding:10px 16px; text-align:center; color:#555; font-weight:700;">Ver</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bandejaDevueltos as $reg)
                                    @php
                                        $motRaw = $reg['obs_revision'] ?? '';
                                        $mot    = preg_replace('/^Devuelto a Oferta\s*:\s*/i', '', $motRaw);
                                        if (str_contains($mot, ' | [')) {
                                            $mot = trim(explode(' | [', $mot)[0]);
                                        }
                                        $fechaDev = !empty($reg['fecha_accion'])
                                            ? \Carbon\Carbon::parse($reg['fecha_accion'])->format('d/m/Y H:i')
                                            : \Carbon\Carbon::parse($reg['fecha_revision'])->format('d/m/Y H:i');
                                    @endphp
                                    <tr style="border-bottom:1px solid #f0f0f0; background:#fffbf5;">
                                        <td style="padding:10px 16px;">
                                            <span style="background:#e3f2fd; color:#1565c0; border-radius:8px;
                                                         padding:3px 10px; font-weight:700; font-size:12px;">
                                                #{{ $reg['flujo_id'] }}
                                            </span>
                                            <span style="background:#fff3e0; color:#e65100; border-radius:8px;
                                                         padding:2px 8px; font-size:10px; font-weight:700; margin-left:4px;">
                                                <i class="fa fa-reply mr-1"></i>Devuelto
                                            </span>
                                        </td>
                                        <td style="padding:10px 16px; color:#2c3e50; font-weight:600;">
                                            {{ $reg['cliente'] }}
                                            @if(!empty($reg['rtn']))
                                            <div style="font-size:11px; color:#888;">RTN: {{ $reg['rtn'] }}</div>
                                            @endif
                                        </td>
                                        <td style="padding:10px 16px; text-align:center;">
                                            @if($reg['cotizacion_id'])
                                            <span style="background:#fff3e0; color:#e65100; border-radius:8px;
                                                         padding:3px 10px; font-weight:700; font-size:12px;">
                                                <i class="fa fa-trophy mr-1"></i>#{{ $reg['cotizacion_id'] }}
                                            </span>
                                            @else
                                            <span style="color:#aaa; font-size:11px;">—</span>
                                            @endif
                                        </td>
                                        <td style="padding:10px 16px; text-align:center;">
                                            <span style="background:#f3e5f5; color:#6a1b9a; border-radius:8px;
                                                         padding:3px 10px; font-weight:700; font-size:12px;">
                                                {{ $reg['total_productos'] }}
                                            </span>
                                        </td>
                                        <td style="padding:10px 16px; font-size:12px; color:#666;">
                                            {{ $fechaDev }}
                                        </td>
                                        <td style="padding:10px 16px; font-size:12px; color:#555; max-width:200px;">
                                            @if($mot)
                                            <span style="display:-webkit-box; -webkit-line-clamp:2;
                                                         -webkit-box-orient:vertical; overflow:hidden;">
                                                {{ $mot }}
                                            </span>
                                            @else
                                            <span style="color:#aaa;">—</span>
                                            @endif
                                        </td>
                                        <td style="padding:10px 16px; text-align:center;">
                                            <button type="button"
                                                    wire:click="seleccionarFlujo({{ $reg['flujo_id'] }})"
                                                    style="background:linear-gradient(135deg,#e67e22,#d35400); color:#fff;
                                                           border:none; border-radius:8px; padding:5px 14px;
                                                           font-size:12px; font-weight:700; cursor:pointer;">
                                                <i class="mr-1 fa fa-eye"></i> Ver
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- Paginación Devueltos --}}
                        @if ($totalDevueltos > $porPagina)
                        @php $totalPagsD = (int) ceil($totalDevueltos / $porPagina); @endphp
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 16px; border-top:1px solid #f0f0f0; background:#f8f9fc;">
                            <span style="font-size:12px; color:#64748b;">
                                Mostrando {{ ($paginaDevueltos-1)*$porPagina+1 }}–{{ min($paginaDevueltos*$porPagina, $totalDevueltos) }} de {{ $totalDevueltos }}
                            </span>
                            <div style="display:flex; gap:4px; align-items:center;">
                                <button wire:click="cambiarPagina('devueltos', {{ max(1,$paginaDevueltos-1) }})" {{ $paginaDevueltos<=1 ? 'disabled' : '' }}
                                        style="border:1px solid #e2e8f0; background:#fff; color:#374151; border-radius:6px; padding:4px 10px; font-size:13px; cursor:pointer; {{ $paginaDevueltos<=1 ? 'opacity:.4;cursor:not-allowed;' : '' }}">&#8249;</button>
                                @for ($pg = max(1,$paginaDevueltos-2); $pg <= min($totalPagsD,$paginaDevueltos+2); $pg++)
                                <button wire:click="cambiarPagina('devueltos', {{ $pg }})"
                                        style="border:1px solid {{ $paginaDevueltos===$pg ? '#e67e22' : '#e2e8f0' }}; background:{{ $paginaDevueltos===$pg ? '#e67e22' : '#fff' }}; color:{{ $paginaDevueltos===$pg ? '#fff' : '#374151' }}; border-radius:6px; padding:4px 10px; font-size:12px; font-weight:{{ $paginaDevueltos===$pg ? '700' : '400' }}; cursor:pointer;">
                                    {{ $pg }}
                                </button>
                                @endfor
                                <button wire:click="cambiarPagina('devueltos', {{ min($totalPagsD,$paginaDevueltos+1) }})" {{ $paginaDevueltos>=$totalPagsD ? 'disabled' : '' }}
                                        style="border:1px solid #e2e8f0; background:#fff; color:#374151; border-radius:6px; padding:4px 10px; font-size:13px; cursor:pointer; {{ $paginaDevueltos>=$totalPagsD ? 'opacity:.4;cursor:not-allowed;' : '' }}">&#8250;</button>
                            </div>
                        </div>
                        @endif
                        @endif

                        {{-- ══ Pestaña: En Prefactura ══ --}}
                        @else
                        @if ($totalPrefactura === 0)
                        <div style="padding:40px; text-align:center; color:#aaa;">
                            <i class="fa fa-file-text-o d-block" style="font-size:40px; margin-bottom:12px; opacity:.3;"></i>
                            <p style="font-size:14px; margin:0;">Ningún flujo ha sido aprobado a Prefactura aún.</p>
                        </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-hover" style="font-size:13px; margin:0;">
                                <thead style="background:#f1f8f4;">
                                    <tr>
                                        <th style="padding:10px 16px; color:#555; font-weight:700;">Flujo</th>
                                        <th style="padding:10px 16px; color:#555; font-weight:700;">Cliente</th>
                                        <th style="padding:10px 16px; text-align:center; color:#555; font-weight:700;">Oferta</th>
                                        <th style="padding:10px 16px; text-align:center; color:#555; font-weight:700;">Productos</th>
                                        <th style="padding:10px 16px; color:#555; font-weight:700;">Aprobado el</th>
                                        <th style="padding:10px 16px; color:#555; font-weight:700;">Prefactura generada</th>
                                        <th style="padding:10px 16px; text-align:center; color:#555; font-weight:700;">Ver</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bandejaPrefactura as $reg)
                                    @php
                                        $fechaApr = !empty($reg['fecha_accion'])
                                            ? \Carbon\Carbon::parse($reg['fecha_accion'])->format('d/m/Y H:i')
                                            : \Carbon\Carbon::parse($reg['fecha_revision'])->format('d/m/Y H:i');
                                        // Extract prefactura # from obs like "Revisión aprobada. Prefactura #X creada."
                                        preg_match('/Prefactura #(\d+)/i', $reg['obs_revision'] ?? '', $prefMatch);
                                        $prefNum = $prefMatch[1] ?? null;
                                    @endphp
                                    <tr style="border-bottom:1px solid #f0f0f0; background:#f9fdf9;">
                                        <td style="padding:10px 16px;">
                                            <span style="background:#e3f2fd; color:#1565c0; border-radius:8px;
                                                         padding:3px 10px; font-weight:700; font-size:12px;">
                                                #{{ $reg['flujo_id'] }}
                                            </span>
                                            <span style="background:#e8f5e9; color:#2e7d32; border-radius:8px;
                                                         padding:2px 8px; font-size:10px; font-weight:700; margin-left:4px;">
                                                <i class="fa fa-check mr-1"></i>Aprobado
                                            </span>
                                        </td>
                                        <td style="padding:10px 16px; color:#2c3e50; font-weight:600;">
                                            {{ $reg['cliente'] }}
                                            @if(!empty($reg['rtn']))
                                            <div style="font-size:11px; color:#888;">RTN: {{ $reg['rtn'] }}</div>
                                            @endif
                                        </td>
                                        <td style="padding:10px 16px; text-align:center;">
                                            @if($reg['cotizacion_id'])
                                            <span style="background:#fff3e0; color:#e65100; border-radius:8px;
                                                         padding:3px 10px; font-weight:700; font-size:12px;">
                                                <i class="fa fa-trophy mr-1"></i>#{{ $reg['cotizacion_id'] }}
                                            </span>
                                            @else
                                            <span style="color:#aaa; font-size:11px;">—</span>
                                            @endif
                                        </td>
                                        <td style="padding:10px 16px; text-align:center;">
                                            <span style="background:#f3e5f5; color:#6a1b9a; border-radius:8px;
                                                         padding:3px 10px; font-weight:700; font-size:12px;">
                                                {{ $reg['total_productos'] }}
                                            </span>
                                        </td>
                                        <td style="padding:10px 16px; font-size:12px; color:#666;">
                                            {{ $fechaApr }}
                                        </td>
                                        <td style="padding:10px 16px;">
                                            @if($prefNum)
                                            <span style="background:#e8f5e9; color:#2e7d32; border-radius:8px;
                                                         padding:3px 10px; font-weight:700; font-size:12px;">
                                                <i class="fa fa-file-text-o mr-1"></i>Prefactura #{{ $prefNum }}
                                            </span>
                                            @else
                                            <span style="font-size:12px; color:#555;">{{ $reg['obs_revision'] ?? '—' }}</span>
                                            @endif
                                        </td>
                                        <td style="padding:10px 16px; text-align:center;">
                                            <button type="button"
                                                    wire:click="seleccionarFlujo({{ $reg['flujo_id'] }}, true)"
                                                    style="background:linear-gradient(135deg,#2e7d32,#1b5e20); color:#fff;
                                                           border:none; border-radius:8px; padding:5px 14px;
                                                           font-size:12px; font-weight:700; cursor:pointer;">
                                                <i class="mr-1 fa fa-eye"></i> Ver
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- Paginación Prefactura --}}
                        @if ($totalPrefactura > $porPagina)
                        @php $totalPagsP = (int) ceil($totalPrefactura / $porPagina); @endphp
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 16px; border-top:1px solid #f0f0f0; background:#f8f9fc;">
                            <span style="font-size:12px; color:#64748b;">
                                Mostrando {{ ($paginaPrefactura-1)*$porPagina+1 }}–{{ min($paginaPrefactura*$porPagina, $totalPrefactura) }} de {{ $totalPrefactura }}
                            </span>
                            <div style="display:flex; gap:4px; align-items:center;">
                                <button wire:click="cambiarPagina('prefactura', {{ max(1,$paginaPrefactura-1) }})" {{ $paginaPrefactura<=1 ? 'disabled' : '' }}
                                        style="border:1px solid #e2e8f0; background:#fff; color:#374151; border-radius:6px; padding:4px 10px; font-size:13px; cursor:pointer; {{ $paginaPrefactura<=1 ? 'opacity:.4;cursor:not-allowed;' : '' }}">&#8249;</button>
                                @for ($pg = max(1,$paginaPrefactura-2); $pg <= min($totalPagsP,$paginaPrefactura+2); $pg++)
                                <button wire:click="cambiarPagina('prefactura', {{ $pg }})"
                                        style="border:1px solid {{ $paginaPrefactura===$pg ? '#2e7d32' : '#e2e8f0' }}; background:{{ $paginaPrefactura===$pg ? '#2e7d32' : '#fff' }}; color:{{ $paginaPrefactura===$pg ? '#fff' : '#374151' }}; border-radius:6px; padding:4px 10px; font-size:12px; font-weight:{{ $paginaPrefactura===$pg ? '700' : '400' }}; cursor:pointer;">
                                    {{ $pg }}
                                </button>
                                @endfor
                                <button wire:click="cambiarPagina('prefactura', {{ min($totalPagsP,$paginaPrefactura+1) }})" {{ $paginaPrefactura>=$totalPagsP ? 'disabled' : '' }}
                                        style="border:1px solid #e2e8f0; background:#fff; color:#374151; border-radius:6px; padding:4px 10px; font-size:13px; cursor:pointer; {{ $paginaPrefactura>=$totalPagsP ? 'opacity:.4;cursor:not-allowed;' : '' }}">&#8250;</button>
                            </div>
                        </div>
                        @endif
                        @endif

                        @endif {{-- /tabActiva --}}

                    </div>{{-- /ibox-content --}}
                </div>
            </div>
        </div>

        @endif {{-- /flujoId --}}

    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Reservas del producto (JS puro, sin re-render Livewire)   --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div wire:ignore>
        <div class="modal fade" id="modal_inv_reservas" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content" style="border-radius:14px; overflow:hidden; border:none;">
                    <div class="modal-header"
                         style="background:linear-gradient(135deg,#e65100,#bf360c); padding:14px 20px; border:none;">
                        <div>
                            <h5 class="modal-title"
                                style="color:#fff; font-weight:700; font-size:14px; margin:0;">
                                <i class="fa fa-lock mr-2"></i>Reservas activas del producto
                            </h5>
                            <small id="invres_nombre"
                                   style="color:rgba(255,255,255,.85); font-size:11px; display:block; margin-top:2px;"></small>
                        </div>
                        <button type="button" class="close" data-dismiss="modal"
                                style="color:#fff; opacity:1; text-shadow:none; font-size:22px; margin-left:16px;">
                            &times;
                        </button>
                    </div>
                    <div class="modal-body" style="padding:18px 20px;">
                        <p id="invres_info" style="font-size:12px; color:#607d8b; margin-bottom:12px;"></p>
                        <div id="invres_empty"
                             style="display:none; text-align:center; color:#aaa; padding:20px 0;">
                            <i class="fa fa-inbox d-block"
                               style="font-size:32px; margin-bottom:8px; opacity:.35;"></i>
                            No hay prefacturas activas reservando este producto.
                        </div>
                        <div id="invres_table_wrap" class="table-responsive">
                            <table class="table table-sm table-hover mb-0" style="font-size:13px;">
                                <thead style="background:#f8f9fc;">
                                    <tr>
                                        <th style="padding:8px 12px; color:#555; font-weight:700;">Prefactura</th>
                                        <th style="padding:8px 12px; color:#555; font-weight:700;">Flujo</th>
                                        <th style="padding:8px 12px; color:#555; font-weight:700;">Cliente</th>
                                        <th style="padding:8px 12px; text-align:center; color:#e65100; font-weight:700;">Cant. Reservada</th>
                                        <th style="padding:8px 12px; color:#555; font-weight:700;">Emisión</th>
                                        <th style="padding:8px 12px; color:#555; font-weight:700;">Vence</th>
                                    </tr>
                                </thead>
                                <tbody id="invres_tbody"></tbody>
                                <tfoot>
                                    <tr style="background:#fff8f0;">
                                        <td colspan="3"
                                            style="padding:8px 12px; font-weight:700; color:#e65100; text-align:right;">
                                            Total reservado:
                                        </td>
                                        <td style="padding:8px 12px; text-align:center;">
                                            <span id="invres_total"
                                                  style="background:#e65100; color:#fff; border-radius:10px;
                                                         padding:3px 12px; font-weight:700; font-size:13px;"></span>
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer" style="border:none; padding:10px 20px;">
                        <button type="button" class="btn btn-default" data-dismiss="modal"
                                style="border-radius:8px;">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function abrirModalReservasInv(btn) {
            var nombre   = btn.getAttribute('data-nombre') || '';
            var reservas = JSON.parse(btn.getAttribute('data-reservas') || '[]');

            document.getElementById('invres_nombre').textContent = nombre;

            var tbody = document.getElementById('invres_tbody');
            tbody.innerHTML = '';
            var total = 0;

            if (reservas.length === 0) {
                document.getElementById('invres_empty').style.display = 'block';
                document.getElementById('invres_table_wrap').style.display = 'none';
                document.getElementById('invres_info').textContent = '';
            } else {
                document.getElementById('invres_empty').style.display = 'none';
                document.getElementById('invres_table_wrap').style.display = 'block';
                document.getElementById('invres_info').textContent =
                    reservas.length + ' prefactura(s) activa(s) con reserva en esta bodega/sección.';

                reservas.forEach(function(r) {
                    var cant = parseFloat(r.cantidad) || 0;
                    total += cant;
                    var tr = document.createElement('tr');
                    tr.style.borderBottom = '1px solid #f0f0f0';
                    tr.innerHTML =
                        '<td style="padding:8px 12px;">' +
                            '<span style="background:#e3f2fd;color:#1565c0;border-radius:8px;padding:2px 9px;font-weight:700;font-size:12px;">#' + r.prefactura_id + '</span>' +
                        '</td>' +
                        '<td style="padding:8px 12px;">' +
                            (r.flujo_id
                                ? '<span style="background:#f3e5f5;color:#6a1b9a;border-radius:8px;padding:2px 9px;font-weight:700;font-size:12px;">#' + r.flujo_id + '</span>'
                                : '<span style="color:#aaa;">—</span>') +
                        '</td>' +
                        '<td style="padding:8px 12px;color:#2c3e50;font-size:12px;">' + (r.nombre_cliente || '—') + '</td>' +
                        '<td style="padding:8px 12px;text-align:center;">' +
                            '<span style="background:#fff3e0;color:#e65100;border-radius:10px;padding:2px 10px;font-weight:700;font-size:13px;">' + Math.round(cant) + '</span>' +
                        '</td>' +
                        '<td style="padding:8px 12px;font-size:12px;color:#555;">' +
                            (r.fecha_emision ? r.fecha_emision.substring(0,10).split('-').reverse().join('/') : '—') +
                        '</td>' +
                        '<td style="padding:8px 12px;font-size:12px;color:#555;">' +
                            (r.fecha_vencimiento ? r.fecha_vencimiento.substring(0,10).split('-').reverse().join('/') : '—') +
                        '</td>';
                    tbody.appendChild(tr);
                });
            }

            document.getElementById('invres_total').textContent = Math.round(total);
            $('#modal_inv_reservas').modal('show');
        }
        </script>
    </div>

</div>

