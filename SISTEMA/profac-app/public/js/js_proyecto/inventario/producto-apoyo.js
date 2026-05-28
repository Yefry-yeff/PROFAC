/* =============================================
   CATÁLOGO APOYO — producto-apoyo.js
   Vendedores: sin precios, sin costos
============================================= */

var tblApoyo = null;

$(document).ready(function () {
    initTblApoyo();
    cargarFiltrosApoyo();
    bindFotoPreviewApoyo();
    // colapsar filtros por defecto
    $('#filtros-body-apoyo').hide();
    $('#ico-filtros-apoyo').removeClass('fa-chevron-down').addClass('fa-chevron-right');
});

/* ── DataTable ── */
function initTblApoyo() {
    tblApoyo = $('#tbl_apoyo_listar').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/apoyo/listar/productos',
            type: 'GET',
            data: function (d) {
                d.filtro_q           = $('#fap_q').val();
                d.filtro_descripcion = $('#fap_descripcion').val();
                d.filtro_categoria_id = $('#fap_categoria').val();
                d.filtro_marca_id    = $('#fap_marca').val();
                d.filtro_estado      = $('#fap_estado').val();
            }
        },
        columns: [
            { data: 'codigo',     name: 'codigo',     className: 'text-center', width: '60px' },
            { data: 'nombre',     name: 'nombre' },
            { data: 'descripcion',name: 'descripcion' },
            { data: 'codigo_barra',name: 'codigo_barra', width: '110px' },
            { data: 'categoria',  name: 'categoria',  width: '130px' },
            { data: 'existencia', name: 'existencia', className: 'text-center stock-num', orderable: false, width: '90px' },
            { data: 'estado',     name: 'estado',     className: 'text-center', orderable: false, width: '90px' },
            { data: 'acciones',   name: 'acciones',   className: 'text-center', orderable: false, width: '90px' }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        language: { url: '/vendor/datatables/Spanish.json' },
        drawCallback: function () {
            $('[data-toggle="tooltip"]').tooltip({ trigger: 'hover' });
        }
    });
}

/* ── Filtros ── */
function toggleFiltrosApoyo() {
    var $body = $('#filtros-body-apoyo');
    var $ico  = $('#ico-filtros-apoyo');
    if ($body.is(':visible')) {
        $body.slideUp(150);
        $ico.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
        $body.slideDown(150);
        $ico.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
}

function aplicarFiltrosApoyo() {
    if (tblApoyo) tblApoyo.ajax.reload();
}

function limpiarFiltrosApoyo() {
    $('#fap_q').val('');
    $('#fap_descripcion').val('');
    $('#fap_categoria').val('');
    $('#fap_marca').val('');
    $('#fap_estado').val('');
    if (tblApoyo) tblApoyo.ajax.reload();
}

function cargarFiltrosApoyo() {
    axios.get('/productos/buscar/categorias')
        .then(function (r) {
            var opts = '<option value="">Todas</option>';
            r.data.forEach(function (c) {
                opts += '<option value="' + c.id + '">' + c.descripcion + '</option>';
            });
            $('#fap_categoria').html(opts);
        }).catch(function () {});

    axios.get('/productos/buscar/marcas')
        .then(function (r) {
            var opts = '<option value="">Todas</option>';
            r.data.forEach(function (m) {
                opts += '<option value="' + m.id + '">' + m.nombre + '</option>';
            });
            $('#fap_marca').html(opts);
        }).catch(function () {});
}

/* ── Subcategorías dentro del modal crear ── */
function listarSubCategoriasApoyo() {
    var catId = $('#ap_categoria_producto').val();
    if (!catId) return;
    axios.get('/producto/sub_categoria/listar/' + catId)
        .then(function (r) {
            var subs = r.data.sub_categorias || [];
            var opts = '<option selected disabled>— Seleccione una subcategoría —</option>';
            subs.forEach(function (s) {
                opts += '<option value="' + s.id + '">' + s.descripcion + '</option>';
            });
            $('#ap_sub_categoria_producto').html(opts);
        })
        .catch(function () {
            Swal.fire('Error', 'No se pudieron cargar las subcategorías.', 'error');
        });
}

/* ── Vista previa de fotos (múltiples) ── */
function bindFotoPreviewApoyo() {
    $(document).on('change', '#ap_foto_producto', function () {
        var files = this.files;
        if (!files || files.length === 0) return;

        $('#ap_preview_placeholder').hide();
        $('#ap_imagenPrevisualizacion').hide();
        $('#ap_multi_preview').empty().css('display', 'flex').show();

        Array.from(files).forEach(function (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var $img = $('<img>')
                    .attr('src', e.target.result)
                    .css({
                        width: '70px', height: '70px',
                        objectFit: 'cover', borderRadius: '8px',
                        border: '2px solid #e0e6ed', margin: '3px'
                    });
                $('#ap_multi_preview').append($img);
            };
            reader.readAsDataURL(file);
        });
    });
}

