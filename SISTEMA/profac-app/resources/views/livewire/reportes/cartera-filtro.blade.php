<div class="cb-filter" x-data="{ abierto: false, buscar: '' }" @click.outside="abierto = false" @keydown.escape.stop="abierto = false">
    <label id="cb-label-{{ $campo }}" class="small font-weight-bold">{{ $nombre }}</label>
    <div class="d-flex">
        <button id="cb-{{ $campo }}" type="button" class="form-control text-left cb-filter-toggle"
            aria-labelledby="cb-label-{{ $campo }} cb-{{ $campo }}" :aria-expanded="abierto"
            aria-controls="cb-options-{{ $campo }}"
            @click="abierto = !abierto; if (abierto) { buscar = ''; $nextTick(() => $refs.busqueda.focus()); }"
            @keydown.arrow-down.prevent="abierto = true; $nextTick(() => $refs.busqueda.focus())">
            <span class="text-truncate">{{ $opciones[$this->$campo] ?? ($this->$campo === '' ? 'Todos' : ($seleccionNombre ?? '#' . $this->$campo)) }}</span>
            <i class="fa fa-angle-down ml-2" aria-hidden="true"></i>
        </button>
        @if($this->$campo !== '')
            <button type="button" class="btn btn-outline-secondary" wire:click="seleccionar('{{ $campo }}', '')" aria-label="Quitar filtro {{ $nombre }}">×</button>
        @endif
    </div>
    <div id="cb-options-{{ $campo }}" class="cb-filter-menu" x-show="abierto" x-cloak>
        <input x-ref="busqueda" type="search" class="form-control mb-2" x-model="buscar"
            placeholder="Escribe para buscar..." aria-label="Buscar {{ $nombre }}">
        <button type="button" class="cb-filter-option" @click="$wire.set(@js($campo), ''); abierto = false">Todos</button>
        <div class="cb-filter-options">
            @foreach($opciones as $id => $etiqueta)
                <button type="button" wire:key="filtro-{{ $campo }}-{{ $id }}"
                    class="cb-filter-option {{ $this->$campo === (string) $id ? 'cb-selected' : '' }}"
                    data-label="{{ $etiqueta }} {{ $id }}"
                    x-show="$el.dataset.label.toLocaleLowerCase().includes(buscar.toLocaleLowerCase())"
                    @click="$wire.set(@js($campo), @js((string) $id)); abierto = false"
                    aria-pressed="{{ $this->$campo === (string) $id ? 'true' : 'false' }}">
                    {{ $etiqueta }} <small class="cb-muted">#{{ $id }}</small>
                </button>
            @endforeach
            @if($opciones->isEmpty())<span class="cb-muted">Sin opciones para la selección.</span>@endif
        </div>
    </div>
</div>
