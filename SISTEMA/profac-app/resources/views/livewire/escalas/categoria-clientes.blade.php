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
    border-radius: 0;
}

.cat-card-header h6 {
    margin: 0;
    font-size: .875rem;
    font-weight: 700;
    letter-spacing: .04em;
    display: flex;
    align-items: center;
    gap: 7px;
}

/* ── Botón principal naranja ── */
.btn-pf-primary {
    background: var(--pf-grad);
    color: #fff !important;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: .8rem;
    padding: 5px 14px;
    transition: background .2s, box-shadow .2s;
    box-shadow: 0 1px 4px rgba(230,126,34,.35);
}
.btn-pf-primary:hover,
.btn-pf-primary:focus {
    background: var(--pf-grad-hover);
    color: #fff !important;
    box-shadow: 0 3px 8px rgba(230,126,34,.45);
    text-decoration: none;
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
#tbl_listaCategoria tbody tr:hover {
    background-color: #fffbf5;
}
#tbl_listaCategoria td {
    font-size: .83rem;
    vertical-align: middle;
}

/* ── Modal ── */
#modalCategoriasClientes .modal-content {
    border: none;
    border-radius: var(--pf-radius);
    box-shadow: 0 8px 32px rgba(0,0,0,.18);
    overflow: hidden;
}

#modalCategoriasClientes .modal-header {
    background: var(--pf-grad);
    padding: 12px 20px;
    border-bottom: none;
}

#modalCategoriasClientes .modal-title {
    color: #fff;
    font-weight: 700;
    font-size: .95rem;
    letter-spacing: .03em;
}

#modalCategoriasClientes .close {
    color: rgba(255,255,255,.8);
    text-shadow: none;
    opacity: 1;
    font-size: 1.4rem;
    padding: 0;
    margin: 0;
}
#modalCategoriasClientes .close:hover { color: #fff; }

#modalCategoriasClientes .modal-body {
    background: #fff;
    padding: 20px 24px 8px;
}

#modalCategoriasClientes .modal-footer {
    background: #fafafa;
    border-top: 1px solid #f0e8dd;
    padding: 10px 20px;
}

#modalCategoriasClientes .form-control {
    border-radius: 6px;
    font-size: .88rem;
    border-color: #d8cfc4;
    transition: border-color .2s, box-shadow .2s;
}
#modalCategoriasClientes .form-control:focus {
    border-color: var(--pf-orange);
    box-shadow: 0 0 0 3px rgba(243,156,18,.18);
}
#modalCategoriasClientes label {
    font-size: .8rem;
    font-weight: 600;
    color: #5a4a38;
    margin-bottom: 4px;
}

/* ── Sección carga masiva ── */
.filtro-container {
    gap: .5rem;
    flex-wrap: wrap;
    align-items: center;
}
.filtro-select {
    min-width: 220px;
    flex: 1 1 220px;
    height: 38px;
    font-size: .9rem;
}
#btnDescargar { height: 38px; }

@media (max-width: 576px) {
    .filtro-container { flex-direction: column; gap: .5rem; }
    #btnDescargar, .filtro-select { width: 100%; }
}

/* ── Sticky preview thead ── */
.sticky-top { position: sticky; top: 0; z-index: 10; }

/* ── Override Bootstrap .btn para btn-pf-primary ── */
.btn.btn-pf-primary,
a.btn.btn-pf-primary {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    box-shadow: 0 1px 4px rgba(230,126,34,.35) !important;
}
.btn.btn-pf-primary:hover,
.btn.btn-pf-primary:focus,
a.btn.btn-pf-primary:hover {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%) !important;
    color: #fff !important;
    box-shadow: 0 3px 8px rgba(230,126,34,.45) !important;
    text-decoration: none !important;
}

