<?php

use App\Models\SubMenu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Opciones de un submenú: filas de sub_menu con padre_id que no se muestran
 * en la navegación, pero se asignan por rol o por usuario como cualquier
 * permiso. Crea "Permite ver todos los flujos" bajo Ventas y migra a ella
 * los roles 1 y 16 (antes fijos en código) y users.ver_todos_flujos.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sub_menu', 'padre_id')) {
            Schema::table('sub_menu', function (Blueprint $table) {
                $table->unsignedBigInteger('padre_id')->nullable()->after('menu_id')->index();
            });
        }

        $ventas = DB::table('sub_menu')->where('url', SubMenu::URL_VENTAS)->first();
        if (!$ventas || DB::table('sub_menu')->where('url', SubMenu::OPCION_VER_TODOS_FLUJOS)->exists()) {
            return;
        }

        $ahora = now();
        $opcionId = DB::table('sub_menu')->insertGetId([
            'url' => SubMenu::OPCION_VER_TODOS_FLUJOS,
            'nombre' => 'Permite ver todos los flujos',
            'menu_id' => $ventas->menu_id,
            'padre_id' => $ventas->id,
            'orden' => 1,
            'estado_id' => 1,
            'icono' => 'fa-solid fa-eye',
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);

        $roles = DB::table('rol')->whereIn('id', [1, 16])->pluck('id');
        DB::table('rol_submenu')->insertOrIgnore($roles->map(fn ($rolId) => [
            'rol_id' => $rolId,
            'sub_menu_id' => $opcionId,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ])->all());

        if (Schema::hasColumn('users', 'ver_todos_flujos')) {
            $usuarios = DB::table('users')->where('ver_todos_flujos', 1)->pluck('id');
            DB::table('usuario_submenu')->insertOrIgnore($usuarios->map(fn ($usuarioId) => [
                'usuario_id' => $usuarioId,
                'sub_menu_id' => $opcionId,
                'permitido' => 1,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ])->all());
        }
    }

    public function down(): void
    {
        $opcionId = DB::table('sub_menu')->where('url', SubMenu::OPCION_VER_TODOS_FLUJOS)->value('id');
        if ($opcionId) {
            if (Schema::hasColumn('users', 'ver_todos_flujos')) {
                $usuarios = DB::table('usuario_submenu')
                    ->where('sub_menu_id', $opcionId)->where('permitido', 1)->pluck('usuario_id');
                DB::table('users')->whereIn('id', $usuarios)->update(['ver_todos_flujos' => 1]);
            }
            DB::table('usuario_submenu')->where('sub_menu_id', $opcionId)->delete();
            DB::table('rol_submenu')->where('sub_menu_id', $opcionId)->delete();
            DB::table('sub_menu')->where('id', $opcionId)->delete();
        }

        if (Schema::hasColumn('sub_menu', 'padre_id')) {
            Schema::table('sub_menu', function (Blueprint $table) {
                $table->dropIndex(['padre_id']);
                $table->dropColumn('padre_id');
            });
        }
    }
};
