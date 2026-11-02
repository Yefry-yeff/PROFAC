@if($this->puedeVerArea('cobros'))
    @include('dashboard.kpi', [
        'etiqueta' => 'Saldo pendiente de pago', 'icono' => 'fa-wallet', 'color' => 'red',
        'valor' => 'L. ' . number_format($kpisCobros['saldo_pendiente'] ?? 0, 2),
        'ayuda' => 'Saldo actual por cobrar', 'enlace' => $this->enlaceAnalisis('cartera'), 'accion' => null,
    ])
@endif
