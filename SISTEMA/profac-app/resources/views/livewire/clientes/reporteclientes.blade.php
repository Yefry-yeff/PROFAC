<div>

@push('styles')
<style>
/* ── Variables PROFAC ──────────────────────────────────── */
:root {
    --pf-grad:       linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
    --pf-grad-hover: linear-gradient(135deg, #e08e0b 0%, #c04e00 100%);
    --pf-orange:     #e67e22;
    --pf-radius:     8px;
    --pf-shadow:     0 2px 8px rgba(0,0,0,.10);
}

/* ── Card principal ────────────────────────────────────── */
.rep-cli-card {
    border: 1px solid #e8d5bf;
    border-radius: var(--pf-radius);
    box-shadow: var(--pf-shadow);
    background: #fff;
    overflow: visible;
}
.rep-cli-card-header {
    background: var(--pf-grad);
    padding: 12px 20px;
    border-radius: var(--pf-radius) var(--pf-radius) 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
}
.rep-cli-card-header h5 {
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
.rep-cli-card-body { padding: 16px 20px; }

/* ── Botones en header ─────────────────────────────────── */
.btn-rep-header {
    background: rgba(255,255,255,.18) !important;
    color: #fff !important;
    border: 1.5px solid rgba(255,255,255,.5) !important;
    border-radius: 5px !important;
    font-weight: 600 !important;
    font-size: .78rem;
    padding: 5px 14px;
    transition: background .18s;
    white-space: nowrap;
}
.btn-rep-header:hover { background: rgba(255,255,255,.30) !important; color: #fff !important; }
.btn-rep-header.pdf   { background: rgba(192,57,43,.55) !important; border-color: rgba(255,150,130,.6) !important; }
.btn-rep-header.pdf:hover { background: rgba(192,57,43,.80) !important; }
.btn-rep-header.excel { background: rgba(30,126,52,.55) !important; border-color: rgba(130,220,130,.6) !important; }
.btn-rep-header.excel:hover { background: rgba(30,126,52,.80) !important; }

/* ── Card de filtros ───────────────────────────────────── */
.rep-filtros-card {
    border: 1px solid #e8d5bf;
    border-radius: var(--pf-radius);
    box-shadow: var(--pf-shadow);
    background: #fff;
    overflow: hidden;
    margin-bottom: 18px;
}
.rep-filtros-header {
    background: #fdf4e7;
    border-bottom: 1px solid #f2d49a;
    padding: 9px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: .78rem;
    font-weight: 700;
    color: #7d3f00;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.rep-filtros-body { padding: 14px 16px; }
.rep-filtros-body label {
    font-size: .75rem;
    font-weight: 700;
    color: #7d3f00;
    text-transform: uppercase;
    letter-spacing: .03em;
    margin-bottom: 4px;
}
.rep-filtros-body .form-control {
    font-size: .83rem;
    border-color: #e8d5bf;
    border-radius: 6px;
}
.rep-filtros-body .form-control:focus {
    border-color: var(--pf-orange);
    box-shadow: 0 0 0 .18rem rgba(230,126,34,.2);
}
.btn-rep-consultar {
    background: var(--pf-grad) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 6px !important;
    font-weight: 700 !important;
    font-size: .83rem;
    padding: 7px 20px;
    transition: opacity .18s;
    white-space: nowrap;
    width: 100%;
}
.btn-rep-consultar:hover { opacity: .88; }

/* ── Tabs ──────────────────────────────────────────────── */
.rep-tabs .nav-link {
    font-size: .82rem;
    font-weight: 600;
    color: #6c757d;
    border-radius: 6px 6px 0 0;
    padding: 8px 16px;
    border: 1px solid transparent;
    transition: color .15s;
}
.rep-tabs .nav-link:hover { color: var(--pf-orange); }
.rep-tabs .nav-link.active {
    color: var(--pf-orange);
    border-color: #dee2e6 #dee2e6 #fff;
    font-weight: 700;
}
.rep-tabs .nav-link i { margin-right: 5px; }

/* ── Cabeceras de tabla ─────────────────────────────────── */
#tbl_rep_general thead th,
#tbl_rep_sincredito thead th,
#tbl_rep_gobierno thead th {
    background: #fdf4e7 !important;
    color: #7d3f00 !important;
    font-size: .70rem !important;
    font-weight: 700 !important;
    letter-spacing: .04em !important;
    text-transform: uppercase !important;
    border-bottom: 2px solid #f2d49a !important;
    white-space: nowrap;
    padding: 7px 9px !important;
    vertical-align: middle !important;
}
#tbl_rep_general tbody td,
#tbl_rep_sincredito tbody td,
#tbl_rep_gobierno tbody td {
    font-size: .82rem;
    vertical-align: middle;
    padding: 7px 9px;
}
#tbl_rep_general tbody tr:hover,
#tbl_rep_sincredito tbody tr:hover,
#tbl_rep_gobierno tbody tr:hover { background: #fffcf5 !important; }

/* ── Badges de documentos ──────────────────────────────── */
.x-mark {
    display: inline-flex; align-items: center; gap: 3px;
    background: #d4edda; color: #155724;
    border: 1px solid #c3e6cb; border-radius: 10px;
    padding: 1px 8px; font-size: .72rem; font-weight: 700;
    white-space: nowrap;
}
.fisico-mark {
    display: inline-flex; align-items: center; gap: 3px;
    background: #fff3cd; color: #856404;
    border: 1px solid #ffc107; border-radius: 10px;
    padding: 1px 8px; font-size: .72rem; font-weight: 700;
    white-space: nowrap;
}
.sol-mark {
    display: inline-flex; align-items: center; gap: 3px;
    background: #f8d7da; color: #721c24;
    border: 1px solid #f5c6cb; border-radius: 10px;
    padding: 1px 8px; font-size: .70rem; font-weight: 600;
    white-space: nowrap;
}
.na-mark { color: #adb5bd; font-size: .80rem; }

/* ── Responsive ────────────────────────────────────────── */
@media (max-width: 767px) {
    .rep-cli-card-body { padding: 10px; }
    .rep-cli-card-header { padding: 10px 12px; }
}
</style>
@endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-12">
            <h2><i class="fa fa-users mr-2" style="color:#e67e22"></i>Reporte de Clientes</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Reporte de Clientes</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- Card de filtros --}}
        <div class="rep-filtros-card">
            <div class="rep-filtros-header">
                <i class="fa fa-filter"></i> Filtros de Consulta
            </div>
            <div class="rep-filtros-body">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label>Vendedor</label>
                        <select id="fil_vendedor" class="form-control">
                            <option value="">— Todos los vendedores —</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label>Estado cliente</label>
                        <select id="fil_estado" class="form-control">
                            <option value="">— Todos —</option>
                            <option value="1">Activo</option>
                            <option value="2">Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-rep-consultar" onclick="cargarTablas()">
                            <i class="fa fa-search mr-1"></i> Consultar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card principal con tabs --}}
        <div class="rep-cli-card">

            <div class="rep-cli-card-header">
                <h5><i class="fa fa-table"></i> Resultado del Reporte</h5>
                <div class="d-flex" style="gap:8px">
                    <button class="btn btn-rep-header pdf" onclick="exportarPdf()">
                        <i class="fa fa-file-pdf-o mr-1"></i> PDF
                    </button>
                    <button class="btn btn-rep-header excel" onclick="exportarExcel()">
                        <i class="fa fa-file-excel-o mr-1"></i> Excel
                    </button>
                </div>
            </div>

            <div class="rep-cli-card-body">

                <ul class="nav nav-tabs rep-tabs" id="tabsReporte" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab-general">
                            <i class="fa fa-users"></i> Clientes Generales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-sincredito">
                            <i class="fa fa-ban"></i> Sin Crédito
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-gobierno">
                            <i class="fa fa-building"></i> Gobierno
                        </a>
                    </li>
                </ul>

                <div class="tab-content mt-3">

                    {{-- Hoja 1: General --}}
                    <div class="tab-pane fade show active" id="tab-general">
                        <div class="table-responsive">
                            <table id="tbl_rep_general" class="table table-striped table-bordered table-hover nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>AÑO INGRESO</th>
                                        <th>VENDEDOR</th>
                                        <th>CLIENTE</th>
                                        <th>COD.</th>
                                        <th>SOL. CRÉDITO</th>
                                        <th>COND. CRÉDITO</th>
                                        <th>ESCRITURA</th>
                                        <th>DNI REP.</th>
                                        <th>RTN</th>
                                        <th>PERMISO OP.</th>
                                        <th>AÑO OPERAC.</th>
                                        <th>CROQUIS</th>
                                        <th>REF. BANC.</th>
                                        <th>REF. COMERC.</th>
                                        <th>REFERENCIA</th>
                                        <th>T. RELACIÓN</th>
                                        <th>T. CRÉDITO</th>
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
                            <table id="tbl_rep_sincredito" class="table table-striped table-bordered table-hover nowrap" style="width:100%">
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

                    {{-- Hoja 3: Gobierno --}}
                    <div class="tab-pane fade" id="tab-gobierno">
                        <div class="table-responsive">
                            <table id="tbl_rep_gobierno" class="table table-striped table-bordered table-hover nowrap" style="width:100%">
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
            </div>{{-- /card-body --}}
        </div>{{-- /rep-cli-card --}}

    </div>

@push('scripts')
<script src="{{ asset('js/js_proyecto/clientes/reporteclientes.js') }}"></script>
@endpush
</div>
