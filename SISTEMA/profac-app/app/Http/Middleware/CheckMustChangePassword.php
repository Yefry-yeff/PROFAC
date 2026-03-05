<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckMustChangePassword
{
    /**
     * Rutas que se excluyen de esta verificación (para evitar bucles de redirección)
     */
    protected $except = [
        'cambiar-contrasena',
        'cambiar-contrasena/guardar',
        'logout',
        'login',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Si el campo existe y está activo
            if (isset($user->must_change_password) && $user->must_change_password == 1) {
                // Permitir las rutas excluidas
                foreach ($this->except as $except) {
                    if ($request->is($except)) {
                        return $next($request);
                    }
                }

                // Redirigir a la pantalla de cambio obligatorio
                return redirect('/cambiar-contrasena');
            }
        }

        return $next($request);
    }
}
