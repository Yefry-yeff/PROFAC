<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRolIdToClienteUsuarioTable extends Migration
{
    /**
     * Antes, el "tipo" (Asesor Comercial vs Tele Asesor) de una fila de cliente_usuario se
     * derivaba dinámicamente de users.rol_id. Con la llegada de multi-rol (usuario_rol), un
     * mismo usuario puede tener Asesor Comercial y Tele Asesor a la vez, por lo que ya no es
     * posible saber con qué tipo fue asignado a un cliente en particular solo mirando su rol
     * actual. Se agrega rol_id a esta tabla para almacenar explícitamente el tipo de la
     * asignación al momento de crearla.
     */
    public function up()
    {
        if (!Schema::hasColumn('cliente_usuario', 'rol_id')) {
            Schema::table('cliente_usuario', function (Blueprint $table) {
                $table->unsignedBigInteger('rol_id')->nullable()->after('usuario_id');
            });
        }

        // Backfill: para las filas existentes, el tipo era el rol_id del usuario en ese momento.
        DB::statement('
            UPDATE cliente_usuario cu
            INNER JOIN users u ON u.id = cu.usuario_id
            SET cu.rol_id = u.rol_id
            WHERE cu.rol_id IS NULL
        ');

        Schema::table('cliente_usuario', function (Blueprint $table) {
            // Permite que el mismo usuario esté asignado a un cliente con dos tipos distintos
            // (p.ej. Asesor Comercial y Tele Asesor) ahora que ambos roles pueden coexistir.
            $table->dropUnique('cliente_usuario_unique');
            $table->unique(['cliente_id', 'usuario_id', 'rol_id'], 'cliente_usuario_unique');
        });
    }

    public function down()
    {
        if (Schema::hasColumn('cliente_usuario', 'rol_id')) {
            Schema::table('cliente_usuario', function (Blueprint $table) {
                $table->dropUnique('cliente_usuario_unique');
                $table->unique(['cliente_id', 'usuario_id'], 'cliente_usuario_unique');
                $table->dropColumn('rol_id');
            });
        }
    }
}
