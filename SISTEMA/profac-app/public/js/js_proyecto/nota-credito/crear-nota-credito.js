
var contador = 1;
var arrayInputs = [];
var productoSeccion = [];

$('#cliente').select2({
    ajax: {
        url: '/nota/credito/clientes',
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

$('#motivo_nota').select2({
    ajax: {
        url: '/nota/credito/motivos',
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

function obtenerFacturasDeCliente() {
    document.getElementById('factura').innerHTML =
        ' <option value="" selected disabled>--Seleccionar una factura--</option>';

    this.limpiarTablas();

    let idCliente = document.getElementById('cliente').value

    $('#factura').select2({
        ajax: {
            url: '/nota/credito/facturas',
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

function datosFactura() {
    let idFactura = document.getElementById('factura').value;


    axios.post('/nota/credito/datos/factura', {
            idFactura: idFactura
        })
        .then(response => {

            let data = response.data.datosFactura;


            document.getElementById('codigo_factura').value = data.id;
            document.getElementById('fecha').value = data.fecha_emision;
            document.getElementById('tipo_pago').value = data.tipoPago;


            document.getElementById('tipo_venta').value = data.tipoFactura;
            document.getElementById('codigo_cliente').value = data.idCliente;
            document.getElementById('rtn').value = data.rtn;

            document.getElementById('nombre_cliente').value = data.nombreCliente;
            document.getElementById('vendedor').value = data.vendedor;
            document.getElementById('facturado').value = data.facturador;
            document.getElementById('fecha_registro').value = data.fechaRegistro;


            document.getElementById('subTotalGeneralMostrar').value = new Intl.NumberFormat('es-HN', {
                style: 'currency',
                currency: 'HNL',
                minimumFractionDigits: 2,
            }).format(data.sub_total);
            document.getElementById('subTotalGeneralGrabadoMostrar').value = new Intl.NumberFormat('es-HN', {
                style: 'currency',
                currency: 'HNL',
                minimumFractionDigits: 2,
            }).format(data.sub_total_grabado);
            document.getElementById('subTotalGeneralExcentoMostrar').value = new Intl.NumberFormat('es-HN', {
                style: 'currency',
                currency: 'HNL',
                minimumFractionDigits: 2,
            }).format(data.sub_total_excento);

            document.getElementById('isvGeneralMostrar').value = new Intl.NumberFormat('es-HN', {
                style: 'currency',
                currency: 'HNL',
                minimumFractionDigits: 2,
            }).format(data.isv);
            document.getElementById('totalGeneralMostrar').value = new Intl.NumberFormat('es-HN', {
                style: 'currency',
                currency: 'HNL',
                minimumFractionDigits: 2,
            }).format(data.total);
        })

    $('#tbl_productos').DataTable().clear().destroy();
    this.obtenerProductos(idFactura);
}








function limpiarTablas() {
    $('#tbl_productos').DataTable().clear().destroy();
}

function infoProducto(facturaId, productoId, seccionId) {


    axios.post('/nota/credito/datos/producto', {
            idFactura: facturaId,
            idProducto: productoId,
            idSeccion: seccionId
        })
        .then(response => {



            let data = response.data.datos;
            let cantidadMax = data.cantidad;

            document.getElementById('nombre').value = data.producto;
            document.getElementById('idFactura').value = data.factura_id;
            document.getElementById('idProducto').value = data.producto_id;
            document.getElementById('idMedidaVenta').value = data.idUnidadVenta;
            document.getElementById('unidad_venta').value = data.unidad_venta;
            document.getElementById('unidad').value = data.unidad_medida;
            document.getElementById('precio').value = data.precio_unidad;
            document.getElementById('isvPorcentaje').value = data.porcentajeISV;
            document.getElementById('cantidadMaxima').value = cantidadMax;
            document.getElementById('precioMostrar').value = monedaLempiras(data.precio_unidad);
            document.getElementById("cantidad").value = 0;
            document.getElementById('cantidad').max = cantidadMax;
            document.getElementById('cantidad').min = 1;

            document.getElementById('isvVenta').value = data.isVenta;
            document.getElementById('totalVenta').value = data.totalVenta;


            let htmlBodega =
                `<option value="${data.bodegaId}" selected="" disabled="">${data.nombreBodega}</option>`;
            let htmlSegmento =
                `<option value="${data.segmentoId}" selected="" disabled="">${data.segmento}</option>`;
            let htmlSeccion =
                `<option value="${data.seccionId}" selected="" disabled="">${data.seccion}</option>`;

            document.getElementById('bodega').innerHTML = htmlBodega;
            document.getElementById('segmento').innerHTML = htmlSegmento;
            document.getElementById('seccion').innerHTML = htmlSeccion;

            $('#modal_devolver_producto').modal('show');

        })
        .catch(err => {
            console.log(err);
            let data = err.response.data;
            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text,
            })
        })

}

function agregarProductoLista() {
    let cantidad = document.getElementById('cantidad').value;
    let cantidadMaxima = document.getElementById('cantidadMaxima').value;

    let idProducto = document.getElementById('idProducto').value;
    let seccion = document.getElementById('seccion');


    let repetidoFlag = false;

    //****************Comprueba si el producto con la seccion se repite************************/
    productoSeccion.forEach(array => {
        if (array[0] == idProducto && array[1] == seccion.value) {
            repetidoFlag = true;
            return;
        }
    });

    if (repetidoFlag) {

        Swal.fire({
            icon: "warning",
            title: "Advertencia!",
            text: "El producto con la sección correspondiente ya se encuentra en la lista.",
        })
        $('#modal_devolver_producto').modal('hide')
        return;
    }
    //****************Comprueba si el producto con la seccion se repite************************/



    if (+cantidad == 0 || !cantidad) {
        $('#modal_devolver_producto').modal('hide')
        Swal.fire({
            icon: "warning",
            title: "Advertencia",
            text: "La cantidad a devolver debe ser mayor a 0.",
        })
        return;
    }


    if (+cantidad > +cantidadMaxima) {
        $('#modal_devolver_producto').modal('hide')
        Swal.fire({
            icon: "warning",
            title: "Advertencia",
            text: "La cantidad excede el maximo permitido.",
        })
        return;
    }




    let nombre = document.getElementById('nombre').value;
    let idFactura = document.getElementById('idFactura').value;
    let idMedidaVenta = document.getElementById('idMedidaVenta').value;
    let unidad = document.getElementById('unidad').value;
    let precio = document.getElementById('precio').value;


    let unidad_venta = document.getElementById('unidad_venta').value;
    let isvPorcentaje = document.getElementById('isvPorcentaje').value;
    let isvVenta = document.getElementById('isvVenta').value;
    let totalVenta = document.getElementById('totalVenta').value;




    let bodega = document.getElementById('bodega');
    let segmento = document.getElementById('segmento');


    let bodegaTexto = bodega.options[bodega.selectedIndex].text;
    let seccionTexto = seccion.options[seccion.selectedIndex].text;

   // let precio2 = totalVenta/cantidad;

    let subTotal = precio * cantidad * unidad_venta;

    let isv = 0 ;
    if (isvVenta != 0){

         isv = subTotal * (isvPorcentaje / 100);
    }

    //let isv = isvPorcentaje;

    let total = subTotal + isv;


    let html = `
        <tr id="tr${contador}">
                        <td>
                            ${nombre}
                            <input type="hidden" id="IdProducto${contador}" name="IdProducto${contador}" value="${idProducto}" form="guardar_devolucion">
                            <input type="hidden" id="IdSeccion${contador}" name="IdSeccion${contador}" value="${seccion.value}" form="guardar_devolucion">
                            <input type="hidden" id="nombreProducto${contador}" name="nombreProducto${contador}" value="${nombre}" form="guardar_devolucion">
                            <input type="hidden" id="precio${contador}" name="precio${contador}" value="${precio}" form="guardar_devolucion">
                        </td>
                        <td>${bodegaTexto}</td>
                        <td>${seccionTexto}</td>
                        <td>${monedaLempiras(precio)}</td>
                        <td>
                            ${cantidad}
                            <input type="hidden" id="cantidad${contador}" name="cantidad${contador}" value="${cantidad}" form="guardar_devolucion">
                        </td>
                        <td>${unidad}
                            <input type="hidden" id="idUnidadMedida${contador}" name="idUnidadMedida${contador}" value="${idMedidaVenta}" form="guardar_devolucion" >
                        </td>
                        <td>
                            ${monedaLempiras(subTotal)}
                            <input type="hidden" id="subTotal${contador}" name="subTotal${contador}" value="${subTotal}" form="guardar_devolucion" >
                        </td>
                        <td>
                            ${monedaLempiras(isv)}
                            <input type="hidden" id="isv${contador}" name="isv${contador}" value="${isv}" form="guardar_devolucion" >
                        </td>
                        <td>
                            ${monedaLempiras(total)}
                            <input type="hidden" id="total${contador}" name="total${contador}" value="${total}" form="guardar_devolucion" >
                        </td>
                        <td><button class="btn btn-danger" onclick="eliminarFila(${contador},${subTotal},${isv},${total})">Eliminar</button></td>
                    </tr>
        `;

    let idCuerpoLista = document.getElementById("cuerpoLista");

    $('#modal_devolver_producto').modal('hide')
    idCuerpoLista.insertAdjacentHTML('beforeend', html);
    document.getElementById("form_producto_devolver").reset();
    $('#form_producto_devolver').parsley().reset();

    let sub_totalInput = document.getElementById("subTotalGeneralCredito").value;
    sub_totalInput = (+sub_totalInput) + (+subTotal);
    document.getElementById("subTotalGeneralCredito").value = sub_totalInput;

    let sub_totalGrabadoInput = document.getElementById("subTotalGeneralGrabadoCredito").value;
    let sub_totalExcentoInput = document.getElementById("subTotalGeneralExcentoCredito").value;

    if(isv >0){
    sub_totalGrabadoInput = (+sub_totalGrabadoInput) + (+subTotal);
    document.getElementById("subTotalGeneralGrabadoCredito").value = sub_totalGrabadoInput;
    }



    if (isv == 0) {
        sub_totalExcentoInput = (+sub_totalExcentoInput) + (+subTotal);
        document.getElementById("subTotalGeneralExcentoCredito").value = sub_totalExcentoInput;
    }


    let isvInput = document.getElementById("isvGeneralCredito").value;
    isvInput = (+isvInput) + (+isv);
    document.getElementById("isvGeneralCredito").value = isvInput;

    let totalInput = document.getElementById("totalGeneralCredito").value;
    totalInput = (+totalInput) + (+total);
    document.getElementById("totalGeneralCredito").value = totalInput;

    document.getElementById("subTotalGeneralCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
        style: 'currency',
        currency: 'HNL',
        minimumFractionDigits: 2,
    }).format(sub_totalInput);


    document.getElementById("subTotalGeneralGrabadoCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
        style: 'currency',
        currency: 'HNL',
        minimumFractionDigits: 2,
    }).format(sub_totalGrabadoInput);



    document.getElementById("subTotalGeneralExcentoCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
        style: 'currency',
        currency: 'HNL',
        minimumFractionDigits: 2,
    }).format(sub_totalExcentoInput);




    document.getElementById("isvGeneralCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
        style: 'currency',
        currency: 'HNL',
        minimumFractionDigits: 2,
    }).format(isvInput);
    document.getElementById("totalGeneralCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
        style: 'currency',
        currency: 'HNL',
        minimumFractionDigits: 2,
    }).format(totalInput);

    document.getElementById("solicitarFactura").disabled = true;
    document.getElementById("cliente").disabled = true;
    document.getElementById("factura").disabled = true;

    arrayInputs.push(contador);
    contador++;
    productoSeccion.push([idProducto, seccion.value]);

    return;
}

function monedaLempiras(monto) {
    let numero = new Intl.NumberFormat('es-HN', {
        style: 'currency',
        currency: 'HNL',
        minimumFractionDigits: 2,
    }).format(monto)

    return numero;
}

function eliminarFila(id, subtotal, isv, total) {

    let idProducto = document.getElementById("IdProducto" + id).value;
    let idSeccion = document.getElementById("IdSeccion" + id).value;
    let array = [];
    for (let i = 0; i < productoSeccion.length; i++) {

        array = productoSeccion[i];
        if (array[0] == idProducto && array[1] == idSeccion) {
            productoSeccion.splice(i, 1);
        }

    }




    const element = document.getElementById('tr' + id);
    element.remove();



    var myIndex = arrayInputs.indexOf(id);
    if (myIndex !== -1) {
        arrayInputs.splice(myIndex, 1);
        // this.totalesGenerales();

    }

    let sub_totalInput = document.getElementById("subTotalGeneralCredito").value;
    sub_totalInput = (+sub_totalInput) - (+subtotal);
    document.getElementById("subTotalGeneralCredito").value = sub_totalInput;

    let sub_totalGrabadoInput = document.getElementById("subTotalGeneralGrabadoCredito").value;
    if(isv > 0){
    sub_totalGrabadoInput = (+sub_totalGrabadoInput) - (+subtotal);
    document.getElementById("subTotalGeneralGrabadoCredito").value = sub_totalGrabadoInput;
    }


    let sub_totalExcentoInput = document.getElementById("subTotalGeneralExcentoCredito").value;
    if(isv == 0){

    sub_totalExcentoInput = (+sub_totalExcentoInput) - (+subtotal);
    document.getElementById("subTotalGeneralExcentoCredito").value = sub_totalExcentoInput;
    }








    let isvInput = document.getElementById("isvGeneralCredito").value;
    isvInput = (+isvInput) - (+isv);
    document.getElementById("isvGeneralCredito").value = isvInput;

    let totalInput = document.getElementById("totalGeneralCredito").value;
    totalInput = (+totalInput) - (+total);
    document.getElementById("totalGeneralCredito").value = totalInput;


    document.getElementById("subTotalGeneralCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
        style: 'currency',
        currency: 'HNL',
        minimumFractionDigits: 2,
    }).format(sub_totalInput);


    document.getElementById("subTotalGeneralGrabadoCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
        style: 'currency',
        currency: 'HNL',
        minimumFractionDigits: 2,
    }).format(sub_totalGrabadoInput);



    document.getElementById("subTotalGeneralExcentoCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
        style: 'currency',
        currency: 'HNL',
        minimumFractionDigits: 2,
    }).format(sub_totalExcentoInput);






    document.getElementById("isvGeneralCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
        style: 'currency',
        currency: 'HNL',
        minimumFractionDigits: 2,
    }).format(isvInput);
    document.getElementById("totalGeneralCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
        style: 'currency',
        currency: 'HNL',
        minimumFractionDigits: 2,
    }).format(totalInput);



}

