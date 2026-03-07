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

/* Overlay de carga */
#overlayProcesandoPrecios {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    display: none;
    justify-content: center;
    align-items: center;
}

#overlayProcesandoPrecios .overlay-content {
    background: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    min-width: 300px;
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
            <!-- Tipo de plantilla: Categoría o General -->
            <div class="filtro-item">
                <select id="tipoPlantilla" name="tipoPlantilla" class="form-control select2bs4 filtro-select">
                    <option value="">🎯 Tipo de plantilla</option>
                    <option value="categoria">📋 Por Categoría</option>
                    <option value="general">🌐 General</option>
                </select>
            </div>

            <!-- Tipo de categoría -->
            <div class="filtro-item" id="containerTipoCategoria" style="display:none;">
                <select id="tipoCategoria" name="tipoCategoria" class="form-control select2bs4 filtro-select">
                    <option value="">🧾 Tipo de categoría</option>
                    <option value="escalable">📈 Escalable</option>
                    <option value="manual">✍️ Manual</option>
                </select>
            </div>

            <!-- Tipo de filtro -->
            <div class="filtro-item" id="containerTipoFiltro" style="display:none;">
                <select id="tipoFiltro" name="tipoFiltro" class="form-control select2bs4 filtro-select">
                    <option value="">📂 Formato</option>
                    <option value="1">🏷️ Marca</option>
                    <option value="2">📂 Categoría</option>
                </select>
            </div>

            <!-- Lista de filtro -->
            <div class="filtro-item" id="containerListaFiltro" style="display:none;">
                <select id="listaTipoFiltro" name="listaTipoFiltro" class="form-control select2bs4 filtro-select">
                    <option value="">Seleccione filtro</option>
                </select>
            </div>

            <!-- Categoria de precios (solo visible en modo "Categoría") -->
            <div class="filtro-item" id="containerCatPrecios" style="display:none;">
                <select id="listaTipoFiltroCatPrecios" name="listaTipoFiltroCatPrecios" class="form-control select2bs4 filtro-select">
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
        <!-- Mensaje informativo dinámico -->
        <div id="mensajeInfoImport" class="alert alert-info mb-3" style="display:none;">
            <i class="bi bi-info-circle"></i> <strong id="tituloInfoImport"></strong>
            <p class="mb-0 mt-2" id="descripcionInfoImport"></p>
        </div>
        
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
                <button type="button" id="btnFinalizarImportPrecios" class="btn btn-success ml-2" style="display:none;">
                    <i class="bi bi-check-circle"></i> Finalizar Actualización
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
                            <th>Categoría Precio</th>
                            <th></th>Precio Base</th>
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

<!-- Modal: Seleccionar categorías a excluir (solo modo General) -->
<div class="modal fade" id="modalSeleccionarCategoriasGeneral" tabindex="-1" role="dialog"
     aria-labelledby="modalSelCatTitle" aria-hidden="true"
     data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="border-0 rounded shadow-lg modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title font-weight-bold" id="modalSelCatTitle">
          <i class="bi bi-funnel-fill mr-2"></i> Categorías a Actualizar — Modo General
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body px-4 py-3">
        <div class="alert alert-warning mb-3">
          <i class="bi bi-exclamation-triangle mr-1"></i>
          <strong>Modo General:</strong> Se actualizarán <strong>todas</strong> las categorías de precios activas.
          Desmarca las categorías que <strong>NO</strong> deseas actualizar.
        </div>
        <div id="loadingCategoriasModal" class="text-center py-4">
          <div class="spinner-border text-warning" role="status">
            <span class="sr-only">Cargando...</span>
          </div>
          <p class="mt-2 text-muted">Cargando categorías...</p>
        </div>
        <div id="listaCategoriasModal" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 font-weight-bold">Categorías de precios activas:</h6>
            <div>
              <button type="button" class="btn btn-sm btn-outline-success mr-1" id="btnSeleccionarTodasCat">
                <i class="bi bi-check-all"></i> Seleccionar todas
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger" id="btnDeseleccionarTodasCat">
                <i class="bi bi-x-square"></i> Deseleccionar todas
              </button>
            </div>
          </div>
          <div id="checkboxCategoriasContainer" class="row px-2"></div>
        </div>
        <div id="errorCargaCategoriasModal" class="alert alert-danger mb-0" style="display:none;">
          <i class="bi bi-exclamation-circle mr-1"></i> No se pudieron cargar las categorías. Intente nuevamente.
        </div>
      </div>
      <div class="modal-footer border-0 bg-light">
        <span class="mr-auto small text-muted">
          <span id="contadorCatSeleccionadas">0</span> categoría(s) seleccionada(s) para actualizar
        </span>
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="btnCancelarSelCat">
          Cancelar
        </button>
        <button type="button" class="btn btn-warning font-weight-bold" id="btnConfirmarProcesarGeneral" disabled>
          <i class="bi bi-check-circle mr-1"></i> Confirmar y Procesar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Overlay de carga para procesamiento -->
