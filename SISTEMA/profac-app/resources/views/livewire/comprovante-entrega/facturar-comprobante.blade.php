<div>
    @push('styles')
        <style>


        </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Facturar Orden De Entrega</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">


                    <a>Cliente Corporativo</a>



                </li>
                {{-- <li class="breadcrumb-item">
                    <a data-toggle="modal" data-target="#modal_producto_crear">Registrar</a>
                </li> --}}

            </ol>
        </div>
    </div>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Datos de Comprobante <i class="fa-solid fa-cart-shopping"></i></h3>
                    </div>
                    <div class="ibox-content">
                        <form onkeydown="return event.key != 'Enter';" autocomplete="off" id="crear_venta"
                            name="crear_venta" data-parsley-validate>

                            <input name="restriccion" id="restriccion" type="hidden" value="1">
                            <input name="idComprobante" id="idComprobante" type="hidden" value="{{ $idComprobante }}">
                            <input name="ordenCompra" id="ordenCompra" type="hidden" value="">

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
                                        class="form-group form-control" style="" data-parsley-required readonly>
                                        <option value="{{ $comprobante->cliente_id }}" selected>
                                            {{ $comprobante->nombre_cliente }}</option>
                                    </select>
                                </div>

                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label class="col-form-label focus-label">Nombre del cliente:<span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" required type="text" id="nombre_cliente_ventas"
                                        name="nombre_cliente_ventas" value="{{ $comprobante->nombre_cliente }}"
                                        data-parsley-required readonly>

                                </div>

                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label class="col-form-label focus-label">RTN:<span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="rtn_ventas" name="rtn_ventas"
                                        value="{{ $comprobante->RTN }}" readonly>

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
                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">

                                        <label for="porDescuento" class="col-form-label focus-label">Descuento
                                            aplicado %
                                            :<span class="text-danger">*</span></label>

                                        <input class="form-control" type="number" min="0" max="15"
                                            value="{{ $comprobante->porc_descuento }}" id="porDescuento"
                                            name="porDescuento" onchange="calcularTotalesInicioPagina()"
                                            data-parsley-required>



                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <div class="form-group">
                                        <label for="nota" class="col-form-label focus-label">Nota:
                                        </label>
                                        <textarea class="form-control" id="nota_comen" name="nota_comen"  cols="30" rows="3" maxlength="220">{{$comprobante->comentario}}</textarea>
                                    </div>

                                </div>


                            </div>


                            <hr>

                            <div class="hide-container">
                                <p><b>Nota:</b>El campo "Unidad" describe la unidad de medida para la venta del producto
                                    -
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
                                         data-parsley-required
                                        autocomplete="off" readonly>

                                    <input type="hidden" 
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
                                    <button id="facturar_comprobante_btn"
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
            var numeroInputs = {{ $numeroInputs }};
            var arregloIdInputs = {{ json_encode($arrayInputs) }};
            var diasCredito = {{ $comprobante->dias_credito }};
            var urlGuardarVenta = "{{ $urlFactura }} "

            for (let i = 0; i < arregloIdInputs.length; i++) {
                arregloIdInputs[i] = Number(arregloIdInputs[i]);

            }

            $(document).ready(function() {
                obtenerTipoPago();
                totalesGenerales();
                calcularTotalesInicioPagina()
            });



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

            function eliminarInput(id) {
                const element = document.getElementById(id);
                element.remove();


                var myIndex = arregloIdInputs.indexOf(id);
                if (myIndex !== -1) {
                    arregloIdInputs.splice(myIndex, 1);
                    this.totalesGenerales();
                }



            }

            function calcularTotales(idPrecio, idCantidad, isvProducto, idUnidad, id, idRestaInventario) {

                let valorInputPrecio = Number(idPrecio.value).toFixed(2);
                let valorInputCantidad = idCantidad.value;
                let valorSelectUnidad = idUnidad.value;

                if (valorInputPrecio && valorInputCantidad) {

                    let descuento = document.getElementById('porDescuento').value;
                    let subTotal = 0;
                    let isv = 0;
                    let total = 0;


                    if (descuento > 0) {
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


                    /* CAMBIO 20230725 toFixed(3):minimumFractionDigits: 2*/
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

            function sumarDiasCredito() {
                tipoPago = document.getElementById('tipoPagoVenta').value;

                if (tipoPago == 2) {

                    let fechaEmision = document.getElementById("fecha_emision").value;
                    let date = new Date(fechaEmision);
                    date.setDate(date.getDate() + diasCredito);
                    let suma = date.toISOString().split('T')[0];
                    //console.log( diasCredito);

                    document.getElementById("fecha_vencimiento").value = suma;

                }
            }

            $(document).on('submit', '#crear_venta',
                function(event) {
                    event.preventDefault();
                    guardarVenta();
                });

            function guardarVenta() {

                document.getElementById("facturar_comprobante_btn").disabled = true;

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

                // let seleccionarCliente = document.getElementById('seleccionarCliente').value;
                // data.append("seleccionarCliente", seleccionarCliente);

                axios.post(urlGuardarVenta, data)
                    .then(response => {
                        let data = response.data;



                        if (data.idFactura == 0) {


                            Swal.fire({
                                icon: data.icon,
                                title: data.title,
                                html: data.text,
                            })
                            document.getElementById("facturar_comprobante_btn").disabled = false;
                            return;

                        }

                        Swal.fire({
                            confirmButtonText: 'Cerrar',
                            confirmButtonColor: '#5A6268',
                            icon: data.icon,
                            title: data.title,
                            html: data.text
                        })


                        // document.getElementById('bloqueImagenes').innerHTML = '';
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



                        let element2 = document.getElementById('detalleProducto');
                        element2.classList.add("d-none");


                        arregloIdInputs = [];
                        numeroInputs = 0;
                        retencionEstado = false;


                        document.getElementById("facturar_comprobante_btn").disabled = false;

                    })
                    .catch(err => {
                        document.getElementById("facturar_comprobante_btn").disabled = false;
                        let data = err.response.data;
                        console.log(err);
                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text
                        })
                    })
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
