<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VentaTemporalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_usuario_puede_crear_actualizar_y_recuperar_su_temporal(): void
    {
        $usuario = User::findOrFail((int) DB::table('users')->min('id'));

        $creado = $this->actingAs($usuario)->postJson('/ventas/temporales', $this->payload());
        $creado->assertOk()->assertJsonStructure(['id', 'expira_at']);
        $id = (int) $creado->json('id');

        $actualizado = $this->actingAs($usuario)->postJson('/ventas/temporales', array_merge($this->payload(), [
            'id' => $id,
            'titulo' => 'Oferta temporal actualizada',
            'contenido' => ['numero_inputs' => 2, 'controles' => []],
        ]));
        $actualizado->assertOk()->assertJsonPath('id', $id);

        $this->actingAs($usuario)->getJson('/ventas/temporales/' . $id)
            ->assertOk()
            ->assertJsonPath('data.titulo', 'Oferta temporal actualizada')
            ->assertJsonPath('data.contenido.numero_inputs', 2);
    }

    public function test_usuario_no_puede_consultar_modificar_ni_eliminar_temporal_ajeno(): void
    {
        $propietario = User::findOrFail((int) DB::table('users')->min('id'));
        $otroUsuario = User::factory()->create(['must_change_password' => 0]);
        $id = DB::table('venta_temporal')->insertGetId([
            'usuario_id' => $propietario->id,
            'tipo' => 'oferta',
            'codigo_tipo' => 'cotizacion_clientes_a',
            'titulo' => 'Temporal ajeno',
            'url_reanudacion' => '/proforma/cotizacion/2',
            'contenido' => '{}',
            'expira_at' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($otroUsuario)->getJson('/ventas/temporales/' . $id)->assertNotFound();
        $this->postJson('/ventas/temporales', array_merge($this->payload(), ['id' => $id]))->assertNotFound();
        $this->deleteJson('/ventas/temporales/' . $id)->assertNotFound();
        $this->assertDatabaseHas('venta_temporal', ['id' => $id, 'usuario_id' => $propietario->id]);
    }

    public function test_consulta_elimina_temporales_vencidos(): void
    {
        $usuario = User::findOrFail((int) DB::table('users')->min('id'));
        $id = DB::table('venta_temporal')->insertGetId([
            'usuario_id' => $usuario->id,
            'tipo' => 'oferta',
            'codigo_tipo' => 'cotizacion_clientes_a',
            'titulo' => 'Vencido',
            'url_reanudacion' => '/proforma/cotizacion/2',
            'contenido' => '{}',
            'expira_at' => now()->subSecond(),
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $this->actingAs($usuario)->getJson('/ventas/temporales?tipo=oferta')->assertOk();
        $this->assertDatabaseMissing('venta_temporal', ['id' => $id]);
    }

    public function test_rechaza_url_de_reanudacion_externa(): void
    {
        $usuario = User::findOrFail((int) DB::table('users')->min('id'));

        $this->actingAs($usuario)->postJson('/ventas/temporales', array_merge($this->payload(), [
            'url_reanudacion' => '//sitio-externo.test/captura',
        ]))->assertUnprocessable()->assertJsonValidationErrors('url_reanudacion');
    }

    private function payload(): array
    {
        return [
            'tipo' => 'oferta',
            'codigo_tipo' => 'cotizacion_clientes_a',
            'titulo' => 'Oferta temporal',
            'url_reanudacion' => '/proforma/cotizacion/2?from=flujo',
            'contenido' => [
                'version' => 1,
                'numero_inputs' => 1,
                'arreglo_id_inputs' => [1],
                'controles' => [['id' => 'cantidad1', 'value' => '2']],
            ],
        ];
    }
}
