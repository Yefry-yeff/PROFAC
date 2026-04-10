<div>
    {{-- ===== ESTILOS ===== --}}
    <style>
        .edit-section-title {
            font-size: 13px; font-weight: 700; color: #1a7efb;
            text-transform: uppercase; letter-spacing: .5px;
            border-bottom: 2px solid #e8f0fe; padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .client-readonly-card {
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f8f5 100%);
            border: 1px solid #d0e4ff;
            border-radius: 12px;
            padding: 16px 20px;
        }
        .client-readonly-card .client-name {
            font-size: 16px; font-weight: 700; color: #1a7efb;
        }
        .client-readonly-card .client-badge {
            background: linear-gradient(135deg,#1a7efb,#1ab394);
            color: #fff; border-radius: 20px; font-size: 11px;
            padding: 2px 12px; font-weight: 600; display: inline-block;
            margin-left: 8px; vertical-align: middle;
        }
        .items-table th {
            background: #f5f7fb; font-size: 12px; font-weight: 700;
            color: #555; border-bottom: 2px solid #e0e3ee !important;
        }
        .items-table td { vertical-align: middle !important; }
        .qty-input { width: 90px !important; text-align: center; }
        .scroll-top-listener {}
    </style>

    {{-- ===== ENCABEZADO ===== --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>
                <i class="fa fa-pencil-square-o text-warning"></i>
                Editar Pedido
                @if($pedidoId)
                    <span class="text-muted" style="font-size:18px;">#{{ $pedidoId }}</span>
                @endif
            </h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item">Flujo</li>
                <li class="breadcrumb-item">
                    <a href="/flujo/pedidos/historico">Historial</a>
                </li>
                <li class="breadcrumb-item active"><strong>Editar Pedido</strong></li>
            </ol>
        </div>
        <div class="col-lg-2 d-flex align-items-center justify-content-end">
            <a href="/flujo/pedidos/historico" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> &nbsp;Volver
            </a>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- ===== ALERTAS ===== --}}
        @if ($mensajeExito)
            <div class="alert alert-success alert-dismissible"
                 style="border-radius:10px; border-left:4px solid #1ab394;">
                <button type="button" class="close" wire:click="$set('mensajeExito', '')">
                    <span>&times;</span>
                </button>
                <i class="fa fa-check-circle"></i>
                <strong>¡Guardado!</strong> {{ $mensajeExito }}
                &nbsp;
                <a href="/flujo/pedidos/historico" class="btn btn-xs btn-success" style="border-radius:12px;">
                    <i class="fa fa-list-alt"></i> Ir al Historial
                </a>
            </div>
        @endif

        @if ($mensajeError)
            <div class="alert alert-danger alert-dismissible"
                 style="border-radius:10px; border-left:4px solid #e74c3c;">
                <button type="button" class="close" wire:click="$set('mensajeError', '')">
                    <span>&times;</span>
                </button>
                <i class="fa fa-exclamation-triangle"></i> {{ $mensajeError }}
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox" style="border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.06);">

                    {{-- Encabezado del ibox --}}
                    <div class="ibox-title editar-main-title py-3 px-4"
                         style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%); border:none;">
                        <h5 class="m-0" style="color:#fff; font-size:15px;">
                            <i class="fa fa-pencil mr-2"></i>
                            Modificar datos del pedido
                            @if($pedidoId)
                                <span style="background:rgba(255,255,255,.2); border-radius:16px;
                                             padding:2px 12px; font-size:13px; margin-left:8px;">#{{ $pedidoId }}</span>
                            @endif
                        </h5>
                    </div>

                    <div class="ibox-content" style="padding:28px;">

                        {{-- ====================================================== --}}
                        {{-- SECCIÓN 1 · CLIENTE (solo lectura) --}}
                        {{-- ====================================================== --}}
                        <div class="edit-section-title">
                            <i class="fa fa-user-circle-o mr-1"></i> Cliente del Pedido
                        </div>

                        @if ($clienteSeleccionado)
                            <div class="client-readonly-card mb-4">
                                <div class="d-flex align-items-start justify-content-between flex-wrap">
                                    <div>
                                        <span class="client-name">
                                            <i class="fa fa-building-o mr-1" style="color:#1ab394;"></i>
                                            {{ $clienteSeleccionado['nombre'] }}
                                        </span>
                                        <span class="client-badge">
                                            <i class="fa fa-lock mr-1"></i> Cliente fijo
                                        </span>
                                        <br>
                                        @if($clienteSeleccionado['rtn'])
                                            <small class="text-muted">
                                                <i class="fa fa-id-card-o mr-1"></i>
                                                RTN: <strong>{{ $clienteSeleccionado['rtn'] }}</strong>
                                            </small>
                                        @endif
                                    </div>
                                    <div class="text-right" style="font-size:12px; color:#888; margin-top:4px;">
                                        @if($clienteSeleccionado['correo'])
                                            <div><i class="fa fa-envelope-o mr-1"></i>{{ $clienteSeleccionado['correo'] }}</div>
                                        @endif
                                        @if($clienteSeleccionado['telefono_empresa'])
                                            <div><i class="fa fa-phone mr-1"></i>{{ $clienteSeleccionado['telefono_empresa'] }}</div>
                                        @endif
                                        @if($clienteSeleccionado['direccion'])
                                            <div><i class="fa fa-map-marker mr-1"></i>{{ $clienteSeleccionado['direccion'] }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div style="margin-top:10px; padding:6px 10px;
                                            background:rgba(26,126,251,.08); border-radius:8px;
                                            font-size:12px; color:#1a7efb;">
                                    <i class="fa fa-info-circle mr-1"></i>
                                    El cliente no puede modificarse en un pedido ya registrado.
                                </div>
                            </div>
                        @endif

                        {{-- Validación --}}
                        @error('clienteSeleccionado')
                            <div class="alert alert-danger alert-sm py-2">
                                <i class="fa fa-exclamation-triangle"></i> {{ $message }}
                            </div>
                        @enderror

                        {{-- ====================================================== --}}
                        {{-- SECCIÓN 2 · PRODUCTOS --}}
                        {{-- ====================================================== --}}
                        <div class="edit-section-title mt-4">
                            <i class="fa fa-list mr-1"></i> Productos del Pedido
                        </div>

                        {{-- Errores de items --}}
                        @if($errors->has('items') || $errors->has('items.*') || $errors->hasAny(collect(range(0,count($items)-1))->map(fn($i)=>"items.$i.nombre_producto")->all()))
                            <div class="alert alert-danger py-2 mb-3" style="border-radius:8px; font-size:13px;">
                                <i class="fa fa-exclamation-triangle mr-1"></i>
                                Revise los datos de los productos.
                            </div>
                        @endif

                        <div class="table-responsive mb-2">
                            <table class="table table-hover items-table" style="font-size:13px;">
                                <thead>
                                    <tr>
                                        <th style="width:40px; text-align:center;">#</th>
                                        <th>Producto / Descripción</th>
                                        <th style="width:110px; text-align:center;">Cantidad</th>
                                        <th style="width:60px; text-align:center;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $idx => $item)
                                    <tr wire:key="item-{{ $idx }}">
                                        <td class="text-center text-muted" style="font-size:11px;">
                                            {{ $idx + 1 }}
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                wire:model.defer="items.{{ $idx }}.nombre_producto"
                                                class="form-control form-control-sm @error('items.'.$idx.'.nombre_producto') is-invalid @enderror"
                                                placeholder="Nombre del producto..."
                                                style="font-size:13px;"
                                            >
                                            @error('items.'.$idx.'.nombre_producto')
                                                <div class="invalid-feedback" style="font-size:11px;">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="text-center">
                                            <input
                                                type="number"
                                                wire:model.defer="items.{{ $idx }}.cantidad"
                                                class="form-control form-control-sm qty-input @error('items.'.$idx.'.cantidad') is-invalid @enderror"
                                                min="1"
                                                step="1"
                                            >
                                            @error('items.'.$idx.'.cantidad')
                                                <div class="invalid-feedback" style="font-size:11px;">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="text-center">
                                            @if(count($items) > 1)
                                                <button type="button"
                                                        wire:click="eliminarItem({{ $idx }})"
                                                        class="btn btn-xs btn-danger"
                                                        title="Eliminar fila"
                                                        style="border-radius:50%; width:24px; height:24px;
                                                               padding:0; line-height:22px;">
                                                    <i class="fa fa-times" style="font-size:10px;"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="button"
                                wire:click="agregarItem"
                                class="btn btn-default btn-sm"
                                style="border-radius:20px; font-size:12px; border:1px dashed #1a7efb; color:#1a7efb;">
                            <i class="fa fa-plus-circle mr-1"></i> Agregar producto
                        </button>

                        {{-- ====================================================== --}}
                        {{-- SECCIÓN 3 · OBSERVACIONES + GUARDAR --}}
                        {{-- ====================================================== --}}
                        <div class="row mt-4">
                            <div class="col-lg-7 col-md-12">
                                <div class="edit-section-title">
                                    <i class="fa fa-comment-o mr-1"></i> Observaciones
                                </div>
                                <textarea
                                    wire:model.defer="observaciones"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Notas adicionales para este pedido..."
                                    style="border-radius:10px; font-size:13px; resize:vertical;"
                                ></textarea>
                            </div>
                            <div class="col-lg-5 col-md-12">
                                <div class="edit-section-title">&nbsp;</div>
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <button type="button"
                                            wire:click="guardarCambios"
                                            wire:loading.attr="disabled"
                                            class="btn btn-warning btn-lg btn-block"
                                            style="border-radius:12px; font-weight:700; color:#fff;
                                                   font-size:15px; letter-spacing:.3px;
                                                   box-shadow:0 6px 20px rgba(243,156,18,.4);">
                                        <span wire:loading.remove wire:target="guardarCambios">
                                            <i class="fa fa-save mr-2"></i> Guardar Cambios
                                        </span>
                                        <span wire:loading wire:target="guardarCambios">
                                            <i class="fa fa-spinner fa-spin mr-2"></i> Guardando...
                                        </span>
                                    </button>
                                    <a href="/flujo/pedidos/historico"
                                       class="btn btn-default btn-block"
                                       style="border-radius:12px; font-size:13px;">
                                        <i class="fa fa-times mr-1"></i> Cancelar y volver
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /ibox-content --}}
                </div>{{-- /ibox --}}
            </div>
        </div>

    </div>{{-- /wrapper-content --}}

    {{-- scroll-to-top listener --}}
    <script>
        window.addEventListener('scroll-top', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
</div>
