<div>
    @push('styles')
    <style>
        /* ── Spin-arrow removal ── */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
        input[type=number] { -moz-appearance:textfield; }

        /* â”€â”€ Form section cards â”€â”€ */
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

        /* â”€â”€ Inputs â”€â”€ */
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
            border-color:#1a7efb; box-shadow:0 0 0 3px rgba(26,126,251,.12);
            outline:none; background-color:#fff !important;
        }
        .of-input[readonly] { background:#f1f3f7; color:#6b7280; cursor:default; }
        .of-label {
            font-size:12px; font-weight:600; color:#4a5568;
            margin-bottom:4px; display:block;
        }
        .of-label .req { color:#e74c3c; margin-left:2px; }

        /* â”€â”€ Product search â”€â”€ */
        .of-search-wrap { position:relative; }
        .of-search-wrap input { padding-right:44px; }
        .of-search-btn {
            position:absolute; right:4px; top:50%; transform:translateY(-50%);
            background:linear-gradient(135deg,#1a7efb,#0d6efd);
            border:none; border-radius:7px; color:#fff;
            width:34px; height:30px; cursor:pointer;
            display:flex; align-items:center; justify-content:center;
            font-size:13px;
        }
        .of-search-btn:hover { background:linear-gradient(135deg,#0d6efd,#0a58ca); }

        /* â”€â”€ Product image preview â”€â”€ */
        .of-img-carousel {
            border-radius:12px; overflow:hidden;
            border:1.5px solid #e8eaef; background:#f8f9fc;
            max-height:180px;
            position:relative;
        }
        #detalleProducto {
            position:absolute; bottom:8px; left:50%; transform:translateX(-50%);
            z-index:10;
            background:rgba(26,126,251,.82);
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
            background:rgba(13,110,253,.95);
            transform:translateX(-50%) translateY(-2px);
        }
        .of-img-carousel .carousel-item img {
            width:100%; height:180px; object-fit:contain;
            background:#f8f9fc;
        }
        .of-carousel-ctrl {
            background:rgba(0,0,0,.25); border-radius:50%;
            width:28px; height:28px;
        }

        /* â”€â”€ Add button â”€â”€ */
        .of-add-btn {
            background:linear-gradient(135deg,#00b894,#00896e) !important;
            border:none !important; border-radius:10px; color:#fff !important;
            font-size:14px; font-weight:700;
            padding:11px 22px; cursor:pointer; width:100%;
            box-shadow:0 4px 14px rgba(0,184,148,.35);
            transition:transform .15s, box-shadow .15s;
            display:flex; align-items:center; justify-content:center; gap:8px;
            text-shadow:none;
        }
        .of-add-btn:hover, .of-add-btn:focus {
            background:linear-gradient(135deg,#00c9a2,#00b894) !important;
            color:#fff !important;
            transform:translateY(-1px); box-shadow:0 6px 18px rgba(0,184,148,.45);
            outline:none;
        }

        /* â”€â”€ Cart â”€â”€ */
        #cart-empty-state {
            text-align:center; padding:36px 20px; color:#aab;
        }
        #cart-empty-state i { font-size:48px; opacity:.25; display:block; margin-bottom:10px; }

        /* Cart header */
        .cart-header-row {
            display:grid;
            grid-template-columns: 2fr 1fr 1.2fr 1fr 0.9fr 0.9fr 1fr 0.8fr 1fr 40px;
            gap:4px;
            background:linear-gradient(135deg,#f39c12,#e67e22);
            border-radius:10px 10px 0 0;
            padding:9px 12px;
            font-size:11px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:.4px;
        }
        /* Cart item rows — generated by JS */
        .cart-item-row {
            display:grid;
            grid-template-columns: 2fr 1fr 1.2fr 1fr 0.9fr 0.9fr 1fr 0.8fr 1fr 40px;
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
            border-color:#1a7efb; outline:none;
            box-shadow:0 0 0 2px rgba(26,126,251,.1);
        }
        .cart-item-row input[readonly] { background:#f1f3f7; color:#6b7280; }
        .cart-del-btn {
            background:none; border:none; color:#e74c3c; cursor:pointer;
            font-size:16px; padding:4px; border-radius:6px;
            transition:background .15s;
        }
        .cart-del-btn:hover { background:#fdecea; }

        /* Desktop: wrapper divs are transparent to the grid */
        @media (min-width:768px) {
            .cir-info, .cir-fields, .cir-del { display:contents; }
            .cir-del > .cart-del-btn { display:block; margin:0 auto; }
        }

        /* Mobile cart */
        @media (max-width:767px) {
            .cart-header-row { display:none !important; }

            .cart-item-row {
                display:flex;
                flex-direction:column;
                border-radius:12px;
                margin:0 4px 12px;
                padding:0;
                position:relative;
                background:#fff;
                box-shadow:0 2px 14px rgba(0,0,0,.1);
                overflow:hidden;
            }

            /* Block 1: blue header — product name + bodega merged */
            .cir-info {
                background:linear-gradient(135deg,#1a7efb,#0d6efd);
                padding:7px 40px 7px 12px;
            }
            .cir-info [data-label]::before { display:none !important; }
            .cir-info [data-label="Producto"] { margin-bottom:1px; }
            .cir-info [data-label="Producto"] input[type="text"] {
                background:transparent !important; border:none !important; box-shadow:none !important;
                color:#fff !important; font-weight:700 !important; font-size:12px !important;
                padding:0 !important; width:100%; line-height:1.3;
                white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
            }
            .cir-info [data-label="Bodega"] input[type="text"] {
                background:transparent !important; border:none !important; box-shadow:none !important;
                color:rgba(255,255,255,.75) !important; font-size:9.5px !important;
                padding:0 !important; width:100%; line-height:1.2;
            }

            /* Block 2: horizontal scroll for all fields */
            .cir-fields {
                display:flex;
                flex-direction:row;
                overflow-x:auto;
                overflow-y:hidden;
                -webkit-overflow-scrolling:touch;
                scrollbar-width:none;
                background:#f4f7fe;
                border-top:2px solid #1a7efb;
            }
            .cir-fields::-webkit-scrollbar { display:none; }

            .cir-fields [data-label] {
                flex:0 0 auto;
                min-width:62px;
                padding:4px 6px;
                border-right:1px solid #e0e8f7;
            }
            .cir-fields [data-label]:last-child { border-right:none; }

            .cir-fields [data-label]::before {
                content:attr(data-label);
                display:block;
                font-size:7.5px; font-weight:800; color:#9ca3af;
                text-transform:uppercase; letter-spacing:.3px; margin-bottom:2px;
            }

            /* Inputs/selects inside the scrollable block */
            .cir-fields input, .cir-fields select {
                font-size:10.5px !important; padding:2px 4px !important;
                width:100% !important; min-width:0; line-height:1.2;
            }
            .cir-fields select { height:auto !important; }

            /* Total field: green highlight */
            .cir-fields [data-label="Total"] {
                background:linear-gradient(135deg,#edfaf4,#d8f5e8);
                border-right:none;
            }
            .cir-fields [data-label="Total"]::before { color:#0fa37a !important; }
            .cir-fields [data-label="Total"] input {
                background:transparent !important; border:none !important; box-shadow:none !important;
                color:#0a8a63 !important; font-weight:800 !important; font-size:12px !important; padding:0 !important;
            }
            .cir-fields [data-label="Sub Total"] input,
            .cir-fields [data-label="ISV"] input {
                background:transparent !important; border:none !important; box-shadow:none !important;
                font-weight:700 !important; padding:0 !important;
            }

            /* Delete button: absolute top-right over the blue header */
            .cir-del {
                position:absolute; top:8px; right:8px; z-index:5;
            }
            .cir-del .cart-del-btn {
                background:rgba(255,255,255,.22) !important; color:#fff !important;
                border-radius:50%; width:28px; height:28px;
                display:flex; align-items:center; justify-content:center;
                font-size:12px; padding:0;
            }
        }

        /* â”€â”€ Totals summary â”€â”€ */
        .of-totals-card {
            background:#fff;
            border:1.5px solid #e8eaef;
            border-radius:14px;
            overflow:hidden;
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

        /* â”€â”€ Save button â”€â”€ */
        .of-save-btn {
            background:linear-gradient(135deg,#f39c12,#e67e22);
            border:none; border-radius:12px; color:#fff;
            font-size:15px; font-weight:800;
            padding:14px 36px; cursor:pointer;
            box-shadow:0 5px 20px rgba(243,156,18,.4);
            transition:transform .15s, box-shadow .15s;
            display:flex; align-items:center; gap:10px; width:100%; justify-content:center;
        }
        .of-save-btn:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(243,156,18,.5); }
        .of-save-btn:disabled { opacity:.6; cursor:not-allowed; transform:none; }

        /* Select2 overrides — hide native arrow on select2-managed elements */
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
            background:linear-gradient(135deg,#6c5ce7,#a855f7);
            padding:8px 14px; color:#fff; font-size:12px; font-weight:700;
        }
        .of-history-body { padding:10px 14px; max-height:200px; overflow-y:auto; }

        /* ── Cart grid — wider columns for selects ── */
        .cart-header-row,
        .cart-item-row {
            grid-template-columns: 2.5fr 1fr 1.4fr 1fr 0.8fr 1fr 1fr 0.8fr 1fr 36px;
        }

        /* ── Product name tooltip ── */
        .of-product-name-wrap { position: relative; }
        .of-product-tooltip {
            display: none;
            position: absolute;
            bottom: calc(100% + 6px);
            left: 0;
            z-index: 9999;
            background: #1a202c;
            color: #fff;
            font-size: 11px;
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 7px;
            white-space: nowrap;
            max-width: 320px;
            white-space: normal;
            word-break: break-word;
            box-shadow: 0 4px 16px rgba(0,0,0,.25);
            pointer-events: none;
        }
        .of-product-tooltip::after {
            content: '';
            position: absolute;
            top: 100%; left: 12px;
            border: 5px solid transparent;
            border-top-color: #1a202c;
        }
        .of-product-name-wrap:hover .of-product-tooltip { display: block; }
    </style>
    @endpush

    {{-- ===== PAGE HEADER ===== --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>
                <i class="fa fa-tag text-warning"></i>
                Oferta de Pedido &nbsp;
                <span class="text-muted" style="font-size:16px;">Pedido #{{ $pedidoId }}</span>
            </h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Flujo</li>
                <li class="breadcrumb-item"><a href="/flujo/pedidos/historico">Historial Pedidos</a></li>
                <li class="breadcrumb-item active"><strong>Crear Oferta</strong></li>
            </ol>
        </div>
        <div class="col-lg-2 d-flex align-items-center justify-content-end">
            <a href="/flujo/pedidos/historico" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- Banner pedido de origen --}}
    <div style="background:linear-gradient(135deg,#6c5ce7 0%,#1a7efb 100%);
                padding:11px 28px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <span style="color:#fff; font-size:13px;">
            <i class="fa fa-link mr-1"></i>
            Esta oferta quedará vinculada al
            <strong>Pedido #{{ $pedidoId }}</strong>
            @if($pedidoCliente)
                &nbsp;&bull;&nbsp;
                <i class="fa fa-building-o mr-1"></i>
                {{ $pedidoCliente['nombre_cliente'] }}
                @if($pedidoCliente['rtn'])
                    &nbsp;(RTN: {{ $pedidoCliente['rtn'] }})
                @endif
            @endif
        </span>
        <a href="/flujo/ofertas" style="margin-left:auto; color:#fff; font-size:12px; opacity:.85;">
            <i class="fa fa-list-alt mr-1"></i> Ver historial de ofertas
        </a>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        <form onkeydown="return event.key != 'Enter';" autocomplete="off"
              id="crear_oferta" name="crear_oferta" data-parsley-validate>

            <input type="hidden" id="tipo_venta_id" name="tipo_venta_id" value="{{ $tipoCotizacion }}">
            <input type="hidden" id="pedido_id"     name="pedido_id"     value="{{ $pedidoId }}">

            {{-- ==============================
                 CARD 1 â€” Datos del cliente
            ============================== --}}
            <div class="of-card">
                <div class="of-card-title">
                    <i class="fa fa-building-o text-primary"></i> Datos del cliente
                    <span id="categoria_cliente_nombre" class="badge ml-auto"
                          style="background:rgba(26,126,251,.1); color:#1a7efb; border-radius:10px; font-size:11px; padding:3px 10px;"></span>
                </div>
                <div class="row">
                    {{-- Cliente --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">Seleccionar cliente <span class="req">*</span></label>
                        <select id="seleccionarCliente" name="seleccionarCliente"
                                class="of-select" data-parsley-required
                                onchange="obtenerDatosCliente()" style="border-radius:9px;">
                            @if($pedidoCliente)
                                <option value="{{ $pedidoCliente['cliente_id'] }}" selected>
                                    {{ $pedidoCliente['nombre_cliente'] }}
                                </option>
                            @else
                                <option value="" selected disabled>--Seleccionar cliente--</option>
                            @endif
                        </select>
                    </div>

                    {{-- Nombre cliente --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">Nombre del cliente <span class="req">*</span></label>
                        <input class="of-input" type="text"
                               id="nombre_cliente_ventas" name="nombre_cliente_ventas"
                               value="{{ $pedidoCliente['nombre_cliente'] ?? '' }}"
                               data-parsley-required readonly>
                    </div>

                    {{-- RTN --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">RTN <span class="req">*</span></label>
                        <input class="of-input" type="text"
                               id="rtn_ventas" name="rtn_ventas"
                               value="{{ $pedidoCliente['rtn'] ?? '' }}" readonly>
                    </div>

                    {{-- Vendedor --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">Vendedor</label>
                        <select name="vendedor" id="vendedor"
                                class="of-select" data-parsley-required style="border-radius:9px;">
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
                               value="{{ date('Y-m-d') }}" data-parsley-required
                               onchange="sumarDiasCredito()">
                    </div>

                    {{-- Descuento --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">Descuento aplicado % <span class="req">*</span></label>
                        <input class="of-input" type="number" min="0" max="50" value="0"
                               id="porDescuento" name="porDescuento" data-parsley-required
                               oninput="validarDescuento()"
                               onchange="calcularTotalesInicioPagina()">
                        <p id="mensajeError" style="color:#e74c3c; font-size:11px; margin:3px 0 0;"></p>
                    </div>

                    {{-- Nota --}}
                    <div class="col-12 col-md-12 col-lg-6 mb-3">
                        <label class="of-label">Nota</label>
                        <textarea class="of-input" id="nota" name="nota"
                                  rows="2" maxlength="250"
                                  style="resize:vertical; height:auto; min-height:38px;"></textarea>
                    </div>
                </div>

                {{-- Hidden inputs --}}
                <div style="display:none;">
                    <select id="tipoPagoVenta" name="tipoPagoVenta" onchange="validarFechaPago()"></select>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento"
                           value="" min="{{ date('Y-m-d') }}" readonly>
                </div>
            </div>

            {{-- ==============================
                 CARD 2 â€” Agregar producto
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
                        @push('scripts')
                        <script>
                        function limpiarProductoCotizacion() {
                            document.getElementById('seleccionarProducto').innerHTML = '<option value="" selected disabled></option>';
                            document.getElementById('codigoProductoCotizacion').value = '';
                            var lbl = document.getElementById('productoSeleccionadoCotizacion');
                            lbl.classList.add('d-none'); lbl.textContent = '';
                            document.getElementById('historialPreciosPanel').classList.add('d-none');
                        }
                        function alSeleccionarProductoCotizacion(producto) {
                            var select = document.getElementById('seleccionarProducto');
                            select.innerHTML = '<option value="' + producto.id + '" selected>' + producto.nombre + '</option>';
                            document.getElementById('codigoProductoCotizacion').value = producto.nombre;
                            var label = document.getElementById('productoSeleccionadoCotizacion');
                            label.textContent = '\u2713 ' + producto.nombre + ' (ID: ' + producto.id + ')';
                            label.classList.remove('d-none');
                            cargarCategoriasProducto();
                            cargarHistorialPrecosCotizacion();
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
                        function cargarHistorialPrecosCotizacion() {
                            var productoId = $('#seleccionarProducto').val();
                            var clienteId  = $('#seleccionarCliente').val();
                            var panel  = document.getElementById('historialPreciosPanel');
                            var cuerpo = document.getElementById('historialPreciosCuerpo');
                            if (!productoId || !clienteId) { panel.classList.add('d-none'); return; }
                            cuerpo.innerHTML = '<p class="text-muted small"><i class="fa fa-spinner fa-spin"></i> Cargando...</p>';
                            panel.classList.remove('d-none');
                            axios.post('/estatal/historial/precios', { cliente_id: clienteId, producto_id: productoId })
                            .then(function(response) {
                                var rows = response.data.historial;
                                if (!rows || rows.length === 0) {
                                    cuerpo.innerHTML = '<p class="text-muted small">Sin ventas previas de este producto a este cliente.</p>'; return;
                                }
                                var fmt = new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 });
                                var html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0" style="font-size:0.82rem;"><thead><tr style="background:#f8f9fc;"><th>Fecha</th><th>Factura</th><th>Precio U.</th><th>Cant.</th><th>Total</th><th>Cat.</th></tr></thead><tbody>';
                                rows.forEach(function(r) {
                                    html += '<tr><td>' + r.fecha_emision + '</td><td>' + r.numero_factura + '</td><td class="text-right font-weight-bold text-success">' + fmt.format(r.precio_unidad) + '</td><td class="text-center">' + r.cantidad + '</td><td class="text-right">' + fmt.format(r.total) + '</td><td><span class="badge badge-secondary" style="font-size:9px;">' + r.categoria + '</span></td></tr>';
                                });
                                html += '</tbody></table></div>';
                                cuerpo.innerHTML = html;
                            }).catch(function() { cuerpo.innerHTML = '<p class="text-danger small">Error al cargar historial.</p>'; });
                        }
                        </script>
                        @endpush
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

                    {{-- Add button + product link ── always-visible col, button toggled --}}
                    <div class="col-12 col-lg-2 mb-3" style="min-height:62px;">
                        <label class="of-label">&nbsp;</label>
                        <button type="button" id="botonAdd" class="of-add-btn d-none" onclick="agregarProductoCarrito()">
                            <i class="fa fa-cart-plus" style="font-size:16px;"></i>
                            Agregar
                        </button>
                    </div>
                </div>

                {{-- Product carousel (shown when product selected) --}}
                <div class="row" id="carouselWrap" style="">
                    <div class="col-12 col-md-4 col-lg-3 mb-2">
                        <div id="carouselProducto" class="carousel slide of-img-carousel" data-ride="carousel">
                            <div id="bloqueImagenes" class="carousel-inner"></div>
                            <a class="carousel-control-prev of-carousel-ctrl" href="#carouselProducto" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </a>
                            <a class="carousel-control-next of-carousel-ctrl" href="#carouselProducto" role="button" data-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </a>
                            <a id="detalleProducto" href="" class="d-none" target="_blank">
                                <i class="fa fa-external-link"></i> Ver detalles
                            </a>
                        </div>
                    </div>
                    <div class="col-12 col-md-8 col-lg-9">
                        {{-- History panel --}}
                        <div id="historialPreciosPanel" class="of-history d-none">
                            <div class="of-history-head">
                                <i class="fa fa-history mr-1"></i> Últimas 5 ventas de este producto a este cliente
                            </div>
                            <div class="of-history-body" id="historialPreciosCuerpo">
                                <p class="text-muted small">Cargando...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==============================
                 CARD 3 â€” Carrito de productos
            ============================== --}}
            <div class="of-card" style="padding:0; overflow:hidden;">
                <div style="padding:16px 24px 12px; border-bottom:1px solid #f0f2f5;
                            display:flex; align-items:center; gap:8px;">
                    <span class="of-card-title mb-0">
                        <i class="fa fa-shopping-cart text-warning"></i> Carrito de productos
                    </span>
                    <span id="cart-count-badge"
                          style="margin-left:auto; background:#f39c12; color:#fff; border-radius:20px;
                                 font-size:11px; font-weight:700; padding:2px 10px;">
                        0 producto(s)
                    </span>
                </div>

                {{-- Column headers --}}
                <div class="cart-header-row d-none d-md-grid">
                    <div>Producto</div>
                    <div>Bodega</div>
                    <div>Opciones</div>
                    <div>Precio</div>
                    <div>Cantidad</div>
                    <div>Unidad</div>
                    <div>Sub total</div>
                    <div>ISV</div>
                    <div>Total</div>
                    <div></div>
                </div>

                {{-- Dynamic product rows container --}}
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
                                       class="val" data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input type="hidden" id="descuentoGeneral" name="descuentoGeneral">
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-list mr-1 text-muted"></i> Sub Total</span>
                                <input type="text" id="subTotalGeneralMostrar" name="subTotalGeneralMostrar"
                                       class="val" data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input type="hidden" id="subTotalGeneral" name="subTotalGeneral">
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-file-text-o mr-1 text-muted"></i> Sub Total Grabado</span>
                                <input type="text" id="subTotalGeneralGrabadoMostrar" name="subTotalGeneralGrabadoMostrar"
                                       class="val" data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input type="hidden" id="subTotalGeneralGrabado" name="subTotalGeneralGrabado">
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-minus-circle mr-1 text-muted"></i> Sub Total Exento</span>
                                <input type="text" id="subTotalGeneralExcentoMostrar" name="subTotalGeneralExcentoMostrar"
                                       class="val" data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input type="hidden" id="subTotalGeneralExcento" name="subTotalGeneralExcento">
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-percent mr-1 text-muted"></i> ISV</span>
                                <input type="text" id="isvGeneralMostrar" name="isvGeneralMostrar"
                                       class="val" data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input type="hidden" id="isvGeneral" name="isvGeneral">
                            </div>
                            <div class="of-total-row of-total-grand" style="padding-top:12px; margin-top:4px;">
                                <span class="lbl">TOTAL</span>
                                <input type="text" id="totalGeneralMostrar" name="totalGeneralMostrar"
                                       class="val" data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input type="hidden" id="totalGeneral" name="totalGeneral">
                            </div>
                        </div>
                    </div>

                    <button id="guardar_oferta_btn" type="button" class="of-save-btn" onclick="guardarVenta()">
                        <i class="fa fa-save" style="font-size:18px;"></i>
                        Guardar Oferta
                    </button>
                </div>
            </div>

        </form>
    </div>

    @push('scripts')
    <script>
        // Update cart count badge + empty state visibility
        function actualizarCartUI() {
            var count = window.arregloIdInputs ? window.arregloIdInputs.length : 0;
            var badge = document.getElementById('cart-count-badge');
            var empty = document.getElementById('cart-empty-state');
            if (badge) badge.textContent = count + ' producto(s)';
            if (empty) empty.style.display = count === 0 ? 'block' : 'none';
        }
        // Patch agregarProductoCarrito and eliminarInput to call actualizarCartUI
        document.addEventListener('DOMContentLoaded', function() {
            actualizarCartUI();
        });
        // Override eliminarInput to also update UI
        var _origEliminar = window.eliminarInput;
        window.eliminarInput = function(id) {
            if (_origEliminar) _origEliminar.call(this, id);
            setTimeout(actualizarCartUI, 50);
        };
        // Override agregarProductoCarrito to also update UI
        var _origAgregar = window.agregarProductoCarrito;
        window.agregarProductoCarrito = function() {
            if (_origAgregar) _origAgregar.apply(this, arguments);
            setTimeout(actualizarCartUI, 300);
        };
    </script>
    <script>
        // Init Select2 for client
        $('#seleccionarCliente').select2({
            ajax: {
                url: '/oferta/clientes',
                data: function(params) {
                    return { search: params.term, tipoCotizacion: {{ $tipoCotizacion }}, type: 'public', page: params.page || 1 };
                }
            }
        });
    </script>
    <script>var public_path = "{{ asset('catalogo/') }}";</script>
    <script src="{{ asset('js/js_proyecto/flujo/oferta.js') }}"></script>
    <script>
        // After cart JS loads, patch agregarProductoCarrito to update UI
        (function() {
            var origAgregar = window.agregarProductoCarrito;
            if (origAgregar) {
                window.agregarProductoCarrito = function() {
                    origAgregar.call(this);
                    setTimeout(actualizarCartUI, 300);
                };
            }
        })();
    </script>
    @endpush
</div>
