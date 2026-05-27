<div>

@push('styles')
<style>
/* -- PROFAC brand variables -------------------------------- */
:root {
    --pf-grad:       linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
    --pf-grad-hover: linear-gradient(135deg, #e08e0b 0%, #c04e00 100%);
    --pf-orange:   #e67e22;
    --pf-brown:    #7d3f00;
    --pf-gold-bg:  #fdf4e7;
    --pf-border:   #e8d5bf;
    --pf-radius:   8px;
    --pf-shadow:   0 2px 8px rgba(0,0,0,.10);
}

/* -- Imagen preview ---------------------------------------- */
.ancho-imagen { max-width: 300px; }
@media (max-width:600px)  { .ancho-imagen { max-width: 200px; } }

/* -- Layout fix -------------------------------------------- */
#page-wrapper         { padding-left: 0 !important; padding-right: 0 !important; }
.wrapper-content      { padding-left: 0 !important; padding-right: 0 !important; }
.wrapper-content > .row                   { margin-left: 0 !important; margin-right: 0 !important; }
.wrapper-content > .row > [class*="col-"] { padding-left: 0 !important; padding-right: 0 !important; }

/* -- Card principal ---------------------------------------- */
.prod-card {
    border: 1px solid var(--pf-border);
    border-radius: var(--pf-radius);
    box-shadow: var(--pf-shadow);
    background: #fff;
}
.prod-card-header {
    background: var(--pf-grad);
    padding: 12px 20px;
    border-radius: var(--pf-radius) var(--pf-radius) 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
}
.prod-card-header h5 {
    margin: 0;
    color: #fff;
    font-size: .85rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 8px;
}
.prod-card-body { padding: 16px 20px; }

/* -- Card filtros ------------------------------------------ */
.prod-filter-card {
    border: 1px solid var(--pf-border);
    border-radius: var(--pf-radius);
    box-shadow: var(--pf-shadow);
    background: #fff;
    margin-bottom: 16px;
}
.prod-filter-header {
    background: var(--pf-gold-bg);
    padding: 9px 16px;
    border-radius: var(--pf-radius) var(--pf-radius) 0 0;
    border-bottom: 1px solid var(--pf-border);
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: .80rem;
    font-weight: 700;
    color: var(--pf-brown);
    text-transform: uppercase;
    letter-spacing: .04em;
}
.prod-filter-body { padding: 14px 16px 10px; }

/* -- Botones cabecera -------------------------------------- */
.btn-prod-new {
    background: rgba(255,255,255,.18) !important;
    color: #fff !important;
    border: 1.5px solid rgba(255,255,255,.5) !important;
    border-radius: 5px !important;
    font-weight: 600 !important;
    font-size: .78rem;
    padding: 5px 14px;
    transition: background .18s;
    white-space: nowrap;
    cursor: pointer;
    text-decoration: none;
}
.btn-prod-new:hover { background: rgba(255,255,255,.30) !important; color: #fff !important; }

/* -- Tabla ------------------------------------------------- */
#tbl_productosListar { width: 100% !important; }
#tbl_productosListar thead th {
    background: var(--pf-gold-bg);
    color: var(--pf-brown);
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    border-bottom: 2px solid #f2d49a;
    white-space: nowrap;
    padding: 8px 10px;
    vertical-align: middle;
}
#tbl_productosListar tbody td { font-size: .83rem; vertical-align: middle; padding: 7px 10px; }
#tbl_productosListar tbody tr:hover { background: #fffcf5; }

/* -- Modal header ------------------------------------------ */
.modal-header-prod {
    background: var(--pf-grad);
    padding: 12px 20px;
    border-radius: var(--pf-radius) var(--pf-radius) 0 0;
}
.modal-header-prod .modal-title { color: #fff; font-size: .95rem; font-weight: 700; }
.modal-header-prod .close       { color: #fff; opacity: .8; text-shadow: none; }
.modal-header-prod .close:hover { opacity: 1; }

/* -- Seccion label dentro modal ---------------------------- */
.modal-section-label {
    font-size: .70rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #6c757d;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 4px;
    margin-bottom: 12px;
    margin-top: 8px;
}

/* -- Spinner modal ----------------------------------------- */
.loader, .loader:before, .loader:after { border-radius: 50%; }
.loader {
    color: #0dc5c1; font-size: 11px; text-indent: -99999em;
    margin: 55px auto; position: relative;
    width: 10em; height: 10em;
    box-shadow: inset 0 0 0 1em;
    transform: translateZ(0);
}
.loader:before, .loader:after { position: absolute; content: ''; }
.loader:before {
    width: 5.2em; height: 10.2em; background: #fff;
    border-radius: 10.2em 0 0 10.2em;
    top: -.1em; left: -.1em;
    transform-origin: 5.1em 5.1em;
    animation: load2 2s infinite ease 1.5s;
}
.loader:after {
    width: 5.2em; height: 10.2em; background: #fff;
    border-radius: 0 10.2em 10.2em 0;
    top: -.1em; left: 4.9em;
    transform-origin: .1em 5.1em;
    animation: load2 2s infinite ease;
}
@keyframes load2 {
    0%   { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* -- Dropdown acciones ------------------------------------- */
.prod-dropdown { position: relative; display: inline-block; }
.btn-prod-menu {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px; height: 32px;
    background: #fff;
    border: 1.5px solid #e0cbb0;
    border-radius: 7px;
    color: #c0622a;
    font-size: .88rem;
    cursor: pointer;
    transition: background .15s, border-color .15s, box-shadow .15s;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
}
.btn-prod-menu:hover, .btn-prod-menu:focus {
    background: #fff8f0;
    border-color: #e67e22;
    box-shadow: 0 2px 6px rgba(230,126,34,.25);
    outline: none;
}
.prod-dropdown .dropdown-menu {
    min-width: 160px;
    border: 1px solid #f0e0cc;
    border-radius: 8px;
    padding: 4px 0;
    font-size: .83rem;
    box-shadow: 0 4px 16px rgba(0,0,0,.13) !important;
}
.prod-dropdown .dropdown-item { padding: 7px 14px; font-weight: 500; transition: background .12s; }
.prod-dropdown .dropdown-item:hover { background: #fff8f0; color: #c0622a; }
.prod-dropdown .dropdown-item i { opacity: .85; }

/* -- Badges estado ----------------------------------------- */
.badge-activo   { background:#dcfce7; color:#14532d; border:1px solid #86efac; font-weight:600; padding:3px 8px; border-radius:12px; font-size:.75rem; }
.badge-inactivo { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; font-weight:600; padding:3px 8px; border-radius:12px; font-size:.75rem; }

/* -- DataTables -------------------------------------------- */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter { margin-bottom: 8px; }

/* -- Responsive -------------------------------------------- */
@media (max-width: 767px) {
    .prod-card-body    { padding: 10px; }
    .prod-card-header  { padding: 10px 12px; }
    .prod-filter-body  { padding: 10px; }
}
@media (max-width: 575px) {
    .modal-dialog { margin: .5rem; }
    .modal-dialog.modal-lg { max-width: calc(100vw - 1rem); }
}
</style>
@endpush

    {{-- === PAGE HEADING === --}}
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-12">
            <h2><i class="fa fa-cube mr-2" style="color:#e67e22"></i>Productos</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Inventario</a></li>
                <li class="breadcrumb-item active"><strong>Productos</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-12">

                {{-- === FILTROS === --}}
                <div class="prod-filter-card">
                    <div class="prod-filter-header">
                        <i class="fa fa-filter"></i> Filtros de búsqueda
                    </div>
                    <div class="prod-filter-body">
                        <div class="row align-items-end">
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <label class="col-form-label">Nombre / ID / Cód. Barra</label>
                                <input type="text" id="fprod_q" class="form-control"
                                       placeholder="Ej: bolsa concept"
                                       onkeydown="if(event.key==='Enter') aplicarFiltros()">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <label class="col-form-label">Descripción</label>
                                <input type="text" id="fprod_descripcion" class="form-control"
                                       placeholder="Buscar en descripción…"
                                       onkeydown="if(event.key==='Enter') aplicarFiltros()">
                            </div>
                            <div class="col-12 col-sm-6 col-md-2 col-lg-2">
                                <label class="col-form-label">ISV</label>
                                <select id="fprod_isv" class="form-control">
                                    <option value="">-- Todos --</option>
                                    <option value="con">Con ISV</option>
                                    <option value="0">Exento</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                                <label class="col-form-label">Categoría</label>
                                <select id="fprod_categoria" class="form-control">
                                    <option value="">-- Todas --</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                                <label class="col-form-label">Marca</label>
                                <select id="fprod_marca" class="form-control">
                                    <option value="">-- Todas --</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-2 col-lg-2">
                                <label class="col-form-label">Estado</label>
                                <select id="fprod_estado" class="form-control">
                                    <option value="">-- Todos --</option>
                                    <option value="1">Activo</option>
                                    <option value="2">Inactivo</option>
                                </select>
                            </div>
                            <div class="mt-2 col-12" style="display:flex; gap:8px; flex-wrap:wrap;">
                                <button onclick="aplicarFiltros()" class="btn btn-sm btn-info">
                                    <i class="fa fa-filter"></i> Filtrar
                                </button>
                                <button onclick="limpiarFiltros()" class="btn btn-sm btn-default">
                                    <i class="fa fa-times"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- === TABLA PRINCIPAL === --}}
                <div class="prod-card">
                    <div class="prod-card-header">
                        <h5><i class="fa fa-cube"></i> Listado de Productos</h5>
                        <div class="d-flex" style="gap:8px; flex-wrap:wrap;">
                            @if (Auth::user()->rol_id == '1' || Auth::user()->rol_id == '7')
                            <button onclick="exportarExcel()" class="btn-prod-new" style="background:rgba(255,255,255,.12)!important">
                                <i class="fa fa-file-excel-o mr-1"></i> Exportar Excel
                            </button>
                            @endif
                            @if (Auth::user()->rol_id == '1')
                            <button type="button" class="btn-prod-new" data-toggle="modal" data-target="#modal_producto_crear">
                                <i class="fa fa-plus mr-1"></i> Registrar Producto
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="prod-card-body">
                        <div class="table-responsive">
                            <table id="tbl_productosListar" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Cód</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Cód. Barra</th>
                                        <th>ISV</th>
                                        <th>Categoría</th>
                                        <th>Existencia</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- === MODAL REGISTRAR PRODUCTO === --}}
    <div class="modal fade" id="modal_producto_crear" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header modal-header-prod">
                    <h5 class="modal-title"><i class="fa fa-cube mr-2"></i>Registro de Producto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="crearProductoForm" name="crearProductoForm" data-parsley-validate>

                        {{-- Informacion general --}}
                        <div class="modal-section-label"><i class="fa fa-info-circle mr-1"></i>Información General</div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Nombre del producto <span class="text-danger">*</span></label>
                                    <input class="form-control" required type="text" id="nombre_producto" name="nombre_producto" data-parsley-required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Descripción del producto <span class="text-danger">*</span></label>
                                    <textarea placeholder="Escriba aquí..." required id="descripcion_producto" name="descripcion_producto"
                                        cols="30" rows="3" class="form-control" data-parsley-required></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">ISV en % <span class="text-danger">*</span></label>
                                    <select class="form-control" name="isv_producto" id="isv_producto" data-parsley-required>
                                        <option value="0">Exento de impuestos</option>
                                        <option value="15" selected>15% de ISV</option>
                                        <option value="18">18% de ISV</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Código de Barra</label>
                                    <input class="form-control" type="number" name="cod_barra_producto" id="cod_barra_producto" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Código Estatal</label>
                                    <input class="form-control" type="number" name="cod_estatal_producto" id="cod_estatal_producto" min="0">
                                </div>
                            </div>
                        </div>

                        {{-- Precios --}}
                        <div class="modal-section-label mt-2"><i class="fa fa-tag mr-1"></i>Precios</div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Precio de venta base <span class="text-danger">*</span></label>
                                    <input class="form-control" min="0" type="number" name="precioBase" id="precioBase"
                                        data-parsley-required step="any" onchange="validacionPrecio()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Costo promedio <span class="text-danger">*</span></label>
                                    <input class="form-control" min="0" type="number" name="costo_promedio" id="costo_promedio"
                                        data-parsley-required step="any">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Último costo de compra <span class="text-danger">*</span></label>
                                    <input class="form-control" min="0" type="number" name="ultimo_costo_compra" id="ultimo_costo_compra"
                                        data-parsley-required step="any">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Precio <b>A</b> <span class="text-danger">*</span></label>
                                    <input class="form-control" type="number" name="precio1" id="precio1" data-parsley-required step="any" disabled>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Precio <b>B</b> <span class="text-danger">*</span></label>
                                    <input class="form-control" type="number" name="precio2" id="precio2" data-parsley-required step="any" disabled>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Precio <b>C</b> <span class="text-danger">*</span></label>
                                    <input class="form-control" type="number" name="precio3" id="precio3" data-parsley-required step="any" disabled>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Precio <b>D</b> <span class="text-danger">*</span></label>
                                    <input class="form-control" type="number" name="precio4" id="precio4" data-parsley-required step="any" disabled>
                                </div>
                            </div>
                        </div>

                        {{-- Clasificacion --}}
                        <div class="modal-section-label mt-2"><i class="fa fa-tags mr-1"></i>Clasificación</div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Marca <span class="text-danger">*</span></label>
                                    <select class="form-control" name="marca_producto" id="marca_producto" data-parsley-required>
                                        <option selected disabled>--- Seleccione una marca ---</option>
                                        @foreach ($marcas as $marca)
                                        <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Categoría <span class="text-danger">*</span></label>
                                    <select class="form-control" name="categoria_producto" id="categoria_producto"
                                        data-parsley-required onchange="listarSubCategorias()">
                                        <option selected disabled>--- Seleccione una categoría ---</option>
                                        @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}">{{ $categoria->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Subcategoría <span class="text-danger">*</span></label>
                                    <select class="form-control" name="sub_categoria_producto" id="sub_categoria_producto" data-parsley-required>
                                        <option selected disabled>--- Seleccione una subcategoría ---</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Unidades de medida --}}
                        <div class="modal-section-label mt-2"><i class="fa fa-balance-scale mr-1"></i>Unidades de Medida</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Unidad para compra <span class="text-danger">*</span></label>
                                    <select class="form-control" name="unidad_producto" id="unidad_producto" data-parsley-required>
                                        <option selected disabled>--- Seleccione una unidad ---</option>
                                        @foreach ($unidades as $unidad)
                                        <option value="{{ $unidad->id }}">{{ $unidad->nombre }} - {{ $unidad->simbolo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Cantidad de unidades para compra <span class="text-danger">*</span></label>
                                    <input class="form-control" min="1" type="number" name="unidades" id="unidades" step="any" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Unidad para venta <span class="text-danger">*</span></label>
                                    <select class="form-control" name="unidad_producto_venta" id="unidad_producto_venta" data-parsley-required>
                                        <option selected disabled>--- Seleccione una unidad ---</option>
                                        @foreach ($unidades as $unidad)
                                        <option value="{{ $unidad->id }}">{{ $unidad->nombre }} - {{ $unidad->simbolo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Cantidad de unidades para venta <span class="text-danger">*</span></label>
                                    <input class="form-control" min="1" type="number" name="unidades_venta" id="unidades_venta" step="any" required>
                                </div>
                            </div>
                        </div>

                        {{-- Fotografia --}}
                        <div class="modal-section-label mt-2"><i class="fa fa-camera mr-1"></i>Fotografía</div>
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <div class="form-group mb-2">
                                    <label class="col-form-label focus-label">Seleccionar imagen</label>
                                    <input type="file" id="foto_producto" name="foto_producto"
                                        accept="image/png, image/gif, image/jpeg" multiple class="form-control-file">
                                </div>
                            </div>
                            <div class="col-md-7 text-center">
                                <img id="imagenPrevisualizacion" class="ancho-imagen img-thumbnail" style="display:none;">
                            </div>
                        </div>

                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Cerrar
                    </button>
                    <button type="submit" form="crearProductoForm" class="btn btn-primary">
                        <i class="fa fa-save mr-1"></i>Guardar Producto
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- === MODAL SPINNER === --}}
    <div class="modal" id="modalSpinnerLoading" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <h4 class="mb-3" style="color:#555;">Espere un momento...</h4>
                    <div class="loader">Loading...</div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script src="{{ asset('js/js_proyecto/inventario/producto.js') }}"></script>
@endpush
</div>
