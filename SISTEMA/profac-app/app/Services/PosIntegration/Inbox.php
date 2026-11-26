<?php

namespace App\Services\PosIntegration;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class Inbox
{
    public function receive(array $event, string $raw, bool $retry = true)
    {
        try {
            $status = DB::transaction(function () use ($event, $raw) {
                $existing = DB::table('pos_integration_inbox')->where('event_id', $event['event_id'])->lockForUpdate()->first();
                if ($existing) {
                    abort_unless(hash_equals($existing->payload_hash, hash('sha256', $raw)), 409, 'Event ID conflict');
                    return $existing->status === 'processed' ? 'duplicate' : 'pending';
                }
                DB::table('pos_integration_inbox')->insert([
                    'event_id' => $event['event_id'], 'payload_hash' => hash('sha256', $raw),
                    'payload' => $raw, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->process($event);
                DB::table('pos_integration_inbox')->where('event_id', $event['event_id'])
                    ->update(['status' => 'processed', 'updated_at' => now()]);
                return 'processed';
            }, 3);
            return response()->json(['status' => $status], $status === 'pending' ? 202 : 200);
        } catch (QueryException $e) {
            // A concurrent delivery may have won the unique event ID race.
            $existing = DB::table('pos_integration_inbox')->where('event_id', $event['event_id'])->first();
            if ($existing) {
                if (!hash_equals($existing->payload_hash, hash('sha256', $raw))) {
                    return response()->json(['error' => 'Event ID conflict'], 409);
                }
                return response()->json(['status' => $existing->status === 'processed' ? 'duplicate' : 'pending'], $existing->status === 'processed' ? 200 : 202);
            }
            if ($retry && (
                ($event['type'] === 'transfer.dispatched' && DB::table('pos_integration_transfers')->where('transfer_uuid', $event['data']['transfer_uuid'])->exists())
                || ($event['type'] === 'inventory.changed' && DB::table('pos_integration_mirror')
                    ->where('store_id', $event['data']['store_id'])->where('product_pos_id', $event['data']['product_pos_id'])->exists())
            )) {
                return $this->receive($event, $raw, false);
            }
            throw $e;
        }
    }

    public function process(array $event): void
    {
        $data = $event['data'];
        $now = now();
        if ($event['type'] === 'transfer.dispatched') {
            $hash = hash('sha256', json_encode($this->canonical($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $transfer = DB::table('pos_integration_transfers')->where('transfer_uuid', $data['transfer_uuid'])->lockForUpdate()->first();
            if ($transfer) {
                abort_unless($transfer->direction === 'in' && hash_equals($transfer->document_hash, $hash), 409, 'Transfer UUID conflict');
                return;
            }
            $id = DB::table('pos_integration_transfers')->insertGetId([
                'transfer_uuid' => $data['transfer_uuid'], 'direction' => 'in', 'status' => 'pending',
                'reference' => $data['reference'], 'document_hash' => $hash,
                'snapshot' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'actor_id' => $data['actor_id'] ?? null, 'created_at' => $now, 'updated_at' => $now,
            ]);
            foreach ($data['lines'] as $line) {
                DB::table('pos_integration_lines')->insert([
                    'transfer_id' => $id, 'line_uuid' => $line['line_uuid'],
                    'snapshot' => json_encode($line, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
            $this->movement($id, $event);
            return;
        }
        if ($event['type'] === 'inventory.changed') {
            $key = ['store_id' => $data['store_id'], 'product_pos_id' => $data['product_pos_id']];
            $row = DB::table('pos_integration_mirror')->where($key)->lockForUpdate()->first();
            if (!$row) {
                DB::table('pos_integration_mirror')->insert($key + [
                    'version' => $data['version'], 'snapshot' => json_encode($data),
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            } elseif ((int) $data['version'] > (int) $row->version) {
                DB::table('pos_integration_mirror')->where('id', $row->id)->update([
                    'version' => $data['version'], 'snapshot' => json_encode($data), 'updated_at' => $now,
                ]);
            }
            $this->movement(null, $event);
            return;
        }
        if ($event['type'] === 'transfer.returned') {
            $transfer = DB::table('pos_integration_transfers')->where('transfer_uuid', $data['transfer_uuid'])
                ->where('direction', 'in')->lockForUpdate()->first();
            abort_unless($transfer && $transfer->status === 'rejected', 409, 'Unknown or non-rejected transfer');
            DB::table('pos_integration_transfers')->where('id', $transfer->id)->update([
                'status' => 'returned', 'updated_at' => $now,
            ]);
            $this->movement($transfer->id, $event);
            return;
        }
        $transfer = DB::table('pos_integration_transfers')->where('transfer_uuid', $data['transfer_uuid'])
            ->where('direction', 'out')->lockForUpdate()->first();
        abort_unless($transfer && $transfer->status === 'dispatched', 409, 'Unknown or finalized transfer');
        if ($event['type'] === 'transfer.received') {
            $lines = DB::table('pos_integration_lines')->where('transfer_id', $transfer->id)->get()->keyBy('line_uuid');
            $totals = [];
            foreach ($data['allocations'] as $allocation) {
                abort_unless(isset($lines[$allocation['line_uuid']]) && $allocation['quantity_base'] % $allocation['factor'] === 0, 422, 'Invalid allocation');
                $totals[$allocation['line_uuid']] = ($totals[$allocation['line_uuid']] ?? 0) + $allocation['quantity_base'];
            }
            foreach ($lines as $line) {
                $snapshot = json_decode($line->snapshot, true);
                abort_unless(($totals[$line->line_uuid] ?? 0) === $snapshot['quantity_base'], 422, 'Incomplete allocation');
            }
        }
        DB::table('pos_integration_transfers')->where('id', $transfer->id)->update([
            'status' => $event['type'] === 'transfer.received' ? 'received' : 'rejected',
            'reason' => $data['reason'] ?? null, 'updated_at' => $now,
        ]);
        $this->movement($transfer->id, $event);
    }

    private function movement(?int $transferId, array $event): void
    {
        DB::table('pos_integration_movements')->insert([
            'transfer_id' => $transferId, 'event_id' => $event['event_id'], 'type' => $event['type'],
            'snapshot' => json_encode($event['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function canonical(array $value): array
    {
        if (!array_is_list($value)) {
            ksort($value);
        }
        foreach ($value as &$item) {
            if (is_array($item)) {
                $item = $this->canonical($item);
            }
        }
        return $value;
    }
}
