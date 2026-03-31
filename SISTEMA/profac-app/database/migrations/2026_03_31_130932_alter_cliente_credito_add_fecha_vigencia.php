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
        Schema::table('cliente_credito', function (Blueprint $table) {
            if (!Schema::hasColumn('cliente_credito', 'fecha_vigencia')) {
                $table->date('fecha_vigencia')->nullable()->after('dias_credito');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cliente_credito', function (Blueprint $table) {
            if (Schema::hasColumn('cliente_credito', 'fecha_vigencia')) {
                $table->dropColumn('fecha_vigencia');
            }
        });
    }
};
