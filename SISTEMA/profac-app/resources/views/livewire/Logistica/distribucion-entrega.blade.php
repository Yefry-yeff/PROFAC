<div>
    <style>
        /* Estilos para scroll en tabla de detalle */
        #bodyDetalleDistribucion .table-responsive::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        #bodyDetalleDistribucion .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        #bodyDetalleDistribucion .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        #bodyDetalleDistribucion .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Animación para botones */
        .btn-group .btn {
            transition: all 0.2s ease;
        }

        .btn-group .btn:hover {
            transform: scale(1.1);
        }

        /* Asegurar que SweetAlert aparezca sobre modales */
        .swal2-container {
            z-index: 10000 !important;
        }

        .swal2-popup {
            z-index: 10001 !important;
        }
    </style>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Distribuciones de Entrega</h3>
                    <div class="card-tools">
                        <a href="{{ route('logistica.distribuciones.nueva') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Nueva Distribucion
                        </a>
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

    <!-- Modal Mejorado: Nueva Distribución -->
    <div class="modal fade" id="modalNuevaDistribucion" data-backdrop="static">
        <div class="modal-dialog modal-xl" style="max-width: 95%;">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-truck-loading"></i> Nueva Distribución de Entrega
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" style="background: #f4f6f9;">
                    <form id="formNuevaDistribucion">
                        
                        <!-- Información Básica -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle text-primary"></i> Información de la Distribución</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label><i class="fas fa-users"></i> Equipo de Entrega *</label>
                                            <select class="form-control form-control-lg" name="equipo_entrega_id" required>
                                                <option value="">-- Seleccione un equipo --</option>
                                                @foreach($equipos as $eq)
                                                    <option value="{{ $eq->id }}">{{ $eq->nombre_equipo }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><i class="fas fa-calendar-alt"></i> Fecha Programada *</label>
                                            <input type="date" class="form-control form-control-lg" name="fecha_programada" 
                                                   value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><i class="fas fa-box"></i> Facturas</label>
                                            <input type="text" class="form-control form-control-lg bg-light" 
                                                   id="contadorFacturas" value="0 facturas" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group mb-0">
                                            <label><i class="fas fa-sticky-note"></i> Observaciones</label>
                                            <textarea class="form-control" name="observaciones" rows="2" 
                                                      placeholder="Ingrese observaciones adicionales..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Búsqueda de Facturas -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-search text-success"></i> Búsqueda de Facturas</h6>
                            </div>
                            <div class="card-body">
                                
                                <!-- Búsqueda por Factura -->
                                <div class="input-group input-group-lg mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-file-invoice"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" id="buscarFacturaNumero" 
                                           placeholder="Escriba el número de factura..." 
                                           autocomplete="off">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="limpiarBusquedaFactura()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Resultados de búsqueda de facturas -->
                                <div id="resultadosFacturas" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> 
                                        <span id="mensajeResultadosFacturas">Ingrese al menos 2 caracteres para buscar</span>
                                    </div>
                                    <div id="listaResultadosFacturas" class="row"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview de Facturas Seleccionadas -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-gradient-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-clipboard-list"></i> 
                                    Facturas para Distribuir 
                                    <span class="badge badge-light ml-2" id="totalFacturasSeleccionadas">0</span>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div id="previewFacturasSeleccionadas" class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th width="100">#Factura</th>
                                                <th>Cliente</th>
                                                <th>Dirección</th>
                                                <th width="120" class="text-right">Total</th>
                                                <th width="80" class="text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaPreviewFacturas">
                                            <tr id="mensajeVacioPreview">
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    <i class="fas fa-inbox fa-3x mb-2"></i>
                                                    <p>No hay facturas seleccionadas</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Total a Distribuir:</strong>
                                    </div>
                                    <div class="col-6 text-right">
                                        <h5 class="mb-0 text-success">
                                            <i class="fas fa-dollar-sign"></i> 
                                            <span id="totalMontoDistribucion">0.00</span>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-success btn-lg" onclick="guardarDistribucion()">
                        <i class="fas fa-save"></i> Guardar Distribución
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Detalle de Distribución -->
    <div class="modal fade" id="modalDetalleDistribucion" data-backdrop="static">
        <div class="modal-dialog modal-xl" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info">
                    <h5 class="modal-title text-white" id="tituloDetalleDistribucion">
                        <i class="fas fa-list"></i> Detalle de Distribución
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="bodyDetalleDistribucion">
                    <!-- Contenido dinámico -->
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Ver Incidencias -->
    <div class="modal fade" id="modalIncidencias">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-warning">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-exclamation-triangle"></i> Incidencias de la Factura
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="bodyIncidencias">
                    <!-- Contenido dinámico -->
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
        order: [[1, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        deferRender: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        drawCallback: function() {
            // Reinicializar tooltips después de cada recarga
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    // Prevenir warning de aria-hidden en modal de incidencias
    $('#modalIncidencias').on('hide.bs.modal', function (e) {
        // Quitar foco del botón antes de cerrar
        $(document.activeElement).blur();
        $(this).removeAttr('aria-hidden');
    }).on('hidden.bs.modal', function (e) {
        // Verificar si el modal padre está abierto
        const modalPadre = $('#modalDetalleDistribucion');
        if (modalPadre.hasClass('show')) {
            // Hay modal padre abierto, mantener su backdrop
            if ($('.modal-backdrop').length > 1) {
                $('.modal-backdrop').last().remove();
            }
            // Asegurar que body mantenga modal-open y padding
            $('body').addClass('modal-open');
            // Re-enfocar el modal padre
            modalPadre.focus();
        } else {
            // No hay modal padre, limpiar todo
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        }
    });
});

// ========== MODAL Y BÚSQUEDA ==========

function abrirModalNuevaDistribucion() {
    $('#formNuevaDistribucion')[0].reset();
    facturasSelTmp = [];
    actualizarPreviewFacturas();
    limpiarBusquedaFactura();
    $('input[name="fecha_programada"]').val('{{ date("Y-m-d") }}');
    $('#modalNuevaDistribucion').modal('show');
    setTimeout(() => {
        $('#tab-factura').tab('show');
        $('#buscarFacturaNumero').focus();
    }, 500);
}

// Limpiar búsquedas
function limpiarBusquedaFactura() {
    $('#buscarFacturaNumero').val('');
    $('#resultadosFacturas').hide();
    $('#listaResultadosFacturas').html('');
}

// ========== BÚSQUEDA POR FACTURA ==========

let timerBusquedaFactura;
$('#buscarFacturaNumero').on('keyup', function() {
    clearTimeout(timerBusquedaFactura);
    const termino = $(this).val().trim();
    
    if (termino.length < 2) {
        $('#resultadosFacturas').hide();
        return;
    }
    
    $('#resultadosFacturas').show();
    $('#mensajeResultadosFacturas').html('<i class="fas fa-spinner fa-spin"></i> Buscando...');
    
    timerBusquedaFactura = setTimeout(() => {
        $.ajax({
            url: "{{ url('/logistica/facturas/autocompletado') }}",
            type: 'GET',
            data: {termino: termino},
            success: function(response) {
                if (response.success && response.facturas.length > 0) {
                    $('#mensajeResultadosFacturas').text(`${response.facturas.length} factura(s) encontrada(s)`);
                    mostrarResultadosFacturas(response.facturas);
                } else {
                    $('#mensajeResultadosFacturas').html('<i class="fas fa-search"></i> No se encontraron facturas');
                    $('#listaResultadosFacturas').html('');
                }
            },
            error: function() {
                $('#mensajeResultadosFacturas').html('<i class="fas fa-exclamation-triangle"></i> Error al buscar');
                $('#listaResultadosFacturas').html('');
            }
        });
    }, 400);
});

function mostrarResultadosFacturas(facturas) {
    let html = '';
    facturas.forEach(f => {
        const yaAgregada = facturasSelTmp.find(fs => fs.id === f.id);
        const disabled = yaAgregada ? 'disabled' : '';
        const btnClass = yaAgregada ? 'btn-secondary' : 'btn-success';
        const btnText = yaAgregada ? '<i class="fas fa-check"></i> Agregada' : '<i class="fas fa-plus"></i> Agregar';
        
        html += `
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 shadow-sm ${yaAgregada ? 'border-success' : ''}">
                <div class="card-body p-3">
                    <h6 class="card-title text-primary mb-2">
                        <i class="fas fa-file-invoice"></i> #${f.numero_factura}
                    </h6>
                    <p class="card-text mb-2">
                        <small class="text-muted"><i class="fas fa-user"></i> ${f.cliente}</small>
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h6 mb-0 text-success">Q${parseFloat(f.total).toFixed(2)}</span>
                        <button class="btn btn-sm ${btnClass}" ${disabled}
                                onclick="agregarFactura(${f.id}, '${f.numero_factura}', '${f.cliente.replace(/'/g, "\\'")}', '${f.direccion || ''}', ${f.total})">
                            ${btnText}
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    });
    $('#listaResultadosFacturas').html(html);
}

function agregarFactura(id, numero, cliente, direccion, total) {
    if (facturasSelTmp.find(f => f.id === id)) {
        return;
    }
    
    facturasSelTmp.push({
        id: id,
        numero: numero,
        cliente: cliente,
        direccion: direccion,
        total: parseFloat(total)
    });
    
    actualizarPreviewFacturas();
    
    // Actualizar resultados
    $('#buscarFacturaNumero').trigger('keyup');
    
    toastr.success(`Factura #${numero} agregada`, 'Éxito');
}

function actualizarPreviewFacturas() {
    const total = facturasSelTmp.length;
    $('#contadorFacturas').val(`${total} factura${total !== 1 ? 's' : ''}`);
    $('#totalFacturasSeleccionadas').text(total);
    
    if (total === 0) {
        $('#mensajeVacioPreview').show();
        $('#tablaPreviewFacturas tr:not(#mensajeVacioPreview)').remove();
        $('#totalMontoDistribucion').text('0.00');
        return;
    }
    
    $('#mensajeVacioPreview').hide();
    $('#tablaPreviewFacturas tr:not(#mensajeVacioPreview)').remove();
    
    let montoTotal = 0;
    facturasSelTmp.forEach((f, index) => {
        montoTotal += parseFloat(f.total);
        const row = `
        <tr>
            <td><strong>#${f.numero}</strong></td>
            <td>${f.cliente}</td>
            <td><small>${f.direccion || 'Sin dirección'}</small></td>
            <td class="text-right"><strong>Q${parseFloat(f.total).toFixed(2)}</strong></td>
            <td class="text-center">
                <button class="btn btn-xs btn-danger" onclick="removerFactura(${index})" title="Quitar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
        $('#tablaPreviewFacturas').append(row);
    });
    
    $('#totalMontoDistribucion').text(montoTotal.toFixed(2));
}

function removerFactura(index) {
    const factura = facturasSelTmp[index];
    facturasSelTmp.splice(index, 1);
    actualizarPreviewFacturas();
    
    // Actualizar resultados si están visibles
    if ($('#resultadosFacturas').is(':visible')) {
        $('#buscarFacturaNumero').trigger('keyup');
    }
    
    toastr.info(`Factura #${factura.numero} eliminada`);
}

// ========== GUARDAR DISTRIBUCIÓN ==========

function guardarDistribucion() {
    if (!facturasSelTmp.length) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin facturas',
            text: 'Debe agregar al menos una factura',
            confirmButtonColor: '#28a745'
        });
        return;
    }
    
    const fd = new FormData($('#formNuevaDistribucion')[0]);
    fd.append('facturas', JSON.stringify(facturasSelTmp.map(f => f.id)));
    
    Swal.fire({
        title: 'Guardando...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: "{{ route('logistica.distribuciones.guardar') }}",
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function(r) {
            Swal.fire({
                icon: r.icon,
                title: r.title,
                text: r.text,
                confirmButtonColor: '#28a745'
            });
            $('#modalNuevaDistribucion').modal('hide');
            tablaDistribuciones.ajax.reload();
        },
        error: function(x) {
            Swal.fire({
                icon: x.responseJSON?.icon || 'error',
                title: x.responseJSON?.title || 'Error',
                text: x.responseJSON?.text || 'Error al guardar la distribución',
                confirmButtonColor: '#dc3545'
            });
        }
    });
}

// ========== FUNCIONES DE DISTRIBUCIÓN ==========

function verFacturas(id) {
    $('#modalDetalleDistribucion').data('distribucion-id', id).modal('show');
    $('#tituloDetalleDistribucion').html('<i class="fas fa-spinner fa-spin"></i> Cargando...');
    $('#bodyDetalleDistribucion').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary"></i><p class="mt-3">Cargando facturas...</p></div>');
    
    $.get("{{ url('/logistica/distribuciones/facturas') }}/" + id, function(r) {
        const distribucion = r.distribucion || {};
        $('#tituloDetalleDistribucion').html(`<i class="fas fa-truck"></i> ${distribucion.nombre_equipo} - ${distribucion.fecha_programada}`);
        
        let html = `
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-info"><i class="fas fa-file-invoice"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Facturas</span>
                                <span class="info-box-number">${r.facturas.length}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Entregadas</span>
                                <span class="info-box-number">${r.facturas.filter(f => f.estado_entrega === 'entregado').length}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pendientes</span>
                                <span class="info-box-number">${r.facturas.filter(f => f.estado_entrega === 'sin_entrega').length}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                <table class="table table-sm table-hover" id="tablaFacturasDetalle">
                    <thead class="thead-light" style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th width="50">#</th>
                            <th>Factura</th>
                            <th>Cliente</th>
                            <th width="100">Estado</th>
                            <th width="200">Opciones</th>
                        </tr>
                    </thead>
                    <tbody>`;
        
        if (r.facturas.length === 0) {
            html += '<tr><td colspan="5" class="text-center py-4 text-muted">No hay facturas asignadas</td></tr>';
        } else {
            // Verificar si la distribución está completada o cancelada
            const soloLectura = distribucion.estado_id === 3 || distribucion.estado_id === 4;
            
            r.facturas.forEach((f, index) => {
                const estadoBadge = f.estado_entrega === 'entregado' ? 'success' : 
                                   f.estado_entrega === 'parcial' ? 'warning' : 'secondary';
                const estadoTexto = f.estado_entrega === 'sin_entrega' ? 'Sin Entrega' : 
                                   f.estado_entrega.charAt(0).toUpperCase() + f.estado_entrega.slice(1);
                const bloqueado = f.confirmada == 1;
                
                html += `<tr>
                    <td>${f.orden_entrega}</td>
                    <td><strong>#${f.numero_factura}</strong></td>
                    <td>${f.cliente}</td>
                    <td><span class="badge badge-${estadoBadge}">${estadoTexto}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            ${!soloLectura && bloqueado ? `<button class="btn btn-warning" onclick="desbloquearFactura(${f.id})" title="Desbloquear">
                                <i class="fas fa-unlock"></i>
                            </button>` : ''}
                            ${!soloLectura && f.estado_entrega !== 'sin_entrega' && !bloqueado ? `<button class="btn btn-danger" onclick="anularEntrega(${f.id})" title="Anular Entrega">
                                <i class="fas fa-times"></i>
                            </button>` : ''}
                            <button class="btn btn-info" onclick="verIncidencias(${f.id})" title="Ver Incidencias">
                                <i class="fas fa-exclamation-circle"></i>
                            </button>
                            ${!soloLectura && f.estado_entrega !== 'entregado' && !bloqueado ? `<button class="btn btn-success" onclick="confirmarEntregaFactura(${f.id})" title="Confirmar Entrega">
                                <i class="fas fa-check"></i>
                            </button>` : ''}
                        </div>
                    </td>
                </tr>`;
            });
        }
        
        html += '</tbody></table></div>';
        $('#bodyDetalleDistribucion').html(html);
    }).fail(function() {
        $('#bodyDetalleDistribucion').html('<div class="alert alert-danger">Error al cargar las facturas</div>');
    });
}

function iniciarDistribucion(id) {
    Swal.fire({title: 'Iniciar?', icon: 'question', showCancelButton: true, confirmButtonColor: '#28a745'}).then(r => {
        if (r.isConfirmed) {
            $.post("{{ url('/logistica/distribuciones/iniciar') }}/" + id, {_token: $('meta[name="csrf-token"]').attr('content')}, r => {
                Swal.fire(r.title, r.text, r.icon);
                tablaDistribuciones.ajax.reload();
            }).fail(x => Swal.fire(x.responseJSON.title, x.responseJSON.text, x.responseJSON.icon));
        }
    });
}

function cancelarDistribucion(id) {
    Swal.fire({title: 'Cancelar?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545'}).then(r => {
        if (r.isConfirmed) {
            $.post("{{ url('/logistica/distribuciones/cancelar') }}/" + id, {_token: $('meta[name="csrf-token"]').attr('content')}, r => {
                Swal.fire(r.title, r.text, r.icon);
                tablaDistribuciones.ajax.reload();
            }).fail(x => Swal.fire(x.responseJSON.title, x.responseJSON.text, x.responseJSON.icon));
        }
    });
}

function abrirConfirmacion(id) {
    Swal.fire({
        title: '¿Completar distribución?',
        text: 'Esto cambiará el estado de la distribución a "Completada".',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, completar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('/logistica/distribuciones/completar') }}/" + id,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(r) {
                    Swal.fire({
                        icon: r.icon || 'success',
                        title: r.title || 'Completada',
                        text: r.text || 'La distribución ha sido completada correctamente',
                        confirmButtonColor: '#28a745'
                    });
                    tablaDistribuciones.ajax.reload(null, false);
                },
                error: function(x) {
                    Swal.fire({
                        icon: 'error',
                        title: x.responseJSON?.title || 'Error',
                        text: x.responseJSON?.text || 'No se pudo completar la distribución',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }
    });
}

// ========== FUNCIONES DE GESTIÓN DE FACTURAS ==========

function desbloquearFactura(facturaId) {
    Swal.fire({
        title: '¿Desbloquear factura?',
        text: 'Esto eliminará el estado de confirmación y permitirá modificar la factura.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f39c12',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, desbloquear',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('/logistica/facturas/desbloquear') }}/" + facturaId,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(r) {
                    Swal.fire({
                        icon: r.icon || 'success',
                        title: r.title || 'Desbloqueada',
                        text: r.text || 'La factura ha sido desbloqueada correctamente',
                        confirmButtonColor: '#28a745'
                    });
                    // Recargar el modal de detalle
                    const distribucionId = $('#modalDetalleDistribucion').data('distribucion-id');
                    if (distribucionId) {
                        verFacturas(distribucionId);
                    }
                },
                error: function(x) {
                    Swal.fire({
                        icon: 'error',
                        title: x.responseJSON?.title || 'Error',
                        text: x.responseJSON?.text || 'No se pudo desbloquear la factura',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }
    });
}

function anularEntrega(facturaId) {
    // Guardar ID de distribución antes de cerrar el modal
    const distribucionId = $('#modalDetalleDistribucion').data('distribucion-id');
    
    // Cerrar temporalmente el modal de detalle para que SweetAlert aparezca correctamente
    $('#modalDetalleDistribucion').modal('hide');
    
    setTimeout(() => {
        Swal.fire({
            title: '¿Anular entrega?',
            text: 'Esto cambiará el estado de la factura a "Sin Entrega".',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('/logistica/facturas/anular-entrega') }}/" + facturaId,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(r) {
                    Swal.fire({
                        icon: r.icon || 'success',
                        title: r.title || 'Anulada',
                        text: r.text || 'La entrega ha sido anulada correctamente',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        // Reabrir el modal de detalle
                        if (distribucionId) {
                            verFacturas(distribucionId);
                        }
                    });
                },
                error: function(x) {
                    Swal.fire({
                        icon: 'error',
                        title: x.responseJSON?.title || 'Error',
                        text: x.responseJSON?.text || 'No se pudo anular la entrega',
                        confirmButtonColor: '#dc3545'
                    }).finally(() => {
                        // Reabrir modal si hay error
                        if (distribucionId) {
                            verFacturas(distribucionId);
                        }
                    });
                }
            });
            } else {
                // Si cancela, reabrir el modal
                if (distribucionId) {
                    $('#modalDetalleDistribucion').modal('show');
                }
            }
        });
    }, 300);
}

