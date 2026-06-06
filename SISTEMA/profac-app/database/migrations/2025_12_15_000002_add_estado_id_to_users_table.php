<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verificar si la columna ya existe
        if (!Schema::hasColumn('users', 'estado_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('estado_id')->default(1)->after('rol_id');
                
                // Agregar índice
                $table->index('estado_id', 'fk_users_estado1_idx');
                
                // Agregar foreign key
                $table->foreign('estado_id', 'fk_users_estado1')
                      ->references('id')
                      ->on('estado')
                      ->onDelete('restrict')
                      ->onUpdate('cascade');
            });

            // Actualizar todos los usuarios existentes a estado Activo (1)
            DB::table('users')->update(['estado_id' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['estado_id']);
            $table->dropColumn('estado_id');
        });
    }
};
