<div>
    @push('styles')
        <style>
            /* Chrome, Safari, Edge, Opera */
            input::-webkit-outer-spin-button,
            input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            /* Firefox */
            input[type=number] {
                -moz-appearance: textfield;
            }



            @media (max-width: 767.5px) {
                .hide-container {
                    display: none;
                }

            }

            .center-div {
                text-align: center
            }
        </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Facturar Cotización</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a>Cliente A</a>
                </li>
                {{-- <li class="breadcrumb-item">
                    <a data-toggle="modal" data-target="#modal_producto_crear">Registrar</a>
                </li> --}}

            </ol>
        </div>


        {{-- <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
                <div style="margin-top: 1.5rem">
                    <a href="#" class="btn add-btn btn-primary" data-toggle="modal" data-target="#modal_producto_crear"><i
                            class="fa fa-plus"></i> Registrar Producto</a>
                </div>
            </div> --}}


    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Datos de compra <i class="fa-solid fa-cart-shopping"></i></h3>
                    </div>
                    <div class="ibox-content">
                        <form onkeydown="return event.key != 'Enter';" autocomplete="off" id="crear_venta"
                            name="crear_venta" data-parsley-validate>
                            <input type="hidden" id="restriccion" name="restriccion" value="1">
                            <input type="hidden" id="tipo_venta_id" name="tipo_venta_id" value="2">
                            <input name="idComprobante" id="idComprobante" type="hidden" value="null">

                            <div class="row">
                                <div class="col-6 col-sm-6 col-md-2 col-lg-2 col-xl-2">
                                    <label class="col-form-label text-danger" for="numero_venta"
                                        style="font-size: 1.5rem; font-weight:600;">Numero de Venta</label>
                                </div>

                                <div class="col-6 col-sm-6 col-md-2 col-lg-2 col-xl-2">
                                    <input class="form-control"
                                        style="font-size: 1.5rem; font-weight:600; text-align:center" type="text"
                                        id="numero_venta" name="numero_venta" value="" data-parsley-required
                                        readonly>
                                </div>


                            </div>

                            <div class="row  mt-4 mb-4">

                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                    <label for="seleccionarCliente" class="col-form-label focus-label">Seleccionar
                                        Cliente:<span class="text-danger">*</span> </label>
                                    <select id="seleccionarCliente" name="seleccionarCliente"
                                        class="form-group form-control" style="" data-parsley-required
                                        onchange="obtenerDatosCliente()">
                                        <option value="{{ $cotizacion->cliente_id }}" selected>
                                            {{ $cotizacion->nombre_cliente }}</option>
                                    </select>
                                </div>

                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                    <label for="ordenCompra" class="col-form-label focus-label">Seleccionar un número de
                                        orden de compra:<span class="text-danger">*</span> </label>
                                    <select class="form-group form-control " name="ordenCompra" id="ordenCompra">
                                        <option value="" selected disabled>--Seleccionar un número de compra--
                                        </option>

                                    </select>
                                </div>


                            </div>


                            <div class="row ">
                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label for="vendedor" class="col-form-label focus-label">Seleccionar Vendedor:<span
                                            class="text-danger">*</span> </label>
                                    <select name="vendedor" id="vendedor" class="form-group form-control" required>
                                        <option value="" selected disabled>--Seleccionar un vendedor--</option>
                                    </select>

                                </div>

                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label class="col-form-label focus-label">Nombre del cliente</label>
                                    <input class="form-control" required type="text" id="nombre_cliente_ventas"
                                        name="nombre_cliente_ventas" value="{{ $cotizacion->nombre_cliente }}" readonly>

                                </div>

                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label class="col-form-label focus-label">RTN</label>
                                    <input class="form-control" type="text" id="rtn_ventas" name="rtn_ventas"
                                        value="{{ $cotizacion->RTN }}" readonly>

                                </div>





                            </div>

                            <div class="row mt-4">
                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">

                                        <label for="fecha_emision" class="col-form-label focus-label">Descuento aplicado
                                            %
                                            :<span class="text-danger">*</span></label>
                                        <input class="form-control" type="number" min="0" max="35"
                                            value="{{ $cotizacion->porc_descuento }}" minlength="1" maxlength="2"
                                            id="porDescuento" name="porDescuento" data-parsley-required
                                            onchange="calcularTotalesInicioPagina()">

                                        <p id="mensajeError" style="color: red;"></p>


                                    </div>
                                </div>
                            </div>




                            <div class="row mt-4">
                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label for="tipoPagoVenta" class="col-form-label focus-label">Seleccionar tipo de
                                        pago:</label>
                                    <select class="form-group form-control " name="tipoPagoVenta" id="tipoPagoVenta"
                                        data-parsley-required onchange="validarFechaPago()">
                                    </select>
                                </div>

                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">

                                        <label for="fecha_emision" class="col-form-label focus-label">Fecha de emisión
                                            :</label>
                                        <input class="form-control" type="date" id="fecha_emision"
                                            onchange="sumarDiasCredito()" name="fecha_emision"
                                            value="{{ date('Y-m-d') }}" data-parsley-required>

                                    </div>
                                </div>


                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">
                                        <label for="fecha_vencimiento"
                                            class="col-form-label focus-label text-warning">Fecha de vencimiento:
                                        </label>
                                        <input class="form-control" type="date" id="fecha_vencimiento"
                                            name="fecha_vencimiento" value="" data-parsley-required
                                            min="{{ date('Y-m-d') }}" readonly>
                                    </div>
                                </div>


                            </div>

                            <div class="row">



                            </div>
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <div class="form-group">
                                        <label for="nota" class="col-form-label focus-label">Nota:
                                        </label>
                                        <textarea class="form-control" id="nota_comen" name="nota_comen" cols="30" rows="3" maxlength="250"></textarea>
                                    </div>

                                </div>


                            </div>

                            <div class="row mt-4">
                                <div class="col-12 col-md-4 col-lg-4 col-xl-4">

                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <label class="col-form-label focus-label">Seleccionar Producto:</label>
                                        <div class="input-group">
                                            <input type="text" id="codigoProductoFacturaCotiGob" class="form-control"
                                                   placeholder="ID o nombre del producto…" autocomplete="off"
                                                   onkeydown="if(event.key==='Enter'){buscarPorCodigoFacturaCotiGob(this.value);return false;}">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" title="Buscar producto"
                                                        onclick="limpiarProductoFacturaCotiGob(); window['abrirBuscador_buscadorProductoFacturaCotiGob'](document.getElementById('codigoProductoFacturaCotiGob').value||'')">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small id="productoSeleccionadoFacturaCotiGob" class="text-success font-weight-bold mt-1 d-block d-none"></small>
                                        {{-- Hidden select conserva la compatibilidad con el JS existente --}}
                                        <select id="seleccionarProducto" name="seleccionarProducto" class="d-none">
                                            <option value="" selected disabled></option>
                                        </select>
                                        <x-buscador-producto id-modal="buscadorProductoFacturaCotiGob" callback="alSeleccionarProductoFacturaCotiGob" />
                                        @push('scripts')
                                        <script>
                                        function limpiarProductoFacturaCotiGob() {
                                            document.getElementById('seleccionarProducto').innerHTML = '<option value="" selected disabled></option>';
                                            document.getElementById('codigoProductoFacturaCotiGob').value = '';
                                            var lbl = document.getElementById('productoSeleccionadoFacturaCotiGob');
                                            lbl.classList.add('d-none'); lbl.textContent = '';
                                            document.getElementById('historialPreciosPanel').classList.add('d-none');
                                        }
                                        function alSeleccionarProductoFacturaCotiGob(producto) {
                                            var select = document.getElementById('seleccionarProducto');
                                            select.innerHTML = '<option value="' + producto.id + '" selected>' + producto.nombre + '</option>';
                                            document.getElementById('codigoProductoFacturaCotiGob').value = producto.nombre;
                                            var label = document.getElementById('productoSeleccionadoFacturaCotiGob');
                                            label.textContent = '✓ ' + producto.nombre + ' (ID: ' + producto.id + ')';
                                            label.classList.remove('d-none');
                                            cargarCategoriasProducto();
                                            cargarHistorialPreciosFacturaCotiGob();
                                        }
                                        function buscarPorCodigoFacturaCotiGob(cod) {
                                            cod = String(cod).trim();
                                            if (!cod) { window['abrirBuscador_buscadorProductoFacturaCotiGob'](''); return; }
                                            axios.get('/productos/buscar', { params: { q: cod, page: 1 } })
                                                .then(function(r) {
                                                    var items = r.data.data;
                                                    var exact = items.find(function(p) { return String(p.id) === cod; });
                                                    if (exact) { alSeleccionarProductoFacturaCotiGob(exact); }
                                                    else if (items.length === 1) { alSeleccionarProductoFacturaCotiGob(items[0]); }
                                                    else { window['abrirBuscador_buscadorProductoFacturaCotiGob'](cod); }
                                                });
                                        }
                                        function cargarHistorialPreciosFacturaCotiGob() {
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
                                                if (!rows || rows.length === 0) { cuerpo.innerHTML = '<p class="text-muted small">No hay ventas previas de este producto a este cliente.</p>'; return; }
                                                var fmt = new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 });
                                                var html = '<div class="table-responsive"><table class="table table-sm table-bordered table-hover mb-0" style="font-size:0.82rem;"><thead class="thead-light"><tr><th>Fecha</th><th>Factura</th><th>Precio Unit.</th><th>Cant.</th><th>Total</th><th>Categoría</th></tr></thead><tbody>';
                                                rows.forEach(function(r) { html += '<tr><td>' + r.fecha_emision + '</td><td>' + r.numero_factura + '</td><td class="text-right font-weight-bold text-success">' + fmt.format(r.precio_unidad) + '</td><td class="text-center">' + r.cantidad + '</td><td class="text-right">' + fmt.format(r.total) + '</td><td><span class="badge badge-secondary">' + r.categoria + '</span></td></tr>'; });
                                                html += '</tbody></table></div>';
                                                cuerpo.innerHTML = html;
                                            }).catch(function() { cuerpo.innerHTML = '<p class="text-danger small">Error al cargar el historial.</p>'; });
                                        }
                                        </script>
                                        @endpush
                                    </div>



                                </div>

                                <div class="col-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <label for="categoriaCliente" class="col-form-label focus-label">Categoría / Cliente:</label>
                                        <select id="categoriaCliente" name="categoriaCliente" class="form-group form-control"
                                            style="" onchange="habilitarBodega()" disabled>
                                            <option value="" selected disabled>--Seleccione un producto--
                                            </option>
                                        </select>
                                    </div>

                                </div>

                                <div class="col-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <label for="bodega" class="col-form-label focus-label">Seleccionar
                                            bodega:</label>
                                        <select id="bodega" name="bodega" class="form-group form-control"
                                            style="" onchange="prueba()" disabled>
                                            <option value="" selected disabled>--Seleccione una categoría--
                                            </option>
                                        </select>
                                    </div>

                                </div>


                            </div>

                            <div class="row">


                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 mt-4">
                                    <div class="text-center">
                                        <a id="detalleProducto" href=""
                                            class="font-bold h3  d-none text-success" style="" target="_blank">
                                            <i class="fa-solid fa-circle-info"></i> Ver Detalles De Producto </a>
                                    </div>


                                    <div id="carouselProducto" class="carousel slide mt-2" data-ride="carousel">
                                        {{-- <ol  id="carousel_imagenes_producto" class="carousel-indicators">

                                                <li data-target="#carouselProducto" data-slide-to="{{ $i }}" class="active"></li>

                                                <li data-target="#carouselProducto" data-slide-to="{{ $i }}" class=""></li>



                                        </ol> --}}
                                        <div id="bloqueImagenes" class="carousel-inner ">






                                        </div>
                                        <a class="carousel-control-prev" href="#carouselProducto" role="button"
                                            data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                        <a class="carousel-control-next" href="#carouselProducto" role="button"
                                            data-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    </div>


                                </div>

                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 ">
                                    <div id="botonAdd"
                                        class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 my-4 text-center d-none">
                                        <button type="button" class="btn-rounded btn btn-success p-3"
                                            style="font-weight: 900; " onclick="agregarProductoCarrito()">Añadir
                                            Producto a venta <i class="fa-solid fa-cart-plus"></i> </button>

                                    </div>

                                    {{-- Historial de precios del producto para este cliente --}}
                                    <div id="historialPreciosPanel" class="d-none mt-3">
                                        <h5 class="mb-2 text-dark"><i class="fa fa-history text-info"></i> Últimas 5 ventas a este cliente</h5>
                                        <div id="historialPreciosCuerpo"><p class="text-muted small">Cargando...</p></div>
                                    </div>

                                </div>

                            </div>

                            <hr>

                            <div class="hide-container">
                                <p>Nota:El campo "Unidad" describe la unidad de medida para la venta del producto -
                                    seguido del numero de unidades a restar del inventario</p>
                                <div class="row no-gutters ">

                                    <div class="form-group col-12 col-md-2 col-lg-2 col-xl-2">
                                        <div class="d-flex">



                                            <div style="width:100%">
                                                <label class="sr-only">Nombre del
                                                    producto</label>
                                                <input type="text" placeholder="Nombre del producto"
                                                    class="form-control" pattern="[A-Z]{1}" disabled>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-6 col-md-1 col-lg- col-xl-1">
                                        <label class="sr-only">Bodega</label>
                                        <input type="number" placeholder="Bodega" class="form-control"
                                            autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-6 col-md-2 col-lg- col-xl-1">
                                        <label class="sr-only">Precio</label>
                                        <input type="number" placeholder="Precio Unidad" class="form-control"
                                            min="1" autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-4 col-md-1 col-lg-1 col-xl-1">
                                        <label class="sr-only">cantidad</label>
                                        <input type="text" placeholder="Cantidad" class="form-control"
                                            min="1" autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-4 col-md-1 col-lg-1 col-xl-1 ">

                                        <label class="sr-only">Unidad</label>
                                        <input type="text" placeholder="Unidad " class="form-control"
                                            min="1" autocomplete="off" disabled>




                                    </div>
                                    {{-- <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                        <label class="sr-only">Seccion</label>
                                        <input type="text" placeholder="Seccion" class="form-control"
                                            min="1" autocomplete="off" disabled>
                                    </div> --}}

                                    <div class="form-group col-4 col-md-1 col-lg-2 col-xl-2">
                                        <label class="sr-only">Sub Total</label>
                                        <input type="number" placeholder="Sub total del producto"
                                            class="form-control" min="1" autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-4 col-md-1 col-lg-2 col-xl-2">
                                        <label class="sr-only">ISV</label>
                                        <input type="number" placeholder="ISV" class="form-control" min="1"
                                            autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-4 col-md-1 col-lg-2 col-xl-2">
                                        <label class="sr-only">Total</label>
                                        <input type="number" placeholder="Total del producto" class="form-control"
                                            min="1" disabled autocomplete="off">
                                    </div>

                                </div>



                            </div>

                            <div id="divProductos">
                                {!! $htmlProductos !!}
                            </div>

                            <hr>

                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="descuentoMostrar">Descuento L.<span
                                            class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="text" placeholder="Descuento aplicado" id="descuentoMostrar"
                                        name="descuentoMostrar" class="form-control"
                                        value="{{ $cotizacion->monto_descuento }}" data-parsley-required
                                        autocomplete="off" readonly>

                                    <input type="hidden" value="{{ $cotizacion->monto_descuento }}"
                                        id="porDescuentoCalculado" name="porDescuentoCalculado">
                                </div>
                            </div>
                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="subTotalGeneralMostrar">Sub Total L.<span
                                            class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="text" placeholder="Sub total " id="subTotalGeneralMostrar"
                                        value="{{ $cotizacion->sub_total }}" name="subTotalGeneralMostrar"
                                        class="form-control" data-parsley-required autocomplete="off" readonly>

                                    <input id="subTotalGeneral" name="subTotalGeneral" type="hidden"
                                        value="{{ $cotizacion->sub_total }}" required>
                                </div>
                            </div>

                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="subTotalGeneralGrabadoMostrar">Sub Total
                                        Grabado L.<span class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="text" placeholder="Sub total " id="subTotalGeneralGrabadoMostrar"
                                        name="subTotalGeneralGrabadoMostrar" class="form-control"
                                        data-parsley-required autocomplete="off"
                                        value="{{ $cotizacion->sub_total_grabado }}" readonly>

                                    <input id="subTotalGeneralGrabado" name="subTotalGeneralGrabado" type="hidden"
                                        value="{{ $cotizacion->sub_total_grabado }}" required>
                                </div>
                            </div>

                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="subTotalGeneralExcentoMostrar">Sub Total
                                        Excento L.<span class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="text" placeholder="Sub total " id="subTotalGeneralExcentoMostrar"
                                        name="subTotalGeneralExcentoMostrar" class="form-control"
                                        data-parsley-required autocomplete="off"
                                        value="{{ $cotizacion->sub_total_excento }}" readonly>

                                    <input id="subTotalGeneralExcento" name="subTotalGeneralExcento" type="hidden"
                                        value="{{ $cotizacion->sub_total_excento }}" required>
                                </div>
                            </div>

                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="isvGeneralMostrar">ISV L.<span
                                            class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="text" placeholder="ISV " id="isvGeneralMostrar"
                                        name="isvGeneralMostrar" class="form-control" value="{{ $cotizacion->isv }}"
                                        data-parsley-required autocomplete="off" readonly>
                                    <input id="isvGeneral" name="isvGeneral" type="hidden"
                                        value="{{ $cotizacion->isv }}" required>
                                </div>
                            </div>

                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="totalGeneralMostrar">Total L.<span
                                            class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="text" placeholder="Total  " id="totalGeneralMostrar"
                                        name="totalGeneralMostrar" class="form-control" data-parsley-required
                                        autocomplete="off" value="{{ $cotizacion->total }}" readonly>

                                    <input id="totalGeneral" name="totalGeneral" type="hidden"
                                        value="{{ $cotizacion->total }}" required>
                                </div>
                            </div>



                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <button id="guardar_cotizacion_btn"
                                        class="btn btn-sm btn-primary float-left m-t-n-xs"><strong>
                                            Realizar Factura</strong></button>
                                </div>
                            </div>


                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            var numeroInputs = {{ $cotizacion->numeroInputs }};
            var arregloIdInputsTemporal = @json($cotizacion->arregloIdInputs);
            var diasCredito = {{$cotizacion->dias_credito}}


        </script>
        <script>var public_path = "{{ asset('catalogo/') }}";</script>
        <script src="{{ asset('js/js_proyecto/cotizaciones/facturar-cotizacion-gobierno.js') }}"></script>
    @endpush
</div>
