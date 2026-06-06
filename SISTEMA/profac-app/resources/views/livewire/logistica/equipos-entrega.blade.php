@push('styles')
<style>
    .eq-page {
            --eq-primary: #0f766e;
            --eq-primary-soft: #ccfbf1;
            --eq-accent: #b45309;
            --eq-slate: #334155;
            --eq-border: #e2e8f0;
            --eq-bg-soft: #f8fafc;
            --eq-card-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
        }

        .eq-page .card.eq-main-card,
        .eq-page .modal-content {
            border: 1px solid var(--eq-border);
            box-shadow: var(--eq-card-shadow);
        }

        .eq-page .eq-main-card .card-header {
            border-bottom: 1px solid var(--eq-border);
            background: linear-gradient(120deg, #ffffff 0%, #f1f5f9 100%);
            padding: 1rem 1.25rem;
        }

        .eq-page .eq-title-wrap h3 {
            margin-bottom: .15rem;
            color: #0f172a;
            font-weight: 700;
            letter-spacing: .01em;
        }

        .eq-page .eq-title-wrap small {
            color: #64748b;
        }

        .eq-page .eq-hero-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--eq-primary), #14b8a6);
            color: #fff;
            margin-right: .7rem;
            box-shadow: 0 10px 20px rgba(15, 118, 110, 0.24);
        }

        .eq-page .eq-btn-primary {
            border: 0;
            border-radius: 10px;
            padding: .5rem .9rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--eq-primary), #0d9488);
            transition: all .2s ease;
        }

        .eq-page .eq-btn-primary:hover,
        .eq-page .eq-btn-primary:focus {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(15, 118, 110, 0.2);
        }

        .eq-page .table-wrap {
            border: 1px solid var(--eq-border);
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }

        .eq-page #tablaEquipos thead th {
            border-bottom: 0;
            background: #f1f5f9;
            color: #334155;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            vertical-align: middle;
        }

        .eq-page #tablaEquipos tbody td {
            vertical-align: middle;
        }

        .eq-page #tablaEquipos tbody tr {
            transition: background-color .2s ease;
        }

        .eq-page #tablaEquipos tbody tr:hover {
            background: #f8fafc;
        }

        .eq-page .eq-modal .modal-dialog {
            max-width: 640px;
        }

        .eq-page .eq-modal .modal-header {
            border-bottom: 1px solid var(--eq-border);
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 65%);
            padding: 1rem 1.25rem;
        }

        .eq-page .eq-modal .modal-title {
            color: #0f172a;
            font-weight: 700;
        }

        .eq-page .eq-modal .modal-body {
            padding: .9rem;
            background: #fcfdff;
        }

        .eq-page .eq-section {
            border: 1px solid var(--eq-border);
            border-radius: 12px;
            background: #fff;
            padding: .75rem;
            margin-bottom: .75rem;
        }

        .eq-page .eq-section-title {
            margin-bottom: .85rem;
            font-size: .93rem;
            font-weight: 700;
            color: #334155;
            display: flex;
            align-items: center;
            gap: .45rem;
        }

        .eq-page .eq-section-title i {
            color: var(--eq-primary);
        }

        .eq-page .form-control,
        .eq-page .custom-select {
            border-radius: 10px;
            border-color: #cbd5e1;
        }

        .eq-page .form-control:focus,
        .eq-page .custom-select:focus {
            border-color: #14b8a6;
            box-shadow: 0 0 0 .2rem rgba(20, 184, 166, 0.15);
        }

        .eq-page .eq-member-add {
            background: var(--eq-bg-soft);
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: .8rem;
        }

        .eq-page .eq-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .25rem .6rem;
            font-weight: 600;
            font-size: .76rem;
            background: var(--eq-primary-soft);
            color: #0f766e;
            margin-right: .4rem;
        }

        .eq-page .eq-commission-box {
            border-radius: 12px;
            border: 1px solid #bae6fd;
            background: linear-gradient(120deg, #eff6ff 0%, #f8fafc 100%);
            padding: .8rem 1rem;
        }

        .eq-page .eq-commission-box .eq-total {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
        }

        .eq-page .eq-progress {
            height: 8px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
            margin-top: .45rem;
        }

        .eq-page .eq-progress .eq-progress-bar {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #0ea5e9, #0f766e);
            transition: width .25s ease;
        }

        .eq-page .eq-list .list-group-item {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: .45rem;
        }

        .eq-page .eq-member-name {
            color: #0f172a;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .45rem;
        }

        .eq-page .eq-modal .modal-footer {
            border-top: 1px solid var(--eq-border);
            padding: .7rem .9rem;
            background: #fff;
        }

        .eq-page .btn {
            border-radius: 10px;
            transition: all .2s ease;
        }

        .eq-page .btn:hover {
            transform: translateY(-1px);
        }

        .eq-page .eq-btn-icon {
            min-width: 42px;
        }

        @media (max-width: 767.98px) {
            .eq-page .eq-modal .modal-body {
                padding: .75rem;
            }

            .eq-page .eq-main-card .card-header {
                padding: .9rem 1rem;
            }

            .eq-page .eq-title-wrap h3 {
                font-size: 1.15rem;
            }
        }

        .swal-over-modal {
            z-index: 10000 !important;
        }

        .eq-page .eq-loading-overlay {
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

        .eq-page .eq-loading-overlay.is-hidden {
            opacity: 0;
            pointer-events: none;
        }

        .eq-page .eq-loader {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 4px solid #d1fae5;
            border-top-color: var(--eq-primary);
            animation: eqSpin .75s linear infinite;
        }

        .eq-page .eq-loading-text {
            color: #0f172a;
            font-weight: 600;
            font-size: .92rem;
        }

        @keyframes eqSpin {
            to {
                transform: rotate(360deg);
            }
        }

        .eq-members-modal {
            text-align: left;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .eq-members-modal thead th {
            background: #f8fafc;
            color: #334155;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            border-bottom: 1px solid #e2e8f0;
        }

        .eq-members-modal td,
        .eq-members-modal th {
            vertical-align: middle;
        }

        .eq-members-modal .eq-user-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-weight: 600;
            color: #0f172a;
        }

        .eq-members-modal .eq-pct-badge {
            display: inline-block;
            padding: .22rem .6rem;
            border-radius: 999px;
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
        }

        .eq-total-inline {
            font-size: .88rem;
            font-weight: 600;
            color: #0f172a;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: .2rem .6rem;
        }

        .eq-member-add .d-flex.gap-2 {
            gap: .5rem;
        }

        .select2-container .select2-dropdown {
            z-index: 2060 !important;
        }

        #modalNuevoEquipo,
        #modalEditarEquipo {
            z-index: 2050 !important;
        }

    .modal-backdrop {
        z-index: 2040 !important;
    }
</style>
@endpush

<div class="eq-page">
    <div class="eq-loading-overlay" id="pageLoadingEquipos" aria-live="polite" aria-busy="true">
        <div class="eq-loader"></div>
        <div class="eq-loading-text">Cargando equipos de entrega...</div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card eq-main-card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <div class="d-flex align-items-center eq-title-wrap mb-2 mb-md-0">
                        <span class="eq-hero-icon"><i class="fas fa-truck"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Equipos de Entrega</h3>
                            <small>Administre camiones, miembros y distribución de comisiones</small>
                        </div>
                    </div>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm eq-btn-primary" onclick="abrirModalNuevoEquipo()">
                            <i class="fa fa-plus mr-1"></i> Nuevo Equipo
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-wrap">
                    <table id="tablaEquipos" class="table table-bordered table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Equipo</th>
                                <th>Miembros</th>
                                <th>% Asignado</th>
                                <th>Creador</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
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

    <div class="modal fade eq-modal" id="modalNuevoEquipo">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-users-cog mr-2"></i>Nuevo Equipo</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoEquipo">
                        <div class="eq-section">
                            <div class="eq-section-title"><i class="fas fa-truck-moving"></i>Datos Generales del Equipo</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-md-0">
                                        <label class="font-weight-semibold mb-1">Nombre del Equipo *</label>
                                        <input type="text" class="form-control" id="inputNombreEquipoNuevo" name="nombre_equipo" required placeholder="Ej: Ruta Norte 01">
                                        <div class="invalid-feedback">El nombre del equipo es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-semibold mb-1">Descripcion</label>
                                        <textarea class="form-control" name="descripcion" rows="2" placeholder="Detalle operativo del equipo"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="eq-section">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="eq-section-title mb-0"><i class="fas fa-users"></i>Miembros y Porcentaje de Comisión</div>
                                <span class="eq-total-inline">Total: <strong id="totalPct">0.00</strong>%</span>
                            </div>

                            <div class="eq-member-add mb-3">
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="flex-grow-1">
                                        <select class="form-control" id="selectUsuarioNuevo">
                                            <option value="">-- Seleccione --</option>
                                            @foreach($usuarios as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div style="width:110px;flex-shrink:0;">
                                        <input type="number" class="form-control" id="inputPorcentaje" min="0" max="100" step="0.01" placeholder="% Comisión">
                                    </div>
                                    <button type="button" class="btn btn-success btn-sm px-3 flex-shrink-0" onclick="agregarMiembroTmp()"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>

                            <div id="listaMiembrosTmp" class="eq-list"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarEquipo()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Equipo -->
    <div class="modal fade eq-modal" id="modalEditarEquipo">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Editar Equipo</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="formEditarEquipo">
                        <input type="hidden" name="equipo_id" id="editEquipoId">
                        <div class="eq-section">
                            <div class="eq-section-title"><i class="fas fa-info-circle"></i>Información del Equipo</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-md-0">
                                        <label class="font-weight-semibold mb-1">Nombre del Equipo *</label>
                                        <input type="text" class="form-control" name="nombre_equipo" id="editNombreEquipo" required>
                                        <div class="invalid-feedback">El nombre del equipo es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-semibold mb-1">Descripcion</label>
                                        <textarea class="form-control" name="descripcion" id="editDescripcion" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="eq-section">
                            <div class="eq-section-title"><i class="fas fa-users"></i>Miembros Actuales</div>
                            <div id="listaMiembrosActuales"></div>

                            <div class="eq-section-title mt-3 d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-user-plus"></i>Agregar Nuevo Miembro</span>
                                <span class="eq-total-inline">Total: <strong id="totalPctEditar">0.00</strong>%</span>
                            </div>
                            <div class="eq-member-add mb-2">
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="flex-grow-1">
                                        <select class="form-control" id="selectUsuarioEditar">
                                            <option value="">-- Seleccione --</option>
                                            @foreach($usuarios as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div style="width:110px;flex-shrink:0;">
                                        <input type="number" class="form-control" id="inputPorcentajeEditar" min="0" max="100" step="0.01" placeholder="% Comisión">
                                    </div>
                                    <button type="button" class="btn btn-success btn-sm px-3 flex-shrink-0" onclick="agregarNuevoMiembro()"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="actualizarEquipo()">Actualizar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let tablaEquipos, miembrosTmp = [], equipoEditando = null;
$(document).ready(() => {
    tablaEquipos = $('#tablaEquipos').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('logistica.equipos.listar') }}",
        columns: [
            {data: 'id'},
            {data: 'nombre_equipo'},
            {data: 'miembros'},
            {data: 'porcentaje'},
            {data: 'creador'},
            {data: 'estado'},
            {data: 'created_at'},
            {data: 'opciones', orderable: false}
        ],
        language: {url: '/js/plugins/dataTables/i18n/Spanish.json'},
        initComplete: function() {
            ocultarLoaderPantalla();
        }
    });

    setTimeout(() => ocultarLoaderPantalla(), 1300);

    // Limpiar validación inline al escribir
    $('#inputNombreEquipoNuevo').on('input', function() { $(this).removeClass('is-invalid'); });
    $('#editNombreEquipo').on('input', function() { $(this).removeClass('is-invalid'); });


});

