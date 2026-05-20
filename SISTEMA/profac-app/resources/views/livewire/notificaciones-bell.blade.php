<div class="relative ml-3"
     x-data="{ open: false, animate: false }"
     x-init="$watch('open', v => { if(v) { animate = true; setTimeout(() => animate = false, 700); } })">

    <style>
    @keyframes bellBounce {
        0%,100% { transform: rotate(0deg); }
        15%      { transform: rotate(12deg); }
        45%      { transform: rotate(-10deg); }
        65%      { transform: rotate(7deg); }
        80%      { transform: rotate(-5deg); }
    }
    .bell-anim { animation: bellBounce 0.6s ease; }
    </style>

    {{-- Poll scoped al componente correcto --}}
    <div wire:poll.30s="cargar" style="display:none;"></div>

    {{-- Botón campana --}}
    <button @click="open = !open"
            :class="animate ? 'bell-anim' : ''"
            class="relative flex items-center justify-center w-9 h-9 rounded-xl transition-all duration-200 focus:outline-none
                   {{ $count > 0 ? 'text-blue-600 hover:bg-blue-50 focus:ring-2 focus:ring-blue-200' : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600' }}"
            title="Notificaciones">
        <i class="fa fa-bell" style="font-size:1.05rem;"></i>
        @if($count > 0)
            <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 flex items-center justify-center
                         text-white text-[9px] font-bold rounded-full bg-red-500 leading-none shadow ring-2 ring-white">
                {{ $count > 99 ? '99+' : $count }}
            </span>
        @endif
    </button>

    {{-- Panel con animación --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
         @click.away="open = false"
         class="absolute right-0 mt-2 w-[350px] bg-white rounded-2xl z-50 flex flex-col"
         style="max-height: min(500px, calc(100vh - 80px));
                display: none;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,.07), 0 20px 40px -8px rgba(0,0,0,.15);
                border: 1px solid rgba(0,0,0,.06);">

        {{-- Cabecera --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100"
             style="background: linear-gradient(135deg,#f8faff 0%,#f0f4ff 100%); border-radius: 1rem 1rem 0 0;">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shadow-sm"
                     style="background: linear-gradient(135deg,#3b82f6,#6366f1);">
                    <i class="fa fa-bell text-white" style="font-size:0.8rem;"></i>
                </div>
                <div>
                    <p class="text-[13px] font-bold text-gray-800 leading-none">Notificaciones</p>
                    @if($count > 0)
                        <p class="text-[10px] text-blue-500 mt-0.5">{{ $count }} sin leer</p>
                    @else
                        <p class="text-[10px] text-gray-400 mt-0.5">Al día</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-1.5">
                @if($count > 0)
                    <button wire:click="marcarTodasLeidas" @click="open = false"
                            class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold px-2.5 py-1 rounded-lg hover:bg-blue-100 transition-all duration-150">
                        ✓ Todo leído
                    </button>
                @endif
                <button @click="open = false"
                        class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all duration-150 text-xs">
                    ✕
                </button>
            </div>
        </div>

        {{-- Lista --}}
        <div class="overflow-y-auto flex-1 divide-y divide-gray-50">
            @forelse($notificaciones as $notif)
                @php
                    $data   = $notif['data'];
                    $color  = $data['color'] ?? '#9E9E9E';
                    $icono  = $data['icono'] ?? 'fa-bell';
                    $titulo = $data['titulo'] ?? 'Notificación';
                    $msg    = $data['mensaje'] ?? '';
                    $tiempo = $notif['tiempo'];
                @endphp
                <button wire:click="marcarLeida('{{ $notif['id'] }}')"
                        class="w-full text-left flex items-start gap-3 px-4 py-3.5 hover:bg-blue-50/50 transition-colors duration-150 group">

                    {{-- Ícono --}}
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center shadow-sm mt-0.5"
                         style="background: linear-gradient(135deg, {{ $color }}22, {{ $color }}11);
                                border: 1.5px solid {{ $color }}30;">
                        <i class="fa {{ $icono }}" style="color:{{ $color }}; font-size:13px;"></i>
                    </div>

                    {{-- Texto --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-[12.5px] font-bold text-gray-800 truncate leading-tight">{{ $titulo }}</p>
                        <p class="text-[11.5px] text-gray-500 mt-0.5 leading-snug line-clamp-2">{{ $msg }}</p>
                        <p class="text-[10.5px] text-gray-400 mt-1 flex items-center gap-1">
                            <i class="fa fa-clock-o"></i> {{ $tiempo }}
                        </p>
                    </div>

                    {{-- Dot no leído --}}
                    <div class="flex-shrink-0 w-2.5 h-2.5 rounded-full bg-blue-500 mt-2 ring-2 ring-blue-100"
                         style="box-shadow: 0 0 0 3px rgba(59,130,246,.15);"></div>
                </button>
            @empty
                <div class="flex flex-col items-center justify-center py-14 px-6">
                    <div class="w-20 h-20 rounded-3xl flex items-center justify-center mb-4"
                         style="background: linear-gradient(135deg,#f1f5f9,#e2e8f0);">
                        <i class="fa fa-bell-slash text-gray-300" style="font-size:1.8rem;"></i>
                    </div>
                    <p class="text-[13px] font-semibold text-gray-600">Sin notificaciones</p>
                    <p class="text-[11.5px] text-gray-400 mt-1 text-center">Estás al día. No hay nada pendiente por revisar.</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="px-4 py-2.5 border-t border-gray-100 flex items-center justify-center"
             style="background:#fafbff; border-radius: 0 0 1rem 1rem;">
            <a href="{{ route('dashboard') }}"
               class="text-[11.5px] text-gray-400 hover:text-blue-600 transition-colors font-medium flex items-center gap-1.5 py-0.5">
                <i class="fa fa-history" style="font-size:10px;"></i>
                Ver historial completo
            </a>
        </div>
    </div>
</div>
