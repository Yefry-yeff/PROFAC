
var contador = 1;
var arrayInputs = [];
var idProductoArray = [];
var idRecibidoArray = [];
var idRecibido = null;
var _trasladoIdActual = null;  // ID del traslado guardado (para impresión)
var secuenciaBusquedaProductoTraslado = 0;

$(document).ready(function() {
    listarBodegas();

});

$(document).on('submit', '#selec_data_form', function(event) {
    event.preventDefault();
    obtenerListaBodega();
});

function limpiar(){
    window.location.reload()
}



function abrirBuscadorProductoTraslado() {
    var termino = document.getElementById('productoTraslado_nombre').value.trim();
    window['abrirBuscador_buscadorProductoTraslados'](termino);
}

function prepararNuevaBusquedaProductoTraslado(valorActual) {
    var seleccionado = document.getElementById('selectProducto');
    if (!seleccionado.value) return;
    seleccionado.value = '';
    document.getElementById('ubicaciones_traslado_panel').style.display = 'none';
    document.getElementById('productoTraslado_nombre').value = valorActual || '';
    secuenciaBusquedaProductoTraslado++;
}

function buscarPorCodigoTraslado(codigo) {
    codigo = String(codigo || '').trim();
    if (!codigo) {
        abrirBuscadorProductoTraslado();
        return;
    }

    var secuenciaActual = ++secuenciaBusquedaProductoTraslado;
    axios.get('/productos/buscar', { params: { q: codigo, page: 1 } })
        .then(function(response) {
            if (secuenciaActual !== secuenciaBusquedaProductoTraslado) return;
            var productos = response.data.data || [];
            var exacto = productos.find(function(producto) {
                return String(producto.id) === codigo
                    || String(producto.codigo_barra || '').trim() === codigo
                    || String(producto.codigo_estatal || '').trim() === codigo;
            });

            if (exacto) alSeleccionarProductoTraslado(exacto);
            else if (productos.length === 1) alSeleccionarProductoTraslado(productos[0]);
            else window['abrirBuscador_buscadorProductoTraslados'](codigo);
        })
        .catch(function() {
            if (secuenciaActual !== secuenciaBusquedaProductoTraslado) return;
            window['abrirBuscador_buscadorProductoTraslados'](codigo);
        });
}

// Callback invocado por el buscador-producto cuando el usuario selecciona un producto
function alSeleccionarProductoTraslado(producto) {
    document.getElementById('selectProducto').value = producto.id;
    document.getElementById('productoTraslado_nombre').value = producto.id + ' - ' + producto.nombre;
    document.getElementById('producto_ubicaciones_traslado_texto').textContent = producto.id + ' - ' + producto.nombre + '.';
    document.getElementById('ubicaciones_traslado_panel').style.display = 'block';
    obtenerListaBodega();
}

function obtenerListaBodega() {
    let idProducto = document.getElementById('selectProducto').value;
    if (!idProducto) return;

    if ($.fn.DataTable.isDataTable('#tbl_translados')) {
        $('#tbl_translados').DataTable().destroy();
    }

    $('#tbl_translados').DataTable({
        "language": {
            "url": "/js/plugins/dataTables/i18n/Spanish.json"
        },
        pageLength: 10,
        responsive: true,
        "ajax": "/translado/producto/ubicaciones/" + idProducto,
        "columns": [{
                data: 'idProducto'
            },
            {
                data: 'nombre'
            },
            {
                data: 'simbolo'
            },
            {
                data: 'cantidad_disponible'
            },
            {
                data: 'bodega'
            },
            {
                data: 'descripcion'
            },
            {
                data: 'created_at'
            },
            {
                data: 'opciones'
            },



        ],
        drawCallback: function() {
            var sum = $('#tbl_translados').DataTable().column(3).data().sum();
            let html = 'Cantidad total disponible: ' + Number(sum || 0).toLocaleString('es-HN', { maximumFractionDigits: 2 });
            $('#total').html(html);
        }



    });

    // let tabla = $('#tbl_translados').DataTable();
    // let suma = tabla.column(4,{page:'current'}).data().sum();
    // console.log(suma);
}

