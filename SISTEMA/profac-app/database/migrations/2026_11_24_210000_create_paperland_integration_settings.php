<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('integration_settings')) {
            Schema::create('integration_settings', function (Blueprint $table) {
                $table->unsignedTinyInteger('id')->primary();
                $table->boolean('enabled')->default(false);
                $table->string('peer_url', 2048)->default('');
                $table->unsignedInteger('store_id')->default(1);
                $table->text('secret')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
        DB::table('integration_settings')->insertOrIgnore([
            'id' => 1, 'enabled' => false, 'peer_url' => '', 'store_id' => 1,
            'secret' => null, 'updated_by' => null, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $menuId = DB::table('menu')->whereIn('nombre_menu', ['Configuración', 'Configuracion'])->value('id');
        if (!$menuId) {
            $menuId = DB::table('menu')->insertGetId([
                'nombre_menu' => 'Configuración', 'icon' => 'fa fa-cog',
                'orden' => ((int) DB::table('menu')->max('orden')) + 1,
                'estado_id' => 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $submenuId = DB::table('sub_menu')->where('url', 'configuracion/api-paperland')->value('id');
        if (!$submenuId) {
            $submenuId = DB::table('sub_menu')->insertGetId([
                'url' => 'configuracion/api-paperland', 'nombre' => 'API Paperland', 'menu_id' => $menuId,
                'orden' => ((int) DB::table('sub_menu')->where('menu_id', $menuId)->max('orden')) + 1,
                'estado_id' => 1, 'icono' => 'fa fa-exchange', 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('rol_submenu')->insertOrIgnore([
                'rol_id' => 1, 'sub_menu_id' => $submenuId, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Settings and permissions are intentionally retained on rollback.
    }
};
