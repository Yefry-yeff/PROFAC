<?php

namespace App\Services\PosIntegration;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class Coordinator
{
    public static function online($runtime): bool
    {
        return $runtime && $runtime->coordinator_token && $runtime->coordinator_heartbeat_at
            && $runtime->coordinator_lease_until
            && Carbon::parse($runtime->coordinator_heartbeat_at)->gte(now()->subSeconds(15))
            && Carbon::parse($runtime->coordinator_lease_until)->gt(now());
    }

    public static function state($runtime): string
    {
        if (!self::online($runtime)) {
            return 'offline';
        }
        return $runtime->worker_requested ? 'running' : ($runtime->worker_busy ? 'stopping' : 'paused');
    }

    public function acquire(string $token): bool
    {
        return DB::transaction(function () use ($token) {
            $runtime = DB::table('integration_runtime')->where('id', 1)->lockForUpdate()->first();
            if (!$runtime || ($runtime->coordinator_token && $runtime->coordinator_lease_until
                && Carbon::parse($runtime->coordinator_lease_until)->gt(now()))) {
                return false;
            }
            if ($runtime->current_operation_id) {
                DB::table('integration_operations')->where('id', $runtime->current_operation_id)->where('status', 'running')
                    ->update(['status' => 'failed', 'error' => 'Coordinador anterior interrumpido.',
                        'finished_at' => now(), 'updated_at' => now()]);
            }
            DB::table('integration_runtime')->where('id', 1)->update([
                'coordinator_token' => $token, 'coordinator_pid' => getmypid(),
                'coordinator_heartbeat_at' => now(), 'coordinator_lease_until' => now()->addSeconds(15),
                'current_operation_id' => null, 'worker_busy' => false, 'updated_at' => now(),
            ]);
            return true;
        }, 3);
    }

    public function heartbeat(string $token): bool
    {
        DB::table('integration_runtime')->where('id', 1)->where('coordinator_token', $token)
            ->where('coordinator_lease_until', '>', now())->update([
                'coordinator_heartbeat_at' => now(), 'coordinator_lease_until' => now()->addSeconds(15), 'updated_at' => now(),
            ]);
        return DB::table('integration_runtime')->where('id', 1)->where('coordinator_token', $token)
            ->where('coordinator_lease_until', '>', now())->exists();
    }

    public function release(string $token): void
    {
        DB::transaction(function () use ($token) {
            $runtime = DB::table('integration_runtime')->where('id', 1)->where('coordinator_token', $token)->lockForUpdate()->first();
            if (!$runtime) {
                return;
            }
            if ($runtime->current_operation_id) {
                DB::table('integration_operations')->where('id', $runtime->current_operation_id)->where('status', 'running')
                    ->update(['status' => 'failed', 'error' => 'Coordinador interrumpido.',
                        'finished_at' => now(), 'updated_at' => now()]);
            }
            DB::table('integration_runtime')->where('id', 1)->where('coordinator_token', $token)->update([
                'coordinator_token' => null, 'coordinator_pid' => null, 'coordinator_lease_until' => null,
                'current_operation_id' => null, 'worker_busy' => false, 'updated_at' => now(),
            ]);
        }, 3);
    }

