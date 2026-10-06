<?php

namespace Tests\Feature;

use App\Livewire\Flujo\ModalFlujoPedido;
use App\Models\User;
use App\Services\Expo\SeccionadorOfertaExpo;
use App\Support\FacturasFlujo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CambiarGanadoraTrasAnularFacturasTest extends TestCase
{
    use DatabaseTransactions;

    public function test_permite_quitar_y_cambiar_ganadora_con_todas_las_facturas_anuladas(): void
    {
        [$flujoId, $anteriorId, $nuevaId] = $this->prepararFlujo();
        $facturasAntes = DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)->where('tipo_tramite_id', 3)->count();
        $seccionesAntes = DB::table('expo_oferta_seccion')->where('flujo_id', $flujoId)->count();

        $modal = Livewire::test(ModalFlujoPedido::class)
            ->call('abrirDesdeFlujo', $flujoId)
            ->call('seleccionarPaso', 'ofertas')
            ->call('verOferta', $anteriorId)
            ->assertSee('Quitar Ganadora')
            ->call('confirmarAccionOferta', 'quitar_ganadora')
            ->assertSee('Motivo (obligatorio)')
            ->set('motivoAnulOferta', 'Descuento adicional autorizado')
            ->call('quitarGanadora')
            ->assertSet('mensajeError', '')
            ->assertSet('pedidoData.has_ganadora', 0)
            ->call('verOferta', $nuevaId)
            ->assertSeeHtml("confirmarAccionOferta('ganadora')")
            ->call('confirmarAccionOferta', 'ganadora')
            ->assertSee('Confirmar y crear secciones')
            ->call('ganadoraOferta')
            ->assertSet('mensajeError', '');

        $this->assertDatabaseHas('historico_flujo', [
            'flujo_id' => $flujoId, 'tipo_tramite_id' => 2,
            'tramite_id' => $nuevaId, 'observaciones' => 'ganadora',
        ]);
        $this->assertDatabaseHas('cotizacion_estado', [
            'flujo_id' => $flujoId, 'cotizacion_id' => $anteriorId, 'ganadora' => 2,
            'comentario' => 'Quitada ganadora: Descuento adicional autorizado',
        ]);
        $this->assertSame(11, (int) DB::table('flujo')->where('id', $flujoId)->value('tipo_tramite_id'));
        $this->assertSame($facturasAntes, DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)->where('tipo_tramite_id', 3)->count());
        $this->assertSame($seccionesAntes, DB::table('expo_oferta_seccion')->where('flujo_id', $flujoId)->count());
        $modal->call('abrirDesdeFlujo', $flujoId);
        foreach ($modal->get('seccionesExpoData') as $seccion) {
            $this->assertSame($nuevaId, (int) $seccion['cotizacion_origen_id']);
        }
    }

    public function test_una_factura_vigente_bloquea_la_interfaz_y_ambas_acciones_del_servidor(): void
    {
        [$flujoId, $anteriorId, $nuevaId, $facturaIds] = $this->prepararFlujo();
        // Una factura vencida sigue vigente, incluso si su histórico quedó inactivo.
        DB::table('factura')->where('id', $facturaIds->first())->update(['estado_venta_id' => 4]);
        DB::table('historico_flujo')->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 3)->update(['estado_id' => 7]);

        $this->assertTrue(FacturasFlujo::tieneVigentes($flujoId));
        Livewire::test(ModalFlujoPedido::class)
            ->call('abrirDesdeFlujo', $flujoId)
            ->call('seleccionarPaso', 'ofertas')
            ->call('verOferta', $anteriorId)
            ->assertDontSee('Quitar Ganadora')
            ->set('motivoAnulOferta', 'Descuento adicional')
            ->call('quitarGanadora')
            ->assertSet('mensajeError', 'Error: Debe anular todas las facturas del flujo antes de quitar la oferta ganadora.')
            ->call('verOferta', $nuevaId)
            ->call('ganadoraOferta')
            ->assertSet('mensajeError', 'Debe anular todas las facturas del flujo antes de cambiar la oferta ganadora.');

        $this->assertDatabaseHas('historico_flujo', [
            'flujo_id' => $flujoId, 'tipo_tramite_id' => 2,
            'tramite_id' => $anteriorId, 'observaciones' => 'ganadora',
        ]);
        $this->expectException(ValidationException::class);
        app(SeccionadorOfertaExpo::class)->iniciarSeccionado($flujoId, $nuevaId, (int) auth()->id());
    }

    public function test_verifica_facturas_legacy_sin_confundir_entregas_con_facturas(): void
    {
        [$flujoId, , , $facturaIds] = $this->prepararFlujo();
        $otraFacturaId = DB::table('factura')->whereNotIn('id', $facturaIds)->value('id');
        $this->assertNotNull($otraFacturaId);
        DB::table('factura')->where('id', $otraFacturaId)->update(['estado_venta_id' => 1]);
        DB::table('historico_flujo')->insert([
            'flujo_id' => $flujoId, 'tipo_tramite_id' => 5,
            'tramite_id' => $otraFacturaId, 'estado_id' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->assertFalse(FacturasFlujo::tieneVigentes($flujoId));

        DB::table('historico_flujo')->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 3)->update(['tipo_tramite_id' => 5]);
        $this->assertTrue(FacturasFlujo::tieneVigentes($flujoId));
        DB::table('factura')->where('id', $otraFacturaId)->update(['estado_venta_id' => 2]);
        $this->assertFalse(FacturasFlujo::tieneVigentes($flujoId));
    }

    private function prepararFlujo(): array
    {
        $usuario = User::query()->firstOrFail();
        $this->actingAs($usuario);
        $candidato = DB::table('historico_flujo as hf')
            ->join('expo_cotizacion as ec', 'ec.cotizacion_id', '=', 'hf.tramite_id')
            ->where('hf.tipo_tramite_id', 2)
            ->whereExists(function ($query) {
                $query->selectRaw('1')->from('historico_flujo as factura')
                    ->whereColumn('factura.flujo_id', 'hf.flujo_id')
                    ->where('factura.tipo_tramite_id', 3);
            })
            ->groupBy('hf.flujo_id')
            ->havingRaw('COUNT(DISTINCT hf.tramite_id) >= 2')
            ->orderByDesc('hf.flujo_id')
            ->first(['hf.flujo_id']);
        $this->assertNotNull($candidato, 'Se requiere un flujo Expo facturado con dos ofertas para la regresión.');
        $flujoId = (int) $candidato->flujo_id;
        $ofertaIds = DB::table('historico_flujo as hf')
            ->join('expo_cotizacion as ec', 'ec.cotizacion_id', '=', 'hf.tramite_id')
            ->where('hf.flujo_id', $flujoId)->where('hf.tipo_tramite_id', 2)
            ->orderBy('hf.id')->pluck('hf.tramite_id')->unique()->values();
        $anteriorId = (int) $ofertaIds->first();
        $nuevaId = (int) $ofertaIds->last();
        DB::table('historico_flujo')->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 2)->update(['observaciones' => null]);
        DB::table('historico_flujo')->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 2)->where('tramite_id', $anteriorId)
            ->update(['observaciones' => 'ganadora']);
        DB::table('cotizacion')->whereIn('id', [$anteriorId, $nuevaId])->update(['estado_id' => 1]);
        DB::table('credito_revision')->where('flujo_id', $flujoId)->update(['estado' => 'cancelado']);
        $facturaIds = DB::table('historico_flujo')->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 3)->pluck('tramite_id');
        DB::table('factura')->whereIn('id', $facturaIds)->update(['estado_venta_id' => 2]);
        DB::table('historico_flujo')->where('flujo_id', $flujoId)
            ->whereIn('tipo_tramite_id', [3, 5])->update(['estado_id' => 1]);

        return [$flujoId, $anteriorId, $nuevaId, $facturaIds];
    }
}
