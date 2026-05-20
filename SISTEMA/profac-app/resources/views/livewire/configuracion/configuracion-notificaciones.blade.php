<div>
    {{-- Encabezado --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-8">
            <h2><i class="fa fa-bell text-primary"></i> Configuración de Notificaciones</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Notificaciones de Flujo</li>
            </ol>
        </div>
        <div class="col-sm-4 d-flex align-items-center justify-content-end gap-2">
            {{-- Interruptor global --}}
            <button wire:click="toggleSistema"
                class="btn btn-sm {{ $notificacionesActivas ? 'btn-success' : 'btn-default' }}"
                title="{{ $notificacionesActivas ? 'Haz clic para desactivar las notificaciones' : 'Haz clic para activar las notificaciones' }}">
                <i class="fa {{ $notificacionesActivas ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                {{ $notificacionesActivas ? 'Notificaciones ON' : 'Notificaciones OFF' }}
            </button>
            <button wire:click="nuevaRegla" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Nueva Regla
            </button>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- Flash message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <i class="fa fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- ── Advertencia de jerarquías incompletas ── --}}
        @if(!empty($rolesIncompletos))
            <div class="alert alert-warning" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <i class="fa fa-exclamation-triangle mt-1 mr-2"></i>
                    <div>
                        <strong>Roles con jerarquía organizacional incompleta</strong>
                        <p class="mb-1 mt-1 text-sm">
                            Los siguientes roles no tienen <strong>nivel jerárquico</strong> y/o <strong>área</strong> configurados.
                            Las reglas de notificación por área no funcionarán correctamente para estos roles:
                        </p>
                        <ul class="mb-2 pl-3">
                            @foreach($rolesIncompletos as $rolInc)
                                <li class="text-sm">
                                    <strong>{{ $rolInc['nombre'] }}</strong>
                                    <span class="text-muted"> — falta: {{ $rolInc['falta'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('roles.gestion') }}" class="btn btn-warning btn-xs">
                            <i class="fa fa-cog"></i> Configurar jerarquía de roles
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Tabla de reglas agrupadas por estado de flujo ── --}}
        @php
            $grupos = collect($configs)->groupBy('tipo_tramite_id');
        @endphp

        @foreach($grupos as $tipoId => $reglas)
            @php $tramiteNombre = $reglas->first()['tramite_nombre'] ?? 'Tramite #'.$tipoId; @endphp
            <div class="ibox">
                <div class="ibox-title d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <span class="badge badge-primary mr-2">Flujo</span>
                        {{ $tramiteNombre }}
                    </h5>
                    <small class="text-muted">{{ $reglas->count() }} regla(s) configurada(s)</small>
                </div>
                <div class="ibox-content p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Destino</th>
                                <th>Nivel máximo</th>
                                <th>Cobertura</th>
                                <th>Escalación</th>
                                <th class="text-center">Activo</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reglas as $reg)
                                <tr class="{{ !$reg['activo'] ? 'text-muted' : '' }}">
                                    <td>
                                        @if($reg['rol_nombre'])
                                            <i class="fa fa-user-tag text-primary mr-1"></i>
                                            <strong>{{ $reg['rol_nombre'] }}</strong>
                                        @elseif($reg['area_nombre'])
                                            <i class="fa fa-users text-success mr-1"></i>
                                            Área: <strong>{{ $reg['area_nombre'] }}</strong>
                                        @else
                                            <span class="text-muted">Sin targeting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reg['nivel_max_nombre'])
                                            <span class="badge badge-secondary">{{ $reg['nivel_max_nombre'] }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $cob = $this->getCobertura($reg['id']); @endphp
                                        <span class="badge {{ $cob > 0 ? 'badge-success' : 'badge-danger' }}">
                                            {{ $cob }} usuario(s)
                                        </span>
                                    </td>
                                    <td class="text-sm">
                                        @if($reg['escalar_activo'])
                                            <i class="fa fa-arrow-up text-warning mr-1"></i>
                                            {{ $reg['escalar_horas'] }}h → {{ $reg['escalar_nivel'] ?? '—' }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button wire:click="toggleActivo({{ $reg['id'] }})"
                                                class="btn btn-xs {{ $reg['activo'] ? 'btn-success' : 'btn-default' }}"
                                                title="{{ $reg['activo'] ? 'Desactivar' : 'Activar' }}">
                                            <i class="fa {{ $reg['activo'] ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button wire:click="editarRegla({{ $reg['id'] }})"
                                                class="btn btn-xs btn-warning mr-1" title="Editar">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button wire:click="eliminar({{ $reg['id'] }})"
                                                wire:confirm="¿Eliminar esta regla de notificación?"
                                                class="btn btn-xs btn-danger" title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        @if(empty($configs))
            <div class="ibox">
                <div class="ibox-content text-center py-5 text-muted">
                    <i class="fa fa-bell-slash fa-3x mb-3"></i>
                    <p>No hay reglas de notificación configuradas. Haz clic en <strong>Nueva Regla</strong> para empezar.</p>
                </div>
            </div>
        @endif

    </div>{{-- /wrapper-content --}}

    {{-- ── MODAL crear / editar ── --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title mb-0">
                        <i class="fa fa-bell mr-2"></i>
                        {{ $editandoId ? 'Editar Regla' : 'Nueva Regla de Notificación' }}
                    </h5>
                    <button wire:click="$set('showModal', false)" class="close text-white">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- Estado del flujo --}}
                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold">Estado de Flujo <span class="text-danger">*</span></label>
                            <select wire:model="tipoTramiteId" class="form-control @error('tipoTramiteId') is-invalid @enderror">
                                <option value="">-- Seleccionar --</option>
                                @foreach($tiposTramites as $tt)
                                    <option value="{{ $tt['id'] }}">{{ $tt['nombre'] }}</option>
                                @endforeach
                            </select>
                            @error('tipoTramiteId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Tipo de targeting --}}
                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold">Tipo de Destino <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="custom-control custom-radio mr-4">
                                    <input type="radio" id="tipo_rol" wire:model="targetTipo" value="rol"
                                           class="custom-control-input">
                                    <label class="custom-control-label" for="tipo_rol">
                                        <i class="fa fa-user-tag text-primary mr-1"></i> Rol específico
                                    </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="tipo_area" wire:model="targetTipo" value="area"
                                           class="custom-control-input">
                                    <label class="custom-control-label" for="tipo_area">
                                        <i class="fa fa-users text-success mr-1"></i> Área / Departamento
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Rol (si targetTipo = rol) --}}
                        @if($targetTipo === 'rol')
                            <div class="col-md-12 mb-3">
                                <label class="font-weight-bold">Rol <span class="text-danger">*</span></label>
                                <select wire:model="rolId" class="form-control @error('rolId') is-invalid @enderror">
                                    <option value="">-- Seleccionar rol --</option>
                                    @foreach($roles as $r)
                                        <option value="{{ $r['id'] }}">{{ $r['nombre'] }}</option>
                                    @endforeach
                                </select>
                                @error('rolId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        {{-- Área + Nivel máximo (si targetTipo = area) --}}
                        @if($targetTipo === 'area')
                            <div class="col-md-12 mb-3">
                                @if(count($areas) === 0)
                                    <div class="alert alert-warning py-2 mb-0 d-flex align-items-center justify-content-between">
                                        <span>
                                            <i class="fa fa-exclamation-triangle mr-1"></i>
                                            No hay <strong>áreas</strong> configuradas.
                                        </span>
                                        <a href="{{ route('configuracion.jerarquia') }}" class="btn btn-xs btn-warning ml-3" target="_blank">
                                            <i class="fa fa-sitemap mr-1"></i> Configurar áreas
                                        </a>
                                    </div>
                                @else
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="font-weight-bold">Área <span class="text-danger">*</span></label>
                                            <select wire:model="areaId" class="form-control @error('areaId') is-invalid @enderror">
                                                <option value="">-- Seleccionar área --</option>
                                                @foreach($areas as $a)
                                                    <option value="{{ $a['id'] }}">{{ $a['nombre'] }}</option>
                                                @endforeach
                                            </select>
                                            @error('areaId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="font-weight-bold">Nivel máximo a notificar</label>
                                            @if(count($niveles) === 0)
                                                <div class="alert alert-warning py-2 d-flex align-items-center justify-content-between">
                                                    <span class="small"><i class="fa fa-exclamation-triangle mr-1"></i> Sin niveles aún.</span>
                                                    <a href="{{ route('configuracion.jerarquia') }}" target="_blank" class="btn btn-xs btn-warning ml-2">
                                                        <i class="fa fa-sitemap"></i> Configurar
                                                    </a>
                                                </div>
                                            @else
                                                <select wire:model="nivelMaxId" class="form-control">
                                                    <option value="">-- Todos los niveles --</option>
                                                    @foreach($niveles as $n)
                                                        <option value="{{ $n['id'] }}">{{ $n['nombre'] }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">Ej: "Colaborador" → solo notifica a ese nivel y debajo.</small>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="col-md-12"><hr class="my-2"></div>

                        {{-- Escalación --}}
                        <div class="col-md-12 mb-2">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="escalarActivo"
                                       wire:model="escalarActivo">
                                <label class="custom-control-label font-weight-bold" for="escalarActivo">
                                    Activar escalación automática
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Si la notificación no se lee en N horas, se notificará al nivel superior.
                            </small>
                        </div>

                        @if($escalarActivo)
                            <div class="col-md-6 mb-3">
                                <label>Escalar después de (horas) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="escalarHoras"
                                       class="form-control @error('escalarHoras') is-invalid @enderror"
                                       min="1" max="720" placeholder="Ej: 4">
                                @error('escalarHoras') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Escalar al nivel <span class="text-danger">*</span></label>
                                @if(count($niveles) === 0)
                                    <div class="alert alert-warning py-2 mb-0 d-flex align-items-center justify-content-between">
                                        <span class="small">
                                            <i class="fa fa-exclamation-triangle mr-1"></i>
                                            No hay niveles configurados.
                                        </span>
                                        <a href="{{ route('configuracion.jerarquia') }}" class="btn btn-xs btn-warning ml-2" target="_blank">
                                            <i class="fa fa-sitemap mr-1"></i> Configurar
                                        </a>
                                    </div>
                                @else
                                    <select wire:model="escalarNivelId"
                                            class="form-control @error('escalarNivelId') is-invalid @enderror">
                                        <option value="">-- Seleccionar nivel --</option>
                                        @foreach($niveles as $n)
                                            <option value="{{ $n['id'] }}">{{ $n['nombre'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('escalarNivelId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @endif
                            </div>
                        @endif

                        {{-- Activo --}}
                        <div class="col-md-12 mb-0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="activoSwitch"
                                       wire:model="activo">
                                <label class="custom-control-label" for="activoSwitch">Regla activa</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal', false)" class="btn btn-default">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button wire:click="guardar" wire:loading.attr="disabled" class="btn btn-primary">
                        <span wire:loading.remove><i class="fa fa-save"></i> Guardar</span>
                        <span wire:loading><i class="fa fa-spinner fa-spin"></i> Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
