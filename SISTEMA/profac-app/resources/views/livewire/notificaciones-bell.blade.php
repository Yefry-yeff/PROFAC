<div class="relative ml-3" wire:poll.30s="cargar" x-data="{ open: false }">

    {{-- Botón campana --}}
    <button @click="open = !open; $wire.togglePanel()"
            class="relative flex items-center justify-center w-8 h-8 rounded-lg transition-all duration-150 focus:outline-none
                   {{ $count > 0 ? 'text-blue-600 hover:bg-blue-50' : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600' }}"
            title="Notificaciones">
        <i class="fa fa-bell" style="font-size:1rem;"></i>
        @if($count > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-0.5 flex items-center justify-center
                         text-white text-[9px] font-bold rounded-full bg-red-500 leading-none">
                {{ $count > 99 ? '99+' : $count }}
            </span>
        @endif
    </button>

    {{-- Panel --}}
    @if($mostrarPanel)
    <div class="absolute right-0 mt-1.5 w-[320px] bg-white rounded-xl shadow-lg border border-gray-200/80 z-50 overflow-hidden flex flex-col"
         style="max-height: min(480px, calc(100vh - 80px));"
         @click.away="open = false; $wire.set('mostrarPanel', false)">

        {{-- Cabecera compacta --}}
        <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <span class="text-[13px] font-semibold text-gray-700">Notificaciones</span>
                @if($count > 0)
                    <span class="text-[10px] font-medium text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded-full">{{ $count }}</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($count > 0)
                    <button wire:click="marcarTodasLeidas"
                            class="text-[11px] text-gray-400 hover:text-blue-600 transition-colors duration-150">
                        Marcar leídas
                    </button>
                    <span class="text-gray-200">|</span>
                @endif
                <button @click="open = false; $wire.set('mostrarPanel', false)"
                        class="text-gray-300 hover:text-gray-500 transition-colors duration-150 text-xs">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Lista --}}
        <div class="overflow-y-auto flex-1">
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
                        class="w-full text-left flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors duration-100 border-b border-gray-50 last:border-0 group">

                    {{-- Ícono pequeño --}}
                    <div class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center mt-0.5"
                         style="background-color:{{ $color }}1a;">
                        <i class="fa {{ $icono }} text-[11px]" style="color:{{ $color }};"></i>
                    </div>

                    {{-- Texto --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-[12px] font-semibold text-gray-700 truncate leading-tight">{{ $titulo }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5 leading-snug line-clamp-2">{{ $msg }}</p>
                        <p class="text-[10px] text-gray-300 mt-1">{{ $tiempo }}</p>
                    </div>

                    {{-- indicador no leído --}}
                    <div class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-blue-500 mt-2 group-hover:bg-blue-400"></div>
                </button>
            @empty
                <div class="flex flex-col items-center justify-center py-10 text-gray-300">
                    <i class="fa fa-bell-slash text-2xl mb-2"></i>
                    <p class="text-[12px] text-gray-400">Sin notificaciones pendientes</p>
                </div>
            @endforelse
        </div>

        {{-- Footer minimal --}}
        <div class="px-4 py-2 border-t border-gray-100 text-center">
            <a href="{{ route('dashboard') }}"
               class="text-[11px] text-gray-400 hover:text-blue-600 transition-colors duration-150">
                Ver historial completo
            </a>
        </div>
    </div>
    @endif
</div>
