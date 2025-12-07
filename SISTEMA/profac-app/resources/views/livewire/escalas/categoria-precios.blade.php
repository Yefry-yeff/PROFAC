@push('styles')
<style>
.select2-dropdown { scroll-behavior: smooth; }
.select2-hidden-accessible {
  border: 0 !important;
  clip: rect(0 0 0 0) !important;
  height: 1px !important;
  margin: -1px !important;
  overflow: hidden !important;
  padding: 0 !important;
  position: absolute !important;
  width: 1px !important;
}
/* Asegurar buen padding del texto y clear dentro del select */
.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
  line-height: 28px;         /* ya lo tenías */
  padding-left: 0.5rem;      /* añade espacio para el texto */
  padding-right: 2rem;       /* deja espacio para el botón clear */
}

.select2-container--bootstrap4 .select2-selection--single .select2-selection__clear {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
}

/* =============================
   Encabezado y tarjetas
============================= */
.page-heading, .d-flex.bg-light {
    background-color: #f8f9fa;
    border-radius: 0.35rem;
    padding: 0.5rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

/* =============================
   Select2 — todos los selects
============================= */
.select2-container {
    z-index: 999 !important; /* Siempre encima de modales */
    width: 100% !important;   /* Ocupa todo el ancho del contenedor */
    font-size: 0.9rem;
}

.select2-dropdown {
    z-index: 3050 !important;
    max-height: 200px; /* Scroll si hay muchos items */
    overflow-y: auto;
}

/* Select2 estilo Bootstrap 4 */
.select2-container--bootstrap4 .select2-selection--single {
    height: 38px;          /* Altura igual a inputs grandes */
    padding: 6px 12px;
    border-radius: 0.35rem;
    border: 1px solid #ced4da;
}

.select2-container--bootstrap4 .select2-selection__rendered {
    line-height: 28px;     /* Ajuste vertical del texto */
}

.select2-container--bootstrap4 .select2-selection__arrow {
    height: 34px;
    right: 8px;
}

/* Placeholder gris más suave */
.select2-container--bootstrap4 .select2-selection__placeholder {
    color: #6c757d;
}

/* =============================
   Botones
============================= */
.btn-success, .btn-primary, .btn-outline-secondary {
    font-weight: 500;
    padding: 0.35rem 0.9rem;
    border-radius: 0.35rem;
}

/* =============================
   Contenedor de filtros (selects + botón)
============================= */
.filtro-container {
    display: flex;
    flex-wrap: wrap;        /* Para que en móviles se acomoden */
    gap: 0.5rem;            /* Espacio entre elementos */
    align-items: center;
}

.filtro-select {
    min-width: 200px;
    flex: 1 1 220px;       /* Crece hasta 220px */
    height: 38px;          /* Altura uniforme */
}

#btnDescargar {
    height: 38px;
    flex: 0 0 auto;        /* Botón no se encoge */
}

/* =============================
   Inputs y textareas
============================= */
textarea.form-control, input.form-control {
    border-radius: 0.35rem;
    border: 1px solid #ced4da;
    font-size: 0.9rem;
    padding: 6px 12px;
}

/* =============================
   Select2 dentro de modales
============================= */
.modal .select2-container {
    width: 100% !important;  /* Siempre ocupa todo el ancho de la columna */
}

/* =============================
   Responsivo móvil (≤576px)
============================= */
@media (max-width: 576px) {
    /* Contenedor general de formularios */
    form.d-flex {
        flex-direction: column;
    }

    form.d-flex > * {
        margin-bottom: 0.5rem;
    }

    /* Filtros dentro de header */
    .filtro-container {
        flex-direction: column;
        margin-left: 0;       /* Quitar alineación a la derecha */
        gap: 0.5rem;
    }

    #btnDescargar {
        width: 100%;          /* Botón ocupa todo el ancho */
    }

    /* Card header: cada div y botón ocupa 100% */
    .card-header .d-flex > div,
    .card-header .d-flex > button {
        width: 100%;
    }

    .card-header .d-flex {
        flex-direction: column;
        gap: 0.5rem;
    }
}

