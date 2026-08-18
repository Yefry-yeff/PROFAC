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