function ocultarLoaderPantalla() {
    $('#pageLoadingEquipos').addClass('is-hidden');
}

function filtrarSelect(selectId, texto) {
    var buscar = texto.toLowerCase();
    $('#' + selectId + ' option').each(function() {
        var val = $(this).val();
        var mostrar = val === '' || $(this).text().toLowerCase().indexOf(buscar) !== -1;
        $(this).toggle(mostrar);
    });
    // Si la opción seleccionada quedó oculta, resetear
    var selVal = $('#' + selectId).val();
    if (selVal && $('#' + selectId + ' option:selected').is(':hidden')) {
        $('#' + selectId).val('');
    }
}

function abrirModalNuevoEquipo() {
    $('#formNuevoEquipo')[0].reset();
    $('#inputNombreEquipoNuevo').removeClass('is-invalid');
    miembrosTmp = [];
    actualizarListaTmp();
    $('#modalNuevoEquipo').modal('show');
}

function agregarMiembroTmp() {
    const uid = $('#selectUsuarioNuevo').val();
    const pct = parseFloat($('#inputPorcentaje').val());
    if (!uid || !pct) return Swal.fire({title: 'Error', text: 'Complete los datos', icon: 'error', customClass: {container: 'swal-over-modal'}});
    if (miembrosTmp.find(m => m.user_id == uid)) return Swal.fire({title: 'Error', text: 'Usuario duplicado', icon: 'error', customClass: {container: 'swal-over-modal'}});
    
    // Calcular total actual
    const totalActual = miembrosTmp.reduce((sum, m) => sum + m.porcentaje, 0);
    const nuevoTotal = totalActual + pct;
    
    if (nuevoTotal > 100) {
        const disponible = 100 - totalActual;
        return Swal.fire({
            title: 'Error', 
            html: `Solo hay <strong>${disponible.toFixed(2)}%</strong> disponible.<br>El total ya es <strong>${totalActual.toFixed(2)}%</strong>`, 
            icon: 'error',
            customClass: {container: 'swal-over-modal'}
        });
    }
    
    miembrosTmp.push({user_id: uid, porcentaje: pct, nombre: $('#selectUsuarioNuevo option:selected').text()});
    actualizarListaTmp();
    $('#selectUsuarioNuevo').val('');
    $('#inputPorcentaje').val('');
}

