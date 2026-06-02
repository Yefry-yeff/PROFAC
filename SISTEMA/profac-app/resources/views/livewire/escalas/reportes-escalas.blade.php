@push('styles')
<style>
/* == PROFAC design system == */
:root {
    --pf-grad:       linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    --pf-grad-hover: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    --pf-orange:     #e67e22;
    --pf-orange-lt:  #fdf6ee;
    --pf-border:     #f0e6d8;
    --pf-shadow:     0 2px 10px rgba(0,0,0,.07);
    --pf-radius:     10px;
}
@keyframes pf-fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.rpt-tab-pane.active { animation: pf-fade-in .22s ease both; }
.rpt-wrapper { padding: 0 0 2rem; }
.rpt-tabs-bar { background:#fff; border:1px solid var(--pf-border); border-radius:var(--pf-radius); padding:10px 14px 0; box-shadow:var(--pf-shadow); margin-bottom:18px; overflow-x:auto; -webkit-overflow-scrolling:touch; }
.rpt-tabs-bar h5 { font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; color:#b07c3c; font-weight:700; margin:0 0 8px; display:flex; align-items:center; gap:6px; }
.rpt-nav { flex-wrap:nowrap; gap:4px; }
.rpt-nav .nav-link { display:flex; align-items:center; gap:6px; font-size:.76rem; font-weight:600; color:#6c757d; border-radius:7px 7px 0 0; padding:7px 14px; white-space:nowrap; border:1px solid transparent; border-bottom:none; transition:color .15s,background .15s; }
.rpt-nav .nav-link:hover { color:var(--pf-orange); background:var(--pf-orange-lt); }
.rpt-nav .nav-link.active { background:var(--pf-grad); color:#fff; border-color:#f39c12; }
.rpt-nav .rpt-badge { background:rgba(255,255,255,.25); border-radius:10px; font-size:.65rem; padding:1px 6px; font-weight:700; }
.rpt-nav .nav-link.active .rpt-badge { background:rgba(255,255,255,.3); }
.rpt-nav .tab-alert { color:#e74c3c; }
.rpt-nav .nav-link.active .tab-alert { color:#ffd5d5; }
.rpt-card { border:1px solid var(--pf-border); border-radius:var(--pf-radius); box-shadow:var(--pf-shadow); background:#fff; overflow:hidden; }
.rpt-card-header { background:var(--pf-grad); padding:12px 20px; display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:8px; }
.rpt-card-header .rpt-title { color:#fff; font-size:.88rem; font-weight:700; margin:0; display:flex; align-items:center; gap:8px; }
.rpt-card-header .rpt-subtitle { color:rgba(255,255,255,.78); font-size:.73rem; margin:2px 0 0; }
.rpt-card-filters { background:#fffdf9; border-bottom:1px solid var(--pf-border); padding:14px 20px; display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; }
.rpt-filter-label { font-size:.68rem; font-weight:700; color:#7d4600; text-transform:uppercase; letter-spacing:.06em; margin-bottom:4px; display:flex; align-items:center; gap:4px; }
.rpt-filter-item { flex:1 1 180px; max-width:280px; }
.rpt-filter-item.flex-none { flex:0 0 auto; }
.rpt-card-footer { background:#f8f5f0; border-top:1px solid var(--pf-border); padding:10px 16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; }
.rpt-card-footer .rpt-info { font-size:.73rem; color:#999; }
.btn-rpt-excel { background:linear-gradient(135deg,#27ae60 0%,#1e8449 100%); color:#fff; border:none; border-radius:7px; font-size:.78rem; font-weight:600; padding:6px 16px; display:inline-flex; align-items:center; gap:6px; box-shadow:0 1px 4px rgba(39,174,96,.3); transition:box-shadow .15s,background .15s; cursor:pointer; }
.btn-rpt-excel:hover { background:linear-gradient(135deg,#1e8449 0%,#196f3d 100%); color:#fff; box-shadow:0 3px 8px rgba(39,174,96,.4); text-decoration:none; }
.btn-rpt-excel:disabled { opacity:.6; cursor:not-allowed; }
.btn-rpt-primary { background:var(--pf-grad); color:#fff; border:none; border-radius:7px; font-size:.78rem; font-weight:600; padding:6px 16px; display:inline-flex; align-items:center; gap:6px; box-shadow:0 1px 4px rgba(230,126,34,.3); transition:box-shadow .15s,background .15s; cursor:pointer; }
.btn-rpt-primary:hover { background:var(--pf-grad-hover); color:#fff; box-shadow:0 3px 8px rgba(230,126,34,.4); text-decoration:none; }
.rpt-table thead th { background:#fdf4ea !important; color:#7d4600; font-size:.72rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; border-bottom:2px solid #ecd5b5 !important; white-space:nowrap; padding:8px 10px; }
.rpt-table td { font-size:.8rem; vertical-align:middle; padding:6px 10px; }
.rpt-table tbody tr:hover { background-color:#fffcf7 !important; }
.badge-activo   { background:#d4edda; color:#155724; border-radius:6px; padding:3px 8px; font-size:.7rem; font-weight:700; }
.badge-inactivo { background:#f8d7da; color:#721c24; border-radius:6px; padding:3px 8px; font-size:.7rem; font-weight:700; }
.badge-cero     { background:#fff3cd; color:#856404; border-radius:6px; padding:3px 8px; font-size:.7rem; font-weight:700; }
.rpt-stats { display:flex; flex-wrap:wrap; gap:10px; padding:12px 16px; background:#fffdf9; border-bottom:1px solid var(--pf-border); }
.rpt-stat-box { background:#fff; border:1px solid var(--pf-border); border-radius:8px; padding:10px 18px; text-align:center; flex:1 1 120px; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.rpt-stat-box .stat-val { font-size:1.5rem; font-weight:700; color:var(--pf-orange); line-height:1.1; }
.rpt-stat-box .stat-lbl { font-size:.68rem; color:#999; text-transform:uppercase; letter-spacing:.05em; margin-top:2px; }
.rpt-loading { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:40px; gap:10px; color:#b07c3c; font-size:.82rem; }
.rpt-empty { padding:40px; text-align:center; color:#adb5bd; font-size:.84rem; }
.rpt-empty i { font-size:2.2rem; display:block; margin-bottom:8px; }
/* === Select2 profesional en filtros === */
.rpt-filter-item .select2-container { width:100% !important; }
.rpt-filter-item .select2-container--bootstrap4 .select2-selection--single {
    height:38px; border:1px solid #ddd; border-radius:7px;
    background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.06);
    transition:border-color .15s,box-shadow .15s;
    display:flex; align-items:center;
}
.rpt-filter-item .select2-container--bootstrap4 .select2-selection--single:hover { border-color:#e67e22; }
.rpt-filter-item .select2-container--bootstrap4.select2-container--open .select2-selection--single,
.rpt-filter-item .select2-container--bootstrap4.select2-container--focus .select2-selection--single {
    border-color:#e67e22; box-shadow:0 0 0 3px rgba(230,126,34,.15);
}
.rpt-filter-item .select2-container--bootstrap4 .select2-selection__rendered {
    line-height:36px; font-size:.82rem; color:#343a40; padding-left:10px; padding-right:28px;
}
.rpt-filter-item .select2-container--bootstrap4 .select2-selection__placeholder { color:#adb5bd; }
.rpt-filter-item .select2-container--bootstrap4 .select2-selection__arrow { height:36px; width:28px; }
.rpt-filter-item .select2-container--bootstrap4 .select2-selection__arrow b {
    border-color:#e67e22 transparent transparent; border-width:5px 4px 0;
}
.rpt-filter-item .select2-container--bootstrap4.select2-container--open .select2-selection__arrow b {
    border-color:transparent transparent #e67e22; border-width:0 4px 5px;
}
.rpt-filter-item .select2-container--bootstrap4 .select2-selection__clear {
    color:#aaa; font-size:1rem; line-height:1; margin-top:2px;
}
.select2-dropdown.select2-dropdown--below,
.select2-dropdown.select2-dropdown--above {
    border:1px solid #e67e22; border-radius:7px; box-shadow:0 4px 16px rgba(0,0,0,.12);
    overflow:hidden;
}
.select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
    border:1px solid #ddd; border-radius:5px; padding:5px 10px; font-size:.82rem;
}
.select2-container--bootstrap4 .select2-results__option {
    font-size:.82rem; padding:7px 12px; color:#343a40;
}
.select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
    background:linear-gradient(135deg,#f39c12,#e67e22); color:#fff;
}
.select2-container--bootstrap4 .select2-results__option[aria-selected=true] {
    background:#fdf6ee; color:#7d4600; font-weight:600;
}
/* === Multi-select tags === */
.rpt-filter-item .select2-container--bootstrap4 .select2-selection--multiple {
    border:1px solid #ddd; border-radius:7px; min-height:38px;
    padding:4px 6px; box-shadow:0 1px 3px rgba(0,0,0,.06);
    transition:border-color .15s,box-shadow .15s; background:#fff;
}
.rpt-filter-item .select2-container--bootstrap4 .select2-selection--multiple:hover { border-color:#e67e22; }
.rpt-filter-item .select2-container--bootstrap4.select2-container--open .select2-selection--multiple,
.rpt-filter-item .select2-container--bootstrap4.select2-container--focus .select2-selection--multiple {
    border-color:#e67e22; box-shadow:0 0 0 3px rgba(230,126,34,.15);
}
.rpt-filter-item .select2-container--bootstrap4 .select2-selection__choice {
    background:linear-gradient(135deg,#f39c12,#e67e22) !important;
    color:#fff !important; border:none !important; border-radius:5px !important;
    padding:2px 8px 2px 6px !important; font-size:.72rem !important;
    font-weight:600 !important; margin:2px 3px 2px 0 !important;
    display:inline-flex; align-items:center; gap:4px;
}
.rpt-filter-item .select2-container--bootstrap4 .select2-selection__choice__remove {
    color:rgba(255,255,255,.75) !important; font-size:.85rem !important;
    font-weight:700 !important; line-height:1 !important;
    order:-1;
}
.rpt-filter-item .select2-container--bootstrap4 .select2-selection__choice__remove:hover { color:#fff !important; }
.rpt-filter-item .select2-container--bootstrap4 .select2-selection__rendered {
    padding:2px 4px; display:flex; flex-wrap:wrap; align-items:center;
}
.rpt-filter-item .select2-container--bootstrap4 .select2-search--inline .select2-search__field {
    font-size:.82rem; color:#343a40; margin-top:2px;
}
/* === select nativo en filtros (estado) === */
.rpt-filter-item select.form-control {
    height:38px; border-radius:7px; border:1px solid #ddd;
    font-size:.82rem; color:#343a40; padding:0 10px;
    box-shadow:0 1px 3px rgba(0,0,0,.06); transition:border-color .15s,box-shadow .15s;
    appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23e67e22' stroke-width='1.8' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 10px center; padding-right:30px;
}
.rpt-filter-item select.form-control:focus {
    border-color:#e67e22; box-shadow:0 0 0 3px rgba(230,126,34,.15); outline:none;
}
@media (max-width:767px) {
    .rpt-tabs-bar { padding:8px 10px 0; }
    .rpt-nav .nav-link { font-size:.7rem; padding:6px 10px; }
    .rpt-filter-item { flex:1 1 140px; max-width:100%; }
    .rpt-card-footer { flex-direction:column; align-items:flex-start; }
}
</style>
@endpush

<div class="rpt-wrapper">
  <div class="rpt-tabs-bar">
    <h5><i class="fa fa-bar-chart"></i> Reportes de Precios y Escalas</h5>
    <ul class="nav rpt-nav" id="rptTabNav" role="tablist">
      <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab-precios" role="tab"><i class="fa fa-tags"></i> Precios por Producto</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-cobertura" role="tab"><i class="fa fa-pie-chart"></i> Cobertura</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-sincat" role="tab"><i class="fa fa-exclamation-triangle tab-alert"></i> Sin Cat. Precio <span class="rpt-badge" id="badge-sincat">—</span></a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-sinprecio" role="tab"><i class="fa fa-minus-circle tab-alert"></i> Productos sin Precios <span class="rpt-badge" id="badge-sinprecio">—</span></a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-comparativo" role="tab"><i class="fa fa-search"></i> Comparativo</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-resumen" role="tab"><i class="fa fa-list-alt"></i> Resumen Cat. Precio</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-comisiones" role="tab"><i class="fa fa-percent"></i> Comisiones Asignadas</a></li>
    </ul>
  </div>

  <div class="tab-content" id="rptTabContent">

    {{-- TAB 1: Precios por Producto --}}
    <div class="tab-pane fade show active rpt-tab-pane" id="tab-precios" role="tabpanel">
      <div class="rpt-card">
        <div class="rpt-card-header">
          <div><p class="rpt-title"><i class="fa fa-tags"></i> Precios por Producto</p><p class="rpt-subtitle">Solo productos con al menos un precio activo configurado.</p></div>
        </div>
        <div class="rpt-card-filters">
          <div class="rpt-filter-item">
            <div class="rpt-filter-label"><i class="fa fa-users"></i> Cat. de Cliente</div>
            <select id="filtro-cat-cliente" class="form-control form-control-sm select2bs4"><option value="">Todas las categorías de cliente</option></select>
          </div>
          <div class="rpt-filter-item">
            <div class="rpt-filter-label"><i class="fa fa-tag"></i> Cat. de Precio</div>
            <select id="filtro-cat-precio" class="form-control form-control-sm select2bs4"><option value="">Todas las categorías de precio</option></select>
          </div>
          <div class="rpt-filter-item">
            <div class="rpt-filter-label"><i class="fa fa-filter"></i> Filtrar por</div>
            <select id="tipoFiltro" class="form-control form-control-sm select2bs4"><option value="">Sin filtro adicional</option><option value="1">Por Marca</option><option value="2">Por Categoría Producto</option></select>
          </div>
          <div class="rpt-filter-item" id="wrapper-lista-filtro" style="display:none;">
            <div class="rpt-filter-label" id="label-lista-filtro"><i class="fa fa-list"></i> Valor</div>
            <select id="listaTipoFiltro" class="form-control form-control-sm select2bs4"><option value="">Seleccione...</option></select>
          </div>
        </div>
        <div class="rpt-card-body">
          <div class="table-responsive">
            <table id="tbl_precios_prod" class="table table-sm table-bordered rpt-table" style="width:100%">
              <thead><tr><th>ID</th><th>Cat. Cliente</th><th>Código</th><th>Producto</th><th>Marca</th><th>Categoría</th><th>Escala</th><th class="text-right">Precio Base</th><th class="text-right">Precio A</th><th class="text-right">Precio B</th><th class="text-right">Precio C</th><th class="text-right">Precio D</th></tr></thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
        <div class="rpt-card-footer">
          <span class="rpt-info"><i class="fa fa-info-circle mr-1"></i>Solo precios activos.</span>
          <button class="btn-rpt-excel" id="btn-export-precios" onclick="exportarPreciosProd()"><i class="fa fa-file-excel-o"></i> Exportar a Excel</button>
        </div>
      </div>
    </div>

    {{-- TAB 2: Cobertura --}}
    <div class="tab-pane fade rpt-tab-pane" id="tab-cobertura" role="tabpanel">
      <div class="rpt-card">
        <div class="rpt-card-header">
          <div><p class="rpt-title"><i class="fa fa-pie-chart"></i> Cobertura por Categoría de Cliente</p><p class="rpt-subtitle">Cuántas categorías de precio y productos activos tiene configurados cada categoría.</p></div>
        </div>
        <div id="stats-cobertura" class="rpt-stats" style="display:none;">
          <div class="rpt-stat-box"><div class="stat-val" id="stat-total-cat">—</div><div class="stat-lbl">Total Categorías</div></div>
          <div class="rpt-stat-box"><div class="stat-val text-danger" id="stat-sin-cobertura">—</div><div class="stat-lbl">Sin cobertura</div></div>
          <div class="rpt-stat-box"><div class="stat-val text-success" id="stat-con-cobertura">—</div><div class="stat-lbl">Con precios</div></div>
          <div class="rpt-stat-box"><div class="stat-val" id="stat-total-prods-cob">—</div><div class="stat-lbl">Productos config.</div></div>
        </div>
        <div class="rpt-card-body">
          <div id="loading-cobertura" class="rpt-loading"><div class="spinner-border text-warning" role="status"></div><span>Cargando...</span></div>
          <div class="table-responsive" id="wrapper-cobertura" style="display:none;">
            <table id="tbl_cobertura" class="table table-sm table-bordered rpt-table" style="width:100%">
              <thead><tr><th>ID</th><th>Categoría Cliente</th><th class="text-center">Estado</th><th class="text-center">Cat. Precio Total</th><th class="text-center">Cat. Activas</th><th class="text-center">Productos</th><th class="text-center">Cobertura</th><th class="text-center">Creación</th></tr></thead>
              <tbody id="tbody-cobertura"></tbody>
            </table>
          </div>
        </div>
        <div class="rpt-card-footer">
          <span class="rpt-info"><i class="fa fa-info-circle mr-1"></i>Categorías con "0" en Cat. Precio no tienen precios configurados.</span>
          <button class="btn-rpt-excel" onclick="exportarCobertura()"><i class="fa fa-file-excel-o"></i> Exportar a Excel</button>
        </div>
      </div>
    </div>

    {{-- TAB 3: Sin categorías de precio --}}
    <div class="tab-pane fade rpt-tab-pane" id="tab-sincat" role="tabpanel">
      <div class="rpt-card">
        <div class="rpt-card-header">
          <div><p class="rpt-title"><i class="fa fa-exclamation-triangle"></i> Categorías de Cliente SIN Categorías de Precio</p><p class="rpt-subtitle">Requieren configuración urgente para poder generar precios de venta.</p></div>
        </div>
        <div class="rpt-card-body">
          <div id="loading-sincat" class="rpt-loading"><div class="spinner-border text-warning" role="status"></div><span>Cargando...</span></div>
          <div class="table-responsive" id="wrapper-sincat" style="display:none;">
            <table id="tbl_sincat" class="table table-sm table-bordered rpt-table" style="width:100%">
              <thead><tr><th>ID</th><th>Categoría Cliente</th><th>Descripción</th><th class="text-center">Estado</th><th class="text-center">Fecha Creación</th></tr></thead>
              <tbody id="tbody-sincat"></tbody>
            </table>
          </div>
          <div id="empty-sincat" class="rpt-empty" style="display:none;"><i class="fa fa-check-circle text-success"></i>¡Excelente! Todas las categorías tienen al menos una categoría de precio.</div>
        </div>
        <div class="rpt-card-footer">
          <span class="rpt-info"><i class="fa fa-info-circle mr-1"></i>Estas categorías no pueden generar precios de venta.</span>
          <button class="btn-rpt-excel" onclick="exportarSinCat()"><i class="fa fa-file-excel-o"></i> Exportar a Excel</button>
        </div>
      </div>
    </div>

    {{-- TAB 4: Productos sin precios --}}
    <div class="tab-pane fade rpt-tab-pane" id="tab-sinprecio" role="tabpanel">
      <div class="rpt-card">
        <div class="rpt-card-header">
          <div><p class="rpt-title"><i class="fa fa-minus-circle"></i> Productos SIN Precios Configurados</p><p class="rpt-subtitle">Productos sin ninguna configuración de precio activa en ninguna categoría.</p></div>
        </div>
        <div class="rpt-card-body">
          <div id="loading-sinprecio" class="rpt-loading"><div class="spinner-border text-warning" role="status"></div><span>Cargando...</span></div>
          <div class="table-responsive" id="wrapper-sinprecio" style="display:none;">
            <table id="tbl_sinprecio" class="table table-sm table-bordered rpt-table" style="width:100%">
              <thead><tr><th>ID</th><th>Código de Barra</th><th>Nombre del Producto</th></tr></thead>
              <tbody id="tbody-sinprecio"></tbody>
            </table>
          </div>
          <div id="empty-sinprecio" class="rpt-empty" style="display:none;"><i class="fa fa-check-circle text-success"></i>¡Todos los productos tienen al menos un precio configurado!</div>
        </div>
        <div class="rpt-card-footer">
          <span class="rpt-info"><i class="fa fa-info-circle mr-1"></i>Incluye todos los productos sin ningún precio activo.</span>
          <button class="btn-rpt-excel" onclick="exportarSinPrecio()"><i class="fa fa-file-excel-o"></i> Exportar a Excel</button>
        </div>
      </div>
    </div>

    {{-- TAB 5: Comparativo --}}
    <div class="tab-pane fade rpt-tab-pane" id="tab-comparativo" role="tabpanel">
      <div class="rpt-card">
        <div class="rpt-card-header">
          <div><p class="rpt-title"><i class="fa fa-search"></i> Comparativo de Precios por Producto</p><p class="rpt-subtitle">Seleccione un producto para ver todos sus precios en cada categoría de cliente.</p></div>
        </div>
        <div class="rpt-card-filters">
          <div class="rpt-filter-item" style="flex:2 1 300px; max-width:500px;"><div class="rpt-filter-label">Buscar producto</div>
            <select id="select-produto-comparativo" class="form-control form-control-sm select2bs4" style="width:100%"><option value="">Escriba para buscar...</option></select>
          </div>
          <div class="rpt-filter-item flex-none"><div class="rpt-filter-label">&nbsp;</div>
            <button class="btn-rpt-primary" onclick="cargarComparativo()"><i class="fa fa-search"></i> Buscar</button>
          </div>
        </div>
        <div class="rpt-card-body">
          <div id="placeholder-comparativo" class="rpt-empty"><i class="fa fa-search"></i>Seleccione un producto arriba para ver su comparativo de precios.</div>
          <div id="loading-comparativo" class="rpt-loading" style="display:none;"><div class="spinner-border text-warning" role="status"></div><span>Cargando comparativo...</span></div>
          <div class="table-responsive" id="wrapper-comparativo" style="display:none;">
            <table id="tbl_comparativo" class="table table-sm table-bordered rpt-table" style="width:100%">
              <thead><tr><th>Cat. Cliente</th><th>Cat. Precio</th><th class="text-center">% A</th><th class="text-center">% B</th><th class="text-center">% C</th><th class="text-center">% D</th><th class="text-right">Precio Base</th><th class="text-right">Precio A</th><th class="text-right">Precio B</th><th class="text-right">Precio C</th><th class="text-right">Precio D</th><th class="text-center">Estado</th></tr></thead>
              <tbody id="tbody-comparativo"></tbody>
            </table>
          </div>
        </div>
        <div class="rpt-card-footer">
          <span class="rpt-info" id="info-comparativo"><i class="fa fa-info-circle mr-1"></i>Seleccione un producto para habilitar la exportación.</span>
          <button class="btn-rpt-excel" id="btn-export-comparativo" onclick="exportarComparativo()" disabled><i class="fa fa-file-excel-o"></i> Exportar a Excel</button>
        </div>
      </div>
    </div>

    {{-- TAB 6: Resumen --}}
    <div class="tab-pane fade rpt-tab-pane" id="tab-resumen" role="tabpanel">
      <div class="rpt-card">
        <div class="rpt-card-header">
          <div><p class="rpt-title"><i class="fa fa-list-alt"></i> Resumen de Categorías de Precio</p><p class="rpt-subtitle">Todas las categorías de precio con porcentajes y cantidad de productos configurados.</p></div>
        </div>
        <div class="rpt-card-filters">
          <div class="rpt-filter-item"><div class="rpt-filter-label">Categoría de cliente</div>
            <select id="filtro-resumen-cat-cliente" class="form-control form-control-sm select2bs4"><option value="">Todas</option></select>
          </div>
          <div class="rpt-filter-item"><div class="rpt-filter-label">Estado</div>
            <select id="filtro-resumen-estado" class="form-control form-control-sm"><option value="">Todos</option><option value="1">Activos</option><option value="0">Inactivos</option></select>
          </div>
          <div class="rpt-filter-item flex-none"><div class="rpt-filter-label">&nbsp;</div>
            <button class="btn-rpt-primary" onclick="cargarResumen()"><i class="fa fa-refresh"></i> Actualizar</button>
          </div>
        </div>
        <div class="rpt-card-body">
          <div id="loading-resumen" class="rpt-loading"><div class="spinner-border text-warning" role="status"></div><span>Cargando...</span></div>
          <div class="table-responsive" id="wrapper-resumen" style="display:none;">
            <table id="tbl_resumen" class="table table-sm table-bordered rpt-table" style="width:100%">
              <thead><tr><th>ID</th><th>Categoría Precio</th><th>Cat. Cliente</th><th class="text-center">% A</th><th class="text-center">% B</th><th class="text-center">% C</th><th class="text-center">% D</th><th class="text-center">Estado</th><th class="text-center">Productos</th><th class="text-center">Últ. Actualización</th></tr></thead>
              <tbody id="tbody-resumen"></tbody>
            </table>
          </div>
        </div>
        <div class="rpt-card-footer">
          <span class="rpt-info"><i class="fa fa-info-circle mr-1"></i>Incluye activas e inactivas según filtro.</span>
          <button class="btn-rpt-excel" onclick="exportarResumen()"><i class="fa fa-file-excel-o"></i> Exportar a Excel</button>
        </div>
      </div>
    </div>

    {{-- TAB 7: Comisiones Asignadas --}}
    <div class="tab-pane fade rpt-tab-pane" id="tab-comisiones" role="tabpanel">
      <div class="rpt-card">
        <div class="rpt-card-header">
          <div><p class="rpt-title"><i class="fa fa-percent"></i> Comisiones Asignadas por Categoría de Precio</p><p class="rpt-subtitle">Categorías de precio que tienen porcentajes de comisión configurados por rol.</p></div>
        </div>
        <div class="rpt-card-filters">
          <div class="rpt-filter-item">
            <div class="rpt-filter-label"><i class="fa fa-users"></i> Cat. de Cliente</div>
            <select id="filtro-comision-cat-cliente" class="form-control form-control-sm select2bs4"><option value="">Todas las categorías</option></select>
          </div>
          <div class="rpt-filter-item">
            <div class="rpt-filter-label"><i class="fa fa-user-circle"></i> Rol</div>
            <select id="filtro-comision-rol" class="form-control form-control-sm select2bs4"><option value="">Todos los roles</option></select>
          </div>
          <div class="rpt-filter-item">
            <div class="rpt-filter-label"><i class="fa fa-toggle-on"></i> Estado</div>
            <select id="filtro-comision-estado" class="form-control form-control-sm"><option value="">Todos</option><option value="1">Activos</option><option value="2">Inactivos</option></select>
          </div>
          <div class="rpt-filter-item flex-none"><div class="rpt-filter-label">&nbsp;</div>
            <button class="btn-rpt-primary" onclick="recargarComisiones()"><i class="fa fa-refresh"></i> Aplicar filtros</button>
          </div>
        </div>
        <div id="stats-comisiones" class="rpt-stats" style="display:none;">
          <div class="rpt-stat-box"><div class="stat-val" id="stat-com-total">—</div><div class="stat-lbl">Total Registros</div></div>
          <div class="rpt-stat-box"><div class="stat-val text-success" id="stat-com-activos">—</div><div class="stat-lbl">Activos</div></div>
          <div class="rpt-stat-box"><div class="stat-val" id="stat-com-roles">—</div><div class="stat-lbl">Roles involucrados</div></div>
          <div class="rpt-stat-box"><div class="stat-val text-warning" id="stat-com-prom">—</div><div class="stat-lbl">% Prom. comisión</div></div>
        </div>
        <div class="rpt-card-body">
          <div id="loading-comisiones" class="rpt-loading"><div class="spinner-border text-warning" role="status"></div><span>Cargando...</span></div>
          <div class="table-responsive" id="wrapper-comisiones" style="display:none;">
            <table id="tbl_comisiones" class="table table-sm table-bordered rpt-table" style="width:100%">
              <thead><tr>
                <th>ID</th><th>Cat. Cliente</th><th>Cat. Precio</th><th>Rol</th>
                <th class="text-center">% Comisión</th>
                <th class="text-center">% Esc. A</th><th class="text-center">% Esc. B</th>
                <th class="text-center">% Esc. C</th><th class="text-center">% Esc. D</th>
                <th class="text-center">Estado</th>
              </tr></thead>
              <tbody id="tbody-comisiones"></tbody>
            </table>
          </div>
        </div>
        <div class="rpt-card-footer">
          <span class="rpt-info"><i class="fa fa-info-circle mr-1"></i>Comisiones configuradas en el módulo de Escalas.</span>
        </div>
      </div>
    </div>

  </div>
</div>

@push('scripts')
<script src="{{ asset('js/js_proyecto/Escalas/reporteEscalas.js') }}?v=20260602"></script>
@endpush
