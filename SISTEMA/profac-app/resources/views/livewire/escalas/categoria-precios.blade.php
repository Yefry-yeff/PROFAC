@push('styles')
<style>
/* ── PROFAC design system ── */
:root {
    --pf-grad: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    --pf-grad-hover: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    --pf-orange: #e67e22;
    --pf-radius: 8px;
    --pf-shadow: 0 1px 6px rgba(0,0,0,.08);
}

/* ── Tarjetas ── */
.cat-card {
    border: 1px solid #f0e6d8;
    border-radius: var(--pf-radius);
    box-shadow: var(--pf-shadow);
    margin-bottom: 1rem;
    overflow: hidden;
    background: #fff;
}
.cat-card-header {
    background: var(--pf-grad);
    color: #fff;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
}
.cat-card-header h6 {
    margin: 0;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
    color: rgba(255,255,255,.95);
}
.cat-card-header h6 i { font-size: .75rem; opacity: .85; }

/* ── Botón principal naranja ── */
.btn.btn-pf-primary,
a.btn.btn-pf-primary {
    background: var(--pf-grad) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 5px !important;
    font-weight: 600 !important;
    font-size: .75rem;
    padding: 4px 12px;
    letter-spacing: .02em;
    box-shadow: 0 1px 3px rgba(230,126,34,.30) !important;
    transition: background .18s, box-shadow .18s;
}
.btn.btn-pf-primary:hover,
.btn.btn-pf-primary:focus,
a.btn.btn-pf-primary:hover {
    background: var(--pf-grad-hover) !important;
    color: #fff !important;
    box-shadow: 0 2px 6px rgba(230,126,34,.40) !important;
    text-decoration: none !important;
}

/* ── Tabla principal ── */
#tbl_listaCategoria thead th {
    background: #fdf4ea;
    color: #7d4600;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    border-bottom: 2px solid #ecd5b5;
    white-space: nowrap;
    padding: 7px 10px;
}
#tbl_listaCategoria tbody tr:hover { background-color: #fffcf7; }
#tbl_listaCategoria tbody tr { cursor: pointer; }
#tbl_listaCategoria td { font-size: .80rem; vertical-align: middle; padding: 5px 10px; }

/* ── Modal principal ── */
/*
 * NO tocar overflow del .modal — Bootstrap lo necesita para scroll y posicionamiento.
 * Solo hacer overflow:visible en .modal-dialog hacia abajo para que Select2
 * (renderizado en body con z-index alto) pueda salir sin clip.
 */
#modalCategoriasPrecios .modal-dialog {
    overflow: visible !important;
    margin-top: 60px; /* evita que se corte detrás del navbar */
}
#modalCategoriasPrecios .modal-content,
#modalSeleccionarCategoriasGeneral .modal-content {
    border: none;
    border-radius: 14px;
    box-shadow: 0 20px 60px rgba(0,0,0,.22);
    overflow: visible !important;
}
#modalCategoriasPrecios .modal-header {
    background: var(--pf-grad);
    padding: 16px 22px 14px;
    border-bottom: none;
    border-radius: 14px 14px 0 0; /* border-radius solo arriba */
}
#modalCategoriasPrecios .modal-header-inner { line-height: 1.3; }
#modalCategoriasPrecios .modal-title {
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    letter-spacing: .03em;
    margin-bottom: 2px;
}
#modalCategoriasPrecios .modal-subtitle {
    color: rgba(255,255,255,.78);
    font-size: .75rem;
    font-weight: 400;
    margin: 0;
}
#modalCategoriasPrecios .close {
    color: rgba(255,255,255,.85);
    text-shadow: none;
    opacity: 1;
    font-size: 1.5rem;
    padding: 0; margin: 0;
    align-self: flex-start;
    margin-top: 2px;
}
#modalCategoriasPrecios .close:hover { color: #fff; }
#modalCategoriasPrecios .modal-body {
    background: #fff;
    padding: 22px 26px 10px;
    overflow: visible !important;
}
#modalCategoriasPrecios .modal-footer {
    background: #f8f5f0;
    border-top: 1px solid #eddfc9;
    padding: 12px 22px;
    border-radius: 0 0 14px 14px; /* border-radius solo abajo */
}
#modalCategoriasPrecios .form-control {
    border-radius: 7px;
    font-size: .88rem;
    border-color: #ddd4c8;
    background: #fdfaf7;
    transition: border-color .2s, box-shadow .2s, background .2s;
}
#modalCategoriasPrecios .form-control:focus {
    border-color: var(--pf-orange);
    box-shadow: 0 0 0 3px rgba(243,156,18,.15);
    background: #fff;
}
#modalCategoriasPrecios .input-group-text {
    background: #fef3e2;
    border-color: #ddd4c8;
    color: #e67e22;
    font-weight: 700;
    font-size: .85rem;
    border-radius: 0 7px 7px 0;
}
#modalCategoriasPrecios .input-group .form-control {
    border-radius: 7px 0 0 7px;
}
#modalCategoriasPrecios label {
    font-size: .8rem;
    font-weight: 600;
    color: #5a4a38;
    margin-bottom: 5px;
    display: block;
}
/* ── Sección divisor dentro del form ── */
.pf-form-section {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .09em;
    color: #c97c20;
    padding: 5px 0 6px;
    border-bottom: 2px solid #fde8cc;
    margin-bottom: 14px;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}
/* ── Required hint ── */
.pf-required-hint {
    font-size: .73rem;
    color: #999;
    margin-bottom: 16px;
    margin-top: -4px;
}
/* ── Select2 dentro del modal ── */
#modalCategoriasPrecios .select2-container--bootstrap4 .select2-selection--single {
    height: 38px;
    background: #fdfaf7;
    border-color: #ddd4c8;
    border-radius: 7px;
}
#modalCategoriasPrecios .select2-container--bootstrap4 .select2-selection--single:focus-within,
#modalCategoriasPrecios .select2-container--bootstrap4.select2-container--focus .select2-selection--single {
    border-color: #e67e22;
    box-shadow: 0 0 0 3px rgba(243,156,18,.15);
}

/* ── Modal selección categorías (modo general) ── */
#modalSeleccionarCategoriasGeneral .modal-header {
    background: linear-gradient(135deg, #f39c12 0%, #f0a500 100%);
    padding: 12px 20px;
    border-bottom: none;
}
#modalSeleccionarCategoriasGeneral .modal-title { color: #fff; font-weight: 700; }
#modalSeleccionarCategoriasGeneral .modal-footer { background: #fafafa; border-top: 1px solid #f0e8dd; }

/* ── Select2 ── */
/* NO z-index en .select2-container: el elemento dropdown tiene ambas clases
   (.select2-container y .select2-dropdown) y si ambas tienen !important con
   igual especificidad, gana la que aparece última en el CSS — imprevisible. */
.select2-container { width: 100% !important; font-size: .9rem; }
.select2-dropdown { z-index: 99999 !important; }
.select2-container--open { z-index: 99999 !important; }
.select2-container--bootstrap4 .select2-selection--single {
    height: 38px; padding: 6px 12px;
    border-radius: .35rem; border: 1px solid #ced4da;
}
.select2-container--bootstrap4 .select2-selection__rendered { line-height: 28px; padding-left: .5rem; padding-right: 2rem; }
.select2-container--bootstrap4 .select2-selection__arrow { height: 34px; right: 8px; }
.select2-container--bootstrap4 .select2-selection__placeholder { color: #6c757d; }
.select2-container--bootstrap4 .select2-selection--single .select2-selection__clear {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
}
.modal .select2-container { width: 100% !important; }

/* ── Filtros plantilla ── */
.filtro-container { display: flex; flex-wrap: wrap; gap: .4rem; align-items: flex-start; }
.filtro-item { flex: 1 1 180px; min-width: 160px; max-width: 260px; }
.filtro-item .select2-container { width: 100% !important; }
.filtro-item .select2-container--bootstrap4 .select2-selection--single {
    height: 32px;
    font-size: .78rem;
    border-color: #d8cfc7;
    border-radius: 5px;
    background: #fdfaf7;
    color: #444;
}
.filtro-item .select2-container--bootstrap4 .select2-selection__rendered { line-height: 22px; padding-left: .6rem; font-size: .78rem; }
.filtro-item .select2-container--bootstrap4 .select2-selection__arrow { height: 30px; }
.filtro-item .select2-container--bootstrap4 .select2-selection--single:focus,
.filtro-item .select2-container--bootstrap4.select2-container--focus .select2-selection--single {
    border-color: #e67e22;
    box-shadow: 0 0 0 2px rgba(243,156,18,.12);
}
#btnDescargar { height: 32px; flex: 0 0 auto; font-size: .75rem; padding: 0 14px; align-self: flex-start; }

/* ── Wizard de plantilla ── */
.pf-wizard-card {
    background: #fff;
    border: 1px solid #f0e6d8;
    border-radius: 12px;
    box-shadow: 0 1px 6px rgba(210,150,60,.10);
    margin-bottom: 1.5rem;
    overflow: hidden;
}
.pf-wizard-header {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    padding: 14px 22px 0;
}
.pf-wizard-header h6 {
    color: #fff;
    font-size: .82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    margin-bottom: 12px;
}
.pf-wizard-tabs {
    display: flex;
    gap: 4px;
}
.pf-wizard-tab {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 8px 20px;
    border-radius: 8px 8px 0 0;
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    cursor: pointer;
    border: none;
    background: rgba(255,255,255,.18);
    color: rgba(255,255,255,.75);
    transition: background .2s, color .2s;
}
.pf-wizard-tab.active {
    background: #fff;
    color: #e67e22;
}
.pf-wizard-tab .tab-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px; height: 20px;
    border-radius: 50%;
    font-size: .72rem;
    background: rgba(255,255,255,.25);
    color: inherit;
    font-weight: 800;
    flex-shrink: 0;
}
.pf-wizard-tab.active .tab-num {
    background: #f39c12;
    color: #fff;
}
.pf-wizard-tab.tab-done .tab-num {
    background: #27ae60;
    color: #fff;
}
.pf-wizard-tab.tab-done {
    color: rgba(255,255,255,.9);
}
.pf-wizard-body { padding: 22px 22px 18px; }
.pf-wizard-pane { display: none; }
.pf-wizard-pane.active { display: block; }
.pf-step-label {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #c97c20;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.pf-step-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #fde8cc;
}
/* Upload zone */
.pf-upload-zone {
    border: 2px dashed #e0cfc0;
    border-radius: 10px;
    background: #fdfaf7;
    padding: 24px 20px;
    text-align: center;
    transition: border-color .2s, background .2s;
    cursor: pointer;
    position: relative;
}
.pf-upload-zone:hover, .pf-upload-zone.drag-over {
    border-color: #e67e22;
    background: #fffaf4;
}
.pf-upload-zone input[type=file] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.pf-upload-zone .upload-icon { font-size: 2rem; color: #e0cfc0; margin-bottom: 8px; }
.pf-upload-zone.has-file .upload-icon { color: #27ae60; }
.pf-upload-zone .upload-text { font-size: .8rem; color: #888; }
.pf-upload-zone .upload-filename { font-size: .83rem; font-weight: 600; color: #444; margin-top: 4px; }
/* Import action bar */
.pf-import-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 14px;
    flex-wrap: wrap;
}
.pf-progress-wrap { margin-top: 12px; }
.pf-progress-wrap .progress { height: 6px; border-radius: 4px; }

/* ── Limpiar archivo ── */
#btnLimpiarArchivoPrecios {
    background: transparent; border: none; color: #dc3545;
    padding: .25rem .4rem; font-size: 1.2rem; line-height: 1;
    transition: all .2s; border-radius: .25rem;
}
#btnLimpiarArchivoPrecios:hover { background: rgba(220,53,69,.1); color: #c82333; transform: scale(1.1); }
#btnLimpiarArchivoPrecios:active { transform: scale(.95); }

/* ── Sticky thead ── */
.sticky-top { position: sticky; top: 0; z-index: 10; }

/* ── SweetAlert2 sobre modales Bootstrap ── */
.swal-sobre-modal { z-index: 99999 !important; }

/* ── Select2: limitar altura del dropdown a ~7 ítems ── */
.select2-container--bootstrap4 .select2-results__options {
    max-height: 196px;
    overflow-y: auto;
}
.select2-container--bootstrap4 .select2-results__option {
    font-size: .8rem;
    padding: 5px 10px;
}
.select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
    font-size: .78rem;
    border-color: #d8cfc7;
    border-radius: 4px;
    padding: 4px 8px;
}
.select2-container--bootstrap4 .select2-dropdown {
    border-color: #d8cfc7;
    border-radius: 5px;
    box-shadow: 0 3px 10px rgba(0,0,0,.12);
}

/* ── Overlay ── */
#overlayProcesandoPrecios {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,.7); z-index: 9999;
    display: none; justify-content: center; align-items: center;
}
#overlayProcesandoPrecios .overlay-content {
    background: #fff; padding: 30px; border-radius: 10px;
    text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,.3); min-width: 300px;
}

/* ── Responsive tablet/móvil ── */
@media (max-width: 767px) {
    #modalCategoriasPrecios .modal-dialog,
    #modalSeleccionarCategoriasGeneral .modal-dialog {
        margin: 10px;
        max-width: calc(100% - 20px);
    }
    #modalCategoriasPrecios .modal-body { padding: 16px 14px 6px; max-height: calc(100vh - 160px); }
    #modalCategoriasPrecios .form-row > [class*="col-"] { flex: 0 0 100%; max-width: 100%; }
    #modalCategoriasPrecios .modal-footer,
    #modalSeleccionarCategoriasGeneral .modal-footer {
        flex-direction: column-reverse; gap: 8px; padding: 10px 14px;
    }
    #modalCategoriasPrecios .modal-footer .btn,
    #modalSeleccionarCategoriasGeneral .modal-footer .btn { width: 100%; text-align: center; }
    .cat-card-header { gap: 8px; }
    .cat-card-header h6 { font-size: .8rem; }
    #tbl_listaCategoria thead th, #tbl_listaCategoria td { font-size: .75rem; }
    .filtro-container { flex-direction: column !important; }
    .filtro-container .btn, .filtro-container .form-control,
    .filtro-container .filtro-item, .filtro-container .filtro-select { width: 100% !important; }
    .pf-wizard-tabs { gap: 0; }
    .pf-wizard-tab { padding: 7px 12px; font-size: .7rem; flex: 1; justify-content: center; }
    .pf-import-bar { flex-direction: column; align-items: stretch; }
    .pf-import-bar .btn { width: 100%; }
}
@media (min-width: 992px) {
    .filtro-select { min-width: 240px; flex: 1 1 240px; }
}

