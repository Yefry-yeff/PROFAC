<div>
    @push('styles')
        <style>

        </style>
    @endpush
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Ajustes de Producto </h2>

        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Listado De Producto En Bodega</h3>
                    </div>
                    <div class="ibox-content">
                        <form id="selec_data_form" name="selec_data_form" data-parsley-validate>
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 b-r">
                                    <div>

                                        <label for="selectBodega" class="col-form-label focus-label">Seleccionar
                                            Bodega:</label>
                                        <select id="selectBodega" class="form-group form-control" style=""
                                            data-parsley-required onchange="obtenerSecciones()">
                                            <option value="" selected disabled>--Seleccionar una Bodega--</option>
                                        </select>

                                    </div>


                                </div>

                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 b-r">
                                    <div>

                                        <label for="selectSeccion" class="col-form-label focus-label">Seleccionar
                                            Seccion:</label>
                                        <select id="selectSeccion" class="form-group form-control" style=""
                                            data-parsley-required onchange="obtenerProductosBodega()">
                                            <option value="" selected disabled>--Seleccionar una Seccion--
                                            </option>
                                        </select>

                                    </div>


                                </div>


                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">

                                    <label for="selectProducto" class="col-form-label focus-label">Seleccionar
                                        Producto:</label>
                                    <select id="selectProducto" class="form-group form-control" style=""
                                        data-parsley-required>
                                        <option value="" selected disabled>--Seleccionar un producto por codigo ó
                                            nombre--</option>
                                    </select>

                                </div>






                            </div>
                        </form>
                        <button type="submit" form="selec_data_form" class="btn btn-primary btn-lg mb-4 mt-3">Solicitar
                            Producto</button>


                        <hr>

                        <h3 class=""><i class="fa-solid fa-warehouse  m-0 p-0" style="color: #1AA689"></i> <span
                                id="total"></span></h3>
                        <div class="table-responsive">
                            <table id="tbl_translados" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Cod Producto</th>
                                        <th>Nombre</th>
                                        <th>Unidad de Medida</th>
                                        <th>Cantidad Disponible</th>
                                        <th>Bodega</th>
                                        <th>Sección</th>
                                        <th>Fecha Ingreso</th>
                                        <th>Translado</th>



                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--Tabla para productos a ajustar-->
    <div id="lista_translado" class="">
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h3>Listado de productos</h3>
                        </div>
                        <div class="ibox-content">
                            <div class="table-responsive">
                                <form onkeydown="return event.key != 'Enter';" id="ajustar_producto_form"
                                    data-parsley-validate>
                                    <div class="form-group">
                                        <label for="tipo_ajuste_id">Motivo de ajuste<span
                                            class="text-danger">*</span></label>
                                        <select class="form-control m-b" name="tipo_ajuste_id" id="tipo_ajuste_id"
                                            required data-parsley-required>
                                            <option value="" selected disabled>---Seleccione una bodega de
                                                destino---
                                            </option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="comentario" class="col-form-label focus-label">Comentario:<span
                                                class="text-danger">*</span></label>
                                        <textarea spellcheck="true" placeholder="Escriba aquí..." required id="comentario" name="comentario" cols="30"
                                            rows="3" class="form-group form-control" data-parsley-required></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="solicitado_por">Solicitado por:<span
                                                    class="text-danger">*</span></label>
                                                <select class="form-control " name="solicitado_por" id="solicitado_por"
                                                    required data-parsley-required>
                                                    <option value="" selected disabled>---Seleccionar una
                                                        opción:---
                                                    </option>

                                                </select>
                                            </div>



                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">

                                                <label for="fecha">Fecha de solicitud:<span
                                                    class="text-danger">*</span></label>
                                                <input class="form-control" required data-parsley-required
                                                    type="date" name="fecha" id="fecha">
                                            </div>

                                        </div>
                                    </div>

                                </form>
                                <table id="tbl_translados_productos"
                                    class="table table-striped table-bordered table-hover">
                                    <thead class="">
                                        <tr>
                                            <th>Eliminar</th>
                                            <th>Cod Producto</th>
                                            <th>Nombre Producto</th>
                                            <th>Bodega</th>
                                            <th>Tipo</th>
                                            <th>Cantidad</th>
                                            <th>Uniad de medida</th>
                                        </tr>
                                    </thead>

                                    <tbody id="cuerpoListaProducto">


                                    </tbody>

                                </table>



                                <button id="btn_realizar_ajuste" type="submit" form="ajustar_producto_form"
                                    class="btn btn-primary btn-lg mb-4 mt-3">Guardar Ajuste</button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <!-- Modal para transferir producto a otra bodega-->
    <div class="modal fade" id="modal_transladar_producto" role="dialog"
        aria-labelledby="modal_transladar_productoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="modal_transladar_productoLabel"> Datos de Ajuste </h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBody">
                    <div class="row">
                        <div class="col-sm-12">
                            <form id="datos_ajuste_form" name="datos_ajuste_form" data-parsley-validate>
                                <input type="hidden" name="idRecibido" id="idRecibido">

                                <input type="hidden" name="bodega" id="bodega" value="">
                                <input type="hidden" name="seccion" id="seccion" value="">





                                <div class="form-group">
                                    <label for="aritmetica">Método de ajuste</label>
                                    <select class="form-control m-b" name="aritmetica" id="aritmetica" required
                                        data-parsley-required>
                                        <option value="" selected disabled>---Seleccione un método de ajuste ---
                                        </option>
                                        <option value="1">Sumar Unidades</option>
                                        <option value="2">Restar Unidades</option>
                                    </select>
                                </div>


                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">

                                            <label for="idProducto">Código de producto</label>
                                            <input type="number" name="idProducto" id="idProducto"
                                                class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label for="nombre_producto">Nombre de producto:</label>
                                            <input class="form-control" required data-parsley-required type="text"
                                                name="nombre_producto" id="nombre_producto" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">

                                            <label for="cantidad_dispo">Cantidad disponible en sección:</label>
                                            <input type="number" name="cantidad_dispo" id="cantidad_dispo"
                                                class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label for="unidad">Unidad de Medida:</label>
                                            <select onchange="calcularTotalUnidades()" class="form-control "
                                                name="unidad" id="unidad" required data-parsley-required>
                                                <option value="" data-id="" selected disabled>---Seleccionar
                                                    una unidad de medida:---</option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">

                                            <label for="precio_producto">Precio unitario de producto:</label>
                                            <input type="number" step="any" name="precio_producto"
                                                id="precio_producto" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label for="cantidad">Cantidad:</label>
                                            <input class="form-control" autocomplete="off" required
                                                data-parsley-required type="number" min="1" name="cantidad"
                                                id="cantidad" onchange="calcularTotalUnidades()">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <div class="form-group">
                                            <label for="total_unidades">Total de unidades para realizar ajuste:</label>
                                            <input class="form-control" autocomplete="off" required
                                                data-parsley-required type="number" name="total_unidades"
                                                id="total_unidades" readonly>
                                        </div>
                                    </div>
                                </div>


                            </form>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" form="datos_ajuste_form" class="btn btn-primary">Agregar producto</button>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')

        <script src="{{ asset('js/js_proyecto/inventario/ajustes.js') }}"></script>
        <script>



            function obtenerListaBodega() {
                let idBodega = document.getElementById('selectBodega').value;
                let idProducto = document.getElementById('selectProducto').value;
                //let data = {'idBodega':idBodega, 'idProducto',idProducto};

                let table = $('#tbl_translados').DataTable();
                table.destroy();

                $('#tbl_translados').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                    },
                    pageLength: 10,
                    responsive: true,
                    "ajax": {
                        'url': "/ajustes/listado/producto/bodega",
                        'data': {
                            'idBodega': idBodega,
                            idProducto: idProducto
                        },
                        'type': 'post',
                        'headers': {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }


                    },
                    "columns": [{
                            data: 'idProducto'
                        },
                        {
                            data: 'nombre'
                        },
                        {
                            data: 'simbolo'
                        },
                        {
                            data: 'cantidad_disponible'
                        },
                        {
                            data: 'bodega'
                        },
                        {
                            data: 'descripcion'
                        },
                        {
                            data: 'created_at'
                        },
                        {
                            data: 'opciones'
                        },



                    ],
                    drawCallback: function() {
                        var sum = $('#tbl_translados').DataTable().column(3).data().sum();
                        let html = 'Cantidad Total en Bodega: ' + sum
                        $('#total').html(html);


                    }

                });



            }


        </script>
    @endpush

</div>
