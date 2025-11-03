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
    z-index: 3050 !important; /* Siempre encima de modales */
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

</style>
@endpush

<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><b>CATEGORÍA DE PRECIOS DE PRODUCTO</b></h6>

    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalCategoriasPrecios">
        <i class="bi bi-plus-circle mr-1"></i> + Creación

    </button>
    </div>
  <div class="card-body p-2">

        <!-- TABLA -->
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

<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-light py-2 d-flex flex-wrap align-items-center justify-content-between">
        <h6 class="mb-2 mb-md-0"><b>PLANTILLA DE PRECIOS POR PRODUCTOS</b></h6>

        <form id="formExport" method="GET" action="{{ route('excel.plantilla') }}" class="d-flex flex-wrap align-items-center filtro-container">
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
    <div class="card-body p-2">
        <!-- Aquí va el contenido de la card si es necesario -->
    </div>
</div>



<!-- MODAL ELEGANTE -->
<div class="modal fade" id="modalCategoriasPrecios" tabindex="-1" role="dialog"
     aria-labelledby="modalCategoriasPreciosTitle" aria-hidden="true"
     data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content border-0 shadow-lg rounded">

      <!-- Header -->
      <div class="modal-header bg-primary text-white rounded-top">
        <h5 class="modal-title font-weight-bold" id="modalCategoriasPreciosTitle">Categoría de Precios</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body px-4 py-4 bg-light">
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
            <div class="form-group col-md-6">
                <label for="categoria_cliente_id" class="font-weight-bold">Categoría de Cliente</label>
                <select id="categoria_cliente_id" name="categoria_cliente_id" required>
                    <option value="">Seleccione una categoría...</option>
                    @foreach($categoriasClientes as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre_categoria }}</option>
                    @endforeach
                </select>
            </div>

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



@push('scripts')
    <script src="{{ asset('js/js_proyecto/Escalas/gestionPrecios.js') }}"></script>
@endpush

