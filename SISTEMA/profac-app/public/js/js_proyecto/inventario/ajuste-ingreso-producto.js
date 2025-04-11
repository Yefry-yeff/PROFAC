
var contador = 1;
var arrayInputs = [];
var idProductoSeccionArray = [];
var idProductoArray = [];
var idRecibido = null;


$(document).ready(function() {
    obtenerMotivos();
});


$('#selectBodega').select2({
    ajax: {
        url: '/ajustes/ingreso/listar/bodegas',
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





$(document).on('submit', '#datos_ajuste_form', function(event) {

    event.preventDefault();
    agregarProductoLista();

});

$(document).on('submit', '#selec_data_form', function(event) {

    event.preventDefault();
    datosProducto();

});

function obtenerSecciones() {
    let bodegaId = document.getElementById('selectBodega').value;
    let selectSeccion = document.getElementById('selectSeccion');
    selectSeccion.innerHTML = '<option value="" selected disabled>--Seleccionar un Sección --</option>';

    $('#selectSeccion').select2({
        ajax: {
            url: '/ajuste/ingreso/listar/secciones',
            data: function(params) {

                var query = {
                    search: params.term,
                    bodegaId: bodegaId,
                    type: 'public',
                    page: params.page || 1
                }


                return query;
            }
        }
    });
}

function obtenerProductosBodega() {


    let selectProducto = document.getElementById('selectProducto');
    selectProducto.innerHTML =
        '<option value="" selected disabled>--Seleccionar un producto por codigo ó nombre--</option>';

    $('#selectProducto').select2({
        ajax: {
            url: '/ajuste/ingreso/productos',
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
}







$(document).on('submit', '#ajustar_producto_form', function(event) {

    event.preventDefault();
    realizarAjuste();

});


function obtenerMotivos() {
    axios.get("/ajustes/motivos/listar")
        .then(response => {
            html = '<option value="" selected disabled>---Seleccionar una opción:---</option>';
            htmlUsers = '<option value="" selected disabled>---Seleccionar una opción:---</option>';
            let data = response.data.results;
            let usuarios = response.data.usuarios;

            data.forEach(element => {
                html += `<option value="${element.id}">${element.text}</option>`
            });

            usuarios.forEach(usuario => {
                htmlUsers += `<option value="${usuario.id}">${usuario.name}</option>`
            })

            let select = document.getElementById('tipo_ajuste_id');
            document.getElementById('solicitado_por').innerHTML = htmlUsers;
            select.innerHTML = html;
        })
        .catch(err => {
            console.log(err);
        })


}



function datosProducto() {
    let idSeccion = document.getElementById("selectSeccion").value;
    let idProducto = document.getElementById("selectProducto").value;
    axios.post('/ajuste/ingreso/datos/producto', {
            id: idProducto,
            idSeccion: idSeccion
        })
        .then(response => {

            let data = response.data.producto;
            let unidades = response.data.unidadesMedida;
            let datosBodega = response.data.datosBodega;

            document.getElementById("bodega").value = datosBodega.bodega;
            document.getElementById("seccion").value = datosBodega.seccion;

            let precioProducto = document.getElementById('precio_producto');
            precioProducto.value = data.precio_base;
            precioProducto.min = data.precio_base;


            document.getElementById('idProducto').value = data.id;
            document.getElementById('nombre_producto').value = data.nombre;

            document.getElementById('idRecibido').value = idRecibido


            let htmlUnidades =
                '<option value="" data-id="" selected disabled>---Seleccionar una unidad de medida:---</option>';

            unidades.forEach(unidad => {

                htmlUnidades += "<option value=" + unidad.unidad_venta + " data-id=" + unidad.id +
                    "  >" + unidad.nombre + "</option>";

            });

            document.getElementById('unidad').innerHTML = htmlUnidades;

            $('#modal_transladar_producto').modal('show')
        })
        .catch(err => {
            console.log(err)
            let data = err.response.data
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Ha ocurrido un error al obtener los datos del producto.",

            })

        })
}

function agregarProductoLista() {



    let idCuerpoLista = document.getElementById("cuerpoListaProducto");

    let idSeccion = document.getElementById("selectSeccion").value;

    let bodega = document.getElementById("bodega").value;
    let seccion = document.getElementById("seccion").value;

    //---------------------Formulario-----------------------------------//

    // let tipo_ajuste_id = document.getElementById("tipo_ajuste_id").value;
    // let solicitado_por = document.getElementById("solicitado_por").value;
    // let fecha = document.getElementById("fecha").value;


    let aritmetica = document.getElementById("aritmetica").value;

    let idProducto = document.getElementById("idProducto").value;
    let nombre_producto = document.getElementById("nombre_producto").value;

    let unidad = document.getElementById("unidad").value;
    let precio_producto = document.getElementById("precio_producto").value
    let cantidad = document.getElementById("cantidad").value;
    let total_unidades = document.getElementById("total_unidades").value;



    let aritmeticaNombre = "Sumar Unidades";


    let unidadSelect = document.getElementById("unidad");
    let unidadNombre = unidadSelect.options[unidadSelect.selectedIndex].text


    let e = document.getElementById('unidad');
    let idUnidadVenta = e.options[e.selectedIndex].getAttribute("data-id");

    //------------------------------------------------------------------//

    //-----------------------------Comprobar existencia de lote en arreglo------------------------//
    let comprobarIdRecibido = idProductoSeccionArray.find(element => element == ('' + idProducto + idSeccion));

    if (comprobarIdRecibido) {
        Swal.fire({
            icon: "warning",
            title: "Advertencia!",
            text: "El producto,  y sección ya existen en la lista. No se puede repetir estos elementos. ",
            confirmButtonColor: "#1AA689",
        })
        $('#modal_transladar_producto').modal('hide')
        return;
    }




    idProductoSeccionArray.push('' + idProducto + idSeccion);


    let html = `
        <tr id="tr${contador}">
            <td>
                <button class="btn btn-danger text-center" type="button" onclick="eliminarInput(${''+idProducto+idSeccion},${contador})" >
                    <i class="fa-regular fa-rectangle-xmark"></i>
                </button>
            </dt>
            <td>
                ${idProducto}
            </td>
            <td>
               ${nombre_producto}
            </td>

            <td>
                ${bodega} - ${seccion}
            </td>

            <td>
                ${aritmeticaNombre}
            </td>

            <td>
                ${cantidad}
            </td>
            <td>
                ${unidadNombre}
            </td>

                    <input type="hidden" id="idSeccion${contador}" name="idSeccion${contador}" value="${idSeccion}" form="ajustar_producto_form">
                    <input type="hidden" id="aritmetica${contador}" name="aritmetica${contador}" value="${aritmetica}" form="ajustar_producto_form">
                    <input type="hidden" id="idProducto${contador}" name="idProducto${contador}" value="${idProducto}" form="ajustar_producto_form">
                    <input type="hidden" id="nombre_producto${contador}" name="nombre_producto${contador}" value="${nombre_producto}" form="ajustar_producto_form">
                    <input type="hidden" id="precio_producto${contador}" name="precio_producto${contador}" value="${precio_producto}" form="ajustar_producto_form">
                    <input type="hidden" id="cantidad${contador}" name="cantidad${contador}" value="${cantidad}" form="ajustar_producto_form">
                    <input type="hidden" id="total_unidades${contador}" name="total_unidades${contador}" value="${total_unidades}" form="ajustar_producto_form">
                    <input type="hidden" id="idUnidadVenta${contador}" name="idUnidadVenta${contador}" value="${idUnidadVenta}" form="ajustar_producto_form">



        </tr>
`;

    $('#modal_transladar_producto').modal('hide')
    idCuerpoLista.insertAdjacentHTML('beforeend', html);
    document.getElementById("ajustar_producto_form").reset();
    $('#ajustar_producto_form').parsley().reset();
    arrayInputs.push(contador);

    contador++;

    document.getElementById("datos_ajuste_form").reset();
    $('#datos_ajuste_form').parsley().reset()

    return;


}

function realizarAjuste() {
    document.getElementById('btn_realizar_ajuste').disabled = false;
    //document.getElementById('btn_realizar_ajuste').disabled = true;

    let dataForm = new FormData($('#ajustar_producto_form').get(0));


    let text = arrayInputs.toString();
    dataForm.append("arregloIdInputs", text);

    const formDataObj = {};

    dataForm.forEach((value, key) => (formDataObj[key] = value));


    const options = {
        headers: {
            "content-type": "application/json"
        }
    }





    axios.post('/ajuste/ingreso/guardar', formDataObj, options)
        .then(response => {

            $('#modal_transladar_producto').modal('hide')
            let data = response.data;
            Swal.fire({
                icon: data.icon,
                title: data.title,
                html: data.text,

            })

            document.getElementById("ajustar_producto_form").reset();
            $('#ajustar_producto_form').parsley().reset();

            window.location.href = "/listado/ajustes";
        })
        .catch(err => {
            let data = err.response.data;
            $('#modal_transladar_producto').modal('hide')
            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text,

            })
            document.getElementById('btn_realizar_ajuste').disabled = false;

        })
}

function calcularTotalUnidades() {
    //let precio = document.getElementById('').value;
    let unidadesMedida = document.getElementById('unidad').value;
    let cantidad = document.getElementById('cantidad').value;


    if (unidadesMedida && cantidad) {
        let resultado = unidadesMedida * cantidad;
        document.getElementById('total_unidades').value = resultado;
    }

    return;
}

function eliminarInput(idProductoSeccion, idInput) {
    const element = document.getElementById("tr" + idInput);
    element.remove();

    console.log(arrayInputs, idProductoSeccionArray)

    let myIndex = arrayInputs.indexOf(idInput);
    if (myIndex !== -1) {
        arrayInputs.splice(myIndex, 1);

    }

    let myIndex2 = idProductoSeccionArray.indexOf('' + idProductoSeccion);
    console.log(myIndex2);
    if (myIndex2 !== -1) {
        idProductoSeccionArray.splice(myIndex2, 1);
    }

    console.log(arrayInputs, idProductoSeccionArray)


}
