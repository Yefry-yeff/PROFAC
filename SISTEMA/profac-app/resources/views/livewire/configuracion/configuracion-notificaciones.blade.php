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
.cfg-warning { border-left:3px solid #f59e0b; background:#fffbeb; border-radius:0 10px 10px 0; padding:11px 16px; margin-bottom:18px; }
.cfg-warning-toggle { cursor:pointer; user-select:none; }

/* ── Section titles ── */
.cfg-section-ttl {
    display:flex; align-items:center; gap:8px;
    font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.7px;
    color:#64748b; margin-bottom:14px; padding-bottom:10px;
    border-bottom:2px solid #f1f5f9;
}
.cfg-section-ttl::before {
    content:''; display:block; width:3px; height:15px;
    border-radius:2px; background:linear-gradient(180deg,#3b82f6,#1d4ed8); flex-shrink:0;
}

/* ── Hero stat chips ── */
.cfg-stat-chip {
    display:inline-flex; align-items:center; gap:7px;
    background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2);
    border-radius:8px; padding:5px 13px; font-size:12px; color:#e2e8f0;
    backdrop-filter:blur(4px);
}
.cfg-stat-chip strong { color:#fff; font-size:14px; font-weight:800; }
.cfg-stat-chip i { opacity:.75; font-size:11px; }

/* ── Refined cards ── */
.notif-card {
    background:#fff; border-radius:12px;
    border:1px solid #e8edf5;
    box-shadow:0 1px 3px rgba(0,0,0,.03), 0 4px 14px rgba(0,0,0,.05);
    margin-bottom:14px; overflow:hidden;
}
.notif-card-header {
    padding:12px 20px; border-bottom:1px solid #e8edf5;
    display:flex; align-items:center; justify-content:space-between;
    background:linear-gradient(90deg,#f0f4ff,#f8faff);
}

/* ── Action buttons ── */
.cfg-btn-edit, .cfg-btn-del {
    width:28px; height:28px; border-radius:7px; border:none;
    display:inline-flex; align-items:center; justify-content:center;
    font-size:12px; cursor:pointer; transition:.15s; outline:none;
}
.cfg-btn-edit { background:#fef3c7; color:#92400e; }
.cfg-btn-edit:hover { background:#fde68a; }
.cfg-btn-del { background:#fee2e2; color:#dc2626; }
.cfg-btn-del:hover { background:#fecaca; }

/* ── Coverage badge ── */
.cfg-cov { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:700; }
.cfg-cov-ok  { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.cfg-cov-err { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }

/* ── Flash messages ── */
.cfg-flash-ok  { background:#f0fdf4; border:1.5px solid #bbf7d0; color:#15803d; border-radius:10px; padding:10px 16px; font-size:13px; display:flex; align-items:center; gap:10px; margin-bottom:14px; }
.cfg-flash-err { background:#fef2f2; border:1.5px solid #fecaca; color:#dc2626;  border-radius:10px; padding:10px 16px; font-size:13px; display:flex; align-items:center; gap:10px; margin-bottom:14px; }

/* ── Green alerts section ── */
.cfg-alerts-head {
    background:linear-gradient(135deg,#064e3b 0%,#065f46 55%,#059669 100%);
    padding:20px 28px; color:#fff; border-radius:12px 12px 0 0;
    position:relative; overflow:hidden;
}
.cfg-alerts-head::before {
    content:''; position:absolute; inset:0; pointer-events:none;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.cfg-alerts-body {
    background:#fff; border:1px solid #e2e8f0; border-top:none;
    border-radius:0 0 12px 12px; padding:22px;
    box-shadow:0 4px 16px rgba(0,0,0,.05);
}
.cfg-type-card {
    background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:10px;
    padding:12px 14px; display:flex; align-items:flex-start; gap:10px; transition:.15s;
}
.cfg-type-card:hover { border-color:#a7f3d0; box-shadow:0 2px 8px rgba(5,150,105,.08); }
.cfg-exec-ok {
    background:#f0fdf4; border:1.5px solid #bbf7d0; color:#065f46;
    border-radius:10px; padding:11px 16px; font-size:13px;
    display:flex; align-items:flex-start; gap:10px; margin-bottom:16px;
}

/* ── Alert table ── */
.notif-table-green thead th {
    font-size:10.5px; font-weight:700; text-transform:uppercase;
    letter-spacing:.6px; color:#374151;
    background:#f0fdf4; border-bottom:2px solid #a7f3d0; padding:9px 14px;
}
.notif-table-green tbody tr { transition:background .12s; }
.notif-table-green tbody tr:hover { background:#f0fdf4; }
.notif-table-green tbody td { padding:11px 14px; vertical-align:middle; font-size:13px; border-color:#ecfdf5; }

/* ── Form fields ── */
.cfg-field-lbl { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#475569; display:block; margin-bottom:5px; }
.cfg-field {
    display:block; width:100%; border-radius:8px; border:1.5px solid #e2e8f0;
    font-size:13px; padding:8px 12px; transition:.15s; background:#fff; color:#0f172a;
}
.cfg-field:focus { border-color:#3b82f6; outline:none; box-shadow:0 0 0 3px rgba(59,130,246,.1); }
.cfg-field.is-invalid { border-color:#ef4444; }
.cfg-active-box { display:flex; align-items:center; justify-content:space-between; background:#f8faff; border-radius:10px; border:1.5px solid #e8edf5; padding:12px 16px; }
    </style>


    {{-- â•â• HERO HEADER â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <div class="cfg-hero">
        <div style="position:relative;z-index:1;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2" style="background:transparent;padding:0;font-size:12px;opacity:.7;margin-bottom:8px;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-white">Inicio</a></li>
                    <li class="breadcrumb-item active text-white">Notificaciones</li>
                </ol>
            </nav>
            <div class="d-flex align-items-start justify-content-between flex-wrap" style="gap:16px;">
                <div>
                    <h2 class="mb-1 text-white font-weight-bold" style="font-size:1.45rem;letter-spacing:-.3px;">
                        <i class="fa fa-bell mr-2" style="opacity:.85;"></i> Configuración de Notificaciones
                    </h2>
                    <p class="mb-0" style="font-size:12px;opacity:.65;">Define quién recibe alertas en cada etapa del flujo de trabajo</p>
                    {{-- Stats chips --}}
                    @php
                        $totalReglasFlujo   = count($configs);
                        $totalAlertasCount  = count($alertasReglas);
                        $activasAlertas     = collect($alertasReglas)->where('activo', true)->count();
                    @endphp
                    <div class="d-flex flex-wrap mt-3" style="gap:8px;">
                        <span class="cfg-stat-chip">
                            <i class="fa fa-sitemap"></i>
                            <strong>{{ $totalReglasFlujo }}</strong> regla{{ $totalReglasFlujo !== 1 ? 's' : '' }} de flujo
                        </span>
                        <span class="cfg-stat-chip">
                            <i class="fa fa-line-chart" style="color:#6ee7b7;"></i>
                            <strong>{{ $activasAlertas }}</strong>/{{ $totalAlertasCount }} alertas activas
                        </span>
                        <span class="cfg-stat-chip" style="background:{{ $notificacionesActivas ? 'rgba(34,197,94,.2)' : 'rgba(239,68,68,.15)' }};border-color:{{ $notificacionesActivas ? 'rgba(74,222,128,.4)' : 'rgba(239,68,68,.3)' }};">
                            <i class="fa {{ $notificacionesActivas ? 'fa-check-circle' : 'fa-times-circle' }}" style="color:{{ $notificacionesActivas ? '#4ade80' : '#f87171' }};"></i>
                            Sistema {{ $notificacionesActivas ? 'activo' : 'desactivado' }}
                        </span>
                    </div>
                </div>
                <div class="d-flex align-items-center flex-wrap" style="gap:8px;margin-top:6px;">
                    @php $sinReglas = !$notificacionesActivas && empty($configs); @endphp
                    <button wire:click="toggleSistema"
                            @if($sinReglas) disabled title="Primero debes crear al menos una regla de notificación" @else title="{{ $notificacionesActivas ? 'Haz clic para desactivar' : 'Haz clic para activar' }}" @endif
                            style="border-radius:8px;padding:7px 16px;font-size:12px;font-weight:700;
                                   border:1.5px solid {{ $notificacionesActivas ? 'rgba(255,255,255,.45)' : ($sinReglas ? 'rgba(255,255,255,.12)' : 'rgba(255,255,255,.28)') }};
                                   background:{{ $notificacionesActivas ? 'rgba(34,197,94,.2)' : ($sinReglas ? 'rgba(255,255,255,.05)' : 'rgba(255,255,255,.1)') }};
                                   color:{{ $sinReglas ? 'rgba(255,255,255,.35)' : '#fff' }};
                                   cursor:{{ $sinReglas ? 'not-allowed' : 'pointer' }};backdrop-filter:blur(4px);">
                        <i class="fa {{ $notificacionesActivas ? 'fa-toggle-on' : 'fa-toggle-off' }} mr-1"
                           style="font-size:14px;color:{{ $notificacionesActivas ? '#4ade80' : ($sinReglas ? 'rgba(148,163,184,.35)' : '#94a3b8') }};"></i>
                        Notificaciones {{ $notificacionesActivas ? 'ON' : 'OFF' }}
                        @if($sinReglas)<i class="fa fa-lock ml-1" style="font-size:10px;opacity:.5;"></i>@endif
                    </button>
                    <button wire:click="nuevaRegla"
                            style="border-radius:8px;padding:7px 16px;font-size:12px;font-weight:700;
                                   background:rgba(255,255,255,.95);color:#1e40af;
                                   border:none;box-shadow:0 2px 8px rgba(0,0,0,.15);cursor:pointer;">
                        <i class="fa fa-plus mr-1"></i> Nueva Regla
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content" style="padding:24px 36px;">

        {{-- â”€â”€ Flash â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        @if(session('success'))
            <div class="cfg-flash-ok">
                <i class="fa fa-check-circle fa-lg" style="flex-shrink:0;"></i>
                <span style="flex:1;">{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#15803d;cursor:pointer;padding:0;"><i class="fa fa-times"></i></button>
            </div>
        @endif
        @if(session('error'))
            <div class="cfg-flash-err">
                <i class="fa fa-exclamation-circle fa-lg" style="flex-shrink:0;"></i>
                <span style="flex:1;">{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#dc2626;cursor:pointer;padding:0;"><i class="fa fa-times"></i></button>
            </div>
        @endif

        {{-- â”€â”€ Advertencia jerarquÃ­as (colapsable) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        @if(!empty($rolesIncompletos))
        <div x-data="{ expanded: false }" class="cfg-warning">
            <div class="d-flex align-items-center justify-content-between cfg-warning-toggle" @click="expanded = !expanded">
                <div class="d-flex align-items-center" style="gap:8px;">
                    <i class="fa fa-exclamation-triangle text-warning"></i>
                    <strong style="font-size:13px;color:#92400e;">{{ count($rolesIncompletos) }} roles con jerarquía incompleta</strong>
                    <span style="font-size:12px;color:#b45309;">— Las reglas por área no funcionarán para estos roles</span>
                </div>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <a href="{{ route('roles.gestion') }}"
                       class="btn btn-xs btn-warning"
                       style="border-radius:6px;font-size:11px;padding:3px 10px;"
                       @click.stop>
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
        {{-- Section title --}}
        <div class="cfg-section-ttl">
            <i class="fa fa-sitemap" style="font-size:11px;color:#3b82f6;"></i>
            Reglas de notificación por etapa de flujo
        </div>

        @php $grupos = collect($configs)->groupBy('tipo_tramite_id'); @endphp

        @foreach($grupos as $tipoId => $reglas)
            @php $tramiteNombre = $reglas->first()['tramite_nombre'] ?? 'Tramite #'.$tipoId; @endphp
            <div class="notif-card cfg-card">
                <div class="notif-card-header">
                    <div class="d-flex align-items-center" style="gap:10px;">
                        <span class="notif-badge-tipo">
                            <i class="fa fa-sitemap" style="font-size:9px;"></i> Flujo
                        </span>
                        <span style="font-size:14px;font-weight:700;color:#1e293b;">{{ $tramiteNombre }}</span>
                    </div>
                    <span style="font-size:11px;color:#94a3b8;font-weight:600;">
                        <i class="fa fa-list mr-1"></i>{{ $reglas->count() }} regla(s)
                    </span>
                </div>
                <div style="overflow:hidden;">
                    <table class="table table-hover mb-0 notif-table">
                        <thead>
                            <tr>
                                <th>Destino</th>
                                <th>Cobertura</th>
                                <th class="text-center" style="width:80px;">Activo</th>
                                <th class="text-center" style="width:80px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reglas as $reg)
                                <tr>
                                    <td>
                                        @if($reg['rol_nombre'])
                                            <span class="d-flex align-items-center" style="gap:8px;">
                                                <span style="width:28px;height:28px;background:linear-gradient(135deg,#eff6ff,#dbeafe);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                    <i class="fa fa-user-tag text-primary" style="font-size:11px;"></i>
                                                </span>
                                                <strong style="color:#1e293b;">{{ $reg['rol_nombre'] }}</strong>
                                            </span>
                                        @elseif($reg['area_nombre'])
                                            <span class="d-flex align-items-center" style="gap:8px;">
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
                                        @php $cob = $this->getCobertura($reg['id']); @endphp
                                        <span class="cfg-cov {{ $cob > 0 ? 'cfg-cov-ok' : 'cfg-cov-err' }}">
                                            <i class="fa {{ $cob > 0 ? 'fa-users' : 'fa-user-times' }}" style="font-size:11px;"></i>
                                            {{ $cob }} usuario(s)
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <label class="toggle-switch mb-0" title="{{ $reg['activo'] ? 'Desactivar' : 'Activar' }}">
                                            <input type="checkbox" wire:click="toggleActivo({{ $reg['id'] }})" {{ $reg['activo'] ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex" style="gap:5px;">
                                            <button wire:click="editarRegla({{ $reg['id'] }})" class="cfg-btn-edit" title="Editar">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <button wire:click="eliminar({{ $reg['id'] }})"
                                                    wire:confirm="¿Eliminar esta regla de notificación?"
                                                    class="cfg-btn-del" title="Eliminar">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
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
                    <div style="width:60px;height:60px;background:linear-gradient(135deg,#f1f5f9,#e2e8f0);border-radius:18px;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;">
                        <i class="fa fa-bell-slash" style="font-size:22px;color:#94a3b8;"></i>
                    </div>
                    <h5 style="color:#475569;font-weight:700;margin-bottom:6px;">Sin reglas configuradas</h5>
                    <p class="text-muted mb-4" style="font-size:13px;">Define quién debe recibir notificaciones en cada etapa del flujo.</p>
                    <button wire:click="nuevaRegla"
                            style="border-radius:8px;padding:8px 22px;font-size:13px;font-weight:700;background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;border:none;box-shadow:0 2px 8px rgba(37,99,235,.3);cursor:pointer;">
                        <i class="fa fa-plus mr-1"></i> Crear primera regla
                    </button>
                </div>
            </div>
        @endif

    </div>{{-- /wrapper-content --}}

    {{-- â•â• MODAL crear / editar â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if($showModal)
    <div tabindex="-1" role="dialog"
         style="position:fixed; top:0; left:0; width:100%; height:100vh; z-index:9999;
                background:rgba(2,8,23,.75); overflow-x:hidden; overflow-y:auto;">
        <div role="document"
             style="width:100%; max-width:720px; margin:4vh auto; padding:0 12px;">
            <div class="cfg-modal-content"
                 style="border:none; border-radius:16px;
                        box-shadow:0 25px 60px rgba(0,0,0,.4), 0 8px 24px rgba(0,0,0,.2);">

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
                     style="padding:24px;background:#fff;overflow-y:visible;">
                    <div class="row">

                        {{-- Estado del flujo --}}
                        <div class="col-md-12 mb-3">
                            <label class="cfg-field-lbl">
                                Estado de Flujo <span class="text-danger">*</span>
                            </label>
                            <select wire:model="tipoTramiteId"
                                    class="cfg-field @error('tipoTramiteId') is-invalid @enderror">
                                <option value="">— Seleccionar etapa —</option>
                                @foreach($tiposTramites as $tt)
                                    <option value="{{ $tt['id'] }}">{{ $tt['nombre'] }}</option>
                                @endforeach
                            </select>
                            @error('tipoTramiteId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Rol --}}
                        <div class="col-md-12 mb-3">
                            <label class="cfg-field-lbl">
                                Rol <span class="text-danger">*</span>
                            </label>
                            <select wire:model="rolId"
                                    class="cfg-field @error('rolId') is-invalid @enderror">
                                <option value="">— Seleccionar rol —</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r['id'] }}">{{ $r['nombre'] }}</option>
                                @endforeach
                            </select>
                            @error('rolId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Regla activa --}}
                        <div class="col-md-12">
                            <div class="cfg-active-box">
                                <div>
                                    <strong style="font-size:13px;color:#1e293b;">Regla activa</strong>
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
                <div class="modal-footer"
                     style="background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 100%);
                            border:none; padding:14px 24px; border-radius:0 0 16px 16px;
                            display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                    <button wire:click="$set('showModal', false)"
                            class="btn btn-sm"
                            style="border-radius:8px; padding:7px 18px; font-size:13px;
                                   border:1.5px solid rgba(255,255,255,.35); background:rgba(255,255,255,.1);
                                   color:#fff; backdrop-filter:blur(4px);">
                        <i class="fa fa-times mr-1"></i> Cancelar
                    </button>
                    <button wire:click="guardar" wire:loading.attr="disabled"
                            class="btn btn-sm"
                            style="border-radius:8px; padding:7px 22px; font-size:13px; font-weight:700;
                                   background:#fff; color:#1e40af; border:none;
                                   box-shadow:0 2px 12px rgba(0,0,0,.25);">
                        <span wire:loading.remove><i class="fa fa-save mr-1"></i> Guardar regla</span>
                        <span wire:loading><i class="fa fa-spinner fa-spin mr-1"></i> Guardando...</span>
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

    <script>
        (function () {
            function syncBodyScroll() {
                var hasModal = document.querySelector('[wire\\:id] [role="dialog"]');
                document.body.style.overflow = hasModal ? 'hidden' : '';
            }
            document.addEventListener('livewire:load', function () {
                Livewire.hook('message.processed', function () { syncBodyScroll(); });
            });
            syncBodyScroll();
        })();
    </script>

    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    {{--               ALERTAS INTELIGENTES DE ROTACIÓN E INVENTARIO             --}}
    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    <div style="animation:cfgFadeUp .3s .05s ease both;padding:0 36px 36px;">

        <div class="cfg-alerts-head">
            <div style="position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:11px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fa fa-line-chart" style="font-size:18px;color:#fff;"></i>
                    </div>
                    <div>
                        <h3 style="margin:0;font-size:16px;font-weight:800;letter-spacing:.2px;">Alertas Inteligentes de Rotación e Inventario</h3>
                        <p style="margin:0;font-size:12px;opacity:.75;">Notificaciones automáticas basadas en el comportamiento de los productos</p>
                    </div>
                </div>
                <button wire:click="alertaEjecutarAhora" wire:loading.attr="disabled"
                        style="background:rgba(255,255,255,.15);border:1.5px solid rgba(255,255,255,.38);color:#fff;border-radius:9px;padding:8px 18px;font-size:12px;font-weight:700;cursor:pointer;backdrop-filter:blur(4px);"
                        title="Evalúa todas las reglas activas ahora (para pruebas)">
                    <span wire:loading.remove wire:target="alertaEjecutarAhora"><i class="fa fa-play mr-1"></i> Ejecutar ahora</span>
                    <span wire:loading wire:target="alertaEjecutarAhora"><i class="fa fa-spinner fa-spin mr-1"></i> Enviando…</span>
                </button>
            </div>
        </div>

        <div class="cfg-alerts-body">

            {{-- Feedback ejecución --}}
            @if($alertaMensajeEjecucion)
                <div class="cfg-exec-ok">
                    <i class="fa fa-check-circle" style="font-size:16px;flex-shrink:0;margin-top:1px;"></i>
                    <span style="flex:1;">{{ $alertaMensajeEjecucion }}</span>
                    <button wire:click="$set('alertaMensajeEjecucion', null)" style="background:none;border:none;color:#065f46;cursor:pointer;padding:0;margin-left:auto;"><i class="fa fa-times"></i></button>
                </div>
            @endif
            @if(session('success_alertas'))
                <div class="cfg-flash-ok">
                    <i class="fa fa-check-circle fa-lg" style="flex-shrink:0;"></i>
                    <span>{{ session('success_alertas') }}</span>
                </div>
            @endif

            {{-- Cards resumen por tipo --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:12px;margin-bottom:22px;">
                @foreach($tiposAlertas as $tKey => $tInfo)
                    @php
                        $tCount   = collect($alertasReglas)->where('tipo', $tKey)->count();
                        $tActivas = collect($alertasReglas)->where('tipo', $tKey)->where('activo', true)->count();
                    @endphp
                    <div class="cfg-type-card">
                        <div style="width:32px;height:32px;border-radius:8px;background:#ecfdf5;border:1px solid #a7f3d0;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fa fa-bell" style="color:#059669;font-size:13px;"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:12px;font-weight:700;color:#111827;margin-bottom:2px;">{{ $tInfo['label'] }}</div>
                            <div style="font-size:11px;color:#64748b;line-height:1.4;">{{ $tInfo['desc'] }}</div>
                            <div style="margin-top:6px;font-size:10px;color:#6b7280;">
                                {{ $tCount }} regla{{ $tCount !== 1 ? 's' : '' }}
                                @if($tActivas > 0)
                                    · <span style="color:#059669;font-weight:700;">{{ $tActivas }} activa{{ $tActivas !== 1 ? 's' : '' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Tabla de reglas --}}
            @if(count($alertasReglas) > 0)
                <div style="border:1.5px solid #d1fae5;border-radius:10px;overflow:hidden;">
                    <table class="table table-borderless notif-table-green mb-0">
                        <thead>
                            <tr>
                                <th>Regla</th>
                                <th>Tipo</th>
                                <th>Parámetro</th>
                                <th>Prioridad</th>
                                <th>Destinatario</th>
                                <th class="text-center">Activo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alertasReglas as $ar)
                                <tr wire:key="ar-{{ $ar['id'] }}">
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div style="width:30px;height:30px;border-radius:8px;flex-shrink:0;background:{{ $ar['color'] }}18;border:1.5px solid {{ $ar['color'] }}35;display:flex;align-items:center;justify-content:center;">
                                                <i class="fa {{ $ar['icono'] }}" style="color:{{ $ar['color'] }};font-size:12px;"></i>
                                            </div>
                                            <span style="font-size:13px;font-weight:700;color:#0f172a;">{{ $ar['nombre'] }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="display:inline-flex;align-items:center;gap:4px;background:#f0fdf4;border:1px solid #bbf7d0;color:#065f46;font-size:10px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;padding:3px 8px;border-radius:20px;">
                                            {{ $tiposAlertas[$ar['tipo']]['label'] ?? $ar['tipo'] }}
                                        </span>
                                    </td>
                                    <td style="font-size:12px;color:#475569;">{{ $ar['descripcion'] }}</td>
                                    <td>
                                        <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;padding:3px 8px;border-radius:20px;background:{{ $ar['prioridad_color'] }}18;color:{{ $ar['prioridad_color'] }};border:1px solid {{ $ar['prioridad_color'] }}30;">
                                            {{ $ar['prioridad_label'] }}
                                        </span>
                                    </td>
                                    <td style="font-size:12px;color:#374151;">
                                        @if($ar['rol_nombre'])
                                            <i class="fa fa-user mr-1" style="color:#6366f1;"></i>{{ $ar['rol_nombre'] }}
                                        @elseif($ar['area_nombre'])
                                            <i class="fa fa-building mr-1" style="color:#0891b2;"></i>{{ $ar['area_nombre'] }}
                                        @else
                                            <span style="font-size:11px;color:#94a3b8;">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <label class="toggle-switch mb-0">
                                            <input type="checkbox"
                                                   wire:click="alertaToggleActivo({{ $ar['id'] }})"
                                                   {{ $ar['activo'] ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align:center;padding:36px;border:2px dashed #d1fae5;border-radius:12px;background:#f0fdf4;">
                    <i class="fa fa-bell-slash" style="font-size:30px;color:#a7f3d0;margin-bottom:10px;display:block;"></i>
                    <p style="font-size:14px;font-weight:700;color:#065f46;margin-bottom:4px;">Sin reglas configuradas</p>
                    <p style="font-size:12px;color:#6b7280;margin:0;">No hay reglas de alerta predefinidas registradas en el sistema.</p>
                </div>
            @endif

            <div style="margin-top:18px;padding:12px 16px;background:#fffbeb;border:1.5px solid #fde68a;border-radius:9px;display:flex;gap:10px;align-items:flex-start;">
                <i class="fa fa-info-circle" style="color:#d97706;font-size:14px;margin-top:1px;flex-shrink:0;"></i>
                <div style="font-size:12px;color:#78350f;line-height:1.55;">
                    <strong>Ejecución automática:</strong> Las alertas se evalúan diariamente a las 06:00 mediante el scheduler de Laravel.
                    Asegúrate de que <code style="background:#fef3c7;border-radius:4px;padding:1px 5px;">php artisan schedule:run</code> esté en el cron del servidor.
                    Usa <em>Ejecutar ahora</em> para probar sin esperar (requiere cola activa).
                </div>
            </div>

        </div>
    </div>

    {{-- ══ MODAL ALERTA: crear / editar ═══════════════════════════════════════ --}}
    @if($showAlertaModal)
    <div tabindex="-1" role="dialog"
         style="position:fixed;top:0;left:0;width:100%;height:100vh;z-index:9999;background:rgba(2,8,23,.75);overflow-x:hidden;overflow-y:auto;">
        <div role="document" style="width:100%;max-width:580px;margin:4vh auto;padding:0 12px;">
            <div style="background:#fff;border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.25);">

                {{-- Header --}}
                <div style="background:linear-gradient(135deg,#064e3b 0%,#059669 100%);color:#fff;padding:18px 24px;border-radius:14px 14px 0 0;display:flex;align-items:center;gap:10px;">
                    <div style="width:34px;height:34px;border-radius:9px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;">
                        <i class="fa fa-bell" style="color:#fff;"></i>
                    </div>
                    <div>
                        <h5 style="margin:0;font-size:15px;font-weight:800;">{{ $alertaEditandoId ? 'Editar regla de alerta' : 'Nueva regla de alerta' }}</h5>
                        <p style="margin:0;font-size:11px;opacity:.8;">Configura cuándo y a quién notificar</p>
                    </div>
                </div>

                {{-- Body --}}
                <div style="padding:24px;max-height:70vh;overflow-y:auto;">

                    {{-- Nombre --}}
                    <div class="form-group mb-3">
                        <label class="cfg-field-lbl">Nombre de la regla <span style="color:#ef4444;">*</span></label>
                        <input type="text" wire:model.defer="alertaNombre" class="cfg-field"
                               placeholder="Ej: Recuperación próxima — 15 días">
                        @error('alertaNombre') <small style="color:#ef4444;">{{ $message }}</small> @enderror
                    </div>

                    {{-- Tipo --}}
                    <div class="form-group mb-3">
                        <label class="cfg-field-lbl">Tipo de alerta <span style="color:#ef4444;">*</span></label>
                        <select wire:model="alertaTipo" class="cfg-field">
                            <option value="">— Selecciona un tipo —</option>
                            @foreach($tiposAlertas as $tKey => $tInfo)
                                <option value="{{ $tKey }}">{{ $tInfo['label'] }}</option>
                            @endforeach
                        </select>
                        @if($alertaTipo && isset($tiposAlertas[$alertaTipo]))
                            <small style="color:#059669;font-size:11px;margin-top:4px;display:block;">
                                <i class="fa fa-info-circle mr-1"></i>{{ $tiposAlertas[$alertaTipo]['desc'] }}
                            </small>
                        @endif
                        @error('alertaTipo') <small style="color:#ef4444;">{{ $message }}</small> @enderror
                    </div>

                    {{-- Parámetros dinámicos --}}
                    @if($alertaTipo && isset($tiposAlertas[$alertaTipo]))
                        @if($tiposAlertas[$alertaTipo]['param_dias'])
                            <div class="form-group mb-3">
                                <label class="cfg-field-lbl">
                                    {{ $tiposAlertas[$alertaTipo]['param_dias_label'] }} <span style="color:#ef4444;">*</span>
                                </label>
                                <input type="number" wire:model.defer="alertaParametroDias" class="cfg-field"
                                       min="1" max="365" placeholder="Ej: 15">
                                @error('alertaParametroDias') <small style="color:#ef4444;">{{ $message }}</small> @enderror
                            </div>
                        @endif
                        @if($tiposAlertas[$alertaTipo]['param_umbral'])
                            <div class="form-group mb-3">
                                <label class="cfg-field-lbl">
                                    {{ $tiposAlertas[$alertaTipo]['param_umbral_label'] }} <span style="color:#ef4444;">*</span>
                                </label>
                                <input type="number" wire:model.defer="alertaParametroUmbral" class="cfg-field"
                                       step="0.1" min="0" placeholder="Ej: 6">
                                @error('alertaParametroUmbral') <small style="color:#ef4444;">{{ $message }}</small> @enderror
                            </div>
                        @endif
                    @endif

                    {{-- Prioridad --}}
                    <div class="form-group mb-3">
                        <label class="cfg-field-lbl">Prioridad</label>
                        <select wire:model.defer="alertaPrioridad" class="cfg-field">
                            <option value="informativa">Informativa</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                            <option value="critica">Crítica</option>
                        </select>
                    </div>

                    {{-- Destinatario (rol / área) --}}
                    <div class="form-group mb-3">
                        <label class="cfg-field-lbl">Destinatario</label>
                        <div style="display:flex;border:1.5px solid #d1d5db;border-radius:8px;overflow:hidden;margin-bottom:8px;">
                            <button type="button" wire:click="$set('alertaTargetTipo', 'rol')"
                                    style="flex:1;padding:8px 12px;font-size:12px;font-weight:600;border:none;cursor:pointer;{{ $alertaTargetTipo === 'rol' ? 'background:#059669;color:#fff;' : 'background:transparent;color:#6b7280;' }}">
                                <i class="fa fa-user mr-1"></i> Por rol
                            </button>
                            <button type="button" wire:click="$set('alertaTargetTipo', 'area')"
                                    style="flex:1;padding:8px 12px;font-size:12px;font-weight:600;border:none;cursor:pointer;{{ $alertaTargetTipo === 'area' ? 'background:#059669;color:#fff;' : 'background:transparent;color:#6b7280;' }}">
                                <i class="fa fa-building mr-1"></i> Por área
                            </button>
                        </div>
                        @if($alertaTargetTipo === 'rol')
                            <select wire:model.defer="alertaRolId" class="cfg-field">
                                <option value="">— Selecciona un rol —</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol['id'] }}">{{ $rol['nombre'] }}</option>
                                @endforeach
                            </select>
                        @else
                            <select wire:model.defer="alertaAreaId" class="cfg-field">
                                <option value="">— Selecciona un área —</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area['id'] }}">{{ $area['nombre'] }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    {{-- Icono + color --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:12px;">
                        <div>
                            <label class="cfg-field-lbl">Ícono</label>
                            <input type="text" wire:model.defer="alertaIcono" class="cfg-field" placeholder="fa-bell">
                            <small style="font-size:11px;color:#94a3b8;">Clase FontAwesome 4</small>
                        </div>
                        <div>
                            <label class="cfg-field-lbl">Color</label>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="color" wire:model.defer="alertaColor"
                                       style="width:36px;height:36px;border:none;cursor:pointer;border-radius:6px;padding:2px;">
                                <input type="text" wire:model.defer="alertaColor"
                                       class="cfg-field" style="flex:1;"
                                       placeholder="#f59e0b">
                            </div>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px;">
                                @foreach(['#ef4444','#f97316','#f59e0b','#22c55e','#3b82f6','#8b5cf6','#06b6d4'] as $pc)
                                    <div wire:click="$set('alertaColor', '{{ $pc }}')"
                                         style="width:22px;height:22px;border-radius:5px;background:{{ $pc }};cursor:pointer;border:2px solid {{ $alertaColor === $pc ? '#0f172a' : 'transparent' }};transition:.1s;"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Estado activo --}}
                    <div class="cfg-active-box">
                        <div>
                            <strong style="font-size:13px;color:#1e293b;">Regla activa</strong>
                            <small class="text-muted d-block" style="font-size:11px;">Las reglas inactivas no generan alertas.</small>
                        </div>
                        <label class="toggle-switch mb-0">
                            <input type="checkbox" wire:model.defer="alertaActivo">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                </div>

                {{-- Footer --}}
                <div style="padding:14px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;border-radius:0 0 14px 14px;display:flex;justify-content:flex-end;gap:10px;">
                    <button wire:click="$set('showAlertaModal', false)"
                            class="btn btn-sm"
                            style="border-radius:8px;padding:7px 18px;font-size:13px;border:1.5px solid #e2e8f0;background:#f1f5f9;color:#64748b;">
                        <i class="fa fa-times mr-1"></i> Cancelar
                    </button>
                    <button wire:click="alertaGuardar" wire:loading.attr="disabled"
                            class="btn btn-sm"
                            style="border-radius:8px;padding:7px 22px;font-size:13px;font-weight:700;background:linear-gradient(135deg,#065f46,#059669);color:#fff;border:none;box-shadow:0 2px 10px rgba(5,150,105,.35);">
                        <span wire:loading.remove wire:target="alertaGuardar">
                            <i class="fa fa-save mr-1"></i> {{ $alertaEditandoId ? 'Actualizar' : 'Crear regla' }}
                        </span>
                        <span wire:loading wire:target="alertaGuardar">
                            <i class="fa fa-spinner fa-spin mr-1"></i> Guardando…
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

</div>
