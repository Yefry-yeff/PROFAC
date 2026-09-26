<x-app-layout>
<div class="container-fluid">
    <!-- Header -->
    <div class="mb-3 row">
        <div class="col-12">
            <div class="shadow-sm card">
                <div class="py-3 card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0" id="tituloPagina">
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
        <input type="hidden" id="editarDistribucionId" name="editar_id" value="">
        <div class="row">
            <!-- Columna Izquierda: Información y Búsqueda -->
            <div class="col-lg-8">
                
                <!-- Información Básica -->
                <div class="mb-3 shadow-sm card">
                    <div class="bg-white card-header border-bottom">
                        <h6 class="mb-0"><i class="fas fa-info-circle text-primary"></i> Información de la Distribución</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-truck"></i> Equipo de Entrega *</label>
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
                                <div class="form-group">
                                    <label><i class="fas fa-user-hard-hat"></i> Personal Encargado de la Distribución *</label>
                                    <select class="form-control" name="personal[]" id="selectPersonalDistribucion" multiple required>
                                        @foreach($personalDisponible as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->rol }})</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Motoristas, Equipo de Entregas y Picking activos.</small>
                                </div>
                                <div id="wrapPorcentajesPersonal" class="mb-3" style="display:none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="mb-0"><i class="fas fa-percentage"></i> Porcentaje de comisión por persona *</label>
                                        <span id="totalPorcentajePersonal" class="badge badge-secondary">Total: 0%</span>
                                    </div>
                                    <div id="listaPorcentajesPersonal"></div>
                                    <small class="text-muted">La suma de los porcentajes debe ser exactamente 100%. Se usará para el cálculo de comisiones.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-0 form-group">
                                    <label><i class="fas fa-sticky-note"></i> Observaciones</label>
                                    <textarea class="form-control" name="observaciones" rows="2" 
                                              placeholder="Ingrese observaciones adicionales..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Búsqueda de Facturas -->
                <div class="mb-3 shadow-sm card">
                    <div class="bg-white card-header border-bottom">
                        <h6 class="mb-0"><i class="fas fa-search text-success"></i> Búsqueda de Facturas</h6>
                    </div>
                    <div class="card-body">
                        
                        <!-- Tabs de búsqueda -->
                        <ul class="mb-3 nav nav-pills nav-fill" id="tipoBusquedaTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-zona" data-toggle="pill" href="#busqueda-zona" role="tab">
                                    <i class="fas fa-map-marked-alt"></i> Facturas por Zona
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-factura" data-toggle="pill" href="#busqueda-factura" role="tab">
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

                            <!-- Búsqueda por Zona Geográfica -->
                            <div class="tab-pane fade show active" id="busqueda-zona" role="tabpanel">
                                <div id="zonasCardsWrap" class="row">
                                    <div class="col-12 text-center text-muted py-3">
                                        <i class="fas fa-spinner fa-spin"></i> Cargando zonas...
                                    </div>
                                </div>

                                <!-- Facturas de la zona seleccionada -->
                                <div id="facturasZonaSeleccionada" style="display: none;" class="mt-3">
                                    <div class="alert alert-success d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-map-marked-alt"></i> <strong>Zona:</strong> <span id="nombreZonaSeleccionada"></span></span>
                                        <button type="button" class="close" onclick="limpiarZonaSeleccionada()">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <div id="listaFacturasZona"></div>
                                </div>
                            </div>

                            <!-- Búsqueda por Factura -->
                            <div class="tab-pane fade" id="busqueda-factura" role="tabpanel">
                                <div class="mb-3 input-group input-group-lg">
                                    <div class="input-group-prepend">
                                        <span class="text-white input-group-text bg-primary">
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
                                    <div class="mb-3 alert alert-info">
                                        <i class="fas fa-info-circle"></i> 
                                        <span id="mensajeResultadosFacturas">Ingrese al menos 2 caracteres para buscar</span>
                                    </div>
                                    <div id="listaResultadosFacturas" class="row"></div>
                                </div>
                            </div>

                            <!-- Búsqueda por Cliente -->
                            <div class="tab-pane fade" id="busqueda-cliente" role="tabpanel">
                                <div class="mb-3 input-group input-group-lg">
                                    <div class="input-group-prepend">
                                        <span class="text-white input-group-text bg-info">
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
                                    <div class="mb-3 alert alert-info">
                                        <i class="fas fa-info-circle"></i> 
                                        <span id="mensajeResultadosClientes">Ingrese al menos 3 caracteres para buscar</span>
                                    </div>
                                    <div id="listaResultadosClientes" class="mb-3 list-group"></div>
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
                <div class="shadow-sm card sticky-top" style="top: 20px;">
                    <div class="text-white card-header bg-gradient-success">
                        <h6 class="mb-0">
                            <i class="fas fa-truck-loading"></i> 
                            Facturas para Distribuir
                        </h6>
                    </div>
                    <div class="p-0 card-body">
                        <div id="previewFacturasSeleccionadas" class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                            <table class="table mb-0 table-sm table-hover">
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
                                        <td colspan="4" class="py-5 text-center text-muted">
                                            <i class="mb-3 fas fa-inbox fa-3x d-block"></i>
                                            <p class="mb-0">No hay facturas seleccionadas</p>
                                            <small>Busque y agregue facturas</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="p-3 mb-3 text-center bg-white border rounded">
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
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="text-white modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice"></i> Detalle de Factura
                </h5>
                <button type="button" class="text-white close" data-dismiss="modal">
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

