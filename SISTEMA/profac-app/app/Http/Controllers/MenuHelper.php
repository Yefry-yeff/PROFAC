<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Support\Facades\Auth;

class MenuHelper
{
    /**
     * Obtener menús del usuario autenticado basado en su rol
     */
    public static function getMenusUsuario()
    {
        if (!Auth::check()) {
            return collect();
        }

        $usuario = Auth::user();
        $rolIds  = $usuario->rolesIds();

        if (empty($rolIds)) {
            return collect();
        }

        return Menu::getMenusParaRoles($rolIds);
    }

    /**
     * Verificar si el usuario tiene acceso a una URL específica
     * (considerando TODOS sus roles: principal + adicionales)
     */
    public static function tieneAcceso($url)
    {
        if (!Auth::check()) {
            return false;
        }

        $usuario = Auth::user();
        $rolIds  = $usuario->rolesIds();

        if (empty($rolIds)) {
            return false;
        }

        return \App\Models\SubMenu::activos()
            ->where('url', $url)
            ->whereHas('roles', function ($query) use ($rolIds) {
                $query->whereIn('rol_id', $rolIds);
            })
            ->exists();
    }

    /**
     * Obtener el submenu activo basado en la URL actual
     */
    public static function getSubmenuActivo()
    {
        $urlActual = request()->path();
        
        return \App\Models\SubMenu::activos()
            ->where('url', $urlActual)
            ->first();
    }
}
