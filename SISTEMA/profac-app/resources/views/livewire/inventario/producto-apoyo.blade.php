<div>
    @push("styles")
    <style>
        /* ── Page header ── */
        .prod-page-header {
            background: linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
            padding: 20px 28px 18px;
            border-bottom: 3px solid rgba(255,255,255,.25);
            margin-bottom: 0;
        }
        .prod-page-header h2 { color: #fff; font-size: 1.6rem; font-weight: 700; margin: 0 0 4px; }
        .prod-page-header .breadcrumb { background: transparent; padding: 0; margin: 0; font-size: .82rem; }
        .prod-page-header .breadcrumb-item a,
        .prod-page-header .breadcrumb-item.active { color: rgba(255,255,255,.65); }
        .prod-page-header .breadcrumb-item a:hover { color: #fff; text-decoration: none; }
        .prod-page-header .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,.4); }
        /* ── Action bar ── */
        .prod-action-bar {
            background: #fff; border-bottom: 1px solid #e8ecef;
            padding: 12px 24px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        }
        .btn-register {
            background: linear-gradient(135deg, #f39c12, #e05a00); color: #fff; border: none;
            border-radius: 8px; padding: 8px 18px; font-weight: 600; font-size: .85rem;
            transition: all .2s; box-shadow: 0 3px 10px rgba(243,156,18,.3); cursor: pointer;
        }
        .btn-register:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(243,156,18,.45); color: #fff; }
        /* ── Filter card ── */
        .prod-filter-card {
            background: #fff; border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08); margin: 20px 24px 0; overflow: hidden;
        }
        .prod-filter-card .filter-header {
            background: linear-gradient(135deg, #f39c12, #e05a00);
            padding: 12px 20px; display: flex; align-items: center;
            justify-content: space-between; cursor: pointer; user-select: none;
        }
        .prod-filter-card .filter-header span { color: #fff; font-weight: 600; font-size: .9rem; }
        .prod-filter-card .filter-header i { color: rgba(255,255,255,.8); }
        .prod-filter-body { padding: 18px 20px 14px; }
        .prod-filter-body label { font-size: .78rem; font-weight: 600; color: #555; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
        .prod-filter-body .form-control { border-radius: 8px; border: 1.5px solid #e0e6ed; font-size: .875rem; height: 36px; }
        .prod-filter-body .form-control:focus { border-color: #e05a00; box-shadow: 0 0 0 3px rgba(224,90,0,.12); }
        .prod-search-inline .input-group-text,
        .prod-search-inline .btn {
            border-radius: 0 8px 8px 0;
            border: 1.5px solid #e0e6ed;
            border-left: 0;
            background: #12b39d;
            color: #fff;
            font-weight: 700;
        }
        .prod-search-inline .form-control {
            border-right: 0;
        }
        .prod-search-inline .btn:hover {
            background: #0f9f8c;
            color: #fff;
        }
        .prod-filter-actions { display: flex; gap: 8px; margin-top: 14px; }
        .btn-filter-apply { background: linear-gradient(135deg, #f39c12, #e05a00); color: #fff; border: none; border-radius: 8px; padding: 7px 20px; font-size: .84rem; font-weight: 600; }
        .btn-filter-apply:hover { background: linear-gradient(135deg, #e08e0b, #c04e00); color: #fff; }
        .btn-filter-clear { background: #f0f2f5; color: #555; border: none; border-radius: 8px; padding: 7px 16px; font-size: .84rem; font-weight: 600; }
        .btn-filter-clear:hover { background: #e0e6ed; color: #333; }
        /* ── Table card ── */
        .prod-table-card {
            background: #fff; border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08); margin: 16px 24px 24px; overflow: hidden;
        }
        .prod-table-card .table-header {
            background: linear-gradient(135deg, #f39c12, #e05a00);
            padding: 14px 20px; display: flex; align-items: center;
        }
        .prod-table-card .table-header span { color: #fff; font-weight: 700; font-size: .95rem; }
        #tbl_apoyo_listar thead th {
            background: #fdf4e7; border-bottom: 2px solid #f2d49a;
            color: #7d3f00; font-size: .78rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .5px;
            padding: 10px 12px; white-space: nowrap;
        }
        #tbl_apoyo_listar tbody tr:hover { background: #fffcf5; }
        #tbl_apoyo_listar tbody td { vertical-align: middle; font-size: .875rem; padding: 10px 12px; border-color: #f0f2f5; }
        .badge-activo   { background:#d5f5e3; color:#1e8449; border-radius:20px; padding:3px 10px; font-size:.75rem; font-weight:700; }
        .badge-inactivo { background:#fdecea; color:#c0392b; border-radius:20px; padding:3px 10px; font-size:.75rem; font-weight:700; }
        .prod-dropdown .btn-prod-menu { background:none; border:none; color:#7d3f00; font-size:1rem; padding:4px 10px; border-radius:6px; cursor:pointer; }
        .prod-dropdown .btn-prod-menu:hover { background:#fdf4e7; }
        .stock-num { font-weight:700; color:#7d3f00; }
        /* ── Modal tabs ── */
        .prod-modal-tabs { border-bottom: 2px solid #e8d5bf; margin-bottom: 16px; }
        .prod-modal-tabs .nav-item { margin-bottom: -2px; }
        .prod-modal-tabs .nav-link { color:#7d3f00; font-weight:600; font-size:.82rem; padding:8px 14px; border:2px solid transparent; border-radius:8px 8px 0 0; transition:all .2s; }
        .prod-modal-tabs .nav-link:hover { background:#fdf4e7; color:#e05a00; }
        .prod-modal-tabs .nav-link.active { background:linear-gradient(135deg,#f39c12,#e05a00); color:#fff !important; border-color:#e05a00 #e05a00 #fff; }
        .prod-modal-tabs .nav-link i { margin-right:5px; }
        /* ── Modal crear ── */
        #modal_apoyo_crear .modal-content { border:none; border-radius:14px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        #modal_apoyo_crear .modal-header { background:linear-gradient(135deg,#f39c12 0%,#e05a00 100%); border:none; padding:20px 24px; }
        #modal_apoyo_crear .modal-title { color:#fff; font-weight:700; font-size:1.1rem; }
        #modal_apoyo_crear .close { color:rgba(255,255,255,.8); opacity:1; font-size:1.4rem; }
        #modal_apoyo_crear .close:hover { color:#fff; }
        #modal_apoyo_crear .modal-body { padding:20px 24px 8px; background:#f8fafc; }
        #modal_apoyo_crear .modal-footer { background:#fff; border-top:1px solid #e8ecef; padding:14px 24px; }
        /* form fields */
        .form-section { background:#fff; border-radius:10px; padding:16px 18px 10px; margin-bottom:12px; border:1px solid #e8ecef; }
        .form-section label { font-size:.8rem; font-weight:600; color:#555; margin-bottom:3px; }
        .form-section .form-control { border-radius:8px; border:1.5px solid #e0e6ed; font-size:.875rem; }
        .form-section .form-control:focus { border-color:#e05a00; box-shadow:0 0 0 3px rgba(224,90,0,.12); }
        /* Foto upload */
        .foto-upload-area { border:2px dashed #ccd3db; border-radius:10px; padding:14px; text-align:center; cursor:pointer; transition:border-color .2s; }
        .foto-upload-area:hover { border-color:#e05a00; background:#fdf4e7; }
        .foto-upload-area i { font-size:1.6rem; color:#aaa; display:block; margin-bottom:4px; }
        .foto-upload-area span { font-size:.8rem; color:#888; }
        /* Spinner */
        #modalSpinnerApoyo .modal-content { background:transparent; border:none; box-shadow:none; }
        .spinner-overlay-box { background:rgba(255,255,255,.97); border-radius:16px; padding:40px 30px; text-align:center; box-shadow:0 15px 50px rgba(0,0,0,.2); }
        .spinner-ring { display:inline-block; width:52px; height:52px; border:5px solid #e8d5bf; border-top-color:#e05a00; border-radius:50%; animation:spin .8s linear infinite; margin-bottom:16px; }
        @keyframes spin { to { transform:rotate(360deg); } }
        .spinner-overlay-box p { margin:0; font-size:1rem; font-weight:600; color:#7d3f00; }
        .spinner-overlay-box small { color:#888; font-size:.8rem; }
    </style>
    @endpush

    {{-- PAGE HEADER --}}
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

    {{-- ACTION BAR --}}
    <div class="prod-action-bar">
        <button class="btn-register" data-toggle="modal" data-target="#modal_apoyo_crear">
            <i class="fa fa-plus mr-1"></i> Nuevo Producto
        </button>
        <span style="margin-left:auto; font-size:.78rem; color:#888;">
            <i class="fa fa-info-circle mr-1"></i> Haga clic en <b>Ver detalle</b> para editar un producto
        </span>
    </div>

    {{-- FILTROS --}}
    <div class="prod-filter-card">
        <div class="filter-header" onclick="toggleFiltrosApoyo()">
            <span><i class="fa fa-filter mr-2"></i> Filtros de búsqueda</span>
            <i class="fa fa-chevron-down" id="ico-filtros-apoyo"></i>
        </div>
        <div class="prod-filter-body" id="filtros-body-apoyo">
            <div class="row">
                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    <label>Seleccionar Producto</label>
                    <div class="input-group prod-search-inline">
                        <input type="text" id="ap_producto_picker" class="form-control"
                               placeholder="ID o nombre del producto..." autocomplete="off"
                               onfocus="abrirModalBusquedaProductoApoyo()">
                        <input type="hidden" id="fap_producto_id" value="">
                        <div class="input-group-append">
                            <button type="button" class="btn" title="Buscar producto"
                                    onclick="abrirModalBusquedaProductoApoyo()">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <label>Nombre / ID / Cód. Barra</label>
                    <input type="text" id="fap_q" class="form-control" placeholder="Ej: bolsa…"
                           onkeydown="if(event.key==='Enter') aplicarFiltrosApoyo()">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <label>Descripción</label>
                    <input type="text" id="fap_descripcion" class="form-control" placeholder="Buscar en descripción…"
                           onkeydown="if(event.key==='Enter') aplicarFiltrosApoyo()">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <label>Categoría</label>
                    <select id="fap_categoria" class="form-control"><option value="">Todas</option></select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <label>Marca</label>
                    <select id="fap_marca" class="form-control"><option value="">Todas</option></select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <label>Estado</label>
                    <select id="fap_estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="1">Activo</option>
                        <option value="2">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="prod-filter-actions">
                <button class="btn-filter-apply" onclick="aplicarFiltrosApoyo()">
                    <i class="fa fa-search mr-1"></i> Buscar
                </button>
                <button class="btn-filter-clear" onclick="limpiarFiltrosApoyo()">
                    <i class="fa fa-times mr-1"></i> Limpiar
                </button>
            </div>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="prod-table-card">
        <div class="table-header">
            <span><i class="fa fa-list mr-2"></i> Listado de Productos</span>
        </div>
        <div style="padding:16px 16px 8px;">
            <div class="table-responsive">
                <table id="tbl_apoyo_listar" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:60px;">ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th style="width:110px;">Cód. Barra</th>
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

    {{-- MODAL: CREAR PRODUCTO (sin precios/costos) --}}
    <div class="modal fade" id="modal_apoyo_crear" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-plus-circle mr-2"></i> Registro de Nuevo Producto</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="crearProductoApoyoForm" name="crearProductoApoyoForm" novalidate>

                        {{-- PESTAÑAS --}}
                        <ul class="nav prod-modal-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tap-ap-general">
                                    <i class="fa fa-info-circle"></i> General
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tap-ap-clasif">
                                    <i class="fa fa-tag"></i> Clasificación
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">

                            {{-- Tab 1: General --}}
                            <div class="tab-pane fade show active" id="tap-ap-general">
                                <div class="form-section">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label>Nombre del producto <span class="text-danger">*</span></label>
                                            <input class="form-control" required type="text" id="ap_nombre_producto"
                                                name="nombre_producto" placeholder="Ej: Bolsa de polietileno 10x15">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label>Descripción <span class="text-danger">*</span></label>
                                            <textarea class="form-control" required id="ap_descripcion_producto"
                                                name="descripcion_producto" rows="3"
                                                placeholder="Descripción detallada del producto…"></textarea>
                                        </div>
                                        {{-- ISV fijo (oculto) — vendedores no lo editan --}}
                                        <input type="hidden" name="isv_producto" id="ap_isv_producto" value="15">

                                        <div class="col-md-6 mb-3">
                                            <label>Código de barra</label>
                                            <input class="form-control" type="number" name="cod_barra_producto"
                                                id="ap_cod_barra_producto" min="0" placeholder="Opcional">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Código estatal</label>
                                            <input class="form-control" type="number" name="cod_estatal_producto"
                                                id="ap_cod_estatal_producto" min="0" placeholder="Opcional">
                                        </div>
                                        {{-- Fotos (múltiples) --}}
                                        <div class="col-12 mb-2">
                                            <label for="ap_foto_producto" class="foto-upload-area w-100" style="cursor:pointer; margin:0;">
                                                <i class="fa fa-cloud-upload"></i>
                                                <span>Clic para seleccionar imágenes<br><small style="color:#aaa;">(PNG, JPG, GIF — puede seleccionar varias a la vez)</small></span>
                                                <input type="file" id="ap_foto_producto" name="files[]"
                                                    accept="image/png,image/gif,image/jpeg" multiple style="display:none;">
                                            </label>
                                        </div>
                                        <div class="col-12 mb-2" style="min-height:80px; text-align:center;">
                                            <div id="ap_multi_preview" style="display:none; flex-wrap:wrap; gap:6px; justify-content:center;"></div>
                                            <div id="ap_preview_placeholder" style="color:#ccc; font-size:.85rem; padding:16px 0;">
                                                <i class="fa fa-images" style="font-size:2rem; display:block; margin-bottom:4px;"></i>
                                                Vista previa aquí
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 2: Clasificación --}}
                            <div class="tab-pane fade" id="tap-ap-clasif">
                                <div class="form-section">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label>Marca</label>
                                            <select class="form-control" name="marca_producto" id="ap_marca_producto">
                                                <option selected disabled>— Seleccione una marca —</option>
                                                @foreach ($marcas as $marca)
                                                <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label>Categoría <span class="text-danger">*</span></label>
                                            <select class="form-control" name="categoria_producto" id="ap_categoria_producto"
                                                onchange="listarSubCategoriasApoyo()">
                                                <option selected disabled>— Seleccione una categoría —</option>
                                                @foreach ($categorias as $categoria)
                                                <option value="{{ $categoria->id }}">{{ $categoria->descripcion }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label>Subcategoría <span class="text-danger">*</span></label>
                                            <select class="form-control" name="sub_categoria_producto" id="ap_sub_categoria_producto">
                                                <option selected disabled>— Seleccione una subcategoría —</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Unidad para compra <span class="text-danger">*</span></label>
                                            <select class="form-control" name="unidad_producto" id="ap_unidad_producto">
                                                <option selected disabled>— Seleccione —</option>
                                                @foreach ($unidades as $unidad)
                                                <option value="{{ $unidad->id }}">{{ $unidad->nombre }} — {{ $unidad->simbolo }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Cantidad unidades compra <span class="text-danger">*</span></label>
                                            <input class="form-control" min="1" type="number" name="unidades"
                                                id="ap_unidades" step="any" placeholder="Ej: 1">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Tiempo de recuperación en meses</label>
                                            <input class="form-control" type="number" min="1" max="999"
                                                name="tiempo_recuperacion_meses" id="ap_tiempo_recuperacion_meses"
                                                placeholder="Ej: 3">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Origen</label>
                                            <input class="form-control" type="text" maxlength="200"
                                                name="origen" id="ap_origen"
                                                placeholder="Ej: China, Honduras...">
                                        </div>
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
                    <button type="button" onclick="guardarProductoApoyo()" class="btn btn-primary" style="border-radius:8px; padding:8px 22px; font-weight:600;">
                        <i class="fa fa-save mr-1"></i> Guardar Producto
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- SPINNER --}}
    <div class="modal fade" id="modalSpinnerApoyo" tabindex="-1" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width:220px;">
            <div class="modal-content">
                <div class="modal-body" style="padding:0; text-align:center;">
                    <div class="spinner-overlay-box">
                        <div class="spinner-ring"></div>
                        <p>Procesando…</p>
                        <small>Por favor espere</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-buscador-producto id-modal="buscadorProductoApoyo" callback="alSeleccionarProductoApoyo" debounce-ms="180" />

    @push('scripts')
    <script src="/js/js_proyecto/inventario/producto-apoyo.js"></script>
    @endpush
</div>
