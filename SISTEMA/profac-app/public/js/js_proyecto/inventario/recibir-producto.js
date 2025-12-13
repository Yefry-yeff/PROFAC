
function mostratModal(compraId, productoId) {



    idProducto = productoId;

    axios.post("/producto/recibir/datos", {
            compraId,
            productoId
        })
        .then(response => {
                let data = response.data.datosCompra;

                let cantidadElemento = document.getElementById("cantidad");
                cantidadElemento.setAttribute("max", data.cantidad_sin_asignar);

                document.getElementById('nompreProducto').value = data.nombre;
                document.getElementById('cantidadMax').value = data.cantidad_sin_asignar;


                $('#modalRecibirProducto').modal('show');

            }

        )
        .catch(err => {

            console.log(err)
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Ha ocurrido un error al obtener los datos generales de la compra.',
            })

        })

}

function listarBodegas() {

    document.getElementById('segmento').innerHTML =   '<option value="" selected disabled>---Seleccione un segmento---</option>';
    document.getElementById('seccion').innerHTML =    '<option value="" selected disabled>---Seleccione una sección---</option>';

    axios.get('/producto/recibir/bodega')
        .then(response => {

            //console.log(response)

            let array = response.data.listaBodegas;
            let htmlBodega = ' <option value="" selected disabled>---Seleccione una bodega---</option>';
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
                htmlSeccion += `
                    <option value="${element.id}">${element.descripcion}</option>
`

            })

            document.getElementById('seccion').innerHTML = htmlSeccion;

        })
        .catch(err => {

            console.log(err);

        })
}

$(document).on('submit', '#recibirProducto', function(event) {
    event.preventDefault();
    guardarProductoBodega();
});

function guardarProductoBodega() {

    document.getElementById("btn_recibir_bodega").disabled = true;
    var data = new FormData($('#recibirProducto').get(0));
    data.append('idCompra', idCompra);
    data.append('idProducto', idProducto);

    axios.post('/producto/recibir/guardar', data)
        .then(response => {

            $('#modalRecibirProducto').modal('hide');
            document.getElementById('recibirProducto').reset();

            $('#recibirProducto').parsley().reset();

            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: 'Producto recibido con exito.',
            })

            $('#tbl_recibir_compra').DataTable().ajax.reload();
            $('#tbl_producto_bodega').DataTable().ajax.reload();
            document.getElementById("btn_recibir_bodega").disabled = false;

        })
        .catch(err => {
            document.getElementById("btn_recibir_bodega").disabled = false;
            $('#modalRecibirProducto').modal('hide');
            //console.log(err.response.data);

            let data = err.response.data;
            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text,
            })
        })
}

function mostrarModalIncidencias(id) {
    this.idRecibido = id;
    $('#modalRecibirIncidencia').modal('show');
}

function mostrarModalExcedente(compraId, productoId) {
    this.idProducto = productoId;
    $('#modalRecibirExcedente').modal('show');
}


window.onload = listarBodegasExcedente;

function listarBodegasExcedente() {
    document.getElementById('segmentoExcedente').innerHTML =
        '<option value="" selected disabled>---Seleccione un segmento---</option>';
    document.getElementById('seccionExcedente').innerHTML =
        '<option value="" selected disabled>---Seleccione una sección---</option>';;
    axios.get('/producto/recibir/bodega')
        .then(response => {

            //console.log(response)

            let array = response.data.listaBodegas;
            let htmlBodega = ' <option value="" selected disabled>---Seleccione una bodega---</option>';
            array.forEach(element => {
                htmlBodega += `
    <option value="${element.id}">${element.nombre}</option>
    `

            })

            document.getElementById('bodegaExcedente').innerHTML = htmlBodega;


        })
        .catch(err => {

            console.log(err);

        })
}


function listarSegmentosExcedente() {

    let bodega = document.getElementById("bodegaExcedente").value;


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

            document.getElementById('segmentoExcedente').innerHTML = htmlSegmento;

        })
        .catch(err => {

            console.log(err);

        })
}

function listarSeccionesExcedente() {

    let segmento = document.getElementById("segmentoExcedente").value;


    axios.post('/producto/recibir/seccion', {
            idSegmento: segmento
        })
        .then(response => {

            //console.log(response)

            let array = response.data.listaSecciones;
            let htmlSeccion = '  <option value="" selected disabled>---Seleccione una sección---</option>';
            array.forEach(element => {
                htmlSeccion += `
                    <option value="${element.id}">${element.descripcion}</option>
`

            })

            document.getElementById('seccionExcedente').innerHTML = htmlSeccion;

        })
        .catch(err => {

            console.log(err);

        })
}

$(document).on('submit', '#recibirProductoExcedente', function(event) {
    event.preventDefault();
    guardarProductoBodegaExcedente();
});


