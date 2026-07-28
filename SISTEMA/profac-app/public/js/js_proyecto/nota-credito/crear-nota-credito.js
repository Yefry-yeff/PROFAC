
var contador = 1;
var arrayInputs = [];
var productoSeccion = [];

// ========== FUNCIONES AUXILIARES PARA MODALES ==========
function abrirModal(modalId) {
    try {
        if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
            $('#' + modalId).modal('show');
        } else {
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                // Crear o actualizar el backdrop
                let backdrop = document.querySelector('.modal-backdrop');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdrop);
                }
                
                modalElement.classList.add('show');
                modalElement.setAttribute('aria-hidden', 'false');
                modalElement.style.display = 'block';
                document.body.classList.add('modal-open');
                document.body.style.overflow = 'hidden';
            }
        }
    } catch (e) {
        console.error('Error al abrir modal ' + modalId + ':', e);
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            modalElement.classList.add('show');
            modalElement.style.display = 'block';
        }
    }
}

function cerrarModal(modalId) {
    try {
        if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
            $('#' + modalId).modal('hide');
        } else {
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                // Remover el backdrop
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                
                modalElement.classList.remove('show');
                modalElement.setAttribute('aria-hidden', 'true');
                modalElement.style.display = 'none';
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
            }
        }
    } catch (e) {
        console.error('Error al cerrar modal ' + modalId + ':', e);
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            modalElement.classList.remove('show');
            modalElement.style.display = 'none';
        }
    }
}
// ===================================================

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

// =====================================================================
// Lógica de pestañas: Por Producto / Por Descuento
// =====================================================================

function cambiarTipoNota(tipo) {
    var tipoAnterior = document.getElementById('tipo_nota_credito').value;

    if (tipo === tipoAnterior) return;

    // Si se intenta salir de "producto" con filas ya añadidas, bloquear
    if (tipoAnterior === 'producto' && arrayInputs.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Ya tiene productos agregados en la nota. Elimínelos antes de cambiar el tipo.',
        });
        // Revertir la pestaña visualmente
        $('#tab-producto-link').tab('show');
        return;
    }

    // Si se sale de "descuento", limpiar campos de descuento y totales
    if (tipoAnterior === 'descuento') {
        document.getElementById('monto_descuento_mostrar').value = '';
        document.getElementById('monto_descuento').value = '0';
        resetearTotalesCredito();
    }

    // Si se entra a "descuento": ocultar lista de productos y resumen de factura; recalcular si ya hay monto
    if (tipo === 'descuento') {
        document.getElementById('seccion_lista_productos').style.display = 'none';
        document.getElementById('seccion_resumen_factura').style.display = 'none';
        var monto = document.getElementById('monto_descuento_mostrar').value;
        if (monto && +monto > 0) {
            calcularDescuento(monto);
        } else {
            resetearTotalesCredito();
        }
    }

    // Si se entra a "producto": mostrar lista de productos y resumen de factura
    if (tipo === 'producto') {
        document.getElementById('seccion_lista_productos').style.display = '';
        document.getElementById('seccion_resumen_factura').style.display = '';
    }

    document.getElementById('tipo_nota_credito').value = tipo;
}

function resetearTotalesCredito() {
    document.getElementById('subTotalGeneralCredito').value        = '0';
    document.getElementById('subTotalGeneralGrabadoCredito').value = '0';
    document.getElementById('subTotalGeneralExcentoCredito').value = '0';
    document.getElementById('isvGeneralCredito').value             = '0';
    document.getElementById('totalGeneralCredito').value           = '0';
    document.getElementById('subTotalGeneralCreditoMostrar').value        = '';
    document.getElementById('subTotalGeneralGrabadoCreditoMostrar').value = '';
    document.getElementById('subTotalGeneralExcentoCreditoMostrar').value = '';
    document.getElementById('isvGeneralCreditoMostrar').value             = '';
    document.getElementById('totalGeneralCreditoMostrar').value           = '';
}

