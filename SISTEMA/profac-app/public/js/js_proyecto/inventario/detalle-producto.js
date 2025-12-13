
const $foto_producto = document.querySelector("#foto_producto_edit"),
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

$(document).on('submit', '#foto_productoForm', function(event) {

event.preventDefault();
guardarFoto();

});

function guardarFoto() {
$('#modal_foto_producto').modal('hide');
$('#modalSpinnerLoading').modal('show');

let data = new FormData($('#foto_productoForm').get(0));

let totalfiles = document.getElementById('foto_producto_edit').files.length;
for (var i = 0; i < totalfiles; i++) {
    data.append("files[]", document.getElementById('foto_producto_edit').files[i]);
};

console.log(data);
axios.post('/ruta/imagen/edit', data)
    .then(response => {


        $('#modalSpinnerLoading').modal('hide');


        $('#foto_productoForm').parsley().reset();
        img = document.getElementById('imagenPrevisualizacion');
        img.src = "";
        document.getElementById("foto_productoForm").reset();
        $('#modal_foto_producto').modal('hide');


        Swal.fire({
            icon: 'success',
            title: 'Exito!',
            text: "Imagen guardada con exito."
        });

        location.reload();

    })
    .catch(err => {
        console.error(err);

    })
}

$(document).ready(function() {

var idProducto_edit = document.getElementById('id_producto_edit').value;
obtenerDatosProductoEditar(idProducto_edit);

$('#tbl_lotes_listar').DataTable({
    "language": {
        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
    },
    pageLength: 10,
    responsive: true,
    dom: '<"html5buttons"B>lTfgitp',
    buttons: [


    ],
    drawCallback: function() {
        var sum = $('#tbl_lotes_listar').DataTable().column(9).data().sum();
        let html = 'Cantidad Total en Bodega: ' + sum
        $('#total_lotes').html(html);
    }



});

$('#tbl_unidades_listar').DataTable({

    "language": {
        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
    },

    pageLength: 10,
    responsive: true,
    "ajax": "/detalle/producto/unidad/" + idProducto_edit,
    "columns": [{
            data: 'contador'
        },
        {
            data: 'nombre'
        },
        {
            data: 'unidad_venta'
        },
        // {
        //     data: 'eliminar'
        // },
        {
            data: 'editar'
        },
    ]


});

});


function obtenerDatosProductoEditar(id) {
var idProducto = document.getElementById('id_producto_edit').value;
axios.get("/producto/datos/" + idProducto)
    .then(response => {
        let datos = response.data;


        document.getElementById("nombre_producto_edit").value = datos.datosProducto.nombre;
        document.getElementById("descripcion_producto_edit").value = datos.datosProducto.descripcion;
        document.getElementById("isv_producto_edit").value = datos.datosProducto.isv;
        document.getElementById("isv_producto_edit").innerHTML += '<option selected value="' + datos
            .datosProducto.isv + '">' + datos.datosProducto.isv + ' % de ISV</option>';
        document.getElementById("cod_barra_producto_edit").value = datos.datosProducto.codigo_barra;
        document.getElementById("cod_estatal_producto_edit").value = datos.datosProducto.codigo_estatal;
        document.getElementById("precioBase_edit").value = datos.datosProducto.precio_base;
        document.getElementById("costo_promedio_editar").value = datos.datosProducto.costo_promedio;
        document.getElementById("unidades_editar").value = datos.datosProducto.unidadad_compra;
        document.getElementById("ultimo_costo_compra_editar").value = datos.datosProducto.ultimo_costo_compra;


        document.getElementById("precio1").value = datos.datosProducto.precio1;
        document.getElementById("precio2").value = datos.datosProducto.precio2;
        document.getElementById("precio3").value = datos.datosProducto.precio3;
        document.getElementById("precio4").value = datos.datosProducto.precio4;



        if (datos.preciosProducto.length != 0) {
            document.getElementById("precio2_edit").value = datos.preciosProducto[1].precio;
            document.getElementById("precio3_edit").value = datos.preciosProducto[2].precio;
        }





        let arrayMarcas = datos.marcas;
        let htmlMarca = "<option selected disabled>---Seleccione una marca de producto---</option>  ";

        arrayMarcas.forEach(marca => {
            if (marca.id == datos.datosProducto.marca_id) {
                htmlMarca += `<option selected value="${marca.id}">${marca.nombre}</option>`;
            } else {
                htmlMarca += `<option  value="${marca.id}">${marca.nombre}</option>`;
            }

        });

        let arrayCategorias = datos.categorias;
        let htmlCategorias = "<option selected disabled>---Seleccione una categoria---</option>"

        arrayCategorias.forEach(categoria => {
            if (categoria.id == datos.categoria.id) {
                htmlCategorias +=
                    `<option selected value="${categoria.id}">${categoria.descripcion}</option>`;
            } else {
                htmlCategorias +=
                    `<option  value="${categoria.id}">${categoria.descripcion}</option>`;
            }

        });


        let arrayUnidades = datos.unidades;
        let htmlUnidades = "<option selected disabled>---Seleccione una unidad---</option>"

        arrayUnidades.forEach(unidad => {
            if (unidad.id == datos.datosProducto.unidad_medida_compra_id) {
                htmlUnidades += `<option selected value="${unidad.id}">${unidad.nombre}</option>`;
            } else {
                htmlUnidades += `<option  value="${unidad.id}">${unidad.nombre}</option>`;
            }

        });



        let arraySubcategorias = datos.subCategorias;

        let htmlSubCategorias = "<option selected disabled>---Seleccione una sub categoria---</option>"

        arraySubcategorias.forEach(unidad => {
            if (unidad.id == datos.subCategoria.id) {
                htmlSubCategorias +=
                    `<option selected value="${unidad.id}">${unidad.descripcion}</option>`;
            } else {
                htmlSubCategorias += `<option  value="${unidad.id}">${unidad.descripcion}</option>`;
            }

        });









        document.getElementById('marca_producto_editar').innerHTML = htmlMarca;
        document.getElementById('categoria_producto_edit').innerHTML = htmlCategorias;
        document.getElementById('unidad_producto_editar').innerHTML = htmlUnidades;
        document.getElementById('sub_categoria_producto_edit').innerHTML = htmlSubCategorias;




        $('#exampleModal').modal('show');

    });


}

