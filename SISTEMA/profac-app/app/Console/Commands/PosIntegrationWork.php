<?php

namespace App\Console\Commands;

use App\Services\PosIntegration\Inbox;
use App\Services\PosIntegration\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PosIntegrationWork extends Command
{
    protected $signature = 'pos-integration:work {--once} {--coordinator-token=} {--operation-id=}';
    protected $description = 'Procesa el buzón y envía la cola persistente de integración POS';

    public function handle(Inbox $inbox, Settings $settings): int
    {
        if (Schema::hasTable('integration_runtime')) {
            $token = $this->option('coordinator-token');
            $operationId = $this->option('operation-id');
            $authorized = false;
            if ($this->option('once') && is_string($token) && $token !== '' && is_string($operationId)) {
                $authorized = DB::transaction(function () use ($token, $operationId) {
                    $runtime = DB::table('integration_runtime')->where('id', 1)->lockForUpdate()->first();
                    if (!$runtime || $runtime->coordinator_token !== $token || !$runtime->worker_busy
                        || $runtime->current_operation_id !== $operationId
                        || !$runtime->coordinator_lease_until || \Illuminate\Support\Carbon::parse($runtime->coordinator_lease_until)->lte(now())) {
                        return false;
                    }
                    return DB::table('integration_operations')->where('id', $operationId)->where('status', 'running')
                        ->where('action', 'workonce')->whereNull('pid')->update(['pid' => getmypid(), 'updated_at' => now()]) === 1;
                }, 3);
            }
            if (!$authorized) {
                $this->error('La ronda debe ser ejecutada por el coordinador.');
                return self::FAILURE;
            }
        }
        do {
            $settings->reload();
            try {
                Cache::put('pos_integration.worker_last_seen_at', now()->toIso8601String(), now()->addMinutes(5));
            } catch (\Throwable $e) {
                Log::warning('Integración POS: heartbeat no disponible', ['exception' => get_class($e)]);
            }
            if (config('pos_integration.enabled') && config('pos_integration.secret') && config('pos_integration.peer_url')) {
                $this->recover($inbox);
                $this->send();
            } elseif ($this->option('once')) {
                $this->info('Integración deshabilitada o configuración incompleta; sin envíos.');
            }
            if (!$this->option('once')) {
                sleep(5);
            }
        } while (!$this->option('once'));
        return self::SUCCESS;
    }

    private function recover(Inbox $inbox): void
    {
        $ids = DB::table('pos_integration_inbox')->where('status', 'pending')->orderBy('id')->limit(50)->pluck('id');
        foreach ($ids as $id) {
            try {
                DB::transaction(function () use ($id, $inbox) {
                    $row = DB::table('pos_integration_inbox')->where('id', $id)->lockForUpdate()->first();
                    if (!$row || $row->status !== 'pending') {
                        return;
                    }
                    $inbox->process(json_decode($row->payload, true, 512, JSON_THROW_ON_ERROR));
                    DB::table('pos_integration_inbox')->where('id', $id)->update([
                        'status' => 'processed', 'error' => null, 'updated_at' => now(),
                    ]);
                }, 3);
            } catch (\Throwable $e) {
                DB::table('pos_integration_inbox')->where('id', $id)->where('status', 'pending')->update([
                    'error' => 'Procesamiento fallido: '.get_class($e), 'updated_at' => now(),
                ]);
                Log::warning('Integración POS: procesamiento pendiente', ['inbox_id' => $id, 'exception' => get_class($e)]);
            }
        }
    }

    private function send(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $token = (string) Str::uuid();
            $row = DB::transaction(function () use ($token) {
                $row = DB::table('pos_integration_outbox')->where('status', 'pending')
                    ->where(function ($q) { $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()); })
                    ->where(function ($q) { $q->whereNull('locked_until')->orWhere('locked_until', '<', now()); })
                    ->orderBy('id')->lockForUpdate()->first();
                if ($row) {
                    DB::table('pos_integration_outbox')->where('id', $row->id)->update([
                        'lock_token' => $token, 'locked_until' => now()->addMinutes(2), 'updated_at' => now(),
                    ]);
                }
                return $row;
            }, 3);
            if (!$row) {
                break;
            }
            $attempts = $row->attempts + 1;
            $status = 'pending';
            $error = null;
            try {
                $timestamp = (string) time();
                $response = Http::timeout(30)->withoutRedirecting()->withHeaders([
                    'X-Integration-Timestamp' => $timestamp,
                    'X-Integration-Signature' => hash_hmac('sha256', $timestamp.'.'.$row->payload, config('pos_integration.secret')),
                ])->withBody($row->payload, 'application/json')->post(rtrim(config('pos_integration.peer_url'), '/').'/api/pos-integration/events');
                if ($response->successful() && in_array($response->json('status'), ['processed', 'pending', 'duplicate'], true)) {
                    $status = 'sent';
                } elseif ($response->status() >= 400 && $response->status() < 500 && !in_array($response->status(), [408, 429], true)) {
                    $status = 'failed';
                    $error = 'HTTP '.$response->status();
                } else {
                    $error = 'HTTP '.$response->status().' o respuesta inválida';
                }
            } catch (\Throwable $e) {
                $error = 'Error de transporte: '.get_class($e);
            }
            DB::table('pos_integration_outbox')->where('id', $row->id)->where('lock_token', $token)->update([
                'status' => $status, 'attempts' => $attempts, 'error' => $error,
                'next_attempt_at' => $status === 'pending' ? now()->addSeconds(min(3600, 5 * (2 ** min($attempts, 9)))) : null,
                'locked_until' => null, 'lock_token' => null, 'updated_at' => now(),
            ]);
            if ($error) {
                Log::warning('Integración POS: envío fallido', ['outbox_id' => $row->id, 'status' => $status, 'error' => $error]);
            }
        }
    }
}
