<div>
    @push("styles")
    <style>
        /* =============================================
           CATÁLOGO DE PRODUCTOS — ESTILOS MODERNOS
        ============================================= */

        /* ── Page header ── */
        .prod-page-header {
            background: linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
            padding: 20px 28px 18px;
            border-bottom: 3px solid rgba(255,255,255,.25);
            margin-bottom: 0;
        }
        .prod-page-header h2 {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0 0 4px;
            letter-spacing: .5px;
        }
        .prod-page-header .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
            font-size: .82rem;
        }
        .prod-page-header .breadcrumb-item a,
        .prod-page-header .breadcrumb-item.active {
            color: rgba(255,255,255,.65);
        }
        .prod-page-header .breadcrumb-item a:hover { color: #fff; text-decoration: none; }
        .prod-page-header .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,.4); }

        /* ── Quick action bar ── */
        .prod-action-bar {
            background: #fff;
            border-bottom: 1px solid #e8ecef;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .prod-action-bar .btn-register {
            background: linear-gradient(135deg, #f39c12, #e05a00);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: .85rem;
            transition: all .2s;
            box-shadow: 0 3px 10px rgba(243,156,18,.3);
        }
        .prod-action-bar .btn-register:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(243,156,18,.45);
            color: #fff;
        }
        .prod-action-bar .btn-excel {
            background: linear-gradient(135deg, #27ae60, #1e8449);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: .85rem;
            transition: all .2s;
            box-shadow: 0 3px 10px rgba(39,174,96,.25);
        }
        .prod-action-bar .btn-excel:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(39,174,96,.4);
            color: #fff;
        }

        /* ── Filter card ── */
        .prod-filter-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            margin: 20px 24px 0;
            overflow: hidden;
        }
        .prod-filter-card .filter-header {
            background: linear-gradient(135deg, #f39c12, #e05a00);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
        }
        .prod-filter-card .filter-header span {
            color: #fff;
            font-weight: 600;
            font-size: .9rem;
        }
        .prod-filter-card .filter-header i { color: rgba(255,255,255,.8); }
        .prod-filter-body { padding: 18px 20px 14px; }
        .prod-filter-body label {
            font-size: .78rem;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 4px;
        }
        .prod-filter-body .form-control {
            border-radius: 8px;
            border: 1.5px solid #e0e6ed;
            font-size: .875rem;
            height: 36px;
            transition: border-color .2s, box-shadow .2s;
        }
        .prod-filter-body .form-control:focus {
            border-color: #e05a00;
            box-shadow: 0 0 0 3px rgba(224,90,0,.12);
        }
        .prod-filter-actions { display: flex; gap: 8px; margin-top: 14px; }
        .btn-filter-apply {
            background: linear-gradient(135deg, #f39c12, #e05a00);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 7px 20px;
            font-size: .84rem;
            font-weight: 600;
            transition: all .2s;
        }
        .btn-filter-apply:hover { background: linear-gradient(135deg, #e08e0b, #c04e00); color: #fff; }
        .btn-filter-clear {
            background: #f0f2f5;
            color: #555;
            border: none;
            border-radius: 8px;
            padding: 7px 16px;
            font-size: .84rem;
            font-weight: 600;
            transition: all .2s;
        }
        .btn-filter-clear:hover { background: #e0e6ed; color: #333; }

        /* ── Table card ── */
        .prod-table-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            margin: 16px 24px 24px;
            overflow: hidden;
        }
        .prod-table-card .table-header {
            background: linear-gradient(135deg, #f39c12, #e05a00);
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .prod-table-card .table-header span {
            color: #fff;
            font-weight: 700;
            font-size: .95rem;
        }
        #tbl_productosListar thead th {
            background: #fdf4e7;
            border-bottom: 2px solid #f2d49a;
            color: #7d3f00;
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 10px 12px;
            white-space: nowrap;
        }
        #tbl_productosListar tbody tr {
            transition: background .15s;
        }
        #tbl_productosListar tbody tr:hover { background: #fffcf5; }
        #tbl_productosListar tbody td {
            vertical-align: middle;
            font-size: .875rem;
            padding: 10px 12px;
            border-color: #f0f2f5;
        }
        .badge-isv-exento  { background:#d5f5e3; color:#1e8449; border-radius:20px; padding:3px 9px; font-size:.75rem; font-weight:700; }
        .badge-isv-15      { background:#fef9e7; color:#d35400; border-radius:20px; padding:3px 9px; font-size:.75rem; font-weight:700; }
        .badge-isv-18      { background:#fdecea; color:#c0392b; border-radius:20px; padding:3px 9px; font-size:.75rem; font-weight:700; }
        .badge-activo   { background:#d5f5e3; color:#1e8449; border-radius:20px; padding:3px 10px; font-size:.75rem; font-weight:700; }
        .badge-inactivo { background:#fdecea; color:#c0392b; border-radius:20px; padding:3px 10px; font-size:.75rem; font-weight:700; }
        .prod-dropdown .btn-prod-menu {
            background: none; border: none; color: #7d3f00;
            font-size: 1rem; padding: 4px 10px; border-radius: 6px;
            cursor: pointer; transition: background .2s;
        }
        .prod-dropdown .btn-prod-menu:hover { background: #fdf4e7; }
        .stock-num { font-weight: 700; color: #7d3f00; }
        .btn-ver-mas {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 5px 14px;
            font-size: .78rem;
            font-weight: 600;
            white-space: nowrap;
            transition: all .2s;
            box-shadow: 0 2px 6px rgba(243,156,18,.3);
        }
        .btn-ver-mas:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(243,156,18,.45);
            color: #fff;
        }

        /* ── Tabs de modales ── */
        .prod-modal-tabs { border-bottom: 2px solid #e8d5bf; margin-bottom: 16px; }
        .prod-modal-tabs .nav-item { margin-bottom: -2px; }
        .prod-modal-tabs .nav-link {
            color: #7d3f00; font-weight: 600; font-size:.82rem; padding:8px 14px;
            border: 2px solid transparent; border-radius: 8px 8px 0 0;
            transition: all .2s;
        }
        .prod-modal-tabs .nav-link:hover { background:#fdf4e7; color:#e05a00; }
        .prod-modal-tabs .nav-link.active {
            background: linear-gradient(135deg,#f39c12,#e05a00);
            color:#fff !important; border-color: #e05a00 #e05a00 #fff;
        }
        .prod-modal-tabs .nav-link i { margin-right:5px; }
        /* ── Modal moderno ── */
        #modal_producto_crear .modal-content {
            border: none;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
        }
        #modal_producto_crear .modal-header {
            background: linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
            border: none;
            padding: 20px 24px;
        }
        #modal_producto_crear .modal-title { color: #fff; font-weight: 700; font-size: 1.1rem; }
        #modal_producto_crear .close { color: rgba(255,255,255,.8); opacity: 1; font-size: 1.4rem; }
        #modal_producto_crear .close:hover { color: #fff; }
        #modal_producto_crear .modal-body { padding: 20px 24px 8px; background: #f8fafc; }
        #modal_producto_crear .modal-footer {
            background: #fff;
            border-top: 1px solid #e8ecef;
            padding: 14px 24px;
        }

        /* Secciones del form */
        .form-section {
            background: #fff;
            border-radius: 10px;
            padding: 18px 20px 12px;
            margin-bottom: 16px;
            border: 1px solid #e8ecef;
        }
        .form-section-title {
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #7d3f00;
            border-bottom: 2px solid #e8d5bf;
            padding-bottom: 8px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-section-title i { font-size: 1rem; }
        .form-section label {
            font-size: .8rem;
            font-weight: 600;
            color: #555;
            margin-bottom: 4px;
        }
        .form-section .form-control {
            border-radius: 8px;
            border: 1.5px solid #e0e6ed;
            font-size: .875rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-section .form-control:focus {
            border-color: #e05a00;
            box-shadow: 0 0 0 3px rgba(224,90,0,.12);
        }
        .form-section .form-control:disabled {
            background: #f8fafc;
            color: #999;
        }
        .price-input-group { position: relative; }
        .price-input-group .currency-prefix {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            font-size: .82rem;
            font-weight: 700;
            pointer-events: none;
            z-index: 4;
        }
        .price-input-group .form-control { padding-left: 28px; }
        .price-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f39c12, #e05a00);
            color: #fff;
            border-radius: 4px;
            padding: 2px 7px;
            font-size: .72rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        /* imagen preview */
        #imagenPrevisualizacion {
            max-width: 100%;
            max-height: 160px;
            border-radius: 10px;
            object-fit: contain;
            border: 2px dashed #e0e6ed;
            padding: 6px;
            display: block;
        }
        .foto-upload-area {
            border: 2px dashed #ccd3db;
            border-radius: 10px;
            padding: 14px;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }
        .foto-upload-area:hover { border-color: #e05a00; background: #fdf4e7; }
        .foto-upload-area i { font-size: 1.6rem; color: #aaa; display: block; margin-bottom: 4px; }
        .foto-upload-area span { font-size: .8rem; color: #888; }

        /* Spinner overlay */
        #modalSpinnerLoading .modal-content { background: transparent; border: none; box-shadow: none; }
        .spinner-overlay-box {
            background: rgba(255,255,255,.97);
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 15px 50px rgba(0,0,0,.2);
        }
        .spinner-ring {
            display: inline-block;
            width: 52px;
            height: 52px;
            border: 5px solid #e8d5bf;
            border-top-color: #e05a00;
            border-radius: 50%;
            animation: spin .8s linear infinite;
            margin-bottom: 16px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner-overlay-box p {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: #7d3f00;
        }
        .spinner-overlay-box small { color: #888; font-size: .8rem; }

        /* Responsive */
        @media (max-width: 767px) {
            .prod-filter-card,
            .prod-table-card { margin-left: 12px; margin-right: 12px; }
            .prod-action-bar { padding: 10px 14px; }
        }
        @media (max-width: 500px) {
            .ancho-imagen { max-width: 200px; }
        }
    </style>
    @endpush
    {{-- ══ PAGE HEADER ══════════════════════════════════════════════ --}}
    <div class="prod-page-header">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                    <h2><i class="fa fa-cube mr-2" style="color:rgba(255,255,255,.85);"></i> Catálogo de Productos</h2>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><i class="fa fa-home mr-1"></i> Inventario</li>
                    <li class="breadcrumb-item active">Catálogo de Productos</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- ══ ACTION BAR ═══════════════════════════════════════════════ --}}
    <div class="prod-action-bar">
        @if (Auth::user()->rol_id == '1')
        <button class="btn-register" data-toggle="modal" data-target="#modal_producto_crear">
            <i class="fa fa-plus mr-1"></i> Nuevo Producto
        </button>
        @endif
        @if (Auth::user()->rol_id == '1' || Auth::user()->rol_id == '7')
        <button class="btn-excel" onclick="exportarExcel()">
            <i class="fa fa-file-excel-o mr-1"></i> Exportar Excel
        </button>
        @endif
        <span style="margin-left:auto; font-size:.78rem; color:#888;">
            <i class="fa fa-info-circle mr-1"></i> Haga clic en <b>Ver más</b> para ver el detalle de un producto
        </span>
    </div>

    {{-- ══ FILTROS ══════════════════════════════════════════════════ --}}
    <div class="prod-filter-card">
        <div class="filter-header" onclick="toggleFiltros()">
            <span><i class="fa fa-filter mr-2"></i> Filtros de búsqueda</span>
            <i class="fa fa-chevron-down" id="ico-filtros"></i>
        </div>
        <div class="prod-filter-body" id="filtros-body">
            <div class="row">
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <label>Nombre / ID / Cód. Barra</label>
                    <input type="text" id="fprod_q" class="form-control"
                           placeholder="Ej: bolsa concept…"
                           onkeydown="if(event.key==='Enter') aplicarFiltros()">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <label>Descripción</label>
                    <input type="text" id="fprod_descripcion" class="form-control"
                           placeholder="Buscar en descripción…"
                           onkeydown="if(event.key==='Enter') aplicarFiltros()">
                </div>
                <div class="col-12 col-sm-6 col-md-2 col-lg-2">
                    <label>ISV</label>
                    <select id="fprod_isv" class="form-control">
                        <option value="">Todos</option>
                        <option value="con">Con ISV</option>
                        <option value="0">Exento</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <label>Categoría</label>
                    <select id="fprod_categoria" class="form-control">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <label>Marca</label>
                    <select id="fprod_marca" class="form-control">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <label>Estado</label>
                    <select id="fprod_estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="1">Activo</option>
                        <option value="2">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="prod-filter-actions">
                <button class="btn-filter-apply" onclick="aplicarFiltros()">
                    <i class="fa fa-search mr-1"></i> Buscar
                </button>
                <button class="btn-filter-clear" onclick="limpiarFiltros()">
                    <i class="fa fa-times mr-1"></i> Limpiar
                </button>
            </div>
        </div>
    </div>

    {{-- ══ TABLA ════════════════════════════════════════════════════ --}}
    <div class="prod-table-card">
        <div class="table-header">
            <span><i class="fa fa-list mr-2"></i> Listado de Productos</span>
        </div>
        <div style="padding: 16px 16px 8px;">
            <div class="table-responsive">
                <table id="tbl_productosListar" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:60px;">ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th style="width:110px;">Cód. Barra</th>
                            <th style="width:80px; text-align:center;">ISV</th>
                            <th style="width:130px;">Categoría</th>
                            <th style="width:90px; text-align:center;">Existencia</th>
                            <th style="width:90px; text-align:center;">Estado</th>
                            <th style="width:90px; text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ══ MODAL: CREAR PRODUCTO ════════════════════════════════════ --}}
    <div class="modal fade" id="modal_producto_crear" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-plus-circle mr-2"></i> Registro de Nuevo Producto
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" style="padding:20px 24px 8px;">
                    <form id="crearProductoForm" name="crearProductoForm" data-parsley-validate>

                        {{-- PESTAÑAS --}}
                        <ul class="nav prod-modal-tabs" id="tabsCrear" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tab-crear-general">
                                    <i class="fa fa-info-circle"></i> General
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-crear-precios">
                                    <i class="fa fa-dollar"></i> Precios
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-crear-clasif">
                                    <i class="fa fa-tag"></i> Clasificación
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">

                            {{-- Tab 1: General --}}
                            <div class="tab-pane fade show active" id="tab-crear-general">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label>Nombre del producto <span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="nombre_producto"
                                            name="nombre_producto" placeholder="Ej: Bolsa de polietileno 10x15" data-parsley-required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label>Descripción <span class="text-danger">*</span></label>
                                        <textarea placeholder="Descripción detallada del producto…" required
                                            id="descripcion_producto" name="descripcion_producto" rows="3"
                                            class="form-control" data-parsley-required></textarea>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>ISV <span class="text-danger">*</span></label>
                                        <select class="form-control" name="isv_producto" id="isv_producto" data-parsley-required>
                                            <option value="0">Exento de impuestos</option>
                                            <option value="15" selected>15% de ISV</option>
                                            <option value="18">18% de ISV</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Código de barra</label>
                                        <input class="form-control" type="number" name="cod_barra_producto"
                                            id="cod_barra_producto" min="0" placeholder="Opcional">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Código estatal</label>
                                        <input class="form-control" type="number" name="cod_estatal_producto"
                                            id="cod_estatal_producto" min="0" placeholder="Opcional">
                                    </div>
                                    {{-- Foto --}}
                                    <div class="col-md-5 mb-2">
                                        <label for="foto_producto" class="foto-upload-area w-100" style="cursor:pointer; margin:0;">
                                            <i class="fa fa-cloud-upload"></i>
                                            <span>Clic para seleccionar imágenes<br><small style="color:#aaa;">(PNG, JPG, GIF)</small></span>
                                            <input type="file" id="foto_producto" name="foto_producto"
                                                accept="image/png,image/gif,image/jpeg" multiple style="display:none;">
                                        </label>
                                    </div>
                                    <div class="col-md-7 mb-2 text-center">
                                        <img id="imagenPrevisualizacion" src="" alt="Vista previa"
                                             style="max-width:100%; max-height:120px; border-radius:10px; object-fit:contain; border:2px dashed #e0e6ed; padding:6px; display:none;">
                                        <div id="preview-placeholder" style="color:#ccc; font-size:.85rem; padding:20px 0;">
                                            <i class="fa fa-image" style="font-size:2rem; display:block; margin-bottom:6px;"></i>
                                            Vista previa aquí
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 2: Precios --}}
                            <div class="tab-pane fade" id="tab-crear-precios">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label>Precio base <span class="text-danger">*</span></label>
                                        <div class="price-input-group">
                                            <span class="currency-prefix">L.</span>
                                            <input class="form-control" min="0" type="number" name="precioBase" id="precioBase"
                                                data-parsley-required step="any" onchange="validacionPrecio()" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Costo promedio <span class="text-danger">*</span></label>
                                        <div class="price-input-group">
                                            <span class="currency-prefix">L.</span>
                                            <input class="form-control" min="0" type="number" name="costo_promedio"
                                                id="costo_promedio" data-parsley-required step="any" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Último costo de compra <span class="text-danger">*</span></label>
                                        <div class="price-input-group">
                                            <span class="currency-prefix">L.</span>
                                            <input class="form-control" min="0" type="number" name="ultimo_costo_compra"
                                                id="ultimo_costo_compra" data-parsley-required step="any" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label><span class="price-badge">A</span> Precio A</label>
                                        <div class="price-input-group">
                                            <span class="currency-prefix">L.</span>
                                            <input class="form-control" type="number" name="precio1" id="precio1" step="any" disabled placeholder="Auto">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label><span class="price-badge">B</span> Precio B</label>
                                        <div class="price-input-group">
                                            <span class="currency-prefix">L.</span>
                                            <input class="form-control" type="number" name="precio2" id="precio2" step="any" disabled placeholder="Auto">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label><span class="price-badge">C</span> Precio C</label>
                                        <div class="price-input-group">
                                            <span class="currency-prefix">L.</span>
                                            <input class="form-control" type="number" name="precio3" id="precio3" step="any" disabled placeholder="Auto">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label><span class="price-badge">D</span> Precio D</label>
                                        <div class="price-input-group">
                                            <span class="currency-prefix">L.</span>
                                            <input class="form-control" type="number" name="precio4" id="precio4" step="any" disabled placeholder="Auto">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 3: Clasificación --}}
                            <div class="tab-pane fade" id="tab-crear-clasif">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label>Marca <span class="text-danger">*</span></label>
                                        <select class="form-control" name="marca_producto" id="marca_producto" data-parsley-required>
                                            <option selected disabled>— Seleccione una marca —</option>
                                            @foreach ($marcas as $marca)
                                            <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Categoría <span class="text-danger">*</span></label>
                                        <select class="form-control" name="categoria_producto" id="categoria_producto"
                                            data-parsley-required onchange="listarSubCategorias()">
                                            <option selected disabled>— Seleccione una categoría —</option>
                                            @foreach ($categorias as $categoria)
                                            <option value="{{ $categoria->id }}">{{ $categoria->descripcion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Subcategoría <span class="text-danger">*</span></label>
                                        <select class="form-control" name="sub_categoria_producto" id="sub_categoria_producto" data-parsley-required>
                                            <option selected disabled>— Seleccione una subcategoría —</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Unidad para compra <span class="text-danger">*</span></label>
                                        <select class="form-control" name="unidad_producto" id="unidad_producto" data-parsley-required>
                                            <option selected disabled>— Seleccione —</option>
                                            @foreach ($unidades as $unidad)
                                            <option value="{{ $unidad->id }}">{{ $unidad->nombre }} — {{ $unidad->simbolo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Cantidad unidades compra <span class="text-danger">*</span></label>
                                        <input class="form-control" min="1" type="number" name="unidades"
                                            id="unidades" step="any" required placeholder="Ej: 1">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Unidad para venta <span class="text-danger">*</span></label>
                                        <select class="form-control" name="unidad_producto_venta" id="unidad_producto_venta" data-parsley-required>
                                            <option selected disabled>— Seleccione —</option>
                                            @foreach ($unidades as $unidad)
                                            <option value="{{ $unidad->id }}">{{ $unidad->nombre }} — {{ $unidad->simbolo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Cantidad unidades venta <span class="text-danger">*</span></label>
                                        <input class="form-control" min="1" type="number" name="unidades_venta"
                                            id="unidades_venta" step="any" required placeholder="Ej: 1">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Tiempo de recuperación en meses</label>
                                        <input class="form-control" type="number" min="1" max="999"
                                            name="tiempo_recuperacion_meses" id="tiempo_recuperacion_meses"
                                            placeholder="Ej: 3">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Origen</label>
                                        <input class="form-control" type="text" maxlength="200"
                                            name="origen" id="origen"
                                            placeholder="Ej: China, Honduras...">
                                    </div>
                                </div>
                            </div>

                        </div>{{-- /tab-content --}}

                    </form>
                </div>

                <div class="modal-footer" style="justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" form="crearProductoForm" class="btn btn-primary" style="border-radius:8px; padding:8px 22px; font-weight:600;">
                        <i class="fa fa-save mr-1"></i> Guardar Producto
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- ══ MODAL: EDITAR PRODUCTO ══════════════════════════════════ --}}
    <div class="modal fade" id="modal_producto_editar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header" style="background:linear-gradient(135deg,#f39c12 0%,#e05a00 100%);">
                    <h5 class="modal-title" style="color:#fff; font-weight:700;">
                        <i class="fa fa-edit mr-2"></i> Editar Producto
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" style="color:rgba(255,255,255,.8); opacity:1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" style="background:#f8fafc; padding:20px 24px 8px;">
                    <form id="editarProductoForm" data-parsley-validate>
                        <input type="hidden" id="id_producto_edit" name="id_producto_edit">

                        {{-- PESTAÑAS --}}
                        <ul class="nav prod-modal-tabs" id="tabsEditar" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tab-edit-general">
                                    <i class="fa fa-info-circle"></i> General
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-edit-precios">
                                    <i class="fa fa-dollar"></i> Precios
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-edit-clasif">
                                    <i class="fa fa-tag"></i> Clasificación
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">

                            {{-- Tab 1: General --}}
                            <div class="tab-pane fade show active" id="tab-edit-general">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label>Nombre del producto <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" id="nombre_producto_edit"
                                            name="nombre_producto_edit" data-parsley-required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label>Descripción <span class="text-danger">*</span></label>
                                        <textarea id="descripcion_producto_edit" name="descripcion_producto_edit"
                                            rows="3" class="form-control" data-parsley-required></textarea>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>ISV <span class="text-danger">*</span></label>
                                        <select class="form-control" name="isv_producto_edit" id="isv_producto_edit">
                                            <option value="0">Exento de impuestos</option>
                                            <option value="15">15% de ISV</option>
                                            <option value="18">18% de ISV</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Código de barra</label>
                                        <input class="form-control" type="number" name="cod_barra_producto_edit"
                                            id="cod_barra_producto_edit" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Código estatal</label>
                                        <input class="form-control" type="number" name="cod_estatal_producto_edit"
                                            id="cod_estatal_producto_edit" min="0">
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 2: Precios --}}
                            <div class="tab-pane fade" id="tab-edit-precios">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label>Precio base <span class="text-danger">*</span></label>
                                        <div class="price-input-group">
                                            <span class="currency-prefix">L.</span>
                                            <input class="form-control" type="number" min="0" step="any"
                                                name="precioBase_edit" id="precioBase_edit" data-parsley-required
                                                onchange="validacionPrecioEdit()">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Costo promedio <span class="text-danger">*</span></label>
                                        <div class="price-input-group">
                                            <span class="currency-prefix">L.</span>
                                            <input class="form-control" type="number" min="0" step="any"
                                                name="costo_promedio_editar" id="costo_promedio_edit" data-parsley-required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Último costo de compra <span class="text-danger">*</span></label>
                                        <div class="price-input-group">
                                            <span class="currency-prefix">L.</span>
                                            <input class="form-control" type="number" min="0" step="any"
                                                name="ultimo_costo_compra_editar" id="ultimo_costo_compra_edit" data-parsley-required>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label><span class="price-badge">A</span> Precio A</label>
                                        <div class="price-input-group">
                                            <span class="currency-prefix">L.</span>
                                            <input class="form-control" type="number" name="precio1" id="precio1_edit" step="any">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label><span class="price-badge">B</span> Precio B</label>
                                        <div class="price-input-group">
                                            <span class="currency-prefix">L.</span>
                                            <input class="form-control" type="number" name="precio2" id="precio2_edit" step="any">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label><span class="price-badge">C</span> Precio C</label>
                                        <div class="price-input-group">
                                            <span class="currency-prefix">L.</span>
                                            <input class="form-control" type="number" name="precio3" id="precio3_edit" step="any">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label><span class="price-badge">D</span> Precio D</label>
                                        <div class="price-input-group">
                                            <span class="currency-prefix">L.</span>
                                            <input class="form-control" type="number" name="precio4" id="precio4_edit" step="any">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 3: Clasificación --}}
                            <div class="tab-pane fade" id="tab-edit-clasif">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label>Marca <span class="text-danger">*</span></label>
                                        <select class="form-control" name="marca_producto_editar" id="marca_producto_editar" data-parsley-required>
                                            <option selected disabled>— Seleccione —</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Categoría <span class="text-danger">*</span></label>
                                        <select class="form-control" id="categoria_producto_edit"
                                            onchange="listarSubCategoriasEdit()">
                                            <option selected disabled>— Seleccione —</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Subcategoría <span class="text-danger">*</span></label>
                                        <select class="form-control" name="sub_categoria_producto_edit"
                                            id="sub_categoria_producto_edit" data-parsley-required>
                                            <option selected disabled>— Seleccione —</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Unidad para compra <span class="text-danger">*</span></label>
                                        <select class="form-control" name="unidad_producto_editar"
                                            id="unidad_producto_editar" data-parsley-required>
                                            <option selected disabled>— Seleccione —</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Cantidad unidades compra <span class="text-danger">*</span></label>
                                        <input class="form-control" type="number" min="1" step="any"
                                            name="unidades_editar" id="unidades_editar" data-parsley-required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Tiempo de recuperación en meses</label>
                                        <input class="form-control" type="number" min="1" max="999"
                                            name="tiempo_recuperacion_meses_edit" id="tiempo_recuperacion_meses_edit"
                                            placeholder="Ej: 3">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Origen</label>
                                        <input class="form-control" type="text" maxlength="200"
                                            name="origen_edit" id="origen_edit"
                                            placeholder="Ej: China, Honduras...">
                                    </div>
                                </div>
                            </div>

                        </div>{{-- /tab-content --}}

                    </form>
                </div>

                <div class="modal-footer" style="justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="button" onclick="guardarEdicionProducto()" class="btn btn-warning" style="border-radius:8px; padding:8px 22px; font-weight:600; color:#fff;">
                        <i class="fa fa-save mr-1"></i> Guardar Cambios
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- ══ MODAL: SPINNER ══════════════════════════════════════════ --}}
    <div class="modal" id="modalSpinnerLoading" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:320px;">
            <div class="modal-content">
                <div class="modal-body" style="padding:0;">
                    <div class="spinner-overlay-box">
                        <div class="spinner-ring"></div>
                        <p>Procesando...</p>
                        <small>Por favor espere un momento</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Toggle panel de filtros
        function toggleFiltros() {
            var body = document.getElementById('filtros-body');
            var ico  = document.getElementById('ico-filtros');
            if (body.style.display === 'none') {
                body.style.display = '';
                ico.classList.replace('fa-chevron-right', 'fa-chevron-down');
            } else {
                body.style.display = 'none';
                ico.classList.replace('fa-chevron-down', 'fa-chevron-right');
            }
        }

        // Vista previa de imagen con manejo del placeholder
        document.addEventListener('DOMContentLoaded', function() {
            var inputFoto = document.getElementById('foto_producto');
            if (inputFoto) {
                inputFoto.addEventListener('change', function() {
                    var archivos = this.files;
                    var img  = document.getElementById('imagenPrevisualizacion');
                    var ph   = document.getElementById('preview-placeholder');
                    if (!archivos || !archivos.length) {
                        img.style.display = 'none';
                        img.src = '';
                        ph.style.display = '';
                        return;
                    }
                    var objectURL = URL.createObjectURL(archivos[0]);
                    img.src = objectURL;
                    img.style.display = 'block';
                    ph.style.display  = 'none';
                });
            }
        });
    </script>
    <script src="{{ asset('js/js_proyecto/inventario/producto.js') }}"></script>
    @endpush
</div>

