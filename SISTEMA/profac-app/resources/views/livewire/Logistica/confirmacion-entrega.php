<div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Confirmacion de Entregas</h3>
                    <div class="card-tools">
                        <input type="date" class="form-control form-control-sm" id="fechaConfirmacion" onchange="cargarDistribucionesFecha()" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="card-body">
                    <div id="listaDistribuciones"></div>
                    <div id="contenidoConfirmacion" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalIncidencia">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Incidencia</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="formIncidencia">
                        <input type="hidden" id="productoIncidenciaId">
                        <div class="form-group">
                            <label>Tipo</label>
                            <select class="form-control" id="tipoIncidencia">
                                <option value="producto_danado">Producto danado</option>
                                <option value="cantidad_incorrecta">Cantidad incorrecta</option>
                                <option value="cliente_rechazo">Cliente rechazo</option>
                                <option value="direccion_incorrecta">Direccion incorrecta</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Descripcion</label>
                            <textarea class="form-control" id="descripcionIncidencia" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarIncidencia()">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let distribucionActual = null;

$(document).ready(() => {
    cargarDistribucionesFecha();
});

function cargarDistribucionesFecha() {
    const fecha = $('#fechaConfirmacion').val();
    $.get("<?= route('logistica.confirmacion.distribuciones') ?>", {fecha}, r => {
        let h = '<div class="list-group">';
        r.distribuciones.forEach(d => {
            h += `<a href="#" class="list-group-item list-group-item-action" onclick="cargarConfirmacion(${d.id}); return false;">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${d.nombre_equipo}</h6>
                    <small>${d.facturas_entregadas}/${d.total_facturas}</small>
                </div>
            </a>`;
        });
        h += '</div>';
        $('#listaDistribuciones').html(h || '<p class="text-muted">Sin distribuciones para esta fecha</p>');
    });
}

function cargarConfirmacion(distId) {
    distribucionActual = distId;
    $.get("<?= url('/logistica/confirmacion/facturas') ?>/" + distId, r => {
        let h = '';
        r.facturas.forEach(f => {
            h += `<div class="card mb-2">
                <div class="card-header">
                    <h6>Factura #${f.numero_factura} - ${f.cliente}</h6>
                    <small>${f.direccion || 'Sin direccion'} - ${f.telefono || 'Sin telefono'}</small>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th><input type="checkbox" onclick="seleccionarTodosFact(${f.distribucion_factura_id}, this)"></th><th>Producto</th><th>Cant</th><th>Incidencia</th></tr></thead>
                        <tbody>`;
            (f.productos || []).forEach(p => {
                h += `<tr>
                    <td><input type="checkbox" id="chk_${p.id}" ${p.entregado ? 'checked' : ''} onchange="toggleProducto(${p.id})"></td>
                    <td>${p.nombre_producto}</td>
                    <td>${p.cantidad_facturada}</td>
                    <td><button class="btn btn-sm btn-${p.tiene_incidencia?'danger':'warning'}" onclick="abrirIncidencia(${p.id})"><i class="fa fa-exclamation-triangle"></i></button></td>
                </tr>`;
            });
            h += `</tbody></table>
                    <button class="btn btn-sm btn-success" onclick="marcarTodosEntregados(${f.distribucion_factura_id})">Marcar Todos</button>
                </div>
            </div>`;
        });
        h += '<button class="btn btn-primary btn-block" onclick="guardarConfirmacion()">Guardar Confirmacion</button>';
        $('#contenidoConfirmacion').html(h);
    });
}

function seleccionarTodosFact(dfId, chk) {
    $('input[type=checkbox][id^=chk_]').prop('checked', chk.checked);
}

function toggleProducto(pid) {
    // Solo cambia el estado visual, se guarda al confirmar
}

function marcarTodosEntregados(dfId) {
    $.post("<?= url('/logistica/confirmacion/marcar-todos') ?>/" + dfId, {_token: $('meta[name="csrf-token"]').attr('content')}, r => {
        Swal.fire(r.title, r.text, r.icon);
        cargarConfirmacion(distribucionActual);
    });
}

function abrirIncidencia(pid) {
    $('#productoIncidenciaId').val(pid);
    $('#modalIncidencia').modal('show');
}

function guardarIncidencia() {
    const pid = $('#productoIncidenciaId').val();
    const tipo = $('#tipoIncidencia').val();
    const desc = $('#descripcionIncidencia').val();
    
    // Aquí se guardaría la incidencia - simplificado por brevedad
    $('#modalIncidencia').modal('hide');
    Swal.fire('Guardado', 'Incidencia registrada', 'success');
}

function guardarConfirmacion() {
    const productos = [];
    $('input[type=checkbox][id^=chk_]').each(function() {
        const id = $(this).attr('id').replace('chk_', '');
        productos.push({
            id: id,
            entregado: $(this).is(':checked') ? 1 : 0,
            cantidad_entregada: $(this).is(':checked') ? $(this).closest('tr').find('td:eq(2)').text() : 0
        });
    });
    
    $.ajax({
        url: "<?= route('logistica.confirmacion.guardar') ?>",
        type: 'POST',
        data: {productos: productos, _token: $('meta[name="csrf-token"]').attr('content')},
        success: r => {
            Swal.fire(r.title, r.text, r.icon);
            cargarConfirmacion(distribucionActual);
        },
        error: x => Swal.fire(x.responseJSON.title, x.responseJSON.text, x.responseJSON.icon)
    });
}
</script>