@push('styles')
<style>
@push('styles')
<style>
/* ===================== ENCABEZADOS ===================== */
.page-heading,
.d-flex.bg-light {
    background-color: #f8f9fa;
    border-radius: .35rem;
    padding: .5rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
}

/* ===================== SELECT2 (BOOTSTRAP 4) ===================== */
.select2-container--bootstrap4 .select2-selection--single {
    height: 38px;
    padding: 4px 10px;
    border-radius: .35rem;
    border: 1px solid #ced4da;
    font-size: .9rem;
}

.select2-container--bootstrap4 .select2-selection__rendered {
    line-height: 30px;
}

.select2-container--bootstrap4 .select2-selection__arrow {
    height: 36px;
    right: 6px;
}

.select2-container--bootstrap4 .select2-dropdown {
    max-height: 220px;
    overflow-y: auto;
}

/* ===================== BOTONES ===================== */
.btn {
    font-weight: 500;
}

.btn-success,
.btn-primary {
    padding: .35rem .9rem;
}

/* ===================== FILTROS / FORM INLINE ===================== */
.filtro-container {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-wrap: wrap;
}

.filtro-select {
    min-width: 200px;
    height: 38px;
    font-size: .9rem;
    flex: 1 1 220px;
}

#btnDescargar {
    height: 38px;
}

/* ===================== RESPONSIVE ===================== */
@media (max-width: 576px) {
    form.d-flex {
        flex-direction: column;
    }

    form.d-flex > * {
        margin-bottom: .5rem;
    }

    .filtro-container {
        flex-direction: column;
    }

    #btnDescargar {
        width: 100%;
    }
}

@media (min-width: 992px) {
    .filtro-select {
        min-width: 240px;
        flex: 1 1 240px;
    }
}

/* ===================== MODALES ===================== */
.modal-content {
    border-radius: .4rem;
}

.modal-header {
    border-bottom: 1px solid #e9ecef;
}

.modal-footer {
    border-top: 1px solid #e9ecef;
}

/* ===================== TABLAS ===================== */
.table thead th {
    font-size: .85rem;
    vertical-align: middle;
}

.table td {
    font-size: .85rem;
}
</style>
@endpush
</style>
@endpush
<!-- MODAL PARAMETRIZACIÓN COMISIÓN -->
<div class="modal fade" id="modalParamComision" tabindex="-1" role="dialog" aria-labelledby="modalParamComisionTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content border-0 shadow-sm rounded-lg">

      <!-- Header -->
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title font-weight-bold mb-0" id="modalCategoriasClientesTitle">
          Parametrización de Comisión
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body bg-light px-4 py-4">
        <form id="paramComisionForm">

          <div class="form-row">

            <div class="form-group col-md-6">
              <label class="font-weight-bold mb-1">Título de Comisión</label>
              <input type="text"
                     class="form-control"
                     id="nombre_comescala"
                     name="nombre_comescala"
                     maxlength="150"
                     required>
              <small class="text-muted">Nombre identificador de la comisión</small>
            </div>

            <div class="form-group col-md-6">
              <label class="font-weight-bold mb-1">Descripción</label>
              <input type="text"
                     class="form-control"
                     id="descripcion_comescala"
                     name="descripcion_comescala"
                     maxlength="250"
                     required>
              <small class="text-muted">Descripción breve para referencia</small>
            </div>

            <div class="form-group col-md-6">
              <label class="font-weight-bold mb-1">Categoría de Cliente</label>
              <select id="categoria_cliente_id"
                      name="categoria_cliente_id"
                      class="form-control"
                      data-url="{{ route('clientes.categorias.escala') }}"
                      required>
                <option value="">Seleccione una categoría...</option>
              </select>
            </div>

            <input type="hidden" id="param_comision_id" name="param_comision_id">

            <div class="form-group col-md-6">
              <label class="font-weight-bold mb-1">Rol asociado</label>
              <select id="rol_id"
                      name="rol_id"
                      class="form-control"
                      data-url="{{ route('comision.configuracion.rol') }}"
                      required>
                <option value="">Seleccione un rol...</option>
              </select>
            </div>

            <div class="form-group col-md-6">
              <label class="font-weight-bold mb-1">Porcentaje de comisión</label>
              <input type="number"
                     step="any"
                     class="form-control"
                     id="porcentaje_comision"
                     name="porcentaje_comision"
                     required>
              <small class="text-muted">Ejemplo: 5, 7.5, 10</small>
            </div>

          </div>

          <!-- Footer -->
          <div class="modal-footer border-0 px-0 pt-4 d-flex justify-content-between">
            <button type="button"
                    class="btn btn-outline-secondary px-4"
                    data-dismiss="modal"
                    id="btnCancelarCategoria">
              Cancelar
            </button>

            <button type="submit"
                    class="btn btn-primary px-4 font-weight-bold"
                    id="btn_guardar_parametro_comision">
              Guardar
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>

<!-- CARD LISTADO -->
<div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
    <h6 class="mb-0 font-weight-bold">
      Parametrización de % de comisiones por Categoría de clientes
    </h6>
    <button type="button"
            class="btn btn-primary btn-sm px-3"
            data-toggle="modal"
            data-target="#modalParamComision">
      <i class="bi bi-plus-circle mr-1"></i> Ingreso
    </button>
  </div>

  <div class="card-body p-3">
    <div class="table-responsive">
      <table id="tbl_listaParametroComision"
             class="table table-striped table-bordered table-hover table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th>ID</th>
            <th>Comisión</th>
            <th>%</th>
            <th>Rol de Usuario</th>
            <th>Clientes</th>
            <th>Registrado</th>
            <th>Creación</th>
            <th>Estado</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>




@push('scripts')
    <script src="{{ asset('js/js_proyecto/comisiones/Escalado/gestionComision.js') }}"></script>
@endpush

