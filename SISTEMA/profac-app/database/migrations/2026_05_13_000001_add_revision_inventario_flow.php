<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRevisionInventarioFlow extends Migration
{
    public function up(): void
    {
        // 1. Agregar estado_venta id=9 para Revision de Inventario
        $existe = DB::table('estado_venta')->where('id', 9)->exists();
        if (!$existe) {
            DB::table('estado_venta')->insert([
                'id'          => 9,
                'descripcion' => 'Revision de Inventario',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // 2. Agregar tipos_tramites id=9 para Revision de Inventario
        $existeTipo = DB::table('tipos_tramites')->where('id', 9)->exists();
        if (!$existeTipo) {
            DB::table('tipos_tramites')->insert([
                'id'         => 9,
                'nombre'     => 'Revision de Inventario',
                'estado'     => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Crear tabla de configuración para activar/desactivar la revisión de inventario
        if (!Schema::hasTable('configuracion_revision_inventario')) {
            Schema::create('configuracion_revision_inventario', function (Blueprint $table) {
                $table->id();
                $table->tinyInteger('activo')->default(0)->comment('1=activo, 0=inactivo');
                $table->text('observaciones')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });

            // Insertar fila de configuración por defecto (desactivada)
            DB::table('configuracion_revision_inventario')->insert([
                'activo'     => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('estado_venta')->where('id', 9)->delete();
        DB::table('tipos_tramites')->where('id', 9)->delete();
        Schema::dropIfExists('configuracion_revision_inventario');
    }
}
