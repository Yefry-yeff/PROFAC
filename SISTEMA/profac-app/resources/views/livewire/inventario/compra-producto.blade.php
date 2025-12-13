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

        </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Compras</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a>Realizar Compra</a>
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
                        <form autocomplete="off" id="crear_compra" name="crear_compra" data-parsley-validate>
                            <div class="row">

                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                    <label class="col-form-label text-danger" for="numero_emision"
                                        style="font-size: 1rem; font-weight:600;">Numero de compra</label>
                                        <input class="form-control" type="text" id="numero_emision" name="numero_emision"
                                        value="{{ $ordenNumero->numero  }}" data-parsley-required readonly>
                                </div>
                            </div>

                            <div class="row mt-4">

                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                    <label class="col-form-label" for="numero_factura"
                                       >Número de factura<span class="text-danger">*</span></label>

                                    <input class="form-control" type="text" id="numero_factura" name="numero_factura"
                                    data-parsley-required placeholder="N° Factura">
                                </div>


                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                    <label class="col-form-label" for="cai"
                                        >Codigo CAI<span class="text-danger">*</span></label>

                                    <input class="form-control" type="text" id="cai" name="cai"
                                    data-parsley-required placeholder="Codigo CAI">
                                </div>

                            </div>

                            <div class="row mt-4">
                                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                    <label for="seleccionarProveedor" class="col-form-label focus-label">Seleccionar
                                        Proveedor:<span class="text-danger">*</span></label>
                                    <select id="seleccionarProveedor" class="form-group form-control" style=""
                                        data-parsley-required >
                                        <option value="" selected disabled>--Seleccionar un proveedor--</option>
                                    </select>
                                </div>

                                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                    <label for="tipoPagoCompra" class="col-form-label focus-label">Seleccionar tipo de
                                        pago:<span class="text-danger">*</span></label>
                                    <select class="form-group form-control " name="tipoPagoCompra" id="tipoPagoCompra"
                                        data-parsley-required onchange="validarFechaPago()">
                                    </select>
                                </div>


                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">
                                        <label for="fecha_vencimiento"
                                            class="col-form-label focus-label text-warning">Fecha de vencimiento:<span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="date" id="fecha_vencimiento"
                                            name="fecha_vencimiento" value="" data-parsley-required
                                            min="{{ date('Y-m-d') }}" readonly>
                                    </div>
                                </div>


                            </div>

                            <div class="row">
                                <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                                    <div class="form-group">

                                        <label for="fecha_emision" class="col-form-label focus-label">Fecha de emisión:<span class="text-danger">*</span></label>
                                        <input class="form-control" type="date" id="fecha_emision"
                                            name="fecha_emision" value="{{ date('Y-m-d') }}" data-parsley-required>

                                    </div>
                                </div>
                                <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                                    <div class="form-group">

                                        <label for="fecha_entrega" class="col-form-label focus-label">Fecha de
                                            recibido:<span class="text-danger">*</span></label>
                                        <input class="form-control" type="date" id="fecha_entrega"
                                            name="fecha_entrega" value="" data-parsley-required
                                            min="{{ date('Y-m-d') }}">

                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 mt-2">

                                    <div class="row">
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                            <select id="seleccionarProdcuto" class="form-group form-control" style=""
                                                onchange="obtenerIdProducto()">
                                                <option value="" selected disabled>--Seleccione un producto--</option>
                                            </select>
                                        </div>

                                        <div id="botonAdd"
                                            class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 my-4 text-center d-none">
                                            <button type="button" class="btn-rounded btn btn-success p-3"
                                                style="font-weight: 900; " onclick="agregarProductoCarrito()">Añadir
                                                Producto a Compra <i class="fa-solid fa-cart-plus"></i> </button>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">

                                    <div id="carouselProducto" class="carousel slide" data-ride="carousel">
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

                            </div>
                            <hr>

                            <div class="hide-container">
                                <p>Nota: Él campo "Unidad" describe la unidad de medida para la compra del producto - seguido del número de unidades a sumar en el inventario.</p>
                                <div class="row no-gutters ">
                                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                        <div class="d-flex">



                                            <div style="width:100%">
                                                <label class="sr-only">Nombre del
                                                    producto</label>
                                                <input type="text" placeholder="Nombre del producto"
                                                    class="form-control" pattern="[A-Z]{1}"
                                                    data-parsley-pattern="[A-Z]{1}" autocomplete="off" disabled>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                        <label class="sr-only">Precio Unidad</label>
                                        <input type="text" placeholder="Precio Unidad" class="form-control"
                                            min="1" autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                        <label class="sr-only">cantidad</label>
                                        <input type="text" placeholder="Cantidad" class="form-control" min="1"
                                            autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                        <label class="sr-only">Unidad</label>
                                        <input type="text" placeholder="Unidad" class="form-control" min="1"
                                            autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                        <label class="sr-only">F.vencimiento</label>
                                        <input type="text" placeholder="Fecha de expiración" class="form-control"
                                            min="1" autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                        <label class="sr-only">Sub Total</label>
                                        <input type="number" placeholder="Sub total del producto" class="form-control"
                                            min="1" autocomplete="off" disabled>
                                    </div>

                                    <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                        <label class="sr-only">ISV</label>
                                        <input type="number" placeholder="ISV" class="form-control" min="0"
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

                            </div>




                            <hr>
                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="subTotalGeneral">Sub Total L.<span class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="number" step="any" placeholder="Sub total " id="subTotalGeneral"
                                        name="subTotalGeneral" class="form-control" min="1" data-parsley-required
                                        autocomplete="off" readonly>
                                </div>
                            </div>

                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="isvGeneral">ISV L.<span class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="number" step="any" placeholder="ISV " id="isvGeneral" name="isvGeneral"
                                        class="form-control" min="0" data-parsley-required autocomplete="off"
                                        readonly>
                                </div>
                            </div>

                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="totalGeneral">Total L.<span class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="number" step="any" placeholder="Total  " id="totalGeneral"
                                        name="totalGeneral" class="form-control" min="1" data-parsley-required
                                        autocomplete="off" readonly>
                                </div>
                            </div>

                            <div class="row">



                                {{-- <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="number" step="any" placeholder="Retencion del 1%" id="retencion"
                                        name="retencion" class="form-control" min="0" required data-parsley-required
                                        autocomplete="off" readonly>
                                </div> --}}
                            </div>

                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <button class="btn btn-sm btn-primary float-left m-t-n-xs"><strong>Guardar
                                            compra</strong></button>
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
            var public_path = "{{ asset('catalogo/') }}";

            function obtenerImagenes(id) {
                let htmlImagenes = '';
                axios.post('/producto/listar/imagenes', {
                        id: id
                    })
                    .then(response => {

                        let imagenes = response.data.imagenes;

                        if (imagenes.length == 0) {

                           // console.log("entro")
                            htmlImagenes += `
                            <div class="carousel-item active " >
                                <img class="d-block  " src="${public_path+'/'+'noimage.png'}" alt="noimage.png" style="width: 100%; height:20rem" >
                            </div>`

                            document.getElementById('bloqueImagenes').innerHTML = htmlImagenes;

                            var element = document.getElementById('botonAdd');
                            element.classList.remove("d-none");

                        } else {





                            imagenes.forEach(element => {



                                if (element.contador == 1) {
                                    htmlImagenes += `
                            <div class="carousel-item active " >
                                <img class="d-block  " src="${public_path+'/'+element.url_img}" alt="imagen ${element.contador}" style="width: 100%; height:20rem" >
                            </div>`
                                } else {

                                    htmlImagenes += `
                            <div class="carousel-item  " >
                                <img class="d-block  " src="${public_path+'/'+element.url_img}" alt="imagen ${element.contador}" style="width: 100%; height:20rem" >
                            </div>`

                                }




                            });

                            document.getElementById('bloqueImagenes').innerHTML = htmlImagenes;

                            var element = document.getElementById('botonAdd');
                            element.classList.remove("d-none");
                        }

                        return;



                    })
                    .catch(err => {

                        console.log(err);

                    })
            }

        </script>

    <script src="{{ asset('js/js_proyecto/inventario/compra-producto.js') }}"></script>
    @endpush
</div>
