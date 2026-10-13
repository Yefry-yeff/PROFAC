<th style="{{ $estilo ?? 'padding:10px 16px; color:#555; font-weight:700;' }} white-space:nowrap;">
    <div style="display:flex; align-items:center; justify-content:{{ ($alineacion ?? 'left') === 'center' ? 'center' : 'space-between' }}; gap:8px;">
        <button type="button" wire:click="ordenarColumnaBandeja('{{ $columna }}')"
            aria-label="Ordenar {{ $titulo }}"
            title="Ordenar {{ $titulo }}"
            style="display:inline-flex; align-items:center; gap:5px; padding:0; border:0; background:transparent;
                   color:#555; font-size:inherit; font-weight:700; cursor:pointer; white-space:nowrap;">
            {{ $titulo }}
            @if ($ordenColumna === $columna)
            <i class="fa {{ $direccionOrden === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc' }}" style="color:#1565c0;"></i>
            @endif
        </button>
        <button type="button" wire:click="abrirFiltroBandeja('{{ $columna }}')"
            data-revision-filter-trigger="{{ $columna }}"
            aria-label="Filtrar {{ $titulo }}"
            title="Filtrar {{ $titulo }}"
                style="display:inline-flex; align-items:center; justify-content:center; width:23px; height:23px;
                       border:1px solid {{ isset($filtrosBandeja[$columna]) ? '#1a7efb' : '#d7dee8' }};
                       border-radius:4px; background:{{ isset($filtrosBandeja[$columna]) ? '#e8f1ff' : '#fff' }};
                       color:{{ isset($filtrosBandeja[$columna]) ? '#1565c0' : '#64748b' }}; cursor:pointer;">
            <i class="fa fa-filter" style="font-size:10px;"></i>
        </button>
    </div>
</th>