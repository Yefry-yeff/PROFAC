<div>
    @push('styles')
        <style>
            /* #divProductos  input {
                                    font-size: 0.8rem;


                                  } */


            .img-size {
                /*width: 10rem*/
                width: 100%;
                height: 20rem;
                margin: 0 auto;
            }

            @media (min-width: 670px) and (max-width:767px) {
                .img-size {
                    /*width: 10rem*/
                    width: 85%;
                    height: 20rem;
                    margin: 0 auto;
                }
            }

            @media (min-width: 768px) and (max-width:960px) {
                .img-size {
                    /*width: 10rem*/
                    width: 75%;
                    height: 12rem;
                    margin: 0 auto;
                    background-color: blue
                }

            }

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
        </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Facturar Cotización</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">

                    @if ($cotizacion->tipo_venta_id == 1)
                        <a>Cliente B</a>
                    @elseif($cotizacion->tipo_venta_id == 2)
                        <a>Cliente A</a>
                    @else
                        <a>Cliente Exonerado</a>
                    @endif
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


                            <input type="hidden" id="tipo_venta_id" name="tipo_venta_id"
                                value="{{ $cotizacion->tipo_venta_id }}">
                            <input type="hidden" id="restriccion" name="restriccion" value="1">
                            <input name="idComprobante" id="idComprobante" type="hidden" value="">


                            <div class="row  mt-4 mb-4">
                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label for="vendedor">Seleccionar Vendedor:<span class="text-danger">*</span>
                                    </label>
                                    <select name="vendedor" id="vendedor" class="form-group form-control" required>
                                        <option value="" selected disabled>--Seleccionar un vendedor--</option>
                                    </select>

                                </div>
                            </div>



                            <div class="row mt-4">
                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label for="seleccionarCliente" class="col-form-label focus-label">Seleccionar
                                        Cliente:<span class="text-danger">*</span> </label>
                                    <select id="seleccionarCliente" name="seleccionarCliente"
                                        class="form-group form-control" style="" data-parsley-required
                                        onchange="obtenerDatosCliente()">
                                        <option value="{{ $cotizacion->cliente_id }}" selected>
                                            {{ $cotizacion->nombre_cliente }}</option>
                                    </select>
                                </div>

                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label class="col-form-label focus-label">Nombre del cliente:<span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" required type="text" id="nombre_cliente_ventas"
                                        name="nombre_cliente_ventas" value="{{ $cotizacion->nombre_cliente }}"
                                        data-parsley-required readonly>

                                </div>

                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label for="ordenCompra" class="col-form-label focus-label">Seleccionar un número de
                                        orden de compra:</label>
                                    <select class="form-group form-control " name="ordenCompra" id="ordenCompra">
                                        <option value="" selected disabled>--Seleccionar un número de compra--
                                        </option>

                                    </select>
                                </div>


                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label class="col-form-label focus-label">RTN:<span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="rtn_ventas" name="rtn_ventas"
                                        value="{{ $cotizacion->RTN }}" readonly>

                                </div>


                                    <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                        <div class="form-group">
                                            <label for="porDescuento" class="col-form-label focus-label">Descuento aplicado
                                                %
                                                :<span class="text-danger">*</span></label>
                                            <input class="form-control" type="number" min="0" max="15"
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
                                        pago:<span class="text-danger">*</span></label>
                                    <select class="form-group form-control " name="tipoPagoVenta" id="tipoPagoVenta"
                                        data-parsley-required onchange="validarFechaPago()">
                                    </select>
                                </div>

                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">

                                        <label for="fecha_emision" class="col-form-label focus-label">Fecha de emisión
                                            :<span class="text-danger">*</span></label>
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
                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 ">


                                    <label for="seleccionarProducto" class="col-form-label focus-label">Seleccionar
                                        Producto:<span class="text-danger">*</span></label>
                                    <select id="seleccionarProducto" name="seleccionarProducto"
                                        class="form-group form-control" style="" onchange="obtenerImagenes()">
                                        <option value="" selected disabled>--Seleccione un producto--</option>
                                    </select>




                                </div>

                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 ">

                                    <label for="bodega" class="col-form-label focus-label">Seleccionar
                                        bodega:</label>
                                    <select id="bodega" name="bodega" class="form-group form-control"
                                        style="" onchange="prueba()" disabled>
                                        <option value="" selected disabled>--Seleccione un producto--</option>
                                    </select>


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

                                </div>

                            </div>

                            <hr>

                            <div class="hide-container">
                                <p>Nota:El campo "Unidad" describe la unidad de medida para la venta del producto -
                                    seguido del numero de unidades a restar del inventario</p>
                                <div class="row no-gutters ">

                                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                        <div class="d-flex">



                                            <div style="width:100%">
                                                <label class="sr-only">Nombre del
                                                    producto</label>
                                                <input type="text" placeholder="Nombre del producto"
                                                    class="form-control" pattern="[A-Z]{1}" disabled>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg- col-xl-1">
                                        <label class="sr-only">Bodega</label>
                                        <input type="number" placeholder="Bodega" class="form-control"
                                            autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg- col-xl-1">
                                        <label class="sr-only">Precio</label>
                                        <input type="number" placeholder="Precio Unidad" class="form-control"
                                            min="1" autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                        <label class="sr-only">cantidad</label>
                                        <input type="text" placeholder="Cantidad" class="form-control"
                                            min="1" autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1 ">

                                        <label class="sr-only">Unidad</label>
                                        <input type="text" placeholder="Unidad " class="form-control"
                                            min="1" autocomplete="off" disabled>




                                    </div>



                                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                        <label class="sr-only">Sub Total</label>
                                        <input type="number" placeholder="Sub total del producto"
                                            class="form-control" min="1" autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                        <label class="sr-only">ISV</label>
                                        <input type="number" placeholder="ISV" class="form-control" min="1"
                                            autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
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
                                    <button id="guardar_cotizacion_btn" name="guardar_cotizacion_btn"
                                        class="btn  btn-primary float-left m-t-n-xs"><strong>
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
            var arregloIdInputs = [];
            var numeroInputs = {{ $cotizacion->numeroInputs }};
            var arregloIdInputsTemporal = @json($cotizacion->arregloIdInputs);
            var retencionEstado = false; // true  aplica retencion, false no aplica retencion;
            var urlGuardarVenta = "{{ $urlGuardarVenta }}";

            window.onload = obtenerTipoPago;
            var public_path = "{{ asset('catalogo/') }}";
            var diasCredito = {{$cotizacion->dias_credito}};



            const searchRegExp = /\"/g;
            arregloIdInputsTemporal = arregloIdInputsTemporal.replace(searchRegExp, '')
            arregloIdInputs = arregloIdInputsTemporal.split(",");

            for (let z = 0; z < arregloIdInputs.length; z++) {
                arregloIdInputs[z] = parseInt(arregloIdInputs[z]);

            }



            calcularTotalesInicioPagina();

