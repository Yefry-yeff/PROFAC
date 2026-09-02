<?php

namespace Tests\Feature;

use App\Http\Livewire\Ventas\FacturacionUnificada;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class FacturacionUnificadaAlcanceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_descripcion_producto_devuelve_el_detalle_solicitado(): void
    {
        $producto = DB::table('producto')->select('id', 'nombre')->first();

        $this->assertNotNull($producto, 'No existe un producto para probar la consulta de descripción.');
        $descripcion = 'Descripción para prueba del modal';
        DB::table('producto')->where('id', $producto->id)->update(['descripcion' => $descripcion]);

        $usuario = User::query()->first();
        $this->assertNotNull($usuario, 'No existe un usuario para probar la consulta de descripción.');
        $this->actingAs($usuario)
            ->getJson('/productos/' . $producto->id . '/descripcion')
            ->assertOk()
            ->assertJsonPath('producto.id', $producto->id)
            ->assertJsonPath('producto.nombre', $producto->nombre)
            ->assertJsonPath('producto.descripcion', $descripcion);

        $this->getJson('/productos/2147483647/descripcion')->assertNotFound();
    }

    public function test_duplicar_oferta_expo_normaliza_productos_a_bodega_virtual(): void
    {
        $oferta = DB::table('expo_cotizacion as ec')
            ->join('expo as e', 'e.id', '=', 'ec.expo_id')
            ->join('expo_usuario as eu', 'eu.expo_id', '=', 'ec.expo_id')
            ->join('expo_bodega as eb', 'eb.expo_id', '=', 'ec.expo_id')
            ->join('expo_escala as ee', 'ee.expo_id', '=', 'ec.expo_id')
            ->join('cotizacion_has_producto as chp', 'chp.cotizacion_id', '=', 'ec.cotizacion_id')
            ->join('precios_producto_carga as ppc', 'ppc.id', '=', 'chp.precios_producto_carga_id')
            ->where('e.estado', 'Activo')
            ->where('ppc.estado_id', 1)
            ->select('ec.cotizacion_id', 'eu.usuario_id')
            ->first();

        $this->assertNotNull($oferta, 'No existe una oferta Expo activa para probar su duplicación.');
        $this->actingAs(User::findOrFail($oferta->usuario_id));

        $componente = Livewire::withQueryParams([
            'from' => 'flujo',
            'duplicar' => 1,
            'cotizacionId' => $oferta->cotizacion_id,
        ])->test(FacturacionUnificada::class, ['codigo' => 'cotizacion_clientes_a'])
            ->assertSet('esOfertaExpo', true)
            ->assertSet('filtrarProductosExpo', true);

        $productos = collect($componente->get('productosParaCarrito'));
        $this->assertNotEmpty($productos);
        $this->assertTrue($productos->every(fn ($producto) =>
            (int) $producto['Bodega_id'] === 0
            && (int) $producto['seccion_id'] === 461
            && $producto['nombre_bodega'] === 'EXPO - DISPONIBLE AGRUPADO'
            && (int) $producto['resta_inventario'] === 1
        ));
    }

    public function test_buscador_y_url_solo_permiten_flujos_involucrados_o_clientes_asignados(): void
    {
        $flujo = DB::table('flujo as f')
            ->join('cotizacion as co', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'co.id')
            ->join('cliente as c', 'c.id', '=', 'co.cliente_id')
            ->where('f.tipo_flujo_id', 1)
            ->whereNotExists(function ($query) {
                $query->from('historico_flujo as hf')
                    ->whereColumn('hf.flujo_id', 'f.id')
                    ->where('hf.tipo_tramite_id', 1);
            })
            ->whereExists(function ($query) {
                $query->from('historico_flujo as hf')
                    ->whereColumn('hf.flujo_id', 'f.id')
                    ->where('hf.tipo_tramite_id', 2);
            })
            ->whereRaw('CHAR_LENGTH(c.nombre) >= 2')
            ->select('f.id as flujo_id', 'co.id as cotizacion_id', 'c.nombre as cliente')
            ->first();

        $this->assertNotNull($flujo, 'No existe un flujo directo para ejecutar la prueba de alcance.');

        $involucrado = User::factory()->create(['must_change_password' => 0]);
        $ajeno = User::factory()->create(['must_change_password' => 0]);
        DB::table('cotizacion')->where('id', $flujo->cotizacion_id)->update(['users_id' => $involucrado->id]);
        $termino = mb_substr($flujo->cliente, 0, min(8, mb_strlen($flujo->cliente)));

        $this->actingAs($involucrado);
        Livewire::test(FacturacionUnificada::class, ['codigo' => 'cotizacion_clientes_a'])
            ->set('busquedaFlujo', $termino)
            ->assertSet('flujoEncontrados', function ($resultados) use ($flujo) {
                return collect($resultados)->contains(fn ($item) => (int) $item->flujo_id === (int) $flujo->flujo_id);
            });
        $this->get('/proforma/cotizacion/2?from=flujo&flujoId=' . $flujo->flujo_id)->assertOk();

        $this->actingAs($ajeno);
        Livewire::test(FacturacionUnificada::class, ['codigo' => 'cotizacion_clientes_a'])
            ->set('busquedaFlujo', $termino)
            ->assertSet('flujoEncontrados', function ($resultados) use ($flujo) {
                return !collect($resultados)->contains(fn ($item) => (int) $item->flujo_id === (int) $flujo->flujo_id);
            });
        $this->get('/proforma/cotizacion/2?from=flujo&flujoId=' . $flujo->flujo_id)->assertForbidden();
    }
}
