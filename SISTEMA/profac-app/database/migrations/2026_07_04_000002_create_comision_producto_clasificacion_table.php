<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comision_producto_clasificacion')) {
            return;
        }

        Schema::create('comision_producto_clasificacion', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('producto_id');
            $table->tinyInteger('es_miselaneo')->default(0)->comment('1=MISELANEO,0=NO MISELANEO');
            $table->tinyInteger('estado_id')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique('producto_id', 'uk_comision_producto_clasif_producto');
            $table->index('estado_id', 'idx_comision_producto_clasif_estado');
            $table->index('es_miselaneo', 'idx_comision_producto_clasif_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comision_producto_clasificacion');
    }
};
