<div>
    @push('styles')
    <style>
        .cmp-shell { --cmp-green:#16856f; --cmp-orange:#e8751a; --cmp-ink:#263238; }
        .cmp-shell input::-webkit-outer-spin-button,
        .cmp-shell input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
        .cmp-shell input[type=number] { -moz-appearance:textfield; }
        .cmp-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
        .cmp-toolbar h2 { margin:0; color:var(--cmp-ink); font-size:22px; font-weight:800; }
        .cmp-toolbar-actions { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
        .cmp-draft-state { min-width:92px; color:#607d8b; font-size:11px; text-align:right; }
        .cmp-panel { margin-bottom:16px; overflow:hidden; border:1px solid #e3e8ea; border-radius:8px; background:#fff; box-shadow:0 2px 10px rgba(38,50,56,.05); }
        .cmp-panel-head { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:11px 16px; border-bottom:1px solid #edf0f1; background:#fafbfb; }
        .cmp-panel-title { margin:0; color:#546e7a; font-size:11px; font-weight:800; letter-spacing:0; text-transform:uppercase; }
        .cmp-panel-title i { margin-right:6px; color:var(--cmp-green); }
        .cmp-panel-body { padding:16px; }
        .cmp-label { display:block; margin-bottom:4px; color:#607d8b; font-size:10px; font-weight:800; letter-spacing:0; text-transform:uppercase; }
        .cmp-label .text-danger { margin-left:2px; }
        .cmp-shell .form-control { border-color:#cfd8dc; border-radius:6px; font-size:13px; }
        .cmp-shell .form-control:focus { border-color:var(--cmp-green); box-shadow:0 0 0 3px rgba(22,133,111,.12); }
        .cmp-order { display:inline-flex; align-items:center; padding:5px 10px; border-radius:6px; background:#e8f5f1; color:#126b5b; font-size:12px; font-weight:800; }
        .cmp-search-band { padding:14px 16px; border-bottom:1px solid #e6ecee; background:linear-gradient(90deg,#f0faf7,#fff8f1); }
        .cmp-search-control { position:relative; max-width:720px; }
        .cmp-search-control > i { position:absolute; top:12px; left:13px; z-index:2; color:#78909c; }
        .cmp-search-control input { height:40px; padding-left:38px; padding-right:116px; background:#fff; }
        .cmp-search-control button { position:absolute; top:3px; right:3px; height:34px; border:0; border-radius:5px; background:var(--cmp-green); color:#fff; padding:0 15px; font-size:12px; font-weight:700; }
        .cmp-cart-tools { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 16px; border-bottom:1px solid #edf0f1; }
        .cmp-cart-filter { position:relative; width:min(330px,100%); }
        .cmp-cart-filter i { position:absolute; top:9px; left:10px; color:#90a4ae; }
        .cmp-cart-filter input { height:32px; padding-left:31px; }
        .cmp-count { padding:3px 9px; border-radius:10px; background:#fff0e4; color:#b6530c; font-size:10px; font-weight:800; white-space:nowrap; }
        .cmp-table-wrap { min-height:280px; max-height:520px; overflow:auto; }
        .cmp-table { width:100%; min-width:1050px; margin:0; font-size:12px; }
        .cmp-table thead th { position:sticky; top:0; z-index:2; padding:9px 8px; border:0; border-bottom:2px solid #dce4e6; background:#f3f6f7; color:#52636a; font-size:9px; font-weight:800; text-transform:uppercase; white-space:nowrap; }
        .cmp-table td { padding:8px; border-color:#edf0f1; vertical-align:middle; }
        .cmp-product { display:flex; align-items:center; gap:9px; min-width:260px; }
        .cmp-product img { width:42px; height:42px; flex:0 0 42px; border:1px solid #e3e8ea; border-radius:5px; object-fit:contain; background:#fafafa; }
        .cmp-product-name { max-width:300px; color:#263238; font-weight:700; line-height:1.25; overflow-wrap:anywhere; }
        .cmp-product-meta { margin-top:2px; color:#87969c; font-size:9px; }
        .cmp-table .cmp-number { width:105px; min-width:105px; text-align:right; font-variant-numeric:tabular-nums; }
        .cmp-table .cmp-date { width:132px; min-width:132px; }
        .cmp-money { font-weight:700; font-variant-numeric:tabular-nums; text-align:right; white-space:nowrap; }
        .cmp-remove { width:31px; height:31px; padding:0; border:1px solid #ffcdd2; border-radius:5px; background:#fff5f5; color:#c62828; }
        .cmp-empty { padding:58px 20px; text-align:center; color:#90a4ae; }
        .cmp-empty i { display:block; margin-bottom:10px; color:#c5d0d4; font-size:38px; }
        .cmp-pagination { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:9px 16px; border-top:1px solid #edf0f1; background:#fafbfb; }
        .cmp-pagination-info { color:#78909c; font-size:10px; }
        .cmp-pagination .btn { width:32px; height:28px; padding:0; }
        .cmp-footer { position:sticky; bottom:0; z-index:5; display:grid; grid-template-columns:minmax(0,1fr) repeat(3,minmax(120px,160px)) auto; align-items:stretch; border-top:1px solid #dfe6e8; background:#fff; box-shadow:0 -5px 18px rgba(38,50,56,.08); }
        .cmp-footer-spacer { padding:14px 16px; color:#78909c; font-size:11px; }
        .cmp-total { padding:9px 14px; border-left:1px solid #edf0f1; text-align:right; }
        .cmp-total span { display:block; color:#78909c; font-size:9px; font-weight:800; text-transform:uppercase; }
        .cmp-total strong { display:block; margin-top:2px; color:#263238; font-size:16px; font-variant-numeric:tabular-nums; }
        .cmp-total.grand { background:#f0faf7; }
        .cmp-total.grand strong { color:#08745f; font-size:19px; }
        .cmp-save { margin:10px 14px; min-width:150px; border:0; border-radius:6px; background:linear-gradient(135deg,#e35f0d,#ef8d32); color:#fff; font-size:12px; font-weight:800; }
        .cmp-temp-list { max-height:310px; overflow:auto; text-align:left; }
        .cmp-temp-item { display:flex; align-items:center; gap:9px; margin-bottom:7px; padding:10px; border:1px solid #e1e7e9; border-radius:6px; cursor:pointer; }
        .cmp-temp-item input { flex:0 0 auto; }
        .cmp-temp-copy { min-width:0; flex:1; }
        .cmp-temp-copy strong,.cmp-temp-copy small { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .cmp-temp-copy small { margin-top:2px; color:#78909c; font-size:10px; }
        .cmp-temp-delete { border:0; background:transparent; color:#c62828; }
        @media (max-width:991.5px) {
            .cmp-footer { grid-template-columns:repeat(3,1fr); }
            .cmp-footer-spacer { display:none; }
            .cmp-save { grid-column:1/-1; height:40px; }
        }
        @media (max-width:575.5px) {
            .cmp-shell .wrapper-content { padding-left:8px; padding-right:8px; }
            .cmp-panel-body { padding:12px; }
            .cmp-toolbar-actions { width:100%; }
            .cmp-toolbar-actions .btn { flex:1; }
            .cmp-draft-state { width:100%; text-align:left; }
            .cmp-cart-tools { align-items:flex-start; flex-direction:column; }
            .cmp-cart-filter { width:100%; }
            .cmp-footer { grid-template-columns:1fr 1fr; }
            .cmp-total { border-bottom:1px solid #edf0f1; }
            .cmp-total.grand { grid-column:1/-1; }
        }
    </style>
    @endpush

    <div class="cmp-shell">
        <div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-12 cmp-toolbar">
                <div>
                    <h2><i class="fa fa-shopping-cart" style="color:#16856f;"></i> Nueva compra</h2>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">Inventario</li>
                        <li class="breadcrumb-item active"><strong>Compras</strong></li>
                    </ol>
                </div>
                <div class="cmp-toolbar-actions">
                    <span id="estadoTemporalCompra" class="cmp-draft-state" aria-live="polite"></span>
                    <button type="button" id="btnNuevaCompra" class="btn btn-white btn-sm" title="Iniciar nueva compra"><i class="fa fa-file-o"></i> Nueva</button>
                    <button type="button" id="btnTemporalesCompra" class="btn btn-white btn-sm" title="Ver registros temporales"><i class="fa fa-clock-o"></i> Temporales <span id="cantidadTemporalesCompra" class="badge badge-warning">0</span></button>
                </div>
            </div>
        </div>

        <div class="wrapper wrapper-content animated fadeInRight">
            <form autocomplete="off" id="crear_compra" name="crear_compra">
                <section class="cmp-panel">
                    <div class="cmp-panel-head">
                        <h3 class="cmp-panel-title"><i class="fa fa-file-text-o"></i>Datos de la compra</h3>
                        <span class="cmp-order">Orden {{ $ordenNumero->numero }}</span>
                    </div>
                    <div class="cmp-panel-body">
                        <div class="row">
                            <div class="col-12 col-md-6 col-xl-3 mb-3">
                                <label class="cmp-label" for="numero_factura">Número de factura<span class="text-danger">*</span></label>
                                <input class="form-control" type="text" id="numero_factura" name="numero_factura" maxlength="255" required>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3 mb-3">
                                <label class="cmp-label" for="cai">Código CAI</label>
                                <input class="form-control text-uppercase" type="text" id="cai" name="cai" maxlength="255">
                            </div>
                            <div class="col-12 col-md-6 col-xl-3 mb-3">
                                <label class="cmp-label" for="seleccionarProveedor">Proveedor<span class="text-danger">*</span></label>
                                <select id="seleccionarProveedor" class="form-control" required><option value="">Seleccione un proveedor</option></select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3 mb-3">
                                <label class="cmp-label" for="tipoPagoCompra">Tipo de pago<span class="text-danger">*</span></label>
                                <select class="form-control" name="tipoPagoCompra" id="tipoPagoCompra" required><option value="">Cargando...</option></select>
                            </div>
                            <div class="col-12 col-md-4 mb-2">
                                <label class="cmp-label" for="fecha_emision">Fecha de emisión<span class="text-danger">*</span></label>
                                <input class="form-control" type="date" id="fecha_emision" name="fecha_emision" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-12 col-md-4 mb-2">
                                <label class="cmp-label" for="fecha_entrega">Fecha de recibido<span class="text-danger">*</span></label>
                                <input class="form-control" type="date" id="fecha_entrega" name="fecha_entrega" min="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-12 col-md-4 mb-2">
                                <label class="cmp-label" for="fecha_vencimiento">Fecha de vencimiento<span class="text-danger">*</span></label>
                                <input class="form-control" type="date" id="fecha_vencimiento" name="fecha_vencimiento" value="{{ date('Y-m-d') }}" required readonly>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="cmp-panel">
                    <div class="cmp-panel-head">
                        <h3 class="cmp-panel-title"><i class="fa fa-cubes"></i>Productos</h3>
                        <span id="contadorProductosCompra" class="cmp-count">0 productos</span>
                    </div>
                    <div class="cmp-search-band">
                        <div class="cmp-search-control">
                            <i class="fa fa-search"></i>
                            <input type="search" id="buscarProductoCompra" class="form-control" placeholder="Buscar por nombre, ID, código de barras o código estatal" autocomplete="off">
                            <button type="button" id="abrirBuscadorCompra"><i class="fa fa-plus mr-1"></i> Agregar</button>
                        </div>
                    </div>
                    <div class="cmp-cart-tools">
                        <div class="cmp-cart-filter">
                            <i class="fa fa-filter"></i>
                            <input type="search" id="filtrarCarritoCompra" class="form-control form-control-sm" placeholder="Filtrar productos agregados" autocomplete="off">
                        </div>
                        <span class="text-muted" style="font-size:10px;">50 líneas por página</span>
                    </div>
                    <div class="cmp-table-wrap">
                        <table class="table cmp-table">
                            <thead>
                                <tr><th>Producto</th><th>Precio unitario</th><th>Cantidad</th><th>Unidad de compra</th><th>Vencimiento</th><th class="text-right">Subtotal</th><th class="text-right">ISV</th><th class="text-right">Total</th><th></th></tr>
                            </thead>
                            <tbody id="productosCompraBody"></tbody>
                        </table>
                        <div id="carritoCompraVacio" class="cmp-empty"><i class="fa fa-shopping-basket"></i>No hay productos en la compra.</div>
                    </div>
                    <div id="paginacionCompra" class="cmp-pagination d-none">
                        <span id="paginacionInfoCompra" class="cmp-pagination-info"></span>
                        <div>
                            <button type="button" id="paginaAnteriorCompra" class="btn btn-white btn-sm" title="Página anterior"><i class="fa fa-chevron-left"></i></button>
                            <button type="button" id="paginaSiguienteCompra" class="btn btn-white btn-sm" title="Página siguiente"><i class="fa fa-chevron-right"></i></button>
                        </div>
                    </div>
                    <div class="cmp-footer">
                        <div class="cmp-footer-spacer"><span id="resumenLineasCompra">Sin líneas pendientes</span></div>
                        <div class="cmp-total"><span>Subtotal</span><strong id="subTotalGeneral">L. 0.00</strong></div>
                        <div class="cmp-total"><span>ISV</span><strong id="isvGeneral">L. 0.00</strong></div>
                        <div class="cmp-total grand"><span>Total</span><strong id="totalGeneral">L. 0.00</strong></div>
                        <button type="submit" id="guardarCompraBtn" class="cmp-save"><i class="fa fa-save mr-1"></i> Guardar compra</button>
                    </div>
                </section>
            </form>
        </div>
    </div>

    <x-buscador-producto
        id-modal="buscadorProductoCompra"
        callback="compraSeleccionarProducto"
        :con-stock-default="false"
        :use-top-preview="false"
    />

    @push('scripts')
    <script>
        window.compraProductoConfig = {
            fechaHoy: @json(date('Y-m-d')),
            numeroOrden: @json($ordenNumero->numero),
            catalogoUrl: @json(asset('catalogo')),
            noImagenUrl: @json(asset('catalogo/noimage.png'))
        };
    </script>
    <script src="{{ asset('js/js_proyecto/inventario/compra-producto.js') }}?v={{ filemtime(public_path('js/js_proyecto/inventario/compra-producto.js')) }}"></script>
    @endpush
</div>
