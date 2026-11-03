@if($this->puedeVerArea('comercial'))
    @include('dashboard.kpi', [
        'etiqueta' => 'Clientes que requieren atención', 'icono' => 'fa-exclamation-triangle', 'color' => 'red',
        'valor' => number_format($kpisGenerales['requieren_atencion']),
        'ayuda' => 'Historial hasta hoy: sin compra por más de 90 días o caída mensual de al menos 20%. Sin límite de clientes.',
        'accion' => 'atencion', 'enlace' => null,
    ])
@endif
