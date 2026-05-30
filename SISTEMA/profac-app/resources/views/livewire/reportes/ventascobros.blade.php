<div>
{{-- Loading overlay --}}
<div id="tbl_loading_overlay" style="display:none; position:fixed; inset:0; background:rgba(255,255,255,0.78); z-index:9000; text-align:center; padding-top:18%; backdrop-filter:blur(2px);">
    <i class="fa fa-spinner fa-spin fa-3x" style="color:#e67e22;"></i>
    <p class="mt-3" style="color:#555; font-size:1rem; font-weight:600;">Cargando reporte...</p>
</div>

@push('styles')
<style>
    :root {
        --rvc-grad: linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
        --rvc-grad-hover: linear-gradient(135deg, #e08e0b 0%, #c04e00 100%);
        --rvc-orange: #e67e22;
        --rvc-green: #27ae60;
        --rvc-radius: 8px;
        --rvc-shadow: 0 2px 8px rgba(0,0,0,.10);
    }

    #page-wrapper {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .wrapper-content.rvc-wrapper {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .wrapper-content.rvc-wrapper > .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .wrapper-content.rvc-wrapper > .row > [class*="col-"] {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .rvc-card {
        border: 1px solid #e8d5bf;
        border-radius: var(--rvc-radius);
        box-shadow: var(--rvc-shadow);
        background: #fff;
        overflow: visible;
    }

    .rvc-card-header {
        background: var(--rvc-grad);
        padding: 12px 20px;
        border-radius: var(--rvc-radius) var(--rvc-radius) 0 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }

    .rvc-card-header h5 {
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

    .rvc-card-body {
        padding: 16px 20px;
    }

    .rvc-btn-soft {
        background: rgba(255,255,255,.18) !important;
        color: #fff !important;
        border: 1.5px solid rgba(255,255,255,.5) !important;
        border-radius: 5px !important;
        font-weight: 600 !important;
        font-size: .78rem !important;
        padding: 5px 14px !important;
        transition: background .18s;
        white-space: nowrap;
    }

    .rvc-btn-soft:hover {
        background: rgba(255,255,255,.30) !important;
        color: #fff !important;
    }

    .rvc-btn-pdf   { background:#c0392b !important; color:#fff !important; border:none !important; }
    .rvc-btn-pdf:hover   { background:#a93226 !important; color:#fff !important; }
    .rvc-btn-excel { background:#1e7e34 !important; color:#fff !important; border:none !important; }
    .rvc-btn-excel:hover { background:#155724 !important; color:#fff !important; }
    .rvc-btn-primary { background: var(--rvc-grad) !important; color:#fff !important; border:none !important; }
    .rvc-btn-primary:hover { background: var(--rvc-grad-hover) !important; color:#fff !important; }

    .rvc-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .rvc-stat-pill {
        display: flex;
        align-items: center;
        gap: 7px;
        background: #fdf6ee;
        border: 1px solid #e8d5bf;
        border-radius: 20px;
        padding: 4px 14px 4px 10px;
        font-size: .78rem;
        color: #555;
        font-weight: 500;
    }

    .rvc-stat-pill .stat-num {
        font-size: .9rem;
        font-weight: 700;
        color: var(--rvc-orange);
    }

    .rvc-stat-pill.green { background: #f0fdf4; border-color: #bbf7d0; }
    .rvc-stat-pill.green .stat-num { color: #1a7a4e; }
    .rvc-stat-pill.blue { background: #eff6ff; border-color: #bfdbfe; }
    .rvc-stat-pill.blue .stat-num { color: #1d4ed8; }

    .rvc-filter-box {
        border: 1px solid #eee3d3;
        border-radius: 8px;
        background: #fffdfa;
        padding: 14px;
        margin-bottom: 14px;
    }

    .rvc-section-label {
        font-size: .70rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #6c757d;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 4px;
        margin-bottom: 12px;
    }

    .rvc-filter-box label {
        font-size: .78rem;
        font-weight: 600;
        color: #6b7280;
    }

    .rvc-filter-box .form-control {
        border-radius: 7px;
        border: 1px solid #ddd;
        min-height: 39px;
        box-shadow: none;
    }

    .rvc-filter-box .form-control:focus {
        border-color: var(--rvc-orange);
        box-shadow: 0 0 0 .18rem rgba(230,126,34,.15);
    }

    .rvc-table-head {
        background: #fdf4e7;
        border: 1px solid #e8d5bf;
        border-bottom: none;
        border-radius: 7px 7px 0 0;
        padding: 9px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .rvc-table-head strong {
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #7d3f00;
    }

    .rvc-table-head span {
        font-size: .76rem;
        color: #8b6f4e;
    }

    .rvc-table-box {
        border: 1px solid #e8d5bf;
        border-top: none;
        border-radius: 0 0 7px 7px;
        overflow: hidden;
    }

    #tbl_ventas_cobros {
        width: 100% !important;
    }

    #tbl_ventas_cobros thead th {
        background: #fdf4e7;
        color: #7d3f00;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        border-bottom: 2px solid #f2d49a;
        white-space: nowrap;
        padding: 8px 10px;
        vertical-align: middle;
    }

    #tbl_ventas_cobros tbody td {
        font-size: .83rem;
        vertical-align: middle;
        padding: 8px 10px;
        white-space: nowrap;
    }

    #tbl_ventas_cobros tbody tr:hover {
        background: #fffcf5;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dt-buttons {
        margin-bottom: 8px;
    }

    .dataTables_wrapper { width: 100% !important; }

    @media (max-width: 767px) {
        .rvc-card-body { padding: 12px; }
        .rvc-card-header { padding: 10px 12px; }
    }
</style>
@endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-9">
            <h2><i class="fa fa-line-chart mr-2" style="color:#e67e22"></i>Reporte de Ventas y Cobros</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Reporte de Ventas y Cobros</strong></li>
            </ol>
        </div>
        <div class="col-lg-3 text-right" style="margin-top:1.2rem">
            <button class="btn rvc-btn-pdf mr-1" onclick="exportarPdf()">
                <i class="fa fa-file-pdf-o"></i> PDF
            </button>
            <button class="btn rvc-btn-excel" onclick="exportarExcel()">
                <i class="fa fa-file-excel-o"></i> Excel
            </button>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight rvc-wrapper">

        <div class="row">
            <div class="col-12">
                <div class="rvc-card">
                    <div class="rvc-card-header">
                        <h5><i class="fa fa-credit-card"></i> Ventas, cobros y cartera</h5>
                        <div class="d-flex" style="gap:8px">
                            <button type="button" class="btn rvc-btn-soft" onclick="cargarTabla()">
                                <i class="fa fa-refresh mr-1"></i> Actualizar
                            </button>
                        </div>
                    </div>

                    <div class="rvc-card-body">

                        <div class="rvc-stats">
                            <div class="rvc-stat-pill">
                                <i class="fa fa-filter" style="font-size:.78rem;color:var(--rvc-orange)"></i>
                                <span>Filtros</span>
                                <span class="stat-num">4</span>
                            </div>
                            <div class="rvc-stat-pill green">
                                <i class="fa fa-file-text-o" style="font-size:.78rem;color:#1a7a4e"></i>
                                <span>Exportación</span>
                                <span class="stat-num">2</span>
                            </div>
                            <div class="rvc-stat-pill blue">
                                <i class="fa fa-calendar" style="font-size:.78rem;color:#1d4ed8"></i>
                                <span>Año</span>
                                <span class="stat-num">{{ date('Y') }}</span>
                            </div>
                        </div>

                        <div class="rvc-filter-box">
                            <div class="rvc-section-label">Filtros del reporte</div>
                            <div class="row align-items-end">

                            <div class="col-md-3">
                                <label>Vendedor</label>
                                <select id="fil_vendedor" class="form-control">
                                    <option value="">-- Todos --</option>
                                    @foreach($vendedores as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Cliente</label>
                                <select id="fil_cliente" class="form-control">
                                    <option value="">-- Todos --</option>
                                    @foreach($clientes as $cl)
                                        <option value="{{ $cl->id }}">{{ $cl->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label>Mes</label>
                                <select id="fil_mes" class="form-control">
                                    <option value="">-- Todos --</option>
                                    <option value="1">Enero</option>
                                    <option value="2">Febrero</option>
                                    <option value="3">Marzo</option>
                                    <option value="4">Abril</option>
                                    <option value="5">Mayo</option>
                                    <option value="6">Junio</option>
                                    <option value="7">Julio</option>
                                    <option value="8">Agosto</option>
                                    <option value="9">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label>Año</label>
                                <select id="fil_anio" class="form-control">
                                    <option value="">-- Todos --</option>
                                    @for($y = date('Y'); $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button class="btn rvc-btn-primary w-100" onclick="cargarTabla()">
                                    <i class="fa fa-search"></i> Consultar
                                </button>
                            </div>

                        </div>
                        </div>

                        <div class="rvc-table-head">
                            <strong><i class="fa fa-table mr-1"></i> Resultado del reporte</strong>
                            <span>Vista tabular de facturas, pagos y saldos pendientes.</span>
                        </div>

                        <div class="rvc-table-box">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover mb-0" id="tbl_ventas_cobros">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>MES</th>
                                        <th>VENDEDOR</th>
                                        <th>CLIENTE</th>
                                        <th>FACTURA</th>
                                        <th>OBSERVACIÓN</th>
                                        <th>ORDEN COMPRA</th>
                                        <th>MODO PAGO</th>
                                        <th>ESTADO F01</th>
                                        <th>EXONERADO</th>
                                        <th>GRAVADO</th>
                                        <th>EXENTO</th>
                                        <th>ABONOS</th>
                                        <th>SUBTOTAL</th>
                                        <th>ISV</th>
                                        <th>TOTAL</th>
                                        {{-- Cartera --}}
                                        <th>SALDO PENDIENTE</th>
                                        <th>MONTO PAGADO</th>
                                        <th>MONTO RETENCIÓN</th>
                                        <th>NÚMERO RETENCIÓN</th>
                                        <th>FECHA VENTA</th>
                                        <th>FECHA VCTO.</th>
                                        <th>DÍAS VCTOS.</th>
                                        <th>ESTADO CRÉDITO</th>
                                        <th>FECHA PAGO</th>
                                        <th>FORMA DE PAGO</th>
                                        <th>CUENTA/BANCO</th>
                                        <th>FECHA ENTREGA</th>
                                        <th>RECIBO</th>
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
        </div>

    </div>
</div>

@push('scripts')
<script src="/js/js_proyecto/reportes/ventascobros.js"></script>
@endpush