<!-- Modal: Éxito al guardar distribución -->
<div class="modal fade" id="modalExitoDistribucion" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:420px;">
        <div class="modal-content" style="border-radius:20px; overflow:hidden; border:none; box-shadow:0 20px 60px rgba(0,0,0,.18);">
            <button type="button" data-dismiss="modal" aria-label="Cerrar"
                    style="position:absolute; top:12px; right:14px; background:none; border:none;
                           font-size:20px; color:#9e9e9e; cursor:pointer; line-height:1; z-index:1;
                           padding:4px 8px; border-radius:50%;" title="Cerrar">&times;</button>
            <div class="modal-body" style="padding:36px 32px 28px; text-align:center;">
                <div style="width:90px; height:90px; border-radius:50%;
                            background:linear-gradient(135deg,#0f766e,#14b8a6);
                            display:flex; align-items:center; justify-content:center;
                            margin:0 auto 20px; box-shadow:0 8px 24px rgba(15,118,110,.30);">
                    <i class="fa fa-check" style="font-size:46px; color:#fff; line-height:1;"></i>
                </div>
                <h4 style="font-weight:800; color:#0f766e; margin-bottom:6px; font-size:18px;">¡Distribución guardada!</h4>
                <p id="msgNumDistribucion" style="color:#546e7a; font-size:13px; margin-bottom:24px;">La distribución fue registrada exitosamente.</p>
                <div style="display:flex; justify-content:center;">
                    <button onclick="distribuccionAccion('imprimir')"
                            style="background:#fafafa; color:#374151; border:1.5px solid #e5e7eb;
                                   border-radius:10px; padding:11px 32px; font-size:13px; font-weight:700;
                                   cursor:pointer; text-align:center; transition:background .15s;"
                            onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fafafa'">
                        <i class="fa fa-print d-block" style="font-size:22px; margin-bottom:4px; color:#6b7280;"></i>
                        Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
/* Centrar modales dentro del área de contenido (excluir sidebar 250px) */
@media (min-width: 992px) {
    .modal {
        padding-left: 250px !important;
    }
}

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

/* Filtro estilo Excel en encabezados de la tabla "Facturas por Zona" */
.filtro-columna-icono { margin-left: 4px; cursor: pointer; }
.filtro-columna-icono:hover { color: #0d6efd !important; }
.filtro-columna-dropdown {
    z-index: 2000;
    width: 220px;
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: 6px;
    box-shadow: 0 6px 18px rgba(0,0,0,.18);
    font-size: 13px;
}
.filtro-columna-opciones { max-height: 220px; overflow-y: auto; }
.filtro-columna-opciones label { font-weight: normal; cursor: pointer; }
</style>

<script>
// Variables y funciones globales (accesibles desde onclick)
let facturasSelTmp = [];
let clienteSeleccionado = null;
let personalPorcentajes = {}; // { user_id: porcentaje }

// ========== PERSONAL ENCARGADO: porcentajes de comisión ==========
let personalPorcentajesManual = {}; // ids cuyo % fue editado a mano por el usuario

function actualizarListaPorcentajesPersonal() {
    const ids = $('#selectPersonalDistribucion').val() || [];

    // Descartar valores de personas ya no seleccionadas
    Object.keys(personalPorcentajes).forEach(id => {
        if (!ids.includes(id)) delete personalPorcentajes[id];
    });
    Object.keys(personalPorcentajesManual).forEach(id => {
        if (!ids.includes(id)) delete personalPorcentajesManual[id];
    });

    if (!ids.length) {
        $('#wrapPorcentajesPersonal').hide();
        $('#listaPorcentajesPersonal').html('');
        actualizarTotalPorcentajePersonal();
        return;
    }
    $('#wrapPorcentajesPersonal').show();

    // Reparto equitativo del restante entre las personas SIN % editado a mano,
    // ajustando el redondeo para que la suma total quede en exactamente 100%.
    const idsManual = ids.filter(id => personalPorcentajesManual[id] !== undefined);
    const idsAuto = ids.filter(id => personalPorcentajesManual[id] === undefined);
    const sumaManual = idsManual.reduce((sum, id) => sum + (parseFloat(personalPorcentajes[id]) || 0), 0);
    const restante = Math.max(0, Math.round((100 - sumaManual) * 100) / 100);

    if (idsAuto.length) {
        const base = Math.floor((restante / idsAuto.length) * 100) / 100;
        let acumulado = 0;
        idsAuto.forEach((id, idx) => {
            const esUltimo = idx === idsAuto.length - 1;
            const valor = esUltimo ? Math.round((restante - acumulado) * 100) / 100 : base;
            personalPorcentajes[id] = valor;
            acumulado += valor;
        });
    }
    idsManual.forEach(id => { personalPorcentajes[id] = parseFloat(personalPorcentajes[id]) || 0; });

    let html = '';
    ids.forEach(id => {
        const nombre = $('#selectPersonalDistribucion option[value="' + id + '"]').text();
        html += `
            <div class="d-flex align-items-center mb-2">
                <div class="flex-grow-1 mr-2">${nombre}</div>
                <div style="width:110px;">
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control text-right input-porcentaje-personal"
                               data-id="${id}" min="0" max="100" step="0.01" value="${personalPorcentajes[id]}">
                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                    </div>
                </div>
            </div>
        `;
    });
    $('#listaPorcentajesPersonal').html(html);
    actualizarTotalPorcentajePersonal();
}

function actualizarTotalPorcentajePersonal() {
    const ids = $('#selectPersonalDistribucion').val() || [];
    let total = 0;
    ids.forEach(id => { total += parseFloat(personalPorcentajes[id] ?? 0); });
    total = Math.round(total * 100) / 100;

    const badge = $('#totalPorcentajePersonal');
    badge.text('Total: ' + total + '%');
    badge.removeClass('badge-success badge-danger badge-secondary');
    badge.addClass(Math.abs(total - 100) < 0.01 ? 'badge-success' : 'badge-danger');
}

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

// ========== BÚSQUEDA POR ZONA GEOGRÁFICA ==========

let zonaSeleccionada = null;

function cargarZonasParaBusqueda() {
    $.get("{{ route('logistica.zonas.resumen') }}", function (data) {
        if (!data.success) {
            $('#zonasCardsWrap').html('<div class="col-12"><div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle"></i> No se pudieron cargar las zonas</div></div>');
            return;
        }

        let html = '';
        (data.zonas || []).forEach(z => {
            const badgeColor = z.facturas_pendientes > 0 ? 'warning' : 'secondary';
            html += `
            <div class="col-md-4 col-lg-3 mb-3">
                <div class="card h-100 shadow-sm zona-card" style="cursor:pointer;" onclick="verFacturasDeZona(${z.id}, '${(z.name || '').replace(/'/g, "\\'")}')">
                    <div class="card-body text-center">
                        <i class="fas fa-map-marked-alt fa-2x text-primary mb-2"></i>
                        <h6 class="mb-1">${z.name}</h6>
                        <span class="badge badge-${badgeColor}">${z.facturas_pendientes} pendiente(s)</span>
                    </div>
                </div>
            </div>`;
        });

        const sinClasificar = data.sin_clasificar || 0;
        html += `
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card h-100 shadow-sm zona-card border-secondary" style="cursor:pointer;" onclick="verFacturasDeZona('sin_clasificar', 'Sin clasificar')">
                <div class="card-body text-center">
                    <i class="fas fa-question-circle fa-2x text-secondary mb-2"></i>
                    <h6 class="mb-1">Sin clasificar</h6>
                    <span class="badge badge-${sinClasificar > 0 ? 'warning' : 'secondary'}">${sinClasificar} pendiente(s)</span>
                </div>
            </div>
        </div>`;

        if (!(data.zonas || []).length && sinClasificar === 0) {
            html = '<div class="col-12"><div class="alert alert-info mb-0"><i class="fas fa-info-circle"></i> No hay zonas configuradas. Cree zonas en el módulo de Agrupaciones de Entregas.</div></div>';
        }

        $('#zonasCardsWrap').html(html);
    }).fail(function () {
        $('#zonasCardsWrap').html('<div class="col-12"><div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle"></i> Error al cargar las zonas</div></div>');
    });
}

function limpiarZonaSeleccionada() {
    zonaSeleccionada = null;
    $('#facturasZonaSeleccionada').hide();
    $('#listaFacturasZona').html('');
    $('#nombreZonaSeleccionada').text('');
}

function verFacturasDeZona(zonaId, nombreZona) {
    zonaSeleccionada = { id: zonaId, nombre: nombreZona };
    $('#nombreZonaSeleccionada').text(nombreZona);
    $('#facturasZonaSeleccionada').show();
    $('#busquedaZonaTermino').val('');
    cargarFacturasDeZona('');
}

let timerBusquedaZona;
function buscarFacturasDeZona(termino) {
    clearTimeout(timerBusquedaZona);
    timerBusquedaZona = setTimeout(() => cargarFacturasDeZona(termino), 350);
}

let facturasZonaCache = [];
let filtrosColumnaZona = {}; // { asesor_comercial: Set([...]), gestor: Set([...]) }

function cargarFacturasDeZona(termino) {
    $('#listaFacturasZona').html('<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando facturas...</div>');
    filtrosColumnaZona = {};

    $.get("{{ route('logistica.zonas.facturas') }}", { zona_id: zonaSeleccionada.id, search: termino || '' }, function (data) {
        facturasZonaCache = (data && data.facturas) || [];
        if (!facturasZonaCache.length) {
            $('#listaFacturasZona').html(`
                <div class="mb-0 alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    ${termino ? 'No hay facturas que coincidan con la búsqueda' : 'No hay facturas pendientes en esta zona'}
                </div>
            `);
            return;
        }

        let html = `
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
            <button type="button" class="btn btn-success btn-sm" onclick="agregarFacturasSeleccionadasZona()">
                <i class="fas fa-plus-circle"></i> Agregar Seleccionadas
            </button>
            <div class="input-group input-group-sm" style="max-width:280px;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                </div>
                <input type="text" id="busquedaZonaTermino" class="form-control" value="${termino ? termino.replace(/"/g, '&quot;') : ''}"
                       placeholder="Buscar por factura o cliente..." oninput="buscarFacturasDeZona(this.value)">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th width="40px" class="text-center">
                            <input type="checkbox" id="checkTodasFacturasZona" onchange="seleccionarTodasFacturasZona(this.checked)">
                        </th>
                        <th>Factura</th>
                        <th>${columnaFiltroHeaderZona('cliente', 'Cliente')}</th>
                        <th>Municipio</th>
                        <th>Dirección</th>
                        <th>${columnaFiltroHeaderZona('asesor_comercial', 'Asesor Comercial')}</th>
                        <th>${columnaFiltroHeaderZona('gestor', 'Gestor de Entrega')}</th>
                        <th>Fecha</th>
                        <th width="100px" class="text-center">Productos</th>
                        <th width="80px" class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody id="tbodyFacturasZona"></tbody>
            </table>
        </div>`;

        $('#listaFacturasZona').html(html);
        renderFilasFacturasZona();
    }).fail(function () {
        $('#listaFacturasZona').html(`
            <div class="mb-0 alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error al cargar las facturas de la zona
            </div>
        `);
    });
}

function columnaFiltroHeaderZona(campo, etiqueta) {
    const activo = filtrosColumnaZona[campo] ? 'text-primary' : 'text-muted';
    return `${etiqueta} <a href="javascript:void(0)" class="filtro-columna-icono ${activo}" data-campo="${campo}" onclick="abrirFiltroColumnaZona('${campo}', this)" title="Filtrar"><i class="fas fa-filter"></i></a>`;
}

function renderFilasFacturasZona() {
    const filtradas = facturasZonaCache.filter(f => {
        return Object.keys(filtrosColumnaZona).every(campo => {
            const valor = f[campo] || '(En blanco)';
            return filtrosColumnaZona[campo].has(valor);
        });
    });

    if (!filtradas.length) {
        $('#tbodyFacturasZona').html('<tr><td colspan="10" class="text-center text-muted py-3">Ningún resultado coincide con los filtros aplicados.</td></tr>');
        return;
    }

    let html = '';
    filtradas.forEach(f => {
        const yaAgregada = facturasSelTmp.find(fs => fs.id === f.id);
        const checkDisabled = yaAgregada ? 'disabled' : '';
        const rowClass = yaAgregada ? 'table-success' : '';
        const badge = yaAgregada ? '<span class="badge badge-success"><i class="fas fa-check"></i> Agregada</span>' : '<span class="badge badge-light">Disponible</span>';

        html += `
            <tr class="${rowClass}">
                <td class="text-center">
                    <input type="checkbox" class="check-factura-zona" ${checkDisabled}
                           data-id="${f.id}"
                           data-numero="${f.cai}"
                           data-cliente="${(f.cliente || '').replace(/"/g, '&quot;')}"
                           data-total="${f.total}"
                           data-productos="${f.cantidad_productos || 0}">
                </td>
                <td>
                    <strong>#${f.cai}</strong>
                    <a href="javascript:void(0)" onclick="verDetalleFactura(${f.id})" class="ml-2 text-info" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
                <td><small>${f.cliente}</small></td>
                <td><small>${f.municipio || '-'}</small></td>
                <td>
                    <small class="direccion-factura-texto" data-id="${f.id}">${f.direccion_completa || '-'}</small>
                    <a href="javascript:void(0)" onclick="editarDireccionFactura(${f.id}, '${(f.direccion_completa || '').replace(/'/g, "\\'")}')" class="ml-1 text-warning" title="Editar dirección">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                </td>
                <td><small>${f.asesor_comercial || '-'}</small></td>
                <td><small>${f.gestor || '-'}</small></td>
                <td><small class="text-muted"><i class="fas fa-calendar"></i> ${f.fecha_emision}</small></td>
                <td class="text-center">
                    <span class="badge badge-info">${f.cantidad_productos || 0} <i class="fas fa-box"></i></span>
                </td>
                <td class="text-center">${badge}</td>
            </tr>`;
    });

    $('#tbodyFacturasZona').html(html);
}

let filtroColumnaZonaAnchor = null;

function abrirFiltroColumnaZona(campo, anchorEl) {
    cerrarFiltroColumnaZona();

    const valoresUnicos = [...new Set(facturasZonaCache.map(f => f[campo] || '(En blanco)'))].sort();
    const seleccionActual = filtrosColumnaZona[campo] || new Set(valoresUnicos);

    let html = `
    <div id="dropdownFiltroZona" class="filtro-columna-dropdown position-fixed">
        <div class="p-2 border-bottom">
            <input type="text" class="form-control form-control-sm" placeholder="Buscar..." oninput="filtrarOpcionesDropdownZona(this.value)">
        </div>
        <div class="p-2 border-bottom">
            <label class="d-block mb-0">
                <input type="checkbox" class="chk-todo-filtro-zona" ${seleccionActual.size === valoresUnicos.length ? 'checked' : ''} onchange="toggleTodoFiltroZona(this.checked)">
                (Seleccionar todo)
            </label>
        </div>
        <div class="filtro-columna-opciones p-2">
            ${valoresUnicos.map(v => `
                <label class="d-block mb-1 opcion-filtro-zona" data-valor="${v.toLowerCase().replace(/"/g, '&quot;')}">
                    <input type="checkbox" class="chk-opcion-filtro-zona" value="${v.replace(/"/g, '&quot;')}" ${seleccionActual.has(v) ? 'checked' : ''}> ${v}
                </label>
            `).join('')}
        </div>
        <div class="p-2 border-top d-flex justify-content-between">
            <button type="button" class="btn btn-sm btn-secondary" onclick="cerrarFiltroColumnaZona()">Cancelar</button>
            <button type="button" class="btn btn-sm btn-primary" onclick="aplicarFiltroColumnaZona('${campo}')">Aceptar</button>
        </div>
    </div>`;

    $('body').append(html);
    filtroColumnaZonaAnchor = anchorEl;
    posicionarFiltroColumnaZona();

    $(document).on('mousedown.filtroColumnaZona', function (e) {
        if (!$(e.target).closest('#dropdownFiltroZona, .filtro-columna-icono').length) {
            cerrarFiltroColumnaZona();
        }
    });
    // Reubicar el dropdown (no cerrarlo) ante cualquier scroll, incluido el de
    // contenedores internos como .table-responsive, para que quede "pegado" al icono.
    window.addEventListener('scroll', posicionarFiltroColumnaZona, true);
    window.addEventListener('resize', posicionarFiltroColumnaZona);
}

