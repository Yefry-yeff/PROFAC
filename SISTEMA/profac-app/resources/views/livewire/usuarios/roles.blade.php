<div>

@push('styles')
<style>
/* -- Variables PROFAC - colores del logo Valencia -- */
:root {
    --pf-grad:       linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
    --pf-grad-hover: linear-gradient(135deg, #e08e0b 0%, #c04e00 100%);
    --pf-orange:   #e67e22;
    --pf-green:    #27ae60;
    --pf-radius:   8px;
    --pf-shadow:   0 2px 8px rgba(0,0,0,.10);
}

/* -- Card principal -- */
.roles-card {
    border: 1px solid #e8d5bf;
    border-radius: var(--pf-radius);
    box-shadow: var(--pf-shadow);
    background: #fff;
    overflow: visible;
}
.roles-card-header {
    background: var(--pf-grad);
    padding: 12px 20px;
    border-radius: var(--pf-radius) var(--pf-radius) 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
}
.roles-card-header h5 {
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
.roles-card-body { padding: 16px 20px; }

/* -- Boton principal -- */
.btn-roles-new {
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
.btn-roles-new:hover {
    background: rgba(255,255,255,.30) !important;
    color: #fff !important;
}

/* -- Stats pills -- */
.roles-stats {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}
.roles-stat-pill {
    display: flex;
    align-items: center;
    gap: 7px;
    background: #fdf6ee;
    border: 1px solid #e8d5bf;
    border-radius: 20px;
    padding: 4px 14px 4px 10px;
    font-size: .78rem;
    color: #555;
    font-weight: 500;
}
.roles-stat-pill .stat-num { font-size: .9rem; font-weight: 700; color: var(--pf-orange); }
.roles-stat-pill.green .stat-num { color: #1a7a4e; }
.roles-stat-pill.green { background: #f0fdf4; border-color: #bbf7d0; }
.roles-stat-pill.red .stat-num   { color: #b91c1c; }
.roles-stat-pill.red   { background: #fef2f2; border-color: #fecaca; }

/* -- Tabla -- */
#tablaRoles { width: 100% !important; }
#tablaRoles thead th {
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
#tablaRoles tbody td { font-size: .83rem; vertical-align: middle; padding: 8px 10px; }
#tablaRoles tbody tr:hover { background: #fffcf5; }

/* -- Badges nivel -- */
.badge-nivel-1 { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-weight: 600; }
.badge-nivel-2 { background: #ede9fe; color: #4c1d95; border: 1px solid #c4b5fd; font-weight: 600; }
.badge-nivel-3 { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; font-weight: 600; }
.badge-nivel-4 { background: #dcfce7; color: #14532d; border: 1px solid #86efac; font-weight: 600; }
.badge-area { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-weight: 500; }
.badge-none { color: #adb5bd; font-size: .80rem; }

/* -- Dropdown de acciones -- */
.rol-dropdown { position: relative; }
.btn-rol-menu {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: #fff;
    border: 1.5px solid #e0cbb0;
    border-radius: 7px;
    color: #c0622a;
    font-size: .88rem;
    cursor: pointer;
    transition: background .15s, border-color .15s, box-shadow .15s;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
}
.btn-rol-menu:hover, .btn-rol-menu:focus {
    background: #fff8f0;
    border-color: #e67e22;
    box-shadow: 0 2px 6px rgba(230,126,34,.25);
    outline: none;
}
.rol-dropdown .dropdown-menu {
    min-width: 160px;
    border: 1px solid #f0e0cc;
    border-radius: 8px;
    padding: 4px 0;
    font-size: .83rem;
    box-shadow: 0 4px 16px rgba(0,0,0,.13) !important;
}
.rol-dropdown .dropdown-item { padding: 7px 14px; font-weight: 500; transition: background .12s; }
.rol-dropdown .dropdown-item:hover { background: #fff8f0; color: #c0622a; }
.rol-dropdown .dropdown-item i { opacity: .85; }

/* -- Modal -- */
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
.modal-header-roles {
    background: var(--pf-grad);
    color: #fff;
    border-radius: var(--pf-radius) var(--pf-radius) 0 0;
}
.modal-header-roles .modal-title { color: #fff; font-size: .95rem; }
.modal-header-roles .close       { color: #fff; opacity: .8; text-shadow: none; }
.modal-header-roles .close:hover { opacity: 1; }
.tabs-roles .nav-link { font-size: .82rem; font-weight: 600; color: #6c757d; border-radius: 6px 6px 0 0; }
.tabs-roles .nav-link.active { color: var(--pf-orange); border-color: #dee2e6 #dee2e6 #fff; }

/* -- DataTables controles responsivos -- */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter { margin-bottom: 8px; }
.dataTables_wrapper .dt-buttons { margin-bottom: 8px; }

/* -- Ancho completo: eliminar padding acumulado del tema INSPINIA -- */
/* El layout app.blade.php ya aplica .wrapper-content, el componente agrega otro.  */
/* Se anulan los paddings apilados SOLO en esta página (push de estilos) */
#page-wrapper {
    padding-left: 0 !important;
    padding-right: 0 !important;
}
.wrapper-content {
    padding-left: 0 !important;
    padding-right: 0 !important;
}
/* Quitar el margen negativo que Bootstrap agrega a .row */
.wrapper-content > .row {
    margin-left: 0 !important;
    margin-right: 0 !important;
}
.wrapper-content > .row > [class*="col-"] {
    padding-left: 0 !important;
    padding-right: 0 !important;
}
/* Márgenes internos del card */
.roles-card-body { padding: 16px 20px; }
.dataTables_wrapper { width: 100% !important; }
#tablaRoles { width: 100% !important; }

/* -- Responsive -- */
@media (max-width: 767px) {
    .roles-card-body { padding: 10px; }
    .roles-card-header { padding: 10px 12px; }
    #tablaRoles thead th:nth-child(7),
    #tablaRoles tbody td:nth-child(7),
    #tablaRoles thead th:nth-child(8),
    #tablaRoles tbody td:nth-child(8) { display: none; }
}
@media (max-width: 575px) {
    #tablaRoles thead th:nth-child(4),
    #tablaRoles tbody td:nth-child(4),
    #tablaRoles thead th:nth-child(6),
    #tablaRoles tbody td:nth-child(6) { display: none; }
    .modal-dialog { margin: .5rem; }
    .modal-dialog.modal-lg { max-width: calc(100vw - 1rem); }
    .wrapper-content { padding: 10px 8px !important; }
}
</style>
@endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-12">
            <h2><i class="fa fa-shield mr-2" style="color:#e67e22"></i>Gestión de Roles</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Roles</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="roles-card">

                    <div class="roles-card-header">
                        <h5><i class="fa fa-shield"></i> Roles del Sistema</h5>
                        <button type="button" class="btn btn-roles-new" onclick="abrirModalRol()">
                            <i class="fa fa-plus mr-1"></i> Nuevo Rol
                        </button>
                    </div>

                    <div class="roles-card-body">

                        <div class="roles-stats" id="rolesStats">
                            <div class="roles-stat-pill">
                                <i class="fa fa-shield" style="font-size:.78rem;color:var(--pf-orange)"></i>
                                <span>Total</span>
                                <span class="stat-num" id="statTotal">-</span>
                            </div>
                            <div class="roles-stat-pill green">
                                <i class="fa fa-check-circle" style="font-size:.78rem;color:#1a7a4e"></i>
                                <span>Activos</span>
                                <span class="stat-num" id="statActivos">-</span>
                            </div>
                            <div class="roles-stat-pill red">
                                <i class="fa fa-times-circle" style="font-size:.78rem;color:#b91c1c"></i>
                                <span>Inactivos</span>
                                <span class="stat-num" id="statInactivos">-</span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tablaRoles" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:50px">ID</th>
                                        <th>Nombre del Rol</th>
                                        <th style="width:140px">Nivel</th>
                                        <th style="width:130px">Área</th>
                                        <th style="width:90px" class="text-center">Estado</th>
                                        <th style="width:90px" class="text-center"># Usuarios</th>
                                        <th style="width:90px" class="text-center"># Permisos</th>
                                        <th style="width:110px" class="text-center">Creación</th>
                                        <th style="width:110px" class="text-center">Acciones</th>
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


    <!-- MODAL: Crear / Editar Rol -->
    <div class="modal fade" id="modalRol" tabindex="-1" role="dialog" aria-labelledby="tituloModalRol" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-roles">
                    <h5 class="modal-title" id="tituloModalRol">
                        <i class="fa fa-shield mr-2"></i>Nuevo Rol
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formRol">
                    <div class="modal-body pb-2">
                        <input type="hidden" id="rolId">

                        <p class="modal-section-label"><i class="fa fa-info-circle mr-1"></i>Información básica</p>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="rolNombre" class="font-weight-bold small">Nombre del Rol <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="rolNombre"
                                           placeholder="Ej: Administrador, Televendedor, Asesor Comercial…" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="rolEstado" class="font-weight-bold small">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm" id="rolEstado" required>
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado->id }}">{{ $estado->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <p class="modal-section-label mt-2"><i class="fa fa-sitemap mr-1"></i>Jerarquía organizacional <span class="text-muted font-weight-normal">(opcional)</span></p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rolNivel" class="font-weight-bold small">Nivel jerárquico</label>
                                    <select class="form-control form-control-sm" id="rolNivel">
                                        <option value="">— Sin nivel —</option>
                                        @foreach($niveles as $nivel)
                                            <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" style="font-size:.73rem">Gerente General, Jefe de Dpto., Supervisor, Colaborador</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rolArea" class="font-weight-bold small">Área / Departamento</label>
                                    <select class="form-control form-control-sm" id="rolArea">
                                        <option value="">— Sin área —</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" style="font-size:.73rem">Ventas, Administración, Logística, Tecnología…</small>
                                </div>
                            </div>
                        </div>

                        <div id="seccionTabs" style="display:none;">
                            <p class="modal-section-label mt-2"><i class="fa fa-cogs mr-1"></i>Configuración del rol</p>

                            <ul class="nav nav-tabs tabs-roles" id="tabsRol" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-usuarios-link" data-toggle="tab" href="#tab-usuarios" role="tab">
                                        <i class="fa fa-users mr-1"></i>Usuarios
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-permisos-link" data-toggle="tab" href="#tab-permisos" role="tab">
                                        <i class="fa fa-lock mr-1"></i>Permisos
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content border border-top-0 rounded-bottom p-3" id="tabsRolContent" style="background:#fafafa">

                                <div class="tab-pane fade show active" id="tab-usuarios" role="tabpanel">
                                    <div class="input-group input-group-sm mb-2">
                                        <select class="form-control form-control-sm" id="selectUsuarioAgregar">
                                            <option value="">Seleccione un usuario para agregar…</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary btn-sm"
                                                    onclick="event.stopPropagation(); event.preventDefault(); agregarUsuarioAlRol(event); return false;">
                                                <i class="fa fa-plus"></i> Agregar
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mb-2" style="font-size:.73rem">
                                        <i class="fa fa-info-circle"></i> Si el usuario ya tiene otro rol, se actualizará al guardar.
                                    </small>
                                    <div class="table-responsive" style="max-height:260px;overflow-y:auto">
                                        <table class="table table-sm table-bordered table-hover mb-0" id="tablaUsuariosRol">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>ID</th><th>Nombre</th><th>Email</th><th>Rol Anterior</th><th style="width:60px">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody id="listaUsuariosRol">
                                                <tr><td colspan="5" class="text-center text-muted py-3">
                                                    <i class="fa fa-users mr-1"></i>Sin usuarios asignados
                                                </td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="tab-permisos" role="tabpanel">
                                    <div class="input-group input-group-sm mb-2">
                                        <select class="form-control form-control-sm" id="selectSubmenuAgregar">
                                            <option value="">Seleccione un submenú para agregar…</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary btn-sm"
                                                    onclick="event.stopPropagation(); event.preventDefault(); agregarPermisoAlRol(event); return false;">
                                                <i class="fa fa-plus"></i> Agregar
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mb-2" style="font-size:.73rem">
                                        <i class="fa fa-info-circle"></i> Agrega o quita permisos de acceso a los submenús del sistema.
                                    </small>
                                    <div class="table-responsive" style="max-height:260px;overflow-y:auto">
                                        <table class="table table-sm table-bordered table-hover mb-0" id="tablaPermisosRol">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>ID</th><th>Menú</th><th>Submenú</th><th>Ruta</th><th style="width:60px">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody id="listaPermisosRol">
                                                <tr><td colspan="5" class="text-center text-muted py-3">
                                                    <i class="fa fa-lock mr-1"></i>Sin permisos asignados
                                                </td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="fa fa-times mr-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa fa-save mr-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Modal Confirmar Eliminar -->
    <div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white py-2">
                    <h5 class="modal-title small font-weight-bold">
                        <i class="fa fa-exclamation-triangle mr-1"></i>Confirmar eliminación
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body py-3">
                    <p class="mb-1">¿Seguro que desea eliminar este rol?</p>
                    <p class="text-danger small mb-0"><strong>Esta acción no se puede deshacer.</strong></p>
                    <input type="hidden" id="rolEliminarId">
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmarEliminarRol()">
                        <i class="fa fa-trash mr-1"></i>Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Quitar Usuario -->
    <div class="modal fade" id="modalConfirmarQuitarUsuario" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning py-2">
                    <h5 class="modal-title small font-weight-bold">
                        <i class="fa fa-exclamation-triangle mr-1"></i>Confirmar acción
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body py-3">
                    <p class="mb-1">¿Quitar este usuario del rol?</p>
                    <p class="text-muted small mb-0"><i class="fa fa-info-circle mr-1"></i>El cambio se aplica al guardar.</p>
                    <input type="hidden" id="usuarioQuitarId">
                    <input type="hidden" id="usuarioQuitarRolId">
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning btn-sm"
                            onclick="event.stopPropagation(); confirmarQuitarUsuarioDelRol(); return false;">
                        <i class="fa fa-check mr-1"></i>Sí, quitar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Quitar Permiso -->
    <div class="modal fade" id="modalConfirmarQuitarPermiso" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning py-2">
                    <h5 class="modal-title small font-weight-bold">
                        <i class="fa fa-exclamation-triangle mr-1"></i>Confirmar acción
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body py-3">
                    <p class="mb-1">¿Quitar este permiso del rol?</p>
                    <p class="text-muted small mb-0"><i class="fa fa-info-circle mr-1"></i>El cambio se aplica al guardar.</p>
                    <input type="hidden" id="permisoQuitarId">
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning btn-sm"
                            onclick="event.stopPropagation(); confirmarQuitarPermisoDelRol(); return false;">
                        <i class="fa fa-check mr-1"></i>Sí, quitar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Spinner Loading -->
    <div class="modal fade" id="modalSpinnerLoading" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status" style="width:2.5rem;height:2.5rem">
                        <span class="sr-only">Cargando…</span>
                    </div>
                    <p class="mb-0 text-muted small">Procesando, por favor espere…</p>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <link href="{{ asset('css/plugins/dataTables/datatables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('js/plugins/dataTables/datatables.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/js_proyecto/roles/roles.js') }}"></script>
@endpush
