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
                            <input type="hidden" id="idMedidaVenta" name="idMedidaVenta" value="0">
                            <input type="hidden" id="unidad_venta" name="unidad_venta" value="0">
                            <input type="hidden" id="isvPorcentaje" name="isvPorcentaje" value="0">
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
        <script>
            var contador = 1;
            var arrayInputs = [];
            var productoSeccion = [];

            $('#cliente').select2({
                ajax: {
                    url: '/nota/credito/clientes',
                    data: function(params) {
                        var query = {
                            search: params.term,
                            type: 'public',
                            page: params.page || 1
                        }

                        // Query parameters will be ?search=[term]&type=public
                        return query;
                    }

                }
            });

            $('#motivo_nota').select2({
                ajax: {
                    url: '/nota/credito/motivos',
                    data: function(params) {
                        var query = {
                            search: params.term,
                            type: 'public',
                            page: params.page || 1
                        }

                        // Query parameters will be ?search=[term]&type=public
                        return query;
                    }

                }
            });

            function obtenerFacturasDeCliente() {
                document.getElementById('factura').innerHTML =
                    ' <option value="" selected disabled>--Seleccionar una factura--</option>';

                this.limpiarTablas();

                let idCliente = document.getElementById('cliente').value

                $('#factura').select2({
                    ajax: {
                        url: '/nota/credito/facturas',
                        data: function(params) {
                            var query = {
                                idCliente: idCliente,
                                search: params.term,
                                type: 'public',
                                page: params.page || 1
                            }

                            // Query parameters will be ?search=[term]&type=public
                            return query;
                        }

                    }
                });
            }

            function datosFactura() {
                let idFactura = document.getElementById('factura').value;


                axios.post('/nota/credito/datos/factura', {
                        idFactura: idFactura
                    })
                    .then(response => {

                        let data = response.data.datosFactura;


                        document.getElementById('codigo_factura').value = data.id;
                        document.getElementById('fecha').value = data.fecha_emision;
                        document.getElementById('tipo_pago').value = data.tipoPago;


                        document.getElementById('tipo_venta').value = data.tipoFactura;
                        document.getElementById('codigo_cliente').value = data.idCliente;
                        document.getElementById('rtn').value = data.rtn;

                        document.getElementById('nombre_cliente').value = data.nombreCliente;
                        document.getElementById('vendedor').value = data.vendedor;
                        document.getElementById('facturado').value = data.facturador;
                        document.getElementById('fecha_registro').value = data.fechaRegistro;


                        document.getElementById('subTotalGeneralMostrar').value = new Intl.NumberFormat('es-HN', {
                            style: 'currency',
                            currency: 'HNL',
                            minimumFractionDigits: 2,
                        }).format(data.sub_total);
                        document.getElementById('subTotalGeneralGrabadoMostrar').value = new Intl.NumberFormat('es-HN', {
                            style: 'currency',
                            currency: 'HNL',
                            minimumFractionDigits: 2,
                        }).format(data.sub_total_grabado);
                        document.getElementById('subTotalGeneralExcentoMostrar').value = new Intl.NumberFormat('es-HN', {
                            style: 'currency',
                            currency: 'HNL',
                            minimumFractionDigits: 2,
                        }).format(data.sub_total_excento);

                        document.getElementById('isvGeneralMostrar').value = new Intl.NumberFormat('es-HN', {
                            style: 'currency',
                            currency: 'HNL',
                            minimumFractionDigits: 2,
                        }).format(data.isv);
                        document.getElementById('totalGeneralMostrar').value = new Intl.NumberFormat('es-HN', {
                            style: 'currency',
                            currency: 'HNL',
                            minimumFractionDigits: 2,
                        }).format(data.total);
                    })

                $('#tbl_productos').DataTable().clear().destroy();
                this.obtenerProductos(idFactura);
            }

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








            function limpiarTablas() {
                $('#tbl_productos').DataTable().clear().destroy();
            }

            function infoProducto(facturaId, productoId, seccionId) {


                axios.post('/nota/credito/datos/producto', {
                        idFactura: facturaId,
                        idProducto: productoId,
                        idSeccion: seccionId
                    })
                    .then(response => {



                        let data = response.data.datos;
                        let cantidadMax = data.cantidad;

                        document.getElementById('nombre').value = data.producto;
                        document.getElementById('idFactura').value = data.factura_id;
                        document.getElementById('idProducto').value = data.producto_id;
                        document.getElementById('idMedidaVenta').value = data.idUnidadVenta;
                        document.getElementById('unidad_venta').value = data.unidad_venta;
                        document.getElementById('unidad').value = data.unidad_medida;
                        document.getElementById('precio').value = data.precio_unidad;
                        document.getElementById('isvPorcentaje').value = data.porcentajeISV;
                        document.getElementById('cantidadMaxima').value = cantidadMax;
                        document.getElementById('precioMostrar').value = monedaLempiras(data.precio_unidad);
                        document.getElementById("cantidad").value = 0;
                        document.getElementById('cantidad').max = cantidadMax;
                        document.getElementById('cantidad').min = 1;

                        let htmlBodega =
                            `<option value="${data.bodegaId}" selected="" disabled="">${data.nombreBodega}</option>`;
                        let htmlSegmento =
                            `<option value="${data.segmentoId}" selected="" disabled="">${data.segmento}</option>`;
                        let htmlSeccion =
                            `<option value="${data.seccionId}" selected="" disabled="">${data.seccion}</option>`;

                        document.getElementById('bodega').innerHTML = htmlBodega;
                        document.getElementById('segmento').innerHTML = htmlSegmento;
                        document.getElementById('seccion').innerHTML = htmlSeccion;

                        $('#modal_devolver_producto').modal('show');

                    })
                    .catch(err => {
                        console.log(err);
                        let data = err.response.data;
                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text,
                        })
                    })

            }

            function agregarProductoLista() {
                let cantidad = document.getElementById('cantidad').value;
                let cantidadMaxima = document.getElementById('cantidadMaxima').value;

                let idProducto = document.getElementById('idProducto').value;
                let seccion = document.getElementById('seccion');


                let repetidoFlag = false;

                //****************Comprueba si el producto con la seccion se repite************************/
                productoSeccion.forEach(array => {
                    if (array[0] == idProducto && array[1] == seccion.value) {
                        repetidoFlag = true;
                        return;
                    }
                });

                if (repetidoFlag) {

                    Swal.fire({
                        icon: "warning",
                        title: "Advertencia!",
                        text: "El producto con la sección correspondiente ya se encuentra en la lista.",
                    })
                    $('#modal_devolver_producto').modal('hide')
                    return;
                }
                //****************Comprueba si el producto con la seccion se repite************************/



                if (+cantidad == 0 || !cantidad) {
                    $('#modal_devolver_producto').modal('hide')
                    Swal.fire({
                        icon: "warning",
                        title: "Advertencia",
                        text: "La cantidad a devolver debe ser mayor a 0.",
                    })
                    return;
                }


                if (+cantidad > +cantidadMaxima) {
                    $('#modal_devolver_producto').modal('hide')
                    Swal.fire({
                        icon: "warning",
                        title: "Advertencia",
                        text: "La cantidad excede el maximo permitido.",
                    })
                    return;
                }




                let nombre = document.getElementById('nombre').value;
                let idFactura = document.getElementById('idFactura').value;
                let idMedidaVenta = document.getElementById('idMedidaVenta').value;
                let unidad = document.getElementById('unidad').value;
                let precio = document.getElementById('precio').value;


                let unidad_venta = document.getElementById('unidad_venta').value;
                let isvPorcentaje = document.getElementById('isvPorcentaje').value;


                let bodega = document.getElementById('bodega');
                let segmento = document.getElementById('segmento');


                let bodegaTexto = bodega.options[bodega.selectedIndex].text;
                let seccionTexto = seccion.options[seccion.selectedIndex].text;

                let subTotal = precio * cantidad * unidad_venta;


                let isv = subTotal * (isvPorcentaje / 100);

                let total = subTotal + isv;


                let html = `
                    <tr id="tr${contador}">
                                    <td>
                                        ${nombre}
                                        <input type="hidden" id="IdProducto${contador}" name="IdProducto${contador}" value="${idProducto}" form="guardar_devolucion">
                                        <input type="hidden" id="IdSeccion${contador}" name="IdSeccion${contador}" value="${seccion.value}" form="guardar_devolucion">
                                        <input type="hidden" id="nombreProducto${contador}" name="nombreProducto${contador}" value="${nombre}" form="guardar_devolucion">
                                        <input type="hidden" id="precio${contador}" name="precio${contador}" value="${precio}" form="guardar_devolucion">
                                    </td>
                                    <td>${bodegaTexto}</td>
                                    <td>${seccionTexto}</td>
                                    <td>${monedaLempiras(precio)}</td>
                                    <td>
                                        ${cantidad}
                                        <input type="hidden" id="cantidad${contador}" name="cantidad${contador}" value="${cantidad}" form="guardar_devolucion">
                                    </td>
                                    <td>${unidad}
                                        <input type="hidden" id="idUnidadMedida${contador}" name="idUnidadMedida${contador}" value="${idMedidaVenta}" form="guardar_devolucion" >
                                    </td>
                                    <td>
                                        ${monedaLempiras(subTotal)}
                                        <input type="hidden" id="subTotal${contador}" name="subTotal${contador}" value="${subTotal}" form="guardar_devolucion" >
                                    </td>
                                    <td>
                                        ${monedaLempiras(isv)}
                                        <input type="hidden" id="isv${contador}" name="isv${contador}" value="${isv}" form="guardar_devolucion" >
                                    </td>
                                    <td>
                                        ${monedaLempiras(total)}
                                        <input type="hidden" id="total${contador}" name="total${contador}" value="${total}" form="guardar_devolucion" >
                                    </td>
                                    <td><button class="btn btn-danger" onclick="eliminarFila(${contador},${subTotal},${isv},${total})">Eliminar</button></td>
                                </tr>
                    `;

                let idCuerpoLista = document.getElementById("cuerpoLista");

                $('#modal_devolver_producto').modal('hide')
                idCuerpoLista.insertAdjacentHTML('beforeend', html);
                document.getElementById("form_producto_devolver").reset();
                $('#form_producto_devolver').parsley().reset();

                let sub_totalInput = document.getElementById("subTotalGeneralCredito").value;
                sub_totalInput = (+sub_totalInput) + (+subTotal);
                document.getElementById("subTotalGeneralCredito").value = sub_totalInput;

                let sub_totalGrabadoInput = document.getElementById("subTotalGeneralGrabadoCredito").value;
                let sub_totalExcentoInput = document.getElementById("subTotalGeneralExcentoCredito").value;

                if(isv >0){
                sub_totalGrabadoInput = (+sub_totalGrabadoInput) + (+subTotal);
                document.getElementById("subTotalGeneralGrabadoCredito").value = sub_totalGrabadoInput;
                }



                if (isv == 0) {
                    sub_totalExcentoInput = (+sub_totalExcentoInput) + (+subTotal);
                    document.getElementById("subTotalGeneralExcentoCredito").value = sub_totalExcentoInput;
                }


                let isvInput = document.getElementById("isvGeneralCredito").value;
                isvInput = (+isvInput) + (+isv);
                document.getElementById("isvGeneralCredito").value = isvInput;

                let totalInput = document.getElementById("totalGeneralCredito").value;
                totalInput = (+totalInput) + (+total);
                document.getElementById("totalGeneralCredito").value = totalInput;

                document.getElementById("subTotalGeneralCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(sub_totalInput);


                document.getElementById("subTotalGeneralGrabadoCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(sub_totalGrabadoInput);



                document.getElementById("subTotalGeneralExcentoCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(sub_totalExcentoInput);




                document.getElementById("isvGeneralCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(isvInput);
                document.getElementById("totalGeneralCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(totalInput);

                document.getElementById("solicitarFactura").disabled = true;
                document.getElementById("cliente").disabled = true;
                document.getElementById("factura").disabled = true;

                arrayInputs.push(contador);
                contador++;
                productoSeccion.push([idProducto, seccion.value]);

                return;
            }

            function monedaLempiras(monto) {
                let numero = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(monto)

                return numero;
            }

            function eliminarFila(id, subtotal, isv, total) {

                let idProducto = document.getElementById("IdProducto" + id).value;
                let idSeccion = document.getElementById("IdSeccion" + id).value;
                let array = [];
                for (let i = 0; i < productoSeccion.length; i++) {

                    array = productoSeccion[i];
                    if (array[0] == idProducto && array[1] == idSeccion) {
                        productoSeccion.splice(i, 1);
                    }

                }




                const element = document.getElementById('tr' + id);
                element.remove();



                var myIndex = arrayInputs.indexOf(id);
                if (myIndex !== -1) {
                    arrayInputs.splice(myIndex, 1);
                    // this.totalesGenerales();

                }

                let sub_totalInput = document.getElementById("subTotalGeneralCredito").value;
                sub_totalInput = (+sub_totalInput) - (+subtotal);
                document.getElementById("subTotalGeneralCredito").value = sub_totalInput;

                let sub_totalGrabadoInput = document.getElementById("subTotalGeneralGrabadoCredito").value;
                if(isv > 0){
                sub_totalGrabadoInput = (+sub_totalGrabadoInput) - (+subtotal);
                document.getElementById("subTotalGeneralGrabadoCredito").value = sub_totalGrabadoInput;
                }


                let sub_totalExcentoInput = document.getElementById("subTotalGeneralExcentoCredito").value;
                if(isv == 0){

                sub_totalExcentoInput = (+sub_totalExcentoInput) - (+subtotal);
                document.getElementById("subTotalGeneralExcentoCredito").value = sub_totalExcentoInput;
                }








                let isvInput = document.getElementById("isvGeneralCredito").value;
                isvInput = (+isvInput) - (+isv);
                document.getElementById("isvGeneralCredito").value = isvInput;

                let totalInput = document.getElementById("totalGeneralCredito").value;
                totalInput = (+totalInput) - (+total);
                document.getElementById("totalGeneralCredito").value = totalInput;


                document.getElementById("subTotalGeneralCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(sub_totalInput);


                document.getElementById("subTotalGeneralGrabadoCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(sub_totalGrabadoInput);



                document.getElementById("subTotalGeneralExcentoCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(sub_totalExcentoInput);






                document.getElementById("isvGeneralCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(isvInput);
                document.getElementById("totalGeneralCreditoMostrar").value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(totalInput);



            }

            $(document).on('submit', '#guardar_devolucion', function(event) {

                event.preventDefault();

                guardarNotaCredito();

            });

            function guardarNotaCredito() {
                let idFactura = document.getElementById("idFactura").value;
                document.getElementById("btn_guardar_nota_credito").disabled = true;

                var dataForm = new FormData($('#guardar_devolucion').get(0));

                let longitudArreglo = arrayInputs.length;
                for (var i = 0; i < longitudArreglo; i++) {



                    dataForm.append("arregloIdInputs[]", arrayInputs[i]);

                }

                dataForm.append("idFactura", idFactura);

                // let table = $('#tbl_translados_destino').DataTable();
                // table.destroy();

                axios.post('/nota/credito/guardar', dataForm)
                    .then(response => {

                        let data = response.data;
                        let contador = data.contadorTranslados;

                        // document.getElementById("btn_guardar_nota_credito").disabled = false;

                        //Eliminar DIVS y que muestre alert para imprimir
                        //Agregar funcion para anular
                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            html: data.text,

                        })
                        {{--  setTimeout(function(){
                            location.reload()
                        }, 40000)  --}}




                        location.reload()


                        return;


                    })
                    .catch(err => {
                        //console.log(err)
                        document.getElementById("btn_guardar_nota_credito").disabled = false;
                        console.log(err);
                        $('#modal_transladar_producto').modal('hide')



                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Ha ocurrido un error.",
                        })

                    })
            }
        </script>
    @endpush

</div>
<?php
    date_default_timezone_set('America/Tegucigalpa');
    $act_fecha=date("Y-m-d");
    $act_hora=date("H:i:s");
    $mes=date("m");
    $year=date("Y");
    $datetim=$act_fecha." ".$act_hora;
?>
<script>
    function mostrarHora() {
        var fecha = new Date(); // Obtener la fecha y hora actual
        var hora = fecha.getHours();
        var minutos = fecha.getMinutes();
        var segundos = fecha.getSeconds();

        // A単adir un 0 delante si los minutos o segundos son menores a 10
        minutos = minutos < 10 ? "0" + minutos : minutos;
        segundos = segundos < 10 ? "0" + segundos : segundos;

        // Mostrar la hora actual en el elemento con el id "reloj"
        document.getElementById("reloj").innerHTML = hora + ":" + minutos + ":" + segundos;
    }
    // Actualizar el reloj cada segundo
    setInterval(mostrarHora, 1000);
</script>
<div class="float-right">
    <?php echo "$act_fecha";  ?> <strong id="reloj"></strong>
</div>
<div>
    <strong>Copyright</strong> Distribuciones Valencia &copy; <?php echo "$year";  ?>
</div>
<p id="reloj"></p>
