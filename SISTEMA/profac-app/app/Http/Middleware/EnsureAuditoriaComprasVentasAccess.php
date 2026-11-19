<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuditoriaComprasVentasAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Auth::user();
        $emailAutorizado = mb_strtolower(trim((string) config('auditoria.compras_ventas_email')));
        $emailUsuario = mb_strtolower(trim((string) ($usuario?->email ?? '')));

        abort_unless(
            $usuario && $emailAutorizado !== '' && hash_equals($emailAutorizado, $emailUsuario),
            403,
            'No tiene autorización para acceder a la auditoría de compras y ventas.'
        );

        return $next($request);
    }
}
