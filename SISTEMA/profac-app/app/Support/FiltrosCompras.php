<?php

namespace App\Support;

class FiltrosCompras
{
    public static function validar(array $datos): array
    {
        return validator($datos + [
            'desde' => now()->startOfMonth()->toDateString(),
            'hasta' => now()->endOfMonth()->toDateString(),
            'factura' => '', 'proveedor' => '',
        ], [
            'desde' => 'required|date_format:Y-m-d',
            'hasta' => 'required|date_format:Y-m-d|after_or_equal:desde',
            'factura' => 'nullable|string|max:255',
            'proveedor' => 'nullable|integer|exists:proveedores,id',
        ], [
            'required' => 'Indique ambas fechas de emision.',
            'date_format' => 'Ingrese una fecha valida.',
            'after_or_equal' => 'La fecha hasta no puede ser anterior a la fecha desde.',
            'exists' => 'Seleccione un proveedor valido.',
            'integer' => 'Seleccione un proveedor valido.',
            'max' => 'El numero de factura no puede superar 255 caracteres.',
        ])->validate();
    }

    public static function aplicar($query, array $filtros, string $tabla, string $proveedor)
    {
        return $query->whereBetween($tabla . '.fecha_emision', [$filtros['desde'], $filtros['hasta']])
            ->when(trim($filtros['factura'] ?? '') !== '', fn ($query) =>
                $query->where($tabla . '.numero_factura', 'like', '%' . trim($filtros['factura']) . '%'))
            ->when($filtros['proveedor'] ?? '', fn ($query, $id) => $query->where($tabla . '.' . $proveedor, $id));
    }
}
