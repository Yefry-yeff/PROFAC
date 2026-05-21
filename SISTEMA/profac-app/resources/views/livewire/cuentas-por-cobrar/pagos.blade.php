<div>
@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   Aplicación de Pagos — Estilos + Animaciones
═══════════════════════════════════════════════════════ */

/* ── Keyframes ── */
@keyframes ap-fadeInDown {
    from { opacity:0; transform:translateY(-18px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes ap-fadeInUp {
    from { opacity:0; transform:translateY(16px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes ap-fadeIn {
    from { opacity:0; } to { opacity:1; }
}
@keyframes ap-slideInLeft {
    from { opacity:0; transform:translateX(-20px); }
    to   { opacity:1; transform:translateX(0); }
}
@keyframes ap-shimmer {
    0%   { background-position: -400px 0; }
    100% { background-position: 400px 0; }
}
@keyframes ap-pulse-orange {
    0%,100% { box-shadow: 0 0 0 0 rgba(243,156,18,.45); }
    50%      { box-shadow: 0 0 0 8px rgba(243,156,18,0); }
}
@keyframes ap-spin-slow {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
@keyframes ap-underline-grow {
    from { width:0; } to { width:100%; }
}
@keyframes ap-row-in {
    from { opacity:0; transform:translateX(-6px); }
    to   { opacity:1; transform:translateX(0); }
}
@keyframes ap-badge-pop {
    0%   { transform:scale(.7); opacity:0; }
    70%  { transform:scale(1.15); }
    100% { transform:scale(1); opacity:1; }
}

/* ── SEARCH CARD ── */
.ap-search-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 6px 28px rgba(0,0,0,.09);
    padding: 24px 28px;
    margin: 24px 0 0;
    border: 1px solid #edf2f7;
    animation: ap-fadeInUp .5s .15s ease both;
    transition: box-shadow .25s;
}
.ap-search-card:hover {
    box-shadow: 0 10px 36px rgba(0,0,0,.12);
}
.ap-search-card .ap-search-title {
    font-size: 13px;
    font-weight: 700;
    color: #4a5568;
    text-transform: uppercase;
    letter-spacing: .6px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ap-search-row {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    flex-wrap: nowrap;
}
.ap-search-row .ap-select-wrap { flex: 0 0 50%; max-width: 50%; }
.ap-search-row .ap-select-wrap label {
    font-size: 11px;
    font-weight: 700;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: .4px;
    margin-bottom: 6px;
    display: block;
}
/* select focus ring */
.ap-search-row .ap-select-wrap .form-control {
    transition: border-color .2s, box-shadow .2s;
}
.ap-search-row .ap-select-wrap .form-control:focus {
    border-color: #f39c12;
    box-shadow: 0 0 0 3px rgba(243,156,18,.15);
}

/* ── BUTTONS ── */
.ap-btn-search {
    background: linear-gradient(135deg, #1a7efb, #0d6efd) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 10px;
    padding: 10px 22px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: flex !important;
    align-items: center;
    gap: 8px;
    transition: transform .18s, box-shadow .18s, filter .18s;
    box-shadow: 0 4px 14px rgba(26,126,251,.35);
    white-space: nowrap;
    height: 42px;
    position: relative;
    overflow: hidden;
}
.ap-btn-search::after {
    content:'';
    position:absolute; inset:0;
    background: rgba(255,255,255,.15);
    opacity:0;
    transition: opacity .18s;
    border-radius: 10px;
}
.ap-btn-search:hover { transform:translateY(-3px); box-shadow:0 8px 22px rgba(26,126,251,.5); }
.ap-btn-search:hover::after { opacity:1; }
.ap-btn-search:active { transform:translateY(-1px); }

.ap-btn-ec {
    background: linear-gradient(135deg, #e74c3c, #c0392b) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 10px;
    padding: 10px 20px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: flex !important;
    align-items: center;
    gap: 8px;
    transition: transform .18s, box-shadow .18s;
    box-shadow: 0 4px 14px rgba(231,76,60,.3);
    white-space: nowrap;
    height: 42px;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}
.ap-btn-ec::after {
    content:'';
    position:absolute; inset:0;
    background: rgba(255,255,255,.15);
    opacity:0;
    transition: opacity .18s;
    border-radius: 10px;
}
.ap-btn-ec:hover { transform:translateY(-3px); box-shadow:0 8px 22px rgba(231,76,60,.45); color:#fff !important; }
.ap-btn-ec:hover::after { opacity:1; }
.ap-btn-ec:active { transform:translateY(-1px); }
.ap-btn-group { display:flex; gap:12px; align-items:center; margin-left:auto; }

/* ── TABS ── */
.ap-tabs-row {
    display: flex;
    gap: 4px;
    margin: 24px 0 0;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 0;
    animation: ap-fadeIn .5s .25s ease both;
}
.ap-tab-btn {
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 10px 22px;
    font-size: 13px;
    font-weight: 700;
    color: #718096;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: color .2s, background .2s, border-color .2s, transform .15s;
    margin-bottom: -2px;
    border-radius: 8px 8px 0 0;
    position: relative;
}
.ap-tab-btn:hover:not(.active) {
    color: #2d3748;
    background: #f7f8fa;
    transform: translateY(-2px);
}
.ap-tab-btn.active {
    color: #f39c12;
    border-bottom-color: #f39c12;
    background: rgba(243,156,18,.06);
}
.ap-tab-badge {
    background: #e2e8f0;
    color: #718096;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 800;
    padding: 1px 7px;
    transition: background .2s, color .2s;
    animation: ap-badge-pop .4s ease both;
}
.ap-tab-btn.active .ap-tab-badge {
    background: rgba(243,156,18,.18);
    color: #e67e22;
}

/* ── PANELS ── */
.ap-panel {
    background: #fff;
    border-radius: 0 0 10px 10px;
    box-shadow: 0 4px 16px rgba(0,0,0,.07);
    border: 1px solid #e2e8f0;
    border-top: none;
    padding: 24px;
    animation: ap-fadeInUp .35s ease both;
}

/* Panel title — gradiente naranja */
.ap-panel-title {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important;
    color: #fff !important;
    border-radius: 4px 4px 0 0;
    font-size: 14px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: -24px -24px 18px -24px;
    padding: 14px 20px;
    border-bottom: none !important;
    position: relative;
    overflow: hidden;
}
.ap-panel-title::after {
    content:'';
    position:absolute; inset:0;
    background: linear-gradient(90deg, transparent 60%, rgba(255,255,255,.08) 100%);
    pointer-events:none;
}
.ap-panel-title .ap-panel-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: rgba(255,255,255,.22) !important;
    color: #fff !important;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    transition: transform .2s;
}
.ap-panel-title:hover .ap-panel-icon { transform: rotate(15deg) scale(1.1); }
.ap-panel-icon.blue,.ap-panel-icon.green,.ap-panel-icon.orange {
    background: rgba(255,255,255,.22) !important;
    color: #fff !important;
}

/* ── DATATABLE ── */
.ap-panel .dataTables_wrapper .dataTables_filter input,
.ap-panel .dataTables_wrapper .dataTables_length select {
    border: 1.5px solid #dde2ec;
    border-radius: 8px;
    padding: 5px 10px;
    font-size: 12px;
    transition: border-color .2s, box-shadow .2s;
}
.ap-panel .dataTables_wrapper .dataTables_filter input:focus {
    border-color: #f39c12;
    outline: none;
    box-shadow: 0 0 0 3px rgba(243,156,18,.12);
}
.ap-panel table.dataTable thead th {
    background: #f5f7fb;
    color: #4a5568;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .4px;
    border-top: none !important;
    border-bottom: 2px solid #e2e8f0 !important;
    white-space: nowrap;
    padding: 10px 12px;
}
.ap-panel table.dataTable tbody tr {
    transition: background .18s, transform .12s;
}
.ap-panel table.dataTable tbody tr:nth-child(even) { background: #f7f9fc !important; }
.ap-panel table.dataTable tbody tr:nth-child(odd)  { background: #ffffff !important; }
.ap-panel table.dataTable tbody tr:hover {
    background: #fff8ec !important;
    transform: scale(1.002);
    box-shadow: 0 2px 10px rgba(243,156,18,.12);
    position: relative;
    z-index: 1;
}
.ap-panel table.dataTable tbody td {
    font-size: 12px;
    color: #2d3748;
    padding: 9px 12px;
    vertical-align: middle;
    border-top: 1px solid #edf2f7 !important;
}
.ap-panel table.dataTable { border: 1.5px solid #dde2ec !important; }
.ap-panel table.dataTable tfoot th {
    font-size: 10px;
    background: #f5f7fb;
    border-top: 2px solid #e2e8f0 !important;
    padding: 7px 12px;
}
.ap-panel table.dataTable tfoot input {
    border: 1px solid #dde2ec;
    border-radius: 6px;
    padding: 3px 6px;
    font-size: 10.5px;
    width: 100%;
}

/* ── MONEY ── */
.ap-money { font-weight:700; font-family:monospace; font-size:12px; }
.ap-money.cargo { color:#2d3748; }
.ap-money.abono { color:#1ab394; }
.ap-money.saldo-alto { color:#e74c3c; font-weight:800; }
.ap-money.saldo-bajo { color:#f39c12; font-weight:700; }
.ap-money.saldo-cero { color:#1ab394; font-weight:700; }

/* ── MODALS ── */
.ap-modal .modal-content {
    border: none;
    border-radius: 16px;
    box-shadow: 0 24px 64px rgba(0,0,0,.18);
    overflow: hidden;
    animation: ap-fadeInUp .3s ease both;
}
.ap-modal .modal-header { padding:18px 24px; border-bottom:1px solid #f0f2f5; }
.ap-modal .modal-header.blue   { background:linear-gradient(135deg,#1a7efb,#0d6efd); }
.ap-modal .modal-header.green  { background:linear-gradient(135deg,#1ab394,#0fa37a); }
.ap-modal .modal-header.orange { background:linear-gradient(135deg,#f39c12,#e67e22); }
.ap-modal .modal-header.red    { background:linear-gradient(135deg,#e74c3c,#c0392b); }
.ap-modal .modal-header.dark   { background:linear-gradient(135deg,#2d3748,#4a5568); }
.ap-modal .modal-title { font-size:15px; font-weight:800; color:#fff; display:flex; align-items:center; gap:10px; margin:0; }
.ap-modal .modal-title i { font-size:17px; }
.ap-modal .close { color:rgba(255,255,255,.8)!important; opacity:1!important; font-size:22px; transition:transform .15s; }
.ap-modal .close:hover { color:#fff!important; transform:rotate(90deg); }
.ap-modal .modal-body { padding:24px; background:#fafbfc; }
.ap-modal .modal-footer { background:#f4f6f9; border-top:1px solid #e2e8f0; padding:14px 24px; }

/* Modal form fields */
.ap-form-group { margin-bottom:18px; }
.ap-form-group label {
    font-size:11px; font-weight:700; color:#718096;
    text-transform:uppercase; letter-spacing:.4px;
    display:block; margin-bottom:6px;
}
.ap-form-group .form-control {
    border:1.5px solid #dde2ec; border-radius:9px;
    font-size:13px; padding:9px 12px;
    color:#2d3748; background:#fff;
    transition:border-color .18s, box-shadow .18s;
}
.ap-form-group .form-control:focus {
    border-color:#1a7efb;
    box-shadow:0 0 0 3px rgba(26,126,251,.1);
    outline:none;
}
.ap-form-group .form-control[readonly] { background:#f1f3f7; color:#6b7280; }
.ap-form-group textarea.form-control { resize:vertical; min-height:90px; }

.ap-info-banner {
    background:linear-gradient(135deg,#edf7ff,#dbeeff);
    border:1px solid #bee3f8; border-radius:10px;
    padding:12px 16px; font-size:12px; color:#2b6cb0;
    display:flex; align-items:flex-start; gap:10px;
    margin-bottom:18px;
    animation: ap-slideInLeft .3s ease both;
}
.ap-info-banner i { font-size:16px; margin-top:1px; flex-shrink:0; }

/* Save buttons */
.ap-btn-save {
    background:linear-gradient(135deg,#2d3748,#4a5568);
    color:#fff; border:none; border-radius:10px;
    padding:11px 28px; font-size:13px; font-weight:800;
    cursor:pointer; display:inline-flex; align-items:center; gap:8px;
    transition:transform .18s, box-shadow .18s, filter .18s;
    box-shadow:0 4px 14px rgba(0,0,0,.2);
}
.ap-btn-save:hover { transform:translateY(-2px); box-shadow:0 8px 22px rgba(0,0,0,.25); filter:brightness(1.08); }
.ap-btn-save:active { transform:translateY(0); }
.ap-btn-save.blue   { background:linear-gradient(135deg,#1a7efb,#0d6efd); box-shadow:0 4px 14px rgba(26,126,251,.35); }
.ap-btn-save.green  { background:linear-gradient(135deg,#1ab394,#0fa37a); box-shadow:0 4px 14px rgba(26,179,148,.35); }
.ap-btn-save.orange { background:linear-gradient(135deg,#f39c12,#e67e22); box-shadow:0 4px 14px rgba(243,156,18,.35); }
.ap-btn-save.red    { background:linear-gradient(135deg,#e74c3c,#c0392b); box-shadow:0 4px 14px rgba(231,76,60,.35); }

/* Empty state */
.ap-empty-state { text-align:center; padding:48px 20px; color:#a0aec0; }
.ap-empty-state i { font-size:52px; display:block; margin-bottom:14px; opacity:.3; }
.ap-empty-state p { font-size:14px; }

/* ── Select2 — caja de selección ── */
.ap-modal .select2-container { width: 100% !important; }
.ap-modal .select2-container .select2-selection--single {
    height: 40px !important;
    border: 1.5px solid #dde2ec !important;
    border-radius: 9px !important;
    background: #fff !important;
    display: flex; align-items: center;
    transition: border-color .18s, box-shadow .18s;
}
.ap-modal .select2-container--open .select2-selection--single,
.ap-modal .select2-container .select2-selection--single:focus {
    border-color: #f39c12 !important;
    box-shadow: 0 0 0 3px rgba(243,156,18,.15) !important;
    outline: none !important;
}
.ap-modal .select2-selection__rendered {
    color: #2d3748 !important;
    font-size: 13px !important;
    line-height: 38px !important;
    padding-left: 13px !important;
    padding-right: 30px !important;
}
.ap-modal .select2-selection__arrow { height: 38px !important; right: 8px !important; }
/* ── Dropdown flotante — z-index superior al tema Inspinia (2040) ── */
.ap-select2-drop.select2-dropdown {
    border: 1.5px solid #f39c12 !important;
    border-radius: 9px !important;
    box-shadow: 0 8px 32px rgba(243,156,18,.22) !important;
    font-size: 13px !important;
    z-index: 99999 !important;
    overflow: hidden;
}
.ap-select2-drop .select2-search--dropdown input {
    border: 1.5px solid #dde2ec;
    border-radius: 7px;
    padding: 6px 10px;
    font-size: 13px;
}
.ap-select2-drop .select2-search--dropdown input:focus {
    border-color: #f39c12;
    box-shadow: 0 0 0 3px rgba(243,156,18,.12);
    outline: none;
}
.ap-select2-drop .select2-results__option {
    padding: 8px 14px;
    font-size: 13px;
    color: #2d3748;
}
.ap-select2-drop .select2-results__option--highlighted {
    background: linear-gradient(135deg, #f39c12, #e67e22) !important;
    color: #fff !important;
}
/* Permite que el dropdown desborde el modal sin ser recortado */
.ap-modal .modal-content { overflow: visible; }
.ap-modal.modal { overflow-y: auto !important; }
.ap-modal .modal-dialog { overflow: visible !important; }

/* ── HERO BANNER ── */
.ap-hero {
    background: linear-gradient(135deg, #1a202c 0%, #2d3748 60%, #4a5568 100%);
    border-radius: 16px; padding: 28px 32px; margin: 24px 0 0;
    color: #fff; display: flex; align-items: center; gap: 20px;
    box-shadow: 0 8px 32px rgba(0,0,0,.18);
    animation: ap-fadeInUp .45s ease both;
    position: relative; overflow: hidden;
}
.ap-hero::before {
    content:''; position: absolute; top:-40px; right:-40px;
    width: 180px; height: 180px; border-radius: 50%;
    background: rgba(243,156,18,.12); pointer-events: none;
}
.ap-hero::after {
    content:''; position: absolute; bottom:-50px; right:80px;
    width: 120px; height: 120px; border-radius: 50%;
    background: rgba(243,156,18,.07); pointer-events: none;
}
.ap-hero-icon {
    width: 56px; height: 56px; border-radius: 14px;
    background: rgba(243,156,18,.2); border: 2px solid rgba(243,156,18,.4);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; color: #f39c12; flex-shrink: 0;
}
.ap-hero-body h3 { margin: 0; font-size: 18px; font-weight: 800; letter-spacing: .3px; }
.ap-hero-body p  { margin: 4px 0 0; font-size: 12px; color: rgba(255,255,255,.65); }

/* ── STAT CARDS ── */
.ap-stat-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 14px; margin-top: 18px;
    animation: ap-fadeInUp .5s .2s ease both;
}
.ap-stat-card {
    background: #fff; border-radius: 12px; padding: 16px 18px;
    border-left: 4px solid #f39c12;
    box-shadow: 0 3px 14px rgba(0,0,0,.06);
    transition: transform .15s, box-shadow .15s;
}
.ap-stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.1); }
.ap-stat-card.blue  { border-left-color: #4299e1; }
.ap-stat-card.green { border-left-color: #48bb78; }
.ap-stat-card.red   { border-left-color: #e53e3e; }
.ap-stat-label {
    font-size: 10px; font-weight: 700; color: #a0aec0;
    text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px;
}
.ap-stat-value {
    font-size: 20px; font-weight: 800; color: #2d3748;
    font-variant-numeric: tabular-nums;
}
.ap-stat-value.red { color: #e53e3e; }

/* ── MENÚ CONTEXTUAL ACCIONES ── */
.ap-ctx-wrap { display: inline-block; }
.ap-ctx-menu {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 24px 64px rgba(0,0,0,.18), 0 4px 18px rgba(0,0,0,.10);
    padding: 6px;
    min-width: 236px;
    border: 1px solid rgba(0,0,0,.06);
    animation: ap-fadeInUp .15s ease both;
}
.ap-ctx-section {
    padding: 6px 10px 2px;
    font-size: 9.5px;
    font-weight: 800;
    color: #b0bac9;
    text-transform: uppercase;
    letter-spacing: .7px;
}
.ap-ctx-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    text-decoration: none !important;
    cursor: pointer;
    transition: background .12s;
    line-height: 1;
}
.ap-ctx-item:hover { background: #f4f6fa; color: #111827 !important; text-decoration: none !important; }
.ap-ctx-item.ap-ctx-highlight {
    background: linear-gradient(90deg, #f0fdf4, #e6fffa);
    color: #065f46 !important;
    font-weight: 700;
}
.ap-ctx-item.ap-ctx-highlight:hover { background: linear-gradient(90deg,#dcfce7,#ccfbf1); }
.ap-ctx-item.ap-ctx-dimmed { opacity: .4; cursor: default; pointer-events: none; }
.ap-ctx-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 13px;
}
.ap-ctx-icon.ci-blue   { background:#eff6ff; color:#3b82f6; }
.ap-ctx-icon.ci-red    { background:#fef2f2; color:#ef4444; }
.ap-ctx-icon.ci-yellow { background:#fffbeb; color:#d97706; }
.ap-ctx-icon.ci-green  { background:#f0fdf4; color:#16a34a; }
.ap-ctx-icon.ci-orange { background:#fff7ed; color:#ea580c; }
.ap-ctx-icon.ci-gray   { background:#f8fafc; color:#64748b; }
.ap-ctx-icon.ci-teal   { background:#f0fdfa; color:#0d9488; }
.ap-ctx-divider { height: 1px; background: #f1f5f9; margin: 4px 6px; }
/* Botón Acciones */
.ap-actions-toggle {
    background: #1e293b !important;
    color: #f8fafc !important;
    border: none !important;
    border-radius: 7px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    padding: 5px 12px !important;
    white-space: nowrap !important;
    cursor: pointer !important;
    letter-spacing: .3px !important;
    transition: background .15s !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
}
.ap-actions-toggle:hover { background: #334155 !important; }
</style>
@endpush

{{-- ===== PAGE HEADER ===== --}}
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><i class="fa fa-file-text-o text-primary"></i> Aplicación de Pagos</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Inicio</a>
            </li>
            <li class="breadcrumb-item">Cuentas por Cobrar</li>
            <li class="breadcrumb-item active">
                <strong>Aplicación de Pagos</strong>
            </li>
        </ol>
    </div>
</div>

{{-- ===== SEARCH CARD ===== --}}
<div class="wrapper wrapper-content pb-0">

{{-- Hero banner --}}
<div class="ap-hero">
    <div class="ap-hero-icon">
        <i class="fa-solid fa-money-bill-wave"></i>
    </div>
    <div class="ap-hero-body">
        <h3>Aplicación de Pagos</h3>
        <p>Gestione cobros, notas de crédito/débito y movimientos de cuentas por cobrar.</p>
    </div>
</div>

<div class="ap-search-card">
    <div class="ap-search-title">
        <i class="fa fa-file-invoice" style="color:#f39c12;"></i>
        <i class="fa fa-file-text-o" style="color:#f39c12;"></i>
        Aplicación de Pagos — Buscar Cliente
    </div>
    <div class="ap-search-row">
        <div class="ap-select-wrap">
            <label><i class="fa fa-building-o mr-1"></i> Cliente</label>
            <select id="cliente" name="cliente" class="form-control" required>
                <option value="" disabled selected>-- Escriba para buscar un cliente --</option>
            </select>
        </div>
        <div class="ap-btn-group">
            <button type="button" class="ap-btn-search" onclick="llamarTablas()">
                <i class="fa fa-search"></i> Cargar datos
            </button>
            <button type="button" class="ap-btn-ec d-none" id="btnEC" onclick="pdfEstadoCuenta()">
                <i class="fa fa-file-pdf-o"></i> Estado de Cuenta
            </button>
        </div>
    </div>
</div>

{{-- ===== STAT CARDS ===== --}}
<div class="ap-stat-row d-none" id="apStats">
    <div class="ap-stat-card">
        <div class="ap-stat-label"><i class="fa fa-list mr-1"></i> Facturas Pendientes</div>
        <div class="ap-stat-value" id="apStatFacturas">—</div>
    </div>
    <div class="ap-stat-card blue">
        <div class="ap-stat-label"><i class="fa fa-dollar mr-1"></i> Total Cargo</div>
        <div class="ap-stat-value" id="apStatCargo">—</div>
    </div>
    <div class="ap-stat-card red">
        <div class="ap-stat-label"><i class="fa-solid fa-scale-unbalanced-flip mr-1"></i> Saldo Total</div>
        <div class="ap-stat-value red" id="apStatSaldo">—</div>
    </div>
    <div class="ap-stat-card green">
        <div class="ap-stat-label"><i class="fa fa-check-circle mr-1"></i> Total Abonado</div>
        <div class="ap-stat-value" id="apStatAbonado">—</div>
    </div>
</div>

{{-- ===== MODAL RETENCIÓN ISV ===== --}}
<div class="modal ap-modal fade" id="modalretencion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header orange">
                <h5 class="modal-title">
                    <i class="fa fa-percent"></i> Gestionar Retención de ISV
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="ap-info-banner">
                    <i class="fa fa-info-circle"></i>
                    <span>Revise la información de la factura y seleccione si la retención de ISV aplica o no para este registro.</span>
                </div>
                <form id="formEstadoRetencion" name="formEstadoRetencion">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-hashtag mr-1"></i> Código de Registro</label>
                                <input type="text" readonly class="form-control" id="codAplicPago" name="codAplicPago">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-file-text-o mr-1"></i> Factura</label>
                                <input type="text" readonly class="form-control" id="facturaCai" name="facturaCai">
                                <input type="hidden" id="idFacturaRetencion" name="idFacturaRetencion">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-dollar mr-1"></i> Monto de Retención</label>
                                <input type="text" readonly class="form-control" id="montoRetencion" name="montoRetencion">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-toggle-on mr-1"></i> Estado de Retención</label>
                                <select id="selectTiporetencion" name="selectTiporetencion" class="form-control">
                                    <option value="2">APLICA</option>
                                    <option value="1">NO APLICA</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ap-form-group">
                                <label><i class="fa fa-pencil mr-1"></i> Nota <span class="text-danger">*</span></label>
                                <textarea required class="form-control" id="comentario_retencion" name="comentario_retencion" rows="3" placeholder="Ingrese una nota obligatoria..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                        <button id="btn_cambioRetencion" type="submit" class="ap-btn-save orange">
                            <i class="fa fa-save"></i> Guardar gestión
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL NOTA DE CRÉDITO ===== --}}
<div class="modal ap-modal fade" id="modalNC" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header orange">
                <h5 class="modal-title">
                    <i class="fa fa-arrow-down"></i> Aplicación de Nota de Crédito
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formNotaCredito" name="formNotaCredito">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-hashtag mr-1"></i> Código de Registro</label>
                                <input required type="text" readonly class="form-control" id="codAplicPagonc" name="codAplicPagonc">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-file-text-o mr-1"></i> Factura</label>
                                <input required type="text" readonly class="form-control" id="facturaCainc" name="facturaCainc">
                                <input type="hidden" id="idFacturaNC" name="idFacturaNC">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ap-form-group">
                                <label><i class="fa fa-list mr-1"></i> Nota de Crédito a Aplicar</label>
                                <select required onchange="datosNotaCredito()" id="selectNotaCredito" name="selectNotaCredito" class="form-control"></select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-dollar mr-1"></i> Monto de Nota de Crédito</label>
                                <input required type="text" readonly class="form-control" id="totalNotaCredito" name="totalNotaCredito">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-toggle-on mr-1"></i> Acción</label>
                                <select required id="selectAplicado" name="selectAplicado" class="form-control">
                                    <option value="">— Seleccione —</option>
                                    <option value="1">SE APLICA</option>
                                    <option value="2">NO SE APLICA</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ap-form-group">
                                <label><i class="fa fa-pencil mr-1"></i> Motivo de la Nota de Crédito</label>
                                <textarea required readonly class="form-control" id="motivoNotacredito" name="motivoNotacredito" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ap-form-group">
                                <label><i class="fa fa-comment mr-1"></i> Nota de Aplicación <span class="text-danger">*</span></label>
                                <textarea required class="form-control" maxlength="500" id="comentarioRebaja" name="comentarioRebaja" rows="3" placeholder="Ingrese su nota..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                        <button id="btn_notacredito" type="submit" class="ap-btn-save orange">
                            <i class="fa fa-save"></i> Gestionar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL NOTA DE DÉBITO ===== --}}
<div class="modal ap-modal fade" id="modalND" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header orange">
                <h5 class="modal-title">
                    <i class="fa fa-arrow-up"></i> Aplicación de Nota de Débito
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formNotaDebito" name="formNotaDebito">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-hashtag mr-1"></i> Código de Registro</label>
                                <input required type="text" readonly class="form-control" id="codAplicPagond" name="codAplicPagond">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-file-text-o mr-1"></i> Factura</label>
                                <input required type="text" readonly class="form-control" id="facturaCaind" name="facturaCaind">
                                <input type="hidden" id="idFacturaND" name="idFacturaND">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ap-form-group">
                                <label><i class="fa fa-list mr-1"></i> Nota de Débito a Aplicar</label>
                                <select required onchange="datosNotaDebito()" id="selectNotaDebito" name="selectNotaDebito" class="form-control"></select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-dollar mr-1"></i> Monto de Nota de Débito</label>
                                <input required type="text" readonly class="form-control" id="totalNotaDebito" name="totalNotaDebito">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-toggle-on mr-1"></i> Acción</label>
                                <select required id="selectAplicadond" name="selectAplicadond" class="form-control">
                                    <option value="">— Seleccione —</option>
                                    <option value="1">SE APLICA</option>
                                    <option value="2">NO SE APLICA</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ap-form-group">
                                <label><i class="fa fa-pencil mr-1"></i> Motivo de la Nota de Débito</label>
                                <textarea required maxlength="500" readonly class="form-control" id="motivoNotaDebito" name="motivoNotaDebito" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ap-form-group">
                                <label><i class="fa fa-comment mr-1"></i> Nota de Aplicación <span class="text-danger">*</span></label>
                                <textarea required class="form-control" id="comentarioSuma" name="comentarioSuma" rows="3" placeholder="Ingrese su nota..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                        <button id="btn_notadebito" type="submit" class="ap-btn-save orange">
                            <i class="fa fa-save"></i> Gestionar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


{{-- ===== MODAL OTROS MOVIMIENTOS ===== --}}
<div class="modal ap-modal fade" id="modalOtrosMovimientos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header orange">
                <h5 class="modal-title">
                    <i class="fa fa-exchange"></i> Otros Movimientos — Cobros / Rebajas
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formOtrosMovimientos" name="formOtrosMovimientos">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-hashtag mr-1"></i> Código de Registro</label>
                                <input required type="text" readonly class="form-control" id="codAplicPagoom" name="codAplicPagoom">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-file-text-o mr-1"></i> Factura</label>
                                <input required type="text" readonly class="form-control" id="facturaCaiom" name="facturaCaiom">
                                <input type="hidden" id="idFacturaom" name="idFacturaom">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-random mr-1"></i> Tipo de Movimiento</label>
                                <select required id="selecttipoMovimiento" name="selecttipoMovimiento" class="form-control">
                                    <option value="">— Seleccione —</option>
                                    <option value="1">CARGO EXTRA</option>
                                    <option value="2">CARGO A DEDUCIR</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-dollar mr-1"></i> Monto a Aplicar <span id="om-saldo-label" style="color:#1a7a4a;font-weight:600;font-size:.82rem;"></span></label>
                                <input required type="number" step="any" min="0" class="form-control" id="montoTM" name="montoTM" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ap-form-group">
                                <label><i class="fa fa-comment mr-1"></i> Comentario del Movimiento <span class="text-danger">*</span></label>
                                <textarea required maxlength="500" class="form-control" id="motivoMovimiento" name="motivoMovimiento" rows="3" placeholder="Ingrese el comentario del movimiento..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                        <button id="btn_tipomov" type="submit" class="ap-btn-save blue">
                            <i class="fa fa-save"></i> Guardar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


{{-- ===== MODAL CRÉDITOS / ABONOS ===== --}}
<div class="modal ap-modal fade" id="modalAbonos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header orange">
                <h5 class="modal-title">
                    <i class="fa fa-credit-card"></i> Aplicar Crédito / Abono
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formabonos" name="formabonos">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-hashtag mr-1"></i> Código de Registro</label>
                                <input required type="text" readonly class="form-control" id="codAplicPagoAbono" name="codAplicPagoAbono">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-file-text-o mr-1"></i> Factura</label>
                                <input required type="text" readonly class="form-control" id="facturaCaiAbono" name="facturaCaiAbono">
                                <input type="hidden" id="idFacturaAbono" name="idFacturaAbono">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-dollar mr-1"></i> Monto a Abonar <span id="abono-saldo-label" style="color:#1a7a4a;font-weight:600;font-size:.82rem;"></span></label>
                                <input required type="number" min="0" step="any" class="form-control" id="montoAbono" name="montoAbono" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-credit-card mr-1"></i> Medio de Pago</label>
                                <select required onchange="metodoPago()" id="selectMetodoPago" name="selectMetodoPago" class="form-control">
                                    <option value="">— Seleccione —</option>
                                    <option value="1">EFECTIVO</option>
                                    <option value="2">TRANSFERENCIA BANCARIA</option>
                                    <option value="3">CHEQUE</option>
                                    <option value="4">LINK DE PAGO</option>
                                    <option value="5">POS</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-bank mr-1"></i> Banco</label>
                                <select required id="selectBanco" name="selectBanco" class="form-control"></select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-calendar mr-1"></i> Fecha del Pago <span class="text-danger">*</span></label>
                                <input class="form-control" required type="date" id="fecha_pago" name="fecha_pago">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-ticket mr-1"></i> Número de Recibo</label>
                                <input class="form-control" type="text" maxlength="100" id="numero_recibo" name="numero_recibo" placeholder="Ingrese el número de recibo">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-paperclip mr-1"></i> Documento de Pago <span class="text-danger">*</span></label>
                                <input class="form-control" id="doc_pago" name="doc_pago" type="file" accept="image/png, image/jpeg, image/jpg, application/pdf">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ap-form-group">
                                <label><i class="fa fa-comment mr-1"></i> Nota del Pago <span class="text-danger">*</span></label>
                                <textarea required class="form-control" id="comentarioAbono" name="comentarioAbono" rows="3" placeholder="Ingrese la nota del pago realizado..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                        <button id="btn_notaabono" type="submit" class="ap-btn-save orange">
                            <i class="fa fa-check-circle"></i> Registrar Abono
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



{{-- ===== MODAL PREVIEW COMISIONES ===== --}}
<div class="modal fade" id="modalPreviewComisiones" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e40af;color:#fff;border-radius:0;">
                <h5 class="modal-title">
                    <i class="fa fa-bell mr-2"></i> Confirmación de Comisiones — Cierre de Factura
                </h5>
            </div>
            <div class="modal-body">
                <div class="alert mb-3" style="background:#eff6ff;border-left:4px solid #3b82f6;border-radius:4px;padding:10px 14px;">
                    <i class="fa fa-info-circle mr-1" style="color:#3b82f6;"></i>
                    Este pago <strong>cerrará completamente la factura</strong>. Los siguientes empleados recibirán comisión automáticamente al confirmar:
                </div>
                <div id="preview-comisiones-lista"></div>
                <div class="alert mb-0 mt-3" style="background:#fffbeb;border-left:4px solid #f59e0b;border-radius:4px;padding:10px 14px;font-size:.84rem;">
                    <i class="fa fa-exclamation-triangle mr-1" style="color:#f59e0b;"></i>
                    Una vez registrado el pago las comisiones se procesarán automáticamente y <strong>no se podrán revertir</strong> sin intervención manual.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-cancel-preview-comision">
                    <i class="fa fa-arrow-left mr-1"></i> Volver al formulario
                </button>
                <button type="button" class="btn btn-success btn-sm px-4" id="btn-confirmar-y-guardar-pago">
                    <i class="fa fa-check-circle mr-1"></i> Confirmar y Registrar Pago
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL CERRAR FACTURA ===== --}}
<div class="modal ap-modal fade" id="modalcerrarFact" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header orange">
                <h5 class="modal-title">
                    <i class="fa fa-lock"></i> Cerrar Factura
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="ap-info-banner" style="background:linear-gradient(135deg,#fff5f5,#fed7d7);border-color:#feb2b2;color:#c53030;">
                    <i class="fa fa-exclamation-triangle"></i>
                    <span>Esta acción cerrará definitivamente la factura. Asegúrese de que toda la información es correcta antes de continuar.</span>
                </div>
                <form id="formCierrefact" name="formCierrefact">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-hashtag mr-1"></i> Código de Registro</label>
                                <input required type="text" readonly class="form-control" id="codAplicCierre" name="codAplicCierre">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ap-form-group">
                                <label><i class="fa fa-file-text-o mr-1"></i> Factura</label>
                                <input required type="text" readonly class="form-control" id="facturaCaiCierre" name="facturaCaiCierre">
                                <input type="hidden" id="idFacturaCierre" name="idFacturaCierre">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ap-form-group">
                                <label><i class="fa fa-comment mr-1"></i> Nota de Cierre <span class="text-danger">*</span></label>
                                <textarea required class="form-control" id="comentarioCierre" name="comentarioCierre" rows="3" placeholder="Ingrese la nota de cierre de la factura..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                        <button id="btn_cierreFact" type="submit" class="ap-btn-save red">
                            <i class="fa fa-lock"></i> Confirmar Cierre
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



    {{-- ================= TABBED TABLES SECTION ================= --}}
    <div id="tbl_principal_div" class="d-none" style="margin-top: 20px;">

        {{-- Tab navigation --}}
        <div class="ap-tabs-row">
            <button class="ap-tab-btn active" onclick="switchTab('tab-facturas', this)">
                <i class="fa fa-file-text-o"></i>
                Saldos por Factura
                <span class="ap-tab-badge" id="badge-facturas">—</span>
            </button>
            <button class="ap-tab-btn" onclick="switchTab('tab-movimientos', this)">
                <i class="fa fa-exchange"></i>
                Movimientos
                <span class="ap-tab-badge" id="badge-movimientos">—</span>
            </button>
            <button class="ap-tab-btn" onclick="switchTab('tab-abonos', this)">
                <i class="fa fa-credit-card"></i>
                Créditos y Abonos
                <span class="ap-tab-badge" id="badge-abonos">—</span>
            </button>
        </div>

        {{-- Tab: Saldos por Factura --}}
        <div id="tab-facturas" class="ap-panel">
            <div class="ap-panel-title">
                <div class="ap-panel-icon blue"><i class="fa fa-file-text-o"></i></div>
                Registros de Saldos por Factura
            </div>
            <div class="table-responsive">
                <table id="tbl_cuentas_facturas_cliente"
                       class="table table-sm table-hover w-100"
                       style="border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Factura ID</th>
                            <th>Correlativo</th>
                            <th>Cargo</th>
                            <th>N. Crédito</th>
                            <th>N. Débito</th>
                            <th>Abonos</th>
                            <th>Cargo Extra</th>
                            <th>Deducciones</th>
                            <th>ISV</th>
                            <th>Retención</th>
                            <th>Saldo</th>
                            <th>Registro</th>
                            <th>Actualización</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Factura ID</th>
                            <th>Correlativo</th>
                            <th>Cargo</th>
                            <th>N. Crédito</th>
                            <th>N. Débito</th>
                            <th>Abonos</th>
                            <th>Cargo Extra</th>
                            <th>Deducciones</th>
                            <th>ISV</th>
                            <th>Retención</th>
                            <th>Saldo</th>
                            <th>Registro</th>
                            <th>Actualización</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Tab: Movimientos --}}
        <div id="tab-movimientos" class="ap-panel d-none">
            <div class="ap-panel-title">
                <div class="ap-panel-icon orange"><i class="fa fa-exchange"></i></div>
                Movimientos por Facturas del Cliente
            </div>
            <div id="tbl_movimientos_div">
                <div class="table-responsive">
                    <table id="tbl_tipo_movimientos_cliente"
                           class="table table-sm table-hover w-100"
                           style="border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th>#Mov</th>
                                <th>#Pago</th>
                                <th>Factura</th>
                                <th>Monto</th>
                                <th>Tipo</th>
                                <th>Comentario</th>
                                <th>Estado</th>
                                <th>Registrado por</th>
                                <th>Fecha</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th>#Mov</th>
                                <th>#Pago</th>
                                <th>Factura</th>
                                <th>Monto</th>
                                <th>Tipo</th>
                                <th>Comentario</th>
                                <th>Estado</th>
                                <th>Registrado por</th>
                                <th>Fecha</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tab: Créditos y Abonos --}}
        <div id="tab-abonos" class="ap-panel d-none">
            <div class="ap-panel-title">
                <div class="ap-panel-icon green"><i class="fa fa-credit-card"></i></div>
                Créditos y Abonos por Factura
            </div>
            <div id="tbl_creditos_abonos_div">
                <div class="table-responsive">
                    <table id="tbl_abonos_cliente"
                           class="table table-sm table-hover w-100"
                           style="border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th>#Abono</th>
                                <th>#Pago</th>
                                <th>Factura</th>
                                <th>Monto</th>
                                <th>Comentario</th>
                                <th>Estado</th>
                                <th>Registrado por</th>
                                <th>Fecha</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th>#Abono</th>
                                <th>#Pago</th>
                                <th>Factura</th>
                                <th>Monto</th>
                                <th>Comentario</th>
                                <th>Estado</th>
                                <th>Registrado por</th>
                                <th>Fecha</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>{{-- /#tbl_principal_div --}}

</div>{{-- /.wrapper --}}
@push('scripts')
<script src="{{ asset('js/js_proyecto/cuentas-por-cobrar/pagos.js') }}?v={{ filemtime(public_path('js/js_proyecto/cuentas-por-cobrar/pagos.js')) }}"></script>
<script>
function switchTab(tabId, btn) {
    ['tab-facturas','tab-movimientos','tab-abonos'].forEach(function(id) {
        document.getElementById(id).classList.add('d-none');
    });
    document.querySelectorAll('.ap-tab-btn').forEach(function(b){ b.classList.remove('active'); });

    var panel = document.getElementById(tabId);
    panel.classList.remove('d-none');
    // restart fade-in animation
    panel.style.animation = 'none';
    panel.offsetHeight; // reflow
    panel.style.animation = 'ap-fadeInUp .35s ease both';

    btn.classList.add('active');
}

// ── Select2 en modal de abonos ──
function initAbonosSelects() {
    // selectMetodoPago: opciones estáticas, sin buscador
    var $metodo = $('#selectMetodoPago');
    try { if ($metodo.data('select2')) $metodo.select2('destroy'); } catch(e) {}
    $metodo.select2({
        dropdownParent: $('#modalAbonos'),
        dropdownCssClass: 'ap-select2-drop',
        minimumResultsForSearch: Infinity,
        width: '100%',
        language: { noResults: function() { return 'Sin resultados'; } }
    });

    // selectBanco: opciones dinámicas via AJAX → usar MutationObserver
    var $banco = $('#selectBanco');
    try { if ($banco.data('select2')) $banco.select2('destroy'); } catch(e) {}
    $banco.select2({
        dropdownParent: $('#modalAbonos'),
        dropdownCssClass: 'ap-select2-drop',
        width: '100%',
        language: { noResults: function() { return 'Sin resultados'; } }
    });
    // Cuando datosBanco() agregue <option> al <select>, Select2 se refresca
    if ($banco.data('_mo')) { try { $banco.data('_mo').disconnect(); } catch(e) {} }
    var mo = new MutationObserver(function() { $banco.trigger('change.select2'); });
    mo.observe($banco[0], { childList: true });
    $banco.data('_mo', mo);
}

function destroyAbonosSelects() {
    ['#selectMetodoPago', '#selectBanco'].forEach(function(id) {
        var $el = $(id);
        try { if ($el.data('_mo')) { $el.data('_mo').disconnect(); $el.data('_mo', null); } } catch(e) {}
        try { if ($el.data('select2')) $el.select2('destroy'); } catch(e) {}
    });
}

$('#modalAbonos').on('shown.bs.modal', function() {
    initAbonosSelects();
});
$('#modalAbonos').on('hidden.bs.modal', function() {
    destroyAbonosSelects();
    document.getElementById('selectBanco').innerHTML = '';
});

// ── Select2 en modal Otros Movimientos ──
function initOtrosMovSelects() {
    var $tipo = $('#selecttipoMovimiento');
    try { if ($tipo.data('select2')) $tipo.select2('destroy'); } catch(e) {}
    $tipo.select2({
        dropdownParent: $('#modalOtrosMovimientos'),
        dropdownCssClass: 'ap-select2-drop',
        minimumResultsForSearch: Infinity,
        width: '100%',
        language: { noResults: function() { return 'Sin resultados'; } }
    });
}

function destroyOtrosMovSelects() {
    var $tipo = $('#selecttipoMovimiento');
    try { if ($tipo.data('select2')) $tipo.select2('destroy'); } catch(e) {}
}

$('#modalOtrosMovimientos').on('shown.bs.modal', function() {
    initOtrosMovSelects();
});
$('#modalOtrosMovimientos').on('hidden.bs.modal', function() {
    destroyOtrosMovSelects();
});

// ── Select2 en modal Retención ISV ──
function initRetencionSelects() {
    var $ret = $('#selectTiporetencion');
    try { if ($ret.data('select2')) $ret.select2('destroy'); } catch(e) {}
    $ret.select2({
        dropdownParent: $('#modalretencion'),
        dropdownCssClass: 'ap-select2-drop',
        minimumResultsForSearch: Infinity,
        width: '100%',
        language: { noResults: function() { return 'Sin resultados'; } }
    });
}

function destroyRetencionSelects() {
    var $ret = $('#selectTiporetencion');
    try { if ($ret.data('select2')) $ret.select2('destroy'); } catch(e) {}
}

$('#modalretencion').on('shown.bs.modal', function() {
    initRetencionSelects();
});
$('#modalretencion').on('hidden.bs.modal', function() {
    destroyRetencionSelects();
});

// ── Select2 en modal Nota de Crédito ──
function initNCSelects() {
    // #selectNotaCredito: opciones ya cargadas vía AJAX antes de abrir el modal
    var $nc = $('#selectNotaCredito');
    try { if ($nc.data('select2')) $nc.select2('destroy'); } catch(e) {}
    $nc.select2({
        dropdownParent: $('#modalNC'),
        dropdownCssClass: 'ap-select2-drop',
        width: '100%',
        language: { noResults: function() { return 'Sin resultados'; } }
    });
    // Re-lanzar datosNotaCredito al cambiar selección vía Select2
    $nc.off('change.nc').on('change.nc', function() { datosNotaCredito(); });

    var $accion = $('#selectAplicado');
    try { if ($accion.data('select2')) $accion.select2('destroy'); } catch(e) {}
    $accion.select2({
        dropdownParent: $('#modalNC'),
        dropdownCssClass: 'ap-select2-drop',
        minimumResultsForSearch: Infinity,
        width: '100%',
        language: { noResults: function() { return 'Sin resultados'; } }
    });
}
function destroyNCSelects() {
    ['#selectNotaCredito','#selectAplicado'].forEach(function(id) {
        var $el = $(id);
        try { if ($el.data('select2')) { $el.off('change.nc'); $el.select2('destroy'); } } catch(e) {}
    });
}
$('#modalNC').on('shown.bs.modal', function() { initNCSelects(); });
$('#modalNC').on('hidden.bs.modal', function() { destroyNCSelects(); });

// ── Select2 en modal Nota de Débito ──
function initNDSelects() {
    var $nd = $('#selectNotaDebito');
    try { if ($nd.data('select2')) $nd.select2('destroy'); } catch(e) {}
    $nd.select2({
        dropdownParent: $('#modalND'),
        dropdownCssClass: 'ap-select2-drop',
        width: '100%',
        language: { noResults: function() { return 'Sin resultados'; } }
    });
    $nd.off('change.nd').on('change.nd', function() { datosNotaDebito(); });

    var $accion = $('#selectAplicadond');
    try { if ($accion.data('select2')) $accion.select2('destroy'); } catch(e) {}
    $accion.select2({
        dropdownParent: $('#modalND'),
        dropdownCssClass: 'ap-select2-drop',
        minimumResultsForSearch: Infinity,
        width: '100%',
        language: { noResults: function() { return 'Sin resultados'; } }
    });
}
function destroyNDSelects() {
    ['#selectNotaDebito','#selectAplicadond'].forEach(function(id) {
        var $el = $(id);
        try { if ($el.data('select2')) { $el.off('change.nd'); $el.select2('destroy'); } } catch(e) {}
    });
}
$('#modalND').on('shown.bs.modal', function() { initNDSelects(); });
$('#modalND').on('hidden.bs.modal', function() { destroyNDSelects(); });
</script>
@endpush
</div>{{-- /Livewire root --}}
