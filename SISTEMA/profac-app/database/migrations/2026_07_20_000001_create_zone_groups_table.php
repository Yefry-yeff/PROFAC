<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZoneGroupsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('zone_groups')) { return; }
        Schema::create('zone_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->integer('orden')->default(0);
            $table->tinyInteger('status')->default(1); // 1 = activo, 0 = inactivo

            // Campos de auditoria
            $table->integer('usr_registro')->nullable();
            $table->integer('usr_actualizo')->nullable();

            $table->timestamps();

            $table->index('name');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('zone_groups');
    }
}
