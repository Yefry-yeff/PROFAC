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
    </div>

    <!-- Modal para editar usuario -->
    <div class="modal fade" id="modal_usuario_rol" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title" id="exampleModalLabel">Editar Usuario</h3>
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
                                    <label class="col-form-label focus-label">Identidad:</label>
                                    <input class="form-control" type="text" pattern="[A-Za-z0-9]+" id="identidad_usuario" name="identidad_usuario">
                                </div>

                                <div class="col-md-12">
                                    <label for="telefono_usuario" class="col-form-label focus-label">Teléfono:</label>
                                    <input class="form-control" type="text" id="telefono_usuario" name="telefono_usuario">
                                </div>

                                <div class="col-md-12">
                                    <label class="col-form-label focus-label">Fecha de Nacimiento:</label>
                                    <input class="form-control" type="date" id="fenacimiento_usuario" name="fenacimiento_usuario">
                                </div>

                                <div class="col-md-12">
                                    <label class="col-form-label focus-label">Correo de usuario:<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="text" id="correo_usuario" name="correo_usuario" data-parsley-required autocomplete="username">
                                </div>

                                <div class="col-md-12">
                                    <label for="seleccionarRol" class="col-form-label focus-label">Seleccione nuevo rol:<span class="text-danger">*</span></label>
                                    <select class="form-control" required id="seleccionarRol" name="seleccionarRol" data-parsley-required>
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

    <!-- Modal Cambiar Contraseña -->
    <div class="modal fade" id="modal_cambiar_contrasena" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Cambiar Contraseña</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formCambiarContrasena" name="formCambiarContrasena">
                        <input type="hidden" id="id_usuario_pwd" name="id_usuario_pwd">
                        <div class="form-group">
                            <label class="col-form-label focus-label">Nueva Contraseña:<span class="text-danger">*</span></label>
                            <input class="form-control" type="password" id="nueva_contrasena" name="nueva_contrasena"
                                minlength="8" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                            <small class="text-muted">Mínimo 8 caracteres</small>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label focus-label">Confirmar Contraseña:<span class="text-danger">*</span></label>
                            <input class="form-control" type="password" id="confirmar_contrasena" name="confirmar_contrasena"
                                minlength="8" placeholder="Confirme la contraseña" autocomplete="new-password">
                            <small id="msg_pwd_no_coincide" class="text-danger" style="display:none;">Las contraseñas no coinciden.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-warning" onclick="guardarContrasena()"><i class="fa fa-key"></i> Guardar Contraseña</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear nuevo usuario-->
    <div class="modal fade" id="modal_usuario_crear" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title" id="exampleModalLabel">Registrar Nuevo Usuario</h3>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <form id="userAddForm" name="userAddForm" data-parsley-validate>
                            <div class="row">

                                <div class="col-md-12">
                                    <label for="nombre_usuario" class="col-form-label focus-label">Nombre Usuario:<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="text" id="nombre_usuario_nuevo" name="nombre_usuario" placeholder="Ingrese el nombre del usuario" data-parsley-required>
                                </div>

                                <div class="col-md-12">
                                    <label for="identidad_user" class="col-form-label focus-label">Identidad:</label>
                                    <input class="form-control" type="text" id="identidad_user" name="identidad_user" placeholder="Ingrese la identidad (opcional)">
                                </div>

                                <div class="col-md-12">
                                    <label for="telefono_user" class="col-form-label focus-label">Teléfono:</label>
                                    <input class="form-control" type="text" id="telefono_user" name="telefono_user" placeholder="Ingrese el teléfono (opcional)">
                                </div>

                                <div class="col-md-12">
                                    <label for="email_user" class="col-form-label focus-label">Correo de usuario:<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="email" id="email_user" name="email_user" placeholder="correo@ejemplo.com" data-parsley-type="email" data-parsley-required autocomplete="email">
                                </div>

                                <div class="col-md-12">
                                    <label for="pass_user" class="col-form-label focus-label">Contraseña:<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="password" id="pass_user" name="pass_user" minlength="8" placeholder="Mínimo 8 caracteres" data-parsley-required autocomplete="new-password">
                                    <small class="text-muted">Mínimo 8 caracteres</small>
                                </div>

                                <div class="col-md-12">
                                    <label for="confirmar_pass" class="col-form-label focus-label">Confirmar Contraseña:<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="password" id="confirmar_pass" name="confirmar_pass" minlength="8" placeholder="Repita la contraseña" data-parsley-required autocomplete="new-password">
                                    <small id="msg_pass_no_coincide" class="text-danger" style="display:none;">Las contraseñas no coinciden.</small>
                                </div>

                                <div class="col-md-12">
                                    <label for="rol_user" class="col-form-label focus-label">Seleccione rol:<span class="text-danger">*</span></label>
                                    <select class="form-control" required id="rol_user" name="rol_user" data-parsley-required>
                                        <option value="" selected>-- Seleccione un rol --</option>
                                    </select>
                                </div>

                            </div>
                        </form>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="guardarUsuario()">Guardar Usuario</button>
                    </div>
                </div>
            </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal_usuario_crear">
                                <i class="fa fa-plus"></i> Crear Nuevo Usuario
                            </button>
                        </div>

                        <div class="table-responsive" style="overflow-x:auto;">
                            <table id="tbl_usuariosListar" class="table table-striped table-bordered table-hover" style="width:100%">
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/js_proyecto/usuarios/usuarios.js') }}"></script>
    <script>
        // Validación en tiempo real del confirm password (modal crear usuario)
        document.addEventListener('DOMContentLoaded', function() {
            var confirmPass = document.getElementById('confirmar_pass');
            if (confirmPass) {
                confirmPass.addEventListener('input', function() {
                    var pass = document.getElementById('pass_user').value;
                    var msg  = document.getElementById('msg_pass_no_coincide');
                    msg.style.display = (this.value && this.value !== pass) ? 'block' : 'none';
                });
            }
        });
    </script>

@endpush

