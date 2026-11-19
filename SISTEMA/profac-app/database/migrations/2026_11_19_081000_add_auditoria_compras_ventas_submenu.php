<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $menuId = DB::table('menu')
            ->whereIn('nombre_menu', ['Kardex', 'Cardex'])
            ->value('id');

        if (!$menuId) {
            return;
        }

        $submenuId = DB::table('sub_menu')
            ->where('url', 'auditoria/compras-ventas')
            ->value('id');

        if (!$submenuId) {
            $submenuId = DB::table('sub_menu')->insertGetId([
                'url' => 'auditoria/compras-ventas',
                'nombre' => 'Auditoría Compras y Ventas',
                'menu_id' => $menuId,
                'orden' => 3,
                'estado_id' => 1,
                'icono' => 'fa fa-balance-scale',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ([1, 5, 7] as $rolId) {
            DB::table('rol_submenu')->updateOrInsert(
                ['rol_id' => $rolId, 'sub_menu_id' => $submenuId],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        $submenuId = DB::table('sub_menu')
            ->where('url', 'auditoria/compras-ventas')
            ->value('id');

        if ($submenuId) {
            DB::table('rol_submenu')->where('sub_menu_id', $submenuId)->delete();
            DB::table('sub_menu')->where('id', $submenuId)->delete();
        }
    }
};