function removerTmp(idx) {
    miembrosTmp.splice(idx, 1);
    actualizarListaTmp();
}

function actualizarListaTmp() {
    let h = '', t = 0;
    miembrosTmp.forEach((m, i) => {
        t += m.porcentaje;
        h += `<div class="list-group-item d-flex justify-content-between align-items-center"><span class="eq-member-name"><i class="fas fa-user-circle text-secondary"></i>${m.nombre}</span><div><span class="badge badge-info mr-2">${m.porcentaje}%</span><button class="btn btn-sm btn-danger" onclick="removerTmp(${i})"><i class="fa fa-trash"></i></button></div></div>`;
    });
    $('#listaMiembrosTmp').html(h || '<p class="text-muted mb-0">Sin miembros asignados todavía</p>');
    $('#totalPct').text(t.toFixed(2));
    
    // Habilitar / bloquear inputs según el total
    if (t >= 100) {
        $('#selectUsuarioNuevo, #inputPorcentaje').prop('disabled', true);
    } else {
        $('#selectUsuarioNuevo, #inputPorcentaje').prop('disabled', false);
    }
}

function guardarEquipo() {
    // Validación inline en cliente
    var nombre = $('#inputNombreEquipoNuevo').val().trim();
    if (!nombre) {
        $('#inputNombreEquipoNuevo').addClass('is-invalid').focus();
        return;
    }
    $('#inputNombreEquipoNuevo').removeClass('is-invalid');

    if (!miembrosTmp.length) {
        Swal.fire({title: 'Sin miembros', text: 'Agregue al menos un miembro al equipo.', icon: 'warning', customClass: {container: 'swal-over-modal'}});
        return;
    }
    const fd = new FormData($('#formNuevoEquipo')[0]);
    fd.append('miembros', JSON.stringify(miembrosTmp));
    $.ajax({
        url: "{{ route('logistica.equipos.guardar') }}",
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: r => {
            $('#modalNuevoEquipo').modal('hide');
            tablaEquipos.ajax.reload();
            Swal.fire({title: r.title, text: r.text, icon: r.icon});
        },
        error: x => {
            var msg = x.responseJSON ? (x.responseJSON.text || x.responseJSON.message || 'Error inesperado.') : 'Error inesperado.';
            Swal.fire({title: 'Error', text: msg, icon: 'error', customClass: {container: 'swal-over-modal'}});
        }
    });
}

