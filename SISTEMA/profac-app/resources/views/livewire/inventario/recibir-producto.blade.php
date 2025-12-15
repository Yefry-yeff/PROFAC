<div>
    @push('styles')
        <style>
            .letra-tamanio {
                font-size: 0.8rem
            }

            .aling-button {
                justify-content: flex-end !important;
                justify-content: flex-end !important;
            }

            @media (max-width: 595px) {
            .aling-button {
                justify-content: flex-start !important;
            }

            }

        </style>
    @endpush
    {{-- The whole world belongs to you. --}}

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">

            <div class="col-12 col-sm-8 col-md-8 col-lg-8 col-xl-9
            ">

                    <h2>Recibir Producto En Bodega</h2>

                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a>Recepcion de Producto</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a>Excedentes</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a>Incidencias</a>
                        </li>

                    </ol>

            </div>
            <div class="col-12 col-sm-4 col-md-4 col-lg-4 col-xl-3">
                <div class="d-flex aling-button " style=" margin-top: 1.5rem">
                    {{-- <a class="" style="display: block" href="/inventario/compras/incidencias/{{$idCompra}}"><p class="" ><span class="badge badge-primary p-2" style="font-size:0.95rem;"><i class="fa-solid fa-eye"></i> Ver incidencias</span></p></a> --}}
                    <button class="btn btn-w-m btn-primary "> <a style="font-size:0.95rem; color:white" href="/inventario/compras/incidencias/{{$idCompra}}"><i class="fa-solid fa-eye"></i> Ver Incidencias</a> </button>
                </div>


            </div>
    </div>


    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Lista de Compra</h3>
                    </div>
                    <div class="ibox-content">
                        <div>

                            <p class="letra-tamanio"><strong>Numero de Factura:</strong>
                                {{ $datosCompra->numero_factura }}</p>
                            <p class="letra-tamanio"><strong class="">Numero de compra:</strong>
                                {{ $datosCompra->numero_orden }}</p>
                            <p class="letra-tamanio"><strong class="">Proveedor:</strong>
                                {{ $datosCompra->nombre }}</p>

                        </div>
                        <div class="table-responsive">
                            <table id="tbl_recibir_compra" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>N°</th>
                                        <th>Codigo de Producto</th>
                                        <th>Nombre</th>
                                        <th>Unidad de Medida</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>Cantidad sin asignar</th>
                                        <th>Sub Total</th>
                                        <th>ISV</th>
                                        <th>Total</th>
                                        <th>Fecha de Vencimiento</th>
                                        <th>Estado de Recibido</th>
                                        <th>Opciones</th>

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


    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="ibox ">
                    <div class="ibox-title ">
                        <h3>Detalle De Recepcion En Bodega</h3>

                    </div>
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="tbl_producto_bodega" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>

                                        <th>Codigo Producto</th>
                                        <th>Nombre</th>
                                        <th>Cantidad en compra</th>
                                        <th>Departamento</th>
                                        <th>Municipio</th>
                                        <th>Bodega</th>
                                        <th>Direccion</th>
                                        <th>Seccion</th>
                                        <th>Cantidad Ingresada</th>
                                        <th>Cantidad Disponible</th>
                                        <th>Recibido Por:</th>
                                        <th>Fecha</th>
                                        <th>Opciones</th>

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

    <!-- Modal para recibir en Bodega-->
    <div class="modal fade" id="modalRecibirProducto" tabindex="-1" role="dialog"
        aria-labelledby="modalRecibirProductoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="modalRecibirProductoLabel"> Recibir En Bodega </h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <form id="recibirProducto" data-parsley-validate>

                                <div class="form-group">
                                    <label for="comentario">Nombre de producto</label>
                                    <input id="nompreProducto" name="nompreProducto" type="text" class="form-control" readonly
                                        required data-parsley-required>
                                </div>

                                <div class="form-group">
                                    <label for="comentario">Cantidad maxima a recibir</label>
                                    <input id="cantidadMax" name="cantidadMax" type="number" class="form-control" readonly
                                        required data-parsley-required>
                                </div>


                                <div class="form-group">
                                    <label for="bodega">Bodega</label>
                                    <select class="form-control m-b" name="bodega" id="bodega"
                                        onchange="listarSegmentos()" required data-parsley-required>
                                        <option value="" selected disabled>---Seleccione una bodega---</option>

                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="segmento">Segmento</label>
                                    <select class="form-control m-b" name="segmento" id="segmento" required
                                        data-parsley-required onchange="listarSecciones()">
                                        <option value="" selected disabled>---Seleccione un segmento---</option>

                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="seccion">Seccion</label>
                                    <select class="form-control m-b" name="seccion" id="seccion" required
                                        data-parsley-required="">
                                        <option value="" selected disabled>---Seleccione una sección---</option>

                                    </select>
                                </div>






                                <div class="form-group">
                                    <label for="comentario">Cantidad a Recibir</label>
                                    <input id="cantidad" name="cantidad" type="number" min="1" class="form-control"
                                        required data-parsley-required>

                                </div>




                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button id="btn_recibir_bodega" type="submit" form="recibirProducto" class="btn btn-primary">Recibir
                        en bodega</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para registrar incidencia de producto-->
    <div class="modal fade" id="modalRecibirIncidencia" tabindex="-1" role="dialog"
        aria-labelledby="modalRecibirIncidenciaLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="modalRecibirIncidenciaLabel"> Registrar Incidencia </h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <form id="registrarIncidencia" data-parsley-validate>


                                <div class="form-group">
                                    <label for="bodega">Comentario</label>
                                    <textarea name="comentario" id="comentario" cols="4" rows="5" class="form-control" required
                                        data-parsley-required></textarea>
                                </div>



                                <div class="form-group">
                                    <label for="imagen">Imgen de evidencia</label>


                                    <input id="imagen" name="imagen" type="file"
                                        accept="image/png, image/jpeg, image/jpg, application/pdf"
                                        class="form-control">

                                </div>




                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button id="btn_registro_incidencia" type="submit" form="registrarIncidencia"
                        class="btn btn-primary">Registrar Incendia
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal para registrar excedente de producto-->
    <div class="modal fade" id="modalRecibirExcedente" tabindex="-1" role="dialog"
        aria-labelledby="modalRecibirExcedenteLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="modalRecibirExcedenteLabel"> Registrar Producto Excedente </h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <form id="recibirProductoExcedente" data-parsley-validate>


                                <div class="form-group">
                                    <label for="bodegaExcedente">Bodega</label>
                                    <select class="form-control m-b" name="bodegaExcedente" id="bodegaExcedente"
                                        onchange="listarSegmentosExcedente()" required data-parsley-required>
                                        <option value="" selected disabled>---Seleccione una bodega---</option>

                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="segmentoExcedente">Segmento</label>
                                    <select class="form-control m-b" name="segmentoExcedente" id="segmentoExcedente"
                                        required data-parsley-required onchange="listarSeccionesExcedente()">
                                        <option value="" selected disabled>---Seleccione un segmento---</option>

                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="seccionExcedente">Seccion</label>
                                    <select class="form-control m-b" name="seccionExcedente" id="seccionExcedente"
                                        required data-parsley-required="">
                                        <option value="" selected disabled>---Seleccione una sección---</option>

                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="cantidadExcedente">Cantidad a Recibir</label>


                                    <input id="cantidadExcedente" name="cantidadExcedente" type="number" min="1"
                                        class="form-control" required data-parsley-required>

                                </div>




                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button id="btn_recibir_excedente" type="submit" form="recibirProductoExcedente"
                        class="btn btn-primary">Recibir en
                        bodega</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para registrar incidencia de producto antes de almacenar-->
    <div class="modal fade" id="modalIncidenciaCompra" tabindex="-1" role="dialog"
        aria-labelledby="modalRecibirIncidenciaLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="modalRecibirIncidenciaLabel"> Registrar Incidencia De Compra</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <form id="registrarIncidenciaCompra" data-parsley-validate>


                                <div class="form-group">
                                    <label for="bodegaCompra">Comentario</label>
                                    <textarea name="comentarioCompra" id="comentarioCompra" cols="4" rows="5" class="form-control" required
                                        data-parsley-required></textarea>
                                </div>



                                <div class="form-group">
                                    <label for="imagenCompra">Imgen de evidencia</label>


                                    <input id="imagenCompra" name="imagenCompra" type="file"
                                        accept="image/png, image/jpeg, image/jpg, application/pdf"
                                        class="form-control">

                                </div>




                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button id="btn_registro_incidencia_compra" type="submit" form="registrarIncidenciaCompra"
                        class="btn btn-primary">Registrar Incendia
                    </button>
                </div>
            </div>
        </div>
    </div>



    @push('scripts')
        <script>

            var idCompra = {{ $idCompra }}
        </script>
        <script src="{{ asset('js/js_proyecto/inventario/recibir-producto.js') }}"></script>
    @endpush
</div>
