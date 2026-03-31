<div>
@push('styles')
<style>
    .btn-export-pdf  { background:#c0392b; color:#fff; border:none; }
    .btn-export-pdf:hover  { background:#a93226; color:#fff; }
    .btn-export-excel{ background:#1e7e34; color:#fff; border:none; }
    .btn-export-excel:hover{ background:#155724; color:#fff; }
    .tab-label-badge { font-size:.75rem; vertical-align:middle; }
    .x-mark  { color:#1ab394; font-weight:700; }
    .sol-mark { color:#e74c3c; font-size:.8rem; }
    .na-mark  { color:#95a5a6; font-size:.8rem; }
</style>
@endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-9">
            <h2>Reporte de Clientes</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Reporte de Clientes</strong></li>
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

        {{-- Filtros --}}
        <div class="row mb-3">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="col-form-label">Vendedor</label>
                                <select id="fil_vendedor" class="form-control">
                                    <option value="">-- Todos los vendedores --</option>
                                    @foreach($vendedores as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="col-form-label">Estado cliente</label>
                                <select id="fil_estado" class="form-control">
                                    <option value="">-- Todos --</option>
                                    <option value="1">Activo</option>
                                    <option value="2">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-success w-100" onclick="cargarTablas()">
                                    <i class="fa fa-search"></i> Consultar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">

                        <ul class="nav nav-tabs" id="tabsReporte" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-general-tab" data-toggle="tab" href="#tab-general">
                                    <i class="fa fa-users"></i> Clientes Generales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-sincredito-tab" data-toggle="tab" href="#tab-sincredito">
                                    <i class="fa fa-ban"></i> Sin Crédito
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-gobierno-tab" data-toggle="tab" href="#tab-gobierno">
                                    <i class="fa fa-building"></i> Corporativo (B)
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3">

                            {{-- Hoja 1: General --}}
                            <div class="tab-pane fade show active" id="tab-general">
                                <div class="table-responsive">
                                    <table id="tbl_rep_general" class="table table-striped table-bordered table-hover nowrap" style="font-size:11px">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>AÑO INGRESO</th>
                                                <th>VENDEDOR</th>
                                                <th>CLIENTE</th>
                                                <th>COD.</th>
                                                <th>SOL. CREDITO</th>
                                                <th>COND. CREDITO</th>
                                                <th>ESCRITURA</th>
                                                <th>DNI REP.</th>
                                                <th>RTN</th>
                                                <th>PERMISO OP.</th>
                                                <th>AÑO OPERAC.</th>
                                                <th>CROQUIS</th>
                                                <th>REF. BANC.</th>
                                                <th>REF. COMERC.</th>
                                                <th>REFERENCIA</th>
                                                <th>T. RELACION</th>
                                                <th>T. CREDITO</th>
                                                <th>LÍM. CRÉDITO</th>
                                                <th>MÉTODO PAGO</th>
                                                <th>CONFIRMACIÓN</th>
                                                <th>OBS. REF.</th>
                                                <th>FECHA VAL. REF.</th>
                                                <th>REALIZÓ</th>
                                                <th>LETRA CAMBIO</th>
                                                <th>AVAL SOLID.</th>
                                                <th>CONTRATO ARR.</th>
                                                <th>FOTOS EST.</th>
                                                <th>ESTADO</th>
                                                <th>MONTO CRÉD.</th>
                                                <th>PLAZO CRÉD.</th>
                                                <th>OBSERVACIONES</th>
                                                <th>AUTORIZADO GER.</th>
                                                <th>FECHA NOTIF.</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Hoja 2: Sin crédito --}}
                            <div class="tab-pane fade" id="tab-sincredito">
                                <div class="table-responsive">
                                    <table id="tbl_rep_sincredito" class="table table-striped table-bordered table-hover nowrap" style="font-size:11px">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>VENDEDOR</th>
                                                <th>CLIENTE</th>
                                                <th>CÓDIGO</th>
                                                <th>ESTADO</th>
                                                <th>OBSERVACIONES</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Hoja 3: Corporativo B --}}
                            <div class="tab-pane fade" id="tab-gobierno">
                                <div class="table-responsive">
                                    <table id="tbl_rep_gobierno" class="table table-striped table-bordered table-hover nowrap" style="font-size:11px">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>VENDEDOR</th>
                                                <th>CLIENTE</th>
                                                <th>CÓDIGO</th>
                                                <th>PLAZO CRÉDITO</th>
                                                <th>ESTADO</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                        </div>{{-- /tab-content --}}
                    </div>
                </div>
            </div>
        </div>

    </div>

@push('scripts')
<script src="{{ asset('js/js_proyecto/clientes/reporteclientes.js') }}"></script>
@endpush
</div>
