<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('rol_submenu')) {
            Schema::create('rol_submenu', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->integer('rol_id');
                $table->integer('sub_menu_id');
                $table->dateTime('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                
                $table->unique(['rol_id', 'sub_menu_id'], 'uk_rol_submenu');
                $table->index('rol_id', 'fk_rol_submenu_rol1_idx');
                $table->index('sub_menu_id', 'fk_rol_submenu_submenu1_idx');
                
                $table->foreign('rol_id', 'fk_rol_submenu_rol1')
                      ->references('id')->on('rol')
                      ->onDelete('cascade')->onUpdate('cascade');
                      
                $table->foreign('sub_menu_id', 'fk_rol_submenu_submenu1')
                      ->references('id')->on('sub_menu')
                      ->onDelete('cascade')->onUpdate('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('rol_submenu');
    }
};