/* ── Responsive: tablet y móvil ── */
@media (max-width: 767px) {
    #modalCategoriasClientes .modal-dialog {
        margin: 10px;
        max-width: calc(100% - 20px);
    }
    #modalCategoriasClientes .modal-body {
        padding: 16px 14px 6px;
    }
    #modalCategoriasClientes .form-row > [class*="col-"] {
        flex: 0 0 100%;
        max-width: 100%;
    }
    #modalCategoriasClientes .modal-footer {
        flex-direction: column-reverse;
        gap: 8px;
        padding: 10px 14px;
    }
    #modalCategoriasClientes .modal-footer .btn {
        width: 100%;
        text-align: center;
    }
    .cat-card-header {
        flex-wrap: wrap;
        gap: 8px;
    }
    .cat-card-header h6 {
        font-size: .8rem;
    }
    #tbl_listaCategoria thead th,
    #tbl_listaCategoria td {
        font-size: .75rem;
    }
    .filtro-container {
        flex-direction: column !important;
    }
    .filtro-container .btn,
    .filtro-container .form-control {
        width: 100% !important;
    }
    #formImportCategorias {
        flex-direction: column !important;
        width: 100% !important;
        gap: 8px;
    }
    #formImportCategorias .position-relative,
    #formImportCategorias .btn,
    #formImportCategorias .form-control {
        width: 100% !important;
        margin-left: 0 !important;
    }
}
</style>
@endpush
<!-- MODAL -->
<div class="modal fade" id="modalCategoriasClientes" tabindex="-1" role="dialog" aria-labelledby="modalCategoriasClientesTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">

      <!-- Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="modalCategoriasClientesTitle">
          <i class="fa fa-tag mr-2" style="opacity:.85;"></i>Nueva Categoría de Cliente
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <form id="clientesCreacionForm">

          <!-- Primera fila: Nombre y Descripción -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="nombre_cat">Nombre de la Categoría</label>
              <input type="text" class="form-control" id="nombre_cat" name="nombre_cat"
                placeholder="Ej: Clientes estatales" maxlength="100" required>
            </div>
            <div class="form-group col-md-6">
              <label for="descripcion_cat">Descripción</label>
              <input type="text" class="form-control" id="descripcion_cat" name="descripcion_cat"
                placeholder="Ej: Clientes institucionales o empresas" maxlength="150">
            </div>
          </div>

          <!-- Comentario -->
          <div class="form-group mt-1">
            <label for="comentario">Comentario</label>
            <textarea id="comentario" name="comentario" class="form-control" rows="3"
              placeholder="Agrega un comentario sobre esta categoría..."></textarea>
          </div>

        </form>
      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal" id="btnCancelarCategoria">
          <i class="fa fa-times mr-1"></i>Cancelar
        </button>
        <button type="submit" form="clientesCreacionForm" class="btn btn-pf-primary btn-sm" id="btn_guardar_categoria"
                style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%);color:#fff;border:none;">
          <i class="fa fa-save mr-1"></i>Guardar
        </button>
      </div>

    </div>
  </div>
</div>

<div class="cat-card">
    <div class="cat-card-header">
        <h6><i class="fa fa-list-ul"></i> CATEGORÍA DE CLIENTES</h6>
        <button type="button" class="btn btn-pf-primary btn-sm" data-toggle="modal" data-target="#modalCategoriasClientes"
                style="background:rgba(255,255,255,.18);border:1.5px solid rgba(255,255,255,.55);box-shadow:none;">
            <i class="fa fa-plus mr-1"></i>+ Creación
        </button>
    </div>
    <div class="card-body p-2">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="tbl_listaCategoria" class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th>Comentario</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th>Creación</th>
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
    <h6><i class="fa fa-upload"></i> PLANTILLA / CARGA MASIVA – CATEGORÍAS DE CLIENTE</h6>
  </div>
  <div class="card-body p-3">
    <div class="d-flex filtro-container">
      <a href="{{ route('clientes.plantilla.categorias') }}" class="btn btn-pf-primary" id="btnDescargar">
        <i class="fa fa-download mr-1"></i> Descargar Plantilla
      </a>

      <form id="formImportCategorias" class="d-flex align-items-center ml-2" enctype="multipart/form-data">
        @csrf
        <div class="position-relative d-flex align-items-center">
          <input type="file" class="form-control filtro-select" name="file" id="fileInputCategorias" accept=".xlsx" required>
          <button type="button" id="btnLimpiarArchivo" class="btn btn-sm btn-danger position-absolute" style="right: 5px; display: none; z-index: 10;" title="Quitar archivo">
            <i class="fa fa-times"></i>
          </button>
        </div>
        <button type="button" id="btnProcesarArchivo" class="btn btn-pf-primary ml-2">
          <i class="fa fa-search mr-1"></i> Procesar Archivo
        </button>
        <button type="submit" id="btnFinalizarImport" class="btn btn-success ml-2" style="display:none;">
          <i class="fa fa-check-circle mr-1"></i> Finalizar Actualización
        </button>
      </form>
    </div>

    <div class="progress mt-3" style="height:8px;">
      <div id="barImportCategorias" class="progress-bar" role="progressbar" style="width:0%"></div>
    </div>
    <div id="msgImportCategorias" class="small mt-2 text-muted"></div>

    <!-- Preview de clientes a actualizar -->
    <div id="previewActualizables" class="mt-4" style="display:none;">
      <div class="alert alert-success">
        <h6><i class="fa fa-check-circle"></i> <b>Clientes que se actualizarán (<span id="countActualizables">0</span>)</b></h6>
      </div>
      <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-sm table-bordered table-hover">
          <thead class="bg-success text-white sticky-top">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>RTN</th>
              <th>Categoría Actual</th>
              <th>Nueva Categoría</th>
            </tr>
          </thead>
          <tbody id="tablaActualizables"></tbody>
        </table>
      </div>
    </div>

    <!-- Preview de clientes NO actualizables -->
    <div id="previewNoActualizables" class="mt-4" style="display:none;">
      <div class="alert alert-warning">
        <h6><i class="fa fa-exclamation-triangle"></i> <b>Clientes NO procesados (<span id="countNoActualizables">0</span>)</b></h6>
      </div>
      <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-sm table-bordered table-hover">
          <thead class="bg-warning sticky-top">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>RTN</th>
              <th>Categoría Propuesta</th>
              <th>Motivo</th>
            </tr>
          </thead>
          <tbody id="tablaNoActualizables"></tbody>
        </table>
      </div>
    </div>

  </div>
</div>

@push('scripts')
    <script src="{{ asset('js/js_proyecto/Escalas/categoriaClientes.js') }}"></script>
@endpush

