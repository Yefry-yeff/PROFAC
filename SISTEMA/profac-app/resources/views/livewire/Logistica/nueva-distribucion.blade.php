<x-app-layout>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-truck-loading text-primary"></i> 
                                Nueva Distribución de Entrega
                            </h4>
                            <small class="text-muted">Complete la información y seleccione las facturas a distribuir</small>
                        </div>
                        <a href="{{ route('logistica.distribuciones') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="formNuevaDistribucion">
        <div class="row">
            <!-- Columna Izquierda: Información y Búsqueda -->
            <div class="col-lg-8">
                
                <!-- Información Básica -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0"><i class="fas fa-info-circle text-primary"></i> Información de la Distribución</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-alt"></i> Fecha Programada *</label>
                                    <input type="date" class="form-control form-control-lg" name="fecha_programada" 
                                           value="{{ date('Y-m-d') }}" required>
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
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0"><i class="fas fa-search text-success"></i> Búsqueda de Facturas</h6>
                    </div>
                    <div class="card-body">
                        
                        <!-- Tabs de búsqueda -->
                        <ul class="nav nav-pills nav-fill mb-3" id="tipoBusquedaTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-factura" data-toggle="pill" href="#busqueda-factura" role="tab">
                                    <i class="fas fa-file-invoice"></i> Buscar por Número de Factura
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-cliente" data-toggle="pill" href="#busqueda-cliente" role="tab">
                                    <i class="fas fa-user"></i> Buscar por Cliente
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content" id="tipoBusquedaContent">
                            
                            <!-- Búsqueda por Factura -->
                            <div class="tab-pane fade show active" id="busqueda-factura" role="tabpanel">
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
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle"></i> 
                                        <span id="mensajeResultadosFacturas">Ingrese al menos 2 caracteres para buscar</span>
                                    </div>
                                    <div id="listaResultadosFacturas" class="row"></div>
                                </div>
                            </div>

                            <!-- Búsqueda por Cliente -->
                            <div class="tab-pane fade" id="busqueda-cliente" role="tabpanel">
                                <div class="input-group input-group-lg mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-info text-white">
                                            <i class="fas fa-user"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" id="buscarClienteNombre" 
                                           placeholder="Escriba el nombre del cliente..." 
                                           autocomplete="off">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="limpiarBusquedaCliente()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Resultados de búsqueda de clientes -->
                                <div id="resultadosClientes" style="display: none;">
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle"></i> 
                                        <span id="mensajeResultadosClientes">Ingrese al menos 3 caracteres para buscar</span>
                                    </div>
                                    <div id="listaResultadosClientes" class="list-group mb-3"></div>
                                </div>

                                <!-- Facturas del cliente seleccionado -->
                                <div id="facturasClienteSeleccionado" style="display: none;">
                                    <div class="alert alert-success">
                                        <i class="fas fa-user-check"></i> 
                                        <strong>Cliente:</strong> <span id="nombreClienteSeleccionado"></span>
                                        <button type="button" class="close" onclick="limpiarClienteSeleccionado()">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <div id="listaFacturasCliente" class="row"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Columna Derecha: Preview de Facturas -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-gradient-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-truck-loading"></i> 
                            Facturas para Distribuir
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div id="previewFacturasSeleccionadas" class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th>#Factura</th>
                                        <th>Cliente</th>
                                        <th class="text-center">Productos</th>
                                        <th class="text-center" width="50">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaPreviewFacturas">
                                    <tr id="mensajeVacioPreview">
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            <p class="mb-0">No hay facturas seleccionadas</p>
                                            <small>Busque y agregue facturas</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="mb-3 p-3 bg-white border rounded text-center">
                            <h6 class="mb-2 text-muted">Total para Distribuir</h6>
                            <div class="d-flex justify-content-around">
                                <div>
                                    <i class="fas fa-file-invoice text-primary"></i>
                                    <strong id="totalFacturasSeleccionadas" class="h4 text-primary d-block">0</strong>
                                    <small class="text-muted">Facturas</small>
                                </div>
                                <div class="border-left"></div>
                                <div>
                                    <i class="fas fa-box text-info"></i>
                                    <strong id="totalProductosDistribuir" class="h4 text-info d-block">0</strong>
                                    <small class="text-muted">Productos</small>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success btn-block btn-lg" onclick="guardarDistribucion()">
                            <i class="fas fa-save"></i> Guardar Distribución
                        </button>
                        <a href="{{ route('logistica.distribuciones') }}" class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Detalle de Factura -->
