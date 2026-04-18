@push('styles')
<style>
/* ── PROFAC design system ── */
:root {
    --pf-grad: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    --pf-grad-hover: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    --pf-orange: #e67e22;
    --pf-radius: 10px;
    --pf-shadow: 0 2px 10px rgba(0,0,0,.10);
}

/* ── Tarjetas ── */
.cat-card {
    border: none;
    border-radius: var(--pf-radius);
    box-shadow: var(--pf-shadow);
    margin-bottom: 1.25rem;
    overflow: hidden;
}
.cat-card-header {
    background: var(--pf-grad);
    color: #fff;
    padding: 10px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.cat-card-header h6 {
    margin: 0;
    font-size: .875rem;
    font-weight: 700;
    letter-spacing: .04em;
    display: flex;
    align-items: center;
    gap: 7px;
    color: #fff;
}

/* ── Botón principal naranja ── */
.btn.btn-pf-primary,
a.btn.btn-pf-primary {
    background: var(--pf-grad) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    font-size: .8rem;
    padding: 5px 14px;
    box-shadow: 0 1px 4px rgba(230,126,34,.35) !important;
    transition: background .2s, box-shadow .2s;
}
.btn.btn-pf-primary:hover,
.btn.btn-pf-primary:focus,
a.btn.btn-pf-primary:hover {
    background: var(--pf-grad-hover) !important;
    color: #fff !important;
    box-shadow: 0 3px 8px rgba(230,126,34,.45) !important;
    text-decoration: none !important;
}

/* ── Tabla ── */
#tbl_listaCategoria thead th {
    background: #f8f0e6;
    color: #7d4600;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .03em;
    border-bottom: 2px solid #e8c49a;
    white-space: nowrap;
}
#tbl_listaCategoria tbody tr:hover { background-color: #fffbf5; }
#tbl_listaCategoria td { font-size: .83rem; vertical-align: middle; }

/* ── Modal principal ── */
#modalCategoriasPrecios .modal-content,
#modalSeleccionarCategoriasGeneral .modal-content {
    border: none;
    border-radius: var(--pf-radius);
    box-shadow: 0 8px 32px rgba(0,0,0,.18);
    overflow: hidden;
}
#modalCategoriasPrecios .modal-header {
    background: var(--pf-grad);
    padding: 12px 20px;
    border-bottom: none;
}
#modalCategoriasPrecios .modal-title {
    color: #fff;
    font-weight: 700;
    font-size: .95rem;
    letter-spacing: .03em;
}
#modalCategoriasPrecios .close {
    color: rgba(255,255,255,.8);
    text-shadow: none;
    opacity: 1;
    font-size: 1.4rem;
    padding: 0; margin: 0;
}
#modalCategoriasPrecios .close:hover { color: #fff; }
#modalCategoriasPrecios .modal-body {
    background: #fff;
    padding: 20px 24px 8px;
}
#modalCategoriasPrecios .modal-footer {
    background: #fafafa;
    border-top: 1px solid #f0e8dd;
    padding: 10px 20px;
}
#modalCategoriasPrecios .form-control {
    border-radius: 6px;
    font-size: .88rem;
    border-color: #d8cfc4;
    transition: border-color .2s, box-shadow .2s;
}
#modalCategoriasPrecios .form-control:focus {
    border-color: var(--pf-orange);
    box-shadow: 0 0 0 3px rgba(243,156,18,.18);
}
#modalCategoriasPrecios label {
    font-size: .8rem;
    font-weight: 600;
    color: #5a4a38;
    margin-bottom: 4px;
}

/* ── Modal selección categorías (modo general) ── */
#modalSeleccionarCategoriasGeneral .modal-header {
    background: linear-gradient(135deg, #f39c12 0%, #f0a500 100%);
    padding: 12px 20px;
    border-bottom: none;
}
#modalSeleccionarCategoriasGeneral .modal-title { color: #fff; font-weight: 700; }
#modalSeleccionarCategoriasGeneral .modal-footer { background: #fafafa; border-top: 1px solid #f0e8dd; }

/* ── Select2 ── */
.select2-container { z-index: 999 !important; width: 100% !important; font-size: .9rem; }
.select2-dropdown { z-index: 3050 !important; }
.select2-container--bootstrap4 .select2-selection--single {
    height: 38px; padding: 6px 12px;
    border-radius: .35rem; border: 1px solid #ced4da;
}
.select2-container--bootstrap4 .select2-selection__rendered { line-height: 28px; padding-left: .5rem; padding-right: 2rem; }
.select2-container--bootstrap4 .select2-selection__arrow { height: 34px; right: 8px; }
.select2-container--bootstrap4 .select2-selection__placeholder { color: #6c757d; }
.select2-container--bootstrap4 .select2-selection--single .select2-selection__clear {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
}
.modal .select2-container { width: 100% !important; }

/* ── Filtros plantilla ── */
.filtro-container { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
.filtro-select { min-width: 200px; flex: 1 1 220px; height: 38px; }
#btnDescargar { height: 38px; flex: 0 0 auto; }

/* ── Limpiar archivo ── */
#btnLimpiarArchivoPrecios {
    background: transparent; border: none; color: #dc3545;
    padding: .25rem .4rem; font-size: 1.2rem; line-height: 1;
    transition: all .2s; border-radius: .25rem;
}
#btnLimpiarArchivoPrecios:hover { background: rgba(220,53,69,.1); color: #c82333; transform: scale(1.1); }
#btnLimpiarArchivoPrecios:active { transform: scale(.95); }

/* ── Sticky thead ── */
.sticky-top { position: sticky; top: 0; z-index: 10; }

/* ── Overlay ── */
#overlayProcesandoPrecios {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,.7); z-index: 9999;
    display: none; justify-content: center; align-items: center;
}
#overlayProcesandoPrecios .overlay-content {
    background: #fff; padding: 30px; border-radius: 10px;
    text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,.3); min-width: 300px;
}

