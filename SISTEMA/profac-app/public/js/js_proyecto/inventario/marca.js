
const $foto_producto = document.querySelector("#foto_producto"),
$imagenPrevisualizacion = document.querySelector("#imagenPrevisualizacion");

// Escuchar cuando cambie
$foto_producto.addEventListener("change", () => {
// Los archivos seleccionados, pueden ser muchos o uno
const archivos = $foto_producto.files;
// Si no hay archivos salimos de la función y quitamos la imagen
if (!archivos || !archivos.length) {
    $imagenPrevisualizacion.src = "";
    return;
}
// Ahora tomamos el primer archivo, el cual vamos a previsualizar
const primerArchivo = archivos[0];
// Lo convertimos a un objeto de tipo objectURL
const objectURL = URL.createObjectURL(primerArchivo);
// Y a la fuente de la imagen le ponemos el objectURL
$imagenPrevisualizacion.src = objectURL;
});

$(document).on('submit', '#crearProductoForm', function(event) {

event.preventDefault();
guardarProducto();

});

function guardarProducto() {
$('#modalSpinnerLoading').modal('show');

var data = new FormData($('#crearProductoForm').get(0));

var totalfiles = document.getElementById('foto_producto').files.length;
for (var i = 0; i < totalfiles; i++) {
    data.append("files[]", document.getElementById('foto_producto').files[i]);
}

axios.post("/producto/marca/guardar", data)
    .then(response => {
        $('#modalSpinnerLoading').modal('hide');


        $('#crearProductoForm').parsley().reset();
        img = document.getElementById('imagenPrevisualizacion');
        img.src = "";
        document.getElementById("crearProductoForm").reset();
        $('#modal_producto_crear').modal('hide');

        $('#tbl_marcas_listar').DataTable().ajax.reload();


        Swal.fire({
            icon: 'success',
            title: 'Exito!',
            text: "Marca creado con exito."
        })

    })
    .catch(err => {
        let data = err.response.data;
        $('#modalSpinnerLoading').modal('hide');
        $('#modal_producto_crear').modal('hide');
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);

    })

}

$(document).ready(function() {
$('#tbl_marcas_listar').DataTable({
    "order": [0, 'desc'],
    "language": {
        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
    },
    pageLength: 10,
    responsive: true,
    dom: '<"html5buttons"B>lTfgitp',
    buttons: [{
            extend: 'copy'
        },
        {
            extend: 'csv'
        },
        {
            extend: 'excel',
            title: 'ExampleFile'
        },
        {
            extend: 'pdf',
            title: 'ExampleFile'
        },

        {
            extend: 'print',
            customize: function(win) {
                $(win.document.body).addClass('white-bg');
                $(win.document.body).css('font-size', '10px');

                $(win.document.body).find('table')
                    .addClass('compact')
                    .css('font-size', 'inherit');
            }
        }
    ],
    "ajax": "/producto/marca/listar",
    "columns": [
        {
            data: 'id'
        },
        {
            data: 'nombre'
        },
        {
            data: 'name'
        },
        {
            data: 'created_at'
        },
        {
            data: 'opciones'
        }

    ]


});
})


function datosMarca(id){

let data = {id:id}
axios.post('/producto/marca/datos',data)
.then( response =>{

    let datos = response.data.datos;

    document.getElementById('nombre_producto_editar').value = datos.nombre;
    document.getElementById('idMarca').value = datos.id;
    if(datos.url_img){
        document.getElementById('imagenPrevisualizacion_editar').src = '/marcas/'+datos.url_img;
    }


    $('#modal_producto_editar').modal('show');
})
.catch( err=>{
    console.log(err)
})
}

const $foto_producto_editar = document.querySelector("#foto_producto_editar"),
$imagenPrevisualizacion_editar = document.querySelector("#imagenPrevisualizacion_editar");

// Escuchar cuando cambie
$foto_producto_editar.addEventListener("change", () => {
// Los archivos seleccionados, pueden ser muchos o uno
const archivos = $foto_producto_editar.files;
// Si no hay archivos salimos de la función y quitamos la imagen
if (!archivos || !archivos.length) {
    $imagenPrevisualizacion_editar.src = "";
    return;
}
// Ahora tomamos el primer archivo, el cual vamos a previsualizar
const primerArchivo = archivos[0];
// Lo convertimos a un objeto de tipo objectURL
const objectURL = URL.createObjectURL(primerArchivo);
// Y a la fuente de la imagen le ponemos el objectURL
$imagenPrevisualizacion_editar.src = objectURL;
});

$(document).on('submit', '#modal_producto_editar', function(event) {

    event.preventDefault();
    editarMarca();

});


function editarMarca(){

$('#modalSpinnerLoading').modal('show');
var data = new FormData($('#editarProductoForm').get(0));
var totalfiles = document.getElementById('foto_producto_editar').files.length;

for (var i = 0; i < totalfiles; i++) {
    data.append("files[]", document.getElementById('foto_producto_editar').files[i]);
}

axios.post('/producto/marca/editar',data)
.then( response =>{
    $('#modalSpinnerLoading').modal('hide');


    $('#editarProductoForm').parsley().reset();
    img = document.getElementById('imagenPrevisualizacion');
    img.src = "";
    document.getElementById("editarProductoForm").reset();
    $('#modal_producto_editar').modal('hide');

    $('#tbl_marcas_listar').DataTable().ajax.reload();


    Swal.fire({
        icon: 'success',
        title: 'Exito!',
        text: "Marca editada con exito."
    })

})
.catch( err=>{
    let data = err.response.data;
        $('#modalSpinnerLoading').modal('hide');
        $('#modal_producto_editar').modal('hide');

        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);

})
}


