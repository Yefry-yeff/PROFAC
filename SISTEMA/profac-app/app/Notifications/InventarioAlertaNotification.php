<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notificación de alerta de rotación / inventario.
 *
 * Se almacena en la tabla `notifications` vía canal 'database'.
 * La campana (notificaciones-bell) ya soporta los campos:
 *   titulo, mensaje, url, icono, color
 *
 * Campos adicionales (para historial y filtros futuros):
 *   tipo_alerta, prioridad, producto_id, producto_nombre
 */
class InventarioAlertaNotification extends Notification
{
    public function __construct(
        private readonly int    $reglaId,
        private readonly string $reglaNombre,
        private readonly string $tipoAlerta,
        private readonly string $prioridad,
        private readonly string $titulo,
        private readonly string $mensaje,
        private readonly string $icono,
        private readonly string $color,
        private readonly int    $productosCount   = 0,
        private readonly array  $productosResumen = [],
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'titulo'            => $this->titulo,
            'mensaje'           => $this->mensaje,
            'url'               => '/alertas/rotacion/' . $this->reglaId . '/reporte',
            'icono'             => $this->icono,
            'color'             => $this->color,
            'tipo_alerta'       => $this->tipoAlerta,
            'prioridad'         => $this->prioridad,
            'regla_id'          => $this->reglaId,
            'regla_nombre'      => $this->reglaNombre,
            'productos_count'   => $this->productosCount,
            'productos_resumen' => $this->productosResumen,
        ];
    }
}