function verMiembros(id) {
    $.get("{{ url('/logistica/equipos/miembros') }}/" + id, r => {
        let h = '<div class="table-responsive eq-members-modal"><table class="table table-sm mb-0"><thead><tr><th>Usuario</th><th class="text-right">% Comisión</th></tr></thead><tbody>';
        if (!r.miembros.length) {
            h += '<tr><td colspan="2" class="text-center text-muted py-3">Sin miembros asignados</td></tr>';
        }
        r.miembros.forEach(m => {
            h += `<tr><td><span class="eq-user-badge"><i class="fas fa-user-circle text-secondary"></i>${m.nombre_usuario}</span></td><td class="text-right"><span class="eq-pct-badge">${parseFloat(m.porcentaje_comision).toFixed(2)}%</span></td></tr>`;
        });
        h += '</tbody></table></div>';
        Swal.fire({
            title: 'Miembros del Equipo',
            html: h,
            width: 700,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#0f766e',
            customClass: {container: 'swal-over-modal'}
        });
    });
}

function desactivarEquipo(id) {
    Swal.fire({
        title: 'Confirmar',
        text: 'Desactivar equipo?',
        icon: 'warning',
        showCancelButton: true
    }).then(r => {
        if (r.isConfirmed) {
            $.post("{{ url('/logistica/equipos/desactivar') }}/" + id, {_token: $('meta[name="csrf-token"]').attr('content')}, r => {
                Swal.fire(r.title, r.text, r.icon);
                tablaEquipos.ajax.reload();
            }).fail(x => Swal.fire(x.responseJSON.title, x.responseJSON.text, x.responseJSON.icon));
        }
    });
}

