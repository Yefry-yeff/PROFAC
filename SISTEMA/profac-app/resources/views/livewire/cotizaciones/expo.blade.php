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

            /* Estilos para el scanner de códigos de barras */
            #cameraContainer {
                position: relative;
                max-width: 400px;
                width: 100%;
                margin: 0 auto;
                border: 3px solid #007bff;
                border-radius: 15px;
                overflow: hidden;
                background: #000;
                box-shadow: 0 8px 20px rgba(0, 123, 255, 0.3);
            }

            #cameraContainer video {
                width: 100%;
                height: 250px;
                object-fit: cover;
                display: block;
            }

            .scanner-overlay {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 200px;
                height: 120px;
                border: 3px solid #ff0000;
                border-radius: 12px;
                box-shadow: 
                    0 0 20px rgba(255, 0, 0, 0.6),
                    inset 0 0 20px rgba(255, 0, 0, 0.1);
                animation: scannerPulse 2s infinite;
                z-index: 10;
            }

            .scanner-overlay::before {
                content: '';
                position: absolute;
                top: 50%;
                left: 0;
                right: 0;
                height: 2px;
                background: linear-gradient(90deg, transparent, #ff0000, transparent);
                animation: scanLine 2s infinite;
            }

            @keyframes scannerPulse {
                0% { 
                    border-color: #ff0000; 
                    box-shadow: 0 0 20px rgba(255, 0, 0, 0.6);
                }
                50% { 
                    border-color: #00ff00; 
                    box-shadow: 0 0 30px rgba(0, 255, 0, 0.8);
                }
                100% { 
                    border-color: #ff0000; 
                    box-shadow: 0 0 20px rgba(255, 0, 0, 0.6);
                }
            }

            @keyframes scanLine {
                0% { transform: translateY(-60px); opacity: 0; }
                50% { opacity: 1; }
                100% { transform: translateY(60px); opacity: 0; }
            }

            .camera-controls {
                text-align: center;
                margin: 15px 0;
            }

            .scanner-status {
                background: linear-gradient(45deg, #28a745, #20c997);
                color: white;
                padding: 12px;
                border-radius: 8px;
                margin: 15px 0;
                text-align: center;
                font-weight: bold;
                animation: statusPulse 1.5s infinite;
            }

            @keyframes statusPulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.7; }
            }

            .scanner-result {
                background: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
                padding: 15px;
                border-radius: 8px;
                margin: 15px 0;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            /* Responsivo para móviles */
            @media (max-width: 768px) {
                #cameraContainer {
                    max-width: 320px;
                }
                
                #cameraContainer video {
                    height: 200px;
                }
                
                .scanner-overlay {
                    width: 160px;
                    height: 100px;
                }
                
                .camera-controls .btn {
                    font-size: 14px;
                    padding: 8px 16px;
                }
            }

            @media (max-width: 480px) {
                #cameraContainer {
                    max-width: 280px;
                }
                
                #cameraContainer video {
                    height: 180px;
                }
                
                .scanner-overlay {
                    width: 140px;
                    height: 80px;
                }
            }

            /* Firefox */
            input[type=number] {
                -moz-appearance: textfield;
            }

            /* Estilos para el escáner de códigos de barras */
            .camera-card {
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                border: none;
                margin-bottom: 20px;
            }

            .camera-container {
                position: relative;
                width: 100%;
                max-width: 600px;
                margin: 0 auto;
            }

            .scanner-overlay {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 280px;
                height: 120px;
                border: 3px solid #dc3545;
                border-radius: 8px;
                background: rgba(220, 53, 69, 0.1);
                animation: scannerPulse 2s infinite;
                z-index: 10;
                pointer-events: none;
            }

            @keyframes scannerPulse {
                0%, 100% { 
                    opacity: 0.7; 
                    transform: translate(-50%, -50%) scale(1);
                }
                50% { 
                    opacity: 1; 
                    transform: translate(-50%, -50%) scale(1.02);
                }
            }

            #scanResult {
                margin-top: 15px;
                border-left: 4px solid #28a745;
            }

            #scannerStatus {
                margin-top: 15px;
            }

            .btn-lg {
                padding: 12px 24px;
                font-size: 16px;
                margin: 0 5px;
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
                                <!-- Scanner de Códigos de Barras -->
                                <div class="row mt-3">
                                    <div class="col-12 col-md-8 col-lg-6 mx-auto">
                                        <div class="card border-primary shadow-sm">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0 text-center">
                                                    <i class="fas fa-qrcode me-2"></i> Scanner de Códigos de Barras
                                                </h6>
                                            </div>
                                            <div class="card-body text-center">
                                                
                                                <!-- Botones de Control -->
                                                <div class="camera-controls mb-3">
                                                    <button id="btnStartCamera" class="btn btn-success btn-lg me-2">
                                                        <i class="fas fa-camera me-2"></i>Activar Cámara
                                                    </button>
                                                    <button id="btnStopCamera" class="btn btn-danger btn-lg" style="display:none;">
                                                        <i class="fas fa-stop me-2"></i>Detener
                                                    </button>
                                                </div>
                                                
                                                <!-- Contenedor de la Cámara -->
                                                <div class="d-flex justify-content-center mb-3">
                                                    <div id="cameraContainer" style="display:none;">
                                                        <video id="scanner-video" playsinline></video>
                                                    </div>
                                                </div>
                                                
                                                <!-- Estado del Scanner -->
                                                <div id="scannerStatus" class="scanner-status" style="display:none;">
                                                    <i class="fas fa-search me-2"></i>
                                                    <span>Escaneando... Enfoque el código en el recuadro rojo</span>
                                                </div>
                                                
                                                <!-- Resultado del Escaneo -->
                                                <div id="scanResult" class="scanner-result" style="display:none;">
                                                    <i class="fa fa-check-circle text-success me-2"></i>
                                                    <strong>Código escaneado:</strong>
                                                    <span id="barcodeResult" class="fw-bold text-primary ms-2"></span>
                                                </div>
                                                
                                                <!-- Instrucciones -->
                                                <div class="mt-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Coloque el código de barras dentro del recuadro rojo para escanearlo
                                                    </small>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

        <!-- Librería QuaggaJS para scanner de códigos de barras -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>

    @endpush
</div>
