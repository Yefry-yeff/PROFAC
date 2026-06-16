@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════
   Conciliación de Comisiones — Estilos Corporativos
═══════════════════════════════════════════════════════════════ */
@keyframes conc-fadeDown { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
@keyframes conc-fadeUp   { from{opacity:0;transform:translateY(6px)}  to{opacity:1;transform:translateY(0)} }
@keyframes conc-fadeIn   { from{opacity:0} to{opacity:1} }
@keyframes conc-spin     { to{transform:rotate(360deg)} }
@keyframes conc-pulse    { 0%,100%{opacity:1} 50%{opacity:.35} }

/* ── TOP BAR ── */
.conc-topbar{
    background:#1e293b;
    border-radius:8px; padding:13px 20px;
    display:flex; align-items:center; gap:16px;
    box-shadow:0 2px 8px rgba(0,0,0,.15);
    animation:conc-fadeDown .3s ease both;
    flex-wrap:wrap;
}
.conc-topbar::before{ display:none; }
.conc-topbar-icon{
    width:34px; height:34px; background:rgba(255,255,255,.09);
    border-radius:6px; display:flex; align-items:center;
    justify-content:center; font-size:15px; color:rgba(255,255,255,.75); flex-shrink:0;
}
.conc-topbar-title{
    color:#f1f5f9; font-weight:700; font-size:14px; line-height:1.25; flex-shrink:0;
}
.conc-topbar-title small{
    display:block; color:rgba(255,255,255,.42);
    font-size:10px; font-weight:500; margin-top:1px; letter-spacing:.1px;
}

/* KPI en línea — separadores verticales, sin fondo */
.conc-kpi-inline{
    display:flex; gap:0; flex-wrap:wrap; flex:1; justify-content:flex-end;
    align-items:center;
}
.conc-kpi-chip{
    background:transparent; border:none;
    border-left:1px solid rgba(255,255,255,.1);
    padding:4px 16px;
    display:flex; align-items:center; gap:8px; flex-shrink:0;
}
.conc-kpi-chip:first-child{ border-left:none; }
.conc-kpi-chip .ck-val{ font-size:14px; font-weight:700; color:#f1f5f9; line-height:1; }
.conc-kpi-chip .ck-lbl{ font-size:9.5px; font-weight:500; color:rgba(255,255,255,.38); text-transform:uppercase; letter-spacing:.5px; }
.conc-kpi-chip .ck-ico{ font-size:11px; color:rgba(255,255,255,.35); }
.conc-kpi-chip.ck-green .ck-val{ color:#4ade80; }
.conc-kpi-chip.ck-amber .ck-val{ color:#fbbf24; }
.conc-kpi-chip.ck-purple .ck-val{ color:#c084fc; }
.conc-btn-refresh{
    background:rgba(255,255,255,.07) !important; color:rgba(255,255,255,.65) !important;
    border:1px solid rgba(255,255,255,.15) !important; border-radius:6px;
    padding:6px 13px; font-size:11.5px; font-weight:600;
    display:inline-flex; align-items:center; gap:6px; cursor:pointer;
    transition:background .15s; flex-shrink:0; white-space:nowrap;
}
.conc-btn-refresh:hover{ background:rgba(255,255,255,.12) !important; }

/* ── PANEL ── */
.conc-panel{
    background:#fff; border-radius:8px;
    border:1px solid #e2e8f0;
    box-shadow:0 1px 3px rgba(0,0,0,.06);
    animation:conc-fadeUp .3s ease both; overflow:hidden;
}
.conc-panel-head{
    background:#1e293b;
    padding:12px 18px; display:flex; align-items:center; gap:9px;
    color:#f1f5f9; font-size:12.5px; font-weight:700;
}
.conc-panel-head-ico{
    width:26px; height:26px; background:rgba(255,255,255,.1);
    border-radius:5px; display:flex; align-items:center;
    justify-content:center; font-size:12px; flex-shrink:0;
}
.conc-panel-body{ padding:20px 22px; }

/* ── FILTROS AÑO/MES ── */
.conc-filter-bar{
    display:flex; align-items:center; gap:7px; flex-wrap:wrap;
    padding:10px 16px; border-bottom:1px solid #eaecf0; background:#fff;
}
.conc-year-select{
    background:#fff; border:1px solid #d1d5db; border-radius:5px;
    padding:4px 28px 4px 10px; font-size:12px; font-weight:600; color:#1e293b;
    cursor:pointer; outline:none; transition:border-color .12s;
    height:30px;
    appearance:none; -webkit-appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%2394a3b8' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 8px center;
}
.conc-year-select:focus{ border-color:#475569; box-shadow:none; }
.conc-mes-btn{
    background:transparent; border:1px solid #e2e8f0; border-radius:4px;
    padding:3px 8px; font-size:11px; font-weight:600; color:#6b7280;
    cursor:pointer; transition:all .1s; white-space:nowrap;
}
.conc-mes-btn:hover{ background:#f3f4f6; border-color:#9ca3af; color:#374151; }
.conc-mes-btn.active{ background:#1e293b; border-color:#1e293b; color:#fff; }
.conc-mes-btn.all{ border-style:dashed; border-color:#d1d5db; }

/* ── PERÍODOS TABLE ── */
/* ── TABLA DE PERÍODOS ── */
.conc-tbl { width:100%; border-collapse:collapse; }
.conc-tbl thead tr { background:#fff; }
.conc-tbl thead th {
    padding:9px 16px; font-size:11px; font-weight:600;
    text-transform:uppercase; letter-spacing:.5px; color:#9ca3af;
    border-top:1px solid #e5e7eb; border-bottom:1px solid #e5e7eb; white-space:nowrap;
}
.conc-tbl tbody tr { border-bottom:1px solid #f3f4f6; }
.conc-tbl tbody tr:hover td { background:#f8fafc; }
.conc-tbl tbody tr.row-sin-abrir td { color:#b0b8c4; }
.conc-tbl tbody tr.row-mes-actual td { background:#fafaf7; font-weight:600; }
.conc-tbl tbody td { padding:12px 16px; font-size:13px; color:#374151; vertical-align:middle; }
.conc-tbl tbody td.td-num { text-align:right; font-variant-numeric:tabular-nums; }
.conc-tbl tbody td.td-actions { text-align:right; white-space:nowrap; }

/* ── ESTADO — texto simple con punto ── */
.conc-estado {
    display:inline-flex; align-items:center; gap:5px;
    font-size:12px; font-weight:500; color:#6b7280; white-space:nowrap;
}
.estado-abierto    { color:#92400e; }
.estado-conciliado { color:#166534; }
.estado-sin-abrir  { color:#9ca3af; }
.estado-dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; display:inline-block; }
.dot-abierto   { background:#d97706; }
.dot-conciliado{ background:#16a34a; }
.dot-sin-abrir { background:#d1d5db; }

/* ── ACCIONES — botones de texto ── */
.conc-btn {
    display:inline-flex; align-items:center; gap:4px;
    padding:4px 10px; border-radius:4px; border:1px solid #e2e8f0;
    font-size:12px; font-weight:500; cursor:pointer; background:#fff;
    color:#374151; transition:border-color .1s,background .1s; white-space:nowrap;
}
.conc-btn:hover { border-color:#9ca3af; background:#f9fafb; }
.conc-btn:active { background:#f3f4f6; }
.btn-conciliar  { background:#1e293b; color:#fff; border-color:#1e293b; }
.btn-conciliar:hover { background:#334155; border-color:#334155; }
.btn-detalle    { color:#475569; }
.btn-reabrir    { color:#b91c1c; border-color:#fca5a5; }
.btn-disabled   { color:#d1d5db !important; border-color:#f3f4f6 !important; cursor:not-allowed !important; background:#f9fafb !important; }

/* ── LOADER ── */
.conc-loader{
    display:flex; align-items:center; justify-content:center;
    gap:10px; padding:48px; color:#6b7280; font-size:13px; font-weight:500;
}
.conc-spinner{
    width:20px; height:20px; border:2px solid #e5e7eb;
    border-top-color:#475569; border-radius:50%;
    animation:conc-spin .75s linear infinite; flex-shrink:0;
}
.conc-spinner-sm{
    width:13px; height:13px; border-width:2px;
    display:inline-block; vertical-align:middle;
}

/* ── EMPTY ── */
.conc-empty{ text-align:center; padding:50px 20px; color:#9ca3af; }
.conc-empty i{ font-size:32px; margin-bottom:12px; display:block; opacity:.25; }
.conc-empty p{ font-size:13px; font-weight:600; color:#6b7280; margin:0 0 5px; }

/* ── MODAL ── */
.conc-modal .modal-content{
    border:1px solid #e2e8f0; border-radius:8px;
    box-shadow:0 16px 48px rgba(0,0,0,.14); overflow:hidden;
}
.conc-modal .modal-header{
    padding:14px 20px; border-bottom:none;
    border-radius:8px 8px 0 0;
}
.conc-modal .modal-header.blue { background:#1e293b; }
.conc-modal .modal-header.red  { background:#7f1d1d; }
.conc-modal .modal-title{ color:#f1f5f9; font-size:13.5px; font-weight:700; display:flex; align-items:center; gap:8px; }
.conc-modal .close{ color:rgba(255,255,255,.6)!important; opacity:1!important; font-size:20px; }
.conc-modal .modal-body{ padding:20px 22px; background:#fff; }
.conc-modal .modal-footer{ background:#f9fafb; border-top:1px solid #e5e7eb; padding:11px 20px; border-radius:0 0 8px 8px; }

/* ── DETALLE TABS ── */
.det-tabs{ display:flex; gap:6px; border-bottom:1px solid #dbe2ea; margin-bottom:0; padding:10px 14px 0; background:#f8fafc; }
.det-tab{
    background:transparent; border:none; padding:10px 14px;
    font-size:12px; font-weight:700; color:#64748b; cursor:pointer;
    border-bottom:2px solid transparent; margin-bottom:-1px;
    border-top-left-radius:8px; border-top-right-radius:8px;
    display:flex; align-items:center; gap:6px;
    transition:color .12s, background .12s, border-color .12s;
}
.det-tab:hover { color:#1e293b; background:#eef2f7; }
.det-tab.active{ color:#1e293b; border-bottom-color:#1e293b; background:#fff; }

.det-badge{
    display:inline-flex; align-items:center; justify-content:center;
    min-width:22px; height:20px; padding:0 7px;
    border-radius:20px; background:#e2e8f0; color:#334155;
    font-size:10px; font-weight:800; line-height:1;
}

.det-tab.active .det-badge{ background:#1e293b; color:#fff; }

.det-toolbar{
    display:flex; align-items:center; gap:10px; flex-wrap:wrap;
    padding:10px 14px; background:#fff; border-bottom:1px solid #e5e7eb;
}

.det-toolbar-info{
    display:inline-flex; align-items:center; gap:6px;
    font-size:13px; font-weight:600; color:#64748b;
    background:#f8fafc; border:1px solid #e2e8f0; border-radius:7px;
    padding:5px 9px;
}

.det-search-wrap{
    position:relative; width:320px; max-width:100%;
}

.det-search-ico{
    position:absolute; left:10px; top:50%; transform:translateY(-50%);
    color:#94a3b8; font-size:11px;
}

.det-search-input{
    width:100%; height:34px; border:1px solid #cbd5e1; border-radius:8px;
    padding:0 11px 0 30px; font-size:12.5px; color:#334155; background:#fff; outline:none;
    transition:border-color .12s, box-shadow .12s;
}

.det-search-input:focus{
    border-color:#475569;
    box-shadow:0 0 0 3px rgba(71,85,105,.10);
}

.det-export-btn{
    margin-left:auto;
    height:34px; border:1px solid #1e293b; background:#1e293b; color:#fff;
    border-radius:8px; padding:0 12px; font-size:12px; font-weight:700;
    display:inline-flex; align-items:center; gap:7px; cursor:pointer;
    transition:background .12s, border-color .12s, transform .06s;
}

.det-export-btn:hover{ background:#334155; border-color:#334155; }
.det-export-btn:active{ transform:translateY(1px); }

#modalDetalle .modal-body .conc-tbl thead th{
    background:#f8fafc; color:#7b8794; border-bottom:1px solid #e5e7eb;
    font-size:10.5px; letter-spacing:.5px;
}

#modalDetalle .modal-body .conc-tbl tbody td{
    border-bottom:1px solid #eef2f7;
}

#modalDetalle .modal-body .conc-tbl tbody tr:nth-child(even) td{
    background:#fcfdff;
}

#modalDetalle .modal-body .conc-tbl tbody tr:hover td{
    background:#f1f5f9;
}

.det-tfoot-row td{
    background:#f8fafc;
}

.det-pag{
    display:flex; align-items:center; justify-content:flex-end; gap:6px;
    padding:10px 14px; border-top:1px solid #e5e7eb; background:#fff;
}

.det-pag-btn{
    min-width:30px; height:30px; border:1px solid #d1d5db; background:#fff;
    border-radius:7px; color:#475569; font-size:11px; font-weight:700;
    display:inline-flex; align-items:center; justify-content:center;
    cursor:pointer; transition:all .12s;
}

.det-pag-btn:hover:not(:disabled){ background:#f3f4f6; border-color:#94a3b8; color:#1e293b; }
.det-pag-btn:disabled{ opacity:.4; cursor:not-allowed; }
.det-pag-btn.active{ background:#1e293b; border-color:#1e293b; color:#fff; }

.det-pag-info{
    font-size:11.5px; font-weight:600; color:#64748b;
    padding:0 2px;
}

/* ── LOG TABLE ── */
.log-row-conciliacion{ background:#f9fafb; }
.log-row-reapertura  { background:#f9fafb; }
.log-badge-conciliacion{ background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; border-radius:3px; font-size:10px; font-weight:700; padding:1px 7px; }
.log-badge-reapertura  { background:#fef2f2; color:#991b1b; border:1px solid #fca5a5; border-radius:3px; font-size:10px; font-weight:700; padding:1px 7px; }

/* ── RESUMEN CONCILIACIÓN ── */
.conc-resumen-grid{
    display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:14px;
}
.conc-resumen-card{
    background:#fff; border:1px solid #e5e7eb; border-radius:7px;
    padding:14px 16px; text-align:center;
}
.conc-resumen-card .cr-val{
    font-size:20px; font-weight:800; color:#1e293b; line-height:1.15; margin-bottom:3px;
}
.conc-resumen-card .cr-lbl{
    font-size:10px; font-weight:600; color:#9ca3af;
    text-transform:uppercase; letter-spacing:.5px;
}
.conc-resumen-card.cr-total{ border-color:#c7d2fe; background:#f5f3ff; }
.conc-resumen-card.cr-total .cr-val{ color:#4338ca; font-size:22px; }
.conc-advertencia{
    background:#fffbeb; border:1px solid #fde68a; border-radius:7px;
    padding:11px 15px; display:flex; align-items:flex-start; gap:9px;
    font-size:12px; color:#78350f; margin-bottom:14px; font-weight:500;
}
.conc-advertencia i{ font-size:13px; flex-shrink:0; margin-top:1px; }

/* ── TABS DEL PANEL ── */
.conc-panel-tab-nav{
    background:#1e293b;
    display:flex; align-items:stretch; gap:0;
    border-bottom:none; overflow:hidden;
}
.conc-tab-btn{
    background:transparent; border:none; border-bottom:2px solid transparent;
    color:rgba(255,255,255,.45); font-size:12px; font-weight:600;
    padding:12px 18px; cursor:pointer; display:flex; align-items:center; gap:7px;
    transition:background .12s, color .12s, border-color .12s;
    white-space:nowrap;
}
.conc-tab-btn:hover{ background:rgba(255,255,255,.06); color:rgba(255,255,255,.75); }
.conc-tab-btn.active{
    color:#fff; border-bottom-color:#f59e0b;
    background:rgba(255,255,255,.09);
}
.conc-tab-btn .conc-tab-badge{
    background:rgba(255,255,255,.12); border-radius:10px;
    padding:1px 7px; font-size:10px; font-weight:700; margin-left:1px; color:rgba(255,255,255,.7);
}
.conc-tab-btn.active .conc-tab-badge{ background:rgba(245,158,11,.25); color:#fde68a; }
.conc-tab-pane{ display:none; }
.conc-tab-pane.active{ display:block; }

/* ── DÍAS DE GRACIA ── */
.dg-infobar{
    background:#f8fafc; border-bottom:1px solid #e2e8f0;
    padding:9px 20px; font-size:11.5px; color:#475569;
    display:flex; align-items:center; gap:8px;
}
.dg-infobar i{ flex-shrink:0; color:#94a3b8; }
/* Tabla de roles */
.dg-tbl-wrap{ padding:0; }
.dg-tbl{ width:100%; border-collapse:collapse; font-size:13px; }
.dg-tbl thead tr{ background:#f9fafb; border-bottom:1px solid #e5e7eb; }
.dg-tbl th{
    padding:10px 18px; font-size:10px; font-weight:700; color:#6b7280;
    text-align:left; text-transform:uppercase; letter-spacing:.6px;
}
.dg-tbl th:first-child{ padding-left:22px; }
.dg-tbl th:last-child{ padding-right:22px; text-align:right; }
.dg-tbl td{
    padding:12px 18px; border-bottom:1px solid #f3f4f6;
    color:#374151; vertical-align:middle;
}
.dg-tbl td:first-child{ padding-left:22px; }
.dg-tbl td:last-child{ padding-right:22px; }
.dg-tbl tbody tr:last-child td{ border-bottom:none; }
.dg-tbl tbody tr:hover td{ background:#f9fafb; }
.dg-tbl tbody tr:hover td:first-child{ border-left:2px solid #334155; padding-left:20px; }
.dg-tbl-role{ font-weight:700; color:#1e293b; font-size:13px; }
/* Badges días+retención */
.dg-cell{ display:flex; align-items:center; gap:7px; }
.dg-dias-badge{
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 10px; border-radius:5px; font-size:12px; font-weight:700;
}
.dg-dias-contado{ background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.dg-dias-credito{ background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
.dg-ret-badge{
    display:inline-flex; align-items:center; gap:3px;
    padding:2px 8px; border-radius:20px; font-size:11px; font-weight:600;
    background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;
}
.dg-none-badge{
    display:inline-flex; align-items:center;
    padding:3px 10px; border-radius:5px; font-size:11px; font-weight:500;
    background:#f9fafb; color:#d1d5db; border:1px dashed #e5e7eb;
}
.dg-edit-btn{
    background:#fff; border:1px solid #d1d5db; color:#475569;
    border-radius:5px; padding:4px 12px; font-size:11.5px; font-weight:600;
    cursor:pointer; transition:all .12s; white-space:nowrap;
    display:inline-flex; align-items:center; gap:5px;
}
.dg-edit-btn:hover{ border-color:#475569; color:#1e293b; background:#f9fafb; }
/* Paginación */
.dg-pager{
    display:flex; align-items:center; justify-content:space-between;
    padding:9px 22px; border-top:1px solid #f3f4f6;
    font-size:11.5px; color:#9ca3af; background:#f9fafb;
}
.dg-pager-btns{ display:flex; gap:3px; }
.dg-pager-btn{
    width:28px; height:28px; border:1px solid #e5e7eb; background:#fff;
    border-radius:5px; cursor:pointer; font-size:11.5px; font-weight:600;
    color:#475569; display:flex; align-items:center; justify-content:center;
    transition:all .1s;
}
.dg-pager-btn:hover:not(:disabled){ background:#f3f4f6; border-color:#9ca3af; color:#1e293b; }
.dg-pager-btn:disabled{ opacity:.3; cursor:not-allowed; }
.dg-pager-btn.active{ background:#1e293b; border-color:#1e293b; color:#fff; }

/* Modal días de gracia */
#modalDiasGracia{ z-index:1055; }
#modalDiasGracia .modal-backdrop{ z-index:1054; }
#modalDiasGracia .modal-content{
    border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;
    box-shadow:0 20px 56px rgba(0,0,0,.18);
}
#modalDiasGracia .dgm-header{
    background:#1e293b;
    padding:14px 20px; display:flex; align-items:center; gap:12px;
}
#modalDiasGracia .dgm-header-title{
    font-size:14px; font-weight:700; color:#f1f5f9;
    display:flex; align-items:center; gap:9px; flex:1;
}
#modalDiasGracia .dgm-header-role{
    font-size:10.5px; font-weight:600;
    background:rgba(255,255,255,.12); color:rgba(255,255,255,.75);
    padding:3px 10px; border-radius:20px; border:1px solid rgba(255,255,255,.15);
    white-space:nowrap;
}
#modalDiasGracia .dgm-close{
    background:none; border:none; color:rgba(255,255,255,.55);
    font-size:20px; line-height:1; cursor:pointer; padding:0 2px;
    transition:color .12s;
}
#modalDiasGracia .dgm-close:hover{ color:#fff; }
/* Grid 2 columnas */
.dgm-grid{ display:grid; grid-template-columns:1fr 1fr; gap:0; background:#fff; }
.dgm-col{ padding:18px 20px; }
.dgm-col-contado{ border-right:1px solid #f3f4f6; }
.dgm-col-title{
    display:flex; align-items:center; gap:7px;
    font-size:12.5px; font-weight:700; margin-bottom:14px;
    padding-bottom:9px;
}
.dgm-col-title-contado{ color:#15803d; border-bottom:1px solid #d1fae5; }
.dgm-col-title-credito { color:#c2410c; border-bottom:1px solid #fed7aa; }
.dgm-col-title-ico{
    width:26px; height:26px; border-radius:6px;
    display:flex; align-items:center; justify-content:center; font-size:12px;
    flex-shrink:0;
}
.dgm-col-title-ico-contado{ background:#f0fdf4; color:#16a34a; }
.dgm-col-title-ico-credito { background:#fff7ed; color:#ea580c; }
/* Campos */
.dgm-field{ margin-bottom:12px; }
.dgm-field:last-child{ margin-bottom:0; }
.dgm-lbl{
    font-size:10px; font-weight:700; color:#9ca3af;
    text-transform:uppercase; letter-spacing:.5px;
    margin-bottom:5px;
}
.dgm-stepper{ display:flex; align-items:center; }
.dgm-step{
    width:32px; height:36px; border:1px solid #d1d5db; background:#f9fafb;
    color:#475569; font-size:17px; font-weight:700; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:all .1s; user-select:none; line-height:1;
}
.dgm-step:first-child{ border-radius:5px 0 0 5px; border-right:none; }
.dgm-step:last-child { border-radius:0 5px 5px 0; border-left:none; }
.dgm-step-contado:hover{ background:#f0fdf4; color:#16a34a; border-color:#86efac; }
.dgm-step-credito:hover { background:#fff7ed; color:#ea580c; border-color:#fdba74; }
.dgm-dias-inp{
    flex:1; height:36px; border:1px solid #d1d5db; border-radius:0;
    padding:0 6px; font-size:17px; font-weight:700; color:#1e293b;
    text-align:center; outline:none; transition:border-color .12s; background:#fff;
    min-width:0; -moz-appearance:textfield;
}
.dgm-dias-inp::-webkit-inner-spin-button,
.dgm-dias-inp::-webkit-outer-spin-button{ -webkit-appearance:none; margin:0; }
.dgm-dias-inp:focus{ border-color:#475569; z-index:1; position:relative; }
.dgm-dias-unit{
    height:36px; padding:0 10px; border:1px solid #d1d5db; border-left:none;
    border-radius:0 5px 5px 0; background:#f9fafb;
    font-size:11px; font-weight:600; color:#9ca3af;
    display:flex; align-items:center;
}
.dgm-pct-row{ display:flex; align-items:center; gap:8px; }
.dgm-pct-inp{
    width:100%; height:36px; border:1px solid #d1d5db; border-radius:5px;
    padding:0 10px; font-size:15px; font-weight:700; color:#1e293b;
    text-align:center; outline:none; transition:border-color .12s; background:#fff;
    -moz-appearance:textfield; box-sizing:border-box;
}
.dgm-pct-inp::-webkit-inner-spin-button,
.dgm-pct-inp::-webkit-outer-spin-button{ -webkit-appearance:none; margin:0; }
.dgm-col-contado .dgm-pct-inp:focus{ border-color:#16a34a; }
.dgm-col-credito  .dgm-pct-inp:focus{ border-color:#ea580c; }
.dgm-pct-sym{ font-size:13px; font-weight:700; color:#9ca3af; flex-shrink:0; }
.dgm-nota-inp{
    width:100%; height:34px; border:1px solid #d1d5db; border-radius:5px;
    padding:0 10px; font-size:12px; color:#475569; outline:none;
    transition:border-color .12s; background:#fff; box-sizing:border-box;
}
.dgm-nota-inp:focus{ border-color:#475569; }
.dgm-footer{
    padding:12px 20px; background:#f9fafb; border-top:1px solid #e5e7eb;
    display:flex; justify-content:flex-end; align-items:center; gap:8px;
}

/* ── AUDITORÍA TAB ── */
.aud-tipo-btn{
    padding:3px 10px; font-size:11px; font-weight:600; border-radius:4px;
    border:1px solid #e2e8f0; background:#fff; color:#6b7280; cursor:pointer;
    transition:all .1s;
}
.aud-tipo-btn.active{ background:#1e293b; border-color:#1e293b; color:#fff; }
.aud-tipo-btn:hover:not(.active){ background:#f3f4f6; border-color:#9ca3af; }
.aud-card{
    background:#fff; border:1px solid #e5e7eb; border-radius:7px;
    padding:13px 17px; margin-bottom:7px;
    display:grid; grid-template-columns:1fr auto;
    gap:12px; align-items:center;
    transition:border-color .12s;
}
.aud-card:hover{ border-color:#94a3b8; }
.aud-card-left{ display:flex; flex-direction:column; gap:4px; }
.aud-card-periodo{ font-size:14px; font-weight:700; color:#1e293b; }
.aud-card-fecha{ font-size:11px; color:#9ca3af; }
.aud-card-usuario{ font-size:11.5px; color:#475569; }
.aud-card-obs{ font-size:11px; color:#78350f; background:#fffbeb; border-radius:4px; padding:2px 7px; margin-top:2px; max-width:420px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block; }
.aud-badge-conciliacion{ background:#1e293b; color:#fff; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:700; }
.aud-badge-reapertura{ background:#7f1d1d; color:#fff; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:700; }
.aud-card-kpis{ display:flex; gap:20px; flex-wrap:wrap; align-items:center; }
.aud-kpi{ text-align:center; min-width:54px; }
.aud-kpi-val{ font-size:15px; font-weight:700; color:#1e293b; }
.aud-kpi-lbl{ font-size:9.5px; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:.4px; }
.aud-card-right{ display:flex; flex-direction:column; align-items:flex-end; gap:7px; }
.aud-btn-detalle{ background:#1e293b; color:#fff; border:none; border-radius:5px; padding:5px 13px; font-size:11.5px; font-weight:600; cursor:pointer; white-space:nowrap; }
.aud-btn-detalle:hover{ background:#334155; }
.aud-empty{ text-align:center; padding:40px 20px; color:#9ca3af; font-size:13px; }

@media(max-width:768px){
    .conc-topbar{ padding:12px 14px; }
    .conc-kpi-chip{ padding:4px 10px; }
    .conc-panel-body{ padding:14px 14px; }
    .det-toolbar{ padding:9px 10px; }
    .det-toolbar-info{ width:100%; justify-content:flex-start; }
    .det-search-wrap{ width:100%; }
    .det-export-btn{ margin-left:0; width:100%; justify-content:center; }
    .det-tabs{ overflow:auto; white-space:nowrap; padding:8px 8px 0; }
}
}

/</style>
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
                <strong>Contado:</strong> desde fecha de emisión.
                <strong>Crédito:</strong> desde vencimiento de factura.</span>
        </div>

        {{-- Panel explicativo de la regla de retención --}}
        <div style="padding:16px 20px 4px;">
            <div style="
                background:#fff;
                border:1.5px solid #e2e8f0;
                border-left:4px solid #f59e0b;
                border-radius:10px;
                overflow:hidden;
                box-shadow:0 1px 4px rgba(0,0,0,.05);
            ">
                {{-- Header colapsable --}}
                <div id="ret-rule-header" onclick="document.getElementById('ret-rule-body').classList.toggle('d-none'); this.querySelector('.ret-chevron').classList.toggle('fa-chevron-down'); this.querySelector('.ret-chevron').classList.toggle('fa-chevron-up');"
                    style="display:flex;align-items:center;gap:10px;padding:11px 16px;cursor:pointer;background:#fffbeb;border-bottom:1px solid #fde68a;user-select:none;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;background:#f59e0b;border-radius:7px;flex-shrink:0;">
                        <i class="fa fa-exclamation" style="color:#fff;font-size:13px;"></i>
                    </span>
                    <div style="flex:1;">
                        <div style="font-size:12.5px;font-weight:800;color:#92400e;letter-spacing:.2px;">Regla de retención por mora — activa en Aplicación de Pagos</div>
                        <div style="font-size:11px;color:#b45309;margin-top:1px;">Se ejecuta automáticamente cuando un pago cierra una factura. Clic para ver el detalle completo.</div>
                    </div>
                    <i class="fa fa-chevron-down ret-chevron" style="color:#b45309;font-size:11px;flex-shrink:0;"></i>
                </div>

                {{-- Cuerpo (colapsado por defecto) --}}
                <div id="ret-rule-body" class="d-none" style="padding:16px 20px;">

                    {{-- Dos tipos de factura --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">

                        {{-- CONTADO --}}
                        <div style="background:#fef2f2;border:1.5px solid #fecaca;border-radius:8px;padding:13px 15px;">
                            <div style="display:flex;align-items:center;gap:7px;margin-bottom:10px;">
                                <span style="background:#dc2626;border-radius:5px;padding:3px 9px;font-size:10px;font-weight:800;color:#fff;letter-spacing:.4px;">CONTADO</span>
                                <span style="font-size:11px;color:#b91c1c;font-weight:700;">Penalización total</span>
                            </div>
                            <div style="font-size:11.5px;color:#7f1d1d;line-height:1.75;">
                                <div><i class="fa fa-calendar-o" style="width:15px;"></i> <strong>Referencia:</strong> Fecha de emisión</div>
                                <div style="margin-top:5px;background:#fee2e2;border-radius:5px;padding:6px 10px;font-family:monospace;font-size:11px;">
                                    DiasTranscurridos = FechaCierre − FechaEmisión
                                </div>
                                <div style="margin-top:8px;padding:7px 10px;background:#dc2626;border-radius:6px;color:#fff;font-weight:700;font-size:11.5px;">
                                    <i class="fa fa-ban" style="margin-right:5px;"></i>
                                    Si excede DiasGracia → Comisión = L 0
                                </div>
                                <div style="margin-top:7px;font-size:11px;color:#991b1b;">
                                    <i class="fa fa-info-circle" style="margin-right:3px;"></i>
                                    La penalización es siempre el <strong>100% de la comisión generada</strong>. No usa el % configurado.
                                </div>
                            </div>
                        </div>

                        {{-- CRÉDITO --}}
                        <div style="background:#fff7ed;border:1.5px solid #fed7aa;border-radius:8px;padding:13px 15px;">
                            <div style="display:flex;align-items:center;gap:7px;margin-bottom:10px;">
                                <span style="background:#ea580c;border-radius:5px;padding:3px 9px;font-size:10px;font-weight:800;color:#fff;letter-spacing:.4px;">CRÉDITO</span>
                                <span style="font-size:11px;color:#c2410c;font-weight:700;">Retención acumulativa por período</span>
                            </div>
                            <div style="font-size:11.5px;color:#7c2d12;line-height:1.75;">
                                <div><i class="fa fa-calendar-o" style="width:15px;"></i> <strong>Referencia:</strong> Fecha de vencimiento</div>
                                <div style="margin-top:5px;background:#ffedd5;border-radius:5px;padding:6px 10px;font-family:monospace;font-size:11px;line-height:1.6;">
                                    DiasTranscurridos = FechaCierre − FechaVencimiento<br>
                                    PeriodosVencidos  = floor( DiasTranscurridos / DiasGracia )<br>
                                    RetenciónTotal    = PeriodosVencidos × (Subtotal × % / 100)<br>
                                    ComisiónFinal     = max(0, Comisión − RetenciónTotal)
                                </div>
                                <div style="margin-top:7px;font-size:11px;color:#9a3412;">
                                    <i class="fa fa-info-circle" style="margin-right:3px;"></i>
                                    Cada período genera un registro de auditoría independiente.
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Ejemplo numérico crédito --}}
                    <div style="background:#f0f9ff;border:1.5px solid #bae6fd;border-radius:8px;padding:13px 15px;margin-bottom:14px;">
                        <div style="display:flex;align-items:center;gap:7px;margin-bottom:10px;">
                            <i class="fa fa-table" style="color:#0369a1;font-size:13px;"></i>
                            <span style="font-size:12px;font-weight:800;color:#0369a1;">Ejemplo — Crédito con 30 días de gracia y 1% de retención</span>
                        </div>
                        <table style="width:100%;border-collapse:collapse;font-size:11.5px;">
                            <thead>
                                <tr style="background:#e0f2fe;">
                                    <th style="padding:5px 10px;text-align:left;color:#0369a1;font-weight:700;border-radius:4px 0 0 4px;">Días vencidos</th>
                                    <th style="padding:5px 10px;text-align:center;color:#0369a1;font-weight:700;">Períodos</th>
                                    <th style="padding:5px 10px;text-align:center;color:#0369a1;font-weight:700;">Retención acum.</th>
                                    <th style="padding:5px 10px;text-align:left;color:#0369a1;font-weight:700;border-radius:0 4px 4px 0;">Registros auditoría</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom:1px solid #e0f2fe;">
                                    <td style="padding:5px 10px;color:#0c4a6e;">35 días</td>
                                    <td style="padding:5px 10px;text-align:center;"><span style="background:#0369a1;color:#fff;border-radius:12px;padding:2px 9px;font-weight:700;">1</span></td>
                                    <td style="padding:5px 10px;text-align:center;color:#0c4a6e;font-weight:600;">1%</td>
                                    <td style="padding:5px 10px;color:#64748b;">Período #1</td>
                                </tr>
                                <tr style="border-bottom:1px solid #e0f2fe;">
                                    <td style="padding:5px 10px;color:#0c4a6e;">65 días</td>
                                    <td style="padding:5px 10px;text-align:center;"><span style="background:#0369a1;color:#fff;border-radius:12px;padding:2px 9px;font-weight:700;">2</span></td>
                                    <td style="padding:5px 10px;text-align:center;color:#0c4a6e;font-weight:600;">2%</td>
                                    <td style="padding:5px 10px;color:#64748b;">Período #1 + #2</td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 10px;color:#0c4a6e;">95 días</td>
                                    <td style="padding:5px 10px;text-align:center;"><span style="background:#0369a1;color:#fff;border-radius:12px;padding:2px 9px;font-weight:700;">3</span></td>
                                    <td style="padding:5px 10px;text-align:center;color:#0c4a6e;font-weight:600;">3%</td>
                                    <td style="padding:5px 10px;color:#64748b;">Período #1 + #2 + #3</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Condiciones y exclusiones --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:11px 13px;">
                            <div style="font-size:10.5px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:7px;">
                                <i class="fa fa-check-circle" style="color:#16a34a;margin-right:4px;"></i>Cuándo se aplica
                            </div>
                            <div style="display:flex;flex-direction:column;gap:5px;font-size:11.5px;color:#334155;">
                                <div><i class="fa fa-circle" style="font-size:6px;color:#16a34a;margin-right:6px;vertical-align:2px;"></i>El pago <strong>cierra completamente</strong> la factura</div>
                                <div><i class="fa fa-circle" style="font-size:6px;color:#16a34a;margin-right:6px;vertical-align:2px;"></i>El rol tiene <strong>días de gracia &gt; 0</strong> configurados</div>
                                <div><i class="fa fa-circle" style="font-size:6px;color:#16a34a;margin-right:6px;vertical-align:2px;"></i>Los días transcurridos <strong>superan</strong> el período de gracia</div>
                                <div><i class="fa fa-circle" style="font-size:6px;color:#16a34a;margin-right:6px;vertical-align:2px;"></i>Para crédito: el rol tiene <strong>% de retención &gt; 0</strong></div>
                            </div>
                        </div>
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:11px 13px;">
                            <div style="font-size:10.5px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:7px;">
                                <i class="fa fa-ban" style="color:#dc2626;margin-right:4px;"></i>Cuándo NO se aplica
                            </div>
                            <div style="display:flex;flex-direction:column;gap:5px;font-size:11.5px;color:#334155;">
                                <div><i class="fa fa-circle" style="font-size:6px;color:#dc2626;margin-right:6px;vertical-align:2px;"></i>El rol no tiene configuración de retención</div>
                                <div><i class="fa fa-circle" style="font-size:6px;color:#dc2626;margin-right:6px;vertical-align:2px;"></i>El pago es anticipado o dentro del período de gracia</div>
                                <div><i class="fa fa-circle" style="font-size:6px;color:#dc2626;margin-right:6px;vertical-align:2px;"></i>La factura queda con saldo pendiente (no cerró)</div>
                                <div><i class="fa fa-circle" style="font-size:6px;color:#dc2626;margin-right:6px;vertical-align:2px;"></i>Ya existe un registro de retención previo (sin duplicados)</div>
                            </div>
                        </div>
                    </div>

                    {{-- Auditoría --}}
                    <div style="background:#faf5ff;border:1.5px solid #e9d5ff;border-radius:8px;padding:11px 14px;">
                        <div style="font-size:10.5px;font-weight:800;color:#7c3aed;text-transform:uppercase;letter-spacing:.5px;margin-bottom:7px;">
                            <i class="fa fa-database" style="margin-right:4px;"></i>Auditoría e histórico — tabla <code style="background:#ede9fe;padding:1px 5px;border-radius:3px;">retencion_mora_log</code>
                        </div>
                        <div style="font-size:11.5px;color:#4c1d95;line-height:1.7;">
                            Cada evento de retención queda registrado de forma <strong>inmutable</strong> con:
                            factura, rol, usuario afectado, tipo de factura, fecha de aplicación, días transcurridos,
                            días de gracia configurados, porcentaje aplicado, número de período (crédito),
                            comisión original, monto retenido, subtotal de factura y usuario que ejecutó el pago.
                            Los registros <strong>no se sobrescriben</strong> — permiten reconstruir el historial completo.
                        </div>
                    </div>

                </div>
            </div>
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
                    style="background:#1e293b;color:#fff;font-weight:600;border:none;min-width:150px;">
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
                            <div class="det-search-wrap">
                                <i class="fa fa-search det-search-ico"></i>
                                <input id="det-search-emp" type="text" oninput="detBuscar('emp', this.value)"
                                    placeholder="Buscar empleado o rol..."
                                    class="det-search-input">
                            </div>
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
                        <div class="det-search-wrap">
                            <i class="fa fa-search det-search-ico"></i>
                            <input id="det-search-fac" type="text" oninput="detBuscar('fac', this.value)"
                                placeholder="Buscar factura, cliente, empleado..."
                                class="det-search-input">
                        </div>
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
                        <div class="det-search-wrap">
                            <i class="fa fa-search det-search-ico"></i>
                            <input id="det-search-log" type="text" oninput="detBuscar('log', this.value)"
                                placeholder="Buscar acción, usuario, observación..."
                                class="det-search-input">
                        </div>
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
let detSearch = { emp:'', fac:'', log:'' };
let detSearchCache = {};
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
        + '<thead><tr>'
        + '<th>Período</th>'
        + '<th>Estado</th>'
        + '<th style="text-align:right;">Total Comisiones</th>'
        + '<th style="text-align:right;">Facturas</th>'
        + '<th>Conciliado por</th>'
        + '<th>Fecha Conciliación</th>'
        + '<th style="text-align:right;">Acciones</th>'
        + '</tr></thead><tbody>';

    periodos.forEach(p => {
        const rowClass = p.estado === 'sin_abrir'  ? 'row-sin-abrir'
                       : p.es_mes_actual           ? 'row-mes-actual' : '';
        const mesActBadge = p.es_mes_actual
            ? ' <span style="background:#f59e0b;color:#fff;border-radius:3px;padding:1px 6px;font-size:9.5px;font-weight:700;vertical-align:middle;">MES</span>'
            : '';
        html += `<tr class="${rowClass}">
            <td style="font-weight:600;">${p.periodo_label}${mesActBadge}</td>
            <td>${badgeEstado(p.estado)}</td>
            <td class="td-num" style="font-weight:700;color:#1e293b;">${p.total_comision > 0 ? 'L ' + numFmt(p.total_comision) : '<span style="color:#d1d5db;">—</span>'}</td>
            <td class="td-num">${p.cantidad_facturas > 0 ? p.cantidad_facturas : '<span style="color:#d1d5db;">—</span>'}</td>
            <td style="font-size:12.5px;color:#6b7280;">${p.usuario_concilio ?? '<span style="color:#d1d5db;">—</span>'}</td>
            <td style="font-size:12.5px;color:#6b7280;">${p.fecha_conciliacion ? fmtFecha(p.fecha_conciliacion) : '<span style="color:#d1d5db;">—</span>'}</td>
            <td class="td-actions">${renderAcciones(p)}</td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    document.getElementById('conc-tabla-wrapper').innerHTML = html;
}

function renderAcciones(p) {
    if (p.estado === 'sin_abrir') return '<span style="font-size:11.5px;color:#d1d5db;">Futuro</span>';
    if (p.estado === 'conciliado') {
        return `<div style="display:flex;gap:6px;justify-content:flex-end;">
            <button class="conc-btn btn-detalle" onclick="abrirDetalle('${p.periodo}')"><i class="fa fa-search"></i> Ver detalle</button>
            <button class="conc-btn btn-reabrir" onclick="abrirReabrir('${p.periodo}','${p.periodo_label}')"><i class="fa fa-unlock"></i> Reabrir</button>
        </div>`;
    }
    const btnConciliar = (p.total_comision > 0 || p.es_mes_actual)
        ? `<button class="conc-btn btn-conciliar" onclick="abrirConciliar('${p.periodo}','${p.periodo_label}')"><i class="fa fa-lock"></i> Conciliar</button>`
        : `<button class="conc-btn btn-conciliar btn-disabled" disabled><i class="fa fa-lock"></i> Conciliar</button>`;
    return `<div style="display:flex;gap:6px;justify-content:flex-end;">
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
    detSearch = detSearchCache[periodo]
        ? { ...detSearchCache[periodo] }
        : { emp:'', fac:'', log:'' };
    document.getElementById('det-label').textContent = '—';
    detTab('det-empleados', document.querySelector('.det-tab'));
    document.getElementById('det-loader-emp').classList.remove('d-none');
    document.getElementById('det-tbl-emp-wrap').classList.add('d-none');
    ['det-emp-body','det-fac-body','det-log-body'].forEach(id => document.getElementById(id).innerHTML = '');
    ['pag-emp','pag-fac','pag-log'].forEach(id => document.getElementById(id).innerHTML = '');
    ['det-cnt-emp','det-cnt-fac','det-cnt-log'].forEach(id => document.getElementById(id).textContent = '—');
    const empInput = document.getElementById('det-search-emp');
    const facInput = document.getElementById('det-search-fac');
    const logInput = document.getElementById('det-search-log');
    if (empInput) empInput.value = detSearch.emp || '';
    if (facInput) facInput.value = detSearch.fac || '';
    if (logInput) logInput.value = detSearch.log || '';
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

function normText(v) {
    return String(v || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();
}

function detBuscar(tab, value) {
    detSearch[tab] = value || '';
    if (detData.periodo) {
        detSearchCache[detData.periodo] = { ...detSearch };
    }
    if (tab === 'emp') renderEmpPage(1);
    if (tab === 'fac') renderFacPage(1);
    if (tab === 'log') renderLogPage(1);
}

/* ── Página: Empleados ──────────────────────────────────────── */
function renderEmpPage(page) {
    detPage.emp = page;
    const arrAll = detData.empleados || [];
    const query = normText(detSearch.emp);
    const arr = query
        ? arrAll.filter(e => {
            const bag = normText(`${e.nombre} ${e.rol} ${e.facturas}`);
            return bag.includes(query);
        })
        : arrAll;
    const total = arr.length;
    const totalGlobal = arrAll.length;
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
    document.getElementById('det-emp-body').innerHTML = html || `<tr><td colspan="6" class="text-center text-muted" style="padding:24px;">${query ? 'Sin coincidencias para el filtro aplicado' : 'Sin empleados con comisión en este período'}</td></tr>`;
    document.getElementById('det-emp-total').textContent = 'L ' + numFmt(totalComision);
    document.getElementById('det-toolbar-emp-info').textContent = total > 0
        ? (query
            ? `Mostrando ${start+1}–${end} de ${total} empleados filtrados (total ${totalGlobal})`
            : `Mostrando ${start+1}–${end} de ${total} empleados`)
        : 'Sin registros';
    renderPaginacion('pag-emp', page, total, 'renderEmpPage');
}

/* ── Página: Facturas ───────────────────────────────────────── */
function renderFacPage(page) {
    detPage.fac = page;
    const arrAll = detData.facturas || [];
    const query = normText(detSearch.fac);
    const tipoLabel = {1:'Facturador', 2:'Rol Real', 3:'Vendedor'};
    const arr = query
        ? arrAll.filter(f => {
            const bag = normText(`${f.factura_id} ${f.correlativo} ${f.cliente} ${f.empleado} ${f.rol} ${tipoLabel[f.tipo_comision] || ''}`);
            return bag.includes(query);
        })
        : arrAll;
    const total = arr.length;
    const totalGlobal = arrAll.length;
    const start = (page-1)*DET_PAGE_SIZE, end = Math.min(start+DET_PAGE_SIZE, total);
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
    document.getElementById('det-fac-body').innerHTML = html || `<tr><td colspan="8" class="text-center text-muted" style="padding:24px;">${query ? 'Sin coincidencias para el filtro aplicado' : 'Sin facturas comisionadas en este período'}</td></tr>`;
    document.getElementById('det-toolbar-fac-info').textContent = total > 0
        ? (query
            ? `Mostrando ${start+1}–${end} de ${total} facturas filtradas (total ${totalGlobal})`
            : `Mostrando ${start+1}–${end} de ${total} facturas`)
        : 'Sin registros';
    renderPaginacion('pag-fac', page, total, 'renderFacPage');
}

/* ── Página: Logs ───────────────────────────────────────────── */
function renderLogPage(page) {
    detPage.log = page;
    const arrAll = detData.logs || [];
    const query = normText(detSearch.log);
    const estadoLbl = {0:'Abierto', 1:'Conciliado'};
    const arr = query
        ? arrAll.filter(l => {
            const accion = l.accion === 'conciliacion' ? 'conciliacion' : 'reapertura';
            const bag = normText(`${accion} ${estadoLbl[l.estado_anterior]} ${estadoLbl[l.estado_nuevo]} ${l.observacion} ${l.usuario_nombre}`);
            return bag.includes(query);
        })
        : arrAll;
    const total = arr.length;
    const totalGlobal = arrAll.length;
    const start = (page-1)*DET_PAGE_SIZE, end = Math.min(start+DET_PAGE_SIZE, total);
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
    document.getElementById('det-log-body').innerHTML = html || `<tr><td colspan="9" class="text-center text-muted" style="padding:24px;">${query ? 'Sin coincidencias para el filtro aplicado' : 'Sin historial de acciones'}</td></tr>`;
    document.getElementById('det-toolbar-log-info').textContent = total > 0
        ? (query
            ? `Mostrando ${start+1}–${end} de ${total} registros filtrados (total ${totalGlobal})`
            : `Mostrando ${start+1}–${end} de ${total} registros`)
        : 'Sin registros';
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

