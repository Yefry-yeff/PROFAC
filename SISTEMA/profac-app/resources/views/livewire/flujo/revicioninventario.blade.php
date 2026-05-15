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
        {{-- MÓDULO DE CONFIGURACIÓN                                       --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox" style="border-radius:14px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.07);">
                    <div class="ibox-title d-flex align-items-center justify-content-between"
                         style="background:linear-gradient(135deg,#34495e,#2c3e50); border:none; padding:12px 20px;">
                        <h5 style="color:#fff; margin:0; font-weight:700; font-size:14px;">
                            <i class="mr-2 fa fa-cogs"></i>Configuración del flujo
                        </h5>
                        <span style="background:rgba(255,255,255,.15); color:#ecf0f1; border-radius:20px;
                                     padding:3px 12px; font-size:11px; font-weight:600;">
                            {{ $configuracionActiva ? 'ACTIVO' : 'INACTIVO' }}
                        </span>
                    </div>
                    <div class="ibox-content" style="padding:20px 24px;">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 style="font-weight:700; color:#2c3e50; margin-bottom:6px;">
                                    <i class="mr-2 fa fa-toggle-{{ $configuracionActiva ? 'on text-success' : 'off text-muted' }}"></i>
                                    Activar revisión de inventario antes de Prefactura
                                </h5>
                                <p class="text-muted" style="font-size:13px; margin:0;">
                                    @if ($configuracionActiva)
                                        <span style="color:#27ae60; font-weight:600;">
                                            <i class="fa fa-check-circle mr-1"></i>Activado:
                                        </span>
                                        El flujo será: <strong>Oferta Ganadora → Revisión de Inventario → Prefactura</strong>
                                    @else
                                        <span style="color:#7f8c8d; font-weight:600;">
                                            <i class="fa fa-times-circle mr-1"></i>Desactivado:
                                        </span>
                                        El flujo actual es: <strong>Oferta Ganadora → Prefactura</strong> (sin revisión intermedia)
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" wire:click="toggleConfiguracion"
                                        wire:loading.attr="disabled"
                                        class="btn {{ $configuracionActiva ? 'btn-danger' : 'btn-success' }}"
                                        style="border-radius:8px; font-weight:700; min-width:160px;">
                                    <span wire:loading.remove wire:target="toggleConfiguracion">
                                        <i class="mr-1 fa fa-{{ $configuracionActiva ? 'toggle-off' : 'toggle-on' }}"></i>
                                        {{ $configuracionActiva ? 'Desactivar' : 'Activar' }}
                                    </span>
                                    <span wire:loading wire:target="toggleConfiguracion">
                                        <i class="fa fa-spinner fa-spin mr-1"></i> Guardando...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                @if($flujoData)
                                    — {{ $flujoData->cliente ?? '—' }}
                                @endif
                            </h5>
                            @if($flujoData && $flujoData->pedido_id)
                            <small style="color:rgba(255,255,255,.8); font-size:11px;">
                                Pedido #{{ $flujoData->pedido_id }}
                                @if($flujoData->pedido_fecha)
                                    · {{ \Carbon\Carbon::parse($flujoData->pedido_fecha)->format('d/m/Y') }}
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
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stockErrors as $se)
                                    <tr style="border-bottom:1px solid #fce4cc;">
                                        <td style="padding:5px 10px; color:#2c3e50;">{{ $se['producto'] }}</td>
                                        <td style="padding:5px 10px; text-align:center; font-weight:700; color:#e65100;">{{ $se['solicitado'] }}</td>
                                        <td style="padding:5px 10px; text-align:center; font-weight:700; color:#b71c1c;">{{ $se['disponible'] }}</td>
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
                        @endif
                        @endif

                        {{-- Tabla de productos --}}
                        <div style="border-radius:12px; overflow:hidden; border:1px solid #e8eaf0; margin-bottom:20px;">
                            <div style="background:linear-gradient(135deg,#546e7a,#37474f); padding:10px 16px;">
                                <span style="color:#fff; font-size:13px; font-weight:700;">
                                    <i class="mr-1 fa fa-list-ul"></i>
                                    Productos de la oferta
                                    <span style="background:rgba(255,255,255,.2); border-radius:20px;
                                                 padding:1px 10px; font-size:11px; margin-left:6px;">
                                        {{ count($productos) }}
                                    </span>
                                </span>
                            </div>
                            @if(count($productos) === 0)
                            <div style="padding:24px; text-align:center; color:#aaa;">
                                <i class="fa fa-inbox d-block" style="font-size:28px; margin-bottom:8px; opacity:.4;"></i>
                                Sin productos registrados.
                            </div>
                            @else
                            <div style="overflow-x:auto;">
                                <table class="table table-hover" style="font-size:13px; margin:0;">
                                    <thead style="background:#f8f9fc;">
                                        <tr>
                                            <th style="padding:10px 14px; color:#555; font-weight:700;">#</th>
                                            <th style="padding:10px 14px; color:#555; font-weight:700;">Producto</th>
                                            <th style="padding:10px 14px; text-align:center; color:#555; font-weight:700;">Cantidad</th>
                                            <th style="padding:10px 14px; text-align:center; color:#555; font-weight:700;">Disponible</th>
                                            <th style="padding:10px 14px; text-align:center; color:#555; font-weight:700;">Estado</th>
                                            <th style="padding:10px 14px; color:#555; font-weight:700;">Nota / Reemplazo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($productos as $prod)
                                        <tr style="border-bottom:1px solid #f0f0f0;
                                                   {{ $prod['falta_stock'] ? 'background:#fff8f5;' : '' }}">
                                            <td style="padding:8px 14px; color:#888;">{{ $loop->iteration }}</td>
                                            <td style="padding:8px 14px; color:#2c3e50; font-weight:600;">
                                                {{ $prod['nombre_producto'] }}
                                            </td>
                                            <td style="padding:8px 14px; text-align:center;">
                                                <span style="background:#e3f2fd; color:#1565c0; border-radius:12px;
                                                             padding:2px 10px; font-weight:700; font-size:13px;">
                                                    {{ $prod['cantidad'] }}
                                                </span>
                                            </td>
                                            <td style="padding:8px 14px; text-align:center;">
                                                @if ($prod['disponible'] !== null)
                                                    <span style="background:{{ $prod['falta_stock'] ? '#fce4ec' : '#e8f5e9' }};
                                                                 color:{{ $prod['falta_stock'] ? '#b71c1c' : '#2e7d32' }};
                                                                 border-radius:12px; padding:2px 10px; font-weight:700; font-size:13px;">
                                                        {{ (int) $prod['disponible'] }}
                                                    </span>
                                                @else
                                                    <span style="color:#aaa; font-size:12px;">—</span>
                                                @endif
                                            </td>
                                            <td style="padding:8px 14px; text-align:center;">
                                                @if ($prod['falta_stock'])
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
                                                       {{ $devuelto ? 'readonly' : '' }}
                                                       style="font-size:12px; border-radius:6px;
                                                              border-color: {{ $prod['falta_stock'] ? '#f9a825' : '#ddd' }};
                                                              {{ $devuelto ? 'background:#f8f8f8; cursor:default;' : '' }}">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>

                        {{-- Panel de acciones --}}
                        @if ($devuelto)
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
                        <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                            {{-- Pasar a Prefactura --}}
                            @if (count($stockErrors) === 0 && count($productos) > 0 && collect($obsProducto)->filter()->isEmpty())
                            <button type="button" wire:click="confirmarAccion('prefactura')"
                                    style="background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff;
                                           border:none; border-radius:10px; padding:9px 22px;
                                           font-size:13px; font-weight:700; cursor:pointer;
                                           box-shadow:0 3px 10px rgba(26,179,148,.4);">
                                <i class="mr-1 fa fa-file-o"></i> Pasar a Prefactura
                            </button>
                            @else
                            <button type="button" disabled
                                    style="background:#ccc; color:#fff; border:none; border-radius:10px;
                                           padding:9px 22px; font-size:13px; font-weight:700; cursor:not-allowed;"
                                    title="{{ collect($obsProducto)->filter()->isNotEmpty() ? 'Elimine las notas/reemplazos antes de pasar a Prefactura' : 'Hay productos sin stock suficiente' }}">
                                <i class="mr-1 fa fa-ban"></i> Pasar a Prefactura
                            </button>
                            @endif

                            {{-- Devolver a Oferta --}}
                            <button type="button" wire:click="confirmarAccion('devolver')"
                                    style="background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff;
                                           border:none; border-radius:10px; padding:9px 22px;
                                           font-size:13px; font-weight:700; cursor:pointer;
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
                                'llegando'   => ['label' => 'Llegando',        'icon' => 'fa-inbox',       'count' => count($bandejaRegistros)],
                                'devueltos'  => ['label' => 'Devueltos',       'icon' => 'fa-reply',       'count' => count($bandejaDevueltos)],
                                'prefactura' => ['label' => 'En Prefactura',   'icon' => 'fa-file-text-o', 'count' => count($bandejaPrefactura)],
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
                        @if (count($bandejaRegistros) === 0)
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
                        @endif

                        {{-- ══ Pestaña: Devueltos ══ --}}
                        @elseif ($tabActiva === 'devueltos')
                        @if (count($bandejaDevueltos) === 0)
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
                        @endif

                        {{-- ══ Pestaña: En Prefactura ══ --}}
                        @else
                        @if (count($bandejaPrefactura) === 0)
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
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                        @endif {{-- /tabActiva --}}

                    </div>{{-- /ibox-content --}}
                </div>
            </div>
        </div>

        @endif {{-- /flujoId --}}

    </div>
</div>

