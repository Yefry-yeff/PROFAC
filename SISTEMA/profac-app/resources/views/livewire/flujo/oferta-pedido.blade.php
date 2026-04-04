<div>
    @push('styles')
        <style>
            .switch {
                position: relative;
                display: inline-block;
                width: 60px;
                height: 34px;
            }
            .switch input { opacity: 0; width: 0; height: 0; }
            .slider {
                position: absolute;
                cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
                background-color: #ccc; transition: .4s;
            }
            .slider:before {
                position: absolute; content: "";
                height: 26px; width: 26px; border-radius: 50%;
                left: 4px; bottom: 4px;
                background-color: white; transition: .4s;
            }
            input:checked + .slider { background-color: #2196F3; }
            input:checked + .slider:before { transform: translateX(26px); }
            .img-size { width: 100%; height: 20rem; margin: 0 auto; }
            @media (min-width: 768px) and (max-width:960px) {
                .img-size { width: 75%; height: 12rem; margin: 0 auto; }
            }
            input::-webkit-outer-spin-button,
            input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
            input[type=number] { -moz-appearance: textfield; }
        </style>
    @endpush

    {{-- ===== ENCABEZADO ===== --}}
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
                padding:10px 24px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
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
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title"
                         style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%); border:none;">
                        <div class="row">
                            <div class="col-12 col-md-6 d-flex align-items-center gap-2">
                                <h3 class="mb-0" style="color:#fff;">
                                    <i class="fa fa-tag mr-2"></i>Oferta de Pedido #{{ $pedidoId }}
                                </h3>
                            </div>
                            <div class="col-12 col-md-6 d-flex align-items-center gap-2">
                                <span class="text-white small">Categoría del Cliente:</span>
                                <span id="categoria_cliente_nombre"
                                      class="badge badge-light px-3 py-2"></span>
                            </div>
                        </div>
                    </div>

                    <div class="ibox-content">
                        <form onkeydown="return event.key != 'Enter';" autocomplete="off"
                              id="crear_oferta" name="crear_oferta" data-parsley-validate>

                            {{-- Hidden fields --}}
                            <input type="hidden" id="tipo_venta_id" name="tipo_venta_id" value="{{ $tipoCotizacion }}">
                            <input type="hidden" id="pedido_id" name="pedido_id" value="{{ $pedidoId }}">

                            <div class="row">
                                {{-- Cliente --}}
                                <div class="col-12 col-md-6">
                                    <label class="col-form-label">Seleccionar Cliente:<span class="text-danger">*</span></label>
                                    <select id="seleccionarCliente" name="seleccionarCliente"
                                            class="form-group form-control" data-parsley-required
                                            onchange="obtenerDatosCliente()">
                                        @if($pedidoCliente)
                                            <option value="{{ $pedidoCliente['cliente_id'] }}" selected>
                                                {{ $pedidoCliente['nombre_cliente'] }}
                                            </option>
                                        @else
                                            <option value="" selected disabled>--Seleccionar un cliente--</option>
                                        @endif
                                    </select>
                                </div>

                                {{-- Nombre cliente --}}
                                <div class="col-12 col-md-6">
                                    <label class="col-form-label">Nombre del cliente:<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="text"
                                           id="nombre_cliente_ventas" name="nombre_cliente_ventas"
                                           value="{{ $pedidoCliente['nombre_cliente'] ?? '' }}"
                                           data-parsley-required readonly>
                                </div>

                                {{-- Vendedor --}}
                                <div class="col-12 col-md-6">
                                    <label for="vendedor">Seleccionar Vendedor:</label>
                                    <select name="vendedor" id="vendedor"
                                            class="form-group form-control" data-parsley-required>
                                        <option value="" selected disabled>--Seleccionar un vendedor--</option>
                                    </select>
                                </div>

                                {{-- RTN --}}
                                <div class="col-12 col-md-6">
                                    <label class="col-form-label">RTN:<span class="text-danger">*</span></label>
                                    <input class="form-control" type="text"
                                           id="rtn_ventas" name="rtn_ventas"
                                           value="{{ $pedidoCliente['rtn'] ?? '' }}"
                                           readonly>
                                </div>

                                {{-- Tipo pago (hidden) --}}
                                <div class="col-12 col-md-6" style="display:none;">
                                    <label class="col-form-label">Tipo de pago:<span class="text-danger">*</span></label>
                                    <select class="form-group form-control" name="tipoPagoVenta"
                                            id="tipoPagoVenta" onchange="validarFechaPago()"></select>
                                </div>

                                {{-- Fecha emisión --}}
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Fecha de emisión:<span class="text-danger">*</span></label>
                                        <input class="form-control" type="date"
                                               id="fecha_emision" name="fecha_emision"
                                               value="{{ date('Y-m-d') }}" data-parsley-required
                                               onchange="sumarDiasCredito()">
                                    </div>
                                </div>

                                {{-- Fecha vencimiento (hidden) --}}
                                <div class="col-12 col-md-6" style="display:none;">
                                    <div class="form-group">
                                        <label class="col-form-label text-warning">Fecha de vencimiento:</label>
                                        <input class="form-control" type="date"
                                               id="fecha_vencimiento" name="fecha_vencimiento"
                                               value="" min="{{ date('Y-m-d') }}" readonly>
                                    </div>
                                </div>

                                {{-- Descuento --}}
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Descuento aplicado %:<span class="text-danger">*</span></label>
                                        <input class="form-control" type="number" min="0" max="50"
                                               value="0" id="porDescuento" name="porDescuento"
                                               data-parsley-required
                                               oninput="validarDescuento()"
                                               onchange="calcularTotalesInicioPagina()">
                                        <p id="mensajeError" style="color:red;"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Product selection row --}}
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="col-12">
                                        <label class="col-form-label">Seleccionar Producto:<span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" id="codigoProductoCotizacion" class="form-control"
                                                   placeholder="ID o nombre del producto…" autocomplete="off"
                                                   onkeydown="if(event.key==='Enter'){buscarPorCodigoCotizacion(this.value);return false;}">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" title="Buscar producto"
                                                        onclick="limpiarProductoCotizacion(); window['abrirBuscador_buscadorProductoCotizacion'](document.getElementById('codigoProductoCotizacion').value||'')">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small id="productoSeleccionadoCotizacion" class="text-success font-weight-bold mt-1 d-block d-none"></small>
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
                                            label.textContent = '✓ ' + producto.nombre + ' (ID: ' + producto.id + ')';
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
                                            cuerpo.innerHTML = '<p class="text-muted small"><i class="fa fa-spinner fa-spin"></i> Cargando historial...</p>';
                                            panel.classList.remove('d-none');
                                            axios.post('/estatal/historial/precios', { cliente_id: clienteId, producto_id: productoId })
                                            .then(function(response) {
                                                var rows = response.data.historial;
                                                if (!rows || rows.length === 0) {
                                                    cuerpo.innerHTML = '<p class="text-muted small">No hay ventas previas de este producto a este cliente.</p>'; return;
                                                }
                                                var fmt = new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 });
                                                var html = '<div class="table-responsive"><table class="table table-sm table-bordered table-hover mb-0" style="font-size:0.82rem;"><thead class="thead-light"><tr><th>Fecha</th><th>Factura</th><th>Precio Unit.</th><th>Cant.</th><th>Total</th><th>Categoría</th></tr></thead><tbody>';
                                                rows.forEach(function(r) {
                                                    html += '<tr><td>' + r.fecha_emision + '</td><td>' + r.numero_factura + '</td><td class="text-right font-weight-bold text-success">' + fmt.format(r.precio_unidad) + '</td><td class="text-center">' + r.cantidad + '</td><td class="text-right">' + fmt.format(r.total) + '</td><td><span class="badge badge-secondary">' + r.categoria + '</span></td></tr>';
                                                });
                                                html += '</tbody></table></div>';
                                                cuerpo.innerHTML = html;
                                            }).catch(function() { cuerpo.innerHTML = '<p class="text-danger small">Error al cargar el historial.</p>'; });
                                        }
                                        </script>
                                        @endpush
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="col-12">
                                        <label class="col-form-label">Categoría Precio Producto:<span class="text-danger">*</span></label>
                                        <select id="categoria_cliente_venta_id" name="categoria_cliente_venta_id"
                                                class="form-group form-control" onchange="listaCategoríaClientes()">
                                            <option value="" selected disabled>--Seleccione primero un producto--</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="col-12">
                                        <label class="col-form-label">Seleccionar bodega:<span class="text-danger">*</span></label>
                                        <select id="bodega" name="bodega" class="form-group form-control"
                                                onchange="prueba()">
                                            <option value="" selected disabled>--Seleccione una categoría primero--</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-6 mt-4">
                                    <div class="text-center">
                                        <a id="detalleProducto" href="" class="font-bold h3 d-none text-success" target="_blank">
                                            <i class="fa fa-info-circle"></i> Ver Detalles De Producto
                                        </a>
                                    </div>
                                    <div id="carouselProducto" class="carousel slide mt-2" data-ride="carousel">
                                        <div id="bloqueImagenes" class="carousel-inner"></div>
                                        <a class="carousel-control-prev" href="#carouselProducto" role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                        <a class="carousel-control-next" href="#carouselProducto" role="button" data-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-12 col-md-6">
                                    <div id="botonAdd" class="col-12 my-4 text-center d-none">
                                        <button type="button" class="btn-rounded btn btn-success p-3"
                                                style="font-weight:900;"
                                                onclick="agregarProductoCarrito()">
                                            Añadir Producto a oferta <i class="fa fa-cart-plus"></i>
                                        </button>
                                    </div>
                                    <div id="historialPreciosPanel" class="d-none mt-3">
                                        <h5 class="mb-2 text-dark"><i class="fa fa-history text-info"></i> Últimas 5 ventas a este cliente</h5>
                                        <div id="historialPreciosCuerpo"><p class="text-muted small">Cargando...</p></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="col-form-label">Nota:</label>
                                        <textarea class="form-control" id="nota" name="nota"
                                                  cols="30" rows="3" maxlength="250"></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Column headers (hidden, just for spacing) --}}
                            <div class="hide-container">
                                <div class="row no-gutters">
                                    <div class="form-group col-12 col-md-2">
                                        <input type="text" placeholder="Producto" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-6 col-md-1">
                                        <input type="number" placeholder="Bodega" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-6 col-md-2">
                                        <input type="number" placeholder="Opciones" class="form-control" min="1" disabled>
                                    </div>
                                    <div class="form-group col-6 col-md-2">
                                        <input type="number" placeholder="Precio" class="form-control" min="1" disabled>
                                    </div>
                                    <div class="form-group col-4 col-md-1">
                                        <input type="text" placeholder="Cantidad" class="form-control" min="1" disabled>
                                    </div>
                                    <div class="form-group col-4 col-md-1">
                                        <input type="text" placeholder="Unidad" class="form-control" min="1" disabled>
                                    </div>
                                    <div class="form-group col-4 col-md-1">
                                        <input type="number" placeholder="Sub total" class="form-control" min="1" disabled>
                                    </div>
                                    <div class="form-group col-4 col-md-1">
                                        <input type="number" placeholder="ISV" class="form-control" min="1" disabled>
                                    </div>
                                    <div class="form-group col-4 col-md-1">
                                        <input type="number" placeholder="Total" class="form-control" min="1" disabled>
                                    </div>
                                </div>
                            </div>

                            {{-- Dynamic product rows --}}
                            <div id="divProductos"></div>

                            {{-- Totals --}}
                            <div class="row">
                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1">
                                    <label class="col-form-label">Descuento L.<span class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2">
                                    <input type="text" placeholder="Descuento aplicado" id="descuentoMostrar"
                                           name="descuentoMostrar" class="form-control" data-parsley-required autocomplete="off" readonly>
                                    <input type="hidden" id="descuentoGeneral" name="descuentoGeneral" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1">
                                    <label class="col-form-label">Sub Total L.<span class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2">
                                    <input type="text" placeholder="Sub total" id="subTotalGeneralMostrar"
                                           name="subTotalGeneralMostrar" class="form-control" data-parsley-required autocomplete="off" readonly>
                                    <input id="subTotalGeneral" name="subTotalGeneral" type="hidden" value="" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1">
                                    <label class="col-form-label">Sub Total Grabado L.<span class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2">
                                    <input type="text" placeholder="Sub total" id="subTotalGeneralGrabadoMostrar"
                                           name="subTotalGeneralGrabadoMostrar" class="form-control" data-parsley-required autocomplete="off" readonly>
                                    <input id="subTotalGeneralGrabado" name="subTotalGeneralGrabado" type="hidden" value="" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1">
                                    <label class="col-form-label">Sub Total Excento L.<span class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2">
                                    <input type="text" placeholder="Sub total" id="subTotalGeneralExcentoMostrar"
                                           name="subTotalGeneralExcentoMostrar" class="form-control" data-parsley-required autocomplete="off" readonly>
                                    <input id="subTotalGeneralExcento" name="subTotalGeneralExcento" type="hidden" value="" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1">
                                    <label class="col-form-label">ISV L.<span class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2">
                                    <input type="text" placeholder="ISV" id="isvGeneralMostrar"
                                           name="isvGeneralMostrar" class="form-control" data-parsley-required autocomplete="off" readonly>
                                    <input id="isvGeneral" name="isvGeneral" type="hidden" value="" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1">
                                    <label class="col-form-label">Total L.<span class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2">
                                    <input type="text" placeholder="Total" id="totalGeneralMostrar"
                                           name="totalGeneralMostrar" class="form-control" data-parsley-required autocomplete="off" readonly>
                                    <input id="totalGeneral" name="totalGeneral" type="hidden" value="" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button id="guardar_oferta_btn" type="button"
                                            class="btn btn-warning float-left m-t-n-xs"
                                            style="font-weight:700; color:#fff; min-width:200px; padding:10px 20px;"
                                            onclick="guardarVenta()">
                                        <i class="fa fa-save mr-2"></i><strong>Guardar Oferta</strong>
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>{{-- /ibox-content --}}
                </div>{{-- /ibox --}}
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Init Select2 for client — reuse cotizacion endpoint
            $('#seleccionarCliente').select2({
                ajax: {
                    url: '/oferta/clientes',
                    data: function(params) {
                        return {
                            search: params.term,
                            tipoCotizacion: {{ $tipoCotizacion }},
                            type: 'public',
                            page: params.page || 1
                        };
                    }
                }
            });
        </script>
        <script>var public_path = "{{ asset('catalogo/') }}";</script>
        <script src="{{ asset('js/js_proyecto/flujo/oferta.js') }}"></script>
    @endpush
</div>
