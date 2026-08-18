<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateClienteZonaResponsablesTable extends Migration
{
    public function up()
    {
        Schema::create('cliente_zona_responsables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zona_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('rol_id');
            $table->timestamps();

            $table->unique(['zona_id', 'usuario_id', 'rol_id'], 'cliente_zona_responsables_unique');
            $table->index(['zona_id', 'rol_id']);
            $table->foreign('zona_id')->references('id')->on('cliente_zonas')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
        });

        $ahora = now();
        $zonas = DB::table('cliente_zonas')->get(['id', 'asesor_comercial_id', 'teleasesor_id']);
        foreach ($zonas as $zona) {
            if ($zona->asesor_comercial_id) {
                DB::table('cliente_zona_responsables')->insert([
                    'zona_id' => $zona->id,
                    'usuario_id' => $zona->asesor_comercial_id,
                    'rol_id' => 2,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            }
            if ($zona->teleasesor_id) {
                DB::table('cliente_zona_responsables')->insert([
                    'zona_id' => $zona->id,
                    'usuario_id' => $zona->teleasesor_id,
                    'rol_id' => 3,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('cliente_zona_responsables');
    }
}