function editarEquipo(id) {
    equipoEditando = id;
    $.get("{{ url('/logistica/equipos/obtener') }}/" + id, function(r) {
        if (r.success) {
            $('#editEquipoId').val(r.equipo.id);
            $('#editNombreEquipo').val(r.equipo.nombre_equipo);
            $('#editDescripcion').val(r.equipo.descripcion);
            $('#selectUsuarioEditar').val('');
            
            // Cargar miembros actuales
            cargarMiembrosActuales(id);
            $('#modalEditarEquipo').modal('show');
        }
    }).fail(() => Swal.fire({title: 'Error', text: 'No se pudo cargar el equipo', icon: 'error', customClass: {container: 'swal-over-modal'}}));
}

function cargarMiembrosActuales(equipoId) {
    $.get("{{ url('/logistica/equipos/miembros') }}/" + equipoId, function(r) {
        if (r.success) {
            let html = '<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Usuario</th><th>%</th><th></th></tr></thead><tbody>';
            let total = 0;
            r.miembros.forEach(m => {
                total += parseFloat(m.porcentaje_comision);
                html += `<tr>
                    <td>${m.nombre_usuario}</td>
                    <td><span class="badge badge-info">${m.porcentaje_comision}%</span></td>
                    <td><button type="button" class="btn btn-xs btn-danger" onclick="removerMiembroEquipo(${m.id})"><i class="fa fa-trash"></i></button></td>
                </tr>`;
            });
            html += '</tbody></table></div>';
            $('#listaMiembrosActuales').html(html);
            $('#totalPctEditar').text(total.toFixed(2));
        }
    });
}

function agregarNuevoMiembro() {
    const equipoId = $('#editEquipoId').val();
    const userId = $('#selectUsuarioEditar').val();
    const porcentaje = parseFloat($('#inputPorcentajeEditar').val());
    
    if (!userId || !porcentaje) return Swal.fire({title: 'Error', text: 'Complete los datos', icon: 'error', customClass: {container: 'swal-over-modal'}});
    
    $.post("{{ url('/logistica/equipos/agregar-miembro') }}", {
        _token: $('meta[name="csrf-token"]').attr('content'),
        equipo_id: equipoId,
        user_id: userId,
        porcentaje: porcentaje
    }, r => {
        Swal.fire({title: r.title, text: r.text, icon: r.icon, customClass: {container: 'swal-over-modal'}});
        if (r.icon === 'success') {
            cargarMiembrosActuales(equipoId);
            $('#selectUsuarioEditar').val('');
            $('#inputPorcentajeEditar').val('');
        }
    }).fail(x => Swal.fire({title: x.responseJSON.title, text: x.responseJSON.text, icon: x.responseJSON.icon, customClass: {container: 'swal-over-modal'}}));
}

function removerMiembroEquipo(miembroId) {
    Swal.fire({
        title: 'Confirmar',
        text: 'Remover este miembro del equipo?',
        icon: 'warning',
        showCancelButton: true,
        customClass: {container: 'swal-over-modal'}
    }).then(r => {
        if (r.isConfirmed) {
            $.post("{{ url('/logistica/equipos/remover-miembro') }}/" + miembroId, {
                _token: $('meta[name="csrf-token"]').attr('content')
            }, r => {
                Swal.fire({title: r.title, text: r.text, icon: r.icon, customClass: {container: 'swal-over-modal'}});
                if (r.icon === 'success') {
                    cargarMiembrosActuales(equipoEditando);
                }
            }).fail(x => Swal.fire({title: x.responseJSON.title, text: x.responseJSON.text, icon: x.responseJSON.icon, customClass: {container: 'swal-over-modal'}}));
        }
    });
}

function actualizarEquipo() {
    // Validación inline en cliente
    var nombre = $('#editNombreEquipo').val().trim();
    if (!nombre) {
        $('#editNombreEquipo').addClass('is-invalid').focus();
        return;
    }
    $('#editNombreEquipo').removeClass('is-invalid');

    const fd = new FormData($('#formEditarEquipo')[0]);
    fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
    $.ajax({
        url: "{{ url('/logistica/equipos/actualizar') }}",
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: r => {
            if (r.icon === 'success') {
                $('#modalEditarEquipo').modal('hide');
                tablaEquipos.ajax.reload();
                Swal.fire({title: r.title, text: r.text, icon: r.icon});
            } else {
                Swal.fire({title: r.title, text: r.text, icon: r.icon, customClass: {container: 'swal-over-modal'}});
            }
        },
        error: x => Swal.fire({title: x.responseJSON.title, text: x.responseJSON.text, icon: x.responseJSON.icon, customClass: {container: 'swal-over-modal'}})
    });
}
</script>
@endpush