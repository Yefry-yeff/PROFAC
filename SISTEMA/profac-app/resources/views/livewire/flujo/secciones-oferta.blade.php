<div>
    <style>
        .expo-section-shell { max-width: 1480px; margin: 0 auto; }
        .expo-section-hero { background:#fff; border-left:4px solid #00897b; }
        .expo-payment-panel { background:#f4fbfa; border:1px solid #b2dfdb; border-radius:8px; padding:16px; }
        .expo-payment-label { color:#45615e; font-size:11px; font-weight:700; text-transform:uppercase; }
        .expo-product-table thead th { background:#f5f7fa; color:#52616b; border-bottom:2px solid #dfe5ea; white-space:nowrap; }
        .expo-product-table tbody tr { transition:background-color .15s ease; }
        .expo-product-table tbody tr.expo-selected { background:#e8f7f5; }
        .expo-product-table tbody tr.expo-no-stock { background:#fff0f0; color:#9b1c1c; }
        .expo-product-table tbody tr.expo-no-stock:hover { background:#ffe3e3; }
        .expo-stock-zero { display:inline-flex; align-items:center; gap:5px; color:#b42318; font-weight:800; }
        .expo-section-saved { border-left:3px solid #00a896; }
        .expo-success-action { border-radius:8px; padding:12px 8px; font-size:12px; font-weight:700; text-align:center; }
        @media (max-width: 767px) {
            .expo-payment-panel .form-group { margin-bottom:12px; }
            .expo-section-actions { width:100%; }
            .expo-section-actions .btn { flex:1; }
        }
    </style>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="mr-2 fa fa-object-group text-info"></i>Secciones de Ofertas</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Ofertas Expo</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight expo-section-shell">
        @if ($flujoId)
            <div class="ibox expo-section-hero">
                <div class="ibox-title d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1">Oferta Expo ganadora #{{ $cotizacionOrigenId }}</h5>
                        <small class="text-muted">{{ $oferta['nombre_cliente'] ?? '' }} · Flujo #{{ $flujoId }}</small>
                    </div>
                    <div class="d-flex expo-section-actions" style="gap:8px;">
                        <a class="btn btn-primary btn-sm" href="{{ route('flujo.ventas.historico', ['flujo_id' => $flujoId]) }}">
                            <i class="mr-1 fa fa-sitemap"></i>Ver flujo
                        </a>
                        <button type="button" class="btn btn-white btn-sm" wire:click="cerrarDetalle">
                            <i class="mr-1 fa fa-list"></i>Bandeja
                        </button>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="expo-payment-panel mb-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3" style="gap:8px;">
                            <div>
                                <h5 class="mb-1" style="color:#00695c;"><i class="mr-1 fa fa-calendar-check-o"></i>{{ $editarEstadoSeccion === 'DEVUELTA_INVENTARIO' ? 'Corregir sección devuelta por Inventario' : ($editarSeccionId ? 'Corregir sección rechazada por Crédito' : 'Condiciones de esta sección') }}</h5>
                                <small class="text-muted">{{ $editarEstadoSeccion === 'DEVUELTA_INVENTARIO' ? 'Las condiciones aprobadas por Crédito no pueden modificarse. La sección corregida volverá directamente a Revisión de Inventario.' : ($editarSeccionId ? 'La sección corregida volverá a Revisión de Crédito.' : 'Estos datos viajarán únicamente con esta sección a Revisión de Crédito.') }}</small>
                            </div>
                            @if (!$puedeCrear)
                                <span class="badge badge-info">Oferta completamente seccionada</span>
                            @endif
                        </div>
                        <div class="row align-items-end">
                            <div class="col-md-3 form-group mb-md-0">
                                <label class="expo-payment-label" for="tipoPagoSeccion">Condición de pago</label>
                                <select id="tipoPagoSeccion" class="form-control" wire:model="tipoPagoId" {{ !$puedeCrear || $editarEstadoSeccion === 'DEVUELTA_INVENTARIO' ? 'disabled' : '' }}>
                                    <option value="1">Contado</option>
                                    <option value="2">Crédito</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-md-0">
                                <label class="expo-payment-label" for="fechaEmisionSeccion">Fecha de emisión</label>
                                <input id="fechaEmisionSeccion" type="date" class="form-control" wire:model="fechaEmision" {{ !$puedeCrear || $editarEstadoSeccion === 'DEVUELTA_INVENTARIO' ? 'disabled' : '' }}>
                                @error('fechaEmision') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-3 form-group mb-md-0">
                                <label class="expo-payment-label" for="fechaPagoSeccion">{{ $tipoPagoId === 2 ? 'Fecha de vencimiento' : 'Fecha de pago' }}</label>
                                <input id="fechaPagoSeccion" type="date" class="form-control" wire:model="fechaPago"
                                       min="{{ $fechaEmision }}" {{ !$puedeCrear || $tipoPagoId === 1 || $editarEstadoSeccion === 'DEVUELTA_INVENTARIO' ? 'disabled' : '' }}>
                                @error('fechaPago') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-3 form-group mb-md-0">
                                <div class="custom-control custom-checkbox pb-2">
                                    <input type="checkbox" class="custom-control-input" id="finalizaSeccionado" wire:model.defer="finalizaSeccionado" {{ !$puedeCrear || $editarEstadoSeccion === 'DEVUELTA_INVENTARIO' ? 'disabled' : '' }}>
                                    <label class="custom-control-label" for="finalizaSeccionado">Esta será la última sección</label>
                                </div>
                            </div>
                            <div class="col-12 form-group mb-0 mt-3">
                                <label class="expo-payment-label" for="comentarioCreditoSeccion">Comentario para Créditos (opcional)</label>
                                <textarea id="comentarioCreditoSeccion" class="form-control" rows="2"
                                          wire:model.defer="comentarioCredito"
                                          placeholder="Observación específica de esta sección..."
                                          {{ !$puedeCrear || $editarEstadoSeccion === 'DEVUELTA_INVENTARIO' ? 'disabled' : '' }}></textarea>
                            </div>
                        </div>
                    </div>

                    @if($editarEstadoSeccion === 'DEVUELTA_INVENTARIO')
                    <div class="alert alert-warning" style="border-left:4px solid #e65100;">
                        <h5 style="color:#8a4700;"><i class="mr-1 fa fa-commenting-o"></i>Observaciones de Revisión de Inventario</h5>
                        <div class="mb-2"><strong>Comentario general:</strong> {{ $comentarioInventarioGeneral ?: 'Sin comentario general.' }}</div>
                        @if(!empty($comentariosInventarioProductos))
                            @foreach($comentariosInventarioProductos as $comentarioProducto)
                            <div style="background:#fff; border-left:3px solid #ef6c00; padding:7px 10px; margin-top:6px; border-radius:4px;">
                                <strong>{{ $comentarioProducto['producto'] }}:</strong> {{ $comentarioProducto['comentario'] }}
                            </div>
                            @endforeach
                        @else
                            <small>No se registraron comentarios por producto.</small>
                        @endif
                    </div>
                    @endif

                    @if (!$puedeCrear && !empty($productos))
                        <div class="alert alert-info">
                            <i class="mr-1 fa fa-info-circle"></i>Esta oferta ya fue seccionada completamente o se marcó una sección como final.
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:10px;">
                        <h5 class="mb-0">Productos pendientes de seccionar</h5>
                        <div class="input-group input-group-sm" style="max-width:340px;">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-search"></i></span></div>
                            <input type="search" class="form-control" placeholder="Buscar producto o código" wire:model.debounce.300ms="busquedaProducto">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle expo-product-table">
                            <thead>
                                <tr>
                                    <th style="width:74px;" class="text-center">
                                        <label class="mb-0" style="cursor:pointer;">
                                            <input type="checkbox" wire:model="seleccionarTodos" {{ !$puedeCrear ? 'disabled' : '' }}>
                                            <span class="d-block" style="font-size:10px;">Incluir</span>
                                        </label>
                                    </th>
                                    <th>Producto</th>
                                    <th class="text-right">Original</th>
                                    <th class="text-right">Asignado</th>
                                    <th class="text-right">Disponible bodega</th>
                                    <th class="text-right">Saldo en Oferta</th>
                                    <th style="width:160px;">Cantidad a incluir</th>
                                    <th class="text-right">Precio</th>
                                    <th class="text-right">Descuento</th>
                                    <th style="width:45px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($productosFiltrados as $producto)
                                    @php
                                        $seleccionado = !empty($seleccionados[$producto['id']]);
                                        $inventarioInsuficiente = (float) $producto['disponible_bodega'] < (float) $producto['cantidad_pendiente'];
                                        $faltante = max(0, (float) $producto['cantidad_pendiente'] - (float) $producto['disponible_bodega']);
                                    @endphp
                                    <tr class="{{ $inventarioInsuficiente ? 'expo-no-stock' : ($seleccionado ? 'expo-selected' : '') }}">
                                        <td class="text-center">
                                            <input type="checkbox" wire:model="seleccionados.{{ $producto['id'] }}" {{ !$puedeCrear ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <strong>{{ $producto['nombre_producto'] }}</strong>
                                            <div><small class="text-muted">Código {{ $producto['producto_id'] }}</small></div>
                                            @if ($inventarioInsuficiente)
                                                <div class="expo-stock-zero"><i class="fa fa-exclamation-circle"></i>Inventario insuficiente: faltan {{ number_format($faltante, 0) }}</div>
                                            @endif
                                        </td>
                                        <td class="text-right">{{ number_format($producto['cantidad'], 0) }}</td>
                                        <td class="text-right">{{ number_format($producto['cantidad_asignada'], 0) }}</td>
                                        <td class="text-right">
                                            <strong class="{{ $inventarioInsuficiente ? 'text-danger' : 'text-success' }}">{{ number_format($producto['disponible_bodega'], 0) }}</strong>
                                        </td>
                                        <td class="text-right"><strong>{{ number_format($producto['cantidad_pendiente'], 0) }}</strong></td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm"
                                                   min="0" max="{{ (int) $producto['cantidad_pendiente'] }}" step="1"
                                                   wire:model.lazy="cantidades.{{ $producto['id'] }}"
                                                   {{ !$puedeCrear ? 'disabled' : '' }}>
                                        </td>
                                        <td class="text-right">L {{ number_format($producto['precio_unidad'], 2) }}</td>
                                        <td class="text-right">L {{ number_format($producto['monto_descProducto'] ?? 0, 2) }}</td>
                                        <td>
                                            <button type="button" class="btn btn-white btn-sm" title="Quitar producto"
                                                    wire:click="quitarProducto({{ $producto['id'] }})" {{ !$puedeCrear || !$seleccionado ? 'disabled' : '' }}>
                                                <i class="fa fa-times text-danger"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="text-center text-muted py-4">No hay productos pendientes que coincidan con la búsqueda.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($puedeCrear)
                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-primary" wire:click="guardar" wire:loading.attr="disabled">
                            <i class="mr-1 fa fa-paper-plane"></i>{{ $editarEstadoSeccion === 'DEVUELTA_INVENTARIO' ? 'Guardar cambios y reenviar a Inventario' : ($editarSeccionId ? 'Guardar cambios y reenviar a Crédito' : 'Guardar y enviar a revisión') }}
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            @if (!empty($secciones))
                <div class="ibox expo-section-saved">
                    <div class="ibox-title"><h5><i class="mr-1 fa fa-check-circle text-navy"></i>Secciones creadas</h5></div>
                    <div class="ibox-content p-0">
                        <table class="table mb-0">
                            <thead><tr><th>#</th><th>Nombre</th><th>Pago</th><th>Emisión</th><th>Pago/Vence</th><th>Estado</th><th class="text-right">Total</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($secciones as $seccion)
                                    <tr>
                                        <td>{{ $seccion['numero'] }}</td>
                                        <td>{{ $seccion['nombre'] }}</td>
                                        <td>{{ ucfirst($seccion['tipo_pago'] ?? 'contado') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($seccion['fecha_emision'])->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($seccion['fecha_vencimiento'])->format('d/m/Y') }}</td>
                                        <td><span class="badge badge-info">{{ str_replace('_', ' ', $seccion['estado']) }}</span></td>
                                        <td class="text-right">L {{ number_format($seccion['total'], 2) }}</td>
                                        <td class="text-right">
                                            <a class="btn btn-white btn-xs" target="_blank" href="/cotizacion/imprimir/{{ $seccion['cotizacion_id'] }}" title="Imprimir sección">
                                                <i class="fa fa-print"></i>
                                            </a>
                                            @if(in_array($seccion['estado'], ['RECHAZADA_CREDITO', 'DEVUELTA_INVENTARIO'], true))
                                            <a class="btn btn-danger btn-xs" href="{{ route('flujo.secciones_ofertas', ['flujo_id' => $flujoId, 'seccion_id' => $seccion['id']]) }}" title="Editar y reenviar">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td colspan="7" class="pt-0">
                                            @foreach ($seccion['productos'] as $productoSeccion)
                                                <span class="badge badge-light mr-1 mb-1">
                                                    {{ number_format($productoSeccion['cantidad'], 0) }} × {{ $productoSeccion['nombre_producto'] }}
                                                </span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="modal fade" id="modalSeccionGuardada" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" wire:ignore.self>
                <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:430px;">
                    <div class="modal-content" style="border-radius:16px; overflow:hidden; border:none; box-shadow:0 20px 60px rgba(0,0,0,.2);">
                        <div class="modal-body text-center" style="padding:34px 30px 28px;">
                            <div style="width:82px; height:82px; border-radius:50%; background:#e8f7f5; color:#00897b;
                                        display:flex; align-items:center; justify-content:center; margin:0 auto 18px; font-size:38px;">
                                <i class="fa fa-check"></i>
                            </div>
                            <h4 style="font-weight:800; color:#145c54;">Sección guardada</h4>
                            <p class="text-muted mb-4">{{ $ultimaSeccionNombre }} fue enviada a Revisión de {{ $ultimaSeccionDestino }}.</p>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                <a class="btn btn-white expo-success-action" target="_blank"
                                   href="{{ $ultimaSeccionId ? '/cotizacion/imprimir/'.$ultimaSeccionId : '#' }}">
                                    <i class="fa fa-print d-block mb-1" style="font-size:20px;"></i>Imprimir sección
                                </a>
                                <a class="btn btn-primary expo-success-action" href="{{ route('flujo.ventas.historico', ['flujo_id' => $flujoId]) }}">
                                    <i class="fa fa-sitemap d-block mb-1" style="font-size:20px;"></i>Ver flujo
                                </a>
                                <button type="button" class="btn btn-info expo-success-action" wire:click="nuevaSeccion" style="grid-column:1 / -1;">
                                    <i class="fa fa-plus-circle d-block mb-1" style="font-size:20px;"></i>Crear nueva sección en la oferta
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="ibox">
                <div class="ibox-title d-flex align-items-center justify-content-between">
                    <h5>Ofertas Expo pendientes de seccionar</h5>
                    <input type="search" class="form-control form-control-sm" style="max-width:280px;" placeholder="Buscar cliente, flujo u oferta" wire:model.debounce.350ms="busqueda">
                </div>
                <div class="ibox-content p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Flujo</th><th>Oferta ganadora</th><th>Cliente</th><th>Fecha</th><th class="text-right">Total</th><th></th></tr></thead>
                            <tbody>
                                @forelse ($bandeja as $fila)
                                    <tr>
                                        <td>#{{ $fila['flujo_id'] }}</td>
                                        <td>#{{ $fila['cotizacion_id'] }}</td>
                                        <td>{{ $fila['nombre_cliente'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($fila['fecha_emision'])->format('d/m/Y') }}</td>
                                        <td class="text-right">L {{ number_format($fila['total'], 2) }}</td>
                                        <td class="text-right">
                                            <button type="button" class="btn btn-info btn-sm" wire:click="seleccionarFlujo({{ $fila['flujo_id'] }})">
                                                <i class="mr-1 fa fa-object-group"></i>Seccionar
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-5">No hay ofertas Expo pendientes de seccionar.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade" id="modalErrorSeccion" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:430px;">
            <div class="modal-content" style="border-radius:12px; border:none; overflow:hidden;">
                <div class="modal-body text-center" style="padding:30px;">
                    <div style="font-size:42px; color:#c62828; margin-bottom:12px;"><i class="fa fa-exclamation-circle"></i></div>
                    <h4 style="font-weight:800; color:#8e1b1b;">No se pudo guardar la sección</h4>
                    <p class="text-muted mb-4">{{ $mensajeError }}</p>
                    <button type="button" class="btn btn-danger" data-dismiss="modal" wire:click="cerrarMensajeError">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.addEventListener('mostrar-modal-seccion-guardada', function () {
                $('#modalSeccionGuardada').modal('show');
            });
            window.addEventListener('cerrar-modal-seccion-guardada', function () {
                $('#modalSeccionGuardada').modal('hide');
            });
            window.addEventListener('mostrar-modal-error-seccion', function () {
                $('#modalErrorSeccion').modal('show');
            });
        </script>
    @endpush
</div>