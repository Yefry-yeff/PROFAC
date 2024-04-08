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
            var numeroInputs = 0;
            var arregloIdInputs = [];
            var idProductoArray = [];
            //var retencionEstado = false;  true  aplica retencion, false no aplica retencion;

            window.onload = obtenerTipoPago;
            var public_path = "{{ asset('catalogo/') }}";

            function obtenerTipoPago() {

                axios.get('/producto/tipo/pagos')
                    .then(response => {

                        let tipoDePago = response.data.tipos;

                        let htmlPagos = '  <option value="" selected disabled >--Seleccione una opcion--</option>';

                        tipoDePago.forEach(element => {

                            htmlPagos += `
                            <option value="${element.id}" >${element.descripcion}</option>
                            `
                        });

                        document.getElementById('tipoPagoCompra').innerHTML = htmlPagos;

                    })
                    .catch(err => {
                        //console.log(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error...',
                            text: "Ha ocurrido un error al obtener los tipos de pago"
                        })
                    })

            }

            $('#seleccionarProveedor').select2({
                ajax: {
                    url: '/producto/lista/proveedores',
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



            $('#seleccionarProdcuto').select2({
                ajax: {
                    url: '/producto/listar/producto',
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

            function obtenerIdProducto() {
                let id = document.getElementById('seleccionarProdcuto').value;

                this.obtenerImagenes(id)
            }

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

            function agregarProductoCarrito() {


                let id = document.getElementById('seleccionarProdcuto').value;

                let idBuscarProducto = idProductoArray.find(element => element == id);

                if(idBuscarProducto){
                    Swal.fire({
                            icon: 'warning',
                            title: 'Advertencia!',
                            text: "Este producto ya ha sido agregado al carrito de compra. No se permite duplicidad de producto."
                        })

                        return;
                }

                axios.post('/prodcuto/compra/datos', {
                        id: id
                    })
                    .then(response => {
                        let producto = response.data.producto;
                        //console.log(response.data.producto);
                        numeroInputs += 1;

                        html = `
                        <div id='${numeroInputs}' class="row no-gutters">
                                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                                <div class="d-flex">

                                                    <button class="btn btn-danger" type="button" style="display: inline" onclick="eliminarInput(${numeroInputs},${id})"><i
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
                                                <label for="precio${numeroInputs}" class="sr-only">Precio</label>
                                                <input type="number" placeholder="Precio Unidad" id="precio${numeroInputs}"
                                                    name="precio${numeroInputs}" class="form-control" min="0" data-parsley-required step="any"
                                                    autocomplete="off" onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},${numeroInputs}, ${producto.unidadad_compra})">
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                                <label for="cantidad${numeroInputs}" class="sr-only">cantidad</label>
                                                <input type="number" placeholder="Cantidad" id="cantidad${numeroInputs}"
                                                    name="cantidad${numeroInputs}" class="form-control" min="0" data-parsley-required
                                                    autocomplete="off" onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},${numeroInputs}, ${producto.unidadad_compra})">
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                                <label for="unidad${numeroInputs}" class="sr-only">Unidad</label>
                                                <input value="${producto.unidad}" type="text" placeholder="Unidad" id="unidad${numeroInputs}"
                                                    name="unidad${numeroInputs}" class="form-control" data-parsley-required
                                                    autocomplete="off" readonly>
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                                <label for="vencimiento${numeroInputs}" class="sr-only">cantidad</label>
                                                <input type="date" placeholder="Fecha de vencimiento" id="vencimiento${numeroInputs}"
                                                    name="vencimiento${numeroInputs}" class="form-control" min="0"
                                                    autocomplete="off" >
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                                <label for="subTotal${numeroInputs}" class="sr-only">Sub Total</label>
                                                <input type="number" placeholder="Sub total producto" id="subTotal${numeroInputs}"
                                                    name="subTotal${numeroInputs}" class="form-control" min="0" step="any"
                                                    autocomplete="off"
                                                    readonly >
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                                <label for="isvProducto${numeroInputs}" class="sr-only">ISV</label>
                                                <input type="number" placeholder="ISV" id="isvProducto${numeroInputs}"
                                                    name="isvProducto${numeroInputs}" class="form-control" min="0" step="any"
                                                    autocomplete="off"
                                                    readonly >
                                            </div>

                                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                                <label for="total${numeroInputs}" class="sr-only">Total</label>
                                                <input type="number" placeholder="Total del producto" id="total${numeroInputs}"
                                                    name="total${numeroInputs}" class="form-control" min="1"  step="any"
                                                    autocomplete="off"
                                                    readonly >
                                            </div>

                                            <input id="unidadesCompra${numeroInputs}" name="unidadesCompra${numeroInputs}" type="hidden" value="${producto.unidadad_compra}">
                                            <input id="medidaCompraId${numeroInputs}" name="medidaCompraId${numeroInputs}" type="hidden" value="${producto.unidad_medida_compra_id}">


                        </div>
                        `;

                        arregloIdInputs.splice(numeroInputs, 0, numeroInputs);
                        idProductoArray .splice(producto.id,0,producto.id);

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

            function eliminarInput(id,idProducto) {
                const element = document.getElementById(id);
                element.remove();

                console.log(idProductoArray);
                var myIndex = arregloIdInputs.indexOf(id);
                if (myIndex !== -1) {
                    arregloIdInputs.splice(myIndex, 1);
                    this.totalesGenerales();
                }

                var myIndex2 = idProductoArray.indexOf(idProducto);
                if (myIndex2 !== -1) {
                    idProductoArray.splice(myIndex2, 1);

                }

                console.log(idProductoArray);


            }

            function calcularTotales(idPrecio, idCantidad, isvProducto, id, unidadad_compra) {

                let valorInputPrecio = Number(idPrecio.value).toFixed(2);
                let valirInputCantidad = idCantidad.value;
   

                if (valorInputPrecio && valirInputCantidad) {

                    let subTotal = valorInputPrecio * (valirInputCantidad*unidadad_compra);
                    let isv = subTotal * (isvProducto / 100);
                    let total = subTotal + subTotal * (isvProducto / 100);

                    document.getElementById('subTotal' + id).value = subTotal.toFixed(2);
                    document.getElementById('total' + id).value = total.toFixed(2);
                    document.getElementById('isvProducto' + id).value = isv.toFixed(2);

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
                let subTotalGeneralValor = new Number(0);


                for (let i = 0; i < arregloIdInputs.length; i++) {
                    subTotalGeneralValor += new Number(document.getElementById('subTotal' + arregloIdInputs[i]).value);
                    totalISV += new Number(document.getElementById('isvProducto' + arregloIdInputs[i]).value);
                    totalGeneralValor += new Number(document.getElementById('total' + arregloIdInputs[i]).value);

                }

                document.getElementById('subTotalGeneral').value = subTotalGeneralValor.toFixed(2);
                document.getElementById('isvGeneral').value = totalISV.toFixed(2);
                document.getElementById('totalGeneral').value = totalGeneralValor.toFixed(2);





                return 0;


            }

            function validarFechaPago() {

                let tipoPago;

                tipoPago = document.getElementById('tipoPagoCompra').value;

                if (tipoPago == 1) {

                    document.getElementById('fecha_vencimiento').value = "Empty";
                    document.getElementById('fecha_vencimiento').readOnly = false;


                } else {
                    document.getElementById('fecha_vencimiento').value = "{{ date('Y-m-d') }}";

                }

                return 0;


            }

            // function retencionProveedor() {
            //     let idProveedor = document.getElementById('seleccionarProveedor').value;
            //     axios.post('/producto/compra/retencion', {
            //             idProveedor: idProveedor
            //         })
            //         .then(response => {
            //             let data = response.data;




            //             Swal.fire({
            //                 title: data.title,
            //                 text: data.text,
            //                 showDenyButton: true,
            //                 showCancelButton: true,
            //                 confirmButtonText: 'Aplicar',
            //                 denyButtonText: `No aplicar`,
            //                 cancelButtonText: 'Cancelar',
            //             }).then((result) => {
            //                 /* Read more about isConfirmed, isDenied below */
            //                 if (result.isConfirmed) {
            //                     Swal.fire('La retencion sera aplicada a esta compra!', '', 'success')
            //                     retencionEstado = true;
            //                     this.totalesGenerales();
            //                 } else if (result.isDenied) {
            //                     Swal.fire('¡No se aplicara retencion a esta compra!', '', 'info')
            //                     retencionEstado = false;
            //                     this.totalesGenerales();
            //                 }
            //             })



            //         })
            //         .catch(err => {

            //             Swal.fire({
            //                 icon: 'error',
            //                 title: 'Error!',
            //                 text: "Ha ocurrido un error al seleccionar el proveedor."
            //             })

            //             console.error('ha ocurrido un erro', err)

            //         })
            // }

            $(document).on('submit', '#crear_compra',
                function(event) {
                    event.preventDefault();
                    guardarCompra();
                });

            function guardarCompra() {
                // if (!retencionEstado) {
                //     document.getElementById('retencion').value = 0;
                // }

                var data = new FormData($('#crear_compra').get(0));

                // let longitudArreglo = arregloIdInputs.length;
                // for (var i = 0; i < longitudArreglo; i++) {
                //     data.append("arregloIdInputs[]", arregloIdInputs[i]);
                // }

                data.append("numeroInputs", numeroInputs);

                let seleccionarProveedorId = document.getElementById('seleccionarProveedor').value;
                data.append("seleccionarProveedorId", seleccionarProveedorId);

                let text = arregloIdInputs.toString();
                data.append("arregloIdInputs", text);

                const formDataObj = {};

                    data.forEach((value, key) => (formDataObj[key] = value));


                    const options = {
                        headers: {"content-type": "application/json"}
                    }

                axios.post('/producto/compra/guardar', formDataObj,options)
                    .then(response => {


                        Swal.fire({
                            icon: 'success',
                            title: 'Exito!',
                            text: "Compra realizada con exito."
                        })

                        //console.log(arregloIdInputs);



                        // for (let i = 0; i < arregloIdInputs.length; i++) {
                        //     const element = document.getElementById(arregloIdInputs[i]);
                        //      element.remove();

                        //     console.log(i,arregloIdInputs[i])
                        // }

                        document.getElementById('bloqueImagenes').innerHTML = '';
                        document.getElementById('divProductos').innerHTML='';

                        document.getElementById("crear_compra").reset();
                        $('#crear_compra').parsley().reset();



                        var element2 = document.getElementById('botonAdd');
                            element2.classList.add("d-none");


                        arregloIdInputs = [];
                        numeroInputs = 0;
                        // retencionEstado=false;

                    })
                    .catch(err => {

                        console.log(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: "Ha ocurrido un error!."
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
