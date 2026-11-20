<?php

namespace App\Console\Commands;

use App\Services\Auditoria\SincronizadorComprasVentas;
use Illuminate\Console\Command;
use Throwable;

class SincronizarAuditoriaComprasVentas extends Command
{
    protected $signature = 'auditoria:sincronizar-compras-ventas {--completa : Reprocesa todo el universo histórico}';
    protected $description = 'Sincroniza compras, ventas, comprobantes y ajustes de la auditoría de inventario';

    public function handle(SincronizadorComprasVentas $sincronizador): int
    {
        $this->info('Iniciando sincronización de auditoría...');

        try {
            $resultado = $sincronizador->sincronizar($this->option('completa'));
        } catch (Throwable $error) {
            $this->error($error->getMessage());
            return Command::FAILURE;
        }

        $this->table(
            [
                'Modo',
                'Compras',
                'Traslados excluidos',
                'Ventas',
                'Comprobantes',
                'Ajustes',
                'Reversas de ajustes',
            ],
            [[
                $resultado['modo'],
                $resultado['compras'],
                $resultado['compras_derivadas_eliminadas'],
                $resultado['ventas'],
                $resultado['comprobantes'],
                $resultado['ajustes'],
                $resultado['anulaciones_ajustes'],
            ]]
        );
        $this->info('Sincronización completada.');

        return Command::SUCCESS;
    }
}
