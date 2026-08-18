<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comision_producto_no_miselaneo')) {
            return;
        }

        Schema::create('comision_producto_no_miselaneo', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('producto_id');
            $table->tinyInteger('estado_id')->default(1)->comment('1=ACTIVO,0=INACTIVO');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique('producto_id', 'uk_comision_prod_no_mis_producto');
            $table->index('estado_id', 'idx_comision_prod_no_mis_estado');
            $table->index(['producto_id', 'estado_id'], 'idx_comision_prod_no_mis_prod_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comision_producto_no_miselaneo');
    }
};
