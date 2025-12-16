<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\SubMenu;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * Guardar nuevo menú
     */
    public function guardarMenu(Request $request)
    {
        try {
            $request->validate([
                'nombre_menu' => 'required|string|max:255',
                'icon' => 'required|string|max:255',
                'orden' => 'required|integer|min:1',
                'estado_id' => 'required|integer|exists:estado,id'
            ]);

            $menu = Menu::create($request->all());

            return response()->json([
                'success' => true,
                'mensaje' => 'Menú creado correctamente',
                'data' => $menu
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear el menú: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos de un menú
     */
    public function obtenerMenu($id)
    {
        try {
            $menu = Menu::findOrFail($id);
            return response()->json($menu);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Menú no encontrado'
            ], 404);
        }
    }

    /**
     * Actualizar menú
     */
    public function actualizarMenu(Request $request, $id)
    {
        try {
            $request->validate([
                'nombre_menu' => 'required|string|max:255',
                'icon' => 'required|string|max:255',
                'orden' => 'required|integer|min:1',
                'estado_id' => 'required|integer|exists:estado,id'
            ]);

            $menu = Menu::findOrFail($id);
            $menu->update($request->all());

            return response()->json([
                'success' => true,
                'mensaje' => 'Menú actualizado correctamente',
                'data' => $menu
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al actualizar el menú: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar nuevo submenu
     */
    public function guardarSubmenu(Request $request)
    {
        try {
            $request->validate([
                'menu_id' => 'required|integer|exists:menu,id',
                'nombre' => 'required|string|max:255',
                'url' => 'required|string|max:255',
                'orden' => 'required|integer|min:1',
                'estado_id' => 'required|integer|exists:estado,id',
                'roles' => 'required|array|min:1',
                'roles.*' => 'integer|exists:rol,id'
            ]);

            DB::beginTransaction();

            // Crear submenu
            $submenu = SubMenu::create([
                'menu_id' => $request->menu_id,
                'nombre' => $request->nombre,
                'url' => $request->url,
                'icono' => $request->icono,
                'orden' => $request->orden,
                'estado_id' => $request->estado_id
            ]);

            // Asociar roles
            $submenu->roles()->sync($request->roles);

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Submenu creado correctamente',
                'data' => $submenu->load('roles')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear el submenu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos de un submenu
     */
    public function obtenerSubmenu($id)
    {
        try {
            $submenu = SubMenu::with('roles')->findOrFail($id);
            return response()->json($submenu);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Submenu no encontrado'
            ], 404);
        }
    }

    /**
     * Actualizar submenu
     */
    public function actualizarSubmenu(Request $request, $id)
    {
        try {
            $request->validate([
                'menu_id' => 'required|integer|exists:menu,id',
                'nombre' => 'required|string|max:255',
                'url' => 'required|string|max:255',
                'orden' => 'required|integer|min:1',
                'estado_id' => 'required|integer|exists:estado,id',
                'roles' => 'required|array|min:1',
                'roles.*' => 'integer|exists:rol,id'
            ]);

            DB::beginTransaction();

            $submenu = SubMenu::findOrFail($id);
            
            // Actualizar datos
            $submenu->update([
                'menu_id' => $request->menu_id,
                'nombre' => $request->nombre,
                'url' => $request->url,
                'icono' => $request->icono,
                'orden' => $request->orden,
                'estado_id' => $request->estado_id
            ]);

            // Actualizar roles asociados
            $submenu->roles()->sync($request->roles);

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Submenu actualizado correctamente',
                'data' => $submenu->load('roles')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al actualizar el submenu: ' . $e->getMessage()
            ], 500);
        }
    }
}
