
const $foto_producto = document.querySelector("#foto_producto_edit");

$foto_producto.addEventListener("change", () => {
    const files = $foto_producto.files;
    const grid = document.getElementById('previewGrid');
    const container = document.getElementById('previewContainer');
    const countEl = document.getElementById('previewCount');

    grid.innerHTML = '';

    if (!files || !files.length) {
        container.style.display = 'none';
        return;
    }

    if (files.length > 10) {
        Swal.fire({ icon: 'warning', title: 'Máximo 10 imágenes', text: 'Solo se subirán las primeras 10 imágenes seleccionadas.' });
    }

    const max = Math.min(files.length, 10);
    countEl.textContent = max;
    container.style.display = 'block';

    for (let i = 0; i < max; i++) {
        const file = files[i];
        const reader = new FileReader();
        const div = document.createElement('div');
        div.style.cssText = 'border-radius:8px;overflow:hidden;border:2px solid #e0e6ed;background:#f8fafc;min-height:90px;display:flex;align-items:center;justify-content:center;';
        const img = document.createElement('img');
        img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        reader.onload = (e) => { img.src = e.target.result; };
        reader.readAsDataURL(file);
        div.appendChild(img);
        grid.appendChild(div);
    }
});

$(document).on('submit', '#foto_productoForm', function(event) {

event.preventDefault();
guardarFoto();

});

function guardarFoto() {
$('#modal_foto_producto').modal('hide');
$('#modalSpinnerLoading').modal('show');

let data = new FormData($('#foto_productoForm').get(0));

let totalfiles = Math.min(document.getElementById('foto_producto_edit').files.length, 10);
for (var i = 0; i < totalfiles; i++) {
    data.append("files[]", document.getElementById('foto_producto_edit').files[i]);
};

axios.post('/ruta/imagen/edit', data)
    .then(response => {


        $('#modalSpinnerLoading').modal('hide');


        $('#foto_productoForm').parsley().reset();
        document.getElementById("foto_productoForm").reset();
        const pc = document.getElementById('previewContainer');
        const pg = document.getElementById('previewGrid');
        if (pc) pc.style.display = 'none';
        if (pg) pg.innerHTML = '';
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
        "url": "/js/plugins/dataTables/i18n/Spanish.json"
    },
    pageLength: 10,
    responsive: true,
    dom: '<"html5buttons"B>lTfgitp',
    buttons: [


    ],
    drawCallback: function() {
        var api = $('#tbl_lotes_listar').DataTable();
        var sum = 0;
        api.column(10).data().each(function(val) {
            var text = String(val).replace(/<[^>]*>/g, '').trim();
            sum += parseInt(text) || 0;
        });
        let html = 'Cantidad Total en Bodegas: ' + sum.toLocaleString('es-HN');
        $('#total_lotes').html(html);
    }



});

$('#tbl_unidades_listar').DataTable({

    "language": {
        "url": "/js/plugins/dataTables/i18n/Spanish.json"
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
        // isv_producto_edit: existe solo para admin (select) — se asigna con .value
        var isvEl = document.getElementById("isv_producto_edit");
        if (isvEl) isvEl.value = datos.datosProducto.isv;
        document.getElementById("cod_barra_producto_edit").value = datos.datosProducto.codigo_barra;
        document.getElementById("cod_estatal_producto_edit").value = datos.datosProducto.codigo_estatal;
        var precioBaseEditEl = document.getElementById("precioBase_edit");
        if (precioBaseEditEl) precioBaseEditEl.value = datos.datosProducto.precio_base;
        var costoPromedioEditarEl = document.getElementById("costo_promedio_editar");
        if (costoPromedioEditarEl) costoPromedioEditarEl.value = datos.datosProducto.costo_promedio;
        document.getElementById("unidades_editar").value = datos.datosProducto.unidadad_compra;
        var ultimoCostoEditarEl = document.getElementById("ultimo_costo_compra_editar");
        if (ultimoCostoEditarEl) ultimoCostoEditarEl.value = datos.datosProducto.ultimo_costo_compra;


        var precio1El = document.getElementById("precio1");
        if (precio1El) precio1El.value = datos.datosProducto.precio1;
        var precio2El = document.getElementById("precio2");
        if (precio2El) precio2El.value = datos.datosProducto.precio2;
        var precio3El = document.getElementById("precio3");
        if (precio3El) precio3El.value = datos.datosProducto.precio3;
        var precio4El = document.getElementById("precio4");
        if (precio4El) precio4El.value = datos.datosProducto.precio4;



        // Compatibilidad: estos IDs ya no existen en la vista actual.





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

        document.getElementById('tiempo_recuperacion_meses_edit').value = datos.datosProducto.tiempo_recuperacion_meses || '';
        document.getElementById('origen_edit').value = datos.datosProducto.origen || '';




        $('#exampleModal').modal('show');

    });


}

$(document).on('submit', '#editarProductoForm', function(event) {

event.preventDefault();
editarProducto();

});

function validacionPrecio(){


const precioBaseEl = document.getElementById('precioBase_edit');
if (!precioBaseEl) {
    return;
}

precioBase = precioBaseEl.value;

const precio1Input = document.getElementById('precio1');
const precio2Input = document.getElementById('precio2');
const precio3Input = document.getElementById('precio3');
const precio4Input = document.getElementById('precio4');

if (!precio1Input || !precio2Input || !precio3Input || !precio4Input) {
    return;
}

precio1Input.setAttribute("min",precioBase);
precio2Input.setAttribute("min",precioBase);
precio3Input.setAttribute("min",precioBase);
precio4Input.setAttribute("min",precioBase);


precio1 = Number(precioBase) + (precioBase*0.03);
precio2 = Number(precioBase) + (precioBase*0.06);
precio3 = Number(precioBase) + (precioBase*0.10);
precio4 = Number(precioBase) + (precioBase*0.3);

precio1Input.value = precio1.toFixed(2);
precio2Input.value = precio2.toFixed(2);
precio3Input.value = precio3.toFixed(2);
precio4Input.value = precio4.toFixed(2);





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

try {
    var data = new FormData($('#editarProductoForm').get(0));

    const precio1El = document.getElementById('precio1');
    const precio2El = document.getElementById('precio2');
    const precio3El = document.getElementById('precio3');
    const precio4El = document.getElementById('precio4');

    if (precio1El && precio2El && precio3El && precio4El) {
        data.append('precio1', precio1El.value);
        data.append('precio2', precio2El.value);
        data.append('precio3', precio3El.value);
        data.append('precio4', precio4El.value);
    }

    axios.post("/producto/editar", data)
    .then(response => {
        $('#modalSpinnerLoading').modal('hide');

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
} catch (error) {
    $('#modalSpinnerLoading').modal('hide');
    console.error(error);
    Swal.fire({
        icon: "error",
        title: "Error!",
        text: "No se pudo procesar la edición del producto.",
    });
}

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
