<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('menu')) {
            Schema::create('menu', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('icon', 45);
                $table->string('nombre_menu', 45);
                $table->integer('orden')->default(0);
                $table->integer('estado_id')->default(1);
                $table->dateTime('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                
                $table->index('estado_id', 'fk_menu_estado1_idx');
                $table->foreign('estado_id', 'fk_menu_estado1')
                      ->references('id')->on('estado')
                      ->onDelete('restrict')->onUpdate('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('menu');
    }
};
