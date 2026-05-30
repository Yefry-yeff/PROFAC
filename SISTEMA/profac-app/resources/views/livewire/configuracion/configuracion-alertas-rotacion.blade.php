<div>
<style>
/* ── Alertas rotación — estilos compartidos con cfg-* del componente padre ── */
@keyframes arFadeUp {
    from { opacity:0; transform:translateY(12px); }
    to   { opacity:1; transform:translateY(0); }
}
.ar-section {
    animation: arFadeUp .3s ease both;
    margin-top: 32px;
}
.ar-section-header {
    background: linear-gradient(135deg,#064e3b 0%,#065f46 60%,#059669 100%);
    padding: 22px 28px 18px;
    color: #fff;
    border-radius: 12px 12px 0 0;
    position: relative;
    overflow: hidden;
}
.ar-section-header::before {
    content:'';
    position:absolute; inset:0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.ar-section-body {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-top: none;
    border-radius: 0 0 12px 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}

/* ── Tabla ─────────────────────────────────────────────────────────────────── */
.ar-table thead th {
    font-size: 10.5px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .6px; color: #64748b;
    background: #f8fafc; border-bottom: 2px solid #e2e8f0;
    padding: 10px 14px;
}
.ar-table tbody tr { transition: background .1s; }
.ar-table tbody tr:hover { background: #f0fdf4; }
.ar-table tbody td { padding: 11px 14px; vertical-align: middle; font-size: 13px; border-color: #f0f4f8; }

/* ── Badges ─────────────────────────────────────────────────────────────────── */
.ar-badge-tipo {
    display: inline-flex; align-items: center; gap: 5px;
    background: #f0fdf4; border: 1px solid #bbf7d0;
    color: #065f46; font-size: 10px; font-weight: 700;
    letter-spacing: .4px; text-transform: uppercase;
    padding: 3px 9px; border-radius: 20px;
}
.ar-badge-prio {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase;
    padding: 3px 9px; border-radius: 20px;
}
.ar-badge-critica  { background: rgba(239,68,68,.12);  color: #b91c1c; border: 1px solid rgba(239,68,68,.25); }
.ar-badge-alta     { background: rgba(249,115,22,.12); color: #c2410c; border: 1px solid rgba(249,115,22,.25); }
.ar-badge-media    { background: rgba(245,158,11,.12); color: #92400e; border: 1px solid rgba(245,158,11,.25); }
.ar-badge-informativa { background: rgba(99,102,241,.12); color: #4338ca; border: 1px solid rgba(99,102,241,.25); }

/* ── Toggle switch (mismo estilo que componente padre) ─────────────────────── */
.ar-toggle { position:relative; display:inline-block; width:36px; height:20px; }
.ar-toggle input { opacity:0; width:0; height:0; }
.ar-toggle-slider {
    position:absolute; inset:0; cursor:pointer;
    background:#cbd5e1; border-radius:20px; transition:.25s;
}
.ar-toggle-slider::before {
    content:''; position:absolute;
    left:3px; bottom:3px; width:14px; height:14px;
    background:#fff; border-radius:50%; transition:.25s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.ar-toggle input:checked + .ar-toggle-slider { background:#10b981; }
.ar-toggle input:checked + .ar-toggle-slider::before { transform:translateX(16px); }

/* ── Botones de acción ─────────────────────────────────────────────────────── */
.ar-btn-edit {
    background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8;
    border-radius: 7px; padding: 5px 10px; font-size: 12px; cursor: pointer; transition: .15s;
}
.ar-btn-edit:hover { background: #dbeafe; }
.ar-btn-del {
    background: #fff1f2; border: 1px solid #fecdd3; color: #be123c;
    border-radius: 7px; padding: 5px 10px; font-size: 12px; cursor: pointer; transition: .15s;
}
.ar-btn-del:hover { background: #ffe4e6; }

/* ── Modal ──────────────────────────────────────────────────────────────────── */
.ar-modal-backdrop {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.45); backdrop-filter: blur(3px);
    z-index: 1060; display: flex; align-items: center; justify-content: center;
    padding: 16px;
}
.ar-modal {
    background: #fff; border-radius: 14px; width: 100%; max-width: 560px;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
    animation: arFadeUp .2s ease-out both;
    display: flex; flex-direction: column; max-height: 90vh;
}
.ar-modal-header {
    background: linear-gradient(135deg,#064e3b 0%,#059669 100%);
    color: #fff; padding: 18px 24px; border-radius: 14px 14px 0 0;
    flex-shrink: 0;
}
.ar-modal-body {
    padding: 24px; overflow-y: auto; flex: 1;
    scrollbar-width: thin; scrollbar-color: #d1d5db transparent;
}
.ar-modal-footer {
    padding: 14px 24px; background: #f8fafc;
    border-top: 1px solid #e2e8f0; border-radius: 0 0 14px 14px;
    display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;
}

/* ── Form helpers ────────────────────────────────────────────────────────────── */
.ar-label { font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 5px; display: block; }
.ar-input {
    width: 100%; padding: 8px 12px; border: 1.5px solid #d1d5db; border-radius: 8px;
    font-size: 13px; color: #111827; transition: border .15s; outline: none;
}
.ar-input:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.12); }
.ar-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath d='M6 8L1 3h10z' fill='%236b7280'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px; }
.ar-segment {
    display: flex; border: 1.5px solid #d1d5db; border-radius: 8px; overflow: hidden;
}
.ar-segment button {
    flex: 1; padding: 8px 12px; font-size: 12px; font-weight: 600;
    border: none; background: transparent; color: #6b7280; cursor: pointer; transition: .15s;
}
.ar-segment button.active { background: #059669; color: #fff; }

/* ── Ejecución alert ─────────────────────────────────────────────────────────── */
.ar-exec-alert {
    background: #f0fdf4; border: 1.5px solid #bbf7d0; color: #065f46;
    border-radius: 10px; padding: 12px 16px; font-size: 13px;
    display: flex; align-items: flex-start; gap: 10px; margin-bottom: 16px;
}

/* ── Color swatch ────────────────────────────────────────────────────────────── */
.ar-color-row { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px; }
.ar-swatch {
    width: 28px; height: 28px; border-radius: 6px; cursor: pointer;
    border: 2px solid transparent; transition: .15s;
}
.ar-swatch:hover, .ar-swatch.selected { border-color: #0f172a; transform: scale(1.12); }
</style>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{--                   ALERTAS INTELIGENTES DE ROTACIÓN E INVENTARIO            --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="ar-section">

    {{-- ── Cabecera ── --}}
    <div class="ar-section-header" style="position:relative;">
        <div style="position:relative; z-index:1; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px;">
            <div>
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:4px;">
                    <div style="width:38px; height:38px; border-radius:10px; background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center;">
                        <i class="fa fa-bell" style="font-size:17px; color:#fff;"></i>
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:16px; font-weight:800; letter-spacing:.3px;">Alertas Inteligentes de Rotación e Inventario</h3>
                        <p style="margin:0; font-size:12px; opacity:.8;">Notificaciones automáticas basadas en el comportamiento de los productos</p>
                    </div>
                </div>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                {{-- Ejecutar ahora --}}
                <button wire:click="ejecutarAhora"
                        wire:loading.attr="disabled"
                        style="background:rgba(255,255,255,.15); border:1.5px solid rgba(255,255,255,.4);
                               color:#fff; border-radius:9px; padding:8px 16px; font-size:12px;
                               font-weight:700; cursor:pointer; backdrop-filter:blur(4px); transition:.15s;"
                        title="Evalúa todas las reglas ahora (para pruebas)">
                    <span wire:loading.remove wire:target="ejecutarAhora">
                        <i class="fa fa-play mr-1"></i> Ejecutar ahora
                    </span>
                    <span wire:loading wire:target="ejecutarAhora">
                        <i class="fa fa-spinner fa-spin mr-1"></i> Evaluando…
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Cuerpo ── --}}
    <div class="ar-section-body">

        {{-- Feedback ejecutar --}}
        @if($mensajeEjecucion)
            <div class="ar-exec-alert">
                <i class="fa fa-check-circle" style="font-size:16px; margin-top:1px;"></i>
                <span>{{ $mensajeEjecucion }}</span>
                <button wire:click="$set('mensajeEjecucion', null)"
                        style="margin-left:auto; background:none; border:none; color:#065f46; cursor:pointer; font-size:14px;">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        @endif

        {{-- Flash mensajes --}}
        @if(session('success_alertas'))
            <div class="alert alert-success" style="font-size:13px; border-radius:9px; margin-bottom:16px;">
                <i class="fa fa-check-circle mr-2"></i>{{ session('success_alertas') }}
            </div>
        @endif
        @if(session('error_alertas'))
            <div class="alert alert-danger" style="font-size:13px; border-radius:9px; margin-bottom:16px;">
                <i class="fa fa-lock mr-2"></i>{{ session('error_alertas') }}
            </div>
        @endif

        {{-- Explicación de categorías --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:12px; margin-bottom:24px;">
            @foreach($tiposAlertas as $key => $info)
                @php
                    $count = collect($reglas)->where('tipo', $key)->count();
                    $activas = collect($reglas)->where('tipo', $key)->where('activo', true)->count();
                @endphp
                <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:10px; padding:12px 14px;
                            display:flex; align-items:flex-start; gap:10px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:#ecfdf5; border:1px solid #a7f3d0;
                                display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fa fa-bell" style="color:#059669; font-size:13px;"></i>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:12px; font-weight:700; color:#111827; margin-bottom:2px;">{{ $info['label'] }}</div>
                        <div style="font-size:11px; color:#64748b; line-height:1.4;">{{ $info['desc'] }}</div>
                        <div style="margin-top:6px; display:flex; gap:6px; align-items:center;">
                            <span style="font-size:10px; color:#6b7280;">
                                {{ $count }} regla{{ $count !== 1 ? 's' : '' }}
                                @if($activas > 0)
                                    · <span style="color:#059669; font-weight:700;">{{ $activas }} activa{{ $activas !== 1 ? 's' : '' }}</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Tabla de reglas --}}
        @if(count($reglas) > 0)
            <div style="border:1.5px solid #e2e8f0; border-radius:10px; overflow:hidden;">
                <table class="table table-borderless ar-table mb-0">
                    <thead>
                        <tr>
                            <th>Regla</th>
                            <th>Tipo</th>
                            <th>Parámetro</th>
                            <th>Prioridad</th>
                            <th>Destinatario</th>
                            <th>Estado datos</th>
                            <th style="text-align:center;">Activo</th>
                            <th style="text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reglas as $r)
                            @php
                                $viab = $viabilidad[$r['tipo']] ?? ['estado'=>'ok','label'=>'','puede_activar'=>true];
                            @endphp
                            <tr style="{{ $viab['estado'] === 'lock' ? 'background:#fafafa; opacity:.85;' : '' }}">
                                {{-- Nombre + icono + color --}}
                                <td>
                                    <div style="display:flex; align-items:center; gap:9px;">
                                        <div style="width:30px; height:30px; border-radius:8px; flex-shrink:0;
                                                    background:{{ $r['color'] }}18; border:1.5px solid {{ $r['color'] }}35;
                                                    display:flex; align-items:center; justify-content:center;">
                                            <i class="fa {{ $r['icono'] }}" style="color:{{ $r['color'] }}; font-size:12px;"></i>
                                        </div>
                                        <div>
                                            <div style="font-size:13px; font-weight:700; color:#0f172a;">{{ $r['nombre'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                {{-- Tipo --}}
                                <td>
                                    <span class="ar-badge-tipo">
                                        {{ $tiposAlertas[$r['tipo']]['label'] ?? $r['tipo'] }}
                                    </span>
                                </td>
                                {{-- Parámetro --}}
                                <td>
                                    <span style="font-size:12px; color:#475569;">{{ $r['descripcion'] }}</span>
                                </td>
                                {{-- Prioridad --}}
                                <td>
                                    <span class="ar-badge-prio ar-badge-{{ $r['prioridad'] }}">
                                        {{ $r['prioridad_label'] }}
                                    </span>
                                </td>
                                {{-- Destinatario --}}
                                <td>
                                    @if($r['rol_nombre'])
                                        <span style="font-size:12px; color:#374151;">
                                            <i class="fa fa-user mr-1" style="color:#6366f1;"></i>{{ $r['rol_nombre'] }}
                                        </span>
                                    @elseif($r['area_nombre'])
                                        <span style="font-size:12px; color:#374151;">
                                            <i class="fa fa-building mr-1" style="color:#0891b2;"></i>{{ $r['area_nombre'] }}
                                        </span>
                                    @else
                                        <span style="font-size:11px; color:#94a3b8;">Sin asignar</span>
                                    @endif
                                </td>
                                {{-- Estado datos --}}
                                <td>
                                    @if($viab['estado'] === 'ok')
                                        <span style="display:inline-flex;align-items:center;gap:5px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;">
                                            <i class="fa fa-check-circle"></i> Puede disparar
                                        </span>
                                        <div style="font-size:10px;color:#64748b;margin-top:3px;">{{ $viab['label'] }}</div>
                                    @elseif($viab['estado'] === 'warn')
                                        <span style="display:inline-flex;align-items:center;gap:5px;background:#fffbeb;border:1px solid #fde68a;color:#b45309;font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;">
                                            <i class="fa fa-exclamation-triangle"></i> Datos limitados
                                        </span>
                                        <div style="font-size:10px;color:#64748b;margin-top:3px;">{{ $viab['label'] }}</div>
                                    @else
                                        <span style="display:inline-flex;align-items:center;gap:5px;background:#fef2f2;border:1px solid #fecaca;color:#dc2626;font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;">
                                            <i class="fa fa-lock"></i> Sin datos
                                        </span>
                                        <div style="font-size:10px;color:#94a3b8;margin-top:3px;">{{ $viab['label'] }}</div>
                                    @endif
                                </td>
                                {{-- Toggle activo --}}
                                <td style="text-align:center;">
                                    @if($viab['puede_activar'])
                                        <label class="ar-toggle">
                                            <input type="checkbox"
                                                   wire:click="toggleActivo({{ $r['id'] }})"
                                                   {{ $r['activo'] ? 'checked' : '' }}>
                                            <span class="ar-toggle-slider"></span>
                                        </label>
                                    @else
                                        <span title="{{ $viab['label'] }}"
                                              style="display:inline-flex;align-items:center;justify-content:center;width:38px;height:20px;background:#f1f5f9;border-radius:99px;cursor:not-allowed;"
                                              wire:click="toggleActivo({{ $r['id'] }})">
                                            <i class="fa fa-lock" style="font-size:11px;color:#94a3b8;"></i>
                                        </span>
                                    @endif
                                </td>
                                {{-- Acciones --}}
                                <td style="text-align:center;">
                                    <div style="display:flex; gap:6px; justify-content:center;">
                                        <button wire:click="editar({{ $r['id'] }})" class="ar-btn-edit" title="Editar parámetros">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <a href="{{ route('alertas.rotacion.reporte', $r['id']) }}"
                                           style="display:inline-flex; align-items:center; justify-content:center;
                                                  width:30px; height:30px; border-radius:7px;
                                                  background:#ecfdf5; border:1.5px solid #a7f3d0;
                                                  color:#059669; font-size:12px; text-decoration:none;"
                                           title="Ver reporte">
                                            <i class="fa fa-bar-chart"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align:center; padding:36px; border:2px dashed #d1fae5; border-radius:12px; background:#f0fdf4;">
                <i class="fa fa-bell-slash" style="font-size:32px; color:#a7f3d0; margin-bottom:12px; display:block;"></i>
                <p style="font-size:14px; font-weight:700; color:#065f46; margin-bottom:4px;">Sin reglas configuradas</p>
                <p style="font-size:12px; color:#6b7280;">
                    No hay reglas de alerta predefinidas registradas en el sistema.
                </p>
            </div>
        @endif

        {{-- Nota sobre el scheduler --}}
        <div style="margin-top:20px; padding:12px 16px; background:#fffbeb; border:1.5px solid #fde68a;
                    border-radius:10px; display:flex; gap:10px; align-items:flex-start;">
            <i class="fa fa-info-circle" style="color:#d97706; font-size:15px; margin-top:1px; flex-shrink:0;"></i>
            <div style="font-size:12px; color:#78350f; line-height:1.5;">
                <strong>Ejecución automática:</strong> Las alertas se evalúan diariamente a medianoche mediante el scheduler de Laravel.
                Asegúrate de que el comando <code style="background:#fef3c7; border-radius:4px; padding:1px 5px;">php artisan schedule:run</code>
                esté registrado como tarea cron en el servidor. Usa el botón <em>Ejecutar ahora</em> para probar sin esperar.
            </div>
        </div>

    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{--                              MODAL CREAR / EDITAR                          --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
@if($showModal)
    <div class="ar-modal-backdrop" wire:click.self="$set('showModal', false)">
        <div class="ar-modal">

            {{-- Header --}}
            <div class="ar-modal-header">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:34px; height:34px; border-radius:9px; background:rgba(255,255,255,.2);
                                display:flex; align-items:center; justify-content:center;">
                        <i class="fa fa-bell" style="color:#fff;"></i>
                    </div>
                    <div>
                        <h5 style="margin:0; font-size:15px; font-weight:800;">
                            {{ $editandoId ? 'Editar regla de alerta' : 'Nueva regla de alerta' }}
                        </h5>
                        <p style="margin:0; font-size:11px; opacity:.8;">Configura cuándo y a quién notificar</p>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div class="ar-modal-body">

                {{-- Nombre --}}
                <div class="form-group mb-3">
                    <label class="ar-label">Nombre de la regla <span style="color:#ef4444;">*</span></label>
                    <input type="text" class="ar-input" wire:model.defer="nombre"
                           placeholder="Ej: Recuperación próxima — 15 días">
                    @error('nombre') <small style="color:#ef4444;">{{ $message }}</small> @enderror
                </div>

                {{-- Tipo --}}
                <div class="form-group mb-3">
                    <label class="ar-label">Tipo de alerta <span style="color:#ef4444;">*</span></label>
                    <select class="ar-input ar-select" wire:model="tipo">
                        <option value="">— Selecciona un tipo —</option>
                        @foreach($tiposAlertas as $key => $info)
                            <option value="{{ $key }}">{{ $info['label'] }}</option>
                        @endforeach
                    </select>
                    @if($tipo && isset($tiposAlertas[$tipo]))
                        <small style="color:#059669; font-size:11px; margin-top:4px; display:block;">
                            <i class="fa fa-info-circle mr-1"></i>{{ $tiposAlertas[$tipo]['desc'] }}
                        </small>
                    @endif
                    @error('tipo') <small style="color:#ef4444;">{{ $message }}</small> @enderror
                </div>

                {{-- Parámetros según tipo --}}
                @if($tipo && isset($tiposAlertas[$tipo]))
                    @if($tiposAlertas[$tipo]['param_dias'])
                        <div class="form-group mb-3">
                            <label class="ar-label">
                                {{ $tiposAlertas[$tipo]['param_dias_label'] }}
                                <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="number" class="ar-input" wire:model.defer="parametroDias"
                                   min="1" max="365" placeholder="Ej: 15">
                            @error('parametroDias') <small style="color:#ef4444;">{{ $message }}</small> @enderror
                        </div>
                    @endif
                    @if($tiposAlertas[$tipo]['param_umbral'] ?? false)
                        <div class="form-group mb-3">
                            <label class="ar-label">
                                {{ $tiposAlertas[$tipo]['param_umbral_label'] ?? 'Umbral' }}
                                <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="number" class="ar-input" wire:model.defer="parametroUmbral"
                                   step="0.1" min="0" placeholder="Ej: 6">
                            @error('parametroUmbral') <small style="color:#ef4444;">{{ $message }}</small> @enderror
                        </div>
                    @endif
                @endif

                {{-- Prioridad --}}
                <div class="form-group mb-3">
                    <label class="ar-label">Prioridad</label>
                    <select class="ar-input ar-select" wire:model.defer="prioridad">
                        <option value="informativa">Informativa</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                        <option value="critica">Crítica</option>
                    </select>
                </div>

                {{-- Destinatario --}}
                <div class="form-group mb-3">
                    <label class="ar-label">Destinatario</label>
                    <div class="ar-segment mb-2">
                        <button type="button"
                                wire:click="$set('targetTipo', 'rol')"
                                class="{{ $targetTipo === 'rol' ? 'active' : '' }}">
                            <i class="fa fa-user mr-1"></i> Por rol
                        </button>
                        <button type="button"
                                wire:click="$set('targetTipo', 'area')"
                                class="{{ $targetTipo === 'area' ? 'active' : '' }}">
                            <i class="fa fa-building mr-1"></i> Por área
                        </button>
                    </div>
                    @if($targetTipo === 'rol')
                        <select class="ar-input ar-select" wire:model.defer="rolId">
                            <option value="">— Selecciona un rol —</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol['id'] }}">{{ $rol['nombre'] }}</option>
                            @endforeach
                        </select>
                    @else
                        <select class="ar-input ar-select" wire:model.defer="areaId">
                            <option value="">— Selecciona un área —</option>
                            @foreach($areas as $area)
                                <option value="{{ $area['id'] }}">{{ $area['nombre'] }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                {{-- Apariencia (icono + color) --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:10px;">
                    <div>
                        <label class="ar-label">Ícono (FontAwesome)</label>
                        <input type="text" class="ar-input" wire:model.defer="icono"
                               placeholder="fa-bell">
                        <small style="font-size:11px; color:#94a3b8;">Ej: fa-bell, fa-exclamation-triangle, fa-clock-o</small>
                    </div>
                    <div>
                        <label class="ar-label">Color de la notificación</label>
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                            <input type="color" wire:model.defer="color"
                                   style="width:36px; height:36px; border:none; cursor:pointer; border-radius:6px; padding:2px;">
                            <input type="text" class="ar-input" wire:model.defer="color"
                                   placeholder="#f59e0b" style="flex:1;">
                        </div>
                        {{-- Paleta rápida --}}
                        <div class="ar-color-row">
                            @foreach(['#ef4444','#f97316','#f59e0b','#22c55e','#3b82f6','#8b5cf6','#ec4899','#06b6d4','#14b8a6','#64748b'] as $c)
                                <div class="ar-swatch {{ $color === $c ? 'selected' : '' }}"
                                     wire:click="$set('color', '{{ $c }}')"
                                     style="background:{{ $c }};" title="{{ $c }}"></div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Estado --}}
                <div style="display:flex; align-items:center; gap:10px; margin-top:8px;">
                    <label class="ar-toggle">
                        <input type="checkbox" wire:model.defer="activo">
                        <span class="ar-toggle-slider"></span>
                    </label>
                    <span style="font-size:13px; color:#374151; font-weight:600;">
                        Regla activa
                    </span>
                </div>

            </div>

            {{-- Footer --}}
            <div class="ar-modal-footer">
                <button wire:click="$set('showModal', false)"
                        style="background:#f1f5f9; border:1.5px solid #e2e8f0; color:#64748b;
                               border-radius:8px; padding:8px 18px; font-size:13px; cursor:pointer;">
                    <i class="fa fa-times mr-1"></i> Cancelar
                </button>
                <button wire:click="guardar" wire:loading.attr="disabled"
                        style="background:linear-gradient(135deg,#065f46,#059669); color:#fff; border:none;
                               border-radius:8px; padding:8px 22px; font-size:13px; font-weight:700;
                               cursor:pointer; box-shadow:0 2px 10px rgba(5,150,105,.35);">
                    <span wire:loading.remove wire:target="guardar">
                        <i class="fa fa-save mr-1"></i> {{ $editandoId ? 'Actualizar regla' : 'Crear regla' }}
                    </span>
                    <span wire:loading wire:target="guardar">
                        <i class="fa fa-spinner fa-spin mr-1"></i> Guardando…
                    </span>
                </button>
            </div>

        </div>
    </div>
@endif

</div>
