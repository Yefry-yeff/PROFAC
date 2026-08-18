<div>
    <style>
        .vt-list { display:grid; gap:10px; }
        .vt-item { display:flex; align-items:center; gap:14px; padding:13px 15px; border:1px solid #e2e8f0; border-radius:8px; background:#fff; }
        .vt-icon { display:flex; align-items:center; justify-content:center; flex:0 0 38px; width:38px; height:38px; border-radius:7px; background:#fff3e0; color:#e65100; }
        .vt-info { flex:1; min-width:0; }
        .vt-info strong { display:block; overflow:hidden; color:#37474f; text-overflow:ellipsis; white-space:nowrap; font-size:13px; }
        .vt-info span { color:#78909c; font-size:11px; }
        .vt-actions { display:flex; gap:6px; }
        button.vt-resume { background:#e65100!important; border-color:#e65100!important; color:#fff!important; }
        .vt-empty { padding:34px 15px; border:1px dashed #cfd8dc; border-radius:8px; color:#90a4ae; text-align:center; }
        .vt-empty i { display:block; margin-bottom:8px; font-size:30px; }
        @media(max-width:575px) { .vt-item { align-items:flex-start; flex-wrap:wrap; } .vt-actions { width:100%; } .vt-actions .btn { flex:1; } }
    </style>

    @if (session()->has('temporal_success'))
        <div class="alert alert-success py-2">{{ session('temporal_success') }}</div>
    @endif

    <div class="vt-list">
        @forelse ($temporales as $temporal)
            <div class="vt-item" wire:key="temporal-{{ $temporal->id }}">
                <div class="vt-icon"><i class="fa {{ $tipo === 'oferta' ? 'fa-file-text-o' : 'fa-receipt' }}"></i></div>
                <div class="vt-info">
                    <strong>{{ $temporal->titulo ?: ucfirst(str_replace('_', ' ', $temporal->codigo_tipo)) . ' pendiente' }}</strong>
                    <span>
                        <i class="fa fa-clock-o mr-1"></i>Actualizado {{ \Carbon\Carbon::parse($temporal->updated_at)->diffForHumans() }}
                        · vence {{ \Carbon\Carbon::parse($temporal->expira_at)->format('d/m/Y H:i') }}
                    </span>
                </div>
                <div class="vt-actions">
                    <button type="button" wire:click="reanudar({{ $temporal->id }})" class="btn btn-sm vt-resume">
                        <i class="fa fa-play mr-1"></i>Continuar
                    </button>
                </div>
            </div>
        @empty
            <div class="vt-empty">
                <i class="fa fa-clock-o"></i>
                No hay {{ $tipo === 'oferta' ? 'cotizaciones' : 'facturas' }} temporales vigentes.
            </div>
        @endforelse
    </div>
</div>
