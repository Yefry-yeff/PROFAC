<?php

namespace App\Services\Expo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecalculadorFacturaExpo
{
    public function __construct(private CalculadorDescuentosExpo $calculador)
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

        $snapshot = json_decode((string) ($expoCotizacion->reglas_descuento_snapshot ?? ''), true) ?: [];
        $reglas = array_key_exists('generales', $snapshot)
            ? $snapshot
            : ['generales' => $snapshot, 'marcas' => []];
        $productoIds = collect($indices)
            ->map(fn ($indice) => (int) $request->input('idProducto' . $indice, 0))
            ->filter()
            ->unique();
        $marcas = DB::table('producto')->whereIn('id', $productoIds)->pluck('marca_id', 'id');
        $lineas = [];

        foreach ($indices as $indice) {
            $productoId = (int) $request->input('idProducto' . $indice, 0);
            $lineas[(string) $indice] = [
                'marca_id' => (int) ($marcas[$productoId] ?? 0),
                'subtotal_bruto' => round(
                    (float) $request->input('precio' . $indice, 0)
                    * (float) $request->input('cantidad' . $indice, 0)
                    * (float) $request->input('unidad' . $indice, 0),
                    2
                ),
            ];
        }

        $calculo = $this->calculador->calcular(array_values($lineas), $reglas);
        $cambios = [];
        $subtotalGeneral = 0.0;
        $subtotalGrabado = 0.0;
        $subtotalExento = 0.0;
        $isvGeneral = 0.0;
        $totalGeneral = 0.0;
        $descuentoGeneral = 0.0;

        foreach ($lineas as $indice => $linea) {
            $bruto = $linea['subtotal_bruto'];
            $porcentajeMarca = (float) ($calculo['porcentajes_marca'][$linea['marca_id']] ?? 0);
            $descuentoMarca = round($bruto * $porcentajeMarca / 100, 2);
            $descuentoSubtotal = round(
                ($bruto - $descuentoMarca) * (float) $calculo['porcentaje_general'] / 100,
                2
            );
            $descuento = round($descuentoMarca + $descuentoSubtotal, 2);
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