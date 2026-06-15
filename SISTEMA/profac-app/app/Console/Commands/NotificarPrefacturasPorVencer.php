<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\PrefacturaVencimientoNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotificarPrefacturasPorVencer extends Command
{
    protected $signature = 'prefacturas:notificar-vencimiento';
    protected $description = 'Notifica al asesor cuando una prefactura activa vence en las próximas 24 horas';

    public function handle(): int
    {
        $ahora = now();
        $limite = now()->addHours(24);

        $prefacturas = DB::table('prefactura as p')
            ->leftJoin('flujo as f', 'f.id', '=', 'p.flujo_id')
            ->where('p.estado', 'activo')
            ->whereNotNull('p.fecha_vencimiento')
            ->select(
                'p.id',
                'p.flujo_id',
                'p.nombre_cliente',
                'p.vendedor',
                'p.users_id',
                'p.fecha_vencimiento',
                'f.id as flujo_numero'
            )
            ->get();

        $enviadas = 0;

        foreach ($prefacturas as $pref) {
            $vence = Carbon::parse($pref->fecha_vencimiento)->endOfDay();
            if ($vence->lt($ahora) || $vence->gt($limite)) {
                continue;
            }

            $asesorId = (int) ($pref->vendedor ?: $pref->users_id ?: 0);
            if ($asesorId <= 0) {
                continue;
            }

            $usuario = User::find($asesorId);
            if (!$usuario) {
                continue;
            }

            $yaNotificada = DB::table('notifications')
                ->where('type', PrefacturaVencimientoNotification::class)
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $asesorId)
                ->whereRaw("JSON_EXTRACT(data, '$.prefactura_id') = ?", [(int) $pref->id])
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.alerta')) = ?", ['prefactura_vencimiento_24h'])
                ->exists();

            if ($yaNotificada) {
                continue;
            }

            $fechaHuman = $vence->format('d/m/Y h:i A');
            $flujoId = (int) ($pref->flujo_numero ?: $pref->flujo_id ?: 0);
            $cliente = (string) ($pref->nombre_cliente ?: 'Cliente sin nombre');

            $usuario->notify(new PrefacturaVencimientoNotification(
                (int) $pref->id,
                $flujoId,
                $cliente,
                $fechaHuman
            ));

            $enviadas++;
        }

        $this->info('Notificaciones enviadas: ' . $enviadas);
        return Command::SUCCESS;
    }
}