/* ── Guardar producto (sin precios) ── */
function guardarProductoApoyo() {
    var nombre = $.trim($('#ap_nombre_producto').val());
    var desc   = $.trim($('#ap_descripcion_producto').val());
    var subcat = $('#ap_sub_categoria_producto').val();
    var unidad = $('#ap_unidad_producto').val();
    var unidades = $.trim($('#ap_unidades').val());

    if (!nombre) {
        Swal.fire('Atención', 'El nombre del producto es obligatorio.', 'warning');
        $('a[href="#tap-ap-general"]').tab('show');
        return;
    }
    if (!desc) {
        Swal.fire('Atención', 'La descripción del producto es obligatoria.', 'warning');
        $('a[href="#tap-ap-general"]').tab('show');
        return;
    }
    if (!subcat || subcat === '— Seleccione una subcategoría —') {
        Swal.fire('Atención', 'Seleccione una subcategoría.', 'warning');
        $('a[href="#tap-ap-clasif"]').tab('show');
        return;
    }
    if (!unidad || unidad === '— Seleccione —') {
        Swal.fire('Atención', 'Seleccione la unidad para compra.', 'warning');
        $('a[href="#tap-ap-clasif"]').tab('show');
        return;
    }
    if (!unidades || parseFloat(unidades) < 1) {
        Swal.fire('Atención', 'Ingrese la cantidad de unidades de compra (mínimo 1).', 'warning');
        $('a[href="#tap-ap-clasif"]').tab('show');
        return;
    }

    var formData = new FormData();
    formData.append('nombre_producto',       nombre);
    formData.append('descripcion_producto',  desc);
    formData.append('isv_producto',          $('#ap_isv_producto').val());
    formData.append('cod_barra_producto',    $('#ap_cod_barra_producto').val());
    formData.append('cod_estatal_producto',  $('#ap_cod_estatal_producto').val());
    formData.append('marca_producto',        $('#ap_marca_producto').val() || '');
    formData.append('sub_categoria_producto', subcat);
    formData.append('unidad_producto',       unidad);
    formData.append('unidades',              unidades);
    formData.append('tiempo_recuperacion_meses', $('#ap_tiempo_recuperacion_meses').val());
    formData.append('origen',                $('#ap_origen').val());

    var archivos = $('#ap_foto_producto')[0].files;
    for (var i = 0; i < archivos.length; i++) {
        formData.append('files[]', archivos[i]);
    }

    $('#modal_apoyo_crear').modal('hide');
    $('#modalSpinnerApoyo').modal('show');

    axios.post('/producto/apoyo/registrar', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    })
    .then(function (r) {
        $('#modalSpinnerApoyo').modal('hide');
        Swal.fire('Éxito', r.data.message, 'success').then(function () {
            limpiarFormCrearApoyo();
            if (tblApoyo) tblApoyo.ajax.reload();
        });
    })
    .catch(function (err) {
        $('#modalSpinnerApoyo').modal('hide');
        var msg = (err.response && err.response.data && err.response.data.message)
            ? err.response.data.message
            : 'Ocurrió un error al guardar el producto.';
        Swal.fire('Error', msg, 'error').then(function () {
            $('#modal_apoyo_crear').modal('show');
        });
    });
}

/* ── Reset formulario crear ── */
function limpiarFormCrearApoyo() {
    document.getElementById('crearProductoApoyoForm').reset();
    $('#ap_sub_categoria_producto').html('<option selected disabled>— Seleccione una subcategoría —</option>');
    $('#ap_multi_preview').empty().hide();
    $('#ap_preview_placeholder').show();
    $('a[href="#tap-ap-general"]').tab('show');
}

/* ── Inactivar / Activar ── */
function cambiarEstado(id, estado) {
    var accion = estado === 1 ? 'activar' : 'inactivar';
    Swal.fire({
        title: '¿Confirmar?',
        text: 'Va a ' + accion + ' este producto.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, ' + accion,
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) return;
        axios.post('/producto/inactivar', { id: id, estado: estado })
            .then(function (r) {
                Swal.fire('Listo', r.data.message, 'success').then(function () {
                    if (tblApoyo) tblApoyo.ajax.reload(null, false);
                });
            })
            .catch(function () {
                Swal.fire('Error', 'No se pudo cambiar el estado.', 'error');
            });
    });
}

/* reset al cerrar modal */
$('#modal_apoyo_crear').on('hidden.bs.modal', function () {
    limpiarFormCrearApoyo();
});
