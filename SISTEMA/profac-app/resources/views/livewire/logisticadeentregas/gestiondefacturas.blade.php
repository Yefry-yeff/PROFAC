@push('styles')
<style>
    .gde-page {
        --gde-primary: #1d4ed8;
        --gde-border: #e2e8f0;
    }

    .gde-page .kpi-card {
        border: 1px solid var(--gde-border);
        border-radius: 12px;
        background: #fff;
        padding: .85rem 1rem;
        display: flex;
        align-items: center;
        gap: .7rem;
        height: 100%;
        cursor: pointer;
        transition: box-shadow .15s ease;
    }

    .gde-page .kpi-card:hover {
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.10);
    }

    .gde-page .kpi-card.active {
        border-color: var(--gde-primary);
        box-shadow: 0 0 0 2px rgba(29, 78, 216, .15);
    }

    .gde-page .kpi-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1rem;
    }

    .gde-page .kpi-value {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
    }

    .gde-page .kpi-label {
        font-size: .72rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .03em;
        font-weight: 600;
    }

    .gde-page .table-wrap {
        border: 1px solid var(--gde-border);
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .gde-page .nav-tabs .nav-link.active {
        font-weight: 700;
        color: var(--gde-primary);
    }

    /* Fix: el tema (Inspinia) fija .modal-dialog en z-index:2200, por lo que el
       dropdown de Select2 (z-index:1051 por defecto) quedaba pintado detrás del
       contenido del modal, invisible aunque técnicamente estuviera abierto. */
    .gde-page .gde-modal .select2-dropdown { z-index: 2210 !important; }
    .gde-page .gde-modal { z-index: 2050 !important; }
    .gde-page .swal-over-modal { z-index: 10000 !important; }

    .gde-page .historial-item {
        border-left: 3px solid var(--gde-border);
        padding: .25rem 0 .25rem .9rem;
        margin-bottom: .6rem;
        position: relative;
    }

    .gde-page .historial-item::before {
        content: '';
        position: absolute;
        left: -7px;
        top: .35rem;
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background: var(--gde-primary);
    }
</style>
@endpush

<div class="gde-page">
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>Gestión de Distribución de Entregas</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong>Gestión de Distribución de Entregas</strong>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        <!-- Filtro por gestor -->
        @if ($esVistaGlobal)
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="font-weight-semibold mb-1">Filtrar por Gestor de Entrega</label>
                <select class="form-control" id="filtroGestor" style="width: 100%">
                    <option value="todos">Todos los gestores</option>
                    @foreach ($gestores as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif

        <!-- KPIs / navegación por sección -->
        <div class="row mb-3">
            <div class="col-6 col-md-2 mb-2">
                <div class="kpi-card" data-tab="singestor">
                    <span class="kpi-icon" style="background:linear-gradient(135deg,#64748b,#94a3b8);"><i class="fas fa-user-slash"></i></span>
                    <div>
                        <div class="kpi-value" id="kpiSinGestor">0</div>
                        <div class="kpi-label">Sin Gestor</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2 mb-2">
                <div class="kpi-card active" data-tab="sintratar">
                    <span class="kpi-icon" style="background:linear-gradient(135deg,#b45309,#f59e0b);"><i class="fas fa-file-invoice"></i></span>
                    <div>
                        <div class="kpi-value" id="kpiSinTratar">0</div>
                        <div class="kpi-label">Sin Tratar</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2 mb-2">
                <div class="kpi-card" data-tab="tratadas">
                    <span class="kpi-icon" style="background:linear-gradient(135deg,#0f766e,#14b8a6);"><i class="fas fa-map-marker-alt"></i></span>
                    <div>
                        <div class="kpi-value" id="kpiTratadas">0</div>
                        <div class="kpi-label">Tratadas</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <div class="kpi-card" data-tab="asignadas">
                    <span class="kpi-icon" style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);"><i class="fas fa-truck"></i></span>
                    <div>
                        <div class="kpi-value" id="kpiAsignadas">0</div>
                        <div class="kpi-label">Asignadas</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <div class="kpi-card" data-tab="completadas">
                    <span class="kpi-icon" style="background:linear-gradient(135deg,#15803d,#22c55e);"><i class="fas fa-check-circle"></i></span>
                    <div>
                        <div class="kpi-value" id="kpiCompletadas">0</div>
                        <div class="kpi-label">Completadas</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs" id="gdeTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" id="tab-singestor" data-toggle="tab" href="#pane-singestor" data-tab="singestor">Sin Gestor de Entrega</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-sintratar" data-toggle="tab" href="#pane-sintratar" data-tab="sintratar">Sin Tratar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-tratadas" data-toggle="tab" href="#pane-tratadas" data-tab="tratadas">Tratadas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-asignadas" data-toggle="tab" href="#pane-asignadas" data-tab="asignadas">Asignadas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-completadas" data-toggle="tab" href="#pane-completadas" data-tab="completadas">Completadas</a>
                    </li>
                </ul>

                <div class="tab-content pt-3">
                    <!-- Sin Gestor: SOLO lectura -->
                    <div class="tab-pane fade" id="pane-singestor">
                        <div class="alert alert-secondary mb-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Vista informativa. Estas facturas no tienen un gestor de entrega asignado; no es posible tratarlas ni asignarlas desde aquí.
                        </div>
                        <div class="table-responsive table-wrap">
                            <table id="tablaSinGestor" class="table table-bordered table-striped table-sm mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th>CAI</th>
                                        <th>N° Factura</th>
                                        <th>Cliente</th>
                                        <th>Asesor Comercial</th>
                                        <th>Total</th>
                                        <th>Fecha Emisión</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sin Tratar -->
                    <div class="tab-pane fade show active" id="pane-sintratar">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div><span class="badge badge-info" id="contadorSinTratar">0 seleccionadas</span></div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalTratar()">
                                <i class="fas fa-map-marker-alt mr-1"></i> Tratar Facturas
                            </button>
                        </div>
                        <div class="table-responsive table-wrap">
                            <table id="tablaSinTratar" class="table table-bordered table-striped table-sm mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="chkAllSinTratar"></th>
                                        <th>CAI</th>
                                        <th>N° Factura</th>
                                        <th>Cliente</th>
                                        <th>Gestor</th>
                                        <th>Asesor Comercial</th>
                                        <th>Total</th>
                                        <th>Fecha Emisión</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tratadas -->
                    <div class="tab-pane fade" id="pane-tratadas">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div><span class="badge badge-info" id="contadorTratadas">0 seleccionadas</span></div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalAsignar()">
                                <i class="fas fa-truck mr-1"></i> Asignar a Equipo
                            </button>
                        </div>
                        <div class="table-responsive table-wrap">
                            <table id="tablaTratadas" class="table table-bordered table-striped table-sm mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="chkAllTratadas"></th>
                                        <th>CAI</th>
                                        <th>Cliente</th>
                                        <th>Gestor</th>
                                        <th>Asesor Comercial</th>
                                        <th>Departamento</th>
                                        <th>Municipio</th>
                                        <th>Dirección</th>
                                        <th>Tratada el</th>
                                        <th>Historial</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Asignadas -->
                    <div class="tab-pane fade" id="pane-asignadas">
                        <div class="table-responsive table-wrap">
                            <table id="tablaAsignadas" class="table table-bordered table-striped table-sm mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th>CAI</th>
                                        <th>Cliente</th>
                                        <th>Gestor</th>
                                        <th>Asesor Comercial</th>
                                        <th>Municipio</th>
                                        <th>Equipo</th>
                                        <th>Fecha Programada</th>
                                        <th>Estado Entrega</th>
                                        <th>Historial</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Completadas -->
                    <div class="tab-pane fade" id="pane-completadas">
                        <div class="table-responsive table-wrap">
                            <table id="tablaCompletadas" class="table table-bordered table-striped table-sm mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th>CAI</th>
                                        <th>Cliente</th>
                                        <th>Gestor</th>
                                        <th>Asesor Comercial</th>
                                        <th>Municipio</th>
                                        <th>Equipo</th>
                                        <th>Entregada el</th>
                                        <th>Historial</th>
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

    <!-- Modal: Tratar Facturas -->
    <div class="modal fade gde-modal" id="modalTratarFacturas">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-map-marker-alt mr-2"></i>Tratar Facturas</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><span class="badge badge-primary" id="tratarCantidadBadge">0 factura(s) seleccionada(s)</span></p>
                    <div class="form-group">
                        <label class="font-weight-semibold mb-1">Departamento *</label>
                        <select class="form-control" id="tratarDeptoSelect" style="width:100%" onchange="cargarMunicipiosTratar()"></select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-semibold mb-1">Municipio *</label>
                        <select class="form-control" id="tratarMunicipioSelect" style="width:100%"></select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-semibold mb-1">Dirección de Entrega *</label>
                        <textarea class="form-control" id="tratarDireccion" rows="2" placeholder="Detalle de la dirección de entrega"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarTratamiento()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Asignar a Equipo -->
    <div class="modal fade gde-modal" id="modalAsignarEquipo">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-truck mr-2"></i>Asignar Facturas a Equipo de Entrega</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><span class="badge badge-primary" id="asignarCantidadBadge">0 factura(s) seleccionada(s)</span></p>
                    <div class="form-group">
                        <label class="font-weight-semibold mb-1">Equipo de Entrega *</label>
                        <select class="form-control" id="asignarEquipoSelect" style="width:100%">
                            <option value="">-- Seleccione --</option>
                            @foreach ($equipos as $eq)
                                <option value="{{ $eq->id }}">{{ $eq->nombre_equipo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-semibold mb-1">Fecha Programada *</label>
                        <input type="date" class="form-control" id="asignarFechaProgramada">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-semibold mb-1">Observaciones</label>
                        <textarea class="form-control" id="asignarObservaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarAsignacion()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Historial de Factura -->
    <div class="modal fade gde-modal" id="modalHistorial">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-history mr-2"></i>Historial de la Factura</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="historialContenido">
                    <p class="text-muted mb-0">Cargando...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let tablaSinGestor, tablaSinTratar, tablaTratadas, tablaAsignadas, tablaCompletadas;
    let seleccionSinTratar = new Set();
    let seleccionTratadas = new Set();
    let departamentosCacheGde = [];

    function gestorSeleccionado() {
        const el = document.getElementById('filtroGestor');
        return el ? ($(el).val() || 'todos') : 'todos';
    }

    $(document).ready(() => {
        if (typeof $.fn.select2 === 'function' && document.getElementById('filtroGestor')) {
            $('#filtroGestor').select2({ width: '100%' });
        }

        cargarResumenGde();

        tablaSinGestor = $('#tablaSinGestor').DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: "{{ route('logistica.gestion.singestor') }}" },
            columns: [
                { data: 'cai' },
                { data: 'numero_factura' },
                { data: 'cliente' },
                { data: 'asesor_comercial' },
                { data: 'total', render: d => 'L. ' + parseFloat(d).toFixed(2) },
                { data: 'fecha_emision' },
            ],
            language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        });

        tablaSinTratar = $('#tablaSinTratar').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('logistica.gestion.sintratar') }}",
                data: d => { d.gestor_id = gestorSeleccionado(); }
            },
            columns: [
                {
                    data: 'id', orderable: false, render: (d) => {
                        const checked = seleccionSinTratar.has(d) ? 'checked' : '';
                        return `<input type="checkbox" class="chk-sin-tratar" value="${d}" ${checked}>`;
                    }
                },
                { data: 'cai' },
                { data: 'numero_factura' },
                { data: 'cliente' },
                { data: 'gestor' },
                { data: 'asesor_comercial' },
                { data: 'total', render: d => 'L. ' + parseFloat(d).toFixed(2) },
                { data: 'fecha_emision' },
            ],
            language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        });

        tablaTratadas = $('#tablaTratadas').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('logistica.gestion.tratadas') }}",
                data: d => { d.gestor_id = gestorSeleccionado(); }
            },
            columns: [
                {
                    data: 'id', orderable: false, render: (d) => {
                        const checked = seleccionTratadas.has(d) ? 'checked' : '';
                        return `<input type="checkbox" class="chk-tratadas" value="${d}" ${checked}>`;
                    }
                },
                { data: 'cai' },
                { data: 'cliente' },
                { data: 'gestor' },
                { data: 'asesor_comercial' },
                { data: 'departamento' },
                { data: 'municipio' },
                { data: 'direccion_entrega' },
                { data: 'fecha_tratamiento' },
                {
                    data: 'id', orderable: false, render: (d) => {
                        return `<button type="button" class="btn btn-xs btn-outline-secondary" onclick="verHistorial(${d})"><i class="fas fa-history"></i></button>`;
                    }
                },
            ],
            language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        });

        tablaAsignadas = $('#tablaAsignadas').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('logistica.gestion.asignadas') }}",
                data: d => { d.gestor_id = gestorSeleccionado(); }
            },
            columns: [
                { data: 'cai' },
                { data: 'cliente' },
                { data: 'gestor' },
                { data: 'asesor_comercial' },
                { data: 'municipio' },
                { data: 'nombre_equipo' },
                { data: 'fecha_programada' },
                {
                    data: 'estado_entrega', render: (d) => {
                        const map = { sin_entrega: 'secondary', parcial: 'warning', entregado: 'success', anulada: 'danger' };
                        return `<span class="badge badge-${map[d] || 'secondary'}">${d}</span>`;
                    }
                },
                {
                    data: 'id', orderable: false, render: (d) => {
                        return `<button type="button" class="btn btn-xs btn-outline-secondary" onclick="verHistorial(${d})"><i class="fas fa-history"></i></button>`;
                    }
                },
            ],
            language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        });

        tablaCompletadas = $('#tablaCompletadas').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('logistica.gestion.completadas') }}",
                data: d => { d.gestor_id = gestorSeleccionado(); }
            },
            columns: [
                { data: 'cai' },
                { data: 'cliente' },
                { data: 'gestor' },
                { data: 'asesor_comercial' },
                { data: 'municipio' },
                { data: 'nombre_equipo' },
                { data: 'fecha_entrega_real' },
                {
                    data: 'id', orderable: false, render: (d) => {
                        return `<button type="button" class="btn btn-xs btn-outline-secondary" onclick="verHistorial(${d})"><i class="fas fa-history"></i></button>`;
                    }
                },
            ],
            language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
        });

        $('#filtroGestor').on('change', () => {
            tablaSinTratar.ajax.reload();
            tablaTratadas.ajax.reload();
            tablaAsignadas.ajax.reload();
            tablaCompletadas.ajax.reload();
            cargarResumenGde();
        });

        $('.kpi-card').on('click', function () {
            const tab = $(this).data('tab');
            $('.kpi-card').removeClass('active');
            $(this).addClass('active');
            $(`#tab-${tab}`).tab('show');
        });

        $('#gdeTabs a').on('shown.bs.tab', function () {
            const tab = $(this).data('tab');
            $('.kpi-card').removeClass('active');
            $(`.kpi-card[data-tab="${tab}"]`).addClass('active');
        });

        $('#tablaSinTratar tbody').on('change', '.chk-sin-tratar', function () {
            const id = parseInt($(this).val(), 10);
            if (this.checked) seleccionSinTratar.add(id); else seleccionSinTratar.delete(id);
            actualizarContadorSinTratar();
        });

        $('#tablaTratadas tbody').on('change', '.chk-tratadas', function () {
            const id = parseInt($(this).val(), 10);
            if (this.checked) seleccionTratadas.add(id); else seleccionTratadas.delete(id);
            actualizarContadorTratadas();
        });

        $('#chkAllSinTratar').on('change', function () {
            const checked = this.checked;
            $('#tablaSinTratar tbody .chk-sin-tratar').each(function () {
                const id = parseInt($(this).val(), 10);
                this.checked = checked;
                if (checked) seleccionSinTratar.add(id); else seleccionSinTratar.delete(id);
            });
            actualizarContadorSinTratar();
        });

        $('#chkAllTratadas').on('change', function () {
            const checked = this.checked;
            $('#tablaTratadas tbody .chk-tratadas').each(function () {
                const id = parseInt($(this).val(), 10);
                this.checked = checked;
                if (checked) seleccionTratadas.add(id); else seleccionTratadas.delete(id);
            });
            actualizarContadorTratadas();
        });

        cargarDepartamentosGde();
    });

    function actualizarContadorSinTratar() {
        $('#contadorSinTratar').text(seleccionSinTratar.size + ' seleccionadas');
    }

    function actualizarContadorTratadas() {
        $('#contadorTratadas').text(seleccionTratadas.size + ' seleccionadas');
    }

    function cargarResumenGde() {
        $.get("{{ route('logistica.gestion.resumen') }}", { gestor_id: gestorSeleccionado() }, r => {
            if (!r.success) return;
            $('#kpiSinGestor').text(r.sin_gestor);
            $('#kpiSinTratar').text(r.sin_tratar);
            $('#kpiTratadas').text(r.tratadas);
            $('#kpiAsignadas').text(r.asignadas);
            $('#kpiCompletadas').text(r.completadas);
        });
    }

    function cargarDepartamentosGde() {
        $.get("{{ route('logistica.zonas.departamentos') }}", r => {
            departamentosCacheGde = Array.isArray(r?.departamentos) ? r.departamentos : [];
            let opts = '<option value="">-- Seleccione Departamento --</option>';
            departamentosCacheGde.forEach(d => { opts += `<option value="${d.id}">${d.nombre}</option>`; });
            $('#tratarDeptoSelect').html(opts);
        });
    }

    function cargarMunicipiosTratar() {
        const deptoId = $('#tratarDeptoSelect').val();
        if (!deptoId) {
            $('#tratarMunicipioSelect').html('<option value="">-- Seleccione Municipio --</option>');
            if ($('#tratarMunicipioSelect').hasClass('select2-hidden-accessible')) $('#tratarMunicipioSelect').trigger('change.select2');
            return;
        }
        $.get("{{ url('/logistica/zonas/municipios') }}/" + deptoId, r => {
            const municipios = Array.isArray(r?.municipios) ? r.municipios : [];
            let opts = '<option value="">-- Seleccione Municipio --</option>';
            municipios.forEach(m => { opts += `<option value="${m.id}">${m.nombre}</option>`; });
            $('#tratarMunicipioSelect').html(opts);
            if ($('#tratarMunicipioSelect').hasClass('select2-hidden-accessible')) $('#tratarMunicipioSelect').trigger('change.select2');
        });
    }

    function inicializarSelect2EnModal(selector, modalSelector) {
        const $el = $(selector);
        if (!$el.length || typeof $el.select2 !== 'function') return;
        if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
        $el.select2({ width: '100%', dropdownParent: $(modalSelector) });
    }

    function abrirModalTratar() {
        if (seleccionSinTratar.size === 0) {
            Swal.fire({ title: 'Sin selección', text: 'Seleccione al menos una factura.', icon: 'warning', customClass: { container: 'swal-over-modal' } });
            return;
        }
        $('#tratarCantidadBadge').text(seleccionSinTratar.size + ' factura(s) seleccionada(s)');
        $('#tratarDeptoSelect').val('');
        $('#tratarMunicipioSelect').html('<option value="">-- Seleccione Municipio --</option>');
        $('#tratarDireccion').val('');
        $('#modalTratarFacturas').modal('show');
        $('#modalTratarFacturas').one('shown.bs.modal', () => {
            inicializarSelect2EnModal('#tratarDeptoSelect', '#modalTratarFacturas');
            inicializarSelect2EnModal('#tratarMunicipioSelect', '#modalTratarFacturas');
        });
    }

    function guardarTratamiento() {
        const departmentId = $('#tratarDeptoSelect').val();
        const municipalityId = $('#tratarMunicipioSelect').val();
        const direccion = $('#tratarDireccion').val().trim();

        if (!departmentId || !municipalityId || !direccion) {
            Swal.fire({ title: 'Datos incompletos', text: 'Complete departamento, municipio y dirección.', icon: 'warning', customClass: { container: 'swal-over-modal' } });
            return;
        }

        $.ajax({
            url: "{{ route('logistica.gestion.tratar') }}",
            method: 'POST',
            data: JSON.stringify({
                factura_ids: Array.from(seleccionSinTratar),
                department_id: departmentId,
                municipality_id: municipalityId,
                direccion_entrega: direccion,
            }),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
        }).done(r => {
            $('#modalTratarFacturas').modal('hide');
            seleccionSinTratar.clear();
            actualizarContadorSinTratar();
            tablaSinTratar.ajax.reload();
            tablaTratadas.ajax.reload();
            cargarResumenGde();
            Swal.fire({ title: r.title, text: r.text, icon: r.icon, customClass: { container: 'swal-over-modal' } });
        }).fail(x => {
            const d = x.responseJSON || {};
            Swal.fire({ title: d.title || 'Error', text: d.text || 'Ocurrió un error al tratar las facturas.', icon: 'error', customClass: { container: 'swal-over-modal' } });
        });
    }

    function abrirModalAsignar() {
        if (seleccionTratadas.size === 0) {
            Swal.fire({ title: 'Sin selección', text: 'Seleccione al menos una factura tratada.', icon: 'warning', customClass: { container: 'swal-over-modal' } });
            return;
        }
        $('#asignarCantidadBadge').text(seleccionTratadas.size + ' factura(s) seleccionada(s)');
        $('#asignarEquipoSelect').val('');
        $('#asignarFechaProgramada').val('');
        $('#asignarObservaciones').val('');
        $('#modalAsignarEquipo').modal('show');
        $('#modalAsignarEquipo').one('shown.bs.modal', () => {
            inicializarSelect2EnModal('#asignarEquipoSelect', '#modalAsignarEquipo');
        });
    }

    function guardarAsignacion() {
        const equipoId = $('#asignarEquipoSelect').val();
        const fecha = $('#asignarFechaProgramada').val();
        const observaciones = $('#asignarObservaciones').val();

        if (!equipoId || !fecha) {
            Swal.fire({ title: 'Datos incompletos', text: 'Seleccione un equipo y una fecha programada.', icon: 'warning', customClass: { container: 'swal-over-modal' } });
            return;
        }

        $.ajax({
            url: "{{ route('logistica.gestion.asignar') }}",
            method: 'POST',
            data: JSON.stringify({
                factura_ids: Array.from(seleccionTratadas),
                equipo_entrega_id: equipoId,
                fecha_programada: fecha,
                observaciones: observaciones,
            }),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
        }).done(r => {
            $('#modalAsignarEquipo').modal('hide');
            seleccionTratadas.clear();
            actualizarContadorTratadas();
            tablaTratadas.ajax.reload();
            tablaAsignadas.ajax.reload();
            cargarResumenGde();
            Swal.fire({ title: r.title, text: r.text, icon: r.icon, customClass: { container: 'swal-over-modal' } });
        }).fail(x => {
            const d = x.responseJSON || {};
            Swal.fire({ title: d.title || 'Error', text: d.text || 'Ocurrió un error al asignar las facturas.', icon: 'error', customClass: { container: 'swal-over-modal' } });
        });
    }

    function verHistorial(facturaId) {
        $('#historialContenido').html('<p class="text-muted mb-0">Cargando...</p>');
        $('#modalHistorial').modal('show');

        $.get("{{ url('/logistica/gestion/historial') }}/" + facturaId, r => {
            if (!r.success || !r.historial.length) {
                $('#historialContenido').html('<p class="text-muted mb-0">Sin movimientos registrados para esta factura.</p>');
                return;
            }

            const estadoLabel = { pendiente: 'Pendiente', tratada: 'Tratada', asignada: 'Asignada', completada: 'Completada' };
            let html = '';
            r.historial.forEach(h => {
                const ubicacion = [h.departamento, h.municipio].filter(Boolean).join(' / ');
                html += `
                    <div class="historial-item">
                        <div><strong>${estadoLabel[h.estado] || h.estado}</strong> &mdash; ${h.fecha}</div>
                        ${ubicacion ? `<div class="small text-muted">${ubicacion}${h.direccion_entrega ? ' - ' + h.direccion_entrega : ''}</div>` : ''}
                        ${h.distribucion_entrega_id ? `<div class="small text-muted">Distribución #${h.distribucion_entrega_id}</div>` : ''}
                        ${h.observaciones ? `<div class="small">${h.observaciones}</div>` : ''}
                        <div class="small text-muted">Por: ${h.usuario}</div>
                    </div>
                `;
            });
            $('#historialContenido').html(html);
        }).fail(() => {
            $('#historialContenido').html('<p class="text-danger mb-0">Error al cargar el historial.</p>');
        });
    }
</script>
@endpush
