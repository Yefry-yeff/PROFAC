<div>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2>Usuarios</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">Gestión de Usuarios</a>
                </li>


            </ol>
        </div>

        <!-- Modal para cambiar rol de usuario-->
        <div class="modal fade" id="modal_usuario_rol" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="exampleModalLabel">Actualización de usuarios</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form id="userEditForm" name="userEditForm" data-parsley-validate>
                                <div class="row" id="row_datos">

                                    <div class="col-md-12">
                                        <label for="nombre" class="col-form-label focus-label">Nombre Usuario:<span class="text-danger">*</span></label>
                                        <input required type="hidden" id="id_usuario" name="id_usuario" >
                                        <input class="form-control" required type="text" id="nombre_usuario" name="nombre_usuario" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="correo" class="col-form-label focus-label">Identidad:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" pattern="[A-Za-z0-9]+" id="identidad_usuario" name="identidad_usuario" data-parsley-required>
                                    </div>


                                    <div class="col-md-12">
                                        <label for="correo" class="col-form-label focus-label">Fecha de Nacimiento:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="date" id="fenacimiento_usuario" name="fenacimiento_usuario" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="correo" class="col-form-label focus-label">Correo de usuario:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="correo_usuario" name="correo_usuario" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="seleccionarRol" class="col-form-label focus-label">Seleccione nuevo rol:<span class="text-danger">*</span></label>
                                        <select class="form-control"required id="seleccionarRol" name="seleccionarRol" data-parsley-required >

                                        </select>

                                    </div>
                                </div>
                            </form>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            <button type="submit" form="userEditForm" class="btn btn-primary">Actualizar</button>
                        </div>
                    </div>
                </div>
        </div>

    </div>


    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="tbl_usuariosListar" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Codigo</th>
                                        <th>Nombre</th>
                                        <th>Telefono</th>
                                        <th>Correo</th>
                                        <th>Identidad</th>
                                        <th>Fecha de Nacimiento</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Fecha Ingreso</th>
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




</div>

@push('scripts')

    <script src="{{ asset('js/js_proyecto/usuarios/usuarios.js') }}"></script>

@endpush
