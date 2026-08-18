<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddConciliacionSubmenuToEscalasComisiones extends Migration
{
    public function up()
    {
        $menuId = DB::table('menu')
            ->where('nombre_menu', 'Escalas de Comisiones')
            ->value('id');

        if (!$menuId) {
            return;
        }

        $submenuId = DB::table('sub_menu')
            ->where('menu_id', $menuId)
            ->where('url', 'comisiones/conciliacion')
            ->value('id');

        if (!$submenuId) {
            $maxOrden = (int) DB::table('sub_menu')
                ->where('menu_id', $menuId)
                ->max('orden');

            $submenuId = DB::table('sub_menu')->insertGetId([
                'url' => 'comisiones/conciliacion',
                'nombre' => 'Conciliación de Comisiones',
                'menu_id' => $menuId,
                'orden' => $maxOrden + 1,
                'estado_id' => 1,
                'icono' => 'fa fa-balance-scale',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $existsRolSubmenu = DB::table('rol_submenu')
            ->where('rol_id', 1)
            ->where('sub_menu_id', $submenuId)
            ->exists();

        if (!$existsRolSubmenu) {
            DB::table('rol_submenu')->insert([
                'rol_id' => 1,
                'sub_menu_id' => $submenuId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        $menuId = DB::table('menu')
            ->where('nombre_menu', 'Escalas de Comisiones')
            ->value('id');

        if (!$menuId) {
            return;
        }

        $submenuId = DB::table('sub_menu')
            ->where('menu_id', $menuId)
            ->where('url', 'comisiones/conciliacion')
            ->value('id');

        if (!$submenuId) {
            return;
        }

        DB::table('rol_submenu')
            ->where('sub_menu_id', $submenuId)
            ->delete();

        DB::table('sub_menu')
            ->where('id', $submenuId)
            ->delete();
    }
}
