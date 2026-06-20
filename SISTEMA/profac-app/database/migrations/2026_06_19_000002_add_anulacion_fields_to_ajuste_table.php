<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnulacionFieldsToAjusteTable extends Migration
{
    public function up()
    {
        Schema::table('ajuste', function (Blueprint $table) {
            $table->tinyInteger('anulado')->default(0)->after('users_id');
            $table->unsignedBigInteger('anulado_por')->nullable()->after('anulado');
            $table->timestamp('anulado_at')->nullable()->after('anulado_por');
            $table->text('motivo_anulacion')->nullable()->after('anulado_at');
        });
    }

    public function down()
    {
        Schema::table('ajuste', function (Blueprint $table) {
            $table->dropColumn(['anulado', 'anulado_por', 'anulado_at', 'motivo_anulacion']);
        });
    }
}
