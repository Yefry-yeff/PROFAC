<?php

namespace App\Support\Comisiones;

class PrecioComisionProyeccion
{
    public static function resolver(
        float $cantidad,
        float $precioUnitario,
        float $precioSeleccionado,
        ?float $precioFacturadoUnitario,
        bool $esCategoriaExpo
    ): array {
        $precioNormal = $precioSeleccionado > 0 ? $precioSeleccionado : $precioUnitario;

        if (!$esCategoriaExpo || $cantidad <= 0 || $precioFacturadoUnitario === null || $precioFacturadoUnitario < 0) {
            return [
                'precio_unitario' => $precioUnitario,
                'precio_comision' => $precioNormal,
                'precio_categoria' => $precioNormal,
                'fuente' => $precioSeleccionado > 0
                    ? 'Cantidad x precioSeleccionado'
                    : 'Cantidad x precio_unidad (fallback)',
            ];
        }

        return [
            'precio_unitario' => round($precioFacturadoUnitario, 2),
            'precio_comision' => $precioFacturadoUnitario,
            'precio_categoria' => round($precioFacturadoUnitario, 2),
            'fuente' => 'Subtotal neto facturado EXPO',
        ];
    }
}