function validarDescuento(){
                const numeroInput = document.getElementById('porDescuento');
                const mensajeError = document.getElementById('mensajeError');
                const numero = parseFloat(numeroInput.value);

                if (isNaN(numero) || numero < 0 || numero > 15) {
                    mensajeError.textContent = 'Este campo solo admite un valor entre 0 a 15';
                    numeroInput.value = '';
                } else {
                    mensajeError.textContent = '';
                }


            }

            $('#vendedor').select2({
                ajax: {
                    url: '/ventas/corporativo/vendedores',
                    data: function(params) {
                        var query = {
                            search: params.term,
                            type: 'public',
                            page: params.page || 1
                        }

                        // Query parameters will be ?search=[term]&type=public
                        return query;
                    }

                }
            });

            $('#seleccionarCliente').select2({
                ajax: {
                    url: '/cotizacion/clientes',
                    data: function(params) {
                        var query = {
                            search: params.term,
                            tipoCotizacion: {{ $cotizacion->tipo_venta_id }},
                            type: 'public',
                            page: params.page || 1
                        }

                        return query;
                    }
                }
            });

            $('#seleccionarProducto').select2({
                ajax: {
                    url: '/ventas/listar',
                    data: function(params) {
                        var query = {
                            search: params.term,
                            type: 'public',
                            page: params.page || 1
                        }



                        return query;
                    }
                }
            });

            function prueba() {

                var element = document.getElementById('botonAdd');
                element.classList.remove("d-none");

            }

            function obtenerBodegas(id) {

                document.getElementById('bodega').innerHTML = "<option  selected disabled>--Seleccione una bodega--</option>";
                let idProducto = id;
                $('#bodega').select2({
                    ajax: {
                        url: '/ventas/listar/bodegas/' + idProducto,
                        data: function(params) {
                            var query = {
                                search: params.term,
                                type: 'public',
                                page: params.page || 1,
                                idProducto: idProducto
                            }

                            return query;
                        }
                    }
                });

            }


            function obtenerTipoPago() {



                axios.get('/ventas/tipo/pago')
                    .then(response => {

                        let tipoDePago = response.data.tipos;
                        let numeroVenta = response.data.numeroVenta.numero;

                        let htmlPagos = '  <option value="" selected disabled >--Seleccione una opcion--</option>';

                        tipoDePago.forEach(element => {

                            htmlPagos += `
                            <option value="${element.id}" >${element.descripcion}</option>
                            `
                        });

                        document.getElementById('tipoPagoVenta').innerHTML = htmlPagos;



                    })
                    .catch(err => {
                        console.log(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error...',
                            text: "Ha ocurrido un error al obtener los tipos de pago"
                        })
                    })

            }

            function obtenerImagenes() {
                let id = document.getElementById('seleccionarProducto').value;

                document.getElementById("bodega").disabled = false;
                let htmlImagenes = '';
                axios.post('/producto/listar/imagenes', {
                        id: id,

                    })
                    .then(response => {

                        let imagenes = response.data.imagenes;

                        if (imagenes.length == 0) {


                            htmlImagenes += `
                            <div class="carousel-item active " >
                                <img  class="d-block  img-size" src="${public_path+'/'+'noimage.png'}" alt="noimage.png"  >
                            </div>`

                            document.getElementById('bloqueImagenes').innerHTML = htmlImagenes;

                            var element = document.getElementById('botonAdd');
                            element.classList.remove("d-none");

                        } else {
                            imagenes.forEach(element => {

                                if (element.contador == 1) {
                                    htmlImagenes += `
                            <div class="carousel-item active " >
                                <img class="d-block  img-size" src="${public_path+'/'+element.url_img}" alt="imagen ${element.contador}"  >
                            </div>`
                                } else {

                                    htmlImagenes += `
                            <div class="carousel-item  " >
                                <img class="d-block  img-size" src="${public_path+'/'+element.url_img}" alt="imagen ${element.contador}"  >
                            </div>`

                                }

                            });

                            document.getElementById('bloqueImagenes').innerHTML = htmlImagenes;


                        }

                        var element = document.getElementById('botonAdd');
                        element.classList.add("d-none");

                        let a = document.getElementById("detalleProducto");
                        let url = "/producto/detalle/" + id;
                        a.href = url;
                        a.classList.remove("d-none");

                        return;



                    })
                    .catch(err => {

                        console.log(err);

                    })

                obtenerBodegas(id);
            }

            function agregarProductoCarrito() {
                let idProducto = document.getElementById('seleccionarProducto').value;

                let data = $("#bodega").select2('data')[0];
                let bodega = data.bodegaSeccion;
                let idBodega = data.idBodega;
                let idSeccion = data.id


                axios.post('/ventas/datos/producto', {
                        idProducto: idProducto,

                    })
                    .then(response => {

                        let flag = false;
                        arregloIdInputs.forEach(idInpunt => {
                            let idProductoFila = document.getElementById("idProducto" + idInpunt).value;
                            let idSeccionFila = document.getElementById("idSeccion" + idInpunt).value;

                            if (idProducto == idProductoFila && idSeccion == idSeccionFila && !flag) {
                                flag = true;
                            }

                        })

                        if (flag) {
                            Swal.fire({

                                icon: 'warning',
                                title: 'Advertencia!',
                                html: `
                            <p class="text-left">
                                La sección de bodega y producto ha sido agregada anteriormente.<br><br>
                                Por favor verificar la sección de bodega y producto sea distinto a los ya existentes en la lista de venta.<br><br>
                                De ser necesario aumentar la cantidad de producto en la lista de productos seleccionados para la venta.
                            </p>`
                            })

                            return;
                        }

                        let producto = response.data.producto;

                        let arrayUnidades = response.data.unidades;


                        let ultimoElemento = arregloIdInputs[arregloIdInputs.length - 1];


                        numeroInputs = parseInt(ultimoElemento) + 1;


                        //     let arraySecciones  = response.data.secciones;
                        // htmlSelectSeccion ="<option selected disabled>--seccion--</option>";

                        // arraySecciones.forEach(seccion => {
                        //     htmlSelectSeccion += `<option values="${seccion.id}" >${seccion.descripcion}</option>`
                        // });

                        htmlSelectUnidades = ""
                        arrayUnidades.forEach(unidad => {
                            if (unidad.valor_defecto == 1) {
                                htmlSelectUnidades +=
                                    `<option selected value="${unidad.id}" data-id="${unidad.idUnidadVenta}">${unidad.nombre}</option>`;
                            } else {
                                htmlSelectUnidades +=
                                    `<option  value="${unidad.id}" data-id="${unidad.idUnidadVenta}">${unidad.nombre}</option>`;
                            }

                        });

                        /*SE QUITO min="${producto.precio_base}" DE LA LINEA 856*/

                        html = `
                        <div id='${numeroInputs}' class="row no-gutters">
                                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                                <div class="d-flex">

                                                    <button class="btn btn-danger" type="button" style="display: inline" onclick="eliminarInput(${numeroInputs})"><i
                                                            class="fa-regular fa-rectangle-xmark"></i>
                                                    </button>

                                                    <input id="idProducto${numeroInputs}" name="idProducto${numeroInputs}" type="hidden" value="${producto.id}">

                                                    <div style="width:100%">
                                                        <label for="nombre${numeroInputs}" class="sr-only">Nombre del producto</label>
                                                        <input type="text" placeholder="Nombre del producto" id="nombre${numeroInputs}"
                                                            name="nombre${numeroInputs}" class="form-control"
                                                            data-parsley-required "
                                                            autocomplete="off"
                                                            readonly
                                                            value='${producto.nombre}'

                                                            >
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                                <label for="" class="sr-only">cantidad</label>
                                                <input type="text" value="${bodega}" placeholder="bodega-seccion" id="bodega${numeroInputs}"
                                                    name="bodega${numeroInputs}" class="form-control"
                                                    autocomplete="off"  readonly  >
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                                <label for="precio${numeroInputs}" class="sr-only">Precio</label>
                                                <input type="number" placeholder="Precio Unidad" id="precio${numeroInputs}"
                                                    name="precio${numeroInputs}" value="${producto.precio_base}" class="form-control"  data-parsley-required step="any"
                                                    autocomplete="off"  onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                                <label for="cantidad${numeroInputs}" class="sr-only">cantidad</label>
                                                <input type="number" placeholder="Cantidad" id="cantidad${numeroInputs}"
                                                    name="cantidad${numeroInputs}" class="form-control" min="1" data-parsley-required
                                                    autocomplete="off" onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                                <label for="" class="sr-only">unidad</label>
                                                <select class="form-control" name="unidad${numeroInputs}" id="unidad${numeroInputs}"
                                                    data-parsley-required style="height:35.7px;"
                                                    onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                                                            ${htmlSelectUnidades}
                                                </select>


                                            </div>




                                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                                <label for="subTotalMostrar${numeroInputs}" class="sr-only">Sub Total</label>
                                                <input type="text" placeholder="Sub total producto" id="subTotalMostrar${numeroInputs}"
                                                    name="subTotalMostrar${numeroInputs}" class="form-control"
                                                    autocomplete="off"
                                                    readonly >

                                                <input id="subTotal${numeroInputs}" name="subTotal${numeroInputs}" type="hidden" value="" required>
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                                <label for="isvProductoMostrar${numeroInputs}" class="sr-only">ISV</label>
                                                <input type="text" placeholder="ISV" id="isvProductoMostrar${numeroInputs}"
                                                    name="isvProductoMostrar${numeroInputs}" class="form-control"
                                                    autocomplete="off"
                                                    readonly >

                                                    <input id="isvProducto${numeroInputs}" name="isvProducto${numeroInputs}" type="hidden" value="" required>
                                                    <input type="hidden" id="acumuladoDescuento${numeroInputs}" name="acumuladoDescuento${numeroInputs}" >
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                                <label for="totalMostrar${numeroInputs}" class="sr-only">Total</label>
                                                <input type="text" placeholder="Total del producto" id="totalMostrar${numeroInputs}"
                                                    name="totalMostrar${numeroInputs}" class="form-control"
                                                    autocomplete="off"
                                                    readonly >

                                                    <input id="total${numeroInputs}" name="total${numeroInputs}" type="hidden" value="" required>


                                            </div>

                                            <input id="idBodega${numeroInputs}" name="idBodega${numeroInputs}" type="hidden" value="${idBodega}">
                                            <input id="idSeccion${numeroInputs}" name="idSeccion${numeroInputs}" type="hidden" value="${idSeccion}">
                                            <input id="restaInventario${numeroInputs}" name="restaInventario${numeroInputs}" type="hidden" value="">
                                            <input id="isv${numeroInputs}" name="isv${numeroInputs}" type="hidden" value="${producto.isv}">



                        </div>
                        `;


                        arregloIdInputs.push(numeroInputs);
                        document.getElementById('divProductos').insertAdjacentHTML('beforeend', html);

                        return;

                    })
                    .catch(err => {

                        console.error(err);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: "Ha ocurrido un error al agregar el producto a la compra."
                        })
                    })
            }

            function eliminarInput(id) {
                const element = document.getElementById(id);
                element.remove();


                var myIndex = arregloIdInputs.indexOf(id);
                if (myIndex !== -1) {
                    arregloIdInputs.splice(myIndex, 1);
                    this.totalesGenerales();
                }



            }

            function calcularTotalesInicioPagina() {

                let arrayInputs = this.arregloIdInputs;


                let valorInputPrecio = 0;
                let valorInputCantidad = 0;
                let valorSelectUnidad = 0;
                let isvProducto = 0;

                let subTotal = 0;
                let isv = 0;
                let total = 0;
                var descuentoCalculado = 0;

                arrayInputs.forEach(id => {
                    // calcularTotales(idPrecio, idCantidad, isvProducto, idUnidad, id)
                    valorInputPrecio = document.getElementById('precio' + id).value;
                    valorInputCantidad = document.getElementById('cantidad' + id).value;
                    valorSelectUnidad = document.getElementById('unidad' + id).value;
                    isvProducto = document.getElementById("isv" + id).value;

                    if (valorInputPrecio && valorInputCantidad) {

                        //subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                        //isv = subTotal * (isvProducto / 100);
                        // total = subTotal + subTotal * (isvProducto / 100);

                        let descuento = document.getElementById('porDescuento').value;


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
                        document.getElementById('totalMostrar' + id).value = new Intl.NumberFormat('es-HN', {
                            style: 'currency',
                            currency: 'HNL',
                            minimumFractionDigits: 2,
                        }).format(total)

                        document.getElementById('subTotal' + id).value = subTotal.toFixed(2);
                        document.getElementById('subTotalMostrar' + id).value = new Intl.NumberFormat('es-HN', {
                            style: 'currency',
                            currency: 'HNL',
                            minimumFractionDigits: 2,
                        }).format(subTotal)


                        document.getElementById('isvProducto' + id).value = isv.toFixed(2);
                        document.getElementById('isvProductoMostrar' + id).value = new Intl.NumberFormat(
                            'es-HN', {
                                style: 'currency',
                                currency: 'HNL',
                                minimumFractionDigits: 2,
                            }).format(isv)

                    }

                });



                this.totalesGenerales();
                return 0;


            }

            function calcularTotales(idPrecio, idCantidad, isvProducto, idUnidad, id, idRestaInventario) {

                let valorInputPrecio = Number(idPrecio.value).toFixed(2);
                let valorInputCantidad = idCantidad.value;
                let valorSelectUnidad = idUnidad.value;

                let subTotal = 0;
                let isv = 0;
                let total = 0;

                let descuentoCalculado = 0;


                if (valorInputPrecio && valorInputCantidad) {

                    let descuento = document.getElementById('porDescuento').value;


                    if (descuento >= 0) {
                        subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                        descuentoCalculado = subTotal * (descuento / 100);
                        subTotal = subTotal - descuentoCalculado;
                        isv = subTotal * (isvProducto / 100);
                        total = subTotal + (subTotal * (isvProducto / 100));
                    } else {
                        descuentoCalculado = 0
                        subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                        isv = subTotal * (isvProducto / 100);
                        total = subTotal + subTotal * (isvProducto / 100);
                    }

                    document.getElementById('acumuladoDescuento' + id).value = descuentoCalculado.toFixed(2);

                    document.getElementById('total' + id).value = total.toFixed(2);
                    document.getElementById('totalMostrar' + id).value = new Intl.NumberFormat('es-HN', {
                        style: 'currency',
                        currency: 'HNL',
                        minimumFractionDigits: 2,
                    }).format(total)

                    document.getElementById('subTotal' + id).value = subTotal.toFixed(2);
                    document.getElementById('subTotalMostrar' + id).value = new Intl.NumberFormat('es-HN', {
                        style: 'currency',
                        currency: 'HNL',
                        minimumFractionDigits: 2,
                    }).format(subTotal)


                    document.getElementById('isvProducto' + id).value = isv.toFixed(2);
                    document.getElementById('isvProductoMostrar' + id).value = new Intl.NumberFormat('es-HN', {
                        style: 'currency',
                        currency: 'HNL',
                        minimumFractionDigits: 2,
                    }).format(isv)


                    idRestaInventario.value = valorInputCantidad * valorSelectUnidad;
                    this.totalesGenerales();




                }

                idPrecio.value = valorInputPrecio;
                return 0;



            }

            function totalesGenerales() {

                //console.log(arregloIdInputs);

                if (numeroInputs == 0) {
                    return;
                }



                let totalGeneralValor = new Number(0);
                let totalISV = new Number(0);
                let subTotalGeneralGrabadoValor = new Number(0);
                let subTotalGeneralExcentoValor = new Number(0);
                let subTotalGeneral = new Number(0);
                let subTotalFila = 0;
                let isvFila = 0;
                let acumularDescuento = new Number(0);

                for (let i = 0; i < arregloIdInputs.length; i++) {

                    subTotalFila = new Number(document.getElementById('subTotal' + arregloIdInputs[i]).value);
                    isvFila = new Number(document.getElementById('isvProducto' + arregloIdInputs[i]).value);

                    if (isvFila == 0) {
                        subTotalGeneralExcentoValor += new Number(document.getElementById('subTotal' + arregloIdInputs[i])
                            .value);
                    } else if (subTotalFila > 0) {
                        subTotalGeneralGrabadoValor += new Number(document.getElementById('subTotal' + arregloIdInputs[i])
                            .value);
                    }

                    subTotalGeneral += new Number(document.getElementById('subTotal' + arregloIdInputs[i]).value);


                    totalISV += new Number(document.getElementById('isvProducto' + arregloIdInputs[i]).value);
                    totalGeneralValor += new Number(document.getElementById('total' + arregloIdInputs[i]).value);

                    acumularDescuento += new Number(document.getElementById('acumuladoDescuento' + arregloIdInputs[i]).value);

                }



                document.getElementById('porDescuentoCalculado').value = acumularDescuento.toFixed(2);

                document.getElementById('descuentoMostrar').value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(acumularDescuento)

                document.getElementById('subTotalGeneral').value = subTotalGeneral.toFixed(2);
                document.getElementById('subTotalGeneralMostrar').value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(subTotalGeneral)

                document.getElementById('subTotalGeneralGrabado').value = subTotalGeneralGrabadoValor.toFixed(2);
                document.getElementById('subTotalGeneralGrabadoMostrar').value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(subTotalGeneralGrabadoValor)

                document.getElementById('subTotalGeneralExcento').value = subTotalGeneralExcentoValor.toFixed(2);
                document.getElementById('subTotalGeneralExcentoMostrar').value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(subTotalGeneralExcentoValor)

                document.getElementById('isvGeneral').value = totalISV.toFixed(2);
                document.getElementById('isvGeneralMostrar').value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(totalISV)

                document.getElementById('totalGeneral').value = totalGeneralValor.toFixed(2);
                document.getElementById('totalGeneralMostrar').value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(totalGeneralValor)





                return 0;


            }

            function validarFechaPago() {

                let tipoPago;

                tipoPago = document.getElementById('tipoPagoVenta').value;

                if (tipoPago == 2) {

                    // document.getElementById('fecha_vencimiento').value = "empty";
                    document.getElementById('fecha_vencimiento').readOnly = false;
                    this.sumarDiasCredito();

                } else {
                    document.getElementById('fecha_vencimiento').value = "{{ date('Y-m-d') }}";

                    document.getElementById('fecha_vencimiento').readOnly = true;

                }

                return 0;


            }

            function obtenerDatosCliente() {
                let idCliente = document.getElementById("seleccionarCliente").value;
                axios.post("/ventas/datos/cliente", {
                        id: idCliente
                    })
                    .then(
                        response => {

                            let data = response.data.datos;

                            if (data.id == 1) {
                                document.getElementById("nombre_cliente_ventas").readOnly = false;
                                document.getElementById("nombre_cliente_ventas").value = '';

                                document.getElementById("rtn_ventas").readOnly = false;
                                document.getElementById("rtn_ventas").value = '';
                                let selectBox = document.getElementById("tipoPagoVenta");
                                selectBox.remove(2);

                            } else {
                                document.getElementById("nombre_cliente_ventas").readOnly = true;
                                document.getElementById("rtn_ventas").readOnly = true;

                                document.getElementById("nombre_cliente_ventas").value = data.nombre;
                                document.getElementById("rtn_ventas").value = data.rtn;
                                obtenerTipoPago();
                                diasCredito = data.dias_credito;
                            }



                        }
                    )
                    .catch(err => {

                        console.log(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error...',
                            text: "Ha ocurrido un error al obtener los datos del cliente"
                        })


                    })

            }


            $(document).on('submit', '#crear_venta',
                function(event) {
                    event.preventDefault();
                    guardarVenta();
                });

            function guardarVenta() {

                document.getElementById("guardar_cotizacion_btn").disabled = true;

                var data = new FormData($('#crear_venta').get(0));

                let longitudArreglo = arregloIdInputs.length;
                for (var i = 0; i < longitudArreglo; i++) {


                    let name = "unidad" + arregloIdInputs[i];
                    let nameForm = "idUnidadVenta" + arregloIdInputs[i];

                    let e = document.getElementById(name);

                    let idUnidadVenta = e.options[e.selectedIndex].getAttribute("data-id");


                    data.append(nameForm, idUnidadVenta)

                }
                data.append("numeroInputs", numeroInputs);

                let text = arregloIdInputs.toString();
                data.append("arregloIdInputs", text);
                const formDataObj = {};
                data.forEach((value, key) => (formDataObj[key] = value));

                const options = {
                    headers: {
                        "content-type": "application/json"
                    }
                }



                axios.post(urlGuardarVenta, formDataObj, options)
                    .then(response => {
                        let data = response.data;



                        if (data.idFactura == 0) {


                            Swal.fire({
                                icon: data.icon,
                                title: data.title,
                                html: data.text,
                            })
                            document.getElementById("guardar_cotizacion_btn").disabled = false;
                            return;

                        }

                        Swal.fire({
                            confirmButtonText: 'Cerrar',
                            confirmButtonColor: '#5A6268',
                            icon: data.icon,
                            title: data.title,
                            html: data.text
                        })


                        document.getElementById('bloqueImagenes').innerHTML = '';
                        document.getElementById('divProductos').innerHTML = '';

                        document.getElementById("crear_venta").reset();
                        $('#crear_venta').parsley().reset();

                        var element = document.getElementById('detalleProducto');
                        element.classList.add("d-none");
                        element.href = "";

                        document.getElementById("seleccionarCliente").innerHTML =
                            '<option value="" selected disabled>--Seleccionar un cliente--</option>';

                        document.getElementById('seleccionarProducto').innerHTML =
                            '<option value="" selected disabled>--Seleccione un producto--</option>';
                        document.getElementById('bodega').innerHTML =
                            '<option value="" selected disabled>--Seleccione un producto--</option>';
                        document.getElementById("bodega").disabled = true;


                        document.getElementById("subTotalGeneralMostrar").value = "";
                        document.getElementById("subTotalGeneral").value = "";
                        document.getElementById("subTotalGeneralGrabadoMostrar").value = "";
                        document.getElementById("subTotalGeneralGrabado").value = "";
                        document.getElementById("subTotalGeneralExcentoMostrar").value = "";
                        document.getElementById("subTotalGeneralExcento").value = "";
                        document.getElementById("isvGeneralMostrar").value = "";
                        document.getElementById("isvGeneral").value = "";
                        document.getElementById("totalGeneralMostrar").value = "";
                        document.getElementById("totalGeneral").value = "";

                        let element2 = document.getElementById('detalleProducto');
                        element2.classList.add("d-none");


                        arregloIdInputs = [];
                        numeroInputs = 0;
                        retencionEstado = false;



                        document.getElementById("guardar_cotizacion_btn").disabled = false;

                    })
                    .catch(err => {
                        document.getElementById("guardar_cotizacion_btn").disabled = false;
                        let data = err.response.data;
                        console.log(err);
                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text
                        })
                    })
            }

            function sumarDiasCredito() {
                tipoPago = document.getElementById('tipoPagoVenta').value;

                if (tipoPago == 2) {

                    let fechaEmision = document.getElementById("fecha_emision").value;
                    let date = new Date(fechaEmision);
                    date.setDate(date.getDate() + diasCredito);
                    let suma = date.toISOString().split('T')[0];
                    // console.log( diasCredito);

                    document.getElementById("fecha_vencimiento").value = suma;

                }
            }
            let idCliente = document.getElementById('seleccionarCliente').value;
            $('#ordenCompra').select2({
                ajax: {
                    url: '/ventas/numero/orden',
                    data: function(params) {
                        var query = {
                            idCliente: idCliente,
                            search: params.term,
                            type: 'public',
                            page: params.page || 1
                        }

                        // Query parameters will be ?search=[term]&type=public

                        return query;
                    }
                }
            });
        </script>
    @endpush
</div>
<?php
date_default_timezone_set('America/Tegucigalpa');
$act_fecha = date('Y-m-d');
$act_hora = date('H:i:s');
$mes = date('m');
$year = date('Y');
$datetim = $act_fecha . ' ' . $act_hora;
?>
<script>
    function mostrarHora() {
        var fecha = new Date(); // Obtener la fecha y hora actual
        var hora = fecha.getHours();
        var minutos = fecha.getMinutes();
        var segundos = fecha.getSeconds();

        // A単adir un 0 delante si los minutos o segundos son menores a 10
        minutos = minutos < 10 ? "0" + minutos : minutos;
        segundos = segundos < 10 ? "0" + segundos : segundos;

        // Mostrar la hora actual en el elemento con el id "reloj"
        document.getElementById("reloj").innerHTML = hora + ":" + minutos + ":" + segundos;
    }
    // Actualizar el reloj cada segundo
    setInterval(mostrarHora, 1000);
</script>
<div class="float-right">
    <?php echo "$act_fecha"; ?> <strong id="reloj"></strong>
</div>
<div>
    <strong>Copyright</strong> Distribuciones Valencia &copy; <?php echo "$year"; ?>
</div>
<p id="reloj"></p>
