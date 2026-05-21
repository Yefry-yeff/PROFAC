<div>
    <style>
/* â”€â”€ Animaciones â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
@keyframes cfgFadeUp {
    from { opacity:0; transform:translateY(14px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes modalIn {
    from { opacity:0; transform:scale(.95) translateY(-12px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
.cfg-card   { animation: cfgFadeUp .25s ease both; }
.cfg-card:nth-child(2) { animation-delay:.05s; }
.cfg-card:nth-child(3) { animation-delay:.1s; }
.cfg-modal-content { animation: modalIn .22s ease-out both; display:flex; flex-direction:column; }
.cfg-modal-content .modal-header { flex-shrink: 0; }
.cfg-modal-content .modal-footer { flex-shrink: 0; }
.cfg-modal-body { min-height: 0; scrollbar-width: thin; scrollbar-color: #d1d5db transparent; }
.cfg-modal-body::-webkit-scrollbar { width: 5px; }
.cfg-modal-body::-webkit-scrollbar-track { background: transparent; }
.cfg-modal-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 99px; }
.cfg-modal-body::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

/* â”€â”€ Hero header â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.cfg-hero {
    background: linear-gradient(135deg,#1e3a5f 0%,#2563eb 60%,#3b82f6 100%);
    padding: 28px 32px 24px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.cfg-hero::before {
    content:'';
    position:absolute; inset:0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

/* â”€â”€ Cards â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.notif-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e8edf5;
    box-shadow: 0 1px 4px rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.04);
    margin-bottom: 16px;
    overflow: hidden;
}
.notif-card-header {
    padding: 14px 20px;
    border-bottom: 1px solid #f0f3f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg,#f8faff 0%,#f0f4ff 100%);
}
.notif-badge-tipo {
    display:inline-flex; align-items:center; gap:6px;
    background: linear-gradient(135deg,#2563eb,#3b82f6);
    color:#fff; font-size:10px; font-weight:700; letter-spacing:.5px;
    padding: 3px 10px; border-radius:20px; text-transform:uppercase;
}

/* â”€â”€ Tabla mejorada â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.notif-table thead th {
    font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.6px; color:#64748b;
    background: #f8fafc; border-bottom: 2px solid #e8edf5;
    padding: 10px 16px;
}
.notif-table tbody tr { transition: background .12s; }
.notif-table tbody tr:hover { background: #f8faff; }
.notif-table tbody td { padding: 12px 16px; vertical-align: middle; font-size:13px; border-color:#f0f3f9; }

/* â”€â”€ Toggle switch â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.toggle-switch { position:relative; display:inline-block; width:36px; height:20px; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider {
    position:absolute; inset:0; cursor:pointer;
    background:#cbd5e1; border-radius:20px; transition:.25s;
}
.toggle-slider::before {
    content:''; position:absolute; height:14px; width:14px; left:3px; bottom:3px;
    background:#fff; border-radius:50%; transition:.25s;
    box-shadow:0 1px 3px rgba(0,0,0,.2);
}
input:checked + .toggle-slider { background: linear-gradient(135deg,#22c55e,#16a34a); }
input:checked + .toggle-slider::before { transform: translateX(16px); }

/* â”€â”€ Warning collapsible â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.cfg-warning { border-left: 4px solid #f59e0b; background:#fffbeb; border-radius:0 8px 8px 0; padding:14px 18px; }
.cfg-warning-toggle { cursor:pointer; user-select:none; }
    </style>


    {{-- â•â• HERO HEADER â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <div class="cfg-hero">
        <div class="d-flex align-items-center justify-content-between" style="position:relative;z-index:1;">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2" style="background:transparent;padding:0;font-size:12px;opacity:.75;">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-white">Inicio</a></li>
                        <li class="breadcrumb-item active text-white">Notificaciones</li>
                    </ol>
                </nav>
                <h2 class="mb-0 text-white font-weight-bold" style="font-size:1.5rem; letter-spacing:-.3px;">
                    <i class="fa fa-bell mr-2" style="opacity:.9;"></i> Configuración de Notificaciones
                </h2>
                <p class="mb-0 mt-1" style="font-size:12px; opacity:.7;">
                    Define quién recibe alertas en cada etapa del flujo de trabajo
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                {{-- Toggle sistema --}}
                <button wire:click="toggleSistema"
                        class="btn btn-sm font-weight-bold mr-2"
                        style="border-radius:20px; padding:6px 16px; font-size:12px; border:2px solid {{ $notificacionesActivas ? 'rgba(255,255,255,.5)' : 'rgba(255,255,255,.3)' }};
                               background:{{ $notificacionesActivas ? 'rgba(34,197,94,.25)' : 'rgba(255,255,255,.12)' }};
                               color:white; backdrop-filter:blur(4px);"
                        title="{{ $notificacionesActivas ? 'Haz clic para desactivar' : 'Haz clic para activar' }}">
                    <i class="fa {{ $notificacionesActivas ? 'fa-toggle-on' : 'fa-toggle-off' }} mr-1"
                       style="font-size:14px; color:{{ $notificacionesActivas ? '#4ade80' : '#94a3b8' }};"></i>
                    Notificaciones {{ $notificacionesActivas ? 'ON' : 'OFF' }}
                </button>
                <button wire:click="nuevaRegla"
                        class="btn btn-sm font-weight-bold"
                        style="border-radius:20px; padding:6px 18px; font-size:12px;
                               background:rgba(255,255,255,.95); color:#2563eb;
                               border:none; box-shadow:0 2px 8px rgba(0,0,0,.15);">
                    <i class="fa fa-plus mr-1"></i> Nueva Regla
                </button>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content" style="padding:24px 32px;">

        {{-- â”€â”€ Flash â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2"
                 style="border-radius:10px; border:none; box-shadow:0 2px 8px rgba(34,197,94,.2); font-size:13px;">
                <i class="fa fa-check-circle fa-lg text-success mr-2"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="close ml-auto" data-dismiss="alert">&times;</button>
            </div>
        @endif

        {{-- â”€â”€ Advertencia jerarquÃ­as (colapsable) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        @if(!empty($rolesIncompletos))
        <div x-data="{ expanded: false }" class="cfg-warning mb-4">
            <div class="d-flex align-items-center justify-content-between cfg-warning-toggle" @click="expanded = !expanded">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa fa-exclamation-triangle text-warning mr-2"></i>
                    <div>
                        <strong style="font-size:13px; color:#92400e;">
                            {{ count($rolesIncompletos) }} roles con jerarquía incompleta
                        </strong>
                        <span class="ml-2" style="font-size:12px; color:#b45309;">
                            — Las reglas por área no funcionarán para estos roles
                        </span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('roles.gestion') }}"
                       class="btn btn-xs btn-warning"
                       style="border-radius:6px; font-size:11px; padding:3px 10px;">
                        <i class="fa fa-cog mr-1"></i> Configurar
                    </a>
                    <i class="fa text-warning ml-2" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </div>
            </div>
            <div x-show="expanded" x-transition style="display:none;">
                <div class="mt-3 pt-2" style="border-top:1px dashed #fcd34d;">
                    <div class="row">
                        @foreach($rolesIncompletos as $rolInc)
                            <div class="col-md-4 mb-1">
                                <span style="font-size:12px; color:#78350f;">
                                    <i class="fa fa-user-tag mr-1 text-warning"></i>
                                    <strong>{{ $rolInc['nombre'] }}</strong>
                                    <span class="text-muted"> — falta: {{ $rolInc['falta'] }}</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- â”€â”€ REGLAS agrupadas â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        @php $grupos = collect($configs)->groupBy('tipo_tramite_id'); @endphp

        @foreach($grupos as $tipoId => $reglas)
            @php $tramiteNombre = $reglas->first()['tramite_nombre'] ?? 'Tramite #'.$tipoId; @endphp
            <div class="notif-card cfg-card">
                <div class="notif-card-header">
                    <div class="d-flex align-items-center gap-3">
                        <span class="notif-badge-tipo">
                            <i class="fa fa-sitemap" style="font-size:9px;"></i> Flujo
                        </span>
                        <span style="font-size:14px; font-weight:700; color:#1e293b;">{{ $tramiteNombre }}</span>
                    </div>
                    <span style="font-size:11px; color:#94a3b8; font-weight:600;">
                        <i class="fa fa-list mr-1"></i>{{ $reglas->count() }} regla(s)
                    </span>
                </div>
                <div style="overflow:hidden;">
                    <table class="table table-hover mb-0 notif-table">
                        <thead>
                            <tr>
                                <th>Destino</th>
                                <th>Nivel máximo</th>
                                <th>Cobertura</th>
                                <th>Escalación</th>
                                <th class="text-center" style="width:80px;">Activo</th>
                                <th class="text-center" style="width:90px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reglas as $reg)
                                <tr>
                                    <td>
                                        @if($reg['rol_nombre'])
                                            <span class="d-flex align-items-center gap-2">
                                                <span style="width:28px;height:28px;background:linear-gradient(135deg,#eff6ff,#dbeafe);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                    <i class="fa fa-user-tag text-primary" style="font-size:11px;"></i>
                                                </span>
                                                <strong style="color:#1e293b;">{{ $reg['rol_nombre'] }}</strong>
                                            </span>
                                        @elseif($reg['area_nombre'])
                                            <span class="d-flex align-items-center gap-2">
                                                <span style="width:28px;height:28px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                    <i class="fa fa-users text-success" style="font-size:11px;"></i>
                                                </span>
                                                <span>Área: <strong style="color:#1e293b;">{{ $reg['area_nombre'] }}</strong></span>
                                            </span>
                                        @else
                                            <span class="text-muted font-italic" style="font-size:12px;">Sin targeting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reg['nivel_max_nombre'])
                                            <span style="display:inline-flex;align-items:center;gap:4px;background:#f1f5f9;color:#475569;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:600;">
                                                <i class="fa fa-layer-group" style="font-size:10px;"></i>
                                                {{ $reg['nivel_max_nombre'] }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $cob = $this->getCobertura($reg['id']); @endphp
                                        <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:700;
                                              background:{{ $cob > 0 ? 'linear-gradient(135deg,#f0fdf4,#dcfce7)' : 'linear-gradient(135deg,#fef2f2,#fee2e2)' }};
                                              color:{{ $cob > 0 ? '#15803d' : '#dc2626' }};">
                                            <i class="fa {{ $cob > 0 ? 'fa-users' : 'fa-user-times' }}" style="font-size:11px;"></i>
                                            {{ $cob }} usuario(s)
                                        </span>
                                    </td>
                                    <td>
                                        @if($reg['escalar_activo'])
                                            <span style="display:inline-flex;align-items:center;gap:5px;background:#fffbeb;color:#92400e;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;border:1px solid #fde68a;">
                                                <i class="fa fa-arrow-up text-warning" style="font-size:10px;"></i>
                                                {{ $reg['escalar_horas'] }}h → {{ $reg['escalar_nivel'] ?? '?' }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <label class="toggle-switch mb-0" title="{{ $reg['activo'] ? 'Desactivar' : 'Activar' }}">
                                            <input type="checkbox" wire:click="toggleActivo({{ $reg['id'] }})"
                                                   {{ $reg['activo'] ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </td>
                                    <td class="text-center">
                                        <button wire:click="editarRegla({{ $reg['id'] }})"
                                                class="btn btn-xs mr-1"
                                                style="background:linear-gradient(135deg,#fef3c7,#fde68a);color:#92400e;border:none;border-radius:6px;padding:4px 8px;"
                                                title="Editar">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button wire:click="eliminar({{ $reg['id'] }})"
                                                wire:confirm="¿Eliminar esta regla de notificación?"
                                                class="btn btn-xs"
                                                style="background:linear-gradient(135deg,#fee2e2,#fecaca);color:#dc2626;border:none;border-radius:6px;padding:4px 8px;"
                                                title="Eliminar">
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
            <div class="notif-card cfg-card">
                <div class="text-center py-5 px-4">
                    <div style="width:64px;height:64px;background:linear-gradient(135deg,#f1f5f9,#e2e8f0);border-radius:20px;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;">
                        <i class="fa fa-bell-slash text-muted fa-2x" style="opacity:.4;"></i>
                    </div>
                    <h5 style="color:#475569;font-weight:700;">Sin reglas configuradas</h5>
                    <p class="text-muted mb-3" style="font-size:13px;">
                        Define quién debe recibir notificaciones en cada etapa del flujo.
                    </p>
                    <button wire:click="nuevaRegla" class="btn btn-primary"
                            style="border-radius:8px; padding:8px 24px; font-size:13px;">
                        <i class="fa fa-plus mr-1"></i> Crear primera regla
                    </button>
                </div>
            </div>
        @endif

    </div>{{-- /wrapper-content --}}

    {{-- â•â• MODAL crear / editar â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog"
         style="background:rgba(2,8,23,.65); backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px);">
        <div class="modal-dialog modal-lg" role="document"
             style="margin: 4vh auto; max-width:720px;">
            <div class="modal-content cfg-modal-content"
                 style="border:none; border-radius:16px;
                        box-shadow:0 25px 60px rgba(0,0,0,.3), 0 8px 24px rgba(0,0,0,.15);">

                {{-- Header --}}
                <div class="modal-header"
                     style="background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 100%);
                            border:none; padding:18px 24px; border-radius:16px 16px 0 0;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                            <i class="fa fa-bell text-white" style="font-size:15px;"></i>
                        </div>
                        <h5 class="modal-title mb-0 text-white font-weight-bold" style="font-size:15px;">
                            {{ $editandoId ? 'Editar Regla de Notificación' : 'Nueva Regla de Notificación' }}
                        </h5>
                    </div>
                    <button wire:click="$set('showModal', false)"
                            class="close text-white" style="opacity:.7; font-size:20px; margin-top:-2px;">
                        <span>&times;</span>
                    </button>
                </div>

                {{-- Body --}}
                <div class="modal-body cfg-modal-body"
                     style="padding:24px; background:#fff; overflow-y:auto;
                            max-height:calc(85vh - 130px);
                            scrollbar-width:thin; scrollbar-color:#d1d5db transparent;">
                    <div class="row">

                        {{-- Estado del flujo --}}
                        <div class="col-md-12 mb-4">
                            <label class="font-weight-bold mb-1" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#475569;">
                                Estado de Flujo <span class="text-danger">*</span>
                            </label>
                            <select wire:model="tipoTramiteId"
                                    class="form-control @error('tipoTramiteId') is-invalid @enderror"
                                    style="border-radius:8px; border-color:#e2e8f0; font-size:13px; height:40px;">
                                <option value="">— Seleccionar etapa —</option>
                                @foreach($tiposTramites as $tt)
                                    <option value="{{ $tt['id'] }}">{{ $tt['nombre'] }}</option>
                                @endforeach
                            </select>
                            @error('tipoTramiteId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Tipo de targeting --}}
                        <div class="col-md-12 mb-4">
                            <label class="font-weight-bold mb-2 d-block" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#475569;">
                                Tipo de Destino <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex gap-3">
                                <label style="cursor:pointer;display:flex;align-items:center;gap:8px;padding:10px 16px;border-radius:8px;border:2px solid {{ $targetTipo==='rol' ? '#2563eb' : '#e2e8f0' }};background:{{ $targetTipo==='rol' ? '#eff6ff' : '#fff' }};transition:.15s;font-size:13px;">
                                    <input type="radio" wire:model="targetTipo" value="rol" style="accent-color:#2563eb;">
                                    <i class="fa fa-user-tag text-primary"></i> Rol específico
                                </label>
                                <label style="cursor:pointer;display:flex;align-items:center;gap:8px;padding:10px 16px;border-radius:8px;border:2px solid {{ $targetTipo==='area' ? '#16a34a' : '#e2e8f0' }};background:{{ $targetTipo==='area' ? '#f0fdf4' : '#fff' }};transition:.15s;font-size:13px;">
                                    <input type="radio" wire:model="targetTipo" value="area" style="accent-color:#16a34a;">
                                    <i class="fa fa-users text-success"></i> Área / Departamento
                                </label>
                            </div>
                        </div>

                        {{-- Rol --}}
                        @if($targetTipo === 'rol')
                            <div class="col-md-12 mb-4">
                                <label class="font-weight-bold mb-1" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#475569;">
                                    Rol <span class="text-danger">*</span>
                                </label>
                                <select wire:model="rolId"
                                        class="form-control @error('rolId') is-invalid @enderror"
                                        style="border-radius:8px; border-color:#e2e8f0; font-size:13px; height:40px;">
                                    <option value="">— Seleccionar rol —</option>
                                    @foreach($roles as $r)
                                        <option value="{{ $r['id'] }}">{{ $r['nombre'] }}</option>
                                    @endforeach
                                </select>
                                @error('rolId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        {{-- Ãrea --}}
                        @if($targetTipo === 'area')
                            <div class="col-md-12 mb-4">
                                @if(count($areas) === 0)
                                    <div class="alert alert-warning d-flex align-items-center justify-content-between py-2"
                                         style="border-radius:8px; font-size:12px;">
                                        <span><i class="fa fa-exclamation-triangle mr-1"></i> No hay áreas configuradas.</span>
                                        <a href="{{ route('configuracion.jerarquia') }}" class="btn btn-xs btn-warning ml-3" target="_blank">
                                            <i class="fa fa-sitemap mr-1"></i> Configurar
                                        </a>
                                    </div>
                                @else
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="font-weight-bold mb-1" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#475569;">
                                                Área <span class="text-danger">*</span>
                                            </label>
                                            <select wire:model="areaId"
                                                    class="form-control @error('areaId') is-invalid @enderror"
                                                    style="border-radius:8px; border-color:#e2e8f0; font-size:13px; height:40px;">
                                                <option value="">— Seleccionar área —</option>
                                                @foreach($areas as $a)
                                                    <option value="{{ $a['id'] }}">{{ $a['nombre'] }}</option>
                                                @endforeach
                                            </select>
                                            @error('areaId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="font-weight-bold mb-1" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#475569;">
                                                Nivel máximo
                                            </label>
                                            @if(count($niveles) === 0)
                                                <div class="alert alert-warning py-2 d-flex align-items-center justify-content-between"
                                                     style="border-radius:8px; font-size:12px;">
                                                    <span class="small"><i class="fa fa-exclamation-triangle mr-1"></i> Sin niveles.</span>
                                                    <a href="{{ route('configuracion.jerarquia') }}" target="_blank" class="btn btn-xs btn-warning ml-2">
                                                        <i class="fa fa-sitemap"></i> Configurar
                                                    </a>
                                                </div>
                                            @else
                                                <select wire:model="nivelMaxId"
                                                        class="form-control"
                                                        style="border-radius:8px; border-color:#e2e8f0; font-size:13px; height:40px;">
                                                    <option value="">— Todos los niveles —</option>
                                                    @foreach($niveles as $n)
                                                        <option value="{{ $n['id'] }}">{{ $n['nombre'] }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted" style="font-size:11px;">Ej: "Colaborador" → solo ese nivel y debajo.</small>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Separador --}}
                        <div class="col-md-12">
                            <div style="border-top:1px solid #f1f5f9; margin:4px 0 20px;"></div>
                        </div>

                        {{-- EscalaciÃ³n --}}
                        <div class="col-md-12 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3"
                                 style="background:#f8faff; border-radius:10px; border:1px solid #e8edf5;">
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa fa-arrow-up text-warning"></i>
                                        <strong style="font-size:13px; color:#1e293b;">Escalación automática</strong>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size:11px;">
                                        Si no se lee en N horas, se notificará al nivel superior.
                                    </small>
                                </div>
                                <label class="toggle-switch mb-0">
                                    <input type="checkbox" id="escalarActivo" wire:model="escalarActivo">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        @if($escalarActivo)
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold mb-1" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#475569;">
                                    Escalar después de (horas) <span class="text-danger">*</span>
                                </label>
                                <input type="number" wire:model="escalarHoras"
                                       class="form-control @error('escalarHoras') is-invalid @enderror"
                                       min="1" max="720" placeholder="Ej: 4"
                                       style="border-radius:8px; border-color:#e2e8f0; font-size:13px; height:40px;">
                                @error('escalarHoras') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold mb-1" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#475569;">
                                    Escalar al nivel <span class="text-danger">*</span>
                                </label>
                                @if(count($niveles) === 0)
                                    <div class="alert alert-warning py-2 mb-0 d-flex align-items-center justify-content-between"
                                         style="border-radius:8px; font-size:12px;">
                                        <span class="small"><i class="fa fa-exclamation-triangle mr-1"></i> Sin niveles.</span>
                                        <a href="{{ route('configuracion.jerarquia') }}" class="btn btn-xs btn-warning ml-2" target="_blank">
                                            <i class="fa fa-sitemap mr-1"></i> Configurar
                                        </a>
                                    </div>
                                @else
                                    <select wire:model="escalarNivelId"
                                            class="form-control @error('escalarNivelId') is-invalid @enderror"
                                            style="border-radius:8px; border-color:#e2e8f0; font-size:13px; height:40px;">
                                        <option value="">— Seleccionar nivel —</option>
                                        @foreach($niveles as $n)
                                            <option value="{{ $n['id'] }}">{{ $n['nombre'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('escalarNivelId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @endif
                            </div>
                        @endif

                        {{-- Regla activa --}}
                        <div class="col-md-12">
                            <div class="d-flex align-items-center justify-content-between p-3"
                                 style="background:#f8faff; border-radius:10px; border:1px solid #e8edf5;">
                                <div>
                                    <strong style="font-size:13px; color:#1e293b;">Regla activa</strong>
                                    <small class="text-muted d-block" style="font-size:11px;">Las reglas inactivas no generan notificaciones.</small>
                                </div>
                                <label class="toggle-switch mb-0">
                                    <input type="checkbox" id="activoSwitch" wire:model="activo">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e8edf5; padding:14px 24px; border-radius:0 0 16px 16px;">
                    <button wire:click="$set('showModal', false)"
                            class="btn btn-sm"
                            style="border-radius:8px; padding:7px 18px; font-size:13px; border:1.5px solid #e2e8f0; background:#fff; color:#475569;">
                        <i class="fa fa-times mr-1"></i> Cancelar
                    </button>
                    <button wire:click="guardar" wire:loading.attr="disabled"
                            class="btn btn-sm btn-primary"
                            style="border-radius:8px; padding:7px 22px; font-size:13px; font-weight:700;
                                   background:linear-gradient(135deg,#2563eb,#3b82f6); border:none;
                                   box-shadow:0 2px 8px rgba(37,99,235,.4);">
                        <span wire:loading.remove><i class="fa fa-save mr-1"></i> Guardar regla</span>
                        <span wire:loading><i class="fa fa-spinner fa-spin mr-1"></i> Guardando...</span>
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

</div>
