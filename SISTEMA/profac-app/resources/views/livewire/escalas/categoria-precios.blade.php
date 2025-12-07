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
            </form>
        </div>

        <div class="progress mt-3" style="height:8px;">
            <div id="barImportPrecios" class="progress-bar" role="progressbar" style="width:0%"></div>
        </div>
        <div id="msgImportPrecios" class="small mt-2 text-muted"></div>

        <!-- Preview de productos a actualizar -->
        <div id="previewActualizablesPrecios" class="mt-4" style="display:none;">
            <div class="alert alert-success">
                <h6><i class="bi bi-check-circle"></i> <b>Productos que se actualizarán (<span id="countActualizablesPrecios">0</span>)</b></h6>
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
                    <tbody id="tablaActualizablesPrecios"></tbody>
                </table>
            </div>
        </div>

        <!-- Preview de productos NO actualizables -->
        <div id="previewNoActualizablesPrecios" class="mt-4" style="display:none;">
            <div class="alert alert-warning">
                <h6><i class="bi bi-exclamation-triangle"></i> <b>Productos NO procesados (<span id="countNoActualizablesPrecios">0</span>)</b></h6>
            </div>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm table-bordered table-hover">
                    <thead class="bg-warning sticky-top">
                        <tr>
                            <th>Fila</th>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody id="tablaNoActualizablesPrecios"></tbody>
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
        const barProgressPrecios = $('#barImportPrecios');
        const msgImportPrecios = $('#msgImportPrecios');
        const formSubirExcel = $('#formSubirExcel');

        // Resetear cuando se cambie el archivo
        fileInputPrecios.on('change', function() {
            // Ocultar previews
            $('#previewActualizablesPrecios').hide();
            $('#previewNoActualizablesPrecios').hide();
            
            // Limpiar barra de progreso y mensajes
            barProgressPrecios.removeClass('bg-success bg-danger').css('width', '0%');
            msgImportPrecios.removeClass('text-danger').text('');
            
            // Mostrar u ocultar botón de limpiar
            if (this.files.length > 0) {
                btnLimpiarPrecios.show();
            } else {
                btnLimpiarPrecios.hide();
            }
        });

        // Limpiar archivo seleccionado
        btnLimpiarPrecios.on('click', function(e) {
            e.preventDefault();
            
            // Limpiar input
            fileInputPrecios.val('');
            
            // Ocultar botón X
            btnLimpiarPrecios.hide();
            
            // Ocultar previews
            $('#previewActualizablesPrecios').hide();
            $('#previewNoActualizablesPrecios').hide();
            
            // Limpiar barra de progreso y mensajes
            barProgressPrecios.removeClass('bg-success bg-danger').css('width', '0%');
            msgImportPrecios.removeClass('text-danger').text('');
        });

        // Procesar archivo para preview
        btnProcesarPrecios.on('click', function(e) {
            e.preventDefault();

            // Validar que el archivo sea .xlsx
            if (fileInputPrecios[0].files.length > 0) {
                const fileName = fileInputPrecios[0].files[0].name;
                const fileExt = fileName.split('.').pop().toLowerCase();
                
                if (fileExt !== 'xlsx') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo inválido',
                        text: 'Solo se permiten archivos con extensión .xlsx'
                    });
                    return;
                }
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Advertencia',
                    text: 'Debe seleccionar un archivo'
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

            const formData = new FormData(formSubirExcel[0]);
            formData.append('tipoCategoria', tipoCategoria);
            formData.append('tipoFiltro', tipoFiltro);
            formData.append('valorFiltro', valorFiltro);
            formData.append('categoriaPrecioId', categoriaPrecioId);
            
            // Ocultar previews anteriores
            $('#previewActualizablesPrecios').hide();
            $('#previewNoActualizablesPrecios').hide();

            barProgressPrecios.removeClass('bg-success bg-danger').css('width', '0%');
            msgImportPrecios.removeClass('text-danger').text('Procesando archivo...');

            $.ajax({
                url: "{{ route('procesar.excel.precios') }}",
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                xhr: function() {
                    const xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const p = Math.round((e.loaded / e.total) * 100);
                                barProgressPrecios.css('width', p + '%');
                            }
                        }, false);
                    }
                    return xhr;
                },
                success: function(res) {
                    barProgressPrecios.addClass('bg-success').css('width', '100%');
                    msgImportPrecios.text('Archivo procesado exitosamente');

                    // El backend actual procesa directamente, mostramos los datos del debug
                    const debug = res.debug || {};
                    const rowsInserted = debug.rows_inserted || 0;
                    const rowsSkipped = debug.rows_skipped || 0;
                    const rowsInactivated = debug.rows_inactivated || 0;
                    const skippedReasons = debug.skipped_reasons || [];
                    
                    // Obtener los productos procesados del debug si existen
                    const productosInsertados = debug.productos_insertados || [];
                    const productosInactivados = debug.productos_inactivados || [];

                    // Mostrar productos procesados con detalles completos
                    if (rowsInserted > 0 || rowsInactivated > 0) {
                        $('#countActualizablesPrecios').text(rowsInserted + rowsInactivated);
                        let htmlActualizables = '';
                        
                        // Si hay datos detallados de productos, mostrarlos
                        if (productosInsertados.length > 0) {
                            productosInsertados.forEach(function(item) {
                                htmlActualizables += `
                                    <tr>
                                        <td>${item.codigo || item.producto_id || 'N/A'}</td>
                                        <td>${item.descripcion || item.nombre || 'N/A'}</td>
                                        <td>${item.precio_base || item.precio_base_venta || 'N/A'}</td>
                                        <td class="text-success font-weight-bold">${item.precio_a || item.precio_venta_a || 'N/A'}</td>
                                        <td class="text-success font-weight-bold">${item.precio_b || item.precio_venta_b || 'N/A'}</td>
                                        <td class="text-success font-weight-bold">${item.precio_c || item.precio_venta_c || 'N/A'}</td>
                                        <td class="text-success font-weight-bold">${item.precio_d || item.precio_venta_d || 'N/A'}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            // Si no hay detalles, mostrar resumen
                            htmlActualizables = `
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <strong>Resumen de procesamiento:</strong><br>
                                        Productos insertados/actualizados: ${rowsInserted}<br>
                                        Productos inactivados: ${rowsInactivated}<br>
                                        Productos omitidos: ${rowsSkipped}
                                    </td>
                                </tr>
                            `;
                        }
                        
                        $('#tablaActualizablesPrecios').html(htmlActualizables);
                        $('#previewActualizablesPrecios').show();
                    }

                    // Mostrar productos NO procesados con detalles
                    if (skippedReasons.length > 0) {
                        $('#countNoActualizablesPrecios').text(skippedReasons.length);
                        let htmlNoActualizables = '';
                        let tieneErroresFiltros = false;
                        
                        skippedReasons.forEach(function(item, index) {
                            // Si item es un objeto con detalles
                            if (typeof item === 'object') {
                                // Detectar si hay errores de filtros
                                if (item.motivo && (item.motivo.includes('no pertenece a la marca') || item.motivo.includes('no pertenece a la categoría'))) {
                                    tieneErroresFiltros = true;
                                }
                                
                                htmlNoActualizables += `
                                    <tr>
                                        <td>${item.fila || index + 1}</td>
                                        <td>${item.codigo || item.producto_id || 'N/A'}</td>
                                        <td>${item.descripcion || item.nombre || 'N/A'}</td>
                                        <td class="text-danger">${item.motivo || item.razon || 'Error desconocido'}</td>
                                    </tr>
                                `;
                            } else {
                                // Si es solo un string
                                if (typeof item === 'string' && (item.includes('no pertenece a la marca') || item.includes('no pertenece a la categoría'))) {
                                    tieneErroresFiltros = true;
                                }
                                
                                htmlNoActualizables += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>N/A</td>
                                        <td>N/A</td>
                                        <td class="text-danger">${item}</td>
                                    </tr>
                                `;
                            }
                        });
                        
                        $('#tablaNoActualizablesPrecios').html(htmlNoActualizables);
                        $('#previewNoActualizablesPrecios').show();
                        
                        // Si hay errores de filtros, mostrar alerta especial
                        if (tieneErroresFiltros) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Filtros no coinciden',
                                html: `
                                    <p><strong>ATENCIÓN:</strong> El archivo contiene productos que no coinciden con los filtros seleccionados.</p>
                                    <p class="text-danger">Productos procesados: ${rowsInserted}</p>
                                    <p class="text-warning">Productos omitidos por filtros: ${skippedReasons.length}</p>
                                    <p class="text-muted mt-3">Verifique que el archivo corresponda a los filtros seleccionados (Marca/Categoría).</p>
                                `,
                            });
                            return; // No mostrar el Swal de éxito
                        }
                    }

                    // No limpiar automáticamente, dejar que el usuario revise
                    btnProcesarPrecios.show();
                    
                    Swal.fire({
                        icon: res.icon || 'success',
                        title: res.title || 'Éxito',
                        html: res.text || 'Archivo procesado correctamente.',
                    });
                },
                error: function(xhr) {
                    barProgressPrecios.addClass('bg-danger').css('width', '100%');
                    let t = 'Error al procesar el archivo.';
                    if (xhr.responseJSON && xhr.responseJSON.text) t = xhr.responseJSON.text;
                    msgImportPrecios.addClass('text-danger').text(t);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: t
                    });
                },
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            });
        });
    });
    </script>
@endpush

