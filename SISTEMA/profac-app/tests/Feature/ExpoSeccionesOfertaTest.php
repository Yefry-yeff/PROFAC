<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Expo\SeccionadorOfertaExpo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExpoSeccionesOfertaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_crea_oferta_hija_con_productos_parciales_y_conserva_descuentos(): void
    {
        [$flujoId, $cotizacionId, $linea] = $this->prepararOfertaExpoGanadora();
        $usuario = User::query()->firstOrFail();
        $cantidad = max(1, (int) floor((int) $linea->cantidad / 2));

        $cotizacionHijaId = app(SeccionadorOfertaExpo::class)->crear(
            $flujoId,
            $cotizacionId,
            [$linea->id => $cantidad],
            2,
            '2026-09-08',
            '2026-10-08',
            false,
            $usuario->id
        );

        $lineaHija = DB::table('cotizacion_has_producto')
            ->where('cotizacion_id', $cotizacionHijaId)
            ->first();

        $this->assertNotNull($lineaHija);
        $this->assertSame($cantidad, (int) $lineaHija->cantidad);
        $this->assertEquals((float) $linea->precio_unidad, (float) $lineaHija->precio_unidad);
        $this->assertEqualsWithDelta(
            round((float) $linea->monto_descProducto * ($cantidad / (float) $linea->cantidad), 2),
            (float) $lineaHija->monto_descProducto,
            0.01
        );
        $this->assertDatabaseHas('expo_cotizacion', ['cotizacion_id' => $cotizacionHijaId]);
        $this->assertDatabaseHas('cotizacion', [
            'id' => $cotizacionHijaId,
            'tipo_pago_id' => 2,
            'fecha_emision' => '2026-09-08',
            'fecha_vencimiento' => '2026-10-08',
        ]);
        $this->assertDatabaseHas('credito_revision', [
            'flujo_id' => $flujoId,
            'cotizacion_id' => $cotizacionHijaId,
            'fecha_emision_solicitada' => '2026-09-08',
            'fecha_vencimiento_solicitada' => '2026-10-08',
            'dias_credito_solicitados' => 30,
        ]);
        $this->assertDatabaseHas('expo_oferta_seccion', [
            'flujo_id' => $flujoId,
            'cotizacion_origen_id' => $cotizacionId,
            'cotizacion_id' => $cotizacionHijaId,
            'estado' => 'EN_REVISION_CREDITO',
        ]);
        $this->assertDatabaseHas('historico_flujo', [
            'flujo_id' => $flujoId,
            'tipo_tramite_id' => 10,
            'tramite_id' => $cotizacionHijaId,
            'estado_id' => 5,
        ]);
        $this->assertDatabaseMissing('historico_flujo', [
            'flujo_id' => $flujoId,
            'tipo_tramite_id' => 2,
            'tramite_id' => $cotizacionHijaId,
        ]);
        $this->assertDatabaseHas('historico_flujo', [
            'flujo_id' => $flujoId,
            'tipo_tramite_id' => 2,
            'tramite_id' => $cotizacionId,
            'observaciones' => 'ganadora',
        ]);
        $this->assertSame(11, (int) DB::table('flujo')->where('id', $flujoId)->value('tipo_tramite_id'));
    }

    public function test_rechaza_cantidad_mayor_al_saldo_de_la_oferta_ganadora(): void
    {
        [$flujoId, $cotizacionId, $linea] = $this->prepararOfertaExpoGanadora();
        $usuario = User::query()->firstOrFail();

        $this->expectException(ValidationException::class);

        app(SeccionadorOfertaExpo::class)->crear(
            $flujoId,
            $cotizacionId,
            [$linea->id => (int) $linea->cantidad + 1],
            1,
            '2026-09-08',
            '2026-09-08',
            false,
            $usuario->id
        );
    }

    private function prepararOfertaExpoGanadora(): array
    {
        $candidata = DB::table('expo_cotizacion as ec')
            ->join('cotizacion_has_producto as chp', 'chp.cotizacion_id', '=', 'ec.cotizacion_id')
            ->join('historico_flujo as hf', function ($join) {
                $join->on('hf.tramite_id', '=', 'ec.cotizacion_id')
                    ->where('hf.tipo_tramite_id', '=', 2);
            })
            ->where('chp.cantidad', '>=', 2)
            ->orderByDesc('ec.cotizacion_id')
            ->select('ec.cotizacion_id', 'hf.flujo_id', 'chp.id as linea_id')
            ->first();

        $this->assertNotNull($candidata, 'No existe una oferta Expo con flujo y cantidades para probar el seccionamiento.');

        DB::table('expo_oferta_seccion')->where('flujo_id', $candidata->flujo_id)->delete();
        DB::table('historico_flujo')
            ->where('flujo_id', $candidata->flujo_id)
            ->where('tipo_tramite_id', 2)
            ->update(['observaciones' => null]);
        DB::table('historico_flujo')
            ->where('flujo_id', $candidata->flujo_id)
            ->where('tipo_tramite_id', 2)
            ->where('tramite_id', $candidata->cotizacion_id)
            ->orderByDesc('id')
            ->limit(1)
            ->update(['observaciones' => 'ganadora']);
        DB::table('flujo')->where('id', $candidata->flujo_id)->update(['tipo_tramite_id' => 11]);

        $linea = DB::table('cotizacion_has_producto')->where('id', $candidata->linea_id)->first();

        return [(int) $candidata->flujo_id, (int) $candidata->cotizacion_id, $linea];
    }
}