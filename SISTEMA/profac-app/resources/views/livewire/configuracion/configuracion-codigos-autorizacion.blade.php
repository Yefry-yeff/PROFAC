<div>
<style>
@keyframes cca-fadeUp {
    from { opacity:0; transform:translateY(12px); }
    to   { opacity:1; transform:translateY(0); }
}
.cca-card { animation: cca-fadeUp .25s ease both; }

/* ── Layout ─────────────────────────────────────────────────────────── */
.cca-wrap { padding: 16px; }
@media(min-width:768px){ .cca-wrap { padding: 24px; } }

.cca-grid { display: grid; grid-template-columns: 1fr; gap: 24px; }
@media(min-width:1024px){ .cca-grid { grid-template-columns: 1fr 2fr; } }

.cca-space { display:flex; flex-direction:column; gap:16px; }

/* ── Hero ────────────────────────────────────────────────────────────── */
.cca-hero {
    background: linear-gradient(135deg,#1e3a5f 0%,#2563eb 60%,#3b82f6 100%);
    padding: 28px 32px 24px;
    color: #fff;
    position: relative;
    overflow: hidden;
    border-radius: 12px 12px 0 0;
}
.cca-hero::before {
    content:'';
    position:absolute; inset:0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.toggle-switch { position:relative; display:inline-block; width:44px; height:24px; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider {
    position:absolute; inset:0; cursor:pointer;
    background:#cbd5e1; border-radius:24px; transition:.25s;
}
.toggle-slider::before {
    content:''; position:absolute;
    height:18px; width:18px; left:3px; bottom:3px;
    background:#fff; border-radius:50%; transition:.25s;
    box-shadow: 0 1px 3px rgba(0,0,0,.25);
}
input:checked + .toggle-slider { background:#2563eb; }
input:checked + .toggle-slider::before { transform:translateX(20px); }

/* ── Cards ───────────────────────────────────────────────────────────── */
.cca-panel {
    background:#fff;
    border-radius:12px;
    border:1px solid #e2e8f0;
    box-shadow:0 1px 4px rgba(0,0,0,.05);
    overflow:hidden;
}
.cca-panel-header {
    padding:14px 20px;
    border-bottom:1px solid #f0f3f9;
    background:linear-gradient(135deg,#f8faff,#f0f4ff);
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.cca-panel-header h2 { margin:0; font-size:14px; font-weight:600; color:#1e293b; display:flex; align-items:center; gap:8px; }
.cca-panel-body { padding:20px; }

/* ── Mensajes ────────────────────────────────────────────────────────── */
.cca-alert { display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:12px; font-size:13px; }
.cca-alert-ok  { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
.cca-alert-err { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; }

/* ── Form ────────────────────────────────────────────────────────────── */
.cca-label { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin-bottom:6px; }
.cca-input { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 12px; font-size:14px; outline:none; transition:border .2s, box-shadow .2s; }
.cca-input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
.cca-hint { margin-top:4px; font-size:11px; color:#94a3b8; }

/* ── Estadísticas ────────────────────────────────────────────────────── */
.cca-stat-row { display:flex; align-items:center; justify-content:space-between; }
.cca-stat-row + .cca-stat-row { margin-top:10px; }
.cca-badge { font-size:11px; font-weight:700; padding:2px 10px; border-radius:20px; }
.badge-pendiente { background:#dbeafe; color:#1d4ed8; }
.badge-utilizado { background:#dcfce7; color:#166534; }
.badge-expirado  { background:#fef9c3; color:#92400e; }
.badge-cancelado { background:#fee2e2; color:#991b1b; }
.badge-default   { background:#f1f5f9; color:#475569; }

/* ── Tabla ───────────────────────────────────────────────────────────── */
.cca-table { width:100%; border-collapse:collapse; font-size:13px; }
.cca-table thead th {
    text-align:left; font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.05em; color:#64748b; background:#f8fafc;
    border-bottom:2px solid #e8edf5; padding:10px 14px;
}
.cca-table tbody tr { transition:background .1s; }
.cca-table tbody tr:hover { background:#f8faff; }
.cca-table tbody td { padding:10px 14px; vertical-align:middle; border-bottom:1px solid #f1f5f9; }
.cca-mono { font-family:monospace; font-weight:700; font-size:15px; color:#1e293b; }
.cca-refresh-btn { font-size:12px; color:#2563eb; background:none; border:none; cursor:pointer; display:flex; align-items:center; gap:4px; }
.cca-refresh-btn:hover { color:#1d4ed8; }
</style>

<div style="padding:16px;">

    {{-- ── HERO ─────────────────────────────────────────────────────────── --}}
    <div class="cca-hero" style="box-shadow:0 4px 12px rgba(0,0,0,.15); margin-bottom:20px;">
        <div style="position:relative;z-index:10;display:flex;align-items:center;gap:16px;">
            <div style="flex-shrink:0;width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;">
                <i class="fa fa-key" style="font-size:22px;color:#fff;"></i>
            </div>
            <div>
                <h1 style="margin:0;font-size:18px;font-weight:700;color:#fff;">Configuración de Códigos de Autorización</h1>
                <p style="margin:4px 0 0;font-size:13px;color:rgba(255,255,255,.8);">Gestione el tiempo de expiración y audite el uso de los códigos de autorización del sistema.</p>
            </div>
        </div>
    </div>

    {{-- ── Mensajes ─────────────────────────────────────────────────────── --}}
    @if($mensajeExito)
        <div class="cca-alert cca-alert-ok" style="margin-bottom:16px;">
            <i class="fa fa-check-circle" style="color:#22c55e;"></i>
            <span>{{ $mensajeExito }}</span>
        </div>
    @endif
    @if($mensajeError)
        <div class="cca-alert cca-alert-err" style="margin-bottom:16px;">
            <i class="fa fa-exclamation-circle" style="color:#ef4444;"></i>
            <span>{{ $mensajeError }}</span>
        </div>
    @endif

    <div class="cca-grid">

        {{-- ── COLUMNA IZQUIERDA: Formulario + Estadísticas ────────────── --}}
        <div class="cca-space">

            {{-- Formulario --}}
            <div class="cca-panel cca-card">
                <div class="cca-panel-header">
                    <h2><i class="fa fa-sliders-h" style="color:#2563eb;"></i> Parámetros de expiración</h2>
                </div>
                <div class="cca-panel-body">

                    {{-- Tiempo de expiración --}}
                    <div style="margin-bottom:18px;">
                        <label class="cca-label">Tiempo de expiración (minutos)</label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="number" min="1" max="1440"
                                   wire:model.defer="tiempoExpiracionMinutos"
                                   class="cca-input">
                            <span style="font-size:12px;color:#94a3b8;white-space:nowrap;">min.</span>
                        </div>
                        <p class="cca-hint">Entre 1 y 1 440 minutos (24 h). Valor por defecto: 10.</p>
                    </div>

                    {{-- Toggle expiración activa --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-top:1px solid #f1f5f9;margin-bottom:18px;">
                        <div>
                            <p style="margin:0;font-size:14px;font-weight:500;color:#374151;">Expiración automática</p>
                            <p style="margin:3px 0 0;font-size:11px;color:#94a3b8;">Si está desactivado, los códigos no expiran.</p>
                        </div>
                        <label class="toggle-switch" style="margin-left:12px;">
                            <input type="checkbox" wire:model.defer="expiracionActiva">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    {{-- Botón guardar --}}
                    <button wire:click="guardarConfiguracion"
                            wire:loading.attr="disabled"
                            style="background:#2563eb;color:#fff;border:none;width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:10px 16px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:background .2s;margin-bottom:10px;"
                            onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                        <span wire:loading.remove wire:target="guardarConfiguracion">
                            <i class="fa fa-save"></i> Guardar configuración
                        </span>
                        <span wire:loading wire:target="guardarConfiguracion">
                            <i class="fa fa-spinner fa-spin"></i> Guardando…
                        </span>
                    </button>

                    {{-- Botón expirar pendientes --}}
                    <button wire:click="expirarCodigosPendientes"
                            wire:loading.attr="disabled"
                            onclick="return confirm('¿Expirar todos los códigos pendientes vencidos?')"
                            style="background:#f59e0b;color:#fff;border:none;width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:10px 16px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:background .2s;"
                            onmouseover="this.style.background='#d97706'" onmouseout="this.style.background='#f59e0b'">
                        <span wire:loading.remove wire:target="expirarCodigosPendientes">
                            <i class="fa fa-clock"></i> Expirar códigos vencidos
                        </span>
                        <span wire:loading wire:target="expirarCodigosPendientes">
                            <i class="fa fa-spinner fa-spin"></i> Procesando…
                        </span>
                    </button>
                </div>
            </div>

            {{-- Estadísticas --}}
            <div class="cca-panel cca-card">
                <div class="cca-panel-header">
                    <h2><i class="fa fa-chart-bar" style="color:#64748b;"></i> Estadísticas globales</h2>
                </div>
                <div class="cca-panel-body">
                    @php
                        $badgeCssMap = [
                            'Pendiente' => 'badge-pendiente',
                            'Utilizado' => 'badge-utilizado',
                            'Expirado'  => 'badge-expirado',
                            'Cancelado' => 'badge-cancelado',
                        ];
                    @endphp
                    @forelse($estadisticas as $estado => $total)
                        <div class="cca-stat-row">
                            <span style="font-size:13px;color:#475569;">{{ $estado ?? 'Sin estado' }}</span>
                            <span class="cca-badge {{ $badgeCssMap[$estado] ?? 'badge-default' }}">
                                {{ number_format($total) }}
                            </span>
                        </div>
                    @empty
                        <p style="font-size:12px;color:#94a3b8;font-style:italic;">Sin datos disponibles.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── COLUMNA DERECHA: Tabla códigos recientes ────────────────── --}}
        <div class="cca-panel cca-card" style="overflow:hidden;">
            <div class="cca-panel-header">
                <h2><i class="fa fa-list-alt" style="color:#64748b;"></i> Códigos recientes (últimos 30)</h2>
                <button wire:click="$refresh" class="cca-refresh-btn">
                    <i class="fa fa-sync-alt"></i> Actualizar
                </button>
            </div>
            <div style="overflow-x:auto;">
                <table class="cca-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Código</th>
                            <th>Usuario</th>
                            <th>Trámite</th>
                            <th>Flujo</th>
                            <th>Expira</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($codigosRecientes as $row)
                            @php
                                $badgeCss = match($row->estado_nombre ?? '') {
                                    'Pendiente' => 'badge-pendiente',
                                    'Utilizado' => 'badge-utilizado',
                                    'Expirado'  => 'badge-expirado',
                                    'Cancelado' => 'badge-cancelado',
                                    default     => 'badge-default',
                                };
                            @endphp
                            <tr>
                                <td style="color:#64748b;white-space:nowrap;font-size:12px;">
                                    {{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i') }}
                                </td>
                                <td><span class="cca-mono">{{ $row->codigo }}</span></td>
                                <td style="color:#475569;">{{ $row->usuario ?? '—' }}</td>
                                <td style="color:#64748b;font-size:12px;">{{ $row->tipo_tramite ?? '—' }}</td>
                                <td style="color:#94a3b8;font-size:12px;">{{ $row->flujo_id ? '#'.$row->flujo_id : '—' }}</td>
                                <td style="color:#94a3b8;font-size:12px;white-space:nowrap;">
                                    {{ $row->fecha_expiracion ? \Carbon\Carbon::parse($row->fecha_expiracion)->format('d/m/Y H:i') : '—' }}
                                </td>
                                <td>
                                    <span class="cca-badge {{ $badgeCss }}">
                                        {{ $row->estado_nombre ?? 'Desconocido' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding:32px;text-align:center;color:#94a3b8;font-style:italic;">
                                    No hay códigos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

