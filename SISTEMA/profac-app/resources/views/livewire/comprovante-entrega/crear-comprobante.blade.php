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
            border-color:#1a7efb; box-shadow:0 0 0 3px rgba(26,126,251,.12);
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
            background:linear-gradient(135deg,#1a7efb,#0d6efd);
            border:none; border-radius:7px; color:#fff;
            width:34px; height:30px; cursor:pointer;
            display:flex; align-items:center; justify-content:center;
            font-size:13px;
        }
        .of-search-btn:hover { background:linear-gradient(135deg,#0d6efd,#0a58ca); }

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

        /* ── Add button ── */
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
        .of-add-btn:disabled { opacity:.6; cursor:not-allowed; transform:none; }

        /* ── Cart ── */
        #cart-empty-state {
            text-align:center; padding:36px 20px; color:#aab;
        }
        #cart-empty-state i { font-size:48px; opacity:.25; display:block; margin-bottom:10px; }

        .cart-header-row {
            display:grid;
            grid-template-columns: 2fr 1fr 1.2fr 1fr 0.9fr 0.9fr 1fr 0.8fr 1fr 40px;
            gap:4px;
            background:linear-gradient(135deg,#f39c12,#e67e22);
            border-radius:10px 10px 0 0;
            padding:9px 12px;
            font-size:11px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:.4px;
        }
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
        .prod-name-display {
            font-size:12px; color:#2d3748; font-weight:600;
            word-break:break-word; white-space:normal; line-height:1.4;
            cursor:default;
        }
        .cart-del-btn {
            background:none; border:none; color:#e74c3c; cursor:pointer;
            font-size:16px; padding:4px; border-radius:6px;
            transition:background .15s;
        }
        .cart-del-btn:hover { background:#fdecea; }

        @media (min-width:768px) {
            .cir-info, .cir-fields, .cir-del { display:contents; }
            .cir-del > .cart-del-btn { display:block; margin:0 auto; }
        }
        @media (max-width:767px) {
            .cart-header-row { display:none !important; }
            .cart-item-row {
                display:flex; flex-direction:column;
                border-radius:12px; margin:0 4px 12px; padding:0;
                position:relative; background:#fff;
                box-shadow:0 2px 14px rgba(0,0,0,.1); overflow:hidden;
            }
            .cir-info {
                background:linear-gradient(135deg,#1a7efb,#0d6efd);
                padding:7px 40px 7px 12px;
            }
            .cir-info [data-label]::before { display:none !important; }
            .cir-info [data-label="Producto"] input[type="text"],
            .cir-info [data-label="Producto"] .prod-name-display {
                background:transparent !important; border:none !important; box-shadow:none !important;
                color:#fff !important; font-weight:700 !important; font-size:12px !important;
                padding:0 !important; width:100%;
            }
            .cir-info [data-label="Bodega"] input[type="text"] {
                background:transparent !important; border:none !important; box-shadow:none !important;
                color:rgba(255,255,255,.75) !important; font-size:9.5px !important;
                padding:0 !important; width:100%;
            }
            .cir-fields {
                display:flex; flex-direction:row;
                overflow-x:auto; overflow-y:hidden;
                -webkit-overflow-scrolling:touch;
                scrollbar-width:none; background:#f4f7fe;
                border-top:2px solid #1a7efb;
            }
            .cir-fields::-webkit-scrollbar { display:none; }
            .cir-fields [data-label] {
                flex:0 0 auto; min-width:62px;
                padding:4px 6px; border-right:1px solid #e0e8f7;
            }
            .cir-fields [data-label="Lista"] { min-width:44px; max-width:52px; }
            .cir-fields [data-label="Lista"] select { font-size:10px !important; padding:2px 2px !important; text-align:center; }
            .cir-fields [data-label]:last-child { border-right:none; }
            .cir-fields [data-label]::before {
                content:attr(data-label);
                display:block; font-size:7.5px; font-weight:800; color:#9ca3af;
                text-transform:uppercase; letter-spacing:.3px; margin-bottom:2px;
            }
            .cir-fields input, .cir-fields select {
                font-size:10.5px !important; padding:2px 4px !important;
                width:100% !important; min-width:0;
            }
            .cir-fields [data-label="Total"] { background:linear-gradient(135deg,#edfaf4,#d8f5e8); border-right:none; }
            .cir-fields [data-label="Total"]::before { color:#0fa37a !important; }
            .cir-fields [data-label="Total"] input {
                background:transparent !important; border:none !important; box-shadow:none !important;
                color:#0a8a63 !important; font-weight:800 !important; font-size:12px !important; padding:0 !important;
            }
            .cir-del { position:absolute; top:8px; right:8px; z-index:5; }
            .cir-del .cart-del-btn {
                background:rgba(255,255,255,.22) !important; color:#fff !important;
                border-radius:50%; width:28px; height:28px;
                display:flex; align-items:center; justify-content:center;
                font-size:12px; padding:0;
            }
        }

        /* ── Totals ── */
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

        /* ── Select2 overrides ── */
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

        /* ── History panel ── */
        .of-history { border-radius:10px; border:1px solid #e8eaef; overflow:hidden; }
        .of-history-head {
            background:linear-gradient(135deg,#6c5ce7,#a855f7);
            padding:8px 14px; color:#fff; font-size:12px; font-weight:700;
        }
        .of-history-body { padding:10px 14px; max-height:200px; overflow-y:auto; }

        /* ── Hide old template row ── */
        .hide-container { display:none !important; }
    </style>
    @endpush

    {{-- ===== PAGE HEADER ===== --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>
                <i class="fa fa-truck text-warning"></i>
                Comprobante de Entrega
            </h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Crear Comprobante</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        <form onkeydown="return event.key != 'Enter';" autocomplete="off"
              id="crear_venta" name="crear_venta" data-parsley-validate>

            {{-- ==============================
                 CARD 1 -- Datos del cliente
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
                        <label class="of-label">Cliente <span class="req">*</span></label>
                        <select id="seleccionarCliente" name="seleccionarCliente"
                                class="of-select" data-parsley-required
                                onchange="obtenerDatosCliente()">
                            <option value="" selected disabled>--Seleccionar un cliente--</option>
                        </select>
                    </div>

                    {{-- Nombre cliente --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">Nombre del cliente <span class="req">*</span></label>
                        <input class="of-input" type="text"
                               id="nombre_cliente_ventas" name="nombre_cliente_ventas"
                               data-parsley-required readonly>
                    </div>

                    {{-- RTN --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">RTN</label>
                        <input class="of-input" type="text"
                               id="rtn_ventas" name="rtn_ventas" readonly>
                    </div>

                    {{-- Tipo de pago --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">Tipo de pago <span class="req">*</span></label>
                        <select class="of-select" name="tipoPagoVenta" id="tipoPagoVenta"
                                data-parsley-required>
                        </select>
                    </div>
                </div>
                <div class="row">
                    {{-- Fecha emision --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">Fecha de emision <span class="req">*</span></label>
                        <input class="of-input" type="date"
                               id="fecha_emision" name="fecha_emision"
                               value="{{ date('Y-m-d') }}" data-parsley-required
                               onchange="sumarDiasCredito()">
                    </div>

                    {{-- Fecha vencimiento --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label" style="color:#e67e22;">Fecha de vencimiento</label>
                        <input class="of-input" type="date"
                               id="fecha_vencimiento" name="fecha_vencimiento"
                               value="" data-parsley-required min="{{ date('Y-m-d') }}">
                    </div>

                    {{-- Descuento --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">Descuento aplicado % <span class="req">*</span></label>
                        <input class="of-input" type="number" min="0" max="50" value="0"
                               id="porDescuento" name="porDescuento"
                               onchange="calcularTotalesInicioPagina()" data-parsley-required>
                    </div>

                    {{-- Comentario --}}
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <label class="of-label">Comentario <span class="req">*</span></label>
                        <textarea class="of-input" id="comentario" name="comentario"
                                  rows="2" maxlength="220" data-parsley-required
                                  style="resize:vertical; height:auto; min-height:38px;"></textarea>
                    </div>
                </div>
            </div>

            {{-- ==============================
                 CARD 2 -- Agregar producto
            ============================== --}}
            <div class="of-card">
                <div class="of-card-title">
                    <i class="fa fa-plus-circle text-success"></i> Agregar producto al comprobante
                </div>
                <div class="row align-items-end">

                    {{-- Buscar producto --}}
                    <div class="col-12 col-md-5 col-lg-4 mb-3">
                        <label class="of-label">Buscar producto <span class="req">*</span></label>
                        <div class="of-search-wrap">
                            <input type="text" id="codigoProductoCrearComprobante" class="of-input"
                                   placeholder="ID o nombre del producto..." autocomplete="off"
                                   onkeydown="if(event.key==='Enter'){buscarPorCodigoCrearComprobante(this.value);return false;}">
                            <button type="button" class="of-search-btn" title="Buscar producto"
                                    onclick="limpiarProductoCrearComprobante(); window['abrirBuscador_buscadorProductoCrearComprobante'](document.getElementById('codigoProductoCrearComprobante').value||'')">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                        <small id="productoSeleccionadoCrearComprobante"
                               class="text-success font-weight-bold mt-1 d-block d-none"
                               style="font-size:11px;"></small>

                        {{-- Hidden select -- mantiene compatibilidad con el JS existente --}}
                        <select id="seleccionarProducto" name="seleccionarProducto" hidden>
                            <option value="" selected disabled></option>
                        </select>
                        <x-buscador-producto id-modal="buscadorProductoCrearComprobante" callback="alSeleccionarProductoCrearComprobante" />

                        @push('scripts')
                        <script>
                        function limpiarProductoCrearComprobante() {
                            document.getElementById('seleccionarProducto').innerHTML = '<option value="" selected disabled></option>';
                            document.getElementById('codigoProductoCrearComprobante').value = '';
                            var lbl = document.getElementById('productoSeleccionadoCrearComprobante');
                            lbl.classList.add('d-none'); lbl.textContent = '';
                            document.getElementById('historialPreciosPanel').classList.add('d-none');
                        }
                        function alSeleccionarProductoCrearComprobante(producto) {
                            var select = document.getElementById('seleccionarProducto');
                            select.innerHTML = '<option value="' + producto.id + '" selected>' + producto.nombre + '</option>';
                            document.getElementById('codigoProductoCrearComprobante').value = producto.nombre;
                            var label = document.getElementById('productoSeleccionadoCrearComprobante');
                            label.textContent = String.fromCharCode(10003) + ' ' + producto.nombre + ' (ID: ' + producto.id + ')';
                            label.classList.remove('d-none');
                            obtenerImagenes();
                            cargarCategoriasProducto();
                            cargarHistorialPreciosCrearComprobante();
                        }
                        function buscarPorCodigoCrearComprobante(cod) {
                            cod = String(cod).trim();
                            if (!cod) { window['abrirBuscador_buscadorProductoCrearComprobante'](''); return; }
                            axios.get('/productos/buscar', { params: { q: cod, page: 1 } })
                                .then(function(r) {
                                    var items = r.data.data;
                                    var exact = items.find(function(p) { return String(p.id) === cod; });
                                    if (exact) { alSeleccionarProductoCrearComprobante(exact); }
                                    else if (items.length === 1) { alSeleccionarProductoCrearComprobante(items[0]); }
                                    else { window['abrirBuscador_buscadorProductoCrearComprobante'](cod); }
                                });
                        }
                        function cargarHistorialPreciosCrearComprobante() {
                            var productoId = $('#seleccionarProducto').val();
                            var clienteId  = $('#seleccionarCliente').val();
                            var panel  = document.getElementById('historialPreciosPanel');
                            var cuerpo = document.getElementById('historialPreciosCuerpo');
                            if (!productoId || !clienteId) { panel.classList.add('d-none'); return; }
                            cuerpo.innerHTML = '<p class="text-muted small"><i class="fa fa-spinner fa-spin"></i> Cargando historial...</p>';
                            panel.classList.remove('d-none');
                            axios.post('/estatal/historial/precios', { cliente_id: clienteId, producto_id: productoId })
                            .then(function(response) {
                                var rows = response.data.historial;
                                if (!rows || rows.length === 0) {
                                    cuerpo.innerHTML = '<p class="text-muted small">No hay ventas previas de este producto a este cliente.</p>'; return;
                                }
                                var fmt = new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 });
                                var html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0" style="font-size:0.82rem;"><thead><tr style="background:#f8f9fc;"><th>Fecha</th><th>Factura</th><th>Precio U.</th><th>Cant.</th><th>Total</th><th>Cat.</th></tr></thead><tbody>';
                                rows.forEach(function(r) {
                                    html += '<tr><td>' + r.fecha_emision + '</td><td>' + r.numero_factura + '</td><td class="text-right font-weight-bold text-success">' + fmt.format(r.precio_unidad) + '</td><td class="text-center">' + r.cantidad + '</td><td class="text-right">' + fmt.format(r.total) + '</td><td><span class="badge badge-secondary" style="font-size:9px;">' + r.categoria + '</span></td></tr>';
                                });
                                html += '</tbody></table></div>';
                                cuerpo.innerHTML = html;
                            }).catch(function() { cuerpo.innerHTML = '<p class="text-danger small">Error al cargar el historial.</p>'; });
                        }
                        </script>
                        @endpush
                    </div>

                    {{-- Categoria precio --}}
                    <div class="col-12 col-md-4 col-lg-3 mb-3">
                        <label class="of-label">Categoria precio <span class="req">*</span></label>
                        <select id="categoria_cliente_venta_id" name="categoria_cliente_venta_id"
                                class="of-select" data-parsley-required
                                onchange="listaCategoriaClientes()">
                            <option value="" selected disabled>--Seleccione un producto primero--</option>
                        </select>
                    </div>

                    {{-- Bodega --}}
                    <div class="col-12 col-md-3 col-lg-3 mb-3">
                        <label class="of-label">Bodega <span class="req">*</span></label>
                        <select id="bodega" name="bodega" class="of-select"
                                onchange="prueba()">
                            <option value="" selected disabled>--Seleccione categoria primero--</option>
                        </select>
                    </div>

                    {{-- Agregar --}}
                    <div class="col-12 col-lg-2 mb-3">
                        <label class="of-label">&nbsp;</label>
                        <button type="button" id="botonAdd" class="of-add-btn d-none"
                                onclick="agregarProductoCarrito()">
                            <i class="fa fa-cart-plus" style="font-size:16px;"></i>
                            Agregar
                        </button>
                    </div>
                </div>

                {{-- Carousel + historial --}}
                <div class="row" id="carouselWrap">
                    <div class="col-12 col-md-4 col-lg-3 mb-2">
                        <div id="carouselProducto" class="carousel slide of-img-carousel" data-ride="carousel">
                            <div id="bloqueImagenes" class="carousel-inner"></div>
                            <a class="carousel-control-prev of-carousel-ctrl" href="#carouselProducto"
                               role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </a>
                            <a class="carousel-control-next of-carousel-ctrl" href="#carouselProducto"
                               role="button" data-slide="next">
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
                                <i class="fa fa-history mr-1"></i> Ultimas 5 ventas de este producto a este cliente
                            </div>
                            <div class="of-history-body" id="historialPreciosCuerpo">
                                <p class="text-muted small">Cargando...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Template row oculto -- requerido por crear-comprobante.js --}}
            <div class="hide-container">
                <div class="row no-gutters">
                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                        <div style="width:100%">
                            <label class="sr-only">Nombre del producto</label>
                            <input type="text" placeholder="Nombre del producto" class="form-control" disabled>
                        </div>
                    </div>
                    <div class="form-group col-12 col-sm-12 col-md-1 col-xl-1">
                        <label class="sr-only">Bodega</label>
                        <input type="number" placeholder="Bodega" class="form-control" autocomplete="off" disabled>
                    </div>
                    <div class="form-group col-12 col-sm-12 col-md-1 col-xl-1">
                        <label class="sr-only">Precio</label>
                        <input type="number" placeholder="Precio Unidad" class="form-control" min="1" autocomplete="off" disabled>
                    </div>
                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                        <label class="sr-only">Cantidad</label>
                        <input type="text" placeholder="Cantidad" class="form-control" min="1" autocomplete="off" disabled>
                    </div>
                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                        <label class="sr-only">Unidad</label>
                        <input type="text" placeholder="Unidad" class="form-control" min="1" autocomplete="off" disabled>
                    </div>
                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                        <label class="sr-only">Sub Total</label>
                        <input type="number" placeholder="Sub total del producto" class="form-control" min="1" autocomplete="off" disabled>
                    </div>
                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                        <label class="sr-only">ISV</label>
                        <input type="number" placeholder="ISV" class="form-control" min="1" autocomplete="off" disabled>
                    </div>
                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                        <label class="sr-only">Total</label>
                        <input type="number" placeholder="Total del producto" class="form-control" min="1" disabled autocomplete="off">
                    </div>
                </div>
            </div>

            {{-- ==============================
                 CARD 3 -- Carrito
            ============================== --}}
            <div class="of-card" style="padding:0; overflow:hidden;">
                <div style="padding:16px 24px 12px; border-bottom:1px solid #f0f2f5;
                            display:flex; align-items:center; gap:8px;">
                    <span class="of-card-title mb-0">
                        <i class="fa fa-shopping-cart text-warning"></i> Productos del comprobante
                    </span>
                    <span id="cart-count-badge"
                          style="margin-left:auto; background:#f39c12; color:#fff; border-radius:20px;
                                 font-size:11px; font-weight:700; padding:2px 10px;">
                        0 producto(s)
                    </span>
                </div>

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

                <div id="divProductos" style="padding:0 0 4px;">
                    <div id="cart-empty-state">
                        <i class="fa fa-inbox"></i>
                        <p style="font-size:13px;">No hay productos en el comprobante.<br>
                        <small>Use el buscador de arriba para agregar productos.</small></p>
                    </div>
                </div>
            </div>

            {{-- ==============================
                 TOTALES + GUARDAR
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
                                <input type="hidden" value="0" id="porDescuentoCalculado" name="porDescuentoCalculado">
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-list mr-1 text-muted"></i> Sub Total</span>
                                <input type="text" id="subTotalGeneralMostrar" name="subTotalGeneralMostrar"
                                       class="val" data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input id="subTotalGeneral" name="subTotalGeneral" type="hidden" value="" required>
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-file-text-o mr-1 text-muted"></i> Sub Total Grabado</span>
                                <input type="text" id="subTotalGeneralGrabadoMostrar" name="subTotalGeneralGrabadoMostrar"
                                       class="val" data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input id="subTotalGeneralGrabado" name="subTotalGeneralGrabado" type="hidden" value="" required>
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-minus-circle mr-1 text-muted"></i> Sub Total Exento</span>
                                <input type="text" id="subTotalGeneralExcentoMostrar" name="subTotalGeneralExcentoMostrar"
                                       class="val" data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input id="subTotalGeneralExcento" name="subTotalGeneralExcento" type="hidden" value="" required>
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-percent mr-1 text-muted"></i> ISV</span>
                                <input type="text" id="isvGeneralMostrar" name="isvGeneralMostrar"
                                       class="val" data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input id="isvGeneral" name="isvGeneral" type="hidden" value="" required>
                            </div>
                            <div class="of-total-row of-total-grand" style="padding-top:12px; margin-top:4px;">
                                <span class="lbl">TOTAL</span>
                                <input type="text" id="totalGeneralMostrar" name="totalGeneralMostrar"
                                       class="val" data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input id="totalGeneral" name="totalGeneral" type="hidden" value="" required>
                            </div>
                        </div>
                    </div>

                    <button id="guardar_cotizacion_btn" type="button" class="of-save-btn">
                        <i class="fa fa-save" style="font-size:18px;"></i>
                        Guardar Comprobante
                    </button>
                </div>
            </div>

        </form>
    </div>

    @push('scripts')
    <script>
    function actualizarCartUI() {
        var count = window.arregloIdInputs ? window.arregloIdInputs.length : 0;
        var badge = document.getElementById('cart-count-badge');
        var empty = document.getElementById('cart-empty-state');
        if (badge) badge.textContent = count + ' producto(s)';
        if (empty) empty.style.display = count === 0 ? 'block' : 'none';
    }
    document.addEventListener('DOMContentLoaded', function() { actualizarCartUI(); });
    var _origEliminarComp = window.eliminarInput;
    window.eliminarInput = function(id) {
        if (_origEliminarComp) _origEliminarComp.call(this, id);
        setTimeout(actualizarCartUI, 50);
    };
    var _origAgregarComp = window.agregarProductoCarrito;
    window.agregarProductoCarrito = function() {
        if (_origAgregarComp) _origAgregarComp.apply(this, arguments);
        setTimeout(actualizarCartUI, 300);
    };
    </script>
    <script>var public_path = "{{ asset('catalogo/') }}";</script>
    <script src="{{ asset('js/js_proyecto/comprobante-entrega/crear-comprobante.js') }}"></script>
    @endpush
</div>