/* ── Modal Ver / Editar Categorías de Precio ── */
/* ── Animaciones del modal ── */
@keyframes pf-modal-in {
    from { opacity: 0; transform: translateY(-28px) scale(.97); }
    to   { opacity: 1; transform: translateY(0)   scale(1);    }
}
@keyframes pf-modal-out {
    from { opacity: 1; transform: translateY(0)   scale(1);    }
    to   { opacity: 0; transform: translateY(20px) scale(.97); }
}
@keyframes pf-dropdown-in {
    from { opacity: 0; transform: translateY(-6px) scale(.97); }
    to   { opacity: 1; transform: translateY(0)    scale(1);   }
}
#modalVerCatPrecios .modal-content {
    border: none;
    border-radius: 14px;
    box-shadow: 0 20px 60px rgba(0,0,0,.22);
    overflow: visible;
}
#modalVerCatPrecios .modal-dialog {
    margin-top: 90px;
    max-width: 900px;
    width: calc(100% - 20px);
    margin-left: auto;
    margin-right: auto;
}
#modalVerCatPrecios.show .modal-dialog {
    animation: pf-modal-in .28s cubic-bezier(.22,.68,0,1.2) both;
}
#modalVerCatPrecios.pf-hiding .modal-dialog {
    animation: pf-modal-out .2s ease-in both;
}
#modalVerCatPrecios .modal-header {
    background: var(--pf-grad);
    padding: 16px 22px 14px;
    border-bottom: none;
    border-radius: 14px 14px 0 0;
}
#modalVerCatPrecios .modal-footer {
    background: #f8f5f0;
    border-top: 1px solid #eddfc9;
    padding: 12px 22px;
    border-radius: 0 0 14px 14px;
    flex-wrap: wrap;
    gap: 8px;
}
/* Tabla responsive */
#tbl_catPrecios_lista { min-width: 480px; }
#tbl_catPrecios_lista thead th {
    background: #f8f0e6;
    color: #7d4600;
    font-size: .76rem;
    font-weight: 700;
    border-bottom: 2px solid #e8c49a;
    white-space: nowrap;
    padding: 8px 6px;
}
#tbl_catPrecios_lista td {
    font-size: .82rem;
    vertical-align: middle;
    padding: 5px 6px;
}
/* Ocultar columnas menos importantes en mobile */
@media (max-width: 575px) {
    #tbl_catPrecios_lista .col-hide-xs { display: none; }
    #modalVerCatPrecios .modal-dialog { margin-top: 14px; }
    #modalVerCatPrecios .modal-footer { flex-direction: column-reverse; }
    #modalVerCatPrecios .modal-footer .btn { width: 100%; justify-content: center; }
}
@media (max-width: 767px) {
    #tbl_catPrecios_lista .col-hide-sm { display: none; }
}
/* ── Dropdown de acciones por fila ── */
.cat-action-dropdown .dropdown-toggle {
    background: linear-gradient(135deg,#5d6d7e 0%,#4a5568 100%);
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: .72rem;
    font-weight: 600;
    padding: 4px 10px;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
    transition: background .15s, box-shadow .15s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.cat-action-dropdown .dropdown-toggle:hover {
    background: linear-gradient(135deg,#4a5568 0%,#2d3748 100%);
    box-shadow: 0 2px 6px rgba(0,0,0,.25);
}
.cat-action-dropdown .dropdown-toggle::after { margin-left: 2px; }
.cat-action-dropdown .dropdown-menu {
    border: none;
    border-radius: 10px;
    box-shadow: 0 6px 24px rgba(0,0,0,.14);
    padding: 4px 0;
    min-width: 160px;
    font-size: .8rem;
    transform-origin: top right;
    z-index: 99999 !important;
}
.cat-action-dropdown .dropdown-menu.show {
    animation: pf-dropdown-in .18s cubic-bezier(.22,.68,0,1.15) both;
}
.cat-action-dropdown .dropdown-item {
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    transition: background .12s;
}
.cat-action-dropdown .dropdown-item:hover { background: #fdf6ee; }
.cat-action-dropdown .dropdown-item.item-edit  { color: #2471a3; }
.cat-action-dropdown .dropdown-item.item-excel { color: #1e8449; }
.cat-action-dropdown .dropdown-item.item-deact { color: #c0392b; }
.cat-action-dropdown .dropdown-divider { margin: 3px 0; }
.btn-edit-cat {
    display: inline-flex; align-items: center; gap: 4px;
    background: linear-gradient(135deg, #3498db 0%, #2471a3 100%);
    color: #fff; border: none;
    font-size: .72rem; padding: 4px 10px;
    border-radius: 6px; font-weight: 600; cursor: pointer;
    box-shadow: 0 1px 3px rgba(52,152,219,.35);
    transition: background .15s, box-shadow .15s, transform .1s;
}
.btn-edit-cat:hover {
    background: linear-gradient(135deg, #2471a3 0%, #1a5276 100%);
    box-shadow: 0 2px 6px rgba(52,152,219,.4);
    color: #fff;
}
.btn-edit-cat:active { transform: scale(.96); }
.btn-save-cat {
    display: inline-flex; align-items: center; gap: 4px;
    background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
    color: #fff; border: none;
    font-size: .72rem; padding: 4px 10px;
    border-radius: 6px; font-weight: 600; cursor: pointer;
    box-shadow: 0 1px 3px rgba(39,174,96,.35);
    transition: background .15s, box-shadow .15s, transform .1s;
}
.btn-save-cat:hover {
    background: linear-gradient(135deg, #1e8449 0%, #186a3b 100%);
    box-shadow: 0 2px 6px rgba(39,174,96,.4);
    color: #fff;
}
.btn-save-cat:active { transform: scale(.96); }
.btn-cancel-cat {
    display: inline-flex; align-items: center; gap: 4px;
    background: #f4f4f4; color: #555;
    border: 1px solid #d0d0d0;
    font-size: .72rem; padding: 4px 10px;
    border-radius: 6px; font-weight: 600; cursor: pointer;
    transition: background .15s, transform .1s;
}
.btn-cancel-cat:hover { background: #e8e8e8; color: #333; }
.btn-cancel-cat:active { transform: scale(.96); }
.btn-deact-cat {
    display: inline-flex; align-items: center; gap: 4px;
    background: transparent; color: #e74c3c;
    border: 1px solid #e74c3c;
    font-size: .72rem; padding: 4px 10px;
    border-radius: 6px; font-weight: 600; cursor: pointer;
    transition: background .15s, color .15s, box-shadow .15s, transform .1s;
}
.btn-deact-cat:hover {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: #fff;
    box-shadow: 0 2px 6px rgba(231,76,60,.35);
}
.btn-deact-cat:active { transform: scale(.96); }
.edit-cat-input { font-size: .8rem !important; height: 28px !important; padding: 2px 6px !important; }

/* ── Overlay custom % Comisiones por Rol ── */
#mcOverlay {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    z-index: 9999;
    background: rgba(0,0,0,.55);
    display: none;
    align-items: center;
    justify-content: center;
}
.mc-popup {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 20px 60px rgba(0,0,0,.35);
    width: calc(100% - 32px);
    max-width: 500px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    animation: mc-popup-in .22s cubic-bezier(.22,.68,0,1.15) both;
    overflow: hidden;
}
@keyframes mc-popup-in {
    from { opacity:0; transform: scale(.94) translateY(12px); }
    to   { opacity:1; transform: scale(1)   translateY(0); }
}
.mc-popup-header {
    background: linear-gradient(135deg, #1a7a4a 0%, #0f5132 100%);
    border-radius: 14px 14px 0 0;
    padding: 14px 20px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-shrink: 0;
}
.mc-popup-title { color: #fff; font-size: .92rem; font-weight: 700; }
.mc-popup-subtitle { color: rgba(255,255,255,.78); font-size: .74rem; margin-top: 3px; }
.mc-popup-close {
    background: none; border: none; color: #fff; opacity: .8;
    font-size: 1.3rem; line-height: 1; cursor: pointer; padding: 0 0 0 12px;
    flex-shrink: 0;
}
.mc-popup-close:hover { opacity: 1; }
.mc-popup-body { padding: 16px 20px 12px; overflow-y: auto; flex: 1; }
.mc-popup-footer {
    background: #f8f8f8;
    border-top: 1px solid #e2e8e4;
    border-radius: 0 0 14px 14px;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    flex-shrink: 0;
}
.mc-search-box {
    position: relative;
    margin-bottom: 12px;
}
.mc-search-box input {
    border-radius: 20px;
    padding-left: 32px;
    font-size: .82rem;
    height: 32px;
    border: 1px solid #c8ddd3;
    background: #f6fbf8;
}
.mc-search-box input:focus { border-color: #4caf50; box-shadow: 0 0 0 2px rgba(76,175,80,.15); outline: none; }
.mc-search-box .fa { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #6aab84; font-size: .75rem; pointer-events: none; }
#tblMC-wrap {
    max-height: 340px;
    overflow-y: auto;
    border-radius: 8px;
    border: 1px solid #ddeee6;
}
#tblMC {
    width: 100%;
    font-size: .82rem;
    margin: 0;
    border-collapse: collapse;
}
#tblMC thead th {
    background: #e8f5e9;
    color: #2e7d32;
    font-size: .71rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: 8px 12px;
    border-bottom: 2px solid #c8e6c9;
    position: sticky;
    top: 0;
    z-index: 1;
}
#tblMC td {
    padding: 6px 12px;
    border-bottom: 1px solid #f0f7f1;
    vertical-align: middle;
}
#tblMC tbody tr:last-child td { border-bottom: none; }
#tblMC tbody tr:hover { background: #f1faf4; }
.mc-pct-badge {
    display: inline-flex;
    align-items: center;
    background: #e8f5e9;
    color: #1b5e20;
    border-radius: 10px;
    padding: 2px 11px;
    font-size: .78rem;
    font-weight: 700;
    min-width: 52px;
    justify-content: center;
}
.mc-pct-input {
    width: 70px;
    height: 28px;
    padding: 2px 6px;
    font-size: .82rem;
    border: 1px solid #a5d6a7;
    border-radius: 6px;
    text-align: center;
    background: #f6fbf8;
    transition: border-color .15s, box-shadow .15s;
}
.mc-pct-input:focus { outline: none; border-color: #4caf50; box-shadow: 0 0 0 2px rgba(76,175,80,.18); }
#mc-no-result { text-align: center; color: #999; padding: 20px; font-size: .8rem; display: none; }
.btn-mc-ver {
    display: inline-flex; align-items: center; gap: 4px;
    background: linear-gradient(135deg, #27ae60 0%, #1a7a4a 100%);
    color: #fff; border: none;
    font-size: .71rem; padding: 3px 10px;
    border-radius: 20px; font-weight: 600; cursor: pointer;
    box-shadow: 0 1px 3px rgba(39,174,96,.3);
    transition: background .15s, box-shadow .15s, transform .1s;
    white-space: nowrap;
}
.btn-mc-ver:hover {
    background: linear-gradient(135deg, #1e8449 0%, #186a3b 100%);
    box-shadow: 0 2px 6px rgba(39,174,96,.4);
    color: #fff;
}
.btn-mc-ver:active { transform: scale(.96); }
.btn-mc-ver-edit {
    display: inline-flex; align-items: center; gap: 4px;
    background: linear-gradient(135deg, #2471a3 0%, #1a5276 100%);
    color: #fff; border: none;
    font-size: .71rem; padding: 3px 10px;
    border-radius: 20px; font-weight: 600; cursor: pointer;
    box-shadow: 0 1px 3px rgba(36,113,163,.3);
    white-space: nowrap;
    transition: background .15s, transform .1s;
}
.btn-mc-ver-edit:hover { background: linear-gradient(135deg, #1a5276 0%, #0f2d4c 100%); color: #fff; }
.btn-mc-ver-edit:active { transform: scale(.96); }
.btn-mc-ver-sin {
    display: inline-flex; align-items: center; gap: 4px;
    background: #f4f4f4; color: #aaa;
    border: 1px dashed #ccc;
    font-size: .71rem; padding: 3px 10px;
    border-radius: 20px; font-weight: 500; cursor: default;
    white-space: nowrap;
}
.btn-mc-aplicar {
    background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
    color: #fff; border: none;
    font-size: .8rem; padding: 6px 18px;
    border-radius: 8px; font-weight: 600; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
    box-shadow: 0 1px 4px rgba(39,174,96,.35);
    transition: background .15s;
}
.btn-mc-aplicar:hover { background: linear-gradient(135deg, #1e8449 0%, #186a3b 100%); color: #fff; }
.btn-mc-cerrar {
    background: #f0f0f0; color: #555;
    border: 1px solid #d0d0d0;
    font-size: .8rem; padding: 6px 18px;
    border-radius: 8px; font-weight: 600; cursor: pointer;
    display: inline-flex; align-items: center; gap: 5px;
    transition: background .15s;
}
.btn-mc-cerrar:hover { background: #e0e0e0; color: #333; }
</style>
@endpush

<div class="cat-card">
    <div class="cat-card-header">
        <h6><i class="fa fa-tags"></i> Categoría de Precios de Producto</h6>
        <button type="button" class="btn btn-pf-primary btn-sm" data-toggle="modal" data-target="#modalCategoriasPrecios"
                style="background:rgba(255,255,255,.15) !important;border:1px solid rgba(255,255,255,.45) !important;box-shadow:none !important;font-size:.72rem;padding:3px 10px;">
            <i class="fa fa-plus mr-1"></i>Nueva categoría
        </button>
    </div>
    <div class="card-body p-2">
        <div class="table-responsive">
            <table id="tbl_listaCategoria" class="table table-sm table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Categoría Cliente</th>
                        <th>Estado</th>
                        <th>Cats. de Precio</th>
                        <th>Creación</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     WIZARD: PLANTILLA E IMPORTACIÓN DE PRECIOS
     Tab 1 → Configurar y descargar plantilla
     Tab 2 → Subir e importar el archivo completado
═══════════════════════════════════════════════════════ --}}
<div class="pf-wizard-card">

    {{-- Header con tabs --}}
    <div class="pf-wizard-header">
        <h6><i class="fa fa-file-excel-o mr-2"></i>Gestión de Plantillas de Precios</h6>
        <div class="pf-wizard-tabs">
            <button class="pf-wizard-tab active" id="tabDescargar" onclick="switchWizardTab('descargar')">
                <span class="tab-num">1</span>
                <i class="fa fa-download mr-1"></i> Descargar Plantilla
            </button>
            <button class="pf-wizard-tab" id="tabImportar" onclick="switchWizardTab('importar')">
                <span class="tab-num">2</span>
                <i class="fa fa-upload mr-1"></i> Importar Plantilla
            </button>
            <button class="pf-wizard-tab" id="tabEditar" onclick="switchWizardTab('editar')">
                <span class="tab-num">3</span>
                <i class="fa fa-pencil-square-o mr-1"></i> Editar precios base
            </button>
        </div>
    </div>

    {{-- Body --}}
    <div class="pf-wizard-body">

        {{-- ─── PASO 1: DESCARGA ─── --}}
        <div class="pf-wizard-pane active" id="paneDescargar">

            <div class="pf-step-label"><i class="fa fa-sliders"></i> Configurar filtros</div>

            <form id="formExport" method="GET" action="{{ route('excel.plantilla') }}" class="d-flex flex-wrap filtro-container">
                {{-- Tipo de plantilla --}}
                <div class="filtro-item">
                    <select id="tipoPlantilla" name="tipoPlantilla" class="form-control select2bs4 filtro-select">
                        <option value="">Seleccionar tipo de plantilla</option>
                        <option value="categoria">Por Categoría</option>
                        <option value="general">Todas las categorías existentes</option>
                    </select>
                </div>

                {{-- Tipo de categoría --}}
                <div class="filtro-item" id="containerTipoCategoria">
                    <select id="tipoCategoria" name="tipoCategoria" class="form-control select2bs4 filtro-select">
                        <option value="">Tipo de categoría</option>
                        <option value="escalable">Escalable</option>
                        <option value="manual">Manual</option>
                    </select>
                </div>

                {{-- Filtrar por --}}
                <div class="filtro-item" id="containerTipoFiltro">
                    <select id="tipoFiltro" name="tipoFiltro" class="form-control select2bs4 filtro-select">
                        <option value="">Filtrar por</option>
                        <option value="1">Marca</option>
                        <option value="2">Categoría de producto</option>
                    </select>
                </div>

                {{-- Lista dinámica --}}
                <div class="filtro-item" id="containerListaFiltro" style="display:none;">
                    <select id="listaTipoFiltro" name="listaTipoFiltro[]" multiple style="display:none;"></select>
                    <button type="button" id="btnAbrirFiltroProductos" class="btn btn-outline-secondary btn-block filtro-select text-left">
                        <i class="fa fa-list mr-1"></i>
                        <span id="resumenFiltroProductos">Seleccionar opciones</span>
                    </button>
                </div>

                {{-- Categoría de cliente --}}
                <div class="filtro-item" id="containerCatCliente" style="display:none;">
                    <select id="catClienteSelect" name="catClienteId" class="form-control select2bs4 filtro-select">
                        <option value="">Categoría de cliente</option>
                        @foreach($categoriasClientes as $cc)
                            <option value="{{ $cc->id }}">{{ $cc->nombre_categoria }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Categoría de precio --}}
                <div class="filtro-item" id="containerCatPrecios" style="display:none;">
                    <select id="listaTipoFiltroCatPrecios" name="listaTipoFiltroCatPrecios" class="form-control select2bs4 filtro-select">
                        <option value="">Categoría de precio</option>
                    </select>
                </div>

                {{-- Botón descargar --}}
                <div style="flex:0 0 auto;align-self:flex-start;">
                    <button type="submit" class="btn btn-pf-primary" id="btnDescargar" disabled>
                        <i class="fa fa-download mr-1"></i> Descargar plantilla
                    </button>
                </div>
            </form>

            {{-- Info dinámica según modo --}}
            <div id="mensajeInfoDescarga" class="alert alert-light border mt-3 mb-0 py-2 px-3" style="display:none; font-size:.8rem;">
                <i class="fa fa-info-circle text-warning mr-1"></i>
                <span id="textoInfoDescarga"></span>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="switchWizardTab('importar')">
                    Ir a Importar <i class="fa fa-arrow-right ml-1"></i>
                </button>
            </div>
        </div>

        {{-- ─── PASO 2: IMPORTACIÓN ─── --}}
        <div class="pf-wizard-pane" id="paneImportar">

            {{-- Zona de subida --}}
            <div class="pf-step-label"><i class="fa fa-upload"></i> Seleccionar archivo</div>

            <form id="formSubirExcel" enctype="multipart/form-data">
                @csrf
                <div class="pf-upload-zone" id="uploadZone">
                    <input type="file" name="archivo_excel" id="archivo_excel" accept=".xlsx">
                    <div class="upload-icon"><i class="fa fa-file-excel-o"></i></div>
                    <div class="upload-text">Arrastrá o hacé click para seleccionar</div>
                    <div class="upload-text" style="font-size:.72rem;color:#bbb;">Solo archivos <strong>.xlsx</strong> · Máx. 20 MB</div>
                    <div class="upload-filename mt-1" id="uploadFilename" style="display:none;"></div>
                </div>

                {{-- Barra de acciones --}}
                <div class="pf-import-bar">
                    <button type="button" id="btnProcesarArchivoPrecios" class="btn btn-pf-primary" disabled>
                        <i class="fa fa-search mr-1"></i> Validar y previsualizar
                    </button>
                    <button type="button" id="btnFinalizarImportPrecios" class="btn btn-success" style="display:none;">
                        <i class="fa fa-check-circle mr-1"></i> Confirmar importación
                    </button>
                    <button type="button" id="btnLimpiarArchivoPrecios" class="btn btn-sm btn-outline-danger" style="display:none;">
                        <i class="fa fa-trash mr-1"></i> Quitar archivo
                    </button>
                    <span id="msgImportPrecios" class="small text-muted ml-auto"></span>
                </div>
            </form>

            {{-- Barra de progreso --}}
            <div class="pf-progress-wrap" id="wrapProgressImport" style="display:none;">
                <div class="progress">
                    <div id="barImportPrecios" class="progress-bar bg-info" role="progressbar" style="width:0%;transition:width .3s;"></div>
                </div>
            </div>

            {{-- Info de modo (viene de los filtros) --}}
            <div id="mensajeInfoImport" class="alert alert-info mt-3 mb-0 py-2 px-3" style="display:none; font-size:.8rem;">
                <i class="fa fa-info-circle mr-1"></i>
                <strong id="tituloInfoImport"></strong>
                <span id="descripcionInfoImport" class="d-block mt-1"></span>
            </div>

            {{-- Preview: productos a actualizar --}}
            <div id="previewActualizablesPrecios" class="mt-4" style="display:none;">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge badge-success px-2 py-1 mr-2" style="font-size:.78rem;">
                        <i class="fa fa-check-circle mr-1"></i> <span id="countActualizablesPrecios">0</span> productos
                    </span>
                    <strong style="font-size:.82rem;">Se actualizarán con los nuevos precios</strong>
                </div>
                <div class="table-responsive" style="max-height:320px;overflow-y:auto;border:1px solid #f0e8dd;border-radius:8px;">
                    <table class="table table-sm table-hover mb-0">
                        <thead style="background:#f8f0e6;position:sticky;top:0;z-index:1;">
                            <tr>
                                <th style="font-size:.73rem;color:#7d4600;font-weight:700;padding:7px 10px;">Código</th>
                                <th style="font-size:.73rem;color:#7d4600;font-weight:700;padding:7px 10px;">Descripción</th>
                                <th style="font-size:.73rem;color:#7d4600;font-weight:700;padding:7px 10px;">Categoría</th>
                                <th style="font-size:.73rem;color:#7d4600;font-weight:700;padding:7px 10px;">Base</th>
                                <th style="font-size:.73rem;color:#7d4600;font-weight:700;padding:7px 10px;">A</th>
                                <th style="font-size:.73rem;color:#7d4600;font-weight:700;padding:7px 10px;">B</th>
                                <th style="font-size:.73rem;color:#7d4600;font-weight:700;padding:7px 10px;">C</th>
                                <th style="font-size:.73rem;color:#7d4600;font-weight:700;padding:7px 10px;">D</th>
                            </tr>
                        </thead>
                        <tbody id="tablaActualizablesPrecios"></tbody>
                    </table>
                </div>
            </div>

            {{-- Preview: productos omitidos --}}
            <div id="previewNoActualizablesPrecios" class="mt-3" style="display:none;">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge badge-warning px-2 py-1 mr-2" style="font-size:.78rem;">
                        <i class="fa fa-exclamation-triangle mr-1"></i> <span id="countNoActualizablesPrecios">0</span> omitidos
                    </span>
                    <strong style="font-size:.82rem;">No se procesarán</strong>
                </div>
                <div class="table-responsive" style="max-height:240px;overflow-y:auto;border:1px solid #f0e8dd;border-radius:8px;">
                    <table class="table table-sm table-hover mb-0">
                        <thead style="background:#fefae8;position:sticky;top:0;z-index:1;">
                            <tr>
                                <th style="font-size:.73rem;color:#7d6000;font-weight:700;padding:7px 10px;">Fila</th>
                                <th style="font-size:.73rem;color:#7d6000;font-weight:700;padding:7px 10px;">Código</th>
                                <th style="font-size:.73rem;color:#7d6000;font-weight:700;padding:7px 10px;">Descripción</th>
                                <th style="font-size:.73rem;color:#7d6000;font-weight:700;padding:7px 10px;">Motivo</th>
                            </tr>
                        </thead>
                        <tbody id="tablaNoActualizablesPrecios"></tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-start mt-3">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="switchWizardTab('descargar')">
                    <i class="fa fa-arrow-left mr-1"></i> Volver a Filtros
                </button>
            </div>
        </div>

{{-- ═══════════════════════════════════════════════════════
     EDITOR MANUAL DE PRECIOS BASE
═══════════════════════════════════════════════════════ --}}
<div class="pf-wizard-pane" id="paneEditar">
<div id="cardEditorPrecios">
    <div class="pf-step-label">
        <i class="fa fa-pencil-square-o mr-1"></i> Editar precios base manualmente
    </div>
    <div>

        {{-- PASO 1: Categoría de cliente --}}
        <div class="pf-step-label"><i class="fa fa-users"></i> Paso 1 — Categoría de cliente</div>
        <div class="mb-3">
            <select id="editorCatClienteSel" class="form-control select2bs4" style="font-size:.82rem;max-width:340px;">
                <option value="">— Seleccione categoría de cliente —</option>
                @foreach($categoriasClientes as $cc)
                    <option value="{{ $cc->id }}">{{ $cc->nombre_categoria }}</option>
                @endforeach
            </select>
        </div>

        {{-- PASO 2: Categoría de precio (se carga vía AJAX) --}}
        <div id="editorStep2" style="display:none;">
            <div class="pf-step-label"><i class="fa fa-tags"></i> Paso 2 — Categoría de precio</div>
            <div class="mb-3 d-flex flex-wrap align-items-center" style="gap:.5rem;">
                <div style="min-width:220px;flex:0 0 220px;">
                    <select id="editorCatPrecioSel" class="form-control select2bs4" style="font-size:.82rem;">
                        <option value="">— Seleccione —</option>
                    </select>
                </div>
                <div id="editorStep2Spinner" style="display:none;">
                    <div class="spinner-border spinner-border-sm text-warning" role="status"></div>
                    <span class="text-muted small ml-1">Cargando...</span>
                </div>
            </div>
        </div>

        {{-- PASO 3: Filtro + tabla --}}
        <div id="editorStep3" style="display:none;">
            <div class="pf-step-label"><i class="fa fa-list"></i> Paso 3 — Productos</div>

            {{-- Badge de porcentajes --}}
            <div id="editorPorcBadges" class="mb-2" style="font-size:.78rem;">
                <span class="badge badge-light border mr-1 py-1 px-2">% A: <strong id="editorPorcA" class="text-primary">0</strong></span>
                <span class="badge badge-light border mr-1 py-1 px-2">% B: <strong id="editorPorcB" class="text-success">0</strong></span>
                <span class="badge badge-light border mr-1 py-1 px-2">% C: <strong id="editorPorcC" style="color:#c0392b;">0</strong></span>
                <span class="badge badge-light border py-1 px-2">% D: <strong id="editorPorcD" style="color:#7d3c98;">0</strong></span>
                <span class="text-muted ml-2" style="font-size:.72rem;">Los precios A/B/C/D se recalculan al editar el Base.</span>
            </div>

            {{-- Buscador --}}
            <div class="d-flex flex-wrap align-items-center mb-3" style="gap:.5rem;">
                <div style="flex:1 1 220px;position:relative;">
                    <input type="text" id="editorBuscarProd" class="form-control"
                        placeholder="Buscar por nombre o código..."
                        style="font-size:.82rem;padding-left:30px;">
                    <i class="fa fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#bbb;font-size:.75rem;pointer-events:none;"></i>
                </div>
                <button type="button" class="btn btn-pf-primary btn-sm"
                        style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%) !important;color:#fff !important;border:none;height:35px;padding:0 16px;font-size:.8rem;"
                        onclick="cargarEditorPrecios(1)">
                    <i class="fa fa-search mr-1"></i> Buscar
                </button>
                <button type="button" class="btn btn-sm btn-success" onclick="abrirAgregarProductoPrecio()" style="height:35px;font-size:.8rem;">
                    <i class="fa fa-plus mr-1"></i> Agregar producto
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="descargarProductosEditor()" style="height:35px;font-size:.8rem;">
                    <i class="fa fa-file-excel-o mr-1"></i> Descargar Excel
                </button>
            </div>
        </div>

        {{-- Spinner tabla --}}
        <div id="editorSpinner" class="text-center py-4" style="display:none;">
            <div class="spinner-border text-warning" style="width:2rem;height:2rem;" role="status"><span class="sr-only">Cargando...</span></div>
            <p class="mt-2 text-muted small">Cargando productos...</p>
        </div>

        {{-- Tabla --}}
        <div id="wrapEditorTabla" style="display:none;">
            <div class="table-responsive" style="max-height:480px;overflow-y:auto;border:1px solid #f0e8dd;border-radius:8px;">
                <table class="table table-sm table-hover mb-0" id="tbl_editor_precios">
                    <thead style="position:sticky;top:0;z-index:5;background:#f8f0e6;">
                        <tr>
                            <th style="font-size:.72rem;color:#7d4600;padding:7px 8px;white-space:nowrap;">Cód.</th>
                            <th style="font-size:.72rem;color:#7d4600;padding:7px 8px;">Descripción</th>
                            <th style="font-size:.72rem;color:#7d4600;padding:7px 8px;white-space:nowrap;">Precio Base</th>
                            <th style="font-size:.72rem;color:#2471a3;padding:7px 8px;text-align:right;white-space:nowrap;">A (<span class="hdr-porc-a">0</span>%)</th>
                            <th style="font-size:.72rem;color:#1e8449;padding:7px 8px;text-align:right;white-space:nowrap;">B (<span class="hdr-porc-b">0</span>%)</th>
                            <th style="font-size:.72rem;color:#c0392b;padding:7px 8px;text-align:right;white-space:nowrap;">C (<span class="hdr-porc-c">0</span>%)</th>
                            <th style="font-size:.72rem;color:#7d3c98;padding:7px 8px;text-align:right;white-space:nowrap;">D (<span class="hdr-porc-d">0</span>%)</th>
                            <th style="font-size:.72rem;color:#7d4600;padding:7px 8px;text-align:center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbody_editor_precios"></tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap" style="gap:.4rem;">
                <span id="editorPaginInfo" class="text-muted" style="font-size:.75rem;"></span>
                <div class="d-flex align-items-center" style="gap:.3rem;">
                    <button type="button" id="btnEditorPrev" class="btn btn-sm btn-outline-secondary"
                            style="font-size:.75rem;padding:3px 10px;" onclick="cambiarPaginaEditor(-1)" disabled>
                        <i class="fa fa-chevron-left"></i> Anterior
                    </button>
                    <span id="editorPaginPages" class="text-muted" style="font-size:.75rem;"></span>
                    <button type="button" id="btnEditorNext" class="btn btn-sm btn-outline-secondary"
                            style="font-size:.75rem;padding:3px 10px;" onclick="cambiarPaginaEditor(1)" disabled>
                        Siguiente <i class="fa fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Estado vacío --}}
        <div id="editorEmpty" class="text-center text-muted py-4 small" style="display:none;">
            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
            No hay productos para esta categoría o búsqueda.
        </div>
        <div id="editorPlaceholder" class="text-center text-muted py-4 small">
            <i class="fa fa-arrow-up fa-lg mb-2 d-block" style="color:#e0cfc0;"></i>
            Seleccione una categoría de cliente para comenzar.
        </div>

    </div>
</div>
</div>

    </div>{{-- /pf-wizard-body --}}
</div>{{-- /pf-wizard-card --}}

<div class="modal fade" id="modalAgregarProductoPrecio" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:620px;">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--pf-grad);color:#fff;">
                <h6 class="modal-title mb-0"><i class="fa fa-plus-circle mr-1"></i>Agregar producto a la categoría de precio</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="editorAgregarSeleccion" class="alert alert-light border mb-3" style="font-size:.8rem;"></div>
                <div class="form-group mb-0">
                    <label for="editorAgregarBase" class="font-weight-bold small">Precio base</label>
                    <input type="number" id="editorAgregarBase" min="0.01" step="0.01" class="form-control" placeholder="0.00">
                    <div id="editorUltimoPrecioBase" class="mt-2 px-2 py-1 border rounded" style="display:none;background:#f8fafc;color:#52616b;font-size:.76rem;"></div>
                    <small class="text-muted">Los precios A, B, C y D se calcularán con los porcentajes de la categoría seleccionada.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarAgregarPrecio" class="btn btn-success" onclick="confirmarAgregarProductoPrecio()" disabled>
                    <i class="fa fa-plus mr-1"></i>Agregar producto
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHistorialPrecioProducto" tabindex="-1" role="dialog" aria-labelledby="tituloHistorialPrecioProducto" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--pf-grad);color:#fff;">
                <div>
                    <small style="color:rgba(255,255,255,.82);">Histórico de precios base y escalas</small>
                    <h6 class="modal-title mb-0" id="tituloHistorialPrecioProducto">Producto</h6>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="historialPrecioResumen" class="mb-3"></div>
                <div id="historialPrecioCargando" class="text-center py-5">
                    <div class="spinner-border text-warning" role="status"><span class="sr-only">Cargando...</span></div>
                    <div class="mt-2 text-muted small">Cargando histórico...</div>
                </div>
                <div id="historialPrecioContenido" class="table-responsive" style="display:none;">
                    <table class="table table-sm table-hover mb-0" id="tablaHistorialPrecioProducto">
                        <thead>
                            <tr>
                                <th>Versión</th>
                                <th>Fecha de registro</th>
                                <th>Última actualización</th>
                                <th class="text-right">Precio Base</th>
                                <th class="text-right historial-col-a">A</th>
                                <th class="text-right historial-col-b">B</th>
                                <th class="text-right historial-col-c">C</th>
                                <th class="text-right historial-col-d">D</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div id="historialPrecioError" class="alert alert-danger mb-0" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <small class="text-muted mr-auto">El porcentaje efectivo se calcula a partir del precio base y el valor guardado en cada versión.</small>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<x-buscador-producto
    id-modal="buscadorProductoPrecio"
    callback="seleccionarProductoParaPrecio"
    extra-params-callback="parametrosBuscadorProductoPrecio"
    :con-stock-default="false"
    :use-top-preview="false"
/>

<div class="modal fade" id="modalSeleccionFiltrosProductos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:620px;">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--pf-grad);color:#fff;">
                <h6 class="modal-title mb-0" id="tituloModalFiltrosProductos">Seleccionar opciones</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="input-group input-group-sm mb-2">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-search"></i></span></div>
                    <input type="text" id="buscarFiltroProductos" class="form-control" placeholder="Buscar...">
                </div>
                <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" class="custom-control-input" id="seleccionarTodosFiltrosProductos">
                    <label class="custom-control-label font-weight-bold" for="seleccionarTodosFiltrosProductos">Seleccionar todas</label>
                </div>
                <div id="listaModalFiltrosProductos" class="border rounded p-2" style="max-height:360px;overflow-y:auto;"></div>
                <div id="cargandoModalFiltrosProductos" class="text-center text-muted py-4" style="display:none;">
                    <i class="fa fa-spinner fa-spin mr-1"></i> Cargando opciones...
                </div>
                <div class="mt-3">
                    <div class="small font-weight-bold text-muted mb-1">Seleccionadas</div>
                    <div id="chipsFiltrosProductos" class="d-flex flex-wrap" style="gap:5px;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <span id="conteoFiltrosProductos" class="text-muted small mr-auto">0 seleccionadas</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-pf-primary" id="btnConfirmarFiltrosProductos">
                    <i class="fa fa-check mr-1"></i> Aplicar selección
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Editor de precios ── */
.editor-base-input {
    width: 100px !important;
    text-align: right !important;
    font-size: .82rem !important;
    padding: 3px 6px !important;
    height: 28px !important;
    border-radius: 5px !important;
    border: 1px solid #d8cfc7 !important;
    background: #fdfaf7 !important;
    transition: border-color .15s, box-shadow .15s !important;
}
.editor-base-input:focus {
    border-color: #e67e22 !important;
    box-shadow: 0 0 0 2px rgba(243,156,18,.18) !important;
    outline: none !important;
    background: #fff !important;
}
.editor-base-input.is-modified {
    border-color: #e67e22 !important;
    background: #fffaf0 !important;
}
.btn-editor-save {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
    color: #fff;
    border: none;
    font-size: .72rem;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(39,174,96,.3);
    transition: background .15s, box-shadow .15s, transform .1s;
    white-space: nowrap;
}
.btn-editor-save:hover {
    background: linear-gradient(135deg, #1e8449 0%, #186a3b 100%);
    box-shadow: 0 2px 6px rgba(39,174,96,.4);
}
.btn-editor-save:active { transform: scale(.96); }
.btn-editor-save:disabled { opacity: .6; cursor: not-allowed; }
#tbl_editor_precios tbody tr.row-saved { background: #eafaf1 !important; transition: background .5s; }
#tbl_editor_precios td { vertical-align: middle; padding: 5px 8px; font-size: .82rem; }
.editor-product-history {
    appearance: none;
    border: 0;
    background: transparent;
    color: #1f5f8b;
    cursor: pointer;
    font: inherit;
    font-weight: 600;
    padding: 0;
    text-align: left;
}
.editor-product-history:hover { color: #d35400; text-decoration: underline; }
.editor-product-history:focus { outline: 2px solid rgba(230,126,34,.35); outline-offset: 2px; }
#modalHistorialPrecioProducto { z-index: 4010 !important; }
#modalHistorialPrecioProducto .modal-dialog {
    max-width: 1180px;
    width: calc(100vw - 40px);
}
#modalHistorialPrecioProducto .modal-body { max-height: 72vh; overflow-y: auto; }
#tablaHistorialPrecioProducto { min-width: 980px; }
#tablaHistorialPrecioProducto thead th {
    background: #f8f0e6;
    border-bottom: 2px solid #edc896;
    color: #6f4309;
    font-size: .72rem;
    padding: 8px;
    position: sticky;
    top: 0;
    white-space: nowrap;
    z-index: 2;
}
#tablaHistorialPrecioProducto td { font-size: .78rem; padding: 8px; vertical-align: middle; }
#tablaHistorialPrecioProducto .historial-precio-valor { display: block; font-size: .88rem; font-weight: 700; white-space: nowrap; }
#tablaHistorialPrecioProducto .historial-precio-porc { display: block; font-size: .68rem; white-space: nowrap; }
#tablaHistorialPrecioProducto .historial-col-a { color: #2471a3; }
#tablaHistorialPrecioProducto .historial-col-b { color: #1e8449; }
#tablaHistorialPrecioProducto .historial-col-c { color: #c0392b; }
#tablaHistorialPrecioProducto .historial-col-d { color: #7d3c98; }
@media (max-width: 767px) {
    #modalHistorialPrecioProducto .modal-dialog { margin: 8px; width: calc(100vw - 16px); }
}
</style>


<div class="modal fade" id="modalSeleccionarCategoriasGeneral" tabindex="-1" role="dialog"
     aria-labelledby="modalSelCatTitle" aria-hidden="true"
     data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="border-0 rounded shadow-lg modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#f39c12 0%,#f0a500 100%);border-bottom:none;padding:12px 20px;">
        <h5 class="modal-title font-weight-bold" id="modalSelCatTitle" style="color:#fff;">
          <i class="fa fa-filter mr-2"></i> Categorías a Actualizar — Modo General
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="color:rgba(255,255,255,.8);text-shadow:none;opacity:1;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body px-4 py-3">
        <div class="alert alert-warning mb-3">
          <i class="fa fa-exclamation-triangle mr-1"></i>
          <strong>Modo General:</strong> Se actualizarán <strong>todas</strong> las categorías de precios activas.
          Desmarca las categorías que <strong>NO</strong> deseas actualizar.
        </div>
        <div id="loadingCategoriasModal" class="text-center py-4">
          <div class="spinner-border text-warning" role="status">
            <span class="sr-only">Cargando...</span>
          </div>
          <p class="mt-2 text-muted">Cargando categorías...</p>
        </div>
        <div id="listaCategoriasModal" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 font-weight-bold">Categorías de precios activas:</h6>
            <div>
              <button type="button" class="btn btn-sm btn-outline-success mr-1" id="btnSeleccionarTodasCat">
                <i class="fa fa-check-double"></i> Seleccionar todas
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger" id="btnDeseleccionarTodasCat">
                <i class="fa fa-times"></i> Deseleccionar todas
              </button>
            </div>
          </div>
          <div id="checkboxCategoriasContainer" class="row px-2"></div>
        </div>
        <div id="errorCargaCategoriasModal" class="alert alert-danger mb-0" style="display:none;">
          <i class="fa fa-exclamation-circle mr-1"></i> No se pudieron cargar las categorías. Intente nuevamente.
        </div>
      </div>
      <div class="modal-footer border-0 bg-light">
        <span class="mr-auto small text-muted">
          <span id="contadorCatSeleccionadas">0</span> categoría(s) seleccionada(s) para actualizar
        </span>
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="btnCancelarSelCat">
          <i class="fa fa-times mr-1"></i>Cancelar
        </button>
        <button type="button" class="btn btn-pf-primary font-weight-bold" id="btnConfirmarProcesarGeneral" disabled
                style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%) !important;color:#fff !important;border:none;">
          <i class="fa fa-check-circle mr-1"></i> Confirmar y Procesar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Overlay de carga para procesamiento -->
<div id="overlayProcesandoPrecios">
    <div class="overlay-content">
        <div class="spinner-border text-primary" role="status" style="width:3rem; height:3rem; margin-bottom:20px;">
            <span class="sr-only">Cargando...</span>
        </div>
        <h5 class="mb-2"><strong id="tituloOverlayPrecios">Procesando archivo...</strong></h5>
        <p class="text-muted mb-0" id="mensajeOverlayPrecios">Por favor espere mientras se validan los datos</p>
    </div>
</div>




<!-- MODAL CATEGORÍA DE PRECIOS -->
<div class="modal fade" id="modalCategoriasPrecios" tabindex="-1" role="dialog"
     aria-labelledby="modalCategoriasPreciosTitle" aria-hidden="true"
     data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" style="max-width:640px;" role="document">
    <div class="modal-content">

      <!-- Header -->
      <div class="modal-header d-flex align-items-start justify-content-between">
        <div class="modal-header-inner">
          <h5 class="modal-title mb-0" id="modalCategoriasPreciosTitle">
            <i class="fa fa-tags mr-2" style="opacity:.9;"></i>Nueva Categoría de Precios
          </h5>
          <p class="modal-subtitle">Complete los campos requeridos para registrar la categoría</p>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <p class="pf-required-hint"><span class="text-danger font-weight-bold">*</span> Indica campo obligatorio</p>

        <form id="CreacionCatPrecios" autocomplete="off">

          <!-- Sección: Identificación -->
          <div class="pf-form-section">
            <i class="fa fa-id-card-o"></i> Identificación
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="nombre_cat_precio">Nombre de la Categoría <span class="text-danger">*</span></label>
              <input type="text" class="form-control"
                id="nombre_cat_precio" name="nombre_cat_precio"
                placeholder="Ej: Precios Cliente Estatal" maxlength="100" required>
            </div>
            <div class="form-group col-md-6">
              <label for="categoria_cliente_id">Categoría de Cliente <span class="text-danger">*</span></label>
              <select id="categoria_cliente_id"
                      name="categoria_cliente_id"
                      class="form-control pf-select2-modal"
                      data-url="{{ route('clientes.categorias.escala') }}"
                      required>
                <option value="">Buscar categoría...</option>
              </select>
            </div>
          </div>

          <!-- Sección: Porcentajes de Precio -->
          <div class="pf-form-section">
            <i class="fa fa-percent"></i> Porcentajes de Precio
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="porc_precio_a">% Precio Venta A <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="number" class="form-control" id="porc_precio_a" name="porc_precio_a"
                  placeholder="Ej: 5" min="0" max="100" step="0.01" inputmode="decimal" required>
                <div class="input-group-append"><span class="input-group-text">%</span></div>
              </div>
            </div>
            <div class="form-group col-md-6">
              <label for="porc_precio_b">% Precio Venta B <small class="text-muted font-weight-normal">(opcional)</small></label>
              <div class="input-group">
                <input type="number" class="form-control" id="porc_precio_b" name="porc_precio_b"
                  placeholder="Ej: 15" min="0" max="100" step="0.01" inputmode="decimal">
                <div class="input-group-append"><span class="input-group-text">%</span></div>
              </div>
            </div>
            <div class="form-group col-md-6">
              <label for="porc_precio_c">% Precio Venta C <small class="text-muted font-weight-normal">(opcional)</small></label>
              <div class="input-group">
                <input type="number" class="form-control" id="porc_precio_c" name="porc_precio_c"
                  placeholder="Ej: 20" min="0" max="100" step="0.01" inputmode="decimal">
                <div class="input-group-append"><span class="input-group-text">%</span></div>
              </div>
            </div>
            <div class="form-group col-md-6">
              <label for="porc_precio_d">% Precio Venta D <small class="text-muted font-weight-normal">(opcional)</small></label>
              <div class="input-group">
                <input type="number" class="form-control" id="porc_precio_d" name="porc_precio_d"
                  placeholder="Ej: 30" min="0" max="100" step="0.01" inputmode="decimal">
                <div class="input-group-append"><span class="input-group-text">%</span></div>
              </div>
            </div>
          </div>

          <!-- Sección: Notas -->
          <div class="pf-form-section">
            <i class="fa fa-comment-o"></i> Notas adicionales
          </div>

          <div class="form-group">
            <label for="comentario_cat_precio">Comentario <small class="text-muted font-weight-normal">(opcional)</small></label>
            <textarea id="comentario_cat_precio" name="comentario_cat_precio" class="form-control" rows="2"
              placeholder="Ej: Categoría para clientes institucionales del sector público"></textarea>
          </div>

        </form>
      </div>

      <!-- Footer -->
      <div class="modal-footer d-flex justify-content-between align-items-center">
        <small class="text-muted"><i class="fa fa-lock mr-1"></i>Los datos se guardan de forma segura</small>
        <div>
          <button type="button" class="btn btn-outline-secondary btn-sm mr-2" data-dismiss="modal" id="btnCancelarCategoria">
            <i class="fa fa-times mr-1"></i>Cancelar
          </button>
          <button type="submit" form="CreacionCatPrecios" class="btn btn-pf-primary" id="btn_guardar_categoria"
                  style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%) !important;color:#fff !important;border:none;font-size:.85rem;padding:6px 18px;">
            <i class="fa fa-save mr-1"></i>Guardar Categoría
          </button>
        </div>
      </div>

    </div>
  </div>
</div>



<!-- MODAL: VER / EDITAR CATEGORÍAS DE PRECIO DE UNA CATEGORÍA CLIENTE -->
<div class="modal fade" id="modalVerCatPrecios" tabindex="-1" role="dialog"
     aria-labelledby="titleVerCatPrecios" aria-hidden="true"
     data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header d-flex align-items-start justify-content-between">
        <div>
          <h5 class="modal-title mb-0" id="titleVerCatPrecios"
              style="color:#fff;font-weight:700;font-size:1rem;">
            <i class="fa fa-list mr-2" style="opacity:.9;"></i>Categorías de Precio
          </h5>
          <p id="subtitleVerCatPrecios"
             style="color:rgba(255,255,255,.78);font-size:.75rem;margin:3px 0 0;"></p>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"
                style="color:rgba(255,255,255,.85);text-shadow:none;opacity:1;font-size:1.5rem;padding:0;margin:0;align-self:flex-start;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body p-0">
        <!-- Spinner de carga -->
        <div id="loadingVerCatPrecios" class="text-center py-5">
          <div class="spinner-border text-warning" role="status"><span class="sr-only">Cargando...</span></div>
          <p class="mt-2 text-muted small">Cargando categorías de precio...</p>
        </div>
        <!-- Tabla -->
        <div id="wrapperVerCatPrecios" style="display:none;">
          <div class="table-responsive" style="-webkit-overflow-scrolling:touch;">
            <table class="table table-sm table-bordered table-hover mb-0" id="tbl_catPrecios_lista">
              <thead>
                <tr>
                  <th class="col-hide-xs">ID</th>
                  <th>Nombre</th>
                  <th class="text-center">% A</th>
                  <th class="text-center col-hide-xs">% B</th>
                  <th class="text-center col-hide-xs">% C</th>
                  <th class="text-center col-hide-xs">% D</th>
                  <th class="text-center">% Comisión</th>
                  <th class="text-center">Estado</th>
                  <th class="text-center col-hide-sm">Últ. actualización</th>
                  <th class="text-center col-hide-sm">Actualizado por</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody id="tbody_catPrecios_lista"></tbody>
            </table>
          </div>
          <div id="emptyCatPrecios" class="text-center text-muted py-4 small" style="display:none;">
            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
            No hay categorías de precio registradas para esta categoría.
          </div>
        </div>
      </div>

      <div class="modal-footer justify-content-between">
        <button type="button" id="btnExportarPreciosCat"
                class="btn btn-sm font-weight-600"
                style="background:linear-gradient(135deg,#27ae60 0%,#1e8449 100%);color:#fff;border:none;border-radius:7px;padding:6px 16px;font-size:.78rem;box-shadow:0 1px 4px rgba(39,174,96,.3);transition:box-shadow .15s,transform .1s;"
                onmouseover="this.style.boxShadow='0 3px 8px rgba(39,174,96,.45)'"
                onmouseout="this.style.boxShadow='0 1px 4px rgba(39,174,96,.3)'"
                onclick="descargarPreciosPorCliente()">
          <i class="fa fa-file-excel-o mr-1"></i> Exportar precios a Excel
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal"
                style="border-radius:7px;font-size:.78rem;padding:6px 16px;">
          <i class="fa fa-times mr-1"></i>Cerrar
        </button>
      </div>

    </div>
  </div>
</div>



<!-- OVERLAY CUSTOM: % COMISIONES POR ROL -->
<div id="mcOverlay" onclick="cerrarMCIfBg(event)">
  <div class="mc-popup">

    <div class="mc-popup-header">
      <div>
        <div class="mc-popup-title"><i class="fa fa-percent mr-1"></i> % Comisión por Rol</div>
        <div class="mc-popup-subtitle" id="mc-modal-subtitle"></div>
      </div>
      <button class="mc-popup-close" onclick="cerrarModalComisiones()" aria-label="Cerrar">&times;</button>
    </div>

    <div class="mc-popup-body">
      <div class="mc-search-box">
        <i class="fa fa-search"></i>
        <input type="text" id="mc-buscador" class="form-control"
               placeholder="Buscar rol..." autocomplete="off"
               oninput="filtrarModalComisiones(this.value)">
      </div>
      <div id="tblMC-wrap">
        <table id="tblMC">
          <thead>
            <tr>
              <th style="width:58%">Rol</th>
              <th class="text-center" style="width:42%">% Comisión</th>
            </tr>
          </thead>
          <tbody id="mc-tbody"></tbody>
        </table>
        <div id="mc-no-result">Sin resultados para la búsqueda.</div>
      </div>
    </div>

    <div class="mc-popup-footer">
      <button class="btn-mc-cerrar" onclick="cerrarModalComisiones()">
        <i class="fa fa-times mr-1"></i>Cerrar
      </button>
      <div style="display:flex;gap:8px;">
        <button class="btn-mc-ver-edit" id="mc-btn-editar" onclick="activarEdicionMC()">
          <i class="fa fa-pencil"></i> Editar
        </button>
        <button class="btn-mc-aplicar" id="mc-btn-aplicar" onclick="aplicarComisionesModal()" style="display:none;">
          <i class="fa fa-check mr-1"></i>Aplicar cambios
        </button>
      </div>
    </div>

  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <script src="{{ asset('js/js_proyecto/Escalas/gestionPrecios.js') }}"></script>

    <!-- Script para carga masiva de productos con preview -->
    <script>
    $(document).ready(function() {
        const fileInputPrecios = $('#archivo_excel');
        const btnLimpiarPrecios = $('#btnLimpiarArchivoPrecios');
        const btnProcesarPrecios = $('#btnProcesarArchivoPrecios');
        const btnFinalizarPrecios = $('#btnFinalizarImportPrecios');
        const barProgressPrecios = $('#barImportPrecios');
        const msgImportPrecios = $('#msgImportPrecios');
        const formSubirExcel = $('#formSubirExcel');
        let opcionesFiltroProductos = [];
        let seleccionTemporalFiltros = new Set();

        function renderizarChipsFiltros() {
            const chips = Array.from(seleccionTemporalFiltros).map(function(id) {
                const item = opcionesFiltroProductos.find(function(opcion) { return String(opcion.id) === String(id); });
                if (!item) return '';
                return '<span class="badge badge-light border py-1 px-2" style="font-size:.75rem;">' +
                    $('<div>').text(item.nombre).html() +
                    ' <button type="button" class="btn-quitar-chip border-0 bg-transparent p-0 ml-1 text-danger" data-id="' + id + '" aria-label="Quitar">&times;</button>' +
                '</span>';
            }).join('');
            $('#chipsFiltrosProductos').html(chips || '<span class="text-muted small">Ninguna selección</span>');
        }

        function actualizarConteoFiltros() {
            const cantidad = seleccionTemporalFiltros.size;
            $('#conteoFiltrosProductos').text(cantidad + (cantidad === 1 ? ' seleccionada' : ' seleccionadas'));
            renderizarChipsFiltros();
        }

        function renderizarFiltrosProductos() {
            const termino = $('#buscarFiltroProductos').val().trim().toLowerCase();
            const visibles = opcionesFiltroProductos.filter(function(item) {
                return !termino || item.nombre.toLowerCase().indexOf(termino) !== -1;
            });
            const html = visibles.map(function(item) {
                const id = String(item.id);
                const checked = seleccionTemporalFiltros.has(id) ? ' checked' : '';
                return '<div class="custom-control custom-checkbox py-1">' +
                    '<input type="checkbox" class="custom-control-input filtro-producto-check" id="filtro-producto-' + id + '" value="' + id + '"' + checked + '>' +
                    '<label class="custom-control-label" for="filtro-producto-' + id + '">' + $('<div>').text(item.nombre).html() + '</label>' +
                '</div>';
            }).join('');
            $('#listaModalFiltrosProductos').html(html || '<div class="text-muted text-center py-3">Sin resultados</div>');
            actualizarConteoFiltros();
        }

        function abrirModalFiltrosProductos() {
            const tipo = $('#tipoFiltro').val();
            if (!tipo) return;
            const esMarca = tipo === '1';
            $('#tituloModalFiltrosProductos').text(esMarca ? 'Seleccionar marcas' : 'Seleccionar categorías de producto');
            $('#buscarFiltroProductos').val('');
            $('#seleccionarTodosFiltrosProductos').prop('checked', false);
            seleccionTemporalFiltros = new Set(($('#listaTipoFiltro').val() || []).map(String));
            $('#listaModalFiltrosProductos').empty();
            $('#cargandoModalFiltrosProductos').show();
            $('#modalSeleccionFiltrosProductos').modal('show');

            $.getJSON(esMarca ? '/filtros/marca' : '/filtros/categoria')
                .done(function(data) {
                    opcionesFiltroProductos = data || [];
                    renderizarFiltrosProductos();
                })
                .fail(function() {
                    $('#listaModalFiltrosProductos').html('<div class="text-danger text-center py-3">No se pudieron cargar las opciones.</div>');
                })
                .always(function() { $('#cargandoModalFiltrosProductos').hide(); });
        }

        $('#btnAbrirFiltroProductos').on('click', abrirModalFiltrosProductos);
        $('#buscarFiltroProductos').on('input', renderizarFiltrosProductos);
        $('#listaModalFiltrosProductos').on('change', '.filtro-producto-check', function() {
            if (this.checked) seleccionTemporalFiltros.add(String(this.value));
            else seleccionTemporalFiltros.delete(String(this.value));
            actualizarConteoFiltros();
        });
        $('#chipsFiltrosProductos').on('click', '.btn-quitar-chip', function() {
            seleccionTemporalFiltros.delete(String($(this).data('id')));
            renderizarFiltrosProductos();
        });
        $('#seleccionarTodosFiltrosProductos').on('change', function() {
            $('#listaModalFiltrosProductos .filtro-producto-check').each(function() {
                this.checked = $('#seleccionarTodosFiltrosProductos').prop('checked');
                if (this.checked) seleccionTemporalFiltros.add(String(this.value));
                else seleccionTemporalFiltros.delete(String(this.value));
            });
            actualizarConteoFiltros();
        });
        $('#btnConfirmarFiltrosProductos').on('click', function() {
            const seleccionados = Array.from(seleccionTemporalFiltros);
            if (!seleccionados.length) {
                Swal.fire({ icon: 'warning', title: 'Selección requerida', text: 'Seleccione al menos una opción.' });
                return;
            }
            const $lista = $('#listaTipoFiltro').empty();
            seleccionados.forEach(function(id) {
                const item = opcionesFiltroProductos.find(function(opcion) { return String(opcion.id) === String(id); });
                if (item) $lista.append(new Option(item.nombre, item.id, true, true));
            });
            $('#resumenFiltroProductos').text(seleccionados.length + (seleccionados.length === 1 ? ' opción seleccionada' : ' opciones seleccionadas'));
            $lista.trigger('change');
            $('#modalSeleccionFiltrosProductos').modal('hide');
        });

        // Gestión dinámica de filtros según tipo de plantilla
        $('#tipoPlantilla').on('change', function() {
            const tipoPlantilla = $(this).val();

            // Resetear todos los filtros
            $('#tipoCategoria').val('').trigger('change');
            $('#tipoFiltro').val('').trigger('change');
            $('#listaTipoFiltro').val('').trigger('change');
            $('#listaTipoFiltroCatPrecios').val('').trigger('change');
            $('#btnDescargar').prop('disabled', true);

            // Ocultar todos los contenedores
            $('#containerTipoCategoria').hide();
            $('#containerTipoFiltro').hide();
            $('#containerListaFiltro').hide();
            $('#containerCatCliente').hide();
            $('#catClienteSelect').val(null).trigger('change');
            $('#containerCatPrecios').hide();
            $('#containerTipoCategoria, #containerTipoFiltro').show();

            // Limpiar archivo y mensajes
            fileInputPrecios.val('');
            btnLimpiarPrecios.hide();
            btnFinalizarPrecios.hide();
            btnProcesarPrecios.show();
            $('#previewActualizablesPrecios').hide();
            $('#previewNoActualizablesPrecios').hide();
            barProgressPrecios.css('width', '0%');
            msgImportPrecios.text('');

            // Actualizar mensaje informativo
            if (tipoPlantilla === 'categoria') {
                // Modo Categoría: mostrar todos los filtros
                $('#containerTipoCategoria').show();
                $('#tituloInfoImport').text('Modo: Por Categoría');
                $('#descripcionInfoImport').html('Los precios se actualizarán <strong>solo para la categoría de precios seleccionada</strong>. El archivo debe contener los productos filtrados por marca o categoría.');
                $('#mensajeInfoImport').removeClass('alert-warning').addClass('alert-info').show();
                $('#textoInfoDescarga').html('Seleccione los filtros para generar la plantilla de una <strong>categoría específica</strong>.');
                $('#mensajeInfoDescarga').show();
            } else if (tipoPlantilla === 'general') {
                // Modo General: mostrar filtros excepto categoría de precios
                $('#containerTipoCategoria').show();
                $('#tituloInfoImport').text('Modo: General');
                $('#descripcionInfoImport').html('Los precios se actualizarán <strong>para TODAS las categorías de precios activas</strong> del sistema. No necesita seleccionar una categoría específica.');
                $('#mensajeInfoImport').removeClass('alert-info').addClass('alert-warning').show();
                $('#textoInfoDescarga').html('Modo <strong>general</strong>: la plantilla incluirá productos de todas las categorías activas del sistema.');
                $('#mensajeInfoDescarga').show();
            } else {
                $('#mensajeInfoImport').hide();
                $('#mensajeInfoDescarga').hide();
            }
        });

        // Al cambiar tipo de categoría
        $('#tipoCategoria').on('change', function() {
            $('#containerTipoFiltro').show();
            if (!$(this).val()) {
                $('#containerListaFiltro').hide();
                $('#catClienteSelect').val(null).trigger('change');
                $('#containerCatCliente').hide();
                $('#listaTipoFiltroCatPrecios').empty().append(new Option('Categoría de precio', '', false, false));
                $('#containerCatPrecios').hide();
            }
            validarFormularioDescarga();
        });

        // Al cambiar tipo de filtro
        $('#tipoFiltro').on('change', function() {
            if ($(this).val()) {
                $('#containerListaFiltro').show();
                $('#listaTipoFiltro').empty().trigger('change');
                $('#resumenFiltroProductos').text('Seleccionar opciones');
                abrirModalFiltrosProductos();
            } else {
                $('#containerListaFiltro').hide();
                $('#catClienteSelect').val(null).trigger('change');
                $('#containerCatCliente').hide();
                $('#listaTipoFiltroCatPrecios').empty().append(new Option('Categoría de precio', '', false, false));
                $('#containerCatPrecios').hide();
            }
            validarFormularioDescarga();
        });

        // Al cambiar lista de filtro
        $('#listaTipoFiltro').on('change', function() {
            const tipoPlantilla = $('#tipoPlantilla').val();
            // Siempre resetear los dependientes
            $('#catClienteSelect').val(null).trigger('change');
            $('#containerCatCliente').hide();
            $('#listaTipoFiltroCatPrecios').empty().append(new Option('Categoría de precio', '', false, false));
            $('#containerCatPrecios').hide();
            if ($(this).val() && tipoPlantilla === 'categoria') {
                $('#containerCatCliente').show();
            }
            validarFormularioDescarga();
        });

        // Al cambiar categoría de cliente → cargar categorías de precio de ese cliente
        $('#catClienteSelect').on('change', function() {
            const clienteId = $(this).val();
            const $catPrecios = $('#listaTipoFiltroCatPrecios');

            // Limpiar select dependiente
            $catPrecios.empty().append(new Option('Categoría de precio', '', false, false));
            $catPrecios.trigger('change');
            $('#containerCatPrecios').hide();

            if (!clienteId) { validarFormularioDescarga(); return; }

            $.ajax({
                url: '/listar/categorias/precios/por-cliente/' + clienteId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    const activos = (data.categorias || []).filter(function(c) { return c.estado_id == 1; });
                    if (activos.length > 0) {
                        // Primera opción: todas las categorías de precios de este cliente
                        $catPrecios.append(new Option('★ Todas las categorías de precios', 'all', false, false));
                        activos.forEach(function(c) {
                            $catPrecios.append(new Option(c.nombre, c.id, false, false));
                        });
                        $catPrecios.trigger('change');
                        $('#containerCatPrecios').show();
                    } else {
                        Swal.fire({ icon: 'info', title: 'Sin categorías', text: 'Esta categoría de cliente no tiene categorías de precio activas.', confirmButtonColor: '#e67e22' });
                    }
                    validarFormularioDescarga();
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las categorías de precio.' });
                }
            });

            validarFormularioDescarga();
        });

        // Al cambiar categoría de precios
        $('#listaTipoFiltroCatPrecios').on('change', function() {
            validarFormularioDescarga();
        });

        // Validar formulario para habilitar/deshabilitar botón de descarga
        function validarFormularioDescarga() {
            const tipoPlantilla    = $('#tipoPlantilla').val();
            const tipoCategoria    = $('#tipoCategoria').val();
            const tipoFiltro       = $('#tipoFiltro').val();
            const valorFiltro      = $('#listaTipoFiltro').val();
            const catClienteId     = $('#catClienteSelect').val();
            const categoriaPrecioId = $('#listaTipoFiltroCatPrecios').val();

            let valido = false;

            if (tipoPlantilla === 'categoria') {
                // Modo Categoría: requiere categoría de cliente + categoría de precio
                valido = !!(tipoCategoria && tipoFiltro && valorFiltro && catClienteId && categoriaPrecioId);
            } else if (tipoPlantilla === 'general') {
                // Modo General: no requiere categoría de precio específica
                valido = !!(tipoCategoria && tipoFiltro && valorFiltro);
            }

            $('#btnDescargar').prop('disabled', !valido);
        }

        // ── Upload zone: actualizar UI al seleccionar archivo ──
        function actualizarUploadZone(hasFile, filename) {
            const zone = $('#uploadZone');
            const filenameLbl = $('#uploadFilename');
            if (hasFile) {
                zone.addClass('has-file');
                filenameLbl.text(filename).show();
                btnLimpiarPrecios.show();
                btnProcesarPrecios.prop('disabled', false);
            } else {
                zone.removeClass('has-file');
                filenameLbl.hide().text('');
                btnLimpiarPrecios.hide();
                btnProcesarPrecios.prop('disabled', true);
            }
        }

        // Drag over / drag leave en upload zone
        $('#uploadZone').on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('drag-over');
        }).on('dragleave drop', function() {
            $(this).removeClass('drag-over');
        });

        // Resetear cuando se cambie el archivo
        fileInputPrecios.on('change', function() {
            // Ocultar previews
            $('#previewActualizablesPrecios').hide();
            $('#previewNoActualizablesPrecios').hide();
            $('#wrapProgressImport').hide();

            // Ocultar botón finalizar
            btnFinalizarPrecios.hide();
            btnProcesarPrecios.show();

            // Limpiar barra de progreso y mensajes
            barProgressPrecios.removeClass('bg-success bg-danger bg-info').css('width', '0%');
            msgImportPrecios.removeClass('text-danger').text('');

            // Actualizar upload zone
            if (this.files.length > 0) {
                actualizarUploadZone(true, this.files[0].name);
            } else {
                actualizarUploadZone(false, '');
            }
        });

        // Limpiar archivo seleccionado
        btnLimpiarPrecios.on('click', function(e) {
            e.preventDefault();
            fileInputPrecios.val('');
            actualizarUploadZone(false, '');
            btnFinalizarPrecios.hide();
            btnProcesarPrecios.show();
            $('#previewActualizablesPrecios').hide();
            $('#previewNoActualizablesPrecios').hide();
            $('#wrapProgressImport').hide();
            barProgressPrecios.removeClass('bg-success bg-danger bg-info').css('width', '0%');
            msgImportPrecios.removeClass('text-danger').text('');
        });

        // =============================================
        // Función centralizada para ejecutar el AJAX de preview
        // =============================================
        function ejecutarPreviewPrecios(categoriasExcluidas) {
            const tipoPlantilla     = $('#tipoPlantilla').val();
            const tipoCategoria     = $('#tipoCategoria').val();
            const tipoFiltro        = $('#tipoFiltro').val();
            const valorFiltro       = $('#listaTipoFiltro').val();
            const categoriaPrecioId = $('#listaTipoFiltroCatPrecios').val();
            const catClienteId      = $('#catClienteSelect').val();

            const formData = new FormData(formSubirExcel[0]);
            formData.append('tipoPlantilla', tipoPlantilla);
            formData.append('tipoCategoria', tipoCategoria);
            formData.append('tipoFiltro', tipoFiltro);
            formData.append('valorFiltro', valorFiltro);
            if (tipoPlantilla === 'categoria') {
                formData.append('categoriaPrecioId', categoriaPrecioId);
            }
            if (catClienteId) {
                formData.append('catClienteId', catClienteId);
            }
            if (categoriasExcluidas && categoriasExcluidas.length > 0) {
                categoriasExcluidas.forEach(function(id) {
                    formData.append('categoriasExcluidas[]', id);
                });
            }

            // Ocultar previews anteriores
            $('#previewActualizablesPrecios').hide();
            $('#previewNoActualizablesPrecios').hide();
            btnFinalizarPrecios.hide();

            // Mostrar overlay de carga
            $('#overlayProcesandoPrecios').css('display', 'flex');
            $('#tituloOverlayPrecios').text('Procesando archivo...');
            $('#mensajeOverlayPrecios').text('Por favor espere mientras se validan los datos');

            $('#wrapProgressImport').show();
            barProgressPrecios.removeClass('bg-success bg-danger').addClass('bg-info').css('width', '0%');
            msgImportPrecios.removeClass('text-danger').text('Validando archivo...');

            $.ajax({
                url: "{{ route('preview.excel.precios') }}",
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                xhr: function() {
                    const xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const p = Math.round((e.loaded / e.total) * 100);
                                barProgressPrecios.css('width', p + '%');
                            }
                        }, false);
                    }
                    return xhr;
                },
                success: function(res) {
                    $('#overlayProcesandoPrecios').hide();
                    barProgressPrecios.addClass('bg-info').css('width', '100%');
                    msgImportPrecios.text('Preview generado - Revise los productos');

                    const debug = res.debug || {};
                    const rowsToProcess = debug.rows_to_process || 0;
                    const rowsSkipped = debug.rows_skipped || 0;
                    const skippedReasons = debug.skipped_reasons || [];
                    const productosParaProcesar = debug.productos_para_procesar || [];

                    if (rowsToProcess > 0 && productosParaProcesar.length > 0) {
                        $('#countActualizablesPrecios').text(productosParaProcesar.length);
                        let htmlActualizables = '';
                        productosParaProcesar.forEach(function(item) {
                            htmlActualizables += `
                                <tr>
                                    <td>${item.codigo || item.producto_id || 'N/A'}</td>
                                    <td>${item.descripcion || item.nombre || 'N/A'}</td>
                                    <td class="font-weight-bold text-info">${item.categoria_precio || 'N/A'}</td>
                                    <td>${item.precio_base || 'N/A'}</td>
                                    <td class="text-success font-weight-bold">${item.precio_a || 'N/A'}</td>
                                    <td class="text-success font-weight-bold">${item.precio_b || 'N/A'}</td>
                                    <td class="text-success font-weight-bold">${item.precio_c || 'N/A'}</td>
                                    <td class="text-success font-weight-bold">${item.precio_d || 'N/A'}</td>
                                </tr>
                            `;
                        });
                        $('#tablaActualizablesPrecios').html(htmlActualizables);
                        $('#previewActualizablesPrecios').show();
                        btnProcesarPrecios.hide();
                        btnFinalizarPrecios.show();
                    }

                    if (skippedReasons.length > 0) {
                        $('#countNoActualizablesPrecios').text(skippedReasons.length);
                        let htmlNoActualizables = '';
                        let tieneErroresFiltros = false;
                        skippedReasons.forEach(function(item, index) {
                            if (typeof item === 'object') {
                                if (item.motivo && (item.motivo.includes('no pertenece a la marca') || item.motivo.includes('no pertenece a la categoría'))) {
                                    tieneErroresFiltros = true;
                                }
                                htmlNoActualizables += `
                                    <tr>
                                        <td>${item.fila || index + 1}</td>
                                        <td>${item.codigo || item.producto_id || 'N/A'}</td>
                                        <td>${item.descripcion || item.nombre || 'N/A'}</td>
                                        <td class="text-danger">${item.motivo || item.razon || 'Error desconocido'}</td>
                                    </tr>
                                `;
                            } else {
                                if (typeof item === 'string' && (item.includes('no pertenece a la marca') || item.includes('no pertenece a la categoría'))) {
                                    tieneErroresFiltros = true;
                                }
                                htmlNoActualizables += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>N/A</td>
                                        <td>N/A</td>
                                        <td class="text-danger">${item}</td>
                                    </tr>
                                `;
                            }
                        });
                        $('#tablaNoActualizablesPrecios').html(htmlNoActualizables);
                        $('#previewNoActualizablesPrecios').show();
                        if (tieneErroresFiltros) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Filtros no coinciden',
                                html: `
                                    <p><strong>ATENCIÓN:</strong> El archivo contiene productos que no coinciden con los filtros seleccionados.</p>
                                    <p class="text-success">Productos a procesar: ${rowsToProcess}</p>
                                    <p class="text-warning">Productos omitidos por filtros: ${skippedReasons.length}</p>
                                    <p class="text-muted mt-3">Verifique que el archivo corresponda a los filtros seleccionados (Marca/Categoría).</p>
                                `,
                            });
                        } else {
                            Swal.fire({
                                icon: res.icon || 'info',
                                title: res.title || 'Preview Generado',
                                html: `
                                    <p>Productos a procesar: <strong>${rowsToProcess}</strong></p>
                                    <p>Productos omitidos: <strong>${rowsSkipped}</strong></p>
                                    <p class="mt-3 text-primary">Revise los datos y presione "Finalizar Actualización" para confirmar.</p>
                                `,
                            });
                        }
                    } else if (rowsToProcess > 0) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Preview Generado',
                            html: `
                                <p>Productos a procesar: <strong>${rowsToProcess}</strong></p>
                                <p class="mt-3 text-primary">Todo correcto. Presione "Finalizar Actualización" para confirmar.</p>
                            `,
                        });
                    }
                },
                error: function(xhr) {
                    $('#overlayProcesandoPrecios').hide();
                    barProgressPrecios.addClass('bg-danger').css('width', '100%');
                    let t = 'Error al procesar el archivo.';
                    let debugInfo = '';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.text) t = xhr.responseJSON.text;
                        if (xhr.responseJSON.debug) {
                            const debug = xhr.responseJSON.debug;
                            if (typeof debug === 'object') {
                                debugInfo = `<br><small class="text-muted">Error: ${debug.message || ''}<br>Archivo: ${debug.file || ''} (Línea: ${debug.line || ''})</small>`;
                            } else {
                                debugInfo = `<br><small class="text-muted">${debug}</small>`;
                            }
                        }
                    }
                    msgImportPrecios.addClass('text-danger').text(t);
                    Swal.fire({ icon: 'error', title: 'Error', html: t + debugInfo });
                },
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            });
        }

        // =============================================
        // Procesar archivo para PREVIEW
        // =============================================
        btnProcesarPrecios.on('click', function(e) {
            e.preventDefault();

            // Validar archivo
            if (fileInputPrecios[0].files.length > 0) {
                const fileName = fileInputPrecios[0].files[0].name;
                const fileExt = fileName.split('.').pop().toLowerCase();
                if (fileExt !== 'xlsx') {
                    Swal.fire({ icon: 'error', title: 'Archivo inválido', text: 'Solo se permiten archivos con extensión .xlsx' });
                    return;
                }
            } else {
                Swal.fire({ icon: 'warning', title: 'Advertencia', text: 'Debe seleccionar un archivo' });
                return;
            }

            const tipoPlantilla  = $('#tipoPlantilla').val();
            const tipoCategoria  = $('#tipoCategoria').val();
            const tipoFiltro     = $('#tipoFiltro').val();
            const valorFiltro    = $('#listaTipoFiltro').val();
            const categoriaPrecioId = $('#listaTipoFiltroCatPrecios').val();

            if (!tipoPlantilla || !tipoCategoria || !tipoFiltro || !valorFiltro) {
                Swal.fire({ icon: 'warning', title: 'Campos requeridos', text: 'Por favor complete todos los filtros antes de procesar el archivo.' });
                return;
            }

            if (tipoPlantilla === 'categoria' && !categoriaPrecioId) {
                Swal.fire({ icon: 'warning', title: 'Categoría de precios requerida', text: 'Debe seleccionar una categoría de precios para el modo "Por Categoría".' });
                return;
            }

            // Modo General: mostrar modal de selección de categorías
            if (tipoPlantilla === 'general') {
                cargarCategoriasEnModalGeneral();
                $('#modalSeleccionarCategoriasGeneral').modal('show');
                return;
            }

            // Modo Categoría: procesar directamente
            ejecutarPreviewPrecios([]);
        });

        // =============================================
        // Lógica del modal de selección de categorías (Modo General)
        // =============================================
        function cargarCategoriasEnModalGeneral() {
            $('#loadingCategoriasModal').show();
            $('#listaCategoriasModal').hide();
            $('#errorCargaCategoriasModal').hide();
            $('#btnConfirmarProcesarGeneral').prop('disabled', true);
            $('#checkboxCategoriasContainer').html('');

            $.ajax({
                url: '/filtros/categoria/precios',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#loadingCategoriasModal').hide();
                    if (!data || data.length === 0) {
                        $('#errorCargaCategoriasModal').text('No hay categorías de precios activas en el sistema.').show();
                        return;
                    }
                    let html = '';
                    data.forEach(function(cat) {
                        html += `
                            <div class="col-md-4 col-sm-6 mb-2">
                                <div class="custom-control custom-checkbox border rounded p-2 bg-white">
                                    <input type="checkbox" class="custom-control-input cat-precio-check"
                                           id="cat_check_${cat.id}" value="${cat.id}" checked>
                                    <label class="custom-control-label font-weight-bold" for="cat_check_${cat.id}">
                                        ${cat.nombre}
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    $('#checkboxCategoriasContainer').html(html);
                    $('#listaCategoriasModal').show();
                    actualizarContadorCategorias();
                },
                error: function() {
                    $('#loadingCategoriasModal').hide();
                    $('#errorCargaCategoriasModal').show();
                }
            });
        }

        function actualizarContadorCategorias() {
            const total = $('.cat-precio-check:checked').length;
            $('#contadorCatSeleccionadas').text(total);
            $('#btnConfirmarProcesarGeneral').prop('disabled', total === 0);
        }

        $(document).on('change', '.cat-precio-check', function() {
            actualizarContadorCategorias();
        });

        $('#btnSeleccionarTodasCat').on('click', function() {
            $('.cat-precio-check').prop('checked', true);
            actualizarContadorCategorias();
        });

        $('#btnDeseleccionarTodasCat').on('click', function() {
            $('.cat-precio-check').prop('checked', false);
            actualizarContadorCategorias();
        });

        $('#btnConfirmarProcesarGeneral').on('click', function() {
            const todasLasIds = $('.cat-precio-check').map(function() { return parseInt($(this).val()); }).get();
            const seleccionadas = $('.cat-precio-check:checked').map(function() { return parseInt($(this).val()); }).get();
            const excluidas = todasLasIds.filter(function(id) { return !seleccionadas.includes(id); });

            $('#modalSeleccionarCategoriasGeneral').modal('hide');
            ejecutarPreviewPrecios(excluidas);
        });

        // FINALIZAR actualización de precios
        btnFinalizarPrecios.on('click', function(e) {
            e.preventDefault();

            const tipoPlantilla = $('#tipoPlantilla').val();
            let mensajeConfirmacion = 'Se actualizarán los precios de los productos mostrados en el preview.';

            if (tipoPlantilla === 'general') {
                mensajeConfirmacion = 'Se actualizarán los precios para TODAS las categorías activas del sistema.';
            }

            Swal.fire({
                title: '¿Confirmar actualización?',
                text: mensajeConfirmacion,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar overlay de carga
                    $('#overlayProcesandoPrecios').css('display', 'flex');
                    $('#tituloOverlayPrecios').text('Finalizando actualización...');
                    $('#mensajeOverlayPrecios').text('Actualizando precios en la base de datos');

                    barProgressPrecios.removeClass('bg-info bg-danger').addClass('bg-success').css('width', '0%');
                    msgImportPrecios.text('Finalizando actualización...');
                    btnFinalizarPrecios.prop('disabled', true);

                    const formData = new FormData();
                    formData.append('tipoPlantilla', tipoPlantilla);

                    $.ajax({
                        url: "{{ route('finalizar.excel.precios') }}",
                        method: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        xhr: function() {
                            const xhr = $.ajaxSettings.xhr();
                            if (xhr.upload) {
                                xhr.upload.addEventListener('progress', function(e) {
                                    if (e.lengthComputable) {
                                        const p = Math.round((e.loaded / e.total) * 100);
                                        barProgressPrecios.css('width', p + '%');
                                    }
                                }, false);
                            }
                            return xhr;
                        },
                        success: function(res) {
                            // Ocultar overlay
                            $('#overlayProcesandoPrecios').hide();

                            barProgressPrecios.css('width', '100%');
                            msgImportPrecios.text('Actualización completada exitosamente');

                            const debug = res.debug || {};
                            const rowsInserted = debug.rows_inserted || 0;
                            const rowsInactivated = debug.rows_inactivated || 0;

                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualización Completada!',
                                html: `
                                    <p>Productos actualizados: <strong>${rowsInserted}</strong></p>
                                    <p>Productos inactivados: <strong>${rowsInactivated}</strong></p>
                                `,
                            }).then(() => {
                                // Limpiar todo
                                fileInputPrecios.val('');
                                actualizarUploadZone(false, '');
                                btnFinalizarPrecios.hide();
                                btnProcesarPrecios.show();
                                $('#previewActualizablesPrecios').hide();
                                $('#previewNoActualizablesPrecios').hide();
                                $('#wrapProgressImport').hide();
                                barProgressPrecios.css('width', '0%');
                                msgImportPrecios.text('');
                            });
                        },
                        error: function(xhr) {
                            // Ocultar overlay
                            $('#overlayProcesandoPrecios').hide();

                            barProgressPrecios.removeClass('bg-success').addClass('bg-danger').css('width', '100%');
                            let t = 'Error al finalizar la actualización.';
                            if (xhr.responseJSON && xhr.responseJSON.text) t = xhr.responseJSON.text;
                            msgImportPrecios.addClass('text-danger').text(t);
                            btnFinalizarPrecios.prop('disabled', false);

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: t
                            });
                        }
                    });
                }
            });
        });
    });
    </script>

    <script>
    // ── Wizard tab switcher (global, usado por onclick en el HTML) ──
    function switchWizardTab(tab) {
        var tabs = ['descargar', 'importar', 'editar'];
        tabs.forEach(function(t) {
            var key  = t.charAt(0).toUpperCase() + t.slice(1);
            var btn  = document.getElementById('tab'  + key);
            var pane = document.getElementById('pane' + key);
            if (t === tab) {
                btn.classList.add('active');
                pane.classList.add('active');
            } else {
                btn.classList.remove('active');
                pane.classList.remove('active');
            }
        });
    }
    </script>

    <script>
(function() {
    var _editorPage   = 1;
    var _editorPer    = 25;
    var _editorTotal  = 0;
    var _editorCatId  = null;
    var _editorBuscar = '';
    var _editorProductoAgregar = null;

    $(document).ready(function() {

        $('#editorCatClienteSel').select2({
            theme: 'bootstrap4',
            placeholder: '— Seleccione categoría de cliente —',
            allowClear: true,
            language: { noResults: function() { return 'Sin resultados'; } }
        });
        $('#editorCatPrecioSel').select2({
            theme: 'bootstrap4',
            placeholder: '— Seleccione categoría de precio —',
            allowClear: true,
            language: { noResults: function() { return 'Sin resultados'; } }
        });

        $('#editorCatClienteSel').on('change', function() {
            var clienteId = $(this).val();

            // Resetear paso 2 y 3
            $('#editorCatPrecioSel').empty().append(new Option('— Seleccione —', '', false, false)).trigger('change');
            $('#editorStep2').hide();
            $('#editorStep3').hide();
            $('#wrapEditorTabla').hide();
            $('#editorEmpty').hide();
            $('#editorPlaceholder').show();

            if (!clienteId) return;

            $('#editorStep2').show();
            $('#editorStep2Spinner').show();

            $.getJSON('/listar/categorias/precios/por-cliente/' + clienteId)
                .done(function(res) {
                    $('#editorStep2Spinner').hide();
                    var cats = (res.categorias || []).filter(function(c) { return c.estado_id == 1; });
                    if (!cats.length) {
                        Swal.fire({ icon: 'info', title: 'Sin categorías', text: 'Esta categoría de cliente no tiene categorías de precio activas.', confirmButtonColor: '#e67e22' });
                        return;
                    }
                    cats.forEach(function(c) {
                        $('#editorCatPrecioSel').append(new Option(c.nombre, c.id, false, false));
                    });
                    $('#editorCatPrecioSel').trigger('change');
                })
                .fail(function() {
                    $('#editorStep2Spinner').hide();
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las categorías de precio.', confirmButtonColor: '#e67e22' });
                });
        });

        /* ─── Paso 2: al cambiar categoría de precio → mostrar paso 3 y cargar ─── */
        $('#editorCatPrecioSel').on('change', function() {
            _editorCatId = $(this).val();
            $('#editorStep3').hide();
            $('#wrapEditorTabla').hide();
            $('#editorEmpty').hide();
            $('#editorBuscarProd').val('');

            if (!_editorCatId) {
                $('#editorPlaceholder').show();
                return;
            }
            $('#editorPlaceholder').hide();
            $('#editorStep3').show();
            cargarEditorPrecios(1);
        });

        $('#editorBuscarProd').on('keydown', function(e) {
            if (e.key === 'Enter') cargarEditorPrecios(1);
        });

    });

    /* ─── Cargar tabla de productos ─── */
    window.cargarEditorPrecios = function(page) {
        if (!_editorCatId) return;
        _editorBuscar = $('#editorBuscarProd').val().trim();
        _editorPage   = page || 1;
        var start     = (_editorPage - 1) * _editorPer;

        $('#editorEmpty').hide();
        $('#wrapEditorTabla').hide();
        $('#editorSpinner').show();

        $.ajax({
            url: '/precios/productos/listar',
            method: 'GET',
            data: { categoria_id: _editorCatId, buscar: _editorBuscar, start: start, length: _editorPer },
            success: function(res) {
                $('#editorSpinner').hide();

                if (res.porc) {
                    $('#editorPorcA').text(res.porc.a);
                    $('#editorPorcB').text(res.porc.b);
                    $('#editorPorcC').text(res.porc.c);
                    $('#editorPorcD').text(res.porc.d);
                    $('.hdr-porc-a').text(res.porc.a);
                    $('.hdr-porc-b').text(res.porc.b);
                    $('.hdr-porc-c').text(res.porc.c);
                    $('.hdr-porc-d').text(res.porc.d);
                }

                _editorTotal = res.recordsTotal || 0;

                if (!res.data || !res.data.length) {
                    $('#editorEmpty').show();
                    return;
                }

                _renderEditorTabla(res.data);
                _renderEditorPagin();
                $('#wrapEditorTabla').show();
            },
            error: function() {
                $('#editorSpinner').hide();
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los productos.', confirmButtonColor: '#e67e22' });
            }
        });
    };

    function _renderEditorTabla(data) {
        var tbody = $('#tbody_editor_precios');
        tbody.empty();
        $.each(data, function(i, row) {
            var tr = $('<tr>').attr({
                'data-id':     row.id,
                'data-porc-a': row.porc_precio_a,
                'data-porc-b': row.porc_precio_b,
                'data-porc-c': row.porc_precio_c,
                'data-porc-d': row.porc_precio_d
            });
            tr.append($('<td>').text(row.codigo).css({ color: '#999', fontSize: '.72rem', whiteSpace:'nowrap' }));
            var nombreProducto = $('<button type="button" class="editor-product-history">')
                .text(row.nombre)
                .attr('title', 'Ver histórico de precios')
                .on('click', function() { abrirHistorialPrecioProducto(row.id); });
            tr.append($('<td>').append(nombreProducto));

            var inputBase = $('<input>').attr({
                type: 'number', step: '0.01', min: '0.01',
                class: 'editor-base-input',
                value: parseFloat(row.precio_base_venta).toFixed(2)
            }).on('input', function() {
                var base = parseFloat($(this).val()) || 0;
                var $tr  = $(this).closest('tr');
                $tr.find('.td-pa').text(_calc(base, $tr.data('porc-a')));
                $tr.find('.td-pb').text(_calc(base, $tr.data('porc-b')));
                $tr.find('.td-pc').text(_calc(base, $tr.data('porc-c')));
                $tr.find('.td-pd').text(_calc(base, $tr.data('porc-d')));
                $(this).addClass('is-modified');
            });

            tr.append($('<td>').append(inputBase));
            tr.append($('<td class="td-pa text-right">').css('color','#2471a3').text(_calc(row.precio_base_venta, row.porc_precio_a)));
            tr.append($('<td class="td-pb text-right">').css('color','#1e8449').text(_calc(row.precio_base_venta, row.porc_precio_b)));
            tr.append($('<td class="td-pc text-right">').css('color','#c0392b').text(_calc(row.precio_base_venta, row.porc_precio_c)));
            tr.append($('<td class="td-pd text-right">').css('color','#7d3c98').text(_calc(row.precio_base_venta, row.porc_precio_d)));

            var btnSave = $('<button class="btn-editor-save mr-1">').html('<i class="fa fa-save"></i> Guardar')
                .on('click', function() { _guardarBase(row.id, tr); });
            var btnDelete = $('<button class="btn btn-sm btn-outline-danger" title="Eliminar de esta categoría">').html('<i class="fa fa-trash"></i>')
                .on('click', function() { _eliminarProductoPrecio(row.id, row.nombre); });
            tr.append($('<td class="text-center" style="white-space:nowrap;">').append(btnSave, btnDelete));
            tbody.append(tr);
        });
    }

    window.abrirHistorialPrecioProducto = function(precioId) {
        $('#tituloHistorialPrecioProducto').text('Histórico del producto');
        $('#historialPrecioResumen').empty();
        $('#tablaHistorialPrecioProducto tbody').empty();
        $('#historialPrecioContenido, #historialPrecioError').hide();
        $('#historialPrecioCargando').show();
        $('#modalHistorialPrecioProducto').modal('show');

        $.get('/precios/producto/historial', { precio_id: precioId })
            .done(function(res) {
                $('#tituloHistorialPrecioProducto').text(res.producto);
                $('#historialPrecioResumen').html(
                    '<div class="d-flex flex-wrap align-items-center" style="gap:8px;">' +
                    '<span class="badge badge-light border px-2 py-1">Código: <strong>' + res.producto_id + '</strong></span>' +
                    '<span class="badge badge-light border px-2 py-1">Categoría: <strong>' + _escaparHtml(res.categoria) + '</strong></span>' +
                    '<span class="badge badge-light border px-2 py-1">Versiones: <strong>' + res.total + '</strong></span>' +
                    '</div>'
                );

                var tbody = $('#tablaHistorialPrecioProducto tbody').empty();
                $.each(res.historial || [], function(index, item) {
                    var estado = item.vigente
                        ? '<span class="badge badge-success">Vigente</span>'
                        : '<span class="badge badge-secondary">Histórico</span>';
                    var celdaNivel = function(nivel, clase) {
                        return '<td class="text-right ' + clase + '">' +
                            '<span class="historial-precio-valor">L. ' + _formatoPrecioHistorial(nivel.valor) + '</span>' +
                            '<span class="historial-precio-porc">' + _formatoPorcentajeHistorial(nivel.porcentaje) + '%</span></td>';
                    };
                    tbody.append('<tr>' +
                        '<td><strong>#' + item.id + '</strong><br>' + estado + '</td>' +
                        '<td style="white-space:nowrap;">' + _formatoFechaHistorial(item.fecha_registro) + '</td>' +
                        '<td style="white-space:nowrap;">' + _formatoFechaHistorial(item.fecha_actualizacion) + '</td>' +
                        '<td class="text-right"><span class="historial-precio-valor">L. ' + _formatoPrecioHistorial(item.precio_base) + '</span></td>' +
                        celdaNivel(item.a, 'historial-col-a') +
                        celdaNivel(item.b, 'historial-col-b') +
                        celdaNivel(item.c, 'historial-col-c') +
                        celdaNivel(item.d, 'historial-col-d') +
                        '<td>' + _escaparHtml(item.usuario) + '</td>' +
                        '</tr>');
                });

                $('#historialPrecioCargando').hide();
                $('#historialPrecioContenido').show();
            })
            .fail(function(xhr) {
                $('#historialPrecioCargando').hide();
                $('#historialPrecioError')
                    .text(xhr.responseJSON?.error || 'No se pudo cargar el histórico de precios.')
                    .show();
            });
    };

    function _formatoPrecioHistorial(valor) {
        return (parseFloat(valor) || 0).toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function _formatoPorcentajeHistorial(valor) {
        return (parseFloat(valor) || 0).toLocaleString('es-HN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function _formatoFechaHistorial(valor) {
        if (!valor) return 'Sin registro';
        var fecha = new Date(String(valor).replace(' ', 'T'));
        if (isNaN(fecha.getTime())) return _escaparHtml(valor);
        return fecha.toLocaleString('es-HN', { dateStyle: 'short', timeStyle: 'short' });
    }

    window.abrirAgregarProductoPrecio = function() {
        if (!_editorCatId) return;
        _editorProductoAgregar = null;
        window.abrirBuscador_buscadorProductoPrecio('', true);
    };

    window.parametrosBuscadorProductoPrecio = function() {
        return { excluir_categoria_precio_id: _editorCatId };
    };

    window.seleccionarProductoParaPrecio = function(producto) {
        _editorProductoAgregar = producto;
        $('#editorAgregarSeleccion').html('<strong>' + _escaparHtml(producto.nombre) + '</strong><br><small>ID ' + producto.id + ' · Código ' + _escaparHtml(producto.codigo_barra || 'Sin código') + '</small>');
        var ultimoPrecioBase = parseFloat(producto.ultimo_precio_base || 0);
        $('#editorAgregarBase').val(ultimoPrecioBase > 0 ? ultimoPrecioBase.toFixed(2) : '');
        $('#editorUltimoPrecioBase')
            .toggle(ultimoPrecioBase > 0)
            .html(ultimoPrecioBase > 0
                ? '<i class="fa fa-history mr-1"></i>Último precio base registrado: <strong>L. ' + ultimoPrecioBase.toFixed(2) + '</strong>'
                : '');
        $('#btnConfirmarAgregarPrecio').prop('disabled', false);
        $('#buscadorProductoPrecio').one('hidden.bs.modal', function() {
            $('#modalAgregarProductoPrecio').modal('show');
            setTimeout(function() { $('#editorAgregarBase').trigger('focus'); }, 200);
        });
    };

    window.confirmarAgregarProductoPrecio = function() {
        var precioBase = parseFloat($('#editorAgregarBase').val());
        if (!_editorProductoAgregar || !precioBase || precioBase <= 0) {
            Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Seleccione un producto e indique un precio base mayor que cero.', confirmButtonColor: '#e67e22' });
            return;
        }
        var $btn = $('#btnConfirmarAgregarPrecio').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Agregando...');
        $.post('/precios/producto/agregar', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            categoria_id: _editorCatId,
            producto_id: _editorProductoAgregar.id,
            precio_base: precioBase
        }).done(function(res) {
            $('#modalAgregarProductoPrecio').modal('hide');
            cargarEditorPrecios(1);
            Swal.fire({ icon: 'success', title: 'Producto agregado', text: res.text, confirmButtonColor: '#27ae60' });
        }).fail(function(xhr) {
            Swal.fire({ icon: 'error', title: 'No se pudo agregar', text: xhr.responseJSON?.text || 'Ocurrió un error al agregar el producto.', confirmButtonColor: '#e67e22' });
        }).always(function() {
            $btn.prop('disabled', false).html('<i class="fa fa-plus mr-1"></i>Agregar producto');
        });
    };

    function _eliminarProductoPrecio(precioId, nombre) {
        Swal.fire({
            icon: 'warning', title: 'Eliminar producto',
            text: '¿Eliminar "' + nombre + '" de esta categoría de precio?',
            showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
            confirmButtonColor: '#c0392b'
        }).then(function(result) {
            if (!result.value) return;
            $.post('/precios/producto/eliminar', {
                _token: $('meta[name="csrf-token"]').attr('content'),
                categoria_id: _editorCatId,
                precio_id: precioId
            }).done(function(res) {
                cargarEditorPrecios(_editorPage);
                Swal.fire({ icon: 'success', title: 'Producto eliminado', text: res.text, confirmButtonColor: '#27ae60' });
            }).fail(function(xhr) {
                Swal.fire({ icon: 'error', title: 'No se pudo eliminar', text: xhr.responseJSON?.text || 'Ocurrió un error.', confirmButtonColor: '#e67e22' });
            });
        });
    }

    window.descargarProductosEditor = function() {
        var categoriaClienteId = $('#editorCatClienteSel').val();
        if (!_editorCatId || !categoriaClienteId) return;
        window.location.href = '/descargar/productos/filtros?cat_cliente_id=' + encodeURIComponent(categoriaClienteId)
            + '&cat_precio_id=' + encodeURIComponent(_editorCatId);
    };

    function _escaparHtml(valor) {
        return $('<div>').text(valor == null ? '' : String(valor)).html();
    }

    function _calc(base, porc) {
        return (parseFloat(base) * (1 + parseFloat(porc) / 100)).toFixed(2);
    }

    function _guardarBase(precioId, $tr) {
        var base = parseFloat($tr.find('.editor-base-input').val());
        if (!base || base <= 0) {
            Swal.fire({ icon: 'warning', title: 'Precio inválido', text: 'El precio base debe ser mayor a 0.', confirmButtonColor: '#e67e22' });
            return;
        }
        var $btn = $tr.find('.btn-editor-save');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/precios/producto/actualizar-base',
            method: 'POST',
            data: {
                _token:      $('meta[name="csrf-token"]').attr('content'),
                precio_id:   precioId,
                precio_base: base
            },
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');
                $tr.find('.editor-base-input').removeClass('is-modified');
                $tr.addClass('row-saved');
                // Actualizar data-id de la fila con el nuevo registro creado
                if (res.nuevo_id) {
                    $tr.attr('data-id', res.nuevo_id);
                    $btn.off('click').on('click', function() { _guardarBase(res.nuevo_id, $tr); });
                }
                setTimeout(function() { $tr.removeClass('row-saved'); }, 1800);
                Swal.fire({ icon: 'success', title: '¡Guardado!', text: res.text, confirmButtonColor: '#27ae60', timer: 1800, timerProgressBar: true });
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar el precio.', confirmButtonColor: '#e67e22' });
            }
        });
    }

    function _renderEditorPagin() {
        var totalPages = Math.ceil(_editorTotal / _editorPer);
        var from = Math.min((_editorPage - 1) * _editorPer + 1, _editorTotal);
        var to   = Math.min(_editorPage * _editorPer, _editorTotal);
        $('#editorPaginInfo').text('Mostrando ' + from + '–' + to + ' de ' + _editorTotal + ' productos');
        $('#editorPaginPages').text('Página ' + _editorPage + ' / ' + totalPages);
        $('#btnEditorPrev').prop('disabled', _editorPage <= 1);
        $('#btnEditorNext').prop('disabled', _editorPage >= totalPages);
    }

    window.cambiarPaginaEditor = function(dir) {
        _editorPage += dir;
        cargarEditorPrecios(_editorPage);
        document.getElementById('cardEditorPrecios').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };
})();
    </script>
@endpush

