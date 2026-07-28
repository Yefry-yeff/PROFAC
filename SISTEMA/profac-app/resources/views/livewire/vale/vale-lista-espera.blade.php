<div>
    @push('styles')
    <style>
        /* ── Spinner inputs ───────────────────────────────────── */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }

        /* ── Product image ────────────────────────────────────── */
        .img-size { width: 100%; height: 18rem; margin: 0 auto; object-fit: contain; }
        @media (min-width: 768px) and (max-width: 960px) { .img-size { height: 12rem; } }

        /* ── Vale header card ─────────────────────────────────── */
        .vale-header-card {
            background: linear-gradient(135deg, #1565c0 0%, #42a5f5 100%);
            border-radius: 14px; padding: 20px 28px; margin-bottom: 20px;
            color: #fff; box-shadow: 0 4px 20px rgba(21,101,192,.25);
        }
        .vale-header-card h2 { color: #fff; font-size: 20px; font-weight: 800; margin: 0 0 10px; }
        .vale-info-chips { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        .vale-info-chip {
            background: rgba(255,255,255,.18); border-radius: 20px;
            padding: 5px 14px; font-size: 12px; font-weight: 600;
            display: flex; align-items: center; gap: 6px;
        }

        /* ── of-card system ──────────────────────────────────── */
        .of-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e8eaef;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            padding: 22px 24px;
            margin-bottom: 18px;
        }
        .of-card-title {
            font-size: 13px; font-weight: 700; color: #6c757d;
            text-transform: uppercase; letter-spacing: .6px;
            margin-bottom: 16px;
            display: flex; align-items: center; gap: 7px;
        }
        .of-card-title i { font-size: 14px; }

        /* ── of-totals-card ──────────────────────────────────── */
        .of-totals-card {
            background: #fff; border: 1.5px solid #e8eaef;
            border-radius: 14px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,.07);
            margin-bottom: 18px;
        }
        .of-totals-header {
            background: linear-gradient(135deg,#2d3748,#4a5568);
            padding: 12px 20px; color: #fff; font-size: 13px; font-weight: 700;
            display: flex; align-items: center; gap: 8px;
        }
        .of-totals-body { padding: 16px 20px; }
        .of-total-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 7px 0; border-bottom: 1px solid #f0f2f5; font-size: 13px;
        }
        .of-total-row:last-child { border-bottom: none; }
        .of-total-row .lbl { color: #6b7280; font-weight: 500; white-space: nowrap; }
        .of-total-row .val {
            font-weight: 700; color: #1a202c;
            background: #f7f8fa; border: 1px solid #e8eaef;
            border-radius: 7px; padding: 4px 12px; font-size: 13px;
            min-width: 140px; text-align: right; font-family: monospace;
            outline: none; flex-shrink: 0;
        }
        .of-total-grand .lbl { font-size: 15px; font-weight: 800; color: #1a202c; }
        .of-total-grand .val {
            background: linear-gradient(135deg,#1ab394,#0fa37a);
            color: #fff; font-size: 15px; border: none;
            box-shadow: 0 3px 10px rgba(26,179,148,.3);
        }

        /* ── Field labels ─────────────────────────────────────── */
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
            border-color: #1565c0 !important; box-shadow: 0 0 0 3px rgba(21,101,192,.12) !important;
        }

        /* ── Cart empty state ──────────────────────────────────── */
        #carritoVacioVale {
            text-align: center; padding: 32px 20px; color: #aab;
        }
        #carritoVacioVale i { font-size: 42px; opacity: .25; display: block; margin-bottom: 8px; }

        /* ── Cart tabla ────────────────────────────────────────── */
        .cart-item-card { transition: background .15s; }
        .cart-item-card:hover > td { background: #f1f8e9 !important; }

        /* ── Collapsible of-cards ──────────────────────────────── */
        .of-card-title { cursor: pointer; user-select: none; }
        .of-card-title .of-chevron {
            margin-left: auto; font-size: 12px; color: #9ca3af;
            transition: transform .25s ease; flex-shrink: 0;
        }
        .of-card-title.is-collapsed .of-chevron { transform: rotate(-90deg); }

        /* ── Historial panel ──────────────────────────────────── */
        .of-historial-header {
            background: linear-gradient(135deg,#1565c0,#42a5f5);
            color: #fff; border-radius: 8px 8px 0 0;
            padding: 8px 14px; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .4px;
            display: flex; align-items: center; gap: 6px;
        }
        .of-historial-body {
            border: 1.5px solid #bbdefb; border-top: none;
            border-radius: 0 0 8px 8px; padding: 8px 12px;
            background: #f8fbff; font-size: 12px; min-height: 38px;
        }
    </style>
    @endpush

    {{-- ===== PAGE HEADING ===== --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10 col-md-9 col-sm-8">
            <h2><i class="fa fa-ticket" style="color:#1565c0;"></i> Crear Vale &ndash; Lista de Espera</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Ventas</li>
                <li class="breadcrumb-item active"><strong>Crear Vale</strong></li>
            </ol>
        </div>
        <div class="col-lg-2 col-md-3 col-sm-4 d-flex align-items-center justify-content-end">
            <a href="javascript:history.back()" class="btn btn-default btn-sm">
                <i class="mr-1 fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <form onkeydown="return event.key != 'Enter';" autocomplete="off" id="crear_venta" name="crear_venta" data-parsley-validate>
        <input id="idFactura" name="idFactura" type="hidden" value="{{ $idFactura }}">

        <div class="wrapper wrapper-content animated fadeInRight">

            {{-- ===== HEADER INFO CARD ===== --}}
            <div class="vale-header-card">
                <h2><i class="fa fa-ticket mr-2"></i> Vale &ndash; Lista de Espera</h2>
                <div class="vale-info-chips">
                    <div class="vale-info-chip">
                        <i class="fa fa-file-text-o"></i>
                        <span>Factura: <strong>{{ $datosFactura->numero_factura }}</strong></span>
                    </div>
                    <div class="vale-info-chip">
                        <i class="fa fa-user"></i>
                        <span>Cliente: <strong>{{ $datosCliente->nombre }}</strong></span>
                    </div>
                    <div class="vale-info-chip">
                        <i class="fa fa-tag"></i>
                        <span>Categor&iacute;a: <strong>{{ $datosCliente->nombre_categoria }}</strong></span>
                    </div>
                    <div class="vale-info-chip">
                        <i class="fa fa-percent"></i>
                        <span>Descuento: <strong>{{ $datosFactura->porc_descuento }}%</strong></span>
                    </div>
                </div>
                {{-- Hidden inputs used by JS --}}
                <input type="hidden" id="categoria_cliente_id" name="categoria_cliente_id" value="{{ $datosCliente->idcategoriacliente }}">
            </div>

            <div class="row">

                {{-- ===== LEFT COLUMN ===== --}}
                <div class="col-12 col-lg-8">

                    {{-- CARD: Informacion del Vale --}}
                    <div class="of-card">
                        <div class="of-card-title" onclick="toggleOfCard('body_info_vale', this)">
                            <i class="fa fa-pencil" style="color:#1565c0;"></i>
                            Informaci&oacute;n del Vale
                            <i class="fa fa-chevron-down of-chevron"></i>
                        </div>
                        <div id="body_info_vale">
                        <div class="row">
                            <div class="col-12 col-md-9">
                                <label class="ofr-label">Comentario <span class="req">*</span></label>
                                <textarea class="form-control ofr-input" id="comentario" name="comentario" rows="4" data-parsley-required></textarea>
                            </div>
                            <div class="col-12 col-md-3 mt-3 mt-md-0">
                                <label class="ofr-label">Descuento %</label>
                                <input class="form-control ofr-input text-center font-weight-bold" type="number"
                                       value="{{ $datosFactura->porc_descuento }}" min="0" max="50"
                                       id="porDescuento" name="porDescuento"
                                       style="font-size:22px !important; color:#e65100; background:#fff8f0; border-color:#ffe0b2 !important;">
                            </div>
                        </div>
                        </div>{{-- /body_info_vale --}}
                    </div>

                    {{-- CARD: Agregar Producto --}}
                    <div class="of-card">
                        <div class="of-card-title" onclick="toggleOfCard('body_agregar_vale', this)">
                            <i class="fa fa-plus-circle" style="color:#2e7d32;"></i>
                            Agregar producto al carrito
                            <i class="fa fa-chevron-down of-chevron"></i>
                        </div>
                        <div id="body_agregar_vale">
                        <div class="row">
                            <div class="col-12 col-md-7">
                                <label class="ofr-label">Producto <span class="req">*</span></label>
                                <div class="input-group mb-2">
                                    <input type="text" id="codigoProductoValeListaEspera" class="form-control ofr-input"
                                           placeholder="ID o nombre del producto..." autocomplete="off"
                                           onkeydown="if(event.key==='Enter'){buscarPorCodigoValeListaEspera(this.value);return false;}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" title="Buscar producto"
                                                onclick="limpiarProductoValeListaEspera(); window['abrirBuscador_buscadorProductoValeListaEspera'](document.getElementById('codigoProductoValeListaEspera').value||'')">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <small id="productoSeleccionadoValeListaEspera" class="text-success font-weight-bold mt-1 d-block d-none"></small>

                                {{-- Hidden select - keeps JS compatibility --}}
                                <select id="seleccionarProductoVale" name="seleccionarProductoVale" class="d-none">
                                    <option value="" selected disabled></option>
                                </select>
                                <x-buscador-producto id-modal="buscadorProductoValeListaEspera" callback="alSeleccionarProductoValeListaEspera" />

                                <div class="mt-3">
                                    <label class="ofr-label">Categor&iacute;a de Precio <span class="req">*</span></label>
                                    <select id="categoria_cliente_venta_id" name="categoria_cliente_venta_id"
                                            class="form-control ofr-input"
                                            data-categoria-cliente-id="{{ $datosCliente->idcategoriacliente }}"
                                            onchange="habilitarBodega()">
                                        <option value="" selected disabled>--Seleccione primero un producto--</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-5 mt-3 mt-md-0">
                                <div class="text-center mb-2">
                                    <a id="detalleProductoVale" href="" class="font-bold d-none text-primary" target="_blank">
                                        <i class="fa-solid fa-circle-info"></i> Ver Detalles del Producto
                                    </a>
                                </div>
                                <div id="carouselProductoVale" class="carousel slide" data-ride="carousel"
                                     style="border-radius:10px; overflow:hidden; background:#f8f9fa; min-height:100px;">
                                    <div id="bloqueImagenesVale" class="carousel-inner"></div>
                                    <a class="carousel-control-prev" href="#carouselProductoVale" role="button" data-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Previous</span>
                                    </a>
                                    <a class="carousel-control-next" href="#carouselProductoVale" role="button" data-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Next</span>
                                    </a>
                                </div>
                                <div id="botonAddVale" class="mt-2 d-none">
                                    <button type="button" onclick="agregarProductoVale()"
                                        style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                                               border-radius:8px; padding:6px 16px; font-size:12px; font-weight:700;
                                               box-shadow:0 2px 8px rgba(230,81,0,.3); cursor:pointer;">
                                        <i class="fa fa-shopping-cart mr-1"></i> A&ntilde;adir al Vale
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Historial de precios --}}
                        <div id="historialPreciosPanel" class="d-none mt-3">
                            <div class="of-historial-header">
                                <i class="fa fa-history"></i> &Uacute;ltimas 5 ventas a este cliente
                            </div>
                            <div class="of-historial-body" id="historialPreciosCuerpo">
                                <p class="text-muted small mb-0">Cargando...</p>
                            </div>
                        </div>
                        </div>{{-- /body_agregar_vale --}}
                    </div>

                    {{-- CARD: Lista de Productos --}}
                    <div class="of-card" style="padding:0; overflow:hidden;">
                        <div style="padding:16px 24px 12px; border-bottom:1px solid #f0f2f5; display:flex; align-items:center; gap:8px; cursor:pointer;"
                             onclick="toggleOfCard('body_carrito_vale', this)">
                            <i class="fa fa-shopping-cart text-warning"></i>
                            <span class="of-card-title" style="cursor:pointer; margin-bottom:0 !important; font-size:13px;">
                                Productos del Vale
                            </span>
                            <span id="cart-count-badge"
                                  style="background:#e65100; color:#fff; border-radius:20px; font-size:11px; font-weight:700; padding:2px 10px;">0 producto(s)</span>
                            <i class="fa fa-chevron-down of-chevron" style="margin-left:auto;"></i>
                        </div>
                        <div id="body_carrito_vale">
                            <div id="carritoVacioVale" class="py-3 text-center" style="color:#aab;">
                                <i class="fa fa-inbox fa-3x mb-2 d-block" style="opacity:.25;"></i>
                                <p style="font-size:13px; margin:0;">No hay productos en el vale.<br><small>Use el buscador de arriba para agregar.</small></p>
                            </div>
                            <div id="carritoTablaWrapper" class="d-none table-responsive" style="max-height:420px; overflow-y:auto;">
                                <table class="table table-sm table-bordered mb-0" style="font-size:12px; min-width:820px;">
                                    <thead style="background:linear-gradient(135deg,#e8f5e9,#e0f7fa); position:sticky; top:0; z-index:1;">
                                        <tr style="color:#00695c; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:.3px;">
                                            <th style="width:36px;"></th>
                                            <th style="min-width:160px;">Producto</th>
                                            <th style="min-width:120px;">Precio Categ.</th>
                                            <th style="min-width:90px;">P. Unitario</th>
                                            <th style="min-width:70px;">Cantidad</th>
                                            <th style="min-width:100px;">Unidad</th>
                                            <th style="min-width:90px;">Sub Total</th>
                                            <th style="min-width:80px;">ISV</th>
                                            <th style="min-width:90px;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="divProductosVale"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>{{-- /col-lg-8 --}}

                {{-- ===== RIGHT COLUMN: Totals + Save ===== --}}
                <div class="col-12 col-lg-4">
                    <div class="of-totals-card" style="position:sticky; top:20px;">
                        <div class="of-totals-header">
                            <i class="fa fa-calculator"></i> Resumen del Vale
                        </div>
                        <div class="of-totals-body">
                            <div class="of-total-row">
                                <span class="lbl">Descuento L.</span>
                                <input type="text" id="descuentoMostrar" name="descuentoMostrar" class="val" readonly placeholder="L 0.00" data-parsley-required>
                                <input type="hidden" id="descuentoGeneral" name="descuentoGeneral" required>
                            </div>
                            <div class="of-total-row">
                                <span class="lbl">Sub Total L.</span>
                                <input type="text" id="subTotalGeneralMostrar" name="subTotalGeneralMostrar" class="val" readonly placeholder="L 0.00" data-parsley-required>
                                <input type="hidden" id="subTotalGeneral" name="subTotalGeneral" required>
                            </div>
                            <div class="of-total-row">
                                <span class="lbl">Sub Total Grabado L.</span>
                                <input type="text" id="subTotalGeneralGrabadoMostrar" name="subTotalGeneralGrabadoMostrar" class="val" readonly placeholder="L 0.00" data-parsley-required>
                                <input type="hidden" id="subTotalGeneralGrabado" name="subTotalGeneralGrabado" required>
                            </div>
                            <div class="of-total-row">
                                <span class="lbl">Sub Total Excento L.</span>
                                <input type="text" id="subTotalGeneralExcentoMostrar" name="subTotalGeneralExcentoMostrar" class="val" readonly placeholder="L 0.00" data-parsley-required>
                                <input type="hidden" id="subTotalGeneralExcento" name="subTotalGeneralExcento" required>
                            </div>
                            <div class="of-total-row">
                                <span class="lbl">ISV L.</span>
                                <input type="text" id="isvGeneralMostrar" name="isvGeneralMostrar" class="val" readonly placeholder="L 0.00" data-parsley-required>
                                <input type="hidden" id="isvGeneral" name="isvGeneral" required>
                            </div>
                            <div class="of-total-row of-total-grand" style="padding-top:12px;">
                                <span class="lbl">Total L.</span>
                                <input type="text" id="totalGeneralMostrar" name="totalGeneralMostrar" class="val" readonly placeholder="L 0.00" data-parsley-required>
                                <input type="hidden" id="totalGeneral" name="totalGeneral" required>
                            </div>
                        </div>
                        <div class="px-3 pb-3">
                            <button id="btn_venta_vale_coorporativo" type="submit" class="btn btn-primary btn-block"
                                    style="border-radius:10px; font-weight:700; padding:12px; font-size:15px;">
                                <i class="fa fa-save mr-2"></i> Guardar Vale
                            </button>
                        </div>
                    </div>
                </div>{{-- /col-lg-4 --}}

            </div>{{-- /row --}}
        </div>{{-- /wrapper-content --}}
    </form>

    @push('scripts')
    <script>
    var numeroInputsVP    = 0;
    var arregloIdInputsVP = [];
    var retencionEstado   = false;
    var public_path       = "{{ asset('catalogo/') }}";

    // Client info from server
    var _clienteId            = {{ $datosCliente->id }};
    var _categoriaClienteId   = {{ $datosCliente->idcategoriacliente }};
    var _categoriaNombre      = @json($datosCliente->nombre_categoria);
    var _categoriaPrecionId   = {{ $datosCliente->categoria_precios_id ?? 'null' }};

    /* Cart count badge + tabla wrapper */
    function actualizarCartCount() {
        var n = arregloIdInputsVP.length;
        document.getElementById('cart-count-badge').textContent = n + ' producto(s)';
        document.getElementById('carritoVacioVale').style.display = n === 0 ? '' : 'none';
        var wrapper = document.getElementById('carritoTablaWrapper');
        if (wrapper) wrapper.classList.toggle('d-none', n === 0);
    }

    /* Collapsible of-card sections */
    function toggleOfCard(bodyId, titleEl) {
        var body = document.getElementById(bodyId);
        if (!body) return;
        var isVisible = body.style.display !== 'none';
        body.style.display = isVisible ? 'none' : '';
        if (titleEl) titleEl.classList.toggle('is-collapsed', isVisible);
    }

    /* Product search helpers */
    function limpiarProductoValeListaEspera() {
        document.getElementById('seleccionarProductoVale').innerHTML = '<option value="" selected disabled></option>';
        document.getElementById('codigoProductoValeListaEspera').value = '';
        var lbl = document.getElementById('productoSeleccionadoValeListaEspera');
        lbl.classList.add('d-none'); lbl.textContent = '';
        document.getElementById('historialPreciosPanel').classList.add('d-none');
    }

    function alSeleccionarProductoValeListaEspera(producto) {
        var select = document.getElementById('seleccionarProductoVale');
        select.innerHTML = '<option value="' + producto.id + '" selected>' + producto.nombre + '</option>';
        document.getElementById('codigoProductoValeListaEspera').value = producto.nombre;
        var label = document.getElementById('productoSeleccionadoValeListaEspera');
        label.textContent = 'OK ' + producto.nombre + ' (ID: ' + producto.id + ')';
        label.classList.remove('d-none');

        // Verificar existencia en bodega — el vale es solo para productos sin stock
        axios.get('/ventas/listar/bodegas/' + producto.id)
            .then(function(r) {
                var secciones = r.data.results || [];
                if (secciones.length > 0) {
                    var lista = secciones.map(function(s) { return '• ' + s.text; }).join('\n');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Producto con existencia',
                        html: '<p>Este producto <strong>sí tiene stock disponible</strong> para vender:</p>' +
                              '<pre style="text-align:left;font-size:0.82rem;background:#f8f9fa;padding:8px;border-radius:4px;">' +
                              lista + '</pre>' +
                              '<p class="mb-0 text-muted">Los vales de lista de espera son para productos <u>sin existencia</u>.</p>',
                        confirmButtonText: 'Entendido, continuar',
                        cancelButtonText: 'Cambiar producto',
                        showCancelButton: true,
                        confirmButtonColor: '#e65100',
                    });
                }
            })
            .catch(function() { /* si falla la consulta, no bloquear al usuario */ });

        obtenerImagenesVale();
        cargarHistorialPreciosValeListaEspera();
    }

    function buscarPorCodigoValeListaEspera(cod) {
        cod = String(cod).trim();
        if (!cod) { window['abrirBuscador_buscadorProductoValeListaEspera'](''); return; }
        axios.get('/productos/buscar', { params: { q: cod, page: 1 } })
            .then(function(r) {
                var items = r.data.data;
                var exact = items.find(function(p) { return String(p.id) === cod; });
                if (exact) { alSeleccionarProductoValeListaEspera(exact); }
                else if (items.length === 1) { alSeleccionarProductoValeListaEspera(items[0]); }
                else { window['abrirBuscador_buscadorProductoValeListaEspera'](cod); }
            });
    }

    function cargarHistorialPreciosValeListaEspera() {
        var productoId = $('#seleccionarProductoVale').val();
        var panel  = document.getElementById('historialPreciosPanel');
        var cuerpo = document.getElementById('historialPreciosCuerpo');
        if (!productoId || !_clienteId) { panel.classList.add('d-none'); return; }
        cuerpo.innerHTML = '<p class="text-muted small"><i class="fa fa-spinner fa-spin"></i> Cargando historial...</p>';
        panel.classList.remove('d-none');
        axios.post('/estatal/historial/precios', { cliente_id: _clienteId, producto_id: productoId })
            .then(function(response) {
                var rows = response.data.historial;
                if (!rows || rows.length === 0) {
                    cuerpo.innerHTML = '<p class="text-muted small mb-0">No hay ventas previas de este producto a este cliente.</p>';
                    return;
                }
                var fmt = new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 });
                var html = '<div class="table-responsive"><table class="table table-sm table-bordered table-hover mb-0" style="font-size:0.82rem;"><thead class="thead-light"><tr><th>Fecha</th><th>Factura</th><th>Precio Unit.</th><th>Cant.</th><th>Total</th><th>Categoria</th></tr></thead><tbody>';
                rows.forEach(function(r) {
                    html += '<tr><td>' + r.fecha_emision + '</td><td>' + r.numero_factura + '</td>'
                          + '<td class="text-right font-weight-bold text-success">' + fmt.format(r.precio_unidad) + '</td>'
                          + '<td class="text-center">' + r.cantidad + '</td>'
                          + '<td class="text-right">' + fmt.format(r.total) + '</td>'
                          + '<td><span class="badge badge-secondary">' + r.categoria + '</span></td></tr>';
                });
                html += '</tbody></table></div>';
                cuerpo.innerHTML = html;
            })
            .catch(function() {
                cuerpo.innerHTML = '<p class="text-danger small mb-0">Error al cargar el historial.</p>';
            });
    }

    /* Product categories – filtrado por escala del cliente, con fallback general */
    function cargarCategoriasProducto() {
        let productoId = $('#seleccionarProductoVale').val();
        if (!productoId) {
            $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>--Seleccione primero un producto--</option>');
            return;
        }
        $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>Cargando categorias...</option>');

        // Solo las categorías de precio de la escala del cliente — sin fallback
        axios.post('/producto/categorias-disponibles', {
            producto_id:                 productoId,
            cliente_categoria_escala_id: _categoriaClienteId,
            incluir_cp_inactivos:        true
        })
        .then(response => {
            let categorias = response.data.categorias || [];
            let fmt = new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 });

            $('#categoria_cliente_venta_id').empty();

            if (categorias.length === 0) {
                $('#categoria_cliente_venta_id').append(
                    '<option value="" selected disabled>Sin precios configurados para ' + _categoriaNombre + '</option>'
                );
                return;
            }

            categorias.sort((a, b) => (parseFloat(b.precio_a) || 0) - (parseFloat(a.precio_a) || 0));

            categorias.forEach((categoria, idx) => {
                let precio     = parseFloat(categoria.precio_a) || 0;
                let pFmt       = fmt.format(precio);
                let texto      = categoria.nombre_categoria + ' - ' + pFmt;
                // Pre-seleccionar la categoria_precios asignada al cliente; si no aplica, seleccionar la primera
                let isSelected = (_categoriaPrecionId && categoria.id == _categoriaPrecionId) ? true : (idx === 0 && !_categoriaPrecionId);
                let option     = new Option(texto, categoria.id, isSelected, isSelected);
                $('#categoria_cliente_venta_id').append(option);
            });

            // Si ninguna coincidió con _categoriaPrecionId, forzar selección del primero
            if (_categoriaPrecionId && $('#categoria_cliente_venta_id').val() == null) {
                $('#categoria_cliente_venta_id option:first').prop('selected', true);
            }

            $('#categoria_cliente_venta_id').prop('disabled', false);

        }).catch(err => {
            console.log(err);
            $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>Error al cargar categorías</option>');
        });
    }

    /* Price validation helper */
    function validacionPrecio(idPrecios, idprecio) {
        var precioSeleccionado = idPrecios.value;
        var idprecioIngresado  = idprecio.id;
        document.getElementById(idprecioIngresado).value = precioSeleccionado;
        document.getElementById(idprecioIngresado).setAttribute("min", precioSeleccionado);
    }

    /* Get product images */
    function obtenerImagenesVale() {
        let id = document.getElementById('seleccionarProductoVale').value;
        let htmlImagenes = '';
        axios.post('/producto/listar/imagenes', { id: id })
            .then(response => {
                let imagenes = response.data.imagenes;
                if (imagenes.length == 0) {
                    htmlImagenes += '<div class="carousel-item active"><img class="d-block img-size" src="' + public_path + '/noimage.png" alt="noimage.png"></div>';
                    document.getElementById('bloqueImagenesVale').innerHTML = htmlImagenes;
                    document.getElementById('botonAddVale').classList.remove("d-none");
                } else {
                    imagenes.forEach(element => {
                        let activeClass = element.contador == 1 ? 'active' : '';
                        htmlImagenes += '<div class="carousel-item ' + activeClass + '"><img class="d-block img-size" src="' + public_path + '/' + element.url_img + '" alt="imagen ' + element.contador + '"></div>';
                    });
                    document.getElementById('bloqueImagenesVale').innerHTML = htmlImagenes;
                    document.getElementById('botonAddVale').classList.remove("d-none");
                }
                let a = document.getElementById("detalleProductoVale");
                a.href = "/producto/detalle/" + id;
                a.classList.remove("d-none");
                cargarCategoriasProducto();
            })
            .catch(err => { console.log(err); });
    }

    /* Add product to cart */
    function agregarProductoVale() {
        let idProducto                = document.getElementById('seleccionarProductoVale').value;
        let categoria_cliente_venta_id = document.getElementById('categoria_cliente_venta_id').value;

        axios.post('/estatal/datos/producto', { idProducto: idProducto, categoria_cliente_venta_id: categoria_cliente_venta_id })
            .then(response => {
                // Duplicate check
                let flag = false;
                arregloIdInputsVP.forEach(idInput => {
                    if (document.getElementById("idProductoVP" + idInput).value == idProducto && !flag) { flag = true; }
                });
                if (flag) {
                    Swal.fire({
                        icon: 'warning', title: 'Advertencia!',
                        html: '<p class="text-left">El producto ya fue agregado anteriormente.<br>Modifique la cantidad si necesita mas unidades.</p>'
                    });
                    return;
                }

                let producto      = response.data.producto;
                let arrayUnidades = response.data.unidades;
                numeroInputsVP   += 1;
                let n             = numeroInputsVP;

                let htmlprecios = '<option value="' + producto.precio1 + '" selected data-id="p1">' + producto.precio1 + ' - A</option>';
                let htmlSelectUnidades = "";
                arrayUnidades.forEach(unidad => {
                    let sel = unidad.valor_defecto == 1 ? 'selected' : '';
                    htmlSelectUnidades += '<option ' + sel + ' value="' + unidad.id + '" data-id="' + unidad.idUnidadVenta + '">' + unidad.nombre + '</option>';
                });

                let htmlVP =
                '<tr id="VP' + n + '" class="cart-item-card">' +
                  '<td style="vertical-align:middle; text-align:center; padding:4px 6px;">' +
                    '<input id="idProductoVP' + n + '" name="idProductoVP' + n + '" type="hidden" value="' + producto.id + '">' +
                                        '<input id="preciosProductoCargaIdVP' + n + '" name="preciosProductoCargaIdVP' + n + '" type="hidden" value="' + producto.precios_producto_carga_id + '">' +
                    '<input id="restaInventarioVP' + n + '" name="restaInventarioVP' + n + '" type="hidden">' +
                    '<input id="isvVP' + n + '" name="isvVP' + n + '" type="hidden" value="' + producto.isv + '">' +
                    '<input id="acumuladoDescuentoVP' + n + '" name="acumuladoDescuentoVP' + n + '" type="hidden">' +
                    '<input id="subTotalVP' + n + '" name="subTotalVP' + n + '" type="hidden">' +
                    '<input id="isvProductoVP' + n + '" name="isvProductoVP' + n + '" type="hidden">' +
                    '<input id="totalVP' + n + '" name="totalVP' + n + '" type="hidden">' +
                    '<button class="btn btn-danger btn-xs" type="button" onclick="eliminarInputVP(' + n + ')" title="Eliminar" style="padding:2px 6px; font-size:11px; border-radius:5px;">' +
                      '<i class="fa fa-times"></i>' +
                    '</button>' +
                  '</td>' +
                  '<td style="vertical-align:middle; padding:4px 6px;">' +
                    '<input type="text" id="nombreVP' + n + '" name="nombreVP' + n + '" value="' + producto.nombre + '" readonly data-parsley-required' +
                    ' style="border:none; background:transparent; font-size:12px; font-weight:700; color:#1b5e20; width:100%; min-width:130px;">' +
                  '</td>' +
                  '<td style="vertical-align:middle; padding:4px 6px;">' +
                    '<select class="form-control form-control-sm" name="precios' + n + '" id="precios' + n + '" data-parsley-required style="font-size:11px; min-width:100px;"' +
                    ' onchange="validacionPrecio(precios' + n + ', precioVP' + n + ')">' +
                      htmlprecios +
                    '</select>' +
                  '</td>' +
                  '<td style="vertical-align:middle; padding:4px 6px;">' +
                    '<input type="number" id="precioVP' + n + '" name="precioVP' + n + '" value="' + producto.precio1 + '" class="form-control form-control-sm"' +
                    ' min="' + producto.precio1 + '" data-parsley-required step="any" autocomplete="off" style="min-width:80px; font-size:11px;"' +
                    ' onchange="calcularTotalesVP(precioVP' + n + ',cantidadVP' + n + ',' + producto.isv + ',unidadVP' + n + ',' + n + ',restaInventarioVP' + n + ')">' +
                  '</td>' +
                  '<td style="vertical-align:middle; padding:4px 6px;">' +
                    '<input type="number" id="cantidadVP' + n + '" name="cantidadVP' + n + '" class="form-control form-control-sm" min="1" data-parsley-required autocomplete="off" style="min-width:60px; font-size:11px;"' +
                    ' onchange="calcularTotalesVP(precioVP' + n + ',cantidadVP' + n + ',' + producto.isv + ',unidadVP' + n + ',' + n + ',restaInventarioVP' + n + ')">' +
                  '</td>' +
                  '<td style="vertical-align:middle; padding:4px 6px;">' +
                    '<select class="form-control form-control-sm" name="unidadVP' + n + '" id="unidadVP' + n + '" data-parsley-required style="font-size:11px; min-width:90px;"' +
                    ' onchange="calcularTotalesVP(precioVP' + n + ',cantidadVP' + n + ',' + producto.isv + ',unidadVP' + n + ',' + n + ',restaInventarioVP' + n + ')">' +
                      htmlSelectUnidades +
                    '</select>' +
                  '</td>' +
                  '<td style="vertical-align:middle; padding:4px 6px; text-align:right;">' +
                    '<input type="text" id="subTotalMostrarVP' + n + '" name="subTotalMostrarVP' + n + '" placeholder="0.00" readonly autocomplete="off"' +
                    ' style="border:none; background:#f1f8e9; border-radius:5px; font-weight:700; color:#2e7d32; font-size:12px; padding:2px 6px; text-align:right; width:100%; min-width:75px;">' +
                  '</td>' +
                  '<td style="vertical-align:middle; padding:4px 6px; text-align:right;">' +
                    '<input type="text" id="isvProductoMostrarVP' + n + '" name="isvProductoMostrarVP' + n + '" placeholder="0.00" readonly autocomplete="off"' +
                    ' style="border:none; background:#fce4ec; border-radius:5px; font-weight:700; color:#b71c1c; font-size:12px; padding:2px 6px; text-align:right; width:100%; min-width:65px;">' +
                  '</td>' +
                  '<td style="vertical-align:middle; padding:4px 6px; text-align:right;">' +
                    '<input type="text" id="totalMostrarVP' + n + '" name="totalMostrarVP' + n + '" placeholder="0.00" readonly autocomplete="off"' +
                    ' style="border:none; background:#e8f5e9; border-radius:5px; font-weight:700; color:#1b5e20; font-size:13px; padding:2px 6px; text-align:right; width:100%; min-width:80px;">' +
                  '</td>' +
                '</tr>';

                arregloIdInputsVP.splice(n, 0, n);
                document.getElementById('divProductosVale').insertAdjacentHTML('beforeend', htmlVP);
                actualizarCartCount();
            })
            .catch(err => {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Error!', text: "Ha ocurrido un error al agregar el producto al vale." });
            });
    }

    /* Remove product from cart */
    function eliminarInputVP(id) {
        document.getElementById("VP" + id).remove();
        var myIndex = arregloIdInputsVP.indexOf(id);
        if (myIndex !== -1) {
            arregloIdInputsVP.splice(myIndex, 1);
            totalesGeneralesVP();
        }
        actualizarCartCount();
    }

    /* Calculate row totals */
    function calcularTotalesVP(idPrecio, idCantidad, isvProducto, idUnidad, id, idRestaInventario) {
        let valorInputPrecio   = Number(idPrecio.value).toFixed(2);
        let valorInputCantidad = idCantidad.value;
        let valorSelectUnidad  = idUnidad.value;
        let subTotal = 0, descuentoCalculado = 0, isv = 0, total = 0;

        if (valorInputPrecio && valorInputCantidad) {
            let descuento = document.getElementById("porDescuento").value;
            if (descuento > 0) {
                subTotal           = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                descuentoCalculado = subTotal * (descuento / 100);
                document.getElementById("acumuladoDescuentoVP" + id).value = descuentoCalculado;
                subTotal = subTotal - descuentoCalculado;
                isv      = subTotal * (isvProducto / 100);
                total    = subTotal + (subTotal * (isvProducto / 100));
            } else {
                document.getElementById("acumuladoDescuentoVP" + id).value = 0;
                subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                isv      = subTotal * (isvProducto / 100);
                total    = subTotal + subTotal * (isvProducto / 100);
            }

            let fmt = new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 });
            document.getElementById('totalVP'              + id).value = total.toFixed(2);
            document.getElementById('totalMostrarVP'       + id).value = fmt.format(total);
            document.getElementById('subTotalVP'           + id).value = subTotal.toFixed(2);
            document.getElementById('subTotalMostrarVP'    + id).value = fmt.format(subTotal);
            document.getElementById('isvProductoVP'        + id).value = isv.toFixed(2);
            document.getElementById('isvProductoMostrarVP' + id).value = fmt.format(isv);
            idRestaInventario.value = valorInputCantidad * valorSelectUnidad;
            totalesGeneralesVP();
        }
        return 0;
    }

    /* Calculate order totals */
    function totalesGeneralesVP() {
        if (arregloIdInputsVP.length === 0) return;
        let totalGeneralValor = 0, totalISV = 0;
        let subTotalGeneralGrabadoValor = 0, subTotalGeneralExcentoValor = 0;
        let subTotalGeneral = 0, acumularDescuento = 0;

        for (let i = 0; i < arregloIdInputsVP.length; i++) {
            let idx          = arregloIdInputsVP[i];
            let subTotalFila = Number(document.getElementById('subTotalVP'     + idx).value);
            let isvFila      = Number(document.getElementById('isvProductoVP'  + idx).value);

            if (isvFila === 0) { subTotalGeneralExcentoValor += subTotalFila; }
            else if (subTotalFila > 0) { subTotalGeneralGrabadoValor += subTotalFila; }

            subTotalGeneral   += subTotalFila;
            totalISV          += isvFila;
            totalGeneralValor += Number(document.getElementById('totalVP'              + idx).value);
            acumularDescuento += Number(document.getElementById('acumuladoDescuentoVP' + idx).value);
        }

        let fmt = new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 });
        document.getElementById('descuentoGeneral').value                 = acumularDescuento.toFixed(2);
        document.getElementById('descuentoMostrar').value                 = fmt.format(acumularDescuento);
        document.getElementById('subTotalGeneral').value                  = subTotalGeneral.toFixed(2);
        document.getElementById('subTotalGeneralMostrar').value           = fmt.format(subTotalGeneral);
        document.getElementById('subTotalGeneralGrabado').value           = subTotalGeneralGrabadoValor.toFixed(2);
        document.getElementById('subTotalGeneralGrabadoMostrar').value    = fmt.format(subTotalGeneralGrabadoValor);
        document.getElementById('subTotalGeneralExcento').value           = subTotalGeneralExcentoValor.toFixed(2);
        document.getElementById('subTotalGeneralExcentoMostrar').value    = fmt.format(subTotalGeneralExcentoValor);
        document.getElementById('isvGeneral').value                       = totalISV.toFixed(2);
        document.getElementById('isvGeneralMostrar').value                = fmt.format(totalISV);
        document.getElementById('totalGeneral').value                     = totalGeneralValor.toFixed(2);
        document.getElementById('totalGeneralMostrar').value              = fmt.format(totalGeneralValor);
    }

    /* Form submit */
    // Recalcular todos los items del carrito cuando cambia el descuento
    $(document).on('change', '#porDescuento', function() {
        arregloIdInputsVP.forEach(function(n) {
            var el = document.getElementById('precioVP' + n);
            if (el) el.dispatchEvent(new Event('change'));
        });
    });

    $(document).on('submit', '#crear_venta', function(event) {
        event.preventDefault();
        guardarVenta();
    });

    function guardarVenta() {
        document.getElementById("btn_venta_vale_coorporativo").disabled = true;
        let data = new FormData($('#crear_venta').get(0));

        for (var i = 0; i < arregloIdInputsVP.length; i++) {
            let name     = "unidadVP"        + arregloIdInputsVP[i];
            let nameForm = "idUnidadVentaVP"  + arregloIdInputsVP[i];
            let e        = document.getElementById(name);
            let idUnidad = e.options[e.selectedIndex].getAttribute("data-id");
            data.append(nameForm, idUnidad);
        }
        data.append("numeroInputsVP",    numeroInputsVP);
        data.append("arregloIdInputsVP", arregloIdInputsVP.toString());

        const formDataObj = {};
        data.forEach((value, key) => (formDataObj[key] = value));
        const options = { headers: { "content-type": "application/json" } };

        axios.post('/vale/lista/espera/guardar', formDataObj, options)
            .then(response => {
                let data = response.data;
                if (data.idFactura == 0) {
                    Swal.fire({ icon: data.icon, title: data.title, html: data.text, confirmButtonColor: "#18A689" });
                    document.getElementById("btn_venta_vale_coorporativo").disabled = false;
                    return;
                }
                Swal.fire({ confirmButtonText: 'Cerrar', confirmButtonColor: '#18A689', icon: data.icon, title: data.title, html: data.text });
                if (data.estadoBorrar == true) {
                    document.getElementById("btn_venta_vale_coorporativo").disabled = false;
                    return;
                }
                // Reset form
                document.getElementById('bloqueImagenesVale').innerHTML = '';
                document.getElementById('divProductosVale').innerHTML   = '';
                document.getElementById("crear_venta").reset();
                $('#crear_venta').parsley().reset();
                let a = document.getElementById('detalleProductoVale');
                a.classList.add("d-none");
                a.href = "";
                arregloIdInputsVP = [];
                numeroInputsVP    = 0;
                actualizarCartCount();
                document.getElementById("btn_venta_vale_coorporativo").disabled = false;
                document.getElementById('botonAddVale').classList.add("d-none");
                limpiarProductoValeListaEspera();
            })
            .catch(err => {
                document.getElementById("btn_venta_vale_coorporativo").disabled = false;
                console.log(err);
                Swal.fire({ icon: 'error', title: 'Error!', text: "Ha ocurrido un error al intentar crear el vale." });
            });
    }

    /* Placeholder - not used in this form */
    function habilitarBodega() {}
    </script>
    @endpush
</div>