<div id="overlayProcesandoPrecios">
    <div class="overlay-content">
        <div class="spinner-border text-primary" role="status" style="width:3rem; height:3rem; margin-bottom:20px;">
            <span class="sr-only">Cargando...</span>
        </div>
        <h5 class="mb-2"><strong id="tituloOverlayPrecios">Procesando archivo...</strong></h5>
        <p class="text-muted mb-0" id="mensajeOverlayPrecios">Por favor espere mientras se validan los datos</p>
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

        // Gestión dinámica de filtros según tipo de plantilla
        $('#tipoPlantilla').on('change', function() {
            const tipoPlantilla = $(this).val();
            
            // Resetear todos los filtros
            $('#tipoCategoria').val('').trigger('change');
            $('#tipoFiltro').val('').trigger('change');
            $('#listaTipoFiltro').val('').trigger('change');
            $('#listaTipoFiltroCatPrecios').val('').trigger('change');
            $('#btnDescargar').prop('disabled', true);
            
            // Ocultar todos los contenedores
            $('#containerTipoCategoria').hide();
            $('#containerTipoFiltro').hide();
            $('#containerListaFiltro').hide();
            $('#containerCatPrecios').hide();
            
            // Limpiar archivo y mensajes
            fileInputPrecios.val('');
            btnLimpiarPrecios.hide();
            btnFinalizarPrecios.hide();
            btnProcesarPrecios.show();
            $('#previewActualizablesPrecios').hide();
            $('#previewNoActualizablesPrecios').hide();
            barProgressPrecios.css('width', '0%');
            msgImportPrecios.text('');
            
            // Actualizar mensaje informativo
            if (tipoPlantilla === 'categoria') {
                // Modo Categoría: mostrar todos los filtros
                $('#containerTipoCategoria').show();
                $('#tituloInfoImport').text('Modo: Por Categoría');
                $('#descripcionInfoImport').html('Los precios se actualizarán <strong>solo para la categoría de precios seleccionada</strong>. El archivo debe contener los productos filtrados por marca o categoría.');
                $('#mensajeInfoImport').removeClass('alert-warning').addClass('alert-info').show();
            } else if (tipoPlantilla === 'general') {
                // Modo General: mostrar filtros excepto categoría de precios
                $('#containerTipoCategoria').show();
                $('#tituloInfoImport').text('Modo: General');
                $('#descripcionInfoImport').html('Los precios se actualizarán <strong>para TODAS las categorías de precios activas</strong> del sistema. No necesita seleccionar una categoría específica.');
                $('#mensajeInfoImport').removeClass('alert-info').addClass('alert-warning').show();
            } else {
                $('#mensajeInfoImport').hide();
            }
        });

        // Al cambiar tipo de categoría
        $('#tipoCategoria').on('change', function() {
            const tipoPlantilla = $('#tipoPlantilla').val();
            if ($(this).val()) {
                $('#containerTipoFiltro').show();
            } else {
                $('#containerTipoFiltro').hide();
                $('#containerListaFiltro').hide();
                $('#containerCatPrecios').hide();
            }
            validarFormularioDescarga();
        });

        // Al cambiar tipo de filtro
        $('#tipoFiltro').on('change', function() {
            if ($(this).val()) {
                $('#containerListaFiltro').show();
            } else {
                $('#containerListaFiltro').hide();
                $('#containerCatPrecios').hide();
            }
            validarFormularioDescarga();
        });

        // Al cambiar lista de filtro
        $('#listaTipoFiltro').on('change', function() {
            const tipoPlantilla = $('#tipoPlantilla').val();
            if ($(this).val()) {
                if (tipoPlantilla === 'categoria') {
                    $('#containerCatPrecios').show();
                }
            } else {
                $('#containerCatPrecios').hide();
            }
            validarFormularioDescarga();
        });

        // Al cambiar categoría de precios
        $('#listaTipoFiltroCatPrecios').on('change', function() {
            validarFormularioDescarga();
        });

        // Validar formulario para habilitar/deshabilitar botón de descarga
        function validarFormularioDescarga() {
            const tipoPlantilla = $('#tipoPlantilla').val();
            const tipoCategoria = $('#tipoCategoria').val();
            const tipoFiltro = $('#tipoFiltro').val();
            const valorFiltro = $('#listaTipoFiltro').val();
            const categoriaPrecioId = $('#listaTipoFiltroCatPrecios').val();
            
            let valido = false;
            
            if (tipoPlantilla === 'categoria') {
                // Modo Categoría: requiere todos los campos
                valido = tipoCategoria && tipoFiltro && valorFiltro && categoriaPrecioId;
            } else if (tipoPlantilla === 'general') {
                // Modo General: requiere todo excepto categoría de precios
                valido = tipoCategoria && tipoFiltro && valorFiltro;
            }
            
            $('#btnDescargar').prop('disabled', !valido);
        }

        // Resetear cuando se cambie el archivo
        fileInputPrecios.on('change', function() {
            // Ocultar previews
            $('#previewActualizablesPrecios').hide();
            $('#previewNoActualizablesPrecios').hide();
            
            // Ocultar botón finalizar
            btnFinalizarPrecios.hide();
            btnProcesarPrecios.show();
            
            // Limpiar barra de progreso y mensajes
            barProgressPrecios.removeClass('bg-success bg-danger bg-info').css('width', '0%');
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
            
            // Ocultar botones
            btnLimpiarPrecios.hide();
            btnFinalizarPrecios.hide();
            btnProcesarPrecios.show();
            
            // Ocultar previews
            $('#previewActualizablesPrecios').hide();
            $('#previewNoActualizablesPrecios').hide();
            
            // Limpiar barra de progreso y mensajes
            barProgressPrecios.removeClass('bg-success bg-danger bg-info').css('width', '0%');
            msgImportPrecios.removeClass('text-danger').text('');
        });

        // =============================================
        // Función centralizada para ejecutar el AJAX de preview
        // =============================================
        function ejecutarPreviewPrecios(categoriasExcluidas) {
            const tipoPlantilla = $('#tipoPlantilla').val();
            const tipoCategoria = $('#tipoCategoria').val();
            const tipoFiltro    = $('#tipoFiltro').val();
            const valorFiltro   = $('#listaTipoFiltro').val();
            const categoriaPrecioId = $('#listaTipoFiltroCatPrecios').val();

            const formData = new FormData(formSubirExcel[0]);
            formData.append('tipoPlantilla', tipoPlantilla);
            formData.append('tipoCategoria', tipoCategoria);
            formData.append('tipoFiltro', tipoFiltro);
            formData.append('valorFiltro', valorFiltro);
            if (tipoPlantilla === 'categoria') {
                formData.append('categoriaPrecioId', categoriaPrecioId);
            }
            if (categoriasExcluidas && categoriasExcluidas.length > 0) {
                categoriasExcluidas.forEach(function(id) {
                    formData.append('categoriasExcluidas[]', id);
                });
            }

            // Ocultar previews anteriores
            $('#previewActualizablesPrecios').hide();
            $('#previewNoActualizablesPrecios').hide();
            btnFinalizarPrecios.hide();

            // Mostrar overlay de carga
            $('#overlayProcesandoPrecios').css('display', 'flex');
            $('#tituloOverlayPrecios').text('Procesando archivo...');
            $('#mensajeOverlayPrecios').text('Por favor espere mientras se validan los datos');

            barProgressPrecios.removeClass('bg-success bg-danger').addClass('bg-info').css('width', '0%');
            msgImportPrecios.removeClass('text-danger').text('Validando archivo...');

            $.ajax({
                url: "{{ route('preview.excel.precios') }}",
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
                    $('#overlayProcesandoPrecios').hide();
                    barProgressPrecios.addClass('bg-info').css('width', '100%');
                    msgImportPrecios.text('Preview generado - Revise los productos');

                    const debug = res.debug || {};
                    const rowsToProcess = debug.rows_to_process || 0;
                    const rowsSkipped = debug.rows_skipped || 0;
                    const skippedReasons = debug.skipped_reasons || [];
                    const productosParaProcesar = debug.productos_para_procesar || [];

                    if (rowsToProcess > 0 && productosParaProcesar.length > 0) {
                        $('#countActualizablesPrecios').text(productosParaProcesar.length);
                        let htmlActualizables = '';
                        productosParaProcesar.forEach(function(item) {
                            htmlActualizables += `
                                <tr>
                                    <td>${item.codigo || item.producto_id || 'N/A'}</td>
                                    <td>${item.descripcion || item.nombre || 'N/A'}</td>
                                    <td class="font-weight-bold text-info">${item.categoria_precio || 'N/A'}</td>
                                    <td>${item.precio_base || 'N/A'}</td>
                                    <td class="text-success font-weight-bold">${item.precio_a || 'N/A'}</td>
                                    <td class="text-success font-weight-bold">${item.precio_b || 'N/A'}</td>
                                    <td class="text-success font-weight-bold">${item.precio_c || 'N/A'}</td>
                                    <td class="text-success font-weight-bold">${item.precio_d || 'N/A'}</td>
                                </tr>
                            `;
                        });
                        $('#tablaActualizablesPrecios').html(htmlActualizables);
                        $('#previewActualizablesPrecios').show();
                        btnProcesarPrecios.hide();
                        btnFinalizarPrecios.show();
                    }

                    if (skippedReasons.length > 0) {
                        $('#countNoActualizablesPrecios').text(skippedReasons.length);
                        let htmlNoActualizables = '';
                        let tieneErroresFiltros = false;
                        skippedReasons.forEach(function(item, index) {
                            if (typeof item === 'object') {
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
                        if (tieneErroresFiltros) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Filtros no coinciden',
                                html: `
                                    <p><strong>ATENCIÓN:</strong> El archivo contiene productos que no coinciden con los filtros seleccionados.</p>
                                    <p class="text-success">Productos a procesar: ${rowsToProcess}</p>
                                    <p class="text-warning">Productos omitidos por filtros: ${skippedReasons.length}</p>
                                    <p class="text-muted mt-3">Verifique que el archivo corresponda a los filtros seleccionados (Marca/Categoría).</p>
                                `,
                            });
                        } else {
                            Swal.fire({
                                icon: res.icon || 'info',
                                title: res.title || 'Preview Generado',
                                html: `
                                    <p>Productos a procesar: <strong>${rowsToProcess}</strong></p>
                                    <p>Productos omitidos: <strong>${rowsSkipped}</strong></p>
                                    <p class="mt-3 text-primary">Revise los datos y presione "Finalizar Actualización" para confirmar.</p>
                                `,
                            });
                        }
                    } else if (rowsToProcess > 0) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Preview Generado',
                            html: `
                                <p>Productos a procesar: <strong>${rowsToProcess}</strong></p>
                                <p class="mt-3 text-primary">Todo correcto. Presione "Finalizar Actualización" para confirmar.</p>
                            `,
                        });
                    }
                },
                error: function(xhr) {
                    $('#overlayProcesandoPrecios').hide();
                    barProgressPrecios.addClass('bg-danger').css('width', '100%');
                    let t = 'Error al procesar el archivo.';
                    let debugInfo = '';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.text) t = xhr.responseJSON.text;
                        if (xhr.responseJSON.debug) {
                            const debug = xhr.responseJSON.debug;
                            if (typeof debug === 'object') {
                                debugInfo = `<br><small class="text-muted">Error: ${debug.message || ''}<br>Archivo: ${debug.file || ''} (Línea: ${debug.line || ''})</small>`;
                            } else {
                                debugInfo = `<br><small class="text-muted">${debug}</small>`;
                            }
                        }
                    }
                    msgImportPrecios.addClass('text-danger').text(t);
                    Swal.fire({ icon: 'error', title: 'Error', html: t + debugInfo });
                },
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            });
        }

        // =============================================
        // Procesar archivo para PREVIEW
        // =============================================
        btnProcesarPrecios.on('click', function(e) {
            e.preventDefault();

            // Validar archivo
            if (fileInputPrecios[0].files.length > 0) {
                const fileName = fileInputPrecios[0].files[0].name;
                const fileExt = fileName.split('.').pop().toLowerCase();
                if (fileExt !== 'xlsx') {
                    Swal.fire({ icon: 'error', title: 'Archivo inválido', text: 'Solo se permiten archivos con extensión .xlsx' });
                    return;
                }
            } else {
                Swal.fire({ icon: 'warning', title: 'Advertencia', text: 'Debe seleccionar un archivo' });
                return;
            }

            const tipoPlantilla  = $('#tipoPlantilla').val();
            const tipoCategoria  = $('#tipoCategoria').val();
            const tipoFiltro     = $('#tipoFiltro').val();
            const valorFiltro    = $('#listaTipoFiltro').val();
            const categoriaPrecioId = $('#listaTipoFiltroCatPrecios').val();

            if (!tipoPlantilla || !tipoCategoria || !tipoFiltro || !valorFiltro) {
                Swal.fire({ icon: 'warning', title: 'Campos requeridos', text: 'Por favor complete todos los filtros antes de procesar el archivo.' });
                return;
            }

            if (tipoPlantilla === 'categoria' && !categoriaPrecioId) {
                Swal.fire({ icon: 'warning', title: 'Categoría de precios requerida', text: 'Debe seleccionar una categoría de precios para el modo "Por Categoría".' });
                return;
            }

            // Modo General: mostrar modal de selección de categorías
            if (tipoPlantilla === 'general') {
                cargarCategoriasEnModalGeneral();
                $('#modalSeleccionarCategoriasGeneral').modal('show');
                return;
            }

            // Modo Categoría: procesar directamente
            ejecutarPreviewPrecios([]);
        });

        // =============================================
        // Lógica del modal de selección de categorías (Modo General)
        // =============================================
        function cargarCategoriasEnModalGeneral() {
            $('#loadingCategoriasModal').show();
            $('#listaCategoriasModal').hide();
            $('#errorCargaCategoriasModal').hide();
            $('#btnConfirmarProcesarGeneral').prop('disabled', true);
            $('#checkboxCategoriasContainer').html('');

            $.ajax({
                url: '/filtros/categoria/precios',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#loadingCategoriasModal').hide();
                    if (!data || data.length === 0) {
                        $('#errorCargaCategoriasModal').text('No hay categorías de precios activas en el sistema.').show();
                        return;
                    }
                    let html = '';
                    data.forEach(function(cat) {
                        html += `
                            <div class="col-md-4 col-sm-6 mb-2">
                                <div class="custom-control custom-checkbox border rounded p-2 bg-white">
                                    <input type="checkbox" class="custom-control-input cat-precio-check"
                                           id="cat_check_${cat.id}" value="${cat.id}" checked>
                                    <label class="custom-control-label font-weight-bold" for="cat_check_${cat.id}">
                                        ${cat.nombre}
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    $('#checkboxCategoriasContainer').html(html);
                    $('#listaCategoriasModal').show();
                    actualizarContadorCategorias();
                },
                error: function() {
                    $('#loadingCategoriasModal').hide();
                    $('#errorCargaCategoriasModal').show();
                }
            });
        }

        function actualizarContadorCategorias() {
            const total = $('.cat-precio-check:checked').length;
            $('#contadorCatSeleccionadas').text(total);
            $('#btnConfirmarProcesarGeneral').prop('disabled', total === 0);
        }

        $(document).on('change', '.cat-precio-check', function() {
            actualizarContadorCategorias();
        });

        $('#btnSeleccionarTodasCat').on('click', function() {
            $('.cat-precio-check').prop('checked', true);
            actualizarContadorCategorias();
        });

        $('#btnDeseleccionarTodasCat').on('click', function() {
            $('.cat-precio-check').prop('checked', false);
            actualizarContadorCategorias();
        });

        $('#btnConfirmarProcesarGeneral').on('click', function() {
            const todasLasIds = $('.cat-precio-check').map(function() { return parseInt($(this).val()); }).get();
            const seleccionadas = $('.cat-precio-check:checked').map(function() { return parseInt($(this).val()); }).get();
            const excluidas = todasLasIds.filter(function(id) { return !seleccionadas.includes(id); });

            $('#modalSeleccionarCategoriasGeneral').modal('hide');
            ejecutarPreviewPrecios(excluidas);
        });

        // FINALIZAR actualización de precios
        btnFinalizarPrecios.on('click', function(e) {
            e.preventDefault();

            const tipoPlantilla = $('#tipoPlantilla').val();
            let mensajeConfirmacion = 'Se actualizarán los precios de los productos mostrados en el preview.';
            
            if (tipoPlantilla === 'general') {
                mensajeConfirmacion = 'Se actualizarán los precios para TODAS las categorías activas del sistema.';
            }

            Swal.fire({
                title: '¿Confirmar actualización?',
                text: mensajeConfirmacion,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar overlay de carga
                    $('#overlayProcesandoPrecios').css('display', 'flex');
                    $('#tituloOverlayPrecios').text('Finalizando actualización...');
                    $('#mensajeOverlayPrecios').text('Actualizando precios en la base de datos');
                    
                    barProgressPrecios.removeClass('bg-info bg-danger').addClass('bg-success').css('width', '0%');
                    msgImportPrecios.text('Finalizando actualización...');
                    btnFinalizarPrecios.prop('disabled', true);

                    const formData = new FormData();
                    formData.append('tipoPlantilla', tipoPlantilla);

                    $.ajax({
                        url: "{{ route('finalizar.excel.precios') }}",
                        method: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
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
                            // Ocultar overlay
                            $('#overlayProcesandoPrecios').hide();
                            
                            barProgressPrecios.css('width', '100%');
                            msgImportPrecios.text('Actualización completada exitosamente');

                            const debug = res.debug || {};
                            const rowsInserted = debug.rows_inserted || 0;
                            const rowsInactivated = debug.rows_inactivated || 0;

                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualización Completada!',
                                html: `
                                    <p>Productos actualizados: <strong>${rowsInserted}</strong></p>
                                    <p>Productos inactivados: <strong>${rowsInactivated}</strong></p>
                                `,
                            }).then(() => {
                                // Limpiar todo
                                fileInputPrecios.val('');
                                btnLimpiarPrecios.hide();
                                btnFinalizarPrecios.hide();
                                btnProcesarPrecios.show();
                                $('#previewActualizablesPrecios').hide();
                                $('#previewNoActualizablesPrecios').hide();
                                barProgressPrecios.css('width', '0%');
                                msgImportPrecios.text('');
                            });
                        },
                        error: function(xhr) {
                            // Ocultar overlay
                            $('#overlayProcesandoPrecios').hide();
                            
                            barProgressPrecios.removeClass('bg-success').addClass('bg-danger').css('width', '100%');
                            let t = 'Error al finalizar la actualización.';
                            if (xhr.responseJSON && xhr.responseJSON.text) t = xhr.responseJSON.text;
                            msgImportPrecios.addClass('text-danger').text(t);
                            btnFinalizarPrecios.prop('disabled', false);
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: t
                            });
                        }
                    });
                }
            });
        });
    });
    </script>
@endpush

