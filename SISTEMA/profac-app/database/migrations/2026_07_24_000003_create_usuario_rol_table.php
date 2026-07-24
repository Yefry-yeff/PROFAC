<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla ADITIVA para soportar multi-rol por usuario.
 *
 * IMPORTANTE: users.rol_id se mantiene intacto como el "rol principal" del
 * usuario (todo el codigo legado que hace JOIN directo con users.rol_id
 * sigue funcionando exactamente igual, sin ningun efecto). Esta tabla
 * usuario_rol solo guarda roles ADICIONALES (extra), nunca el principal.
 *
 * El acceso combinado a menus/submenus se calcula como la union de:
 *   [users.rol_id]  +  [usuario_rol.rol_id de este usuario]
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('usuario_rol')) {
            Schema::create('usuario_rol', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('usuario_id');
                $table->integer('rol_id');
                $table->dateTime('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->unique(['usuario_id', 'rol_id'], 'uk_usuario_rol');
                $table->index('usuario_id', 'fk_usuario_rol_usuario_idx');
                $table->index('rol_id', 'fk_usuario_rol_rol_idx');

                $table->foreign('usuario_id', 'fk_usuario_rol_usuario')
                      ->references('id')->on('users')
                      ->onDelete('cascade')->onUpdate('cascade');

                $table->foreign('rol_id', 'fk_usuario_rol_rol')
                      ->references('id')->on('rol')
                      ->onDelete('cascade')->onUpdate('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('usuario_rol');
    }
};
