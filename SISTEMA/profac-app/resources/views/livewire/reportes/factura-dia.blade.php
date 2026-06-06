<div>

@push('styles')
<style>
/* ── Variables PROFAC ─────────────────────────────────────────── */
:root {
    --pf-grad:       linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
    --pf-grad-hover: linear-gradient(135deg, #e08e0b 0%, #c04e00 100%);
    --pf-orange:  #e67e22;
    --pf-radius:  8px;
    --pf-shadow:  0 2px 8px rgba(0,0,0,.10);
}

/* ── Página ───────────────────────────────────────────────────── */
.facdia-wrap { padding: 0; }
.wrapper-content {
    padding-left: 0 !important;
    padding-right: 0 !important;
}
.wrapper-content > .row {
    margin-left: 0 !important;
    margin-right: 0 !important;
}
.wrapper-content > .row > [class*="col-"] {
    padding-left: 0 !important;
    padding-right: 0 !important;
}

/* ── Card principal ───────────────────────────────────────────── */
.facdia-card {
    border: 1px solid #e8d5bf;
    border-radius: var(--pf-radius);
    box-shadow: var(--pf-shadow);
    background: #fff;
    overflow: visible;
}
.facdia-card-header {
    background: var(--pf-grad);
    padding: 12px 20px;
    border-radius: var(--pf-radius) var(--pf-radius) 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
}
.facdia-card-header h5 {
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
.facdia-card-body { padding: 16px 20px; }

/* ── Filtros ──────────────────────────────────────────────────── */
.facdia-filter-bar {
    background: linear-gradient(90deg, rgba(236,64,27,.05) 0%, rgba(241,85,51,.03) 100%);
    border: 1px solid rgba(230,126,34,.22);
    border-radius: 6px;
    padding: 14px 18px;
    margin-bottom: 18px;
}
.facdia-filter-bar label {
    font-size: .78rem;
    font-weight: 700;
    color: #6d4c1e;
    margin-bottom: 4px;
    display: block;
}
.facdia-filter-bar .form-control {
    font-size: .82rem;
    border-color: #e8d5bf;
    border-radius: 5px;
}
.facdia-filter-bar .form-control:focus {
    border-color: #e67e22;
    box-shadow: 0 0 0 .18rem rgba(230,126,34,.2);
}

/* ── Botón consultar ──────────────────────────────────────────── */
.btn-facdia-consult {
    background: var(--pf-grad) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    font-size: .82rem;
    padding: 7px 20px;
    transition: background .18s, box-shadow .18s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}
.btn-facdia-consult:hover {
    background: var(--pf-grad-hover) !important;
    box-shadow: 0 3px 10px rgba(230,126,34,.30);
}

/* ── Tabla ────────────────────────────────────────────────────── */
#tbl_facdia { width: 100% !important; }
#tbl_facdia thead th {
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
#tbl_facdia tbody td {
    font-size: .82rem;
    vertical-align: middle;
    padding: 7px 10px;
}
#tbl_facdia tbody tr:hover { background: #fffcf5; }

/* ── Nota ─────────────────────────────────────────────────────── */
.facdia-note {
    font-size: .78rem;
    color: #7d5b3a;
    background: #fffdf6;
    border-left: 3px solid #e67e22;
    padding: 6px 12px;
    border-radius: 0 5px 5px 0;
    margin-bottom: 16px;
}

/* ── Responsive ───────────────────────────────────────────────── */
@media (max-width: 767px) {
    .facdia-card-body { padding: 10px; }
    .facdia-card-header { padding: 10px 12px; }
}
</style>
@endpush

    {{-- Encabezado de página --}}
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-12">
            <h2><i class="fa fa-file-invoice-dollar mr-2" style="color:#e67e22"></i>Reporte de Facturación Diaria</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Facturación del Día</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-12">
                <div class="facdia-card">

                    {{-- Header --}}
                    <div class="facdia-card-header">
                        <h5><i class="fa fa-file-invoice-dollar"></i> Facturación por Rango de Fechas</h5>
                    </div>

                    <div class="facdia-card-body">

                        {{-- Nota informativa --}}
                        <div class="facdia-note">
                            <i class="fa fa-info-circle mr-1"></i>
                            <strong>Nota:</strong> Se requiere seleccionar un rango de fechas para mostrar la información.
                        </div>

                        {{-- Filtros --}}
                        <div class="facdia-filter-bar">
                            <div class="row align-items-end">
                                <div class="col-sm-5 col-md-4 mb-2 mb-sm-0">
                                    <label for="fecha_inicio">
                                        <i class="fa fa-calendar mr-1"></i>Fecha de inicio
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input class="form-control form-control-sm" type="date"
                                           id="fecha_inicio" name="fecha_inicio" value="{{ date('Y-m-01') }}">
                                </div>
                                <div class="col-sm-5 col-md-4 mb-2 mb-sm-0">
                                    <label for="fecha_final">
                                        <i class="fa fa-calendar-check mr-1"></i>Fecha final
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input class="form-control form-control-sm" type="date"
                                           id="fecha_final" name="fecha_final" value="{{ date('Y-m-t') }}">
                                </div>
                                <div class="col-sm-2 col-md-4 d-flex align-items-end">
                                    <button class="btn btn-facdia-consult" onclick="cargaConsulta()">
                                        <i class="fa fa-search"></i> Consultar
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Tabla --}}
                        <div class="table-responsive">
                            <table id="tbl_facdia" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Mes</th>
                                        <th>N° Factura</th>
                                        <th>Cliente</th>
                                        <th>Asesor Comercial</th>
                                        <th>Tele Asesor</th>
                                        <th>Subtotal</th>
                                        <th>Impuesto de Venta</th>
                                        <th>Total</th>
                                        <th>Tipo</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                    </div>{{-- /card-body --}}
                </div>{{-- /card --}}
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/js_proyecto/factura-dia/factura-dia.js') }}"></script>
@endpush

