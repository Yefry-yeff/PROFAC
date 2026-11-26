<?php

namespace App\Http\Controllers;

use App\Services\PosIntegration\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class PaperlandSettingsController extends Controller
{
    private function authorizeSettings(Request $request): void
    {
        abort_unless($request->user() && $request->user()->tienePermiso('configuracion/api-paperland'), 403);
    }

    private function json(array $data, int $status = 200)
    {
        return response()->json($data, $status)->header('Cache-Control', 'no-store, private');
    }

    public function index(Request $request)
    {
        $this->authorizeSettings($request);
        if (Schema::hasTable('integration_connections')) {
            return app(PaperlandConnectionsController::class)->index($request);
        }
        abort_unless(Schema::hasTable('integration_settings'), 503, 'Configuración de integración no instalada.');
        $row = DB::table('integration_settings')->where('id', 1)->first();
        abort_unless($row, 503, 'Configuración de integración no disponible.');
        $settings = [
            'enabled' => (bool) config('pos_integration.enabled'),
            'peer_url' => $row->peer_url,
            'store_id' => (int) $row->store_id,
            'has_secret' => $row->secret !== null && $row->secret !== '',
        ];
        $outbox = DB::table('pos_integration_outbox')->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');
        $inboxPending = DB::table('pos_integration_inbox')->where('status', 'pending')->count();
        $lastError = DB::table('pos_integration_outbox')->whereNotNull('error')->orderByDesc('updated_at')->value('error');
        try {
            $workerLastSeen = Cache::get('pos_integration.worker_last_seen_at');
        } catch (\Throwable $e) {
            $workerLastSeen = null;
        }
        return response()->view('pos-integration.settings', compact('settings', 'outbox', 'inboxPending', 'lastError', 'workerLastSeen'))
            ->header('Cache-Control', 'no-store, private');
    }

    public function save(Request $request, Settings $settings)
    {
        $this->authorizeSettings($request);
        abort_if(Schema::hasTable('integration_connections'), 410, 'La pantalla cambió: recargue la página y use los perfiles de conexión.');
        $validator = Validator::make($request->all(), [
            'enabled' => 'required|boolean',
            'peer_url' => 'nullable|string|max:2048',
            'store_id' => 'required|integer|min:1|max:4294967295',
            'secret' => 'nullable|string|min:32|max:512',
        ]);
        if ($validator->fails()) {
            return $this->json(['message' => 'Revise los campos.', 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $url = trim($data['peer_url'] ?? '');
        if ($url !== '' && !Settings::validPeerUrl($url)) {
            return $this->json(['message' => 'La URL debe ser un origen HTTP(S) válido sin credenciales ni ruta.'], 422);
        }
        try {
            DB::transaction(function () use ($data, $url, $request) {
                $row = DB::table('integration_settings')->where('id', 1)->lockForUpdate()->first();
                abort_unless($row, 503, 'Configuración de integración no disponible.');
                $secret = $data['secret'] ?? null;
                if ($secret === null || $secret === '') {
                    $secret = $row->secret ? Crypt::decryptString($row->secret) : null;
                }
                if ($data['enabled'] && (!Settings::validPeerUrl($url) || !is_string($secret) || strlen($secret) < 32)) {
                    abort(422, 'Para habilitar se requieren URL válida y clave de al menos 32 caracteres.');
                }
                $changes = ['enabled' => (bool) $data['enabled'], 'peer_url' => $url,
                    'store_id' => (int) $data['store_id'], 'updated_by' => $request->user()->id, 'updated_at' => now()];
                if (isset($data['secret']) && $data['secret'] !== '') {
                    $changes['secret'] = Crypt::encryptString($data['secret']);
                }
                DB::table('integration_settings')->where('id', 1)->update($changes);
            }, 3);
            $settings->reload();
            return $this->json(['message' => 'Configuración guardada.']);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            return $this->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Throwable $e) {
            \Log::error('No se pudo guardar configuración POS.', ['exception' => get_class($e)]);
            return $this->json(['message' => 'No se pudo guardar la configuración.'], 500);
        }
    }

    public function generate(Request $request)
    {
        $this->authorizeSettings($request);
        return $this->json(['secret' => bin2hex(random_bytes(32))]);
    }

    public function testConnection(Request $request, Settings $settings)
    {
        $this->authorizeSettings($request);
        $settings->reload();
        $validator = Validator::make($request->all(), [
            'peer_url' => 'nullable|string|max:2048',
            'secret' => 'nullable|string|min:32|max:512',
        ]);
        if ($validator->fails()) {
            return $this->json(['message' => 'Revise la URL y la clave.'], 422);
        }
        $data = $validator->validated();
        $secret = $data['secret'] ?? config('pos_integration.secret');
        $url = trim($data['peer_url'] ?? config('pos_integration.peer_url') ?? '');
        if (!is_string($secret) || strlen($secret) < 32 || !Settings::validPeerUrl($url)) {
            return $this->json(['message' => 'Se requieren URL y clave válidas para probar.'], 422);
        }
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
                || (!is_null($body['worker_last_seen_at']) && !is_string($body['worker_last_seen_at']))) {
                return $this->json(['message' => 'El otro sistema no respondió con un health válido.'], 502);
            }
            return $this->json(['message' => 'Conexión y firma verificadas.', 'peer_enabled' => $body['enabled'],
                'worker_last_seen_at' => $body['worker_last_seen_at']]);
        } catch (\Throwable $e) {
            return $this->json(['message' => 'No fue posible contactar al otro sistema.'], 502);
        }
    }
}
