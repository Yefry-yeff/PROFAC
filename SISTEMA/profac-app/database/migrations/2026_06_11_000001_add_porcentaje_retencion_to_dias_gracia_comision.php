<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPorcentajeRetencionToDiasGraciaComision extends Migration
{
    public function up()
    {
        Schema::table('dias_gracia_comision', function (Blueprint $table) {
            // Porcentaje de retención aplicado cuando el pago cae fuera del período de gracia.
            // Puede ser 0 (sin retención). Máx 100.00 %.
            $table->decimal('porcentaje_retencion', 5, 2)->default(0)->after('dias_gracia');
        });
    }

    public function down()
    {
        Schema::table('dias_gracia_comision', function (Blueprint $table) {
            $table->dropColumn('porcentaje_retencion');
        });
    }
}
