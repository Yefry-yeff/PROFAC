<div>
    {{-- Encabezado --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-8">
            <h2><i class="fa fa-sitemap text-primary"></i> Jerarquía Organizacional</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Jerarquía Organizacional</li>
            </ol>
        </div>
        <div class="col-sm-4 d-flex align-items-center justify-content-end">
            <a href="{{ route('configuracion.notificaciones.flujo') }}" class="btn btn-default btn-sm">
                <i class="fa fa-bell mr-1"></i> Configurar Notificaciones
            </a>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- Flash success --}}
        @if($successMsg)
            <div class="alert alert-success alert-dismissible fade show" wire:key="success-msg">
                <button type="button" class="close" wire:click="$set('successMsg', null)"><span>&times;</span></button>
                <i class="fa fa-check-circle mr-1"></i> {{ $successMsg }}
            </div>
        @endif

        {{-- Flash errors --}}
        @if(session('error_area'))
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <i class="fa fa-exclamation-circle mr-1"></i> {{ session('error_area') }}
            </div>
        @endif
        @if(session('error_nivel'))
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <i class="fa fa-exclamation-circle mr-1"></i> {{ session('error_nivel') }}
            </div>
        @endif

        {{-- Info general --}}
        <div class="alert alert-info border-left border-info py-2 mb-4" style="border-left-width:4px!important;">
            <i class="fa fa-info-circle mr-2"></i>
            Las <strong>Áreas</strong> agrupan departamentos y las <strong>Niveles Jerárquicos</strong> definen el rango de cada rol.
            Ambas configuraciones son utilizadas por las <strong>reglas de notificación</strong> del flujo de ventas.
        </div>

        <div class="row">

            {{-- ================================================================
                 PANEL IZQUIERDO: ÁREAS / DEPARTAMENTOS
                 ================================================================ --}}
            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5><i class="fa fa-building text-primary mr-2"></i>Áreas / Departamentos</h5>
                        <div class="ibox-tools">
                            <button wire:click="nuevaArea" class="btn btn-primary btn-xs">
                                <i class="fa fa-plus mr-1"></i> Nueva Área
                            </button>
                        </div>
                    </div>
                    <div class="ibox-content">
                        @if(count($areas) === 0)
                            <div class="text-center py-5">
                                <i class="fa fa-building fa-4x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-3">No hay áreas configuradas todavía.</p>
                                <p class="text-muted small mb-4">Las áreas son necesarias para definir a qué departamento pertenece cada rol y para las reglas de notificación por área.</p>
                                <button wire:click="nuevaArea" class="btn btn-primary">
                                    <i class="fa fa-plus mr-1"></i> Crear primera área
                                </button>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr class="bg-light">
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th class="text-center" style="width:70px">Roles</th>
                                            <th class="text-center" style="width:80px">Estado</th>
                                            <th class="text-center" style="width:80px">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($areas as $area)
                                            <tr wire:key="area-{{ $area['id'] }}">
                                                <td class="font-weight-semibold align-middle">
                                                    {{ $area['nombre'] }}
                                                </td>
                                                <td class="align-middle text-muted small">
                                                    {{ $area['descripcion'] ?: '—' }}
                                                </td>
                                                <td class="text-center align-middle">
                                                    @if($area['roles_count'] > 0)
                                                        <span class="badge badge-primary">{{ $area['roles_count'] }}</span>
                                                    @else
                                                        <span class="badge badge-light text-muted">0</span>
                                                    @endif
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button wire:click="toggleArea({{ $area['id'] }})"
                                                            class="btn btn-xs {{ $area['estado_id'] === 1 ? 'btn-success' : 'btn-default' }}"
                                                            title="{{ $area['estado_id'] === 1 ? 'Activa — clic para desactivar' : 'Inactiva — clic para activar' }}">
                                                        <i class="fa {{ $area['estado_id'] === 1 ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                    </button>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <div class="btn-group">
                                                        <button wire:click="editarArea({{ $area['id'] }})"
                                                                class="btn btn-xs btn-white" title="Editar">
                                                            <i class="fa fa-pencil text-primary"></i>
                                                        </button>
                                                        @if($area['roles_count'] === 0)
                                                            <button wire:click="eliminarArea({{ $area['id'] }})"
                                                                    class="btn btn-xs btn-white" title="Eliminar"
                                                                    onclick="return confirm('¿Eliminar el área \'{{ addslashes($area['nombre']) }}\'?')">
                                                                <i class="fa fa-trash text-danger"></i>
                                                            </button>
                                                        @else
                                                            <button class="btn btn-xs btn-white" disabled title="Tiene roles asignados">
                                                                <i class="fa fa-trash text-muted"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-muted small mt-2 px-1">
                                <i class="fa fa-info-circle mr-1"></i>
                                {{ count($areas) }} área(s) registrada(s)
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ================================================================
                 PANEL DERECHO: NIVELES JERÁRQUICOS
                 ================================================================ --}}
            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5><i class="fa fa-level-up text-warning mr-2"></i>Niveles Jerárquicos</h5>
                        <div class="ibox-tools">
                            <button wire:click="nuevoNivel" class="btn btn-warning btn-xs">
                                <i class="fa fa-plus mr-1"></i> Nuevo Nivel
                            </button>
                        </div>
                    </div>
                    <div class="ibox-content">
                        @if(count($niveles) === 0)
                            <div class="text-center py-5">
                                <i class="fa fa-sitemap fa-4x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-3">No hay niveles jerárquicos configurados todavía.</p>
                                <p class="text-muted small mb-4">Los niveles definen la jerarquía de los roles (ej: Gerente → Supervisor → Colaborador) y se usan para la escalación automática de notificaciones.</p>
                                <button wire:click="nuevoNivel" class="btn btn-warning">
                                    <i class="fa fa-plus mr-1"></i> Crear primer nivel
                                </button>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr class="bg-light">
                                            <th class="text-center" style="width:55px">Orden</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th class="text-center" style="width:70px">Roles</th>
                                            <th class="text-center" style="width:80px">Estado</th>
                                            <th class="text-center" style="width:80px">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($niveles as $nivel)
                                            <tr wire:key="nivel-{{ $nivel['id'] }}">
                                                <td class="text-center align-middle">
                                                    <span class="badge badge-secondary" style="font-size:.85em;min-width:28px">
                                                        {{ $nivel['orden'] }}
                                                    </span>
                                                </td>
                                                <td class="font-weight-semibold align-middle">
                                                    {{ $nivel['nombre'] }}
                                                </td>
                                                <td class="align-middle text-muted small">
                                                    {{ $nivel['descripcion'] ?: '—' }}
                                                </td>
                                                <td class="text-center align-middle">
                                                    @if($nivel['roles_count'] > 0)
                                                        <span class="badge badge-primary">{{ $nivel['roles_count'] }}</span>
                                                    @else
                                                        <span class="badge badge-light text-muted">0</span>
                                                    @endif
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button wire:click="toggleNivel({{ $nivel['id'] }})"
                                                            class="btn btn-xs {{ $nivel['estado_id'] === 1 ? 'btn-success' : 'btn-default' }}"
                                                            title="{{ $nivel['estado_id'] === 1 ? 'Activo — clic para desactivar' : 'Inactivo — clic para activar' }}">
                                                        <i class="fa {{ $nivel['estado_id'] === 1 ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                    </button>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <div class="btn-group">
                                                        <button wire:click="editarNivel({{ $nivel['id'] }})"
                                                                class="btn btn-xs btn-white" title="Editar">
                                                            <i class="fa fa-pencil text-primary"></i>
                                                        </button>
                                                        @if($nivel['roles_count'] === 0)
                                                            <button wire:click="eliminarNivel({{ $nivel['id'] }})"
                                                                    class="btn btn-xs btn-white" title="Eliminar"
                                                                    onclick="return confirm('¿Eliminar el nivel \'{{ addslashes($nivel['nombre']) }}\'?')">
                                                                <i class="fa fa-trash text-danger"></i>
                                                            </button>
                                                        @else
                                                            <button class="btn btn-xs btn-white" disabled title="Tiene roles asignados">
                                                                <i class="fa fa-trash text-muted"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-muted small mt-2 px-1">
                                <i class="fa fa-info-circle mr-1"></i>
                                {{ count($niveles) }} nivel(es) registrado(s) &mdash;
                                <span class="text-muted">Orden 1 = mayor jerarquía</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>{{-- /row --}}

    </div>{{-- /wrapper-content --}}

    {{-- ════════════════════════════════════════════════════════════════════════
         MODAL: CREAR / EDITAR ÁREA
         ════════════════════════════════════════════════════════════════════════ --}}
    @if($showAreaModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title mb-0">
                        <i class="fa fa-building mr-2"></i>
                        {{ $editAreaId ? 'Editar Área' : 'Nueva Área / Departamento' }}
                    </h5>
                    <button wire:click="$set('showAreaModal', false)" class="close text-white">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">
                            Nombre del Área <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               wire:model.defer="areaNombre"
                               class="form-control @error('areaNombre') is-invalid @enderror"
                               placeholder="Ej: Ventas, Bodega, Administración…"
                               maxlength="100"
                               autofocus>
                        @error('areaNombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Descripción <span class="text-muted">(opcional)</span></label>
                        <textarea wire:model.defer="areaDescripcion"
                                  class="form-control"
                                  rows="2"
                                  maxlength="255"
                                  placeholder="Descripción breve del área…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showAreaModal', false)" class="btn btn-default">
                        <i class="fa fa-times mr-1"></i> Cancelar
                    </button>
                    <button wire:click="guardarArea" wire:loading.attr="disabled" class="btn btn-primary">
                        <span wire:loading.remove wire:target="guardarArea">
                            <i class="fa fa-save mr-1"></i> Guardar
                        </span>
                        <span wire:loading wire:target="guardarArea">
                            <i class="fa fa-spinner fa-spin mr-1"></i> Guardando…
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════════
         MODAL: CREAR / EDITAR NIVEL JERÁRQUICO
         ════════════════════════════════════════════════════════════════════════ --}}
    @if($showNivelModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background:#f0b429;color:#7a4f01;">
                    <h5 class="modal-title mb-0">
                        <i class="fa fa-level-up mr-2"></i>
                        {{ $editNivelId ? 'Editar Nivel Jerárquico' : 'Nuevo Nivel Jerárquico' }}
                    </h5>
                    <button wire:click="$set('showNivelModal', false)" class="close" style="color:#7a4f01;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">
                            Nombre del Nivel <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               wire:model.defer="nivelNombre"
                               class="form-control @error('nivelNombre') is-invalid @enderror"
                               placeholder="Ej: Gerente, Supervisor, Colaborador…"
                               maxlength="100"
                               autofocus>
                        @error('nivelNombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">
                            Orden Jerárquico <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               wire:model.defer="nivelOrden"
                               class="form-control @error('nivelOrden') is-invalid @enderror"
                               min="1" max="99"
                               style="max-width:120px">
                        <small class="text-muted">
                            <i class="fa fa-info-circle mr-1"></i>
                            Número menor = mayor jerarquía. Ej: Gerente=1, Supervisor=2, Colaborador=3.
                        </small>
                        @error('nivelOrden')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Descripción <span class="text-muted">(opcional)</span></label>
                        <textarea wire:model.defer="nivelDescripcion"
                                  class="form-control"
                                  rows="2"
                                  maxlength="255"
                                  placeholder="Descripción del nivel…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showNivelModal', false)" class="btn btn-default">
                        <i class="fa fa-times mr-1"></i> Cancelar
                    </button>
                    <button wire:click="guardarNivel" wire:loading.attr="disabled" class="btn btn-warning">
                        <span wire:loading.remove wire:target="guardarNivel">
                            <i class="fa fa-save mr-1"></i> Guardar
                        </span>
                        <span wire:loading wire:target="guardarNivel">
                            <i class="fa fa-spinner fa-spin mr-1"></i> Guardando…
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
