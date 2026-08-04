

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

var filtroQ           = '';
var filtroDescripcion = '';
var filtroIsv         = '';
var filtroCategoriaId = '';
var filtroMarcaId     = '';
var filtroEstado      = '';

$(document).ready(function() {
    cargarFiltros();
    inicializarBusquedaCodigoBarra();
    $('#tbl_productosListar').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [[0, 'desc']],
        "language": {
            "url": "/js/plugins/dataTables/i18n/Spanish.json",
            "processing": '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Cargando...</span>'
        },
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "responsive": true,
        "dom": 'lrtip',
        "ajax": {
            "url": "/producto/listar/productos",
            "type": "GET",
            "data": function(d) {
                d.filtro_q           = filtroQ;
                d.filtro_descripcion = filtroDescripcion;
                d.filtro_isv         = filtroIsv;
                d.filtro_categoria_id = filtroCategoriaId;
                d.filtro_marca_id    = filtroMarcaId;
                d.filtro_estado      = filtroEstado;
            }
        },
        "columns": [
            { "data": "codigo", "name": "codigo" },
            { "data": "nombre", "name": "nombre" },
            { "data": "descripcion", "name": "descripcion" },
            { "data": "codigo_barra", "name": "codigo_barra" },
            {
                "data": "ISV", "name": "ISV", "searchable": false,
                "render": function(data) {
                    if (data == 0)  return '<span class="badge-isv-exento">Exento</span>';
                    if (data == 15) return '<span class="badge-isv-15">15%</span>';
                    if (data == 18) return '<span class="badge-isv-18">18%</span>';
                    return '<span class="badge-isv-15">' + data + '%</span>';
                }
            },
            { "data": "categoria", "name": "categoria" },
            {
                "data": "existencia", "name": "existencia", "searchable": false,
                "render": function(data) {
                    return '<span class="stock-num">' + data + '</span>';
                }
            },            { "data": "estado", "name": "estado", "orderable": false, "searchable": false },            { "data": "disponibilidad", "name": "disponibilidad", "orderable": false, "searchable": false }
        ]
    });
})

function inicializarBusquedaCodigoBarra() {
    var campoBusqueda = document.getElementById('fprod_q');
    var botonBuscar = document.getElementById('btn_fprod_buscar');
    var temporizadorBusqueda = null;

    if (!campoBusqueda || !botonBuscar) return;

    campoBusqueda.addEventListener('keydown', function(event) {
        if (event.key !== 'Enter') return;

        event.preventDefault();
        clearTimeout(temporizadorBusqueda);
        botonBuscar.click();
    });

    campoBusqueda.addEventListener('input', function() {
        clearTimeout(temporizadorBusqueda);
        var codigo = campoBusqueda.value.trim();

        if (!/^\d{4,}$/.test(codigo)) return;

        temporizadorBusqueda = setTimeout(function() {
            botonBuscar.click();
        }, 250);
    });
}

function cargarFiltros() {
    axios.get('/productos/buscar/categorias').then(function(r) {
        var opts = '<option value="">-- Todas --<\/option>';
        r.data.forEach(function(c) {
            opts += '<option value="' + c.id + '">' + c.text + '<\/option>';
        });
        document.getElementById('fprod_categoria').innerHTML = opts;
    }).catch(function() {});

    axios.get('/productos/buscar/marcas').then(function(r) {
        var opts = '<option value="">-- Todas --<\/option>';
        r.data.forEach(function(m) {
            opts += '<option value="' + m.id + '">' + m.text + '<\/option>';
        });
        document.getElementById('fprod_marca').innerHTML = opts;
    }).catch(function() {});
}

function aplicarFiltros() {
    filtroQ           = document.getElementById('fprod_q').value.trim();
    filtroDescripcion = document.getElementById('fprod_descripcion').value.trim();
    filtroIsv         = document.getElementById('fprod_isv').value;
    filtroCategoriaId = document.getElementById('fprod_categoria').value;
    filtroMarcaId     = document.getElementById('fprod_marca').value;
    filtroEstado      = document.getElementById('fprod_estado').value;
    $('#tbl_productosListar').DataTable().ajax.reload();
}

function limpiarFiltros() {
    document.getElementById('fprod_q').value           = '';
    document.getElementById('fprod_descripcion').value = '';
    document.getElementById('fprod_isv').value         = '';
    document.getElementById('fprod_categoria').value   = '';
    document.getElementById('fprod_marca').value       = '';
    document.getElementById('fprod_estado').value      = '';
    filtroQ = ''; filtroDescripcion = ''; filtroIsv = ''; filtroCategoriaId = ''; filtroMarcaId = ''; filtroEstado = '';
    $('#tbl_productosListar').DataTable().ajax.reload();
}

function exportarExcel() {
    var params = new URLSearchParams({
        filtro_q:            filtroQ,
        filtro_descripcion:  filtroDescripcion,
        filtro_isv:          filtroIsv,
        filtro_categoria_id: filtroCategoriaId,
        filtro_marca_id:     filtroMarcaId,
        filtro_estado:       filtroEstado,
    });
    window.location.href = '/producto/excel?' + params.toString();
}

function disponibilidadProducto(id){
    axios.post("/producto/detalle", {"id":id})
}

