<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sub_menu')) {
            Schema::create('sub_menu', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('url', 150);
                $table->string('nombre', 60);
                $table->integer('menu_id');
                $table->integer('orden')->default(0);
                $table->integer('estado_id')->default(1);
                $table->string('icono', 45)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                
                $table->index('menu_id', 'fk_sub_menu_menu1_idx');
                $table->index('estado_id', 'fk_sub_menu_estado1_idx');
                
                $table->foreign('menu_id', 'fk_sub_menu_menu1')
                      ->references('id')->on('menu')
                      ->onDelete('cascade')->onUpdate('cascade');
                      
                $table->foreign('estado_id', 'fk_sub_menu_estado1')
                      ->references('id')->on('estado')
                      ->onDelete('restrict')->onUpdate('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sub_menu');
    }
};
