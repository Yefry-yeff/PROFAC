<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterClienteCreditoAddColumns extends Migration
{
    public function up()
    {
        Schema::table('cliente_credito', function (Blueprint $table) {
            if (!Schema::hasColumn('cliente_credito', 'activo')) {
                $table->tinyInteger('activo')->default(1)->after('id')->comment('1=registro activo/vigente, 0=historial');
            }
            if (!Schema::hasColumn('cliente_credito', 'obs_letra_cambio')) {
                $table->text('obs_letra_cambio')->nullable()->after('letra_cambio');
            }
            if (!Schema::hasColumn('cliente_credito', 'obs_aval_solidario')) {
                $table->text('obs_aval_solidario')->nullable()->after('aval_solidario');
            }
        });
    }

    public function down()
    {
        Schema::table('cliente_credito', function (Blueprint $table) {
            $table->dropColumn(['activo', 'obs_letra_cambio', 'obs_aval_solidario']);
        });
    }
}
