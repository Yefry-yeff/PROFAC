<div>
    @push('styles')
        <style>

        </style>
    @endpush
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Translado en Bodega</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a>Translado de Producto</a>
                </li>
                {{-- <li class="breadcrumb-item">
                    <a data-toggle="modal" data-target="#modal_producto_crear">Registrar</a>
                </li> --}}

            </ol>
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
                                            data-parsley-required onchange="obteneProducto()">
                                            <option value="" selected disabled>--Seleccionar una Bodega--</option>
                                        </select>

                                    </div>


                                </div>


                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">

                                    <label for="selectProducto" class="col-form-label focus-label">Seleccionar
                                        Producto:</label>
                                    <select id="selectProducto" class="form-group form-control" style=""
                                        data-parsley-required disabled >
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




    <!-- Modal para transferir producto a otra bodega-->
    <div class="modal fade" id="modal_transladar_producto" tabindex="-22" role="dialog"
        aria-labelledby="modal_transladar_productoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="modal_transladar_productoLabel"> Transladar a otra bodega </h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <form id="recibirProducto" data-parsley-validate>


                                <div class="form-group">
                                    <label for="bodega">Bodega</label>
                                    <select class="form-control m-b" name="bodega" id="bodega"
                                        onchange="listarSegmentos()" required data-parsley-required>
                                        <option value="" selected disabled>---Seleccione una bodega de destino---
                                        </option>

                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="segmento">Segmento</label>
                                    <select class="form-control m-b" name="segmento" id="segmento" required
                                        data-parsley-required onchange="listarSecciones()">
                                        <option value="" selected disabled>---Seleccione un segmento de destino---
                                        </option>

                                    </select>
                                </div>



                                <div class="form-group">
                                    <label for="seccion">Seccion</label>
                                    <select class="form-control m-b" name="seccion" id="seccion" required
                                        data-parsley-required="">
                                        <option value="" selected disabled>---Seleccione una sección de destino---
                                        </option>

                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="comentario">Cantidad a transladar</label>


                                    <input id="cantidad" name="cantidad" type="number" min="1"
                                        class="form-control" required data-parsley-required>

                                </div>

                                <div class="form-group">
                                    <label for="seccion">Unidad de medida</label>
                                    <select class="form-control m-b" name="Umedida" id="Umedida" required
                                        data-parsley-required="">
                                        <option value="" selected disabled>---Seleccione una Unidad de medida---
                                        </option>

                                    </select>
                                </div>

                                <input id="idProducto" type="hidden" value="">

                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button id="btn_recibir_bodega" type="submit" form="recibirProducto"
                        class="btn btn-primary">Agregar a lista</button>
                </div>
            </div>
        </div>
    </div>


    <!--Tabla para productos a transladar-->
    <div id="lista_translado" class="">
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="ibox ">
                            <div class="ibox-title">
                                <h3>Listado de productos a transaladar</h3>
                            </div>
                             <div class="ibox-content">
                                <div class="table-responsive">
                                    <form onkeydown="return event.key != 'Enter';" autocomplete="off" id="guardar_translados"   name="guardar_translados" data-parsley-validate>

                                    </form>
                                    <table id="tbl_translados_productos"
                                        class="table table-striped table-bordered table-hover">
                                        <thead class="">
                                            <tr>
                                                <th>Eliminar</th>
                                                <th>Cod Producto</th>
                                                <th>Bodega</th>
                                                <th>Segmento</th>
                                                <th>Seccion</th>
                                                <th>Cantidad</th>
                                                <th>Uniad de medida</th>
                                            </tr>
                                        </thead>

                                        <tbody id="cuerpoListaProducto">



                                        </tbody>

                                    </table>

                                    <button id="btn_guardar_translado" type="submit" form="guardar_translados"   class="btn btn-primary btn-lg mb-4 mt-3" >Guardar Translado</button>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="destino" class="d-none">
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h3>Listado De Producto En Bodega De Destino</h3>
                        </div>
                        <div class="ibox-content">
                            <div class="table-responsive">
                                <table id="tbl_translados_destino"
                                    class="table table-striped table-bordered table-hover">
                                    <thead class="">
                                        <tr>
                                            <th>Cod Producto</th>
                                            <th>Nombre</th>
                                            <th>Unidad de Medida</th>
                                            <th>Cantidad Disponible</th>
                                            <th>Bodega</th>
                                            <th>Sección</th>
                                            <th>Fecha Ingreso</th>




                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                                <button onclick="limpiar()" type="button" class="btn btn-warning btn-lg mb-4 mt-3" >Limpiar</button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@push('scripts')
<script src="{{ asset('js/js_proyecto/inventario/traslados.js') }}"></script>
@endpush

</div>
