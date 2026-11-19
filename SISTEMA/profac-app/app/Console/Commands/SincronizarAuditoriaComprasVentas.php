<?php

namespace App\Console\Commands;

use App\Services\Auditoria\SincronizadorComprasVentas;
use Illuminate\Console\Command;
use Throwable;

class SincronizarAuditoriaComprasVentas extends Command
{
    protected $signature = 'auditoria:sincronizar-compras-ventas {--completa : Reprocesa todo el universo histórico}';
    protected $description = 'Sincroniza manualmente la auditoría independiente de compras y ventas por lote';

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
            ['Modo', 'Compras afectadas', 'Traslados excluidos', 'Ventas afectadas'],
            [[
                $resultado['modo'],
                $resultado['compras'],
                $resultado['compras_derivadas_eliminadas'],
                $resultado['ventas'],
            ]]
        );
        $this->info('Sincronización completada.');

        return Command::SUCCESS;
    }
}