function modalTranslado(idsRecibido, cantidadDisponible, idProducto) {
    this.idRecibido = idsRecibido
    document.getElementById('cantidad').max = cantidadDisponible;

    //console.log(idProducto);
    this.listarUmedidas(idProducto);

    $('#modal_transladar_producto').modal('show')
    // console.log(this.idRecibido);

    document.getElementById("idProducto").value = idProducto

}

function listarBodegas() {
    //console.log("entro")
    document.getElementById('segmento').innerHTML =
        '<option value="" selected disabled>---Seleccione un segmento de destino---</option>';
    document.getElementById('seccion').innerHTML =
        '<option value="" selected disabled>---Seleccione una sección de destino---</option>';

    axios.get('/producto/recibir/bodega')
        .then(response => {

            //console.log(response)

            let array = response.data.listaBodegas;
            let htmlBodega =
                ' <option value="" selected disabled>---Seleccione una bodega de destino---</option>';
            array.forEach(element => {
                htmlBodega += `
                <option value="${element.id}">${element.nombre}</option>
                 `

            })

            document.getElementById('bodega').innerHTML = htmlBodega;


        })
        .catch(err => {

            console.log(err);

        })
}

function listarSegmentos() {

    let bodega = document.getElementById("bodega").value;


    axios.post('/producto/recibir/segmento', {
            idBodega: bodega
        })
        .then(response => {

            //console.log(response)

            let array = response.data.listaSegmentos;
            let htmlSegmento = '  <option value="" selected disabled>---Seleccione un segmento---</option>';
            array.forEach(element => {
                htmlSegmento += `
                    <option value="${element.id}">${element.descripcion}</option>
                        `

            })

            document.getElementById('segmento').innerHTML = htmlSegmento;

        })
        .catch(err => {

            console.log(err);

        })
}

function listarSecciones() {

    let segmento = document.getElementById("segmento").value;


    axios.post('/producto/recibir/seccion', {
            idSegmento: segmento
        })
        .then(response => {

            //console.log(response)

            let array = response.data.listaSecciones;
            let htmlSeccion = '  <option value="" selected disabled>---Seleccione una sección---</option>';
            array.forEach(element => {
                htmlSeccion += `<option value="${element.id}">${element.descripcion}</option>`

            })

            document.getElementById('seccion').innerHTML = htmlSeccion;

        })
        .catch(err => {

            console.log(err);

        })
}

function listarUmedidas(idProducto) {
    axios.get('/producto/recibir/Umedidas/' + idProducto)
        .then(response => {

            let array = response.data.listaUmedidas;
            let htmlSeccion = '  <option value="" selected disabled>---Seleccione una sección---</option>';
            array.forEach(element => {
                htmlSeccion += `<option value="${element.id}">${element.unidad}</option>`
            })

            document.getElementById('Umedida').innerHTML = htmlSeccion;

        })
        .catch(err => {

            console.log(err);

        })
}



$(document).on('submit', '#recibirProducto', function(event) {

    event.preventDefault();
    transladoProducto();

});

// function transladoProducto(){
//     document.getElementById('btn_recibir_bodega').disabled = true;
//     let idSeccion = document.getElementById('seccion').value;

//     let dataForm = new FormData($('#recibirProducto').get(0));
//     dataForm.append('idRecibido',idRecibido);
//     //console.log(dataForm);

//     let table = $('#tbl_translados_destino').DataTable();
//     table.destroy();

//     axios.post('/translado/producto/bodega',dataForm)
//     .then( response =>{

//         let data = response.data;

//         $('#modal_transladar_producto').modal('hide')
//         document.getElementById('btn_recibir_bodega').disabled = false;
//         document.getElementById("recibirProducto").reset();
//         $('#recibirProducto').parsley().reset();


//             Swal.fire({
//                 icon: data.icon,
//                 title: data.title,
//                 text: data.text,

//             })


//         $('#tbl_translados').DataTable().ajax.reload();


//         listadoBodegaDestino(idSeccion);

//         //document.getElementById('destino').class
//         document.getElementById('destino').classList.remove('d-none');
//         document.getElementById("recibirProducto").reset();
//         $('#recibirProducto').parsley().reset();


//         return;


//     })
//     .catch( err =>{
//         //console.log(err)
//         $('#modal_transladar_producto').modal('hide')
//         document.getElementById('btn_recibir_bodega').disabled = false;