function posicionarFiltroColumnaZona() {
    const dropdown = document.getElementById('dropdownFiltroZona');
    if (!dropdown || !filtroColumnaZonaAnchor) return;
    const rect = filtroColumnaZonaAnchor.getBoundingClientRect();
    dropdown.style.top = (rect.bottom + 4) + 'px';
    dropdown.style.left = rect.left + 'px';
}

function toggleTodoFiltroZona(checked) {
    $('.opcion-filtro-zona:visible .chk-opcion-filtro-zona').prop('checked', checked);
}

function filtrarOpcionesDropdownZona(texto) {
    const buscar = texto.toLowerCase();
    $('.opcion-filtro-zona').each(function () {
        $(this).toggle($(this).data('valor').toString().includes(buscar));
    });
}

function aplicarFiltroColumnaZona(campo) {
    const seleccionados = new Set();
    $('.chk-opcion-filtro-zona:checked').each(function () { seleccionados.add($(this).val()); });
    const totalOpciones = $('.chk-opcion-filtro-zona').length;

    if (seleccionados.size === totalOpciones) {
        delete filtrosColumnaZona[campo];
    } else {
        filtrosColumnaZona[campo] = seleccionados;
    }

    $(`.filtro-columna-icono[data-campo="${campo}"]`)
        .toggleClass('text-primary', !!filtrosColumnaZona[campo])
        .toggleClass('text-muted', !filtrosColumnaZona[campo]);

    cerrarFiltroColumnaZona();
    renderFilasFacturasZona();
}

