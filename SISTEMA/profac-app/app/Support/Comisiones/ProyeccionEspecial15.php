<?php

namespace App\Support\Comisiones;

class ProyeccionEspecial15
{
    public const PORCENTAJE_FIJO = 15.0;

    private const CLIENTE_IDS_PORCENTAJE_FIJO = [
        1374,
        1394,
        1398,
        1399,
        1402,
        1408,
    ];

    public static function aplicaPorcentajeFijo(array $row): bool
    {
        return in_array((int) ($row['cliente_id'] ?? 0), self::CLIENTE_IDS_PORCENTAJE_FIJO, true);
    }

    public static function calcular(array $row): array
    {
        $aplicaPorcentajeFijo = self::aplicaPorcentajeFijo($row);
        $base = (float) ($row['base_comisionable'] ?? 0);

        return [
            'regla' => $aplicaPorcentajeFijo ? 'FIJO 15%' : 'ESCALA NORMAL',
            'porcentaje' => $aplicaPorcentajeFijo
                ? self::PORCENTAJE_FIJO
                : (float) ($row['porcentaje_promedio'] ?? 0),
            'comision' => $aplicaPorcentajeFijo
                ? round($base * (self::PORCENTAJE_FIJO / 100), 4)
                : (float) ($row['comision_proyectada'] ?? 0),
        ];
    }

}