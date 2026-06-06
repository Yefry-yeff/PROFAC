<div>
<style>
.nh-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #1d4ed8 100%);
    border-radius: 16px 16px 0 0;
    padding: 20px 28px 18px;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px;
}
.nh-header-left { display:flex; align-items:center; gap:14px; }
.nh-header-icon {
    width:44px; height:44px; border-radius:13px;
    background:rgba(255,255,255,.15); border:1.5px solid rgba(255,255,255,.25);
    display:flex; align-items:center; justify-content:center;
}
.nh-header-title { color:#fff; font-size:20px; font-weight:800; margin:0; line-height:1.1; }
.nh-header-sub { color:rgba(255,255,255,.6); font-size:12px; margin:0; }
.nh-pill-unread {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(239,68,68,.25); color:#fca5a5;
    font-size:11px; font-weight:700; letter-spacing:.4px; text-transform:uppercase;
    padding:4px 12px; border-radius:99px; border:1px solid rgba(239,68,68,.3);
}
.nh-pill-ok {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(34,197,94,.15); color:#86efac;
    font-size:11px; font-weight:700; padding:4px 12px; border-radius:99px;
    border:1px solid rgba(34,197,94,.2);
}
/* Tabs */
.nh-tabs { display:flex; gap:6px; }
.nh-tab {
    padding:7px 18px; border-radius:8px; font-size:13px; font-weight:600;
    cursor:pointer; border:none; transition:.15s; background:transparent; color:#64748b;
}
.nh-tab.active { background:#1d4ed8; color:#fff; box-shadow:0 3px 12px rgba(29,78,216,.3); }
.nh-tab:not(.active):hover { background:#f1f5f9; color:#1e3a5f; }
/* Tabla */
.nh-card {
    background:#fff; border-radius:0 0 16px 16px;
    border:1px solid #e2e8f0; border-top:none;
    overflow:hidden;
}
.nh-table { width:100%; border-collapse:collapse; }
.nh-table thead tr { background:linear-gradient(90deg,#f8fafc,#f1f5f9); }
.nh-table thead th {
    padding:11px 16px; text-align:left;
    font-size:11px; font-weight:700; color:#64748b;
    text-transform:uppercase; letter-spacing:.6px;
    border-bottom:2px solid #e2e8f0;
}
.nh-table tbody tr { border-bottom:1px solid #f1f5f9; transition:.12s; cursor:pointer; }
.nh-table tbody tr:last-child { border-bottom:none; }
.nh-table tbody tr:hover { background:#eef4ff; }
.nh-table td { padding:13px 16px; font-size:13px; color:#334155; vertical-align:middle; }
.nh-avatar {
    width:38px; height:38px; border-radius:11px;
    display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;
}
.nh-unread-row td:first-child { border-left:3px solid #3b82f6; }
.nh-unread-dot {
    width:8px; height:8px; border-radius:50%; background:#3b82f6;
    display:inline-block; box-shadow:0 0 0 3px rgba(59,130,246,.2);
}
.nh-read-dot {
    width:8px; height:8px; border-radius:50%; background:#cbd5e1;
    display:inline-block;
}
.nh-btn-leer {
    padding:5px 12px; border-radius:7px; font-size:11px; font-weight:700;
    border:1px solid #e2e8f0; background:#f8fafc; color:#64748b;
    cursor:pointer; transition:.13s; white-space:nowrap;
}
.nh-btn-leer:hover { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
.nh-btn-all {
    padding:8px 20px; border-radius:9px; font-size:13px; font-weight:700;
    background:linear-gradient(135deg,#1e3a5f,#1d4ed8); color:#fff;
    border:none; cursor:pointer; transition:.15s;
    box-shadow:0 3px 12px rgba(29,78,216,.25);
}
.nh-btn-all:hover { filter:brightness(1.1); transform:translateY(-1px); }
.nh-empty {
    padding:60px 24px; text-align:center;
}
.nh-empty-icon {
    width:72px; height:72px; border-radius:20px;
    background:linear-gradient(135deg,#f0f9ff,#e0f2fe);
    display:flex; align-items:center; justify-content:center;
    font-size:30px; color:#7dd3fc; margin:0 auto 16px;
    border:1px solid #bae6fd;
}
.nh-pagination { padding:14px 20px; border-top:1px solid #f1f5f9; background:#fafbff; }
</style>

<div class="ibox float-e-margins" style="margin:24px;">

    {{-- HEADER --}}
    <div class="nh-header">
        <div class="nh-header-left">
            <div class="nh-header-icon">
                <i class="fa fa-bell" style="color:#fff; font-size:18px;"></i>
            </div>
            <div>
                <p class="nh-header-title">Historial de Notificaciones</p>
                <p class="nh-header-sub">Registro de todas tus notificaciones del sistema</p>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            @if($totalNoLeidas > 0)
                <span class="nh-pill-unread">
                    <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                    {{ $totalNoLeidas }} sin leer
                </span>
                <button wire:click="marcarTodasLeidas" class="nh-btn-all">
                    <i class="fa fa-check"></i> Marcar todas leídas
                </button>
            @else
                <span class="nh-pill-ok">
                    <i class="fa fa-check-circle" style="font-size:10px;"></i>
                    Al día
                </span>
            @endif
        </div>
    </div>

    {{-- CARD + TABS --}}
    <div style="background:#fff; border:1px solid #e2e8f0; border-top:none; padding:14px 20px 0; border-bottom:1px solid #e2e8f0;">
        <div class="nh-tabs">
            <button class="nh-tab {{ $filtro === 'pendientes' ? 'active' : '' }}"
                    wire:click="$set('filtro', 'pendientes')">
                <i class="fa fa-clock-o mr-1"></i> Pendientes
                @if($totalNoLeidas > 0)
                    <span style="background:{{ $filtro==='pendientes' ? 'rgba(255,255,255,.25)' : '#eff6ff' }};
                                 color:{{ $filtro==='pendientes' ? '#fff' : '#1d4ed8' }};
                                 font-size:10px; font-weight:800;
                                 padding:1px 7px; border-radius:99px; margin-left:6px;">
                        {{ $totalNoLeidas }}
                    </span>
                @endif
            </button>
            <button class="nh-tab {{ $filtro === 'todas' ? 'active' : '' }}"
                    wire:click="$set('filtro', 'todas')">
                <i class="fa fa-list mr-1"></i> Todas
            </button>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="nh-card">
        @if($notificaciones->count() > 0)
            <table class="nh-table">
                <thead>
                    <tr>
                        <th style="width:44px;"></th>
                        <th>Notificación</th>
                        <th>Mensaje</th>
                        <th>Hace</th>
                        <th style="width:38px;"></th>
                        <th style="width:110px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notificaciones as $notif)
                        @php
                            $data    = $notif->data;
                            $color   = $data['color']   ?? '#6366f1';
                            $icono   = $data['icono']   ?? 'fa-bell';
                            $titulo  = $data['titulo']  ?? 'Notificación';
                            $msg     = $data['mensaje'] ?? '';
                            $leida   = !is_null($notif->read_at);
                        @endphp
                        <tr class="{{ !$leida ? 'nh-unread-row' : '' }}"
                            wire:click="irA('{{ $notif->id }}')"
                            style="{{ !$leida ? 'background:#fafcff;' : '' }}"
                            title="{{ $notif->data['url'] ?? '' ? 'Ir a la gestión' : '' }}">
                            <td>
                                <div class="nh-avatar"
                                     style="background:{{ $color }}18; border:1.5px solid {{ $color }}35;">
                                    <i class="fa {{ $icono }}" style="color:{{ $color }};"></i>
                                </div>
                            </td>
                            <td>
                                <span style="font-weight:{{ !$leida ? '700' : '500' }}; color:{{ !$leida ? '#0f172a' : '#64748b' }};">
                                    {{ $titulo }}
                                </span>
                            </td>
                            <td style="max-width:280px; color:#64748b;">
                                <span style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                    {{ $msg }}
                                </span>
                            </td>
                            <td style="white-space:nowrap; color:#94a3b8; font-size:11px;">
                                <i class="fa fa-clock-o mr-1"></i>{{ $notif->created_at->diffForHumans() }}
                            </td>
                            <td>
                                @if(!$leida)
                                    <span class="nh-unread-dot" title="Sin leer"></span>
                                @else
                                    <span class="nh-read-dot" title="Leída"></span>
                                @endif
                            </td>
                            <td>
                                @if(!$leida)
                                    <button wire:click.stop="marcarLeida('{{ $notif->id }}')" class="nh-btn-leer">
                                        <i class="fa fa-check mr-1"></i> Marcar leída
                                    </button>
                                @else
                                    <span style="font-size:11px; color:#94a3b8;">Leída</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- PAGINACIÓN --}}
            <div class="nh-pagination">
                {{ $notificaciones->links() }}
            </div>
        @else
            <div class="nh-empty">
                <div class="nh-empty-icon">
                    <i class="fa fa-bell-slash"></i>
                </div>
                <p style="font-size:15px; font-weight:700; color:#334155; margin:0 0 6px;">
                    {{ $filtro === 'pendientes' ? 'No hay notificaciones pendientes' : 'Sin notificaciones' }}
                </p>
                <p style="font-size:13px; color:#94a3b8; margin:0;">
                    {{ $filtro === 'pendientes' ? 'Estás al día. Todo ha sido revisado.' : 'Aún no has recibido ninguna notificación.' }}
                </p>
            </div>
        @endif
    </div>

</div>
</div>
