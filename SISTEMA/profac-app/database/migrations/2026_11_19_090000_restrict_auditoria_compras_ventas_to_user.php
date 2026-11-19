<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EMAIL_AUTORIZADO = 'yefryyo@gmail.com';

    public function up(): void
    {
        $submenuId = DB::table('sub_menu')
            ->where('url', 'auditoria/compras-ventas')
            ->value('id');

        if (!$submenuId) {
            return;
        }

        $usuarios = DB::table('users')
            ->where('estado_id', 1)
            ->whereRaw('LOWER(TRIM(email)) = ?', [self::EMAIL_AUTORIZADO])
            ->pluck('id');

        if ($usuarios->count() !== 1) {
            throw new RuntimeException(
                'Debe existir exactamente un usuario activo con el correo '.self::EMAIL_AUTORIZADO.'.'
            );
        }

        DB::table('rol_submenu')->where('sub_menu_id', $submenuId)->delete();
        DB::table('usuario_submenu')->where('sub_menu_id', $submenuId)->delete();

        DB::table('usuario_submenu')->insert([
            'usuario_id' => $usuarios->first(),
            'sub_menu_id' => $submenuId,
            'permitido' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $submenuId = DB::table('sub_menu')
            ->where('url', 'auditoria/compras-ventas')
            ->value('id');

        if (!$submenuId) {
            return;
        }

        DB::table('usuario_submenu')->where('sub_menu_id', $submenuId)->delete();

        foreach ([1, 5, 7] as $rolId) {
            DB::table('rol_submenu')->updateOrInsert(
                ['rol_id' => $rolId, 'sub_menu_id' => $submenuId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
};
