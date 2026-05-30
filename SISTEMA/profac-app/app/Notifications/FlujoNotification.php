<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FlujoNotification extends Notification
{
    use Queueable;

    /**
     * Mapa de configuración visual por tipo_tramite_id.
     * icono: clase Font Awesome, color: hex para el badge.
     */
    private const TRAMITE_CONFIG = [
        1  => ['titulo' => 'Nuevo Pedido',            'icono' => 'fa-shopping-cart',       'color' => '#607D8B', 'url' => '/flujo/ventas/historico'],
        2  => ['titulo' => 'Oferta en revisión',      'icono' => 'fa-file-alt',            'color' => '#9C27B0', 'url' => '/flujo/ventas/historico'],
        3  => ['titulo' => 'Nueva Factura emitida',   'icono' => 'fa-file-invoice-dollar', 'color' => '#4CAF50', 'url' => '/flujo/ventas/historico'],
        4  => ['titulo' => 'Prefactura pendiente',    'icono' => 'fa-file-invoice',        'color' => '#00BCD4', 'url' => '/flujo/ventas/historico'],
        5  => ['titulo' => 'Entrega pendiente',       'icono' => 'fa-truck',               'color' => '#FF5722', 'url' => '/flujo/ventas/historico'],
        6  => ['titulo' => 'Cobro pendiente',         'icono' => 'fa-money-bill-wave',     'color' => '#8BC34A', 'url' => '/flujo/ventas/historico'],
        7  => ['titulo' => 'Entrega y Cobro',         'icono' => 'fa-handshake',           'color' => '#009688', 'url' => '/flujo/ventas/historico'],
        8  => ['titulo' => 'Flujo Finalizado',        'icono' => 'fa-check-circle',        'color' => '#4CAF50', 'url' => '/flujo/ventas/historico'],
        9  => ['titulo' => 'Revisión de Inventario',  'icono' => 'fa-boxes',               'color' => '#FF9800', 'url' => '/flujo/revicion_inventario'],
        10 => ['titulo' => 'Revisión de Crédito',     'icono' => 'fa-credit-card',         'color' => '#2196F3', 'url' => '/flujo/revision_creditos'],
        11 => ['titulo' => 'Prefactura Anulada',       'icono' => 'fa-times-circle',        'color' => '#F44336', 'url' => '/flujo/ventas/historico'],
    ];

    public function __construct(
        private readonly int   $flujoId,
        private readonly int   $tipoTramiteId,
        private readonly array $contexto = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $cfg     = self::TRAMITE_CONFIG[$this->tipoTramiteId] ?? [
            'titulo' => 'Notificación de Flujo',
            'icono'  => 'fa-bell',
            'color'  => '#9E9E9E',
            'url'    => '/flujo/ventas/historico',
        ];

        $cliente    = $this->contexto['cliente']    ?? 'Cliente sin nombre';
        $monto      = $this->contexto['monto']      ?? null;
        $referencia = $this->contexto['referencia'] ?? null;

        $mensaje = 'Flujo #' . $this->flujoId . ' · ' . $cliente;
        if ($monto !== null) {
            $mensaje .= ' | L ' . number_format((float) $monto, 2, '.', ',');
        }
        if ($referencia) {
            $mensaje .= ' · ' . $referencia;
        }

        return [
            'flujo_id'        => $this->flujoId,
            'tipo_tramite_id' => $this->tipoTramiteId,
            'titulo'          => $cfg['titulo'],
            'mensaje'         => $mensaje,
            'url'             => $cfg['url'] . '?flujo_id=' . $this->flujoId,
            'icono'           => $cfg['icono'],
            'color'           => $cfg['color'],
            'cliente'         => $cliente,
            'monto'           => $monto,
        ];
    }
}
