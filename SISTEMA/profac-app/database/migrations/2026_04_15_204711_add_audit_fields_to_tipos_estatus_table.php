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
        Schema::table('tipos_estatus', function (Blueprint $table) {
            $table->string('estado')->after('nombre');
            $table->unsignedBigInteger('created_by')->nullable()->after('estado');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
        });
    }

    public function down()
    {
        Schema::table('tipos_estatus', function (Blueprint $table) {
            $table->dropColumn(['estado', 'created_by', 'updated_by']);
        });
    }
};