function cerrarFiltroColumnaZona() {
    $('#dropdownFiltroZona').remove();
    $(document).off('mousedown.filtroColumnaZona');
    window.removeEventListener('scroll', posicionarFiltroColumnaZona, true);
    window.removeEventListener('resize', posicionarFiltroColumnaZona);
    filtroColumnaZonaAnchor = null;
}

function seleccionarTodasFacturasZona(checked) {
    $('.check-factura-zona:not(:disabled)').prop('checked', checked);
}

function editarDireccionFactura(facturaId, direccionActual) {
    Swal.fire({
        title: 'Editar dirección de entrega',
        input: 'textarea',
        inputValue: direccionActual || '',
        inputPlaceholder: 'Dirección de entrega (esta es la que viajará a la carta de entrega)',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745'
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: "{{ route('logistica.facturas.actualizarDireccion') }}",
            method: 'POST',
            data: JSON.stringify({ factura_id: facturaId, direccion_entrega: result.value }),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        }).done(r => {
            $(`.direccion-factura-texto[data-id="${facturaId}"]`).text(result.value || '-');
            toastr.success(r.text || 'Dirección actualizada', 'Éxito', { positionClass: 'toast-top-right', timeOut: 2000 });
        }).fail(x => {
            Swal.fire({ icon: 'error', title: 'Error', text: x.responseJSON?.text || 'No se pudo actualizar la dirección.' });
        });
    });
}

