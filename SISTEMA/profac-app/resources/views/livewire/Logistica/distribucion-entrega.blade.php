@push('styles')
<style>
    /* ===== DISTRIBUCIÓN ENTREGA ===== */
    .de-page { font-family: 'Source Sans Pro', sans-serif; }

    .de-main-card {
        border: none;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }

    .de-header {
        background: linear-gradient(135deg, #0f766e 0%, #0d9488 60%, #14b8a6 100%);
        padding: 1.5rem 1.75rem;
        border-bottom: none;
    }

    .de-hero-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.18);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        margin-right: 1rem;
        font-size: 1.5rem; color: #fff;
        flex-shrink: 0;
    }

    .de-header h4 { color: #fff; font-weight: 700; margin: 0; font-size: 1.25rem; }
    .de-header small { color: rgba(255,255,255,0.8); font-size: 0.82rem; }

    .de-btn-primary {
        background: rgba(255,255,255,0.15);
        border: 1.5px solid rgba(255,255,255,0.5);
        color: #fff;
        border-radius: 8px;
        padding: 0.5rem 1.2rem;
        font-weight: 600;
        transition: background 0.2s, border-color 0.2s;
        white-space: nowrap;
    }
    .de-btn-primary:hover {
        background: rgba(255,255,255,0.28);
        border-color: #fff;
        color: #fff;
        text-decoration: none;
    }

    /* Nav tabs */
    .de-tabs { border-bottom: 2px solid #e9ecef; }
    .de-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        font-weight: 600;
        padding: 0.85rem 1.25rem;
        margin-bottom: -2px;
        border-radius: 0;
        transition: color 0.2s;
    }
    .de-tabs .nav-link:hover { color: #0f766e; }
    .de-tabs .nav-link.active {
        color: #0f766e;
        border-bottom-color: #0f766e;
        background: transparent;
    }
    .de-tabs .nav-link i { margin-right: 0.4rem; }

    /* Loading overlay */
    .de-loading-overlay {
        position: fixed; inset: 0;
        background: rgba(255,255,255,0.92);
        z-index: 9000;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        transition: opacity 0.4s;
    }
    .de-loading-overlay.is-hidden { opacity: 0; pointer-events: none; }
    .de-loader {
        width: 48px; height: 48px;
        border: 5px solid #e0f2f1;
        border-top-color: #0f766e;
        border-radius: 50%;
        animation: de-spin 0.8s linear infinite;
    }
    @keyframes de-spin { to { transform: rotate(360deg); } }

    /* Scrollbar en modal detalle */
    #bodyDetalleDistribucion .table-responsive::-webkit-scrollbar { width: 8px; height: 8px; }
    #bodyDetalleDistribucion .table-responsive::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    #bodyDetalleDistribucion .table-responsive::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
    #bodyDetalleDistribucion .table-responsive::-webkit-scrollbar-thumb:hover { background: #555; }

    /* SweetAlert z-index */
    .swal2-container { z-index: 10000 !important; }
    .swal2-popup { z-index: 10001 !important; }

    /* ====================================================
       AdminLTE: posicionar modales dentro del área de
       contenido (excluye el sidebar ~250px y el header ~57px)
    ==================================================== */
    @media (min-width: 992px) {
        .modal {
            padding-left: 250px !important;
            padding-top:  57px  !important;
        }
        .modal-backdrop {
            left:   250px !important;
            top:    57px  !important;
            width:  calc(100% - 250px) !important;
            height: calc(100% - 57px)  !important;
        }
        /* Los modales centrados no necesitan margin-top adicional */
        .modal-dialog-centered {
            min-height: calc(100% - 57px - 2rem);
        }
    }
    .modal-dialog {
        margin-top: 1.5rem;
    }

    /* ===== Modal Detalle Distribución ===== */
    #modalDetalleDistribucion .modal-content {
        border: none;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 8px 40px rgba(0,0,0,0.18);
    }
    #modalDetalleDistribucion .de-modal-header {
        background: linear-gradient(135deg, #0f766e 0%, #0d9488 55%, #14b8a6 100%);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    #modalDetalleDistribucion .de-modal-header h5 {
        color: #fff;
        font-weight: 700;
        font-size: 1.05rem;
        margin: 0;
    }
    #modalDetalleDistribucion .de-modal-header .de-modal-meta {
        color: rgba(255,255,255,0.82);
        font-size: 0.8rem;
        margin-top: 2px;
    }
    #modalDetalleDistribucion .de-modal-header .btn-close-modal {
        background: rgba(255,255,255,0.18);
        border: 1.5px solid rgba(255,255,255,0.4);
        color: #fff;
        border-radius: 8px;
        width: 32px; height: 32px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        transition: background .15s;
        flex-shrink: 0;
    }
    #modalDetalleDistribucion .de-modal-header .btn-close-modal:hover {
        background: rgba(255,255,255,0.32);
    }
    #modalDetalleDistribucion .de-stat-strip {
        display: flex;
        gap: 0;
        border-bottom: 1px solid #e9ecef;
        background: #f8fffe;
    }
    #modalDetalleDistribucion .de-stat-item {
        flex: 1;
        text-align: center;
        padding: .75rem .5rem;
        border-right: 1px solid #e9ecef;
    }
    #modalDetalleDistribucion .de-stat-item:last-child { border-right: none; }
    #modalDetalleDistribucion .de-stat-item .de-stat-num {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 2px;
    }
    #modalDetalleDistribucion .de-stat-item .de-stat-lbl {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #78909c;
        font-weight: 600;
    }
    #modalDetalleDistribucion .modal-body {
        padding: 0;
    }
    #modalDetalleDistribucion .de-table-wrap {
        padding: 1rem 1.25rem;
        max-height: 55vh;
        overflow-y: auto;
    }
    #modalDetalleDistribucion .de-table-wrap::-webkit-scrollbar { width: 6px; }
    #modalDetalleDistribucion .de-table-wrap::-webkit-scrollbar-track { background: #f1f5f9; }
    #modalDetalleDistribucion .de-table-wrap::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 3px; }
    #modalDetalleDistribucion .de-dist-table thead th {
        background: #0f766e;
        color: #fff;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        border: none;
        padding: .6rem .75rem;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    #modalDetalleDistribucion .de-dist-table tbody tr:hover { background: #f0fdfa; }
    #modalDetalleDistribucion .de-dist-table td { vertical-align: middle; font-size: .88rem; border-color: #e9ecef; }
    #modalDetalleDistribucion .modal-footer {
        background: #f8fffe;
        border-top: 1px solid #e0f2f1;
        padding: .75rem 1.25rem;
        gap: .5rem;
    }

    /* Botones en tabla */
    .btn-group .btn { transition: all 0.2s ease; }
    .btn-group .btn:hover { transform: scale(1.1); }

    /* Tablas */
    .table-responsive { width: 100%; }
    .table { width: 100% !important; }
