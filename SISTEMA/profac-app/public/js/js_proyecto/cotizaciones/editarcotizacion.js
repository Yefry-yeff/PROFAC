
var array = [];
var arregloIdInputs = [];



var retencionEstado = false; // true  aplica retencion, false no aplica retencion;


window.onload = obtenerTipoPago;

var public_path = "{{ asset('catalogo/') }}";

// for (let i = 0; i < arregloIdInputsTemporal.length; i++) {

//     if(!isNaN(arregloIdInputsTemporal[i]) ){
//         arregloIdInputs.push(arregloIdInputsTemporal[i])
//     }

// }

const searchRegExp = /\"/g;

arregloIdInputsTemporal = arregloIdInputsTemporal.replace(searchRegExp, '')
arregloIdInputs = arregloIdInputsTemporal.split(",");

for (let z = 0; z < arregloIdInputs.length; z++) {
    arregloIdInputs[z] = parseInt(arregloIdInputs[z]);

}


calcularTotalesInicioPagina();

function validarDescuento() {
    const numeroInput = document.getElementById('porDescuento');
    const mensajeError = document.getElementById('mensajeError');
    const numero = parseFloat(numeroInput.value);

    if (isNaN(numero) || numero < 0 || numero > 25) {
        mensajeError.textContent = 'Este campo solo admite un valor entre 0 a 100';
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
        url: '/estatal/lista/clientes',
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




$('#seleccionarProducto').select2({
    ajax: {
        url: '/ventas/listar',
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

function prueba() {

    var element = document.getElementById('botonAdd');
    element.classList.remove("d-none");

}

function obtenerBodegas(id) {

    document.getElementById('bodega').innerHTML = "<option  selected disabled>--Seleccione una bodega--</option>";
    let idProducto = id;
    $('#bodega').select2({
        ajax: {
            url: '/estatal/listar/bodegas/' + idProducto,
            data: function(params) {
                var query = {
                    search: params.term,
                    type: 'public',
                    page: params.page || 1,
                    idProducto: idProducto
                }

                // Query parameters will be ?search=[term]&type=public
                return query;
            }
        }
    });

}


function obtenerTipoPago() {

    axios.get('/estatal/tipo/pago')
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
            document.getElementById("numero_venta").value = numeroVenta;
            this.obtenerOrdenesCompra();

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
                    <img class="d-block  " src="${public_path+'/'+element.url_img}" alt="imagen ${element.contador}" style="width: 100%; height:30rem" >
                </div>`
                    } else {

                        htmlImagenes += `
                <div class="carousel-item  " >
                    <img class="d-block  " src="${public_path+'/'+element.url_img}" alt="imagen ${element.contador}" style="width: 100%; height:30rem" >
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
    let idCliente = document.getElementById('seleccionarCliente').value;

    let data = $("#bodega").select2('data')[0];
    let bodega = data.bodegaSeccion;
    let idBodega = data.idBodega;
    let idSeccion = data.id


    axios.post('/ventas/datos/producto', {
            idProducto: idProducto,
            idCliente: idCliente

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

            /*SE QUITO min="${producto.precio_base}" DE LA LINEA 868*/

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
                                                    name="cantidad${numeroInputs}" class="form-control" min="${producto.precio1}" data-parsley-required
                                        autocomplete="off"  onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                    <label for="cantidad${numeroInputs}" class="sr-only">cantidad</label>
                                    <input type="number" placeholder="Cantidad" id="cantidad${numeroInputs}"
                                        name="cantidad${numeroInputs}" class="form-control" min="1" data-parsley-required
                                        autocomplete="off"  onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
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
                                        <input type="hidden" id="acumuladoDescuento${numeroInputs}" name="acumuladoDescuento${numeroInputs}" value="" >
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

    let descuentoCalculado = 0;

    arrayInputs.forEach(id => {
        // calcularTotales(idPrecio, idCantidad, isvProducto, idUnidad, id)
        valorInputPrecio = document.getElementById('precio' + id).value;
        valorInputCantidad = document.getElementById('cantidad' + id).value;
        valorSelectUnidad = document.getElementById('unidad' + id).value;
        isvProducto = document.getElementById("isv" + id).value;

        if (valorInputPrecio && valorInputCantidad) {

            //subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
            //isv = subTotal * (isvProducto / 100);
            //total = subTotal + subTotal * (isvProducto / 100);


            let descuento = document.getElementById('porDescuento').value;


            /*if (descuento > 0) {
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

            }*/

            if (descuento > 0) {
                subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                descuentoCalculado = subTotal * (descuento / 100);
                subTotal = subTotal - descuentoCalculado;
                let isv1 = subTotal * (isvProducto / 100);
                let isvSinRedondeo1 = parseFloat(isv1.toFixed(2));
                isv = isvSinRedondeo1 ;
                total = subTotal + (subTotal * (isvProducto / 100));
            } else {
                descuentoCalculado = 0
                subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                let isv2 = subTotal * (isvProducto / 100);
                let isvSinRedondeo2 = parseFloat(isv2.toFixed(2));
                isv = isvSinRedondeo2;
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




            document.getElementById('descuentoMostrar').value = new Intl.NumberFormat('es-HN', {
                style: 'currency',
                currency: 'HNL',
                minimumFractionDigits: 2,
            }).format(descuentoCalculado)


        }

    });



    this.totalesGenerales();
    return 0;


}

function calcularTotales(idPrecio, idCantidad, isvProducto, idUnidad, id, idRestaInventario) {

    let valorInputPrecio = Number(idPrecio.value).toFixed(2);
    let valorInputCantidad = idCantidad.value;
    let valorSelectUnidad = idUnidad.value;
    let descuentoCalculado = 0;

    if (valorInputPrecio && valorInputCantidad) {

        //let subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
        //let isv = subTotal * (isvProducto / 100);
        //let total = subTotal + subTotal * (isvProducto / 100);

        let descuento = document.getElementById('porDescuento').value;

        /*if (descuento >= 0) {
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
        }*/

        if (descuento > 0) {
            subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
            descuentoCalculado = subTotal * (descuento / 100);
            subTotal = subTotal - descuentoCalculado;
            let isv1 = subTotal * (isvProducto / 100);
            let isvSinRedondeo1 = parseFloat(isv1.toFixed(2));
            isv = isvSinRedondeo1 ;
            total = subTotal + (subTotal * (isvProducto / 100));
        } else {
            descuentoCalculado = 0
            subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
            let isv2 = subTotal * (isvProducto / 100);
            let isvSinRedondeo2 = parseFloat(isv2.toFixed(2));
            isv = isvSinRedondeo2;
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


        document.getElementById('descuentoMostrar').value = new Intl.NumberFormat('es-HN', {
            style: 'currency',
            currency: 'HNL',
            minimumFractionDigits: 2,
        }).format(descuentoCalculado)


    }

    idPrecio.value = valorInputPrecio;
    return 0;

}

function totalesGenerales() {


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

    document.getElementById('porDescuentoCalculado').value = acumularDescuento.toFixed(2);;

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
    axios.post("/estatal/datos/cliente", {
            id: idCliente
        })
        .then(
            response => {

                let data = response.data.datos;

                if (data.id == 1) {
                    document.getElementById("nombre_cliente_ventas").readOnly = false;
                    document.getElementById("rtn_ventas").readOnly = false;

                    let selectBox = document.getElementById("tipoPagoVenta");
                    selectBox.remove(2);

                } else {
                    document.getElementById("nombre_cliente_ventas").readOnly = true;
                    document.getElementById("rtn_ventas").readOnly = true;
                    document.getElementById("nombre_cliente_ventas").value = data.nombre;
                    document.getElementById("rtn_ventas").value = data.rtn;

                    diasCredito = data.dias_credito;
                    obtenerTipoPago();
                    obtenerOrdenesCompra();
                }

                // document.getElementById('fecha_vencimiento').value = "";
                // document.getElementById('fecha_emision').value="";



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


                document.getElementById("guardar_cotizacion_btn").style.display = "none";

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


    axios.post('/editar/cotizacion', formDataObj, options)
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





                document.getElementById("guardar_cotizacion_btn").style.display = "inline-block";

            location.reload();

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

function obtenerOrdenesCompra() {

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
}