/* =============================
   Pantallas grandes (≥992px)
============================= */
@media (min-width: 992px) {
    .filtro-select {
        min-width: 240px;
        flex: 1 1 240px;
    }
}

/* Botón de eliminar archivo - diseño suave y profesional */
#btnLimpiarArchivoPrecios {
    background: transparent;
    border: none;
    color: #dc3545;
    padding: 0.25rem 0.4rem;
    font-size: 1.2rem;
    line-height: 1;
    transition: all 0.2s ease;
    border-radius: 0.25rem;
}

#btnLimpiarArchivoPrecios:hover {
    background-color: rgba(220, 53, 69, 0.1);
    color: #c82333;
    transform: scale(1.1);
}

#btnLimpiarArchivoPrecios:active {
    transform: scale(0.95);
}

#btnLimpiarArchivoPrecios i {
    font-weight: 600;
}

/* Sticky header para tablas de preview */
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

</style>
@endpush

<div class="mb-3 border-0 shadow-sm card">
    <div class="py-2 card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><b>CATEGORÍA DE PRECIOS DE PRODUCTO</b></h6>

    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalCategoriasPrecios">
        <i class="mr-1 bi bi-plus-circle"></i> + Creación

    </button>
    </div>
  <div class="p-2 card-body">

        <!-- TABLA -->
    <div class="mt-4 row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="tbl_listaCategoria" class="table table-striped table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Categoría</th>
                                    <th>Estado</th>
                                    <th>Categoria Cliente</th>
                                    <th>% A</th>
                                    <th>% B</th>
                                    <th>% C</th>
                                    <th>% D</th>
                                    <th>Creación</th>
                                    <th>Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-3 border-0 shadow-sm card">
    <div class="flex-wrap py-2 card-header bg-light d-flex align-items-center justify-content-between">
        <h6 class="mb-2 mb-md-0"><b>PLANTILLA DE PRECIOS POR PRODUCTOS</b></h6>

        <form id="formExport" method="GET" action="{{ route('excel.plantilla') }}" class="flex-wrap d-flex align-items-center filtro-container">
            <!-- Tipo de categoría -->
            <div class="filtro-item">
                <select id="tipoCategoria" name="tipoCategoria" class="form-control select2bs4 filtro-select">
                    <option value="">🧾 Tipo de categoría</option>
                    <option value="escalable">📈 Escalable</option>
                    <option value="manual">✍️ Manual</option>
                </select>
            </div>

            <!-- Tipo de filtro -->
            <div class="filtro-item">
                <select id="tipoFiltro" name="tipoFiltro" class="form-control select2bs4 filtro-select">
                    <option value="">📂 Formato</option>
                    <option value="1">🏷️ Marca</option>
                    <option value="2">📂 Categoría</option>
                </select>
            </div>

            <!-- Lista de filtro -->
            <div class="filtro-item">
                <select id="listaTipoFiltro" name="listaTipoFiltro" class="form-control select2bs4 filtro-select">
                    <option value="">Seleccione filtro</option>
                </select>
            </div>

            <!-- Categoria de precios -->
            <div class="filtro-item">
                <select id="listaTipoFiltroCatPrecios" name="listaTipoFiltroCatPrecios" class="form-control select2bs4 filtro-select" required>

                    <option value="">Seleccione Categoría de precio</option>
                </select>
            </div>

            <!-- Botón -->
            <div class="filtro-item">
                <button type="submit" class="btn btn-success" id="btnDescargar" disabled>
                    📥 Descargar plantilla
                </button>
            </div>
        </form>
    </div>
</div>