function guardarProductoBodegaExcedente() {
    document.getElementById("btn_recibir_excedente").disabled = true;
    let tidProducto = this.idProducto;
    var data = new FormData($('#recibirProductoExcedente').get(0));
    data.append('idCompra', idCompra);
    data.append('idProducto', idProducto);

    axios.post("/producto/recibir/excedente", data)
        .then(response => {

            let data = response.data;
            $('#modalRecibirExcedente').modal('hide');
            document.getElementById('recibirProductoExcedente').reset();

            $('#recibirProductoExcedente').parsley().reset();

            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text,
            })

            $('#tbl_recibir_compra').DataTable().ajax.reload();
            $('#tbl_producto_bodega').DataTable().ajax.reload();
            document.getElementById("btn_recibir_excedente").disabled = false;

        })
        .catch(err => {
            document.getElementById("btn_recibir_excedente").disabled = false;
            $('#modalRecibirExcedente').modal('hide');
            //console.log(err.response.data);
            let data = err.response.data;
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ha ocurrido un error al intentar ingresar el producto excedente.",
            })
        })



}


$(document).on('submit', '#modalRecibirIncidencia', function(event) {
    event.preventDefault();
    incidenciaBodega();
});

function incidenciaBodega() {
    document.getElementById('btn_registro_incidencia').disabled = true;

    var data = new FormData($('#registrarIncidencia').get(0));
    data.append('idRecibido', idRecibido)

    axios.post("/producto/incidencia/bodega", data)
        .then(response => {
            let data = response.data;
            $('#modalRecibirIncidencia').modal('hide');
            document.getElementById('registrarIncidencia').reset();

            $('#registrarIncidencia').parsley().reset();

            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text,
            })

            document.getElementById('btn_registro_incidencia').disabled = false;

            idRecibido = null;
            return;

        })
        .catch(err => {

                let data = err.response.data;

                console.log(err)

                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,
                })

                return;

            }

        )

}

function mostrarModalIncidenciaSinAlmacenar(idCompra, idProducto) {
    this.idProducto = idProducto;
    $('#modalIncidenciaCompra').modal('show');


}

$(document).on('submit', '#registrarIncidenciaCompra', function(event) {
    event.preventDefault();
    incidenciaCompra();
});

function incidenciaCompra() {
    //
    document.getElementById('btn_registro_incidencia_compra').disabled = true;

    var data = new FormData($('#registrarIncidenciaCompra').get(0));
    data.append('idProducto', idProducto)
    data.append('idCompra', idCompra)

    axios.post("/producto/incidencia/compra", data)
        .then(response => {
            let data = response.data;
            $('#modalIncidenciaCompra').modal('hide');
            document.getElementById('registrarIncidenciaCompra').reset();

            $('#registrarIncidenciaCompra').parsley().reset();

            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text,
            })

            document.getElementById('btn_registro_incidencia_compra').disabled = false;

            this.idProducto = null;
            return;

        })
        .catch(err => {

                let data = err.response.data;

                console.log(err)

                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,
                })
                document.getElementById('btn_registro_incidencia_compra').disabled = false;
                return;

            }

        )

}




var idProducto = null;
var idRecibido = null;


$(document).ready(function() {

    listarBodegas();



    $('#tbl_recibir_compra').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        pageLength: 10,
        responsive: true,
        "ajax": "/producto/compra/recibir/listar/" + idCompra,
        "columns": [{
                "data": "contador"
            },
            {
                "data": "producto_id"
            },
            {
                "data": "nombre"
            },
            {
                "data": "precio_unidad"
            },
            {
                "data": "cantidad_comprada"

            },
            {
                "data": "cantidad_sin_asignar"
            },
            {
                "data": "sub_total_producto"
            },
            {
                "data": "isv"
            },
            {
                "data": "precio_total"
            },
            {
                "data": "fecha_expiracion"
            },
            {
                "data": "estado_recibido"
            },
            {
                "data": "opciones"
            },


        ]
    });


    $('#tbl_producto_bodega').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        pageLength: 10,
        responsive: true,
        "ajax": "/producto/lista/bodega/" + idCompra,
        "columns": [{
                "data": "id"
            },
            {
                "data": "producto"
            },
            {
                "data": "cantidad_compra_lote"
            },
            {
                "data": "departamento"
            },
            {
                "data": "municipio"
            },
            {
                "data": "bodega"
            },
            {
                "data": "direccion"
            },
            {
                "data": "seccion"
            },
            {
                "data": "cantidad_inicial_seccion"
            },
            {
                "data": "cantidad_disponible"
            },
            {
                "data": "name"
            },
            {
                "data": "created_at"
            },
            {
                "data": "opciones"
            }

        ]
    });

});
