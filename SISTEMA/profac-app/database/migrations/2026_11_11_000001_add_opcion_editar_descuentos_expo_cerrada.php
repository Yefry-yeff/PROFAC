<?php

use App\Models\SubMenu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $asistencia = DB::table('sub_menu')->where('url', SubMenu::URL_EXPO_LISTA_ASISTENCIA)->first();
        if (!$asistencia || DB::table('sub_menu')
            ->where('url', SubMenu::OPCION_EXPO_EDITAR_DESCUENTOS_CERRADA)->exists()) {
            return;
        }

        DB::table('sub_menu')->insert([
            'url' => SubMenu::OPCION_EXPO_EDITAR_DESCUENTOS_CERRADA,
            'nombre' => 'Editar descuentos con Expo cerrada',
            'menu_id' => $asistencia->menu_id,
            'padre_id' => $asistencia->id,
            'orden' => 1,
            'estado_id' => 1,
            'icono' => 'fa-solid fa-percent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $opcionId = DB::table('sub_menu')
            ->where('url', SubMenu::OPCION_EXPO_EDITAR_DESCUENTOS_CERRADA)
            ->value('id');
        if (!$opcionId) {
            return;
        }

        DB::table('usuario_submenu')->where('sub_menu_id', $opcionId)->delete();
        DB::table('rol_submenu')->where('sub_menu_id', $opcionId)->delete();
        DB::table('sub_menu')->where('id', $opcionId)->delete();
    }
};
