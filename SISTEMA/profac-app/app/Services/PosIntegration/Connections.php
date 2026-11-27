<?php

namespace App\Services\PosIntegration;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Connections
{
    public function probe(string $url, string $secret): array
    {
        abort_unless(Settings::validPeerUrl($url) && strlen($secret) >= 32, 422, 'Se requieren URL y clave válidas.');
        try {
            $timestamp = (string) time();
            $response = Http::timeout(5)->withoutRedirecting()->withHeaders([
                'X-Integration-Timestamp' => $timestamp,
                'X-Integration-Signature' => hash_hmac('sha256', $timestamp.'.', $secret),
                'Accept' => 'application/json',
            ])->get(rtrim($url, '/').'/api/pos-integration/health');
            $body = $response->json();
            if (!$response->successful() || !is_array($body) || ($body['schema_version'] ?? null) !== 1
                || ($body['source'] ?? null) !== 'pos' || !is_bool($body['enabled'] ?? null)
                || !array_key_exists('worker_last_seen_at', $body)
                || (!is_null($body['worker_last_seen_at']) && !is_string($body['worker_last_seen_at']))
                || !is_string($body['instance_id'] ?? null) || !Str::isUuid($body['instance_id'])
                || !is_bool($body['coordinator_online'] ?? null)
                || !is_string($body['worker_state'] ?? null) || !is_bool($body['prepared'] ?? null)) {
                abort(502, 'El POS no respondió con un health válido.');
            }
            return $body;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            throw $e;
        } catch (\Throwable $e) {
            abort(502, 'No fue posible contactar al POS.');
        }
    }

    public function pending(): bool
    {
        return DB::table('integration_operations')->whereIn('status', ['pending', 'running'])->exists()
            || DB::table('pos_integration_transfers')->whereIn('status',             ['pending', 'dispatched', 'partial', 'rejected'])->exists()
            || DB::table('pos_integration_inbox')->where('status', 'pending')->exists()
            || DB::table('pos_integration_outbox')->whereIn('status', ['pending', 'failed'])->exists();
    }

    public function hasIntegratedData(): bool
    {
        foreach (['pos_integration_inbox', 'pos_integration_outbox', 'pos_integration_transfers',
                  'pos_integration_mirror', 'pos_integration_movements'] as $table) {
            if (DB::table($table)->exists()) {
                return true;
            }
        }
        return false;
    }

    public function activate(int $id, int $actor): void
    {
        $profile = DB::table('integration_connections')->where('id', $id)->first();
        abort_unless($profile && $profile->secret, 422, 'El perfil necesita una clave.');
        try {
            $secret = Crypt::decryptString($profile->secret);
        } catch (\Throwable $e) {
            abort(422, 'No se pudo descifrar la clave del perfil.');
        }
        $peer = $this->probe($profile->peer_url, $secret);
        DB::transaction(function () use ($profile, $peer, $actor) {
            $runtime = DB::table('integration_runtime')->where('id', 1)->lockForUpdate()->first();
            $settings = DB::table('integration_settings')->where('id', 1)->lockForUpdate()->first();
            $current = DB::table('integration_connections')->where('id', $profile->id)->lockForUpdate()->first();
            abort_unless($runtime && $settings && $current && $current->peer_url === $profile->peer_url
                && $current->secret === $profile->secret && $current->store_id == $profile->store_id,
                409, 'El perfil cambió durante la prueba; inténtelo de nuevo.');
            abort_if($runtime->worker_requested || $runtime->worker_busy || $this->pending(), 409, 'Pause el worker y resuelva las operaciones y traslados pendientes.');
            abort_if($runtime->bound_peer_instance_id && $runtime->bound_peer_instance_id !== $peer['instance_id']
                && $this->hasIntegratedData(), 409, 'Otra instancia POS ya está vinculada a datos de integración.');
            DB::table('integration_connections')->where('active_slot', 1)->update(['active_slot' => null, 'updated_at' => now()]);
            DB::table('integration_connections')->where('id', $current->id)->update([
                'active_slot' => 1, 'peer_instance_id' => $peer['instance_id'], 'updated_by' => $actor, 'updated_at' => now(),
            ]);
            DB::table('integration_settings')->where('id', 1)->update([
                'connection_id' => $current->id, 'enabled' => true, 'peer_url' => $current->peer_url,
                'store_id' => $current->store_id, 'secret' => $current->secret, 'updated_by' => $actor, 'updated_at' => now(),
            ]);
            DB::table('integration_runtime')->where('id', 1)->update([
                'bound_peer_instance_id' => $peer['instance_id'], 'updated_at' => now(),
            ]);
        }, 3);
    }
}
