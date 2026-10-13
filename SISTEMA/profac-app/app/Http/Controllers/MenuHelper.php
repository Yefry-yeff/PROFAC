<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $directos = DB::table('usuario_submenu')
            ->where('usuario_id', $usuario->id)
            ->get(['sub_menu_id', 'permitido']);
        $revocados = $directos->where('permitido', 0)->pluck('sub_menu_id')->map('intval')->all();
        $otorgados = $directos->where('permitido', 1)->pluck('sub_menu_id')->map('intval')->all();

        $menus = Menu::getMenusParaRoles($rolIds);
        $menus->each(function ($menu) use ($revocados) {
            $menu->setRelation('submenus', $menu->submenus->reject(function ($submenu) use ($revocados) {
                return in_array((int) $submenu->id, $revocados, true);
            })->values());
        });

        if ($otorgados) {
            $extras = \App\Models\SubMenu::activos()
                ->whereIn('id', $otorgados)
                ->whereHas('menu', fn ($query) => $query->where('estado_id', 1))
                ->with('menu')
                ->get();

            foreach ($extras as $submenu) {
                $menu = $menus->firstWhere('id', $submenu->menu_id);
                if ($menu) {
                    if (!$menu->submenus->contains('id', $submenu->id)) {
                        $menu->submenus->push($submenu);
                    }
                } else {
                    $submenu->menu->setRelation('submenus', collect([$submenu]));
                    $menus->push($submenu->menu);
                }
            }
        }

        return $menus->filter(fn ($menu) => $menu->submenus->isNotEmpty())->values();
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

        $directo = DB::table('usuario_submenu')
            ->where('usuario_id', $usuario->id)
            ->where('sub_menu_id', function ($query) use ($url) {
                $query->select('sm.id')
                    ->from('sub_menu as sm')
                    ->whereColumn('sm.id', 'usuario_submenu.sub_menu_id')
                    ->where('sm.url', $url)
                    ->limit(1);
            })
            ->first();

        if ($directo) {
            return (bool) $directo->permitido;
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