<div class="modal fade" id="modalDetalleFactura" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice"></i> Detalle de Factura
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Datos de la factura -->
                <div class="mb-3">
                    <p class="mb-1"><strong>Factura:</strong> <span id="detalleNumeroFactura"></span></p>
                    <p class="mb-1"><strong>Fecha:</strong> <span id="detalleFechaFactura"></span></p>
                    <p class="mb-0"><strong>Cliente:</strong> <span id="detalleCliente"></span></p>
                </div>
                
                <hr>
                
                <!-- Tabla de productos -->
                <h6 class="mb-3"><i class="fas fa-box"></i> Productos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th width="80px">Código</th>
                                <th>Producto</th>
                                <th class="text-center" width="100px">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody id="detalleProductosTabla">
                            <tr>
                                <td colspan="3" class="text-center">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.sticky-top {
    z-index: 1020;
}

.nav-pills .nav-link {
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.nav-pills .nav-link:hover {
    background-color: #e9ecef;
}

.nav-pills .nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card {
    border-radius: 0.5rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.list-group-item {
    transition: all 0.2s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn {
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.badge {
    border-radius: 0.25rem;
    padding: 0.35em 0.65em;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%) !important;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    animation: fadeIn 0.3s ease;
}
</style>

<script>
// Variables y funciones globales (accesibles desde onclick)
let facturasSelTmp = [];
let clienteSeleccionado = null;

// ========== BÚSQUEDA Y LIMPIEZA ==========

function limpiarBusquedaFactura() {
    $('#buscarFacturaNumero').val('');
    $('#resultadosFacturas').hide();
    $('#listaResultadosFacturas').html('');
}

function limpiarBusquedaCliente() {
    $('#buscarClienteNombre').val('');
    $('#resultadosClientes').hide();
    $('#listaResultadosClientes').html('');
    limpiarClienteSeleccionado();
}

function limpiarClienteSeleccionado() {
    $('#facturasClienteSeleccionado').hide();
    $('#listaFacturasCliente').html('');
    $('#nombreClienteSeleccionado').text('');
}

function seleccionarTodasFacturas(checked) {
    $('.check-factura:not(:disabled)').prop('checked', checked);
}

function toggleSeleccionarTodas() {
    const todasMarcadas = $('.check-factura:not(:disabled):checked').length === $('.check-factura:not(:disabled)').length;
    seleccionarTodasFacturas(!todasMarcadas);
    $('#checkTodasFacturas').prop('checked', !todasMarcadas);
}

function agregarFacturasSeleccionadas() {
    const seleccionadas = [];
    $('.check-factura:checked').each(function() {
        const id = parseInt($(this).data('id'));
        const numero = $(this).data('numero');
        const total = parseFloat($(this).data('total'));
        const cantidadProductos = parseInt($(this).data('productos')) || 0;
        
        if (!facturasSelTmp.find(f => f.id === id)) {
            seleccionadas.push({id, numero, total, cantidadProductos});
        }
    });
    
    if (seleccionadas.length === 0) {
        toastr.warning('No hay facturas seleccionadas', 'Atención', {
            positionClass: 'toast-top-right',
            timeOut: 2000
        });
        return;
    }
    
    seleccionadas.forEach(f => {
        agregarFactura(f.id, f.numero, clienteSeleccionado.nombre, '', f.total, f.cantidadProductos);
    });
    
    // Recargar lista de facturas del cliente para actualizar estados
    if (clienteSeleccionado) {
        seleccionarCliente(clienteSeleccionado.id, clienteSeleccionado.nombre, 0);
    }
    
    toastr.success(`${seleccionadas.length} factura(s) agregada(s)`, 'Éxito', {
        positionClass: 'toast-top-right',
        timeOut: 2000
    });
}

function seleccionarTodasFacturasBusqueda(checked) {
    $('.check-factura-busqueda:not(:disabled)').prop('checked', checked);
}

function toggleSeleccionarTodasBusqueda() {
    const todasMarcadas = $('.check-factura-busqueda:not(:disabled):checked').length === $('.check-factura-busqueda:not(:disabled)').length;
    seleccionarTodasFacturasBusqueda(!todasMarcadas);
    $('#checkTodasFacturasBusqueda').prop('checked', !todasMarcadas);
}

function agregarFacturasSeleccionadasBusqueda() {
    const seleccionadas = [];
    $('.check-factura-busqueda:checked').each(function() {
        const id = parseInt($(this).data('id'));
        const numero = $(this).data('numero');
        const cliente = $(this).data('cliente');
        const total = parseFloat($(this).data('total'));
        const cantidadProductos = parseInt($(this).data('productos')) || 0;
        
        if (!facturasSelTmp.find(f => f.id === id)) {
            seleccionadas.push({id, numero, cliente, total, cantidadProductos});
        }
    });
    
    if (seleccionadas.length === 0) {
        toastr.warning('No hay facturas seleccionadas', 'Atención', {
            positionClass: 'toast-top-right',
            timeOut: 2000
        });
        return;
    }
    
    seleccionadas.forEach(f => {
        agregarFactura(f.id, f.numero, f.cliente, '', f.total, f.cantidadProductos);
    });
    
    toastr.success(`${seleccionadas.length} factura(s) agregada(s)`, 'Éxito', {
        positionClass: 'toast-top-right',
        timeOut: 2000
    });
    
    // Deshabilitar los checkboxes agregados
    $('.check-factura-busqueda:checked').prop('disabled', true).prop('checked', true);
    $('.check-factura-busqueda:checked').closest('tr').addClass('table-success');
    $('#checkTodasFacturasBusqueda').prop('checked', false);
}

function verDetalleFactura(facturaId) {
    $('#modalDetalleFactura').modal('show');
    $('#detalleProductosTabla').html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
    
    $.get(`/logistica/facturas/detalle?factura_id=${facturaId}`, function(data) {
        if (data.success) {
            const f = data.factura;
            const formatoMoneda = new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL' });
            
            // Datos de la factura
            $('#detalleNumeroFactura').text('#' + f.numero_factura);
            $('#detalleFechaFactura').text(f.fecha_factura);
            $('#detalleCliente').text(f.cliente);
            
            // Productos
            let htmlProductos = '';
            data.productos.forEach(p => {
                htmlProductos += `
                <tr>
                    <td class="text-center"><small>${p.codigo}</small></td>
                    <td>${p.producto}</td>
                    <td class="text-center"><strong>${parseInt(p.cantidad)}</strong></td>
                </tr>`;
            });
            
            $('#detalleProductosTabla').html(htmlProductos || '<tr><td colspan="3" class="text-center text-muted">No hay productos</td></tr>');
        } else {
            toastr.error('Error al cargar detalle de factura', 'Error');
            $('#modalDetalleFactura').modal('hide');
        }
    }).fail(function() {
        toastr.error('Error al cargar detalle de factura', 'Error');
        $('#modalDetalleFactura').modal('hide');
    });
}

function agregarFactura(id, numero, cliente, direccion, total, cantidadProductos) {
    if (facturasSelTmp.find(f => f.id === id)) {
        return;
    }
    
    facturasSelTmp.push({
        id: id,
        numero: numero,
        cliente: cliente,
        direccion: direccion,
        total: parseFloat(total),
        cantidadProductos: cantidadProductos || 0
    });
    
    actualizarPreviewFacturas();
    
    // Actualizar resultados
    if ($('#buscarFacturaNumero').length) {
        $('#buscarFacturaNumero').trigger('keyup');
    }
    
    toastr.success(`Factura #${numero} agregada`, 'Éxito', {
        positionClass: 'toast-top-right',
        timeOut: 2000
    });
}

function seleccionarCliente(clienteId, nombreCliente, facturas) {
    clienteSeleccionado = {id: clienteId, nombre: nombreCliente};
    
    $('#resultadosClientes').hide();
    $('#buscarClienteNombre').val(nombreCliente);
    $('#nombreClienteSeleccionado').text(nombreCliente);
    $('#facturasClienteSeleccionado').show();
    
    // Cargar facturas del cliente
    $.get(`/logistica/facturas/por-cliente-id?cliente_id=${clienteId}`, function(data) {
        const facturas = data.facturas || [];
        if (!facturas.length) {
            $('#listaFacturasCliente').html(`
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i> 
                    Este cliente no tiene facturas disponibles
                </div>
            `);
            return;
        }
        
        let html = `
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-success btn-sm" onclick="agregarFacturasSeleccionadas()">
                <i class="fas fa-plus-circle"></i> Agregar Seleccionadas
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleSeleccionarTodas()">
                <i class="fas fa-check-square"></i> Seleccionar Todas
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th width="40px" class="text-center">
                            <input type="checkbox" id="checkTodasFacturas" onchange="seleccionarTodasFacturas(this.checked)">
                        </th>
                        <th>Factura</th>
                        <th>Fecha</th>
                        <th width="100px" class="text-center">Productos</th>
                        <th width="80px" class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>`;
        
        facturas.forEach(f => {
            const yaAgregada = facturasSelTmp.find(fs => fs.id === f.id);
            const checkDisabled = yaAgregada ? 'disabled' : '';
            const rowClass = yaAgregada ? 'table-success' : '';
            const badge = yaAgregada ? '<span class="badge badge-success"><i class="fas fa-check"></i> Agregada</span>' : '<span class="badge badge-light">Disponible</span>';
            
            html += `
                <tr class="${rowClass}">
                    <td class="text-center">
                        <input type="checkbox" class="check-factura" ${checkDisabled} 
                               data-id="${f.id}" 
                               data-numero="${f.numero_factura}" 
                               data-total="${f.total}"
                               data-productos="${f.cantidad_productos || 0}">
                    </td>
                    <td>
                        <strong>#${f.numero_factura}</strong>
                        <a href="javascript:void(0)" onclick="verDetalleFactura(${f.id})" class="ml-2 text-info" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                    <td><small class="text-muted"><i class="fas fa-calendar"></i> ${f.fecha_factura}</small></td>
                    <td class="text-center">
                        <span class="badge badge-info">${f.cantidad_productos || 0} <i class="fas fa-box"></i></span>
                    </td>
                    <td class="text-center">${badge}</td>
                </tr>`;
        });
        
        html += `
                </tbody>
            </table>
        </div>`;
        
        $('#listaFacturasCliente').html(html);
    }).fail(function() {
        $('#listaFacturasCliente').html(`
            <div class="alert alert-danger mb-0">
                <i class="fas fa-exclamation-triangle"></i> 
                Error al cargar las facturas
            </div>
        `);
    });
}

function actualizarPreviewFacturas() {
    const total = facturasSelTmp.length;
    $('#totalFacturasSeleccionadas').text(total);
    
    // Calcular total de productos
    let totalProductos = 0;
    facturasSelTmp.forEach(f => {
        totalProductos += parseInt(f.cantidadProductos || 0);
    });
    $('#totalProductosDistribuir').text(totalProductos);
    
    if (total === 0) {
        $('#mensajeVacioPreview').show();
        $('#tablaPreviewFacturas tr:not(#mensajeVacioPreview)').remove();
        return;
    }
    
    $('#mensajeVacioPreview').hide();
    $('#tablaPreviewFacturas tr:not(#mensajeVacioPreview)').remove();
    
    facturasSelTmp.forEach((f, index) => {
        const row = `
        <tr>
            <td><strong>#${f.numero}</strong></td>
            <td><small>${f.cliente}</small></td>
            <td class="text-center"><span class="badge badge-info">${f.cantidadProductos || 0} <i class="fas fa-box"></i></span></td>
            <td class="text-center">
                <button class="btn btn-xs btn-danger" onclick="removerFactura(${index})" title="Quitar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
        $('#tablaPreviewFacturas').append(row);
    });
}

function removerFactura(index) {
    const factura = facturasSelTmp[index];
    facturasSelTmp.splice(index, 1);
    actualizarPreviewFacturas();
    
    if ($('#resultadosFacturas').is(':visible')) {
        $('#buscarFacturaNumero').trigger('keyup');
    }
    if ($('#facturasClienteSeleccionado').is(':visible') && clienteSeleccionado) {
        seleccionarCliente(clienteSeleccionado.id, clienteSeleccionado.nombre, 0);
    }
    
    toastr.info(`Factura #${factura.numero} removida`, 'Información', {
        positionClass: 'toast-top-right',
        timeOut: 2000
    });
}

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
    
    const equipoId = $('select[name="equipo_entrega_id"]').val();
    const fechaProgramada = $('input[name="fecha_programada"]').val();
    const observaciones = $('textarea[name="observaciones"]').val();
    
    if (!equipoId) {
        Swal.fire({
            icon: 'warning',
            title: 'Equipo requerido',
            text: 'Debe seleccionar un equipo de entrega',
            confirmButtonColor: '#28a745'
        });
        return;
    }
    
    if (!fechaProgramada) {
        Swal.fire({
            icon: 'warning',
            title: 'Fecha requerida',
            text: 'Debe seleccionar una fecha programada',
            confirmButtonColor: '#28a745'
        });
        return;
    }
    
    const data = {
        equipo_entrega_id: equipoId,
        fecha_programada: fechaProgramada,
        observaciones: observaciones,
        facturas: facturasSelTmp.map(f => f.id)
    };
    
    console.log('Datos a enviar:', data);
    
    $.ajax({
        url: '/logistica/distribuciones/guardar',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(r) {
            console.log('Respuesta exitosa:', r);
            Swal.fire({
                icon: r.icon || 'success',
                title: r.title || 'Éxito',
                text: r.text || 'Distribución guardada correctamente',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.href = '{{ route("logistica.distribuciones") }}';
            });
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', {xhr, status, error});
            console.error('Response:', xhr.responseJSON);
            Swal.fire({
                icon: xhr.responseJSON?.icon || 'error',
                title: xhr.responseJSON?.title || 'Error',
                text: xhr.responseJSON?.text || 'Error al guardar la distribución',
                confirmButtonColor: '#dc3545'
            });
        }
    });
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {

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
    let html = `
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-success btn-sm" onclick="agregarFacturasSeleccionadasBusqueda()">
                <i class="fas fa-plus-circle"></i> Agregar Seleccionadas
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleSeleccionarTodasBusqueda()">
                <i class="fas fa-check-square"></i> Seleccionar Todas
            </button>
        </div>
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-sm table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th width="40px" class="text-center">
                            <input type="checkbox" id="checkTodasFacturasBusqueda" onchange="seleccionarTodasFacturasBusqueda(this.checked)">
                        </th>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th class="text-center">Productos</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>`;
    
    facturas.forEach(f => {
        const yaAgregada = facturasSelTmp.find(fs => fs.id === f.id);
        const checkDisabled = yaAgregada ? 'disabled' : '';
        const rowClass = yaAgregada ? 'table-success' : '';
        const badge = yaAgregada ? '<span class="badge badge-success"><i class="fas fa-check"></i> Agregada</span>' : '<span class="badge badge-light">Disponible</span>';
        
        html += `
        <tr class="${rowClass}">
            <td class="text-center">
                <input type="checkbox" class="check-factura-busqueda" ${checkDisabled}
                       data-id="${f.id}"
                       data-numero="${f.numero_factura}"
                       data-cliente="${f.cliente.replace(/"/g, '&quot;')}"
                       data-total="${f.total}"
                       data-productos="${f.cantidad_productos || 0}">
            </td>
            <td>
                <strong>#${f.numero_factura}</strong>
                <a href="javascript:void(0)" onclick="verDetalleFactura(${f.id})" class="ml-2 text-info" title="Ver detalle">
                    <i class="fas fa-eye"></i>
                </a>
            </td>
            <td><small>${f.cliente}</small></td>
            <td><small class="text-muted"><i class="fas fa-calendar"></i> ${f.fecha_emision || ''}</small></td>
            <td class="text-center"><span class="badge badge-info">${f.cantidad_productos || 0} <i class="fas fa-box"></i></span></td>
            <td class="text-center">${badge}</td>
        </tr>`;
    });
    
    html += `
                </tbody>
            </table>
        </div>`;
    
    $('#listaResultadosFacturas').html(html);
}

// ========== BÚSQUEDA POR CLIENTE ==========

let timerBusquedaCliente;
$('#buscarClienteNombre').on('keyup', function() {
    clearTimeout(timerBusquedaCliente);
    const termino = $(this).val().trim();
    
    limpiarClienteSeleccionado();
    
    if (termino.length < 3) {
        $('#resultadosClientes').hide();
        return;
    }
    
    $('#resultadosClientes').show();
    $('#mensajeResultadosClientes').html('<i class="fas fa-spinner fa-spin"></i> Buscando clientes...');
    
    timerBusquedaCliente = setTimeout(() => {
        $.ajax({
            url: "{{ url('/logistica/facturas/clientes-autocompletado') }}",
            type: 'GET',
            data: {termino: termino},
            success: function(response) {
                if (response.success && response.clientes.length > 0) {
                    $('#mensajeResultadosClientes').text(`${response.clientes.length} cliente(s) encontrado(s)`);
                    mostrarResultadosClientes(response.clientes);
                } else {
                    $('#mensajeResultadosClientes').html('<i class="fas fa-search"></i> No se encontraron clientes');
                    $('#listaResultadosClientes').html('');
                }
            },
            error: function() {
                $('#mensajeResultadosClientes').html('<i class="fas fa-exclamation-triangle"></i> Error al buscar');
                $('#listaResultadosClientes').html('');
            }
        });
    }, 500);
});

function mostrarResultadosClientes(clientes) {
    let html = '';
    clientes.forEach(c => {
        html += `
        <a href="javascript:void(0)" class="list-group-item list-group-item-action" 
           onclick="seleccionarCliente(${c.id}, '${c.nombre.replace(/'/g, "\\'")}', ${c.facturas_disponibles || 0})">
            <div class="d-flex w-100 justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1"><i class="fas fa-user-circle text-info"></i> ${c.nombre}</h6>
                </div>
                <span class="badge badge-primary badge-pill">${c.facturas_disponibles || 0} facturas</span>
            </div>
        </a>`;
    });
    $('#listaResultadosClientes').html(html);
}

function seleccionarCliente(clienteId, nombreCliente, facturas) {
    clienteSeleccionado = {id: clienteId, nombre: nombreCliente};
    
    $('#resultadosClientes').hide();
    $('#buscarClienteNombre').val(nombreCliente);
    $('#nombreClienteSeleccionado').text(nombreCliente);
    $('#facturasClienteSeleccionado').show();
    
    $('#listaFacturasCliente').html(`
        <div class="col-12 text-center py-4">
            <i class="fas fa-spinner fa-spin fa-3x text-info"></i>
            <p class="mt-2">Cargando facturas...</p>
        </div>
    `);
    
    $.ajax({
        url: "{{ url('/logistica/facturas/por-cliente-id') }}",
        type: 'GET',
        data: {cliente_id: clienteId},
        success: function(response) {
            if (response.success && response.facturas.length > 0) {
                mostrarFacturasCliente(response.facturas, nombreCliente);
            } else {
                $('#listaFacturasCliente').html(`
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            No hay facturas disponibles para este cliente
                        </div>
                    </div>
                `);
            }
        },
        error: function() {
            $('#listaFacturasCliente').html(`
                <div class="col-12">
                    <div class="alert alert-danger">
                        <i class="fas fa-times"></i> Error al cargar facturas
                    </div>
                </div>
            `);
        }
    });
}

function mostrarFacturasCliente(facturas, nombreCliente) {
    let html = '';
    facturas.forEach(f => {
        const yaAgregada = facturasSelTmp.find(fs => fs.id === f.id);
        const disabled = yaAgregada ? 'disabled' : '';
        const btnClass = yaAgregada ? 'btn-secondary' : 'btn-success';
        const btnText = yaAgregada ? '<i class="fas fa-check"></i> Agregada' : '<i class="fas fa-plus"></i> Agregar';
        
        html += `
        <div class="col-md-6 mb-3">
            <div class="card h-100 ${yaAgregada ? 'border-success' : ''}">
                <div class="card-body p-3">
                    <h6 class="card-title text-primary mb-2">
                        <i class="fas fa-file-invoice"></i> #${f.numero_factura}
                    </h6>
                    <p class="card-text mb-1">
                        <small class="text-muted"><i class="fas fa-calendar"></i> ${f.fecha_factura}</small>
                    </p>
                    <p class="card-text mb-2">
                        <small class="text-muted"><i class="fas fa-map-marker-alt"></i> ${f.direccion || 'Sin dirección'}</small>
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h6 mb-0 text-success">Q${parseFloat(f.total).toFixed(2)}</span>
                        <button class="btn btn-sm ${btnClass}" ${disabled}
                                onclick="agregarFactura(${f.id}, '${f.numero_factura}', '${nombreCliente.replace(/'/g, "\\'")}', '${f.direccion || ''}', ${f.total})">
                            ${btnText}
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    });
    $('#listaFacturasCliente').html(html);
}

}); // END DOMContentLoaded
</script>
</x-app-layout>