    public function claim(string $token, bool $allowAutomatic = true): ?object
    {
        return DB::transaction(function () use ($token, $allowAutomatic) {
            $runtime = DB::table('integration_runtime')->where('id', 1)->lockForUpdate()->first();
            if (!$runtime || $runtime->coordinator_token !== $token || !self::online($runtime)) {
                return null;
            }
            $op = DB::table('integration_operations')->where('status', 'pending')->orderBy('created_at')->lockForUpdate()->first();
            if (!$op && $runtime->worker_requested && $allowAutomatic) {
                $connection = DB::table('integration_settings')->where('id', 1)->where('enabled', 1)->value('connection_id');
                if ($connection) {
                    $id = (string) Str::uuid();
                    DB::table('integration_operations')->insert([
                        'id' => $id, 'connection_id' => $connection, 'action' => 'workonce',
                        'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    $op = DB::table('integration_operations')->where('id', $id)->first();
                }
            }
            if (!$op) {
                return null;
            }
            $active = DB::table('integration_settings')->where('id', 1)->where('enabled', 1)->value('connection_id');
            $profile = $active ? DB::table('integration_connections')->where('id', $active)->where('active_slot', 1)->first() : null;
            if ($op->action !== 'workonce' || !$active || (int) $active !== (int) $op->connection_id
                || !$profile || !$runtime->bound_peer_instance_id || $profile->peer_instance_id !== $runtime->bound_peer_instance_id) {
                DB::table('integration_operations')->where('id', $op->id)->update([
                    'status' => 'failed', 'error' => 'Operación no permitida o perfil inactivo.',
                    'finished_at' => now(), 'updated_at' => now(),
                ]);
                return null;
            }
            DB::table('integration_operations')->where('id', $op->id)->update([
                'status' => 'running', 'started_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('integration_runtime')->where('id', 1)->update([
                'current_operation_id' => $op->id, 'worker_busy' => true, 'updated_at' => now(),
            ]);
            return $op;
        }, 3);
    }

    public function finish(string $token, string $id, ?int $pid, bool $success, ?string $error = null): void
    {
        DB::transaction(function () use ($token, $id, $pid, $success, $error) {
            $runtime = DB::table('integration_runtime')->where('id', 1)->lockForUpdate()->first();
            if (!$runtime || $runtime->coordinator_token !== $token || $runtime->current_operation_id !== $id) {
                \Log::warning('Integración POS: concesión de operación perdida.', ['operation_id' => $id]);
                return;
            }
            $query = DB::table('integration_operations')->where('id', $id)->where('status', 'running');
            $pid === null ? $query->whereNull('pid') : $query->where('pid', $pid);
            $changed = $query->update([
                'status' => $success ? 'succeeded' : 'failed', 'output' => $success ? 'Ronda procesada.' : null,
                'error' => $error, 'finished_at' => now(), 'updated_at' => now(),
            ]);
            if ($changed !== 1) {
                \Log::warning('Integración POS: concesión de operación perdida.', ['operation_id' => $id]);
                return;
            }
            DB::table('integration_runtime')->where('id', 1)->update([
                'current_operation_id' => null, 'worker_busy' => false, 'last_error' => $error, 'updated_at' => now(),
            ]);
        }, 3);
    }

    public function run(string $token, object $op, bool $inline, ?callable $stopping = null): void
    {
        $success = false;
        $error = null;
        $process = null;
        $pid = null;
        try {
            if ($inline) {
                $success = Artisan::call('pos-integration:work', ['--once' => true, '--coordinator-token' => $token, '--operation-id' => $op->id]) === 0;
            } else {
                $process = new Process([PHP_BINARY, base_path('artisan'), 'pos-integration:work', '--once',
                    '--coordinator-token='.$token, '--operation-id='.$op->id], base_path());
                $process->setTimeout(1800);
                $process->disableOutput();
                $process->start();
                while ($process->isRunning()) {
                    if ($stopping && $stopping()) {
                        $process->stop(10);
                        $error = 'Ronda interrumpida por detención del servicio.';
                        break;
                    }
                    if (!$this->heartbeat($token)) {
                        $process->stop(0);
                        return;
                    }
                    if ($pid === null) {
                        $recorded = DB::table('integration_operations')->where('id', $op->id)->value('pid');
                        $pid = $recorded === null ? null : (int) $recorded;
                    }
                    $process->checkTimeout();
                    sleep(2);
                }
                $success = !$error && $process->isSuccessful();
            }
            if (!$success && !$error) {
                $error = 'La ronda terminó con error.';
            }
        } catch (\Throwable $e) {
            if ($process && $process->isRunning()) {
                $process->stop(0);
            }
            $error = 'No se pudo completar la ronda ('.get_class($e).').';
        }
        if ($pid === null) {
            $recordedPid = DB::table('integration_operations')->where('id', $op->id)->value('pid');
            $pid = $recordedPid === null ? null : (int) $recordedPid;
        }
        if ($success && $pid === null) {
            $success = false;
            $error = 'La ronda no registró su proceso.';
        }
        $this->finish($token, $op->id, $pid, $success, $error);
    }
}