/* ── Responsive tablet/móvil ── */
@media (max-width: 767px) {
    #modalCategoriasPrecios .modal-dialog,
    #modalSeleccionarCategoriasGeneral .modal-dialog {
        margin: 10px;
        max-width: calc(100% - 20px);
    }
    #modalCategoriasPrecios .modal-body { padding: 16px 14px 6px; }
    #modalCategoriasPrecios .form-row > [class*="col-"] { flex: 0 0 100%; max-width: 100%; }
    #modalCategoriasPrecios .modal-footer,
    #modalSeleccionarCategoriasGeneral .modal-footer {
        flex-direction: column-reverse; gap: 8px; padding: 10px 14px;
    }
    #modalCategoriasPrecios .modal-footer .btn,
    #modalSeleccionarCategoriasGeneral .modal-footer .btn { width: 100%; text-align: center; }
    .cat-card-header { gap: 8px; }
    .cat-card-header h6 { font-size: .8rem; }
    #tbl_listaCategoria thead th, #tbl_listaCategoria td { font-size: .75rem; }
    .filtro-container { flex-direction: column !important; }
    .filtro-container .btn, .filtro-container .form-control,
    .filtro-container .filtro-item, .filtro-container .filtro-select { width: 100% !important; }
    #formSubirExcel { flex-direction: column !important; width: 100% !important; gap: 8px; }
    #formSubirExcel .position-relative, #formSubirExcel .btn { width: 100% !important; margin-left: 0 !important; }
}
@media (min-width: 992px) {
    .filtro-select { min-width: 240px; flex: 1 1 240px; }
}
</style>
@endpush

<div class="cat-card">
    <div class="cat-card-header">
        <h6><i class="fa fa-tags"></i> CATEGORÍA DE PRECIOS DE PRODUCTO</h6>
        <button type="button" class="btn btn-pf-primary btn-sm" data-toggle="modal" data-target="#modalCategoriasPrecios"
                style="background:rgba(255,255,255,.18) !important;border:1.5px solid rgba(255,255,255,.55) !important;box-shadow:none !important;">
            <i class="fa fa-plus mr-1"></i>+ Creación
        </button>
    </div>
    <div class="card-body p-2">
        <!-- TABLA -->
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="tbl_listaCategoria" class="table table-striped table-bordered table-hover">
                        <thead>
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
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="cat-card">
    <div class="cat-card-header">
        <h6><i class="fa fa-download"></i> PLANTILLA DE PRECIOS POR PRODUCTOS</h6>
    </div>
    <div class="card-body p-3">
        <form id="formExport" method="GET" action="{{ route('excel.plantilla') }}" class="d-flex flex-wrap filtro-container">
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
                <button type="submit" class="btn btn-pf-primary" id="btnDescargar" disabled
                        style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%) !important;color:#fff !important;border:none;">
                    <i class="fa fa-download mr-1"></i> Descargar plantilla
                </button>
            </div>
        </form>
    </div>
</div>

