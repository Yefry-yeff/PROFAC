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
        #tbl_boletas thead th {
            background: #fdf4e7; color: #7d3f00;
            font-size: .70rem; font-weight: 700;
            letter-spacing: .04em; text-transform: uppercase;
            border-bottom: 2px solid #f2d49a;
            white-space: nowrap; padding: 7px 8px; vertical-align: middle;
        }
        #tbl_boletas tbody td {
            font-size: .80rem; vertical-align: middle; padding: 6px 8px;
        }
        #tbl_boletas tbody tr:hover { background: #fffcf5; }
        .swal2-container { z-index: 2000 !important; }
        .boleta-check:disabled { accent-color: #aaa; cursor: not-allowed; opacity: .5; }
        #tbl_boletas tbody tr { cursor: pointer; }
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2><i class="fa fa-shopping-cart mr-2" style="color:#e67e22"></i>Historial de Boletas de Compra</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a>Boleta de Compra</a></li>
                <li class="breadcrumb-item active"><strong>Historial</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="cdx-card">
                    <div class="cdx-card-header">
                        <h5><i class="fa fa-filter"></i> Filtrar Boletas</h5>
                    </div>
                    <div class="cdx-card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Fecha Inicio:</label>
                                    <input type="date" id="fechaInicio" class="form-control"
                                           value="{{ $fechaInicio }}">
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Fecha Final:</label>
                                    <input type="date" id="fechaFinal" class="form-control"
                                           value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="font-weight-bold small" for="buscarBoletasTexto">Boleta, cliente, RTN o direccion:</label>
                                <input id="buscarBoletasTexto" class="form-control" maxlength="255">
                            </div>
                            <div class="col-12 col-sm-12 col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary mb-3" onclick="buscarBoletas()">
                                    <i class="fa fa-search mr-1"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="cdx-card">
                    <div class="cdx-card-header">
                        <h5><i class="fa fa-list"></i> Listado de Boletas de Compra</h5>
                    </div>
                    <div class="cdx-card-body">
                        <div class="d-flex flex-wrap align-items-center mb-3" style="gap:8px">
                            <span id="boletasSeleccionadas">0 seleccionadas</span>
                            <button id="incluirBoletas" class="btn btn-primary" disabled>Pasar al reporte de compras</button>
                            <button id="quitarBoletas" class="btn btn-warning" disabled>Quitar del reporte de compras</button>
                            <a id="excelBoletas" class="btn btn-success ml-auto disabled" aria-disabled="true">
                                <i class="fa fa-file-excel-o mr-1"></i> Exportar Excel
                            </a>
                        </div>
                        <div id="errorBoletas" class="alert alert-danger d-none" role="alert"></div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tbl_boletas">
                                <thead>
                                    <tr>
                                        <th><input id="seleccionarBoletas" type="checkbox" aria-label="Seleccionar todas las boletas activas filtradas"></th>
                                        <th>No. Boleta</th>
                                        <th>Cliente</th>
                                        <th>Dirección</th>
                                        <th>Fecha</th>
                                        <th>Total (L.)</th>
                                        <th>Registrado por</th>
                                        <th>Estado</th>
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

    <div class="modal fade" id="modalReporteBoletas" tabindex="-1" role="dialog" aria-labelledby="tituloReporteBoletas" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content">
            <div class="modal-header" style="background:var(--pf-grad);color:white">
                <h5 id="tituloReporteBoletas" class="modal-title">Reporte de compras</h5>
                <button class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p id="confirmacionReporteBoletas"></p>
                <p>El cambio quedara auditado con usuario, fecha y hora. Las boletas incluidas no se pueden editar ni anular hasta retirarlas de Compras.</p>
                <div id="errorAccionBoletas" class="alert alert-danger d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button id="confirmarReporteBoletas" class="btn btn-primary">Confirmar</button>
            </div>
        </div></div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/js_proyecto/boleta-compras/historial.js') }}?v={{ filemtime(public_path('js/js_proyecto/boleta-compras/historial.js')) }}"></script>
    @endpush
</div>