</style>
@endpush

<div class="de-page">

    <!-- Loading overlay -->
    <div id="pageLoadingDE" class="de-loading-overlay">
        <div class="de-loader"></div>
        <p class="mt-3 text-muted small">Cargando distribuciones...</p>
    </div>

    <!-- Tarjeta principal con pestañas -->
    <div class="card de-main-card">

        <!-- Header -->
        <div class="card-header de-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="de-hero-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div>
                        <h4>Distribución de Entregas</h4>
                        <small>Gestión de rutas y distribución de facturas</small>
                    </div>
                </div>
                <a href="{{ route('logistica.distribuciones.nueva') }}" class="btn de-btn-primary">
                    <i class="fas fa-plus"></i> Nueva Distribución
                </a>
            </div>
        </div>

        <!-- Pestañas -->
        <ul class="nav de-tabs px-3 pt-2" id="tabsDistribucion" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#tab-pendientes" role="tab">
                    <i class="fas fa-clock text-warning"></i> Pendientes de Tratar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-proceso" role="tab">
                    <i class="fas fa-truck text-info"></i> Sin Finalizar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-completadas" role="tab">
                    <i class="fas fa-check-circle text-success"></i> Completadas
                </a>
            </li>
        </ul>

        <!-- Contenido de pestañas -->
        <div class="card-body tab-content p-3">

            <!-- Pestaña: Pendientes de Tratar -->
            <div class="tab-pane fade show active" id="tab-pendientes" role="tabpanel">
                <div class="table-responsive">
                    <table id="tablaPendientes" class="table table-bordered table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Equipo</th>
                                <th>Descripción</th>
                                <th>Progreso</th>
                                <th>Estado</th>
                                <th>Creador</th>
                                <th>F. Actualización</th>
                                <th>Usuario Autorizó</th>
                                <th>Opciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Pestaña: Sin Finalizar -->
            <div class="tab-pane fade" id="tab-proceso" role="tabpanel">
                <div class="table-responsive">
                    <table id="tablaEnProceso" class="table table-bordered table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Equipo</th>
                                <th>Descripción</th>
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

            <!-- Pestaña: Completadas -->
            <div class="tab-pane fade" id="tab-completadas" role="tabpanel">
                <div class="table-responsive">
                    <table id="tablaCompletadas" class="table table-bordered table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Equipo</th>
                                <th>Descripción</th>
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
        <div class="modal-dialog modal-xl" style="max-width:92%; margin-left:auto; margin-right:auto;">
            <div class="modal-content">

                <!-- Header con gradiente teal -->
                <div class="de-modal-header">
                    <div>
                        <h5><i class="fas fa-truck mr-2"></i><span id="tituloDetalleDistribucion">Detalle de Distribución</span></h5>
                        <div class="de-modal-meta" id="metaDetalleDistribucion"></div>
                    </div>
                    <button class="btn-close-modal" data-dismiss="modal" title="Cerrar">&times;</button>
                </div>

                <!-- Strip de estadísticas -->
                <div class="de-stat-strip" id="statStripDistribucion">
                    <div class="de-stat-item">
                        <div class="de-stat-num text-primary" id="statTotal">-</div>
                        <div class="de-stat-lbl"><i class="fas fa-file-invoice"></i> Total Facturas</div>
                    </div>
                    <div class="de-stat-item">
                        <div class="de-stat-num text-success" id="statEntregadas">-</div>
                        <div class="de-stat-lbl"><i class="fas fa-check-circle"></i> Entregadas</div>
                    </div>
                    <div class="de-stat-item">
                        <div class="de-stat-num text-warning" id="statPendientes">-</div>
                        <div class="de-stat-lbl"><i class="fas fa-clock"></i> Pendientes</div>
                    </div>
                </div>

                <!-- Cuerpo: tabla de facturas -->
                <div class="modal-body">
                    <div class="de-table-wrap" id="bodyDetalleDistribucion">
                        <!-- Contenido dinámico -->
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="imprimirCartaEntrega()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <button type="button" class="btn btn-warning" onclick="editarDistribucion()">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button type="button" class="btn btn-secondary ml-auto" data-dismiss="modal">
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

    <!-- Modal: Hora de Salida del Equipo -->
    <div class="modal fade" id="modalHoraSalida" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title"><i class="fas fa-clock"></i> Hora de Salida del Equipo</h5>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="text-muted mb-3">Registre la hora en que el equipo sale a ruta</p>
                    <input type="time" id="inputHoraSalida" class="form-control form-control-lg text-center mb-3"
                           style="font-size:1.8rem; height:65px; letter-spacing:2px;">
                    <div class="text-left">
                        <label class="text-muted small mb-1">Observaciones de salida <span class="text-muted">(opcional)</span></label>
                        <textarea id="inputObservacionesSalida" class="form-control" rows="2"
                                  placeholder="Ej: equipo completo, carga verificada..."></textarea>
                    </div>
                    <small class="text-muted mt-2 d-block">Se guardará junto con el inicio de la distribución</small>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="$('#modalHoraSalida').modal('hide')">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-info" onclick="confirmarHoraSalidaYIniciar()">
                        <i class="fas fa-play"></i> Iniciar Distribución
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Imágenes de Incidencia -->
    <div class="modal fade" id="modalImagenesIncidencia" tabindex="-1" role="dialog" style="z-index: 1060;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mb-0">
                        <i class="fas fa-images"></i> Evidencias Fotográficas
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="bodyImagenesIncidencia">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="mt-2 text-muted">Cargando imágenes...</p>
                    </div>
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
let tablaPendientes, tablaEnProceso, tablaCompletadas, facturasSelTmp = [];

