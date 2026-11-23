<?php

namespace App\Services\Expo;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CondicionesPagoPrefactura
{
    /** @return array{fecha_emision: string, fecha_vencimiento: string, tipo_pago_id: int, dias_credito: int} */
    public function resolver(object $prefactura): array
    {
        $fechas = Validator::make([
            'fecha_emision' => $prefactura->fecha_emision,
            'fecha_vencimiento' => $prefactura->fecha_vencimiento,
        ], [
            'fecha_emision' => 'required|date_format:Y-m-d',
            'fecha_vencimiento' => 'required|date_format:Y-m-d',
        ])->validate();

        $dias = max(0, (int) Carbon::parse($fechas['fecha_emision'])
            ->diffInDays(Carbon::parse($fechas['fecha_vencimiento']), false));
        $tipoPago = DB::table('cotizacion')->where('id', $prefactura->cotizacion_id)->value('tipo_pago_id');

        // Una seccion de credito vencida sigue siendo credito, aunque no queden dias.
        // Las ofertas antiguas sin tipo explicito conservan la inferencia por fechas.
        $tipoPago = $tipoPago === null ? ($dias > 0 ? 2 : 1) : (int) $tipoPago;
        Validator::make(['tipo_pago_id' => $tipoPago], ['tipo_pago_id' => 'required|in:1,2'])->validate();

        return $fechas + [
            'tipo_pago_id' => $tipoPago,
            'dias_credito' => $tipoPago === 2 ? $dias : 0,
        ];
    }
}
