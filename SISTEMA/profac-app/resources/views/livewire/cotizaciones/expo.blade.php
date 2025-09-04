<div>
    @push('styles')
        <style>


            /* Estilos opcionales para el interruptor */
            .switch {
                position: relative;
                display: inline-block;
                width: 60px;
                height: 24px;
            }

            .switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
            }

            .slider:before {
                position: absolute;
                content: "";
                height: 16px;
                width: 26px;
                border-radius: 100%;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: .4s;
            }

            input:checked + .slider {
                background-color: #d1641b;
            }

            input:checked + .slider:before {
                transform: translateX(26px);
            }




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

            /* Estilos para el escáner de códigos de barras */
            #cameraContainer {
                position: relative;
                display: flex;
                justify-content: center;
                align-items: center;
                background-color: #f8f9fa;
                border-radius: 8px;
                overflow: hidden;
                max-width: 100%;
                margin: 0 auto;
            }

            #videoElement {
                max-width: 100%;
                height: 300px;
                width: 100%;
                object-fit: cover;
            }

            .scanner-overlay {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                border: 3px solid #ff0000;
                border-radius: 8px;
                width: 280px;
                height: 120px;
                background: transparent;
                box-shadow: 
                    0 0 0 2000px rgba(0, 0, 0, 0.4),
                    inset 0 0 0 2px rgba(255, 255, 255, 0.2);
                pointer-events: none;
                animation: scannerPulse 2s infinite ease-in-out;
                z-index: 10;
            }

            .scanner-overlay::before {
                content: 'Enfoque el código aquí';
                position: absolute;
                top: -35px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 5px 12px;
                border-radius: 15px;
                font-size: 0.8em;
                white-space: nowrap;
            }

            .scanner-overlay::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 2px;
                height: 60%;
                background: #ff0000;
                animation: scanLine 1.5s infinite ease-in-out;
            }

            @keyframes scannerPulse {
                0%, 100% {
                    border-color: #ff0000;
                    box-shadow: 
                        0 0 0 2000px rgba(0, 0, 0, 0.4),
                        inset 0 0 0 2px rgba(255, 255, 255, 0.2),
                        0 0 20px rgba(255, 0, 0, 0.5);
                }
                50% {
                    border-color: #00ff00;
                    box-shadow: 
                        0 0 0 2000px rgba(0, 0, 0, 0.5),
                        inset 0 0 0 2px rgba(255, 255, 255, 0.3),
                        0 0 25px rgba(0, 255, 0, 0.7);
                }
            }

            @keyframes scanLine {
                0% {
                    opacity: 1;
                    transform: translate(-50%, -50%) scaleY(1);
                }
                50% {
                    opacity: 0.6;
                    transform: translate(-50%, -50%) scaleY(0.8);
                }
                100% {
                    opacity: 1;
                    transform: translate(-50%, -50%) scaleY(1);
                }
            }

            /* Mejoras para el contenedor de la cámara */
            .camera-card {
                border: 2px solid #007bff;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
            }
        </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            @if ($tipoCotizacion == 3)
                <h2>Expo Feria 2024</h2>
            @else
                <h2>Cotización</h2>
            @endif
            <ol class="breadcrumb">
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
                        <h3>Datos de cotización <i class="fa-solid fa-cart-shopping"></i></h3>
                    </div>
                    <div class="ibox-content">
                        <form onkeydown="return event.key != 'Enter';" autocomplete="off" id="crear_venta"
                            name="crear_venta" data-parsley-validate>


                            <input type="hidden" id="tipo_venta_id" name="tipo_venta_id" value="{{ $tipoCotizacion }}">



                            <div class="mt-4 row">
                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label for="seleccionarCliente" class="col-form-label focus-label">Seleccionar
                                        Cliente:<span class="text-danger">*</span> </label>
                                    <select id="seleccionarCliente" name="seleccionarCliente"
                                        class="form-group form-control" style="" data-parsley-required
                                        onchange="obtenerDatosCliente()">
                                        <option value="" selected disabled>--Seleccionar un cliente--</option>
                                    </select>
                                </div>

                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label class="col-form-label focus-label">Nombre del cliente:<span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" required type="text" id="nombre_cliente_ventas"
                                        name="nombre_cliente_ventas" data-parsley-required readonly>

                                </div>

                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label class="col-form-label focus-label">RTN:<span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="rtn_ventas" name="rtn_ventas"
                                        readonly>

                                </div>





                            </div>

                            <div class="mt-4 row">
                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4" style="display: none;">
                                    <label for="tipoPagoVenta" class="col-form-label focus-label">Seleccionar tipo de
                                        pago:<span class="text-danger">*</span></label>
                                    <select class="form-group form-control " name="tipoPagoVenta" id="tipoPagoVenta"
                                        onchange="validarFechaPago()">
                                    </select>
                                </div>
                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label for="vendedor">Vendedor:<span class="text-danger"></span> </label>
                                    <select name="vendedor" id="vendedor" class="form-group form-control">

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

                                        <label for="porDescuento" class="col-form-label focus-label">Descuento aplicado %
                                            :<span class="text-danger">*</span></label>
                                        <input class="form-control" oninput="validarDescuento()" onchange="calcularTotalesInicioPagina()" type="number" min="0" max="25" value="0" minlength="1" maxlength="2" id="porDescuento" name="porDescuento"  >
                                        <p id="mensajeError" style="color: red;"></p>


                                    </div>
                                </div>

                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4" style="display: none;">
                                    <div class="form-group">
                                        <label for="fecha_vencimiento"
                                            class="col-form-label focus-label text-warning">Fecha de vencimiento:
                                        </label>
                                        <input class="form-control" type="date" id="fecha_vencimiento"
                                            name="fecha_vencimiento" value="" min="{{ date('Y-m-d') }}" readonly>
                                    </div>
                                </div>


                            </div>

                            <div class="row">



                            </div>

                            <div class="mt-4 row">
                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 ">

                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <label for="seleccionarProducto" class="col-form-label focus-label"> <label style="display: none" class="switch"> <input type="checkbox" id="mySwitch"><span class="slider"></span></label> Seleccionar
                                            Producto :<span class="text-danger">*</span></label>
                                        <select id="seleccionarProducto" name="seleccionarProducto"
                                            class="form-group form-control" style="" onchange="obtenerImagenes()">
                                            <option value="" selected disabled>--Seleccione un producto--</option>
                                        </select>
                                    </div>

                                    <!-- Sección para lectura de código de barras con cámara -->
                                    <div class="mt-3 col-12">
                                        <div class="card camera-card">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="mb-0"><i class="fa fa-camera"></i> Lectura de Código de Barras</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-12 text-center">
                                                        <button type="button" id="btnStartCamera" class="btn btn-success btn-lg me-3">
                                                            <i class="fa fa-camera"></i> Activar Escáner
                                                        </button>
                                                        <button type="button" id="btnStopCamera" class="btn btn-danger btn-lg" style="display: none;">
                                                            <i class="fa fa-stop-circle"></i> Detener Escáner
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <!-- Video preview de la cámara -->
                                                        <div id="cameraContainer" class="camera-container" style="display: none;">
                                                            <video id="videoElement" playsinline muted></video>
                                                            <canvas id="canvasElement" style="display: none;"></canvas>
                                                        </div>
                                                        <!-- Mensaje de estado -->
                                                        <div id="scannerStatus" class="alert alert-info" style="display: none;">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fa fa-info-circle me-2"></i> 
                                                                <span id="statusMessage">Preparando escáner...</span>
                                                                <div class="spinner-border spinner-border-sm ms-auto" role="status" aria-hidden="true"></div>
                                                            </div>
                                                        </div>
                                                        <!-- Resultado del escaneo -->
                                                        <div id="scanResult" class="alert alert-success" style="display: none;">
                                                            <strong><i class="fa fa-check-circle"></i> Código escaneado:</strong> 
                                                            <span id="barcodeResult" class="fw-bold"></span>
                                                        </div>
                                                        <!-- Mensaje de ayuda -->
                                                        <div class="mt-3 alert alert-light" style="font-size: 0.9em;">
                                                            <i class="fa fa-lightbulb text-warning"></i> 
                                                            <strong>Consejos:</strong>
                                                            <ul class="mb-0 mt-2">
                                                                <li>Mantenga el código de barras dentro del recuadro rojo</li>
                                                                <li>Asegúrese de tener buena iluminación</li>
                                                                <li>Mantenga el dispositivo estable</li>
                                                                <li>El escaneo se realizará automáticamente</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>


                            </div>

                            <div class="row">


                                <div class="mt-4 col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                    <div class="text-center">
                                        <a id="detalleProducto" href=""
                                            class="font-bold h3 d-none text-success" style="" target="_blank">
                                            <i class="fa-solid fa-circle-info"></i> Ver Detalles De Producto </a>
                                    </div>


                                    <div id="carouselProducto" class="mt-2 carousel slide" data-ride="carousel">
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
                                        class="my-4 text-center col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 d-none">
                                        <button type="button" class="p-3 btn-rounded btn btn-success"
                                            style="font-weight: 900; " onclick="agregarProductoCarrito()">Añadir
                                            Producto a venta <i class="fa-solid fa-cart-plus"></i> </button>

                                    </div>

                                    <div class="card" >
                                        <ul class="list-group list-group-flush" id="descripcionProducto">
                                        </ul>
                                      </div>
                                    <div style="display: none" class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <label for="bodega" class="col-form-label focus-label">Bodega:<span class="text-danger">*</span></label>
                                        <select id="bodega" name="bodega" class="form-group form-control"
                                            style="" onchange="prueba()" disabled>
                                            <option value="156" selected disabled>SALA DE VENTAS</option>
                                        </select>
                                    </div>
                                </div>


                            </div>

                            <hr>

                            <div class="hide-container">
                                <p>Nota:El campo "Unidad" describe la unidad de medida para la venta del producto -
                                    seguido del numero de unidades a restar del inventario</p>
                                <div class="row no-gutters ">

                                    <div class="form-group col-3">
                                        <div class="d-flex">



                                            <div style="width:100%">
                                                <label class="sr-only">Producto</label>
                                                <input type="text" placeholder="Producto"
                                                    class="form-control" pattern="[A-Z]{1}" disabled>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-1">
                                        <label class="sr-only">Bodega</label>
                                        <input type="number" placeholder="Bodega" class="form-control"
                                            autocomplete="off" disabled>
                                    </div>


                                    <div class="form-group col-2">
                                        <label class="sr-only">Precios</label>
                                        <input type="number" placeholder="Opciones" class="form-control"
                                            min="1" autocomplete="off" disabled>
                                    </div>


                                    <div class="form-group col-1">
                                        <label class="sr-only">Precio</label>
                                        <input type="number" placeholder="Precio" class="form-control"
                                            min="1" autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-1">
                                        <label class="sr-only">cantidad</label>
                                        <input type="text" placeholder="Cantidad" class="form-control"
                                            min="1" autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-1">

                                        <label class="sr-only">Unidad</label>
                                        <input type="text" placeholder="Unidad " class="form-control"
                                            min="1" autocomplete="off" disabled>




                                    </div>
                                    {{--
                                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                        <label class="sr-only">Seccion</label>
                                        <input type="text" placeholder="Seccion" class="form-control"
                                            min="1" autocomplete="off" disabled>
                                    </div> --}}


                                    <div class="form-group col-1">
                                        <label class="sr-only">Sub Total</label>
                                        <input type="number" placeholder="Sub total"
                                            class="form-control" min="1" autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-1">
                                        <label class="sr-only">ISV</label>
                                        <input type="number" placeholder="ISV" class="form-control" min="1"
                                            autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-1">
                                        <label class="sr-only">Total</label>
                                        <input type="number" placeholder="Total" class="form-control"
                                            min="1" disabled autocomplete="off">
                                    </div>

                                </div>



                            </div>

                            <div id="divProductos">

                            </div>









                            <hr>
                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="descuentoMostrar">Descuento L.<span class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="text" placeholder="Descuento aplicado" id="descuentoMostrar"
                                        name="descuentoMostrar" class="form-control"
                                        data-parsley-required autocomplete="off" readonly>

                                        <input type="hidden" id="descuentoGeneral" name="descuentoGeneral" required>

                                </div>
                            </div>

                            <div class="row">


                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="subTotalGeneralMostrar">Sub Total L.<span
                                            class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="text" placeholder="Sub total " id="subTotalGeneralMostrar"
                                        name="subTotalGeneralMostrar" class="form-control" data-parsley-required
                                        autocomplete="off" readonly>

                                    <input id="subTotalGeneral" name="subTotalGeneral" type="hidden" value=""
                                        required>
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
                                        data-parsley-required autocomplete="off" readonly>

                                    <input id="subTotalGeneralGrabado" name="subTotalGeneralGrabado" type="hidden"
                                        value="" required>
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
                                        data-parsley-required autocomplete="off" readonly>

                                    <input id="subTotalGeneralExcento" name="subTotalGeneralExcento" type="hidden"
                                        value="" required>
                                </div>
                            </div>

                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="isvGeneralMostrar">ISV L.<span
                                            class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="text" placeholder="ISV " id="isvGeneralMostrar"
                                        name="isvGeneralMostrar" class="form-control" data-parsley-required
                                        autocomplete="off" readonly>
                                    <input id="isvGeneral" name="isvGeneral" type="hidden" value="" required>
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
                                        autocomplete="off" readonly>

                                    <input id="totalGeneral" name="totalGeneral" type="hidden" value=""
                                        required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <button id="guardar_cotizacion_btn"
                                        class="float-left btn btn-primary m-t-n-xs"><strong>
                                            Guardar Cotizacion</strong></button>
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

            $('#seleccionarCliente').select2({
                ajax: {
                    url: '/expo/clientes',
                    data: function(params) {
                        var query = {
                            search: params.term,
                            tipoCotizacion: {{ $tipoCotizacion }},
                            type: 'public',
                            page: params.page || 1
                        }

                        // Query parameters will be ?search=[term]&type=public
                        return query;
                    }
                }
            });


        </script>

        <script src="{{ asset('js/js_proyecto/cotizaciones/expo.js') }}"></script>
        
        <!-- QuaggaJS para lectura de códigos de barras -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
        
        <script>
            // Variables globales para el escáner
            let isScanning = false;
            let videoStream = null;

            // Función para inicializar el escáner
            function initBarcodeScanner() {
                const video = document.getElementById('videoElement');
                const canvas = document.getElementById('canvasElement');
                const cameraContainer = document.getElementById('cameraContainer');
                const scannerStatus = document.getElementById('scannerStatus');
                const statusMessage = document.getElementById('statusMessage');
                
                // Mostrar estado de carga
                scannerStatus.style.display = 'block';
                statusMessage.textContent = 'Solicitando acceso a la cámara...';
                
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: { ideal: 'environment' }, // Preferir cámara trasera
                            width: { min: 640, ideal: 1280, max: 1920 },
                            height: { min: 480, ideal: 720, max: 1080 },
                            frameRate: { ideal: 30, max: 60 }
                        },
                        audio: false
                    })
                    .then(function(stream) {
                        videoStream = stream;
                        video.srcObject = stream;
                        
                        // Configurar el video para mejor rendimiento
                        video.setAttribute('playsinline', true);
                        video.setAttribute('webkit-playsinline', true);
                        video.muted = true;
                        
                        video.onloadedmetadata = function() {
                            video.play().then(function() {
                                cameraContainer.style.display = 'block';
                                statusMessage.textContent = 'Inicializando escáner...';
                                
                                // Crear overlay de escaneo con mejor posicionamiento
                                setTimeout(function() {
                                    if (!document.querySelector('.scanner-overlay')) {
                                        const overlay = document.createElement('div');
                                        overlay.className = 'scanner-overlay';
                                        cameraContainer.appendChild(overlay);
                                    }
                                    
                                    startQuaggaScanner();
                                    isScanning = true;
                                    
                                    document.getElementById('btnStartCamera').style.display = 'none';
                                    document.getElementById('btnStopCamera').style.display = 'inline-block';
                                }, 500);
                                
                            }).catch(function(playError) {
                                console.error('Error al reproducir el video:', playError);
                                statusMessage.textContent = 'Error al iniciar la vista previa de la cámara.';
                                scannerStatus.className = 'alert alert-danger';
                            });
                        };
                        
                        video.onerror = function(videoError) {
                            console.error('Error en el video:', videoError);
                            statusMessage.textContent = 'Error en la transmisión de video.';
                            scannerStatus.className = 'alert alert-danger';
                        };
                        
                    })
                    .catch(function(err) {
                        console.error('Error al acceder a la cámara:', err);
                        let errorMsg = 'Error al acceder a la cámara. ';
                        
                        if (err.name === 'NotAllowedError') {
                            errorMsg += 'Permisos denegados. Por favor, permite el acceso a la cámara.';
                        } else if (err.name === 'NotFoundError') {
                            errorMsg += 'No se encontró ninguna cámara en el dispositivo.';
                        } else if (err.name === 'NotSupportedError') {
                            errorMsg += 'La cámara no es compatible con este navegador.';
                        } else {
                            errorMsg += 'Verifique los permisos y que la cámara esté disponible.';
                        }
                        
                        statusMessage.textContent = errorMsg;
                        scannerStatus.className = 'alert alert-danger';
                    });
                } else {
                    statusMessage.textContent = 'Su navegador no soporta acceso a la cámara. Pruebe con Chrome, Firefox o Safari.';
                    scannerStatus.className = 'alert alert-warning';
                    scannerStatus.style.display = 'block';
                }
            }

            // Función para inicializar QuaggaJS
            function startQuaggaScanner() {
                Quagga.init({
                    inputStream: {
                        name: "Live",
                        type: "LiveStream",
                        target: document.querySelector('#videoElement'),
                        constraints: {
                            width: { min: 640, ideal: 1280, max: 1920 },
                            height: { min: 480, ideal: 720, max: 1080 },
                            facingMode: "environment", // Cámara trasera preferentemente
                            aspectRatio: { min: 1, max: 2 }
                        },
                        area: { // Área de escaneo
                            top: "25%",    // 25% desde arriba
                            right: "15%",  // 15% desde la derecha
                            left: "15%",   // 15% desde la izquierda
                            bottom: "25%"  // 25% desde abajo
                        }
                    },
                    locator: {
                        patchSize: "medium", // Tamaño de parche para detección
                        halfSample: false    // Mejor calidad
                    },
                    numOfWorkers: navigator.hardwareConcurrency || 4, // Usar todos los núcleos disponibles
                    frequency: 10, // Frecuencia de escaneo
                    decoder: {
                        readers: [
                            "code_128_reader",
                            "ean_reader",
                            "ean_8_reader", 
                            "code_39_reader",
                            "code_39_vin_reader",
                            "codabar_reader",
                            "upc_reader",
                            "upc_e_reader",
                            "i2of5_reader",
                            "2of5_reader",
                            "code_93_reader"
                        ],
                        debug: {
                            showCanvas: false,
                            showPatches: false,
                            showFoundPatches: false,
                            showSkeleton: false,
                            showLabels: false,
                            showPatchLabels: false,
                            showRemainingPatchLabels: false,
                            boxFromPatches: {
                                showTransformed: false,
                                showTransformedBox: false,
                                showBB: false
                            }
                        }
                    },
                    locate: true
                }, function(err) {
                    if (err) {
                        console.error('Error al inicializar Quagga:', err);
                        document.getElementById('statusMessage').textContent = 'Error al inicializar el escáner: ' + err;
                        document.getElementById('scannerStatus').className = 'alert alert-danger';
                        return;
                    }
                    
                    console.log("Inicialización de Quagga completa. Listo para escanear");
                    document.getElementById('statusMessage').textContent = 'Escáner activo. Enfoque el código de barras en el recuadro rojo...';
                    
                    Quagga.start();
                    
                    // Configurar variables para control de detección
                    let lastDetectionTime = 0;
                    let detectionCooldown = 2000; // 2 segundos entre detecciones
                    let detectionCount = 0;
                    let lastCode = '';
                    
                    // Evento cuando se detecta un código de barras
                    Quagga.onDetected(function(data) {
                        const currentTime = Date.now();
                        const code = data.codeResult.code;
                        
                        // Evitar detecciones duplicadas muy seguidas
                        if (currentTime - lastDetectionTime < detectionCooldown && code === lastCode) {
                            return;
                        }
                        
                        // Validar que el código tenga una longitud mínima
                        if (!code || code.length < 3) {
                            return;
                        }
                        
                        lastDetectionTime = currentTime;
                        lastCode = code;
                        detectionCount++;
                        
                        console.log(`Código de barras detectado (${detectionCount}):`, code);
                        
                        // Mostrar el resultado visualmente
                        document.getElementById('barcodeResult').textContent = code;
                        document.getElementById('scanResult').style.display = 'block';
                        
                        // Detener temporalmente el escáner para procesar
                        Quagga.pause();
                        
                        // Agregar automáticamente al carrito
                        agregarProductoCarritoBarra(code);
                        
                        // Reactivar el escáner después de un tiempo
                        setTimeout(function() {
                            if (isScanning) {
                                document.getElementById('scanResult').style.display = 'none';
                                Quagga.start();
                            }
                        }, 3000);
                    });
                    
                    // Evento para errores durante el procesamiento
                    Quagga.onProcessed(function(result) {
                        if (result) {
                            // Opcional: mostrar información de debug
                            if (result.codeResult && result.codeResult.code) {
                                console.log("Código procesado:", result.codeResult.code);
                            }
                        }
                    });
                });
            }

            // Función para detener el escáner
            function stopBarcodeScanner() {
                if (isScanning) {
                    Quagga.stop();
                    isScanning = false;
                }
                
                if (videoStream) {
                    videoStream.getTracks().forEach(track => track.stop());
                    videoStream = null;
                }
                
                document.getElementById('cameraContainer').style.display = 'none';
                document.getElementById('scannerStatus').style.display = 'none';
                document.getElementById('scanResult').style.display = 'none';
                
                document.getElementById('btnStartCamera').style.display = 'inline-block';
                document.getElementById('btnStopCamera').style.display = 'none';
                
                // Remover overlay
                const overlay = document.querySelector('.scanner-overlay');
                if (overlay) {
                    overlay.remove();
                }
            }

            // Event listeners para los botones
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('btnStartCamera').addEventListener('click', initBarcodeScanner);
                document.getElementById('btnStopCamera').addEventListener('click', stopBarcodeScanner);
            });
        </script>
    @endpush
</div>
