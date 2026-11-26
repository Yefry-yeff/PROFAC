<?php

namespace App\Http\Middleware;

use App\Services\PosIntegration\Settings;
use Closure;
use Illuminate\Http\Request;

class LoadPosIntegrationSettings
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/pos-integration/*', 'translados/*', 'translado/*',
            'inventario/translado', 'producto/detalle/*', 'producto/apoyo/detalle/*',
            'configuracion/api-paperland*', 'livewire/update')) {
            app(Settings::class)->reload();
        }
        return $next($request);
    }
}
