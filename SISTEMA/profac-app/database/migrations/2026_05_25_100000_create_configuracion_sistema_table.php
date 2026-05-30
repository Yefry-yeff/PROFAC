<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 100)->unique();
            $table->string('valor', 500)->default('');
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });

        // Seed con el valor actual de caché para no romper el estado existente
        $valorActual = Cache::get('notificaciones_sistema_activo', true) ? '1' : '0';

        DB::table('configuracion_sistema')->insert([
            'clave'       => 'notificaciones_sistema_activo',
            'valor'       => $valorActual,
            'descripcion' => 'Interruptor global del sistema de notificaciones de flujo',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_sistema');
    }
};
