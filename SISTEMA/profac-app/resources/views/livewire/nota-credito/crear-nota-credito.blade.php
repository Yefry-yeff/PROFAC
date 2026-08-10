<div>
    @push('styles')
    <style>
    :root { --nc-grad: linear-gradient(135deg,#f39c12 0%,#e05a00 100%); --nc-red:#e67e22; --nc-radius:8px; --nc-shadow:0 2px 8px rgba(0,0,0,.10); }
    .rnc-card { border:1px solid #e8d5bf; border-radius:var(--nc-radius); box-shadow:var(--nc-shadow); background:#fff; overflow:visible; margin-bottom:20px; }
    .rnc-card-header { background:var(--nc-grad); padding:12px 20px; border-radius:var(--nc-radius) var(--nc-radius) 0 0; display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
    .rnc-card-header h5 { margin:0; color:#fff; font-size:.85rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; display:flex; align-items:center; gap:8px; }
    .rnc-card-body { padding:18px 20px; }
    .btn-nc-primary { background:var(--nc-grad)!important; color:#fff!important; border:none!important; border-radius:6px!important; font-weight:600!important; padding:8px 20px; box-shadow:0 2px 6px rgba(224,90,0,.25); }
    .btn-nc-primary:hover { filter:brightness(1.05); color:#fff!important; }
    .btn-rnc-action { background:rgba(255,255,255,.18)!important; color:#fff!important; border:1.5px solid rgba(255,255,255,.5)!important; border-radius:5px!important; font-weight:600!important; font-size:.78rem; padding:5px 14px; transition:background .18s; white-space:nowrap; cursor:pointer; }
    .btn-rnc-action:hover { background:rgba(255,255,255,.30)!important; color:#fff!important; }
    #tbl_productos thead th, #tbl_productos_lista thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; border-bottom:2px solid #f2d49a; white-space:nowrap; }
    #tbl_productos tbody td, #tbl_productos_lista tbody td { font-size:.85rem; vertical-align:middle; }
    #tbl_productos tbody tr:hover>td, #tbl_productos_lista tbody tr:hover>td { background:#fffcf5; }
    .nc-tabs.nav-tabs { border-bottom:2px solid #f2d49a; }
    .nc-tabs.nav-tabs .nav-link { color:#7d3f00; font-weight:600; border:none; border-bottom:3px solid transparent; }
    .nc-tabs.nav-tabs .nav-link.active { color:#e05a00; border-bottom:3px solid #e67e22; background:transparent; }
    .nc-totales { background:#fdfaf5; border:1px solid #ead9c8; border-radius:7px; padding:14px 18px; }
    .nc-totales .form-group { margin-bottom:10px; }
    .nc-totales label { font-size:.78rem; font-weight:600; color:#7d3f00; }
    .nc-section-title { font-size:.72rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#e67e22; border-bottom:2px solid #fdebd0; padding-bottom:6px; margin-bottom:14px; display:flex; align-items:center; gap:6px; }
    .nc-destino { background:#f8fafc; border:1px solid #dbe4ee; border-radius:7px; padding:16px; margin-top:18px; }
    .nc-destino-resumen { display:none; margin-top:12px; margin-bottom:0; font-size:.84rem; }
    .modal-header-nc { background:var(--nc-grad); color:#fff; border-radius:calc(var(--nc-radius) - 1px) calc(var(--nc-radius) - 1px) 0 0; padding:14px 20px; }
    .modal-header-nc h4, .modal-header-nc .modal-title { color:#fff; font-size:.95rem; font-weight:700; margin:0; }
    .modal-header-nc .close { color:#fff; opacity:.85; text-shadow:none; font-size:1.4rem; }
    .modal-header-nc .close:hover { opacity:1; }
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2><i class="fa fa-undo mr-2" style="color:#e67e22"></i>Registrar Devolución de Producto</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Notas de Crédito</li>
                <li class="breadcrumb-item active"><strong>Registrar Devolución</strong></li>
            </ol>
        </div>

    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="rnc-card">
                    <div class="rnc-card-header">
                        <h5><i class="fa fa-user"></i> Seleccionar Cliente y Factura</h5>
                    </div>
                    <div class="rnc-card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-6">
                                <label for="cliente" class="col-form-label focus-label">Seleccionar
                                    Cliente:</label>
                                <select id="cliente" name="cliente" class="form-group form-control" style=""
                                    onchange="obtenerFacturasDeCliente()" data-parsley-required>
                                    <option value="" selected disabled>--Seleccionar Cliente--</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-12 col-md-6">
                                <label for="factura" class="col-form-label focus-label">Seleccionar
                                    Factura:</label>
                                <select id="factura" name="factura" class="form-group form-control" style=""
                                    data-parsley-required onchange="limpiarTablas()">
                                    <option value="" selected disabled>--Seleccionar una Factura--</option>
                                </select>
                            </div>

                        </div>
                        <div class="row ">
                            <div class="col-12">
                                <button id="solicitarFactura" onclick="datosFactura()" class="btn btn-nc-primary mt-4"><i
                                        class="fa-solid fa-paper-plane"></i> Solicitar Factura</button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="rnc-card">
                    <div class="rnc-card-header">
                        <h5><i class="fa fa-file-invoice"></i> Detalle de Factura</h5>
                    </div>

                    <div class="rnc-card-body">
                        <form id="selec_nota_form" name="selec_nota_form" data-parsley-validate>
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-3">

                                    <div class="form-group">
                                        <label for="codigo_factura">Código de factura:</label>
                                        <input type="text" name="codigo_factura" id="codigo_factura"
                                            class="form-control" readonly required>
                                    </div>

                                </div>

                                <div class="col-12 col-sm-12 col-md-3">

                                    <div class="form-group">
                                        <label for="fecha">Fecha de emisión: </label>
                                        <input type="date" name="fecha" id="fecha" class="form-control"
                                            readonly required>
                                    </div>

                                </div>

                                <div class="col-12 col-sm-12 col-md-3">

                                    <div class="form-group">
                                        <label for="tipo_pago">Tipo de factura:</label>
                                        <input type="text" name="tipo_pago" id="tipo_pago" class="form-control"
                                            readonly required>
                                    </div>

                                </div>

                                <div class="col-12 col-sm-12 col-md-3">

                                    <div class="form-group">
                                        <label for="tipo_venta">Tipo de venta:</label>
                                        <input type="text" name="tipo_venta" id="tipo_venta" class="form-control"
                                            readonly required>
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-4">

                                    <div class="form-group">
                                        <label for="codigo_cliente">Código de cliente:</label>
                                        <input type="text" name="codigo_cliente" id="codigo_cliente"
                                            class="form-control" readonly required>
                                    </div>

                                </div>

                                <div class="col-12 col-sm-12 col-md-4">

                                    <div class="form-group">
                                        <label for="rtn">RTN:</label>
                                        <input type="text" name="rtn" id="rtn" class="form-control"
                                            readonly required>
                                    </div>

                                </div>

                                <div class="col-12 col-sm-12 col-md-4">

                                    <div class="form-group">
                                        <label for="nombre_cliente">Nombre de cliente:</label>
                                        <input type="text" name="nombre_cliente" id="nombre_cliente"
                                            class="form-control" readonly required>
                                    </div>

                                </div>



                            </div>

                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-4">

                                    <div class="form-group">
                                        <label for="vendedor">Vendido por:</label>
                                        <input type="text" name="vendedor" id="vendedor" class="form-control"
                                            readonly required>
                                    </div>

                                </div>

                                <div class="col-12 col-sm-12 col-md-4">

                                    <div class="form-group">
                                        <label for="facturado">Facturado por:</label>
                                        <input type="text" name="facturado" id="facturado" class="form-control"
                                            readonly required>
                                    </div>

                                </div>

                                <div class="col-12 col-sm-12 col-md-4">

                                    <div class="form-group">
                                        <label for="fecha_registro">Registado en sistema:</label>
                                        <input type="text" name="fecha_registro" id="fecha_registro"
                                            class="form-control" readonly required>
                                    </div>

                                </div>



                            </div>
                        </form>

                        {{-- Campo oculto: tipo de nota de crédito --}}
                        <input type="hidden" id="tipo_nota_credito" name="tipo_nota_credito" value="producto"
                            form="guardar_devolucion">

                        {{-- Pestañas: Por Producto / Por Descuento --}}
                        <ul class="nav nav-tabs nc-tabs mt-3" id="tabs_tipo_nota" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-producto-link" data-toggle="tab"
                                    href="#tab_productos_factura" role="tab"
                                    onclick="cambiarTipoNota('producto')">
                                    <i class="fa fa-boxes"></i> Por Producto
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-descuento-link" data-toggle="tab"
                                    href="#tab_descuento_factura" role="tab"
                                    onclick="cambiarTipoNota('descuento')">
                                    <i class="fa fa-tag"></i> Por Descuento
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            {{-- ===== TAB: POR PRODUCTO ===== --}}
                            <div class="tab-pane fade show active" id="tab_productos_factura" role="tabpanel">
                                <div class="table-responsive mt-3">
                                    <table id="tbl_productos" class="table table-striped table-bordered table-hover">
                                        <thead class="">
                                            <tr>
                                                <th>Producto</th>
                                                <th>Bodega</th>
                                                <th>Precio Unidad en Lps</th>
                                                <th>Cantidad</th>
                                                <th>Unidad de medida</th>
                                                <th>Sub total</th>
                                                <th>ISV</th>
                                                <th>Total</th>
                                                <th>Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- ===== TAB: POR DESCUENTO ===== --}}
                            <div class="tab-pane fade" id="tab_descuento_factura" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-12 col-sm-12 col-md-8">
                                        <div class="form-group">
                                            <label for="comentario_descuento" class="col-form-label focus-label">
                                                Comentario del descuento:<span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="comentario_descuento"
                                                id="comentario_descuento" rows="5"
                                                placeholder="Describa el motivo del descuento..."
                                                form="guardar_devolucion"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="monto_descuento_mostrar" class="col-form-label focus-label">
                                                Monto del descuento (L.):<span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" min="0" class="form-control"
                                                id="monto_descuento_mostrar" name="monto_descuento_mostrar"
                                                placeholder="0.00"
                                                oninput="calcularDescuento(this.value)">
                                            <input type="hidden" id="monto_descuento" name="monto_descuento"
                                                value="0" form="guardar_devolucion">
                                        </div>
                                    </div>
                                </div>

                                {{-- RESUMEN DE LA FACTURA EN TAB DESCUENTO --}}
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <p class="nc-section-title"><i class="fa fa-receipt"></i> Resumen de Factura Original</p>
                                    </div>
                                </div>

                                <div class="nc-totales">
                                <div class="row">
                                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                        <label class="col-form-label" for="subTotalGeneralMostrar_desc">Sub Total L.<span class="text-danger">*</span></label>
                                    </div>
                                    <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                        <input type="text" placeholder="Sub total " id="subTotalGeneralMostrar_desc"
                                            name="subTotalGeneralMostrar_desc" class="form-control" data-parsley-required
                                            autocomplete="off" readonly>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                        <label class="col-form-label" for="subTotalGeneralGrabadoMostrar_desc">Sub Total Grabado L.<span class="text-danger">*</span></label>
                                    </div>
                                    <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                        <input type="text" placeholder="Sub total " id="subTotalGeneralGrabadoMostrar_desc"
                                            name="subTotalGeneralGrabadoMostrar_desc" class="form-control" data-parsley-required
                                            autocomplete="off" readonly>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                        <label class="col-form-label" for="subTotalGeneralExcentoMostrar_desc">Sub Total Excento L.<span class="text-danger">*</span></label>
                                    </div>
                                    <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                        <input type="text" placeholder="Sub total " id="subTotalGeneralExcentoMostrar_desc"
                                            name="subTotalGeneralExcentoMostrar_desc" class="form-control" data-parsley-required
                                            autocomplete="off" readonly>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                        <label class="col-form-label" for="isvGeneralMostrar_desc">ISV L.<span class="text-danger">*</span></label>
                                    </div>
                                    <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                        <input type="text" placeholder="ISV " id="isvGeneralMostrar_desc"
                                            name="isvGeneralMostrar_desc" class="form-control" data-parsley-required
                                            autocomplete="off" readonly>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                        <label class="col-form-label" for="totalGeneralMostrar_desc">Total L.<span class="text-danger">*</span></label>
                                    </div>
                                    <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                        <input type="text" placeholder="Total  " id="totalGeneralMostrar_desc"
                                            name="totalGeneralMostrar_desc" class="form-control" data-parsley-required
                                            autocomplete="off" readonly>
                                    </div>
                                </div>
                                </div>{{-- fin nc-totales --}}
                            </div>
                        </div>{{-- fin tab-content --}}



                        <br>
                        {{-- Resumen de la factura seleccionada (solo visible en modo Por Producto) --}}
                        <div id="seccion_resumen_factura">
                            <p class="nc-section-title"><i class="fa fa-receipt"></i> Resumen de Factura Original</p>
                            <div class="nc-totales">
                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="subTotalGeneralMostrar">Sub Total L.<span
                                            class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="text" placeholder="Sub total " id="subTotalGeneralMostrar"
                                        name="subTotalGeneralMostrar" class="form-control" data-parsley-required
                                        autocomplete="off" readonly>

                                    <input id="subTotalGeneral" name="subTotalGeneral" type="hidden" value=""
                                        required>
                                </div>
                            </div>

                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="subTotalGeneralGrabadoMostrar">Sub Total
                                        Grabado L.<span class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="text" placeholder="Sub total " id="subTotalGeneralGrabadoMostrar"
                                        name="subTotalGeneralGrabadoMostrar" class="form-control" data-parsley-required
                                        autocomplete="off" readonly>

                                    <input id="subTotalGeneralGrabado" name="subTotalGeneralGrabado" type="hidden"
                                        value="" required>
                                </div>
                            </div>

                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="subTotalGeneralExcentoMostrar">Sub Total
                                        Excento L.<span class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="text" placeholder="Sub total " id="subTotalGeneralExcentoMostrar"
                                        name="subTotalGeneralExcentoMostrar" class="form-control" data-parsley-required
                                        autocomplete="off" readonly>

                                    <input id="subTotalGeneralExcento" name="subTotalGeneralExcento" type="hidden"
                                        value="" required>
                                </div>
                            </div>

                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="isvGeneralMostrar">ISV L.<span
                                            class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="text" placeholder="ISV " id="isvGeneralMostrar"
                                        name="isvGeneralMostrar" class="form-control" data-parsley-required
                                        autocomplete="off" readonly>
                                    <input id="isvGeneral" name="isvGeneral" type="hidden" value="" required>
                                </div>
                            </div>

                            <div class="row">

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="totalGeneralMostrar">Total L.<span
                                            class="text-danger">*</span></label>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input type="text" placeholder="Total  " id="totalGeneralMostrar"
                                        name="totalGeneralMostrar" class="form-control" data-parsley-required
                                        autocomplete="off" readonly>

                                    <input id="totalGeneral" name="totalGeneral" type="hidden" value="" required>
                                </div>
                            </div>
                            </div>{{-- fin nc-totales --}}
                        </div>






                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="rnc-card">
                    <div class="rnc-card-header">
                        <h5><i class="fa fa-file-invoice-dollar"></i> Nota de Crédito</h5>
                    </div>

                    <div class="rnc-card-body">

                        <form onkeydown="return event.key != 'Enter';" autocomplete="off"
                            id="guardar_devolucion" name="guardar_devolucion" data-parsley-validate>

                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-6">
                                    <label for="motivo_nota" class="col-form-label focus-label">
                                        Seleccionar motivo de nota de credito:</label>
                                    <select id="motivo_nota" name="motivo_nota"
                                        class="form-group form-control" style=""
                                        data-parsley-required form="guardar_devolucion">
                                        <option value="" selected disabled>--Seleccionar Motivo--</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-12 col-md-6">
                                    <label for="comentario" class="col-form-label focus-label">
                                        Comentario de nota de crédito:</label>
                                    <textarea class="form-group form-control" name="comentario"
                                        id="comentario" cols="30" rows="4"></textarea>
                                </div>
                            </div>

                        </form>

                        {{-- Lista de productos seleccionados (visible solo en modo Por Producto) --}}
                        <div id="seccion_lista_productos" class="table-responsive mt-4">
                            <table id="tbl_productos_lista"
                                class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Bodega</th>
                                        <th>Seccion</th>
                                        <th>Precio Unidad en Lps</th>
                                        <th>Cantidad</th>
                                        <th>Unidad de medida</th>
                                        <th>Sub total</th>
                                        <th>ISV</th>
                                        <th>Total</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoLista">
                                </tbody>
                            </table>
                        </div>

                        {{-- ===== TOTALES DE LA NOTA DE CREDITO (siempre visible) ===== --}}
                        <hr>
                        <p class="nc-section-title"><i class="fa fa-calculator"></i> Resumen de Totales</p>

                        <div class="nc-totales">
                        <div class="row">
                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="subTotalGeneralCreditoMostrar">Sub Total
                                        L.<span class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input id="subTotalGeneralCreditoMostrar" class="form-control" type="text"
                                        placeholder="Sub total " disabled>
                                    <input type="hidden" step="any" placeholder="Sub total "
                                        id="subTotalGeneralCredito" name="subTotalGeneralCredito"
                                        class="form-control" value="0" min="0" data-parsley-required
                                        autocomplete="off" form="guardar_devolucion">
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label"
                                        for="subTotalGeneralGrabadoCreditoMostrar">Sub Total Grabado L.<span
                                            class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input id="subTotalGeneralGrabadoCreditoMostrar" class="form-control"
                                        type="text" placeholder="Sub total " disabled>
                                    <input type="hidden" step="any" placeholder="Sub total "
                                        id="subTotalGeneralGrabadoCredito" name="subTotalGeneralGrabadoCredito"
                                        class="form-control" value="0" min="0" data-parsley-required
                                        autocomplete="off" form="guardar_devolucion">
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label"
                                        for="subTotalGeneralExcentoCreditoMostrar">Sub Total Excento L.<span
                                            class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input id="subTotalGeneralExcentoCreditoMostrar" class="form-control"
                                        type="text" placeholder="Sub total " disabled>
                                    <input type="hidden" step="any" placeholder="Sub total "
                                        id="subTotalGeneralExcentoCredito" name="subTotalGeneralExcentoCredito"
                                        class="form-control" value="0" min="0" data-parsley-required
                                        autocomplete="off" form="guardar_devolucion">
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="isvGeneralCreditoMostrar">ISV L.<span
                                            class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input id="isvGeneralCreditoMostrar" type="text" class="form-control"
                                        placeholder="ISV " disabled>
                                    <input type="hidden" step="any" id="isvGeneralCredito"
                                        name="isvGeneralCredito" class="form-control" min="0" value="0"
                                        data-parsley-required autocomplete="off" form="guardar_devolucion">
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                    <label class="col-form-label" for="totalGeneralCreditoMostrar">Total L.<span
                                            class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">
                                    <input id="totalGeneralCreditoMostrar" class="form-control" type="text"
                                        placeholder="Total " disabled>
                                    <input type="hidden" step="any" id="totalGeneralCredito"
                                        name="totalGeneralCredito" min="0" value="0" data-parsley-required
                                        autocomplete="off" form="guardar_devolucion">
                                </div>
                            </div>
                        </div>{{-- fin nc-totales --}}

                        <div class="nc-destino">
                            <p class="nc-section-title"><i class="fa fa-random"></i> Aplicación de la nota de crédito</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="destinoCreditoCrear">¿Qué desea hacer con el crédito? <span class="text-danger">*</span></label>
                                        <select id="destinoCreditoCrear" name="destinoCredito" class="form-control" required
                                            form="guardar_devolucion" onchange="actualizarDestinoCreditoCrear()">
                                            <option value="">— Seleccione —</option>
                                            <option value="saldos">Aplicar automáticamente a saldos pendientes</option>
                                            <option value="reembolso">Reembolsar todo el crédito</option>
                                            <option value="mixto">Mixto: aplicar saldos y reembolsar el excedente</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Crédito de esta nota</label>
                                        <input id="creditoPrevistoCrear" type="text" class="form-control" readonly value="L 0.00">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Saldos pendientes</label>
                                        <input id="saldoPendienteCrear" type="text" class="form-control" readonly value="L 0.00" data-valor="0">
                                    </div>
                                </div>
                            </div>
                            <div id="resumenDestinoCreditoCrear" class="alert alert-info nc-destino-resumen"></div>
                            <div id="detalleAplicacionCreditoCrear" class="table-responsive" style="display:none;">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Factura destino</th>
                                            <th class="text-right">Saldo actual</th>
                                            <th class="text-right">Se aplicará</th>
                                            <th class="text-right">Saldo restante</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detalleAplicacionCreditoCrearBody"></tbody>
                                </table>
                            </div>

                            <div id="panelReembolsoCrear" style="display:none;">
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="bancoReembolsoCrear">Cuenta de salida <span class="text-danger">*</span></label>
                                            <select id="bancoReembolsoCrear" name="bancoReembolso" class="form-control" form="guardar_devolucion">
                                                <option value="">— Seleccione —</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="metodoReembolsoCrear">Método <span class="text-danger">*</span></label>
                                            <select id="metodoReembolsoCrear" name="metodoReembolso" class="form-control" form="guardar_devolucion">
                                                <option value="">— Seleccione —</option>
                                                <option value="1">Efectivo</option>
                                                <option value="2">Transferencia bancaria</option>
                                                <option value="3">Cheque</option>
                                                <option value="4">Link de pago</option>
                                                <option value="5">POS</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="fechaReembolsoCrear">Fecha <span class="text-danger">*</span></label>
                                            <input id="fechaReembolsoCrear" name="fechaReembolso" type="date" class="form-control"
                                                value="{{ date('Y-m-d') }}" form="guardar_devolucion">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="referenciaReembolsoCrear">Referencia</label>
                                            <input id="referenciaReembolsoCrear" name="referenciaReembolso" type="text" maxlength="100"
                                                class="form-control" form="guardar_devolucion">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="comprobanteReembolsoCrear">Comprobante</label>
                                            <input id="comprobanteReembolsoCrear" name="comprobanteReembolso" type="file"
                                                accept=".pdf,.jpg,.jpeg,.png" class="form-control" form="guardar_devolucion">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <br>

                        <button type="submit" id="btn_guardar_nota_credito" form="guardar_devolucion"
                            class="btn btn-nc-primary"><i class="fa fa-check-circle mr-1"></i>Cerrar Nota de Credito</button>

                    </div>
                </div>
            </div>
        </div>
        <!-- Button trigger modal -->

        <!-- Modal -->
        <div class="modal fade" id="modal_devolver_producto" tabindex="-1" role="dialog"
            aria-labelledby="modal_devolver_producto" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered  modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header modal-header-nc">
                        <h4 class="" id=""><i class="fa fa-box-open mr-2"></i>Datos de Producto</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="cerrarModal('modal_devolver_producto')">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="form_producto_devolver" name="form_producto_devolver" data-parsley-validate>
                            <input type="hidden" id="idFactura" name="idFactura" value="0">
                            <input type="hidden" id="idProducto" name="idProducto" value="0">
                            <input type="hidden" id="subtotalproducto" name="subtotalproducto" value="0">
                            <input type="hidden" id="porc_descuento" name="porc_descuento" value="0">
                            <input type="hidden" id="idMedidaVenta" name="idMedidaVenta" value="0">
                            <input type="hidden" id="unidad_venta" name="unidad_venta" value="0">
                            <input type="hidden" id="isvPorcentaje" name="isvPorcentaje" value="0">
                            <input type="hidden" id="isvVenta" name="isvVenta" value="0">
                            <input type="hidden" id="totalVenta" name="totalVenta" value="0">
                            <div class="row">

                                <div class="col-12 col-md-6">
                                    <label for="nombre" class="col-form-label focus-label">Nombre de producto:<span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" required type="text" id="nombre"
                                        name="nombre" data-parsley-required readonly>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="unidad" class="col-form-label focus-label">Unidad de Medida:<span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" required type="text" id="unidad"
                                        name="unidad" data-parsley-required readonly>
                                </div>


                            </div>

                            <div class="row">

                                <div class="col-12 col-md-12">
                                    <label for="precio" class="col-form-label focus-label">Precio de producto:<span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" required type="text" step="any"
                                        id="precioMostrar" name="precioMostrar" disabled>

                                    <input required type="hidden" step="any" id="precio" name="precio"
                                        data-parsley-required readonly>

                                    <div id="descuentoInfo" class="alert alert-warning py-2 px-3 mt-2 mb-0" style="display:none; font-size:.82rem;">
                                        <i class="fa fa-info-circle"></i>
                                        Esta factura tiene un <strong id="descuentoInfoPorcentaje"></strong> de descuento aplicado.
                                        Precio con descuento: <strong id="descuentoInfoPrecio"></strong> por unidad.
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="cantidadMaxima" class="col-form-label focus-label">Cantidad maxima
                                        permitida:<span class="text-danger">*</span></label>
                                    <input class="form-control" value="0" required type="number"
                                        id="cantidadMaxima" name="cantidadMaxima" data-parsley-required disabled>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="cantidad" class="col-form-label focus-label">Cantidad a devolver:<span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" required type="number" id="cantidad"
                                        name="cantidad" data-parsley-required>
                                </div>


                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <label for="bodega" class="col-form-label focus-label">Bodega de destino <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control m-b" name="bodega" id="bodega" required
                                        data-parsley-required>
                                        <option value="" selected disabled>---Seleccione una bodega de destino---
                                        </option>

                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <label for="segmento" class="col-form-label focus-label">Segmento de destino <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control m-b" name="segmento" id="segmento" required
                                        data-parsley-required>
                                        <option value="" selected disabled>---Seleccione una segmento de
                                            destino---</option>

                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <label for="seccion" class="col-form-label focus-label">Sección de destino <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control m-b" name="seccion" id="seccion" required
                                        data-parsley-required>
                                        <option value="" selected disabled>---Seleccione una sección de
                                            destino---</option>

                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="cerrarModal('modal_devolver_producto')">Cerrar</button>
                        <button type="button" class="btn btn-nc-primary" onclick="agregarProductoLista()">Agregar a
                            Nota de Credito</button>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- Modal de Impresión de Nota de Crédito -->
    <div class="modal fade" id="modal_imprimir_nota_credito" tabindex="-1" role="dialog"
        aria-labelledby="modal_imprimir_nota_credito" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white border-bottom-0">
                    <div>
                        <h4 class="modal-title mb-0">
                            <i class="fa fa-check-circle"></i> Nota de Crédito Registrada
                        </h4>
                        <p class="modal-text-small text-light mb-0 mt-1" style="font-size: 0.9rem;">
                            La nota de crédito ha sido creada exitosamente en el sistema
                        </p>
                    </div>
                    <button type="button" class="close text-white" aria-label="Close" onclick="finalizarYContinuar()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="background-color: #f8f9fa;">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-dark mb-3">
                                <i class="fa fa-print text-primary"></i> 
                                <strong>Seleccione las opciones de impresión</strong>
                            </h5>
                            <p class="text-muted small">
                                Puede imprimir el original y copia de la nota de crédito. 
                                Se abrirá una nueva ventana con el documento listo para imprimir.
                            </p>
                        </div>
                    </div>

                    <div id="contenido_impresion" class="print-area" style="display:none;">
                        <!-- Contenido para impresión será cargado aquí -->
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-6 mb-3">
                            <button type="button" class="btn btn-primary btn-block py-3" onclick="imprimirNotaCredito('original')">
                                <div>
                                    <i class="fa fa-print fa-lg"></i>
                                </div>
                                <div style="margin-top: 8px;">
                                    <strong>Imprimir Original</strong>
                                    <br>
                                    <small class="text-muted">Copia oficial</small>
                                </div>
                            </button>
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <button type="button" class="btn btn-info btn-block py-3" onclick="imprimirNotaCredito('copia')">
                                <div>
                                    <i class="fa fa-copy fa-lg"></i>
                                </div>
                                <div style="margin-top: 8px;">
                                    <strong>Imprimir Copia</strong>
                                    <br>
                                    <small class="text-muted">Copia de archivo</small>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info border-left-4" style="border-left: 4px solid #17a2b8;">
                                <i class="fa fa-info-circle"></i> 
                                <strong>Información:</strong> 
                                Seleccione qué copia desea imprimir. Se abrirá en una nueva ventana del navegador.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary" onclick="finalizarYContinuar()">
                        <i class="fa fa-times"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-success" onclick="finalizarYContinuar()">
                        <i class="fa fa-check"></i> Finalizar
                    </button>
                </div>
            </div>
        </div>
    </div>



    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/js_proyecto/nota-credito/crear-nota-credito.js') }}"></script>
    <style>
        /* Asegurar que los modales se muestren correctamente */
        .modal.show {
            display: block !important;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-dialog {
            position: relative;
        }

        /* Estilos para modal de impresión */
        #modal_imprimir_nota_credito .modal-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 0.5rem 0.5rem 0 0;
            padding: 1.5rem;
        }

        #modal_imprimir_nota_credito .modal-body {
            background-color: #f8f9fa;
            padding: 2rem;
        }

        #modal_imprimir_nota_credito .btn {
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        #modal_imprimir_nota_credito .btn:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        #modal_imprimir_nota_credito .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
        }

        #modal_imprimir_nota_credito .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
        }

        #modal_imprimir_nota_credito .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
            border: none;
        }

        #modal_imprimir_nota_credito .btn-info:hover {
            background: linear-gradient(135deg, #117a8b 0%, #0c5460 100%);
        }

        #modal_imprimir_nota_credito .btn i {
            display: block;
            margin-bottom: 0.5rem;
        }

        /* Estilos para SweetAlert2 */
        .swal2-container {
            z-index: 2000 !important;
        }
        
        .swal2-actions {
            justify-content: center !important;
            gap: 10px !important;
        }

        .swal2-confirm, .swal2-cancel {
            min-width: 120px !important;
            padding: 10px 20px !important;
            border-radius: 4px !important;
            font-weight: 500 !important;
        }

        .swal2-confirm:focus {
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(48, 133, 214, 0.5) !important;
        }

        /* Asegurar visibilidad en impresión */
        @media print {
            .modal, .navbar, .sidebar, button, form {
                display: none !important;
            }
            .print-area {
                display: block !important;
                width: 100% !important;
            }
        }

        /* Mejoras visuales para el alert */
        .alert-info.border-left-4 {
            border-radius: 0.25rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0;
        }

        .alert-info.border-left-4 i {
            margin-right: 0.5rem;
        }

        /* Estilos para textos en el header */
        .modal-text-small {
            opacity: 0.9;
        }
    </style>
    <script>

    function obtenerProductos(idFactura) {

        //let table = $('#tbl_productos').DataTable();
        //table.destroy();



        $('#tbl_productos').DataTable({

            "language": {
                "url": "/js/plugins/dataTables/i18n/Spanish.json"
            },
            pageLength: 10,
            responsive: true,
            'ajax': {
                'url': "/nota/credito/obtener/productos",
                'data': {
                    'idFactura': idFactura,
                    "_token": "{{ csrf_token() }}"
                },
                'type': 'post'
            },
            "columns": [{
                    data: 'nombre'
                },
                {
                    data: 'bodega'
                },
                {
                    data: 'precio_unidad'
                },
                {
                    data: 'cantidad'
                },
                {
                    data: 'unidad_medida'
                },
                {
                    data: 'sub_total'
                },
                {
                    data: 'isv'
                },
                {
                    data: 'total'
                },
                {
                    data: 'opciones'
                },


            ]


        });
    }

    </script>
    @endpush

</div>
