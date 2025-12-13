<div>
    @push('styles')
        <style>
            @media (max-width: 600px) {
                .ancho-imagen {
                    max-width: 200px;
                }
            }

            @media (min-width: 601px) and (max-width:900px) {
                .ancho-imagen {
                    max-width: 300px;
                }
            }

            @media (min-width: 901px) {
                .ancho-imagen {
                    max-width: 12rem;
                }
            }

            /* a {
                    pointer-events: none;
                } */
            .loader,
            .loader:before,
            .loader:after {
                border-radius: 50%;
            }

            .loader {
                color: #0dc5c1;
                font-size: 11px;
                text-indent: -99999em;
                margin: 55px auto;
                position: relative;
                width: 10em;
                height: 10em;
                box-shadow: inset 0 0 0 1em;
                -webkit-transform: translateZ(0);
                -ms-transform: translateZ(0);
                transform: translateZ(0);
            }

            .loader:before,
            .loader:after {
                position: absolute;
                content: '';
            }

            .loader:before {
                width: 5.2em;
                height: 10.2em;
                background: #ffffff;
                border-radius: 10.2em 0 0 10.2em;
                top: -0.1em;
                left: -0.1em;
                -webkit-transform-origin: 5.1em 5.1em;
                transform-origin: 5.1em 5.1em;
                -webkit-animation: load2 2s infinite ease 1.5s;
                animation: load2 2s infinite ease 1.5s;
            }

            .loader:after {
                width: 5.2em;
                height: 10.2em;
                background: #ffffff;
                border-radius: 0 10.2em 10.2em 0;
                top: -0.1em;
                left: 4.9em;
                -webkit-transform-origin: 0.1em 5.1em;
                transform-origin: 0.1em 5.1em;
                -webkit-animation: load2 2s infinite ease;
                animation: load2 2s infinite ease;
            }

            @-webkit-keyframes load2 {
                0% {
                    -webkit-transform: rotate(0deg);
                    transform: rotate(0deg);
                }

                100% {
                    -webkit-transform: rotate(360deg);
                    transform: rotate(360deg);
                }
            }

            @keyframes load2 {
                0% {
                    -webkit-transform: rotate(0deg);
                    transform: rotate(0deg);
                }

                100% {
                    -webkit-transform: rotate(360deg);
                    transform: rotate(360deg);
                }
            }
        </style>
    @endpush
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Unidades De Medida</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a>Listar</a>
                </li>
                <li class="breadcrumb-item">
                    <a data-toggle="modal" data-target="#modal_producto_crear">Registrar</a>
                </li>

            </ol>
        </div>


        <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
            <div style="margin-top: 1.5rem">
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal"
                    data-target="#modal_producto_crear"><i class="fa fa-plus"></i> Añadir Unidad</a>
            </div>
        </div>


    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_unidades_listar" class="table table-striped table-bordered table-hover col-md-10">
                                <thead class="">
                                    <tr>
                                        <th>Cod</th>
                                        <th>Nombre</th>
                                        <th>Simbolo</th>
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

            <!-- Modal para registro de Unidad-->
            <div class="modal fade" id="modal_producto_crear" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="exampleModalLabel">Registro de Unidad</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form id="crearUnidadForm" name="crearUnidadForm" data-parsley-validate>
                                {{-- <input type="hidden" name="_token" value="{!! csrf_token() !!}"> --}}
                                <div class="row" id="row_datos">
                                    <div class="col-md-12">
                                        <label for="nombre_producto" class="col-form-label focus-label">Nombre de la
                                            Unidad:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="nombre_producto"
                                            name="nombre_producto" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="simbolo_producto" class="col-form-label focus-label">Simbolo de la
                                            Unidad:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="simbolo_producto"
                                            name="simbolo_producto" data-parsley-required>
                                    </div>




                                </div>
                            </form>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            <button type="submit" form="crearUnidadForm" class="btn btn-primary">Guardar
                                Unidad</button>
                        </div>
                    </div>
                </div>
            </div>

                        <!-- Modal para editar Unidad-->
                        <div class="modal fade" id="modal_producto_editar" tabindex="-1" role="dialog"
                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title" id="exampleModalLabel">Editar Unidad</h3>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="modal-body">
                                    <form id="editarProductoForm" name="editarProductoForm" data-parsley-validate>
                                        {{-- <input type="hidden" name="_token" value="{!! csrf_token() !!}"> --}}
                                        <input id="idUnidad" name="idUnidad" type="hidden" value="">
                                        <div class="row" id="row_datos">
                                            <div class="col-md-12">
                                                <label for="nombre_producto_editar" class="col-form-label focus-label">Nombre de la
                                                    Unidad:</label>
                                                <input class="form-control" required type="text" id="nombre_producto_editar"
                                                    name="nombre_producto_editar" data-parsley-required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="simbolo_producto_editar" class="col-form-label focus-label">Simbolo de la
                                                    Unidad:</label>
                                                <input class="form-control" required type="text" id="simbolo_producto_editar"
                                                    name="simbolo_producto_editar" data-parsley-required>
                                            </div>




                                        </div>
                                    </form>

                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                    <button type="submit" form="editarProductoForm" class="btn btn-primary">Editar
                                        Unidad</button>
                                </div>
                            </div>
                        </div>
                    </div>


        </div>










    </div>
    @push('scripts')
        <script src="{{ asset('js/js_proyecto/inventario/unidades-medida.js') }}"></script>
    @endpush
</div>
