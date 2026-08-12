<div>
    @push('styles')
    <style>
    :root {
        --nd-grad: linear-gradient(135deg,#f39c12 0%,#e05a00 100%);
        --nd-orange:#e67e22;
        --nd-red:#c9381b;
        --nd-ink:#593018;
        --nd-line:#e8d5bf;
        --nd-soft:#fdfaf5;
        --nd-radius:8px;
        --nd-shadow:0 2px 8px rgba(0,0,0,.10);
    }
    .nd-workspace { padding:18px 0 28px; }
    .nd-tabs { display:flex; gap:6px; flex-wrap:wrap; padding:0 0 12px; border-bottom:1px solid var(--nd-line); margin-bottom:16px; }
    .nd-tabs .nav-link { border:1px solid var(--nd-line); border-radius:6px; color:#704421; background:#fff; font-size:.8rem; font-weight:700; padding:8px 14px; }
    .nd-tabs .nav-link:hover { color:var(--nd-orange); background:#fffaf2; }
    .nd-tabs .nav-link.active { color:#fff; border-color:transparent; background:var(--nd-grad); box-shadow:0 2px 5px rgba(224,90,0,.24); }
    .nd-tabs .nd-tab-link { margin-left:auto; }
    .nd-card { border:1px solid var(--nd-line); border-radius:var(--nd-radius); box-shadow:var(--nd-shadow); background:#fff; overflow:visible; }
    .nd-card-header { background:var(--nd-grad); padding:12px 18px; border-radius:var(--nd-radius) var(--nd-radius) 0 0; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
    .nd-card-header h5 { margin:0; color:#fff; font-size:.84rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; }
    .nd-card-actions { display:flex; gap:7px; flex-wrap:wrap; }
    .btn-nd-header { background:rgba(255,255,255,.18)!important; color:#fff!important; border:1.5px solid rgba(255,255,255,.55)!important; border-radius:5px!important; font-size:.77rem; font-weight:700; padding:5px 12px; cursor:pointer; }
    .btn-nd-header:hover { background:rgba(255,255,255,.30)!important; }
    .nd-card-body { padding:16px 18px; }
    .nd-filter-bar { padding:8px 16px; background:var(--nd-soft); border-bottom:1px solid var(--nd-line); display:flex; flex-wrap:wrap; gap:6px; }
    .nd-filter-chip { display:inline-flex; align-items:center; gap:4px; background:#fff8ee; border:1px solid #f2d49a; border-radius:12px; padding:2px 9px; color:#7d3f00; font-size:.73rem; }
    .nd-filter-chip button { border:0; background:transparent; color:#bf4d21; font-weight:700; padding:0; cursor:pointer; }
    .nd-note { margin:0; padding:9px 14px; border-bottom:1px solid var(--nd-line); background:#fffaf3; color:#745038; font-size:.78rem; }
    .nd-table { width:100%!important; }
    .nd-table thead th { background:#fdf4e7; color:#7d3f00; font-size:.7rem; font-weight:700; letter-spacing:.03em; text-transform:uppercase; border-bottom:2px solid #f2d49a; white-space:nowrap; padding:8px 9px; vertical-align:middle; }
    .nd-table tbody td { font-size:.8rem; vertical-align:middle; padding:8px 9px; }
    .nd-table tbody tr:hover>td { background:#fffcf5; }
    .dataTables_wrapper.form-inline { display:block!important; }
    .dataTables_wrapper { width:100%!important; }
    body .modal.nd-modal { z-index:10050!important; }
    body.nd-modal-open>.modal-backdrop { z-index:10040!important; }
    .nd-modal .modal-content { border:0; border-radius:var(--nd-radius); box-shadow:0 12px 35px rgba(0,0,0,.24); overflow:hidden; }
    .nd-modal-header { background:var(--nd-grad); color:#fff; padding:14px 18px; border:0; }
    .nd-modal-header .modal-title { color:#fff; font-size:.92rem; font-weight:700; }
    .nd-modal-header .close { color:#fff; opacity:.9; text-shadow:none; }
    .nd-modal .modal-body { background:#fdfaf6; padding:18px 20px; }
    .nd-modal .modal-footer { background:#f8f4ef; border-top:1px solid #ead9c8; padding:10px 20px; }
    .nd-modal .form-group label, .nd-modal label { font-size:.77rem; font-weight:600; color:#555; margin-bottom:4px; }
    .nd-modal .form-control { border-color:#ddd; border-radius:5px; font-size:.82rem; }
    .nd-modal .form-control:focus { border-color:var(--nd-orange); box-shadow:0 0 0 .15rem rgba(230,126,34,.18); }
    .nd-section-label { font-size:.68rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--nd-orange); border-bottom:2px solid #fdebd0; padding-bottom:5px; margin:4px 0 12px; }
    .nd-filter-grid { background:#fff; border:1px solid #ead9c8; border-radius:7px; padding:13px 15px 4px; margin-bottom:14px; }
    .btn-nd-primary { background:var(--nd-grad)!important; border:0!important; color:#fff!important; font-weight:700; }
    .btn-nd-danger { background:linear-gradient(135deg,#e55b32,#bd2d18)!important; border:0!important; color:#fff!important; font-weight:700; }
    .nd-loader { width:42px; height:42px; margin:14px auto 4px; border:4px solid #f8d5b2; border-top-color:var(--nd-orange); border-radius:50%; animation:nd-spin .8s linear infinite; }
    @keyframes nd-spin { to { transform:rotate(360deg); } }
    .select2-container--open { z-index:10060!important; }
    @media(max-width:768px) {
        .nd-tabs .nav-link { flex:1 1 calc(50% - 6px); text-align:center; }
        .nd-tabs .nd-tab-link { margin-left:0; }
        .nd-card-body { padding:12px; }
    }
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2><i class="fa fa-plus-circle mr-2" style="color:var(--nd-orange)"></i>Notas de Débito</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Notas de Débito</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight nd-workspace">
        @if($cai_nd_existencia->existe == 0)
            <div class="alert alert-warning" style="border-left:4px solid #e67e22">
                <strong><i class="fa fa-exclamation-triangle mr-1"></i>CAI no disponible.</strong>
                Cree o habilite un CAI de nota de débito con correlativos disponibles.
                <a href="/ventas/cai" class="alert-link">Gestionar CAI</a>
            </div>
        @endif
            <nav class="nav nd-tabs" role="tablist" aria-label="Funciones de notas de débito">
                <a class="nav-link active" data-toggle="tab" href="#nd-asignar" role="tab"><i class="fa fa-file-invoice mr-1"></i>Asignar nota</a>
                <a class="nav-link" data-toggle="tab" href="#nd-emitidas" role="tab"><i class="fa fa-list-alt mr-1"></i>Notas generadas</a>
                <a class="nav-link" data-toggle="tab" href="#nd-monto" role="tab"><i class="fa fa-money mr-1"></i>Configurar monto</a>
                <a class="nav-link nd-tab-link" href="/nota/debito/lista"><i class="fa fa-building mr-1"></i>Clientes B</a>
                <a class="nav-link" href="/nota/debito/lista/gobierno"><i class="fa fa-university mr-1"></i>Clientes A</a>
            </nav>

            <div class="tab-content">
                <section class="tab-pane fade show active" id="nd-asignar" role="tabpanel">
                    <div class="nd-card">
                        <div class="nd-card-header">
                            <h5><i class="fa fa-file-invoice mr-2"></i>Facturas disponibles para nota de débito</h5>
                            <div class="nd-card-actions">
                                <button type="button" class="btn-nd-header" data-toggle="modal" data-target="#modal_filtros_facturas_nd"><i class="fa fa-filter mr-1"></i>Filtros</button>
                            </div>
                        </div>
                        <p class="nd-note"><i class="fa fa-info-circle mr-1"></i>Seleccione una factura activa y use Acciones para asignar o imprimir su nota de débito.</p>
                        <div class="nd-filter-bar" id="nd_facturas_filtros_bar" style="display:none"></div>
                        <div class="nd-card-body"><div style="overflow-x:auto">
                            <table id="tbl_listar_facturas" class="table table-hover table-bordered nd-table">
                                <thead><tr><th>N° Factura</th><th>Fecha Emisión</th><th>Cliente</th><th>Tipo Pago</th><th>Vencimiento</th><th>Sub Total</th><th>ISV</th><th>Total</th><th>Estado Cobro</th><th>Vendedor</th><th>Nota Débito</th><th>Acciones</th></tr></thead>
                                <tbody></tbody>
                            </table>
                        </div></div>
                    </div>
                </section>

                <section class="tab-pane fade" id="nd-emitidas" role="tabpanel">
                    <div class="nd-card">
                        <div class="nd-card-header">
                            <h5><i class="fa fa-list-alt mr-2"></i>Notas de débito generadas</h5>
                            <button type="button" class="btn-nd-header" data-toggle="modal" data-target="#modal_filtros_notas_nd"><i class="fa fa-filter mr-1"></i>Filtros</button>
                        </div>
                        <div class="nd-filter-bar" id="nd_notas_filtros_bar" style="display:none"></div>
                        <div class="nd-card-body"><div style="overflow-x:auto">
                            <table id="tbl_listar_notas_debito" class="table table-hover table-bordered nd-table">
                                <thead><tr><th>Código</th><th>Factura</th><th>Monto</th><th>Fecha Emisión</th><th>Motivo</th><th>CAI</th><th>Correlativo</th><th>Registrado por</th><th>Estado</th><th>Documento</th><th>Fecha Registro</th></tr></thead>
                                <tbody></tbody>
                            </table>
                        </div></div>
                    </div>
                </section>

                <section class="tab-pane fade" id="nd-monto" role="tabpanel">
                    <div class="nd-card">
                        <div class="nd-card-header">
                            <h5><i class="fa fa-money mr-2"></i>Configuración de monto activo</h5>
                            <button type="button" class="btn-nd-header" data-toggle="modal" data-target="#modal_monto_crear"><i class="fa fa-plus mr-1"></i>Registrar monto</button>
                        </div>
                        <p class="nd-note"><i class="fa fa-info-circle mr-1"></i>El nuevo monto sustituirá al monto activo anterior para futuras notas.</p>
                        <div class="nd-card-body"><div style="overflow-x:auto">
                            <table id="tbl_listar_monto_debito" class="table table-hover table-bordered nd-table">
                                <thead><tr><th>Código</th><th>Monto</th><th>Registrado por</th><th>Fecha creación</th><th>Estado</th></tr></thead>
                                <tbody></tbody>
                            </table>
                        </div></div>
                    </div>
                </section>
            </div>

            <div class="modal fade nd-modal" id="modal_monto_crear" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content">
                    <div class="modal-header nd-modal-header"><h5 class="modal-title"><i class="fa fa-money mr-2"></i>Registrar monto de nota de débito</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                    <div class="modal-body"><form id="montoAddForm" data-parsley-validate>
                        <div class="form-group"><label for="monto">Monto <span class="text-danger">*</span></label><input class="form-control" required min="0.01" type="number" step="0.01" id="monto" name="monto"></div>
                        <div class="form-group mb-0"><label for="descripcion">Descripción <span class="text-danger">*</span></label><textarea class="form-control" required id="descripcion" name="descripcion" rows="3" maxlength="255"></textarea></div>
                    </form></div>
                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button><button type="submit" form="montoAddForm" class="btn btn-nd-primary btn-sm"><i class="fa fa-save mr-1"></i>Guardar monto</button></div>
                </div></div>
            </div>

            <div class="modal fade nd-modal" id="modal_nota_debito_crear" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content">
                    <div class="modal-header nd-modal-header"><h5 class="modal-title"><i class="fa fa-plus-circle mr-2"></i>Asignar nota de débito</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                    <div class="modal-body"><form id="ndAddForm" data-parsley-validate>
                        <div class="row"><div class="col-md-6"><div class="form-group"><label>Factura</label><input class="form-control" readonly required type="number" id="factura_id" name="factura_id"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Monto asignado</label><input class="form-control" readonly required type="number" step="0.01" id="monto_" name="monto_"></div></div></div>
                        <input type="hidden" id="montoNotaDebito_id" name="montoNotaDebito_id">
                        <div class="form-group"><label for="fechaEmision">Fecha de emisión <span class="text-danger">*</span></label><input class="form-control" required type="date" id="fechaEmision" name="fechaEmision" value="{{ date('Y-m-d') }}"></div>
                        <div class="form-group mb-0"><label for="motivoDescripcion">Motivo o descripción <span class="text-danger">*</span></label><textarea class="form-control" required id="motivoDescripcion" name="motivoDescripcion" rows="4" maxlength="500"></textarea></div>
                    </form></div>
                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button><button type="submit" form="ndAddForm" class="btn btn-nd-primary btn-sm"><i class="fa fa-save mr-1"></i>Crear nota</button></div>
                </div></div>
            </div>

            @include('livewire.nota-debito.partials.filtros-moderno')

            <div class="modal fade nd-modal" id="modalSpinnerLoading" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm" role="document"><div class="modal-content"><div class="modal-body text-center py-4"><h5 style="color:var(--nd-ink)">Procesando operación</h5><div class="nd-loader"></div><small class="text-muted">Espere un momento...</small></div></div></div>
            </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/js_proyecto/nota-debito/nota-debito-moderno.js') }}"></script>
    @endpush
</div>