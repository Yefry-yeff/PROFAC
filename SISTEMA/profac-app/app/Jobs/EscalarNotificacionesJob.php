<?php

namespace App\Jobs;

use App\Models\NotificacionFlujoConfig;
use App\Models\User;
use App\Notifications\FlujoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class EscalarNotificacionesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Obtener todas las reglas con escalación activa
        $configs = NotificacionFlujoConfig::activos()
            ->where('escalar_activo', true)
            ->whereNotNull('escalar_horas')
            ->whereNotNull('escalar_nivel_id')
            ->whereNotNull('area_id')
            ->get();

        foreach ($configs as $config) {
            $umbralFecha = now()->subHours($config->escalar_horas);

            // Buscar notificaciones de este tipo que:
            // 1. Sean del tipo correcto (FlujoNotification)
            // 2. Tengan más de N horas sin leer
            // 3. Aún no hayan sido escaladas (no existe notificación de escalación para ese flujo_id)
            $notificacionesPendientes = DB::table('notifications')
                ->where('type', FlujoNotification::class)
                ->whereNull('read_at')
                ->where('created_at', '<=', $umbralFecha)
                ->whereRaw("JSON_EXTRACT(data, '$.tipo_tramite_id') = ?", [$config->tipo_tramite_id])
                ->whereRaw("JSON_EXTRACT(data, '$.escalada') IS NULL OR JSON_EXTRACT(data, '$.escalada') != 1")
                ->get()
                ->unique(fn ($n) => json_decode($n->data, true)['flujo_id'] ?? 0);

            if ($notificacionesPendientes->isEmpty()) {
                continue;
            }

            // Resolver usuarios de escalación para esta regla
            $usuariosEscalacion = $config->resolverUsuariosEscalacion();
            if ($usuariosEscalacion->isEmpty()) {
                continue;
            }

            foreach ($notificacionesPendientes as $notif) {
                $data    = json_decode($notif->data, true);
                $flujoId = $data['flujo_id'] ?? null;

                if (!$flujoId) continue;

                // Marcar la notificación original como escalada para no volver a procesarla
                DB::table('notifications')
                    ->where('id', $notif->id)
                    ->update([
                        'data' => json_encode(array_merge($data, ['escalada' => 1])),
                    ]);

                // Enviar notificación de escalación a los supervisores/jefes
                $contextoEscalado = array_merge($data, [
                    'cliente' => $data['cliente'] ?? 'N/A',
                    'monto'   => $data['monto']   ?? null,
                    'referencia' => '⚠️ ESCALADO — sin atender por ' . $config->escalar_horas . 'h',
                ]);

                Notification::send(
                    $usuariosEscalacion,
                    new FlujoNotification($flujoId, $config->tipo_tramite_id, $contextoEscalado)
                );
            }
        }
    }
}
