

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

function guardarProducto(){
    $('#modalSpinnerLoading').modal('show');

    var data = new FormData($('#crearProductoForm').get(0));

    var totalfiles = document.getElementById('foto_producto').files.length;
    for (var i = 0; i < totalfiles; i++) {
        data.append("files[]", document.getElementById('foto_producto').files[i]);
    }

    axios.post("/producto/registrar", data)
    .then( response => {
        $('#modalSpinnerLoading').modal('hide');


        $('#crearProductoForm').parsley().reset();
        img = document.getElementById('imagenPrevisualizacion');
        img.src = "";
        document.getElementById("crearProductoForm").reset();
        $('#modal_producto_crear').modal('hide');

        $('#tbl_productosListar').DataTable().ajax.reload();

            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Producto creado con éxito."
            })

    })
    .catch( err =>{
        $('#modalSpinnerLoading').modal('hide');
        $('#modal_producto_crear').modal('hide');

        console.error(err);
        let data = err.response.data;
        if(data.icon){
            Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,
                })

        }else{
            Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "Ha ocurrido un error.",
                })

        }

    })

}

$(document).ready(function() {
    $('#tbl_productosListar').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [[0, 'desc']],
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json",
            "processing": '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Cargando...</span>'
        },
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "responsive": true,
        "dom": 'lfrtip',
        "ajax": {
            "url": "/producto/listar/productos",
            "type": "GET",
            "data": function(d) {
                // Parámetros adicionales si es necesario
            }
        },
        "columns": [
            { "data": "codigo", "name": "codigo" },
            { "data": "nombre", "name": "nombre" },
            { "data": "descripcion", "name": "descripcion" },
            { "data": "codigo_barra", "name": "codigo_barra" },
            { "data": "ISV", "name": "ISV", "searchable": false },
            { "data": "categoria", "name": "categoria" },
            { "data": "existencia", "name": "existencia", "searchable": false },
            { "data": "disponibilidad", "name": "disponibilidad", "orderable": false, "searchable": false }
        ]
    });
})

function disponibilidadProducto(id){
    axios.post("/producto/detalle", {"id":id})
}

///////////////////////////////////////////////////////////////////
function listarSubCategorias(){

    var categoria_produ = document.getElementById('categoria_producto').value;
      axios.get("/producto/sub_categoria/listar/"+categoria_produ)
      .then( response=>{
          let data = response.data.sub_categorias;

          let htmlSelect = '<option disabled selected>--Seleccione una Subcategoria--</option>'

          data.forEach(element => {
              htmlSelect += `<option value="${element.id}">${element.descripcion}</option>`
          });

          document.getElementById('sub_categoria_producto').innerHTML = htmlSelect;
      })
      .catch(err=>{
          console.log(err.response.data)
          Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Ha ocurrido un error',
          })
      })
  }
///////////////////////////////////////////////////////////////////
function validacionPrecio(){


    precioBase = document.getElementById('precioBase').value;

    document.getElementById('precio1').setAttribute("min",precioBase);
    document.getElementById('precio2').setAttribute("min",precioBase);
    document.getElementById('precio3').setAttribute("min",precioBase);
    document.getElementById('precio4').setAttribute("min",precioBase);



    precio1 = Number(precioBase) + (precioBase*0.03);
    precio2 = Number(precioBase) + (precioBase*0.06);
    precio3 = Number(precioBase) + (precioBase*0.10);
    precio4 = Number(precioBase) + (precioBase*0.3);

    document.getElementById('precio1').value = precio1.toFixed(2);
    document.getElementById('precio2').value = precio2.toFixed(2);
    document.getElementById('precio3').value = precio3.toFixed(2);
    document.getElementById('precio4').value = precio4.toFixed(2);





    /*if(precio1<precioBase || precio2<precioBase  || precio3<precioBase  || precio4<precioBase ){
        Swal.fire({
            icon: 'Info',
            title: 'Cuidado!',
            text: "PAsegurese de que los precios A, B, C y D no sean menores que el precio base del producto."
        })

    }*/




}
