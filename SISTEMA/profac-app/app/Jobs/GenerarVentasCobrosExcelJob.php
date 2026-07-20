<?php

namespace App\Jobs;

use App\Exports\ReporteVentasCobrosExport;
use App\Http\Livewire\Reportes\ReporteVentasCobros;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class GenerarVentasCobrosExcelJob
{
    use Dispatchable, Queueable, SerializesModels;

    public $timeout = 3600;
        public $memory = 2048;

    public function __construct(array $payload, string $token, int $userId, string $usuario)
    {
        $this->payload = $payload;
        $this->token = $token;
        $this->userId = $userId;
        $this->usuario = $usuario;
    }

    /** Umbral para omitir movimientos y loop por fila (superFastMode) */
    const SUPER_FAST_THRESHOLD = 8000;
    private function progress(string $statusKey, int $pct, string $msg = ''): void
    {
        Cache::put($statusKey, [
            'status'     => 'processing',
            'user_id'    => $this->userId,
            'created_at' => now()->toDateTimeString(),
            'progress'   => $pct,
            'message'    => $msg,
        ], now()->addHours(6));
    }

    public function handle(): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '2048M');

        $statusKey = 'rvc_export_status_' . $this->token;

        try {
            $this->progress($statusKey, 10, 'Iniciando consulta...');

            $ctrl = app(ReporteVentasCobros::class);

            $this->progress($statusKey, 20, 'Consultando facturas...');
            $rows     = $ctrl->buildExcelRowsFromPayload($this->payload);
            $rowCount = count($rows);

            // Para datasets grandes (>8K facturas) se omiten los movimientos de detalle
            // para evitar 14+ queries UNION ALL que pueden tardar varios minutos.
            $superFastMode = $rowCount >= self::SUPER_FAST_THRESHOLD;

            if ($superFastMode) {
                // Para datasets grandes: usa JOIN directo con filtros en vez de IN(N IDs)
                $this->progress($statusKey, 50, "Consultando movimientos ({$rowCount} facturas)...");
                $movimientos = $ctrl->buildExcelMovimientosFromPayload($this->payload);
                $fastMode    = true;
                $totalMovs   = 0;
                foreach ($movimientos as $ms) { $totalMovs += count($ms); }
            } else {
                $this->progress($statusKey, 45, 'Consultando movimientos...');
                $facturaIds  = array_map(fn($r) => (int) $r->factura_id, $rows);
                $movimientos = $ctrl->buildExcelMovimientosFromFacturaIds($facturaIds);

                $totalMovs = 0;
                foreach ($movimientos as $ms) { $totalMovs += count($ms); }
                $fastMode  = ($rowCount + $totalMovs) > 4000;

                $this->progress($statusKey, 60, 'Generando archivo Excel...');
            }

            $this->progress($statusKey, 65, 'Construyendo filas del reporte...');

            $fileName     = 'ReporteVentasCobros_' . now()->format('Y-m-d_H-i-s') . '_' . substr($this->token, 0, 8) . '.xlsx';
            $relativePath = 'exports/ventas-cobros/' . $fileName;

            $this->progress($statusKey, 72, 'Escribiendo Excel...');

            Excel::store(
                new ReporteVentasCobrosExport($rows, $this->usuario, $movimientos, $fastMode, $superFastMode, false),
                $relativePath,
                'local'
            );

            Cache::put($statusKey, [
                'status'     => 'ready',
                'user_id'    => $this->userId,
                'created_at' => now()->toDateTimeString(),
                'progress'   => 100,
                'file'       => $relativePath,
                'file_name'  => $fileName,
            ], now()->addHours(6));

        } catch (\Throwable $e) {
            Cache::put($statusKey, [
                'status'     => 'failed',
                'user_id'    => $this->userId,
                'created_at' => now()->toDateTimeString(),
                'progress'   => 100,
                'message'    => $e->getMessage(),
            ], now()->addHours(6));
        }
    }
}
