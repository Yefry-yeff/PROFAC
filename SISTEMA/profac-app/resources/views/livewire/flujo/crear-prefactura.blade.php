<div>
    @push('styles')
    <style>
        /* ── Spin-arrow removal ── */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
        input[type=number] { -moz-appearance:textfield; }

        /* ── Form section cards ── */
        .of-card {
            background:#fff;
            border-radius:14px;
            border:1px solid #e8eaef;
            box-shadow:0 2px 12px rgba(0,0,0,.06);
            padding:22px 24px;
            margin-bottom:18px;
        }
        .of-card-title {
            font-size:13px; font-weight:700; color:#6c757d;
            text-transform:uppercase; letter-spacing:.6px;
            margin-bottom:16px;
            display:flex; align-items:center; gap:7px;
        }
        .of-card-title i { font-size:14px; }

        /* ── Inputs ── */
        .of-input {
            border:1.5px solid #dde2ec;
            border-radius:9px;
            padding:8px 12px;
            font-size:13px;
            color:#2d3748;
            background-color:#fafbfc;
            transition:border .2s, box-shadow .2s;
            width:100%;
        }
        .of-select {
            -webkit-appearance:none !important;
            -moz-appearance:none !important;
            appearance:none !important;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%236b7280' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E") !important;
            background-repeat:no-repeat !important;
            background-position:right 11px center !important;
            background-size:10px !important;
            background-color:#fafbfc !important;
            border:1.5px solid #dde2ec;
            border-radius:9px;
            padding:8px 32px 8px 12px;
            font-size:13px;
            color:#2d3748;
            transition:border .2s, box-shadow .2s;
            width:100%;
        }
        .of-input:focus, .of-select:focus {
            border-color:#e65100; box-shadow:0 0 0 3px rgba(230,81,0,.12);
            outline:none; background-color:#fff !important;
        }
        .of-input[readonly] { background:#f1f3f7; color:#6b7280; cursor:default; }
        .of-label {
            font-size:12px; font-weight:600; color:#4a5568;
            margin-bottom:4px; display:block;
        }
        .of-label .req { color:#e74c3c; margin-left:2px; }

        /* ── Product search ── */
        .of-search-wrap { position:relative; }
        .of-search-wrap input { padding-right:44px; }
        .of-search-btn {
            position:absolute; right:4px; top:50%; transform:translateY(-50%);
            background:linear-gradient(135deg,#e65100,#f9a826);
            border:none; border-radius:7px; color:#fff;
            width:34px; height:30px; cursor:pointer;
            display:flex; align-items:center; justify-content:center;
            font-size:13px;
        }
        .of-search-btn:hover { background:linear-gradient(135deg,#bf360c,#e65100); }

        /* ── Product image preview ── */
        .of-img-carousel {
            border-radius:12px; overflow:hidden;
            border:1.5px solid #e8eaef; background:#f8f9fc;
            max-height:180px;
            position:relative;
        }
        #detalleProducto {
            position:absolute; bottom:8px; left:50%; transform:translateX(-50%);
            z-index:10;
            background:rgba(230,81,0,.82);
            backdrop-filter:blur(4px);
            color:#fff !important; font-size:11px; font-weight:700;
            padding:5px 14px; border-radius:20px;
            text-decoration:none !important;
            display:flex; align-items:center; gap:5px;
            white-space:nowrap;
            transition:background .2s, transform .2s;
            box-shadow:0 2px 8px rgba(0,0,0,.25);
        }
        #detalleProducto:hover {
            background:rgba(191,54,12,.95);
            transform:translateX(-50%) translateY(-2px);
        }
        .of-img-carousel .carousel-item img {
            width:100%; height:180px; object-fit:contain;
            background:#f8f9fc;
        }

        /* ── Add button ── */
        .of-add-btn {
            background:linear-gradient(135deg,#00b894,#00896e) !important;
            border:none !important; border-radius:10px; color:#fff !important;
            font-size:14px; font-weight:700;
            padding:11px 22px; cursor:pointer; width:100%;
            box-shadow:0 4px 14px rgba(0,184,148,.35);
            transition:transform .15s, box-shadow .15s;
            display:flex; align-items:center; justify-content:center; gap:8px;
        }
        .of-add-btn:hover {
            background:linear-gradient(135deg,#00c9a2,#00b894) !important;
            color:#fff !important;
            transform:translateY(-1px); box-shadow:0 6px 18px rgba(0,184,148,.45);
            outline:none;
        }

        /* ── Cart ── */
        #cart-empty-state {
            text-align:center; padding:36px 20px; color:#aab;
        }
        #cart-empty-state i { font-size:48px; opacity:.25; display:block; margin-bottom:10px; }

        /* Cart header */
        .cart-header-row {
            display:grid;
            grid-template-columns: 2.5fr 1fr 1.4fr 1fr 0.8fr 1fr 1fr 0.8fr 1fr 36px;
            gap:4px;
            background:linear-gradient(135deg,#e65100,#f9a826);
            border-radius:10px 10px 0 0;
            padding:9px 12px;
            font-size:11px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:.4px;
        }
        /* Cart item rows — generated by oferta.js */
        .cart-item-row {
            display:grid;
            grid-template-columns: 2.5fr 1fr 1.4fr 1fr 0.8fr 1fr 1fr 0.8fr 1fr 36px;
            gap:4px;
            background:#fff;
            border-bottom:1px solid #f0f0f4;
            padding:8px 12px;
            align-items:center;
            transition:background .15s;
        }
        .cart-item-row:hover { background:#fafbff; }
        .cart-item-row select {
            -webkit-appearance:none; -moz-appearance:none; appearance:none;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%236b7280' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
            background-repeat:no-repeat;
            background-position:right 8px center;
            background-size:10px;
            padding-right:24px !important;
        }
        .cart-item-row input, .cart-item-row select {
            border:1.5px solid #dde2ec; border-radius:7px;
            padding:5px 7px; font-size:12px; color:#2d3748;
            background-color:#fafbfc; width:100%;
        }
        .cart-item-row input:focus, .cart-item-row select:focus {
            border-color:#e65100; outline:none;
            box-shadow:0 0 0 2px rgba(230,81,0,.12);
        }
        .cart-item-row input[readonly] { background:#f1f3f7; color:#6b7280; }
        .prod-name-display {
            font-size:12px; color:#2d3748; font-weight:600;
            word-break:break-word; white-space:normal; line-height:1.4;
        }
        .cart-del-btn {
            background:none; border:none; color:#e74c3c; cursor:pointer;
            font-size:16px; padding:4px; border-radius:6px;
            transition:background .15s;
        }
        .cart-del-btn:hover { background:#fdecea; }

        /* Desktop: wrapper divs are transparent to the grid */
        @media (min-width:768px) {
            .cir-info, .cir-fields, .cir-del { display:contents; }
        }

        /* Mobile cart */
        @media (max-width:767px) {
            .cart-header-row { display:none !important; }
            .cart-item-row {
                display:flex; flex-direction:column;
                border-radius:12px; margin:0 4px 12px; padding:0;
                position:relative; background:#fff;
                box-shadow:0 2px 14px rgba(0,0,0,.1); overflow:hidden;
            }
            .cir-info {
                background:linear-gradient(135deg,#e65100,#f9a826);
                padding:7px 40px 7px 12px;
            }
            .cir-info [data-label="Producto"] input[type="text"],
            .cir-info [data-label="Producto"] .prod-name-display {
                background:transparent !important; border:none !important; box-shadow:none !important;
                color:#fff !important; font-weight:700 !important; font-size:12px !important;
                padding:0 !important; width:100%;
            }
            .cir-info [data-label="Bodega"] input[type="text"] {
                background:transparent !important; border:none !important; box-shadow:none !important;
                color:rgba(255,255,255,.75) !important; font-size:9.5px !important; padding:0 !important;
            }
            .cir-fields {
                display:flex; flex-direction:row;
                overflow-x:auto; overflow-y:hidden;
                -webkit-overflow-scrolling:touch; scrollbar-width:none;
                background:#f4f7fe; border-top:2px solid #e65100;
            }
            .cir-fields::-webkit-scrollbar { display:none; }
            .cir-fields [data-label] {
                flex:0 0 auto; min-width:62px;
                padding:4px 6px; border-right:1px solid #e0e8f7;
            }
            .cir-fields [data-label]::before {
                content:attr(data-label); display:block;
                font-size:7.5px; font-weight:800; color:#9ca3af;
                text-transform:uppercase; letter-spacing:.3px; margin-bottom:2px;
            }
            .cir-fields input, .cir-fields select {
                font-size:10.5px !important; padding:2px 4px !important;
                width:100% !important;
            }
            .cir-del { position:absolute; top:8px; right:8px; z-index:5; }
            .cir-del .cart-del-btn {
                background:rgba(255,255,255,.22) !important; color:#fff !important;
                border-radius:50%; width:28px; height:28px;
                display:flex; align-items:center; justify-content:center;
                font-size:12px; padding:0;
            }
        }

        /* ── Totals summary ── */
        .of-totals-card {
            background:#fff; border:1.5px solid #e8eaef;
            border-radius:14px; overflow:hidden;
            box-shadow:0 4px 20px rgba(0,0,0,.07);
        }
        .of-totals-header {
            background:linear-gradient(135deg,#2d3748,#4a5568);
            padding:12px 20px; color:#fff; font-size:13px; font-weight:700;
        }
        .of-totals-body { padding:16px 20px; }
        .of-total-row {
            display:flex; justify-content:space-between; align-items:center;
            padding:7px 0; border-bottom:1px solid #f0f2f5; font-size:13px;
        }
        .of-total-row:last-child { border-bottom:none; }
        .of-total-row .lbl { color:#6b7280; font-weight:500; }
        .of-total-row .val {
            font-weight:700; color:#1a202c;
            background:#f7f8fa; border:1px solid #e8eaef;
            border-radius:7px; padding:4px 12px; font-size:13px;
            min-width:130px; text-align:right; font-family:monospace;
        }
        .of-total-grand .lbl { font-size:15px; font-weight:800; color:#1a202c; }
        .of-total-grand .val {
            background:linear-gradient(135deg,#1ab394,#0fa37a);
            color:#fff; font-size:15px; border:none;
            box-shadow:0 3px 10px rgba(26,179,148,.3);
        }

        /* ── Save button ── */
        .of-save-btn {
            background:linear-gradient(135deg,#e65100,#f9a826);
            border:none; border-radius:12px; color:#fff;
            font-size:15px; font-weight:800;
            padding:14px 36px; cursor:pointer;
            box-shadow:0 5px 20px rgba(230,81,0,.4);
            transition:transform .15s, box-shadow .15s;
            display:flex; align-items:center; gap:10px; width:100%; justify-content:center;
        }
        .of-save-btn:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(230,81,0,.5); }
        .of-save-btn:disabled { opacity:.6; cursor:not-allowed; transform:none; }

        /* Select2 overrides */
        .select2-container .select2-selection--single {
            border:1.5px solid #dde2ec !important; border-radius:9px !important;
            height:38px !important; background:#fafbfc !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height:36px !important; font-size:13px; color:#2d3748;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height:36px !important;
        }

        /* History panel */
        .of-history { border-radius:10px; border:1px solid #e8eaef; overflow:hidden; }
        .of-history-head {
            background:linear-gradient(135deg,#e65100,#f9a826);
            padding:8px 14px; color:#fff; font-size:12px; font-weight:700;
        }
        .of-history-body { padding:10px 14px; max-height:200px; overflow-y:auto; }
    </style>
    @endpush

    {{-- ===== PAGE HEADER ===== --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>
                <i class="fa fa-file-text text-warning"></i>
                Nueva Prefactura
                @if($cotizacionId)
                    &nbsp;<span class="text-muted" style="font-size:16px; font-weight:400;">— Oferta Ganadora #{{ $cotizacionId }}</span>
                @endif
            </h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Flujo</li>
                <li class="breadcrumb-item active"><strong>Nueva Prefactura</strong></li>
            </ol>
        </div>
        <div class="col-lg-2 d-flex align-items-center justify-content-end">
            <a href="{{ route('flujo.ventas') }}" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- Validity + origin banner --}}
    <div style="background:linear-gradient(135deg,#e65100 0%,#f9a826 100%);
                padding:11px 28px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <span style="color:#fff; font-size:13px;">
            <i class="fa fa-clock-o mr-1"></i>
            @if($cotizacionId)
                Prefactura generada desde la <strong>Oferta #{{ $cotizacionId }}</strong>
                @if($clientePrecargado)
                    &nbsp;&bull;&nbsp;<i class="fa fa-building-o mr-1"></i>{{ $clientePrecargado['nombre'] }}
                @endif
                &nbsp;&bull;&nbsp;
            @endif
            Tiempo de validez: <strong>{{ $descripcionValidez }}</strong> ({{ $diasValidez }} días)
        </span>
        <a href="{{ route('flujo.ofertas') }}" style="margin-left:auto; color:#fff; font-size:12px; opacity:.85;">
            <i class="fa fa-list-alt mr-1"></i> Ver historial de ofertas
        </a>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        <form onkeydown="return event.key != 'Enter';" autocomplete="off"
              id="crear_prefactura" name="crear_prefactura" data-parsley-validate>

            <input type="hidden" id="tipo_venta_id"  name="tipo_venta_id"  value="">
            <input type="hidden" id="cotizacion_id"  name="cotizacion_id"  value="{{ $cotizacionId }}">
            <input type="hidden" id="flujo_id"       name="flujo_id"       value="{{ $flujoId }}">

            {{-- ==============================
                 CARD 1 – Datos del cliente
            ============================== --}}
            <div class="of-card">
                <div class="of-card-title">
                    <i class="fa fa-building-o text-primary"></i> Datos del cliente
                    <span id="categoria_cliente_nombre" class="badge ml-auto"
                          style="background:rgba(230,81,0,.1); color:#e65100; border-radius:10px; font-size:11px; padding:3px 10px;"></span>
                </div>
                <div class="row">
                    {{-- Cliente --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">Cliente <span class="req">*</span></label>
                        @if($clientePrecargado)
                            <div class="of-input d-flex align-items-center"
                                 style="background:#f8f9fa; cursor:not-allowed; gap:6px; color:#495057; font-weight:600; font-size:13px; overflow:hidden;">
                                <i class="fa fa-lock text-muted" style="font-size:11px; flex-shrink:0;"></i>
                                <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                      title="{{ $clientePrecargado['nombre'] }}">
                                    {{ $clientePrecargado['nombre'] }}
                                </span>
                            </div>
                            <input type="hidden" id="seleccionarCliente" name="seleccionarCliente"
                                   value="{{ $clientePrecargado['id'] }}">
                        @else
                            <select id="seleccionarCliente" name="seleccionarCliente"
                                    class="of-select" data-parsley-required
                                    onchange="obtenerDatosCliente()">
                                <option value="" selected disabled>--Seleccionar cliente--</option>
                            </select>
                        @endif
                    </div>

                    {{-- Nombre cliente --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">Nombre del cliente <span class="req">*</span></label>
                        <input class="of-input" type="text"
                               id="nombre_cliente_ventas" name="nombre_cliente_ventas"
                               value="{{ $clientePrecargado['nombre'] ?? '' }}"
                               data-parsley-required readonly>
                    </div>

                    {{-- RTN --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">RTN</label>
                        <input class="of-input" type="text"
                               id="rtn_ventas" name="rtn_ventas"
                               value="{{ $clientePrecargado['rtn'] ?? '' }}" readonly>
                    </div>

                    {{-- Vendedor --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">Vendedor</label>
                        <select name="vendedor" id="vendedor" class="of-select">
                            <option value="" selected disabled>--Seleccionar vendedor--</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    {{-- Fecha emisión --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">Fecha de emisión <span class="req">*</span></label>
                        <input class="of-input" type="date"
                               id="fecha_emision" name="fecha_emision"
                               value="{{ date('Y-m-d') }}" data-parsley-required>
                    </div>

                    {{-- Descuento --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">Descuento %</label>
                        <input class="of-input" type="number" min="0" max="50" value="0"
                               id="porDescuento" name="porDescuento"
                               onchange="calcularTotalesInicioPagina()">
                    </div>

                    {{-- Nota --}}
                    <div class="col-12 col-lg-6 mb-3">
                        <label class="of-label">Nota</label>
                        <textarea class="of-input" id="nota" name="nota" rows="2"
                                  maxlength="250" style="resize:vertical; height:auto; min-height:38px;"></textarea>
                    </div>
                </div>

                {{-- Hidden fields required by oferta.js --}}
                <div style="display:none;">
                    <select id="tipoPagoVenta" name="tipoPagoVenta"></select>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" value="">
                </div>
            </div>

            {{-- ==============================
                 CARD 2 – Agregar producto
            ============================== --}}
            <div class="of-card">
                <div class="of-card-title">
                    <i class="fa fa-plus-circle text-success"></i> Agregar producto al carrito
                </div>
                <div class="row align-items-start">

                    {{-- Search --}}
                    <div class="col-12 col-md-5 col-lg-4 mb-3">
                        <label class="of-label">Buscar producto <span class="req">*</span></label>
                        <div class="of-search-wrap">
                            <input type="text" id="codigoProductoCotizacion" class="of-input"
                                   placeholder="ID o nombre del producto..."
                                   autocomplete="off"
                                   onkeydown="if(event.key==='Enter'){buscarPorCodigoCotizacion(this.value);return false;}">
                            <button type="button" class="of-search-btn" title="Buscar"
                                    onclick="limpiarProductoCotizacion(); window['abrirBuscador_buscadorProductoCotizacion'](document.getElementById('codigoProductoCotizacion').value||'')">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                        <small id="productoSeleccionadoCotizacion"
                               class="text-success font-weight-bold mt-1 d-block d-none"
                               style="font-size:11px;"></small>
                        <select id="seleccionarProducto" name="seleccionarProducto" hidden>
                            <option value="" selected disabled></option>
                        </select>
                        <x-buscador-producto id-modal="buscadorProductoCotizacion" callback="alSeleccionarProductoCotizacion" />
                    </div>

                    {{-- Category --}}
                    <div class="col-12 col-md-4 col-lg-3 mb-3">
                        <label class="of-label">Categoría precio <span class="req">*</span></label>
                        <select id="categoria_cliente_venta_id" name="categoria_cliente_venta_id"
                                class="of-select" onchange="listaCategoríaClientes()">
                            <option value="" selected disabled>--Seleccione un producto primero--</option>
                        </select>
                    </div>

                    {{-- Warehouse --}}
                    <div class="col-12 col-md-3 col-lg-3 mb-3">
                        <label class="of-label">Bodega <span class="req">*</span></label>
                        <select id="bodega" name="bodega" class="of-select" onchange="prueba()">
                            <option value="" selected disabled>--Seleccione categoría primero--</option>
                        </select>
                    </div>

                    {{-- Add button --}}
                    <div class="col-12 col-lg-2 mb-3">
                        <label class="of-label">&nbsp;</label>
                        <button type="button" id="botonAdd" class="of-add-btn d-none"
                                onclick="agregarProductoCarrito()">
                            <i class="fa fa-cart-plus" style="font-size:16px;"></i> Agregar
                        </button>
                    </div>
                </div>

                {{-- Product image + history --}}
                <div class="row" id="carouselWrap">
                    <div class="col-12 col-md-4 col-lg-3 mb-2">
                        <div id="carouselProducto" class="carousel slide of-img-carousel" data-ride="carousel">
                            <div id="bloqueImagenes" class="carousel-inner"></div>
                            <a class="carousel-control-prev" href="#carouselProducto" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </a>
                            <a class="carousel-control-next" href="#carouselProducto" role="button" data-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </a>
                            <a id="detalleProducto" href="" class="d-none" target="_blank">
                                <i class="fa fa-external-link"></i> Ver detalles
                            </a>
                        </div>
                    </div>
                    <div class="col-12 col-md-8 col-lg-9">
                        <div id="historialPreciosPanel" class="of-history d-none">
                            <div class="of-history-head">
                                <i class="fa fa-history mr-1"></i>
                                Últimas 5 ventas de este producto a este cliente
                            </div>
                            <div class="of-history-body" id="historialPreciosCuerpo">
                                <p class="text-muted small">Cargando...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==============================
                 CARD 3 – Carrito de productos
            ============================== --}}
            <div class="of-card" style="padding:0; overflow:hidden;">
                <div style="padding:16px 24px 12px; border-bottom:1px solid #f0f2f5;
                            display:flex; align-items:center; gap:8px;">
                    <span class="of-card-title mb-0">
                        <i class="fa fa-shopping-cart text-warning"></i> Carrito de productos
                    </span>
                    <span id="cart-count-badge"
                          style="margin-left:auto; background:#e65100; color:#fff; border-radius:20px;
                                 font-size:11px; font-weight:700; padding:2px 10px;">
                        0 producto(s)
                    </span>
                </div>

                {{-- Column headers --}}
                <div class="cart-header-row d-none d-md-grid">
                    <div>Producto</div>
                    <div>Bodega</div>
                    <div>Lista</div>
                    <div>Precio</div>
                    <div>Cantidad</div>
                    <div>Unidad</div>
                    <div>Sub total</div>
                    <div>ISV</div>
                    <div>Total</div>
                    <div></div>
                </div>

                {{-- Dynamic product rows --}}
                <div id="divProductos" style="padding:0 0 4px;">
                    <div id="cart-empty-state">
                        <i class="fa fa-inbox"></i>
                        <p style="font-size:13px;">No hay productos en el carrito.<br>
                        <small>Use el buscador de arriba para agregar productos.</small></p>
                    </div>
                </div>
            </div>

            {{-- ==============================
                 TOTALS + SAVE
            ============================== --}}
            <div class="row">
                <div class="col-12 col-lg-6 offset-lg-6">
                    <div class="of-totals-card mb-4">
                        <div class="of-totals-header">
                            <i class="fa fa-calculator mr-2"></i> Resumen de totales
                        </div>
                        <div class="of-totals-body">
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-tag mr-1 text-muted"></i> Descuento</span>
                                <input type="text" id="descuentoMostrar" name="descuentoMostrar"
                                       class="val" readonly placeholder="L. 0.00">
                                <input type="hidden" id="descuentoGeneral" name="descuentoGeneral">
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-list mr-1 text-muted"></i> Sub Total</span>
                                <input type="text" id="subTotalGeneralMostrar" name="subTotalGeneralMostrar"
                                       class="val" readonly placeholder="L. 0.00">
                                <input type="hidden" id="subTotalGeneral" name="subTotalGeneral">
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-file-text-o mr-1 text-muted"></i> Sub Total Grabado</span>
                                <input type="text" id="subTotalGeneralGrabadoMostrar" name="subTotalGeneralGrabadoMostrar"
                                       class="val" readonly placeholder="L. 0.00">
                                <input type="hidden" id="subTotalGeneralGrabado" name="subTotalGeneralGrabado">
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-minus-circle mr-1 text-muted"></i> Sub Total Exento</span>
                                <input type="text" id="subTotalGeneralExcentoMostrar" name="subTotalGeneralExcentoMostrar"
                                       class="val" readonly placeholder="L. 0.00">
                                <input type="hidden" id="subTotalGeneralExcento" name="subTotalGeneralExcento">
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-percent mr-1 text-muted"></i> ISV</span>
                                <input type="text" id="isvGeneralMostrar" name="isvGeneralMostrar"
                                       class="val" readonly placeholder="L. 0.00">
                                <input type="hidden" id="isvGeneral" name="isvGeneral">
                            </div>
                            <div class="of-total-row of-total-grand" style="padding-top:12px; margin-top:4px;">
                                <span class="lbl">TOTAL</span>
                                <input type="text" id="totalGeneralMostrar" name="totalGeneralMostrar"
                                       class="val" readonly placeholder="L. 0.00">
                                <input type="hidden" id="totalGeneral" name="totalGeneral">
                            </div>
                        </div>
                    </div>

                    <button id="guardar_oferta_btn" type="button" class="of-save-btn" onclick="guardarVenta()">
                        <i class="fa fa-save" style="font-size:18px;"></i>
                        Guardar Prefactura
                    </button>
                </div>
            </div>

        </form>
    </div>

    {{-- ── Modal éxito prefactura ── --}}
    <div id="modalExitoPrefactura" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius:16px; overflow:hidden; border:none;">
                <div class="modal-header"
                     style="background:linear-gradient(135deg,#e65100,#f9a826); border:none; padding:16px 24px;">
                    <h5 class="modal-title" style="color:#fff; font-weight:800; margin:0;">
                        <i class="fa fa-check-circle mr-2"></i> Prefactura Guardada
                    </h5>
                </div>
                <div class="modal-body" style="padding:28px; text-align:center;">
                    <div style="font-size:48px; margin-bottom:16px;">🎉</div>
                    <h5 style="font-weight:800; color:#2c3e50;">¡Prefactura creada correctamente!</h5>
                    <p style="color:#555; font-size:14px; margin-bottom:24px;">
                        Prefactura <strong id="nroPrefacturaModal"></strong> guardada.
                        El inventario fue reservado.
                    </p>
                    <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                        <a id="btnImprimirPrefactura" href="#" target="_blank"
                           style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                                  border-radius:20px; padding:9px 24px; font-size:13px; font-weight:700;
                                  text-decoration:none; display:inline-block;">
                            <i class="fa fa-print mr-1"></i> Imprimir Prefactura
                        </a>
                        <button type="button" onclick="$('#modalExitoPrefactura').modal('hide'); window.location='{{ route('flujo.ventas') }}';"
                                style="background:#f0f0f0; color:#555; border:none; border-radius:20px;
                                       padding:9px 24px; font-size:13px; cursor:pointer;">
                            <i class="fa fa-check mr-1"></i> Finalizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    // ── Cart-related helpers (must be defined before oferta.js loads) ────
    function actualizarCartUI() {
        var count = (typeof arregloIdInputs !== 'undefined') ? arregloIdInputs.length : 0;
        var badge = document.getElementById('cart-count-badge');
        var empty = document.getElementById('cart-empty-state');
        if (badge) badge.textContent = count + ' producto(s)';
        if (empty) empty.style.display = count === 0 ? 'block' : 'none';
    }

    // Product search helpers
    function limpiarProductoCotizacion() {
        var sel = document.getElementById('seleccionarProducto');
        if (sel) sel.innerHTML = '<option value="" selected disabled></option>';
        var inp = document.getElementById('codigoProductoCotizacion');
        if (inp) inp.value = '';
        var lbl = document.getElementById('productoSeleccionadoCotizacion');
        if (lbl) { lbl.classList.add('d-none'); lbl.textContent = ''; }
        var panel = document.getElementById('historialPreciosPanel');
        if (panel) panel.classList.add('d-none');
    }

    function alSeleccionarProductoCotizacion(producto) {
        var select = document.getElementById('seleccionarProducto');
        select.innerHTML = '<option value="' + producto.id + '" selected>' + producto.nombre + '</option>';
        var inp = document.getElementById('codigoProductoCotizacion');
        if (inp) inp.value = producto.nombre;
        var label = document.getElementById('productoSeleccionadoCotizacion');
        if (label) {
            label.textContent = '\u2713 ' + producto.nombre + ' (ID: ' + producto.id + ')';
            label.classList.remove('d-none');
        }
        // cargarCategoriasProducto and obtenerImagenes come from oferta.js
        if (typeof cargarCategoriasProducto === 'function') cargarCategoriasProducto();
        if (typeof obtenerImagenes === 'function') obtenerImagenes(producto.id);
        cargarHistorialPreciosPref();
    }

    function buscarPorCodigoCotizacion(cod) {
        cod = String(cod).trim();
        if (!cod) { window['abrirBuscador_buscadorProductoCotizacion'](''); return; }
        axios.get('/productos/buscar', { params: { q: cod, page: 1 } })
            .then(function(r) {
                var items = r.data.data;
                var exact = items.find(function(p) { return String(p.id) === cod; });
                if (exact) { alSeleccionarProductoCotizacion(exact); }
                else if (items.length === 1) { alSeleccionarProductoCotizacion(items[0]); }
                else { window['abrirBuscador_buscadorProductoCotizacion'](cod); }
            });
    }

    function cargarHistorialPreciosPref() {
        var productoId = document.getElementById('seleccionarProducto').value;
        var clienteId  = document.getElementById('seleccionarCliente').value;
        var panel  = document.getElementById('historialPreciosPanel');
        var cuerpo = document.getElementById('historialPreciosCuerpo');
        if (!panel || !cuerpo) return;
        if (!productoId || !clienteId) { panel.classList.add('d-none'); return; }
        cuerpo.innerHTML = '<p class="text-muted small"><i class="fa fa-spinner fa-spin"></i> Cargando...</p>';
        panel.classList.remove('d-none');
        axios.post('/estatal/historial/precios', { cliente_id: clienteId, producto_id: productoId })
            .then(function(response) {
                var rows = response.data.historial;
                if (!rows || rows.length === 0) {
                    cuerpo.innerHTML = '<p class="text-muted small">Sin ventas previas de este producto a este cliente.</p>';
                    return;
                }
                var fmt = new Intl.NumberFormat('es-HN', { style:'currency', currency:'HNL', minimumFractionDigits:2 });
                var html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0" style="font-size:.82rem;"><thead><tr style="background:#f8f9fc;"><th>Fecha</th><th>Factura</th><th>Precio U.</th><th>Cant.</th><th>Total</th><th>Cat.</th></tr></thead><tbody>';
                rows.forEach(function(r) {
                    html += '<tr><td>' + r.fecha_emision + '</td><td>' + r.numero_factura + '</td>'
                          + '<td class="text-right font-weight-bold text-success">' + fmt.format(r.precio_unidad) + '</td>'
                          + '<td class="text-center">' + r.cantidad + '</td>'
                          + '<td class="text-right">' + fmt.format(r.total) + '</td>'
                          + '<td><span class="badge badge-secondary" style="font-size:9px;">' + r.categoria + '</span></td></tr>';
                });
                html += '</tbody></table></div>';
                cuerpo.innerHTML = html;
            })
            .catch(function() { cuerpo.innerHTML = '<p class="text-danger small">Error al cargar historial.</p>'; });
    }

    // obtenerDatosCliente (called when client changes)
    function obtenerDatosCliente() {
        var idCliente = document.getElementById('seleccionarCliente').value;
        if (!idCliente) return;
        axios.post('/estatal/datos/cliente', { id: idCliente })
            .then(function(response) {
                var data = response.data.datos;
                if (data.id != 1) {
                    var nombreEl = document.getElementById('nombre_cliente_ventas');
                    var rtnEl    = document.getElementById('rtn_ventas');
                    if (nombreEl) nombreEl.value = data.nombre;
                    if (rtnEl)    rtnEl.value    = data.rtn || '';
                }
                var catSel = document.getElementById('categoria_cliente_venta_id');
                if (catSel) {
                    $(catSel).data('categoria-cliente-id', data.idcategoriacliente);
                    $(catSel).empty().append(
                        new Option(data.nombre_categoria, data.idcategoriacliente, true, true)
                    );
                }
                var catNombre = document.getElementById('categoria_cliente_nombre');
                if (catNombre) catNombre.textContent = data.nombre_categoria || '';
                window.diasCredito = data.dias_credito;
                window.dispatchEvent(new CustomEvent('cliente-datos-cargados'));
            })
            .catch(function(err) { console.error('obtenerDatosCliente:', err); });
    }
    </script>

    {{-- Select2 for client (only when NOT pre-loaded) --}}
    @if(!$clientePrecargado)
    <script>
    $(function() {
        $('#seleccionarCliente').select2({
            placeholder: '-- Buscar cliente --',
            allowClear: true,
            ajax: {
                url: '/cotizacion/clientes',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { search: params.term || '', type: 'public', page: params.page || 1 };
                },
                processResults: function(data) { return { results: data.results || data }; }
            }
        }).on('change', function() { obtenerDatosCliente(); });
    });
    </script>
    @endif

    <script>var public_path = "{{ asset('catalogo/') }}";</script>
    <script src="{{ asset('js/js_proyecto/flujo/oferta.js') }}"></script>

    <script>
    // Override guardarVenta to POST to prefactura endpoint
    window.guardarVenta = function guardarVenta() {
        if (!arregloIdInputs || arregloIdInputs.length === 0) {
            Swal.fire({ icon:'warning', title:'Sin productos',
                        text:'Debe agregar al menos un producto al carrito.' });
            return;
        }
        var btn = document.getElementById('guardar_oferta_btn');
        if (btn) btn.disabled = true;

        var form   = document.getElementById('crear_prefactura');
        var data   = new FormData(form);
        var clVal  = document.getElementById('seleccionarCliente').value;
        if (clVal) data.set('seleccionarCliente', clVal);

        arregloIdInputs.forEach(function(idx) {
            var uSel = document.getElementById('unidad' + idx);
            if (uSel) data.append('idUnidadVenta' + idx,
                                  uSel.options[uSel.selectedIndex].getAttribute('data-id'));
            var pSel = document.getElementById('precios' + idx);
            if (pSel) data.append('idPrecioSeleccionado' + idx,
                                  pSel.options[pSel.selectedIndex].getAttribute('data-id'));
        });
        data.append('numeroInputs',    numeroInputs);
        data.append('arregloIdInputs', arregloIdInputs.toString());

        var formObj = {};
        data.forEach(function(v, k) { formObj[k] = v; });

        axios.post('/flujo/prefactura/guardar', formObj,
                   { headers: { 'content-type': 'application/json' } })
            .then(function(response) {
                var d = response.data;
                document.getElementById('nroPrefacturaModal').textContent = '#' + d.idPrefactura;
                document.getElementById('btnImprimirPrefactura').href = '/prefactura/imprimir/' + d.idPrefactura;
                $('#modalExitoPrefactura').modal('show');
            })
            .catch(function(err) {
                if (btn) btn.disabled = false;
                var d = err.response ? err.response.data : {};
                Swal.fire({ icon: d.icon || 'error',
                            title: d.title || 'Error',
                            text:  d.text  || 'Error al guardar la prefactura.' });
            });
    };

    // Wrap eliminarInput to keep count badge updated
    (function() {
        var _orig = window.eliminarInput;
        window.eliminarInput = function(id) {
            if (_orig) _orig.call(this, id);
            setTimeout(actualizarCartUI, 60);
        };
    })();

    // Load client data on page load (for pre-loaded client)
    document.addEventListener('DOMContentLoaded', function() {
        obtenerDatosCliente();
        actualizarCartUI();
    });
    </script>

    {{-- ── Auto-cart: load products from winning offer ── --}}
    @if(count($productosParaCarrito) > 0)
    <script>
    (function() {
        var _autoAgregado = false;

        function cargarProductosDesdeOferta() {
            if (_autoAgregado) return;
            _autoAgregado = true;

            var productos = @json($productosParaCarrito);
            if (!productos || !productos.length) return;

            var chain = Promise.resolve();
            productos.forEach(function(prod) {
                chain = chain.then(function() { return agregarProductoDesdeOferta(prod); });
            });
            chain.then(function() {
                setTimeout(function() {
                    if (typeof totalesGenerales === 'function') totalesGenerales();
                    actualizarCartUI();
                }, 200);
                Swal.fire({
                    icon: 'success',
                    title: 'Productos cargados',
                    text: productos.length + ' producto(s) de la oferta ganadora cargados.',
                    timer: 2500, showConfirmButton: false, toast: true, position: 'top-end'
                });
            });
        }

        function agregarProductoDesdeOferta(prod) {
            return new Promise(function(resolve) {
                if (!prod.producto_id) { resolve(); return; }

                var categoriaId = document.getElementById('categoria_cliente_venta_id').value
                    || $(document.getElementById('categoria_cliente_venta_id')).data('categoria-cliente-id')
                    || '';

                axios.post('/ventas/datos/producto', {
                    idProducto: prod.producto_id,
                    categoria_cliente_venta_id: categoriaId
                })
                .then(function(response) {
                    var producto      = response.data.producto;
                    var arrayUnidades = response.data.unidades;
                    numeroInputs += 1;
                    var idx = numeroInputs;

                    // Build unit options — prefer the unit from the original offer
                    var htmlSelectUnidades = '';
                    arrayUnidades.forEach(function(u) {
                        var isSel = String(u.idUnidadVenta) === String(prod.unidad_medida_venta_id);
                        var isDefault = u.valor_defecto == 1 && htmlSelectUnidades.indexOf('selected') === -1;
                        var selAttr = (isSel || isDefault) ? ' selected' : '';
                        htmlSelectUnidades += '<option' + selAttr + ' value="' + u.id
                            + '" data-id="' + u.idUnidadVenta + '">' + u.nombre + '</option>';
                    });

                    var htmlPrecios = '<option value="' + producto.precio1
                        + '" data-id="p1" selected>A</option>';
                    var precioUsar   = prod.precio_unidad   || producto.precio1;
                    var cantidadUsar = prod.cantidad        || 1;
                    var bodegaTexto  = prod.nombre_bodega   || '';
                    var idBodega     = prod['Bodega_id']    || '';
                    var idSeccion    = prod.seccion_id      || '';

                    var html = '<div id="' + idx + '" class="cart-item-row">'
                        + '<div class="cir-info">'
                        + '  <div data-label="Producto" style="min-width:0;">'
                        + '    <input id="idProducto' + idx + '" name="idProducto' + idx + '" type="hidden" value="' + producto.id + '">'
                        + '    <input id="precios_producto_carga_id' + idx + '" name="precios_producto_carga_id' + idx + '" type="hidden" value="' + (prod.precios_producto_carga_id || '') + '">'
                        + '    <input type="hidden" id="nombre' + idx + '" name="nombre' + idx + '" value="' + String(producto.nombre).replace(/"/g, '&quot;') + '">'
                        + '    <div class="prod-name-display" title="' + String(producto.nombre).replace(/"/g, '&quot;') + '">' + producto.nombre + '</div>'
                        + '  </div>'
                        + '  <div data-label="Bodega">'
                        + '    <input type="text" value="' + bodegaTexto + '" id="bodega' + idx + '" name="bodega' + idx + '" readonly'
                        + '      style="font-size:11px; background:#f7f8fa; border:1.5px solid #dde2ec; border-radius:7px; padding:5px 7px; width:100%;">'
                        + '  </div>'
                        + '</div>'
                        + '<div class="cir-fields">'
                        + '  <div data-label="Lista">'
                        + '    <select name="precios' + idx + '" id="precios' + idx + '" data-parsley-required'
                        + '      onchange="validacionPrecio(precios' + idx + ', precio' + idx + ')">'
                        + '      ' + htmlPrecios
                        + '    </select>'
                        + '  </div>'
                        + '  <div data-label="Precio">'
                        + '    <input type="number" id="precio' + idx + '" name="precio' + idx + '"'
                        + '      value="' + precioUsar + '" data-parsley-required step="any" autocomplete="off"'
                        + '      onchange="calcularTotales(precio' + idx + ',cantidad' + idx + ',' + producto.isv + ',unidad' + idx + ',' + idx + ',restaInventario' + idx + ')">'
                        + '  </div>'
                        + '  <div data-label="Cantidad">'
                        + '    <input type="number" id="cantidad' + idx + '" name="cantidad' + idx + '"'
                        + '      value="' + cantidadUsar + '" min="1" data-parsley-required autocomplete="off"'
                        + '      onchange="calcularTotales(precio' + idx + ',cantidad' + idx + ',' + producto.isv + ',unidad' + idx + ',' + idx + ',restaInventario' + idx + ')">'
                        + '  </div>'
                        + '  <div data-label="Unidad">'
                        + '    <select name="unidad' + idx + '" id="unidad' + idx + '" data-parsley-required'
                        + '      onchange="calcularTotales(precio' + idx + ',cantidad' + idx + ',' + producto.isv + ',unidad' + idx + ',' + idx + ',restaInventario' + idx + ')">'
                        + '      ' + htmlSelectUnidades
                        + '    </select>'
                        + '  </div>'
                        + '  <div data-label="Sub Total">'
                        + '    <input type="text" id="subTotalMostrar' + idx + '" name="subTotalMostrar' + idx + '" readonly placeholder="0.00"'
                        + '      style="background:#f7f8fa; color:#1ab394; font-weight:700;">'
                        + '    <input id="subTotal' + idx + '" name="subTotal' + idx + '" type="hidden" value="">'
                        + '    <input type="hidden" id="acumuladoDescuento' + idx + '" name="acumuladoDescuento' + idx + '">'
                        + '  </div>'
                        + '  <div data-label="ISV">'
                        + '    <input type="text" id="isvProductoMostrar' + idx + '" name="isvProductoMostrar' + idx + '" readonly placeholder="0.00">'
                        + '    <input id="isvProducto' + idx + '" name="isvProducto' + idx + '" type="hidden" value="">'
                        + '  </div>'
                        + '  <div data-label="Total">'
                        + '    <input type="text" id="totalMostrar' + idx + '" name="totalMostrar' + idx + '" readonly placeholder="0.00"'
                        + '      style="background:#f7f8fa; color:#6c5ce7; font-weight:700;">'
                        + '    <input id="total' + idx + '" name="total' + idx + '" type="hidden" value="">'
                        + '  </div>'
                        + '</div>'
                        + '<div class="cir-del">'
                        + '  <button class="cart-del-btn" type="button" onclick="eliminarInput(' + idx + ')" title="Eliminar">'
                        + '    <i class="fa fa-trash"></i>'
                        + '  </button>'
                        + '</div>'
                        + '<input id="idBodega' + idx + '"        name="idBodega' + idx + '"        type="hidden" value="' + idBodega + '">'
                        + '<input id="idSeccion' + idx + '"       name="idSeccion' + idx + '"       type="hidden" value="' + idSeccion + '">'
                        + '<input id="restaInventario' + idx + '" name="restaInventario' + idx + '" type="hidden" value="' + cantidadUsar + '">'
                        + '<input id="isv' + idx + '"             name="isv' + idx + '"             type="hidden" value="' + producto.isv + '">'
                        + '</div>';

                    arregloIdInputs.splice(idx, 0, idx);
                    document.getElementById('divProductos').insertAdjacentHTML('beforeend', html);
                    var emptyState = document.getElementById('cart-empty-state');
                    if (emptyState) emptyState.style.display = 'none';
                    actualizarCartUI();

                    setTimeout(function() {
                        if (typeof calcularTotales === 'function') {
                            calcularTotales(
                                document.getElementById('precio' + idx),
                                document.getElementById('cantidad' + idx),
                                producto.isv,
                                document.getElementById('unidad' + idx),
                                idx,
                                document.getElementById('restaInventario' + idx)
                            );
                        }
                        resolve();
                    }, 60);
                })
                .catch(function(err) {
                    console.error('Auto-cart error para producto ' + prod.producto_id, err);
                    resolve();
                });
            });
        }

        // Fire when client data is ready
        window.addEventListener('cliente-datos-cargados', function handler() {
            window.removeEventListener('cliente-datos-cargados', handler);
            cargarProductosDesdeOferta();
        });
    })();
    </script>
    @endif
    @endpush
</div>
