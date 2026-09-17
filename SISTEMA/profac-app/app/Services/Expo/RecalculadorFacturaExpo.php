<?php

namespace App\Services\Expo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecalculadorFacturaExpo
{
    public function __construct(private DescuentoFirmadoLineaExpo $descuentoFirmado)
    {
    }

    /** @param array<int, string|int> $indices */
    public function aplicar(Request $request, array $indices): void
    {
        $cotizacionId = (int) $request->input('cotizacion_id', 0);
        $expoCotizacion = $cotizacionId > 0
            ? DB::table('expo_cotizacion')->where('cotizacion_id', $cotizacionId)->first(['reglas_descuento_snapshot'])
            : null;

        if (!$expoCotizacion) {
            return;
        }

        $lineaIds = collect($indices)
            ->map(fn ($indice) => (int) $request->input('cotizacionLineaId' . $indice, 0))
            ->filter()
            ->unique();
        $lineasOferta = DB::table('cotizacion_has_producto')
            ->where('cotizacion_id', $cotizacionId)
            ->whereIn('id', $lineaIds)
            ->get(['id', 'cantidad', 'monto_descProducto'])
            ->keyBy('id');
        $lineas = [];

        foreach ($indices as $indice) {
            $lineaId = (int) $request->input('cotizacionLineaId' . $indice, 0);
            $lineaOferta = $lineasOferta[$lineaId] ?? null;
            $lineas[(string) $indice] = [
                'subtotal_bruto' => round(
                    (float) $request->input('precio' . $indice, 0)
                    * (float) $request->input('cantidad' . $indice, 0)
                    * (float) $request->input('unidad' . $indice, 0),
                    2
                ),
                'cantidad_ofertada' => (float) ($lineaOferta->cantidad ?? 0),
                'cantidad_facturada' => (float) $request->input('cantidadOfertaAplicada' . $indice, 0),
                'descuento_firmado' => (float) ($lineaOferta->monto_descProducto ?? 0),
            ];
        }

        $cambios = [];
        $subtotalGeneral = 0.0;
        $subtotalGrabado = 0.0;
        $subtotalExento = 0.0;
        $isvGeneral = 0.0;
        $totalGeneral = 0.0;
        $descuentoGeneral = 0.0;

        foreach ($lineas as $indice => $linea) {
            $bruto = $linea['subtotal_bruto'];
            $descuento = min($bruto, $this->descuentoFirmado->prorratear(
                $linea['descuento_firmado'],
                $linea['cantidad_ofertada'],
                $linea['cantidad_facturada']
            ));
            $subtotal = round($bruto - $descuento, 2);
            $porcentajeIsv = (float) $request->input('isv' . $indice, 0);
            $isv = round($subtotal * $porcentajeIsv / 100, 2);
            $total = round($subtotal + $isv, 2);

            $cambios['acumuladoDescuento' . $indice] = $descuento;
            $cambios['subTotal' . $indice] = $subtotal;
            $cambios['isvProducto' . $indice] = $isv;
            $cambios['total' . $indice] = $total;
            $subtotalGeneral += $subtotal;
            $isvGeneral += $isv;
            $totalGeneral += $total;
            $descuentoGeneral += $descuento;
            $porcentajeIsv > 0 ? $subtotalGrabado += $subtotal : $subtotalExento += $subtotal;
        }

        $request->merge(array_merge($cambios, [
            'subTotalGeneral' => round($subtotalGeneral, 2),
            'subTotalGeneralGrabado' => round($subtotalGrabado, 2),
            'subTotalGeneralExcento' => round($subtotalExento, 2),
            'isvGeneral' => round($isvGeneral, 2),
            'totalGeneral' => round($totalGeneral, 2),
            'porDescuento' => 0,
            'porDescuentoCalculado' => round($descuentoGeneral, 2),
        ]));
    }
}