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
            <h2>Crear Vale</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a>Código de Factura: {{ $datosFactura->numero_factura }}</a>
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
    <form onkeydown="return event.key != 'Enter';" autocomplete="off" id="crear_venta" name="crear_venta"
        data-parsley-validate>

        <input id="idFactura" name="idFactura" type="hidden" value="{{ $idFactura }}">
        <!------------------------------------------------------------DIV DE VALE--------------------------------------------------------------------------------------->
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="ibox ">
                <div class="ibox-title">
                    <h3>Datos de Vale <i class="fa-regular fa-calendar"></i></h3>

                </div>
                <div class="ibox-content">
                    <div class="row mt-4">
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 ">
                            <div class="form-group">
                                <label for="comentario">Comentario:<span class="text-danger">*</span></label>
                                <textarea class="form-control" id="comentario" name="comentario" rows="5" data-parsley-required></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row ">
                        <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 ">


                            <label for="seleccionarProductoVale" class="col-form-label focus-label">Seleccionar Producto
                                Para Vale:<span class="text-danger">*</span></label>
                            <select id="seleccionarProductoVale" name="seleccionarProductoVale"
                                class="form-group form-control" style="" onchange="obtenerImagenesVale()">
                                <option value="" selected disabled>--Seleccione un producto--</option>
                            </select>




                        </div>
                        <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 ">


                            <label for="porDescuento" class="col-form-label focus-label">Descuento aplicado %
                                <span class="text-danger">*</span></label>

                                <input class="form-control" type="number" value="{{ $datosFactura->porc_descuento }}" min="0" max="15" minlength="1" maxlength="2" id="porDescuento" name="porDescuento" data-parsley-required readonly>                           



                        </div>







                    </div>

                    <div class="row">


                        <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 mt-4">
                            <div class="text-center">
                                <a id="detalleProductoVale" href="" class="font-bold h3  d-none text-success"
                                    style="" target="_blank"> <i class="fa-solid fa-circle-info"></i> Ver Detalles
                                    De Producto </a>
                            </div>


                            <div id="carouselProductoVale" class="carousel slide mt-2" data-ride="carousel">
                                {{-- <ol  id="carousel_imagenes_producto" class="carousel-indicators">

                                        <li data-target="#carouselProducto" data-slide-to="{{ $i }}" class="active"></li>

                                        <li data-target="#carouselProducto" data-slide-to="{{ $i }}" class=""></li>



                                </ol> --}}
                                <div id="bloqueImagenesVale" class="carousel-inner ">






                                </div>
                                <a class="carousel-control-prev" href="#carouselProductoVale" role="button"
                                    data-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Previous</span>
                                </a>
                                <a class="carousel-control-next" href="#carouselProductoVale" role="button"
                                    data-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Next</span>
                                </a>
                            </div>


                        </div>

                        <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 ">
                            <div id="botonAddVale"
                                class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 my-4 text-center d-none">
                                <button type="button" class="btn-rounded btn btn-success p-3"
                                    style="font-weight: 900; " onclick="agregarProductoVale()">Añadir
                                    Producto a Vale </button>

                            </div>

                        </div>

                    </div>

                    <hr>

                    <div class="hide-container">
                        <p>Nota:El campo "Unidad" describe la unidad de medida para la venta del producto - seguido del
                            numero de unidades a restar del inventario</p>
                        <div class="row no-gutters ">

                            <div class="form-group col-12 col-sm-12 col-md-3 col-lg-3 col-xl-3">
                                <div class="d-flex">



                                    <div style="width:100%">
                                        <label class="sr-only">Nombre del
                                            producto</label>
                                        <input type="text" placeholder="Nombre del producto" class="form-control"
                                            pattern="[A-Z]{1}" disabled>
                                    </div>
                                </div>
                            </div>



                            <div class="form-group col-12 col-sm-12 col-md-1 col-lg- col-xl-1">
                                <label class="sr-only">Precio</label>
                                <input type="number" placeholder="Precio Unidad" class="form-control" min="1"
                                    autocomplete="off" disabled>
                            </div>

                            <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                <label class="sr-only">cantidad</label>
                                <input type="text" placeholder="Cantidad" class="form-control" min="1"
                                    autocomplete="off" disabled>
                            </div>

                            <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1 ">

                                <label class="sr-only">Unidad</label>
                                <input type="text" placeholder="Unidad " class="form-control" min="1"
                                    autocomplete="off" disabled>




                            </div>


                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                <label class="sr-only">Sub Total</label>
                                <input type="number" placeholder="Sub total del producto" class="form-control"
                                    min="1" autocomplete="off" disabled>
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

                    <div id="divProductosVale">

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
                            <button id="btn_venta_vale_coorporativo"
                                class="btn  btn-primary float-left m-t-n-xs"><strong>
                                    Guardar Vale</strong></button>
                        </div>
                    </div>
                </div>
            </div>

        </div>


    </form>
    @push('scripts')
        <script>
            var numeroInputsVP = 0;
            var arregloIdInputsVP = [];


            var retencionEstado = false; // true  aplica retencion, false no aplica retencion;


            var public_path = "{{ asset('catalogo/') }}";
            var diasCredito = 0;



            $('#seleccionarProductoVale').select2({
                ajax: {
                    type: "POST",
                    url: '/crear/vale/lista/espera/obtenerProductos',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
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






            function obtenerImagenesVale() {
                let id = document.getElementById('seleccionarProductoVale').value;


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

                            document.getElementById('bloqueImagenesVale').innerHTML = htmlImagenes;

                            let element = document.getElementById('botonAddVale');
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

                            document.getElementById('bloqueImagenesVale').innerHTML = htmlImagenes;
                            let element = document.getElementById('botonAddVale');
                            element.classList.remove("d-none");


                        }


                        let a = document.getElementById("detalleProductoVale");
                        let url = "/producto/detalle/" + id;
                        a.href = url;
                        a.classList.remove("d-none");

                        return;



                    })
                    .catch(err => {

                        console.log(err);

                    })


            }



            function agregarProductoVale() {
                let idProducto = document.getElementById('seleccionarProductoVale').value;

                axios.post('/ventas/datos/producto', {
                        idProducto: idProducto,

                    })
                    .then(response => {

                        let flag = false;
                        arregloIdInputsVP.forEach(idInpunt => {
                            let idProductoFila = document.getElementById("idProductoVP" + idInpunt).value;


                            if (idProducto == idProductoFila && !flag) {
                                flag = true;
                            }

                        })

                        if (flag) {
                            Swal.fire({

                                icon: 'warning',
                                title: 'Advertencia!',
                                html: `
                            <p class="text-left">
                                El producto ha sido agregado anteriormente.<br><br>
                                Por favor verificar que el producto sea distinto a los ya existentes en la lista de venta.<br><br>
                                De ser necesario aumentar la cantidad de producto en la lista de productos seleccionados para el vale.
                            </p>`
                            })

                            return;
                        }

                        let producto = response.data.producto;
                        let precio_base = new Intl.NumberFormat('es-HN').format(producto.precio_base);

                        let arrayUnidades = response.data.unidades;


                        numeroInputsVP += 1;

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


                        let htmlVP = `
                        <div id='VP${numeroInputsVP}' class="row no-gutters">
                                            <div class="form-group col-12 col-sm-12 col-md-3 col-lg-3 col-xl-3">
                                                <div class="d-flex">

                                                    <button class="btn btn-danger" type="button" style="display: inline" onclick="eliminarInputVP(${numeroInputsVP})"><i
                                                            class="fa-regular fa-rectangle-xmark"></i>
                                                    </button>

                                                    <input id="idProductoVP${numeroInputsVP}" name="idProductoVP${numeroInputsVP}" type="hidden" value="${producto.id}">

                                                    <div style="width:100%">
                                                        <label for="nombreVP${numeroInputsVP}" class="sr-only">Nombre del producto</label>
                                                        <input type="text" placeholder="Nombre del producto" id="nombreVP${numeroInputsVP}"
                                                            name="nombreVP${numeroInputsVP}" class="form-control"
                                                            data-parsley-required "
                                                            autocomplete="off"
                                                            readonly
                                                            value='${producto.nombre}'

                                                            >
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                                <label for="precioVP${numeroInputsVP}" class="sr-only">Precio</label>
                                                <input type="number" placeholder="Precio Unidad" id="precioVP${numeroInputsVP}"
                                                    name="precioVP${numeroInputsVP}" value="${producto.precio_base}" class="form-control"  data-parsley-required step="any"
                                                    autocomplete="off" min="${producto.precio_base}" onchange="calcularTotalesVP(precio${numeroInputsVP},cantidad${numeroInputsVP},${producto.isv},unidad${numeroInputsVP},${numeroInputsVP},restaInventario${numeroInputsVP})">
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                                <label for="cantidadVP${numeroInputsVP}" class="sr-only">cantidad</label>
                                                <input type="number" placeholder="Cantidad" id="cantidadVP${numeroInputsVP}"
                                                    name="cantidadVP${numeroInputsVP}" class="form-control" min="1" data-parsley-required
                                                    autocomplete="off" onchange="calcularTotalesVP(precioVP${numeroInputsVP},cantidadVP${numeroInputsVP},${producto.isv},unidadVP${numeroInputsVP},${numeroInputsVP},restaInventarioVP${numeroInputsVP})">
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                                <label for="" class="sr-only">unidad</label>
                                                <select class="form-control" name="unidadVP${numeroInputsVP}" id="unidadVP${numeroInputsVP}"
                                                    data-parsley-required style="height:35.7px;"
                                                    onchange="calcularTotalesVP(precioVP${numeroInputsVP},cantidadVP${numeroInputsVP},${producto.isv},unidadVP${numeroInputsVP},${numeroInputsVP},restaInventarioVP${numeroInputsVP})">
                                                            ${htmlSelectUnidades}
                                                </select>


                                            </div>




                                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                                <label for="subTotalMostrarVP${numeroInputsVP}" class="sr-only">Sub Total</label>
                                                <input type="text" placeholder="Sub total producto" id="subTotalMostrarVP${numeroInputsVP}"
                                                    name="subTotalMostrarVP${numeroInputsVP}" class="form-control"
                                                    autocomplete="off"
                                                    readonly >
                                                <input type="hidden" id="acumuladoDescuentoVP${numeroInputsVP}" name="acumuladoDescuentoVP${numeroInputsVP}" >
                                                <input id="subTotalVP${numeroInputsVP}" name="subTotalVP${numeroInputsVP}" type="hidden" value="" required>
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                                <label for="isvProductoMostrarVP${numeroInputsVP}" class="sr-only">ISV</label>
                                                <input type="text" placeholder="ISV" id="isvProductoMostrarVP${numeroInputsVP}"
                                                    name="isvProductoMostrarVP${numeroInputsVP}" class="form-control"
                                                    autocomplete="off"
                                                    readonly >

                                                    <input id="isvProductoVP${numeroInputsVP}" name="isvProductoVP${numeroInputsVP}" type="hidden" value="" required>
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                                <label for="totalMostrarVP${numeroInputsVP}" class="sr-only">Total</label>
                                                <input type="text" placeholder="Total del producto" id="totalMostrarVP${numeroInputsVP}"
                                                    name="totalMostrarVP${numeroInputsVP}" class="form-control"
                                                    autocomplete="off"
                                                    readonly >

                                                    <input id="totalVP${numeroInputsVP}" name="totalVP${numeroInputsVP}" type="hidden" value="" required>


                                            </div>


                                            <input id="restaInventarioVP${numeroInputsVP}" name="restaInventarioVP${numeroInputsVP}" type="hidden" value="" required>
                                            <input id="isvVP${numeroInputsVP}" name="isvVP${numeroInputsVP}" type="hidden" value="${producto.isv}">



                        </div>
                        `;

                        arregloIdInputsVP.splice(numeroInputsVP, 0, numeroInputsVP);
                        document.getElementById('divProductosVale').insertAdjacentHTML('beforeend', htmlVP);

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



            function eliminarInputVP(id) {
                const element = document.getElementById("VP" + id);
                element.remove();


                var myIndex = arregloIdInputsVP.indexOf(id);
                if (myIndex !== -1) {
                    arregloIdInputsVP.splice(myIndex, 1);
                    this.totalesGeneralesVP();
                }


            }



            function calcularTotalesVP(idPrecio, idCantidad, isvProducto, idUnidad, id, idRestaInventario) {

                let valorInputPrecio = Number(idPrecio.value).toFixed(2);
                let valorInputCantidad = idCantidad.value;
                let valorSelectUnidad = idUnidad.value;

                let subTotal = 0;
                let descuentoCalculado= 0;
                let isv=0;
                let total=0;
                

                if (valorInputPrecio && valorInputCantidad) {

                                let descuento = document.getElementById("porDescuento").value


                                if (descuento > 0){
                                     subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                                     descuentoCalculado = subTotal * (descuento/100);

                                     document.getElementById("acumuladoDescuentoVP"+id).value=descuentoCalculado                                  

                                     subTotal = subTotal - descuentoCalculado;
                                     isv = subTotal * (isvProducto / 100);
                                     total = subTotal + (subTotal * (isvProducto / 100));


                                }else{
                                     document.getElementById("acumuladoDescuentoVP"+id).value=0
                                     subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                                     isv = subTotal * (isvProducto / 100);
                                     total = subTotal + subTotal * (isvProducto / 100);

                                }

                    // let subTotalVP = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                    // let isv = subTotalVP * (isvProducto / 100);
                    // let total = subTotalVP + subTotalVP * (isvProducto / 100);

                    document.getElementById('totalVP' + id).value = total.toFixed(2);
                    document.getElementById('totalMostrarVP' + id).value = new Intl.NumberFormat('es-HN', {
                        style: 'currency',
                        currency: 'HNL',
                        minimumFractionDigits: 2,
                    }).format(total)

                    document.getElementById('subTotalVP' + id).value = subTotal.toFixed(2);

                    document.getElementById('subTotalMostrarVP' + id).value = new Intl.NumberFormat('es-HN', {
                        style: 'currency',
                        currency: 'HNL',
                        minimumFractionDigits: 2,
                    }).format(subTotal)


                    document.getElementById('isvProductoVP' + id).value = isv.toFixed(2);
                    document.getElementById('isvProductoMostrarVP' + id).value = new Intl.NumberFormat('es-HN', {
                        style: 'currency',
                        currency: 'HNL',
                        minimumFractionDigits: 2,
                    }).format(isv)


                    idRestaInventario.value = valorInputCantidad * valorSelectUnidad;
                    this.totalesGeneralesVP();



                }


                return 0;


            }


            function totalesGeneralesVP() {

             //console.log(arregloIdInputsVP);

             if (numeroInputsVP == 0) {
                    return;
                }



                let totalGeneralValor = new Number(0);
                let totalISV = new Number(0);
                let subTotalGeneralGrabadoValor = new Number(0);
                let subTotalGeneralExcentoValor = new Number(0);
                let subTotalGeneral = new Number(0);
                let subTotalFila = 0;
                let isvFila = 0;
                let acumularDescuento = 0;

                for (let i = 0; i < arregloIdInputsVP.length; i++) {

                    subTotalFila = new Number(document.getElementById('subTotalVP' + arregloIdInputsVP[i]).value);
                    isvFila = new Number(document.getElementById('isvProductoVP' + arregloIdInputsVP[i]).value);

                    ;

                    if (isvFila == 0) {
                        subTotalGeneralExcentoValor += new Number(document.getElementById('subTotalVP' + arregloIdInputsVP[i])
                            .value);
                    } else if (subTotalFila > 0) {
                        subTotalGeneralGrabadoValor += new Number(document.getElementById('subTotalVP' + arregloIdInputsVP[i])
                            .value);
                    }

                    subTotalGeneral += new Number(document.getElementById('subTotalVP' + arregloIdInputsVP[i]).value);
                    


                    totalISV += new Number(document.getElementById('isvProductoVP' + arregloIdInputsVP[i]).value);
                    totalGeneralValor += new Number(document.getElementById('totalVP' + arregloIdInputsVP[i]).value);                    
                    acumularDescuento += new Number(document.getElementById('acumuladoDescuentoVP' + arregloIdInputsVP[i]).value);

                }

                
                document.getElementById('descuentoGeneral').value = acumularDescuento.toFixed(2);
                document.getElementById('descuentoMostrar').value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(acumularDescuento)

                document.getElementById('subTotalGeneralMostrar').value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(subTotalGeneral)

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



            $(document).on('submit', '#crear_venta',
                function(event) {
                    event.preventDefault();
                    guardarVenta();
                });

            function guardarVenta() {

                document.getElementById("btn_venta_vale_coorporativo").disabled = true;

                let data = new FormData($('#crear_venta').get(0));



                let longitudArregloVP = arregloIdInputsVP.length;
                for (var i = 0; i < longitudArregloVP; i++) {


                    let name = "unidadVP" + arregloIdInputsVP[i];
                    let nameForm = "idUnidadVentaVP" + arregloIdInputsVP[i];

                    let e = document.getElementById(name);
                    let idUnidadVenta = e.options[e.selectedIndex].getAttribute("data-id");

                    data.append(nameForm, idUnidadVenta)
                }



                data.append("numeroInputsVP", numeroInputsVP);


                let text = arregloIdInputsVP.toString();
                data.append("arregloIdInputsVP", text);

                const formDataObj = {};

                    data.forEach((value, key) => (formDataObj[key] = value));


                    const options = {
                        headers: {"content-type": "application/json"}
                    }


                axios.post('/vale/lista/espera/guardar', formDataObj,options)
                    .then(response => {
                        let data = response.data;



                        if (data.idFactura == 0) {
                            // console.log("entro")

                            Swal.fire({
                                icon: data.icon,
                                title: data.title,
                                html: data.text,
                                confirmButtonColor: "#18A689"
                            })
                            document.getElementById("btn_venta_vale_coorporativo").disabled = false;
                            return;

                        }


                        Swal.fire({
                            confirmButtonText: 'Cerrar',
                            confirmButtonColor: '#18A689',
                            icon: data.icon,
                            title: data.title,
                            html: data.text
                        })

                        if(data.estadoBorrar == true){
                            document.getElementById("btn_venta_vale_coorporativo").disabled = false;
                            return
                        }



                        document.getElementById('bloqueImagenesVale').innerHTML = '';
                        document.getElementById('divProductosVale').innerHTML = '';

                        document.getElementById("crear_venta").reset();
                        $('#crear_venta').parsley().reset();



                        let element2 = document.getElementById('detalleProductoVale');
                        element2.classList.add("d-none");
                        element2.href = "";



                        arregloIdInputsVP = [];
                        numeroInputsVP = 0;



                        document.getElementById("btn_venta_vale_coorporativo").disabled = false;


                        document.getElementById('botonAddVale').classList.add("d-none");

                    })
                    .catch(err => {
                        document.getElementById("btn_venta_vale_coorporativo").disabled = false;
                        console.log(err);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: "Ha ocurrido un error al intentar crear el vale."
                        })
                    })
            }
        </script>
    @endpush
</div>
<?php
    date_default_timezone_set('America/Tegucigalpa');
    $act_fecha=date("Y-m-d");
    $act_hora=date("H:i:s");
    $mes=date("m");
    $year=date("Y");
    $datetim=$act_fecha." ".$act_hora;
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
    <?php echo "$act_fecha";  ?> <strong id="reloj"></strong>
</div>
<div>
    <strong>Copyright</strong> Distribuciones Valencia &copy; <?php echo "$year";  ?>
</div>
<p id="reloj"></p>
