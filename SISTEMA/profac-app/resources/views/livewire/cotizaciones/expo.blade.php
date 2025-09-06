<div>
    @push('styles')
        <style>
 #alert-fixed {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999; /* para que quede por encima de todo */
      max-width: 300px;
    }

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
        </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            @if ($tipoCotizacion == 3)
                <h2>Expo pedidos</h2>
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
    <div id="alert-fixed"></div>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Datos del pedido <i class="fa-solid fa-cart-shopping"></i></h3>
                    </div>
                    <div class="ibox-content">
                        <form onkeydown="return event.key != 'Enter';" autocomplete="off" id="crear_venta"
                            name="crear_venta" data-parsley-validate>


                            <input type="hidden" id="tipo_venta_id" name="tipo_venta_id" value="4">

                            <div class="row align-items-center" style="margin-top: -60px; margin-left:210px;">
                                <label for="pedido_id" class="col-md-3 col-form-label focus-label">
                                    Pedido:<span class="text-danger">*</span>
                                </label>
                                <div class="col-md-9">
                                    <input class="form-control" type="text" readonly id="pedido_id" name="pedido_id"
                                        style="width: 150px;"  value="">
                                </div>
                            </div>



                            <div class="row">
                                <div class="col-md-6">
                                    <label for="seleccionarCliente" class="col-form-label focus-label">
                                    Seleccionar Cliente: <span class="text-danger">*</span>
                                    </label>
                                    <select id="seleccionarCliente" name="seleccionarCliente"
                                    class="form-control" data-parsley-required onchange="obtenerDatosCliente()">
                                    <option value="" selected disabled>--Seleccionar un cliente--</option>
                                    </select>
                                </div>

                                <!-- Nombre del cliente -->
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">
                                    Nombre del cliente: <span class="text-danger">*</span>
                                    </label>
                                    <input class="form-control" required type="text"
                                    id="nombre_cliente_ventas" name="nombre_cliente_ventas" readonly>
                                </div>
                                </div>

                                <div class="row mt-3">
                                <!-- RTN -->
                                    <div class="col-md-6">
                                        <label class="col-form-label focus-label">
                                        RTN: <span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="text" id="rtn_ventas" name="rtn_ventas" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="vendedor">Vendedor:<span class="text-danger"></span> </label>
                                        <select name="vendedor" id="vendedor" class="form-group form-control">

                                        </select>
                                    </div>

                                </div>

                            <div class="row mt-3">
                                <div class="col-md-6" style="display: none;">
                                    <label for="tipoPagoVenta" class="col-form-label focus-label">Seleccionar tipo de
                                        pago:<span class="text-danger">*</span></label>
                                    <select class="form-group form-control " name="tipoPagoVenta" id="tipoPagoVenta"
                                        onchange="validarFechaPago()">
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">

                                        <label for="fecha_emision" class="col-form-label focus-label">Fecha de emisión
                                            :<span class="text-danger">*</span></label>
                                        <input class="form-control" type="date" id="fecha_emision"
                                            onchange="sumarDiasCredito()" name="fecha_emision"
                                            value="{{ date('Y-m-d') }}" data-parsley-required>

                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">

                                        <label for="porDescuento" class="col-form-label focus-label">Descuento aplicado %
                                            :<span class="text-danger">*</span></label>
                                        <input class="form-control" oninput="validarDescuento()" onchange="calcularTotalesInicioPagina()" type="number" value="0" max="100" min="0"  mminlength="1" maxlength="2" id="porDescuento" name="porDescuento"  >
                                        <p id="mensajeError" style="color: red;"></p>


                                    </div>
                                </div>

                                <div class="col-md-6" style="display: none;">
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
                                {{--  <!-- Sección para lectura de código de barras con cámara -->  --}}
                                    {{--  <div class="row mt-3">
                                        <div class="card camera-card">
                                            <div class="text-white card-header bg-primary">
                                                <h5 class="mb-0"><i class="fa fa-camera"></i> Lectura de Código de Barras</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 text-center col-12">
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
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>  --}}
                            <div class="row mt-3">
                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 ">

                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <label for="seleccionarProducto" class="col-form-label focus-label"> <label style="display: none" class="switch"> <input type="checkbox" id="mySwitch"><span class="slider"></span></label> Seleccionar
                                            Producto :<span class="text-danger">*</span></label>
                                        <select id="seleccionarProducto" name="seleccionarProducto"
                                            class="form-group form-control" style="width: 110%;" onchange="obtenerImagenes()">
                                            <option value="" selected disabled>--Seleccione un producto--</option>
                                        </select>
                                    </div>

                                </div>


                            </div>

                            <div class="row">


                                <div class="col-md-6 mt-3">
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
                                <div class="col-md-6" style="margin-top:-35px;">
                                    <div id="botonAdd"class="text-center d-none">
                                        <button type="button" class="btn-rounded btn btn-success p-3"
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

                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <div class="form-group">
                                        <label for="nota" class="col-form-label focus-label">Nota:
                                        </label>
                                        <textarea class="form-control" id="nota" name="nota" cols="30" rows="3" maxlength="250"></textarea>
                                    </div>

                                </div>


                            </div>
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
                                        class="btn  btn-primary float-left m-t-n-xs"><strong>
                                            Guardar </strong></button>
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
                        let code = data.codeResult.code;

                        // Omitir los ceros a la izquierda
                        code = code.replace(/^0+/, '') || '0';

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

                        // Agregar automáticamente al carrito funciono la basuk
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
                            // mando el fucking debug por que no me saba esta basuk
                            if (result.codeResult && result.codeResult.code) {
                                console.log("Código procesado:", result.codeResult.code);
                            }
                        }
                    });
                });
            }


             function agregarProductoCarritoBarra(codigoBarra) {
                console.log('Buscando producto con código de barras:', codigoBarra);

                // Función para reproducir sonido como le gusta a yeff
                function playSound(type) {
                    try {
                        let frequency = type === 'success' ? 800 : 300;
                        let duration = type === 'success' ? 200 : 500;

                        // Crear contexto de audio
                        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        const oscillator = audioContext.createOscillator();
                        const gainNode = audioContext.createGain();

                        oscillator.connect(gainNode);
                        gainNode.connect(audioContext.destination);

                        oscillator.frequency.value = frequency;
                        oscillator.type = 'sine';

                        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + duration / 1000);

                        oscillator.start(audioContext.currentTime);
                        oscillator.stop(audioContext.currentTime + duration / 1000);
                    } catch (e) {
                        console.log('No se pudo reproducir el sonido:', e);
                    }
                }

                // Usar el método existente obtenerDatosProductoExpo
                axios.post('/ventas/datos/producto/expo', {
                    barraProd: codigoBarra
                })
                .then(response => {
                    // Verificar si la respuesta indica éxito
                    if (!response.data.success) {
                        playSound('error');
                        Swal.fire({
                            icon: 'error',
                            title: '¡Producto No Encontrado!',
                            html: `
                                <div style="text-align: left;">
                                    <p><strong>Código escaneado:</strong> ${codigoBarra}</p>
                                    <p><strong>Estado:</strong> No existe en la base de datos</p>
                                    <hr>
                                    <p style="color: #666; font-size: 0.9em;">
                                        • Verifique que el código esté completo<br>
                                        • Asegúrese de que el producto esté registrado<br>
                                        • Contacte al administrador si persiste el problema
                                    </p>
                                </div>
                            `,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#d33'
                        });
                        return;
                    }

                    let producto = response.data.producto;
                    let arrayUnidades = response.data.unidades;

                    if (!producto || !producto.id) {
                        playSound('error');
                        Swal.fire({
                            icon: 'warning',
                            title: 'Producto no encontrado!',
                            text: `No se encontró ningún producto con el código de barras: ${codigoBarra}`,
                            confirmButtonColor: '#f39c12'
                        });
                        return;
                    }

                    // Reproducir sonido de éxito
                    playSound('success');

                    // Usar la misma lógica que agregarProductoCarrito
                    let bodega = 'SALA DE VENTAS';
                    let idBodega = 16;
                    let idSeccion = 156;
                    let idProducto = producto.id;

                    // Verificar si el producto ya existe en el carrito
                    let flag = false;
                    arregloIdInputs.forEach(idInpunt => {
                        let idProductoFila = document.getElementById("idProducto" + idInpunt).value;
                        let idSeccionFila = document.getElementById("idSeccion" + idInpunt).value;

                        if (idProducto == idProductoFila && idSeccion == idSeccionFila && !flag) {
                            flag = true;
                        }
                    });

                    if (flag) {
                        playSound('error');
                        Swal.fire({
                            icon: 'info',
                            title: '¡Producto ya agregado!',
                            text: 'Este producto ya se encuentra en el carrito. Modifique la cantidad si es necesario.',
                            confirmButtonColor: '#17a2b8'
                        });
                        return;
                    }

                    numeroInputs += 1;

                    let htmlSelectUnidades = "";
                    let htmlprecios = `
                    <option data-id="0" selected>--Seleccione precio--</option>
                    <option  value="${producto.precio_base}" data-id="pb">${producto.precio_base} - Base</option>
                    <option  value="${producto.precio1}" data-id="p1">${producto.precio1} - A</option>
                    <option  value="${producto.precio2}" data-id="p2">${producto.precio2} - B</option>
                    <option  value="${producto.precio3}" data-id="p3">${producto.precio3} - C</option>
                    <option  value="${producto.precio4}" data-id="p4">${producto.precio4} - D</option>
                    `;

                    arrayUnidades.forEach(unidad => {
                        if (unidad.valor_defecto == 1) {
                            htmlSelectUnidades +=
                                `<option selected value="${unidad.id}" data-id="${unidad.idUnidadVenta}">${unidad.nombre}</option>`;
                        } else {
                            htmlSelectUnidades +=
                                `<option  value="${unidad.id}" data-id="${unidad.idUnidadVenta}">${unidad.nombre}</option>`;
                        }
                    });

                    let html = `
                    <div id='${numeroInputs}' class="row no-gutters">
                        <div class="form-group col-3">
                            <div class="d-flex">
                                <button class="btn btn-danger" type="button" style="display: inline" onclick="eliminarInput(${numeroInputs})"><i
                                        class="fa-regular fa-rectangle-xmark"></i>
                                </button>
                                <input id="idProducto${numeroInputs}" name="idProducto${numeroInputs}" type="hidden" value="${producto.id}">
                                <div style="width:100%">
                                    <label for="nombre${numeroInputs}" class="sr-only">Producto</label>
                                    <input type="text" placeholder="Producto" id="nombre${numeroInputs}"
                                        name="nombre${numeroInputs}" class="form-control"
                                        data-parsley-required
                                        autocomplete="off"
                                        readonly
                                        value='${producto.nombre} 📱'
                                        style="background-color: #e8f5e8; border-color: #28a745; font-weight: bold;">
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-1">
                            <label for="" class="sr-only">cantidad</label>
                            <input type="text" value="${bodega}" placeholder="Bodega" id="bodega${numeroInputs}"
                                name="bodega${numeroInputs}" class="form-control"
                                autocomplete="off"  readonly  >
                        </div>
                        <div class="form-group col-2">
                            <label for="" class="sr-only">precios</label>
                            <select class="form-control" name="precios${numeroInputs}" id="precios${numeroInputs}"
                                data-parsley-required style="height:35.7px;"
                                onchange="validacionPrecio(precios${numeroInputs}, precio${numeroInputs})"
                                >
                                    ${htmlprecios}
                            </select>
                        </div>
                        <div class="form-group col-1">
                            <label for="precio${numeroInputs}" class="sr-only">Precio</label>
                            <input type="number" placeholder="Precio Unidad" id="precio${numeroInputs}"
                                name="precio${numeroInputs}" class="form-control"  data-parsley-required step="any"
                                autocomplete="off" onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                        </div>
                        <div class="form-group col-1">
                            <label for="cantidad${numeroInputs}" class="sr-only">cantidad</label>
                            <input type="number" placeholder="Cantidad" id="cantidad${numeroInputs}"
                                name="cantidad${numeroInputs}" class="form-control" min="1" data-parsley-required
                                autocomplete="off" value="1" onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                        </div>
                        <div class="form-group col-1">
                            <label for="" class="sr-only">unidad</label>
                            <select class="form-control" name="unidad${numeroInputs}" id="unidad${numeroInputs}"
                                data-parsley-required style="height:35.7px;"
                                onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                                    ${htmlSelectUnidades}
                            </select>
                        </div>
                        <div class="form-group col-1">
                            <label for="subTotalMostrar${numeroInputs}" class="sr-only">Sub Total</label>
                            <input type="text" placeholder="Sub total" id="subTotalMostrar${numeroInputs}"
                                name="subTotalMostrar${numeroInputs}" class="form-control"
                                autocomplete="off"
                                readonly >
                            <input id="subTotal${numeroInputs}" name="subTotal${numeroInputs}" type="hidden" value="" required>
                            <input type="hidden" id="acumuladoDescuento${numeroInputs}" name="acumuladoDescuento${numeroInputs}" >
                        </div>
                        <div class="form-group col-1">
                            <label for="isvProductoMostrar${numeroInputs}" class="sr-only">ISV</label>
                            <input type="text" placeholder="ISV" id="isvProductoMostrar${numeroInputs}"
                                name="isvProductoMostrar${numeroInputs}" class="form-control"
                                autocomplete="off"
                                readonly >
                            <input id="isvProducto${numeroInputs}" name="isvProducto${numeroInputs}" type="hidden" value="" required>
                        </div>
                        <div class="form-group col-1">
                            <label for="totalMostrar${numeroInputs}" class="sr-only">Total</label>
                            <input type="text" placeholder="Total" id="totalMostrar${numeroInputs}"
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

                    arregloIdInputs.splice(numeroInputs, 0, numeroInputs);
                    document.getElementById('divProductos').insertAdjacentHTML('beforeend', html);

                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Producto Escaneado!',
                        html: `
                            <div style="text-align: left;">
                                <p><strong>Código:</strong> ${codigoBarra}</p>
                                <p><strong>Producto:</strong> ${producto.nombre}</p>
                                <p style="color: #28a745;">✓ Agregado al carrito exitosamente</p>
                            </div>
                        `,
                        timer: 2500,
                        showConfirmButton: false
                    });

                    return;
                })
                .catch(err => {
                    console.error('Error al buscar producto por código de barras:', err);
                    playSound('error');

                    // Manejar diferentes tipos de errores
                    let errorMessage = 'No se pudo encontrar el producto con ese código de barras.';
                    let errorTitle = 'Error al escanear';

                    if (err.response) {
                        if (err.response.status === 404) {
                            errorTitle = '¡Producto No Encontrado!';
                            errorMessage = err.response.data.message || 'El producto no existe en la base de datos';
                        } else if (err.response.data && err.response.data.message) {
                            errorMessage = err.response.data.message;
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: errorTitle,
                        html: `
                            <div style="text-align: left;">
                                <p><strong>Código escaneado:</strong> ${codigoBarra}</p>
                                <p><strong>Error:</strong> ${errorMessage}</p>
                                <hr>
                                <p style="color: #666; font-size: 0.9em;">
                                    Intente escanear el código nuevamente o verifique que el producto esté registrado en el sistema.
                                </p>
                            </div>
                        `,
                        confirmButtonColor: '#d33'
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

            // Función de prueba para debuggear
            window.testBarcodeAPI = function(codigo) {
                codigo = codigo || '849607055569';
                console.log('Probando API con código:', codigo);

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('/ventas/datos/producto/expo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        barraProd: codigo
                    })
                })
                .then(response => {
                    console.log('Status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Respuesta completa:', data);

                    if (data.success) {
                        console.log('✅ Producto encontrado:', data.producto.nombre);
                        alert(`✅ Producto encontrado: ${data.producto.nombre}\nCódigo: ${codigo}\nPrecio: L. ${data.producto.precio_base}`);
                    } else {
                        console.log('❌ Producto no encontrado');
                        alert(`❌ Producto no encontrado con código: ${codigo}`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(`❌ Error de conexión: ${error.message}`);
                });
            };

            // Función para agregar producto al carrito mediante código de barras
            window.agregarProductoCarritoBarra = function(codigoBarra) {
                console.log('Buscando producto con código de barras:', codigoBarra);

                // Función para reproducir sonido como le gusta a yeff
                function playSound(type) {
                    try {
                        let frequency = type === 'success' ? 800 : 300;
                        let duration = type === 'success' ? 200 : 500;

                        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        const oscillator = audioContext.createOscillator();
                        const gainNode = audioContext.createGain();

                        oscillator.connect(gainNode);
                        gainNode.connect(audioContext.destination);

                        oscillator.frequency.value = frequency;
                        oscillator.type = 'sine';

                        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + duration / 1000);

                        oscillator.start(audioContext.currentTime);
                        oscillator.stop(audioContext.currentTime + duration / 1000);
                    } catch (e) {
                        console.log('No se pudo reproducir el sonido:', e);
                    }
                }

                // Obtener token CSRF
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Hacer petición al backend
                fetch('/ventas/datos/producto/expo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        barraProd: codigoBarra
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Respuesta del servidor:', data);

                    if (!data.success) {
                        playSound('error');
                        Swal.fire({
                            icon: 'error',
                            title: '¡Producto No Encontrado!',
                            html: `
                                <div style="text-align: left;">
                                    <p><strong>Código escaneado:</strong> ${codigoBarra}</p>
                                    <p><strong>Estado:</strong> No existe en la base de datos</p>
                                    <hr>
                                    <p style="color: #666; font-size: 0.9em;">
                                        • Verifique que el código esté completo<br>
                                        • Asegúrese de que el producto esté registrado<br>
                                        • Contacte al administrador si persiste el problema
                                    </p>
                                </div>
                            `,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#d33'
                        });
                        return;
                    }

                    // Verificar que tenemos los datos necesarios
                    const producto = data.producto;
                    const arrayUnidades = data.unidades;

                    if (!producto || !producto.id) {
                        playSound('error');
                        Swal.fire({
                            icon: 'warning',
                            title: 'Datos incompletos',
                            text: `El producto con código ${codigoBarra} no tiene datos válidos.`,
                            confirmButtonColor: '#f39c12'
                        });
                        return;
                    }

                    // Reproducir sonido de éxito yeah
                    playSound('success');

                    // Usar la misma lógica que agregarProductoCarrito
                    let bodega = 'SALA DE VENTAS';
                    let idBodega = 16;
                    let idSeccion = 156;
                    let idProducto = producto.id;

                    // Verificar si el producto ya existe en el carrito(La dejo pero si queres la quitas)
                    let flag = false;
                    if (typeof arregloIdInputs !== 'undefined') {
                        arregloIdInputs.forEach(idInpunt => {
                            let idProductoFila = document.getElementById("idProducto" + idInpunt)?.value;
                            let idSeccionFila = document.getElementById("idSeccion" + idInpunt)?.value;

                            if (idProducto == idProductoFila && idSeccion == idSeccionFila && !flag) {
                                flag = true;
                            }
                        });
                    }

                    if (flag) {
                        playSound('error');
                        Swal.fire({
                            icon: 'info',
                            title: '¡Producto ya agregado!',
                            text: 'Este producto ya se encuentra en el carrito. Modifique la cantidad si es necesario.',
                            confirmButtonColor: '#17a2b8'
                        });
                        return;
                    }

                    // Incrementar contador y agregar a array
                    if (typeof numeroInputs !== 'undefined') {
                        numeroInputs += 1;
                    } else {
                        window.numeroInputs = 1;
                    }

                    // Construir HTML de unidades
                    let htmlSelectUnidades = "";
                    arrayUnidades.forEach(unidad => {
                        if (unidad.valor_defecto == 1) {
                            htmlSelectUnidades += `<option selected value="${unidad.id}" data-id="${unidad.idUnidadVenta}">${unidad.nombre}</option>`;
                        } else {
                            htmlSelectUnidades += `<option value="${unidad.id}" data-id="${unidad.idUnidadVenta}">${unidad.nombre}</option>`;
                        }
                    });

                    // Construir HTML de precios
                    let htmlprecios = `
                        <option data-id="0" selected>--Seleccione precio--</option>
                        <option value="${producto.precio_base}" data-id="pb">${producto.precio_base} - Base</option>
                        <option value="${producto.precio1}" data-id="p1">${producto.precio1} - A</option>
                        <option value="${producto.precio2}" data-id="p2">${producto.precio2} - B</option>
                        <option value="${producto.precio3}" data-id="p3">${producto.precio3} - C</option>
                        <option value="${producto.precio4}" data-id="p4">${producto.precio4} - D</option>
                    `;

                    let currentInputId = typeof numeroInputs !== 'undefined' ? numeroInputs : 1;

                    // Construir HTML completo del producto
                    let html = `
                        <div id='${currentInputId}' class="row no-gutters">
                            <div class="form-group col-3">
                                <div class="d-flex">
                                    <button class="btn btn-danger" type="button" style="display: inline" onclick="eliminarInput(${currentInputId})">
                                        <i class="fa-regular fa-rectangle-xmark"></i>
                                    </button>
                                    <input id="idProducto${currentInputId}" name="idProducto${currentInputId}" type="hidden" value="${producto.id}">
                                    <div style="width:100%">
                                        <label for="nombre${currentInputId}" class="sr-only">Producto</label>
                                        <input type="text" placeholder="Producto" id="nombre${currentInputId}"
                                            name="nombre${currentInputId}" class="form-control"
                                            data-parsley-required autocomplete="off" readonly
                                            value='${producto.nombre} 📱'
                                            style="background-color: #e8f5e8; border-color: #28a745; font-weight: bold;">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-1">
                                <label for="" class="sr-only">Bodega</label>
                                <input type="text" value="${bodega}" placeholder="Bodega" id="bodega${currentInputId}"
                                    name="bodega${currentInputId}" class="form-control" autocomplete="off" readonly>
                            </div>
                            <div class="form-group col-2">
                                <label for="" class="sr-only">Precios</label>
                                <select class="form-control" name="precios${currentInputId}" id="precios${currentInputId}"
                                    data-parsley-required style="height:35.7px;"
                                    onchange="validacionPrecio(precios${currentInputId}, precio${currentInputId})">
                                    ${htmlprecios}
                                </select>
                            </div>
                            <div class="form-group col-1">
                                <label for="precio${currentInputId}" class="sr-only">Precio</label>
                                <input type="number" placeholder="Precio Unidad" id="precio${currentInputId}"
                                    name="precio${currentInputId}" class="form-control" data-parsley-required step="any"
                                    autocomplete="off" onchange="calcularTotales(precio${currentInputId},cantidad${currentInputId},${producto.isv},unidad${currentInputId},${currentInputId},restaInventario${currentInputId})">
                            </div>
                            <div class="form-group col-1">
                                <label for="cantidad${currentInputId}" class="sr-only">Cantidad</label>
                                <input type="number" placeholder="Cantidad" id="cantidad${currentInputId}"
                                    name="cantidad${currentInputId}" class="form-control" min="1" data-parsley-required
                                    autocomplete="off" value="1" onchange="calcularTotales(precio${currentInputId},cantidad${currentInputId},${producto.isv},unidad${currentInputId},${currentInputId},restaInventario${currentInputId})">
                            </div>
                            <div class="form-group col-1">
                                <label for="" class="sr-only">Unidad</label>
                                <select class="form-control" name="unidad${currentInputId}" id="unidad${currentInputId}"
                                    data-parsley-required style="height:35.7px;"
                                    onchange="calcularTotales(precio${currentInputId},cantidad${currentInputId},${producto.isv},unidad${currentInputId},${currentInputId},restaInventario${currentInputId})">
                                    ${htmlSelectUnidades}
                                </select>
                            </div>
                            <div class="form-group col-1">
                                <label for="subTotalMostrar${currentInputId}" class="sr-only">Sub Total</label>
                                <input type="text" placeholder="Sub total" id="subTotalMostrar${currentInputId}"
                                    name="subTotalMostrar${currentInputId}" class="form-control" autocomplete="off" readonly>
                                <input id="subTotal${currentInputId}" name="subTotal${currentInputId}" type="hidden" value="" required>
                                <input type="hidden" id="acumuladoDescuento${currentInputId}" name="acumuladoDescuento${currentInputId}">
                            </div>
                            <div class="form-group col-1">
                                <label for="isvProductoMostrar${currentInputId}" class="sr-only">ISV</label>
                                <input type="text" placeholder="ISV" id="isvProductoMostrar${currentInputId}"
                                    name="isvProductoMostrar${currentInputId}" class="form-control" autocomplete="off" readonly>
                                <input id="isvProducto${currentInputId}" name="isvProducto${currentInputId}" type="hidden" value="" required>
                            </div>
                            <div class="form-group col-1">
                                <label for="totalMostrar${currentInputId}" class="sr-only">Total</label>
                                <input type="text" placeholder="Total" id="totalMostrar${currentInputId}"
                                    name="totalMostrar${currentInputId}" class="form-control" autocomplete="off" readonly>
                                <input id="total${currentInputId}" name="total${currentInputId}" type="hidden" value="" required>
                            </div>
                            <input id="idBodega${currentInputId}" name="idBodega${currentInputId}" type="hidden" value="${idBodega}">
                            <input id="idSeccion${currentInputId}" name="idSeccion${currentInputId}" type="hidden" value="${idSeccion}">
                            <input id="restaInventario${currentInputId}" name="restaInventario${currentInputId}" type="hidden" value="">
                            <input id="isv${currentInputId}" name="isv${currentInputId}" type="hidden" value="${producto.isv}">
                        </div>
                    `;

                    // Agregar el HTML al DOM
                    const divProductos = document.getElementById('divProductos');
                    if (divProductos) {
                        divProductos.insertAdjacentHTML('beforeend', html);

                        // Agregar al array de IDs si existe
                        if (typeof arregloIdInputs !== 'undefined') {
                            arregloIdInputs.splice(currentInputId, 0, currentInputId);
                        }

                        // Mostrar mensaje de éxito
                        Swal.fire({
                            icon: 'success',
                            title: '¡Producto Escaneado!',
                            html: `
                                <div style="text-align: left;">
                                    <p><strong>Código:</strong> ${codigoBarra}</p>
                                    <p><strong>Producto:</strong> ${producto.nombre}</p>
                                    <p style="color: #28a745;">✓ Agregado al carrito exitosamente</p>
                                </div>
                            `,
                            timer: 2500,
                            showConfirmButton: false
                        });
                    } else {
                        console.error('No se encontró el div de productos');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo agregar el producto al carrito. Intente nuevamente.',
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    playSound('error');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor. Intente nuevamente.',
                        confirmButtonColor: '#d33'
                    });
                });
            };
        </script>

    @endpush
</div>
