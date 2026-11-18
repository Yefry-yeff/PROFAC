<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entregas_productos_incidencias', function (Blueprint $table) {
            $table->decimal('cantidad_devolucion', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('entregas_productos_incidencias', function (Blueprint $table) {
            $table->dropColumn('cantidad_devolucion');
        });
    }
};
