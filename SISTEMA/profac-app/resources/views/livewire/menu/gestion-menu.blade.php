<div>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>Gestión de Menús</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong>Gestión de Menús</strong>
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

        {{-- Pestañas principales --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <ul class="nav nav-tabs" id="menuTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-menus" data-toggle="tab" href="#panel-menus" role="tab">
                                    <i class="fa fa-bars"></i> Menús Principales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-submenus" data-toggle="tab" href="#panel-submenus" role="tab">
                                    <i class="fa fa-list"></i> Submenús
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content pt-3" id="menuTabsContent">
                            {{-- ==================== PESTAÑA 1: MENÚS PRINCIPALES ==================== --}}
                            <div class="tab-pane fade show active" id="panel-menus" role="tabpanel">
                                <div class="mb-3 text-right">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalMenu()">
                                        <i class="fa fa-plus"></i> Nuevo Menú
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="tablaMenus">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Icono</th>
                                                <th>Nombre</th>
                                                <th>Orden</th>
                                                <th>Estado</th>
                                                <th># Submenus</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($menus as $menu)
                                            <tr>
                                                <td>{{ $menu->id }}</td>
                                                <td><i class="{{ $menu->icon }}"></i> {{ $menu->icon }}</td>
                                                <td>{{ $menu->nombre_menu }}</td>
                                                <td>{{ $menu->orden }}</td>
                                                <td>
                                                    @if($menu->estado_id == 1)
                                                        <span class="badge badge-success">Activo</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactivo</span>
                                                    @endif
                                                </td>
                                                <td>{{ $menu->submenus->count() }}</td>
                                                <td>
                                                    <button class="btn btn-warning btn-xs" onclick="editarMenu({{ $menu->id }})" title="Editar">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-{{ $menu->estado_id == 1 ? 'danger' : 'success' }} btn-xs" 
                                                            wire:click="cambiarEstadoMenu({{ $menu->id }})" title="{{ $menu->estado_id == 1 ? 'Inactivar' : 'Activar' }}">
                                                        <i class="fa fa-{{ $menu->estado_id == 1 ? 'times' : 'check' }}"></i>
                                                    </button>
                                                    @if($menu->submenus->count() == 0)
                                                    <button class="btn btn-danger btn-xs" wire:click="eliminarMenu({{ $menu->id }})" title="Eliminar">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- ==================== PESTAÑA 2: SUBMENÚS ==================== --}}
                            <div class="tab-pane fade" id="panel-submenus" role="tabpanel">
                                <div class="mb-3 text-right">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalSubmenu()">
                                        <i class="fa fa-plus"></i> Nuevo Submenu
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="tablaSubmenus">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Menú</th>
                                                <th>Icono</th>
                                                <th>Nombre</th>
                                                <th>URL</th>
                                                <th>Orden</th>
                                                <th>Estado</th>
                                                <th>Roles</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($submenus as $submenu)
                                            <tr>
                                                <td>{{ $submenu->id }}</td>
                                                <td>{{ $submenu->menu->nombre_menu }}</td>
                                                <td>
                                                    @if($submenu->icono)
                                                        <i class="{{ $submenu->icono }}"></i>
                                                    @endif
                                                </td>
                                                <td>{{ $submenu->nombre }}</td>
                                                <td><code>{{ $submenu->url }}</code></td>
                                                <td>{{ $submenu->orden }}</td>
                                                <td>
                                                    @if($submenu->estado_id == 1)
                                                        <span class="badge badge-success">Activo</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactivo</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @foreach($submenu->roles as $rol)
                                                        <span class="badge badge-info">{{ $rol->nombre }}</span>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    <button class="btn btn-warning btn-xs" onclick="editarSubmenu({{ $submenu->id }})" title="Editar">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-{{ $submenu->estado_id == 1 ? 'danger' : 'success' }} btn-xs" 
                                                            wire:click="cambiarEstadoSubmenu({{ $submenu->id }})" title="{{ $submenu->estado_id == 1 ? 'Inactivar' : 'Activar' }}">
                                                        <i class="fa fa-{{ $submenu->estado_id == 1 ? 'times' : 'check' }}"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-xs" wire:click="eliminarSubmenu({{ $submenu->id }})" title="Eliminar">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ==================== Modal Menú (Crear / Editar) - Centrado ==================== -->
    <div class="modal fade" id="modalMenu" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalMenu">Nuevo Menú</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formMenu">
                    <div class="modal-body">
                        <input type="hidden" id="menuId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombre del Menú *</label>
                                    <input type="text" class="form-control" id="menuNombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Icono (Font Awesome) *</label>
                                    <input type="text" class="form-control" id="menuIcono" placeholder="fa fa-home" required>
                                    <small class="form-text text-muted">
                                        Ej: fa fa-home, fa fa-users. 
                                        <a href="https://fontawesome.com/v4/icons/" target="_blank">Ver iconos disponibles</a>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Orden *</label>
                                    <input type="number" class="form-control" id="menuOrden" required min="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Estado *</label>
                                    <select class="form-control" id="menuEstado" required>
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado->id }}">{{ $estado->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Sección de Submenus visible solo al editar --}}
                        <div id="seccionSubmenusMenu" style="display:none;">
                            <hr>
                            <h5>Submenús de este menú</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>URL</th>
                                            <th>Orden</th>
                                            <th>Estado</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodySubmenusDelMenu">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== Modal Submenu (Crear / Editar) con 2 pestañas - Centrado ==================== -->
    <div class="modal fade" id="modalSubmenu" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalSubmenu">Nuevo Submenu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formSubmenu">
                    <div class="modal-body">
                        <input type="hidden" id="submenuId">

                        {{-- Pestañas internas del modal --}}
                        <ul class="nav nav-tabs" id="submenuModalTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-datos-generales" data-toggle="tab" href="#panel-datos-generales" role="tab">
                                    Datos Generales y Acceso
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-datos-tecnicos" data-toggle="tab" href="#panel-datos-tecnicos" role="tab">
                                    Datos Técnicos
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content pt-3" id="submenuModalTabsContent">
                            {{-- ---- Pestaña: Datos Generales y Acceso ---- --}}
                            <div class="tab-pane fade show active" id="panel-datos-generales" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nombre del Submenu *</label>
                                            <input type="text" class="form-control" id="submenuNombre" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Menú Principal *</label>
                                            <select class="form-control" id="submenuMenuId" required>
                                                <option value="">Seleccione...</option>
                                                @foreach($menus->where('estado_id', 1) as $menu)
                                                    <option value="{{ $menu->id }}">{{ $menu->nombre_menu }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Orden *</label>
                                            <input type="number" class="form-control" id="submenuOrden" required min="1">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Estado *</label>
                                            <select class="form-control" id="submenuEstado" required>
                                                @foreach($estados as $estado)
                                                    <option value="{{ $estado->id }}">{{ $estado->descripcion }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Roles con Acceso *</label>
                                    <div class="border p-3" style="max-height: 200px; overflow-y: auto;">
                                        @foreach($roles as $rol)
                                            <div class="form-check">
                                                <input class="form-check-input rol-checkbox" type="checkbox" 
                                                       value="{{ $rol->id }}" id="rol{{ $rol->id }}">
                                                <label class="form-check-label" for="rol{{ $rol->id }}">
                                                    {{ $rol->nombre }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="form-text text-muted">Seleccione los roles que pueden ver este submenu</small>
                                </div>
                            </div>

                            {{-- ---- Pestaña: Datos Técnicos ---- --}}
                            <div class="tab-pane fade" id="panel-datos-tecnicos" role="tabpanel">
                                <div class="form-group">
                                    <label>Icono (Font Awesome)</label>
                                    <input type="text" class="form-control" id="submenuIcono" placeholder="fa fa-list">
                                    <small class="form-text text-muted">
                                        Ej: fa fa-home, fa fa-users, fa fa-cog. 
                                        <a href="https://fontawesome.com/v4/icons/" target="_blank">Ver iconos disponibles</a>
                                    </small>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="generarArchivos">
                                        <label class="form-check-label" for="generarArchivos">
                                            <strong>Generar archivos automáticamente</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fa fa-info-circle"></i> Crea automáticamente: Componente Livewire, Vista Blade y la Ruta en web.php
                                    </small>
                                </div>

                                <div class="form-group" id="campoUrlRuta" style="display:none;">
                                    <label>URL/Ruta *</label>
                                    <input type="text" class="form-control" id="submenuUrl" placeholder="usuarios/listar">
                                    <small class="form-text text-muted">Sin "/" al inicio. Ej: clientes/listar</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/js_proyecto/menu/gestion-menu.js') }}"></script>
@endpush
