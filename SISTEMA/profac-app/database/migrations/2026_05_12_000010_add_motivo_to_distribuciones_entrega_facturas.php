<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMotivoToDistribucionesEntregaFacturas extends Migration
{
    public function up()
    {
        Schema::table('distribuciones_entrega_facturas', function (Blueprint $table) {
            $table->text('motivo_anulacion')->nullable()->after('observaciones');
            $table->text('motivo_confirmacion')->nullable()->after('motivo_anulacion');
        });
    }

    public function down()
    {
        Schema::table('distribuciones_entrega_facturas', function (Blueprint $table) {
            $table->dropColumn(['motivo_anulacion', 'motivo_confirmacion']);
        });
    }
}