function verIncidencias(facturaId) {
    console.log('Cargando incidencias para factura ID:', facturaId);
    $('#modalIncidencias').modal('show');
    $('#bodyIncidencias').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-3">Cargando incidencias...</p></div>');
    
    const url = "{{ url('/logistica/facturas/incidencias') }}/" + facturaId;
    console.log('URL de incidencias:', url);
    
    $.ajax({
        url: url,
        type: 'GET',
        success: function(r) {
            console.log('Respuesta de incidencias:', r);
            let html = '';
            
            if (!r.incidencias || r.incidencias.length === 0) {
                html = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Esta factura no tiene incidencias registradas.</div>';
            } else {
                html = `<div class="mb-3">
                    <h6>Factura: <strong>#${r.factura?.numero_factura || 'N/A'}</strong></h6>
                    <p class="text-muted mb-0">Cliente: ${r.factura?.cliente || 'N/A'}</p>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th width="150">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>`;
                
                r.incidencias.forEach((inc, index) => {
                    html += `<tr>
                        <td>${index + 1}</td>
                        <td><strong>#${inc.producto_id || 'N/A'}</strong> - ${inc.producto_nombre || 'N/A'}</td>
                        <td><span class="badge badge-warning">${inc.tipo || 'N/A'}</span></td>
                        <td>${inc.descripcion || 'Sin descripción'}</td>
                        <td>${inc.created_at ? new Date(inc.created_at).toLocaleString('es-HN') : 'N/A'}</td>
                    </tr>`;
                });
                
                html += `</tbody></table></div>
                <div class="alert alert-light mt-3">
                    <strong>Total de incidencias:</strong> ${r.incidencias.length}
                </div>`;
            }
            
            $('#bodyIncidencias').html(html);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar incidencias:');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
            console.error('Status Code:', xhr.status);
            
            let errorMsg = 'Error al cargar las incidencias';
            if (xhr.status === 404) {
                errorMsg = 'No se encontró la ruta para cargar incidencias (Error 404)';
            } else if (xhr.status === 500) {
                errorMsg = 'Error interno del servidor (Error 500)';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            
            $('#bodyIncidencias').html(`<div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> ${errorMsg}
                <br><small class="text-muted">Código: ${xhr.status} | Ver consola para más detalles</small>
            </div>`);
        }
    });
}

function confirmarEntregaFactura(facturaId) {
    Swal.fire({
        title: '¿Confirmar entrega completa?',
        text: 'Esto cambiará el estado de la factura a "Entregado".',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('/logistica/facturas/confirmar-entrega') }}/" + facturaId,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(r) {
                    Swal.fire({
                        icon: r.icon || 'success',
                        title: r.title || 'Confirmada',
                        text: r.text || 'La entrega ha sido confirmada como completa',
                        confirmButtonColor: '#28a745'
                    });
                    // Recargar el modal de detalle
                    const distribucionId = $('#modalDetalleDistribucion').data('distribucion-id');
                    if (distribucionId) {
                        verFacturas(distribucionId);
                    }
                    // Recargar la tabla principal
                    tablaDistribuciones.ajax.reload(null, false);
                },
                error: function(x) {
                    Swal.fire({
                        icon: 'error',
                        title: x.responseJSON?.title || 'Error',
                        text: x.responseJSON?.text || 'No se pudo confirmar la entrega',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }
    });
}
</script>
@endpush