<?php

namespace Tests\Feature;

use App\Http\Livewire\FlujoDeVenta\Expo as ExpoComponent;
use App\Models\User;
use App\Support\ExpoConfig;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
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
        DB::table('expo_usuario')->insert([
            'expo_id' => $expoId,
            'usuario_id' => $userId,
            'created_at' => now(),
        ]);

        $detalle = ExpoConfig::detalleActiva($expoId);
        $detalleAutorizado = ExpoConfig::detalleActivaParaUsuario($expoId, $userId);
        $otroUsuarioId = DB::table('users')->where('id', '<>', $userId)->value('id');

        $this->assertSame($expoId, $detalle['id']);
        $this->assertSame([$bodegaId], $detalle['bodegas']);
        $this->assertSame([$escalaId], $detalle['escalas']);
        $this->assertSame($expoId, $detalleAutorizado['id']);
        if ($otroUsuarioId) {
            $this->assertNull(ExpoConfig::detalleActivaParaUsuario($expoId, (int) $otroUsuarioId));
        }

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

    public function test_editar_actualiza_la_misma_expo_y_protege_nombre_y_fecha_inicio(): void
    {
        $userId = (int) DB::table('users')->min('id');
        $bodegaId = (int) DB::table('bodega')->min('id');
        $escalaId = (int) DB::table('categoria_precios')->min('id');
        $fechaInicio = now()->subDay()->startOfMinute();
        $fechaFinNueva = now()->addDays(2)->startOfMinute();

        $expoId = DB::table('expo')->insertGetId([
            'nombre' => 'Expo editable',
            'descripcion' => 'Descripción original',
            'estado' => 'Inactivo',
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => now()->addDay(),
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('expo_bodega')->insert(['expo_id' => $expoId, 'bodega_id' => $bodegaId, 'created_at' => now()]);
        DB::table('expo_escala')->insert(['expo_id' => $expoId, 'escala_id' => $escalaId, 'created_at' => now()]);
        DB::table('expo_usuario')->insert(['expo_id' => $expoId, 'usuario_id' => $userId, 'created_at' => now()]);
        DB::table('expo_descuento')->insert([
            'expo_id' => $expoId,
            'venta_minima' => 1000,
            'porcentaje_descuento' => 5,
            'orden' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $totalAntes = DB::table('expo')->count();
        $this->actingAs(User::findOrFail($userId));

        Livewire::test(ExpoComponent::class)
            ->call('editar', $expoId)
            ->set('nombre', 'Nombre manipulado')
            ->set('fechaInicio', now()->addMonth()->format('Y-m-d\TH:i'))
            ->set('descripcion', 'Descripción actualizada')
            ->set('fechaFin', $fechaFinNueva->format('Y-m-d\TH:i'))
            ->call('guardar')
            ->assertHasNoErrors();

        $expo = DB::table('expo')->where('id', $expoId)->first();

        $this->assertSame($totalAntes, DB::table('expo')->count());
        $this->assertSame('Expo editable', $expo->nombre);
        $this->assertSame($fechaInicio->format('Y-m-d H:i:00'), $expo->fecha_inicio);
        $this->assertSame('Descripción actualizada', $expo->descripcion);
        $this->assertSame($fechaFinNueva->format('Y-m-d H:i:00'), $expo->fecha_fin);
    }

    public function test_el_historial_inactiva_automaticamente_una_expo_vencida(): void
    {
        DB::table('expo')->where('estado', 'Activo')->update(['estado' => 'Inactivo']);
        $userId = (int) DB::table('users')->min('id');

        $expoId = DB::table('expo')->insertGetId([
            'nombre' => 'Expo vencida',
            'estado' => 'Activo',
            'fecha_inicio' => now()->subDays(2),
            'fecha_fin' => now()->subMinute(),
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs(User::findOrFail($userId));
        Livewire::test(ExpoComponent::class)->assertSee('Expo vencida');

        $this->assertSame('Inactivo', DB::table('expo')->where('id', $expoId)->value('estado'));
    }

    public function test_una_expo_finalizada_no_se_edita_pero_puede_duplicarse(): void
    {
        $userId = (int) DB::table('users')->min('id');
        $bodegaId = (int) DB::table('bodega')->min('id');
        $escalaId = (int) DB::table('categoria_precios')->min('id');

        $expoId = DB::table('expo')->insertGetId([
            'nombre' => 'Expo finalizada',
            'descripcion' => 'Configuración base',
            'estado' => 'Inactivo',
            'fecha_inicio' => now()->subDays(2),
            'fecha_fin' => now()->subDay(),
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('expo_bodega')->insert(['expo_id' => $expoId, 'bodega_id' => $bodegaId, 'created_at' => now()]);
        DB::table('expo_escala')->insert(['expo_id' => $expoId, 'escala_id' => $escalaId, 'created_at' => now()]);
        DB::table('expo_usuario')->insert(['expo_id' => $expoId, 'usuario_id' => $userId, 'created_at' => now()]);
        DB::table('expo_descuento')->insert([
            'expo_id' => $expoId,
            'venta_minima' => 500,
            'porcentaje_descuento' => 3,
            'orden' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs(User::findOrFail($userId));

        Livewire::test(ExpoComponent::class)
            ->call('editar', $expoId)
            ->assertSet('mostrarFormulario', false)
            ->assertSet('expoEditandoId', null);

        $totalAntes = DB::table('expo')->count();
        Livewire::test(ExpoComponent::class)
            ->call('duplicar', $expoId)
            ->assertSet('mostrarFormulario', true)
            ->assertSet('expoDuplicandoId', $expoId)
            ->assertSet('bodegasSeleccionadas', [(string) $bodegaId])
            ->assertSet('escalasSeleccionadas', [(string) $escalaId])
            ->assertSet('usuariosSeleccionados', [(string) $userId])
            ->set('nombre', 'Expo nueva desde copia')
            ->set('fechaInicio', now()->addHour()->format('Y-m-d\TH:i'))
            ->set('fechaFin', now()->addDay()->format('Y-m-d\TH:i'))
            ->call('guardar')
            ->assertHasNoErrors();

        $copia = DB::table('expo')->where('nombre', 'Expo nueva desde copia')->first();
        $this->assertNotNull($copia);
        $this->assertSame($totalAntes + 1, DB::table('expo')->count());
        $this->assertSame($expoId, (int) $copia->expo_anterior_id);
        $this->assertSame(1, DB::table('expo_bodega')->where('expo_id', $copia->id)->count());
        $this->assertSame(1, DB::table('expo_escala')->where('expo_id', $copia->id)->count());
        $this->assertSame(1, DB::table('expo_usuario')->where('expo_id', $copia->id)->count());
        $this->assertSame(1, DB::table('expo_descuento')->where('expo_id', $copia->id)->count());
    }
}