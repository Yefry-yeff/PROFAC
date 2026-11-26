<?php

namespace App\Services\PosIntegration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class Outbox
{
    public function enqueue(string $type, array $data): string
    {
        $id = (string) Str::uuid();
        $event = [
            'schema_version' => 1, 'event_id' => $id, 'source' => 'profac',
            'type' => $type, 'occurred_at' => now()->toIso8601String(), 'data' => $data,
        ];
        DB::table('pos_integration_outbox')->insert([
            'event_id' => $id, 'payload' => json_encode($event, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);
        return $id;
    }

    public function captureTransfer(int $localId, int $actorId, array $logIds): void
    {
        if (!config('pos_integration.enabled')) {
            return;
        }
        if (!$logIds) {
            return;
        }
        if (count(array_unique($logIds)) !== count($logIds)) {
            throw new RuntimeException('No se puede sincronizar el traslado: registros duplicados.');
        }
        $logs = DB::table('log_translado as lt')
            ->join('recibido_bodega as origin', 'origin.id', '=', 'lt.origen')
            ->join('producto as p', 'p.id', '=', 'origin.producto_id')
            ->join('unidad_medida_venta as uv', 'uv.id', '=', 'lt.unidad_medida_venta_id')
            ->leftJoin('compra_has_producto as cp', function ($join) {
                $join->on('cp.compra_id', '=', 'origin.compra_id')->on('cp.producto_id', '=', 'origin.producto_id');
            })
            ->where('lt.translado_id', $localId)->where('lt.descripcion', 'Translado de bodega')
            ->whereNull('lt.destino')->whereIn('lt.id', $logIds)
            ->orderBy('lt.id')
            ->get(['lt.id', 'lt.cantidad', 'lt.origen', 'origin.fecha_expiracion', 'origin.compra_id',
                'p.id as producto_id', 'p.nombre', 'p.costo_promedio', 'uv.id as presentation_id',
                'uv.unidad_venta', 'uv.unidad_medida_id', 'uv.producto_id as presentation_product_id', 'cp.precio_unidad']);
        if ($logs->count() !== count($logIds) || $logs->pluck('id')->unique()->count() !== count($logIds)) {
            throw new RuntimeException('No se puede sincronizar el traslado: lote, presentación o costo ambiguo.');
        }
        $header = DB::table('translado')->where('id', $localId)->first();
        $lines = [];
        foreach ($logs as $log) {
            $cost = $log->compra_id === null ? $log->costo_promedio : $log->precio_unidad;
            $expiresAt = $log->fecha_expiracion ? substr($log->fecha_expiracion, 0, 10) : null;
            if ($cost === null || !preg_match('/^\d+(?:\.\d+)?$/D', (string) $cost) || (int) $log->cantidad <= 0
                || !PresentationFactor::valid($log->unidad_venta) || (int) $log->unidad_medida_id <= 0
                || (int) $log->presentation_product_id !== (int) $log->producto_id
                || ($expiresAt !== null && (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $expiresAt)
                    || !checkdate((int) substr($expiresAt, 5, 2), (int) substr($expiresAt, 8, 2), (int) substr($expiresAt, 0, 4))))) {
                throw new RuntimeException('No se puede sincronizar el traslado: falta costo o unidad válida del lote.');
            }
            $lines[] = [
                'line_uuid' => (string) Str::uuid(), 'product_profac_id' => (int) $log->producto_id,
                'product_pos_id' => null, 'name' => $log->nombre, 'quantity_base' => (int) $log->cantidad,
                'source_factor' => (int) $log->unidad_venta, 'unit_profac_id' => (int) $log->unidad_medida_id,
                'presentation_profac_id' => (int) $log->presentation_id, 'source_lot_id' => (int) $log->origen,
                'expires_at' => $expiresAt,
                'cost_base' => (string) $cost,
            ];
        }
        $data = [
            'transfer_uuid' => (string) Str::uuid(), 'reference' => $header->codigo,
            'source_store_id' => null, 'destination_store_id' => (int) config('pos_integration.store_id', 1),
            'comment' => $header->comentario, 'actor_id' => $actorId, 'lines' => $lines,
        ];
        $id = DB::table('pos_integration_transfers')->insertGetId([
            'transfer_uuid' => $data['transfer_uuid'], 'direction' => 'out', 'status' => 'dispatched',
            'reference' => $data['reference'], 'local_transfer_id' => $localId,
            'document_hash' => hash('sha256', json_encode($data)), 'snapshot' => json_encode($data),
            'actor_id' => $actorId, 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ($lines as $index => $line) {
            DB::table('pos_integration_lines')->insert([
                'transfer_id' => $id, 'line_uuid' => $line['line_uuid'], 'local_log_id' => $logs[$index]->id,
                'snapshot' => json_encode($line),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $eventId = $this->enqueue('transfer.dispatched', $data);
        DB::table('pos_integration_movements')->insert([
            'transfer_id' => $id, 'event_id' => $eventId, 'type' => 'transfer.dispatched',
            'snapshot' => json_encode($data), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
