<?php

namespace App\Jobs;

use App\Models\AlertaRotacionConfig;
use App\Notifications\InventarioAlertaNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Evalúa diariamente las reglas activas de rotación e inventario.
 * Por cada regla envía UNA sola notificación con el conteo total de productos
 * afectados, enlazando al informe detallado en /alertas/rotacion/{id}/reporte.
 */
class AlertasRotacionInventarioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $reglas = AlertaRotacionConfig::activas()->get();

        foreach ($reglas as $regla) {
            $usuarios = $regla->resolverUsuariosDestino();
            if ($usuarios->isEmpty()) {
                continue;
            }

            $productos = $regla->getProductosAfectados();
            if ($productos->isEmpty()) {
                continue;
            }

            // Una sola notificación por regla cada 23 horas
            if ($this->reglaYaNotificada($regla->id)) {
                continue;
            }

            $count   = $productos->count();
            $resumen = $productos->take(3)->pluck('producto_nombre')->toArray();
            [$titulo, $mensaje] = $this->mensajeResumen($regla, $count, $resumen);

            Notification::send($usuarios, new InventarioAlertaNotification(
                reglaId:          $regla->id,
                reglaNombre:      $regla->nombre,
                tipoAlerta:       $regla->tipo,
                prioridad:        $regla->prioridad,
                titulo:           $titulo,
                mensaje:          $mensaje,
                icono:            $regla->icono,
                color:            $regla->color,
                productosCount:   $count,
                productosResumen: $resumen,
            ));
        }
    }

    // ─── Deduplicación ────────────────────────────────────────────────────────

    private function reglaYaNotificada(int $reglaId): bool
    {
        // Dedup de 23h: evita re-enviar la misma regla dentro del mismo día.
        return DB::table('notifications')
            ->where('type', InventarioAlertaNotification::class)
            ->where('created_at', '>=', now()->subHours(23))
            ->whereRaw("JSON_EXTRACT(data, '$.regla_id') = ?", [$reglaId])
            ->exists();
    }

    // ─── Mensaje resumen ──────────────────────────────────────────────────────

    private function mensajeResumen(AlertaRotacionConfig $regla, int $count, array $resumen): array
    {
        $lista = implode(', ', array_map(fn ($n) => "«{$n}»", $resumen));
        $mas   = $count > 3 ? ' y ' . ($count - 3) . ' más' : '';

        $titulo = match ($regla->tipo) {
            'recuperacion_proxima' => "⏰ {$count} producto(s) con recuperación próxima",
            'recuperacion_vencida' => "🚨 {$count} producto(s) con recuperación vencida",
            'sin_ventas'           => "📦 {$count} producto(s) sin ventas recientes",
            'baja_rotacion'        => "📉 {$count} producto(s) con baja rotación",
            'sobreinventario'      => "🏭 {$count} producto(s) en sobreinventario",
            'incremento_demanda'   => "📈 {$count} producto(s) con aumento de demanda",
            default                => "🔔 {$count} producto(s) — {$regla->nombre}",
        };

        $mensaje = "Regla «{$regla->nombre}»: {$lista}{$mas}. Toca para ver el informe completo.";

        return [$titulo, $mensaje];
    }
}
