<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FlujoAvanzadoEvent
{
    use Dispatchable, SerializesModels;

    /**
     * @param  int    $flujoId         ID del flujo que avanzó de estado
     * @param  int    $tipoTramiteId   Nuevo tipo_tramite_id al que llegó el flujo
     * @param  array  $contexto        Datos adicionales para el mensaje de notificación
     *                                  Esperado: ['cliente' => '', 'monto' => 0, 'referencia' => '']
     */
    public function __construct(
        public readonly int   $flujoId,
        public readonly int   $tipoTramiteId,
        public readonly array $contexto = []
    ) {}
}
