<?php

namespace App\Http\Controllers;

use App\Services\PosIntegration\Connections;
use App\Services\PosIntegration\Coordinator;
use App\Services\PosIntegration\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaperlandConnectionsController extends Controller
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
        $profiles = DB::table('integration_connections')->select('id', 'name', 'peer_url', 'store_id', 'active_slot', 'peer_instance_id')
            ->selectRaw('CASE WHEN secret IS NULL OR secret = ? THEN 0 ELSE 1 END AS has_secret', [''])
            ->orderBy('name')->get();
        $settings = DB::table('integration_settings')->where('id', 1)->select('enabled', 'connection_id')->first();
        $runtime = DB::table('integration_runtime')->where('id', 1)->first();
        $operations = DB::table('integration_operations')->select('id', 'connection_id', 'action', 'requested_by', 'status', 'started_at', 'finished_at', 'output', 'error')
            ->orderByDesc('created_at')->limit(30)->get();
        $outbox = DB::table('pos_integration_outbox')->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');
        $inboxPending = DB::table('pos_integration_inbox')->where('status', 'pending')->count();
        $lastOutboxError = DB::table('pos_integration_outbox')->whereNotNull('error')->orderByDesc('updated_at')->value('error');
        $lastInboxError = DB::table('pos_integration_inbox')->whereNotNull('error')->orderByDesc('updated_at')->value('error');
        $active = $profiles->firstWhere('active_slot', 1);
        $peerStatus = null;
        if ($active && $active->peer_instance_id) {
            $peerStatus = Cache::remember('paperland-peer-health-'.$active->id, 5, function () use ($active) {
                try {
                    $secret = DB::table('integration_connections')->where('id', $active->id)->value('secret');
                    $peer = app(Connections::class)->probe($active->peer_url, Crypt::decryptString($secret));
                    abort_unless($peer['instance_id'] === $active->peer_instance_id, 409, 'La instancia POS no coincide con la conexion guardada.');
                    return $peer;
                } catch (\Illuminate\Contracts\Encryption\DecryptException|\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
                    \Log::warning('Integracion: estado remoto no disponible', ['profile_id' => $active->id, 'exception' => get_class($e)]);
                    return ['error' => 'No se pudo verificar el estado del POS. Use Probar conexion.'];
                }
            });
        }
        if ($request->expectsJson()) {
            return $this->json(['online' => Coordinator::online($runtime), 'worker_state' => Coordinator::state($runtime),
                'peer' => $peerStatus, 'connection_id' => $settings->connection_id, 'enabled' => (bool) $settings->enabled]);
        }
        return response()->view('pos-integration.connections', [
            'profiles' => $profiles, 'settings' => $settings, 'runtime' => $runtime, 'operations' => $operations,
            'online' => Coordinator::online($runtime), 'workerState' => Coordinator::state($runtime),
            'outbox' => $outbox, 'inboxPending' => $inboxPending, 'lastOutboxError' => $lastOutboxError, 'lastInboxError' => $lastInboxError,
            'peerStatus' => $peerStatus,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function save(Request $request, Connections $connections, Settings $settings)
    {
        $this->authorizeSettings($request);
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|integer|min:1', 'name' => 'required|string|max:255',
            'peer_url' => 'nullable|string|max:2048', 'store_id' => 'required|integer|min:1|max:4294967295',
            'secret' => 'nullable|string|min:32|max:512',
        ]);
        if ($validator->fails()) {
            return $this->json(['message' => 'Revise los campos del perfil.'], 422);
        }
        $data = $validator->validated();
        $url = trim($data['peer_url'] ?? '');
        if ($url !== '' && !Settings::validPeerUrl($url)) {
            return $this->json(['message' => 'La URL debe ser un origen HTTP(S) válido sin credenciales ni ruta.'], 422);
        }
        DB::transaction(function () use ($data, $url, $request, $connections) {
            $runtime = DB::table('integration_runtime')->where('id', 1)->lockForUpdate()->first();
            $settingsRow = DB::table('integration_settings')->where('id', 1)->lockForUpdate()->first();
            abort_unless($runtime && $settingsRow, 503, 'Configuración no disponible.');
            $profile = !empty($data['id']) ? DB::table('integration_connections')->where('id', $data['id'])->lockForUpdate()->first() : null;
            abort_if(!empty($data['id']) && !$profile, 404, 'Perfil no encontrado.');
            $secretChanged = isset($data['secret']) && $data['secret'] !== '';
            $materialChanged = $profile && ($profile->peer_url !== $url || (int) $profile->store_id !== (int) $data['store_id'] || $secretChanged);
            if ($profile && $materialChanged) {
                abort_if(DB::table('integration_operations')->where('connection_id', $profile->id)->whereIn('status', ['pending', 'running'])->exists(),
                    409, 'El perfil tiene operaciones pendientes.');
                if ($profile->active_slot) {
                    abort_if($settingsRow->enabled || $runtime->worker_requested || $runtime->worker_busy || $connections->pending(),
                        409, 'Desactive y pause la conexión y resuelva los pendientes antes de editarla.');
                }
            }
            $changes = ['name' => trim($data['name']), 'peer_url' => $url, 'store_id' => (int) $data['store_id'],
                'updated_by' => $request->user()->id, 'updated_at' => now()];
            abort_if($changes['name'] === '', 422, 'El nombre es obligatorio.');
            if ($secretChanged) {
                $changes['secret'] = Crypt::encryptString($data['secret']);
            }
            if ($materialChanged) {
                $changes['peer_instance_id'] = null;
            }
            if ($profile) {
                DB::table('integration_connections')->where('id', $profile->id)->update($changes);
                if ($profile->active_slot && $materialChanged) {
                    DB::table('integration_settings')->where('id', 1)->update([
                        'enabled' => false, 'peer_url' => $url, 'store_id' => (int) $data['store_id'],
                        'secret' => $changes['secret'] ?? $profile->secret,
                        'updated_by' => $request->user()->id, 'updated_at' => now(),
                    ]);
                    DB::table('integration_runtime')->where('id', 1)->update(['inventory_ready' => false, 'updated_at' => now()]);
                }
            } else {
                $changes['created_at'] = now();
                DB::table('integration_connections')->insert($changes);
            }
        }, 3);
        $settings->reload();
        \Log::info('Integración POS: perfil guardado.', ['actor_id' => $request->user()->id, 'profile_id' => $data['id'] ?? null]);
        return $this->json(['message' => 'Perfil guardado.']);
    }

    public function activate(Request $request, Connections $connections, Settings $settings, int $id)
    {
        $this->authorizeSettings($request);
        $connections->activate($id, $request->user()->id);
        $settings->reload();
        \Log::info('Integración POS: perfil activado.', ['actor_id' => $request->user()->id, 'profile_id' => $id]);
        return $this->json(['message' => 'Perfil activado; el worker sigue pausado.']);
    }

    public function deactivate(Request $request, Connections $connections, Settings $settings)
    {
        $this->authorizeSettings($request);
        DB::transaction(function () use ($connections, $request) {
            $runtime = DB::table('integration_runtime')->where('id', 1)->lockForUpdate()->first();
            abort_unless($runtime, 503);
            abort_if($runtime->worker_requested || $runtime->worker_busy || $connections->pending(), 409,
                'Pause el worker y resuelva las operaciones y traslados pendientes.');
            DB::table('integration_settings')->where('id', 1)->update([
                'enabled' => false, 'updated_by' => $request->user()->id, 'updated_at' => now(),
            ]);
        }, 3);
        $settings->reload();
        \Log::info('Integración POS: conexión deshabilitada.', ['actor_id' => $request->user()->id]);
        return $this->json(['message' => 'Sincronización deshabilitada; se conserva el perfil seleccionado.']);
    }

    public function delete(Request $request, Connections $connections, int $id)
    {
        $this->authorizeSettings($request);
        DB::transaction(function () use ($connections, $id) {
            $runtime = DB::table('integration_runtime')->where('id', 1)->lockForUpdate()->first();
            $profile = DB::table('integration_connections')->where('id', $id)->lockForUpdate()->first();
            abort_unless($runtime && $profile, 404);
            abort_if($profile->active_slot || $runtime->worker_busy || $connections->pending(), 409,
                'No se puede eliminar el perfil activo ni mientras existan operaciones o traslados pendientes.');
            DB::table('integration_connections')->where('id', $id)->delete();
        }, 3);
        \Log::info('Integración POS: perfil eliminado.', ['actor_id' => $request->user()->id, 'profile_id' => $id]);
        return $this->json(['message' => 'Perfil eliminado.']);
    }

    public function testProfile(Request $request, Connections $connections)
    {
        $this->authorizeSettings($request);
        $data = $request->validate(['id' => 'nullable|integer|min:1', 'peer_url' => 'nullable|string|max:2048',
            'secret' => 'nullable|string|min:32|max:512']);
        $profile = !empty($data['id']) ? DB::table('integration_connections')->where('id', $data['id'])->first() : null;
        abort_if(!empty($data['id']) && !$profile, 404, 'Perfil no encontrado.');
        try {
            $secret = $data['secret'] ?? ($profile && $profile->secret ? Crypt::decryptString($profile->secret) : '');
        } catch (\Throwable $e) {
            abort(422, 'No se pudo descifrar la clave del perfil.');
        }
        $peer = $connections->probe(trim($data['peer_url'] ?? ($profile->peer_url ?? '')), $secret);
        return $this->json(['message' => 'Conexión y firma verificadas.', 'peer_enabled' => $peer['enabled'],
            'instance_id' => $peer['instance_id'], 'coordinator_online' => $peer['coordinator_online'],
            'worker_state' => $peer['worker_state'], 'prepared' => $peer['prepared']]);
    }

    public function control(Request $request, string $action)
    {
        $this->authorizeSettings($request);
        abort_unless(in_array($action, ['start', 'pause', 'workonce'], true), 404);
        $input = $request->validate(['profile_id' => 'nullable|integer|min:1']);
        if ($action === 'start') {
            $settings = DB::table('integration_settings')->where('id', 1)->first();
            $runtime = DB::table('integration_runtime')->where('id', 1)->first();
            $profile = $settings?->connection_id ? DB::table('integration_connections')->where('id', $settings->connection_id)->where('active_slot', 1)->first() : null;
            abort_unless($settings?->enabled && $profile && $runtime?->bound_peer_instance_id
                && $profile->peer_instance_id === $runtime->bound_peer_instance_id, 409, 'Verifique y active el perfil antes de iniciar.');
            abort_if(isset($input['profile_id']) && (int) $input['profile_id'] !== (int) $profile->id, 409, 'El perfil ya no es la conexion activa. Actualice la pantalla.');
            app(\App\Services\PosIntegration\LocalSupervisor::class)->ensureOnline();
        }
        DB::transaction(function () use ($request, $action) {
            $runtime = DB::table('integration_runtime')->where('id', 1)->lockForUpdate()->first();
            $settings = DB::table('integration_settings')->where('id', 1)->lockForUpdate()->first();
            abort_unless($runtime && $settings, 503);
            if ($action !== 'pause') {
                abort_unless($settings->enabled && $settings->connection_id, 409, 'Active un perfil primero.');
                $profile = DB::table('integration_connections')->where('id', $settings->connection_id)->where('active_slot', 1)->first();
                abort_unless($profile && $runtime->bound_peer_instance_id && $profile->peer_instance_id === $runtime->bound_peer_instance_id,
                    409, 'Verifique y active el perfil para vincular la instancia POS.');
            }
            if ($action === 'start') {
                abort_unless(Coordinator::online($runtime), 409, 'El supervisor dejo de responder. No se inicio el worker.');
                abort_if($request->filled('profile_id') && (int) $request->input('profile_id') !== (int) $settings->connection_id,
                    409, 'La conexion activa cambio durante el inicio. Actualice la pantalla.');
            }
            if ($action === 'workonce') {
                abort_if($runtime->worker_busy || DB::table('integration_operations')->whereIn('status', ['pending', 'running'])->exists(),
                    409, 'Ya existe una operación pendiente o en ejecución.');
                DB::table('integration_operations')->insert([
                    'id' => (string) Str::uuid(), 'connection_id' => $settings->connection_id,
                    'action' => 'workonce', 'requested_by' => $request->user()->id,
                    'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
                ]);
            } else {
                DB::table('integration_runtime')->where('id', 1)->update([
                    'worker_requested' => $action === 'start', 'updated_at' => now(),
                ]);
            }
        }, 3);
        \Log::info('Integración POS: control solicitado.', ['actor_id' => $request->user()->id, 'action' => $action]);
        return $this->json(['message' => $action === 'pause'
            ? 'Pausa solicitada; la ronda en curso termina antes de detenerse.'
            : ($action === 'start' ? 'Supervisor en linea. Procesamiento automatico activado; no necesita ejecutar rondas manuales.' : 'Ronda encolada.')]);
    }

    public function systemd(Request $request)
    {
        $this->authorizeSettings($request);
        $quote = function (string $value): string {
            abort_if(preg_match('/[\x00-\x1f\x7f]/', $value), 500, 'Ruta inválida.');
            return '"'.str_replace(['\\', '"', '%'], ['\\\\', '\\"', '%%'], $value).'"';
        };
        $unit = "[Unit]\nDescription=PROFAC Paperland integration coordinator\nAfter=network.target\n\n[Service]\n"
            ."Type=simple\nUser=www-data\nWorkingDirectory=".$quote(base_path())."\n"
            ."ExecStart=".$quote((string) config('pos_integration.cli_php', '/usr/bin/php')).' '.$quote(base_path('artisan'))." pos-integration:manage\n"
            ."Restart=always\nRestartSec=5\nKillMode=mixed\nTimeoutStopSec=1860\n\n[Install]\nWantedBy=multi-user.target\n";
        return response($unit, 200, ['Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="profac-paperland.service"', 'Cache-Control' => 'no-store, private']);
    }
}
