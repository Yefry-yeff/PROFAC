@push('styles')
<style>
/* -- Variables PROFAC -- */
:root {
    --pf-grad:     linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
    --pf-orange:   #e67e22;
    --pf-radius:   8px;
    --pf-shadow:   0 2px 8px rgba(0,0,0,.10);
}

/* -- Card principal -- */
.menu-card {
    border: 1px solid #e8d5bf;
    border-radius: var(--pf-radius);
    box-shadow: var(--pf-shadow);
    background: #fff;
    overflow: visible;
}
.menu-card-header {
    background: var(--pf-grad);
    padding: 12px 20px;
    border-radius: var(--pf-radius) var(--pf-radius) 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
}
.menu-card-header h5 {
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
.menu-card-body { padding: 16px 20px; }

/* -- Botón en header -- */
.btn-menu-new {
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
.btn-menu-new:hover { background: rgba(255,255,255,.30) !important; color: #fff !important; }

/* -- Tabs -- */
.tabs-menu .nav-link {
    font-size: .82rem;
    font-weight: 600;
    color: #6c757d;
    border-radius: 6px 6px 0 0;
    padding: 7px 16px;
}
.tabs-menu .nav-link.active { color: var(--pf-orange); border-color: #dee2e6 #dee2e6 #fff; }
.tabs-menu .nav-link:hover  { color: var(--pf-orange); }

/* -- Tablas -- */
#tablaMenus thead th,
#tablaSubmenus thead th {
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
#tablaMenus tbody td,
#tablaSubmenus tbody td { font-size: .83rem; vertical-align: middle; padding: 7px 10px; }
#tablaMenus tbody tr:hover,
#tablaSubmenus tbody tr:hover { background: #fffcf5; }

/* -- Modal header gradiente -- */
.modal-header-menu {
    background: var(--pf-grad);
    color: #fff;
    border-radius: var(--pf-radius) var(--pf-radius) 0 0;
}
.modal-header-menu .modal-title { color: #fff; font-size: .95rem; }
.modal-header-menu .close       { color: #fff; opacity: .8; text-shadow: none; }
.modal-header-menu .close:hover { opacity: 1; }

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

/* -- Roles checklist -- */
.roles-checklist {
    background: #fdfaf5;
    border: 1px solid #e8d5bf !important;
    border-radius: 6px;
    max-height: 180px;
    overflow-y: auto;
    padding: 10px 14px !important;
}
.roles-checklist .form-check-label { font-size: .83rem; }

/* -- Tabla submenús del menú (dentro del modal) -- */
#tbodySubmenusDelMenu td { font-size: .82rem; vertical-align: middle; }

@media (max-width: 575px) {
    .modal-dialog.modal-lg { max-width: calc(100vw - 1rem); }
    .menu-card-body { padding: 10px; }
}
</style>
@endpush

<div>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-10">
            <h2><i class="fa fa-bars mr-2" style="color:#e67e22"></i>Gestión de Menús</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Gestión de Menús</strong></li>
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

        {{-- Card principal con pestañas --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="menu-card">

                    <div class="menu-card-header">
                        <h5><i class="fa fa-bars"></i> Gestión de Menús</h5>
                        <div class="d-flex" style="gap:8px">
                            <button type="button" class="btn btn-menu-new" id="btnNuevoMenu" onclick="abrirModalMenu()">
                                <i class="fa fa-plus mr-1"></i> Nuevo Menú
                            </button>
                            <button type="button" class="btn btn-menu-new" id="btnNuevoSubmenu" onclick="abrirModalSubmenu()" style="display:none">
                                <i class="fa fa-plus mr-1"></i> Nuevo Submenu
                            </button>
                        </div>
                    </div>

                    <div class="menu-card-body">

                        <ul class="nav nav-tabs tabs-menu" id="menuTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-menus" data-toggle="tab" href="#panel-menus" role="tab"
                                   onclick="document.getElementById('btnNuevoMenu').style.display='';document.getElementById('btnNuevoSubmenu').style.display='none'">
                                    <i class="fa fa-bars mr-1"></i> Menús Principales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-submenus" data-toggle="tab" href="#panel-submenus" role="tab"
                                   onclick="document.getElementById('btnNuevoMenu').style.display='none';document.getElementById('btnNuevoSubmenu').style.display=''">
                                    <i class="fa fa-list mr-1"></i> Submenús
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content pt-3" id="menuTabsContent">
                            {{-- ==================== PESTAÑA 1: MENÚS PRINCIPALES ==================== --}}
                            <div class="tab-pane fade show active" id="panel-menus" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="tablaMenus">
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
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="tablaSubmenus">
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

    <!-- ==================== Modal Menú (Crear / Editar) ==================== -->
    <div class="modal fade" id="modalMenu" tabindex="-1" role="dialog" aria-labelledby="tituloModalMenu" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-menu">
                    <h5 class="modal-title" id="tituloModalMenu">
                        <i class="fa fa-bars mr-2"></i>Nuevo Menú
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formMenu">
                    <div class="modal-body pb-2">
                        <input type="hidden" id="menuId">

                        <p class="modal-section-label"><i class="fa fa-info-circle mr-1"></i>Información del menú</p>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Nombre del Menú <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="menuNombre"
                                           placeholder="Ej: Ventas, Configuración…" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Orden <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control form-control-sm" id="menuOrden" required min="1">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Icono (Font Awesome) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="menuIcono"
                                           placeholder="fa fa-home" required>
                                    <small class="text-muted" style="font-size:.73rem">
                                        Acepta clase o HTML completo: <code>&lt;i class="fa-solid fa-home"&gt;&lt;/i&gt;</code>
                                        &mdash; <a href="https://fontawesome.com/search?o=r&m=free" target="_blank">Ver iconos gratuitos</a><br>
                                        <span class="text-warning" style="font-size:.70rem"><i class="fa fa-exclamation-triangle"></i> Los estilos <code>fa-duotone</code>, <code>fa-light</code> y <code>fa-thin</code> son Pro (de pago).</span>
                                    </small>
                                    {{-- Preview del icono --}}
                                    <div class="d-flex align-items-center mt-2 p-2 border rounded" id="previewMenuIcono"
                                         style="gap:12px;background:#f8f9fa;min-height:52px">
                                        <div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;
                                                    font-size:1.7rem;border-radius:6px;background:#fff;
                                                    border:1px solid #dee2e6;color:#495057;flex-shrink:0">
                                            <i class="fa fa-question-circle text-muted" id="previewMenuIconoEl"></i>
                                        </div>
                                        <div style="overflow:hidden">
                                            <div class="text-muted" style="font-size:.70rem">Vista previa</div>
                                            <code id="previewMenuIconoClase" style="font-size:.70rem;color:#6c757d">escribe un icono…</code>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm" id="menuEstado" required>
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado->id }}">{{ $estado->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Sección de Submenús visible solo al editar --}}
                        <div id="seccionSubmenusMenu" style="display:none;">
                            <p class="modal-section-label mt-2"><i class="fa fa-list mr-1"></i>Submenús de este menú</p>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Nombre</th>
                                            <th>URL</th>
                                            <th>Orden</th>
                                            <th>Estado</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodySubmenusDelMenu"></tbody>
                                </table>
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

    <!-- ==================== Modal Submenu (Crear / Editar) ==================== -->
    <div class="modal fade" id="modalSubmenu" tabindex="-1" role="dialog" aria-labelledby="tituloModalSubmenu" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-menu">
                    <h5 class="modal-title" id="tituloModalSubmenu">
                        <i class="fa fa-list mr-2"></i>Nuevo Submenú
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formSubmenu">
                    <div class="modal-body pb-2">
                        <input type="hidden" id="submenuId">

                        {{-- Pestañas internas del modal --}}
                        <ul class="nav nav-tabs tabs-menu" id="submenuModalTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-datos-generales" data-toggle="tab" href="#panel-datos-generales" role="tab">
                                    <i class="fa fa-info-circle mr-1"></i>Datos Generales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-datos-tecnicos" data-toggle="tab" href="#panel-datos-tecnicos" role="tab">
                                    <i class="fa fa-cog mr-1"></i>Datos Técnicos
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content border border-top-0 rounded-bottom p-3" id="submenuModalTabsContent" style="background:#fafafa">

                            {{-- ---- Pestaña: Datos Generales y Acceso ---- --}}
                            <div class="tab-pane fade show active" id="panel-datos-generales" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold small">Nombre del Submenú <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" id="submenuNombre" required
                                                   placeholder="Ej: Listar Clientes…">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold small">Menú Principal <span class="text-danger">*</span></label>
                                            <select class="form-control form-control-sm" id="submenuMenuId" required>
                                                <option value="">— Seleccione —</option>
                                                @foreach($menus->where('estado_id', 1) as $menu)
                                                    <option value="{{ $menu->id }}">{{ $menu->nombre_menu }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold small">Orden <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control form-control-sm" id="submenuOrden" required min="1">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold small">Estado <span class="text-danger">*</span></label>
                                            <select class="form-control form-control-sm" id="submenuEstado" required>
                                                @foreach($estados as $estado)
                                                    <option value="{{ $estado->id }}">{{ $estado->descripcion }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold small">Roles con Acceso <span class="text-danger">*</span></label>
                                    <div class="roles-checklist border p-3">
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
                                    <small class="text-muted" style="font-size:.73rem">Seleccione los roles que pueden ver este submenú</small>
                                </div>
                            </div>

                            {{-- ---- Pestaña: Datos Técnicos ---- --}}
                            <div class="tab-pane fade" id="panel-datos-tecnicos" role="tabpanel">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Icono (Font Awesome)</label>
                                    <input type="text" class="form-control form-control-sm" id="submenuIcono" placeholder="fa-solid fa-home">
                                    <small class="text-muted" style="font-size:.73rem">
                                        Acepta clase o HTML completo: <code>&lt;i class="fa-solid fa-home"&gt;&lt;/i&gt;</code>
                                        &mdash; <a href="https://fontawesome.com/search?o=r&m=free" target="_blank">Ver iconos gratuitos</a><br>
                                        <span class="text-warning" style="font-size:.70rem"><i class="fa fa-exclamation-triangle"></i> Los estilos <code>fa-duotone</code>, <code>fa-light</code> y <code>fa-thin</code> son Pro (de pago) y no se mostrarán.</span>
                                    </small>
                                    {{-- Preview del icono --}}
                                    <div class="d-flex align-items-center mt-2 p-2 border rounded" id="previewSubmenuIcono"
                                         style="gap:12px;background:#f8f9fa;min-height:52px">
                                        <div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;
                                                    font-size:1.7rem;border-radius:6px;background:#fff;
                                                    border:1px solid #dee2e6;color:#495057;flex-shrink:0">
                                            <i class="fa fa-question-circle text-muted" id="previewSubmenuIconoEl"></i>
                                        </div>
                                        <div style="overflow:hidden">
                                            <div class="text-muted" style="font-size:.70rem">Vista previa</div>
                                            <code id="previewSubmenuIconoClase" style="font-size:.70rem;color:#6c757d">escribe un icono…</code>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="generarArchivos">
                                        <label class="form-check-label font-weight-bold small" for="generarArchivos">
                                            Generar archivos automáticamente
                                        </label>
                                    </div>
                                    <small class="text-muted" style="font-size:.73rem">
                                        <i class="fa fa-info-circle"></i> Crea automáticamente: Componente Livewire, Vista Blade y la Ruta en web.php
                                    </small>
                                </div>

                                <div id="alertaIconoPro" class="alert alert-warning py-1 px-2" style="display:none;font-size:.75rem">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    Este icono usa un estilo <strong>Pro</strong> (<code>fa-duotone</code> / <code>fa-light</code> / <code>fa-thin</code>) que requiere una licencia de pago.
                                    Usa iconos gratuitos: <code>fa-solid fa-chart-line</code>, <code>fa-solid fa-users</code>, etc.
                                    <a href="https://fontawesome.com/search?o=r&m=free" target="_blank">Buscar icono gratis</a>
                                </div>

                                <div class="form-group" id="campoUrlRuta">
                                    <label class="font-weight-bold small">
                                        URL / Ruta <span class="text-danger">*</span>
                                        <span id="iconoBloqueoUrl" style="display:none" class="text-secondary ml-1"><i class="fa fa-lock"></i></span>
                                    </label>
                                    <input type="text" class="form-control form-control-sm" id="submenuUrl" placeholder="menu/nombre_submenu">
                                    <small class="text-muted" id="hintUrlNuevo" style="font-size:.73rem">Se genera automáticamente desde el menú y el nombre.</small>
                                    <small class="text-warning" id="hintUrlEdicion" style="display:none;font-size:.73rem">
                                        <i class="fa fa-lock"></i> La ruta no puede modificarse una vez creado el submenú.
                                    </small>
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

</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/js_proyecto/menu/gestion-menu.js') }}"></script>
@endpush
