<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rol', function (Blueprint $table) {
            if (!Schema::hasColumn('rol', 'nivel_id')) {
                $table->integer('nivel_id')->nullable()->after('estado_id');

                $table->foreign('nivel_id', 'fk_rol_nivel_rol')
                      ->references('id')->on('nivel_rol')
                      ->onDelete('set null')->onUpdate('cascade');
            }

            if (!Schema::hasColumn('rol', 'area_id')) {
                $table->integer('area_id')->nullable()->after('nivel_id');

                $table->foreign('area_id', 'fk_rol_area')
                      ->references('id')->on('area')
                      ->onDelete('set null')->onUpdate('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rol', function (Blueprint $table) {
            if (Schema::hasColumn('rol', 'nivel_id')) {
                $table->dropForeign('fk_rol_nivel_rol');
                $table->dropColumn('nivel_id');
            }
            if (Schema::hasColumn('rol', 'area_id')) {
                $table->dropForeign('fk_rol_area');
                $table->dropColumn('area_id');
            }
        });
    }
};
