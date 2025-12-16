<div>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2>Gestión de Roles</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong>Roles</strong>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-lg-12 text-right">
                <button type="button" class="btn btn-primary" onclick="abrirModalRol()">
                    <i class="fa fa-plus"></i> Nuevo Rol
                </button>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tablaRoles" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre del Rol</th>
                                        <th>Estado</th>
                                        <th># Usuarios</th>
                                        <th># Permisos</th>
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
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

    <!-- Modal para Crear/Editar Rol -->
    <div class="modal fade" id="modalRol" tabindex="-1" role="dialog" aria-labelledby="modalRolLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalRol">Nuevo Rol</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formRol">
                    <div class="modal-body">
                        <input type="hidden" id="rolId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rolNombre">Nombre del Rol <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="rolNombre" 
                                           placeholder="Ej: Administrador, Vendedor, etc."
                                           required>
                                    <small class="form-text text-muted">
                                        <i class="fa fa-info-circle"></i> Ingrese un nombre descriptivo para el rol
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rolEstado">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control" id="rolEstado" required>
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado->id }}">{{ $estado->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Pestañas de Usuarios y Permisos (solo visible al editar) -->
                        <div id="seccionTabs" style="display:none;">
                            <hr>
                            
                            <ul class="nav nav-tabs" id="tabsRol" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-usuarios-link" data-toggle="tab" href="#tab-usuarios" role="tab">
                                        <i class="fa fa-users"></i> Usuarios
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-permisos-link" data-toggle="tab" href="#tab-permisos" role="tab">
                                        <i class="fa fa-lock"></i> Permisos
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content mt-3" id="tabsRolContent">
                                <!-- Tab de Usuarios -->
                                <div class="tab-pane fade show active" id="tab-usuarios" role="tabpanel">
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="input-group">
                                                <select class="form-control" id="selectUsuarioAgregar">
                                                    <option value="">Seleccione un usuario para agregar...</option>
                                                </select>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-primary" onclick="event.stopPropagation(); event.preventDefault(); agregarUsuarioAlRol(event); return false;">
                                                        <i class="fa fa-plus"></i> Agregar
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fa fa-info-circle"></i> Si el usuario ya tiene otro rol, se actualizará automáticamente
                                            </small>
                                        </div>
                                    </div>

                                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered table-hover" id="tablaUsuariosRol">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nombre</th>
                                                    <th>Email</th>
                                                    <th>Rol Anterior</th>
                                                    <th width="80px">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody id="listaUsuariosRol">
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">
                                                        <i class="fa fa-info-circle"></i> No hay usuarios asignados
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Tab de Permisos -->
                                <div class="tab-pane fade" id="tab-permisos" role="tabpanel">
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="input-group">
                                                <select class="form-control" id="selectSubmenuAgregar">
                                                    <option value="">Seleccione un submenu para agregar...</option>
                                                </select>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-primary" onclick="event.stopPropagation(); event.preventDefault(); agregarPermisoAlRol(event); return false;">
                                                        <i class="fa fa-plus"></i> Agregar Permiso
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fa fa-info-circle"></i> Agregue o quite permisos de acceso a los submenús
                                            </small>
                                        </div>
                                    </div>

                                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered table-hover" id="tablaPermisosRol">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Menú</th>
                                                    <th>Submenú</th>
                                                    <th>Ruta</th>
                                                    <th width="80px">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody id="listaPermisosRol">
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">
                                                        <i class="fa fa-info-circle"></i> No hay permisos asignados
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación para Eliminar Rol -->
    <div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-exclamation-triangle"></i> Confirmar Eliminación
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar este rol?</p>
                    <p class="text-danger">
                        <strong>Esta acción no se puede deshacer.</strong>
                    </p>
                    <input type="hidden" id="rolEliminarId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="confirmarEliminarRol()">
                        <i class="fa fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación para Quitar Usuario -->
    <div class="modal fade" id="modalConfirmarQuitarUsuario" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fa fa-exclamation-triangle"></i> Confirmar Acción
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de quitar este usuario del rol?</p>
                    <p class="text-muted mb-0">
                        <small><i class="fa fa-info-circle"></i> El cambio se aplicará cuando presione "Guardar" en el formulario principal.</small>
                    </p>
                    <input type="hidden" id="usuarioQuitarId">
                    <input type="hidden" id="usuarioQuitarRolId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" onclick="event.stopPropagation(); confirmarQuitarUsuarioDelRol(); return false;">
                        <i class="fa fa-check"></i> Sí, quitar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación para Quitar Permiso -->
    <div class="modal fade" id="modalConfirmarQuitarPermiso" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fa fa-exclamation-triangle"></i> Confirmar Acción
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de quitar este permiso del rol?</p>
                    <p class="text-muted mb-0">
                        <small><i class="fa fa-info-circle"></i> El cambio se aplicará cuando presione "Guardar" en el formulario principal.</small>
                    </p>
                    <input type="hidden" id="permisoQuitarId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" onclick="event.stopPropagation(); confirmarQuitarPermisoDelRol(); return false;">
                        <i class="fa fa-check"></i> Sí, quitar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Spinner Loading -->
    <div class="modal fade" id="modalSpinnerLoading" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-3">Procesando...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <!-- DataTables -->
    <link href="{{ asset('css/plugins/dataTables/datatables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('js/plugins/dataTables/datatables.min.js') }}"></script>
    
    {{-- Parsley Validation - Comentado porque no existe en el proyecto --}}
    {{-- <link href="{{ asset('css/plugins/parsley/parsley.css') }}" rel="stylesheet"> --}}
    {{-- <script src="{{ asset('js/plugins/parsley/parsley.min.js') }}"></script> --}}
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('js/js_proyecto/roles/roles.js') }}"></script>
@endpush