function abrirEditarProducto(id) {
    axios.get('/producto/datos/' + id)
    .then(function(response) {
        var d  = response.data;
        var p  = d.datosProducto;

        // Campos básicos
        document.getElementById('id_producto_edit').value              = p.id;
        document.getElementById('nombre_producto_edit').value          = p.nombre;
        document.getElementById('descripcion_producto_edit').value     = p.descripcion;
        document.getElementById('isv_producto_edit').value             = p.isv;
        document.getElementById('cod_barra_producto_edit').value       = p.codigo_barra || '';
        document.getElementById('cod_estatal_producto_edit').value     = p.codigo_estatal || '';
        document.getElementById('precioBase_edit').value               = p.precio_base;
        document.getElementById('costo_promedio_edit').value           = p.costo_promedio;
        document.getElementById('ultimo_costo_compra_edit').value      = p.ultimo_costo_compra;
        document.getElementById('unidades_editar').value               = p.unidadad_compra;
        document.getElementById('precio1_edit').value                  = p.precio1;
        document.getElementById('precio2_edit').value                  = p.precio2;
        document.getElementById('precio3_edit').value                  = p.precio3;
        document.getElementById('precio4_edit').value                  = p.precio4;
        document.getElementById('tiempo_recuperacion_meses_edit').value = p.tiempo_recuperacion_meses || '';
        document.getElementById('origen_edit').value                   = p.origen || '';

        // Marcas
        var marcaHtml = '';
        d.marcas.forEach(function(m) {
            marcaHtml += '<option value="' + m.id + '"' + (m.id == p.marca_id ? ' selected' : '') + '>' + m.nombre + '</option>';
        });
        document.getElementById('marca_producto_editar').innerHTML = marcaHtml;

        // Categorías
        var catHtml = '';
        d.categorias.forEach(function(c) {
            catHtml += '<option value="' + c.id + '"' + (c.id == d.categoria.id ? ' selected' : '') + '>' + c.descripcion + '</option>';
        });
        document.getElementById('categoria_producto_edit').innerHTML = catHtml;

        // Subcategorías
        var subHtml = '';
        d.subCategorias.forEach(function(s) {
            subHtml += '<option value="' + s.id + '"' + (s.id == p.sub_categoria_id ? ' selected' : '') + '>' + s.descripcion + '</option>';
        });
        document.getElementById('sub_categoria_producto_edit').innerHTML = subHtml;

        // Unidades
        var uniHtml = '';
        d.unidades.forEach(function(u) {
            uniHtml += '<option value="' + u.id + '"' + (u.id == p.unidad_medida_compra_id ? ' selected' : '') + '>' + u.nombre + '</option>';
        });
        document.getElementById('unidad_producto_editar').innerHTML = uniHtml;

        $('#modal_producto_editar').modal('show');
    })
    .catch(function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el producto.' });
    });
}

function listarSubCategoriasEdit() {
    var catId = document.getElementById('categoria_producto_edit').value;
    axios.get('/producto/sub_categoria/listar/' + catId)
    .then(function(response) {
        var html = '';
        response.data.sub_categorias.forEach(function(s) {
            html += '<option value="' + s.id + '">' + s.descripcion + '</option>';
        });
        document.getElementById('sub_categoria_producto_edit').innerHTML = html;
    })
    .catch(function() {});
}

function validacionPrecioEdit() {
    var base = parseFloat(document.getElementById('precioBase_edit').value) || 0;
    document.getElementById('precio1_edit').value = (base + base * 0.03).toFixed(2);
    document.getElementById('precio2_edit').value = (base + base * 0.06).toFixed(2);
    document.getElementById('precio3_edit').value = (base + base * 0.10).toFixed(2);
    document.getElementById('precio4_edit').value = (base + base * 0.30).toFixed(2);
}

function guardarEdicionProducto() {
    var form = document.getElementById('editarProductoForm');
    var data = new FormData(form);
    $('#modalSpinnerLoading').modal('show');
    axios.post('/producto/editar', data)
    .then(function() {
        $('#modalSpinnerLoading').modal('hide');
        $('#modal_producto_editar').modal('hide');
        $('#tbl_productosListar').DataTable().ajax.reload();
        Swal.fire({ icon: 'success', title: '¡Listo!', text: 'Producto editado correctamente.' });
    })
    .catch(function(err) {
        $('#modalSpinnerLoading').modal('hide');
        var data = err.response ? err.response.data : {};
        Swal.fire({ icon: data.icon || 'error', title: data.title || 'Error', text: data.text || 'No se pudo editar el producto.' });
    });
}

function cambiarEstadoProducto(id, nuevoEstado) {
    var esActivar = nuevoEstado === 1;
    Swal.fire({
        title: esActivar ? '¿Activar producto?' : '¿Inactivar producto?',
        text: esActivar
            ? 'El producto volverá a estar disponible.'
            : 'El producto quedará inactivo y no aparecerá en ventas.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: esActivar ? '#28a745' : '#e05a00',
        cancelButtonColor: '#6c757d',
        confirmButtonText: esActivar ? 'Sí, activar' : 'Sí, inactivar',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.isConfirmed) {
            axios.post('/producto/inactivar', { id: id, estado: nuevoEstado })
            .then(function() {
                var txt = esActivar ? 'Producto activado.' : 'Producto inactivado.';
                Swal.fire({ icon: 'success', title: '¡Listo!', text: txt });
                $('#tbl_productosListar').DataTable().ajax.reload();
            })
            .catch(function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cambiar el estado del producto.' });
            });
        }
    });
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
