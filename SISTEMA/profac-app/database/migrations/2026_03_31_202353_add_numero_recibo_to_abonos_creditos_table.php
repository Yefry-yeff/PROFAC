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
        Schema::table('abonos_creditos', function (Blueprint $table) {
            $table->string('numero_recibo', 100)->nullable()->after('fecha_pago');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('abonos_creditos', function (Blueprint $table) {
            $table->dropColumn('numero_recibo');
        });
    }
};
