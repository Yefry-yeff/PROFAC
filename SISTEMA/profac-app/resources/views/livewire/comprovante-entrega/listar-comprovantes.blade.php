<div>
    @push('styles')
    <style>
    :root {
        --pf-grad: linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
        --pf-radius: 8px;
        --pf-shadow: 0 2px 8px rgba(0,0,0,.10);
    }
    .cmp-card {
        border: 1px solid #e8d5bf;
        border-radius: var(--pf-radius);
        box-shadow: var(--pf-shadow);
        background: #fff;
        overflow: visible;
    }
    .cmp-card-header {
        background: var(--pf-grad);
        padding: 12px 20px;
        border-radius: var(--pf-radius) var(--pf-radius) 0 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }
    .cmp-card-header h5 {
        margin: 0;
        color: #fff;
        font-size: .85rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .cmp-card-body { padding: 12px 16px; }
    .btn-cmp-filter {
        background: rgba(255,255,255,.18) !important;
        color: #fff !important;
        border: 1.5px solid rgba(255,255,255,.5) !important;
        border-radius: 5px !important;
        font-weight: 600 !important;
        font-size: .78rem;
        padding: 5px 14px;
        transition: background .18s;
        white-space: nowrap;
        cursor: pointer;
    }
    .btn-cmp-filter:hover { background: rgba(255,255,255,.30) !important; }
    .filtros-bar {
        padding: 8px 16px;
        background: #fdfaf5;
        border-bottom: 1px solid #e8d5bf;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        font-size: .78rem;
    }
    .filtro-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #fff8ee;
        border: 1px solid #f2d49a;
        border-radius: 12px;
        padding: 2px 10px;
        font-size: .75rem;
        color: #7d3f00;
    }
    .filtro-badge .filtro-remove {
        cursor: pointer;
        color: #c0622a;
        font-weight: 700;
        margin-left: 3px;
    }
    .filtro-badge .filtro-remove:hover { color: #e74c3c; }
    #tbl_listar_comprobantes {
        width: 100% !important;
    }
    #tbl_listar_comprobantes thead th {
        background: #fdf4e7;
        color: #7d3f00;
        font-size: .70rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        border-bottom: 2px solid #f2d49a;
        white-space: nowrap;
        padding: 7px 8px;
        vertical-align: middle;
    }
    #tbl_listar_comprobantes tbody td {
        font-size: .80rem;
        vertical-align: middle;
        padding: 6px 8px;
    }
    #tbl_listar_comprobantes tbody tr:hover { background: #fffcf5; }
    .modal-header-cmp {
        background: var(--pf-grad);
        color: #fff;
        border-radius: var(--pf-radius) var(--pf-radius) 0 0;
    }
    .modal-header-cmp .modal-title { color: #fff; font-size: .95rem; font-weight: 700; }
    .modal-header-cmp .close { color: #fff; opacity: .8; text-shadow: none; }
    .modal-header-cmp .close:hover { opacity: 1; }
    .modal-section-label {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: #e67e22;
        border-bottom: 2px solid #fdebd0;
        padding-bottom: 5px;
        margin-bottom: 12px;
        margin-top: 6px;
    }
    .select2-container--open { z-index: 99999 !important; }
    </style>
    @endpush

    <div id="tbl_loading_overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.78); z-index:9000; text-align:center; padding-top:18%; display:none;">
        <i class="fa fa-spinner fa-spin fa-3x" style="color:#1ab394;"></i>
        <p class="mt-3" style="color:#555; font-size:1rem;">Cargando datos...</p>
    </div>

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2><i class="fa fa-truck mr-2" style="color:#e67e22"></i>Comprobantes de Entrega</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Listado Activos</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="cmp-card">
                    <div class="cmp-card-header">
                        <h5><i class="fa fa-list"></i> Listado de Comprobantes</h5>
                        <button type="button" class="btn-cmp-filter" data-toggle="modal" data-target="#modalFiltrosComprobantes">
                            <i class="fa fa-filter mr-1"></i>Filtros
                        </button>
                    </div>

                    <div class="filtros-bar" id="cmpFiltrosBar" style="display:none;"></div>

                    <div id="cmp-placeholder" class="text-center py-5" style="color:#aaa">
                        <i class="fa fa-filter" style="font-size:2.5rem; color:#e67e22; opacity:.45"></i>
                        <p class="mt-3 mb-0" style="font-size:1rem; font-weight:600">Aplique filtros para cargar los resultados</p>
                        <p class="small">Haga clic en <strong>Filtros</strong> para definir los criterios de búsqueda.</p>
                    </div>

                    <div class="cmp-card-body" id="cmp-table-wrapper" style="display:none;">
                        <div class="table-responsive">
                            <table id="tbl_listar_comprobantes" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>N° Comprobante</th>
                                        <th>Cliente</th>
                                        <th>RTN</th>
                                        <th>Fecha de Emisión</th>
                                        <th>Sub Total Lps.</th>
                                        <th>ISV en Lps.</th>
                                        <th>Total en Lps.</th>
                                        <th>Estado</th>
                                        <th>Registrado Por</th>
                                        <th>Fecha de Registro</th>
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

    <div class="modal fade" id="modalFiltrosComprobantes" tabindex="-1" role="dialog" aria-labelledby="tituloModalFiltrosComprobantes" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-cmp">
                    <h5 class="modal-title" id="tituloModalFiltrosComprobantes">
                        <i class="fa fa-filter mr-2"></i>Filtros de Búsqueda
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-2">
                    <p class="modal-section-label"><i class="fa fa-calendar mr-1"></i>Rango de fechas</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">Desde</label>
                                <input type="date" class="form-control form-control-sm" id="cmpFiltroDesde">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">Hasta</label>
                                <input type="date" class="form-control form-control-sm" id="cmpFiltroHasta">
                            </div>
                        </div>
                    </div>

                    <p class="modal-section-label"><i class="fa fa-search mr-1"></i>Criterios de búsqueda</p>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold small">N° Comprobante</label>
                                <input type="text" class="form-control form-control-sm" id="cmpFiltroNumero" placeholder="Ej: 0001 o parcial">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold small">Cliente</label>
                                <select id="cmpFiltroCliente" class="form-control" style="width:100%">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold small">Registrado por</label>
                                <select id="cmpFiltroUsuario" class="form-control" style="width:100%">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="limpiarFiltrosComprobantes()">
                        <i class="fa fa-eraser mr-1"></i>Limpiar
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="aplicarFiltrosComprobantes()">
                        <i class="fa fa-search mr-1"></i>Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/js_proyecto/comprobante-entrega/listar-comprobantes.js') }}?v={{ @filemtime(public_path('js/js_proyecto/comprobante-entrega/listar-comprobantes.js')) }}"></script>
    @endpush
</div>
