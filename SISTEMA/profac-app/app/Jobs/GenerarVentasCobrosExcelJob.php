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

    private $payload;
    private $token;
    private $userId;
    private $usuario;

    public function __construct(array $payload, string $token, int $userId, string $usuario)
    {
        $this->payload = $payload;
        $this->token = $token;
        $this->userId = $userId;
        $this->usuario = $usuario;
    }

    public function handle(): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $statusKey = 'rvc_export_status_' . $this->token;

        try {
            Cache::put($statusKey, [
                'status' => 'processing',
                'user_id' => $this->userId,
                'created_at' => now()->toDateTimeString(),
                'progress' => 25,
            ], now()->addHours(6));

            $ctrl = app(ReporteVentasCobros::class);
            $rows = $ctrl->buildExcelRowsFromPayload($this->payload);

            Cache::put($statusKey, [
                'status' => 'processing',
                'user_id' => $this->userId,
                'created_at' => now()->toDateTimeString(),
                'progress' => 60,
            ], now()->addHours(6));

            $facturaIds  = array_map(fn($r) => (int) $r->factura_id, $rows);
            $movimientos = $ctrl->buildExcelMovimientosFromFacturaIds($facturaIds);

            $totalMovs = 0;
            foreach ($movimientos as $ms) {
                $totalMovs += count($ms);
            }
            $fastMode = (count($rows) + $totalMovs) > 4000;

            $fileName = 'ReporteVentasCobros_' . now()->format('Y-m-d_H-i-s') . '_' . substr($this->token, 0, 8) . '.xlsx';
            $relativePath = 'exports/ventas-cobros/' . $fileName;

            Excel::store(
                new ReporteVentasCobrosExport($rows, $this->usuario, $movimientos, $fastMode),
                $relativePath,
                'local'
            );

            Cache::put($statusKey, [
                'status' => 'ready',
                'user_id' => $this->userId,
                'created_at' => now()->toDateTimeString(),
                'progress' => 100,
                'file' => $relativePath,
                'file_name' => $fileName,
            ], now()->addHours(6));
        } catch (\Throwable $e) {
            Cache::put($statusKey, [
                'status' => 'failed',
                'user_id' => $this->userId,
                'created_at' => now()->toDateTimeString(),
                'progress' => 100,
                'message' => $e->getMessage(),
            ], now()->addHours(6));
        }
    }
}
