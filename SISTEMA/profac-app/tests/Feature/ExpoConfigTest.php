<?php

namespace Tests\Feature;

use App\Support\ExpoConfig;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExpoConfigTest extends TestCase
{
    use DatabaseTransactions;

    public function test_resuelve_configuracion_vigente_y_rechaza_dos_expos_activas(): void
    {
        DB::table('expo')->where('estado', 'Activo')->update(['estado' => 'Inactivo']);

        $userId = (int) DB::table('users')->min('id');
        $bodegaId = (int) DB::table('bodega')->min('id');
        $escalaId = (int) DB::table('categoria_precios')->min('id');

        $expoId = DB::table('expo')->insertGetId([
            'nombre' => 'Expo de prueba',
            'estado' => 'Activo',
            'fecha_inicio' => now()->subMinute(),
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('expo_bodega')->insert([
            'expo_id' => $expoId,
            'bodega_id' => $bodegaId,
            'created_at' => now(),
        ]);
        DB::table('expo_escala')->insert([
            'expo_id' => $expoId,
            'escala_id' => $escalaId,
            'created_at' => now(),
        ]);

        $detalle = ExpoConfig::detalleActiva($expoId);

        $this->assertSame($expoId, $detalle['id']);
        $this->assertSame([$bodegaId], $detalle['bodegas']);
        $this->assertSame([$escalaId], $detalle['escalas']);

        $rechazada = false;
        try {
            DB::table('expo')->insert([
                'nombre' => 'Segunda Expo activa',
                'estado' => 'Activo',
                'fecha_inicio' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (QueryException $e) {
            $rechazada = true;
        }

        $this->assertTrue($rechazada, 'La base de datos permitió más de una Expo activa.');
    }
}