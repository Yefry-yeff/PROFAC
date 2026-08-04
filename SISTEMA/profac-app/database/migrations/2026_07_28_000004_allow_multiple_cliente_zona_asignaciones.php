<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AllowMultipleClienteZonaAsignaciones extends Migration
{
    public function up()
    {
        Schema::table('cliente_zona_asignaciones', function (Blueprint $table) {
            $table->index('cliente_id', 'cliente_zona_asignaciones_cliente_index');
        });
        Schema::table('cliente_zona_asignaciones', function (Blueprint $table) {
            $table->dropUnique('cliente_zona_asignaciones_cliente_rol_unique');
            $table->unique(
                ['zona_id', 'cliente_id', 'usuario_id', 'rol_id'],
                'cliente_zona_asignaciones_zona_cliente_usuario_rol_unique'
            );
        });
    }

    public function down()
    {
        Schema::table('cliente_zona_asignaciones', function (Blueprint $table) {
            $table->dropUnique('cliente_zona_asignaciones_zona_cliente_usuario_rol_unique');
            $table->unique(['cliente_id', 'rol_id'], 'cliente_zona_asignaciones_cliente_rol_unique');
        });
        Schema::table('cliente_zona_asignaciones', function (Blueprint $table) {
            $table->dropIndex('cliente_zona_asignaciones_cliente_index');
        });
    }
}