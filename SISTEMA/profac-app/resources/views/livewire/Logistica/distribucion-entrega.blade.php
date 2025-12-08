<div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Distribuciones de Entrega</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalNuevaDistribucion()">
                            <i class="fa fa-plus"></i> Nueva Distribucion
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="tablaDistribuciones" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Equipo</th>
                                <th>Progreso</th>
                                <th>Estado</th>
                                <th>Creador</th>
                                <th>Opciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNuevaDistribucion">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Distribucion</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="formNuevaDistribucion">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Equipo *</label>
                                    <select class="form-control" name="equipo_entrega_id" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($equipos as $eq)
                                            <option value="{{ $eq->id }}">{{ $eq->nombre_equipo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Fecha Programada *</label>
                                    <input type="date" class="form-control" name="fecha_programada" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea class="form-control" name="observaciones" rows="2"></textarea>
                        </div>
                        <hr>
                        <h6>Facturas a Entregar</h6>
                        
                        <!-- Ingresar por número de factura -->
                        <div class="card mb-3 border-primary">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-10">
                                        <input type="text" class="form-control form-control-lg" id="numeroFactura" 
                                               placeholder="Ingrese número de factura y presione Enter" 
                                               onkeypress="if(event.keyCode==13){agregarPorNumero(); return false;}"
                                               autofocus>
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-success btn-lg btn-block" onclick="agregarPorNumero()">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted"><i class="fa fa-info-circle"></i> Ingrese el número de factura y presione <kbd>Enter</kbd> para agregar</small>
                            </div>
                        </div>
                        
                        <div id="facturasSeleccionadas"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarDistribucion()">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let tablaDistribuciones, facturasSelTmp = [];
$(document).ready(() => {
    tablaDistribuciones = $('#tablaDistribuciones').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('logistica.distribuciones.listar') }}",
        columns: [
            {data: 'id'},
            {data: 'fecha_programada'},
            {data: 'nombre_equipo'},
            {data: 'progreso'},
            {data: 'estado'},
            {data: 'creador'},
            {data: 'opciones', orderable: false}
        ],
        language: {url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'},
        order: [[1, 'desc']]
    });
});

function abrirModalNuevaDistribucion() {
    $('#formNuevaDistribucion')[0].reset();
    facturasSelTmp = [];
    actualizarFacturasSel();
    $('#numeroFactura').val('');
    $('#modalNuevaDistribucion').modal('show');
    setTimeout(() => $('#numeroFactura').focus(), 500);
}

function agregarPorNumero() {
    const numero = $('#numeroFactura').val().trim();
    if (!numero) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo vacio',
            text: 'Ingrese un numero de factura',
            timer: 1500
        });
        return;
    }
    
    // Buscar la factura por número exacto
    $.ajax({
        url: "{{ url('/logistica/facturas/por-numero') }}",
        type: 'GET',
        data: {numero: numero},
        success: function(response) {
            if (response.success && response.factura) {
                const f = response.factura;
                facturasSelTmp.push({
                    id: f.id, 
                    numero: f.numero_factura, 
                    cliente: f.cliente, 
                    total: f.total
                });
                actualizarFacturasSel();
                $('#numeroFactura').val('').focus();
                
                // Notificación breve
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'success',
                    title: `Factura #${f.numero_factura} agregada`
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No encontrada',
                    text: `No existe la factura #${numero}`,
                    timer: 2000
                });
                $('#numeroFactura').select();
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al buscar la factura',
                timer: 2000
            });
        }
    });
}

function removerFacturaTmp(idx) {
    facturasSelTmp.splice(idx, 1);
    actualizarFacturasSel();
    $('#numeroFactura').focus();
}

function actualizarFacturasSel() {
    if (!facturasSelTmp.length) {
        $('#facturasSeleccionadas').html(`
            <div class="alert alert-info text-center">
                <i class="fa fa-info-circle"></i> Sin facturas agregadas. 
                <br><small>Ingrese el número de factura y presione Enter</small>
            </div>
        `);
        return;
    }
    
    let h = '<h6 class="mt-3">Facturas Seleccionadas <span class="badge badge-primary">' + facturasSelTmp.length + '</span></h6>';
    h += '<div class="list-group">';
    facturasSelTmp.forEach((f, i) => {
        h += `<div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <strong>#${f.numero}</strong> - ${f.cliente} 
                <br><small class="text-muted">Total: Q${f.total}</small>
            </div>
            <button class="btn btn-sm btn-danger" onclick="removerFacturaTmp(${i})" title="Eliminar">
                <i class="fa fa-trash"></i>
            </button>
        </div>`;
    });
    h += '</div>';
    $('#facturasSeleccionadas').html(h);
}

function guardarDistribucion() {
    if (!facturasSelTmp.length) return Swal.fire('Error', 'Agregue facturas', 'error');
    const fd = new FormData($('#formNuevaDistribucion')[0]);
    fd.append('facturas', JSON.stringify(facturasSelTmp.map(f => f.id)));
    $.ajax({
        url: "{{ route('logistica.distribuciones.guardar') }}",
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: r => {
            Swal.fire(r.title, r.text, r.icon);
            $('#modalNuevaDistribucion').modal('hide');
            tablaDistribuciones.ajax.reload();
        },
        error: x => Swal.fire(x.responseJSON.title, x.responseJSON.text, x.responseJSON.icon)
    });
}

function verFacturas(id) {
    $.get("{{ url('/logistica/distribuciones/facturas') }}/" + id, r => {
        let h = '<table class="table table-sm"><tr><th>#</th><th>Factura</th><th>Cliente</th><th>Estado</th></tr>';
        r.facturas.forEach(f => h += `<tr><td>${f.orden_entrega}</td><td>${f.numero_factura}</td><td>${f.cliente}</td><td><span class="badge badge-${f.estado_entrega=='entregado'?'success':f.estado_entrega=='parcial'?'warning':'danger'}">${f.estado_entrega}</span></td></tr>`);
        Swal.fire({title: 'Facturas', html: h + '</table>', width: 800});
    });
}

function iniciarDistribucion(id) {
    Swal.fire({title: 'Iniciar?', icon: 'question', showCancelButton: true}).then(r => {
        if (r.isConfirmed) {
            $.post("{{ url('/logistica/distribuciones/iniciar') }}/" + id, {_token: $('meta[name="csrf-token"]').attr('content')}, r => {
                Swal.fire(r.title, r.text, r.icon);
                tablaDistribuciones.ajax.reload();
            }).fail(x => Swal.fire(x.responseJSON.title, x.responseJSON.text, x.responseJSON.icon));
        }
    });
}

function cancelarDistribucion(id) {
    Swal.fire({title: 'Cancelar?', icon: 'warning', showCancelButton: true}).then(r => {
        if (r.isConfirmed) {
            $.post("{{ url('/logistica/distribuciones/cancelar') }}/" + id, {_token: $('meta[name="csrf-token"]').attr('content')}, r => {
                Swal.fire(r.title, r.text, r.icon);
                tablaDistribuciones.ajax.reload();
            }).fail(x => Swal.fire(x.responseJSON.title, x.responseJSON.text, x.responseJSON.icon));
        }
    });
}

function abrirConfirmacion(id) {
    window.location.href = "{{ url('/logistica/confirmacion') }}?distribucion=" + id;
}
</script>
@endpush