<div class="cat-card">
    <div class="cat-card-header">
        <h6><i class="fa fa-upload"></i> IMPORTACIÓN DE LA PLANTILLA DE PRECIOS DE PRODUCTO</h6>
    </div>
    <div class="card-body p-3">
        <!-- Mensaje informativo dinámico -->
        <div id="mensajeInfoImport" class="alert alert-info mb-3" style="display:none;">
            <i class="fa fa-info-circle mr-1"></i> <strong id="tituloInfoImport"></strong>
            <p class="mb-0 mt-2" id="descripcionInfoImport"></p>
        </div>

        <div class="d-flex justify-content-center align-items-center">
            <form id="formSubirExcel" class="d-flex align-items-center" enctype="multipart/form-data">
                @csrf
                <div class="position-relative d-flex align-items-center">
                    <input type="file" class="form-control filtro-select" name="archivo_excel" id="archivo_excel" accept=".xlsx" required>
                    <button type="button" id="btnLimpiarArchivoPrecios" class="position-absolute" style="right: 8px; display: none; z-index: 10;" title="Quitar archivo">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <button type="button" id="btnProcesarArchivoPrecios" class="btn btn-pf-primary ml-2"
                        style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%) !important;color:#fff !important;border:none;">
                    <i class="fa fa-search mr-1"></i> Procesar Archivo
                </button>
                <button type="button" id="btnFinalizarImportPrecios" class="btn btn-success ml-2" style="display:none;">
                    <i class="fa fa-check-circle mr-1"></i> Finalizar Actualización
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
                <h6><i class="fa fa-check-circle"></i> <b>Productos que se actualizarán (<span id="countActualizablesPrecios">0</span>)</b></h6>
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
                <h6><i class="fa fa-exclamation-triangle"></i> <b>Productos NO procesados (<span id="countNoActualizablesPrecios">0</span>)</b></h6>
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
      <div class="modal-header" style="background:linear-gradient(135deg,#f39c12 0%,#f0a500 100%);border-bottom:none;padding:12px 20px;">
        <h5 class="modal-title font-weight-bold" id="modalSelCatTitle" style="color:#fff;">
          <i class="fa fa-filter mr-2"></i> Categorías a Actualizar — Modo General
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="color:rgba(255,255,255,.8);text-shadow:none;opacity:1;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body px-4 py-3">
        <div class="alert alert-warning mb-3">
          <i class="fa fa-exclamation-triangle mr-1"></i>
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
                <i class="fa fa-check-double"></i> Seleccionar todas
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger" id="btnDeseleccionarTodasCat">
                <i class="fa fa-times"></i> Deseleccionar todas
              </button>
            </div>
          </div>
          <div id="checkboxCategoriasContainer" class="row px-2"></div>
        </div>
        <div id="errorCargaCategoriasModal" class="alert alert-danger mb-0" style="display:none;">
          <i class="fa fa-exclamation-circle mr-1"></i> No se pudieron cargar las categorías. Intente nuevamente.
        </div>
      </div>
      <div class="modal-footer border-0 bg-light">
        <span class="mr-auto small text-muted">
          <span id="contadorCatSeleccionadas">0</span> categoría(s) seleccionada(s) para actualizar
        </span>
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="btnCancelarSelCat">
          <i class="fa fa-times mr-1"></i>Cancelar
        </button>
        <button type="button" class="btn btn-pf-primary font-weight-bold" id="btnConfirmarProcesarGeneral" disabled
                style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%) !important;color:#fff !important;border:none;">
          <i class="fa fa-check-circle mr-1"></i> Confirmar y Procesar
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
      <div class="modal-header">
        <h5 class="modal-title" id="modalCategoriasPreciosTitle">
          <i class="fa fa-tags mr-2" style="opacity:.85;"></i>Categoría de Precios
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <form id="CreacionCatPrecios" autocomplete="off">

          <!-- Primera fila: Nombre -->
          <div class="form-row">
            <!-- Nombre de la categoría -->
            <div class="form-group col-md-6">
                <label for="nombre_cat_precio">Nombre de la Categoría</label>
                <input type="text" class="form-control"
                    id="nombre_cat_precio" name="nombre_cat_precio"
                    placeholder="Ej: Precios de Cliente estatal" maxlength="100" required>
            </div>

            <!-- Categoría de cliente -->
            <div class="form-group col-md-6">
                <label for="categoria_cliente_id">Categoría de Cliente</label>
                <select id="categoria_cliente_id"
                        name="categoria_cliente_id"
                        class="form-control"
                        data-url="{{ route('clientes.categorias.escala') }}"
                        required>
                    <option value="">Seleccione una categoría...</option>
                </select>
            </div>

            <div class="form-group col-md-6">
              <label for="porc_precio_a">% Precio A</label>
              <input type="number" class="form-control" id="porc_precio_a" name="porc_precio_a"
                placeholder="Ej: 5" min="0" max="100" step="1" inputmode="numeric">
            </div>
            <div class="form-group col-md-6">
              <label for="porc_precio_b">% Precio B</label>
              <input type="number" class="form-control" id="porc_precio_b" name="porc_precio_b"
                placeholder="Ej: 15" min="0" max="100" step="1" inputmode="numeric">
            </div>
            <div class="form-group col-md-6">
              <label for="porc_precio_c">% Precio C</label>
              <input type="number" class="form-control" id="porc_precio_c" name="porc_precio_c"
                placeholder="Ej: 20" min="0" max="100" step="1" inputmode="numeric">
            </div>
            <div class="form-group col-md-6">
              <label for="porc_precio_d">% Precio D</label>
              <input type="number" class="form-control" id="porc_precio_d" name="porc_precio_d"
                placeholder="Ej: 30" min="0" max="100" step="1" inputmode="numeric">
            </div>
          </div>

          <!-- Comentario -->
          <div class="form-group mt-1">
            <label for="comentario_cat_precio">Comentario</label>
            <textarea id="comentario_cat_precio" name="comentario_cat_precio" class="form-control" rows="3"
              placeholder="Ej: Precio 1 para categoría de cliente estatal"></textarea>
          </div>

        </form>
      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal" id="btnCancelarCategoria">
          <i class="fa fa-times mr-1"></i>Cancelar
        </button>
        <button type="submit" form="CreacionCatPrecios" class="btn btn-pf-primary btn-sm" id="btn_guardar_categoria"
                style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%) !important;color:#fff !important;border:none;">
          <i class="fa fa-save mr-1"></i>Guardar
        </button>
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

