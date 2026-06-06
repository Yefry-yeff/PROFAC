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
        if (!Schema::hasColumn('cliente', 'metodo_pago')) {
            Schema::table('cliente', function (Blueprint $table) {
                $table->string('metodo_pago', 100)->nullable()->after('vendedor');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('cliente', 'metodo_pago')) {
            Schema::table('cliente', function (Blueprint $table) {
                $table->dropColumn('metodo_pago');
            });
        }
    }
};