function calcularDescuento(monto) {
    monto = parseFloat(monto) || 0;

    // Obtener el total de la factura original
    let totalFactura = parseFloat(document.getElementById('totalGeneralMostrar').value.replace(/[^\d.-]/g, '')) || 0;

    // Validar que el monto no exceda el total de la factura
    if (monto > totalFactura) {
        document.getElementById('monto_descuento_mostrar').value = '';
        document.getElementById('monto_descuento').value = 0;
        resetearTotalesCredito();
        
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: `El monto del descuento no puede ser mayor al total de la factura (L. ${monedaLempiras(totalFactura)})`,
        });
        return;
    }

    // Actualizar hidden
    document.getElementById('monto_descuento').value = monto;

    // Para nota por descuento: sub_total = monto, grabado = 0, excento = monto, ISV = 0, total = monto
    document.getElementById('subTotalGeneralCredito').value        = monto;
    document.getElementById('subTotalGeneralGrabadoCredito').value = 0;
    document.getElementById('subTotalGeneralExcentoCredito').value = monto;
    document.getElementById('isvGeneralCredito').value             = 0;
    document.getElementById('totalGeneralCredito').value           = monto;

    document.getElementById('subTotalGeneralCreditoMostrar').value        = monedaLempiras(monto);
    document.getElementById('subTotalGeneralGrabadoCreditoMostrar').value = monedaLempiras(0);
    document.getElementById('subTotalGeneralExcentoCreditoMostrar').value = monedaLempiras(monto);
    document.getElementById('isvGeneralCreditoMostrar').value             = monedaLempiras(0);
    document.getElementById('totalGeneralCreditoMostrar').value           = monedaLempiras(monto);
}

