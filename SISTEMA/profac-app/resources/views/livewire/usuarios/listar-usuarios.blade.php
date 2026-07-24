@push('styles')
<style>
/* -- Variables PROFAC -- */
:root {
    --pf-grad:       linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
    --pf-grad-hover: linear-gradient(135deg, #e08e0b 0%, #c04e00 100%);
    --pf-orange:   #e67e22;
    --pf-radius:   8px;
    --pf-shadow:   0 2px 8px rgba(0,0,0,.10);
}

/* -- Card usuarios -- */
.usr-card {
    border: 1px solid #e8d5bf;
    border-radius: var(--pf-radius);
    box-shadow: var(--pf-shadow);
    background: #fff;
    overflow: visible;
}
.usr-card-header {
    background: var(--pf-grad);
    padding: 12px 20px;
    border-radius: var(--pf-radius) var(--pf-radius) 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
}
.usr-card-header h5 {
    margin: 0;
    color: #fff;
    font-size: .85rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 8px;
}
.usr-card-body { padding: 16px 20px; }

/* -- Botón en header -- */
.btn-usr-new {
    background: rgba(255,255,255,.18) !important;
    color: #fff !important;
    border: 1.5px solid rgba(255,255,255,.5) !important;
    border-radius: 5px !important;
    font-weight: 600 !important;
    font-size: .78rem;
    padding: 5px 14px;
    transition: background .18s;
    white-space: nowrap;
}
.btn-usr-new:hover {
    background: rgba(255,255,255,.30) !important;
    color: #fff !important;
}

/* -- Tabla -- */
#tbl_usuariosListar thead th {
    background: #fdf4e7;
    color: #7d3f00;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    border-bottom: 2px solid #f2d49a;
    white-space: nowrap;
    padding: 8px 10px;
    vertical-align: middle;
}
#tbl_usuariosListar tbody td { font-size: .83rem; vertical-align: middle; padding: 8px 10px; }
#tbl_usuariosListar tbody tr:hover { background: #fffcf5; }

