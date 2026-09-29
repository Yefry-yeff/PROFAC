<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('usuario_submenu')) {
            Schema::create('usuario_submenu', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('usuario_id');
                $table->integer('sub_menu_id');
                $table->boolean('permitido')->default(true);
                $table->timestamps();

                $table->unique(['usuario_id', 'sub_menu_id'], 'uk_usuario_submenu');
                $table->index('sub_menu_id', 'idx_usuario_submenu_submenu');
                $table->foreign('usuario_id', 'fk_usuario_submenu_usuario')
                    ->references('id')->on('users')
                    ->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreign('sub_menu_id', 'fk_usuario_submenu_submenu')
                    ->references('id')->on('sub_menu')
                    ->cascadeOnDelete()->cascadeOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_submenu');
    }
};
