<div class="position-relative ml-3"
     x-data="{ open: false }">

    <style>
    /* ── Campana ─────────────────────────────────────────── */
    @keyframes bellRing {
        0%,100% { transform:rotate(0); }
        10%      { transform:rotate(14deg); }
        30%      { transform:rotate(-12deg); }
        50%      { transform:rotate(8deg); }
        70%      { transform:rotate(-5deg); }
        85%      { transform:rotate(3deg); }
    }
    @keyframes panelDrop {
        from { opacity:0; transform:translateY(-10px) scale(.97); }
        to   { opacity:1; transform:translateY(0) scale(1); }
    }
    .notif-bell-btn {
        width:40px; height:40px; border-radius:50%;
        background:linear-gradient(135deg,#f06030,#d02010);
        border:2px solid rgba(255,255,255,.35);
        display:flex; align-items:center; justify-content:center;
        cursor:pointer; transition:.2s; box-shadow:0 3px 10px rgba(208,32,16,.4);
        position:relative; outline:none;
    }
    .notif-bell-btn:hover { filter:brightness(1.1); transform:scale(1.08); }
    .notif-bell-btn.ringing i { animation: bellRing .55s ease; }
    .notif-badge {
        position:absolute; top:-4px; right:-4px;
        min-width:18px; height:18px; padding:0 4px;
        background:#ef4444; color:#fff; font-size:10px; font-weight:700;
        border-radius:99px; display:flex; align-items:center; justify-content:center;
        border:2px solid rgba(255,255,255,.5); line-height:1;
        box-shadow:0 2px 6px rgba(239,68,68,.55);
    }
    /* ── Panel ───────────────────────────────────────────── */
    .notif-panel {
        position:absolute; right:0; top:calc(100% + 12px);
        width:380px; max-width:calc(100vw - 24px);
        background:#fff; border-radius:18px;
        box-shadow:0 24px 64px rgba(0,0,0,.22), 0 4px 20px rgba(0,0,0,.1);
        border:1px solid rgba(0,0,0,.07);
        z-index:9999; display:flex; flex-direction:column;
        max-height:min(530px, calc(100vh - 90px));
        animation: panelDrop .18s ease-out both;
        overflow:hidden;
    }
    .notif-panel-head {
        background:linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #1d4ed8 100%);
        padding:10px 16px; flex-shrink:0;
    }
    .notif-panel-head-row {
        display:flex; align-items:center; justify-content:space-between;
    }
    .notif-panel-title { display:flex; align-items:center; gap:10px; }
    .notif-panel-icon {
        width:34px; height:34px; border-radius:10px;
        background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.2);
        display:flex; align-items:center; justify-content:center; flex-shrink:0;
    }
    .notif-panel-head-actions { display:flex; align-items:center; gap:6px; }
    .notif-mark-btn {
        font-size:11px; font-weight:700; color:#93c5fd;
        background:rgba(255,255,255,.1); border:1px solid rgba(147,197,253,.3);
        border-radius:6px; padding:4px 10px; cursor:pointer; transition:.15s;
        white-space:nowrap;
    }
    .notif-mark-btn:hover { background:rgba(255,255,255,.2); color:#fff; }
    .notif-x-btn {
        width:28px; height:28px; border-radius:8px;
        background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15);
        color:rgba(255,255,255,.6); cursor:pointer; transition:.15s;
        display:flex; align-items:center; justify-content:center; font-size:13px;
    }
    .notif-x-btn:hover { background:rgba(239,68,68,.3); color:#fff; }
    .notif-pill-unread {
        display:inline-flex; align-items:center; gap:5px;
        background:rgba(239,68,68,.2); color:#fca5a5;
        font-size:10px; font-weight:700; letter-spacing:.4px; text-transform:uppercase;
        padding:2px 9px; border-radius:99px;
        border:1px solid rgba(239,68,68,.25);
    }
    .notif-pill-ok {
        display:inline-flex; align-items:center; gap:5px;
        background:rgba(34,197,94,.15); color:#86efac;
        font-size:10px; font-weight:700; letter-spacing:.4px; text-transform:uppercase;
        padding:2px 9px; border-radius:99px;
        border:1px solid rgba(34,197,94,.2);
    }
    /* ── Lista ───────────────────────────────────────────── */
    .notif-list {
        overflow-y:auto; flex:1;
        max-height:375px;
        scrollbar-width:thin; scrollbar-color:#e2e8f0 transparent;
    }
    .notif-list::-webkit-scrollbar { width:4px; }
    .notif-list::-webkit-scrollbar-thumb { background:#e2e8f0; border-radius:99px; }
    .notif-item {
        display:flex; align-items:flex-start; gap:12px;
        padding:13px 18px; cursor:pointer; width:100%; text-align:left;
        background:#fff; border:none; border-bottom:1px solid #f1f5f9; transition:.13s;
    }
    .notif-item:last-child { border-bottom:none; }
    .notif-item:hover { background:#f8faff; }
    .notif-item-avatar {
        width:42px; height:42px; border-radius:12px;
        flex-shrink:0; display:flex; align-items:center; justify-content:center;
        font-size:17px;
    }
    .notif-item-body { flex:1; min-width:0; }
    .notif-item-title {
        font-size:13px; font-weight:700; color:#0f172a; line-height:1.3;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }
    .notif-item-msg {
        font-size:12px; color:#64748b; margin-top:2px; line-height:1.45;
        display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
    }
    .notif-item-time {
        font-size:10px; color:#94a3b8; margin-top:5px;
        display:flex; align-items:center; gap:4px;
    }
    .notif-unread-dot {
        width:8px; height:8px; border-radius:50%; background:#3b82f6;
        flex-shrink:0; margin-top:6px; box-shadow:0 0 0 3px rgba(59,130,246,.18);
    }
    /* ── Empty ───────────────────────────────────────────── */
    .notif-empty {
        padding:44px 24px; text-align:center;
        display:flex; flex-direction:column; align-items:center; gap:14px;
    }
    .notif-empty-icon {
        width:68px; height:68px; border-radius:20px;
        background:linear-gradient(135deg,#f0f9ff,#e0f2fe);
        display:flex; align-items:center; justify-content:center;
        font-size:28px; color:#7dd3fc; border:1px solid #bae6fd;
    }
    /* ── Footer ──────────────────────────────────────────── */
    .notif-footer {
        padding:10px 18px; border-top:1px solid #f1f5f9;
        background:#fafbff; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
    }
    .notif-footer-link {
        font-size:12px; font-weight:600; color:#3b82f6; text-decoration:none;
        display:flex; align-items:center; gap:6px;
        padding:5px 14px; border-radius:8px; transition:.15s;
    }
    .notif-footer-link:hover { background:#eff6ff; color:#1d4ed8; text-decoration:none; }
    </style>

    {{-- Poll silencioso cada 3 minutos --}}
    <div wire:poll.180s="cargar" style="display:none;"></div>

    {{-- Botón campana --}}
    <button @click="open = !open; $el.classList.add('ringing'); setTimeout(() => $el.classList.remove('ringing'), 600)"
            class="notif-bell-btn"
            title="Notificaciones">
        <i class="fa fa-bell" style="color:#fff; font-size:1.05rem; text-shadow:0 1px 4px rgba(0,0,0,.35);"></i>
        @if($count > 0)
            <span class="notif-badge">{{ $count > 99 ? '99+' : $count }}</span>
        @endif
    </button>

    {{-- Panel desplegable --}}
    <div x-show="open"
         x-transition:enter="transition-none"
         x-transition:leave="transition-none"
         @click.away="open = false"
         class="notif-panel"
         style="display:none;">

        {{-- Header --}}
        <div class="notif-panel-head">
            <div class="notif-panel-head-row">
                <div class="notif-panel-title">
                    <div class="notif-panel-icon">
                        <i class="fa fa-bell" style="color:#fff; font-size:14px;"></i>
                    </div>

                </div>
                <div class="notif-panel-head-actions">
                    @if($count > 0)
                        <span class="notif-pill-unread">
                            <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                            {{ $count }} sin leer
                        </span>
                        <button wire:click="marcarTodasLeidas" @click="open = false" class="notif-mark-btn">
                            <i class="fa fa-check"></i> Todo leído
                        </button>
                    @else
                        <span class="notif-pill-ok">
                            <i class="fa fa-check-circle" style="font-size:9px;"></i>
                            Al día
                        </span>
                    @endif
                    <button @click="open = false" class="notif-x-btn">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Lista de notificaciones --}}
        <div class="notif-list">
            @forelse($notificaciones as $notif)
                @php
                    $data   = $notif['data'];
                    $color  = $data['color'] ?? '#6366f1';
                    $icono  = $data['icono'] ?? 'fa-bell';
                    $titulo = $data['titulo'] ?? 'Notificación';
                    $msg    = $data['mensaje'] ?? '';
                    $tiempo = $notif['tiempo'];
                    $url    = $data['url'] ?? null;
                @endphp
                <a href="{{ $url ?? '#' }}"
                   wire:click="marcarLeida('{{ $notif['id'] }}')"
                   class="notif-item"
                   style="text-decoration:none;">
                    <div class="notif-item-avatar"
                         style="background:{{ $color }}18; border:1.5px solid {{ $color }}35;">
                        <i class="fa {{ $icono }}" style="color:{{ $color }};"></i>
                    </div>
                    <div class="notif-item-body">
                        <div class="notif-item-title">{{ $titulo }}</div>
                        <div class="notif-item-msg">{{ $msg }}</div>
                        <div class="notif-item-time">
                            <i class="fa fa-clock-o"></i> {{ $tiempo }}
                        </div>
                    </div>
                    <div class="notif-unread-dot"></div>
                </a>
            @empty
                <div class="notif-empty">
                    <div class="notif-empty-icon">
                        <i class="fa fa-bell-slash"></i>
                    </div>
                    <div>
                        <p style="font-size:14px; font-weight:700; color:#334155; margin:0 0 4px;">Todo en orden</p>
                        <p style="font-size:12px; color:#94a3b8; margin:0;">No tienes notificaciones pendientes.</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="notif-footer">
            <a href="{{ route('notificaciones.historial') }}" class="notif-footer-link">
                <i class="fa fa-history"></i> Ver historial completo
            </a>
        </div>

    </div>
</div>