//         let data = err.response.data;
//             Swal.fire({
//                 icon: data.icon,
//                 title: data.title,
//                 text: data.text,
//             })

//     })

// }

function transladoProducto() {

    let idCuerpoLista = document.getElementById("cuerpoListaProducto");

    let idProducto = document.getElementById("idProducto").value;

    let bodegaSelect = document.getElementById("bodega");
    let bodegaNombre = bodegaSelect.options[bodegaSelect.selectedIndex].text
    let bodegaId = bodegaSelect.value;

    let segmentoSelect = document.getElementById("segmento");
    let segmentoNombre = segmentoSelect.options[segmentoSelect.selectedIndex].text
    let segmentoId = segmentoSelect.value;


    let seccionSelect = document.getElementById("seccion");
    let seccionNombre = seccionSelect.options[seccionSelect.selectedIndex].text
    let seccionId = seccionSelect.value;

    let cantidad = document.getElementById("cantidad").value;


    let medidaSelect = document.getElementById("Umedida");
    let medidaNombre = medidaSelect.options[medidaSelect.selectedIndex].text
    let medidaId = medidaSelect.value;

    let comentario_item = document.getElementById("comentario_item").value;

    let comprobarIdRecibido = idRecibidoArray.find(element => element == ('' + idProducto + seccionId));

    if (comprobarIdRecibido) {
        Swal.fire({
            icon: "warning",
            title: "Advertencia!",
            text: "El producto, bodega y sección de destino ya existen en la lista. No se puede repetir estos elementos. ",
            confirmButtonColor: "#1AA689",
        })
        $('#modal_transladar_producto').modal('hide')
        return;
    }

    let productoSeccion = ''+idProducto + seccionId;

    idRecibidoArray.push('' + idProducto + seccionId);

    let html = `
        <tr id="tr${contador}">
                                    <td>
                                             <button class="btn btn-danger text-center" type="button" onclick="eliminarInput(${contador},${productoSeccion})"><i
                                                    class="fa-regular fa-rectangle-xmark"></i>
                                            </button>
                                    </dt>
                                    <td>
                                        <input id="producto${contador}" name="producto${contador}" type="text" value="${idProducto}" disabled class="form-control" required form="guardar_translados">
                                    </td>

                                    <td>
                                        <input id="nombreBodega${contador}" name="nombreBodega${contador}" type="text" value="${bodegaNombre}" disabled required class="form-control" form="guardar_translados">
                                        <input id="idBodega${contador}" name="idBodega${contador}" type="hidden" value="${bodegaId}" disabled required form="guardar_translados">
                                    </td>

                                    <td>
                                        <input id="nombreSegmento${contador}" name="nombreSegmento${contador}" type="text" value="${segmentoNombre}" disabled class="form-control" required form="guardar_translados">
                                        <input id="idSegmento${contador}" name="idSegmento${contador}" type="hidden" value="${segmentoId}" disabled form="guardar_translados">
                                    </td>

                                    <td>
                                        <input id="nombreSeccion${contador}" name="nombreSeccion${contador}" type="text" value="${seccionNombre}" disabled class="form-control" form="guardar_translados">
                                        <input id="idSeccion${contador}" name="idSeccion${contador}" type="hidden" value="${seccionId}" required form="guardar_translados">
                                    </td>

                                    <td>
                                        <input id="cantidad${contador}"  name="cantidad${contador}" type="number" value="${cantidad}" readonly class="form-control" form="guardar_translados">
                                    </td>

                                    <td>
                                        <input id="unidadMedida${contador}" name="unidadMedida${contador}" type="text" value="${medidaNombre}" disabled class="form-control" form="guardar_translados">
                                        <input id="unidadMedidaId${contador}" name="unidadMedidaId${contador}" type="hidden" value="${medidaId}" readonly form="guardar_translados">
                                        <input id="idRecibido${contador}" name="idRecibido${contador}" type="hidden" value="${idRecibido}" readonly form="guardar_translados">
                                        <input id="comentarioItem${contador}" name="comentarioItem${contador}" type="hidden" value="${comentario_item}" form="guardar_translados">
                                    </td>

                                    <td>
                                        <span>${comentario_item}</span>
                                    </td>

                                </tr>
        `



    $('#modal_transladar_producto').modal('hide')
    idCuerpoLista.insertAdjacentHTML('beforeend', html);
    document.getElementById("recibirProducto").reset();
    $('#recibirProducto').parsley().reset();

    arrayInputs.push(contador);

    contador++;
    return;
}

