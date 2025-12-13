<div>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2>Registrar Devolución de Producto</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a>Nota de crédito</a>
                </li>


            </ol>
        </div>

    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
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
                                <button id="solicitarFactura" onclick="datosFactura()" class="btn btn-primary mt-4"><i
                                        class="fa-solid fa-paper-plane text-white"></i> Solicitar Factura</button>
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
                <div class="ibox ">

                    <div class="ibox-content">
                        <h3>Detalle de Factura</h3>

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

                        <div class="table-responsive">
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
                                <tbody>

                                </tbody>
                            </table>
                        </div>



                        <br>
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






                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Listado de Productos en Nota de Crédito</h3>
                    </div>

                    <div class="ibox-content">
                        <form onkeydown="return event.key != 'Enter';" autocomplete="off" id="guardar_devolucion"
                            name="guardar_devolucion" data-parsley-validate>

                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-6">
                                    <label for="motivo_nota" class="col-form-label focus-label">
                                        Seleccionar motivo de nota de credito:</label>
                                    <select id="motivo_nota" name="motivo_nota" class="form-group form-control"
                                        style="" data-parsley-required form="guardar_devolucion">
                                        <option value="" selected disabled>--Seleccionar Motivo--</option>
                                    </select>
                                </div>

                                <div class="col-12 col-sm-12 col-md-6">
                                    <label for="comentario_nota" class="col-form-label focus-label">
                                        Comentario de nota de crédito:</label>
                                    <textarea class="form-group form-control" name="comentario" id="comentario" cols="30" rows="10"></textarea>
                                </div>
                            </div>

                        </form>



                        <div class="table-responsive mt-4">





                            <table id="tbl_productos_lista" class="table table-striped table-bordered table-hover">
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
                        <br>
                        <div class="row">

                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                <label class="col-form-label" for="subTotalGeneralCreditoMostrar">Sub Total L.<span
                                        class="text-danger">*</span></label>
                            </div>

                            <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">

                                <input id="subTotalGeneralCreditoMostrar" class="form-control" type="text"
                                    placeholder="Sub total " disabled>

                                <input type="hidden" step="any" placeholder="Sub total "
                                    id="subTotalGeneralCredito" name="subTotalGeneralCredito" class="form-control"
                                    value="0" min="0" data-parsley-required autocomplete="off"
                                    form="guardar_devolucion">
                            </div>
                        </div>

                        <div class="row">

                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                <label class="col-form-label" for="subTotalGeneralCreditoMostrar">Sub Total Grabado
                                    L.<span class="text-danger">*</span></label>
                            </div>

                            <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">

                                <input id="subTotalGeneralGrabadoCreditoMostrar" class="form-control" type="text"
                                    placeholder="Sub total " disabled>

                                <input type="hidden" step="any" placeholder="Sub total "
                                    id="subTotalGeneralGrabadoCredito" name="subTotalGeneralGrabadoCredito"
                                    class="form-control" value="0" min="0" data-parsley-required
                                    autocomplete="off" form="guardar_devolucion">
                            </div>
                        </div>

                        <div class="row">

                            <div class="form-group col-12 col-sm-12 col-md-2 col-lg-1 col-xl-1">
                                <label class="col-form-label" for="subTotalGeneralCreditoMostrar">Sub Total Excento
                                    L.<span class="text-danger">*</span></label>
                            </div>

                            <div class="form-group col-12 col-sm-12 col-md-3 col-lg-2 col-xl-2">

                                <input id="subTotalGeneralExcentoCreditoMostrar" class="form-control" type="text"
                                    placeholder="Sub total " disabled>

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

                                <input type="hidden" step="any" id="isvGeneralCredito" name="isvGeneralCredito"
                                    class="form-control" min="0" value="0" data-parsley-required
                                    autocomplete="off" form="guardar_devolucion">
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

                        <br>

                        <button type="submit" id="btn_guardar_nota_credito" form="guardar_devolucion"
                            class="btn btn-success">Cerrar Nota de Credito</button>


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
                    <div class="modal-header">
                        <h4 class="" id="">Datos de Producto</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="agregarProductoLista()">Agregar a
                            Nota de Credito</button>
                    </div>
                </div>
            </div>
        </div>


    </div>


    @push('scripts')
    <script src="{{ asset('js/js_proyecto/nota-credito/crear-nota-credito.js') }}"></script>
    <script>

    function obtenerProductos(idFactura) {

        //let table = $('#tbl_productos').DataTable();
        //table.destroy();



        $('#tbl_productos').DataTable({

            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
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