<div class="mb-3 border-0 shadow-sm card">
    <div class="flex-wrap py-2 card-header bg-light d-flex align-items-center justify-content-center">
        <h6 class="mb-0"><b>IMPORTACIÓN DE LA PLANTILLA DE PRECIOS DE PRODUCTO</b></h6>
    </div>
    <div class="p-2 card-body">
        <div class="d-flex justify-content-center align-items-center">
            <form id="formSubirExcel" class="d-flex align-items-center" enctype="multipart/form-data">
                @csrf
                <div class="position-relative d-flex align-items-center">
                    <input type="file" class="form-control filtro-select" name="archivo_excel" id="archivo_excel" accept=".xlsx" required>
                    <button type="button" id="btnLimpiarArchivoPrecios" class="position-absolute" style="right: 8px; display: none; z-index: 10;" title="Quitar archivo">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <button type="button" id="btnProcesarArchivoPrecios" class="btn btn-primary ml-2">
                    <i class="bi bi-search"></i> Procesar Archivo
                </button>
                <button type="submit" id="btnFinalizarImportPrecios" class="btn btn-success ml-2" style="display:none;">
                    <i class="bi bi-check-circle"></i> Finalizar Importación
                </button>
            </form>
        </div>

        <div class="progress mt-3" style="height:8px;">
            <div id="barImportPrecios" class="progress-bar" role="progressbar" style="width:0%"></div>
        </div>
        <div id="msgImportPrecios" class="small mt-2 text-muted"></div>

        <!-- Preview de productos a importar -->
        <div id="previewProductosImport" class="mt-4" style="display:none;">
            <div class="alert alert-success">
                <h6><i class="bi bi-check-circle"></i> <b>Productos que se importarán (<span id="countProductosImport">0</span>)</b></h6>
            </div>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm table-bordered table-hover">
                    <thead class="bg-success text-white sticky-top">
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Precio Base</th>
                            <th>Precio A</th>
                            <th>Precio B</th>
                            <th>Precio C</th>
                            <th>Precio D</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyProductosImport"></tbody>
                </table>
            </div>
        </div>

        <!-- Preview de errores -->
        <div id="previewErroresImport" class="mt-4" style="display:none;">
            <div class="alert alert-danger">
                <h6><i class="bi bi-exclamation-triangle"></i> <b>Registros con errores (<span id="countErroresImport">0</span>)</b></h6>
            </div>
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm table-bordered table-hover">
                    <thead class="bg-danger text-white sticky-top">
                        <tr>
                            <th>Fila</th>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyErroresImport"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>




<!-- MODAL ELEGANTE -->
<div class="modal fade" id="modalCategoriasPrecios" tabindex="-1" role="dialog"
     aria-labelledby="modalCategoriasPreciosTitle" aria-hidden="true"
     data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="border-0 rounded shadow-lg modal-content">

      <!-- Header -->
      <div class="text-white modal-header bg-primary rounded-top">
        <h5 class="modal-title font-weight-bold" id="modalCategoriasPreciosTitle">Categoría de Precios</h5>
        <button type="button" class="text-white close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="px-4 py-4 modal-body bg-light">
        <form id="CreacionCatPrecios" autocomplete="off">

          <!-- Primera fila: Nombre y Descripción -->
          <div class="form-row">
            <!-- Nombre de la categoría -->
            <div class="form-group col-md-6">
                <label for="nombre_cat_precio" class="font-weight-bold">Nombre de la Categoría</label>
                <input type="text" class="form-control form-control-lg border-primary"
                    id="nombre_cat_precio" name="nombre_cat_precio"
                    placeholder="Ej: Precios de Cliente estatal" maxlength="100" required>
            </div>

            <!-- Categoría de cliente -->
            <select id="categoria_cliente_id"
                    name="categoria_cliente_id"
                    class="form-control"
                    data-url="{{ route('clientes.categorias.escala') }}"  {{-- devuelve id, nombre_categoria --}}
                    required>
                <option value="">Seleccione una categoría...</option>
            </select>


            <div class="form-group col-md-6">
              <label for="porc_precio_a" class="font-weight-bold">% Precio A</label>
              <input type="number" class="form-control form-control-lg border-primary" id="porc_precio_a" name="porc_precio_a"
                placeholder="Ej: 5" min="0" max="100" step="1" inputmode="numeric">
            </div>
            <div class="form-group col-md-6">
              <label for="porc_precio_b" class="font-weight-bold">% Precio B</label>
              <input type="number" class="form-control form-control-lg border-primary" id="porc_precio_b" name="porc_precio_b"
                placeholder="Ej: 15"  min="0" max="100" step="1" inputmode="numeric">
            </div>
            <div class="form-group col-md-6">
              <label for="porc_precio_c" class="font-weight-bold">% Precio C</label>
              <input type="number" class="form-control form-control-lg border-primary" id="porc_precio_c" name="porc_precio_c"
                placeholder="Ej: 20" min="0" max="100" step="1" inputmode="numeric">
            </div>
            <div class="form-group col-md-6">
              <label for="porc_precio_d" class="font-weight-bold">% Precio D</label>
              <input type="number" class="form-control form-control-lg border-primary" id="porc_precio_d" name="porc_precio_d"
                placeholder="Ej: 30" min="0" max="100" step="1" inputmode="numeric">
            </div>
          </div>

          <!-- Comentario -->
          <div class="mt-4">
            <label for="comentario_cat_precio" class="font-weight-bold">Comentario</label>
            <textarea id="comentario_cat_precio" name="comentario_cat_precio" class="form-control border-primary" rows="3" placeholder="Ej: Precio 1 para categoría de cliente estatal">
            </textarea>
          </div>

          <!-- Footer -->
          <div class="mt-4 border-0 modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="btnCancelarCategoria">
              Cancelar
            </button>

            <button type="submit" class="btn btn-primary font-weight-bold" id="btn_guardar_categoria">
              Guardar
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>



