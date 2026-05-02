@push('styles')
<style>
/* -------------------------------------------------------
   Configuracion de Comisiones - Estilos
------------------------------------------------------- */
@keyframes cc-fadeInDown {
    from { opacity:0; transform:translateY(-16px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes cc-fadeInUp {
    from { opacity:0; transform:translateY(14px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes cc-fadeIn {
    from { opacity:0; } to { opacity:1; }
}
@keyframes cc-slideInLeft {
    from { opacity:0; transform:translateX(-18px); }
    to   { opacity:1; transform:translateX(0); }
}
@keyframes cc-badge-pop {
    0%   { transform:scale(.7); opacity:0; }
    70%  { transform:scale(1.15); }
    100% { transform:scale(1); opacity:1; }
}

/* -- HEADER CARD -- */
.cc-header-card {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    border-radius: 14px;
    padding: 22px 28px;
    margin: 22px 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    box-shadow: 0 8px 28px rgba(243,156,18,.30);
    animation: cc-fadeInDown .45s ease both;
    position: relative;
    overflow: hidden;
}
.cc-header-card::before {
    content:'';
    position:absolute; top:-40px; right:-40px;
    width:160px; height:160px;
    background: rgba(255,255,255,.08);
    border-radius: 50%;
    pointer-events: none;
}
.cc-header-card::after {
    content:'';
    position:absolute; bottom:-50px; left:30%;
    width:120px; height:120px;
    background: rgba(255,255,255,.05);
    border-radius: 50%;
    pointer-events: none;
}
.cc-header-left { display:flex; align-items:center; gap:16px; }
.cc-header-icon {
    width: 48px; height: 48px;
    background: rgba(255,255,255,.22);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; color: #fff;
    flex-shrink: 0;
}
.cc-header-text h4 {
    color: #fff; font-weight: 800; font-size: 16px;
    margin: 0 0 3px;
    text-shadow: 0 1px 3px rgba(0,0,0,.12);
}
.cc-header-text p {
    color: rgba(255,255,255,.82); font-size: 12px; margin: 0 0 8px;
}
.cc-btn-new {
    background: rgba(255,255,255,.2) !important;
    backdrop-filter: blur(4px);
    color: #fff !important;
    border: 1.5px solid rgba(255,255,255,.5) !important;
    border-radius: 10px;
    padding: 9px 20px;
    font-size: 13px; font-weight: 700;
    display: inline-flex; align-items: center; gap: 8px;
    cursor: pointer;
    transition: background .2s, transform .18s, box-shadow .18s;
    text-decoration: none !important;
    white-space: nowrap;
    flex-shrink: 0;
    position: relative; z-index: 1;
}
.cc-btn-new:hover {
    background: rgba(255,255,255,.3) !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,.15);
    color: #fff !important;
}
.cc-btn-new:active { transform: translateY(0); }

/* -- PANEL TABLE -- */
.cc-panel {
    background: #fff;
    border-radius: 0 0 12px 12px;
    box-shadow: 0 4px 18px rgba(0,0,0,.07);
    border: 1px solid #e2e8f0;
    border-top: none;
    padding: 22px;
    animation: cc-fadeInUp .4s .1s ease both;
}
.cc-panel-title {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: #fff;
    border-radius: 10px 10px 0 0;
    font-size: 13px; font-weight: 800;
    display: flex; align-items: center; gap: 10px;
    margin: -22px -22px 18px -22px;
    padding: 14px 20px;
    position: relative; overflow: hidden;
}
.cc-panel-title::after {
    content:'';
    position:absolute; inset:0;
    background: linear-gradient(90deg, transparent 60%, rgba(255,255,255,.07) 100%);
    pointer-events:none;
}
.cc-panel-icon {
    width: 30px; height: 30px;
    background: rgba(255,255,255,.22);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; color: #fff; flex-shrink: 0;
}

/* -- TABLE STYLES -- */
.cc-panel table.dataTable thead th {
    background: #f5f7fb;
    color: #4a5568;
    font-size: 11px; font-weight: 800;
    text-transform: uppercase; letter-spacing: .4px;
    border-top: none !important;
    border-bottom: 2px solid #e2e8f0 !important;
    white-space: nowrap; padding: 10px 12px;
}
.cc-panel table.dataTable tbody tr {
    transition: background .15s, transform .1s;
}
.cc-panel table.dataTable tbody tr:nth-child(even) { background: #f7f9fc !important; }
.cc-panel table.dataTable tbody tr:nth-child(odd)  { background: #ffffff !important; }
.cc-panel table.dataTable tbody tr:hover {
    background: #fff8ec !important;
    transform: scale(1.002);
    box-shadow: 0 2px 10px rgba(243,156,18,.12);
    position: relative; z-index: 1;
}
.cc-panel table.dataTable tbody td {
    font-size: 12px; color: #2d3748;
    padding: 9px 12px; vertical-align: middle;
    border-top: 1px solid #edf2f7 !important;
}
.cc-panel table.dataTable { border: 1.5px solid #dde2ec !important; }
.cc-panel .dataTables_wrapper .dataTables_filter input,
.cc-panel .dataTables_wrapper .dataTables_length select {
    border: 1.5px solid #dde2ec; border-radius: 8px;
    padding: 5px 10px; font-size: 12px;
    transition: border-color .2s, box-shadow .2s;
}
.cc-panel .dataTables_wrapper .dataTables_filter input:focus {
    border-color: #f39c12; outline: none;
    box-shadow: 0 0 0 3px rgba(243,156,18,.12);
}

/* -- BADGES -- */
.cc-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 10px; font-weight: 800; letter-spacing: .3px;
    white-space: nowrap; animation: cc-badge-pop .3s ease both;
}
.cc-badge.activo  { background: #e6faf5; color: #0fa37a; border: 1px solid #a7f3d0; }
.cc-badge.inactivo { background: #fff0f0; color: #e74c3c; border: 1px solid #fecaca; }

/* -- MODAL -- */
.cc-modal .modal-content {
    border: none; border-radius: 16px;
    box-shadow: 0 24px 64px rgba(0,0,0,.18);
    overflow: visible;
    animation: cc-fadeInUp .3s ease both;
    position: relative;
}
/* El modal necesita overflow visible para que el dropdown de Select2 no quede recortado */
.cc-modal.modal { overflow-y: auto !important; }
#modalParamComision .modal-dialog { overflow: visible !important; }
.cc-modal .modal-header {
    border-radius: 16px 16px 0 0;
}
.cc-modal .modal-footer {
    border-radius: 0 0 16px 16px;
}
.cc-modal .modal-header {
    background: linear-gradient(135deg, #f39c12, #e67e22);
    padding: 18px 24px; border-bottom: none;
    position: relative; overflow: hidden;
    border-radius: 16px 16px 0 0;
}
.cc-modal .modal-header::after {
    content:'';
    position:absolute; top:-30px; right:-30px;
    width:100px; height:100px;
    background: rgba(255,255,255,.08); border-radius:50%;
}
.cc-modal .modal-title {
    font-size: 15px; font-weight: 800; color: #fff;
    display: flex; align-items: center; gap: 10px; margin: 0;
}
.cc-modal .modal-title i { font-size: 18px; }
.cc-modal .close {
    color: rgba(255,255,255,.85) !important; opacity: 1 !important;
    font-size: 22px; transition: transform .15s;
}
.cc-modal .close:hover { color: #fff !important; transform: rotate(90deg); }
.cc-modal .modal-body { padding: 26px; background: #fafbfc; overflow: visible; }
.cc-modal .modal-footer {
    background: #f4f6f9; border-top: 1px solid #e2e8f0;
    padding: 14px 24px;
    border-radius: 0 0 16px 16px;
}

/* -- FORM FIELDS -- */
.cc-form-group { margin-bottom: 18px; }
.cc-form-group label {
    font-size: 11px; font-weight: 700; color: #718096;
    text-transform: uppercase; letter-spacing: .4px;
    display: block; margin-bottom: 6px;
}
.cc-form-group label .req { color: #e74c3c; margin-left: 2px; }
.cc-form-group .form-control {
    border: 1.5px solid #dde2ec; border-radius: 9px;
    font-size: 13px; padding: 9px 13px; color: #2d3748;
    background: #fff; width: 100%;
    transition: border-color .18s, box-shadow .18s;
}
.cc-form-group .form-control:focus {
    border-color: #f39c12;
    box-shadow: 0 0 0 3px rgba(243,156,18,.15);
    outline: none;
}
.cc-form-group small { font-size: 11px; color: #a0aec0; margin-top: 4px; display: block; }

.cc-divider {
    height: 1px; background: linear-gradient(90deg, #e2e8f0, transparent);
    margin: 4px 0 20px;
}

/* -- INFO BANNER -- */
.cc-info-banner {
    background: linear-gradient(135deg, #fff8e1, #fef3c7);
    border: 1px solid #fde68a; border-radius: 10px;
    padding: 12px 16px; font-size: 12px; color: #92400e;
    display: flex; align-items: flex-start; gap: 10px;
    margin-bottom: 20px;
    animation: cc-slideInLeft .3s ease both;
}
.cc-info-banner i { font-size: 15px; margin-top: 1px; flex-shrink: 0; }

/* -- BUTTONS -- */
.cc-btn-save {
    background: linear-gradient(135deg, #f39c12, #e67e22) !important;
    color: #fff !important; border: none !important; border-radius: 10px;
    padding: 10px 26px; font-size: 13px; font-weight: 800;
    cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
    transition: transform .18s, box-shadow .18s, filter .18s;
    box-shadow: 0 4px 14px rgba(243,156,18,.4);
    text-shadow: none;
}
.cc-btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(243,156,18,.5);
    filter: brightness(1.05); color: #fff !important;
}
.cc-btn-save:active { transform: translateY(0); }
.cc-btn-save:disabled { opacity: .6; cursor: not-allowed; transform: none; }

.cc-btn-cancel {
    background: #fff; color: #718096;
    border: 1.5px solid #dde2ec; border-radius: 10px;
    padding: 10px 22px; font-size: 13px; font-weight: 600;
    cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
    transition: border-color .18s, color .18s, background .18s;
}
.cc-btn-cancel:hover { border-color: #f39c12; color: #e67e22; background: #fff8e1; }

/* -- STATS PILLS -- */
.cc-stats-row {
    display: flex; gap: 10px; flex-wrap: wrap;
}
.cc-stat-pill {
    background: rgba(255,255,255,.22);
    border: 1px solid rgba(255,255,255,.38);
    border-radius: 20px; padding: 4px 12px;
    font-size: 11px; font-weight: 700; color: #fff;
    display: flex; align-items: center; gap: 6px;
    white-space: nowrap;
}
.cc-stat-pill i { font-size: 11px; opacity: .85; }

/* -- RESPONSIVE -- */
@media (max-width: 575px) {
    .cc-header-card { padding: 16px 18px; flex-direction: column; align-items: flex-start; }
    .cc-header-text h4 { font-size: 14px; }
    .cc-btn-new { width: 100%; justify-content: center; }
    .cc-modal .modal-body { padding: 16px; }
    .cc-modal .modal-footer { padding: 12px 16px; flex-direction: column; }
    .cc-btn-save, .cc-btn-cancel { width: 100%; justify-content: center; }
}
/* Select2 dentro del modal */
.cc-modal .select2-container { width: 100% !important; }
.cc-modal .select2-container .select2-selection--single {
    height: 40px !important;
    border: 1.5px solid #dde2ec !important;
    border-radius: 9px !important;
    background: #fff !important;
    display: flex; align-items: center;
    transition: border-color .18s, box-shadow .18s;
}
.cc-modal .select2-container--open .select2-selection--single,
.cc-modal .select2-container .select2-selection--single:focus {
    border-color: #f39c12 !important;
    box-shadow: 0 0 0 3px rgba(243,156,18,.15) !important;
    outline: none !important;
}
.cc-modal .select2-selection--single .select2-selection__rendered {
    color: #2d3748 !important;
    font-size: 13px !important;
    line-height: 38px !important;
    padding-left: 13px !important;
    padding-right: 30px !important;
}
.cc-modal .select2-selection--single .select2-selection__arrow {
    height: 38px !important; right: 8px !important;
}
/* Dropdown flotante — z-index mayor al modal Inspinia (2040) */
.cc-select2-drop.select2-dropdown {
    border: 1.5px solid #f39c12 !important;
    border-radius: 9px !important;
    box-shadow: 0 8px 32px rgba(243,156,18,.25) !important;
    font-size: 13px !important;
    z-index: 99999 !important;
    overflow: hidden;
}
.cc-select2-drop .select2-search--dropdown input {
    border: 1.5px solid #dde2ec;
    border-radius: 7px;
    padding: 6px 10px;
    font-size: 13px;
}
.cc-select2-drop .select2-search--dropdown input:focus {
    border-color: #f39c12;
    box-shadow: 0 0 0 3px rgba(243,156,18,.12);
    outline: none;
}
.cc-select2-drop .select2-results__option {
    padding: 8px 14px;
    font-size: 13px;
    color: #2d3748;
}
.cc-select2-drop .select2-results__option--highlighted {
    background: linear-gradient(135deg, #f39c12, #e67e22) !important;
    color: #fff !important;
}
/* ===================== SELECT2 (BOOTSTRAP 4) ===================== */
.select2-container--bootstrap4 .select2-selection--single {
    height: 38px;
    padding: 4px 10px;
    border-radius: .35rem;
    border: 1px solid #ced4da;
    font-size: .9rem;
}

.select2-container--bootstrap4 .select2-selection__rendered {
    line-height: 30px;
}

.select2-container--bootstrap4 .select2-selection__arrow {
    height: 36px;
    right: 6px;
}

.select2-container--bootstrap4 .select2-dropdown {
    max-height: 220px;
    overflow-y: auto;
}

/* ===================== BOTONES ===================== */
.btn {
    font-weight: 500;
}

.btn-success,
.btn-primary {
    padding: .35rem .9rem;
}

/* ===================== FILTROS / FORM INLINE ===================== */
.filtro-container {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-wrap: wrap;
}

.filtro-select {
    min-width: 200px;
    height: 38px;
    font-size: .9rem;
    flex: 1 1 220px;
}

#btnDescargar {
    height: 38px;
}

/* ===================== RESPONSIVE ===================== */
@media (max-width: 576px) {
    form.d-flex {
        flex-direction: column;
    }

    form.d-flex > * {
        margin-bottom: .5rem;
    }

    .filtro-container {
        flex-direction: column;
    }

    #btnDescargar {
        width: 100%;
    }
}

@media (min-width: 992px) {
    .filtro-select {
        min-width: 240px;
        flex: 1 1 240px;
    }
}
</style>
@endpush

{{-- ===== PAGE HEADER ===== --}}
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><i class="fa fa-percent text-warning mr-2"></i>Comisiones</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item">Comisiones</li>
            <li class="breadcrumb-item active"><strong>Configuración de Parámetros</strong></li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content pb-4">

    {{-- ===== HEADER CARD ===== --}}
    <div class="cc-header-card">
        <div class="cc-header-left">
            <div class="cc-header-icon">
                <i class="fa fa-sliders"></i>
            </div>
            <div class="cc-header-text">
                <h4>Parametrización de Comisiones</h4>
                <p>Configure el % de comisión por rol de usuario y categoría de cliente</p>
                <div class="cc-stats-row">
                    <span class="cc-stat-pill"><i class="fa fa-users"></i> Por rol de usuario</span>
                    <span class="cc-stat-pill"><i class="fa fa-tag"></i> Por categoría de cliente</span>
                    <span class="cc-stat-pill"><i class="fa fa-percent"></i> Porcentaje sobre precio de venta</span>
                </div>
            </div>
        </div>
        <button type="button" class="cc-btn-new" data-toggle="modal" data-target="#modalParamComision" onclick="abrirModalNuevo()">
            <i class="fa fa-plus-circle"></i> Nuevo Parámetro
        </button>
    </div>

    {{-- ===== TABLA ===== --}}
    <div class="cc-panel" style="margin-top:0;">
        <div class="cc-panel-title">
            <div class="cc-panel-icon"><i class="fa fa-list-ul"></i></div>
            Parámetros de Comisión Registrados
        </div>
        <div class="table-responsive">
            <table id="tbl_listaParametroComision"
                   class="table table-sm table-hover w-100"
                   style="border-collapse:collapse;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Comisión</th>
                        <th>%</th>
                        <th>Rol de Usuario</th>
                        <th>Categoría Cliente</th>
                        <th>Registrado por</th>
                        <th>Fecha</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- ===== MODAL CREAR / EDITAR ===== --}}
    <div class="modal cc-modal fade" id="modalParamComision" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="ccModalTitle">
                        <i class="fa fa-plus-circle"></i>
                        <span id="ccModalTitleText">Nuevo Parámetro de Comisión</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">

                    <div class="cc-info-banner">
                        <i class="fa fa-info-circle"></i>
                        <span>Defina el porcentaje de comisión que recibirá cada rol de usuario según la categoría del cliente en las ventas cobradas.</span>
                    </div>

                    <form id="paramComisionForm" novalidate>
                        <input type="hidden" id="param_comision_id" name="param_comision_id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="cc-form-group">
                                    <label><i class="fa fa-tag mr-1"></i>Título de Comisión <span class="req">*</span></label>
                                    <input type="text"
                                           class="form-control"
                                           id="nombre_comescala"
                                           name="nombre_comescala"
                                           maxlength="150"
                                           placeholder="Ej: Comisión Mayoristas Vendedor"
                                           required>
                                    <small>Nombre identificador de la comisión</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="cc-form-group">
                                    <label><i class="fa fa-pencil mr-1"></i>Descripción <span class="req">*</span></label>
                                    <input type="text"
                                           class="form-control"
                                           id="descripcion_comescala"
                                           name="descripcion_comescala"
                                           maxlength="250"
                                           placeholder="Descripción breve..."
                                           required>
                                    <small>Breve descripción para referencia interna</small>
                                </div>
                            </div>
                        </div>

                        <div class="cc-divider"></div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="cc-form-group">
                                    <label><i class="fa fa-users mr-1"></i>Categoría de Cliente <span class="req">*</span></label>
                                    <select id="categoria_cliente_id"
                                            name="categoria_cliente_id"
                                            class="form-control"
                                            data-url="{{ route('clientes.categorias.escala') }}"
                                            required>
                                        <option value="">-- Seleccione una categoria --</option>
                                    </select>
                                    <small>Categoría de cliente a la que aplica la comisión</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="cc-form-group">
                                    <label><i class="fa fa-id-badge mr-1"></i>Rol Asociado <span class="req">*</span></label>
                                    <select id="rol_id"
                                            name="rol_id"
                                            class="form-control"
                                            data-url="{{ route('comision.configuracion.rol') }}"
                                            required>
                                        <option value="">-- Seleccione un rol --</option>
                                    </select>
                                    <small>Rol de usuario que recibirá la comisión</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="cc-form-group">
                                    <label><i class="fa fa-percent mr-1"></i>Porcentaje de Comisión <span class="req">*</span></label>
                                    <div style="position:relative;">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               max="100"
                                               class="form-control"
                                               id="porcentaje_comision"
                                               name="porcentaje_comision"
                                               placeholder="0.00"
                                               required
                                               style="padding-right: 42px;">
                                        <span style="position:absolute;right:13px;top:50%;transform:translateY(-50%);color:#f39c12;font-weight:800;font-size:15px;pointer-events:none;">%</span>
                                    </div>
                                    <small>Ej: 5, 7.5, 10 — sobre el precio de venta del producto</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="cc-form-group">
                                    <label><i class="fa fa-eye mr-1"></i>Vista Previa</label>
                                    <div id="ccPreviewBox" style="
                                        background: linear-gradient(135deg,#fff8e1,#fef3c7);
                                        border: 1.5px dashed #f59e0b;
                                        border-radius: 9px; padding: 11px 14px;
                                        font-size: 12px; color: #92400e; min-height: 56px;
                                        display:flex; align-items:center; gap:8px;
                                    ">
                                        <i class="fa fa-info-circle" style="color:#f59e0b;font-size:14px;flex-shrink:0;"></i>
                                        <span id="ccPreviewText" style="font-weight:600;">Complete los campos para ver la vista previa</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>

                <div class="modal-footer" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                    <button type="button" class="cc-btn-cancel" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="cc-btn-save" id="btn_guardar_parametro_comision" onclick="$('#paramComisionForm').submit();">
                        <i class="fa fa-save"></i>
                        <span id="ccBtnSaveText">Guardar Parámetro</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/js_proyecto/comisiones/Escalado/gestionComision.js') }}"></script>
<script>
// -- Preview dinamico --
function actualizarPreview() {
    var cat = $('#categoria_cliente_id option:selected').text();
    var rol = $('#rol_id option:selected').text();
    var pct = parseFloat($('#porcentaje_comision').val()) || 0;
    var txt = document.getElementById('ccPreviewText');
    var hascat = cat && cat.indexOf('Seleccione') === -1 && cat.trim() !== '';
    var hasrol = rol && rol.indexOf('Seleccione') === -1 && rol.trim() !== '';
    if (hascat && hasrol && pct > 0) {
        txt.innerHTML = 'Los usuarios con rol <strong>' + rol + '</strong> ganar&aacute;n <strong style="color:#e67e22;">' + pct + '%</strong> sobre cada producto vendido a clientes de la categor&iacute;a <strong>' + cat + '</strong>.';
    } else {
        txt.innerHTML = 'Complete los campos para ver la vista previa';
    }
}

$('#categoria_cliente_id, #rol_id, #porcentaje_comision').on('change input', actualizarPreview);

// -- Select2 con clase de dropdown personalizada --
function initModalSelects() {
    ['#categoria_cliente_id', '#rol_id'].forEach(function(id) {
        var $el = $(id);
        try { if ($el.data('select2')) $el.select2('destroy'); } catch(e) {}
        $el.select2({
            dropdownParent: $('#modalParamComision'),
            dropdownCssClass: 'cc-select2-drop',
            width: '100%',
            language: { noResults: function() { return 'Sin resultados'; } }
        });
        // Actualizar preview cuando cambia la selección
        $el.off('change.preview').on('change.preview', actualizarPreview);
        // MutationObserver: re-renderizar Select2 cuando AJAX cargue las opciones
        if ($el.data('_mo')) { try { $el.data('_mo').disconnect(); } catch(e) {} }
        var mo = new MutationObserver(function() {
            $el.trigger('change.select2');
            actualizarPreview();
        });
        mo.observe($el[0], { childList: true });
        $el.data('_mo', mo);
    });
}

function destroyModalSelects() {
    ['#categoria_cliente_id', '#rol_id'].forEach(function(id) {
        var $el = $(id);
        try { if ($el.data('_mo')) { $el.data('_mo').disconnect(); $el.data('_mo', null); } } catch(e) {}
        try { if ($el.data('select2')) { $el.off('change.preview'); $el.select2('destroy'); } } catch(e) {}
    });
}

// -- Abrir modal en modo nuevo --
function abrirModalNuevo() {
    document.getElementById('ccModalTitleText').textContent = 'Nuevo Par\u00e1metro de Comisi\u00f3n';
    document.getElementById('ccBtnSaveText').textContent = 'Guardar Par\u00e1metro';
    document.querySelector('#ccModalTitle i').className = 'fa fa-plus-circle';
}

// Parchar editarParametro para actualizar el titulo del modal
$(document).ready(function() {
    var _orig = window.editarParametro;
    window.editarParametro = function(id) {
        document.getElementById('ccModalTitleText').textContent = 'Editar Par\u00e1metro de Comisi\u00f3n';
        document.getElementById('ccBtnSaveText').textContent = 'Actualizar Par\u00e1metro';
        document.querySelector('#ccModalTitle i').className = 'fa fa-edit';
        if (typeof _orig === 'function') _orig(id);
    };
});

// Inicializar Select2 cuando se abre el modal (despues de que gestionComision.js registre su handler)
$('#modalParamComision').on('shown.bs.modal', function() {
    initModalSelects();
    actualizarPreview();
});

// Limpiar Select2 y preview al cerrar
$('#modalParamComision').on('hidden.bs.modal', function() {
    destroyModalSelects();
    document.getElementById('ccPreviewText').innerHTML = 'Complete los campos para ver la vista previa';
});
</script>
@endpush
