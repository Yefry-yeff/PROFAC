@push('styles')
<style>
    .zn-page {
        --zn-primary: #0f766e;
        --zn-accent: #b45309;
        --zn-border: #e2e8f0;
        --zn-bg-soft: #f8fafc;
        --zn-card-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
    }

    .zn-page .card.zn-main-card,
    .zn-page .modal-content {
        border: 1px solid var(--zn-border);
        box-shadow: var(--zn-card-shadow);
    }

    .zn-page .zn-main-card .card-header {
        border-bottom: 1px solid var(--zn-border);
        background: linear-gradient(120deg, #ffffff 0%, #f1f5f9 100%);
        padding: 1rem 1.25rem;
    }

    .zn-page .zn-title-wrap h3 {
        margin-bottom: .15rem;
        color: #0f172a;
        font-weight: 700;
    }

    .zn-page .zn-title-wrap small {
        color: #64748b;
    }

    .zn-page .zn-hero-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--zn-primary), #14b8a6);
        color: #fff;
        margin-right: .7rem;
        box-shadow: 0 10px 20px rgba(15, 118, 110, 0.24);
    }

    .zn-page .zn-btn-primary {
        border: 0;
        border-radius: 10px;
        padding: .5rem .9rem;
        font-weight: 600;
        background: linear-gradient(135deg, var(--zn-primary), #0d9488);
    }

    .zn-page .zn-kpi-card {
        border: 1px solid var(--zn-border);
        border-radius: 12px;
        background: #fff;
        padding: .9rem 1.1rem;
        display: flex;
        align-items: center;
        gap: .7rem;
        height: 100%;
    }

    .zn-page .zn-kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        color: #fff;
    }

    .zn-page .zn-kpi-value {
        font-size: 1.3rem;
        font-weight: 800;
        color: #0f172a;
    }

    .zn-page .zn-kpi-label {
        font-size: .78rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .03em;
        font-weight: 600;
    }

    .zn-page .table-wrap {
        border: 1px solid var(--zn-border);
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .zn-page #tablaZonas thead th {
        border-bottom: 0;
        background: #f1f5f9;
        color: #334155;
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        vertical-align: middle;
    }

    .zn-page .zn-modal .modal-dialog {
        max-width: 720px;
    }

    .zn-page .zn-section {
        border: 1px solid var(--zn-border);
        border-radius: 12px;
        background: #fff;
        padding: .75rem;
        margin-bottom: .75rem;
    }

    .zn-page .zn-section-title {
        margin-bottom: .85rem;
        font-size: .93rem;
        font-weight: 700;
        color: #334155;
        display: flex;
        align-items: center;
        gap: .45rem;
    }

    .zn-page .zn-section-title i {
        color: var(--zn-primary);
    }

    .zn-page .zn-depto-row {
        background: var(--zn-bg-soft);
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        padding: .75rem;
        margin-bottom: .6rem;
    }

    .zn-page .zn-depto-row select[multiple] {
        min-height: 90px;
    }

    .zn-page .zn-preview-list .zn-preview-item {
        background: var(--zn-bg-soft);
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: .55rem .7rem;
        margin-bottom: .45rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .5rem;
    }

    .zn-page .zn-preview-label {
        font-size: .8rem;
        color: #475569;
        margin-bottom: .3rem;
    }

    .zn-page .zn-preview-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: .55rem .7rem;
        color: #64748b;
        font-size: .82rem;
        background: #f8fafc;
    }

    .zn-page .form-control,
    .zn-page .custom-select {
        border-radius: 10px;
        border-color: #cbd5e1;
    }

    .zn-page .zn-loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(255, 255, 255, 0.86);
        z-index: 3000;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: .65rem;
        transition: opacity .25s ease;
    }

    .zn-page .zn-loading-overlay.is-hidden {
        opacity: 0;
        pointer-events: none;
    }

    .zn-page .zn-loader {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: 4px solid #d1fae5;
        border-top-color: var(--zn-primary);
        animation: znSpin .75s linear infinite;
    }

    @keyframes znSpin {
        to { transform: rotate(360deg); }
    }

    .swal-over-modal { z-index: 10000 !important; }
    #modalNuevaZona, #modalEditarZona { z-index: 2050 !important; }
    .modal-backdrop { z-index: 2040 !important; }

    /* Fix: el tema (Inspinia) fija .modal-dialog en z-index:2200, por lo que el
       dropdown de Select2 (z-index:1051 por defecto) quedaba pintado detrás del
       contenido del modal, invisible aunque técnicamente estuviera abierto. */
    .zn-modal .select2-dropdown { z-index: 2210 !important; }
