<?php

namespace App\Listeners;

use App\Events\FlujoAvanzadoEvent;
use App\Models\NotificacionFlujoConfig;
use App\Notifications\FlujoNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class NotificarPersonalFlujoListener
{
    public function handle(FlujoAvanzadoEvent $event): void
    {
        \Log::info('NotificarPersonalFlujoListener::handle invoked', [
            'flujo_id'        => $event->flujoId,
            'tipo_tramite_id' => $event->tipoTramiteId,
        ]);

        // Respetar interruptor global (se activa desde pantalla de configuración)
        if (!Cache::get('notificaciones_sistema_activo', false)) {
            \Log::info('NotificacionFlujo: sistema desactivado, saltando.');
            return;
        }

        // Obtener todas las reglas activas para este tipo de tramite
        $configs = NotificacionFlujoConfig::with(['rol', 'area', 'nivelMax'])
            ->activos()
            ->paraTramite($event->tipoTramiteId)
            ->get();

        if ($configs->isEmpty()) {
            \Log::info('NotificacionFlujo: sin reglas para tipo_tramite_id=' . $event->tipoTramiteId);
            return;
        }

        // Acumular usuarios destino evitando duplicados
        $usuariosNotificados = collect();

        foreach ($configs as $config) {
            $usuarios = $config->resolverUsuariosDestino();
            $usuariosNotificados = $usuariosNotificados->merge($usuarios);
        }

        // Eliminar duplicados por ID de usuario
        $usuariosUnicos = $usuariosNotificados->unique('id');

        if ($usuariosUnicos->isEmpty()) {
            \Log::info('NotificacionFlujo: sin usuarios destino para tipo_tramite_id=' . $event->tipoTramiteId);
            return;
        }

        \Log::info('NotificacionFlujo: enviando a ' . $usuariosUnicos->count() . ' usuarios');

        // Disparar las notificaciones (se guardan en tabla 'notifications')
        Notification::send(
            $usuariosUnicos,
            new FlujoNotification($event->flujoId, $event->tipoTramiteId, $event->contexto)
        );
    }
}

