@push('styles')
<style>
/* Encabezado más ligero */
.page-heading, .d-flex.bg-light {
    background-color: #f8f9fa;
    border-radius: 0.35rem;
    padding: 0.5rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

/* Select2 moderno y compacto */
.select2-container--bootstrap4 .select2-selection--single {
    height: 36px;
    padding: 3px 10px;
    border-radius: 0.35rem;
    border: 1px solid #ced4da;
    font-size: 0.9rem;
}

.select2-container--bootstrap4 .select2-selection__rendered {
    line-height: 30px;
}

.select2-container--bootstrap4 .select2-selection__arrow {
    height: 34px;
    right: 6px;
}

.select2-container--bootstrap4 .select2-dropdown {
    max-height: 200px; /* scroll si hay muchos items */
    overflow-y: auto;
}

/* Botón más plano y limpio */
.btn-success {
    font-weight: 500;
    padding: 0.35rem 0.9rem;
}

/* Responsivo */
@media (max-width: 576px) {
    form.d-flex {
        flex-direction: column;
    }

    form.d-flex > * {
        margin-bottom: 0.5rem;
    }
}
/* Contenedor de filtros: todo en línea, con espacio dinámico */
.filtro-container {
    gap: 0.5rem; /* Espacio entre elementos */
    flex-wrap: wrap; /* Si no cabe en una línea, se mueve abajo */
}

/* Select uniforme */
.filtro-select {
    min-width: 150px;
    flex: 1 1 150px; /* Crece o se reduce dinámicamente */
    height: 38px; /* Igual altura para todos */
}

/* Botón alineado con los selects */
#btnDescargar {
    height: 38px; /* Mismo alto que los selects */
}

/* Responsivo: en pantallas pequeñas */
@media (max-width: 576px) {
    .filtro-container {
        flex-direction: column;
        gap: 0.5rem;
    }
    #btnDescargar {
        width: 100%; /* Botón ocupa todo el ancho en móvil */
    }
}
/* Select uniforme y un poco más ancho */
.filtro-select {
    min-width: 200px;      /* antes era 150px */
    flex: 1 1 220px;       /* crece hasta 220px aprox */
    height: 38px;          /* misma altura */
    font-size: 0.9rem;     /* tamaño de texto consistente */
}

/* Para pantallas medianas o grandes, deja respirar más los selects */
@media (min-width: 992px) {
    .filtro-select {
        min-width: 240px;
        flex: 1 1 240px;
    }
}
</style>
@endpush
<!-- MODAL ELEGANTE -->
<div class="modal fade" id="modalCategoriasClientes" tabindex="-1" role="dialog" aria-labelledby="modalCategoriasClientesTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content border-0 shadow-lg rounded">

      <!-- Header -->
      <div class="modal-header bg-primary text-white rounded-top">
        <h5 class="modal-title font-weight-bold" id="modalCategoriasClientesTitle">Nueva Categoría de Cliente</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body px-4 py-4 bg-light">
        <form id="clientesCreacionForm">

          <!-- Primera fila: Nombre y Descripción -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="nombre_cat" class="font-weight-bold">Nombre de la Categoría</label>
              <input type="text" class="form-control form-control-lg border-primary" id="nombre_cat" name="nombre_cat"
                placeholder="Ej: Clientes estatales" maxlength="100" required>
            </div>
            <div class="form-group col-md-6">
              <label for="descripcion_cat" class="font-weight-bold">Descripción</label>
              <input type="text" class="form-control form-control-lg border-primary" id="descripcion_cat" name="descripcion_cat"
                placeholder="Ej: Clientes institucionales o empresas" maxlength="150">
            </div>
          </div>

          <!-- Comentario -->
          <div class="mt-4">
            <label for="comentario" class="font-weight-bold">Comentario</label>
            <textarea id="comentario" name="comentario" class="form-control border-primary" rows="3"
              placeholder="Agrega un comentario sobre esta categoría..."></textarea>
          </div>

          <!-- Footer -->
          <div class="modal-footer border-0 mt-4">
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

<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><b>CATEGORÍA DE CLIENTES</b></h6>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalCategoriasClientes">
            <i class="bi bi-plus-circle mr-1"></i>
            + Creación
        </button>
    </div>
    <div class="card-body p-2">
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="ibox">
                <div class="ibox-content">
                    <div class="table-responsive">
                    <table id="tbl_listaCategoria" class="table table-striped table-bordered table-hover">
                        <thead class="thead-light">
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
    </div>
</div>


<div class="card shadow-sm border-0 mb-3">
  <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
    <h6 class="mb-0"><b>PLANTILLA / CARGA MASIVA – CATEGORÍAS DE CLIENTE</b></h6>
  </div>
  <div class="card-body p-3">
    <div class="d-flex filtro-container align-items-center">
      <a href="{{ route('clientes.plantilla.categorias') }}" class="btn btn-success" id="btnDescargar">
        <i class="bi bi-download"></i> Descargar Plantilla
      </a>

      <form id="formImportCategorias" class="d-flex align-items-center ml-2" enctype="multipart/form-data">
        @csrf
        <input type="file" class="form-control filtro-select" name="file" accept=".xlsx,.xls,.csv" required>
        <button type="submit" class="btn btn-primary ml-2">
          <i class="bi bi-upload"></i> Importar
        </button>
      </form>
    </div>

    <div class="progress mt-3" style="height:8px;">
      <div id="barImportCategorias" class="progress-bar" role="progressbar" style="width:0%"></div>
    </div>
    <div id="msgImportCategorias" class="small mt-2 text-muted"></div>

    <div id="erroresImportCategorias" class="mt-3 d-none">
      <div class="alert alert-warning mb-2"><b>Errores (primeros 10):</b></div>
      <ul class="small" id="erroresLista"></ul>
    </div>
  </div>
</div>

@push('scripts')
    <script src="{{ asset('js/js_proyecto/Escalas/categoriaClientes.js') }}"></script>
@endpush

