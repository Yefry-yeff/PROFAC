<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('comision_periodo')) {
            return;
        }

        Schema::table('comision_periodo', function (Blueprint $table) {
            if (!Schema::hasColumn('comision_periodo', 'total_comision_escala')) {
                $table->decimal('total_comision_escala', 16, 2)
                    ->default(0)
                    ->after('total_comision')
                    ->comment('Total comisión por escala del período');
            }

            if (!Schema::hasColumn('comision_periodo', 'total_comision_politica_anterior')) {
                $table->decimal('total_comision_politica_anterior', 16, 2)
                    ->default(0)
                    ->after('total_comision_escala')
                    ->comment('Total comisión por política anterior del período');
            }

            if (!Schema::hasColumn('comision_periodo', 'total_comision_global')) {
                $table->decimal('total_comision_global', 16, 2)
                    ->default(0)
                    ->after('total_comision_politica_anterior')
                    ->comment('Total global de comisión del período (escala + política anterior)');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('comision_periodo')) {
            return;
        }

        Schema::table('comision_periodo', function (Blueprint $table) {
            if (Schema::hasColumn('comision_periodo', 'total_comision_global')) {
                $table->dropColumn('total_comision_global');
            }
            if (Schema::hasColumn('comision_periodo', 'total_comision_politica_anterior')) {
                $table->dropColumn('total_comision_politica_anterior');
            }
            if (Schema::hasColumn('comision_periodo', 'total_comision_escala')) {
                $table->dropColumn('total_comision_escala');
            }
        });
    }
};
