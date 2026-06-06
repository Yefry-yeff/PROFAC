// ── Foto: previsualización (grid multi-imagen) ───────────────────────────
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

// ── Subir foto ─────────────────────────────────────────────────────────────
$(document).on('submit', '#foto_productoForm', function (event) {
    event.preventDefault();
    guardarFotoDiseno();
});

function guardarFotoDiseno() {
    $('#modal_foto_producto').modal('hide');
    $('#modalSpinnerDisenoLoading').modal('show');

    let data = new FormData($('#foto_productoForm').get(0));
    let totalfiles = Math.min(document.getElementById('foto_producto_edit').files.length, 10);
    for (let i = 0; i < totalfiles; i++) {
        data.append("files[]", document.getElementById('foto_producto_edit').files[i]);
    }

    axios.post('/ruta/imagen/edit', data)
        .then(() => {
            $('#modalSpinnerDisenoLoading').modal('hide');
            $('#foto_productoForm').parsley().reset();
            document.getElementById("foto_productoForm").reset();
            const pc = document.getElementById('previewContainer');
            const pg = document.getElementById('previewGrid');
            if (pc) pc.style.display = 'none';
            if (pg) pg.innerHTML = '';
            Swal.fire({ icon: 'success', title: 'Éxito!', text: "Imagen guardada con éxito." });
            location.reload();
        })
        .catch(err => console.error(err));
}

// ── Eliminar imagen ────────────────────────────────────────────────────────
function eliminar(urlImagen) {
    axios.post("/producto/eliminar", { "urlImagen": urlImagen })
        .then(() => {
            Swal.fire({ icon: 'success', title: 'Éxito!', text: "Imagen eliminada con éxito." });
            location.reload();
        })
        .catch(err => console.error(err));
}

// ── Inicialización ─────────────────────────────────────────────────────────
$(document).ready(function () {
    var idProducto = document.getElementById('id_producto_diseno').value;
    obtenerDatosProductoDiseno(idProducto);

    $('#tbl_lotes_diseno').DataTable({
        language: { url: "/js/plugins/dataTables/i18n/Spanish.json" },
        pageLength: 10,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [],
        drawCallback: function () {
            var sum = $('#tbl_lotes_diseno').DataTable().column(9).data().sum();
            $('#total_lotes_diseno').html('Cantidad Total en Bodega: ' + sum);
        }
    });

    $('#tbl_unidades_diseno').DataTable({
        language: { url: "/js/plugins/dataTables/i18n/Spanish.json" },
        pageLength: 10,
        responsive: true,
        ajax: "/detalle/producto/unidad/" + idProducto,
        columns: [
            { data: 'contador' },
            { data: 'nombre' },
            { data: 'unidad_venta' },
            { data: 'editar' },
        ]
    });
});

// ── Cargar datos en modal (sin campos de precio) ───────────────────────────
function obtenerDatosProductoDiseno(id) {
    axios.get("/producto/datos/" + id)
        .then(response => {
            let datos = response.data;

            document.getElementById("nombre_producto_diseno").value      = datos.datosProducto.nombre;
            document.getElementById("descripcion_producto_diseno").value = datos.datosProducto.descripcion;
            document.getElementById("cod_barra_diseno").value            = datos.datosProducto.codigo_barra;
            document.getElementById("cod_estatal_diseno").value          = datos.datosProducto.codigo_estatal;
            document.getElementById("unidades_diseno").value             = datos.datosProducto.unidadad_compra;

            // Marcas
            let htmlMarca = "<option selected disabled>---Seleccione una marca---</option>";
            datos.marcas.forEach(m => {
                htmlMarca += `<option ${m.id == datos.datosProducto.marca_id ? 'selected' : ''} value="${m.id}">${m.nombre}</option>`;
            });

            // Categorías
            let htmlCategorias = "<option selected disabled>---Seleccione una categoría---</option>";
            datos.categorias.forEach(c => {
                htmlCategorias += `<option ${c.id == datos.categoria.id ? 'selected' : ''} value="${c.id}">${c.descripcion}</option>`;
            });

            // Subcategorías
            let htmlSub = "<option selected disabled>---Seleccione una subcategoría---</option>";
            datos.subCategorias.forEach(s => {
                htmlSub += `<option ${s.id == datos.subCategoria.id ? 'selected' : ''} value="${s.id}">${s.descripcion}</option>`;
            });

            // Unidades
            let htmlUnidades = "<option selected disabled>---Seleccione una unidad---</option>";
            datos.unidades.forEach(u => {
                htmlUnidades += `<option ${u.id == datos.datosProducto.unidad_medida_compra_id ? 'selected' : ''} value="${u.id}">${u.nombre}</option>`;
            });

            document.getElementById('marca_diseno').innerHTML          = htmlMarca;
            document.getElementById('categoria_diseno').innerHTML      = htmlCategorias;
            document.getElementById('sub_categoria_diseno').innerHTML  = htmlSub;
            document.getElementById('unidad_diseno').innerHTML         = htmlUnidades;
        })
        .catch(err => console.error(err));
}

