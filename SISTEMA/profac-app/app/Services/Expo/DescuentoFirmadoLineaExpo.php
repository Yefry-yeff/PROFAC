<?php

namespace App\Services\Expo;

class DescuentoFirmadoLineaExpo
{
    public function prorratear(float $descuentoFirmado, float $cantidadOfertada, float $cantidadFacturada): float
    {
        if ($descuentoFirmado <= 0 || $cantidadOfertada <= 0 || $cantidadFacturada <= 0) {
            return 0.0;
        }

        $proporcion = min($cantidadFacturada, $cantidadOfertada) / $cantidadOfertada;

        return round($descuentoFirmado * $proporcion, 2);
    }
}