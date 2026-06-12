@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════
   Conciliación de Comisiones — Estilos
═══════════════════════════════════════════════════════════════ */
@keyframes conc-fadeDown { from{opacity:0;transform:translateY(-14px)} to{opacity:1;transform:translateY(0)} }
@keyframes conc-fadeUp   { from{opacity:0;transform:translateY(12px)}  to{opacity:1;transform:translateY(0)} }
@keyframes conc-fadeIn   { from{opacity:0} to{opacity:1} }
@keyframes conc-spin     { to{transform:rotate(360deg)} }
@keyframes conc-pulse    { 0%,100%{opacity:1} 50%{opacity:.5} }

/* ── TOP BAR COMPACTA ── */
.conc-topbar{
    background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 100%);
    border-radius:12px; padding:13px 20px;
    display:flex; align-items:center; gap:16px;
    box-shadow:0 4px 18px rgba(30,58,138,.25);
    animation:conc-fadeDown .4s ease both;
    flex-wrap:wrap;
    position:relative; overflow:hidden;
}
.conc-topbar::before{
    content:''; position:absolute; top:-30px; right:-30px;
    width:110px; height:110px; background:rgba(255,255,255,.06);
    border-radius:50%; pointer-events:none;
}
.conc-topbar-icon{
    width:36px; height:36px; background:rgba(255,255,255,.18);
    border-radius:9px; display:flex; align-items:center;
    justify-content:center; font-size:17px; color:#fff; flex-shrink:0;
}
.conc-topbar-title{
    color:#fff; font-weight:800; font-size:14px; line-height:1.2; flex-shrink:0;
}
.conc-topbar-title small{
    display:block; color:rgba(255,255,255,.65);
    font-size:10.5px; font-weight:600; margin-top:1px;
}
/* KPI en línea dentro de la topbar */
.conc-kpi-inline{
    display:flex; gap:6px; flex-wrap:wrap; flex:1; justify-content:flex-end;
}
.conc-kpi-chip{
    background:rgba(255,255,255,.13); border:1px solid rgba(255,255,255,.22);
    border-radius:8px; padding:5px 13px;
    display:flex; align-items:center; gap:8px; flex-shrink:0;
}
.conc-kpi-chip .ck-val{ font-size:15px; font-weight:800; color:#fff; line-height:1; }
.conc-kpi-chip .ck-lbl{ font-size:10px; font-weight:700; color:rgba(255,255,255,.68); text-transform:uppercase; letter-spacing:.3px; }
.conc-kpi-chip .ck-ico{ font-size:13px; color:rgba(255,255,255,.75); }
.conc-kpi-chip.ck-green{ border-color:rgba(110,231,183,.35); background:rgba(16,185,129,.18); }
.conc-kpi-chip.ck-amber{ border-color:rgba(252,211,77,.35); background:rgba(245,158,11,.18); }
.conc-kpi-chip.ck-purple{ border-color:rgba(196,181,253,.35); background:rgba(109,40,217,.18); }
.conc-btn-refresh{
    background:rgba(255,255,255,.18) !important; color:#fff !important;
    border:1.5px solid rgba(255,255,255,.42) !important; border-radius:8px;
    padding:7px 15px; font-size:12px; font-weight:700;
    display:inline-flex; align-items:center; gap:6px; cursor:pointer;
    transition:background .2s; flex-shrink:0; white-space:nowrap;
}
.conc-btn-refresh:hover{ background:rgba(255,255,255,.28) !important; }

/* ── PANEL ── */
.conc-panel{
    background:#fff; border-radius:12px;
    border:1.5px solid #e2e8f0;
    box-shadow:0 4px 18px rgba(0,0,0,.06);
    animation:conc-fadeUp .4s ease both; overflow:hidden;
}
.conc-panel-head{
    background:linear-gradient(135deg,#1e3a8a,#2563eb);
    padding:13px 20px; display:flex; align-items:center; gap:10px;
    color:#fff; font-size:13px; font-weight:800;
}
.conc-panel-head-ico{
    width:28px; height:28px; background:rgba(255,255,255,.20);
    border-radius:7px; display:flex; align-items:center;
    justify-content:center; font-size:13px; flex-shrink:0;
}
.conc-panel-body{ padding:20px 22px; }

/* ── FILTROS AÑO/MES ── */
.conc-filter-bar{
    display:flex; align-items:center; gap:10px; flex-wrap:wrap;
    padding:12px 18px; border-bottom:1px solid #e2e8f0; background:#f8fafc;
}
.conc-year-select{
    background:#fff; border:1.5px solid #e2e8f0; border-radius:8px;
    padding:5px 32px 5px 12px; font-size:12.5px; font-weight:700; color:#1e293b;
    cursor:pointer; outline:none; transition:border-color .15s;
    height:34px;
    appearance:none; -webkit-appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2364748b' stroke-width='1.8' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 10px center;
}
.conc-year-select:focus{ border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
.conc-mes-btn{
    background:#fff; border:1.5px solid #e2e8f0; border-radius:7px;
    padding:4px 11px; font-size:11.5px; font-weight:700; color:#64748b;
    cursor:pointer; transition:background .12s,border-color .12s,color .12s;
    white-space:nowrap;
}
.conc-mes-btn:hover{ background:#eff6ff; border-color:#93c5fd; color:#1e40af; }
.conc-mes-btn.active{ background:#1e3a8a; border-color:#1e3a8a; color:#fff; }
.conc-mes-btn.all{ border-style:dashed; }

/* ── PERÍODOS TABLE ── */
.conc-tbl { width:100%; border-collapse:collapse; }
.conc-tbl thead tr { background:linear-gradient(135deg,#f8fafc,#f1f5f9); }
.conc-tbl thead th {
    padding:11px 14px; font-size:10.5px; font-weight:800;
    text-transform:uppercase; letter-spacing:.5px; color:#475569;
    border-bottom:2px solid #e2e8f0; white-space:nowrap;
}
.conc-tbl tbody tr { border-bottom:1px solid #f1f5f9; transition:background .12s; }
.conc-tbl tbody tr:hover { background:#f0f7ff !important; }
.conc-tbl tbody tr.row-conciliado { background:#f0fdf4; }
.conc-tbl tbody tr.row-sin-abrir  { background:#fafafa; opacity:.72; }
.conc-tbl tbody tr.row-mes-actual { background:#fffbeb !important; }
.conc-tbl tbody td { padding:13px 14px; font-size:12.5px; color:#334155; vertical-align:middle; }

/* ── BADGES DE ESTADO ── */
.conc-estado {
    display:inline-flex; align-items:center; gap:5px;
    padding:4px 11px; border-radius:20px;
    font-size:10.5px; font-weight:800; white-space:nowrap;
}
.estado-abierto    { background:#fef3c7; color:#92400e; border:1px solid #fcd34d; }
.estado-conciliado { background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; }
.estado-sin-abrir  { background:#f1f5f9; color:#94a3b8; border:1px solid #e2e8f0; }
.estado-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.dot-abierto   { background:#f59e0b; animation:conc-pulse 2s ease infinite; }
.dot-conciliado{ background:#10b981; }
.dot-sin-abrir { background:#cbd5e1; }

/* ── BUTTONS INLINE ── */
.conc-btn {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:8px; border:none;
    font-size:11.5px; font-weight:700; cursor:pointer;
    transition:opacity .15s,transform .12s; white-space:nowrap;
}
.conc-btn:hover{ opacity:.85; transform:translateY(-1px); }
.conc-btn:active{ transform:translateY(0); }
.btn-conciliar  { background:#1e3a8a; color:#fff; }
.btn-detalle    { background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0 !important; }
.btn-reabrir    { background:#fef2f2; color:#b91c1c; border:1.5px solid #fecaca !important; }
.btn-disabled   { background:#f1f5f9 !important; color:#cbd5e1 !important; cursor:not-allowed !important; }

/* ── LOADER ── */
.conc-loader{
    display:flex; align-items:center; justify-content:center;
    gap:10px; padding:48px; color:#64748b; font-size:13px; font-weight:600;
}
.conc-spinner{
    width:22px; height:22px; border:3px solid #dbeafe;
    border-top-color:#2563eb; border-radius:50%;
    animation:conc-spin .7s linear infinite; flex-shrink:0;
}
.conc-spinner-sm{
    width:14px; height:14px; border-width:2px;
    display:inline-block; vertical-align:middle;
}

/* ── EMPTY ── */
.conc-empty{ text-align:center; padding:50px 20px; color:#94a3b8; }
.conc-empty i{ font-size:38px; margin-bottom:12px; display:block; opacity:.38; }
.conc-empty p{ font-size:14px; font-weight:700; color:#64748b; margin:0 0 5px; }

/* ── MODAL PERSONALIZADO ── */
.conc-modal .modal-content{
    border:none; border-radius:14px;
    box-shadow:0 20px 60px rgba(0,0,0,.18); overflow:hidden;
}
.conc-modal .modal-header{
    padding:16px 22px; border-bottom:none;
    border-radius:14px 14px 0 0;
}
.conc-modal .modal-header.blue { background:linear-gradient(135deg,#1e3a8a,#2563eb); }
.conc-modal .modal-header.red  { background:linear-gradient(135deg,#991b1b,#dc2626); }
.conc-modal .modal-title{ color:#fff; font-size:15px; font-weight:800; display:flex; align-items:center; gap:9px; }
.conc-modal .close{ color:rgba(255,255,255,.85)!important; opacity:1!important; font-size:22px; }
.conc-modal .modal-body{ padding:22px 24px; background:#fafbfc; }
.conc-modal .modal-footer{ background:#f4f6f9; border-top:1px solid #e2e8f0; padding:13px 22px; border-radius:0 0 14px 14px; }

/* ── DETALLE TABS ── */
.det-tabs{ display:flex; gap:3px; border-bottom:2px solid #e2e8f0; margin-bottom:16px; }
.det-tab{
    background:transparent; border:none; padding:8px 16px;
    font-size:12.5px; font-weight:700; color:#64748b; cursor:pointer;
    border-bottom:3px solid transparent; margin-bottom:-2px;
    border-radius:6px 6px 0 0; transition:color .15s;
}
.det-tab:hover { color:#2563eb; }
.det-tab.active{ color:#2563eb; border-bottom-color:#2563eb; background:#eff6ff; }

/* ── LOG TABLE ── */
.log-row-conciliacion{ background:#f0f9ff; }
.log-row-reapertura  { background:#fff5f5; }
.log-badge-conciliacion{ background:#dbeafe; color:#1e40af; border:1px solid #93c5fd; }
.log-badge-reapertura  { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }

/* ── RESUMEN CONCILIACIÓN ── */
.conc-resumen-grid{
    display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:16px;
}
.conc-resumen-card{
    background:#fff; border:1.5px solid #e2e8f0; border-radius:10px;
    padding:14px 16px; text-align:center;
    box-shadow:0 2px 8px rgba(0,0,0,.04);
}
.conc-resumen-card .cr-val{
    font-size:22px; font-weight:800; color:#1e3a8a; line-height:1.1; margin-bottom:3px;
}
.conc-resumen-card .cr-lbl{
    font-size:10.5px; font-weight:700; color:#94a3b8;
    text-transform:uppercase; letter-spacing:.4px;
}
.conc-resumen-card.cr-total{ border-color:#bfdbfe; background:linear-gradient(135deg,#eff6ff,#dbeafe); }
.conc-resumen-card.cr-total .cr-val{ color:#1d4ed8; font-size:24px; }
.conc-advertencia{
    background:linear-gradient(135deg,#fef2f2,#fee2e2);
    border:1.5px solid #fca5a5; border-radius:10px;
    padding:13px 16px; display:flex; align-items:flex-start; gap:10px;
    font-size:12.5px; color:#991b1b; margin-bottom:16px;
    font-weight:600;
}
.conc-advertencia i{ font-size:16px; flex-shrink:0; margin-top:1px; }

/* ── TABS DEL PANEL ── */
.conc-panel-tab-nav{
    background:linear-gradient(135deg,#1e3a8a,#2563eb);
    display:flex; align-items:stretch; gap:0;
    border-bottom:none; overflow:hidden;
}
.conc-tab-btn{
    background:transparent; border:none; border-bottom:3px solid transparent;
    color:rgba(255,255,255,.65); font-size:12.5px; font-weight:700;
    padding:13px 20px; cursor:pointer; display:flex; align-items:center; gap:8px;
    transition:background .15s, color .15s, border-color .15s;
    white-space:nowrap; position:relative;
}
.conc-tab-btn:hover{ background:rgba(255,255,255,.12); color:#fff; }
.conc-tab-btn.active{
    color:#fff; border-bottom-color:#facc15;
    background:rgba(255,255,255,.15);
}
.conc-tab-btn .conc-tab-badge{
    background:rgba(255,255,255,.22); border-radius:10px;
    padding:1px 8px; font-size:10px; font-weight:800; margin-left:2px;
}
.conc-tab-btn.active .conc-tab-badge{ background:rgba(255,215,0,.35); }
.conc-tab-pane{ display:none; }
.conc-tab-pane.active{ display:block; }

/* ── DÍAS DE GRACIA (REDISEÑO MODAL) ── */
.dg-infobar{
    background:#f0f9ff; border-bottom:1px solid #bae6fd;
    padding:9px 20px; font-size:11.5px; color:#0369a1;
    display:flex; align-items:center; gap:8px;
}
.dg-infobar i{ flex-shrink:0; }
/* Tabla de roles */
.dg-tbl-wrap{ padding:0; }
.dg-tbl{ width:100%; border-collapse:collapse; font-size:13px; }
.dg-tbl thead tr{
    background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);
    border-bottom:2px solid #e2e8f0;
}
.dg-tbl th{
    padding:12px 18px; font-size:10px; font-weight:800; color:#64748b;
    text-align:left; text-transform:uppercase; letter-spacing:.6px;
}
.dg-tbl th:first-child{ padding-left:24px; }
.dg-tbl th:last-child{ padding-right:24px; text-align:right; }
.dg-tbl td{
    padding:13px 18px; border-bottom:1px solid #f1f5f9;
    color:#334155; vertical-align:middle;
}
.dg-tbl td:first-child{ padding-left:24px; }
.dg-tbl td:last-child{ padding-right:24px; }
.dg-tbl tbody tr:last-child td{ border-bottom:none; }
.dg-tbl tbody tr{ transition:background .1s; }
.dg-tbl tbody tr:hover td{ background:#f8fafc; }
.dg-tbl tbody tr:hover td:first-child{ border-left:3px solid #2563eb; padding-left:21px; }
.dg-tbl-role{ font-weight:700; color:#0f172a; font-size:13.5px; }
/* Cell de días+retención */
.dg-cell{ display:flex; align-items:center; gap:8px; }
.dg-dias-badge{
    display:inline-flex; align-items:center; gap:4px;
    padding:4px 11px; border-radius:8px; font-size:12px; font-weight:700;
    letter-spacing:.2px;
}
.dg-dias-contado{ background:#f0fdf4; color:#15803d; border:1.5px solid #86efac; }
.dg-dias-credito{ background:#fff7ed; color:#c2410c; border:1.5px solid #fdba74; }
.dg-ret-badge{
    display:inline-flex; align-items:center; gap:3px;
    padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700;
    background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd;
}
.dg-none-badge{
    display:inline-flex; align-items:center;
    padding:4px 12px; border-radius:8px; font-size:11px; font-weight:600;
    background:#f8fafc; color:#cbd5e1; border:1px dashed #e2e8f0;
    letter-spacing:.2px;
}
.dg-edit-btn{
    background:#fff; border:1.5px solid #e2e8f0; color:#475569;
    border-radius:8px; padding:5px 14px; font-size:11.5px; font-weight:600;
    cursor:pointer; transition:all .15s; white-space:nowrap;
    display:inline-flex; align-items:center; gap:5px;
}
.dg-edit-btn:hover{ border-color:#2563eb; color:#2563eb; background:#eff6ff; box-shadow:0 2px 8px rgba(37,99,235,.1); }
/* Paginación */
.dg-pager{
    display:flex; align-items:center; justify-content:space-between;
    padding:10px 24px; border-top:1px solid #f1f5f9;
    font-size:11.5px; color:#94a3b8; background:#fafbfc;
    border-radius:0 0 12px 12px;
}
.dg-pager-btns{ display:flex; gap:4px; }
.dg-pager-btn{
    width:30px; height:30px; border:1px solid #e2e8f0; background:#fff;
    border-radius:7px; cursor:pointer; font-size:11.5px; font-weight:700;
    color:#475569; display:flex; align-items:center; justify-content:center;
    transition:all .12s;
}
.dg-pager-btn:hover:not(:disabled){ background:#eff6ff; border-color:#2563eb; color:#2563eb; }
.dg-pager-btn:disabled{ opacity:.3; cursor:not-allowed; }
.dg-pager-btn.active{ background:#1e3a8a; border-color:#1e3a8a; color:#fff; box-shadow:0 2px 6px rgba(30,58,138,.3); }
/* Modal días de gracia */
#modalDiasGracia{ z-index:1055; }
#modalDiasGracia .modal-backdrop{ z-index:1054; }
#modalDiasGracia .modal-content{
    border:none; border-radius:14px; overflow:hidden;
    box-shadow:0 24px 64px rgba(0,0,0,.22);
}
#modalDiasGracia .dgm-header{
    background:linear-gradient(135deg,#1e3a8a,#2563eb);
    padding:16px 22px; display:flex; align-items:center; gap:12px;
}
#modalDiasGracia .dgm-header-title{
    font-size:15px; font-weight:800; color:#fff;
    display:flex; align-items:center; gap:10px; flex:1;
}
#modalDiasGracia .dgm-header-role{
    font-size:11px; font-weight:700;
    background:rgba(255,255,255,.18); color:#fff;
    padding:3px 11px; border-radius:20px; border:1px solid rgba(255,255,255,.25);
    white-space:nowrap;
}
#modalDiasGracia .dgm-close{
    background:none; border:none; color:rgba(255,255,255,.7);
    font-size:22px; line-height:1; cursor:pointer; padding:0 2px;
    transition:color .12s;
}
#modalDiasGracia .dgm-close:hover{ color:#fff; }
/* Grid 2 columnas */
.dgm-grid{
    display:grid; grid-template-columns:1fr 1fr; gap:0;
    background:#fff;
}
.dgm-col{
    padding:20px 22px; border-top:none;
}
.dgm-col-contado{ border-right:1px solid #f1f5f9; }
.dgm-col-title{
    display:flex; align-items:center; gap:8px;
    font-size:13px; font-weight:800; margin-bottom:16px;
    padding-bottom:10px;
}
.dgm-col-title-contado{ color:#15803d; border-bottom:2px solid #bbf7d0; }
.dgm-col-title-credito { color:#c2410c; border-bottom:2px solid #fed7aa; }
.dgm-col-title-ico{
    width:28px; height:28px; border-radius:8px;
    display:flex; align-items:center; justify-content:center; font-size:13px;
    flex-shrink:0;
}
.dgm-col-title-ico-contado{ background:#f0fdf4; color:#16a34a; }
.dgm-col-title-ico-credito { background:#fff7ed; color:#ea580c; }
/* Campos */
.dgm-field{ margin-bottom:12px; }
.dgm-field:last-child{ margin-bottom:0; }
.dgm-lbl{
    font-size:10.5px; font-weight:700; color:#94a3b8;
    text-transform:uppercase; letter-spacing:.4px;
    margin-bottom:5px;
}
.dgm-stepper{ display:flex; align-items:center; }
.dgm-step{
    width:34px; height:38px; border:1.5px solid #e2e8f0; background:#f8fafc;
    color:#475569; font-size:18px; font-weight:700; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:all .12s; user-select:none; line-height:1;
}
.dgm-step:first-child{ border-radius:8px 0 0 8px; border-right:none; }
.dgm-step:last-child { border-radius:0 8px 8px 0; border-left:none; }
.dgm-step-contado:hover{ background:#f0fdf4; color:#16a34a; border-color:#86efac; }
.dgm-step-credito:hover { background:#fff7ed; color:#ea580c; border-color:#fdba74; }
.dgm-dias-inp{
    flex:1; height:38px; border:1.5px solid #e2e8f0; border-radius:0;
    padding:0 6px; font-size:18px; font-weight:800; color:#1e293b;
    text-align:center; outline:none; transition:border-color .15s; background:#fff;
    min-width:0; -moz-appearance:textfield;
}
.dgm-dias-inp::-webkit-inner-spin-button,
.dgm-dias-inp::-webkit-outer-spin-button{ -webkit-appearance:none; margin:0; }
.dgm-dias-inp:focus{ border-color:#2563eb; z-index:1; position:relative; }
.dgm-dias-unit{
    height:38px; padding:0 10px; border:1.5px solid #e2e8f0; border-left:none;
    border-radius:0 8px 8px 0; background:#f8fafc;
    font-size:11.5px; font-weight:700; color:#94a3b8;
    display:flex; align-items:center;
}
.dgm-pct-row{ display:flex; align-items:center; gap:8px; }
.dgm-pct-inp{
    width:100%; height:38px; border:1.5px solid #e2e8f0; border-radius:8px;
    padding:0 10px; font-size:16px; font-weight:800; color:#1e293b;
    text-align:center; outline:none; transition:border-color .15s; background:#fff;
    -moz-appearance:textfield; box-sizing:border-box;
}
.dgm-pct-inp::-webkit-inner-spin-button,
.dgm-pct-inp::-webkit-outer-spin-button{ -webkit-appearance:none; margin:0; }
.dgm-col-contado .dgm-pct-inp:focus{ border-color:#16a34a; }
.dgm-col-credito  .dgm-pct-inp:focus{ border-color:#ea580c; }
.dgm-pct-sym{
    font-size:14px; font-weight:800; color:#94a3b8; flex-shrink:0;
}
.dgm-nota-inp{
    width:100%; height:36px; border:1.5px solid #e2e8f0; border-radius:8px;
    padding:0 10px; font-size:12.5px; color:#475569; outline:none;
    transition:border-color .15s; background:#fff; box-sizing:border-box;
}
.dgm-nota-inp:focus{ border-color:#2563eb; }
.dgm-footer{
    padding:14px 22px; background:#f8fafc; border-top:1px solid #e2e8f0;
    display:flex; justify-content:flex-end; align-items:center; gap:10px;
}

/* ── AUDITORÍA TAB ── */
.aud-tipo-btn{
    padding:4px 12px; font-size:11.5px; font-weight:600; border-radius:6px;
    border:1.5px solid #cbd5e1; background:#fff; color:#475569; cursor:pointer;
    transition:all .15s;
}
.aud-tipo-btn.active{ background:#1e3a8a; border-color:#1e3a8a; color:#fff; }
.aud-tipo-btn:hover:not(.active){ background:#f1f5f9; }
.aud-card{
    background:#fff; border:1px solid #e2e8f0; border-radius:10px;
    padding:16px 20px; margin-bottom:10px;
    display:grid; grid-template-columns:1fr auto;
    gap:12px; align-items:center; cursor:default;
    transition:box-shadow .15s;
}
.aud-card:hover{ box-shadow:0 2px 12px rgba(30,58,138,.10); border-color:#bfdbfe; }
.aud-card-left{ display:flex; flex-direction:column; gap:5px; }
.aud-card-periodo{ font-size:15px; font-weight:800; color:#1e3a8a; }
.aud-card-fecha{ font-size:11.5px; color:#94a3b8; }
.aud-card-usuario{ font-size:12px; color:#475569; }
.aud-card-obs{ font-size:11.5px; color:#78350f; background:#fffbeb; border-radius:5px; padding:3px 8px; margin-top:2px; max-width:420px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.aud-badge-conciliacion{ background:#16a34a; color:#fff; padding:2px 10px; border-radius:10px; font-size:10.5px; font-weight:700; }
.aud-badge-reapertura{ background:#dc2626; color:#fff; padding:2px 10px; border-radius:10px; font-size:10.5px; font-weight:700; }
.aud-card-kpis{ display:flex; gap:16px; flex-wrap:wrap; align-items:center; }
.aud-kpi{ text-align:center; min-width:64px; }
.aud-kpi-val{ font-size:16px; font-weight:800; color:#1e3a8a; }
.aud-kpi-lbl{ font-size:10px; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:.4px; }
.aud-card-right{ display:flex; flex-direction:column; align-items:flex-end; gap:8px; }
.aud-btn-detalle{ background:#1e3a8a; color:#fff; border:none; border-radius:7px; padding:6px 14px; font-size:12px; font-weight:700; cursor:pointer; white-space:nowrap; }
.aud-btn-detalle:hover{ background:#1e40af; }
.aud-empty{ text-align:center; padding:40px 20px; color:#94a3b8; font-size:13px; }

    .conc-header{ padding:16px 16px; flex-direction:column; align-items:flex-start; }
    .conc-kpi-strip{ grid-template-columns:1fr 1fr; }
    .conc-panel-body{ padding:14px 14px; }
}

/* ── DETALLE TOOLBAR ── */
.det-toolbar{
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap:8px;
    padding:10px 16px; margin-bottom:0;
    background:#f8fafc; border-bottom:1px solid #e2e8f0;
}
.det-toolbar-info{
    font-size:12px; color:#64748b; font-weight:600;
    display:flex; align-items:center; gap:6px;
}
.det-toolbar-info i{ color:#94a3b8; font-size:11px; }
.det-export-btn{
    display:inline-flex; align-items:center; gap:6px;
    background:#fff; color:#15803d; border:1.5px solid #86efac;
    border-radius:8px; padding:6px 14px; font-size:11.5px; font-weight:700;
    cursor:pointer; transition:background .15s, box-shadow .15s;
    box-shadow:0 1px 4px rgba(21,128,61,.10);
}
.det-export-btn:hover{ background:#f0fdf4; box-shadow:0 3px 10px rgba(21,128,61,.18); }

/* ── DET TABS (rediseño) ── */
.det-tabs{
    display:flex; gap:0; border-bottom:2px solid #e2e8f0;
    background:#fff; padding:0 20px;
}
.det-tab{
    background:transparent; border:none; padding:13px 20px;
    font-size:13px; font-weight:700; color:#94a3b8; cursor:pointer;
    border-bottom:3px solid transparent; margin-bottom:-2px;
    transition:color .15s, border-color .15s;
    display:flex; align-items:center; gap:7px;
}
.det-tab:hover{ color:#2563eb; }
.det-tab.active{ color:#1e3a8a; border-bottom-color:#1e3a8a; background:transparent; }
.det-tab .det-badge{
    background:#e2e8f0; color:#64748b; border-radius:10px;
    padding:1px 7px; font-size:10px; font-weight:800; transition:background .15s,color .15s;
}
.det-tab.active .det-badge{ background:#1e3a8a; color:#fff; }

/* ── TABLE DENTRO DEL MODAL ── */
.det-tbl-wrap{ padding:0 20px 0; }
.det-tfoot-row td{
    background: linear-gradient(135deg,#eff6ff,#dbeafe);
    font-weight:800; border-top:2px solid #bfdbfe !important;
}

/* ── PAGINATION ── */
.det-pag{
    display:flex; align-items:center; justify-content:center;
    gap:4px; padding:12px 20px; border-top:1px solid #f1f5f9;
    background:#fafbfc; flex-wrap:wrap;
}
.det-pag-btn{
    min-width:32px; height:32px; border:1.5px solid #e2e8f0;
    background:#fff; color:#475569; border-radius:8px;
    font-size:12px; font-weight:700; cursor:pointer;
    display:inline-flex; align-items:center; justify-content:center;
    transition:background .12s,color .12s,border-color .12s,box-shadow .12s; padding:0 9px;
}
.det-pag-btn:hover{ background:#eff6ff; border-color:#93c5fd; color:#1e40af; box-shadow:0 2px 6px rgba(37,99,235,.12); }
.det-pag-btn.active{ background:#1e3a8a; color:#fff; border-color:#1e3a8a; box-shadow:0 3px 8px rgba(30,58,138,.25); }
.det-pag-btn:disabled{ opacity:.35; cursor:not-allowed; box-shadow:none; }
.det-pag-info{ font-size:11px; color:#94a3b8; font-weight:700; padding:0 8px; }
</style>
@endpush

<div>

{{-- ══════════════════════════════════════════════════════════════════
     TOP BAR COMPACTA (Header + KPIs en una sola línea)
══════════════════════════════════════════════════════════════════ --}}
<div class="conc-topbar" style="margin-top:18px;">
    {{-- Ícono + Título --}}
    <div class="conc-topbar-icon"><i class="fa fa-balance-scale"></i></div>
    <div class="conc-topbar-title">
        Conciliación de Comisiones
        <small>Control de períodos mensuales · Cierre contable</small>
    </div>

    {{-- KPIs inline --}}
    <div class="conc-kpi-inline">
        <div class="conc-kpi-chip ck-green">
            <i class="fa fa-check-circle ck-ico"></i>
            <div><div class="ck-val" id="kpi-conciliados">—</div><div class="ck-lbl">Conciliados</div></div>
        </div>
        <div class="conc-kpi-chip ck-amber">
            <i class="fa fa-clock-o ck-ico"></i>
            <div><div class="ck-val" id="kpi-abiertos">—</div><div class="ck-lbl">Abiertos</div></div>
        </div>
        <div class="conc-kpi-chip">
            <i class="fa fa-calendar-o ck-ico"></i>
            <div><div class="ck-val" id="kpi-sin-abrir">—</div><div class="ck-lbl">Futuros</div></div>
        </div>
        <div class="conc-kpi-chip">
            <i class="fa fa-dollar ck-ico"></i>
            <div><div class="ck-val" id="kpi-monto-abierto">—</div><div class="ck-lbl">Total Abierto</div></div>
        </div>
        <div class="conc-kpi-chip ck-purple">
            <i class="fa fa-lock ck-ico"></i>
            <div><div class="ck-val" id="kpi-monto-conciliado">—</div><div class="ck-lbl">Conciliado</div></div>
        </div>
    </div>

    <button class="conc-btn-refresh" id="btn-reload-periodos">
        <i class="fa fa-refresh"></i> Actualizar
    </button>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     TABLA DE PERÍODOS
══════════════════════════════════════════════════════════════════ --}}
<div class="conc-panel">
    {{-- Tab nav --}}
    <div class="conc-panel-tab-nav">
        <button class="conc-tab-btn active" id="tab-btn-periodos" onclick="concTab('periodos')">
            <i class="fa fa-calendar"></i>
            Histórico de Períodos de Comisión
            <span class="conc-tab-badge" id="badge-total-periodos">—</span>
        </button>
        <button class="conc-tab-btn" id="tab-btn-diasgracia" onclick="concTab('diasgracia')">
            <i class="fa fa-hourglass-half"></i>
            Días de Gracia por Rol
            <span class="conc-tab-badge" id="dg-badge-total">—</span>
        </button>
        <button class="conc-tab-btn" id="tab-btn-auditoria" onclick="concTab('auditoria')">
            <i class="fa fa-history"></i>
            Auditoría de Cambios
            <span class="conc-tab-badge" id="aud-badge-total">—</span>
        </button>
    </div>

    {{-- Tab 1: Períodos --}}
    <div id="conc-tab-periodos" class="conc-tab-pane active">
        {{-- Barra de filtros --}}
        <div class="conc-filter-bar">
            <i class="fa fa-filter" style="color:#94a3b8;font-size:12px;"></i>
            <select id="filtro-anio" class="conc-year-select" onchange="aplicarFiltros()">
                {{-- años generados por JS --}}
            </select>
            <div style="display:flex;gap:5px;flex-wrap:wrap;" id="filtro-meses">
                <button class="conc-mes-btn all active" data-mes="0" onclick="selMes(this)">Todos los meses</button>
                @foreach(['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'] as $i => $m)
                    <button class="conc-mes-btn" data-mes="{{ $i+1 }}" onclick="selMes(this)">{{ $m }}</button>
                @endforeach
            </div>
            <span style="margin-left:auto;font-size:11.5px;color:#94a3b8;font-weight:600;" id="filtro-count">—</span>
        </div>
        <div class="conc-panel-body" style="padding:0;">
            <div id="conc-tabla-wrapper" style="padding:16px 20px;">
                <div class="conc-loader"><div class="conc-spinner"></div> Cargando períodos...</div>
            </div>
        </div>
    </div>

    {{-- Tab 2: Días de Gracia --}}
    <div id="conc-tab-diasgracia" class="conc-tab-pane">

        {{-- Info bar --}}
        <div class="dg-infobar">
            <i class="fa fa-info-circle"></i>
            <span>Define los días de gracia y el porcentaje de retención por tipo de factura.
                <strong>Contado:</strong> desde fecha de pago.
                <strong>Crédito:</strong> desde vencimiento de factura.</span>
        </div>

        {{-- Tabla de roles --}}
        <div class="dg-tbl-wrap">
            <div id="dg-summary-body">
                <div class="conc-loader"><div class="conc-spinner"></div> Cargando...</div>
            </div>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     TAB 3: AUDITORÍA DE CAMBIOS (snapshots de conciliación/reapertura)
══════════════════════════════════════════════════════════════════ --}}
<div id="conc-tab-auditoria" class="conc-tab-pane" style="display:none;padding:0;">

    {{-- Barra de filtros --}}
    <div class="conc-filter-bar" style="padding:12px 20px;gap:10px;flex-wrap:wrap;">
        <i class="fa fa-filter" style="color:#94a3b8;font-size:12px;"></i>
        <select id="aud-filtro-anio" class="conc-year-select" onchange="audCargar()" style="width:110px;">
            <option value="0">Todos los años</option>
        </select>
        <div style="display:flex;gap:6px;">
            <button class="aud-tipo-btn active" data-tipo="todos" onclick="audFiltrarTipo(this)">Todos</button>
            <button class="aud-tipo-btn" data-tipo="conciliacion" onclick="audFiltrarTipo(this)">
                <i class="fa fa-lock"></i> Conciliaciones
            </button>
            <button class="aud-tipo-btn" data-tipo="reapertura" onclick="audFiltrarTipo(this)">
                <i class="fa fa-unlock"></i> Reaperturas
            </button>
        </div>
        <button class="conc-btn-refresh" onclick="audCargar()" style="margin-left:auto;">
            <i class="fa fa-refresh"></i> Actualizar
        </button>
    </div>

    {{-- Cuerpo --}}
    <div id="aud-body" style="padding:16px 20px;">
        <div class="conc-loader"><div class="conc-spinner"></div> Cargando historial...</div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL — DÍAS DE GRACIA & RETENCIÓN POR ROL
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDiasGracia" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:640px;">
        <div class="modal-content" style="border:none;border-radius:14px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.22);">

            {{-- Header --}}
            <div class="dgm-header">
                <div class="dgm-header-title">
                    <span style="width:32px;height:32px;background:rgba(255,255,255,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="fa fa-clock-o"></i>
                    </span>
                    Días de gracia &amp; retención
                    <span id="dgm-rol-badge" class="dgm-header-role"></span>
                </div>
                <button type="button" class="dgm-close" data-dismiss="modal">&times;</button>
            </div>

            {{-- Grid 2 columnas --}}
            <div class="dgm-grid">

                {{-- Contado --}}
                <div class="dgm-col dgm-col-contado">
                    <div class="dgm-col-title dgm-col-title-contado">
                        <span class="dgm-col-title-ico dgm-col-title-ico-contado">
                            <i class="fa fa-check-circle"></i>
                        </span>
                        <div>
                            <div style="font-size:13px;font-weight:800;line-height:1.2;">Contado</div>
                            <div style="font-size:10.5px;font-weight:500;color:#94a3b8;margin-top:1px;">desde fecha de pago</div>
                        </div>
                    </div>

                    <div class="dgm-field">
                        <div class="dgm-lbl">Días de gracia</div>
                        <div class="dgm-stepper">
                            <button type="button" class="dgm-step dgm-step-contado" onclick="dgmStep('dgm-cont-dias',-1)">−</button>
                            <input type="number" id="dgm-cont-dias" class="dgm-dias-inp" value="0" min="0" max="9999" />
                            <span class="dgm-dias-unit">días</span>
                        </div>
                    </div>

                    <div class="dgm-field">
                        <div class="dgm-lbl">% de retención</div>
                        <div class="dgm-pct-row">
                            <input type="number" id="dgm-cont-ret" class="dgm-pct-inp" value="0" min="0" max="100" step="0.01" />
                            <span class="dgm-pct-sym">%</span>
                        </div>
                    </div>

                    <div class="dgm-field">
                        <div class="dgm-lbl">Nota interna</div>
                        <input type="text" id="dgm-cont-desc" class="dgm-nota-inp" placeholder="Opcional..." />
                    </div>
                </div>

                {{-- Crédito --}}
                <div class="dgm-col dgm-col-credito">
                    <div class="dgm-col-title dgm-col-title-credito">
                        <span class="dgm-col-title-ico dgm-col-title-ico-credito">
                            <i class="fa fa-credit-card"></i>
                        </span>
                        <div>
                            <div style="font-size:13px;font-weight:800;line-height:1.2;">Crédito</div>
                            <div style="font-size:10.5px;font-weight:500;color:#94a3b8;margin-top:1px;">desde vencimiento de factura</div>
                        </div>
                    </div>

                    <div class="dgm-field">
                        <div class="dgm-lbl">Días de gracia</div>
                        <div class="dgm-stepper">
                            <button type="button" class="dgm-step dgm-step-credito" onclick="dgmStep('dgm-cred-dias',-1)">−</button>
                            <input type="number" id="dgm-cred-dias" class="dgm-dias-inp" value="0" min="0" max="9999" />
                            <span class="dgm-dias-unit">días</span>
                        </div>
                    </div>

                    <div class="dgm-field">
                        <div class="dgm-lbl">% de retención</div>
                        <div class="dgm-pct-row">
                            <input type="number" id="dgm-cred-ret" class="dgm-pct-inp" value="0" min="0" max="100" step="0.01" />
                            <span class="dgm-pct-sym">%</span>
                        </div>
                    </div>

                    <div class="dgm-field">
                        <div class="dgm-lbl">Nota interna</div>
                        <input type="text" id="dgm-cred-desc" class="dgm-nota-inp" placeholder="Opcional..." />
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="dgm-footer">
                <button type="button" class="btn btn-light btn-sm px-4" data-dismiss="modal"
                    style="font-weight:600;border:1.5px solid #e2e8f0;">
                    Cancelar
                </button>
                <button type="button" id="dgm-btn-guardar" class="btn btn-sm px-4" onclick="dgmGuardar()"
                    style="background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;font-weight:700;border:none;min-width:150px;box-shadow:0 2px 8px rgba(30,58,138,.30);">
                    <i class="fa fa-check mr-1"></i> Guardar cambios
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL — DETALLE SNAPSHOT DE AUDITORÍA
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade conc-modal" id="modalAuditoriaDetalle" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header" id="aud-modal-header" style="background:#1e3a8a;color:#fff;border-radius:12px 12px 0 0;">
                <h5 class="modal-title" id="aud-modal-title">
                    <i class="fa fa-history mr-2"></i> Snapshot de Auditoría
                </h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
            </div>
            <div class="modal-body" style="padding:24px;">

                {{-- KPIs del snapshot --}}
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;" id="aud-modal-kpis">
                </div>

                {{-- Info de quién hizo el cambio --}}
                <div id="aud-modal-meta" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:12.5px;color:#475569;">
                </div>

                {{-- Observación --}}
                <div id="aud-modal-obs-wrap" style="display:none;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;margin-bottom:20px;">
                    <div style="font-size:11px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">
                        <i class="fa fa-comment mr-1"></i> Observación
                    </div>
                    <div id="aud-modal-obs" style="font-size:13px;color:#78350f;"></div>
                </div>

                {{-- Tabs internas: Empleados / Facturas --}}
                <div style="display:flex;gap:0;border-bottom:2px solid #e2e8f0;margin-bottom:16px;">
                    <button class="aud-inner-tab active" id="aud-inner-tab-emp" onclick="audInnerTab('emp')" style="padding:8px 18px;font-size:12.5px;font-weight:700;border:none;background:none;color:#1e3a8a;border-bottom:2px solid #1e3a8a;margin-bottom:-2px;cursor:pointer;">
                        <i class="fa fa-users mr-1"></i> Empleados <span id="aud-inner-cnt-emp" style="background:#1e3a8a;color:#fff;border-radius:10px;padding:1px 7px;font-size:10px;margin-left:4px;">0</span>
                    </button>
                    <button class="aud-inner-tab" id="aud-inner-tab-fac" onclick="audInnerTab('fac')" style="padding:8px 18px;font-size:12.5px;font-weight:700;border:none;background:none;color:#64748b;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;">
                        <i class="fa fa-file-text-o mr-1"></i> Facturas <span id="aud-inner-cnt-fac" style="background:#64748b;color:#fff;border-radius:10px;padding:1px 7px;font-size:10px;margin-left:4px;">0</span>
                    </button>
                </div>

                {{-- Tabla empleados --}}
                <div id="aud-inner-emp" style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:12.5px;" id="aud-tbl-emp">
                        <thead style="background:#1e40af;color:#fff;">
                            <tr>
                                <th style="padding:8px 12px;text-align:left;font-weight:600;">Empleado</th>
                                <th style="padding:8px 12px;text-align:left;font-weight:600;">Rol</th>
                                <th style="padding:8px 12px;text-align:right;font-weight:600;">Comisión Acumulada</th>
                                <th style="padding:8px 12px;text-align:center;font-weight:600;">Facturas</th>
                                <th style="padding:8px 12px;text-align:left;font-weight:600;">Mes</th>
                            </tr>
                        </thead>
                        <tbody id="aud-tbl-emp-body"></tbody>
                    </table>
                </div>

                {{-- Tabla facturas --}}
                <div id="aud-inner-fac" style="overflow-x:auto;display:none;">
                    <table style="width:100%;border-collapse:collapse;font-size:12.5px;" id="aud-tbl-fac">
                        <thead style="background:#1e40af;color:#fff;">
                            <tr>
                                <th style="padding:8px 12px;text-align:center;font-weight:600;"># Factura</th>
                                <th style="padding:8px 12px;text-align:left;font-weight:600;">Fecha Cierre</th>
                                <th style="padding:8px 12px;text-align:right;font-weight:600;">Monto Rol</th>
                                <th style="padding:8px 12px;text-align:left;font-weight:600;">Rol</th>
                                <th style="padding:8px 12px;text-align:center;font-weight:600;">Tipo</th>
                            </tr>
                        </thead>
                        <tbody id="aud-tbl-fac-body"></tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer" style="border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL — CONCILIAR PERÍODO
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade conc-modal" id="modalConciliar" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header blue">
                <h5 class="modal-title"><i class="fa fa-lock"></i> Conciliar Período</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                {{-- Loader mientras carga resumen --}}
                <div id="conc-resumen-loader" style="display:flex;align-items:center;gap:10px;padding:24px 0;color:#64748b;font-size:13px;font-weight:600;">
                    <div class="conc-spinner"></div> Calculando totales del período...
                </div>

                {{-- Resumen (se muestra tras cargar) --}}
                <div id="conc-resumen-wrap" style="display:none;">
                    {{-- Período --}}
                    <div style="text-align:center;margin-bottom:16px;">
                        <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Período a conciliar</div>
                        <div style="font-size:20px;font-weight:800;color:#1e3a8a;" id="conc-resumen-label">—</div>
                    </div>

                    {{-- Cards de totales --}}
                    <div class="conc-resumen-grid">
                        <div class="conc-resumen-card">
                            <div class="cr-val" id="cr-empleados">—</div>
                            <div class="cr-lbl"><i class="fa fa-users mr-1"></i> Empleados</div>
                        </div>
                        <div class="conc-resumen-card">
                            <div class="cr-val" id="cr-facturas">—</div>
                            <div class="cr-lbl"><i class="fa fa-file-text-o mr-1"></i> Facturas</div>
                        </div>
                        <div class="conc-resumen-card cr-total">
                            <div class="cr-val" id="cr-total">—</div>
                            <div class="cr-lbl"><i class="fa fa-dollar mr-1"></i> Total Comisiones</div>
                        </div>
                    </div>

                    {{-- Advertencia de bloqueo --}}
                    <div class="conc-advertencia">
                        <i class="fa fa-exclamation-triangle"></i>
                        <div>
                            A partir de este momento <strong>no se aceptarán nuevas comisiones</strong>
                            para el período <strong id="conc-adv-label">—</strong>.
                            Esta acción queda registrada en el log de auditoría con un snapshot completo.
                        </div>
                    </div>

                    {{-- Observación --}}
                    <div>
                        <label style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:6px;">
                            <i class="fa fa-comment mr-1"></i> Observación <span style="color:#94a3b8;font-weight:600;">(opcional)</span>
                        </label>
                        <textarea id="conc-obs-conciliar" rows="2" class="form-control"
                            style="border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;resize:none;"
                            placeholder="Ej: Cierre de mayo 2026 revisado y aprobado..."></textarea>
                    </div>
                </div>

                <input type="hidden" id="conc-periodo-conciliar">
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-secondary" data-dismiss="modal">
                    <i class="fa fa-arrow-left mr-1"></i> Cancelar
                </button>
                <button class="btn btn-sm btn-primary px-4" id="btn-confirmar-conciliar"
                    style="background:#1e3a8a;border-color:#1e3a8a;font-weight:700;" disabled>
                    <i class="fa fa-lock mr-1"></i> Confirmar Conciliación
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL — REABRIR PERÍODO
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade conc-modal" id="modalReabrir" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content">
            <div class="modal-header red">
                <h5 class="modal-title"><i class="fa fa-unlock"></i> Reabrir Período Conciliado</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div style="background:#fff5f5;border-left:4px solid #dc2626;border-radius:6px;padding:12px 14px;margin-bottom:18px;font-size:12.5px;color:#991b1b;">
                    <i class="fa fa-exclamation-triangle mr-1"></i>
                    Reabrir <strong id="conc-modal-label-reabrir">—</strong> permitirá nuevamente la
                    acreditación de comisiones. Se guardará un <strong>snapshot del estado actual</strong>
                    antes de reabrir como registro de auditoría.
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:6px;">
                        <i class="fa fa-comment mr-1"></i> Motivo de reapertura <span style="color:#dc2626;">*</span>
                    </label>
                    <textarea id="conc-obs-reabrir" rows="3" class="form-control"
                        style="border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;resize:none;"
                        placeholder="Ingrese el motivo obligatorio para reabrir..."></textarea>
                </div>
                <input type="hidden" id="conc-periodo-reabrir">
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-secondary" data-dismiss="modal">
                    <i class="fa fa-arrow-left mr-1"></i> Cancelar
                </button>
                <button class="btn btn-sm btn-danger px-4" id="btn-confirmar-reabrir"
                    style="font-weight:700;">
                    <i class="fa fa-unlock mr-1"></i> Confirmar Reapertura
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL — DETALLE DE PERÍODO
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade conc-modal" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable"
         style="max-width:1100px; width:calc(100% - 40px); margin:90px auto 20px;">
        <div class="modal-content" style="border:none;border-radius:14px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.22);">

            {{-- Header --}}
            <div class="modal-header blue" style="padding:0;border:none;flex-shrink:0;">
                <div style="padding:16px 24px;display:flex;align-items:center;gap:12px;flex:1;">
                    <div style="width:36px;height:36px;background:rgba(255,255,255,.20);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:16px;color:#fff;">
                        <i class="fa fa-calendar-check-o"></i>
                    </div>
                    <div>
                        <div style="color:rgba(255,255,255,.72);font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">Conciliación de Comisiones</div>
                        <h5 class="modal-title" style="margin:0;font-size:15px;font-weight:800;color:#fff;">
                            <span id="det-label">—</span>
                        </h5>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal"
                    style="color:rgba(255,255,255,.75)!important;opacity:1!important;font-size:20px;padding:16px 20px;margin:0;self-align:flex-start;">
                    &times;
                </button>
            </div>

            {{-- Tabs --}}
            <div class="det-tabs" style="flex-shrink:0;">
                <button class="det-tab active" onclick="detTab('det-empleados',this)">
                    <i class="fa fa-users"></i> Empleados
                    <span class="det-badge" id="det-cnt-emp">—</span>
                </button>
                <button class="det-tab" onclick="detTab('det-facturas',this)">
                    <i class="fa fa-file-text-o"></i> Facturas
                    <span class="det-badge" id="det-cnt-fac">—</span>
                </button>
                <button class="det-tab" onclick="detTab('det-logs',this)">
                    <i class="fa fa-history"></i> Historial
                    <span class="det-badge" id="det-cnt-log">—</span>
                </button>
            </div>

            {{-- Body scrollable --}}
            <div class="modal-body" style="padding:0;background:#fafbfc;">

                {{-- TAB: Empleados --}}
                <div id="det-empleados">
                    <div class="conc-loader" id="det-loader-emp" style="padding:48px;">
                        <div class="conc-spinner"></div> Cargando empleados...
                    </div>
                    <div class="d-none" id="det-tbl-emp-wrap">
                        <div class="det-toolbar">
                            <span class="det-toolbar-info">
                                <i class="fa fa-users"></i>
                                <span id="det-toolbar-emp-info">—</span>
                            </span>
                            <button class="det-export-btn" onclick="exportarExcel('emp')">
                                <i class="fa fa-file-excel-o"></i> Exportar Excel
                            </button>
                        </div>
                        <div class="table-responsive" style="margin:0;">
                            <table class="conc-tbl" id="det-tbl-emp" style="margin:0;">
                                <thead><tr>
                                    <th style="width:44px;text-align:center;">#</th>
                                    <th>Empleado</th>
                                    <th>Rol</th>
                                    <th class="text-right">Comisión Acumulada</th>
                                    <th class="text-center">Facturas</th>
                                    <th>Última Modificación</th>
                                </tr></thead>
                                <tbody id="det-emp-body"></tbody>
                                <tfoot>
                                    <tr class="det-tfoot-row">
                                        <td colspan="3" style="text-align:right;font-size:12px;color:#1e40af;padding:12px 14px;">
                                            <i class="fa fa-sigma" style="margin-right:4px;"></i> TOTAL PERÍODO
                                        </td>
                                        <td class="text-right" id="det-emp-total"
                                            style="color:#1e3a8a;font-size:15px;padding:12px 14px;">—</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="det-pag" id="pag-emp"></div>
                    </div>
                </div>

                {{-- TAB: Facturas --}}
                <div id="det-facturas" class="d-none">
                    <div class="det-toolbar">
                        <span class="det-toolbar-info">
                            <i class="fa fa-file-text-o"></i>
                            <span id="det-toolbar-fac-info">—</span>
                        </span>
                        <button class="det-export-btn" onclick="exportarExcel('fac')">
                            <i class="fa fa-file-excel-o"></i> Exportar Excel
                        </button>
                    </div>
                    <div class="table-responsive" style="margin:0;">
                        <table class="conc-tbl" id="det-tbl-fac" style="margin:0;">
                            <thead><tr>
                                <th style="width:44px;text-align:center;">#</th>
                                <th>Factura</th>
                                <th>Correlativo</th>
                                <th>Cliente</th>
                                <th>Empleado</th>
                                <th>Rol / Tipo</th>
                                <th>Fecha Cierre</th>
                                <th class="text-right">Comisión</th>
                            </tr></thead>
                            <tbody id="det-fac-body"></tbody>
                        </table>
                    </div>
                    <div class="det-pag" id="pag-fac"></div>
                </div>

                {{-- TAB: Logs --}}
                <div id="det-logs" class="d-none">
                    <div class="det-toolbar">
                        <span class="det-toolbar-info">
                            <i class="fa fa-history"></i>
                            <span id="det-toolbar-log-info">—</span>
                        </span>
                        <button class="det-export-btn" onclick="exportarExcel('log')">
                            <i class="fa fa-file-excel-o"></i> Exportar Excel
                        </button>
                    </div>
                    <div class="table-responsive" style="margin:0;">
                        <table class="conc-tbl" id="det-tbl-log" style="margin:0;">
                            <thead><tr>
                                <th>Acción</th>
                                <th>Anterior</th>
                                <th>Nuevo</th>
                                <th class="text-right">Total Snapshot</th>
                                <th class="text-center">Empls.</th>
                                <th class="text-center">Facts.</th>
                                <th>Observación</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                            </tr></thead>
                            <tbody id="det-log-body"></tbody>
                        </table>
                    </div>
                    <div class="det-pag" id="pag-log"></div>
                </div>

            </div>{{-- /modal-body --}}

            {{-- Footer --}}
            <div class="modal-footer"
                 style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:12px 20px;border-radius:0 0 14px 14px;flex-shrink:0;">
                <button class="btn btn-sm" data-dismiss="modal"
                    style="background:#fff;border:1.5px solid #e2e8f0;color:#64748b;font-weight:700;border-radius:8px;padding:7px 20px;font-size:12.5px;">
                    <i class="fa fa-times mr-1"></i> Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

</div>{{-- /Livewire root --}}

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
/* ═══════════════════════════════════════════════════════════════
   Conciliación de Comisiones — JS
═══════════════════════════════════════════════════════════════ */

const DET_PAGE_SIZE = 8;
let detData = { empleados:[], facturas:[], logs:[], label:'', periodo:'' };
let detPage = { emp:1, fac:1, log:1 };
let todosLosPeriodos = [];   // cache de todos los períodos cargados

/* ── Cargar períodos ─────────────────────────────────────────── */
function cargarPeriodos() {
    document.getElementById('conc-tabla-wrapper').innerHTML =
        '<div class="conc-loader"><div class="conc-spinner"></div> Cargando períodos...</div>';
    axios.get('/comisiones/conciliacion/periodos')
        .then(r => {
            todosLosPeriodos = r.data.periodos || [];
            renderKpis(r.data.kpis || {});
            poblarFiltroAnios(todosLosPeriodos);
            aplicarFiltros();
        })
        .catch(() => {
            document.getElementById('conc-tabla-wrapper').innerHTML =
                '<div class="alert alert-danger m-3"><i class="fa fa-times-circle mr-1"></i> Error al cargar períodos.</div>';
        });
}

/* ── KPIs ────────────────────────────────────────────────────── */
function renderKpis(kpis) {
    document.getElementById('kpi-conciliados').textContent      = kpis.total_conciliados ?? 0;
    document.getElementById('kpi-abiertos').textContent         = kpis.total_abiertos    ?? 0;
    document.getElementById('kpi-sin-abrir').textContent        = kpis.total_sin_abrir   ?? 0;
    document.getElementById('kpi-monto-abierto').textContent    = 'L ' + numFmt(kpis.monto_abierto ?? 0);
    document.getElementById('kpi-monto-conciliado').textContent = 'L ' + numFmt(kpis.monto_conciliado ?? 0);
}

/* ── Poblar selector de años ─────────────────────────────────── */
function poblarFiltroAnios(periodos) {
    const sel = document.getElementById('filtro-anio');
    const aniosSet = new Set(periodos.map(p => p.anio));
    const anioActual = new Date().getFullYear();
    const anios = Array.from(aniosSet).sort((a,b) => b - a);
    sel.innerHTML = anios.map(a =>
        `<option value="${a}" ${a === anioActual ? 'selected' : ''}>${a}</option>`
    ).join('');
}

/* ── Seleccionar mes ─────────────────────────────────────────── */
function selMes(btn) {
    document.querySelectorAll('#filtro-meses .conc-mes-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    aplicarFiltros();
}

/* ── Aplicar filtros año + mes ───────────────────────────────── */
function aplicarFiltros() {
    const anio = parseInt(document.getElementById('filtro-anio').value);
    const mesBtn = document.querySelector('#filtro-meses .conc-mes-btn.active');
    const mes  = mesBtn ? parseInt(mesBtn.dataset.mes) : 0;

    let filtrados = todosLosPeriodos.filter(p => p.anio === anio);
    if (mes > 0) filtrados = filtrados.filter(p => p.mes === mes);

    document.getElementById('filtro-count').textContent = filtrados.length + ' período' + (filtrados.length !== 1 ? 's' : '');
    renderPeriodos({ periodos: filtrados });
}

function renderPeriodos(data) {
    const periodos = data.periodos || [];
    document.getElementById('badge-total-periodos').textContent = periodos.length + ' registros';

    if (periodos.length === 0) {
        document.getElementById('conc-tabla-wrapper').innerHTML =
            '<div class="conc-empty"><i class="fa fa-calendar-o"></i><p>Sin períodos</p></div>';
        return;
    }

    let html = '<div class="table-responsive"><table class="conc-tbl">'
        + '<thead><tr><th>Período</th><th>Estado</th>'
        + '<th class="text-right">Total Comisiones</th>'
        + '<th class="text-center">Empleados</th>'
        + '<th class="text-center">Facturas</th>'
        + '<th>Conciliado por</th><th>Fecha Conciliación</th>'
        + '<th class="text-center">Acciones</th></tr></thead><tbody>';

    periodos.forEach(p => {
        const rowClass = p.estado === 'conciliado' ? 'row-conciliado'
                       : p.estado === 'sin_abrir'  ? 'row-sin-abrir'
                       : p.es_mes_actual           ? 'row-mes-actual' : '';
        const mesActBadge = p.es_mes_actual
            ? '<span style="background:#f59e0b;color:#fff;border-radius:8px;padding:1px 6px;font-size:10px;font-weight:800;margin-left:6px;">MES ACTUAL</span>'
            : '';
        html += `<tr class="${rowClass}">
            <td style="font-weight:700;color:#1e293b;">${p.periodo_label}${mesActBadge}</td>
            <td>${badgeEstado(p.estado)}</td>
            <td class="text-right" style="font-weight:700;color:#1e3a8a;">${p.total_comision > 0 ? 'L ' + numFmt(p.total_comision) : '<span style="color:#cbd5e1;">—</span>'}</td>
            <td class="text-center">${p.cantidad_empleados > 0 ? p.cantidad_empleados : '<span style="color:#cbd5e1;">—</span>'}</td>
            <td class="text-center">${p.cantidad_facturas > 0 ? p.cantidad_facturas : '<span style="color:#cbd5e1;">—</span>'}</td>
            <td style="font-size:12px;color:#64748b;">${p.usuario_concilio ?? '<span style="color:#cbd5e1;">—</span>'}</td>
            <td style="font-size:12px;color:#64748b;">${p.fecha_conciliacion ? fmtFecha(p.fecha_conciliacion) : '<span style="color:#cbd5e1;">—</span>'}</td>
            <td class="text-center">${renderAcciones(p)}</td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    document.getElementById('conc-tabla-wrapper').innerHTML = html;
}

function renderAcciones(p) {
    if (p.estado === 'sin_abrir') return '<span style="font-size:11px;color:#cbd5e1;"><i class="fa fa-clock-o mr-1"></i>Futuro</span>';
    if (p.estado === 'conciliado') {
        return `<div style="display:flex;gap:5px;justify-content:center;flex-wrap:wrap;">
            <button class="conc-btn btn-detalle" onclick="abrirDetalle('${p.periodo}')"><i class="fa fa-search"></i> Ver detalle</button>
            <button class="conc-btn btn-reabrir" onclick="abrirReabrir('${p.periodo}','${p.periodo_label}')"><i class="fa fa-unlock"></i> Reabrir</button>
        </div>`;
    }
    const btnConciliar = (p.total_comision > 0 || p.es_mes_actual)
        ? `<button class="conc-btn btn-conciliar" onclick="abrirConciliar('${p.periodo}','${p.periodo_label}')"><i class="fa fa-lock"></i> Conciliar</button>`
        : `<button class="conc-btn btn-conciliar btn-disabled" disabled><i class="fa fa-lock"></i> Conciliar</button>`;
    return `<div style="display:flex;gap:5px;justify-content:center;flex-wrap:wrap;">
        ${btnConciliar}
        <button class="conc-btn btn-detalle" onclick="abrirDetalle('${p.periodo}')"><i class="fa fa-search"></i> Ver detalle</button>
    </div>`;
}

function badgeEstado(e) {
    if (e === 'conciliado') return '<span class="conc-estado estado-conciliado"><span class="estado-dot dot-conciliado"></span> Conciliado</span>';
    if (e === 'abierto')   return '<span class="conc-estado estado-abierto"><span class="estado-dot dot-abierto"></span> Sin Conciliar</span>';
    return '<span class="conc-estado estado-sin-abrir"><span class="estado-dot dot-sin-abrir"></span> Sin Abrir</span>';
}

/* ── Conciliar ───────────────────────────────────────────────── */
function abrirConciliar(periodo, label) {
    // Reset modal
    document.getElementById('conc-periodo-conciliar').value = periodo;
    document.getElementById('conc-obs-conciliar').value = '';
    document.getElementById('conc-resumen-loader').style.display = 'flex';
    document.getElementById('conc-resumen-wrap').style.display   = 'none';
    document.getElementById('btn-confirmar-conciliar').disabled  = true;
    $('#modalConciliar').modal('show');

    // Cargar resumen real del período
    axios.get('/comisiones/conciliacion/detalle', { params: { periodo } })
        .then(r => {
            const d = r.data;
            const totalComision = (d.empleados || []).reduce((s, e) => s + parseFloat(e.comision_acumulada || 0), 0);
            document.getElementById('conc-resumen-label').textContent = d.label || label;
            document.getElementById('conc-adv-label').textContent     = d.label || label;
            document.getElementById('cr-empleados').textContent = d.empleados.length;
            document.getElementById('cr-facturas').textContent  = d.facturas.length;
            document.getElementById('cr-total').textContent     = 'L ' + numFmt(totalComision);
            document.getElementById('conc-resumen-loader').style.display = 'none';
            document.getElementById('conc-resumen-wrap').style.display   = '';
            document.getElementById('btn-confirmar-conciliar').disabled  = false;
        })
        .catch(() => {
            // Si falla el detalle, igual mostrar con datos básicos de la tabla
            document.getElementById('conc-resumen-label').textContent = label;
            document.getElementById('conc-adv-label').textContent     = label;
            document.getElementById('cr-empleados').textContent = '—';
            document.getElementById('cr-facturas').textContent  = '—';
            document.getElementById('cr-total').textContent     = '—';
            document.getElementById('conc-resumen-loader').style.display = 'none';
            document.getElementById('conc-resumen-wrap').style.display   = '';
            document.getElementById('btn-confirmar-conciliar').disabled  = false;
        });
}
document.getElementById('btn-confirmar-conciliar').addEventListener('click', function() {
    const periodo = document.getElementById('conc-periodo-conciliar').value;
    const observacion = document.getElementById('conc-obs-conciliar').value.trim();
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="conc-spinner conc-spinner-sm"></span> Procesando...';
    axios.post('/comisiones/conciliacion/conciliar', { periodo, observacion })
        .then(r => { $('#modalConciliar').modal('hide'); Swal.fire({ icon:r.data.icon, title:r.data.title, text:r.data.text }); cargarPeriodos(); })
        .catch(err => { const d = err.response?.data||{}; Swal.fire({ icon:d.icon||'error', title:d.title||'Error', text:d.text||'Error inesperado.' }); })
        .finally(() => { btn.disabled=false; btn.innerHTML='<i class="fa fa-lock mr-1"></i> Confirmar Conciliación'; });
});

/* ── Reabrir ─────────────────────────────────────────────────── */
function abrirReabrir(periodo, label) {
    document.getElementById('conc-modal-label-reabrir').textContent = label;
    document.getElementById('conc-periodo-reabrir').value = periodo;
    document.getElementById('conc-obs-reabrir').value = '';
    $('#modalReabrir').modal('show');
}
document.getElementById('btn-confirmar-reabrir').addEventListener('click', function() {
    const periodo = document.getElementById('conc-periodo-reabrir').value;
    const observacion = document.getElementById('conc-obs-reabrir').value.trim();
    const btn = this;
    if (!observacion) { document.getElementById('conc-obs-reabrir').style.borderColor='#dc2626'; document.getElementById('conc-obs-reabrir').focus(); return; }
    btn.disabled = true;
    btn.innerHTML = '<span class="conc-spinner conc-spinner-sm"></span> Procesando...';
    axios.post('/comisiones/conciliacion/reabrir', { periodo, observacion })
        .then(r => { $('#modalReabrir').modal('hide'); Swal.fire({ icon:r.data.icon, title:r.data.title, text:r.data.text }); cargarPeriodos(); })
        .catch(err => { const d = err.response?.data||{}; Swal.fire({ icon:d.icon||'error', title:d.title||'Error', text:d.text||'Error inesperado.' }); })
        .finally(() => { btn.disabled=false; btn.innerHTML='<i class="fa fa-unlock mr-1"></i> Confirmar Reapertura'; });
});
$('#modalReabrir').on('shown.bs.modal', function() {
    document.getElementById('conc-obs-reabrir').style.borderColor = '#e2e8f0';
});

/* ── Detalle ─────────────────────────────────────────────────── */
function abrirDetalle(periodo) {
    detData = { empleados:[], facturas:[], logs:[], label:'—', periodo };
    detPage = { emp:1, fac:1, log:1 };
    document.getElementById('det-label').textContent = '—';
    detTab('det-empleados', document.querySelector('.det-tab'));
    document.getElementById('det-loader-emp').classList.remove('d-none');
    document.getElementById('det-tbl-emp-wrap').classList.add('d-none');
    ['det-emp-body','det-fac-body','det-log-body'].forEach(id => document.getElementById(id).innerHTML = '');
    ['pag-emp','pag-fac','pag-log'].forEach(id => document.getElementById(id).innerHTML = '');
    ['det-cnt-emp','det-cnt-fac','det-cnt-log'].forEach(id => document.getElementById(id).textContent = '—');
    $('#modalDetalle').modal('show');
    axios.get('/comisiones/conciliacion/detalle', { params:{ periodo } })
        .then(r => renderDetalle(r.data))
        .catch(() => { document.getElementById('det-loader-emp').innerHTML = '<div class="alert alert-danger"><i class="fa fa-times-circle mr-1"></i> Error al cargar.</div>'; });
}

function renderDetalle(d) {
    detData = d;
    detPage = { emp:1, fac:1, log:1 };
    document.getElementById('det-label').textContent    = d.label;
    document.getElementById('det-cnt-emp').textContent  = d.empleados.length;
    document.getElementById('det-cnt-fac').textContent  = d.facturas.length;
    document.getElementById('det-cnt-log').textContent  = d.logs.length;
    renderEmpPage(1);
    renderFacPage(1);
    renderLogPage(1);
    document.getElementById('det-loader-emp').classList.add('d-none');
    document.getElementById('det-tbl-emp-wrap').classList.remove('d-none');
}

/* ── Página: Empleados ──────────────────────────────────────── */
function renderEmpPage(page) {
    detPage.emp = page;
    const arr = detData.empleados || [], total = arr.length;
    const start = (page-1)*DET_PAGE_SIZE, end = Math.min(start+DET_PAGE_SIZE, total);
    let totalComision = arr.reduce((s,e) => s + parseFloat(e.comision_acumulada||0), 0);
    let html = '';
    arr.slice(start, end).forEach((e,i) => {
        html += `<tr>
            <td style="color:#94a3b8;font-size:11px;">${start+i+1}</td>
            <td style="font-weight:700;"><i class="fa fa-user mr-1 text-muted" style="font-size:11px;"></i>${e.nombre}</td>
            <td><span style="background:#eff6ff;color:#1e40af;border-radius:6px;padding:2px 7px;font-size:11px;font-weight:700;">${e.rol}</span></td>
            <td class="text-right" style="font-weight:700;color:#1e3a8a;">L ${numFmt(e.comision_acumulada)}</td>
            <td class="text-center">${e.facturas}</td>
            <td style="font-size:11.5px;color:#64748b;">${e.fecha_ult_modificacion ? fmtFecha(e.fecha_ult_modificacion) : '—'}</td>
        </tr>`;
    });
    document.getElementById('det-emp-body').innerHTML = html || '<tr><td colspan="6" class="text-center text-muted" style="padding:24px;">Sin empleados con comisión en este período</td></tr>';
    document.getElementById('det-emp-total').textContent = 'L ' + numFmt(totalComision);
    document.getElementById('det-toolbar-emp-info').textContent = total > 0 ? `Mostrando ${start+1}–${end} de ${total} empleados` : 'Sin registros';
    renderPaginacion('pag-emp', page, total, 'renderEmpPage');
}

/* ── Página: Facturas ───────────────────────────────────────── */
function renderFacPage(page) {
    detPage.fac = page;
    const arr = detData.facturas || [], total = arr.length;
    const start = (page-1)*DET_PAGE_SIZE, end = Math.min(start+DET_PAGE_SIZE, total);
    const tipoLabel = {1:'Facturador', 2:'Rol Real', 3:'Vendedor'};
    let html = '';
    arr.slice(start, end).forEach((f,i) => {
        html += `<tr>
            <td style="color:#94a3b8;font-size:11px;">${start+i+1}</td>
            <td style="font-weight:700;">#${f.factura_id}</td>
            <td style="font-family:monospace;font-size:12px;">${f.correlativo ?? '—'}</td>
            <td style="font-size:12px;">${f.cliente ?? '—'}</td>
            <td style="font-size:12px;">${f.empleado ?? '—'}</td>
            <td><span style="font-size:11px;">${f.rol}</span> · <span style="background:#f1f5f9;color:#475569;border-radius:5px;padding:1px 6px;font-size:10px;">${tipoLabel[f.tipo_comision] ?? '?'}</span></td>
            <td style="font-size:12px;">${fmtFecha(f.fecha_cierre_factura)}</td>
            <td class="text-right" style="font-weight:700;color:#1e3a8a;">L ${numFmt(f.monto_rol)}</td>
        </tr>`;
    });
    document.getElementById('det-fac-body').innerHTML = html || '<tr><td colspan="8" class="text-center text-muted" style="padding:24px;">Sin facturas comisionadas en este período</td></tr>';
    document.getElementById('det-toolbar-fac-info').textContent = total > 0 ? `Mostrando ${start+1}–${end} de ${total} facturas` : 'Sin registros';
    renderPaginacion('pag-fac', page, total, 'renderFacPage');
}

/* ── Página: Logs ───────────────────────────────────────────── */
function renderLogPage(page) {
    detPage.log = page;
    const arr = detData.logs || [], total = arr.length;
    const start = (page-1)*DET_PAGE_SIZE, end = Math.min(start+DET_PAGE_SIZE, total);
    const estadoLbl = {0:'Abierto', 1:'Conciliado'};
    let html = '';
    arr.slice(start, end).forEach(l => {
        const isConc = l.accion === 'conciliacion';
        html += `<tr class="${isConc ? 'log-row-conciliacion' : 'log-row-reapertura'}">
            <td><span class="conc-estado ${isConc ? 'log-badge-conciliacion' : 'log-badge-reapertura'}" style="border-radius:12px;padding:3px 10px;font-size:10px;font-weight:800;"><i class="fa ${isConc ? 'fa-lock' : 'fa-unlock'} mr-1"></i>${isConc ? 'CONCILIACIÓN' : 'REAPERTURA'}</span></td>
            <td><span class="conc-estado ${l.estado_anterior===1?'estado-conciliado':'estado-abierto'}" style="font-size:10px;">${estadoLbl[l.estado_anterior]}</span></td>
            <td><span class="conc-estado ${l.estado_nuevo===1?'estado-conciliado':'estado-abierto'}" style="font-size:10px;">${estadoLbl[l.estado_nuevo]}</span></td>
            <td class="text-right" style="font-weight:700;color:#1e3a8a;">L ${numFmt(l.snapshot_total_comision)}</td>
            <td class="text-center">${l.snapshot_cantidad_empleados}</td>
            <td class="text-center">${l.snapshot_cantidad_facturas}</td>
            <td style="font-size:11.5px;color:#64748b;max-width:200px;">${l.observacion ?? '<span style="color:#cbd5e1;">—</span>'}</td>
            <td style="font-size:12px;font-weight:600;">${l.usuario_nombre}</td>
            <td style="font-size:11.5px;color:#64748b;">${fmtFecha(l.created_at)}</td>
        </tr>`;
    });
    document.getElementById('det-log-body').innerHTML = html || '<tr><td colspan="9" class="text-center text-muted" style="padding:24px;">Sin historial de acciones</td></tr>';
    document.getElementById('det-toolbar-log-info').textContent = total > 0 ? `Mostrando ${start+1}–${end} de ${total} registros` : 'Sin registros';
    renderPaginacion('pag-log', page, total, 'renderLogPage');
}

/* ── Paginación ─────────────────────────────────────────────── */
function renderPaginacion(containerId, page, total, cb) {
    const el = document.getElementById(containerId);
    const totalPages = Math.ceil(total / DET_PAGE_SIZE);
    if (totalPages <= 1) { el.innerHTML = ''; return; }
    const MAX = 5;
    let s = Math.max(1, page - Math.floor(MAX/2));
    let e = Math.min(totalPages, s + MAX - 1);
    if (e - s < MAX - 1) s = Math.max(1, e - MAX + 1);
    let h = `<button class="det-pag-btn" onclick="${cb}(${page-1})" ${page===1?'disabled':''}><i class="fa fa-chevron-left"></i></button>`;
    if (s > 1) { h += `<button class="det-pag-btn" onclick="${cb}(1)">1</button>`; if (s > 2) h += `<span class="det-pag-info">…</span>`; }
    for (let i = s; i <= e; i++) h += `<button class="det-pag-btn${i===page?' active':''}" onclick="${cb}(${i})">${i}</button>`;
    if (e < totalPages) { if (e < totalPages-1) h += `<span class="det-pag-info">…</span>`; h += `<button class="det-pag-btn" onclick="${cb}(${totalPages})">${totalPages}</button>`; }
    h += `<button class="det-pag-btn" onclick="${cb}(${page+1})" ${page===totalPages?'disabled':''}><i class="fa fa-chevron-right"></i></button>`;
    h += `<span class="det-pag-info">Pág. ${page} / ${totalPages}</span>`;
    el.innerHTML = h;
}

/* ── Exportar Excel ─────────────────────────────────────────── */
function exportarExcel(tab) {
    if (typeof XLSX === 'undefined') {
        Swal.fire({ icon:'warning', title:'Librería no disponible', text:'Recarga la página e intenta de nuevo.' });
        return;
    }
    const now = new Date();
    const periodoLabel  = detData.label || '—';
    const periodoFile   = (detData.periodo || '').replace(/-/g,'').substring(0,6);
    const fechaDescarga = now.toLocaleDateString('es-HN',{day:'2-digit',month:'2-digit',year:'numeric'})
                        + ' ' + now.toLocaleTimeString('es-HN',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
    const sufijo = `${periodoFile}_descargado_${now.getFullYear()}${String(now.getMonth()+1).padStart(2,'0')}${String(now.getDate()).padStart(2,'0')}`;

    const wb = XLSX.utils.book_new();

    if (tab === 'emp') {
        const data = [
            ['Conciliación de Comisiones — Detalle de Empleados'],
            ['Período:', periodoLabel],
            ['Fecha de descarga:', fechaDescarga],
            [],
            ['#','Empleado','Rol','Comisión Acumulada (L)','Facturas','Última Modificación'],
            ...detData.empleados.map((e,i) => [
                i+1, e.nombre, e.rol,
                parseFloat(e.comision_acumulada||0),
                parseInt(e.facturas||0),
                e.fecha_ult_modificacion ? fmtFechaPlain(e.fecha_ult_modificacion) : ''
            ]),
            [],
            ['','','TOTAL', detData.empleados.reduce((s,e) => s+parseFloat(e.comision_acumulada||0),0), '', '']
        ];
        const ws = XLSX.utils.aoa_to_sheet(data);
        ws['!merges'] = [{s:{r:0,c:0},e:{r:0,c:5}}];
        ws['!cols']   = [{wch:5},{wch:34},{wch:24},{wch:22},{wch:10},{wch:24}];
        XLSX.utils.book_append_sheet(wb, ws, 'Empleados');
        XLSX.writeFile(wb, `Comisiones_Empleados_${sufijo}.xlsx`);

    } else if (tab === 'fac') {
        const tipoLabel = {1:'Facturador',2:'Rol Real',3:'Vendedor'};
        const data = [
            ['Conciliación de Comisiones — Detalle de Facturas'],
            ['Período:', periodoLabel],
            ['Fecha de descarga:', fechaDescarga],
            [],
            ['#','Factura #','Correlativo','Cliente','Empleado','Rol / Tipo','Fecha Cierre','Monto Comisión (L)'],
            ...detData.facturas.map((f,i) => [
                i+1, f.factura_id, f.correlativo??'', f.cliente??'', f.empleado??'',
                `${f.rol} / ${tipoLabel[f.tipo_comision]??'?'}`,
                fmtFechaPlain(f.fecha_cierre_factura),
                parseFloat(f.monto_rol||0)
            ])
        ];
        const ws = XLSX.utils.aoa_to_sheet(data);
        ws['!merges'] = [{s:{r:0,c:0},e:{r:0,c:7}}];
        ws['!cols']   = [{wch:5},{wch:12},{wch:18},{wch:28},{wch:28},{wch:24},{wch:22},{wch:20}];
        XLSX.utils.book_append_sheet(wb, ws, 'Facturas');
        XLSX.writeFile(wb, `Comisiones_Facturas_${sufijo}.xlsx`);

    } else if (tab === 'log') {
        const estadoLbl = {0:'Abierto',1:'Conciliado'};
        const data = [
            ['Conciliación de Comisiones — Historial de Acciones'],
            ['Período:', periodoLabel],
            ['Fecha de descarga:', fechaDescarga],
            [],
            ['Acción','Estado Anterior','Estado Nuevo','Total Snapshot (L)','Empleados','Facturas','Observación','Usuario','Fecha'],
            ...detData.logs.map(l => [
                l.accion === 'conciliacion' ? 'CONCILIACIÓN' : 'REAPERTURA',
                estadoLbl[l.estado_anterior] ?? l.estado_anterior,
                estadoLbl[l.estado_nuevo]    ?? l.estado_nuevo,
                parseFloat(l.snapshot_total_comision||0),
                parseInt(l.snapshot_cantidad_empleados||0),
                parseInt(l.snapshot_cantidad_facturas||0),
                l.observacion ?? '',
                l.usuario_nombre,
                fmtFechaPlain(l.created_at)
            ])
        ];
        const ws = XLSX.utils.aoa_to_sheet(data);
        ws['!merges'] = [{s:{r:0,c:0},e:{r:0,c:8}}];
        ws['!cols']   = [{wch:16},{wch:16},{wch:16},{wch:20},{wch:12},{wch:12},{wch:32},{wch:24},{wch:22}];
        XLSX.utils.book_append_sheet(wb, ws, 'Historial');
        XLSX.writeFile(wb, `Comisiones_Historial_${sufijo}.xlsx`);
    }
}

/* ── Tab switcher modal detalle ──────────────────────────────── */
function detTab(id, btn) {
    ['det-empleados','det-facturas','det-logs'].forEach(t => document.getElementById(t).classList.add('d-none'));
    document.querySelectorAll('.det-tab').forEach(b => b.classList.remove('active'));
    document.getElementById(id).classList.remove('d-none');
    if (btn) btn.classList.add('active');
}

/* ── Helpers ─────────────────────────────────────────────────── */
function numFmt(n) {
    return parseFloat(n||0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
}
function fmtFecha(s) {
    if (!s) return '—';
    return new Date(s).toLocaleString('es-HN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
}
function fmtFechaPlain(s) {
    if (!s) return '';
    return new Date(s).toLocaleString('es-HN',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});
}

/* ── Bootstrap ───────────────────────────────────────────────── */
document.getElementById('btn-reload-periodos').addEventListener('click', cargarPeriodos);
document.addEventListener('DOMContentLoaded', function() {
    cargarPeriodos();
});

/* ── Tabs del panel ──────────────────────────────────────────── */
let dgLoaded = false;
function concTab(tab) {
    // Activar botones
    document.getElementById('tab-btn-periodos').classList.toggle('active', tab === 'periodos');
    document.getElementById('tab-btn-diasgracia').classList.toggle('active', tab === 'diasgracia');
    document.getElementById('tab-btn-auditoria').classList.toggle('active', tab === 'auditoria');
    // Mostrar panes
    document.getElementById('conc-tab-periodos').classList.toggle('active', tab === 'periodos');
    document.getElementById('conc-tab-diasgracia').classList.toggle('active', tab === 'diasgracia');
    // El tab de auditoría usa display:none/block (no tiene clase active en CSS heredada)
    document.getElementById('conc-tab-auditoria').style.display = (tab === 'auditoria') ? 'block' : 'none';
    // Cargar días de gracia la primera vez que se abre ese tab
    if (tab === 'diasgracia' && !dgLoaded) {
        dgLoaded = true;
        dgCargar();
    }
    // Cargar auditoría la primera vez
    if (tab === 'auditoria' && !audLoaded) {
        audLoaded = true;
        audCargar();
    }
}

/* ═══════════════════════════════════════════════════════════════
   DÍAS DE GRACIA — JS (modal)
═══════════════════════════════════════════════════════════════ */

let dgData   = [];
let dgRolSel = null;

function dgCargar() {
    axios.get('/comisiones/dias-gracia')
        .then(r => {
            dgData = r.data.roles || [];
            document.getElementById('dg-badge-total').textContent = dgData.length + ' roles';
            dgRenderResumen();
        })
        .catch(() => {
            document.getElementById('dg-summary-body').innerHTML =
                '<div class="alert alert-danger m-3"><i class="fa fa-times-circle mr-1"></i> Error al cargar configuración.</div>';
        });
}

function dgAbrirModal(rolId) {
    dgRolSel = dgData.find(r => r.rol_id === rolId) || null;
    if (!dgRolSel) return;

    document.getElementById('dgm-rol-badge').textContent = dgRolSel.rol_nombre;
    document.getElementById('dgm-cont-dias').value = dgRolSel.contado_dias      !== null ? dgRolSel.contado_dias      : 0;
    document.getElementById('dgm-cont-ret').value  = dgRolSel.contado_retencion !== null ? dgRolSel.contado_retencion : 0;
    document.getElementById('dgm-cont-desc').value = dgRolSel.contado_descripcion || '';
    document.getElementById('dgm-cred-dias').value = dgRolSel.credito_dias       !== null ? dgRolSel.credito_dias      : 0;
    document.getElementById('dgm-cred-ret').value  = dgRolSel.credito_retencion  !== null ? dgRolSel.credito_retencion : 0;
    document.getElementById('dgm-cred-desc').value = dgRolSel.credito_descripcion || '';

    $('#modalDiasGracia').modal('show');
}

function dgmStep(id, delta) {
    const el = document.getElementById(id);
    el.value = Math.max(0, Math.min(9999, (parseInt(el.value) || 0) + delta));
}

function dgmGuardar() {
    if (!dgRolSel) return;
    const btn      = document.getElementById('dgm-btn-guardar');
    const contDias = parseInt(document.getElementById('dgm-cont-dias').value);
    const contRet  = parseFloat(document.getElementById('dgm-cont-ret').value);
    const contDesc = document.getElementById('dgm-cont-desc').value.trim();
    const credDias = parseInt(document.getElementById('dgm-cred-dias').value);
    const credRet  = parseFloat(document.getElementById('dgm-cred-ret').value);
    const credDesc = document.getElementById('dgm-cred-desc').value.trim();

    if (isNaN(contDias) || contDias < 0 || isNaN(credDias) || credDias < 0) {
        Swal.fire({ icon: 'warning', title: 'Valores inválidos', text: 'Los días deben ser números ≥ 0.', timer: 2200, showConfirmButton: false });
        return;
    }
    if ([contRet, credRet].some(v => isNaN(v) || v < 0 || v > 100)) {
        Swal.fire({ icon: 'warning', title: 'Porcentaje inválido', text: 'La retención debe estar entre 0 y 100.', timer: 2200, showConfirmButton: false });
        return;
    }

    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="conc-spinner conc-spinner-sm"></span> Guardando...';

    Promise.all([
        axios.post('/comisiones/dias-gracia/guardar', { rol_id: dgRolSel.rol_id, tipo: 'contado', dias: contDias, porcentaje_retencion: contRet, descripcion: contDesc }),
        axios.post('/comisiones/dias-gracia/guardar', { rol_id: dgRolSel.rol_id, tipo: 'credito', dias: credDias, porcentaje_retencion: credRet, descripcion: credDesc  })
    ])
    .then(() => {
        $('#modalDiasGracia').modal('hide');
        Swal.fire({ icon: 'success', title: '¡Guardado!', text: `Configuración actualizada para "${dgRolSel.rol_nombre}".`, timer: 2000, showConfirmButton: false });
        dgRolSel = null;
        dgCargar();
    })
    .catch(err => {
        const d = err.response?.data || {};
        Swal.fire({ icon: 'error', title: 'Error', text: d.message || 'Error al guardar.' });
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = orig;
    });
}

const DG_PAGE_SIZE = 10;
let dgPage = 0;

function dgRenderResumen() {
    const body = document.getElementById('dg-summary-body');
    if (!dgData.length) {
        body.innerHTML = `<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px 20px;gap:10px;color:#94a3b8;">
            <i class="fa fa-inbox" style="font-size:32px;opacity:.4;"></i>
            <p style="margin:0;font-size:13px;font-weight:500;">Sin datos disponibles</p>
        </div>`;
        return;
    }

    dgPage = Math.min(dgPage, Math.max(0, Math.ceil(dgData.length / DG_PAGE_SIZE) - 1));
    const start = dgPage * DG_PAGE_SIZE;
    const slice = dgData.slice(start, start + DG_PAGE_SIZE);
    const total = dgData.length;
    const pages = Math.ceil(total / DG_PAGE_SIZE);

    let rows = '';
    slice.forEach(r => {
        const hasContado = r.contado_dias !== null;
        const hasCred    = r.credito_dias !== null;

        const contCell = hasContado
            ? `<div class="dg-cell">
                <span class="dg-dias-badge dg-dias-contado"><i class="fa fa-clock-o"></i> ${r.contado_dias}d</span>
                ${r.contado_retencion > 0 ? `<span class="dg-ret-badge"><i class="fa fa-percent" style="font-size:9px;"></i> ${parseFloat(r.contado_retencion)}%</span>` : ''}
               </div>`
            : `<span class="dg-none-badge">Sin configurar</span>`;

        const credCell = hasCred
            ? `<div class="dg-cell">
                <span class="dg-dias-badge dg-dias-credito"><i class="fa fa-clock-o"></i> ${r.credito_dias}d</span>
                ${r.credito_retencion > 0 ? `<span class="dg-ret-badge"><i class="fa fa-percent" style="font-size:9px;"></i> ${parseFloat(r.credito_retencion)}%</span>` : ''}
               </div>`
            : `<span class="dg-none-badge">Sin configurar</span>`;

        rows += `<tr>
            <td class="dg-tbl-role">${r.rol_nombre}</td>
            <td>${contCell}</td>
            <td>${credCell}</td>
            <td style="text-align:right;">
                <button class="dg-edit-btn" onclick="dgAbrirModal(${r.rol_id})">
                    <i class="fa fa-pencil-square-o"></i> Editar
                </button>
            </td>
        </tr>`;
    });

    let pageBtns = `<button class="dg-pager-btn" onclick="dgPageGo(${dgPage-1})" ${dgPage===0?'disabled':''}><i class="fa fa-chevron-left"></i></button>`;
    const maxBtns = 5, pFrom = Math.max(0, Math.min(dgPage-2, pages-maxBtns));
    for (let p = pFrom; p < Math.min(pages, pFrom+maxBtns); p++) {
        pageBtns += `<button class="dg-pager-btn${p===dgPage?' active':''}" onclick="dgPageGo(${p})">${p+1}</button>`;
    }
    pageBtns += `<button class="dg-pager-btn" onclick="dgPageGo(${dgPage+1})" ${dgPage>=pages-1?'disabled':''}><i class="fa fa-chevron-right"></i></button>`;

    body.innerHTML = `
    <table class="dg-tbl">
        <thead><tr>
            <th>Rol</th>
            <th style="width:200px;">
                <span style="display:inline-flex;align-items:center;gap:6px;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;background:#f0fdf4;border-radius:4px;border:1px solid #86efac;">
                        <i class="fa fa-check" style="color:#16a34a;font-size:9px;"></i>
                    </span>
                    Contado
                    <span style="font-weight:500;color:#cbd5e1;font-size:9px;letter-spacing:.4px;">DÍAS / RET.</span>
                </span>
            </th>
            <th style="width:200px;">
                <span style="display:inline-flex;align-items:center;gap:6px;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;background:#fff7ed;border-radius:4px;border:1px solid #fdba74;">
                        <i class="fa fa-credit-card" style="color:#ea580c;font-size:9px;"></i>
                    </span>
                    Crédito
                    <span style="font-weight:500;color:#cbd5e1;font-size:9px;letter-spacing:.4px;">DÍAS / RET.</span>
                </span>
            </th>
            <th style="width:100px;"></th>
        </tr></thead>
        <tbody>${rows}</tbody>
    </table>
    <div class="dg-pager">
        <span style="display:flex;align-items:center;gap:6px;">
            <i class="fa fa-users" style="opacity:.5;"></i>
            Mostrando <strong style="color:#475569;">${start+1}–${Math.min(start+DG_PAGE_SIZE,total)}</strong> de <strong style="color:#475569;">${total}</strong> roles
        </span>
        <div class="dg-pager-btns">${pageBtns}</div>
    </div>`;
}

function dgPageGo(p) {
    dgPage = p;
    dgRenderResumen();
}

/* ═══════════════════════════════════════════════════════════════
   AUDITORÍA — JS
═══════════════════════════════════════════════════════════════ */
let audData      = [];   // cache de todos los logs
let audTipoActivo = 'todos';
let audLoaded    = false;
let audSnapshotActual = null; // el log abierto en el modal

function audCargar() {
    const anio = document.getElementById('aud-filtro-anio').value;
    document.getElementById('aud-body').innerHTML = '<div class="conc-loader"><div class="conc-spinner"></div> Cargando historial...</div>';
    axios.get('/comisiones/conciliacion/auditoria-logs', { params: { anio } })
        .then(function(r) {
            const data = r.data;
            // Poblar select de años
            const sel = document.getElementById('aud-filtro-anio');
            const curVal = sel.value;
            sel.innerHTML = '<option value="0">Todos los años</option>';
            (data.anios || []).forEach(function(a) {
                const opt = document.createElement('option');
                opt.value = a; opt.textContent = a;
                if (String(a) === String(curVal)) opt.selected = true;
                sel.appendChild(opt);
            });
            audData = data.logs || [];
            document.getElementById('aud-badge-total').textContent = audData.length + ' registros';
            audRenderLista();
        })
        .catch(function() {
            document.getElementById('aud-body').innerHTML = '<div class="aud-empty"><i class="fa fa-exclamation-triangle" style="font-size:28px;color:#fca5a5;"></i><br>Error al cargar el historial.</div>';
        });
}

function audFiltrarTipo(btn) {
    document.querySelectorAll('.aud-tipo-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    audTipoActivo = btn.dataset.tipo;
    audRenderLista();
}

function audRenderLista() {
    const filtered = audTipoActivo === 'todos'
        ? audData
        : audData.filter(function(l) { return l.accion === audTipoActivo; });

    if (filtered.length === 0) {
        document.getElementById('aud-body').innerHTML = '<div class="aud-empty"><i class="fa fa-inbox" style="font-size:32px;display:block;margin-bottom:10px;"></i>No hay registros de auditoría para este filtro.</div>';
        return;
    }

    let html = '';
    filtered.forEach(function(log) {
        const badge = log.accion === 'conciliacion'
            ? '<span class="aud-badge-conciliacion"><i class="fa fa-lock"></i> Conciliación</span>'
            : '<span class="aud-badge-reapertura"><i class="fa fa-unlock"></i> Reapertura</span>';

        const obsHtml = log.observacion
            ? '<div class="aud-card-obs"><i class="fa fa-comment mr-1"></i>' + escHtml(log.observacion) + '</div>'
            : '';

        html += '<div class="aud-card">'
            + '<div class="aud-card-left">'
            +   '<div style="display:flex;align-items:center;gap:10px;">'
            +     '<span class="aud-card-periodo"><i class="fa fa-calendar mr-1"></i>' + escHtml(log.periodo_label) + '</span>'
            +     badge
            +   '</div>'
            +   '<div class="aud-card-usuario"><i class="fa fa-user mr-1"></i>' + escHtml(log.usuario_nombre) + '</div>'
            +   '<div class="aud-card-fecha"><i class="fa fa-clock-o mr-1"></i>' + escHtml(log.fecha) + '</div>'
            +   obsHtml
            + '</div>'
            + '<div class="aud-card-right">'
            +   '<div class="aud-card-kpis">'
            +     '<div class="aud-kpi"><div class="aud-kpi-val">' + log.snapshot_cantidad_empleados + '</div><div class="aud-kpi-lbl">Empleados</div></div>'
            +     '<div class="aud-kpi"><div class="aud-kpi-val">' + log.snapshot_cantidad_facturas + '</div><div class="aud-kpi-lbl">Facturas</div></div>'
            +     '<div class="aud-kpi"><div class="aud-kpi-val" style="font-size:13px;color:#16a34a;">' + escHtml(log.snapshot_total_fmt) + '</div><div class="aud-kpi-lbl">Total Comisión</div></div>'
            +   '</div>'
            +   '<button class="aud-btn-detalle" onclick="audAbrirDetalle(' + log.id + ')"><i class="fa fa-eye mr-1"></i> Ver Detalle</button>'
            + '</div>'
            + '</div>';
    });

    document.getElementById('aud-body').innerHTML = html;
}

function audAbrirDetalle(logId) {
    const log = audData.find(function(l) { return l.id === logId; });
    if (!log) return;
    audSnapshotActual = log;

    // Header color según acción
    const hdr = document.getElementById('aud-modal-header');
    hdr.style.background = log.accion === 'conciliacion' ? '#16a34a' : '#dc2626';

    // Título
    const badge = log.accion === 'conciliacion' ? 'Conciliación' : 'Reapertura';
    document.getElementById('aud-modal-title').innerHTML =
        '<i class="fa fa-history mr-2"></i> ' + badge + ' — ' + escHtml(log.periodo_label);

    // KPIs
    document.getElementById('aud-modal-kpis').innerHTML =
        '<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px;text-align:center;">'
        + '<div style="font-size:22px;font-weight:900;color:#1e3a8a;">' + log.snapshot_cantidad_empleados + '</div>'
        + '<div style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Empleados</div>'
        + '</div>'
        + '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px;text-align:center;">'
        + '<div style="font-size:22px;font-weight:900;color:#16a34a;">' + log.snapshot_cantidad_facturas + '</div>'
        + '<div style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Facturas</div>'
        + '</div>'
        + '<div style="background:#fef9c3;border:1px solid #fde68a;border-radius:8px;padding:14px;text-align:center;">'
        + '<div style="font-size:20px;font-weight:900;color:#92400e;">' + escHtml(log.snapshot_total_fmt) + '</div>'
        + '<div style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Total Comisión</div>'
        + '</div>';

    // Meta
    document.getElementById('aud-modal-meta').innerHTML =
        '<span style="margin-right:20px;"><i class="fa fa-user mr-1"></i><strong>Usuario:</strong> ' + escHtml(log.usuario_nombre) + '</span>'
        + '<span style="margin-right:20px;"><i class="fa fa-clock-o mr-1"></i><strong>Fecha:</strong> ' + escHtml(log.fecha) + '</span>'
        + '<span><i class="fa fa-exchange mr-1"></i><strong>Cambio:</strong> '
        + (log.estado_anterior === 1 ? 'Conciliado' : 'Abierto') + ' → '
        + (log.estado_nuevo === 1 ? 'Conciliado' : 'Abierto') + '</span>';

    // Observación
    const obsWrap = document.getElementById('aud-modal-obs-wrap');
    if (log.observacion) {
        obsWrap.style.display = 'block';
        document.getElementById('aud-modal-obs').textContent = log.observacion;
    } else {
        obsWrap.style.display = 'none';
    }

    // Tabla empleados
    const emps = log.snapshot_detalle_empleados || [];
    document.getElementById('aud-inner-cnt-emp').textContent = emps.length;
    let rowsEmp = '';
    emps.forEach(function(e, i) {
        const cls = i % 2 === 0 ? '#f8fafc' : '#fff';
        const comFmt = 'L ' + parseFloat(e.comision_acumulada || 0).toLocaleString('es-HN', {minimumFractionDigits:2, maximumFractionDigits:2});
        rowsEmp += '<tr style="background:' + cls + ';border-bottom:1px solid #f1f5f9;">'
            + '<td style="padding:7px 12px;font-weight:600;color:#1e3a8a;">' + escHtml(e.nombre || '') + '</td>'
            + '<td style="padding:7px 12px;color:#475569;">' + escHtml(e.rol || '') + '</td>'
            + '<td style="padding:7px 12px;text-align:right;font-weight:700;color:#16a34a;">' + comFmt + '</td>'
            + '<td style="padding:7px 12px;text-align:center;">' + (e.cantidad_facturas || 0) + '</td>'
            + '<td style="padding:7px 12px;color:#64748b;">' + escHtml(e.mes_comision || '') + '</td>'
            + '</tr>';
    });
    document.getElementById('aud-tbl-emp-body').innerHTML = rowsEmp || '<tr><td colspan="5" style="text-align:center;padding:20px;color:#94a3b8;">Sin datos de empleados</td></tr>';

    // Tabla facturas
    const facs = log.snapshot_detalle_facturas || [];
    document.getElementById('aud-inner-cnt-fac').textContent = facs.length;
    const tipoLabels = { 1: 'Facturador', 2: 'Rol Real', 3: 'Vendedor' };
    const tipoColors = { 1: '#f59e0b', 2: '#3b82f6', 3: '#10b981' };
    let rowsFac = '';
    facs.forEach(function(f, i) {
        const cls = i % 2 === 0 ? '#f8fafc' : '#fff';
        const montoFmt = 'L ' + parseFloat(f.monto_rol || 0).toLocaleString('es-HN', {minimumFractionDigits:2, maximumFractionDigits:2});
        const tipoCfg  = tipoLabels[f.tipo_comision] || 'N/D';
        const tipoColor = tipoColors[f.tipo_comision] || '#6b7280';
        const badge = '<span style="background:' + tipoColor + ';color:#fff;padding:1px 8px;border-radius:8px;font-size:10px;font-weight:700;">' + tipoCfg + '</span>';
        rowsFac += '<tr style="background:' + cls + ';border-bottom:1px solid #f1f5f9;">'
            + '<td style="padding:7px 12px;text-align:center;font-weight:700;color:#1e3a8a;">#' + (f.factura_id || '') + '</td>'
            + '<td style="padding:7px 12px;color:#475569;">' + escHtml(f.fecha_cierre || '') + '</td>'
            + '<td style="padding:7px 12px;text-align:right;font-weight:700;color:#16a34a;">' + montoFmt + '</td>'
            + '<td style="padding:7px 12px;color:#475569;">' + escHtml(f.rol || '') + '</td>'
            + '<td style="padding:7px 12px;text-align:center;">' + badge + '</td>'
            + '</tr>';
    });
    document.getElementById('aud-tbl-fac-body').innerHTML = rowsFac || '<tr><td colspan="5" style="text-align:center;padding:20px;color:#94a3b8;">Sin datos de facturas</td></tr>';

    // Resetear inner tab a empleados
    audInnerTab('emp');

    $('#modalAuditoriaDetalle').modal('show');
}

function audInnerTab(tab) {
    const isEmp = tab === 'emp';
    document.getElementById('aud-inner-emp').style.display = isEmp ? 'block' : 'none';
    document.getElementById('aud-inner-fac').style.display = isEmp ? 'none'  : 'block';
    document.getElementById('aud-inner-tab-emp').style.color         = isEmp ? '#1e3a8a' : '#64748b';
    document.getElementById('aud-inner-tab-emp').style.borderBottomColor = isEmp ? '#1e3a8a' : 'transparent';
    document.getElementById('aud-inner-tab-fac').style.color         = isEmp ? '#64748b' : '#1e3a8a';
    document.getElementById('aud-inner-tab-fac').style.borderBottomColor = isEmp ? 'transparent' : '#1e3a8a';
    const cntEmp = document.getElementById('aud-inner-cnt-emp');
    const cntFac = document.getElementById('aud-inner-cnt-fac');
    cntEmp.style.background = isEmp ? '#1e3a8a' : '#64748b';
    cntFac.style.background = isEmp ? '#64748b' : '#1e3a8a';
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush

