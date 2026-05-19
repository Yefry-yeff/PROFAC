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
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2><i class="fa fa-archive mr-2" style="color:#e67e22"></i>Cardex Completo</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Cardex Completo</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="cdx-card">
                    <div class="cdx-card-header">
                        <h5><i class="fa fa-filter"></i> Filtros de Búsqueda</h5>
                    </div>
                    <div class="cdx-card-body">
                        <div class="row">
                            <div class="col-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Bodega <span class="text-danger">*</span></label>
                                    <select id="bodega" name="bodega" class="form-control"
                                        data-parsley-required onchange="obtenerIdBodega()">
                                        <option value="" selected disabled>--Seleccionar una Bodega--</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Producto <span class="text-danger">*</span></label>
                                    <select id="producto" name="producto" class="form-control"
                                        data-parsley-required>
                                        <option value="" selected disabled>--Seleccionar un Producto--</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button class="btn btn-primary" onclick="cargaCardex()">
                                <i class="fa fa-search mr-1"></i> Solicitar
                            </button>
                        </div>
                    </div>
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
                                        <th>Vale T.1</th>
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
                                            <th>Vale T.1</th>
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
    <script src="{{ asset('js/js_proyecto/cardex/cardextres.js') }}"></script>
@endpush
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


    <script src="{{ asset('js/js_proyecto/cardex/cardextres.js') }}"></script>

@endpush

