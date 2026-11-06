<?php

namespace App\Support\Logistica;

class FacturasDistribucion
{
    public const FECHA_INICIO = '2026-05-19';
    public const FECHA_INICIO_NUEVA_DISTRIBUCION = '2026-10-01';

    public static function condicionFecha(string $alias = 'f'): string
    {
        return self::condicionFechaDesde(self::FECHA_INICIO, $alias);
    }

    public static function condicionFechaNuevaDistribucion(string $alias = 'f'): string
    {
        return self::condicionFechaDesde(self::FECHA_INICIO_NUEVA_DISTRIBUCION, $alias);
    }

    private static function condicionFechaDesde(string $inicio, string $alias): string
    {
        $fin = now()->addDay()->toDateString();

        return "{$alias}.fecha_emision >= '{$inicio}' AND {$alias}.fecha_emision < '{$fin}'";
    }
}
