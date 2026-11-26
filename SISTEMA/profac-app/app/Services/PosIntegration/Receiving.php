<?php

namespace App\Services\PosIntegration;

use Illuminate\Support\Facades\DB;

class Receiving
{
    public function approve(string $uuid, int $actorId, array $allocations): void
    {
        DB::transaction(function () use ($uuid, $actorId, $allocations) {
            $transfer = DB::table('pos_integration_transfers')->where('transfer_uuid', $uuid)
                ->where('direction', 'in')->lockForUpdate()->first();
            abort_unless($transfer && $transfer->status === 'pending', 409, 'El traslado no está pendiente.');
            $lines = DB::table('pos_integration_lines')->where('transfer_id', $transfer->id)->get()->keyBy('line_uuid');
            $sums = [];
            $prepared = [];
            foreach ($allocations as $a) {
                abort_unless(isset($lines[$a['line_uuid']]), 422, 'Línea desconocida.');
                $line = json_decode($lines[$a['line_uuid']]->snapshot, true, 512, JSON_THROW_ON_ERROR);
                $product = DB::table('producto')->where('id', $line['product_profac_id'])->first();
                $sourceUnit = DB::table('unidad_medida')->where('id', $line['unit_profac_id'])->exists();
                $sourcePresentation = empty($line['presentation_profac_id']) || DB::table('unidad_medida_venta')
                    ->where('id', $line['presentation_profac_id'])->where('producto_id', $line['product_profac_id'])
                    ->where('unidad_medida_id', $line['unit_profac_id'])->exists();
                abort_unless($product && $sourceUnit && $sourcePresentation, 422, 'Producto o unidad de origen sin correspondencia.');
                $presentation = DB::table('unidad_medida_venta')->where('id', $a['presentation_id'])
                    ->where('producto_id', $line['product_profac_id'])->where('estado_id', 1)->first();
                $location = DB::table('seccion as s')->join('segmento as g', 'g.id', '=', 's.segmento_id')
                    ->join('bodega as b', 'b.id', '=', 'g.bodega_id')
                    ->where('s.id', $a['seccion_id'])->where('g.id', $a['segmento_id'])
                    ->where('b.id', $a['bodega_id'])->where('s.id', '<>', 433)->exists();
                abort_unless($presentation && $location && PresentationFactor::valid($presentation->unidad_venta), 422, 'Presentación o ubicación inválida.');
                $factor = (int) $presentation->unidad_venta;
                abort_if((int) $a['quantity'] > intdiv(PHP_INT_MAX, $factor), 422, 'Cantidad demasiado grande.');
                $quantity = (int) $a['quantity'] * $factor;
                $key = $a['line_uuid'];
                abort_if(($sums[$key] ?? 0) > (int) $line['quantity_base'] - $quantity, 422, 'La distribución excede la línea.');
                $sums[$key] = ($sums[$key] ?? 0) + $quantity;
                $prepared[] = [$line, $a, $presentation, $quantity];
            }
            foreach ($lines as $lineRow) {
                $line = json_decode($lineRow->snapshot, true, 512, JSON_THROW_ON_ERROR);
                abort_unless(($sums[$lineRow->line_uuid] ?? 0) === (int) $line['quantity_base'], 422, 'Distribuya exactamente todas las unidades base.');
            }
            $result = [];
            foreach ($prepared as [$line, $a, $presentation, $quantity]) {
                $lotId = DB::table('recibido_bodega')->insertGetId([
                    'compra_id' => null, 'producto_id' => $line['product_profac_id'], 'seccion_id' => $a['seccion_id'],
                    'cantidad_compra_lote' => $line['quantity_base'], 'cantidad_inicial_seccion' => $quantity,
                    'cantidad_disponible' => $quantity, 'fecha_recibido' => now(), 'fecha_expiracion' => $line['expires_at'] ?? null,
                    'estado_recibido' => 4, 'recibido_por' => $actorId, 'unidad_compra_id' => $presentation->unidad_medida_id,
                    'unidades_compra' => $presentation->unidad_venta, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $result[] = ['line_uuid' => $line['line_uuid'], 'lot_id' => $lotId,
                    'presentation_id' => (int) $presentation->id, 'factor' => (int) $presentation->unidad_venta,
                    'quantity_base' => $quantity, 'seccion_id' => (int) $a['seccion_id']];
            }
            $data = ['transfer_uuid' => $uuid, 'actor_id' => $actorId, 'allocations' => $result];
            $eventId = app(Outbox::class)->enqueue('transfer.received', $data);
            $this->finish($transfer->id, 'received', $actorId, null, $eventId, 'transfer.received', $data);
        }, 3);
    }

    public function reject(string $uuid, int $actorId, string $reason): void
    {
        DB::transaction(function () use ($uuid, $actorId, $reason) {
            $transfer = DB::table('pos_integration_transfers')->where('transfer_uuid', $uuid)
                ->where('direction', 'in')->lockForUpdate()->first();
            abort_unless($transfer && $transfer->status === 'pending', 409, 'El traslado no está pendiente.');
            $data = ['transfer_uuid' => $uuid, 'actor_id' => $actorId, 'reason' => $reason];
            $eventId = app(Outbox::class)->enqueue('transfer.rejected', $data);
            $this->finish($transfer->id, 'rejected', $actorId, $reason, $eventId, 'transfer.rejected', $data);
        }, 3);
    }

    public function confirmReturn(string $uuid, int $actorId): void
    {
        DB::transaction(function () use ($uuid, $actorId) {
            $transfer = DB::table('pos_integration_transfers')->where('transfer_uuid', $uuid)
                ->where('direction', 'out')->lockForUpdate()->first();
            abort_unless($transfer && $transfer->status === 'rejected' && $transfer->local_transfer_id, 409, 'El traslado no está rechazado.');
            $lines = DB::table('pos_integration_lines')->where('transfer_id', $transfer->id)->get();
            $logIds = $lines->pluck('local_log_id')->all();
            abort_unless($lines->count() && !in_array(null, $logIds, true) && count(array_unique($logIds)) === count($logIds), 409, 'Faltan registros originales.');
            $logs = DB::table('log_translado')->whereIn('id', $logIds)->where('translado_id', $transfer->local_transfer_id)
                ->where('descripcion', 'Translado de bodega')->whereNull('destino')->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            abort_unless($logs->count() === $lines->count(), 409, 'Los registros originales cambiaron.');
            $sources = []; $details = []; $products = [];
            foreach ($lines as $row) {
                $line = json_decode($row->snapshot, true, 512, JSON_THROW_ON_ERROR);
                $log = $logs[$row->local_log_id];
                abort_unless((int) $log->origen === (int) $line['source_lot_id']
                    && (int) $log->cantidad === (int) $line['quantity_base'] && (int) $log->cantidad > 0, 409, 'Los lotes originales no coinciden con el traslado.');
                $id = (int) $log->origen;
                abort_if(isset($products[$id]) && $products[$id] !== (int) $line['product_profac_id'], 409, 'Producto original inconsistente.');
                $products[$id] = (int) $line['product_profac_id'];
                $sources[$id] = ($sources[$id] ?? 0) + (int) $log->cantidad;
                $details[] = ['origen' => $id, 'quantity_base' => (int) $log->cantidad];
            }
            $ids = array_keys($sources);
            sort($ids);
            $lots = DB::table('recibido_bodega')->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            abort_unless($lots->count() === count($ids), 409, 'Falta un lote original.');
            foreach ($sources as $id => $quantity) {
                abort_unless((int) $lots[$id]->producto_id === $products[$id], 409, 'El lote original cambió de producto.');
                DB::table('recibido_bodega')->where('id', $id)->increment('cantidad_disponible', $quantity);
            }
            $data = ['transfer_uuid' => $uuid, 'actor_id' => $actorId];
            $eventId = app(Outbox::class)->enqueue('transfer.returned', $data);
            $this->finish($transfer->id, 'returned', $actorId, $transfer->reason, $eventId, 'transfer.returned', $data + ['return_lots' => $details]);
        }, 3);
    }

    private function finish(int $id, string $status, int $actorId, ?string $reason, string $eventId, string $type, array $data): void
    {
        DB::table('pos_integration_transfers')->where('id', $id)->update([
            'status' => $status, 'actor_id' => $actorId, 'reason' => $reason, 'updated_at' => now(),
        ]);
        DB::table('pos_integration_movements')->insert([
            'transfer_id' => $id, 'event_id' => $eventId, 'type' => $type, 'snapshot' => json_encode($data),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