function listadoBodegaDestino(contadorTranslados) {


  let   contador = contadorTranslados;

    // console.log(idSeccion);
    // console.log(idProducto);
    $('#tbl_translados_destino').DataTable({
        "order": [6, 'desc'],
        "language": {
            "url": "/js/plugins/dataTables/i18n/Spanish.json"
        },
        pageLength: 10,
        responsive: true,
        "ajax": "/translado/destino/lista/"+contador,

        "columns": [{
                data: 'idProducto'
            },
            {
                data: 'nombre'
            },
            {
                data: 'simbolo'
            },
            {
                data: 'cantidad_disponible'
            },
            {
                data: 'bodega'
            },
            {
                data: 'descripcion'
            },
            {
                data: 'created_at'
            }


        ]


    });
}


function eliminarInput(id, productoSeccion) {
   const element = document.getElementById("tr" + id);
    element.remove();



    var myIndex = arrayInputs.indexOf(id);
    if (myIndex !== -1) {
        arrayInputs.splice(myIndex, 1);

    }

    productoSeccion = ''+productoSeccion;
    var myIndex2 = idRecibidoArray.indexOf(productoSeccion);

    if (myIndex2 !== -1) {
        idRecibidoArray.splice(myIndex2, 1);

    }


}


$(document).on('submit', '#guardar_translados', function(event) {
    event.preventDefault();
});

function abrirModalMotivo() {
    if (arrayInputs.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia!',
            text: 'No hay productos en la lista para transladar.',
            confirmButtonColor: '#1AA689',
        });
        return;
    }
    document.getElementById('motivo_traslado').value = '';
    document.getElementById('motivo_error').classList.add('d-none');
    $('#modal_motivo_traslado').modal('show');
}

$(document).on('click', '#btn_confirmar_traslado', function() {
    let motivo = document.getElementById('motivo_traslado').value.trim();
    if (!motivo) {
        document.getElementById('motivo_error').classList.remove('d-none');
        return;
    }
    document.getElementById('motivo_error').classList.add('d-none');
    $('#modal_motivo_traslado').modal('hide');
    guardarTranslado(motivo);
});


function guardarTranslado(motivo) {
    document.getElementById("btn_guardar_translado").disabled = true;

    var dataForm = new FormData($('#guardar_translados').get(0));

    // let longitudArreglo = arrayInputs.length;
    // for (var i = 0; i < longitudArreglo; i++) {
    //     dataForm.append("arregloIdInputs[]", arrayInputs[i]);
    // }


    let table = $('#tbl_translados_destino').DataTable();
    table.destroy();


        let text = arrayInputs.toString();
        dataForm.append("arregloIdInputs", text);
        dataForm.append("motivo", motivo);

        const formDataObj = {};

        dataForm.forEach((value, key) => (formDataObj[key] = value));


            const options = {
                headers: {"content-type": "application/json"}
            }

    axios.post('/translado/producto/bodega', formDataObj,options)
        .then(response => {

            let data = response.data;
            let contador = data.contadorTranslados;

            _trasladoIdActual = data.trasladoId || null;

            if (_trasladoIdActual) {
                $('#modal_imprimir_traslado').modal('show');
            }



            let table1 = $('#tbl_translados').DataTable();
            table1.destroy();





            //document.getElementById('destino').class
            document.getElementById('destino').classList.remove('d-none');
            document.getElementById("recibirProducto").reset();
            $('#recibirProducto').parsley().reset();

            listadoBodegaDestino(contador);

            return;


        })
        .catch(err => {
            //console.log(err)

            document.getElementById("btn_guardar_translado").disabled = false;
            console.log(err);
            $('#modal_transladar_producto').modal('hide')
            document.getElementById('btn_recibir_bodega').disabled = false;


            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ha ocurrido un error.",
            })

        })


}

function imprimirTrasladoPDF() {
    if (_trasladoIdActual) {
        window.open('/translado/imprimir/' + _trasladoIdActual, '_blank');
    }
}

function cerrarModalImpresionTraslado() {
    $('#modal_imprimir_traslado').modal('hide');
    window.location.reload();
}
