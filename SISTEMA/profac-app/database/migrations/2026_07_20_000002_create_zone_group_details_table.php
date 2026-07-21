<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZoneGroupDetailsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('zone_group_details')) { return; }
        Schema::create('zone_group_details', function (Blueprint $table) {
            $table->id();
            $table->integer('zone_group_id');
            $table->integer('department_id');
            $table->integer('municipality_id')->nullable(); // NULL = todo el departamento
            $table->tinyInteger('status')->default(1); // 1 = activo, 0 = inactivo

            // Campos de auditoria
            $table->integer('usr_registro')->nullable();
            $table->integer('usr_actualizo')->nullable();

            $table->timestamps();

            $table->index(['zone_group_id', 'department_id', 'municipality_id'], 'idx_zgd_zona_depto_muni');
            $table->index('municipality_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('zone_group_details');
    }
}
