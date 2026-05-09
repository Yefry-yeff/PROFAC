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

    {{-- ===== SECCIÓN GESTIÓN MASIVA (card unificada con tabs) ===== --}}
    <div style="background:#fff;border-radius:16px;margin-bottom:20px;
                box-shadow:0 4px 24px rgba(0,0,0,.07);overflow:hidden;border:1px solid #edf0f7;">

        {{-- Header del card --}}
        <div style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);
                    padding:18px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,.12);
                            display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.2);">
                    <i class="fa fa-table" style="color:#fff;font-size:16px;"></i>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:800;color:#fff;letter-spacing:.2px;">Gestión Masiva de Comisiones</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.6);margin-top:2px;">
                        Descargue, edite y cargue porcentajes de comisión en lote
                    </div>
                </div>
            </div>

            {{-- Tab switcher pill --}}
            <div style="display:flex;background:rgba(255,255,255,.1);border-radius:10px;padding:4px;gap:3px;border:1px solid rgba(255,255,255,.15);">
                <button id="tab_btn_global" onclick="switchTab('global')"
                    style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:7px;
                           border:none;cursor:pointer;font-size:12px;font-weight:700;transition:all .2s;
                           background:linear-gradient(135deg,#f39c12,#e67e22);color:#fff;
                           box-shadow:0 2px 8px rgba(243,156,18,.4);">
                    <i class="fa fa-globe"></i> Carga Global
                </button>
                <button id="tab_btn_selectiva" onclick="switchTab('selectiva')"
                    style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:7px;
                           border:none;cursor:pointer;font-size:12px;font-weight:700;transition:all .2s;
                           background:transparent;color:rgba(255,255,255,.7);">
                    <i class="fa fa-filter"></i> Carga Selectiva
                </button>
            </div>
        </div>

        {{-- ── TAB GLOBAL ── --}}
        <div id="tab_global" style="padding:28px 28px 24px;">
            <div style="display:flex;gap:20px;align-items:stretch;flex-wrap:wrap;">

                {{-- Paso 1 --}}
                <div style="flex:1;min-width:220px;border:1.5px solid #e8f0fe;border-radius:12px;
                            padding:20px;background:linear-gradient(135deg,#f8f9ff,#eef1fd);
                            display:flex;flex-direction:column;align-items:center;text-align:center;gap:12px;">
                    <div style="width:52px;height:52px;border-radius:14px;
                                background:linear-gradient(135deg,#667eea,#764ba2);
                                display:flex;align-items:center;justify-content:center;
                                box-shadow:0 4px 12px rgba(102,126,234,.35);">
                        <i class="fa fa-download" style="color:#fff;font-size:20px;"></i>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:800;color:#3730a3;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">
                            Paso 1
                        </div>
                        <div style="font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px;">Descargar Plantilla</div>
                        <div style="font-size:11px;color:#64748b;line-height:1.5;">
                            Descargue el Excel con <strong>todas</strong> las combinaciones de rol + categorías.
                        </div>
                    </div>
                    <a href="{{ route('comision.configuracion.plantilla.masiva') }}"
                       style="margin-top:auto;display:inline-flex;align-items:center;gap:7px;padding:9px 20px;
                              border-radius:9px;border:2px solid #667eea;background:#fff;
                              color:#667eea;font-size:12px;font-weight:700;text-decoration:none;
                              transition:all .2s;box-shadow:0 2px 6px rgba(102,126,234,.15);"
                       onmouseover="this.style.background='#667eea';this.style.color='#fff'"
                       onmouseout="this.style.background='#fff';this.style.color='#667eea'">
                        <i class="fa fa-download"></i> Descargar
                    </a>
                </div>

                {{-- Flecha --}}
                <div style="display:flex;align-items:center;justify-content:center;flex-shrink:0;padding:0 4px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#f1f5f9;
                                display:flex;align-items:center;justify-content:center;border:1.5px solid #e2e8f0;">
                        <i class="fa fa-arrow-right" style="color:#94a3b8;font-size:13px;"></i>
                    </div>
                </div>

                {{-- Paso 2 --}}
                <div style="flex:1;min-width:220px;border:1.5px solid #fef3c7;border-radius:12px;
                            padding:20px;background:linear-gradient(135deg,#fffdf5,#fef9e7);
                            display:flex;flex-direction:column;align-items:center;text-align:center;gap:12px;">
                    <div style="width:52px;height:52px;border-radius:14px;
                                background:linear-gradient(135deg,#f59e0b,#d97706);
                                display:flex;align-items:center;justify-content:center;
                                box-shadow:0 4px 12px rgba(245,158,11,.35);">
                        <i class="fa fa-pencil" style="color:#fff;font-size:20px;"></i>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:800;color:#92400e;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">
                            Paso 2
                        </div>
                        <div style="font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px;">Completar Porcentajes</div>
                        <div style="font-size:11px;color:#64748b;line-height:1.5;">
                            Edite la columna <strong>% Comisión</strong> en amarillo y guarde el archivo.
                        </div>
                    </div>
                    <div style="margin-top:auto;display:inline-flex;align-items:center;gap:6px;padding:9px 20px;
                                border-radius:9px;background:#fef3c7;border:1.5px dashed #f59e0b;
                                color:#92400e;font-size:11px;font-weight:600;">
                        <i class="fa fa-info-circle"></i> Edite el Excel descargado
                    </div>
                </div>

                {{-- Flecha --}}
                <div style="display:flex;align-items:center;justify-content:center;flex-shrink:0;padding:0 4px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#f1f5f9;
                                display:flex;align-items:center;justify-content:center;border:1.5px solid #e2e8f0;">
                        <i class="fa fa-arrow-right" style="color:#94a3b8;font-size:13px;"></i>
                    </div>
                </div>

                {{-- Paso 3 --}}
                <div style="flex:1;min-width:220px;border:1.5px solid #d1fae5;border-radius:12px;
                            padding:20px;background:linear-gradient(135deg,#f0fdf4,#ecfdf5);
                            display:flex;flex-direction:column;align-items:center;text-align:center;gap:12px;">
                    <div style="width:52px;height:52px;border-radius:14px;
                                background:linear-gradient(135deg,#10b981,#059669);
                                display:flex;align-items:center;justify-content:center;
                                box-shadow:0 4px 12px rgba(16,185,129,.35);">
                        <i class="fa fa-upload" style="color:#fff;font-size:20px;"></i>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:800;color:#065f46;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">
                            Paso 3
                        </div>
                        <div style="font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px;">Subir y Aplicar</div>
                        <div style="font-size:11px;color:#64748b;line-height:1.5;">
                            Suba el archivo editado. Los cambios se aplican <strong>automáticamente</strong>.
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('input_carga_masiva').click()"
                        style="margin-top:auto;display:inline-flex;align-items:center;gap:7px;padding:9px 20px;
                               border-radius:9px;border:none;cursor:pointer;font-size:12px;font-weight:700;
                               background:linear-gradient(135deg,#10b981,#059669);color:#fff;
                               box-shadow:0 3px 10px rgba(16,185,129,.35);transition:opacity .2s;"
                        onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        <i class="fa fa-upload"></i> Subir Plantilla
                    </button>
                    <input type="file" id="input_carga_masiva" accept=".xlsx,.xls" style="display:none"
                           onchange="procesarCargaMasiva(this)">
                </div>

            </div>
        </div>

        {{-- ── TAB SELECTIVA ── --}}
        <div id="tab_selectiva" style="display:none;">

            {{-- Sub-header selectiva --}}
            <div style="padding:14px 24px;background:#fafbff;border-bottom:1px solid #edf0f7;
                        display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <div style="font-size:12px;color:#64748b;">
                    <i class="fa fa-info-circle mr-1" style="color:#667eea;"></i>
                    Marque las categorías que desea incluir — deje en blanco para <strong>incluir todas</strong>.
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="button" onclick="descargarPlantillaFiltrada()"
                        style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;
                               border-radius:8px;border:1.5px solid #667eea;background:#fff;
                               color:#667eea;font-size:12px;font-weight:700;cursor:pointer;transition:all .2s;"
                        onmouseover="this.style.background='#667eea';this.style.color='#fff'"
                        onmouseout="this.style.background='#fff';this.style.color='#667eea'">
                        <i class="fa fa-download"></i> Descargar Plantilla Filtrada
                    </button>
                    <button type="button" onclick="document.getElementById('input_carga_filtrada').click()"
                        style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;
                               border-radius:8px;border:none;cursor:pointer;font-size:12px;font-weight:700;
                               background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;
                               box-shadow:0 2px 8px rgba(102,126,234,.3);transition:opacity .2s;"
                        onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        <i class="fa fa-upload"></i> Subir Plantilla Filtrada
                    </button>
                    <input type="file" id="input_carga_filtrada" accept=".xlsx,.xls" style="display:none"
                           onchange="iniciarCargaFiltrada(this)">
                </div>
            </div>

            {{-- Grid checklists --}}
            <div style="display:flex;gap:0;">

                {{-- Columna Categoría Cliente --}}
                <div style="flex:1;padding:20px;border-right:1px solid #edf0f7;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:28px;height:28px;border-radius:8px;
                                        background:linear-gradient(135deg,#667eea,#764ba2);
                                        display:flex;align-items:center;justify-content:center;">
                                <i class="fa fa-users" style="color:#fff;font-size:11px;"></i>
                            </div>
                            <span style="font-size:12px;font-weight:800;color:#1e293b;text-transform:uppercase;letter-spacing:.4px;">
                                Categoría de Cliente
                            </span>
                        </div>
                        <div style="display:flex;gap:5px;">
                            <button type="button" onclick="checklistSelTodos('chk_cat_cli', true)"
                                style="font-size:10px;padding:3px 9px;border-radius:6px;border:none;
                                       background:#667eea;color:#fff;cursor:pointer;font-weight:700;">
                                <i class="fa fa-check-square-o mr-1"></i>Todos
                            </button>
                            <button type="button" onclick="checklistSelTodos('chk_cat_cli', false)"
                                style="font-size:10px;padding:3px 9px;border-radius:6px;border:1px solid #cbd5e0;
                                       background:#f8fafc;color:#64748b;cursor:pointer;font-weight:600;">
                                <i class="fa fa-square-o mr-1"></i>Ninguno
                            </button>
                        </div>
                    </div>
                    <div id="lista_chk_cat_cli"
                         style="max-height:220px;overflow-y:auto;border:1.5px solid #e8f0fe;
                                border-radius:10px;padding:6px 8px;background:#fafbff;
                                scrollbar-width:thin;scrollbar-color:#c7d2fe #f1f5f9;">
                        <div style="text-align:center;color:#a0aec0;font-size:12px;padding:16px 0;">
                            <i class="fa fa-spinner fa-spin"></i> Cargando...
                        </div>
                    </div>
                </div>

                {{-- Columna Categoría Precio --}}
                <div style="flex:1;padding:20px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:28px;height:28px;border-radius:8px;
                                        background:linear-gradient(135deg,#f59e0b,#d97706);
                                        display:flex;align-items:center;justify-content:center;">
                                <i class="fa fa-tag" style="color:#fff;font-size:11px;"></i>
                            </div>
                            <span style="font-size:12px;font-weight:800;color:#1e293b;text-transform:uppercase;letter-spacing:.4px;">
                                Categoría de Precio
                            </span>
                        </div>
                        <div style="display:flex;gap:5px;">
                            <button type="button" onclick="checklistSelTodos('chk_cat_precio', true)"
                                style="font-size:10px;padding:3px 9px;border-radius:6px;border:none;
                                       background:#f59e0b;color:#fff;cursor:pointer;font-weight:700;">
                                <i class="fa fa-check-square-o mr-1"></i>Todos
                            </button>
                            <button type="button" onclick="checklistSelTodos('chk_cat_precio', false)"
                                style="font-size:10px;padding:3px 9px;border-radius:6px;border:1px solid #cbd5e0;
                                       background:#f8fafc;color:#64748b;cursor:pointer;font-weight:600;">
                                <i class="fa fa-square-o mr-1"></i>Ninguno
                            </button>
                        </div>
                    </div>
                    <div id="lista_chk_cat_precio"
                         style="max-height:220px;overflow-y:auto;border:1.5px solid #fef3c7;
                                border-radius:10px;padding:6px 8px;background:#fffdf5;
                                scrollbar-width:thin;scrollbar-color:#fde68a #fffdf5;">
                        <div style="text-align:center;color:#a0aec0;font-size:12px;padding:16px 0;">
                            Marque categorías de cliente para ver las de precio disponibles.
                        </div>
                    </div>
                </div>

            </div>
        </div>

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
                        <th>Categoría Precio</th>
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
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
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
                        <span>Seleccione el <strong>rol</strong> y la <strong>categoría de cliente</strong>. El sistema cargará todas las categorías de precio activas para esa categoría — asigne el % a cada una.</span>
                    </div>

                    <form id="paramComisionForm" novalidate>
                        <input type="hidden" id="param_comision_id" name="param_comision_id">

                        {{-- Fila 1: Título + Rol --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="cc-form-group">
                                    <label><i class="fa fa-tag mr-1"></i>Título de la Configuración <span class="req">*</span></label>
                                    <input type="text" class="form-control" id="nombre_comescala" name="nombre_comescala"
                                           maxlength="150" placeholder="Ej: Comisión Mayoristas — Vendedor" required>
                                    <small>Nombre identificador del grupo de comisiones</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="cc-form-group">
                                    <label><i class="fa fa-id-badge mr-1"></i>Rol Asociado <span class="req">*</span></label>
                                    <select id="rol_id" name="rol_id" class="form-control"
                                            data-url="{{ route('comision.configuracion.rol') }}" required>
                                        <option value="">-- Seleccione un rol --</option>
                                    </select>
                                    <small>Rol de usuario que recibirá la comisión</small>
                                </div>
                            </div>
                        </div>

                        {{-- Fila 2: Categoría Cliente --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="cc-form-group">
                                    <label><i class="fa fa-users mr-1"></i>Categoría de Cliente <span class="req">*</span></label>
                                    <select id="categoria_cliente_id" name="categoria_cliente_id" class="form-control"
                                            data-url="{{ route('clientes.categorias.escala') }}" required>
                                        <option value="">-- Seleccione una categoría --</option>
                                    </select>
                                    <small>Categoría de cliente a la que aplica la comisión</small>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex align-items-end pb-3">
                                <div id="ccSelectHint" style="font-size:12px;color:#92400e;padding:8px 12px;
                                     background:#fff8e1;border:1px solid #fde68a;border-radius:8px;width:100%;">
                                    <i class="fa fa-arrow-up mr-1" style="color:#f59e0b;"></i>
                                    Seleccione rol y categoría de cliente para cargar las categorías de precio.
                                </div>
                            </div>
                        </div>

                        <div class="cc-divider"></div>

                        {{-- Tabla dinámica de categorías de precio --}}
                        <div id="seccionCategoriasPrecio" style="display:none;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                                <div style="font-size:13px;font-weight:800;color:#4a5568;">
                                    <i class="fa fa-percent text-warning mr-1"></i>
                                    Porcentaje de comisión por Categoría de Precio
                                </div>
                                <span id="ccCatPreCount" class="badge" style="background:#f39c12;color:#fff;font-size:11px;padding:4px 10px;border-radius:20px;"></span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="tbl_categorias_precio" style="font-size:13px;">
                                    <thead style="background:#f5f7fb;">
                                        <tr>
                                            <th style="width:50%;padding:10px 14px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#4a5568;">
                                                Categoría de Precio
                                            </th>
                                            <th style="width:30%;padding:10px 14px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#4a5568;">
                                                % Comisión
                                            </th>
                                            <th style="width:20%;padding:10px 14px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#4a5568;text-align:center;">
                                                Estado
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_categorias_precio">
                                        {{-- Se llena dinámicamente --}}
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted">
                                <i class="fa fa-info-circle mr-1"></i>
                                Deje en blanco las categorías que no apliquen. Solo se guardan las que tengan % &gt; 0.
                            </small>
                        </div>

                        <div id="seccionCargandoPrecios" style="display:none;text-align:center;padding:20px;">
                            <i class="fa fa-spinner fa-spin fa-2x text-warning"></i>
                            <p style="margin-top:8px;font-size:13px;color:#718096;">Cargando categorías de precio...</p>
                        </div>

                    </form>
                </div>

                <div class="modal-footer" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                    <button type="button" class="cc-btn-cancel" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="cc-btn-save" id="btn_guardar_parametro_comision" onclick="$('#paramComisionForm').submit();" style="display:none;">
                        <i class="fa fa-save"></i>
                        <span id="ccBtnSaveText">Guardar Parámetros</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== MODAL EDITAR % INDIVIDUAL ===== --}}
    <div class="modal cc-modal fade" id="modalEditarPct" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-edit mr-1"></i> Editar Porcentaje
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_pct_id">
                    <div class="cc-form-group mb-0">
                        <label style="font-size:12px;font-weight:700;color:#718096;">
                            <i class="fa fa-percent mr-1"></i> Nuevo Porcentaje <span class="req">*</span>
                        </label>
                        <div style="position:relative;">
                            <input type="number" step="0.01" min="0" max="100"
                                   class="form-control" id="edit_pct_valor" placeholder="0.00"
                                   style="padding-right:42px;">
                            <span style="position:absolute;right:13px;top:50%;transform:translateY(-50%);
                                         color:#f39c12;font-weight:800;font-size:15px;pointer-events:none;">%</span>
                        </div>
                        <small id="edit_pct_label" style="font-size:11px;color:#a0aec0;margin-top:6px;display:block;"></small>
                    </div>
                </div>
                <div class="modal-footer" style="justify-content:space-between;">
                    <button type="button" class="cc-btn-cancel" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="cc-btn-save" id="btn_confirmar_editar_pct" onclick="confirmarEditarPct()">
                        <i class="fa fa-save mr-1"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/js_proyecto/comisiones/Escalado/gestionComision.js') }}"></script>
<script>
// ── Select2 ──────────────────────────────────────────────────
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
        var mo = new MutationObserver(function() { $el.trigger('change.select2'); });
        if ($el.data('_mo')) { try { $el.data('_mo').disconnect(); } catch(e) {} }
        mo.observe($el[0], { childList: true });
        $el.data('_mo', mo);
    });
}

function destroyModalSelects() {
    ['#categoria_cliente_id', '#rol_id'].forEach(function(id) {
        var $el = $(id);
        try { if ($el.data('_mo')) { $el.data('_mo').disconnect(); $el.data('_mo', null); } } catch(e) {}
        try { if ($el.data('select2')) $el.select2('destroy'); } catch(e) {}
    });
}

// ── Cargar categorías de precio cuando cambien rol o categoría cliente ──
function intentarCargarCategoriasPrecio() {
    var rolId  = $('#rol_id').val();
    var catId  = $('#categoria_cliente_id').val();
    if (!rolId || !catId) return;

    $('#seccionCategoriasPrecio').hide();
    $('#seccionCargandoPrecios').show();
    $('#btn_guardar_parametro_comision').hide();

    var excludeId = $('#param_comision_id').val() || 0;

    $.getJSON('/comisiones/configuracion/categorias-precio', {
        cliente_categoria_escala_id: catId,
        rol_id: rolId,
        exclude_id: excludeId
    })
    .done(function(res) {
        renderFilasCategorias(res.categorias || []);
    })
    .fail(function() {
        Swal.fire({ icon:'error', title:'Error', text:'No se pudieron cargar las categorías de precio.' });
    })
    .always(function() {
        $('#seccionCargandoPrecios').hide();
    });
}

function renderFilasCategorias(categorias) {
    var $tbody = $('#tbody_categorias_precio');
    $tbody.empty();

    if (!categorias.length) {
        $tbody.append('<tr><td colspan="3" class="text-center text-muted py-3">' +
            '<i class="fa fa-exclamation-circle mr-1"></i>' +
            'No hay categorías de precio activas para esta categoría de cliente.</td></tr>');
        $('#seccionCategoriasPrecio').show();
        $('#ccCatPreCount').text('0 categorías');
        return;
    }

    categorias.forEach(function(cat) {
        var tieneConfig = cat.porcentaje_comision !== null && cat.porcentaje_comision !== undefined;
        var pct = tieneConfig ? parseFloat(cat.porcentaje_comision) : '';
        var estadoBadge = tieneConfig
            ? '<span class="badge" style="background:#e6faf5;color:#0fa37a;border:1px solid #a7f3d0;font-size:10px;padding:3px 8px;border-radius:12px;"><i class="fa fa-check-circle mr-1"></i>Configurado</span>'
            : '<span class="badge" style="background:#fff0f0;color:#e74c3c;border:1px solid #fecaca;font-size:10px;padding:3px 8px;border-radius:12px;"><i class="fa fa-minus-circle mr-1"></i>Sin configurar</span>';

        var row = '<tr data-cat-id="' + cat.id + '" data-ce-id="' + (cat.comision_escala_id || '') + '">' +
            '<td style="vertical-align:middle;padding:10px 14px;font-weight:600;color:#2d3748;">' +
                '<i class="fa fa-tag text-warning mr-2" style="font-size:11px;"></i>' + cat.nombre +
            '</td>' +
            '<td style="vertical-align:middle;padding:8px 14px;">' +
                '<div style="position:relative;">' +
                    '<input type="number" step="0.01" min="0" max="100"' +
                    '       class="form-control form-control-sm pct-input"' +
                    '       name="porcentaje_' + cat.id + '"' +
                    '       placeholder="Ej: 5.00"' +
                    '       value="' + pct + '"' +
                    '       style="padding-right:36px;font-weight:600;"' +
                    '       oninput="actualizarEstadoFila(this)">' +
                    '<span style="position:absolute;right:10px;top:50%;transform:translateY(-50%);' +
                    '             color:#f39c12;font-weight:800;font-size:13px;pointer-events:none;">%</span>' +
                '</div>' +
            '</td>' +
            '<td style="vertical-align:middle;padding:8px 14px;text-align:center;" class="td-estado">' +
                estadoBadge +
            '</td>' +
        '</tr>';
        $tbody.append(row);
    });

    $('#seccionCategoriasPrecio').show();
    $('#btn_guardar_parametro_comision').show();
    $('#ccCatPreCount').text(categorias.length + ' categoría' + (categorias.length !== 1 ? 's' : ''));
}

function actualizarEstadoFila(input) {
    var $td = $(input).closest('tr').find('.td-estado');
    var val  = parseFloat($(input).val());
    if (val > 0) {
        $td.html('<span class="badge" style="background:#e6faf5;color:#0fa37a;border:1px solid #a7f3d0;font-size:10px;padding:3px 8px;border-radius:12px;"><i class="fa fa-check-circle mr-1"></i>Se guardará</span>');
    } else {
        $td.html('<span class="badge" style="background:#f4f6f9;color:#a0aec0;border:1px solid #e2e8f0;font-size:10px;padding:3px 8px;border-radius:12px;"><i class="fa fa-minus mr-1"></i>Se omitirá</span>');
    }
}

// ── Eventos de cambio ──
$(document).on('change', '#rol_id, #categoria_cliente_id', intentarCargarCategoriasPrecio);

// ── Abrir modal nuevo ──
function abrirModalNuevo() {
    document.getElementById('ccModalTitleText').textContent = 'Nuevo Parámetro de Comisión';
    document.getElementById('ccBtnSaveText').textContent    = 'Guardar Parámetros';
    document.querySelector('#ccModalTitle i').className = 'fa fa-plus-circle';
    $('#seccionCategoriasPrecio').hide();
    $('#seccionCargandoPrecios').hide();
    $('#btn_guardar_parametro_comision').hide();
    $('#ccSelectHint').show();
}

// ── Inicializar al abrir modal ──
$('#modalParamComision').on('shown.bs.modal', function() {
    initModalSelects();
});

// ── Limpiar al cerrar ──
$('#modalParamComision').on('hidden.bs.modal', function() {
    destroyModalSelects();
    $('#paramComisionForm')[0].reset();
    $('#param_comision_id').val('');
    $('#tbody_categorias_precio').empty();
    $('#seccionCategoriasPrecio').hide();
    $('#seccionCargandoPrecios').hide();
    $('#btn_guardar_parametro_comision').hide();
    $('#ccSelectHint').show();
    try { $('#paramComisionForm').parsley().reset(); } catch(e) {}
});

// ── Modal editar porcentaje individual ──
function editarParametro(id) {
    $.getJSON('/parametro-comision/' + id, function(data) {
        $('#edit_pct_id').val(data.id);
        $('#edit_pct_valor').val(parseFloat(data.porcentaje_comision));
        $('#edit_pct_label').text('Registro ID #' + data.id);
        $('#modalEditarPct').modal('show');
    }).fail(function() {
        Swal.fire({ icon:'error', title:'Error', text:'No se pudo cargar el registro.' });
    });
}

function confirmarEditarPct() {
    var id  = $('#edit_pct_id').val();
    var pct = $('#edit_pct_valor').val();

    if (!pct || parseFloat(pct) <= 0) {
        Swal.fire({ icon:'warning', title:'Porcentaje requerido', text:'Ingrese un % mayor a 0.' });
        return;
    }

    var $btn = $('#btn_confirmar_editar_pct').prop('disabled', true);
    var fd   = new FormData();
    fd.append('porcentaje_comision', pct);

    axios.post('/actualizar/parametro/comision/' + id, fd)
        .then(function(res) {
            $('#modalEditarPct').modal('hide');
            $('#tbl_listaParametroComision').DataTable().ajax.reload(null, false);
            Swal.fire({ icon: res.data.icon, title: res.data.title, text: res.data.text });
        })
        .catch(function(err) {
            var d = err.response?.data || { icon:'error', title:'Error', text:'Ocurrió un error.' };
            Swal.fire({ icon: d.icon, title: d.title, text: d.text });
        })
        .finally(function() { $btn.prop('disabled', false); });
}

// ── Carga masiva ─────────────────────────────────────────────────────────────
function procesarCargaMasiva(input) {
    if (!input.files || !input.files[0]) return;
    var archivo = input.files[0];

    // Reset input para permitir subir el mismo archivo nuevamente
    input.value = '';

    Swal.fire({
        title: '¿Subir plantilla?',
        html: '<span style="font-size:13px;">Se procesará <strong>' + archivo.name + '</strong>.<br>' +
              'Los registros existentes serán <strong>actualizados</strong> y los nuevos serán <strong>insertados</strong>.</span>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa fa-upload mr-1"></i> Sí, procesar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e67e22',
        cancelButtonColor: '#6c757d',
    }).then(function(result) {
        if (!result.isConfirmed) return;

        // Mostrar progreso
        Swal.fire({
            title: 'Procesando...',
            html: '<div style="display:flex;flex-direction:column;align-items:center;gap:12px;padding:10px 0;">' +
                  '<i class="fa fa-spinner fa-spin fa-2x" style="color:#e67e22;"></i>' +
                  '<span style="font-size:13px;color:#4a5568;">Leyendo y registrando comisiones...<br>Esto puede tomar unos segundos.</span>' +
                  '</div>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
        });

        var fd = new FormData();
        fd.append('archivo_comision', archivo);
        fd.append('_token', '{{ csrf_token() }}');

        axios.post('{{ route("comision.configuracion.carga.masiva") }}', fd, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(function(res) {
            var d = res.data;
            var detalles = '<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:10px;">' +
                '<span style="background:#e6faf5;color:#0fa37a;border:1px solid #a7f3d0;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:700;">' +
                '<i class="fa fa-plus-circle mr-1"></i>Insertados: ' + (d.insertados||0) + '</span>' +
                '<span style="background:#fff8e1;color:#856404;border:1px solid #fde68a;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:700;">' +
                '<i class="fa fa-edit mr-1"></i>Actualizados: ' + (d.actualizados||0) + '</span>' +
                '<span style="background:#f4f6f9;color:#718096;border:1px solid #e2e8f0;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:700;">' +
                '<i class="fa fa-minus-circle mr-1"></i>Omitidos: ' + (d.omitidos||0) + '</span>' +
                '</div>';

            var errHtml = '';
            if (d.errores && d.errores.length) {
                errHtml = '<div style="margin-top:10px;font-size:11px;color:#e53e3e;text-align:left;max-height:80px;overflow-y:auto;background:#fff5f5;padding:8px;border-radius:6px;">' +
                    d.errores.slice(0,5).join('<br>') + '</div>';
            }

            $('#tbl_listaParametroComision').DataTable().ajax.reload(null, false);

            Swal.fire({
                icon: d.icon || 'success',
                title: d.title || 'Carga completada',
                html: detalles + errHtml,
                confirmButtonColor: '#e67e22',
            });
        })
        .catch(function(err) {
            var d = err.response?.data || { icon:'error', title:'Error', text:'Error al procesar el archivo.' };
            Swal.fire({ icon: d.icon, title: d.title, text: d.text });
        });
    });
}

// ── Gestión Masiva — Tab Switcher ────────────────────────────────────────────
var _catCliCargadas           = false;
var _archivoPendienteFiltrado = null;

function switchTab(tab) {
    var isGlobal = tab === 'global';
    document.getElementById('tab_global').style.display    = isGlobal ? '' : 'none';
    document.getElementById('tab_selectiva').style.display = isGlobal ? 'none' : '';

    var btnG = document.getElementById('tab_btn_global');
    var btnS = document.getElementById('tab_btn_selectiva');

    if (isGlobal) {
        btnG.style.cssText += ';background:linear-gradient(135deg,#f39c12,#e67e22);color:#fff;box-shadow:0 2px 8px rgba(243,156,18,.4);';
        btnS.style.cssText += ';background:transparent;color:rgba(255,255,255,.7);box-shadow:none;';
    } else {
        btnS.style.cssText += ';background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;box-shadow:0 2px 8px rgba(102,126,234,.4);';
        btnG.style.cssText += ';background:transparent;color:rgba(255,255,255,.7);box-shadow:none;';
        if (!_catCliCargadas) cargarCatCliParaFiltro();
    }
}

/* helper: checkbox item */
function _chkItem(grupo, id, label, subLabel) {
    var sub = subLabel ? '<span style="font-size:10px;color:#a0aec0;margin-left:4px;">('+subLabel+')</span>' : '';
    return '<label style="display:flex;align-items:center;gap:8px;padding:5px 6px;border-radius:6px;'
        + 'cursor:pointer;transition:background .12s;font-size:12px;color:#2d3748;font-weight:500;"'
        + ' onmouseover="this.style.background=\'#f0f4ff\'" onmouseout="this.style.background=\'transparent\'">'
        + '<input type="checkbox" class="chk_'+grupo+'" value="'+id+'"'
        + ' onchange="_onChkCatCliChange()"'
        + ' style="width:14px;height:14px;cursor:pointer;accent-color:#667eea;">'
        + label + sub + '</label>';
}
function _chkItemPrecio(id, label, subLabel) {
    var sub = subLabel ? '<span style="font-size:10px;color:#a0aec0;margin-left:4px;">('+subLabel+')</span>' : '';
    return '<label style="display:flex;align-items:center;gap:8px;padding:5px 6px;border-radius:6px;'
        + 'cursor:pointer;transition:background .12s;font-size:12px;color:#2d3748;font-weight:500;"'
        + ' onmouseover="this.style.background=\'#fffbf0\'" onmouseout="this.style.background=\'transparent\'">'
        + '<input type="checkbox" class="chk_cat_precio" value="'+id+'"'
        + ' style="width:14px;height:14px;cursor:pointer;accent-color:#f39c12;">'
        + label + sub + '</label>';
}

function cargarCatCliParaFiltro() {
    $.getJSON('{{ route("comision.configuracion.cat.cliente.activas") }}', function(res) {
        var $lista = $('#lista_chk_cat_cli').empty();
        (res.categorias || []).forEach(function(c) {
            $lista.append(_chkItem('cat_cli', c.id, c.nombre_categoria));
        });
        _catCliCargadas = true;
        // Cargar precios con todas las catCli al inicio
        recargarCatPrecioFiltro([]);
    });
}

function _onChkCatCliChange() {
    var ids = _getChecked('cat_cli');
    recargarCatPrecioFiltro(ids);
}

function recargarCatPrecioFiltro(catCliIds) {
    var $lista = $('#lista_chk_cat_precio');
    $lista.html('<div style="text-align:center;color:#a0aec0;font-size:12px;padding:12px 0;"><i class="fa fa-spinner fa-spin"></i></div>');

    var params = (catCliIds || []).map(function(id) { return 'cat_cli_ids[]=' + id; }).join('&');
    var url = '{{ route("comision.configuracion.cat.precio.filtro") }}' + (params ? '?' + params : '');

    $.getJSON(url, function(res) {
        $lista.empty();
        if (!res.categorias || res.categorias.length === 0) {
            $lista.html('<div style="text-align:center;color:#a0aec0;font-size:12px;padding:12px 0;">Sin categorías de precio disponibles.</div>');
            return;
        }
        var mostrarSub = catCliIds && catCliIds.length !== 1; // mostrar a qué catCli pertenece si hay varias
        res.categorias.forEach(function(c) {
            $lista.append(_chkItemPrecio(c.id, c.nombre, mostrarSub ? c.cat_cli_nombre : null));
        });
    });
}

function checklistSelTodos(grupo, marcar) {
    $('.chk_' + grupo).prop('checked', marcar);
    if (grupo === 'cat_cli') _onChkCatCliChange();
}

function _getChecked(grupo) {
    var ids = [];
    $('.chk_' + grupo + ':checked').each(function() { ids.push($(this).val()); });
    return ids;
}

function descargarPlantillaFiltrada() {
    var catCli    = _getChecked('cat_cli');
    var catPrecio = _getChecked('cat_precio');

    var base = '{{ route("comision.configuracion.plantilla.filtrada") }}';
    var qs   = [];
    catCli.forEach(function(id)    { qs.push('cat_cli[]='    + id); });
    catPrecio.forEach(function(id) { qs.push('cat_precio[]=' + id); });

    window.location.href = base + (qs.length ? '?' + qs.join('&') : '');
}

function iniciarCargaFiltrada(input) {
    if (!input.files || !input.files[0]) return;
    _archivoPendienteFiltrado = input.files[0];
    input.value = '';

    // Step 1: preview
    var fd = new FormData();
    fd.append('archivo_comision', _archivoPendienteFiltrado);
    fd.append('_token', '{{ csrf_token() }}');

    Swal.fire({
        title: 'Analizando archivo...',
        html: '<div style="text-align:center;padding:10px 0;"><i class="fa fa-spinner fa-spin fa-2x" style="color:#667eea;"></i><p style="margin-top:8px;font-size:13px;color:#4a5568;">Leyendo contenido del Excel...</p></div>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
    });

    axios.post('{{ route("comision.configuracion.preview.filtrada") }}', fd, {
        headers: { 'Content-Type': 'multipart/form-data' }
    })
    .then(function(res) {
        var d = res.data; // {existentes, nuevos, omitidos}
        var total = (d.existentes || 0) + (d.nuevos || 0);

        if (total === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin datos válidos',
                text: 'No se encontraron filas con porcentaje mayor a 0 para procesar.',
            });
            return;
        }

        var detalleHtml =
            '<div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin:12px 0;">' +
            (d.existentes > 0
                ? '<span style="background:#fff8e1;color:#856404;border:1px solid #fde68a;border-radius:20px;padding:5px 14px;font-size:12px;font-weight:700;"><i class="fa fa-edit mr-1"></i>Actualizarán: ' + d.existentes + '</span>'
                : '') +
            (d.nuevos > 0
                ? '<span style="background:#e6faf5;color:#0fa37a;border:1px solid #a7f3d0;border-radius:20px;padding:5px 14px;font-size:12px;font-weight:700;"><i class="fa fa-plus-circle mr-1"></i>Insertarán: ' + d.nuevos + '</span>'
                : '') +
            (d.omitidos > 0
                ? '<span style="background:#f4f6f9;color:#718096;border:1px solid #e2e8f0;border-radius:20px;padding:5px 14px;font-size:12px;font-weight:700;"><i class="fa fa-minus-circle mr-1"></i>Omitidos: ' + d.omitidos + '</span>'
                : '') +
            '</div>';

        var advertencia = d.existentes > 0
            ? '<p style="font-size:12px;color:#e53e3e;margin-top:6px;"><i class="fa fa-exclamation-triangle mr-1"></i>' +
              d.existentes + ' registro(s) ya configurado(s) serán sobreescritos con el nuevo porcentaje.</p>'
            : '';

        Swal.fire({
            title: '¿Confirmar carga?',
            html: detalleHtml + advertencia +
                  '<p style="font-size:12px;color:#718096;margin-top:4px;">Esta acción no se puede deshacer.</p>',
            icon: d.existentes > 0 ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-upload mr-1"></i> Sí, procesar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: d.existentes > 0 ? '#e67e22' : '#667eea',
            cancelButtonColor: '#6c757d',
        }).then(function(result) {
            if (result.isConfirmed) procesarArchivoFiltrado();
        });
    })
    .catch(function(err) {
        var d = err.response?.data || { icon:'error', title:'Error', text:'Error al leer el archivo.' };
        Swal.fire({ icon: d.icon, title: d.title, text: d.text });
    });
}

function procesarArchivoFiltrado() {
    var fd = new FormData();
    fd.append('archivo_comision', _archivoPendienteFiltrado);
    fd.append('_token', '{{ csrf_token() }}');

    Swal.fire({
        title: 'Procesando...',
        html: '<div style="text-align:center;padding:10px 0;"><i class="fa fa-spinner fa-spin fa-2x" style="color:#667eea;"></i><p style="margin-top:8px;font-size:13px;color:#4a5568;">Registrando comisiones...</p></div>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
    });

    axios.post('{{ route("comision.configuracion.procesar.filtrada") }}', fd, {
        headers: { 'Content-Type': 'multipart/form-data' }
    })
    .then(function(res) {
        var d = res.data;
        var detalles =
            '<div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:10px;">' +
            '<span style="background:#e6faf5;color:#0fa37a;border:1px solid #a7f3d0;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:700;">' +
            '<i class="fa fa-plus-circle mr-1"></i>Insertados: ' + (d.insertados||0) + '</span>' +
            '<span style="background:#fff8e1;color:#856404;border:1px solid #fde68a;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:700;">' +
            '<i class="fa fa-edit mr-1"></i>Actualizados: ' + (d.actualizados||0) + '</span>' +
            '<span style="background:#f4f6f9;color:#718096;border:1px solid #e2e8f0;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:700;">' +
            '<i class="fa fa-minus-circle mr-1"></i>Omitidos: ' + (d.omitidos||0) + '</span>' +
            '</div>';

        $('#tbl_listaParametroComision').DataTable().ajax.reload(null, false);
        _archivoPendienteFiltrado = null;

        Swal.fire({
            icon: 'success',
            title: 'Carga completada',
            html: detalles,
            confirmButtonColor: '#667eea',
        });
    })
    .catch(function(err) {
        var d = err.response?.data || { icon:'error', title:'Error', text:'Error al procesar.' };
        Swal.fire({ icon: d.icon, title: d.title, text: d.text });
    });
}
</script>
@endpush

