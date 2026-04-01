<div>
{{-- Loading overlay --}}
<div id="tbl_loading_overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.78); z-index:9000; text-align:center; padding-top:18%;">
    <i class="fa fa-spinner fa-spin fa-3x" style="color:#1ab394;"></i>
    <p class="mt-3" style="color:#555; font-size:1rem;">Cargando reporte...</p>
</div>

@push('styles')
<style>
    .btn-export-pdf   { background:#c0392b; color:#fff; border:none; }
    .btn-export-pdf:hover   { background:#a93226; color:#fff; }
    .btn-export-excel { background:#1e7e34; color:#fff; border:none; }
    .btn-export-excel:hover { background:#155724; color:#fff; }
    .badge-vigente  { background:#1ab394; color:#fff; }
    .badge-vencida  { background:#e74c3c; color:#fff; }
    .badge-cancelada{ background:#27ae60; color:#fff; }
    .badge-contado  { background:#2980b9; color:#fff; }
    td.readonly-col { background:#f4f4f4 !important; color:#555; font-style:italic; }
</style>
@endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-9">
            <h2>Reporte de Ventas y Cobros</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Reporte de Ventas y Cobros</strong></li>
            </ol>
        </div>
        <div class="col-lg-3 text-right" style="margin-top:1.2rem">
            <button class="btn btn-export-pdf mr-1" onclick="exportarPdf()">
                <i class="fa fa-file-pdf-o"></i> PDF
            </button>
            <button class="btn btn-export-excel" onclick="exportarExcel()">
                <i class="fa fa-file-excel-o"></i> Excel
            </button>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- ═══════ FILTROS ═══════ --}}
        <div class="row mb-3">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row align-items-end">

                            <div class="col-md-3">
                                <label class="col-form-label">Vendedor</label>
                                <select id="fil_vendedor" class="form-control">
                                    <option value="">-- Todos --</option>
                                    @foreach($vendedores as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="col-form-label">Cliente</label>
                                <select id="fil_cliente" class="form-control">
                                    <option value="">-- Todos --</option>
                                    @foreach($clientes as $cl)
                                        <option value="{{ $cl->id }}">{{ $cl->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="col-form-label">Mes</label>
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
                                <label class="col-form-label">Año</label>
                                <select id="fil_anio" class="form-control">
                                    <option value="">-- Todos --</option>
                                    @for($y = date('Y'); $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button class="btn btn-success w-100" onclick="cargarTabla()">
                                    <i class="fa fa-search"></i> Consultar
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════ TABLA ═══════ --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="tbl_ventas_cobros" style="font-size:12px">
                                <thead>
                                    <tr>
                                        {{-- Identificación --}}
                                        <th>#</th>
                                        <th>MES</th>
                                        <th>VENDEDOR</th>
                                        <th>CLIENTE</th>
                                        <th>FACTURA</th>
                                        <th>OBSERVACIÓN</th>
                                        <th>ORDEN COMPRA</th>
                                        {{-- Clasificación fiscal --}}
                                        <th>MODO PAGO</th>
                                        <th>ESTADO F01</th>
                                        {{-- Montos --}}
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

@push('scripts')
<script src="/js/js_proyecto/reportes/ventascobros.js"></script>
@endpush
