

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
        "order": [0, 'desc'],
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
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
        "ajax": "/producto/listar/productos",
        "columns": [{
                data: 'codigo'
            },
            {
                data: 'nombre'
            },
            {
                data: 'descripcion'
            },
            {
                data: 'codigo_barra'
            },
            {
                data: 'ISV'
            },
            {
                data: 'categoria'
            },

            {
                data: 'existencia'
            },
            {
                data: 'disponibilidad'
            }

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
