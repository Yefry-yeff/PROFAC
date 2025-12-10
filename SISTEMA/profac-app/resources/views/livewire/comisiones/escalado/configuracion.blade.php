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
<div class="modal fade" id="modalParamComision" tabindex="-1" role="dialog" aria-labelledby="modalParamComisionTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content border-0 shadow-lg rounded">

      <!-- Header -->
      <div class="modal-header bg-primary text-white rounded-top">
        <h5 class="modal-title font-weight-bold" id="modalCategoriasClientesTitle">Parametrización de Comisión</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body px-4 py-4 bg-light">
        <form id="paramComisionForm">

          <!-- Primera fila: Nombre y Descripción -->
          <div class="form-row">

            <div class="form-group col-md-6">
              <label for="nombre_cat" class="font-weight-bold">Título de Comisión</label>
              <input type="text" class="form-control form-control-lg border-primary" id="nombre_comescala" name="nombre_comescala" maxlength="150" required>
            </div>

            <div class="form-group col-md-6">
              <label for="descripcion_cat" class="font-weight-bold">Descripción</label>
              <input type="text" class="form-control form-control-lg border-primary" id="descripcion_comescala" name="descripcion_comescala" maxlength="250"  required>
            </div>

            <div class="form-group col-md-6">
              <label for="Categoria de cliente" class="font-weight-bold">Categoría de Cliente</label>
                <select id="categoria_cliente_id" name="categoria_cliente_id" class="form-control" data-url="{{ route('clientes.categorias.escala') }}"  required>
                    <option value="">Seleccione una categoría...</option>
                </select>
            </div>
            <input type="hidden" id="param_comision_id" name="param_comision_id">
            <div class="form-group col-md-6">
              <label for="Categoria de cliente" class="font-weight-bold">Rol asociado</label>
                <select id="rol_id" name="rol_id" class="form-control" data-url="{{ route('comision.configuracion.rol') }}"  required>
                    <option value="">Seleccione un rol...</option>
                </select>
            </div>

            <div class="form-group col-md-6">
              <label for="Categoria de cliente" class="font-weight-bold">Porcentaje de comisión a aplicar</label>
              <input type="number" step="any" class="form-control form-control-lg border-primary" id="porcentaje_comision" name="porcentaje_comision" required>
            </div>
            <hr>
            <p>Se debe ingresar un rango de compra mensual del cliente, para diferenciación de ingreso</p>
            <div class="form-group col-md-6">
              <label for="descripcion_cat" class="font-weight-bold">Inicio</label>
              <input type="number" class="form-control form-control-lg border-primary" id="rango_inicial_comescala" name="rango_inicial_comescala" min="0" required>
            </div>

            <div class="form-group col-md-6">
              <label for="descripcion_cat" class="font-weight-bold">Fin</label>
              <input type="number" class="form-control form-control-lg border-primary" id="rango_final_comescala" name="rango_final_comescala" min="0" required>
            </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer border-0 mt-4">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="btnCancelarCategoria">
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary font-weight-bold" id="btn_guardar_parametro_comision">
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
        <h6 class="mb-0"><b>Parametrización de % de comisiones por Categoría de clientes</b></h6>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalParamComision"><i class="bi bi-plus-circle mr-1"></i>+ Ingreso</button>
    </div>
    <div class="card-body p-2">
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="ibox">
                <div class="ibox-content">
                    <div class="table-responsive">
                    <table id="tbl_listaParametroComision" class="table table-striped table-bordered table-hover">
                        <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Comisión</th>
                            <th>%</th>
                            <th>Monto compra Inicial</th>
                            <th>Monto compra Final</th>
                            <th>Rol de Usuario</th>
                            <th>Clientes</th>
                            <th>Registrado</th>
                            <th>Creación</th>
                            <th>Estado</th>
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



@push('scripts')
    <script src="{{ asset('js/js_proyecto/comisiones/Escalado/gestionComision.js') }}"></script>
@endpush

