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
        
        if (!$usuario->rol_id) {
            return collect();
        }

        return Menu::getMenusParaRol($usuario->rol_id);
    }

    /**
     * Verificar si el usuario tiene acceso a una URL específica
     */
    public static function tieneAcceso($url)
    {
        if (!Auth::check()) {
            return false;
        }

        $usuario = Auth::user();
        
        if (!$usuario->rol_id) {
            return false;
        }

        return \App\Models\SubMenu::activos()
            ->where('url', $url)
            ->whereHas('roles', function ($query) use ($usuario) {
                $query->where('rol_id', $usuario->rol_id);
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