function toggleSeleccionarTodasZona() {
    const todasMarcadas = $('.check-factura-zona:not(:disabled):checked').length === $('.check-factura-zona:not(:disabled)').length;
    seleccionarTodasFacturasZona(!todasMarcadas);
    $('#checkTodasFacturasZona').prop('checked', !todasMarcadas);
}

function agregarFacturasSeleccionadasZona() {
    const seleccionadas = [];
    $('.check-factura-zona:checked').each(function () {
        const id = parseInt($(this).data('id'));
        const numero = $(this).data('numero');
        const cliente = $(this).data('cliente');
        const total = parseFloat($(this).data('total'));
        const cantidadProductos = parseInt($(this).data('productos')) || 0;

        if (!facturasSelTmp.find(f => f.id === id)) {
            seleccionadas.push({ id, numero, cliente, total, cantidadProductos });
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

    // Recargar la lista de la zona para refrescar los estados "Agregada"
    if (zonaSeleccionada) {
        verFacturasDeZona(zonaSeleccionada.id, zonaSeleccionada.nombre);
    }

    toastr.success(`${seleccionadas.length} factura(s) agregada(s)`, 'Éxito', {
        positionClass: 'toast-top-right',
        timeOut: 2000
    });
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
            $('#detalleNumeroFactura').text('#' + f.cai);
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
                <div class="mb-0 alert alert-info">
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
                               data-numero="${f.cai}" 
                               data-total="${f.total}"
                               data-productos="${f.cantidad_productos || 0}">
                    </td>
                    <td>
                        <strong>#${f.cai}</strong>
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
            <div class="mb-0 alert alert-danger">
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

let guardandoDistribucion = false;

function guardarDistribucion() {
    if (guardandoDistribucion) return;

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
    const personal = $('#selectPersonalDistribucion').val() || [];
    
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

    if (!personal.length) {
        Swal.fire({
            icon: 'warning',
            title: 'Personal requerido',
            text: 'Debe asignar al menos un encargado de la distribución',
            confirmButtonColor: '#28a745'
        });
        return;
    }

    const personalConPorcentaje = personal.map(id => ({
        user_id: parseInt(id, 10),
        porcentaje: parseFloat(personalPorcentajes[id]) || 0,
    }));
    const totalPorcentaje = Math.round(personalConPorcentaje.reduce((sum, p) => sum + p.porcentaje, 0) * 100) / 100;

    if (Math.abs(totalPorcentaje - 100) >= 0.01) {
        Swal.fire({
            icon: 'warning',
            title: 'Porcentajes incompletos',
            text: `La suma de los porcentajes del personal encargado debe ser exactamente 100%. Actualmente es ${totalPorcentaje}%.`,
            confirmButtonColor: '#28a745'
        });
        return;
    }
    
    const data = {
        equipo_entrega_id: equipoId,
        fecha_programada: fechaProgramada,
        observaciones: observaciones,
        personal: personalConPorcentaje,
        facturas: facturasSelTmp.map(f => f.id),
        editar_id: $('#editarDistribucionId').val() || null,
    };
    
    console.log('Datos a enviar:', data);

    guardandoDistribucion = true;

    // Verificar disponibilidad de facturas antes de guardar
    const facturaIds = data.facturas;
    const queryString = facturaIds.map(id => `facturas[]=${id}`).join('&')
        + (data.editar_id ? `&editar_id=${data.editar_id}` : '');
    $.ajax({
        url: '/logistica/facturas/verificar-disponibilidad?' + queryString,
        type: 'GET',
        success: function(check) {
            if (!check.disponibles && check.bloqueadas.length > 0) {
                const rows = check.bloqueadas.map(b =>
                    `<li><strong>#${b.cai}</strong> &mdash; Equipo: <strong>${b.nombre_equipo}</strong></li>`
                ).join('');
                Swal.fire({
                    icon: 'warning',
                    title: 'Facturas ya asignadas',
                    html: `<p>Las siguientes facturas ya se encuentran en una distribución pendiente o en proceso:</p>
                           <ul style="text-align:left;margin-top:8px">${rows}</ul>
                           <p style="margin-top:10px">Elimínelas del carrito para poder continuar.</p>`,
                    confirmButtonColor: '#f0ad4e'
                });
                guardandoDistribucion = false;
                return;
            }
            _enviarGuardarDistribucion(data);
        },
        error: function() {
            // Si falla la verificación, el backend también valida
            _enviarGuardarDistribucion(data);
        }
    });
}

function _enviarGuardarDistribucion(data) {
    const $btnGuardar = $('button[onclick="guardarDistribucion()"]').prop('disabled', true);
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
            $('#modalNuevaDistribucion').modal('hide');
            $('#msgNumDistribucion').html(`Distribución #${r.distribucion_id} registrada correctamente`);
            $('#modalExitoDistribucion')
                .data('distribucion-id', r.distribucion_id)
                .data('pedido-id', r.pedido_id || null)
                .modal('show');
            resetFormularioDistribucion();
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
        },
        complete: function() {
            $btnGuardar.prop('disabled', false);
            guardandoDistribucion = false;
        }
    });
}

// Limpia el formulario y refresca las facturas pendientes por zona/búsqueda
// para que una factura recién asignada deje de aparecer como disponible.
function resetFormularioDistribucion() {
    facturasSelTmp = [];
    clienteSeleccionado = null;
    personalPorcentajes = {};
    personalPorcentajesManual = {};
    $('#editarDistribucionId').val('');
    $('select[name="equipo_entrega_id"]').val('');
    $('input[name="fecha_programada"]').val('{{ date('Y-m-d') }}');
    $('textarea[name="observaciones"]').val('');
    $('#selectPersonalDistribucion').val([]).trigger('change');
    actualizarPreviewFacturas();
    limpiarZonaSeleccionada();
    limpiarBusquedaFactura();
    limpiarBusquedaCliente();
    cargarZonasParaBusqueda();
}

// ========== ACCIONES DISTRIBUCIÓN ==========
function distribuccionAccion(accion) {
    const distribucionId = $('#modalExitoDistribucion').data('distribucion-id');
    if (!distribucionId) return;
    
    if (accion === 'flujo') {
        const pedidoId = $('#modalExitoDistribucion').data('pedido-id');
        $('#modalExitoDistribucion').modal('hide');
        if (pedidoId) {
            Livewire.dispatch('abrirFlujoPedido', { pedidoId: parseInt(pedidoId), pasoInicial: 'entrega' });
        } else {
            // Sin pedido vinculado, abrir detalle de distribución
            window.location.href = `/logistica/distribuciones?ver=${distribucionId}`;
        }
        return;
    } else if (accion === 'imprimir') {
        // Abrir PDF de carta de entrega
        window.open(`/logistica/distribuciones/${distribucionId}/carta-entrega`, '_blank');
    }
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {

// ========== SELECT2: Personal Encargado de la Distribución ==========
$('#selectPersonalDistribucion').select2({
    placeholder: '-- Seleccione el personal encargado --',
    width: '100%',
}).on('change', function() {
    actualizarListaPorcentajesPersonal();
});

$(document).on('input', '.input-porcentaje-personal', function() {
    const id = String($(this).data('id'));
    personalPorcentajesManual[id] = true;
    personalPorcentajes[id] = parseFloat($(this).val()) || 0;
    actualizarTotalPorcentajePersonal();
});

// ========== CARGA INICIAL: pestaña "Facturas por Zona" ==========
cargarZonasParaBusqueda();

// ========== MODO EDICIÓN: detectar ?editar=ID ==========
(function() {
    const params = new URLSearchParams(window.location.search);
    const editarId = params.get('editar');
    if (!editarId) return;

    $('#editarDistribucionId').val(editarId);
    $('#tituloPagina').html('<i class="fas fa-edit text-warning"></i> Editar Distribución #' + editarId);
    $('button[onclick="guardarDistribucion()"]').html('<i class="fas fa-save"></i> Actualizar Distribución');

    $.get('/logistica/distribuciones/' + editarId + '/datos', function(d) {
        // Rellenar campos básicos
        $('select[name="equipo_entrega_id"]').val(d.equipo_entrega_id);
        $('input[name="fecha_programada"]').val(d.fecha_programada);
        $('textarea[name="observaciones"]').val(d.observaciones || '');
        personalPorcentajes = {};
        personalPorcentajesManual = {};
        (d.personal || []).forEach(p => {
            personalPorcentajes[String(p.user_id)] = parseFloat(p.porcentaje_comision) || 0;
            personalPorcentajesManual[String(p.user_id)] = true;
        });
        $('#selectPersonalDistribucion').val((d.personal || []).map(p => String(p.user_id))).trigger('change');

        // Cargar facturas
        facturasSelTmp = d.facturas.map(f => ({
            id: f.id,
            numero: f.numero,
            cliente: f.cliente,
            direccion: f.direccion || '',
            total: parseFloat(f.total)
        }));
        actualizarPreviewFacturas();
    }).fail(function() {
        Swal.fire({icon: 'error', title: 'Error', text: 'No se pudieron cargar los datos de la distribución'});
    });
})();

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
                       data-numero="${f.cai}"
                       data-cliente="${f.cliente.replace(/"/g, '&quot;')}"
                       data-total="${f.total}"
                       data-productos="${f.cantidad_productos || 0}">
            </td>
            <td>
                <strong>#${f.cai}</strong>
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
        <div class="py-4 text-center col-12">
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
        <div class="mb-3 col-md-6">
            <div class="card h-100 ${yaAgregada ? 'border-success' : ''}">
                <div class="p-3 card-body">
                    <h6 class="mb-2 card-title text-primary">
                        <i class="fas fa-file-invoice"></i> #${f.cai}
                    </h6>
                    <p class="mb-1 card-text">
                        <small class="text-muted"><i class="fas fa-calendar"></i> ${f.fecha_factura}</small>
                    </p>
                    <p class="mb-2 card-text">
                        <small class="text-muted"><i class="fas fa-map-marker-alt"></i> ${f.direccion || 'Sin dirección'}</small>
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="mb-0 h6 text-success">Q${parseFloat(f.total).toFixed(2)}</span>
                        <button class="btn btn-sm ${btnClass}" ${disabled}
                                onclick="agregarFactura(${f.id}, '${f.cai}', '${nombreCliente.replace(/'/g, "\\'")}', '${f.direccion || ''}', ${f.total})">
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

<livewire:flujo.modal-flujo-pedido />
</x-app-layout>
