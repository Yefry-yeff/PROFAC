<div>
    @push('styles')
    <style>
        :root {
            --pf-grad:   linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
            --pf-orange: #e67e22;
            --pf-radius: 8px;
            --pf-shadow: 0 2px 8px rgba(0,0,0,.10);
        }
        .cdx-card {
            border: 1px solid #e8d5bf;
            border-radius: var(--pf-radius);
            box-shadow: var(--pf-shadow);
            background: #fff;
            overflow: visible;
        }
        .cdx-card-header {
            background: var(--pf-grad);
            padding: 10px 18px;
            border-radius: var(--pf-radius) var(--pf-radius) 0 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .cdx-card-header h5 {
            margin: 0; color: #fff;
            font-size: .85rem; font-weight: 700;
            letter-spacing: .05em; text-transform: uppercase;
            display: flex; align-items: center; gap: 8px;
        }
        .cdx-card-body { padding: 14px 18px; }
        #tbl_cardex thead th {
            background: #fdf4e7; color: #7d3f00;
            font-size: .70rem; font-weight: 700;
            letter-spacing: .04em; text-transform: uppercase;
            border-bottom: 2px solid #f2d49a;
            white-space: nowrap; padding: 7px 8px; vertical-align: middle;
        }
        #tbl_cardex tbody td {
            font-size: .80rem; vertical-align: middle; padding: 6px 8px;
        }
        #tbl_cardex tbody tr:hover { background: #fffcf5; }
        tfoot input {
            width: 100%;
            padding: 3px;
            box-sizing: border-box;
        }
        .btn-cardex-filter {
            background: rgba(255,255,255,.18) !important;
            color: #fff !important;
            border: 1.5px solid rgba(255,255,255,.5) !important;
            border-radius: 5px !important;
            font-weight: 600 !important;
            font-size: .78rem;
            padding: 5px 14px;
            cursor: pointer;
        }
        .btn-cardex-filter:hover { background: rgba(255,255,255,.30) !important; }
        .filtros-bar {
            padding: 8px 16px;
            background: #fdfaf5;
            border-bottom: 1px solid #e8d5bf;
            display: flex;
            flex-wrap: wrap;
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
        .modal-header-cdx {
            background: linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
            color: #fff;
            border-radius: 8px 8px 0 0;
        }
        .modal-header-cdx .modal-title { color: #fff; font-size: .95rem; font-weight: 700; }
        .modal-header-cdx .close { color: #fff; opacity: .85; text-shadow: none; }
        .modal-header-cdx .close:hover { opacity: 1; }
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
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2><i class="fa fa-archive mr-2" style="color:#e67e22"></i>Cardex versión 2</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Cardex v2</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="cdx-card">
                    <div class="cdx-card-header">
                        <h5><i class="fa fa-filter"></i> Filtros de Búsqueda</h5>
                        <button type="button" class="btn-cardex-filter" data-toggle="modal" data-target="#modalFiltrosCardex">
                            <i class="fa fa-filter mr-1"></i>Filtros
                        </button>
                    </div>
                    <div class="filtros-bar" id="cdxFiltrosBar" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFiltrosCardex" tabindex="-1" role="dialog" aria-labelledby="tituloModalFiltrosCardex" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-cdx">
                    <h5 class="modal-title" id="tituloModalFiltrosCardex"><i class="fa fa-filter mr-2"></i>Filtros de Cardex</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-2">
                    <p class="modal-section-label"><i class="fa fa-box mr-1"></i>Filtros activos</p>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">Bodega <span class="text-danger">*</span></label>
                                <select id="bodega" name="bodega" class="form-control" data-parsley-required onchange="obtenerIdBodega()">
                                    <option value="" selected disabled>--Seleccionar una Bodega--</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">Producto <span class="text-danger">*</span></label>
                                <select id="producto" name="producto" class="form-control" data-parsley-required>
                                    <option value="" selected disabled>--Seleccionar un Producto--</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="cardexCommonClearFilters()">
                        <i class="fa fa-eraser mr-1"></i>Limpiar
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="cargaCardex(); $('#modalFiltrosCardex').modal('hide');">
                        <i class="fa fa-search mr-1"></i>Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="cdx-card">
                    <div class="cdx-card-header">
                        <h5><i class="fa fa-table"></i> Movimientos de Cardex</h5>
                    </div>
                    <div class="cdx-card-body">
                        <div class="table-responsive">
                            <table id="tbl_cardex" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha de gestion</th>
                                        <th>Producto</th>
                                        <th>Código</th>
                                        <th>Factura</th>
                                        <th>Ajuste</th>
                                        <th>Compra</th>
                                        <th>Comprobante</th>
                                        <th>Vale T.2</th>
                                        <th>N. Crédito</th>
                                        <th>Descripcion</th>
                                        <th>Origen</th>
                                        <th>Destino</th>
                                        <th>Cantidad</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Fecha de gestion</th>
                                            <th>Producto</th>
                                            <th>Código</th>
                                            <th>Factura</th>
                                            <th>Ajuste</th>
                                            <th>Compra</th>
                                            <th>Comprobante</th>
                                            <th>Vale T.2</th>
                                            <th>N. Crédito</th>
                                            <th>Descripcion</th>
                                            <th>Origen</th>
                                            <th>Destino</th>
                                            <th>Cantidad</th>
                                            <th>Usuario</th>
                                        </tr>
                                    </tfoot>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@push('scripts')
    <script src="{{ asset('js/js_proyecto/cardex/cardexDos.js') }}"></script>
    <script src="{{ asset('js/js_proyecto/cardex/cardexFiltrosCommon.js') }}"></script>
@endpush
