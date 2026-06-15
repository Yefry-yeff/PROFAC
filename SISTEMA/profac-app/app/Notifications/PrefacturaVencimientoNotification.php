<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PrefacturaVencimientoNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly int $prefacturaId,
        private readonly int $flujoId,
        private readonly string $cliente,
        private readonly string $fechaHoraVencimiento,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $codigoPrefactura = 'PF-' . str_pad((string) $this->prefacturaId, 6, '0', STR_PAD_LEFT);
        $mensaje = 'La prefactura ' . $codigoPrefactura
            . ' del cliente "' . $this->cliente . '" vencerá el día ' . $this->fechaHoraVencimiento
            . '. Una vez vencida, los productos dejarán de estar reservados y quedarán disponibles para otros procesos de venta.';

        return [
            'titulo'        => 'Prefactura por vencer',
            'mensaje'       => $mensaje,
            'url'           => '/flujo/ventas/historico?flujo_id=' . $this->flujoId,
            'icono'         => 'fa-clock-o',
            'color'         => '#FF9800',
            'alerta'        => 'prefactura_vencimiento_24h',
            'prefactura_id' => $this->prefacturaId,
            'flujo_id'      => $this->flujoId,
            'cliente'       => $this->cliente,
            'vencimiento'   => $this->fechaHoraVencimiento,
        ];
    }
}