</style>
@endpush

<div class="zn-page">
    <div class="zn-loading-overlay" id="pageLoadingZonas" aria-live="polite" aria-busy="true">
        <div class="zn-loader"></div>
        <div>Cargando agrupaciones de entregas...</div>
    </div>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>Agrupaciones de Entregas</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong>Agrupaciones de Entregas</strong>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- KPIs resumen -->
        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <div class="zn-kpi-card">
                    <span class="zn-kpi-icon" style="background:linear-gradient(135deg,#0f766e,#14b8a6);"><i class="fas fa-map-marked-alt"></i></span>
                    <div>
                        <div class="zn-kpi-value" id="kpiTotalZonas">0</div>
                        <div class="zn-kpi-label">Zonas Activas</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="zn-kpi-card">
                    <span class="zn-kpi-icon" style="background:linear-gradient(135deg,#b45309,#f59e0b);"><i class="fas fa-file-invoice"></i></span>
                    <div>
                        <div class="zn-kpi-value" id="kpiFacturasPendientes">0</div>
                        <div class="zn-kpi-label">Facturas Pendientes (todas las zonas)</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="zn-kpi-card">
                    <span class="zn-kpi-icon" style="background:linear-gradient(135deg,#64748b,#94a3b8);"><i class="fas fa-question-circle"></i></span>
                    <div>
                        <div class="zn-kpi-value" id="kpiSinClasificar">0</div>
                        <div class="zn-kpi-label">Facturas Sin Clasificar</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card zn-main-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                        <div class="d-flex align-items-center zn-title-wrap mb-2 mb-md-0">
                            <span class="zn-hero-icon"><i class="fas fa-layer-group"></i></span>
                            <div>
                                <h3 class="card-title mb-0">Agrupaciones de Entregas por Zona</h3>
                                <small>Defina zonas geográficas (departamentos/municipios) para agilizar la búsqueda de facturas pendientes</small>
                            </div>
                        </div>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary btn-sm zn-btn-primary" onclick="abrirModalNuevaZona()">
                                <i class="fa fa-plus mr-1"></i> Nueva Zona
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-wrap">
                            <table id="tablaZonas" class="table table-bordered table-striped table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Cobertura</th>
                                        <th>Pendientes</th>
                                        <th>Estado</th>
                                        <th>Creada</th>
                                        <th>Opciones</th>
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

    <!-- Modal Nueva Zona -->
    <div class="modal fade zn-modal" id="modalNuevaZona">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-map-marked-alt mr-2"></i>Nueva Zona Geográfica</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="formNuevaZona">
                        <div class="zn-section">
                            <div class="zn-section-title"><i class="fas fa-info-circle"></i>Datos Generales de la Agrupación</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-md-0">
                                        <label class="font-weight-semibold mb-1">Nombre de la Zona *</label>
                                        <input type="text" class="form-control" id="inputNombreZonaNueva" name="name" required placeholder="Ej: Zona Norte">
                                        <div class="invalid-feedback">El nombre de la zona es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-semibold mb-1">Descripción</label>
                                        <textarea class="form-control" name="description" id="inputDescripcionZonaNueva" rows="1" placeholder="Detalle de la zona"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="zn-section">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="zn-section-title mb-0"><i class="fas fa-map"></i>Departamentos y Municipios</div>
                                <button type="button" class="btn btn-success btn-sm" onclick="agregarDepartamentoSeleccion('nueva')"><i class="fa fa-plus mr-1"></i>Agregar Departamento</button>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-md-0">
                                        <label class="font-weight-semibold mb-1">Departamento *</label>
                                        <select class="form-control" id="nuevaDeptoSelect" onchange="onDepartamentoChange('nueva')"></select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-semibold mb-1">Municipio</label>
                                        <select class="form-control" id="nuevaMunicipioSelect"></select>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">La opción <strong>Todos los municipios</strong> se selecciona por defecto al elegir un departamento.</small>
                        </div>

                        <div class="zn-section">
                            <div class="zn-section-title mb-2"><i class="fas fa-list-check"></i>Vista previa de la agrupación</div>
                            <div class="zn-preview-list" id="previewDeptosNueva"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarZona()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Zona -->
    <div class="modal fade zn-modal" id="modalEditarZona">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Editar Zona Geográfica</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="formEditarZona">
                        <input type="hidden" id="editZonaId">
                        <div class="zn-section">
                            <div class="zn-section-title"><i class="fas fa-info-circle"></i>Datos Generales de la Agrupación</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-md-0">
                                        <label class="font-weight-semibold mb-1">Nombre de la Zona *</label>
                                        <input type="text" class="form-control" id="editNombreZona" required>
                                        <div class="invalid-feedback">El nombre de la zona es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-semibold mb-1">Descripción</label>
                                        <textarea class="form-control" id="editDescripcionZona" rows="1"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="zn-section">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="zn-section-title mb-0"><i class="fas fa-map"></i>Departamentos y Municipios</div>
                                <button type="button" class="btn btn-success btn-sm" onclick="agregarDepartamentoSeleccion('editar')"><i class="fa fa-plus mr-1"></i>Agregar Departamento</button>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-md-0">
                                        <label class="font-weight-semibold mb-1">Departamento *</label>
                                        <select class="form-control" id="editDeptoSelect" onchange="onDepartamentoChange('editar')"></select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-semibold mb-1">Municipio</label>
                                        <select class="form-control" id="editMunicipioSelect"></select>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">La opción <strong>Todos los municipios</strong> se selecciona por defecto al elegir un departamento.</small>
                        </div>

                        <div class="zn-section">
                            <div class="zn-section-title mb-2"><i class="fas fa-list-check"></i>Vista previa de la actualización</div>
                            <div class="zn-preview-list" id="previewDeptosEditar"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="actualizarZona()">Actualizar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let tablaZonas, departamentosCache = [];
    const detallesSeleccionados = { nueva: [], editar: [] };

    $(document).ready(() => {
        cargarDepartamentos();

        $('#modalNuevaZona').on('shown.bs.modal', () => inicializarSelectsModo('nueva'));
        $('#modalEditarZona').on('shown.bs.modal', () => inicializarSelectsModo('editar'));

        tablaZonas = $('#tablaZonas').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('logistica.zonas.listar') }}",
            columns: [
                {data: 'id'},
                {data: 'name'},
                {data: 'detalles', orderable: false},
                {data: 'pendientes', orderable: false},
                {data: 'estado'},
                {data: 'created_at'},
                {data: 'opciones', orderable: false}
            ],
            language: {url: '/js/plugins/dataTables/i18n/Spanish.json'},
            initComplete: function() {
                ocultarLoaderZonas();
            }
        });

        cargarResumenZonas();
        setTimeout(() => ocultarLoaderZonas(), 1300);
    });

    function ocultarLoaderZonas() {
        $('#pageLoadingZonas').addClass('is-hidden');
    }

    function cargarDepartamentos() {
        return $.ajax({
            url: "{{ route('logistica.zonas.departamentos') }}",
            method: 'GET',
            dataType: 'json'
        }).done(r => {
            const departamentos = Array.isArray(r?.departamentos)
                ? r.departamentos
                : (r?.departamentos ? Object.values(r.departamentos) : []);

            departamentosCache = departamentos;
            renderSelectDepartamentos('nueva');
            renderSelectDepartamentos('editar');
            inicializarSelectsModo('nueva');
            inicializarSelectsModo('editar');

            if (!departamentosCache.length) {
                Swal.fire({title: 'Sin datos', text: 'No se encontraron departamentos para mostrar.', icon: 'info', customClass: {container: 'swal-over-modal'}});
            }
        }).fail(x => {
            departamentosCache = [];
            renderSelectDepartamentos('nueva');
            renderSelectDepartamentos('editar');
            const msg = x.responseJSON?.mensaje || x.statusText || 'No se pudieron cargar los departamentos.';
            Swal.fire({title: 'Error', text: msg, icon: 'error', customClass: {container: 'swal-over-modal'}});
        });
    }

    function cargarResumenZonas() {
        $.get("{{ route('logistica.zonas.resumen') }}", r => {
            if (!r.success) return;
            $('#kpiTotalZonas').text(r.zonas.length);
            $('#kpiSinClasificar').text(r.sin_clasificar);
            const totalPendientes = r.zonas.reduce((sum, z) => sum + z.facturas_pendientes, 0) + r.sin_clasificar;
            $('#kpiFacturasPendientes').text(totalPendientes);
        });
    }

    function obtenerConfigModo(modo) {
        return modo === 'nueva'
            ? {
                depto: '#nuevaDeptoSelect',
                muni: '#nuevaMunicipioSelect',
                preview: '#previewDeptosNueva'
            }
            : {
                depto: '#editDeptoSelect',
                muni: '#editMunicipioSelect',
                preview: '#previewDeptosEditar'
            };
    }

    function inicializarSelectBuscable(selector, modalSelector) {
        const $el = $(selector);
        if (!$el.length || typeof $el.select2 !== 'function') return;

        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }

        $el.select2({
            width: '100%',
            dropdownParent: $(modalSelector)
        });
    }

    function inicializarSelectsModo(modo) {
        const cfg = obtenerConfigModo(modo);
        const modalSelector = modo === 'nueva' ? '#modalNuevaZona' : '#modalEditarZona';
        inicializarSelectBuscable(cfg.depto, modalSelector);
        inicializarSelectBuscable(cfg.muni, modalSelector);
    }

    function renderSelectDepartamentos(modo) {
        const cfg = obtenerConfigModo(modo);
        let opts = '<option value="">-- Seleccione Departamento --</option>';
        departamentosCache.forEach(d => {
            opts += `<option value="${d.id}">${d.nombre}</option>`;
        });
        $(cfg.depto).html(opts);
        $(cfg.depto).val('');
        if ($(cfg.depto).hasClass('select2-hidden-accessible')) {
            $(cfg.depto).trigger('change.select2');
        }
        limpiarSelectMunicipios(modo);
    }

    function limpiarSelectMunicipios(modo) {
        const cfg = obtenerConfigModo(modo);
        $(cfg.muni).html('<option value="__TODOS__">Todos los municipios</option>');
        $(cfg.muni).val('__TODOS__');
        if ($(cfg.muni).hasClass('select2-hidden-accessible')) {
            $(cfg.muni).trigger('change.select2');
        }
    }

    function onDepartamentoChange(modo) {
        const cfg = obtenerConfigModo(modo);
        const deptoId = $(cfg.depto).val();

        if (!deptoId) {
            limpiarSelectMunicipios(modo);
            return;
        }

        $.ajax({
            url: "{{ url('/logistica/zonas/municipios') }}/" + deptoId,
            method: 'GET',
            dataType: 'json'
        }).done(r => {
            let opts = '<option value="__TODOS__" selected>Todos los municipios</option>';
            const municipios = Array.isArray(r?.municipios)
                ? r.municipios
                : (r?.municipios ? Object.values(r.municipios) : []);

            municipios.forEach(m => {
                opts += `<option value="${m.id}">${m.nombre}</option>`;
            });
            $(cfg.muni).html(opts);
            $(cfg.muni).val('__TODOS__');
            if ($(cfg.muni).hasClass('select2-hidden-accessible')) {
                $(cfg.muni).trigger('change.select2');
            }
        }).fail(x => {
            limpiarSelectMunicipios(modo);
            const msg = x.responseJSON?.mensaje || x.statusText || 'No se pudieron cargar los municipios.';
            Swal.fire({title: 'Error', text: msg, icon: 'error', customClass: {container: 'swal-over-modal'}});
        });
    }

    function agregarDepartamentoSeleccion(modo) {
        const cfg = obtenerConfigModo(modo);
        const deptoId = parseInt($(cfg.depto).val(), 10);
        if (!deptoId) {
            Swal.fire({title: 'Datos incompletos', text: 'Seleccione un departamento.', icon: 'warning', customClass: {container: 'swal-over-modal'}});
            return;
        }

        const deptoNombre = $(cfg.depto + ' option:selected').text().trim();
        const municipioVal = $(cfg.muni).val() || '__TODOS__';
        const municipioId = municipioVal === '__TODOS__' ? null : parseInt(municipioVal, 10);
        const municipioNombre = municipioId === null
            ? 'Todos los municipios'
            : $(cfg.muni + ' option:selected').text().trim();

        if (municipioId === null) {
            detallesSeleccionados[modo] = detallesSeleccionados[modo].filter(d => d.department_id !== deptoId);
        } else {
            detallesSeleccionados[modo] = detallesSeleccionados[modo].filter(d => !(d.department_id === deptoId && d.municipality_id === null));
            const repetido = detallesSeleccionados[modo].some(d => d.department_id === deptoId && d.municipality_id === municipioId);
            if (repetido) {
                Swal.fire({title: 'Duplicado', text: 'Ese municipio ya está agregado en la vista previa.', icon: 'info', customClass: {container: 'swal-over-modal'}});
                return;
            }
        }

        detallesSeleccionados[modo].push({
            department_id: deptoId,
            municipality_id: municipioId,
            departamento: deptoNombre,
            municipio: municipioNombre
        });

        renderPreviewDetalles(modo);
    }

    function quitarDetallePreview(modo, idx) {
        detallesSeleccionados[modo].splice(idx, 1);
        renderPreviewDetalles(modo);
    }

    function renderPreviewDetalles(modo) {
        const cfg = obtenerConfigModo(modo);
        const lista = detallesSeleccionados[modo] || [];

        if (!lista.length) {
            $(cfg.preview).html('<div class="zn-preview-empty">Aún no ha agregado departamentos o municipios.</div>');
            return;
        }

        let html = '';
        lista.forEach((d, idx) => {
            html += `
                <div class="zn-preview-item">
                    <div>
                        <div class="zn-preview-label">Departamento</div>
                        <strong>${d.departamento}</strong>
                        <span class="mx-1 text-muted">/</span>
                        <span>${d.municipio}</span>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" onclick="quitarDetallePreview('${modo}', ${idx})" title="Quitar">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>`;
        });

        $(cfg.preview).html(html);
    }

    function recolectarDetalles(modo) {
        return (detallesSeleccionados[modo] || []).map(d => ({
            department_id: d.department_id,
            municipality_id: d.municipality_id
        }));
    }

    function abrirModalNuevaZona() {
        $('#formNuevaZona')[0].reset();
        $('#inputNombreZonaNueva').removeClass('is-invalid');
        detallesSeleccionados.nueva = [];
        if (!departamentosCache.length) {
            cargarDepartamentos();
        } else {
            renderSelectDepartamentos('nueva');
        }
        renderPreviewDetalles('nueva');
        $('#modalNuevaZona').modal('show');
    }

    function guardarZona() {
        const nombre = $('#inputNombreZonaNueva').val().trim();
        if (!nombre) {
            $('#inputNombreZonaNueva').addClass('is-invalid').focus();
            return;
        }
        $('#inputNombreZonaNueva').removeClass('is-invalid');

        const detalles = recolectarDetalles('nueva');
        if (detalles.length === 0) {
            Swal.fire({title: 'Datos incompletos', text: 'Seleccione al menos un departamento válido.', icon: 'warning', customClass: {container: 'swal-over-modal'}});
            return;
        }

        $.ajax({
            url: "{{ route('logistica.zonas.guardar') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                name: nombre,
                description: $('#inputDescripcionZonaNueva').val(),
                detalles: JSON.stringify(detalles)
            },
            success: r => {
                $('#modalNuevaZona').modal('hide');
                tablaZonas.ajax.reload();
                cargarResumenZonas();
                Swal.fire({title: r.title, text: r.text, icon: r.icon});
            },
            error: x => {
                const msg = x.responseJSON ? (x.responseJSON.text || x.responseJSON.message || 'Error inesperado.') : 'Error inesperado.';
                Swal.fire({title: 'Error', text: msg, icon: 'error', customClass: {container: 'swal-over-modal'}});
            }
        });
    }

    function editarZona(id) {
        $.get("{{ url('/logistica/zonas/obtener') }}/" + id, r => {
            if (!r.success) {
                return Swal.fire({title: 'Error', text: r.mensaje || 'No se pudo cargar la zona', icon: 'error'});
            }

            $('#editZonaId').val(r.zona.id);
            $('#editNombreZona').val(r.zona.name).removeClass('is-invalid');
            $('#editDescripcionZona').val(r.zona.description);
            detallesSeleccionados.editar = (r.detalles || []).map(d => ({
                department_id: parseInt(d.department_id, 10),
                municipality_id: d.municipality_id ? parseInt(d.municipality_id, 10) : null,
                departamento: d.departamento,
                municipio: d.municipality_id ? d.municipio : 'Todos los municipios'
            }));

            if (!departamentosCache.length) {
                cargarDepartamentos();
            } else {
                renderSelectDepartamentos('editar');
            }
            renderPreviewDetalles('editar');

            $('#modalEditarZona').modal('show');
        }).fail(() => Swal.fire({title: 'Error', text: 'No se pudo cargar la zona', icon: 'error'}));
    }

    function actualizarZona() {
        const nombre = $('#editNombreZona').val().trim();
        if (!nombre) {
            $('#editNombreZona').addClass('is-invalid').focus();
            return;
        }
        $('#editNombreZona').removeClass('is-invalid');

        const detalles = recolectarDetalles('editar');
        if (detalles.length === 0) {
            Swal.fire({title: 'Datos incompletos', text: 'Seleccione al menos un departamento válido.', icon: 'warning', customClass: {container: 'swal-over-modal'}});
            return;
        }

        $.ajax({
            url: "{{ route('logistica.zonas.actualizar') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: $('#editZonaId').val(),
                name: nombre,
                description: $('#editDescripcionZona').val(),
                detalles: JSON.stringify(detalles)
            },
            success: r => {
                $('#modalEditarZona').modal('hide');
                tablaZonas.ajax.reload();
                cargarResumenZonas();
                Swal.fire({title: r.title, text: r.text, icon: r.icon});
            },
            error: x => {
                const msg = x.responseJSON ? (x.responseJSON.text || x.responseJSON.message || 'Error inesperado.') : 'Error inesperado.';
                Swal.fire({title: 'Error', text: msg, icon: 'error', customClass: {container: 'swal-over-modal'}});
            }
        });
    }

    function eliminarZona(id) {
        Swal.fire({
            title: 'Confirmar',
            text: '¿Desea eliminar esta zona geográfica?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(r => {
            if (r.isConfirmed) {
                $.post("{{ url('/logistica/zonas/eliminar') }}/" + id, {_token: $('meta[name="csrf-token"]').attr('content')})
                    .done(resp => {
                        Swal.fire(resp.title, resp.text, resp.icon);
                        tablaZonas.ajax.reload();
                        cargarResumenZonas();
                    })
                    .fail(x => {
                        const r2 = x.responseJSON || {};
                        Swal.fire(r2.title || 'Error', r2.text || 'No se pudo eliminar la zona', r2.icon || 'error');
                    });
            }
        });
    }
</script>
@endpush