@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <script src="{{ asset('js/js_proyecto/Escalas/gestionPrecios.js') }}"></script>

    <!-- Script para carga masiva de productos con preview -->
    <script>
    $(document).ready(function() {
        const fileInputPrecios = $('#archivo_excel');
        const btnLimpiarPrecios = $('#btnLimpiarArchivoPrecios');
        const btnProcesarPrecios = $('#btnProcesarArchivoPrecios');
        const btnFinalizarPrecios = $('#btnFinalizarImportPrecios');
        const barProgressPrecios = $('#barImportPrecios');
        const msgImportPrecios = $('#msgImportPrecios');
        const formSubirExcel = $('#formSubirExcel');

        // Mostrar/ocultar botón de limpiar archivo
        fileInputPrecios.on('change', function() {
            if (this.files.length > 0) {
                btnLimpiarPrecios.show();
                btnProcesarPrecios.prop('disabled', false);
            } else {
                btnLimpiarPrecios.hide();
                btnProcesarPrecios.prop('disabled', true);
            }
        });

        // Limpiar archivo seleccionado
        btnLimpiarPrecios.on('click', function() {
            fileInputPrecios.val('');
            btnLimpiarPrecios.hide();
            btnProcesarPrecios.prop('disabled', true);
            limpiarPreviewPrecios();
        });

        // Procesar archivo y mostrar preview
        btnProcesarPrecios.on('click', function(e) {
            e.preventDefault();
            
            if (fileInputPrecios[0].files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Advertencia',
                    text: 'Por favor seleccione un archivo.',
                });
                return;
            }

            // Obtener valores de los filtros del formulario de exportación
            const tipoCategoria = $('#tipoCategoria').val();
            const tipoFiltro = $('#tipoFiltro').val();
            const valorFiltro = $('#listaTipoFiltro').val();
            const categoriaPrecioId = $('#listaTipoFiltroCatPrecios').val();

            if (!tipoCategoria || !tipoFiltro || !valorFiltro || !categoriaPrecioId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Por favor complete todos los filtros antes de procesar el archivo.',
                });
                return;
            }

            const formData = new FormData();
            formData.append('archivo_excel', fileInputPrecios[0].files[0]);
            formData.append('tipoCategoria', tipoCategoria);
            formData.append('tipoFiltro', tipoFiltro);
            formData.append('valorFiltro', valorFiltro);
            formData.append('categoriaPrecioId', categoriaPrecioId);
            formData.append('_token', $('input[name="_token"]', formSubirExcel).val());

            barProgressPrecios.css('width', '30%');
            msgImportPrecios.text('Procesando archivo...');
            btnProcesarPrecios.prop('disabled', true);

            $.ajax({
                url: "{{ route('procesar.excel.precios') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(resp) {
                    barProgressPrecios.css('width', '100%').addClass('bg-success');
                    msgImportPrecios.text('Archivo procesado correctamente.');

                    if (resp.resumen) {
                        mostrarResumenImport(resp.resumen);
                        btnFinalizarPrecios.show();
                    }

                    Swal.fire({
                        icon: resp.icon || 'success',
                        title: resp.title || 'Éxito',
                        html: resp.text || 'Archivo procesado correctamente.',
                    });
                },
                error: function(xhr) {
                    barProgressPrecios.css('width', '100%').removeClass('bg-success').addClass('bg-danger');
                    msgImportPrecios.text('Error al procesar el archivo.');
                    btnProcesarPrecios.prop('disabled', false);

                    const resp = xhr.responseJSON || {};
                    Swal.fire({
                        icon: 'error',
                        title: resp.title || 'Error',
                        text: resp.text || 'Ocurrió un error al procesar el archivo.',
                    });
                }
            });
        });

        // Finalizar importación (este botón ya no se usa porque la importación es automática)
        formSubirExcel.on('submit', function(e) {
            e.preventDefault();
            
            Swal.fire({
                icon: 'success',
                title: 'Importación completada',
                text: 'Los precios se han procesado correctamente.',
            }).then(() => {
                limpiarTodoPrecios();
            });
        });

        function mostrarResumenImport(resumen) {
            if (resumen.leidas > 0) {
                $('#countProductosImport').text(resumen.insertadas || 0);
                $('#previewProductosImport').show();
                
                // Mostrar información resumida
                const tbody = $('#tbodyProductosImport');
                tbody.empty();
                const tr = $('<tr>');
                tr.append($('<td colspan="7" class="text-center">').html(
                    `<strong>Resumen de importación:</strong><br>
                    Filas leídas: ${resumen.leidas}<br>
                    Insertadas: ${resumen.insertadas}<br>
                    Inactivadas: ${resumen.inactivadas}<br>
                    Omitidas: ${resumen.omitidas}`
                ));
                tbody.append(tr);
            }

            if (resumen.errores && resumen.errores.length > 0) {
                mostrarErroresImport(resumen.errores);
            } else {
                $('#previewErroresImport').hide();
            }
        }

        function mostrarErroresImport(errores) {
            const tbody = $('#tbodyErroresImport');
            tbody.empty();

            errores.slice(0, 50).forEach(error => {
                const tr = $('<tr>');
                tr.append($('<td>').text(error.fila || 'N/A'));
                tr.append($('<td>').text(error.codigo || 'N/A'));
                tr.append($('<td>').text(error.descripcion || 'N/A'));
                tr.append($('<td>').text(error.motivo || error.error || 'Error desconocido'));
                tbody.append(tr);
            });

            $('#countErroresImport').text(errores.length);
            $('#previewErroresImport').show();
        }

        function limpiarPreviewPrecios() {
            $('#previewProductosImport').hide();
            $('#previewErroresImport').hide();
            $('#tbodyProductosImport').empty();
            $('#tbodyErroresImport').empty();
            btnFinalizarPrecios.hide();
            barProgressPrecios.css('width', '0%').removeClass('bg-success bg-danger');
            msgImportPrecios.text('');
        }

        function limpiarTodoPrecios() {
            fileInputPrecios.val('');
            btnLimpiarPrecios.hide();
            btnProcesarPrecios.prop('disabled', true);
            btnFinalizarPrecios.hide();
            limpiarPreviewPrecios();
        }
    });
    </script>
@endpush

