<?php

namespace App\Console\Commands;

use App\Jobs\AlertasRotacionInventarioJob;
use App\Models\AlertaRotacionConfig;
use App\Notifications\InventarioAlertaNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AlertasEvaluarRotacion extends Command
{
    protected $signature   = 'alertas:evaluar-rotacion';
    protected $description = 'Evalúa las reglas activas de rotación e inventario y envía notificaciones';

    public function handle(): int
    {
        $this->info('Evaluando reglas de rotación e inventario…');

        $reglas = AlertaRotacionConfig::activas()->get();

        if ($reglas->isEmpty()) {
            $this->warn('No hay reglas activas.');
            return Command::SUCCESS;
        }

        foreach ($reglas as $regla) {
            $usuarios  = $regla->resolverUsuariosDestino();
            $productos = $regla->getProductosAfectados();
            $yaEnviada = DB::table('notifications')
                ->where('type', InventarioAlertaNotification::class)
                ->where('created_at', '>=', now()->subHours(23))
                ->whereRaw("JSON_EXTRACT(data, '$.regla_id') = ?", [$regla->id])
                ->exists();

            if ($usuarios->isEmpty()) {
                $this->line("  □ [{$regla->nombre}] OMITIDA — sin usuarios destino");
                continue;
            }
            if ($productos->isEmpty()) {
                $this->line("  □ [{$regla->nombre}] OMITIDA — 0 productos cumplen el criterio hoy");
                continue;
            }
            if ($yaEnviada) {
                $this->line("  □ [{$regla->nombre}] OMITIDA — ya notificada en las últimas 23h (dedup)");
                continue;
            }

            $this->line("  ✓ [{$regla->nombre}] ENVIANDO — {$productos->count()} productos, {$usuarios->count()} usuario(s)");
        }

        (new AlertasRotacionInventarioJob())->handle();

        $this->info('Listo.');
        return Command::SUCCESS;
    }
}
