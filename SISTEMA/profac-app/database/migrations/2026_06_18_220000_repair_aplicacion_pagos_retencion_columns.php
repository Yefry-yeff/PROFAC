<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('aplicacion_pagos')) {
            return;
        }

        Schema::table('aplicacion_pagos', function (Blueprint $table) {
            if (!Schema::hasColumn('aplicacion_pagos', 'numero_retencion')) {
                $table->string('numero_retencion', 100)->nullable()->after('estado_retencion_isv');
            }

            if (!Schema::hasColumn('aplicacion_pagos', 'archivo_retencion')) {
                $table->string('archivo_retencion', 255)->nullable()->after('numero_retencion');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('aplicacion_pagos')) {
            return;
        }

        Schema::table('aplicacion_pagos', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('aplicacion_pagos', 'archivo_retencion')) {
                $dropColumns[] = 'archivo_retencion';
            }

            if (Schema::hasColumn('aplicacion_pagos', 'numero_retencion')) {
                $dropColumns[] = 'numero_retencion';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