$(document).on('submit', '#guardar_devolucion', function(event) {

    event.preventDefault();

    guardarNotaCredito();

});

function guardarNotaCredito() {
    let idFactura = document.getElementById("idFactura").value;
    document.getElementById("btn_guardar_nota_credito").disabled = true;

    var dataForm = new FormData($('#guardar_devolucion').get(0));

    let longitudArreglo = arrayInputs.length;
    for (var i = 0; i < longitudArreglo; i++) {



        dataForm.append("arregloIdInputs[]", arrayInputs[i]);

    }

    dataForm.append("idFactura", idFactura);

    // let table = $('#tbl_translados_destino').DataTable();
    // table.destroy();

    axios.post('/nota/credito/guardar', dataForm)
        .then(response => {

            let data = response.data;
            let contador = data.contadorTranslados;

            // document.getElementById("btn_guardar_nota_credito").disabled = false;

            //Eliminar DIVS y que muestre alert para imprimir
            //Agregar funcion para anular
            Swal.fire({
                icon: data.icon,
                title: data.title,
                html: data.text,

            })

            if(data.icon = 'warning'){
                setTimeout(function(){
                    location.reload();
                }, 3000)
            }else{
                location.reload();
            }




           // location.reload()


            return;


        })
        .catch(err => {
            //console.log(err)
            document.getElementById("btn_guardar_nota_credito").disabled = false;
            console.log(err);
            $('#modal_transladar_producto').modal('hide')



            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ha ocurrido un error, reporte con soporte.",
            })

        })
}
