<div>
    @push('styles')
    <style>
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
        input[type=number] { -moz-appearance:textfield; }

        .of-card {
            background:#fff; border-radius:14px; border:1px solid #e8eaef;
            box-shadow:0 2px 12px rgba(0,0,0,.06); padding:22px 24px; margin-bottom:18px;
        }
        .of-card-title {
            font-size:13px; font-weight:700; color:#6c757d; text-transform:uppercase;
            letter-spacing:.6px; margin-bottom:16px; display:flex; align-items:center; gap:7px;
            cursor:pointer; user-select:none;
        }
        .of-card-title .of-chevron { margin-left:auto; font-size:12px; color:#9ca3af; transition:transform .25s ease; flex-shrink:0; }
        .of-card-title.is-collapsed .of-chevron { transform:rotate(-90deg); }
        .of-input {
            border:1px solid #cfd8dc; border-radius:.2rem; padding:.25rem .5rem;
            font-size:.875rem; line-height:1.5; color:#495057; background-color:#fff;
            transition:border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            width:100%; display:block;
        }
        .of-select {
            border:1px solid #cfd8dc; border-radius:.2rem; padding:.25rem .5rem;
            font-size:.875rem; line-height:1.5; color:#495057; background-color:#fff;
            width:100%; display:block;
            -webkit-appearance:none; -moz-appearance:none; appearance:none;
            background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5'%3e%3cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e");
            background-repeat:no-repeat; background-position:right .75rem center; background-size:8px 10px;
        }
        .of-input:focus, .of-select:focus {
            border-color:#80bdff; box-shadow:0 0 0 .2rem rgba(0,123,255,.25);
            outline:0; background-color:#fff;
        }
        .of-input[readonly] { background-color:#e9ecef; opacity:1; }
        .of-label { font-size:11px; font-weight:700; color:#546e7a; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; display:block; }
        .of-label .req { color:#e53935; margin-left:2px; }
        .ofr-label { font-size:11px; font-weight:700; color:#546e7a; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; display:block; }
        .ofr-label .req { color:#e53935; margin-left:2px; }

        .of-img-carousel {
            border-radius:12px; overflow:hidden; border:1.5px solid #e8eaef;
            background:#f8f9fc; max-height:180px; position:relative;
        }
        #detalleProducto {
            position:absolute; bottom:8px; left:50%; transform:translateX(-50%); z-index:10;
            background:rgba(26,126,251,.82); color:#fff !important; font-size:11px; font-weight:700;
            padding:5px 14px; border-radius:20px; text-decoration:none !important;
            display:flex; align-items:center; gap:5px; white-space:nowrap;
        }
        .of-img-carousel .carousel-item img { width:100%; height:180px; object-fit:contain; }

        .of-add-btn {
            background:linear-gradient(135deg,#e65100,#f9a826);
            border:none; border-radius:8px; color:#fff;
            font-size:12px; font-weight:700; padding:5px 14px; cursor:pointer;
            box-shadow:0 2px 8px rgba(230,81,0,.3); transition:transform .15s, box-shadow .15s;
        }
        .of-add-btn:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(230,81,0,.45); }
        .of-add-btn.d-none { display:none !important; }

        #cart-empty-state { text-align:center; padding:36px 20px; color:#aab; }
        #cart-empty-state i { font-size:48px; opacity:.25; display:block; margin-bottom:10px; }

        .of-totals-card { background:#fff; border:1.5px solid #e8eaef; border-radius:14px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.07); }
        .of-totals-header { background:linear-gradient(135deg,#2d3748,#4a5568); padding:12px 20px; color:#fff; font-size:13px; font-weight:700; }
        .of-totals-body { padding:16px 20px; }
        .of-total-row { display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid #f0f2f5; font-size:13px; }
        .of-total-row:last-child { border-bottom:none; }
        .of-total-row .lbl { color:#6b7280; font-weight:500; }
        .of-total-row .val { font-weight:700; color:#1a202c; background:#f7f8fa; border:1px solid #e8eaef; border-radius:7px; padding:4px 12px; min-width:130px; text-align:right; font-family:monospace; }
        .of-total-grand .lbl { font-size:15px; font-weight:800; color:#1a202c; }
        .of-total-grand .val { background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff; border:none; font-size:15px; }

        .of-save-btn {
            background:linear-gradient(135deg,#f39c12,#e67e22); border:none; border-radius:12px;
            color:#fff; font-size:15px; font-weight:800; padding:14px 36px; cursor:pointer;
            box-shadow:0 5px 20px rgba(243,156,18,.4); display:flex; align-items:center;
            gap:10px; width:100%; justify-content:center; transition:transform .15s;
        }
        .of-save-btn:hover { transform:translateY(-2px); }

        .select2-container .select2-selection--single { border:1px solid #cfd8dc !important; border-radius:.2rem !important; height:31px !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height:29px !important; font-size:.875rem !important; color:#495057; padding-left:8px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height:29px !important; }

        .of-historial-header { background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border-radius:8px 8px 0 0; padding:8px 14px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; display:flex; align-items:center; gap:6px; }
        .of-historial-body { border:1.5px solid #ffe0b2; border-top:none; border-radius:0 0 8px 8px; padding:8px 12px; background:#fffbf7; font-size:12px; min-height:38px; }

        .hide-container { display:none !important; }
    </style>
    @endpush

    {{-- PAGE HEADER --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="fa fa-truck text-warning"></i> Comprobante de Entrega</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Crear Comprobante</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        <form onkeydown="return event.key != 'Enter';" autocomplete="off"
              id="crear_venta" name="crear_venta" data-parsley-validate>

            {{-- CARD 1: Datos del cliente --}}
            <div class="of-card">
                <div class="of-card-title" onclick="toggleOfCard('body_cliente', this)">
                    <i class="fa fa-user text-primary"></i> Datos del cliente
                    <span id="categoria_cliente_nombre"
                          style="background:rgba(230,81,0,.1); color:#e65100; border:1px solid rgba(230,81,0,.2); border-radius:20px; font-size:11px; font-weight:700; padding:2px 12px;"></span>
                    <i class="fa fa-chevron-down of-chevron"></i>
                </div>
                <div id="body_cliente">
                <div class="row" style="row-gap:10px;">
                    <div class="col-12 col-md-4">
                        <label class="ofr-label">Cliente <span class="req">*</span></label>
                        <select id="seleccionarCliente" name="seleccionarCliente"
                                class="form-control form-control-sm" data-parsley-required onchange="obtenerDatosCliente()">
                            <option value="" selected disabled>--Seleccionar--</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="ofr-label">Nombre del Cliente <span class="req">*</span></label>
                        <input class="form-control form-control-sm" type="text" id="nombre_cliente_ventas"
                               name="nombre_cliente_ventas" data-parsley-required readonly placeholder="(autocompletado)">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="ofr-label">RTN</label>
                        <input class="form-control form-control-sm" type="text" id="rtn_ventas" name="rtn_ventas" readonly placeholder="(autocompletado)">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="ofr-label">Tipo de Pago <span class="req">*</span></label>
                        <select class="form-control form-control-sm" name="tipoPagoVenta" id="tipoPagoVenta"
                                data-parsley-required onchange="validarFechaPago()"></select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="ofr-label">Fecha Emisión <span class="req">*</span></label>
                        <input class="form-control form-control-sm" type="date" id="fecha_emision" name="fecha_emision"
                               value="{{ date('Y-m-d') }}" data-parsley-required onchange="sumarDiasCredito()">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="ofr-label" style="color:#f57f17;">Vencimiento</label>
                        <input class="form-control form-control-sm" type="date" id="fecha_vencimiento" name="fecha_vencimiento"
                               value="" data-parsley-required min="{{ date('Y-m-d') }}" readonly>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="ofr-label">Descuento %</label>
                        <input class="form-control form-control-sm" type="number" min="0" max="50" value="0"
                               id="porDescuento" name="porDescuento" onchange="calcularTotalesInicioPagina()">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="ofr-label">Nota <span class="req">*</span></label>
                        <textarea class="form-control form-control-sm" id="comentario" name="comentario"
                                  rows="1" maxlength="250" data-parsley-required></textarea>
                    </div>
                </div>
                </div>{{-- /body_cliente --}}
            </div>

            {{-- CARD 2: Agregar producto --}}
            <div class="of-card">
                <div class="of-card-title" onclick="toggleOfCard('body_producto', this)">
                    <i class="fa fa-plus-circle text-success"></i> Agregar producto al carrito
                    <i class="fa fa-chevron-down of-chevron"></i>
                </div>
                <div id="body_producto">
                <div class="row" style="row-gap:10px; margin-bottom:10px;">
                    {{-- Buscador --}}
                    <div class="col-12 col-md-4 mb-3">
                        <label class="ofr-label">Seleccionar Producto <span class="req">*</span></label>
                        <div class="input-group">
                            <input type="text" id="codigoProductoCrearComprobante" class="form-control form-control-sm"
                                   placeholder="ID o nombre del producto..." autocomplete="off"
                                   onkeydown="if(event.key==='Enter'){buscarPorCodigoCrearComprobante(this.value);return false;}">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary btn-sm" title="Buscar"
                                        onclick="limpiarProductoCrearComprobante(); window['abrirBuscador_buscadorProductoCrearComprobante'](document.getElementById('codigoProductoCrearComprobante').value||'')">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <small id="productoSeleccionadoCrearComprobante"
                               class="text-success font-weight-bold mt-1 d-block d-none" style="font-size:11px;"></small>
                        <span style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;pointer-events:none;">
                            <select id="seleccionarProducto" name="seleccionarProducto">
                                <option value="" selected disabled></option>
                            </select>
                        </span>
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
                            var productoId = document.getElementById('seleccionarProducto').value;
                            var clienteId  = document.getElementById('seleccionarCliente').value;
                            var panel  = document.getElementById('historialPreciosPanel');
                            var cuerpo = document.getElementById('historialPreciosCuerpo');
                            if (!productoId || !clienteId) { panel.classList.add('d-none'); return; }
                            cuerpo.innerHTML = '<p class="text-muted small"><i class="fa fa-spinner fa-spin"></i> Cargando historial...</p>';
                            panel.classList.remove('d-none');
                            axios.post('/estatal/historial/precios', { cliente_id: clienteId, producto_id: productoId })
                            .then(function(response) {
                                var rows = response.data.historial;
                                if (!rows || rows.length === 0) { cuerpo.innerHTML = '<p class="text-muted small">Sin ventas previas.</p>'; return; }
                                var fmt = new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 });
                                var html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0" style="font-size:0.82rem;"><thead><tr style="background:#f8f9fc;"><th>Fecha</th><th>Factura</th><th>Precio U.</th><th>Cant.</th><th>Total</th><th>Cat.</th></tr></thead><tbody>';
                                rows.forEach(function(r) { html += '<tr><td>' + r.fecha_emision + '</td><td>' + r.numero_factura + '</td><td class="text-right font-weight-bold text-success">' + fmt.format(r.precio_unidad) + '</td><td class="text-center">' + r.cantidad + '</td><td class="text-right">' + fmt.format(r.total) + '</td><td><span class="badge badge-secondary" style="font-size:9px;">' + r.categoria + '</span></td></tr>'; });
                                html += '</tbody></table></div>';
                                cuerpo.innerHTML = html;
                            }).catch(function() { cuerpo.innerHTML = '<p class="text-danger small">Error al cargar.</p>'; });
                        }
                        </script>
                        @endpush
                    </div>
                    {{-- Categoria precio --}}
                    <div class="col-12 col-md-4 mb-3">
                        <label class="ofr-label">Categoría Precio <span class="req">*</span></label>
                        <select id="categoria_cliente_venta_id" name="categoria_cliente_venta_id"
                                class="form-control form-control-sm" data-parsley-required onchange="listaCategoriaClientes()">
                            <option value="" selected disabled>--Seleccione un producto primero--</option>
                        </select>
                    </div>
                    {{-- Bodega --}}
                    <div class="col-12 col-md-4 mb-3">
                        <label class="ofr-label">Bodega <span class="req">*</span></label>
                        <select id="bodega" name="bodega" class="form-control form-control-sm" onchange="prueba()">
                            <option value="" selected disabled>--Seleccione una categoría primero--</option>
                        </select>
                    </div>
                </div>
                {{-- Botón añadir --}}
                <div id="botonAdd" class="mb-3 d-none">
                    <button type="button" onclick="agregarProductoCarrito()"
                            style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                                   border-radius:8px; padding:5px 14px; font-size:12px; font-weight:700;
                                   box-shadow:0 2px 8px rgba(230,81,0,.3); cursor:pointer;">
                        <i class="mr-1 fa fa-shopping-cart"></i> Añadir al Carrito
                    </button>
                </div>
                {{-- Carousel e historial --}}
                <div class="row">
                    <div class="col-12 col-md-5 mb-2">
                        <div id="carouselProducto" class="carousel slide" data-ride="carousel">
                            <div id="bloqueImagenes" class="carousel-inner"
                                 style="border-radius:10px; overflow:hidden; height:220px; background:#f8f9fa;"></div>
                            <a class="carousel-control-prev" href="#carouselProducto" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#carouselProducto" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                        <a id="detalleProducto" href="" class="d-none mt-1 d-block" target="_blank" style="font-size:12px; color:#1a7efb;">
                            <i class="fa fa-external-link mr-1"></i> Ver detalles
                        </a>
                    </div>
                    <div class="col-12 col-md-7">
                        <div id="historialPreciosPanel" class="d-none">
                            <div class="of-historial-header">
                                <i class="fa fa-history"></i> Últimas 5 ventas de este producto a este cliente
                            </div>
                            <div class="of-historial-body" id="historialPreciosCuerpo">
                                <p class="mb-0 text-muted small">Sin ventas previas de este producto a este cliente.</p>
                            </div>
                        </div>
                    </div>
                </div>
                </div>{{-- /body_producto --}}
            </div>

            {{-- Template row oculto: requerido internamente por crear-comprobante.js --}}
            <div class="hide-container">
                <div class="row no-gutters">
                    <div class="form-group col-md-2"><input type="text" placeholder="Nombre" class="form-control" disabled></div>
                    <div class="form-group col-md-1"><input type="number" placeholder="Bodega" class="form-control" autocomplete="off" disabled></div>
                    <div class="form-group col-md-1"><input type="number" placeholder="Precio" class="form-control" min="1" autocomplete="off" disabled></div>
                    <div class="form-group col-md-1"><input type="text" placeholder="Cantidad" class="form-control" min="1" autocomplete="off" disabled></div>
                    <div class="form-group col-md-1"><input type="text" placeholder="Unidad" class="form-control" min="1" autocomplete="off" disabled></div>
                    <div class="form-group col-md-2"><input type="number" placeholder="Sub total" class="form-control" min="1" autocomplete="off" disabled></div>
                    <div class="form-group col-md-2"><input type="number" placeholder="ISV" class="form-control" min="1" autocomplete="off" disabled></div>
                    <div class="form-group col-md-2"><input type="number" placeholder="Total" class="form-control" min="1" disabled autocomplete="off"></div>
                </div>
            </div>

            {{-- CARD 3: Carrito --}}
            <div class="of-card" style="padding:0; overflow:hidden;">
                <div style="padding:16px 24px 12px; border-bottom:1px solid #f0f2f5; display:flex; align-items:center; gap:8px;">
                    <span class="of-card-title mb-0">
                        <i class="fa fa-shopping-cart text-warning"></i> Carrito de productos
                    </span>
                    <span id="cart-count-badge"
                          style="margin-left:auto; background:#e65100; color:#fff; border-radius:20px; font-size:11px; font-weight:700; padding:2px 10px;">
                        0 producto(s)
                    </span>
                </div>
                <div id="cart-empty-state" style="padding:36px 20px; text-align:center; color:#aab;">
                    <i class="fa fa-inbox fa-3x d-block mb-2" style="opacity:.25;"></i>
                    <p class="mb-0" style="font-size:13px;">No hay productos en el carrito.<br>
                    <small>Use el buscador de arriba para agregar productos.</small></p>
                </div>
                <div id="divProductos-wrapper" class="table-responsive d-none" style="max-height:420px; overflow-y:auto;">
                    <table class="table mb-0 table-sm table-bordered" style="font-size:12px; min-width:800px;">
                        <thead style="background:linear-gradient(135deg,#e8f5e9,#e0f7fa); position:sticky; top:0; z-index:1;">
                            <tr style="color:#00695c; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:.3px;">
                                <th style="width:36px;"></th>
                                <th style="min-width:150px;">Producto</th>
                                <th style="min-width:100px;">Bodega</th>
                                <th style="min-width:90px;">Precio Unit.</th>
                                <th style="min-width:80px;">Cantidad</th>
                                <th style="min-width:90px;">Unidad</th>
                                <th style="min-width:90px;">Sub Total</th>
                                <th style="min-width:80px;">ISV</th>
                                <th style="min-width:90px; background:linear-gradient(135deg,#e65100,#f9a826); color:#fff;">Total</th>
                            </tr>
                        </thead>
                        <tbody id="divProductos"></tbody>
                    </table>
                </div>
            </div>

            {{-- TOTALES + GUARDAR --}}
            <div class="row">
                <div class="col-12 col-lg-6 offset-lg-6">
                    <div class="of-totals-card mb-4">
                        <div class="of-totals-header">
                            <i class="fa fa-calculator mr-2"></i> Resumen de totales
                        </div>
                        <div class="of-totals-body">
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-tag mr-1 text-muted"></i> Descuento</span>
                                <input type="text" id="descuentoMostrar" name="descuentoMostrar" class="val"
                                       data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input type="hidden" value="0" id="porDescuentoCalculado" name="porDescuentoCalculado">
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-list mr-1 text-muted"></i> Sub Total</span>
                                <input type="text" id="subTotalGeneralMostrar" name="subTotalGeneralMostrar" class="val"
                                       data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input id="subTotalGeneral" name="subTotalGeneral" type="hidden" value="" required>
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-file-text-o mr-1 text-muted"></i> Sub Total Grabado</span>
                                <input type="text" id="subTotalGeneralGrabadoMostrar" name="subTotalGeneralGrabadoMostrar" class="val"
                                       data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input id="subTotalGeneralGrabado" name="subTotalGeneralGrabado" type="hidden" value="" required>
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-minus-circle mr-1 text-muted"></i> Sub Total Exento</span>
                                <input type="text" id="subTotalGeneralExcentoMostrar" name="subTotalGeneralExcentoMostrar" class="val"
                                       data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input id="subTotalGeneralExcento" name="subTotalGeneralExcento" type="hidden" value="" required>
                            </div>
                            <div class="of-total-row">
                                <span class="lbl"><i class="fa fa-percent mr-1 text-muted"></i> ISV</span>
                                <input type="text" id="isvGeneralMostrar" name="isvGeneralMostrar" class="val"
                                       data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input id="isvGeneral" name="isvGeneral" type="hidden" value="" required>
                            </div>
                            <div class="of-total-row of-total-grand" style="padding-top:12px; margin-top:4px;">
                                <span class="lbl">TOTAL</span>
                                <input type="text" id="totalGeneralMostrar" name="totalGeneralMostrar" class="val"
                                       data-parsley-required autocomplete="off" readonly placeholder="L. 0.00">
                                <input id="totalGeneral" name="totalGeneral" type="hidden" value="" required>
                            </div>
                        </div>
                    </div>
                    <button id="guardar_cotizacion_btn" type="submit" class="of-save-btn">
                        <i class="fa fa-save" style="font-size:18px;"></i> Guardar Comprobante
                    </button>
                </div>
            </div>

        </form>
    </div>

    @push('scripts')
    <script>
    function toggleOfCard(bodyId, titleEl) {
        var body = document.getElementById(bodyId);
        if (!body) return;
        if (body.style.display === 'none') {
            body.style.display = '';
            titleEl.classList.remove('is-collapsed');
        } else {
            body.style.display = 'none';
            titleEl.classList.add('is-collapsed');
        }
    }
    function listaCategoriaClientes() {
        var categoriaId = $('#categoria_cliente_venta_id').val();
        var productoId  = $('#seleccionarProducto').val();
        if (categoriaId && productoId) {
            $('#bodega').prop('disabled', false);
            obtenerBodegas(productoId);
        }
        // Actualizar precio visible en las filas del carrito que ya existen
        // (el precio de la categoría seleccionada lo obtiene agregarProductoCarrito)
    }
    function actualizarCartUI() {
        var count = window.arregloIdInputs ? window.arregloIdInputs.length : 0;
        var badge = document.getElementById('cart-count-badge');
        var empty = document.getElementById('cart-empty-state');
        var wrapper = document.getElementById('divProductos-wrapper');
        if (badge) badge.textContent = count + ' producto(s)';
        if (empty) empty.style.display = count === 0 ? '' : 'none';
        if (wrapper) wrapper.classList.toggle('d-none', count === 0);
    }
    document.addEventListener('DOMContentLoaded', function() { actualizarCartUI(); });
    window.eliminarInput = (function(orig) {
        return function(id) { if (orig) orig.call(this, id); setTimeout(actualizarCartUI, 50); };
    })(window.eliminarInput);
    window.agregarProductoCarrito = (function(orig) {
        return function() { if (orig) orig.apply(this, arguments); setTimeout(actualizarCartUI, 300); };
    })(window.agregarProductoCarrito);
    </script>
    <script>var public_path = "{{ asset('catalogo/') }}";</script>
    <script src="{{ asset('js/js_proyecto/comprobante-entrega/crear-comprobante.js') }}"></script>
    @endpush
</div>