// ── Cambio de categoría → recargar subcategorías ───────────────────────────
function listarSubCategoriasDiseno() {
    let categoriaId = document.getElementById('categoria_diseno').value;
    axios.get("/producto/sub-categorias/" + categoriaId)
        .then(response => {
            let html = "<option selected disabled>---Seleccione una subcategoría---</option>";
            response.data.forEach(s => {
                html += `<option value="${s.id}">${s.descripcion}</option>`;
            });
            document.getElementById('sub_categoria_diseno').innerHTML = html;
        })
        .catch(err => console.error(err));
}

// ── Guardar edición (solo campos no-precio) ────────────────────────────────
$(document).on('submit', '#editarProductoDisenoForm', function (event) {
    event.preventDefault();
    editarProductoDiseno();
});

function editarProductoDiseno() {
    $('#modalSpinnerDisenoLoading').modal('show');

    var data = new FormData($('#editarProductoDisenoForm').get(0));

    axios.post("/producto/diseno/editar", data)
        .then(() => {
            $('#modalSpinnerDisenoLoading').modal('hide');
            $('#editarProductoDisenoForm').parsley().reset();
            document.getElementById("editarProductoDisenoForm").reset();
            $('#modal_diseno_editar').modal('hide');
            Swal.fire({ icon: 'success', title: 'Éxito!', text: "Producto editado con éxito." });
            location.reload();
        })
        .catch(err => {
            $('#modalSpinnerDisenoLoading').modal('hide');
            $('#modal_diseno_editar').modal('hide');
            console.error(err);
            let data = err.response && err.response.data;
            if (data && data.icon) {
                Swal.fire({ icon: data.icon, title: data.title, text: data.text });
            } else {
                Swal.fire({ icon: "error", title: "Error!", text: "Ha ocurrido un error." });
            }
        });
}

// ── Editar unidades de venta ───────────────────────────────────────────────
function modalEditarUnidades(idVentas, unidadesVentas, idUnidadVentas) {
    $('#modal_editar_unidades').modal('show');

    axios.get('/detalle/unidades/venta')
        .then(response => {
            let html = "<option selected disabled>---Seleccione una unidad---</option>";
            response.data.unidades.forEach(u => {
                html += `<option ${u.id == idUnidadVentas ? 'selected' : ''} value="${u.id}">${u.nombre}</option>`;
            });
            document.getElementById("unidad_venta_editar").innerHTML = html;
            document.getElementById("unidades_venta_editar").value   = unidadesVentas;
            document.getElementById('idUniadVenta').value            = idVentas;
        })
        .catch(err => console.error(err));
}

$(document).on('submit', '#form_editar_unidades', function (event) {
    event.preventDefault();
    let data = new FormData($('#form_editar_unidades').get(0));
    axios.post('/detalle/unidades/editar', data)
        .then(() => {
            $('#modal_editar_unidades').modal('hide');
            Swal.fire({ icon: 'success', title: 'Éxito!', text: "Unidad actualizada con éxito." });
            location.reload();
        })
        .catch(err => console.error(err));
});