/* -- Modal header gradiente -- */
.modal-header-usr {
    background: var(--pf-grad);
    color: #fff;
    border-radius: var(--pf-radius) var(--pf-radius) 0 0;
}
.modal-header-usr .modal-title { color: #fff; font-size: .95rem; }
.modal-header-usr .close       { color: #fff; opacity: .8; text-shadow: none; }
.modal-header-usr .close:hover { opacity: 1; }

/* -- Divisores de sección en modal -- */
.modal-section-label {
    font-size: .70rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #6c757d;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 4px;
    margin-bottom: 12px;
    margin-top: 4px;
}

/* -- Focus inputs naranja -- */
.modal-content .form-control:focus {
    border-color: #e67e22;
    box-shadow: 0 0 0 .18rem rgba(230,126,34,.2);
}

/* -- Roles adicionales (multi-rol) -- */
.usr-roladd-buscar { display: flex; gap: 8px; align-items: flex-start; }
.usr-roladd-buscar .select2-container { flex: 1 1 auto; }
.btn-usr-roladd {
    background: var(--pf-grad) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 5px !important;
    font-weight: 600 !important;
    font-size: .78rem;
    padding: 6px 14px;
    white-space: nowrap;
}
.btn-usr-roladd:hover { filter: brightness(1.05); color: #fff !important; }
.usr-chip-lista { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; min-height: 30px; }
.usr-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fdf4e7;
    color: #7d3f00;
    border: 1px solid #f2d49a;
    border-radius: 20px;
    padding: 4px 6px 4px 12px;
    font-size: .78rem;
    font-weight: 600;
}
.usr-chip .usr-chip-remove {
    cursor: pointer;
    color: #b45309;
    background: rgba(180,83,9,.12);
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .68rem;
    transition: background .15s;
}
.usr-chip .usr-chip-remove:hover { background: rgba(180,83,9,.28); }
.usr-chip-empty { color: #9ca3af; font-size: .78rem; font-style: italic; }

/* -- Responsive -- */
@media (max-width: 575px) {
    .modal-dialog { margin: .5rem; }
    .modal-dialog.modal-lg { max-width: calc(100vw - 1rem); }
    .usr-card-body { padding: 10px; }
}
</style>
@endpush

<div>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2><i class="fa fa-users mr-2" style="color:#e67e22"></i>Gestión de Usuarios</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Usuarios</strong></li>
            </ol>
        </div>
    </div>

    <!-- Modal para editar usuario -->
    <div class="modal fade" id="modal_usuario_rol" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header modal-header-usr">
                        <h5 class="modal-title" id="exampleModalLabel">
                            <i class="fa fa-pencil mr-2"></i>Editar Usuario
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body pb-2">
                        <form id="userEditForm" name="userEditForm" data-parsley-validate>
                            <input required type="hidden" id="id_usuario" name="id_usuario">

                            <p class="modal-section-label"><i class="fa fa-id-card mr-1"></i>Información personal</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre_usuario" class="font-weight-bold small">Nombre Usuario <span class="text-danger">*</span></label>
                                        <input class="form-control form-control-sm" required type="text" id="nombre_usuario" name="nombre_usuario" data-parsley-required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small">Identidad</label>
                                        <input class="form-control form-control-sm" type="text" id="identidad_usuario" name="identidad_usuario" placeholder="Número de identidad (opcional)">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="telefono_usuario" class="font-weight-bold small">Teléfono</label>
                                        <input class="form-control form-control-sm" type="text" id="telefono_usuario" name="telefono_usuario" placeholder="Teléfono (opcional)">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small">Fecha de Nacimiento</label>
                                        <input class="form-control form-control-sm" type="date" id="fenacimiento_usuario" name="fenacimiento_usuario">
                                    </div>
                                </div>
                            </div>

                            <p class="modal-section-label mt-2"><i class="fa fa-lock mr-1"></i>Acceso al sistema</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small">Correo de usuario <span class="text-danger">*</span></label>
                                        <input class="form-control form-control-sm" required type="email" id="correo_usuario" name="correo_usuario" data-parsley-required autocomplete="username" placeholder="correo@ejemplo.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="seleccionarRol" class="font-weight-bold small">Rol <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm" required id="seleccionarRol" name="seleccionarRol" data-parsley-required>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <p class="modal-section-label mt-2">
                            <i class="fa fa-user-tag mr-1"></i>Roles adicionales
                            <span class="text-muted font-weight-normal text-lowercase">(opcional, además del rol principal — se guardan al instante)</span>
                        </p>
                        <input type="hidden" id="usr_roladd_usuario_id">
                        <div class="usr-roladd-buscar">
                            <select class="form-control form-control-sm" id="usr_roladd_select" style="width:100%"></select>
                            <button type="button" class="btn-usr-roladd" onclick="agregarRolAdicionalUsuario()">
                                <i class="fa fa-plus mr-1"></i>Agregar
                            </button>
                        </div>
                        <div id="usr_roladd_lista" class="usr-chip-lista"></div>
                    </div>

                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="fa fa-times mr-1"></i>Cerrar
                        </button>
                        <button type="submit" form="userEditForm" class="btn btn-primary btn-sm">
                            <i class="fa fa-save mr-1"></i>Actualizar
                        </button>
                    </div>
                </div>
            </div>
    </div>

    <!-- Modal Cambiar Contraseña -->
    <div class="modal fade" id="modal_cambiar_contrasena" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-usr">
                    <h5 class="modal-title"><i class="fa fa-key mr-2"></i>Cambiar Contraseña</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-2">
                    <form id="formCambiarContrasena" name="formCambiarContrasena">
                        <input type="hidden" id="id_usuario_pwd" name="id_usuario_pwd">
                        <div class="form-group">
                            <label class="font-weight-bold small">Nueva Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-sm" type="password" id="nueva_contrasena" name="nueva_contrasena"
                                    minlength="8" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" tabindex="-1"
                                        onclick="togglePwd('nueva_contrasena', this)" title="Mostrar/ocultar">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted" style="font-size:.73rem">Mínimo 8 caracteres</small>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-sm" type="password" id="confirmar_contrasena" name="confirmar_contrasena"
                                    minlength="8" placeholder="Confirme la contraseña" autocomplete="new-password">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" tabindex="-1"
                                        onclick="togglePwd('confirmar_contrasena', this)" title="Mostrar/ocultar">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <small id="msg_pwd_no_coincide" class="text-danger" style="display:none;font-size:.73rem">
                                <i class="fa fa-exclamation-circle"></i> Las contraseñas no coinciden.
                            </small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Cerrar
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="guardarContrasena()">
                        <i class="fa fa-key mr-1"></i>Guardar Contraseña
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear nuevo usuario -->
    <div class="modal fade" id="modal_usuario_crear" tabindex="-1" role="dialog"
            aria-labelledby="tituloModalUsuario" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-usr">
                    <h5 class="modal-title" id="tituloModalUsuario">
                        <i class="fa fa-user-plus mr-2"></i>Registrar Nuevo Usuario
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form id="userAddForm" name="userAddForm" data-parsley-validate>
                    <div class="modal-body pb-2">

                        <p class="modal-section-label"><i class="fa fa-id-card mr-1"></i>Información personal</p>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="nombre_usuario_nuevo" class="font-weight-bold small">Nombre completo <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-sm" required type="text"
                                        id="nombre_usuario_nuevo" name="nombre_usuario"
                                        placeholder="Ingrese el nombre del usuario" data-parsley-required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="identidad_user" class="font-weight-bold small">Identidad</label>
                                    <input class="form-control form-control-sm" type="text"
                                        id="identidad_user" name="identidad_user"
                                        placeholder="N° identidad (opcional)">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="telefono_user" class="font-weight-bold small">Teléfono</label>
                                    <input class="form-control form-control-sm" type="text"
                                        id="telefono_user" name="telefono_user"
                                        placeholder="Teléfono (opcional)">
                                </div>
                            </div>
                        </div>

                        <p class="modal-section-label mt-2"><i class="fa fa-lock mr-1"></i>Acceso al sistema</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email_user" class="font-weight-bold small">Correo electrónico <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-sm" required type="email"
                                        id="email_user" name="email_user"
                                        placeholder="correo@ejemplo.com"
                                        data-parsley-type="email" data-parsley-required autocomplete="email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rol_user" class="font-weight-bold small">Rol <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm" required
                                        id="rol_user" name="rol_user" data-parsley-required>
                                        <option value="">— Seleccione un rol —</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pass_user" class="font-weight-bold small">Contraseña <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <input class="form-control form-control-sm" required type="password"
                                            id="pass_user" name="pass_user"
                                            minlength="8" placeholder="Mínimo 8 caracteres"
                                            data-parsley-required autocomplete="new-password">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" tabindex="-1"
                                                onclick="togglePwd('pass_user', this)" title="Mostrar/ocultar">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted" style="font-size:.73rem">Mínimo 8 caracteres</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirmar_pass" class="font-weight-bold small">Confirmar contraseña <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <input class="form-control form-control-sm" required type="password"
                                            id="confirmar_pass" name="confirmar_pass"
                                            minlength="8" placeholder="Repita la contraseña"
                                            data-parsley-required autocomplete="new-password">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" tabindex="-1"
                                                onclick="togglePwd('confirmar_pass', this)" title="Mostrar/ocultar">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small id="msg_pass_no_coincide" class="text-danger" style="display:none;font-size:.73rem">
                                        <i class="fa fa-exclamation-circle"></i> Las contraseñas no coinciden.
                                    </small>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="fa fa-times mr-1"></i>Cancelar
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="guardarUsuario()">
                            <i class="fa fa-save mr-1"></i>Guardar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="usr-card">

                    <div class="usr-card-header">
                        <h5><i class="fa fa-users"></i> Usuarios del Sistema</h5>
                        <button type="button" class="btn btn-usr-new" data-toggle="modal" data-target="#modal_usuario_crear">
                            <i class="fa fa-user-plus mr-1"></i> Nuevo Usuario
                        </button>
                    </div>

                    <div class="usr-card-body">
                        <div class="table-responsive">
                            <table id="tbl_usuariosListar" class="table table-bordered table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Teléfono</th>
                                        <th>Correo</th>
                                        <th>Identidad</th>
                                        <th>F. Nacimiento</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Fecha Ingreso</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
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
        // Mostrar/ocultar contraseña
        function togglePwd(fieldId, btn) {
            var input = document.getElementById(fieldId);
            var icon  = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

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

