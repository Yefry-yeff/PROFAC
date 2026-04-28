<div>
    @push('styles')
    <style>
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
        @media (max-width: 767.5px) { .hide-container { display: none; } }
        .center-div { text-align: center; }
        .img-size { width: 100%; height: 16rem; margin: 0 auto; object-fit: contain; }
        .tipo-factura-selector .btn { margin-right: 5px; margin-bottom: 5px; }
        .tipo-factura-selector .btn.active { box-shadow: 0 0 0 3px rgba(0,123,255,.5); }

        /* ── Pedido panel ─────────────────────────────────────────── */
        .pedido-link-panel {
            border: 2px dashed #b2dfdb;
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 24px;
            background: #f0fdf4;
        }
        .pedido-link-panel.linked { border: 2px solid #00897b; background: #e8f5e9; }
        .ped-row {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; border-radius: 10px;
            background: #fff; border: 1px solid #e0f2f1;
            margin-bottom: 6px; cursor: pointer;
            transition: box-shadow .15s, border-color .15s;
        }
        .ped-row:hover { box-shadow: 0 3px 12px rgba(0,0,0,.09); border-color: #80cbc4; }

        /* ── Section headers ──────────────────────────────────────── */
        .ofr-section-header {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 18px; border-radius: 10px;
            margin: 20px 0 16px;
            font-weight: 700; font-size: 13px;
            letter-spacing: .4px; text-transform: uppercase;
        }

        /* ── Main ibox ────────────────────────────────────────────── */
        .ofr-main-ibox { border-radius: 16px !important; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.07) !important; }
        .ofr-main-ibox > .ibox-title {
            background: linear-gradient(135deg,#004d40 0%,#00897b 100%) !important;
            border: none !important; padding: 16px 24px !important;
        }
        .ofr-main-ibox > .ibox-title h3 { color: #fff !important; margin: 0; font-size: 16px; }
        .ofr-main-ibox > .ibox-title .badge { background: rgba(255,255,255,.2); color: #fff; }
        .ofr-main-ibox > .ibox-content { padding: 24px 28px !important; }

        /* ── Field labels ─────────────────────────────────────────── */
        .ofr-label {
            font-size: 11px; font-weight: 700; color: #546e7a;
            text-transform: uppercase; letter-spacing: .5px;
            margin-bottom: 4px; display: block;
        }
        .ofr-label .req { color: #e53935; margin-left: 2px; }
        .form-control.ofr-input {
            border-radius: 8px !important; border: 1px solid #cfd8dc !important; font-size: 13px !important;
        }
        .form-control.ofr-input:focus {
            border-color: #00897b !important; box-shadow: 0 0 0 3px rgba(0,137,123,.12) !important;
        }

        /* ── Totales grid ─────────────────────────────────────────── */
        .totales-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px; margin-bottom: 24px;
        }
        .total-card { border-radius: 10px; padding: 12px 16px; text-align: center; }
        .total-card .tc-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; opacity: .75; margin-bottom: 4px; }
        .total-card input {
            border: none !important; background: transparent !important;
            font-size: 18px !important; font-weight: 800 !important;
            padding: 0 !important; text-align: center; width: 100%;
        }
        .total-card.total-final input { font-size: 22px !important; font-weight: 900 !important; color: #fff !important; }

        /* ── Modal centrado (compatibilidad Bootstrap 3/4) ─────────── */
        .modal-dialog-centered {
            display: flex !important;
            align-items: center !important;
            min-height: calc(100% - 3.5rem) !important;
        }
        /* ── Carrito items ─────────────────────────────────────────── */
        .cart-item-card { transition: box-shadow .15s; }
        .cart-item-card:hover { box-shadow: 0 4px 18px rgba(27,94,32,.14) !important; }
        .cart-field-label { font-size:10px; color:#78909c; font-weight:700; text-transform:uppercase; letter-spacing:.3px; margin-bottom:3px; }
    </style>
    @endpush

    {{-- ===== PAGE HEADING (solo en flujo) ===== --}}
    @if($fromFlujo && ($config->codigo ?? '') === 'cotizacion_clientes_a')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="fa fa-file-text-o" style="color:#00897b;"></i> Nueva Oferta</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('flujo.ventas') }}">Ventas</a></li>
                <li class="breadcrumb-item"><a href="{{ route('flujo.ofertas') }}">Ofertas</a></li>
                <li class="breadcrumb-item active"><strong>Nueva Oferta</strong></li>
            </ol>
        </div>
        <div class="col-lg-2 d-flex align-items-center justify-content-end">
            <a href="{{ route('flujo.ventas') }}" class="btn btn-default btn-sm">
                <i class="mr-1 fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    @elseif($fromFlujo)
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>{{ $config->nombre ?? 'Venta' }}</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('flujo.ventas') }}">Ventas</a></li>
                <li class="breadcrumb-item active"><strong>{{ $config->nombre ?? 'Factura' }}</strong></li>
            </ol>
        </div>
        <div class="col-lg-2 d-flex align-items-center justify-content-end">
            <a href="{{ route('flujo.ventas') }}" class="btn btn-default btn-sm">
                <i class="mr-1 fa fa-arrow-left"></i> Volver
            </a>

            
        </div>
    </div>
    @endif

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- ===== SELECTOR DE TIPO (fuera de flujo) ===== --}}
        @if(!$fromFlujo)
        <div class="mb-3 row">
            <div class="col-12">
                <div class="ibox">
                    <div class="py-2 ibox-content">
                        <div class="flex-wrap d-flex align-items-center tipo-factura-selector">
                            <strong class="mr-3">Tipo de Facturación:</strong>
                            @foreach($tiposFactura as $tipo)
                                <button type="button" id="btnTipo_{{ $tipo->id }}"
                                    class="btn btn-sm {{ $config && $config->id == $tipo->id ? 'btn-primary active' : 'btn-outline-secondary' }}"
                                    onclick="cambiarTipoFactura('{{ $tipo->ruta_menu }}')">{{ $tipo->nombre }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== PANEL: VINCULAR A PEDIDO (solo en modo oferta desde flujo) ===== --}}
        @if($fromFlujo && ($config->codigo ?? '') === 'cotizacion_clientes_a')
        <div class="pedido-link-panel {{ $pedidoVinculado ? 'linked' : '' }}">
            @if(!$pedidoVinculado)
            <div class="mb-3">
                <h6 style="margin:0; font-weight:800; color:#00695c;">
                    <i class="mr-2 fa fa-link"></i>Vincular a un Pedido
                    <span style="font-size:11px; font-weight:400; color:#78909c; margin-left:8px;">(opcional)</span>
                </h6>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background:#00897b; color:#fff; border-color:#00897b; border-radius:8px 0 0 8px;">
                                <i class="fa fa-search"></i>
                            </span>
                        </div>
                        <input type="text"
                               wire:model.debounce.350ms="busquedaPedido"
                               class="form-control"
                               placeholder="Buscar pedido por # o nombre de cliente…"
                               style="border-radius:0 8px 8px 0;"
                               autocomplete="off">
                    </div>
                    @if(strlen(trim($busquedaPedido)) > 0 && strlen(trim($busquedaPedido)) < 2)
                        <small class="mt-1 text-muted d-block">Escribe al menos 2 caracteres</small>
                    @endif
                </div>
                <div class="col-md-6 d-flex align-items-center">
                    <small class="text-muted">
                        <i class="mr-1 fa fa-info-circle text-info"></i>
                        Puedes crear <strong>múltiples ofertas</strong> para el mismo pedido.
                    </small>
                </div>
            </div>

            @if(count($pedidosEncontrados) > 0)
            <div style="max-height:280px; overflow-y:auto; margin-top:12px;">
                @foreach($pedidosEncontrados as $ped)
                @php $p = (array)$ped; @endphp
                <div class="ped-row" wire:click="seleccionarPedido({{ $p['id'] }})" style="cursor:pointer;">
                    <div style="flex-shrink:0;">
                        <span style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border-radius:8px; padding:4px 12px; font-size:13px; font-weight:800;">#{{ $p['id'] }}</span>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:700; color:#2c3e50; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $p['cliente'] }}</div>
                        <div style="font-size:11px; color:#90a4ae;">RTN: {{ $p['rtn'] ?: '—' }} &nbsp;·&nbsp; {{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}</div>
                    </div>
                    <div style="flex-shrink:0; text-align:center; min-width:70px;">
                        <div style="font-size:10px; color:#90a4ae;">Ofertas</div>
                        <div style="font-weight:700; color:{{ $p['total_ofertas'] > 0 ? '#00897b' : '#b0bec5' }}; font-size:15px;">
                            {{ $p['total_ofertas'] }}
                            @if($p['has_ganadora'] > 0)<i class="fa fa-trophy text-warning" style="font-size:12px;"></i>@endif
                        </div>
                    </div>
                    <div style="flex-shrink:0;">
                        @php $estMap=['pendiente'=>['#e3f2fd','#1565c0'],'pre_factura'=>['#fff8e1','#f57f17'],'activo'=>['#e8f5e9','#2e7d32'],'cancelado'=>['#fce4ec','#b71c1c']]; $col=$estMap[$p['estado']]??['#f5f5f5','#546e7a']; @endphp
                        <span style="background:{{ $col[0] }}; color:{{ $col[1] }}; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700;">{{ ucfirst(str_replace('_',' ',$p['estado'])) }}</span>
                    </div>
                    <div style="flex-shrink:0;" wire:click.stop="verDetallePedido({{ $p['id'] }})">
                        <span style="background:#1565c0; color:#fff; border-radius:8px; padding:6px 14px; font-size:12px; font-weight:700; cursor:pointer;"><i class="mr-1 fa fa-eye"></i> Detalle</span>
                    </div>
                </div>
                @endforeach
            </div>
            @elseif(strlen(trim($busquedaPedido)) >= 2)
            <div class="py-3 mt-2 text-center">
                <i class="mb-2 fa fa-search fa-2x" style="color:#b2dfdb; display:block;"></i>
                <p style="color:#78909c; font-size:13px; margin:0;">No se encontraron pedidos activos con ese criterio.</p>
            </div>
            @endif

            @else
            {{-- Pedido vinculado: versión compacta con desvincular --}}
            <div class="flex-wrap d-flex align-items-center justify-content-between" style="gap:8px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="background:linear-gradient(135deg,#1b5e20,#2e7d32); color:#fff; border-radius:8px; padding:4px 14px; font-size:13px; font-weight:800;">
                        <i class="mr-1 fa fa-link"></i> Vinculado a Pedido
                    </span>
                    <span style="font-weight:700; color:#1b5e20; font-size:14px;">
                        #{{ $pedidoVinculado['id'] }} — {{ $pedidoVinculado['cliente'] }}
                    </span>
                </div>
                <button type="button" wire:click="desvincularPedido"
                        style="background:#fce4ec; color:#b71c1c; border:1px solid #ffcdd2; border-radius:8px; padding:5px 14px; font-size:12px; font-weight:700; cursor:pointer;">
                    <i class="mr-1 fa fa-unlink"></i> Desvincular
                </button>
            </div>
            @endif
        </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ofr-main-ibox">
                    <div class="ibox-title">
                        <div class="d-flex align-items-center justify-content-between">
                            <h3>
                                <i class="mr-2 fa fa-file-text-o"></i>
                                @if($fromFlujo && ($config->codigo ?? '') === 'cotizacion_clientes_a')
                                    Nueva Oferta
                                @else
                                    <span id="titulo_factura">{{ $config->nombre ?? 'Venta' }}</span>
                                @endif
                            </h3>
                            <div class="gap-3 d-flex align-items-center">
                                <input type="text" id="numero_venta" name="numero_venta"
                                    style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); color:#fff; border-radius:8px; padding:4px 10px; max-width:150px; font-size:13px; font-weight:700;" readonly
                                    placeholder="# Oferta">
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <form onkeydown="return event.key != 'Enter';" autocomplete="off" id="crear_venta"
                            name="crear_venta" data-parsley-validate>

                            {{-- Campos ocultos de configuración --}}
                            <input type="hidden" id="restriccion"        name="restriccion"        value="{{ $config->restriccion ?? 1 }}">
                            <input type="hidden" id="tipo_venta_id"      name="tipo_venta_id"      value="{{ $config->tipo_venta_id ?? 2 }}">
                            <input type="hidden" id="tipo_factura_id"    name="tipo_factura_id"    value="{{ $config->id ?? '' }}">
                            <input type="hidden" id="idComprobante"      name="idComprobante"      value="">
                            <input type="hidden" id="codigo_autorizacion" name="codigo_autorizacion" value="">
                            <input type="hidden" id="pedido_vinculado_id" name="pedido_id"          value="{{ $pedidoId ?? '' }}"> {{-- vinculación a pedido --}}

                            {{-- ── SECCIÓN 1: Datos del Cliente ────────────────────────── --}}
                            <div class="ofr-section-header" style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; cursor:pointer; user-select:none;"
                                 onclick="toggleSeccion('sec_cliente', this)">
                                <i class="fa fa-user"></i> 1. Datos del Cliente
                                @if($fromFlujo && ($config->codigo ?? '') === 'cotizacion_clientes_a')
                                <span id="cat_cliente_badge" style="margin-left:auto; background:rgba(26,115,232,.12); color:#1a73e8; border-radius:20px; padding:2px 12px; font-size:11px; font-weight:700;">
                                    <i class="mr-1 fa fa-tag"></i><span id="cat_badge_text">—</span>
                                </span>
                                @endif
                                <i class="ml-2 fa fa-chevron-up" style="font-size:11px;" id="ico_sec_cliente"></i>
                            </div>
                            <div id="sec_cliente">

                            <div class="row" style="row-gap:10px;">
                                {{-- Cliente --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Cliente <span class="req">*</span></label>
                                    <select id="seleccionarCliente" name="seleccionarCliente"
                                        class="form-control form-control-sm" data-parsley-required
                                        onchange="obtenerDatosCliente()"
                                        {{ $pedidoVinculado ? 'disabled' : '' }}>
                                        <option value="" selected disabled>--Seleccionar--</option>
                                    </select>
                                </div>
                                {{-- Nombre --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Nombre del Cliente <span class="req">*</span></label>
                                    <input class="form-control form-control-sm" required type="text" id="nombre_cliente_ventas"
                                        name="nombre_cliente_ventas" data-parsley-required readonly placeholder="(autocompletado)">
                                </div>
                                {{-- RTN --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">RTN <span class="req">*</span></label>
                                    <input class="form-control form-control-sm" type="text" id="rtn_ventas" name="rtn_ventas" readonly placeholder="(autocompletado)">
                                </div>
                                {{-- Vendedor --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Vendedor <span class="req">*</span></label>
                                    <select name="vendedor" id="vendedor" class="form-control form-control-sm" required>
                                        <option value="" selected disabled>--Seleccionar--</option>
                                    </select>
                                </div>
                                {{-- Tipo de pago --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Tipo de Pago <span class="req">*</span></label>
                                    <select class="form-control form-control-sm" name="tipoPagoVenta" id="tipoPagoVenta"
                                        data-parsley-required onchange="validarFechaPago()">
                                    </select>
                                </div>
                                {{-- Descuento --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Descuento % <span class="req">*</span></label>
                                    <input class="form-control form-control-sm" type="number" min="0"
                                        max="{{ $config->max_descuento ?? 50 }}"
                                        value="0" id="porDescuento" name="porDescuento"
                                        onchange="calcularTotalesInicioPagina()" data-parsley-required>
                                </div>
                                {{-- Fecha emisión --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Fecha Emisión <span class="req">*</span></label>
                                    <input class="form-control form-control-sm" type="date" id="fecha_emision"
                                        onchange="sumarDiasCredito()" name="fecha_emision"
                                        value="{{ date('Y-m-d') }}" data-parsley-required>
                                </div>
                                {{-- Fecha vencimiento --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label" style="color:#f57f17;">Vencimiento</label>
                                    <input class="form-control form-control-sm" type="date" id="fecha_vencimiento"
                                        name="fecha_vencimiento" value="" data-parsley-required
                                        min="{{ date('Y-m-d') }}" readonly>
                                </div>
                                {{-- Nota --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Nota</label>
                                    <textarea class="form-control form-control-sm" id="nota_comen" name="nota_comen" rows="1" maxlength="250"></textarea>
                                </div>
                                {{-- Código de exoneración (oculto por defecto) --}}
                                <div class="col-12 col-md-4" id="campo_codigo_exoneracion"
                                    style="{{ ($config->requiere_codigo_exoneracion ?? false) ? '' : 'display:none' }}">
                                    <label class="ofr-label">Código Exoneración <span class="req">*</span></label>
                                    <select id="codigoExoneracion" name="codigoExoneracion" class="form-control form-control-sm">
                                        <option value="" selected disabled>--Seleccione--</option>
                                    </select>
                                </div>
                                {{-- Orden de compra (oculto por defecto) --}}
                                <div class="col-12 col-md-4" id="campo_orden_compra"
                                    style="{{ ($config->requiere_orden_compra ?? false) ? '' : 'display:none' }}">
                                    <label class="ofr-label">Orden de Compra</label>
                                    <select class="form-control form-control-sm" name="ordenCompra" id="ordenCompra">
                                        <option value="" selected disabled>--Seleccionar--</option>
                                    </select>
                                </div>
                            </div>

                            </div>{{-- /sec_cliente --}}

                            {{-- ── SECCIÓN 2: Agregar Producto ─────────────────────────── --}}
                            <div class="ofr-section-header" style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; cursor:pointer; user-select:none;"
                                 onclick="toggleSeccion('sec_producto', this)">
                                <i class="fa fa-plus-circle"></i> 2. Agregar Producto
                                <i class="ml-auto fa fa-chevron-up" style="font-size:11px;" id="ico_sec_producto"></i>
                            </div>
                            <div id="sec_producto">

                            <div class="row">
                                {{-- LEFT: búsqueda + categoría + bodega + boton --}}
                                <div class="col-12 col-md-6">
                                    {{-- Sugerencias del pedido --}}
                                    @if($fromFlujo && ($config->codigo ?? '') === 'cotizacion_clientes_a' && count($productosSugeridos) > 0)
                                    <div style="border:1.5px solid #c8e6c9; border-radius:8px; padding:7px 12px; margin-bottom:10px; background:#f1f8e9; display:flex; align-items:center; justify-content:space-between;">
                                        <span style="font-weight:700; color:#1b5e20; font-size:12px;">
                                            <i class="mr-1 fa fa-list-ul"></i> {{ count($productosSugeridos) }} ítem(s) en el pedido
                                        </span>
                                        <button type="button" data-toggle="modal" data-target="#modalProductosPedido"
                                                style="background:linear-gradient(135deg,#1b5e20,#2e7d32); color:#fff; border:none; border-radius:6px; padding:4px 10px; font-size:11px; font-weight:700; cursor:pointer;">
                                            <i class="mr-1 fa fa-eye"></i> Ver Productos
                                        </button>
                                    </div>
                                    @endif

                                    <label class="ofr-label">Seleccionar Producto <span class="req">*</span></label>
                                    <div class="mb-1 input-group">
                                        <input type="text" id="codigoProductoBuscar" class="form-control form-control-sm"
                                            placeholder="ID o nombre del producto…" autocomplete="off"
                                            onkeydown="if(event.key==='Enter'){buscarPorCodigo(this.value);return false;}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary btn-sm" title="Buscar producto"
                                                onclick="limpiarProducto(); window['abrirBuscador_buscadorProductoUnificado'](document.getElementById('codigoProductoBuscar').value||'')">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small id="productoSeleccionadoLabel" class="text-success font-weight-bold d-block d-none" style="font-size:11px; margin-bottom:6px;"></small>
                                    <select id="seleccionarProducto" name="seleccionarProducto" class="d-none">
                                        <option value="" selected disabled></option>
                                    </select>

                                    <label class="mt-2 ofr-label">Categoría Precio <span class="req">*</span></label>
                                    <select id="categoria_cliente_venta_id" name="categoria_cliente_venta_id"
                                        class="mb-2 form-control form-control-sm" onchange="habilitarBodega()">
                                        <option value="" selected disabled>--Seleccione primero un producto--</option>
                                    </select>

                                    <label class="mt-2 ofr-label">Bodega <span class="req">*</span></label>
                                    <select id="bodega" name="bodega" class="mb-2 form-control form-control-sm" onchange="prueba()">
                                        <option value="" selected disabled>--Seleccione una categoría primero--</option>
                                    </select>

                                    <div id="botonAdd" class="mt-2 d-none">
                                        <button type="button" onclick="agregarProductoCarrito()"
                                            style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                                                   border-radius:8px; padding:5px 14px; font-size:12px; font-weight:700;
                                                   box-shadow:0 2px 8px rgba(230,81,0,.3); cursor:pointer;">
                                            <i class="mr-1 fa fa-shopping-cart"></i> Añadir al Carrito
                                        </button>
                                    </div>
                                </div>

                                {{-- RIGHT: imagen + historial --}}
                                <div class="col-12 col-md-6">
                                    <div class="mb-1 text-center">
                                        <a id="detalleProducto" href="" class="font-bold d-none text-success" target="_blank" style="font-size:12px;">
                                            <i class="mr-1 fa fa-info-circle"></i> Ver Detalles del Producto
                                        </a>
                                    </div>
                                    <div id="carouselProducto" class="carousel slide" data-ride="carousel">
                                        <div id="bloqueImagenes" class="carousel-inner" style="border-radius:10px; overflow:hidden; max-height:220px;"></div>
                                        <a class="carousel-control-prev" href="#carouselProducto" role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                        <a class="carousel-control-next" href="#carouselProducto" role="button" data-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    </div>
                                    <div id="historialPreciosPanel" class="mt-2 d-none">
                                        <div style="font-size:11px; font-weight:700; color:#546e7a; text-transform:uppercase; letter-spacing:.3px; margin-bottom:4px;">
                                            <i class="mr-1 fa fa-history text-info"></i> Últimas 5 ventas a este cliente
                                        </div>
                                        <div id="historialPreciosCuerpo"><p class="text-muted small">Cargando...</p></div>
                                    </div>
                                </div>
                            </div>

                            <hr style="border-color:#e0f2f1; margin:16px 0 14px;">
                            <div style="font-size:11px; font-weight:700; color:#00695c; text-transform:uppercase; letter-spacing:.5px; margin-bottom:10px;">
                                <i class="mr-1 fa fa-list"></i> Productos en el carrito
                            </div>

                            {{-- ── Lista productos ────────────────────────────────────────── --}}
                            <div id="divProductos">
                                <div id="carritoVacio" class="py-3 text-center" style="color:#b2dfdb; font-size:12px;">
                                    <i class="mb-1 fa fa-shopping-cart fa-2x d-block"></i> Sin productos en el carrito
                                </div>
                                <div id="carritoTablaWrapper" class="d-none table-responsive" style="max-height:400px; overflow-y:auto;">
                                    <table class="table mb-0 table-sm table-bordered" style="font-size:12px; min-width:900px;">
                                        <thead style="background:linear-gradient(135deg,#e8f5e9,#e0f7fa); position:sticky; top:0; z-index:1;">
                                            <tr style="color:#00695c; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:.3px;">
                                                <th style="width:36px;"></th>
                                                <th style="min-width:150px;">Producto</th>
                                                <th style="min-width:100px;">Bodega</th>
                                                <th style="min-width:110px;">Precio Opc.</th>
                                                <th style="min-width:90px;">P. Unitario</th>
                                                <th style="min-width:70px;">Cantidad</th>
                                                <th style="min-width:90px;">Unidad</th>
                                                <th style="min-width:90px;">Subtotal</th>
                                                <th style="min-width:80px;">ISV</th>
                                                <th style="min-width:90px; background:linear-gradient(135deg,#e65100,#f9a826); color:#fff;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="carritoTbody"></tbody>
                                    </table>
                                </div>
                            </div>

                            </div>{{-- /sec_producto --}}

                            {{-- ── SECCIÓN 3: Totales ───────────────────────────────────── --}}
                            <div class="ofr-section-header" style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; cursor:pointer; user-select:none;"
                                 onclick="toggleSeccion('sec_totales', this)">
                                <i class="fa fa-calculator"></i> 3. Totales
                                <i class="ml-auto fa fa-chevron-up" style="font-size:11px;" id="ico_sec_totales"></i>
                            </div>
                            <div id="sec_totales">

                            <div style="background:#fff; border:2px solid #a5d6a7; border-radius:16px; overflow:hidden; margin-bottom:24px; box-shadow:0 4px 20px rgba(27,94,32,.08);">
                                {{-- Header del bloque totales --}}
                                <div style="background:linear-gradient(135deg,#e65100,#f9a826); padding:12px 22px; display:flex; align-items:center; gap:8px;">
                                    <i class="fa fa-calculator" style="color:rgba(255,255,255,.8);"></i>
                                    <span style="color:#fff; font-weight:700; font-size:13px; letter-spacing:.4px; text-transform:uppercase;">Resumen de Totales</span>
                                </div>
                                {{-- Cuerpo --}}
                                <div style="padding:20px 24px; background:#fafffe;">
                                    <div class="row">
                                        <div class="mb-3 col-6 col-md-4 col-lg-2">
                                            <div style="font-size:10px; color:#78909c; font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px;">Descuento</div>
                                            <input type="text" id="descuentoMostrar" name="descuentoMostrar" placeholder="L. 0.00"
                                                   data-parsley-required autocomplete="off" readonly
                                                   style="border:none; background:transparent; font-size:17px; font-weight:800; color:#e65100; padding:0; width:100%; outline:none;">
                                            <input type="hidden" value="0" id="porDescuentoCalculado" name="porDescuentoCalculado">
                                        </div>
                                        <div class="mb-3 col-6 col-md-4 col-lg-2">
                                            <div style="font-size:10px; color:#78909c; font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px;">Sub Total</div>
                                            <input type="text" id="subTotalGeneralMostrar" placeholder="L. 0.00" readonly autocomplete="off"
                                                   style="border:none; background:transparent; font-size:17px; font-weight:800; color:#2e7d32; padding:0; width:100%; outline:none;">
                                            <input id="subTotalGeneral" name="subTotalGeneral" type="hidden" value="" required>
                                        </div>
                                        <div class="mb-3 col-6 col-md-4 col-lg-2">
                                            <div style="font-size:10px; color:#78909c; font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px;">Grabado</div>
                                            <input type="text" id="subTotalGeneralGrabadoMostrar" placeholder="L. 0.00" readonly autocomplete="off"
                                                   style="border:none; background:transparent; font-size:17px; font-weight:800; color:#1565c0; padding:0; width:100%; outline:none;">
                                            <input id="subTotalGeneralGrabado" name="subTotalGeneralGrabado" type="hidden" value="" required>
                                        </div>
                                        <div class="mb-3 col-6 col-md-4 col-lg-2">
                                            <div style="font-size:10px; color:#78909c; font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px;">Excento</div>
                                            <input type="text" id="subTotalGeneralExcentoMostrar" placeholder="L. 0.00" readonly autocomplete="off"
                                                   style="border:none; background:transparent; font-size:17px; font-weight:800; color:#6a1b9a; padding:0; width:100%; outline:none;">
                                            <input id="subTotalGeneralExcento" name="subTotalGeneralExcento" type="hidden" value="" required>
                                        </div>
                                        <div class="mb-3 col-6 col-md-4 col-lg-2" id="fila_isv" style="{{ ($config->aplica_isv ?? true) ? '' : 'display:none' }}">
                                            <div style="font-size:10px; color:#78909c; font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px;">ISV</div>
                                            <input type="text" id="isvGeneralMostrar" placeholder="L. 0.00" readonly autocomplete="off"
                                                   style="border:none; background:transparent; font-size:17px; font-weight:800; color:#b71c1c; padding:0; width:100%; outline:none;">
                                            <input id="isvGeneral" name="isvGeneral" type="hidden" value="" required>
                                        </div>
                                        <div class="mb-0 col-12 col-md-4 col-lg-2 d-flex align-items-end">
                                            <div style="background:linear-gradient(135deg,#e65100,#f9a826); border-radius:12px; padding:14px 18px; width:100%; text-align:center; box-shadow:0 4px 14px rgba(230,81,0,.3);">
                                                <div style="font-size:10px; color:rgba(255,255,255,.75); font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px;">TOTAL</div>
                                                <input type="text" id="totalGeneralMostrar" placeholder="L. 0.00" readonly autocomplete="off"
                                                       style="border:none; background:transparent; font-size:22px; font-weight:900; color:#fff; padding:0; text-align:center; width:100%; outline:none;">
                                                <input id="totalGeneral" name="totalGeneral" type="hidden" value="" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            </div>{{-- /sec_totales --}}

                            {{-- ── Botón principal ─────────────────────────────────────── --}}
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <button id="btn_venta_coorporativa"
                                            style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                                                   border-radius:12px; padding:14px 32px; font-size:15px; font-weight:800;
                                                   box-shadow:0 4px 18px rgba(230,81,0,.35); width:100%; cursor:pointer;">
                                        @if($fromFlujo && ($config->codigo ?? '') === 'cotizacion_clientes_a')
                                            <i class="mr-2 fa fa-save"></i> Guardar Oferta
                                        @else
                                            <i class="mr-2 fa fa-check-circle"></i> Realizar Venta
                                        @endif
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL: Solicitar código de autorización                        --}}
        {{-- (Visible solo para tipos que requieren código)                 --}}
        {{-- ============================================================== --}}
        <div class="modal fade" id="modal_solicitar_codigo" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Solicitar código</h3>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <button class="btn btn-primary btn-large-dim" type="button" onclick="solicitarCodigo()">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                        <div id="div_imprimir" class="mt-2 text-center d-none">
                            <a id="btn_imprimir" target="_blank" class="text-white btn add-btn btn-success">
                                <i class="fa-solid fa-file-invoice"></i> Imprimir Factura
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="history.back()">Salir</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL: Ingresar código de autorización --}}
        <div class="modal fade" id="modalPermiso" data-backdrop="static" tabindex="1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Ingresar código</h3>
                    </div>
                    <div class="modal-body">
                        <form id="verificarCodigoForm" autocomplete="off" data-parsley-validate>
                            <label for="codigo" class="col-form-label focus-label">
                                Código de autorización:<span class="text-danger">*</span>
                            </label>
                            <input class="form-control" required type="text" id="codigo" name="codigo" data-parsley-required>
                        </form>
                        <span id="mensajeCodigo" class="text-danger d-none">Código incorrecto</span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="history.back()">Salir</button>
                        <button type="submit" form="verificarCodigoForm" class="btn btn-primary">Verificar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL: Detalle del Pedido --}}
        <div class="modal fade" id="modalDetallePedido" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content" style="border-radius:14px; overflow:hidden;">
                    <div class="modal-header" style="background:linear-gradient(135deg,#e65100,#f9a826); border:none; padding:16px 24px;">
                        <h5 class="modal-title" style="color:#fff; font-weight:700; margin:0;">
                            <i class="mr-2 fa fa-clipboard-list"></i>
                            Detalle del Pedido
                            @if($pedidoDetalle)
                                <span style="opacity:.8;">#{{ $pedidoDetalle['pedido']['id'] }}</span>
                            @endif
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding:24px;">
                        @if($pedidoDetalle)
                        @php $ped = $pedidoDetalle['pedido']; @endphp
                        {{-- Info del pedido --}}
                        <div class="mb-3 row">
                            <div class="col-md-6">
                                <div style="font-size:11px; color:#78909c; font-weight:700; text-transform:uppercase;">Cliente</div>
                                <div style="font-weight:700; color:#2c3e50;">{{ $ped['cliente'] }}</div>
                            </div>
                            <div class="col-md-3">
                                <div style="font-size:11px; color:#78909c; font-weight:700; text-transform:uppercase;">RTN</div>
                                <div style="color:#546e7a;">{{ $ped['rtn'] ?: '\u2014' }}</div>
                            </div>
                            <div class="col-md-3">
                                <div style="font-size:11px; color:#78909c; font-weight:700; text-transform:uppercase;">Estado</div>
                                <span style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border-radius:20px; padding:2px 10px; font-size:11px; font-weight:700;">{{ ucfirst(str_replace('_',' ',$ped['estado'])) }}</span>
                            </div>
                        </div>
                        @if($ped['observaciones'])
                        <div class="p-2 mb-3" style="background:#fff8e1; border-radius:8px; font-size:12px; color:#7b6000;">
                            <i class="mr-1 fa fa-comment"></i> {{ $ped['observaciones'] }}
                        </div>
                        @endif
                        {{-- Tabla de productos del pedido --}}
                        <div style="font-size:11px; font-weight:700; color:#546e7a; text-transform:uppercase; margin-bottom:8px;">Productos solicitados</div>
                        @if(count($pedidoDetalle['productos']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm" style="font-size:13px;">
                                <thead style="background:linear-gradient(135deg,#e65100,#f9a826);">
                                    <tr style="color:#fff; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:.3px;">
                                        <th style="border:none;">Producto</th>
                                        <th style="width:80px; text-align:center; border:none;">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody id="pdTbody">
                                    @foreach($pedidoDetalle['productos'] as $pd)
                                    @php $pd = (array)$pd; @endphp
                                    <tr data-pd-idx="{{ $loop->index }}" style="{{ $loop->index >= 5 ? 'display:none;' : '' }}">
                                        <td style="font-weight:600; vertical-align:middle;">{{ $pd['nombre_producto'] }}</td>
                                        <td style="text-align:center; vertical-align:middle;"><span style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border-radius:20px; padding:2px 12px; font-weight:700;">{{ intval($pd['cantidad']) }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- Paginación detalle --}}
                        @if(count($pedidoDetalle['productos']) > 5)
                        <div id="pdPaginacion" class="mt-2 d-flex align-items-center justify-content-center" style="gap:10px;">
                            <button onclick="pdChangePage(-1)" id="pdPrev" style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none; border-radius:8px; padding:4px 14px; font-size:12px; cursor:pointer; font-weight:700;">&#8592; Anterior</button>
                            <span id="pdPageInfo" style="font-size:12px; font-weight:700; color:#546e7a;"></span>
                            <button onclick="pdChangePage(1)" id="pdNext" style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none; border-radius:8px; padding:4px 14px; font-size:12px; cursor:pointer; font-weight:700;">Siguiente &#8594;</button>
                        </div>
                        @endif
                        @else
                        <p class="text-center text-muted">Sin productos registrados.</p>
                        @endif
                        @else
                        <div class="py-4 text-center">
                            <i class="fa fa-spinner fa-spin fa-2x" style="color:#00897b;"></i>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if($pedidoDetalle)
                        <button type="button"
                                wire:click="seleccionarPedido({{ $pedidoDetalle['pedido']['id'] }})" data-dismiss="modal"
                                style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none; border-radius:8px; padding:8px 20px; font-weight:700; cursor:pointer;">
                            <i class="mr-1 fa fa-link"></i> Vincular este Pedido
                        </button>
                        @endif
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL: Éxito guardado oferta – 4 opciones --}}
        <div class="modal fade" id="modalExitoOferta" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:500px;">
                <div class="modal-content" style="border-radius:16px; overflow:hidden;">
                    <div class="modal-header" style="background:linear-gradient(135deg,#e65100,#f9a826); border:none; padding:18px 24px;">
                        <h5 class="modal-title" style="color:#fff; font-weight:800; margin:0; font-size:15px;">
                            <i class="mr-2 fa fa-check-circle"></i> Oferta guardada exitosamente
                        </h5>
                    </div>
                    <div class="modal-body" style="padding:24px;">
                        <p style="color:#546e7a; font-size:13px; margin-bottom:20px; text-align:center;">¿Qué desea hacer ahora?</p>
                        <div class="d-flex flex-column" style="gap:12px;">
                            <button onclick="ofertaAccion('nueva')" class="btn btn-block" style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; font-weight:700; border:none; border-radius:10px; padding:12px 20px; text-align:left;">
                                <i class="mr-2 fa fa-plus-circle"></i> Agregar nueva oferta
                                <div style="font-size:11px; font-weight:400; opacity:.85;">Limpiar productos y crear otra oferta para el mismo pedido</div>
                            </button>
                            <button onclick="ofertaAccion('ganadora')" class="btn btn-block" style="background:#fff; color:#e65100; font-weight:700; border:2px solid #f9a826; border-radius:10px; padding:12px 20px; text-align:left;">
                                <i class="mr-2 fa fa-trophy"></i> Seleccionar oferta ganadora
                                <div style="font-size:11px; font-weight:400; color:#546e7a;">Ver todas las ofertas del pedido y elegir la ganadora</div>
                            </button>
                            <button onclick="ofertaAccion('prefacturar')" class="btn btn-block" style="background:#fff3e0; color:#bf360c; font-weight:700; border:none; border-radius:10px; padding:12px 20px; text-align:left;">
                                <i class="mr-2 fa fa-file-text-o"></i> Prefacturar esta oferta
                                <div style="font-size:11px; font-weight:400; color:#546e7a;">Esta oferta se marca como ganadora y se manda a prefactura</div>
                            </button>
                            <button onclick="ofertaAccion('imprimir')" class="btn btn-block" style="background:#fafafa; color:#546e7a; font-weight:700; border:1px solid #e0e0e0; border-radius:10px; padding:12px 20px; text-align:left;">
                                <i class="mr-2 fa fa-print"></i> Imprimir oferta
                                <div style="font-size:11px; font-weight:400; color:#78909c;">Abrir PDF de la oferta recién guardada</div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL: Seleccionar oferta ganadora --}}
        <div class="modal fade" id="modalOfertasGanadoras" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:560px;">
                <div class="modal-content" style="border-radius:16px; overflow:hidden;">
                    <div class="modal-header" style="background:linear-gradient(135deg,#e65100,#f9a826); border:none; padding:16px 24px;">
                        <h5 class="modal-title" style="color:#fff; font-weight:800; margin:0; font-size:14px;">
                            <i class="mr-2 fa fa-trophy"></i> Seleccionar oferta ganadora
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;"><span>&times;</span></button>
                    </div>
<<<<<<< HEAD
                    <div class="modal-body" style="padding:16px 18px; overflow:hidden; display:flex; flex-direction:column; flex:1; min-height:0;">
                        <div id="ogLoading" class="py-3 text-center" style="display:none;">
                            <i class="fa fa-spinner fa-spin fa-2x" style="color:#e65100;"></i>
                        </div>
                        <div id="ogLista"></div>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid #f0f0f0; padding:10px 18px; flex-shrink:0; background:#fafafa;">
                        <button type="button" id="ogBtnVolver"
                                style="background:#fff; color:#e65100; border:2px solid #f9a826; border-radius:9px;
                                       padding:7px 22px; font-size:13px; font-weight:700; cursor:pointer; transition:background .15s;"
                                onmouseover="this.style.background='#fff3e0';" onmouseout="this.style.background='#fff';">
                            <i class="mr-1 fa fa-arrow-left"></i> Volver
                        </button>
                    </div>
=======
                    <div class="modal-body" style="padding:20px;">
                        <div id="ogLoading" class="text-center py-3" style="display:none;"><i class="fa fa-spinner fa-spin fa-2x" style="color:#e65100;"></i></div>
                        <div id="ogLista"></div>
                    </div>
>>>>>>> parent of bdeaa912 (Merge branch 'antes-de-cagarla' into antes-cagarla-yef)
                </div>
            </div>
        </div>

        {{-- MODAL: Productos del Pedido con sugerencias --}}
        <div class="modal fade" id="modalProductosPedido" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content" style="border-radius:14px; overflow:hidden;">
                    <div class="modal-header" style="background:linear-gradient(135deg,#e65100,#f9a826); border:none; padding:14px 24px;">
                        <h5 class="modal-title" style="color:#fff; font-weight:700; margin:0; font-size:14px;">
                            <i class="mr-2 fa fa-list-ul"></i> Productos del Pedido
                            @if(count($productosSugeridos) > 0)
                            <span style="background:rgba(255,255,255,.2); border-radius:20px; padding:1px 10px; font-size:12px; margin-left:6px;">{{ count($productosSugeridos) }}</span>
                            @endif
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" style="padding:16px 20px;">
                        <p style="font-size:11px; color:#78909c; margin-bottom:12px;">
                            <i class="mr-1 fa fa-info-circle text-info"></i>
                            Selecciona un producto sugerido para pre-cargarlo. Luego elige la bodega y categoría.
                        </p>
                        @if(count($productosSugeridos) > 0)
                        <div id="ppPagItems">
                        @foreach($productosSugeridos as $idx => $item)
                        <div class="pp-item" data-idx="{{ $idx }}" style="{{ $idx >= 5 ? 'display:none;' : '' }}background:#f9f9f9; border:1px solid #e0e0e0; border-radius:10px; padding:12px 16px; margin-bottom:10px;">
                            <div class="flex-wrap d-flex align-items-center" style="gap:8px; margin-bottom:6px;">
                                <span style="font-weight:700; color:#2c3e50; font-size:13px;">{{ $item['nombre_pedido'] }}</span>
                                <span style="background:#e8f5e9; color:#2e7d32; border-radius:12px; padding:1px 9px; font-size:11px; font-weight:700;">x{{ $item['cantidad'] }}</span>
                            </div>
                            @if(count($item['similares']) > 0)
                            <div style="font-size:10px; color:#546e7a; font-weight:700; text-transform:uppercase; letter-spacing:.3px; margin-bottom:5px;">Similares en catálogo:</div>
                            <div class="flex-wrap d-flex" style="gap:6px;">
                                @foreach($item['similares'] as $sim)
                                @php $s = (array)$sim; @endphp
                                <button type="button"
                                    onclick="preseleccionarProductoSugerido({{ $s['id'] }}, '{{ addslashes($s['nombre']) }}');"
                                    style="background:#e8f5e9; color:#1b5e20; border:1px solid #a5d6a7; border-radius:7px; padding:6px 12px; font-size:12px; font-weight:600; cursor:pointer;"
                                    onmouseover="this.style.background='#c8e6c9';" onmouseout="this.style.background='#e8f5e9';">
                                    <i class="mr-1 fa fa-plus-circle"></i>{{ Str::limit($s['nombre'], 38) }}
                                </button>
                                @endforeach
                            </div>
                            @else
                            <div style="font-size:11px; color:#90a4ae;"><i class="mr-1 fa fa-exclamation-triangle"></i>Sin coincidencias en catálogo.</div>
                            @endif
                        </div>
                        @endforeach
                        </div>
                        {{-- Paginación --}}
                        @if(count($productosSugeridos) > 5)
                        <div class="mt-2 d-flex align-items-center justify-content-between" id="ppPagNav">
                            <button type="button" onclick="ppChangePage(-1)"
                                style="background:#f5f5f5; border:1px solid #e0e0e0; border-radius:7px; padding:5px 14px; font-size:12px; font-weight:600; cursor:pointer;" id="ppBtnPrev" disabled>
                                <i class="mr-1 fa fa-chevron-left"></i> Anterior
                            </button>
                            <span id="ppPageInfo" style="font-size:12px; color:#546e7a; font-weight:700;"></span>
                            <button type="button" onclick="ppChangePage(1)"
                                style="background:#1b5e20; color:#fff; border:none; border-radius:7px; padding:5px 14px; font-size:12px; font-weight:600; cursor:pointer;" id="ppBtnNext">
                                Siguiente <i class="ml-1 fa fa-chevron-right"></i>
                            </button>
                        </div>
                        @endif
                        @else
                        <p class="text-center text-muted">No hay productos sugeridos.</p>
                        @endif
                    </div>
                    <div class="modal-footer" style="padding:10px 20px;">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Buscador de producto reutilizable --}}
    <x-buscador-producto id-modal="buscadorProductoUnificado" callback="alSeleccionarProducto" />

    @push('scripts')
    <script>
    // ================================================================
    // CONFIGURACIÓN DEL TIPO DE FACTURA (desde PHP)
    // ================================================================
    var tipoFacturaConfig = @json($config);

    // Mapa de URLs por código de tipo de factura
    var urlsPorTipo = {
        estatal: {
            guardar: '/ventas/estatal/guardar',
            listar_clientes: '/estatal/lista/clientes',
            datos_cliente: '/estatal/datos/cliente',
            datos_producto: '/estatal/datos/producto',
            tipo_pago: '/estatal/tipo/pago',
            bodegas: '/estatal/listar/bodegas/{idProducto}',
            imprimir: '/estatal/factura/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: '/ventas/numero/orden',
            codigos_exoneracion: null
        },
        sin_restriccion_gobierno: {
            guardar: '/ventas/estatal/guardar',
            listar_clientes: '/estatal/lista/clientes',
            datos_cliente: '/estatal/datos/cliente',
            datos_producto: '/estatal/datos/producto',
            tipo_pago: '/estatal/tipo/pago',
            bodegas: '/estatal/listar/bodegas/{idProducto}',
            imprimir: '/estatal/factura/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: null,
            codigos_exoneracion: null
        },
        corporativa: {
            guardar: '/ventas/corporativo/guardar',
            listar_clientes: '/ventas/lista/clientes',
            datos_cliente: '/ventas/datos/cliente',
            datos_producto: '/ventas/datos/producto',
            tipo_pago: '/ventas/tipo/pago',
            bodegas: '/ventas/listar/bodegas/{idProducto}',
            imprimir: '/factura/cooporativo/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: '/ventas/numero/orden',
            codigos_exoneracion: null
        },
        sin_restriccion_precio: {
            guardar: '/ventas/corporativo/guardar',
            listar_clientes: '/ventas/lista/clientes',
            datos_cliente: '/ventas/datos/cliente',
            datos_producto: '/ventas/datos/producto',
            tipo_pago: '/ventas/tipo/pago',
            bodegas: '/ventas/listar/bodegas/{idProducto}',
            imprimir: '/factura/cooporativo/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: null,
            codigos_exoneracion: null
        },
        exoneradas: {
            guardar: '/exonerado/venta/guardar',
            listar_clientes: '/exonerado/lista/clientes',
            datos_cliente: '/ventas/datos/cliente',
            datos_producto: '/estatal/datos/producto',
            tipo_pago: '/estatal/tipo/pago',
            bodegas: '/estatal/listar/bodegas/{idProducto}',
            imprimir: '/exonerado/factura/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: null,
            codigos_exoneracion: '/exonerado/listar/codigos'
        },
        cotizacion_clientes_a: {
            guardar: '/guardar/cotizacion',
            listar_clientes: '/cotizacion/clientes',
            datos_cliente: '/estatal/datos/cliente',
            datos_producto: '/estatal/datos/producto',
            tipo_pago: '/estatal/tipo/pago',
            bodegas: '/estatal/listar/bodegas/{idProducto}',
            imprimir: '/cotizacion/imprimir/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: null,
            codigos_exoneracion: null
        }
    };

    // Obtener URLs del tipo actual
    var codigoActual = tipoFacturaConfig ? tipoFacturaConfig.codigo : 'estatal';
    var urls = urlsPorTipo[codigoActual] || urlsPorTipo['estatal'];

    var numeroInputs = 0;
    var arregloIdInputs = [];
    var retencionEstado = false;
    var diasCredito = 0;
    var idAutorizacion = 0;
    var idFactura = 0;
    var public_path = "{{ asset('catalogo/') }}";

    // ================================================================
    // INICIALIZACIÓN
    // ================================================================
    window.onload = function() {
        inicializarFormulario();
    };

    function inicializarFormulario() {
        obtenerTipoPago();
        inicializarSelect2();

        // Si requiere código de autorización, mostrar modal
        if (tipoFacturaConfig && tipoFacturaConfig.requiere_codigo_autorizacion) {
            $('#modal_solicitar_codigo').modal('show');
        }
    }

    function inicializarSelect2() {
        var urlClientes = urls.listar_clientes;
        var urlVendedores = urls.vendedores;

        // Destruir select2 existentes si los hay
        if ($('#vendedor').hasClass('select2-hidden-accessible')) {
            $('#vendedor').select2('destroy');
        }
        if ($('#seleccionarCliente').hasClass('select2-hidden-accessible')) {
            $('#seleccionarCliente').select2('destroy');
        }

        $('#vendedor').select2({
            ajax: {
                url: urlVendedores,
                data: function(params) {
                    return { search: params.term, type: 'public', page: params.page || 1 };
                }
            }
        });

        $('#seleccionarCliente').select2({
            ajax: {
                url: urlClientes,
                data: function(params) {
                    return { search: params.term, type: 'public', page: params.page || 1 };
                }
            }
        });

        // ── Pre-seleccionar vendedor = usuario actual ──────────────────────
        @if(!empty($vendedorDefault))
        (function() {
            var opt = new Option(
                '{{ addslashes($vendedorDefault['name']) }}',
                '{{ $vendedorDefault['id'] }}',
                true, true
            );
            $('#vendedor').append(opt).trigger('change');
        })();
        @endif

        // ── Pre-seleccionar cliente si viene de un pedido ─────────────────
        @if($clientePedido)
        (function() {
            var opt = new Option(
                '{{ addslashes($clientePedido['nombre']) }}',
                '{{ $clientePedido['id'] }}',
                true, true
            );
            $('#seleccionarCliente').append(opt).trigger('change');
            setTimeout(function() { obtenerDatosCliente(); }, 300);
        })();
        @endif
    }

    // ================================================================
    // PRODUCTO SUGERIDO DESDE PEDIDO → Pre-selecciona en el selector
    // ================================================================
    function preseleccionarProductoSugerido(id, nombre) {
        // Cierra el modal de productos del pedido
        $('#modalProductosPedido').modal('hide');

        // Rellena el select oculto igual que alSeleccionarProducto()
        var select = document.getElementById('seleccionarProducto');
        select.innerHTML = '<option value="' + id + '" selected>' + nombre + '</option>';
        document.getElementById('codigoProductoBuscar').value = nombre;
        var label = document.getElementById('productoSeleccionadoLabel');
        label.textContent = '\u2713 ' + nombre;
        label.classList.remove('d-none');

        // Cargar categorías e imágenes (cargarCategoriasProducto llama obtenerImagenes internamente)
        cargarCategoriasProducto();
    }

    // ================================================================
    // SECCIONES COLAPSABLES
    // ================================================================
    function toggleSeccion(id, headerEl) {
        var el = document.getElementById(id);
        if (!el) return;
        var isOpen = el.style.display !== 'none';
        el.style.display = isOpen ? 'none' : '';
        // Girar ícono chevron
        var icoId = 'ico_' + id;
        var ico = document.getElementById(icoId);
        if (ico) {
            if (isOpen) {
                ico.classList.remove('fa-chevron-up');
                ico.classList.add('fa-chevron-down');
            } else {
                ico.classList.remove('fa-chevron-down');
                ico.classList.add('fa-chevron-up');
            }
        }
    }

    // ================================================================
    // PAGINACIÓN MODAL PRODUCTOS PEDIDO
    // ================================================================
    var ppCurrentPage = 0;
    var ppItemsPerPage = 5;

    function ppChangePage(dir) {
        var items = document.querySelectorAll('#ppPagItems .pp-item');
        var total = items.length;
        var totalPages = Math.ceil(total / ppItemsPerPage);
        ppCurrentPage = Math.max(0, Math.min(ppCurrentPage + dir, totalPages - 1));
        var from = ppCurrentPage * ppItemsPerPage;
        var to   = from + ppItemsPerPage;
        items.forEach(function(el, i) { el.style.display = (i >= from && i < to) ? '' : 'none'; });
        var prev = document.getElementById('ppBtnPrev');
        var next = document.getElementById('ppBtnNext');
        var info = document.getElementById('ppPageInfo');
        if (prev) prev.disabled = ppCurrentPage === 0;
        if (next) next.disabled = ppCurrentPage >= totalPages - 1;
        if (next) next.style.opacity = ppCurrentPage >= totalPages - 1 ? '.5' : '1';
        if (info) info.textContent = 'Página ' + (ppCurrentPage + 1) + ' / ' + totalPages;
    }

    // Inicializar paginación cuando el modal se abre
    $('#modalProductosPedido').on('show.bs.modal', function() {
        ppCurrentPage = 0;
        ppChangePage(0);
    });

    // ================================================================
    // PAGINACIÓN MODAL DETALLE PEDIDO
    // ================================================================
    var pdCurrentPage = 0;
    var pdItemsPerPage = 5;

    function pdChangePage(dir) {
        var rows = document.querySelectorAll('#pdTbody tr[data-pd-idx]');
        var total = rows.length;
        if (total === 0) return;
        var totalPages = Math.ceil(total / pdItemsPerPage);
        pdCurrentPage = Math.max(0, Math.min(pdCurrentPage + dir, totalPages - 1));
        var from = pdCurrentPage * pdItemsPerPage;
        var to   = from + pdItemsPerPage;
        rows.forEach(function(el, i) { el.style.display = (i >= from && i < to) ? '' : 'none'; });
        var prev = document.getElementById('pdPrev');
        var next = document.getElementById('pdNext');
        var info = document.getElementById('pdPageInfo');
        if (prev) prev.disabled = pdCurrentPage === 0;
        if (prev) prev.style.opacity = pdCurrentPage === 0 ? '.5' : '1';
        if (next) next.disabled = pdCurrentPage >= totalPages - 1;
        if (next) next.style.opacity = pdCurrentPage >= totalPages - 1 ? '.5' : '1';
        if (info) info.textContent = 'Página ' + (pdCurrentPage + 1) + ' / ' + totalPages;
    }

    $('#modalDetallePedido').on('show.bs.modal', function() {
        pdCurrentPage = 0;
        pdChangePage(0);
    });

    function cambiarTipoFactura(rutaMenu) {
        window.location.href = '/' + rutaMenu;
    }

    // ================================================================
    // BROWSER EVENTS: Pedido vinculado / desvinculado (Livewire → JS)
    // ================================================================
    window.addEventListener('pedido-seleccionado', function(e) {
        var d = e.detail;
        // Re-habilitar Select2 de cliente (puede estar disabled en re-render)
        var selC = document.getElementById('seleccionarCliente');
        if (selC) selC.removeAttribute('disabled');

        // Reinicializar Select2 si es necesario
        if (!$('#seleccionarCliente').hasClass('select2-hidden-accessible')) {
            inicializarSelect2();
        }

        // Pre-seleccionar cliente
        if (d.clienteId) {
            $('#seleccionarCliente').empty();
            var optC = new Option(d.clienteNombre, d.clienteId, true, true);
            $('#seleccionarCliente').append(optC).trigger('change');
            setTimeout(function() { obtenerDatosCliente(); }, 300);
        }

        // Pre-seleccionar vendedor por defecto
        if (d.vendedorId) {
            $('#vendedor').empty();
            var optV = new Option(d.vendedorNombre, d.vendedorId, true, true);
            $('#vendedor').append(optV).trigger('change');
        }

        // Bloquear cliente cuando hay pedido vinculado
        $('#seleccionarCliente').prop('disabled', true);
    });

    window.addEventListener('pedido-desvinculado', function(e) {
        var d = e.detail;
        // Habilitar cliente nuevamente
        $('#seleccionarCliente').prop('disabled', false);
        // Limpiar cliente
        $('#seleccionarCliente').empty().append('<option value="" selected disabled>--Seleccionar un cliente--</option>').trigger('change');
        document.getElementById('nombre_cliente_ventas').value = '';
        document.getElementById('rtn_ventas').value = '';

        // Restaurar vendedor por defecto
        if (d.vendedorId) {
            $('#vendedor').empty();
            var optV = new Option(d.vendedorNombre, d.vendedorId, true, true);
            $('#vendedor').append(optV).trigger('change');
        }

        // Actualizar badge categoría
        $('#cat_badge_text').text('\u2014');
    });

    window.addEventListener('mostrar-modal-detalle-pedido', function() {
        $('#modalDetallePedido').modal('show');
    });

    // ================================================================
    // CÓDIGO DE AUTORIZACIÓN (sin restricción gobierno / sin restricción precio)
    // ================================================================
    function solicitarCodigo() {
        axios.get('/ventas/solicitud/codigo')
            .then(response => {
                $("#modal_solicitar_codigo").removeClass("fade").modal("hide");
                $("#modalPermiso").modal("show").addClass("fade");
            })
            .catch(err => {
                console.log(err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al solicitar código' });
            });
    }

    $(document).on('submit', '#verificarCodigoForm', function(event) {
        event.preventDefault();
        ocultarModalVerificar();
    });

    function ocultarModalVerificar() {
        let codigo = document.getElementById('codigo').value;
        axios.post('/ventas/verificar/codigo', { codigo: codigo })
            .then(response => {
                let data = response.data;
                if (data.estado == 1) {
                    $("#modalPermiso").removeClass("fade").modal("hide");
                    document.getElementById('mensajeCodigo').classList.add('d-none');
                    document.getElementById('codigo_autorizacion').value = data.idAutorizacion;
                } else {
                    document.getElementById('mensajeCodigo').classList.remove('d-none');
                    document.getElementById('codigo_autorizacion').value = '';
                }
            })
            .catch(err => { console.log(err); });
    }

    function desactivarCodigo() {
        if (!idAutorizacion) return;
        axios.post('/ventas/autorizacion/desactivar', { idAutorizacion: idAutorizacion })
            .then(response => {
                let element = document.getElementById("div_imprimir");
                element.classList.remove("d-none");
                $("#modal_solicitar_codigo").modal("show").addClass("fade");
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Error al desactivar código' });
            });
    }

    // ================================================================
    // BUSCADOR DE PRODUCTO
    // ================================================================
    function limpiarProducto() {
        document.getElementById('seleccionarProducto').innerHTML = '<option value="" selected disabled></option>';
        document.getElementById('codigoProductoBuscar').value = '';
        var lbl = document.getElementById('productoSeleccionadoLabel');
        lbl.classList.add('d-none'); lbl.textContent = '';
        document.getElementById('historialPreciosPanel').classList.add('d-none');
    }

    function alSeleccionarProducto(producto) {
        var select = document.getElementById('seleccionarProducto');
        select.innerHTML = '<option value="' + producto.id + '" selected>' + producto.nombre + '</option>';
        document.getElementById('codigoProductoBuscar').value = producto.nombre;
        var label = document.getElementById('productoSeleccionadoLabel');
        label.textContent = '✓ ' + producto.nombre + ' (ID: ' + producto.id + ')';
        label.classList.remove('d-none');
        cargarCategoriasProducto();
    }

    function buscarPorCodigo(cod) {
        cod = String(cod).trim();
        if (!cod) { window['abrirBuscador_buscadorProductoUnificado'](''); return; }
        axios.get('/productos/buscar', { params: { q: cod, page: 1 } })
            .then(function(r) {
                var items = r.data.data;
                var exact = items.find(function(p) { return String(p.id) === cod; });
                if (exact) { alSeleccionarProducto(exact); }
                else if (items.length === 1) { alSeleccionarProducto(items[0]); }
                else { window['abrirBuscador_buscadorProductoUnificado'](cod); }
            });
    }

    // ================================================================
    // CLIENTE
    // ================================================================
    function obtenerDatosCliente() {
        let idCliente = document.getElementById("seleccionarCliente").value;
        if (!idCliente) return; // Evitar error al desvincular pedido
        var urlDatosCliente = urls.datos_cliente;

        axios.post(urlDatosCliente, { id: idCliente })
            .then(response => {
                let data = response.data.datos;

                if (data.id == 1) {
                    document.getElementById("nombre_cliente_ventas").readOnly = false;
                    document.getElementById("rtn_ventas").readOnly = false;

                    let selectBox = document.getElementById("tipoPagoVenta");
                    if (selectBox.options.length > 2) selectBox.remove(2);

                    $('#categoria_cliente_nombre').text(data.nombre_categoria);
                    $('#cat_badge_text').text(data.nombre_categoria);
                    $('#categoria_cliente_venta_id').data('categoria-cliente-id', data.idcategoriacliente);

                    if ($('#seleccionarProducto').val()) {
                        cargarCategoriasProducto();
                    } else {
                        $('#categoria_cliente_venta_id').empty()
                            .append(new Option(data.nombre_categoria, data.idcategoriacliente, true, true));
                    }
                } else {
                    document.getElementById("nombre_cliente_ventas").readOnly = true;
                    document.getElementById("rtn_ventas").readOnly = true;
                    document.getElementById("nombre_cliente_ventas").value = data.nombre;
                    document.getElementById("rtn_ventas").value = data.rtn;

                    $('#categoria_cliente_nombre').text(data.nombre_categoria);
                    $('#cat_badge_text').text(data.nombre_categoria);
                    $('#categoria_cliente_venta_id').data('categoria-cliente-id', data.idcategoriacliente);

                    if ($('#seleccionarProducto').val()) {
                        cargarCategoriasProducto();
                    } else {
                        $('#categoria_cliente_venta_id').empty()
                            .append(new Option(data.nombre_categoria, data.idcategoriacliente, true, true));
                    }

                    diasCredito = data.dias_credito;
                    obtenerTipoPago();

                    // Orden de compra si aplica
                    if (tipoFacturaConfig && tipoFacturaConfig.requiere_orden_compra) {
                        obtenerOrdenesCompra();
                    }

                    // Códigos de exoneración si aplica
                    if (tipoFacturaConfig && tipoFacturaConfig.requiere_codigo_exoneracion) {
                        obtenerCodigosExoneracion(idCliente);
                    }

                    cargarHistorialPrecios();
                }
            })
            .catch(err => {
                console.log(err);
                Swal.fire({ icon: 'error', title: 'Error...', text: "Error al obtener datos del cliente" });
            });
    }

    // ================================================================
    // CÓDIGOS DE EXONERACIÓN (solo para tipo exoneradas)
    // ================================================================
    function obtenerCodigosExoneracion(idCliente) {
        if (!urls.codigos_exoneracion) return;

        axios.get(urls.codigos_exoneracion, { params: { cliente_id: idCliente } })
            .then(response => {
                let codigos = response.data.codigos || response.data;
                let html = '<option value="" selected disabled>--Seleccione un código--</option>';
                if (Array.isArray(codigos)) {
                    codigos.forEach(c => {
                        html += '<option value="' + c.id + '">' + (c.codigo || c.correlativo || c.id) + '</option>';
                    });
                }
                document.getElementById('codigoExoneracion').innerHTML = html;
            })
            .catch(err => {
                console.log(err);
            });
    }

    // ================================================================
    // TIPO DE PAGO
    // ================================================================
    function obtenerTipoPago() {
        var urlTipoPago = urls.tipo_pago;

        axios.get(urlTipoPago)
            .then(response => {
                let tipoDePago = response.data.tipos;
                let numeroVenta = response.data.numeroVenta.numero;
                let htmlPagos = '<option value="" selected disabled>--Seleccione una opcion--</option>';
                tipoDePago.forEach(element => {
                    htmlPagos += '<option value="' + element.id + '">' + element.descripcion + '</option>';
                });
                document.getElementById('tipoPagoVenta').innerHTML = htmlPagos;
                document.getElementById("numero_venta").value = numeroVenta;
            })
            .catch(err => {
                console.log(err);
                Swal.fire({ icon: 'error', title: 'Error...', text: "Error al obtener tipos de pago" });
            });
    }

    // ================================================================
    // CATEGORÍAS DE PRODUCTO
    // ================================================================
    function cargarCategoriasProducto() {
        let productoId = $('#seleccionarProducto').val();
        let clienteId = $('#seleccionarCliente').val();

        if (!productoId) {
            $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>--Seleccione primero un producto--</option>');
            return;
        }

        $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>Cargando categorías...</option>');

        axios.post('/producto/categorias-disponibles', { producto_id: productoId })
            .then(response => {
                let categorias = response.data.categorias;
                if (categorias.length > 0) {
                    categorias.sort((a, b) => (parseFloat(b.precio_a) || 0) - (parseFloat(a.precio_a) || 0));

                    $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>--Seleccione una categoría--</option>');
                    let categoriaClienteId = $('#categoria_cliente_venta_id').data('categoria-cliente-id');

                    categorias.forEach(categoria => {
                        let precio = parseFloat(categoria.precio_a) || 0;
                        let precioFormateado = new Intl.NumberFormat('es-HN', {
                            style: 'currency', currency: 'HNL', minimumFractionDigits: 2
                        }).format(precio);
                        let textoOpcion = categoria.nombre_categoria + ' - ' + precioFormateado;
                        let isSelected = (clienteId && categoria.id == categoriaClienteId);
                        let option = new Option(textoOpcion, categoria.id, isSelected, isSelected);
                        $('#categoria_cliente_venta_id').append(option);
                    });
                    $('#categoria_cliente_venta_id').prop('disabled', false);
                } else {
                    $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>No hay categorías disponibles</option>');
                    Swal.fire({ icon: 'warning', title: 'Advertencia', text: 'Este producto no tiene escalas de precio.' });
                }
            })
            .catch(err => {
                console.log(err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar categorías.' });
            });

        obtenerImagenes();
        cargarHistorialPrecios();
    }

    function habilitarBodega() {
        let categoriaId = $('#categoria_cliente_venta_id').val();
        let productoId = $('#seleccionarProducto').val();
        if (categoriaId && productoId) {
            $('#bodega').prop('disabled', false);
            obtenerBodegas(productoId);
        }
    }

    // ================================================================
    // HISTORIAL DE PRECIOS
    // ================================================================
    function cargarHistorialPrecios() {
        var productoId = $('#seleccionarProducto').val();
        var clienteId = $('#seleccionarCliente').val();
        var panel = document.getElementById('historialPreciosPanel');
        var cuerpo = document.getElementById('historialPreciosCuerpo');

        if (!productoId || !clienteId) { panel.classList.add('d-none'); return; }

        var urlHistorial = urls.historial_precios;

        cuerpo.innerHTML = '<p class="text-muted small"><i class="fa fa-spinner fa-spin"></i> Cargando historial...</p>';
        panel.classList.remove('d-none');

        axios.post(urlHistorial, { cliente_id: clienteId, producto_id: productoId })
            .then(function(response) {
                var rows = response.data.historial;
                if (!rows || rows.length === 0) {
                    cuerpo.innerHTML = '<p class="text-muted small">No hay ventas previas.</p>';
                    return;
                }
                var fmt = new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 });
                var html = '<div class="table-responsive"><table class="table mb-0 table-sm table-bordered table-hover" style="font-size:0.82rem;"><thead class="thead-light"><tr><th>Fecha</th><th>Factura</th><th>Precio Unit.</th><th>Cant.</th><th>Total</th><th>Categoría</th></tr></thead><tbody>';
                rows.forEach(function(r) {
                    html += '<tr><td>' + r.fecha_emision + '</td><td>' + r.numero_factura + '</td><td class="text-right font-weight-bold text-success">' + fmt.format(r.precio_unidad) + '</td><td class="text-center">' + r.cantidad + '</td><td class="text-right">' + fmt.format(r.total) + '</td><td><span class="badge badge-secondary">' + r.categoria + '</span></td></tr>';
                });
                html += '</tbody></table></div>';
                cuerpo.innerHTML = html;
            })
            .catch(function() { cuerpo.innerHTML = '<p class="text-danger small">Error al cargar historial.</p>'; });
    }

    // ================================================================
    // BODEGAS
    // ================================================================
    function obtenerBodegas(id) {
        document.getElementById('bodega').innerHTML = "<option selected disabled>--Seleccione una bodega--</option>";
        var urlBase = urls.bodegas;
        var url = urlBase.replace('{idProducto}', id);

        $('#bodega').select2({
            ajax: {
                url: url,
                data: function(params) {
                    return { search: params.term, type: 'public', page: params.page || 1, idProducto: id };
                }
            }
        });
    }

    // ================================================================
    // IMÁGENES
    // ================================================================
    function obtenerImagenes() {
        let id = document.getElementById('seleccionarProducto').value;
        let htmlImagenes = '';
        axios.post('/producto/listar/imagenes', { id: id })
            .then(response => {
                let imagenes = response.data.imagenes;
                if (imagenes.length == 0) {
                    document.getElementById('bloqueImagenes').innerHTML = '<div class="carousel-item active"><div style="height:180px;display:flex;align-items:center;justify-content:center;background:#f5f5f5;border-radius:8px;"><div style="text-align:center;color:#b0bec5;"><i class="fa fa-image" style="font-size:2.5rem;display:block;margin-bottom:8px;"></i><span style="font-size:13px;">Sin imagen</span></div></div></div>';
                } else {
                    imagenes.forEach(element => {
                        let activeClass = element.contador == 1 ? ' active' : '';
                        htmlImagenes += '<div class="carousel-item' + activeClass + '"><img class="d-block" src="' + public_path + '/' + element.url_img + '" alt="imagen ' + element.contador + '" style="width:100%;height:30rem"></div>';
                    });
                    document.getElementById('bloqueImagenes').innerHTML = htmlImagenes;
                }
                document.getElementById('botonAdd').classList.add("d-none");
                let a = document.getElementById("detalleProducto");
                a.href = "/producto/detalle/" + id;
                a.classList.remove("d-none");
            })
            .catch(err => { console.log(err); });

        obtenerBodegas(id);
    }

    // ================================================================
    // AGREGAR PRODUCTO AL CARRITO
    // ================================================================
    function agregarProductoCarrito() {
        let idProducto = document.getElementById('seleccionarProducto').value;
        let categoria_cliente_venta_id = document.getElementById('categoria_cliente_venta_id').value;
        let data = $("#bodega").select2('data')[0];
        let bodega = data.bodegaSeccion;
        let idBodega = data.idBodega;
        let idSeccion = data.id;

        var urlDatosProducto = urls.datos_producto;

        axios.post(urlDatosProducto, { idProducto: idProducto, categoria_cliente_venta_id: categoria_cliente_venta_id })
            .then(response => {
                // Verificar duplicados
                let flag = false;
                arregloIdInputs.forEach(idInpunt => {
                    let idProductoFila = document.getElementById("idProducto" + idInpunt).value;
                    let idSeccionFila = document.getElementById("idSeccion" + idInpunt).value;
                    if (idProducto == idProductoFila && idSeccion == idSeccionFila && !flag) flag = true;
                });

                if (flag) {
                    Swal.fire({
                        icon: 'warning', title: 'Advertencia!',
                        html: '<p class="text-left">La sección de bodega y producto ha sido agregada anteriormente.<br><br>Por favor verificar la sección de bodega y producto sea distinto a los ya existentes.</p>'
                    });
                    return;
                }

                let producto = response.data.producto;
                let arrayUnidades = response.data.unidades;
                numeroInputs += 1;

                let htmlSelectUnidades = "";
                arrayUnidades.forEach(unidad => {
                    let sel = unidad.valor_defecto == 1 ? 'selected' : '';
                    htmlSelectUnidades += '<option ' + sel + ' value="' + unidad.id + '" data-id="' + unidad.idUnidadVenta + '">' + unidad.nombre + '</option>';
                });

                // Determinar opciones de precios según configuración
                let htmlprecios = '';
                if (tipoFacturaConfig && tipoFacturaConfig.multiples_precios) {
                    // Múltiples precios A/B/C/D (sin restricción)
                    htmlprecios = '<option value="' + producto.precio1 + '" data-id="p1" selected>' + producto.precio1 + ' - A</option>';
                    if (producto.precio2) htmlprecios += '<option value="' + producto.precio2 + '" data-id="p2">' + producto.precio2 + ' - B</option>';
                    if (producto.precio3) htmlprecios += '<option value="' + producto.precio3 + '" data-id="p3">' + producto.precio3 + ' - C</option>';
                    if (producto.precio4) htmlprecios += '<option value="' + producto.precio4 + '" data-id="p4">' + producto.precio4 + ' - D</option>';
                } else {
                    // Solo precio A (con restricción)
                    htmlprecios = '<option value="' + producto.precio1 + '" data-id="p1" selected>' + producto.precio1 + ' - A</option>';
                }

                // Determinar el min del precio
                let minPrecio = (tipoFacturaConfig && tipoFacturaConfig.multiples_precios) ? '' : 'min="' + producto.precio1 + '"';

                let html = `
                <tr id='${numeroInputs}'>
                    <td style="vertical-align:middle; text-align:center; padding:4px 6px;">
                        <input id="idProducto${numeroInputs}" name="idProducto${numeroInputs}" type="hidden" value="${producto.id}">
                        <input id="precios_producto_carga_id${numeroInputs}" name="precios_producto_carga_id${numeroInputs}" type="hidden" value="${producto.precios_producto_carga_id || ''}">
                        <input id="isv${numeroInputs}" name="isv${numeroInputs}" type="hidden" value="${producto.isv}">
                        <input id="idBodega${numeroInputs}" name="idBodega${numeroInputs}" type="hidden" value="${idBodega}">
                        <input id="idSeccion${numeroInputs}" name="idSeccion${numeroInputs}" type="hidden" value="${idSeccion}">
                        <input id="restaInventario${numeroInputs}" name="restaInventario${numeroInputs}" type="hidden" value="">
                        <input id="subTotal${numeroInputs}" name="subTotal${numeroInputs}" type="hidden" value="" required>
                        <input id="isvProducto${numeroInputs}" name="isvProducto${numeroInputs}" type="hidden" value="" required>
                        <input id="acumuladoDescuento${numeroInputs}" name="acumuladoDescuento${numeroInputs}" type="hidden">
                        <input id="total${numeroInputs}" name="total${numeroInputs}" type="hidden" value="" required>
                        <input id="bodega${numeroInputs}" name="bodega${numeroInputs}" type="hidden" value="${bodega}">
                        <button class="btn btn-danger btn-xs" type="button" onclick="eliminarInput(${numeroInputs})" title="Eliminar" style="padding:2px 6px; font-size:11px; border-radius:5px;">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <input type="text" id="nombre${numeroInputs}" name="nombre${numeroInputs}" value='${producto.nombre}' readonly data-parsley-required
                            style="border:none; background:transparent; font-size:12px; font-weight:700; color:#1b5e20; width:100%; min-width:130px;">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px; white-space:nowrap;">
                        <span style="background:#e3f2fd; color:#1565c0; border-radius:6px; padding:2px 8px; font-size:11px; font-weight:700;">
                            <i class="fa fa-archive" style="font-size:10px;"></i> ${bodega}
                        </span>
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <select class="form-control form-control-sm" name="precios${numeroInputs}" id="precios${numeroInputs}" data-parsley-required style="font-size:11px; min-width:100px;"
                            onchange="validacionPrecio(precios${numeroInputs}, precio${numeroInputs})">
                            ${htmlprecios}
                        </select>
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <input type="number" id="precio${numeroInputs}" name="precio${numeroInputs}" value="${producto.precio1}" class="form-control form-control-sm"
                            ${minPrecio} data-parsley-required step="any" autocomplete="off" style="min-width:80px; font-size:11px;"
                            onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <input type="number" id="cantidad${numeroInputs}" name="cantidad${numeroInputs}" class="form-control form-control-sm" min="1" data-parsley-required autocomplete="off" style="min-width:60px; font-size:11px;"
                            onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <select class="form-control form-control-sm" name="unidad${numeroInputs}" id="unidad${numeroInputs}" data-parsley-required style="font-size:11px; min-width:80px;"
                            onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                            ${htmlSelectUnidades}
                        </select>
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px; text-align:right;">
                        <input type="text" id="subTotalMostrar${numeroInputs}" name="subTotalMostrar${numeroInputs}" placeholder="0.00" readonly autocomplete="off"
                            style="border:none; background:#f1f8e9; border-radius:5px; font-weight:700; color:#2e7d32; font-size:12px; padding:2px 6px; text-align:right; width:100%; min-width:75px;">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px; text-align:right;">
                        <input type="text" id="isvProductoMostrar${numeroInputs}" name="isvProductoMostrar${numeroInputs}" placeholder="0.00" readonly autocomplete="off"
                            style="border:none; background:#fce4ec; border-radius:5px; font-weight:700; color:#b71c1c; font-size:12px; padding:2px 6px; text-align:right; width:100%; min-width:65px;">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px; text-align:right;">
                        <input type="text" id="totalMostrar${numeroInputs}" name="totalMostrar${numeroInputs}" placeholder="0.00" readonly autocomplete="off"
                            style="border:none; background:linear-gradient(135deg,#e65100,#f9a826); border-radius:5px; font-weight:800; color:#fff; font-size:12px; padding:2px 6px; text-align:right; width:100%; min-width:80px;">
                    </td>
                </tr>`;

                arregloIdInputs.splice(numeroInputs, 0, numeroInputs);
                document.getElementById('carritoTbody').insertAdjacentHTML('beforeend', html);
                // Mostrar tabla, ocultar mensaje vacío
                document.getElementById('carritoVacio').classList.add('d-none');
                document.getElementById('carritoTablaWrapper').classList.remove('d-none');
            })
            .catch(err => {
                const mensaje = err.response?.data?.message || 'Error al agregar producto';
                Swal.fire({ icon: 'error', title: 'Error', html: mensaje });
            });
    }

    function prueba() {
        document.getElementById('botonAdd').classList.remove("d-none");
    }

    // ================================================================
    // CÁLCULOS
    // ================================================================
    function calcularTotalesInicioPagina() {
        let arrayInputs = this.arregloIdInputs;
        arrayInputs.forEach(id => {
            let valorInputPrecio = document.getElementById('precio' + id).value;
            let valorInputCantidad = document.getElementById('cantidad' + id).value;
            let valorSelectUnidad = document.getElementById('unidad' + id).value;
            let isvProducto = document.getElementById("isv" + id).value;

            // Si no aplica ISV, forzar a 0
            if (tipoFacturaConfig && !tipoFacturaConfig.aplica_isv) {
                isvProducto = 0;
            }

            if (valorInputPrecio && valorInputCantidad) {
                let descuento = document.getElementById("porDescuento").value;
                let subTotal = 0, isv = 0, total = 0, descuentoCalculado = 0;

                if (descuento > 0) {
                    subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                    descuentoCalculado = subTotal * (descuento / 100);
                    subTotal = subTotal - descuentoCalculado;
                    isv = parseFloat((subTotal * (isvProducto / 100)).toFixed(2));
                    total = subTotal + (subTotal * (isvProducto / 100));
                } else {
                    descuentoCalculado = 0;
                    subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                    isv = parseFloat((subTotal * (isvProducto / 100)).toFixed(2));
                    total = subTotal + subTotal * (isvProducto / 100);
                }

                document.getElementById("acumuladoDescuento" + id).value = descuentoCalculado.toFixed(2);
                document.getElementById('total' + id).value = total.toFixed(2);
                document.getElementById('totalMostrar' + id).value = formatoMoneda(total);
                document.getElementById('subTotal' + id).value = subTotal.toFixed(2);
                document.getElementById('subTotalMostrar' + id).value = formatoMoneda(subTotal);
                document.getElementById('isvProducto' + id).value = isv.toFixed(2);
                document.getElementById('isvProductoMostrar' + id).value = formatoMoneda(isv);
            }
        });
        this.totalesGenerales();
    }

    function calcularTotales(idPrecio, idCantidad, isvProducto, idUnidad, id, idRestaInventario) {
        let valorInputPrecio = Number(idPrecio.value).toFixed(2);
        let valorInputCantidad = idCantidad.value;
        let valorSelectUnidad = idUnidad.value;

        // Si no aplica ISV, forzar a 0
        if (tipoFacturaConfig && !tipoFacturaConfig.aplica_isv) {
            isvProducto = 0;
        }

        if (valorInputPrecio && valorInputCantidad) {
            let descuento = document.getElementById('porDescuento').value;
            let subTotal = 0, isv = 0, total = 0, descuentoCalculado = 0;

            if (descuento > 0) {
                subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                descuentoCalculado = subTotal * (descuento / 100);
                subTotal = subTotal - descuentoCalculado;
                isv = subTotal * (isvProducto / 100);
                total = subTotal + (subTotal * (isvProducto / 100));
            } else {
                descuentoCalculado = 0;
                subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                isv = subTotal * (isvProducto / 100);
                total = subTotal + subTotal * (isvProducto / 100);
            }

            document.getElementById('acumuladoDescuento' + id).value = descuentoCalculado.toFixed(2);
            document.getElementById('total' + id).value = total.toFixed(2);
            document.getElementById('totalMostrar' + id).value = formatoMoneda(total);
            document.getElementById('subTotal' + id).value = subTotal.toFixed(2);
            document.getElementById('subTotalMostrar' + id).value = formatoMoneda(subTotal);
            document.getElementById('isvProducto' + id).value = isv.toFixed(2);
            document.getElementById('isvProductoMostrar' + id).value = formatoMoneda(isv);

            idRestaInventario.value = valorInputCantidad * valorSelectUnidad;
            this.totalesGenerales();
        }

        idPrecio.value = valorInputPrecio;
    }

    function formatoMoneda(valor) {
        return new Intl.NumberFormat('es-HN', {
            style: 'currency', currency: 'HNL', minimumFractionDigits: 2
        }).format(valor);
    }

    function totalesGenerales() {
        if (numeroInputs == 0) return;

        let totalGeneralValor = 0, totalISV = 0, subTotalGeneralGrabadoValor = 0;
        let subTotalGeneralExcentoValor = 0, subTotalGeneral = 0, acumularDescuento = 0;

        for (let i = 0; i < arregloIdInputs.length; i++) {
            let subTotalFila = Number(document.getElementById('subTotal' + arregloIdInputs[i]).value);
            let isvFila = Number(document.getElementById('isvProducto' + arregloIdInputs[i]).value);

            if (isvFila == 0) {
                subTotalGeneralExcentoValor += subTotalFila;
            } else if (subTotalFila > 0) {
                subTotalGeneralGrabadoValor += subTotalFila;
            }

            subTotalGeneral += subTotalFila;
            totalISV += isvFila;
            totalGeneralValor += Number(document.getElementById('total' + arregloIdInputs[i]).value);
            acumularDescuento += Number(document.getElementById('acumuladoDescuento' + arregloIdInputs[i]).value);
        }

        document.getElementById('porDescuentoCalculado').value = acumularDescuento.toFixed(2);
        document.getElementById('descuentoMostrar').value = formatoMoneda(acumularDescuento);
        document.getElementById('subTotalGeneral').value = subTotalGeneral.toFixed(2);
        document.getElementById('subTotalGeneralMostrar').value = formatoMoneda(subTotalGeneral);
        document.getElementById('subTotalGeneralGrabado').value = subTotalGeneralGrabadoValor.toFixed(2);
        document.getElementById('subTotalGeneralGrabadoMostrar').value = formatoMoneda(subTotalGeneralGrabadoValor);
        document.getElementById('subTotalGeneralExcento').value = subTotalGeneralExcentoValor.toFixed(2);
        document.getElementById('subTotalGeneralExcentoMostrar').value = formatoMoneda(subTotalGeneralExcentoValor);
        document.getElementById('isvGeneral').value = totalISV.toFixed(2);
        document.getElementById('isvGeneralMostrar').value = formatoMoneda(totalISV);
        document.getElementById('totalGeneral').value = totalGeneralValor.toFixed(2);
        document.getElementById('totalGeneralMostrar').value = formatoMoneda(totalGeneralValor);
    }

    function eliminarInput(id) {
        document.getElementById(id).remove();
        var myIndex = arregloIdInputs.indexOf(id);
        if (myIndex !== -1) {
            arregloIdInputs.splice(myIndex, 1);
            this.totalesGenerales();
        }
        // Ocultar tabla si no quedan productos
        if (arregloIdInputs.length === 0) {
            document.getElementById('carritoTablaWrapper').classList.add('d-none');
            document.getElementById('carritoVacio').classList.remove('d-none');
        }
    }

    function validacionPrecio(idPrecios, idprecio) {
        var idPrecioSeleccionado = idPrecios.options[idPrecios.selectedIndex].getAttribute("data-id");
        var precioSeleccionado = idPrecios.value;
        var idprecioIngresado = idprecio.id;

        document.getElementById(idprecioIngresado).value = precioSeleccionado;

        // Solo aplicar mínimo si NO es sin restricción
        if (tipoFacturaConfig && !tipoFacturaConfig.multiples_precios) {
            document.getElementById(idprecioIngresado).setAttribute("min", precioSeleccionado);
        }
    }

    // ================================================================
    // FECHAS Y PAGOS
    // ================================================================
    function validarFechaPago() {
        let tipoPago = document.getElementById('tipoPagoVenta').value;
        if (tipoPago == 2) {
            document.getElementById('fecha_vencimiento').readOnly = false;
            sumarDiasCredito();
        } else {
            document.getElementById('fecha_vencimiento').value = "{{ date('Y-m-d') }}";
            document.getElementById('fecha_vencimiento').readOnly = true;
        }
    }

    function sumarDiasCredito() {
        let tipoPago = document.getElementById('tipoPagoVenta').value;
        if (tipoPago == 2) {
            let fechaEmision = document.getElementById("fecha_emision").value;
            let date = new Date(fechaEmision);
            date.setDate(date.getDate() + diasCredito);
            document.getElementById("fecha_vencimiento").value = date.toISOString().split('T')[0];
        }
    }

    function obtenerOrdenesCompra() {
        if (!tipoFacturaConfig || !tipoFacturaConfig.requiere_orden_compra || !urls.orden_compra) return;

        let idCliente = document.getElementById('seleccionarCliente').value;
        $('#ordenCompra').select2({
            ajax: {
                url: urls.orden_compra,
                data: function(params) {
                    return { idCliente: idCliente, search: params.term, type: 'public', page: params.page || 1 };
                }
            }
        });
    }

    // ================================================================
    // GUARDAR VENTA
    // ================================================================
    var _ofertaGuardadaId = null;
    var _ofertaPedidoId   = null;

    function limpiarFormularioVenta(data) {
        document.getElementById('bloqueImagenes').innerHTML = '';
        document.getElementById('divProductos').innerHTML = '';
        document.getElementById("crear_venta").reset();
        $('#crear_venta').parsley().reset();

        document.getElementById('detalleProducto').classList.add("d-none");
        document.getElementById('detalleProducto').href = "";
        document.getElementById("seleccionarCliente").innerHTML = '<option value="" selected disabled>--Seleccionar un cliente--</option>';
        $('#seleccionarCliente').prop('disabled', false);
        document.getElementById('seleccionarProducto').innerHTML = '<option value="" selected disabled></option>';
        document.getElementById('codigoProductoBuscar').value = '';
        var lblProd = document.getElementById('productoSeleccionadoLabel');
        if (lblProd) { lblProd.classList.add('d-none'); lblProd.textContent = ''; }
        document.getElementById('bodega').innerHTML = '<option value="" selected disabled>--Seleccione un producto--</option>';
        document.getElementById("bodega").disabled = true;

        // Ocultar tabla carrito
        var carritoTabla = document.getElementById('carritoTablaWrapper');
        var carritoVacio = document.getElementById('carritoVacio');
        if (carritoTabla) carritoTabla.style.display = 'none';
        if (carritoVacio) carritoVacio.style.display = '';

        arregloIdInputs = [];
        numeroInputs = 0;
        retencionEstado = false;

        if (data && data.numeroVenta) document.getElementById('numero_venta').value = data.numeroVenta;
        document.getElementById("btn_venta_coorporativa").disabled = false;

        document.getElementById('restriccion').value = tipoFacturaConfig ? tipoFacturaConfig.restriccion : 1;
        document.getElementById('tipo_venta_id').value = tipoFacturaConfig ? tipoFacturaConfig.tipo_venta_id : 2;
        document.getElementById('tipo_factura_id').value = tipoFacturaConfig ? tipoFacturaConfig.id : '';
    }

    function ofertaAccion(tipo) {
        var idOferta  = _ofertaGuardadaId;
        var idPedido  = _ofertaPedidoId;

        if (tipo === 'nueva') {
            // Limpiar sólo productos; mantener cliente y pedido vinculado si hay
            document.getElementById('bloqueImagenes').innerHTML = '';
            document.getElementById('divProductos').innerHTML = '';
            var carritoTabla = document.getElementById('carritoTablaWrapper');
            var carritoVacio = document.getElementById('carritoVacio');
            if (carritoTabla) carritoTabla.style.display = 'none';
            if (carritoVacio) carritoVacio.style.display = '';
            arregloIdInputs = [];
            numeroInputs = 0;
            $('#modalExitoOferta').modal('hide');

        } else if (tipo === 'ganadora') {
            $('#modalExitoOferta').modal('hide');
            if (!idPedido) {
                Swal.fire({ icon: 'info', title: 'Sin pedido', text: 'Esta oferta no está vinculada a un pedido.' });
                return;
            }
            document.getElementById('ogLista').innerHTML = '';
            document.getElementById('ogLoading').style.display = '';
<<<<<<< HEAD
            // Esperar a que el primer modal cierre completamente antes de abrir el segundo
            $('#modalExitoOferta').one('hidden.bs.modal', function () {
                $('#modalOfertasGanadoras').modal('show');
                axios.get('/cotizacion/por-pedido/' + idPedido)
                    .then(function(res) {
                        document.getElementById('ogLoading').style.display = 'none';
                        var ofertas = res.data;
                        if (!ofertas.length) {
                            document.getElementById('ogLista').innerHTML = '<p class="text-center text-muted">No hay ofertas para este pedido.</p>';
                            return;
                        }
                        var fmt = new Intl.NumberFormat('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        var html = '';
                        ofertas.forEach(function(o, idx) {
                            var esActual   = (o.id == idOferta) ? ' <span style="background:#e3f2fd;color:#1565c0;border-radius:12px;padding:1px 8px;font-size:10px;font-weight:700;">Esta oferta</span>' : '';
                            var esGanadora = o.es_ganadora ? ' <span style="background:#fff8e1;color:#f57f17;border-radius:12px;padding:1px 8px;font-size:10px;font-weight:700;"><i class="fa fa-trophy"></i> Ganadora</span>' : '';
                            var cardBorder = o.es_ganadora ? 'border:2px solid #f9a826;background:#fffde7;' : 'border:1px solid #e0e0e0;background:#fff;';
                            var numProds   = (o.productos && o.productos.length) ? o.productos.length : 0;
                            var cardId     = 'ogCard_' + o.id;

                            html += '<div class="og-card" style="' + cardBorder + '">';

                            // ── Fila principal ──────────────────────────────
                            html += '<div class="og-card-header">';
                            // Info izquierda
                            html += '<div style="flex:1;min-width:0;">';
                            html += '<div style="font-weight:800;font-size:13px;color:#2d3748;display:flex;align-items:center;flex-wrap:wrap;gap:4px;">';
                            html += 'Oferta #' + o.id + esActual + esGanadora;
                            html += '</div>';
                            html += '<div style="font-size:11px;color:#90a4ae;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + (o.nombre_cliente || '') + '</div>';
                            html += '</div>';
                            // Total + botón seleccionar
                            html += '<div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px;flex-shrink:0;">';
                            html += '<span style="font-weight:800;color:#e65100;font-size:14px;">L ' + fmt.format(o.total) + '</span>';
                            html += '<button onclick="confirmarGanadora(' + o.id + ')" style="background:linear-gradient(135deg,#e65100,#f9a826);color:#fff;border:none;border-radius:8px;padding:4px 14px;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">';
                            html += '<i class="mr-1 fa fa-trophy"></i>Seleccionar</button>';
                            html += '</div>';
                            html += '</div>';

                            // ── Toggle de productos ─────────────────────────
                            if (numProds > 0) {
                                html += '<div style="padding:0 14px 8px;">';
                                html += '<button type="button" class="og-toggle-btn" id="ogToggle_' + o.id + '" onclick="ogToggle(' + o.id + ')">';
                                html += '<span class="og-toggle-icon" id="ogIcon_' + o.id + '">+</span>';
                                html += 'Productos (' + numProds + ')';
                                html += '</button>';
                                html += '</div>';

                                html += '<div class="og-products" id="ogProds_' + o.id + '">';
                                o.productos.forEach(function(p) {
                                    html += '<div class="og-prod-row">';
                                    html += '<span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#2d3748;" title="' + p.nombre + '">' + p.nombre + '</span>';
                                    html += '<span style="margin-left:12px;white-space:nowrap;color:#546e7a;font-size:11px;font-weight:600;">x' + p.cantidad + ' &nbsp; L ' + fmt.format(p.total) + '</span>';
                                    html += '</div>';
                                });
                                html += '</div>';
                            }

                            html += '</div>'; // .og-card
                        });
                        document.getElementById('ogLista').innerHTML = html;
                    })
                    .catch(function() {
                        document.getElementById('ogLoading').style.display = 'none';
                        document.getElementById('ogLista').innerHTML = '<p class="text-center text-danger">Error al cargar ofertas.</p>';
=======
            $('#modalOfertasGanadoras').modal('show');
            axios.get('/cotizacion/por-pedido/' + idPedido)
                .then(function(res) {
                    document.getElementById('ogLoading').style.display = 'none';
                    var ofertas = res.data;
                    if (!ofertas.length) {
                        document.getElementById('ogLista').innerHTML = '<p class="text-muted text-center">No hay ofertas para este pedido.</p>';
                        return;
                    }
                    var html = '<div class="list-group">';
                    ofertas.forEach(function(o) {
                        var esActual = o.id == idOferta ? ' (esta oferta)' : '';
                        html += '<button onclick="confirmarGanadora(' + o.id + ')" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" style="border-radius:8px; margin-bottom:6px; border:1px solid #f9a826;">';
                        html += '<span><strong>Oferta #' + o.id + '</strong>' + esActual + '<br><small class="text-muted">' + (o.nombre_cliente || '') + '</small></span>';
                        html += '<span style="font-weight:700; color:#e65100;">L ' + parseFloat(o.total).toFixed(2) + '</span>';
                        html += '</button>';
>>>>>>> parent of bdeaa912 (Merge branch 'antes-de-cagarla' into antes-cagarla-yef)
                    });
                    html += '</div>';
                    document.getElementById('ogLista').innerHTML = html;
                })
                .catch(function() {
                    document.getElementById('ogLoading').style.display = 'none';
                    document.getElementById('ogLista').innerHTML = '<p class="text-danger text-center">Error al cargar ofertas.</p>';
                });

        } else if (tipo === 'prefacturar') {
            $('#modalExitoOferta').modal('hide');
            axios.post('/cotizacion/marcar-ganadora', { cotizacion_id: idOferta }, { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } })
                .then(function() {
                    window.location.href = '/flujo/prefactura';
                })
                .catch(function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo prefacturar la oferta.' });
                });

        } else if (tipo === 'imprimir') {
            var urlImprimir = urls.imprimir;
            if (urlImprimir && idOferta) {
                window.open(urlImprimir.replace('{id}', idOferta), '_blank');
            }
            $('#modalExitoOferta').modal('hide');
        }
    }

    function confirmarGanadora(cotizacionId) {
        axios.post('/cotizacion/marcar-ganadora', { cotizacion_id: cotizacionId }, { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } })
            .then(function() {
                $('#modalOfertasGanadoras').modal('hide');
                Swal.fire({ icon: 'success', title: '¡Ganadora seleccionada!', text: 'La oferta #' + cotizacionId + ' fue marcada como ganadora.' });
            })
            .catch(function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo marcar la oferta como ganadora.' });
            });
    }

    $(document).on('submit', '#crear_venta', function(event) {
        event.preventDefault();
        guardarVenta();
    });

    function guardarVenta() {
        document.getElementById("btn_venta_coorporativa").disabled = true;

        var data = new FormData($('#crear_venta').get(0));
        // Forzar inclusión del cliente aunque el select esté deshabilitado (pedido vinculado)
        var clienteVal = $('#seleccionarCliente').val();
        if (clienteVal) data.set('seleccionarCliente', clienteVal);

        let longitudArreglo = arregloIdInputs.length;
        for (var i = 0; i < longitudArreglo; i++) {
            let name = "unidad" + arregloIdInputs[i];
            let nameForm = "idUnidadVenta" + arregloIdInputs[i];
            let e = document.getElementById(name);
            let idUnidadVenta = e.options[e.selectedIndex].getAttribute("data-id");
            data.append(nameForm, idUnidadVenta);

            let name2 = "precios" + arregloIdInputs[i];
            let nameForm2 = "idPrecioSeleccionado" + arregloIdInputs[i];
            let a = document.getElementById(name2);
            let idPrecioSeleccionado = a.options[a.selectedIndex].getAttribute("data-id");
            data.append(nameForm2, idPrecioSeleccionado);
        }

        data.append("numeroInputs", numeroInputs);
        let text = arregloIdInputs.toString();
        data.append("arregloIdInputs", text);

        const formDataObj = {};
        data.forEach((value, key) => (formDataObj[key] = value));

        var urlGuardar = urls.guardar;

        axios.post(urlGuardar, formDataObj, { headers: { "content-type": "application/json" } })
            .then(response => {
                let data = response.data;

                // Para tipos con código de autorización
                if (tipoFacturaConfig && tipoFacturaConfig.requiere_codigo_autorizacion) {
                    idAutorizacion = document.getElementById('codigo_autorizacion').value;
                    idFactura = data.idFactura;
                    var urlImprimir = urls.imprimir || '/factura/cooporativo/{id}';
                    document.getElementById('btn_imprimir').href = urlImprimir.replace('{id}', idFactura);
                }

                if (data.idFactura == 0) {
                    Swal.fire({ icon: data.icon, title: data.title, html: data.text });
                    document.getElementById("btn_venta_coorporativa").disabled = false;
                    return;
                }

                // Para cotizaciones, mostrar modal de opciones post-guardado
                if (codigoActual === 'cotizacion_clientes_a') {
                    _ofertaGuardadaId = data.idFactura;
                    _ofertaPedidoId   = data.pedidoId || null;
                    limpiarFormularioVenta(data);
                    $('#modalExitoOferta').modal('show');
                    return;
                }

                Swal.fire({ icon: data.icon, title: data.title, html: data.text });
                limpiarFormularioVenta(data);

                limpiarFormularioVenta(data);

                // Desactivar código si aplica
                if (tipoFacturaConfig && tipoFacturaConfig.requiere_codigo_autorizacion) {
                    setTimeout(function() { desactivarCodigo(); }, 30000);
                }
            })
            .catch(err => {
                document.getElementById("btn_venta_coorporativa").disabled = false;
                let data = err.response ? err.response.data : {};
                console.log(err);
                Swal.fire({ icon: data.icon || 'error', title: data.title || 'Error', text: data.text || 'Error al guardar' });
            });
    }

    function obtenerCategoriasClientes() {
        $('#categoria_cliente_venta_id').select2({
            placeholder: 'Seleccione una categoría',
            allowClear: true,
            ajax: {
                url: '/clientes/categorias-escala',
                dataType: 'json',
                delay: 250,
                data: function(params) { return { q: params.term || '', page: params.page || 1 }; },
                processResults: function(data) {
                    return { results: data.categorias.map(function(item) { return { id: item.id, text: item.nombre_categoria }; }) };
                }
            }
        });
    }
    </script>

    <script>
    <?php
        date_default_timezone_set('America/Tegucigalpa');
        $act_fecha = date('Y-m-d');
        $year = date('Y');
    ?>
    function mostrarHora() {
        var fecha = new Date();
        var hora = fecha.getHours();
        var minutos = fecha.getMinutes();
        var segundos = fecha.getSeconds();
        minutos = minutos < 10 ? "0" + minutos : minutos;
        segundos = segundos < 10 ? "0" + segundos : segundos;
        var el = document.getElementById("reloj");
        if (el) el.innerHTML = hora + ":" + minutos + ":" + segundos;
    }
    setInterval(mostrarHora, 1000);
    </script>
    @endpush

    <div class="mt-3">
        <div class="float-right">
            <?php echo "$act_fecha"; ?> <strong id="reloj"></strong>
        </div>
        <div>
            <strong>Copyright</strong> Distribuciones Valencia &copy; <?php echo "$year"; ?>
        </div>
        <div style="clear: both;"></div>
    </div>
</div>
