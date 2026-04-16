<div>
    @push('styles')
        <style>
            input::-webkit-outer-spin-button,
            input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            input[type=number] {
                -moz-appearance: textfield;
            }
            @media (max-width: 767.5px) {
                .hide-container { display: none; }
            }
            .center-div { text-align: center }
            .img-size {
                width: 100%;
                height: 20rem;
                margin: 0 auto;
            }
            .tipo-factura-selector .btn {
                margin-right: 5px;
                margin-bottom: 5px;
            }
            .tipo-factura-selector .btn.active {
                box-shadow: 0 0 0 3px rgba(0,123,255,.5);
            }
        </style>
    @endpush

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- ============================================================== --}}
        {{-- SELECTOR DE TIPO DE FACTURACIÓN                                --}}
        {{-- ============================================================== --}}
        @if(!$fromFlujo)
        <div class="row mb-3">
            <div class="col-12">
                <div class="ibox">
                    <div class="ibox-content py-2">
                        <div class="d-flex align-items-center flex-wrap tipo-factura-selector">
                            <strong class="mr-3">Tipo de Facturación:</strong>
                            @foreach($tiposFactura as $tipo)
                                <button
                                    type="button"
                                    class="btn btn-sm {{ $config && $config->id == $tipo->id ? 'btn-primary active' : 'btn-outline-secondary' }}"
                                    onclick="cambiarTipoFactura('{{ $tipo->ruta_menu }}')"
                                    id="btnTipo_{{ $tipo->id }}"
                                >
                                    {{ $tipo->nombre }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="row mb-3">
            <div class="col-12">
                <a href="{{ route('flujo.ventas') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left mr-1"></i> Volver a Ventas
                </a>
            </div>
        </div>
        @endif

        {{-- ============================================================== --}}
        {{-- FORMULARIO PRINCIPAL DE FACTURACIÓN                            --}}
        {{-- ============================================================== --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <form onkeydown="return event.key != 'Enter';" autocomplete="off" id="crear_venta"
                            name="crear_venta" data-parsley-validate>

                            {{-- Campos ocultos de configuración --}}
                            <input type="hidden" id="restriccion" name="restriccion" value="{{ $config->restriccion ?? 1 }}">
                            <input type="hidden" id="tipo_venta_id" name="tipo_venta_id" value="{{ $config->tipo_venta_id ?? 2 }}">
                            <input type="hidden" id="tipo_factura_id" name="tipo_factura_id" value="{{ $config->id ?? '' }}">
                            <input name="idComprobante" id="idComprobante" type="hidden" value="">
                            <input type="hidden" id="codigo_autorizacion" name="codigo_autorizacion" value="">

                            {{-- Encabezado --}}
                            <div class="row align-items-center">
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center gap-2 flex-nowrap">
                                        <h3 class="mb-0" id="titulo_factura">
                                            {{ $config->nombre ?? 'Venta Estatal' }}:
                                        </h3>
                                        <input type="text" id="numero_venta" name="numero_venta"
                                            class="form-control form-control-sm" style="max-width: 140px;" readonly>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 d-flex align-items-center gap-2">
                                    <span class="text-muted small">Categoría del Cliente:</span>
                                    <span id="categoria_cliente_nombre" class="badge badge-info px-3 py-2"></span>
                                </div>
                            </div>

                            {{-- Campos del formulario --}}
                            <div class="row">
                                {{-- Cliente --}}
                                <div class="col-12 col-md-6">
                                    <label for="seleccionarCliente" class="col-form-label focus-label">
                                        Seleccionar Cliente:<span class="text-danger">*</span>
                                    </label>
                                    <select id="seleccionarCliente" name="seleccionarCliente"
                                        class="form-group form-control" data-parsley-required
                                        onchange="obtenerDatosCliente()">
                                        <option value="" selected disabled>--Seleccionar un cliente--</option>
                                    </select>
                                </div>

                                {{-- Nombre del cliente --}}
                                <div class="col-12 col-md-6">
                                    <label class="col-form-label focus-label">Nombre del cliente:<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="text" id="nombre_cliente_ventas"
                                        name="nombre_cliente_ventas" data-parsley-required readonly>
                                </div>

                                {{-- Código de exoneración (solo para exoneradas) --}}
                                <div class="col-12 col-md-6" id="campo_codigo_exoneracion"
                                    style="{{ ($config->requiere_codigo_exoneracion ?? false) ? '' : 'display:none' }}">
                                    <label for="codigoExoneracion" class="col-form-label focus-label">
                                        Código de Exoneración:<span class="text-danger">*</span>
                                    </label>
                                    <select id="codigoExoneracion" name="codigoExoneracion"
                                        class="form-group form-control">
                                        <option value="" selected disabled>--Seleccione un código--</option>
                                    </select>
                                </div>

                                {{-- Vendedor --}}
                                <div class="col-12 col-md-6">
                                    <label for="vendedor" class="col-form-label focus-label">
                                        Seleccionar Vendedor:<span class="text-danger">*</span>
                                    </label>
                                    <select name="vendedor" id="vendedor" class="form-group form-control" required>
                                        <option value="" selected disabled>--Seleccionar un vendedor--</option>
                                    </select>
                                </div>

                                {{-- Orden de compra (solo estatal y corporativa) --}}
                                <div class="col-12 col-md-6" id="campo_orden_compra"
                                    style="{{ ($config->requiere_orden_compra ?? false) ? '' : 'display:none' }}">
                                    <label for="ordenCompra" class="col-form-label focus-label">
                                        Seleccionar número de orden de compra:
                                    </label>
                                    <select class="form-group form-control" name="ordenCompra" id="ordenCompra">
                                        <option value="" selected disabled>--Seleccionar un número de compra--</option>
                                    </select>
                                </div>

                                {{-- RTN --}}
                                <div class="col-12 col-md-6">
                                    <label class="col-form-label focus-label">RTN:<span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="rtn_ventas" name="rtn_ventas" readonly>
                                </div>

                                {{-- Tipo de pago --}}
                                <div class="col-12 col-md-6">
                                    <label for="tipoPagoVenta" class="col-form-label focus-label">
                                        Seleccionar tipo de pago:<span class="text-danger">*</span>
                                    </label>
                                    <select class="form-group form-control" name="tipoPagoVenta" id="tipoPagoVenta"
                                        data-parsley-required onchange="validarFechaPago()">
                                    </select>
                                </div>

                                {{-- Fecha de emisión --}}
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="fecha_emision" class="col-form-label focus-label">
                                            Fecha de emisión:<span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="date" id="fecha_emision"
                                            onchange="sumarDiasCredito()" name="fecha_emision"
                                            value="{{ date('Y-m-d') }}" data-parsley-required>
                                    </div>
                                </div>

                                {{-- Fecha de vencimiento --}}
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="fecha_vencimiento" class="col-form-label focus-label text-warning">
                                            Fecha de vencimiento:
                                        </label>
                                        <input class="form-control" type="date" id="fecha_vencimiento"
                                            name="fecha_vencimiento" value="" data-parsley-required
                                            min="{{ date('Y-m-d') }}" readonly>
                                    </div>
                                </div>

                                {{-- Descuento --}}
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="porDescuento" class="col-form-label focus-label">
                                            Descuento aplicado %:<span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="number" min="0"
                                            max="{{ $config->max_descuento ?? 50 }}"
                                            value="0" id="porDescuento" name="porDescuento"
                                            onchange="calcularTotalesInicioPagina()" data-parsley-required>
                                    </div>
                                </div>

                                {{-- Nota --}}
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="nota" class="col-form-label focus-label">Nota:</label>
                                        <textarea class="form-control" id="nota_comen" name="nota_comen" cols="30" rows="3" maxlength="250"></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Selección de producto --}}
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <label class="col-form-label focus-label">
                                        Seleccionar Producto:<span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" id="codigoProductoBuscar" class="form-control"
                                            placeholder="ID o nombre del producto…" autocomplete="off"
                                            onkeydown="if(event.key==='Enter'){buscarPorCodigo(this.value);return false;}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary" title="Buscar producto"
                                                onclick="limpiarProducto(); window['abrirBuscador_buscadorProductoUnificado'](document.getElementById('codigoProductoBuscar').value||'')">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small id="productoSeleccionadoLabel" class="text-success font-weight-bold mt-1 d-block d-none"></small>
                                    <select id="seleccionarProducto" name="seleccionarProducto" class="d-none">
                                        <option value="" selected disabled></option>
                                    </select>
                                </div>

                                {{-- Categoría de precio --}}
                                <div class="col-12 col-md-6">
                                    <label class="col-form-label focus-label">
                                        Categoría Precio Producto:<span class="text-danger">*</span>
                                    </label>
                                    <select id="categoria_cliente_venta_id" name="categoria_cliente_venta_id"
                                        class="form-group form-control" onchange="habilitarBodega()">
                                        <option value="" selected disabled>--Seleccione primero un producto--</option>
                                    </select>
                                </div>

                                {{-- Bodega --}}
                                <div class="col-12 col-md-6">
                                    <label class="col-form-label focus-label">
                                        Seleccionar bodega:<span class="text-danger">*</span>
                                    </label>
                                    <select id="bodega" name="bodega" class="form-group form-control"
                                        onchange="prueba()">
                                        <option value="" selected disabled>--Seleccione una categoría primero--</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Imágenes y botón añadir --}}
                            <div class="row">
                                <div class="col-12 col-md-6 mt-4">
                                    <div class="text-center">
                                        <a id="detalleProducto" href="" class="font-bold h3 d-none text-success" target="_blank">
                                            <i class="fa-solid fa-circle-info"></i> Ver Detalles De Producto
                                        </a>
                                    </div>
                                    <div id="carouselProducto" class="carousel slide mt-2" data-ride="carousel">
                                        <div id="bloqueImagenes" class="carousel-inner"></div>
                                        <a class="carousel-control-prev" href="#carouselProducto" role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                        <a class="carousel-control-next" href="#carouselProducto" role="button" data-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div id="botonAdd" class="my-4 text-center d-none">
                                        <button type="button" class="p-3 btn-rounded btn btn-success"
                                            style="font-weight: 900;" onclick="agregarProductoCarrito()">
                                            Añadir Producto a venta <i class="fa-solid fa-cart-plus"></i>
                                        </button>
                                    </div>
                                    <div id="historialPreciosPanel" class="d-none mt-3">
                                        <h5 class="mb-2 text-dark">
                                            <i class="fa fa-history text-info"></i> Últimas 5 ventas a este cliente
                                        </h5>
                                        <div id="historialPreciosCuerpo"><p class="text-muted small">Cargando...</p></div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            {{-- Encabezado tabla productos --}}
                            <div class="hide-container">
                                <p>Nota: El campo "Unidad" describe la unidad de medida para la venta del producto - seguido del numero de unidades a restar del inventario</p>
                                <div class="row no-gutters">
                                    <div class="form-group col-3">
                                        <div class="d-flex">
                                            <div style="width:100%">
                                                <label class="sr-only">Producto</label>
                                                <input type="text" placeholder="Producto" class="form-control" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-1">
                                        <input type="number" placeholder="Bodega" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-2">
                                        <input type="number" placeholder="Opciones" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-1">
                                        <input type="number" placeholder="Precio Unidad" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-1">
                                        <input type="text" placeholder="Cantidad" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-1">
                                        <input type="text" placeholder="Unidad" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-1">
                                        <input type="number" placeholder="Sub total" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-1">
                                        <input type="number" placeholder="ISV" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-1">
                                        <input type="number" placeholder="Total" class="form-control" disabled>
                                    </div>
                                </div>
                            </div>

                            {{-- Contenedor de productos dinámicos --}}
                            <div id="divProductos"></div>
                            <hr>

                            {{-- Totales --}}
                            <div class="row">
                                <div class="form-group col-12 col-md-2 col-lg-1">
                                    <label class="col-form-label" for="descuentoMostrar">Descuento L.<span class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-md-3 col-lg-2">
                                    <input type="text" placeholder="Descuento aplicado" id="descuentoMostrar"
                                        name="descuentoMostrar" class="form-control" value="Descuento Aplicado"
                                        data-parsley-required autocomplete="off" readonly>
                                    <input type="hidden" value="0" id="porDescuentoCalculado" name="porDescuentoCalculado">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-12 col-md-2 col-lg-1">
                                    <label class="col-form-label">Sub Total L.<span class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-md-3 col-lg-2">
                                    <input type="text" placeholder="Sub total" id="subTotalGeneralMostrar"
                                        class="form-control" autocomplete="off" readonly>
                                    <input id="subTotalGeneral" name="subTotalGeneral" type="hidden" value="" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-12 col-md-2 col-lg-1">
                                    <label class="col-form-label">Sub Total Grabado L.<span class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-md-3 col-lg-2">
                                    <input type="text" placeholder="Sub total" id="subTotalGeneralGrabadoMostrar"
                                        class="form-control" autocomplete="off" readonly>
                                    <input id="subTotalGeneralGrabado" name="subTotalGeneralGrabado" type="hidden" value="" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-12 col-md-2 col-lg-1">
                                    <label class="col-form-label">Sub Total Excento L.<span class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-md-3 col-lg-2">
                                    <input type="text" placeholder="Sub total" id="subTotalGeneralExcentoMostrar"
                                        class="form-control" autocomplete="off" readonly>
                                    <input id="subTotalGeneralExcento" name="subTotalGeneralExcento" type="hidden" value="" required>
                                </div>
                            </div>
                            <div class="row" id="fila_isv" style="{{ ($config->aplica_isv ?? true) ? '' : 'display:none' }}">
                                <div class="form-group col-12 col-md-2 col-lg-1">
                                    <label class="col-form-label">ISV L.<span class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-md-3 col-lg-2">
                                    <input type="text" placeholder="ISV" id="isvGeneralMostrar"
                                        class="form-control" autocomplete="off" readonly>
                                    <input id="isvGeneral" name="isvGeneral" type="hidden" value="" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-12 col-md-2 col-lg-1">
                                    <label class="col-form-label">Total L.<span class="text-danger">*</span></label>
                                </div>
                                <div class="form-group col-12 col-md-3 col-lg-2">
                                    <input type="text" placeholder="Total" id="totalGeneralMostrar"
                                        class="form-control" autocomplete="off" readonly>
                                    <input id="totalGeneral" name="totalGeneral" type="hidden" value="" required>
                                </div>
                            </div>

                            {{-- Botón de venta --}}
                            <div class="row">
                                <div class="col-12">
                                    <button id="btn_venta_coorporativa" class="btn btn-sm btn-primary float-left m-t-n-xs">
                                        <strong>Realizar Venta</strong>
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL: Solicitar código de autorización                        --}}
        {{-- (Visible solo para tipos que requieren código)                 --}}
        {{-- ============================================================== --}}
        <div class="modal fade" id="modal_solicitar_codigo" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Solicitar código</h3>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <button class="btn btn-primary btn-large-dim" type="button" onclick="solicitarCodigo()">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                        <div id="div_imprimir" class="text-center mt-2 d-none">
                            <a id="btn_imprimir" target="_blank" class="btn add-btn btn-success text-white">
                                <i class="fa-solid fa-file-invoice"></i> Imprimir Factura
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="history.back()">Salir</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL: Ingresar código de autorización --}}
        <div class="modal fade" id="modalPermiso" data-backdrop="static" tabindex="1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Ingresar código</h3>
                    </div>
                    <div class="modal-body">
                        <form id="verificarCodigoForm" autocomplete="off" data-parsley-validate>
                            <label for="codigo" class="col-form-label focus-label">
                                Código de autorización:<span class="text-danger">*</span>
                            </label>
                            <input class="form-control" required type="text" id="codigo" name="codigo" data-parsley-required>
                        </form>
                        <span id="mensajeCodigo" class="text-danger d-none">Código incorrecto</span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="history.back()">Salir</button>
                        <button type="submit" form="verificarCodigoForm" class="btn btn-primary">Verificar</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Buscador de producto reutilizable --}}
    <x-buscador-producto id-modal="buscadorProductoUnificado" callback="alSeleccionarProducto" />

    @push('scripts')
    <script>
    // ================================================================
    // CONFIGURACIÓN DEL TIPO DE FACTURA (desde PHP)
    // ================================================================
    var tipoFacturaConfig = @json($config);

    // Mapa de URLs por código de tipo de factura
    var urlsPorTipo = {
        estatal: {
            guardar: '/ventas/estatal/guardar',
            listar_clientes: '/estatal/lista/clientes',
            datos_cliente: '/estatal/datos/cliente',
            datos_producto: '/estatal/datos/producto',
            tipo_pago: '/estatal/tipo/pago',
            bodegas: '/estatal/listar/bodegas/{idProducto}',
            imprimir: '/estatal/factura/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: '/ventas/numero/orden',
            codigos_exoneracion: null
        },
        sin_restriccion_gobierno: {
            guardar: '/ventas/estatal/guardar',
            listar_clientes: '/estatal/lista/clientes',
            datos_cliente: '/estatal/datos/cliente',
            datos_producto: '/estatal/datos/producto',
            tipo_pago: '/estatal/tipo/pago',
            bodegas: '/estatal/listar/bodegas/{idProducto}',
            imprimir: '/estatal/factura/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: null,
            codigos_exoneracion: null
        },
        corporativa: {
            guardar: '/ventas/corporativo/guardar',
            listar_clientes: '/ventas/lista/clientes',
            datos_cliente: '/ventas/datos/cliente',
            datos_producto: '/ventas/datos/producto',
            tipo_pago: '/ventas/tipo/pago',
            bodegas: '/ventas/listar/bodegas/{idProducto}',
            imprimir: '/factura/cooporativo/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: '/ventas/numero/orden',
            codigos_exoneracion: null
        },
        sin_restriccion_precio: {
            guardar: '/ventas/corporativo/guardar',
            listar_clientes: '/ventas/lista/clientes',
            datos_cliente: '/ventas/datos/cliente',
            datos_producto: '/ventas/datos/producto',
            tipo_pago: '/ventas/tipo/pago',
            bodegas: '/ventas/listar/bodegas/{idProducto}',
            imprimir: '/factura/cooporativo/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: null,
            codigos_exoneracion: null
        },
        exoneradas: {
            guardar: '/exonerado/venta/guardar',
            listar_clientes: '/exonerado/lista/clientes',
            datos_cliente: '/ventas/datos/cliente',
            datos_producto: '/estatal/datos/producto',
            tipo_pago: '/estatal/tipo/pago',
            bodegas: '/estatal/listar/bodegas/{idProducto}',
            imprimir: '/exonerado/factura/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: null,
            codigos_exoneracion: '/exonerado/listar/codigos'
        },
        cotizacion_clientes_a: {
            guardar: '/guardar/cotizacion',
            listar_clientes: '/cotizacion/clientes',
            datos_cliente: '/estatal/datos/cliente',
            datos_producto: '/estatal/datos/producto',
            tipo_pago: '/estatal/tipo/pago',
            bodegas: '/estatal/listar/bodegas/{idProducto}',
            imprimir: null,
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: null,
            codigos_exoneracion: null
        }
    };

    // Obtener URLs del tipo actual
    var codigoActual = tipoFacturaConfig ? tipoFacturaConfig.codigo : 'estatal';
    var urls = urlsPorTipo[codigoActual] || urlsPorTipo['estatal'];

    var numeroInputs = 0;
    var arregloIdInputs = [];
    var retencionEstado = false;
    var diasCredito = 0;
    var idAutorizacion = 0;
    var idFactura = 0;
    var public_path = "{{ asset('catalogo/') }}";

    // ================================================================
    // INICIALIZACIÓN
    // ================================================================
    window.onload = function() {
        inicializarFormulario();
    };

    function inicializarFormulario() {
        obtenerTipoPago();
        inicializarSelect2();

        // Si requiere código de autorización, mostrar modal
        if (tipoFacturaConfig && tipoFacturaConfig.requiere_codigo_autorizacion) {
            $('#modal_solicitar_codigo').modal('show');
        }
    }

    function inicializarSelect2() {
        var urlClientes = urls.listar_clientes;
        var urlVendedores = urls.vendedores;

        // Destruir select2 existentes si los hay
        if ($('#vendedor').hasClass('select2-hidden-accessible')) {
            $('#vendedor').select2('destroy');
        }
        if ($('#seleccionarCliente').hasClass('select2-hidden-accessible')) {
            $('#seleccionarCliente').select2('destroy');
        }

        $('#vendedor').select2({
            ajax: {
                url: urlVendedores,
                data: function(params) {
                    return { search: params.term, type: 'public', page: params.page || 1 };
                }
            }
        });

        $('#seleccionarCliente').select2({
            ajax: {
                url: urlClientes,
                data: function(params) {
                    return { search: params.term, type: 'public', page: params.page || 1 };
                }
            }
        });
    }

    // ================================================================
    // CAMBIAR TIPO DE FACTURA (navegación con recarga completa)
    // ================================================================
    function cambiarTipoFactura(rutaMenu) {
        window.location.href = '/' + rutaMenu;
    }

    // ================================================================
    // CÓDIGO DE AUTORIZACIÓN (sin restricción gobierno / sin restricción precio)
    // ================================================================
    function solicitarCodigo() {
        axios.get('/ventas/solicitud/codigo')
            .then(response => {
                $("#modal_solicitar_codigo").removeClass("fade").modal("hide");
                $("#modalPermiso").modal("show").addClass("fade");
            })
            .catch(err => {
                console.log(err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al solicitar código' });
            });
    }

    $(document).on('submit', '#verificarCodigoForm', function(event) {
        event.preventDefault();
        ocultarModalVerificar();
    });

    function ocultarModalVerificar() {
        let codigo = document.getElementById('codigo').value;
        axios.post('/ventas/verificar/codigo', { codigo: codigo })
            .then(response => {
                let data = response.data;
                if (data.estado == 1) {
                    $("#modalPermiso").removeClass("fade").modal("hide");
                    document.getElementById('mensajeCodigo').classList.add('d-none');
                    document.getElementById('codigo_autorizacion').value = data.idAutorizacion;
                } else {
                    document.getElementById('mensajeCodigo').classList.remove('d-none');
                    document.getElementById('codigo_autorizacion').value = '';
                }
            })
            .catch(err => { console.log(err); });
    }

    function desactivarCodigo() {
        if (!idAutorizacion) return;
        axios.post('/ventas/autorizacion/desactivar', { idAutorizacion: idAutorizacion })
            .then(response => {
                let element = document.getElementById("div_imprimir");
                element.classList.remove("d-none");
                $("#modal_solicitar_codigo").modal("show").addClass("fade");
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Error al desactivar código' });
            });
    }

    // ================================================================
    // BUSCADOR DE PRODUCTO
    // ================================================================
    function limpiarProducto() {
        document.getElementById('seleccionarProducto').innerHTML = '<option value="" selected disabled></option>';
        document.getElementById('codigoProductoBuscar').value = '';
        var lbl = document.getElementById('productoSeleccionadoLabel');
        lbl.classList.add('d-none'); lbl.textContent = '';
        document.getElementById('historialPreciosPanel').classList.add('d-none');
    }

    function alSeleccionarProducto(producto) {
        var select = document.getElementById('seleccionarProducto');
        select.innerHTML = '<option value="' + producto.id + '" selected>' + producto.nombre + '</option>';
        document.getElementById('codigoProductoBuscar').value = producto.nombre;
        var label = document.getElementById('productoSeleccionadoLabel');
        label.textContent = '✓ ' + producto.nombre + ' (ID: ' + producto.id + ')';
        label.classList.remove('d-none');
        cargarCategoriasProducto();
    }

    function buscarPorCodigo(cod) {
        cod = String(cod).trim();
        if (!cod) { window['abrirBuscador_buscadorProductoUnificado'](''); return; }
        axios.get('/productos/buscar', { params: { q: cod, page: 1 } })
            .then(function(r) {
                var items = r.data.data;
                var exact = items.find(function(p) { return String(p.id) === cod; });
                if (exact) { alSeleccionarProducto(exact); }
                else if (items.length === 1) { alSeleccionarProducto(items[0]); }
                else { window['abrirBuscador_buscadorProductoUnificado'](cod); }
            });
    }

    // ================================================================
    // CLIENTE
    // ================================================================
    function obtenerDatosCliente() {
        let idCliente = document.getElementById("seleccionarCliente").value;
        var urlDatosCliente = urls.datos_cliente;

        axios.post(urlDatosCliente, { id: idCliente })
            .then(response => {
                let data = response.data.datos;

                if (data.id == 1) {
                    document.getElementById("nombre_cliente_ventas").readOnly = false;
                    document.getElementById("rtn_ventas").readOnly = false;

                    let selectBox = document.getElementById("tipoPagoVenta");
                    if (selectBox.options.length > 2) selectBox.remove(2);

                    $('#categoria_cliente_nombre').text(data.nombre_categoria);
                    $('#categoria_cliente_venta_id').data('categoria-cliente-id', data.idcategoriacliente);

                    if ($('#seleccionarProducto').val()) {
                        cargarCategoriasProducto();
                    } else {
                        $('#categoria_cliente_venta_id').empty()
                            .append(new Option(data.nombre_categoria, data.idcategoriacliente, true, true));
                    }
                } else {
                    document.getElementById("nombre_cliente_ventas").readOnly = true;
                    document.getElementById("rtn_ventas").readOnly = true;
                    document.getElementById("nombre_cliente_ventas").value = data.nombre;
                    document.getElementById("rtn_ventas").value = data.rtn;

                    $('#categoria_cliente_nombre').text(data.nombre_categoria);
                    $('#categoria_cliente_venta_id').data('categoria-cliente-id', data.idcategoriacliente);

                    if ($('#seleccionarProducto').val()) {
                        cargarCategoriasProducto();
                    } else {
                        $('#categoria_cliente_venta_id').empty()
                            .append(new Option(data.nombre_categoria, data.idcategoriacliente, true, true));
                    }

                    diasCredito = data.dias_credito;
                    obtenerTipoPago();

                    // Orden de compra si aplica
                    if (tipoFacturaConfig && tipoFacturaConfig.requiere_orden_compra) {
                        obtenerOrdenesCompra();
                    }

                    // Códigos de exoneración si aplica
                    if (tipoFacturaConfig && tipoFacturaConfig.requiere_codigo_exoneracion) {
                        obtenerCodigosExoneracion(idCliente);
                    }

                    cargarHistorialPrecios();
                }
            })
            .catch(err => {
                console.log(err);
                Swal.fire({ icon: 'error', title: 'Error...', text: "Error al obtener datos del cliente" });
            });
    }

    // ================================================================
    // CÓDIGOS DE EXONERACIÓN (solo para tipo exoneradas)
    // ================================================================
    function obtenerCodigosExoneracion(idCliente) {
        if (!urls.codigos_exoneracion) return;

        axios.get(urls.codigos_exoneracion, { params: { cliente_id: idCliente } })
            .then(response => {
                let codigos = response.data.codigos || response.data;
                let html = '<option value="" selected disabled>--Seleccione un código--</option>';
                if (Array.isArray(codigos)) {
                    codigos.forEach(c => {
                        html += '<option value="' + c.id + '">' + (c.codigo || c.correlativo || c.id) + '</option>';
                    });
                }
                document.getElementById('codigoExoneracion').innerHTML = html;
            })
            .catch(err => {
                console.log(err);
            });
    }

    // ================================================================
    // TIPO DE PAGO
    // ================================================================
    function obtenerTipoPago() {
        var urlTipoPago = urls.tipo_pago;

        axios.get(urlTipoPago)
            .then(response => {
                let tipoDePago = response.data.tipos;
                let numeroVenta = response.data.numeroVenta.numero;
                let htmlPagos = '<option value="" selected disabled>--Seleccione una opcion--</option>';
                tipoDePago.forEach(element => {
                    htmlPagos += '<option value="' + element.id + '">' + element.descripcion + '</option>';
                });
                document.getElementById('tipoPagoVenta').innerHTML = htmlPagos;
                document.getElementById("numero_venta").value = numeroVenta;
            })
            .catch(err => {
                console.log(err);
                Swal.fire({ icon: 'error', title: 'Error...', text: "Error al obtener tipos de pago" });
            });
    }

    // ================================================================
    // CATEGORÍAS DE PRODUCTO
    // ================================================================
    function cargarCategoriasProducto() {
        let productoId = $('#seleccionarProducto').val();
        let clienteId = $('#seleccionarCliente').val();

        if (!productoId) {
            $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>--Seleccione primero un producto--</option>');
            return;
        }

        $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>Cargando categorías...</option>');

        axios.post('/producto/categorias-disponibles', { producto_id: productoId })
            .then(response => {
                let categorias = response.data.categorias;
                if (categorias.length > 0) {
                    categorias.sort((a, b) => (parseFloat(b.precio_a) || 0) - (parseFloat(a.precio_a) || 0));

                    $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>--Seleccione una categoría--</option>');
                    let categoriaClienteId = $('#categoria_cliente_venta_id').data('categoria-cliente-id');

                    categorias.forEach(categoria => {
                        let precio = parseFloat(categoria.precio_a) || 0;
                        let precioFormateado = new Intl.NumberFormat('es-HN', {
                            style: 'currency', currency: 'HNL', minimumFractionDigits: 2
                        }).format(precio);
                        let textoOpcion = categoria.nombre_categoria + ' - ' + precioFormateado;
                        let isSelected = (clienteId && categoria.id == categoriaClienteId);
                        let option = new Option(textoOpcion, categoria.id, isSelected, isSelected);
                        $('#categoria_cliente_venta_id').append(option);
                    });
                    $('#categoria_cliente_venta_id').prop('disabled', false);
                } else {
                    $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>No hay categorías disponibles</option>');
                    Swal.fire({ icon: 'warning', title: 'Advertencia', text: 'Este producto no tiene escalas de precio.' });
                }
            })
            .catch(err => {
                console.log(err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar categorías.' });
            });

        obtenerImagenes();
        cargarHistorialPrecios();
    }

    function habilitarBodega() {
        let categoriaId = $('#categoria_cliente_venta_id').val();
        let productoId = $('#seleccionarProducto').val();
        if (categoriaId && productoId) {
            $('#bodega').prop('disabled', false);
            obtenerBodegas(productoId);
        }
    }

    // ================================================================
    // HISTORIAL DE PRECIOS
    // ================================================================
    function cargarHistorialPrecios() {
        var productoId = $('#seleccionarProducto').val();
        var clienteId = $('#seleccionarCliente').val();
        var panel = document.getElementById('historialPreciosPanel');
        var cuerpo = document.getElementById('historialPreciosCuerpo');

        if (!productoId || !clienteId) { panel.classList.add('d-none'); return; }

        var urlHistorial = urls.historial_precios;

        cuerpo.innerHTML = '<p class="text-muted small"><i class="fa fa-spinner fa-spin"></i> Cargando historial...</p>';
        panel.classList.remove('d-none');

        axios.post(urlHistorial, { cliente_id: clienteId, producto_id: productoId })
            .then(function(response) {
                var rows = response.data.historial;
                if (!rows || rows.length === 0) {
                    cuerpo.innerHTML = '<p class="text-muted small">No hay ventas previas.</p>';
                    return;
                }
                var fmt = new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 });
                var html = '<div class="table-responsive"><table class="table table-sm table-bordered table-hover mb-0" style="font-size:0.82rem;"><thead class="thead-light"><tr><th>Fecha</th><th>Factura</th><th>Precio Unit.</th><th>Cant.</th><th>Total</th><th>Categoría</th></tr></thead><tbody>';
                rows.forEach(function(r) {
                    html += '<tr><td>' + r.fecha_emision + '</td><td>' + r.numero_factura + '</td><td class="text-right font-weight-bold text-success">' + fmt.format(r.precio_unidad) + '</td><td class="text-center">' + r.cantidad + '</td><td class="text-right">' + fmt.format(r.total) + '</td><td><span class="badge badge-secondary">' + r.categoria + '</span></td></tr>';
                });
                html += '</tbody></table></div>';
                cuerpo.innerHTML = html;
            })
            .catch(function() { cuerpo.innerHTML = '<p class="text-danger small">Error al cargar historial.</p>'; });
    }

    // ================================================================
    // BODEGAS
    // ================================================================
    function obtenerBodegas(id) {
        document.getElementById('bodega').innerHTML = "<option selected disabled>--Seleccione una bodega--</option>";
        var urlBase = urls.bodegas;
        var url = urlBase.replace('{idProducto}', id);

        $('#bodega').select2({
            ajax: {
                url: url,
                data: function(params) {
                    return { search: params.term, type: 'public', page: params.page || 1, idProducto: id };
                }
            }
        });
    }

    // ================================================================
    // IMÁGENES
    // ================================================================
    function obtenerImagenes() {
        let id = document.getElementById('seleccionarProducto').value;
        let htmlImagenes = '';
        axios.post('/producto/listar/imagenes', { id: id })
            .then(response => {
                let imagenes = response.data.imagenes;
                if (imagenes.length == 0) {
                    htmlImagenes += '<div class="carousel-item active"><img class="d-block" src="' + public_path + '/noimage.png" alt="noimage.png" style="width:100%;height:20rem"></div>';
                    document.getElementById('bloqueImagenes').innerHTML = htmlImagenes;
                    document.getElementById('botonAdd').classList.remove("d-none");
                } else {
                    imagenes.forEach(element => {
                        let activeClass = element.contador == 1 ? ' active' : '';
                        htmlImagenes += '<div class="carousel-item' + activeClass + '"><img class="d-block" src="' + public_path + '/' + element.url_img + '" alt="imagen ' + element.contador + '" style="width:100%;height:30rem"></div>';
                    });
                    document.getElementById('bloqueImagenes').innerHTML = htmlImagenes;
                }
                document.getElementById('botonAdd').classList.add("d-none");
                let a = document.getElementById("detalleProducto");
                a.href = "/producto/detalle/" + id;
                a.classList.remove("d-none");
            })
            .catch(err => { console.log(err); });

        obtenerBodegas(id);
    }

    // ================================================================
    // AGREGAR PRODUCTO AL CARRITO
    // ================================================================
    function agregarProductoCarrito() {
        let idProducto = document.getElementById('seleccionarProducto').value;
        let categoria_cliente_venta_id = document.getElementById('categoria_cliente_venta_id').value;
        let data = $("#bodega").select2('data')[0];
        let bodega = data.bodegaSeccion;
        let idBodega = data.idBodega;
        let idSeccion = data.id;

        var urlDatosProducto = urls.datos_producto;

        axios.post(urlDatosProducto, { idProducto: idProducto, categoria_cliente_venta_id: categoria_cliente_venta_id })
            .then(response => {
                // Verificar duplicados
                let flag = false;
                arregloIdInputs.forEach(idInpunt => {
                    let idProductoFila = document.getElementById("idProducto" + idInpunt).value;
                    let idSeccionFila = document.getElementById("idSeccion" + idInpunt).value;
                    if (idProducto == idProductoFila && idSeccion == idSeccionFila && !flag) flag = true;
                });

                if (flag) {
                    Swal.fire({
                        icon: 'warning', title: 'Advertencia!',
                        html: '<p class="text-left">La sección de bodega y producto ha sido agregada anteriormente.<br><br>Por favor verificar la sección de bodega y producto sea distinto a los ya existentes.</p>'
                    });
                    return;
                }

                let producto = response.data.producto;
                let arrayUnidades = response.data.unidades;
                numeroInputs += 1;

                let htmlSelectUnidades = "";
                arrayUnidades.forEach(unidad => {
                    let sel = unidad.valor_defecto == 1 ? 'selected' : '';
                    htmlSelectUnidades += '<option ' + sel + ' value="' + unidad.id + '" data-id="' + unidad.idUnidadVenta + '">' + unidad.nombre + '</option>';
                });

                // Determinar opciones de precios según configuración
                let htmlprecios = '';
                if (tipoFacturaConfig && tipoFacturaConfig.multiples_precios) {
                    // Múltiples precios A/B/C/D (sin restricción)
                    htmlprecios = '<option value="' + producto.precio1 + '" data-id="p1" selected>' + producto.precio1 + ' - A</option>';
                    if (producto.precio2) htmlprecios += '<option value="' + producto.precio2 + '" data-id="p2">' + producto.precio2 + ' - B</option>';
                    if (producto.precio3) htmlprecios += '<option value="' + producto.precio3 + '" data-id="p3">' + producto.precio3 + ' - C</option>';
                    if (producto.precio4) htmlprecios += '<option value="' + producto.precio4 + '" data-id="p4">' + producto.precio4 + ' - D</option>';
                } else {
                    // Solo precio A (con restricción)
                    htmlprecios = '<option value="' + producto.precio1 + '" data-id="p1" selected>' + producto.precio1 + ' - A</option>';
                }

                // Determinar el min del precio
                let minPrecio = (tipoFacturaConfig && tipoFacturaConfig.multiples_precios) ? '' : 'min="' + producto.precio1 + '"';

                let html = `
                <div id='${numeroInputs}' class="row no-gutters">
                    <div class="form-group col-12 col-md-3">
                        <div class="d-flex">
                            <button class="btn btn-danger" type="button" onclick="eliminarInput(${numeroInputs})">
                                <i class="fa-regular fa-rectangle-xmark"></i>
                            </button>
                            <input id="idProducto${numeroInputs}" name="idProducto${numeroInputs}" type="hidden" value="${producto.id}">
                            <input id="precios_producto_carga_id${numeroInputs}" name="precios_producto_carga_id${numeroInputs}" type="hidden" value="${producto.precios_producto_carga_id || ''}">
                            <div style="width:100%">
                                <label for="nombre${numeroInputs}" class="sr-only">Nombre</label>
                                <input type="text" placeholder="Nombre" id="nombre${numeroInputs}" name="nombre${numeroInputs}"
                                    class="form-control" data-parsley-required autocomplete="off" readonly value='${producto.nombre}'>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-6 col-md-1">
                        <input type="text" value="${bodega}" id="bodega${numeroInputs}" name="bodega${numeroInputs}"
                            class="form-control" autocomplete="off" readonly>
                    </div>
                    <div class="form-group col-6 col-md-2">
                        <select class="form-control" name="precios${numeroInputs}" id="precios${numeroInputs}"
                            data-parsley-required style="height:35.7px;"
                            onchange="validacionPrecio(precios${numeroInputs}, precio${numeroInputs})">
                            ${htmlprecios}
                        </select>
                    </div>
                    <div class="form-group col-4 col-md-1">
                        <input type="number" placeholder="Precio" id="precio${numeroInputs}" name="precio${numeroInputs}"
                            value="${producto.precio1}" class="form-control" ${minPrecio} data-parsley-required step="any"
                            autocomplete="off" onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                    </div>
                    <div class="form-group col-4 col-md-1">
                        <input type="number" placeholder="Cantidad" id="cantidad${numeroInputs}" name="cantidad${numeroInputs}"
                            class="form-control" min="1" data-parsley-required autocomplete="off"
                            onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                    </div>
                    <div class="form-group col-4 col-md-1">
                        <select class="form-control" name="unidad${numeroInputs}" id="unidad${numeroInputs}"
                            data-parsley-required style="height:35.7px;"
                            onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                            ${htmlSelectUnidades}
                        </select>
                    </div>
                    <div class="form-group col-4 col-md-1">
                        <input type="text" placeholder="Sub total" id="subTotalMostrar${numeroInputs}" name="subTotalMostrar${numeroInputs}"
                            class="form-control" autocomplete="off" readonly>
                        <input id="subTotal${numeroInputs}" name="subTotal${numeroInputs}" type="hidden" value="" required>
                    </div>
                    <div class="form-group col-4 col-md-1">
                        <input type="text" placeholder="ISV" id="isvProductoMostrar${numeroInputs}" name="isvProductoMostrar${numeroInputs}"
                            class="form-control" autocomplete="off" readonly>
                        <input id="isvProducto${numeroInputs}" name="isvProducto${numeroInputs}" type="hidden" value="" required>
                        <input type="hidden" id="acumuladoDescuento${numeroInputs}" name="acumuladoDescuento${numeroInputs}">
                    </div>
                    <div class="form-group col-4 col-md-1">
                        <input type="text" placeholder="Total" id="totalMostrar${numeroInputs}" name="totalMostrar${numeroInputs}"
                            class="form-control" autocomplete="off" readonly>
                        <input id="total${numeroInputs}" name="total${numeroInputs}" type="hidden" value="" required>
                    </div>
                    <input id="idBodega${numeroInputs}" name="idBodega${numeroInputs}" type="hidden" value="${idBodega}">
                    <input id="idSeccion${numeroInputs}" name="idSeccion${numeroInputs}" type="hidden" value="${idSeccion}">
                    <input id="restaInventario${numeroInputs}" name="restaInventario${numeroInputs}" type="hidden" value="">
                    <input id="isv${numeroInputs}" name="isv${numeroInputs}" type="hidden" value="${producto.isv}">
                </div>`;

                arregloIdInputs.splice(numeroInputs, 0, numeroInputs);
                document.getElementById('divProductos').insertAdjacentHTML('beforeend', html);
            })
            .catch(err => {
                const mensaje = err.response?.data?.message || 'Error al agregar producto';
                Swal.fire({ icon: 'error', title: 'Error', html: mensaje });
            });
    }

    function prueba() {
        document.getElementById('botonAdd').classList.remove("d-none");
    }

    // ================================================================
    // CÁLCULOS
    // ================================================================
    function calcularTotalesInicioPagina() {
        let arrayInputs = this.arregloIdInputs;
        arrayInputs.forEach(id => {
            let valorInputPrecio = document.getElementById('precio' + id).value;
            let valorInputCantidad = document.getElementById('cantidad' + id).value;
            let valorSelectUnidad = document.getElementById('unidad' + id).value;
            let isvProducto = document.getElementById("isv" + id).value;

            // Si no aplica ISV, forzar a 0
            if (tipoFacturaConfig && !tipoFacturaConfig.aplica_isv) {
                isvProducto = 0;
            }

            if (valorInputPrecio && valorInputCantidad) {
                let descuento = document.getElementById("porDescuento").value;
                let subTotal = 0, isv = 0, total = 0, descuentoCalculado = 0;

                if (descuento > 0) {
                    subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                    descuentoCalculado = subTotal * (descuento / 100);
                    subTotal = subTotal - descuentoCalculado;
                    isv = parseFloat((subTotal * (isvProducto / 100)).toFixed(2));
                    total = subTotal + (subTotal * (isvProducto / 100));
                } else {
                    descuentoCalculado = 0;
                    subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                    isv = parseFloat((subTotal * (isvProducto / 100)).toFixed(2));
                    total = subTotal + subTotal * (isvProducto / 100);
                }

                document.getElementById("acumuladoDescuento" + id).value = descuentoCalculado.toFixed(2);
                document.getElementById('total' + id).value = total.toFixed(2);
                document.getElementById('totalMostrar' + id).value = formatoMoneda(total);
                document.getElementById('subTotal' + id).value = subTotal.toFixed(2);
                document.getElementById('subTotalMostrar' + id).value = formatoMoneda(subTotal);
                document.getElementById('isvProducto' + id).value = isv.toFixed(2);
                document.getElementById('isvProductoMostrar' + id).value = formatoMoneda(isv);
            }
        });
        this.totalesGenerales();
    }

    function calcularTotales(idPrecio, idCantidad, isvProducto, idUnidad, id, idRestaInventario) {
        let valorInputPrecio = Number(idPrecio.value).toFixed(2);
        let valorInputCantidad = idCantidad.value;
        let valorSelectUnidad = idUnidad.value;

        // Si no aplica ISV, forzar a 0
        if (tipoFacturaConfig && !tipoFacturaConfig.aplica_isv) {
            isvProducto = 0;
        }

        if (valorInputPrecio && valorInputCantidad) {
            let descuento = document.getElementById('porDescuento').value;
            let subTotal = 0, isv = 0, total = 0, descuentoCalculado = 0;

            if (descuento > 0) {
                subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                descuentoCalculado = subTotal * (descuento / 100);
                subTotal = subTotal - descuentoCalculado;
                isv = subTotal * (isvProducto / 100);
                total = subTotal + (subTotal * (isvProducto / 100));
            } else {
                descuentoCalculado = 0;
                subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                isv = subTotal * (isvProducto / 100);
                total = subTotal + subTotal * (isvProducto / 100);
            }

            document.getElementById('acumuladoDescuento' + id).value = descuentoCalculado.toFixed(2);
            document.getElementById('total' + id).value = total.toFixed(2);
            document.getElementById('totalMostrar' + id).value = formatoMoneda(total);
            document.getElementById('subTotal' + id).value = subTotal.toFixed(2);
            document.getElementById('subTotalMostrar' + id).value = formatoMoneda(subTotal);
            document.getElementById('isvProducto' + id).value = isv.toFixed(2);
            document.getElementById('isvProductoMostrar' + id).value = formatoMoneda(isv);

            idRestaInventario.value = valorInputCantidad * valorSelectUnidad;
            this.totalesGenerales();
        }

        idPrecio.value = valorInputPrecio;
    }

    function formatoMoneda(valor) {
        return new Intl.NumberFormat('es-HN', {
            style: 'currency', currency: 'HNL', minimumFractionDigits: 2
        }).format(valor);
    }

    function totalesGenerales() {
        if (numeroInputs == 0) return;

        let totalGeneralValor = 0, totalISV = 0, subTotalGeneralGrabadoValor = 0;
        let subTotalGeneralExcentoValor = 0, subTotalGeneral = 0, acumularDescuento = 0;

        for (let i = 0; i < arregloIdInputs.length; i++) {
            let subTotalFila = Number(document.getElementById('subTotal' + arregloIdInputs[i]).value);
            let isvFila = Number(document.getElementById('isvProducto' + arregloIdInputs[i]).value);

            if (isvFila == 0) {
                subTotalGeneralExcentoValor += subTotalFila;
            } else if (subTotalFila > 0) {
                subTotalGeneralGrabadoValor += subTotalFila;
            }

            subTotalGeneral += subTotalFila;
            totalISV += isvFila;
            totalGeneralValor += Number(document.getElementById('total' + arregloIdInputs[i]).value);
            acumularDescuento += Number(document.getElementById('acumuladoDescuento' + arregloIdInputs[i]).value);
        }

        document.getElementById('porDescuentoCalculado').value = acumularDescuento.toFixed(2);
        document.getElementById('descuentoMostrar').value = formatoMoneda(acumularDescuento);
        document.getElementById('subTotalGeneral').value = subTotalGeneral.toFixed(2);
        document.getElementById('subTotalGeneralMostrar').value = formatoMoneda(subTotalGeneral);
        document.getElementById('subTotalGeneralGrabado').value = subTotalGeneralGrabadoValor.toFixed(2);
        document.getElementById('subTotalGeneralGrabadoMostrar').value = formatoMoneda(subTotalGeneralGrabadoValor);
        document.getElementById('subTotalGeneralExcento').value = subTotalGeneralExcentoValor.toFixed(2);
        document.getElementById('subTotalGeneralExcentoMostrar').value = formatoMoneda(subTotalGeneralExcentoValor);
        document.getElementById('isvGeneral').value = totalISV.toFixed(2);
        document.getElementById('isvGeneralMostrar').value = formatoMoneda(totalISV);
        document.getElementById('totalGeneral').value = totalGeneralValor.toFixed(2);
        document.getElementById('totalGeneralMostrar').value = formatoMoneda(totalGeneralValor);
    }

    function eliminarInput(id) {
        document.getElementById(id).remove();
        var myIndex = arregloIdInputs.indexOf(id);
        if (myIndex !== -1) {
            arregloIdInputs.splice(myIndex, 1);
            this.totalesGenerales();
        }
    }

    function validacionPrecio(idPrecios, idprecio) {
        var idPrecioSeleccionado = idPrecios.options[idPrecios.selectedIndex].getAttribute("data-id");
        var precioSeleccionado = idPrecios.value;
        var idprecioIngresado = idprecio.id;

        document.getElementById(idprecioIngresado).value = precioSeleccionado;

        // Solo aplicar mínimo si NO es sin restricción
        if (tipoFacturaConfig && !tipoFacturaConfig.multiples_precios) {
            document.getElementById(idprecioIngresado).setAttribute("min", precioSeleccionado);
        }
    }

    // ================================================================
    // FECHAS Y PAGOS
    // ================================================================
    function validarFechaPago() {
        let tipoPago = document.getElementById('tipoPagoVenta').value;
        if (tipoPago == 2) {
            document.getElementById('fecha_vencimiento').readOnly = false;
            sumarDiasCredito();
        } else {
            document.getElementById('fecha_vencimiento').value = "{{ date('Y-m-d') }}";
            document.getElementById('fecha_vencimiento').readOnly = true;
        }
    }

    function sumarDiasCredito() {
        let tipoPago = document.getElementById('tipoPagoVenta').value;
        if (tipoPago == 2) {
            let fechaEmision = document.getElementById("fecha_emision").value;
            let date = new Date(fechaEmision);
            date.setDate(date.getDate() + diasCredito);
            document.getElementById("fecha_vencimiento").value = date.toISOString().split('T')[0];
        }
    }

    function obtenerOrdenesCompra() {
        if (!tipoFacturaConfig || !tipoFacturaConfig.requiere_orden_compra || !urls.orden_compra) return;

        let idCliente = document.getElementById('seleccionarCliente').value;
        $('#ordenCompra').select2({
            ajax: {
                url: urls.orden_compra,
                data: function(params) {
                    return { idCliente: idCliente, search: params.term, type: 'public', page: params.page || 1 };
                }
            }
        });
    }

    // ================================================================
    // GUARDAR VENTA
    // ================================================================
    $(document).on('submit', '#crear_venta', function(event) {
        event.preventDefault();
        guardarVenta();
    });

    function guardarVenta() {
        document.getElementById("btn_venta_coorporativa").disabled = true;

        var data = new FormData($('#crear_venta').get(0));

        let longitudArreglo = arregloIdInputs.length;
        for (var i = 0; i < longitudArreglo; i++) {
            let name = "unidad" + arregloIdInputs[i];
            let nameForm = "idUnidadVenta" + arregloIdInputs[i];
            let e = document.getElementById(name);
            let idUnidadVenta = e.options[e.selectedIndex].getAttribute("data-id");
            data.append(nameForm, idUnidadVenta);

            let name2 = "precios" + arregloIdInputs[i];
            let nameForm2 = "idPrecioSeleccionado" + arregloIdInputs[i];
            let a = document.getElementById(name2);
            let idPrecioSeleccionado = a.options[a.selectedIndex].getAttribute("data-id");
            data.append(nameForm2, idPrecioSeleccionado);
        }

        data.append("numeroInputs", numeroInputs);
        let text = arregloIdInputs.toString();
        data.append("arregloIdInputs", text);

        const formDataObj = {};
        data.forEach((value, key) => (formDataObj[key] = value));

        var urlGuardar = urls.guardar;

        axios.post(urlGuardar, formDataObj, { headers: { "content-type": "application/json" } })
            .then(response => {
                let data = response.data;

                // Para tipos con código de autorización
                if (tipoFacturaConfig && tipoFacturaConfig.requiere_codigo_autorizacion) {
                    idAutorizacion = document.getElementById('codigo_autorizacion').value;
                    idFactura = data.idFactura;
                    var urlImprimir = urls.imprimir || '/factura/cooporativo/{id}';
                    document.getElementById('btn_imprimir').href = urlImprimir.replace('{id}', idFactura);
                }

                if (data.idFactura == 0) {
                    Swal.fire({ icon: data.icon, title: data.title, html: data.text });
                    document.getElementById("btn_venta_coorporativa").disabled = false;
                    return;
                }

                Swal.fire({ icon: data.icon, title: data.title, html: data.text });

                // Limpiar formulario
                document.getElementById('bloqueImagenes').innerHTML = '';
                document.getElementById('divProductos').innerHTML = '';
                document.getElementById("crear_venta").reset();
                $('#crear_venta').parsley().reset();

                document.getElementById('detalleProducto').classList.add("d-none");
                document.getElementById('detalleProducto').href = "";
                document.getElementById("seleccionarCliente").innerHTML = '<option value="" selected disabled>--Seleccionar un cliente--</option>';
                document.getElementById('seleccionarProducto').innerHTML = '<option value="" selected disabled></option>';
                document.getElementById('codigoProductoBuscar').value = '';
                var lblProd = document.getElementById('productoSeleccionadoLabel');
                lblProd.classList.add('d-none'); lblProd.textContent = '';
                document.getElementById('bodega').innerHTML = '<option value="" selected disabled>--Seleccione un producto--</option>';
                document.getElementById("bodega").disabled = true;

                arregloIdInputs = [];
                numeroInputs = 0;
                retencionEstado = false;

                document.getElementById('numero_venta').value = data.numeroVenta;
                document.getElementById("btn_venta_coorporativa").disabled = false;

                // Restaurar campos ocultos
                document.getElementById('restriccion').value = tipoFacturaConfig ? tipoFacturaConfig.restriccion : 1;
                document.getElementById('tipo_venta_id').value = tipoFacturaConfig ? tipoFacturaConfig.tipo_venta_id : 2;
                document.getElementById('tipo_factura_id').value = tipoFacturaConfig ? tipoFacturaConfig.id : '';

                // Desactivar código si aplica
                if (tipoFacturaConfig && tipoFacturaConfig.requiere_codigo_autorizacion) {
                    setTimeout(function() { desactivarCodigo(); }, 30000);
                }
            })
            .catch(err => {
                document.getElementById("btn_venta_coorporativa").disabled = false;
                let data = err.response ? err.response.data : {};
                console.log(err);
                Swal.fire({ icon: data.icon || 'error', title: data.title || 'Error', text: data.text || 'Error al guardar' });
            });
    }

    function obtenerCategoriasClientes() {
        $('#categoria_cliente_venta_id').select2({
            placeholder: 'Seleccione una categoría',
            allowClear: true,
            ajax: {
                url: '/clientes/categorias-escala',
                dataType: 'json',
                delay: 250,
                data: function(params) { return { q: params.term || '', page: params.page || 1 }; },
                processResults: function(data) {
                    return { results: data.categorias.map(function(item) { return { id: item.id, text: item.nombre_categoria }; }) };
                }
            }
        });
    }
    </script>

    <script>
    <?php
        date_default_timezone_set('America/Tegucigalpa');
        $act_fecha = date('Y-m-d');
        $year = date('Y');
    ?>
    function mostrarHora() {
        var fecha = new Date();
        var hora = fecha.getHours();
        var minutos = fecha.getMinutes();
        var segundos = fecha.getSeconds();
        minutos = minutos < 10 ? "0" + minutos : minutos;
        segundos = segundos < 10 ? "0" + segundos : segundos;
        var el = document.getElementById("reloj");
        if (el) el.innerHTML = hora + ":" + minutos + ":" + segundos;
    }
    setInterval(mostrarHora, 1000);
    </script>
    @endpush

    <div class="mt-3">
        <div class="float-right">
            <?php echo "$act_fecha"; ?> <strong id="reloj"></strong>
        </div>
        <div>
            <strong>Copyright</strong> Distribuciones Valencia &copy; <?php echo "$year"; ?>
        </div>
        <div style="clear: both;"></div>
    </div>
</div>