$(document).ready(() => {
    // Ajustar columnas de DataTable al cambiar de pestaña
    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    setTimeout(() => ocultarLoaderDE(), 1500);

    // Auto-abrir detalle si viene con ?ver=ID en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const verDistribucionId = urlParams.get('ver');
    if (verDistribucionId) {
        // Esperar que los DataTables terminen de cargar para abrir el modal
        setTimeout(() => verFacturas(parseInt(verDistribucionId)), 1800);
        // Limpiar el param de la URL sin recargar
        const cleanUrl = window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }

    // Configuración base común de DataTables
    const configBase = {
        processing: true,
        serverSide: true,
        language: {url: '/js/plugins/dataTables/i18n/Spanish.json'},
        order: [[1, 'desc']],
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        deferRender: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        drawCallback: function() {
            $('[data-toggle="tooltip"]').tooltip();
        }
    };

    // Tabla Pendientes de Tratar (10 columnas)
    tablaPendientes = $('#tablaPendientes').DataTable({
        ...configBase,
        ajax: "{{ route('logistica.distribuciones.listar') }}?tipo=pendientes",
        columns: [
            {data: 'id'},
            {data: 'fecha_programada'},
            {data: 'nombre_equipo'},
            {data: 'observaciones'},
            {data: 'progreso'},
            {data: 'estado'},
            {data: 'creador'},
            {data: 'fecha_actualizacion', defaultContent: '-'},
            {data: 'usuario_autorizacion', defaultContent: '-'},
            {data: 'opciones', orderable: false}
        ]
    });

    // Tabla Sin Finalizar (8 columnas)
    tablaEnProceso = $('#tablaEnProceso').DataTable({
        ...configBase,
        ajax: "{{ route('logistica.distribuciones.listar') }}?tipo=sin_finalizar",
        columns: [
            {data: 'id'},
            {data: 'fecha_programada'},
            {data: 'nombre_equipo'},
            {data: 'observaciones'},
            {data: 'progreso'},
            {data: 'estado'},
            {data: 'creador'},
            {data: 'opciones', orderable: false}
        ]
    });

    // Tabla Completadas (8 columnas)
    tablaCompletadas = $('#tablaCompletadas').DataTable({
        ...configBase,
        ajax: "{{ route('logistica.distribuciones.listar') }}?tipo=completadas",
        columns: [
            {data: 'id'},
            {data: 'fecha_programada'},
            {data: 'nombre_equipo'},
            {data: 'observaciones'},
            {data: 'progreso'},
            {data: 'estado'},
            {data: 'creador'},
            {data: 'opciones', orderable: false}
        ]
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

function ocultarLoaderDE() {
    $('#pageLoadingDE').addClass('is-hidden');
}

// ========== FUNCIÓN HELPER ==========
function recargarTodasLasTablas(mantenerPaginacion = true) {
    if (mantenerPaginacion) {
        tablaPendientes.ajax.reload(null, false);
        tablaEnProceso.ajax.reload(null, false);
        tablaCompletadas.ajax.reload(null, false);
    } else {
        tablaPendientes.ajax.reload();
        tablaEnProceso.ajax.reload();
        tablaCompletadas.ajax.reload();
    }
}

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
                        <i class="fas fa-file-invoice"></i> #${f.cai}
                    </h6>
                    <p class="card-text mb-2">
                        <small class="text-muted"><i class="fas fa-user"></i> ${f.cliente}</small>
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h6 mb-0 text-success">Q${parseFloat(f.total).toFixed(2)}</span>
                        <button class="btn btn-sm ${btnClass}" ${disabled}
                                onclick="agregarFactura(${f.id}, '${f.cai}', '${f.cliente.replace(/'/g, "\\'")}', '${f.direccion || ''}', ${f.total})">
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
            recargarTodasLasTablas(false);
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
    $('#tituloDetalleDistribucion').html('<i class="fas fa-spinner fa-spin mr-1"></i> Cargando...');
    $('#metaDetalleDistribucion').html('');
    $('#statTotal, #statEntregadas, #statPendientes').html('<i class="fas fa-spinner fa-spin"></i>');
    $('#bodyDetalleDistribucion').html(
        '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x" style="color:#0f766e;"></i>' +
        '<p class="mt-3 text-muted">Cargando facturas...</p></div>'
    );

    $.get("{{ url('/logistica/distribuciones/facturas') }}/" + id, function(r) {
        const distribucion = r.distribucion || {};
        const total      = r.facturas.length;
        const entregadas = r.facturas.filter(f => f.estado_entrega === 'entregado').length;
        const pendientes = r.facturas.filter(f => f.estado_entrega === 'sin_entrega').length;

        // Actualizar header
        $('#tituloDetalleDistribucion').text(distribucion.nombre_equipo + ' — Distribución #' + distribucion.id);
        $('#metaDetalleDistribucion').text('Fecha programada: ' + distribucion.fecha_programada);

        // Stats strip
        $('#statTotal').text(total);
        $('#statEntregadas').text(entregadas);
        $('#statPendientes').text(pendientes);

        // Tabla
        let html = `<table class="table table-sm table-hover de-dist-table w-100">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>Factura</th>
                    <th>Cliente</th>
                    <th width="110">Estado</th>
                    <th width="90" class="text-center">Incidencias</th>
                    <th width="80" class="text-center">Tratadas</th>
                    <th width="160" class="text-center">Opciones</th>
                </tr>
            </thead>
            <tbody>`;

        if (total === 0) {
            html += '<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay facturas asignadas</td></tr>';
        } else {
            const soloLectura = distribucion.estado_id === 3 || distribucion.estado_id === 4;

            r.facturas.forEach(f => {
                const estadoBadge = f.estado_entrega === 'entregado' ? 'success' :
                                    f.estado_entrega === 'parcial'   ? 'warning' : 'secondary';
                const estadoTexto = f.estado_entrega === 'sin_entrega' ? 'Sin Entrega'
                    : f.estado_entrega.charAt(0).toUpperCase() + f.estado_entrega.slice(1);
                const bloqueado  = f.estado_entrega === 'entregado' || f.estado_entrega === 'parcial';
                const totalInc   = parseInt(f.total_incidencias) || 0;
                const tratadas   = parseInt(f.incidencias_tratadas) || 0;
                const todasTrat  = totalInc > 0 && totalInc === tratadas;
                const sinTratar  = totalInc > 0 && tratadas < totalInc;

                html += `<tr>
                    <td class="text-muted">${f.orden_entrega}</td>
                    <td><span class="font-weight-bold text-primary">#${f.cai}</span></td>
                    <td>${f.cliente}</td>
                    <td><span class="badge badge-${estadoBadge} px-2 py-1">${estadoTexto}</span></td>
                    <td class="text-center">
                        ${totalInc > 0
                            ? `<span class="badge badge-${sinTratar ? 'warning' : 'info'}">${totalInc}</span>`
                            : '<span class="text-muted small">0</span>'}
                    </td>
                    <td class="text-center">
                        ${totalInc > 0
                            ? (todasTrat
                                ? '<span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>'
                                : '<span class="badge badge-danger"><i class="fas fa-times"></i> No</span>')
                            : '<span class="text-muted small">N/A</span>'}
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            ${!soloLectura && bloqueado
                                ? `<button class="btn btn-warning" onclick="desbloquearFactura(${f.id})" title="Desbloquear"><i class="fas fa-unlock"></i></button>`
                                : ''}
                            ${!soloLectura && !bloqueado
                                ? `<button class="btn btn-danger" onclick="anularEntrega(${f.id})" title="Cancelar"><i class="fas fa-times"></i></button>`
                                : ''}
                            <button class="btn btn-info" onclick="verIncidencias(${f.id})" title="Incidencias">
                                <i class="fas fa-exclamation-circle"></i>
                            </button>
                            ${!soloLectura && f.estado_entrega === 'sin_entrega' && !bloqueado
                                ? `<button class="btn btn-success" onclick="confirmarEntregaFactura(${f.id}, ${distribucion.id})" title="Confirmar Entrega"><i class="fas fa-check"></i></button>`
                                : ''}
                        </div>
                    </td>
                </tr>`;
            });
        }

        html += '</tbody></table>';
        $('#bodyDetalleDistribucion').html(html);

    }).fail(function() {
        $('#tituloDetalleDistribucion').text('Error');
        $('#bodyDetalleDistribucion').html('<div class="alert alert-danger m-3"><i class="fas fa-exclamation-triangle"></i> Error al cargar las facturas</div>');
    });
}

function iniciarDistribucion(id) {
    // Mostrar modal de hora de salida antes de iniciar
    const ahora = new Date();
    const horaActual = ahora.getHours().toString().padStart(2,'0') + ':' + ahora.getMinutes().toString().padStart(2,'0');
    $('#inputHoraSalida').val(horaActual);
    $('#modalHoraSalida').data('distribucion-id', id).modal('show');
}

function confirmarHoraSalidaYIniciar() {
    const id = $('#modalHoraSalida').data('distribucion-id');
    const horaSalida = $('#inputHoraSalida').val();
    if (!horaSalida) {
        Swal.fire({icon: 'warning', title: 'Hora requerida', text: 'Ingrese la hora de salida del equipo'});
        return;
    }
    $('#modalHoraSalida').modal('hide');
    $.post("{{ url('/logistica/distribuciones/iniciar') }}/" + id, {
        _token: $('meta[name="csrf-token"]').attr('content'),
        hora_salida: horaSalida,
        observaciones_salida: $('#inputObservacionesSalida').val()
    }, function(r) {
        Swal.fire({icon: r.icon, title: r.title, text: r.text, confirmButtonColor: '#28a745'});
        recargarTodasLasTablas(false);
    }).fail(x => Swal.fire({icon: 'error', title: x.responseJSON?.title || 'Error', text: x.responseJSON?.text || 'Error al iniciar'}));
}

function imprimirCartaEntrega() {
    const id = $('#modalDetalleDistribucion').data('distribucion-id');
    if (!id) return;
    window.open('/logistica/distribuciones/' + id + '/carta-entrega', '_blank');
}

function editarDistribucion() {
    const id = $('#modalDetalleDistribucion').data('distribucion-id');
    if (!id) return;
    window.location.href = '/logistica/distribuciones/nueva?editar=' + id;
}

function cancelarDistribucion(id) {
    Swal.fire({title: 'Cancelar?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545'}).then(r => {
        if (r.isConfirmed) {
            $.post("{{ url('/logistica/distribuciones/cancelar') }}/" + id, {_token: $('meta[name="csrf-token"]').attr('content')}, r => {
                Swal.fire(r.title, r.text, r.icon);
                recargarTodasLasTablas(false);
            }).fail(x => Swal.fire(x.responseJSON.title, x.responseJSON.text, x.responseJSON.icon));
        }
    });
}

function abrirConfirmacion(id) {
    // Primero validar que todas las facturas estén entregadas y que no haya incidencias sin tratar
    $.ajax({
        url: "{{ url('/logistica/distribuciones/validar-completar') }}/" + id,
        type: 'GET',
        success: function(validacion) {
            if (!validacion.puede_completar) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No se puede completar',
                    html: validacion.mensaje,
                    confirmButtonColor: '#f0ad4e'
                });
                return;
            }
            
            // Si pasa todas las validaciones, mostrar confirmación
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
                            recargarTodasLasTablas(true);
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
        },
        error: function(x) {
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: x.responseJSON?.message || 'No se pudo validar la distribución',
                confirmButtonColor: '#dc3545'
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
            html: '<div style="text-align:left;padding:0 0.25rem">' +
                  '<p style="font-size:0.85rem;color:#6c757d;margin:0 0 0.75rem">El estado cambiará a <strong>Sin Entrega</strong>.</p>' +
                  '<label style="font-size:0.8rem;font-weight:600;color:#495057;display:block;margin-bottom:4px">Motivo <span style="color:#dc3545">*</span></label>' +
                  '<textarea id="motivoAnulacion" rows="2" placeholder="Motivo de anulación..." style="width:100%;font-size:0.85rem;padding:6px 10px;border:1px solid #ced4da;border-radius:6px;resize:none;outline:none;box-sizing:border-box;"></textarea>' +
                  '</div>',
            icon: 'warning',
            width: 420,
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar',
            customClass: { htmlContainer: 'swal-compact' },
            preConfirm: () => {
                const motivo = document.getElementById('motivoAnulacion').value.trim();
                if (!motivo) {
                    Swal.showValidationMessage('El motivo de anulación es obligatorio.');
                    return false;
                }
                return motivo;
            }
        }).then((result) => {
            if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('/logistica/facturas/anular-entrega') }}/" + facturaId,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    motivo: result.value
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

// Handle nested modals properly
$('#modalImagenesIncidencia').on('show.bs.modal', function () {
    // Increase z-index of the backdrop for this modal
    setTimeout(function() {
        $('.modal-backdrop').last().css('z-index', 1055);
    }, 0);
});

$('#modalImagenesIncidencia').on('hidden.bs.modal', function () {
    // Ensure body stays with modal-open class if another modal is still open
    if ($('.modal:visible').length > 0) {
        $('body').addClass('modal-open');
    }
});

function verImagenesIncidenciaDistribucion(incidenciaId) {
    $('#modalImagenesIncidencia').modal('show');
    $('#bodyImagenesIncidencia').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><p class="mt-2 text-muted">Cargando imágenes...</p></div>');
    
    const url = "{{ url('/logistica/confirmacion/incidencias') }}/" + incidenciaId + "/evidencias";
    
    $.get(url)
        .done(resp => {
            const evidencias = resp.evidencias || [];
            if (!evidencias.length) {
                $('#bodyImagenesIncidencia').html('<div class="alert alert-info mb-0"><i class="fas fa-info-circle"></i> Esta incidencia no tiene evidencias fotográficas.</div>');
                return;
            }
            
            let grid = '<div class="row">';
            evidencias.forEach(e => {
                grid += `<div class="col-6 col-md-4 mb-3">
                    <div class="border rounded p-2" style="height:200px;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f8f9fa;">
                        <a href="${e.url}" target="_blank" title="Ver imagen completa">
                            <img src="${e.url}" alt="evidencia" class="img-fluid" style="max-height:180px;max-width:100%;object-fit:contain;">
                        </a>
                    </div>
                    ${e.descripcion ? `<small class="text-muted d-block mt-1">${e.descripcion}</small>` : ''}
                </div>`;
            });
            grid += '</div>';
            $('#bodyImagenesIncidencia').html(grid);
        })
        .fail(() => {
            $('#bodyImagenesIncidencia').html('<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle"></i> Error al cargar las imágenes.</div>');
        });
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
                    <h6>Factura: <strong>#${r.factura?.cai || 'N/A'}</strong></h6>
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
                                <th width="120" class="text-center">Imágenes</th>
                            </tr>
                        </thead>
                        <tbody>`;
                
                r.incidencias.forEach((inc, index) => {
                    const imagenCount = inc.evidencias_count || 0;
                    const btnImagenes = imagenCount > 0 
                        ? `<button type="button" class="btn btn-sm btn-info" onclick="verImagenesIncidenciaDistribucion(${inc.id})" title="Ver imágenes">
                            <i class="fas fa-images"></i> ${imagenCount}
                           </button>`
                        : '<span class="text-muted"><i class="fas fa-image-slash"></i> Sin imágenes</span>';
                    
                    html += `<tr>
                        <td>${index + 1}</td>
                        <td><strong>#${inc.producto_id || 'N/A'}</strong> - ${inc.producto_nombre || 'N/A'}</td>
                        <td><span class="badge badge-warning">${inc.tipo || 'N/A'}</span></td>
                        <td>${inc.descripcion || 'Sin descripción'}</td>
                        <td>${inc.created_at ? new Date(inc.created_at).toLocaleString('es-HN') : 'N/A'}</td>
                        <td class="text-center">${btnImagenes}</td>
                    </tr>`;
                });
                
                html += `</tbody></table></div>
                <div class="alert alert-light mt-3">
                    <strong>Total de incidencias:</strong> ${r.incidencias.length}
                </div>
                <hr>
                <div class="mt-3">
                    <h6 class="mb-2"><i class="fas fa-clipboard-check"></i> Tratamiento de Incidencias</h6>`;
                
                // Verificar el estado de la factura
                const estadoEntrega = r.factura?.estado_entrega || '';
                
                // Mostrar tratamientos existentes si hay
                if (r.tratamientos && r.tratamientos.length > 0) {
                    html += `<div class="mb-3">`;
                    html += `<h6 class="text-muted small"><i class="fas fa-history"></i> Historial de Tratamientos (${r.tratamientos.length})</h6>`;
                    r.tratamientos.forEach((t, index) => {
                        html += `
                            <div class="alert alert-success mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div style="flex: 1;">
                                        <strong>Tratamiento #${r.tratamientos.length - index}:</strong>
                                        <p class="mb-1 mt-1" style="white-space: pre-wrap;">${t.tratamiento}</p>
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> ${t.usuario_registro} · 
                                            <i class="fas fa-calendar"></i> ${new Date(t.tratamiento_fecha).toLocaleString('es-HN')}
                                        </small>
                                    </div>
                                </div>
                            </div>`;
                    });
                    html += `</div>`;
                }
                
                // Formulario para agregar nuevo tratamiento
                if (estadoEntrega === 'sin_entrega') {
                    // Si está en sin_entrega, no permitir dar tratamiento
                    html += `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Tratamiento No Disponible</strong>
                            <p class="mb-0 mt-2">No se puede registrar tratamiento mientras la factura esté en estado <strong>"Sin Entrega"</strong>.</p>
                            <p class="mb-0">Primero debe confirmar la entrega de la factura para poder registrar el tratamiento de las incidencias.</p>
                        </div>`;
                } else {
                    // Siempre permitir agregar nuevo tratamiento si no está en sin_entrega
                    html += `
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-plus-circle"></i> Agregar Nuevo Tratamiento</h6>
                                <p class="text-muted small mb-2">Registra un nuevo tratamiento que se aplicará a todas las incidencias de esta factura.</p>
                                <textarea id="tratamientoIncidencias" class="form-control" rows="3" placeholder="Describe el tratamiento o solución aplicada..."></textarea>
                                <button type="button" class="btn btn-success btn-sm mt-2" onclick="guardarTratamiento(${facturaId})">
                                    <i class="fas fa-save"></i> Guardar Tratamiento
                                </button>
                            </div>
                        </div>`;
                }
                
                html += `</div>`;
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

function guardarTratamiento(facturaId) {
    const tratamiento = $('#tratamientoIncidencias').val().trim();
    
    if (!tratamiento) {
        Swal.fire({
            icon: 'warning',
            title: 'Tratamiento requerido',
            text: 'Por favor describe el tratamiento que se aplicará a estas incidencias.',
            confirmButtonColor: '#f0ad4e'
        });
        return;
    }

    Swal.fire({
        title: '¿Guardar tratamiento?',
        text: 'Este tratamiento se aplicará a todas las incidencias de esta factura.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = "{{ url('/logistica/facturas/incidencias/tratamiento') }}";
            
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    factura_id: facturaId,
                    tratamiento: tratamiento,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tratamiento guardado',
                        text: response.message || 'El tratamiento ha sido registrado exitosamente.',
                        confirmButtonColor: '#28a745'
                    });
                    // Recargar las incidencias para mostrar el nuevo tratamiento
                    verIncidencias(facturaId);
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'No se pudo guardar el tratamiento';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }
    });
}

function confirmarEntregaFactura(facturaId, distribucionId) {
    // Primero validar si hay incidencias sin tratamiento en toda la distribución
    $.ajax({
        url: "{{ url('/logistica/distribuciones/validar-incidencias') }}/" + distribucionId,
        type: 'GET',
        success: function(validacion) {
            if (!validacion.puede_confirmar) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incidencias pendientes',
                    html: validacion.mensaje,
                    confirmButtonColor: '#f0ad4e'
                });
                return;
            }
            
            // Si pasa la validación, proceder con la confirmación
            Swal.fire({
                title: '¿Confirmar entrega?',
                html: '<div style="text-align:left;padding:0 0.25rem">' +
                      '<p style="font-size:0.85rem;color:#6c757d;margin:0 0 0.75rem">El estado cambiará a <strong>Entregado</strong>.</p>' +
                      '<label style="font-size:0.8rem;font-weight:600;color:#495057;display:block;margin-bottom:4px">Observación <span style="color:#dc3545">*</span></label>' +
                      '<textarea id="motivoConfirmacion" rows="2" placeholder="Observación de la entrega..." style="width:100%;font-size:0.85rem;padding:6px 10px;border:1px solid #ced4da;border-radius:6px;resize:none;outline:none;box-sizing:border-box;"></textarea>' +
                      '</div>',
                icon: 'question',
                width: 420,
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar',
                customClass: { htmlContainer: 'swal-compact' },
                preConfirm: () => {
                    const motivo = document.getElementById('motivoConfirmacion').value.trim();
                    if (!motivo) {
                        Swal.showValidationMessage('El motivo de confirmación es obligatorio.');
                        return false;
                    }
                    return motivo;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('/logistica/facturas/confirmar-entrega') }}/" + facturaId,
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            motivo: result.value
                        },
                        success: function(r) {
                            Swal.fire({
                                icon: r.icon || 'success',
                                title: r.title || 'Confirmada',
                                text: r.text || 'La entrega ha sido confirmada como completa',
                                confirmButtonColor: '#28a745'
                            });
                            // Recargar el modal de detalle
                            const modalDistribucionId = $('#modalDetalleDistribucion').data('distribucion-id');
                            if (modalDistribucionId) {
                                verFacturas(modalDistribucionId);
                            }
                            // Recargar la tabla principal
                            recargarTodasLasTablas(true);
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
        },
        error: function(x) {
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: x.responseJSON?.message || 'No se pudo validar las incidencias',
                confirmButtonColor: '#dc3545'
            });
        }
    });
}
</script>
@endpush
