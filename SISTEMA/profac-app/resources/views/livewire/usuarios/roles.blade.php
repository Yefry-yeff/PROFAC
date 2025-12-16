<div>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
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

        <!-- Contenido Principal -->
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5><i class="fa fa-users"></i> Lista de Roles</h5>
                        <div class="ibox-tools">
                            <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalRol()">
                                <i class="fa fa-plus"></i> Nuevo Rol
                            </button>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="tablaRoles">
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
                                    {{-- Los datos se cargarán vía DataTables --}}
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
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalRol">Nuevo Rol</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formRol" data-parsley-validate>
                    <div class="modal-body">
                        <input type="hidden" id="rolId">
                        
                        <div class="form-group">
                            <label for="rolNombre">Nombre del Rol <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="rolNombre" 
                                   placeholder="Ej: Administrador, Vendedor, etc."
                                   required
                                   data-parsley-required-message="El nombre del rol es obligatorio"
                                   data-parsley-maxlength="255">
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle"></i> Ingrese un nombre descriptivo para el rol
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="rolEstado">Estado <span class="text-danger">*</span></label>
                            <select class="form-control" id="rolEstado" required>
                                @foreach($estados as $estado)
                                    <option value="{{ $estado->id }}">{{ $estado->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <i class="fa fa-info-circle"></i> 
                            <strong>Nota:</strong> Los permisos del rol se gestionan en el módulo de "Gestión de Menús".
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

    <!-- Modal de Confirmación para Eliminar -->
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
    
    <!-- Parsley Validation -->
    <link href="{{ asset('css/plugins/parsley/parsley.css') }}" rel="stylesheet">
    <script src="{{ asset('js/plugins/parsley/parsley.min.js') }}"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('js/js_proyecto/roles/roles.js') }}"></script>
@endpush