$(document).on('submit', '#editarProductoForm', function(event) {

event.preventDefault();
editarProducto();

});

function validacionPrecio(){


precioBase = document.getElementById('precioBase_edit').value;

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
function editarProducto() {
$('#modalSpinnerLoading').modal('show');

var data = new FormData($('#editarProductoForm').get(0));

let precio1 = document.getElementById('precio1').value;
let precio2 =  document.getElementById('precio2').value
let precio3 =  document.getElementById('precio3').value
let precio4 = document.getElementById('precio4').value
data.append('precio1', precio1);
data.append('precio2', precio2);
data.append('precio3', precio3);
data.append('precio4', precio4);
axios.post("/producto/editar", data)
    .then(response => {
        $('#modalSpinnerLoading').modal('hide');


        $('#editarProductoForm').parsley().reset();
        document.getElementById("editarProductoForm").reset();
        $('#modal_producto_editar').modal('hide');

        Swal.fire({
            icon: 'success',
            title: 'Exito!',
            text: "Producto Editado con exito."
        })

        location.reload();

    })
    .catch(err => {
        $('#modalSpinnerLoading').modal('hide');
        $('#modal_producto_editar').modal('hide');

        console.error(err);
        let data = err.response.data;
        if (data.icon) {
            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text,
            })
        } else {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Ha ocurrido un error.",
            })
        }

    })

}

function eliminar(urlImagen) {
//console.log("Esto es una URL --->     "+urlImagen)
axios.post("/producto/eliminar", {
        "urlImagen": urlImagen
    })
    .then(response => {

        Swal.fire({
            icon: 'success',
            title: 'Exito!',
            text: "Imagen eliminada con exito."
        })
        location.reload();

    })
    .catch(err => {
        console.error(err);

    });

}

function modalEditarUnidades(idVentas, unidadesVentas, idUnidadVentas) {
let id = idVentas;
let unidadesVenta = unidadesVentas;
let idUnidad = idUnidadVentas
$('#modal_editar_unidades').modal('show');

axios.get('/detalle/unidades/venta')
    .then(response => {

        let unidades = response.data.unidades;

        let htmlSelect = "<option selected disabled>---Seleccione una unidad---</option>";

        unidades.forEach(unidad => {
            if (unidad.id == idUnidad) {
                htmlSelect += `<option selected value="${unidad.id}">${unidad.nombre}</option>`;
            } else {
                htmlSelect += `<option  value="${unidad.id}">${unidad.nombre}</option>`;
            }
        });

        document.getElementById("unidad_venta_editar").innerHTML = htmlSelect;
        document.getElementById("unidades_venta_editar").value = unidadesVenta;
        document.getElementById('idUniadVenta').value = id;



    })
    .catch(err => {
        console.log(err);
    })

}

$(document).on('submit', '#form_editar_unidades', function(event) {

event.preventDefault();
editarUnidadesVenta();

});

function editarUnidadesVenta() {
var data = new FormData($('#form_editar_unidades').get(0));

axios.post("/detalle/unidades/editar", data)
    .then(response => {
        $("#modal_editar_unidades").modal("hide");
        Swal.fire({
            icon: 'success',
            title: 'Exito!',
            text: "Producto Editado con exito."
        })

        location.reload();
    })
    .catch(err => {
        console.log(err);
        $("#modal_editar_unidades").modal("hide");
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: "Ha ocurrido un error."
        })

    })
}


function actualizarCostos(idProducto) {

axios.post('/producto/actualizar/costos', {
        idProducto: idProducto
    })
    .then(response => {
        let data = response.data;

        if (data.ultimoCosto != 0 && data.costoPromedio != 0) {
            document.getElementById('ultimo_costo_compra_editar').value = data.ultimoCosto;
            document.getElementById('costo_promedio_editar').value = data.costoPromedio;
        }




    }).catch(err => {
        console.error(err);

    })

}

function listarSubCategorias() {

var categoria_produ = document.getElementById('categoria_producto_edit').value;
axios.get("/producto/sub_categoria/listar/" + categoria_produ)
    .then(response => {
        let data = response.data.sub_categorias;

        let htmlSelect = '<option disabled selected>--Seleccione una Subcategoria--</option>'

        data.forEach(element => {
            htmlSelect += `<option value="${element.id}">${element.descripcion}</option>`
        });

        document.getElementById('sub_categoria_producto_edit').innerHTML = htmlSelect;
    })
    .catch(err => {
        console.log(err.response.data)
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Ha ocurrido un error',
        })
    })
}

///////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////
