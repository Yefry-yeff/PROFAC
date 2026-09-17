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

    public function test_el_historial_cierra_automaticamente_una_expo_vencida(): void
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

        $this->assertSame('Cerrada', DB::table('expo')->where('id', $expoId)->value('estado'));
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

    public function test_descuento_condicionado_exige_asistencia_en_la_misma_expo(): void
    {
        DB::table('expo')->where('estado', 'Activo')->update(['estado' => 'Inactivo']);

        $userId = (int) DB::table('users')->min('id');
        $clienteIds = DB::table('cliente')->where('id', '<>', 1)->orderBy('id')->limit(2)->pluck('id');
        $marcaIds = DB::table('marca')->orderBy('id')->limit(2)->pluck('id');
        $this->assertCount(2, $clienteIds);
        $this->assertCount(2, $marcaIds);

        $expoId = DB::table('expo')->insertGetId([
            'nombre' => 'Expo asistencia vigente',
            'estado' => 'Activo',
            'fecha_inicio' => now()->subMinute(),
            'fecha_fin' => now()->addDay(),
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otraExpoId = DB::table('expo')->insertGetId([
            'nombre' => 'Expo asistencia ajena',
            'estado' => 'Inactivo',
            'fecha_inicio' => now()->subDays(2),
            'fecha_fin' => now()->subDay(),
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('expo_usuario')->insert([
            'expo_id' => $expoId,
            'usuario_id' => $userId,
            'created_at' => now(),
        ]);
        DB::table('expo_descuento_marca')->insert([
            [
                'expo_id' => $expoId,
                'marca_id' => $marcaIds[0],
                'venta_minima' => 1,
                'porcentaje_descuento' => 20,
                'requiere_asistencia' => true,
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'expo_id' => $expoId,
                'marca_id' => $marcaIds[0],
                'venta_minima' => 500000,
                'porcentaje_descuento' => 25,
                'requiere_asistencia' => false,
                'orden' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'expo_id' => $expoId,
                'marca_id' => $marcaIds[1],
                'venta_minima' => 100,
                'porcentaje_descuento' => 5,
                'requiere_asistencia' => false,
                'orden' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        DB::table('expo_asistencia')->insert([
            'expo_id' => $otraExpoId,
            'cliente_id' => $clienteIds[0],
            'registrado_por' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $detalleOtraExpo = ExpoConfig::detalleActivaParaUsuario($expoId, $userId, (int) $clienteIds[0]);
        $detalleNoAsistente = ExpoConfig::detalleActivaParaUsuario($expoId, $userId, (int) $clienteIds[1]);

        $this->assertFalse($detalleOtraExpo['cliente_asistio']);
        $this->assertFalse($detalleNoAsistente['cliente_asistio']);
        $this->assertSame(
            [(int) $marcaIds[0], (int) $marcaIds[1]],
            array_column($detalleOtraExpo['descuentos_marca'], 'marca_id')
        );
        $this->assertSame(
            [500000.0, 100.0],
            array_column($detalleNoAsistente['descuentos_marca'], 'venta_minima')
        );
        $this->assertSame(
            [25.0, 5.0],
            array_column($detalleNoAsistente['descuentos_marca'], 'porcentaje_descuento')
        );

        DB::table('expo_asistencia')->insert([
            'expo_id' => $expoId,
            'cliente_id' => $clienteIds[0],
            'registrado_por' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $detalleAsistente = ExpoConfig::detalleActivaParaUsuario($expoId, $userId, (int) $clienteIds[0]);

        $this->assertTrue($detalleAsistente['cliente_asistio']);
        $this->assertSame(
            [(int) $marcaIds[0], (int) $marcaIds[0], (int) $marcaIds[1]],
            array_column($detalleAsistente['descuentos_marca'], 'marca_id')
        );
    }

    public function test_asistencia_no_se_duplica_y_regla_nueva_es_libre_por_defecto(): void
    {
        $userId = (int) DB::table('users')->min('id');
        $clienteId = (int) DB::table('cliente')->where('id', '<>', 1)->min('id');
        $marcaId = (int) DB::table('marca')->min('id');
        $expoId = DB::table('expo')->insertGetId([
            'nombre' => 'Expo restricciones de asistencia',
            'estado' => 'Inactivo',
            'fecha_inicio' => now(),
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $asistencia = [
            'expo_id' => $expoId,
            'cliente_id' => $clienteId,
            'registrado_por' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $this->assertSame(1, DB::table('expo_asistencia')->insertOrIgnore($asistencia));
        $this->assertSame(0, DB::table('expo_asistencia')->insertOrIgnore($asistencia));
        $this->assertSame(1, DB::table('expo_asistencia')->where('expo_id', $expoId)->count());

        DB::table('expo_descuento_marca')->insert([
            'expo_id' => $expoId,
            'marca_id' => $marcaId,
            'venta_minima' => 100,
            'porcentaje_descuento' => 5,
            'orden' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(0, (int) DB::table('expo_descuento_marca')
            ->where('expo_id', $expoId)
            ->value('requiere_asistencia'));
    }

    public function test_editor_expo_reemplaza_los_escalones_de_la_marca_seleccionada(): void
    {
        $marcaIds = DB::table('marca')->orderBy('id')->limit(2)->pluck('id');
        $this->assertCount(2, $marcaIds);

        $componente = Livewire::test(ExpoComponent::class)
            ->set('descuentosMarca', [
                ['marca_id' => (string) $marcaIds[0], 'venta_minima' => '100', 'porcentaje_descuento' => '5', 'requiere_asistencia' => false],
                ['marca_id' => (string) $marcaIds[0], 'venta_minima' => '500', 'porcentaje_descuento' => '10', 'requiere_asistencia' => false],
                ['marca_id' => (string) $marcaIds[1], 'venta_minima' => '200', 'porcentaje_descuento' => '8', 'requiere_asistencia' => false],
            ])
            ->set('marcaDescuentoGestionId', (string) $marcaIds[0])
            ->call('editarDescuentoMarcaSeleccionado')
            ->assertSet('mostrarModalDescuentoMarca', true)
            ->assertSet('marcaDescuentoEditandoId', (int) $marcaIds[0]);

        $this->assertCount(2, $componente->get('escalonesMarcaModal'));

        $componente
            ->set('escalonesMarcaModal', [
                ['venta_minima' => '250', 'porcentaje_descuento' => '7.5', 'requiere_asistencia' => true],
                ['venta_minima' => '1000', 'porcentaje_descuento' => '15', 'requiere_asistencia' => false],
            ])
            ->call('guardarDescuentoMarcaModal')
            ->assertHasNoErrors()
            ->assertSet('mostrarModalDescuentoMarca', false)
            ->assertSet('marcaDescuentoGestionId', (string) $marcaIds[0]);

        $reglas = collect($componente->get('descuentosMarca'));
        $reglasEditadas = $reglas->where('marca_id', (string) $marcaIds[0])->values();
        $this->assertCount(3, $reglas);
        $this->assertSame(['250', '1000'], $reglasEditadas->pluck('venta_minima')->all());
        $this->assertSame(['7.5', '15'], $reglasEditadas->pluck('porcentaje_descuento')->all());
        $this->assertSame([true, false], $reglasEditadas->pluck('requiere_asistencia')->all());
        $this->assertCount(1, $reglas->where('marca_id', (string) $marcaIds[1]));
    }

}