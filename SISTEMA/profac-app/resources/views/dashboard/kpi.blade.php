@php
    if (!empty($accion) && !$this->puedeVerArea(in_array($accion, ['inventario', 'poco_movimiento', 'sin_imagen'], true) ? 'inventario' : 'comercial')) {
        $accion = null;
    }
@endphp
@if(!empty($enlace))
<a href="{{ $enlace }}" class="dash-kpi {{ $color ?? '' }} dash-kpi-action">
@elseif(!empty($accion))
<button type="button" wire:click="abrirDetalle('{{ $accion }}')" wire:loading.attr="disabled" class="dash-kpi {{ $color ?? '' }} dash-kpi-action">
@else
<div class="dash-kpi {{ $color ?? '' }}">
@endif
    <span class="dash-kpi-icon"><i class="fa {{ $icono }}" aria-hidden="true"></i></span>
    <span class="dash-kpi-label">{{ $etiqueta }}</span>
    <span class="dash-kpi-value">{{ $valor }}</span>
    <span class="dash-kpi-sub">{{ $ayuda }}</span>
    @if(!empty($enlace) || !empty($accion))
        <span class="dash-kpi-hint">Ver detalle <i class="fa fa-arrow-right" aria-hidden="true"></i></span>
    @endif
@if(!empty($enlace))
</a>
@elseif(!empty($accion))
</button>
@else
</div>
@endif
