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
                'fuente' => $precioSeleccionado > 0
                    ? 'Cantidad x precioSeleccionado'
                    : 'Cantidad x precio_unidad (fallback)',
            ];
        }

        return [
            'precio_unitario' => $precioFacturadoUnitario,
            'precio_comision' => $precioFacturadoUnitario,
            'fuente' => 'Subtotal neto facturado EXPO',
        ];
    }
}
