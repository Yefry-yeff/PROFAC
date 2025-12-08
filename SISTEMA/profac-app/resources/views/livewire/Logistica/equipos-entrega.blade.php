<div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Equipos de Entrega</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalNuevoEquipo()">
                            <i class="fa fa-plus"></i> Nuevo Equipo
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="tablaEquipos" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Equipo</th>
                                <th>Descripcion</th>
                                <th>Creador</th>
                                <th>Miembros</th>
                                <th>% Asignado</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Opciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNuevoEquipo">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Equipo</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoEquipo">
                        <div class="form-group">
                            <label>Nombre del Equipo *</label>
                            <input type="text" class="form-control" name="nombre_equipo" required>
                        </div>
                        <div class="form-group">
                            <label>Descripcion</label>
                            <textarea class="form-control" name="descripcion" rows="3"></textarea>
                        </div>
                        <hr>
                        <h6>Miembros</h6>
                        <div class="row mb-2">
                            <div class="col-8">
                                <select class="form-control" id="selectUsuarioNuevo">
                                    <option value="">Seleccione...</option>
                                    @foreach($usuarios as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <input type="number" class="form-control" id="inputPorcentaje" min="0" max="100" step="0.01" placeholder="%">
                            </div>
                            <div class="col-1">
                                <button type="button" class="btn btn-success" onclick="agregarMiembroTmp()"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                        <div id="listaMiembrosTmp"></div>
                        <div class="alert alert-info">Total: <span id="totalPct">0</span>%</div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarEquipo()">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let tablaEquipos, miembrosTmp = [];
$(document).ready(() => {
    tablaEquipos = $('#tablaEquipos').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('logistica.equipos.listar') }}",
        columns: [
            {data: 'id'},
            {data: 'nombre_equipo'},
            {data: 'descripcion'},
            {data: 'creador'},
            {data: 'miembros'},
            {data: 'porcentaje'},
            {data: 'estado'},
            {data: 'created_at'},
            {data: 'opciones', orderable: false}
        ],
        language: {url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'}
    });
});

function abrirModalNuevoEquipo() {
    $('#formNuevoEquipo')[0].reset();
    miembrosTmp = [];
    actualizarListaTmp();
    $('#modalNuevoEquipo').modal('show');
}

function agregarMiembroTmp() {
    const uid = $('#selectUsuarioNuevo').val();
    const pct = parseFloat($('#inputPorcentaje').val());
    if (!uid || !pct) return Swal.fire('Error', 'Complete los datos', 'error');
    if (miembrosTmp.find(m => m.user_id == uid)) return Swal.fire('Error', 'Usuario duplicado', 'error');
    
    // Calcular total actual
    const totalActual = miembrosTmp.reduce((sum, m) => sum + m.porcentaje, 0);
    const nuevoTotal = totalActual + pct;
    
    if (nuevoTotal > 100) {
        const disponible = 100 - totalActual;
        return Swal.fire('Error', `Solo hay ${disponible.toFixed(2)}% disponible. El total ya es ${totalActual.toFixed(2)}%`, 'error');
    }
    
    miembrosTmp.push({user_id: uid, porcentaje: pct, nombre: $('#selectUsuarioNuevo option:selected').text()});
    actualizarListaTmp();
    $('#selectUsuarioNuevo, #inputPorcentaje').val('');
}

function removerTmp(idx) {
    miembrosTmp.splice(idx, 1);
    actualizarListaTmp();
}

function actualizarListaTmp() {
    let h = '', t = 0;
    miembrosTmp.forEach((m, i) => {
        t += m.porcentaje;
        h += `<div class="list-group-item d-flex justify-content-between"><span>${m.nombre}</span><div><span class="badge badge-primary">${m.porcentaje}%</span> <button class="btn btn-sm btn-danger" onclick="removerTmp(${i})"><i class="fa fa-trash"></i></button></div></div>`;
    });
    $('#listaMiembrosTmp').html(h || '<p class="text-muted">Sin miembros</p>');
    $('#totalPct').text(t.toFixed(2));
    
    // Cambiar color según el total
    const alert = $('#totalPct').parent();
    alert.removeClass('alert-info alert-danger alert-success');
    if (t > 100) {
        alert.addClass('alert-danger');
    } else if (t === 100) {
        alert.addClass('alert-success');
        // Bloquear agregar más miembros
        $('#selectUsuarioNuevo, #inputPorcentaje').prop('disabled', true);
    } else {
        alert.addClass('alert-info');
        // Habilitar agregar miembros
        $('#selectUsuarioNuevo, #inputPorcentaje').prop('disabled', false);
    }
}

function guardarEquipo() {
    if (!miembrosTmp.length) return Swal.fire('Error', 'Agregue miembros', 'error');
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
            Swal.fire(r.title, r.text, r.icon);
            $('#modalNuevoEquipo').modal('hide');
            tablaEquipos.ajax.reload();
        },
        error: x => Swal.fire(x.responseJSON.title, x.responseJSON.text, x.responseJSON.icon)
    });
}

function verMiembros(id) {
    $.get("{{ url('/logistica/equipos/miembros') }}/" + id, r => {
        let h = '<table class="table table-sm"><tr><th>Usuario</th><th>%</th></tr>';
        r.miembros.forEach(m => h += `<tr><td>${m.nombre_usuario}</td><td>${m.porcentaje_comision}%</td></tr>`);
        Swal.fire({title: 'Miembros', html: h + '</table>', width: 600});
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
</script>
@endpush