<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRetencionMoraToFacturasComision extends Migration
{
    public function up()
    {
        Schema::table('facturas_comision', function (Blueprint $table) {
            // Monto descontado por mora (null = sin retención aplicada)
            $table->decimal('retencion_mora_monto', 16, 2)->nullable()->after('monto_rol');
            // Días de atraso que provocaron la retención (para auditoría)
            $table->smallInteger('retencion_mora_dias')->nullable()->after('retencion_mora_monto');
        });
    }

    public function down()
    {
        Schema::table('facturas_comision', function (Blueprint $table) {
            $table->dropColumn(['retencion_mora_monto', 'retencion_mora_dias']);
        });
    }
}