// =====================================================================

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

            // También llenar los campos de la pestaña de descuento
            document.getElementById('subTotalGeneralMostrar_desc').value = new Intl.NumberFormat('es-HN', {
                style: 'currency',
                currency: 'HNL',
                minimumFractionDigits: 2,
            }).format(data.sub_total);
            document.getElementById('subTotalGeneralGrabadoMostrar_desc').value = new Intl.NumberFormat('es-HN', {
                style: 'currency',
                currency: 'HNL',
                minimumFractionDigits: 2,
            }).format(data.sub_total_grabado);
            document.getElementById('subTotalGeneralExcentoMostrar_desc').value = new Intl.NumberFormat('es-HN', {
                style: 'currency',
                currency: 'HNL',
                minimumFractionDigits: 2,
            }).format(data.sub_total_excento);

            document.getElementById('isvGeneralMostrar_desc').value = new Intl.NumberFormat('es-HN', {
                style: 'currency',
                currency: 'HNL',
                minimumFractionDigits: 2,
            }).format(data.isv);
            document.getElementById('totalGeneralMostrar_desc').value = new Intl.NumberFormat('es-HN', {
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
            document.getElementById('subtotalproducto').value = data.sub_total;
            document.getElementById('porc_descuento').value = data.porc_descuento;
            document.getElementById('isvPorcentaje').value = data.porcentajeISV;
            document.getElementById('cantidadMaxima').value = cantidadMax;
            document.getElementById('precioMostrar').value = monedaLempiras(data.precio_unidad);
            document.getElementById("cantidad").value = 0;
            document.getElementById('cantidad').max = cantidadMax;
            document.getElementById('cantidad').min = 1;

            document.getElementById('isvVenta').value = data.isVenta;
            document.getElementById('totalVenta').value = data.totalVenta;

            let descuentoInfo = document.getElementById('descuentoInfo');
            let porcDescuento = +data.porc_descuento;
            if (porcDescuento > 0) {
                let precioConDescuento = data.precio_unidad * (1 - (porcDescuento / 100));
                document.getElementById('descuentoInfoPorcentaje').innerText = porcDescuento + '%';
                document.getElementById('descuentoInfoPrecio').innerText = monedaLempiras(precioConDescuento);
                descuentoInfo.style.display = 'block';
            } else {
                descuentoInfo.style.display = 'none';
            }

            let htmlBodega =
                `<option value="${data.bodegaId}" selected="" disabled="">${data.nombreBodega}</option>`;
            let htmlSegmento =
                `<option value="${data.segmentoId}" selected="" disabled="">${data.segmento}</option>`;
            let htmlSeccion =
                `<option value="${data.seccionId}" selected="" disabled="">${data.seccion}</option>`;

            document.getElementById('bodega').innerHTML = htmlBodega;
            document.getElementById('segmento').innerHTML = htmlSegmento;
            document.getElementById('seccion').innerHTML = htmlSeccion;

            abrirModal('modal_devolver_producto');

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
    let subtotalproducto = document.getElementById('subtotalproducto').value;
    let porc_descuento = document.getElementById('porc_descuento').value;
    console.log(subtotalproducto);
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
        cerrarModal('modal_devolver_producto');
        return;
    }
    //****************Comprueba si el producto con la seccion se repite************************/



    if (+cantidad == 0 || !cantidad) {
        cerrarModal('modal_devolver_producto');
        Swal.fire({
            icon: "warning",
            title: "Advertencia",
            text: "La cantidad a devolver debe ser mayor a 0.",
        })
        return;
    }


    if (+cantidad > +cantidadMaxima) {
        cerrarModal('modal_devolver_producto');
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

    let subTotalAnt = precio * cantidad * unidad_venta;
    console.log("Se supone que aqui es el anterior: "+subTotalAnt);
    let descuento = subTotalAnt*(porc_descuento/100);
    console.log("Se supone que el descuento: "+porc_descuento);
    let subTotal = subTotalAnt - (subTotalAnt*(porc_descuento/100));

    console.log("subtotal menos descuento: "+subTotal);


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

    cerrarModal('modal_devolver_producto');
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
    var tipoNota = document.getElementById('tipo_nota_credito').value;

    // Validaciones según tipo
    if (tipoNota === 'producto') {
        if (arrayInputs.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: 'Debe agregar al menos un producto a la nota de crédito.',
            });
            return;
        }
    } else if (tipoNota === 'descuento') {
        var monto = parseFloat(document.getElementById('monto_descuento').value) || 0;
        var comentarioDesc = document.getElementById('comentario_descuento').value.trim();
        if (monto <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: 'Debe ingresar un monto de descuento mayor a cero.',
            });
            return;
        }
        if (!comentarioDesc) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: 'Debe ingresar un comentario para la nota de crédito por descuento.',
            });
            return;
        }
    }

    let idFactura;
    
    // Obtener idFactura según el tipo de nota
    if (tipoNota === 'descuento') {
        // En modo descuento, obtenemos directamente del select de factura
        idFactura = document.getElementById('factura').value;
        if (!idFactura) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: 'Debe seleccionar una factura.',
            });
            return;
        }
    } else {
        // En modo producto, obtenemos del campo oculto
        idFactura = document.getElementById("idFactura").value;
        if (!idFactura) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: 'Debe seleccionar los productos de la factura.',
            });
            return;
        }
    }
    
    document.getElementById("btn_guardar_nota_credito").disabled = true;

    var dataForm = new FormData($('#guardar_devolucion').get(0));

    let longitudArreglo = arrayInputs.length;
    for (var i = 0; i < longitudArreglo; i++) {
        dataForm.append("arregloIdInputs[]", arrayInputs[i]);
    }

    dataForm.append("idFactura", idFactura);
    
    // Debug temporal
    console.log('Tipo de nota:', tipoNota);
    console.log('ID Factura:', idFactura);
    console.log('Array Inputs:', arrayInputs);
    console.log('Valores del form:');
    for (var pair of dataForm.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    // let table = $('#tbl_translados_destino').DataTable();
    // table.destroy();

    axios.post('/nota/credito/guardar', dataForm)
        .then(response => {

            let data = response.data;
            let contador = data.contadorTranslados;
            let idNotaCredito = data.idNota || 0;

            // document.getElementById("btn_guardar_nota_credito").disabled = false;

            //Eliminar DIVS y que muestre alert para imprimir
            //Agregar funcion para anular
            console.log('Respuesta del servidor:', data);
            console.log('ID Nota Crédito:', idNotaCredito);
            
            // Guardar el ID en el modal para usarlo en la impresión
            document.getElementById('modal_imprimir_nota_credito').setAttribute('data-id-nota', idNotaCredito);
            
            try {
                if (typeof Swal !== 'undefined' && Swal !== null) {
                    Swal.fire({
                        icon: data.icon || 'info',
                        title: data.title || 'Resultado',
                        html: data.text || 'Operación completada',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#3085d6',
                        didOpen: () => {
                            console.log('Alerta mostrada exitosamente');
                        }
                    }).then((result) => {
                        // Si fue exitoso, mostrar modal de impresión
                        if(data.icon === 'success' || data.icon === undefined) {
                            console.log('Mostrando modal de impresión...');
                            setTimeout(() => {
                                abrirModal('modal_imprimir_nota_credito');
                            }, 500);
                        } else {
                            // Si fue warning, recargar normalmente
                            setTimeout(function(){
                                location.reload();
                            }, 1500);
                        }
                    });
                } else {
                    // Fallback si Swal no está disponible
                    console.warn('Swal no disponible, usando alert nativo');
                    alert((data.title || '') + '\n' + (data.text || ''));
                    setTimeout(() => {
                        abrirModal('modal_imprimir_nota_credito');
                    }, 500);
                }
            } catch (e) {
                console.error('Error al mostrar alerta:', e);
                alert('Operación completada. Abriendo opciones de impresión...');
                setTimeout(() => {
                    abrirModal('modal_imprimir_nota_credito');
                }, 1500);
            }

            return;


        })
        .catch(err => {
            //console.log(err)
            document.getElementById("btn_guardar_nota_credito").disabled = false;
            console.error('Error al guardar nota de crédito:', err);
            cerrarModal('modal_transladar_producto');

            // Intentar obtener el mensaje del servidor
            let errorMessage = "Ha ocurrido un error, reporte con soporte.";
            let errorTitle = "Error";
            
            if (err.response && err.response.data) {
                if (err.response.data.text) {
                    errorMessage = err.response.data.text;
                }
                if (err.response.data.title) {
                    errorTitle = err.response.data.title;
                }
                // Si hay información del error de la base de datos
                if (err.response.data.error && err.response.data.error.errorInfo) {
                    errorMessage += "<br><small>" + err.response.data.error.errorInfo[2] + "</small>";
                }
            } else if (err.message) {
                errorMessage = err.message;
            }

            console.log('Mostrando error:', errorTitle, errorMessage);

            try {
                if (typeof Swal !== 'undefined' && Swal !== null) {
                    Swal.fire({
                        icon: "error",
                        title: errorTitle,
                        html: errorMessage,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            console.log('Error mostrado');
                        }
                    });
                } else {
                    console.warn('Swal no disponible para mostrar error');
                    alert(errorTitle + '\n' + errorMessage.replace(/<[^>]*>/g, ''));
                }
            } catch (e) {
                console.error('Error al mostrar alerta de error:', e);
                alert(errorTitle + '\n' + errorMessage.replace(/<[^>]*>/g, ''));
            }

        })
}
// ====== FUNCIONES PARA IMPRESIÓN ======

function imprimirNotaCredito(tipo) {
    // Obtener el ID de la nota crédito del atributo data del modal
    let idNotaCredito = document.getElementById('modal_imprimir_nota_credito').getAttribute('data-id-nota');
    
    if (!idNotaCredito || idNotaCredito === '0') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró el ID de la nota de crédito. Intente nuevamente.'
        });
        return;
    }
    
    let url = '';
    
    if (tipo === 'original') {
        url = `/nota/credito/imprimir/${idNotaCredito}`;
    } else if (tipo === 'copia') {
        url = `/nota/credito/imprimir/copia/${idNotaCredito}`;
    }
    
    if (url) {
        // Abrir en nueva ventana para que el usuario pueda imprimir
        window.open(url, '_blank');
    }
}

function finalizarYContinuar() {
    cerrarModal('modal_imprimir_nota_credito');
    // Recargar la página
    setTimeout(() => {
        location.reload();
    }, 500);
}

// ======================================