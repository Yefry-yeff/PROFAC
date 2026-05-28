<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->unsignedSmallInteger('tiempo_recuperacion_meses')->nullable()->after('costo_promedio');
            $table->string('origen', 200)->nullable()->after('tiempo_recuperacion_meses');
        });
    }

    public function down()
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->dropColumn(['tiempo_recuperacion_meses', 'origen']);
        });
    }
};
