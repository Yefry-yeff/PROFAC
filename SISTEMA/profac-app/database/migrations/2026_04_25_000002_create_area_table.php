<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('area')) {
            Schema::create('area', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('nombre', 100);
                $table->string('descripcion', 255)->nullable();
                $table->integer('estado_id')->default(1);
                $table->timestamps();

                $table->foreign('estado_id')
                      ->references('id')->on('estado')
                      ->onDelete('restrict')->onUpdate('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('area');
    }
};
