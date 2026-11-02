@if($this->puedeVerArea('inventario'))
@include('dashboard.kpi', [
    'etiqueta' => 'Productos con poco movimiento', 'icono' => 'fa-box-open', 'color' => 'orange',
    'valor' => number_format($kpisGenerales['poco_movimiento']),
    'ayuda' => 'Sin ventas en los últimos 90 días hasta hoy. Incluye productos nunca vendidos; no depende del período.',
    'accion' => 'poco_movimiento', 'enlace' => null,
])
@endif
