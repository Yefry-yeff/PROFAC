<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

/**
 * Notifica en tiempo real (Reverb) un cambio de revisión de una línea de
 * producto en la bandeja de Revisión de Inventario.
 */
class RevisionInventarioLineaActualizada implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $flujoId,
        public int $cotizacionId,
        public int $lineaId,
        public bool $revisado,
        public ?string $observacion,
        public ?int $usuarioId,
        public ?string $usuarioNombre,
        public string $accionAt,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('revision-inventario.' . $this->flujoId . '.' . $this->cotizacionId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'revision.actualizada';
    }

    public function broadcastWith(): array
    {
        return [
            'linea_id' => $this->lineaId,
            'revisado' => $this->revisado,
            'observacion' => $this->observacion,
            'usuario_id' => $this->usuarioId,
            'usuario_nombre' => $this->usuarioNombre,
            'accion_at' => $this->accionAt,
        ];
    }
}
