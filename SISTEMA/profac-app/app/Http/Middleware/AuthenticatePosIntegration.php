<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuthenticatePosIntegration
{
    public function handle(Request $request, Closure $next, string $mode = '')
    {
        if ($request->is('api/pos-integration/events') && Schema::hasTable('integration_runtime')) {
            return DB::transaction(function () use ($request, $next, $mode) {
                $runtime = DB::table('integration_runtime')->where('id', 1)->lockForUpdate()->first();
                app(\App\Services\PosIntegration\Settings::class)->reload();
                if (!$runtime || !$runtime->bound_peer_instance_id) {
                    return response()->json(['error' => 'Peer identity not verified'], 503);
                }
                return $this->authenticate($request, $next, $mode);
            }, 3);
        }
        return $this->authenticate($request, $next, $mode);
    }

    private function authenticate(Request $request, Closure $next, string $mode)
    {
        $secret = config('pos_integration.secret');
        $timestamp = $request->header('X-Integration-Timestamp', '');
        $signature = $request->header('X-Integration-Signature', '');
        if ((!config('pos_integration.enabled') && $mode !== 'health') || !is_string($secret) || $secret === '') {
            return response()->json(['error' => 'Integration unavailable'], 503);
        }
        if (($request->isMethod('GET') && $request->getContent() !== '')
            || !preg_match('/^\d{1,12}$/D', $timestamp) || abs(time() - (int) $timestamp) > 300
            || !preg_match('/^[a-f0-9]{64}$/D', $signature)
            || !hash_equals(hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret), $signature)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
