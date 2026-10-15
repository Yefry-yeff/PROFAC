<?php

namespace App\Support\Logistica;

class FacturasDistribucion
{
    public const FECHA_INICIO = '2026-05-19';

    public static function condicionFecha(string $alias = 'f'): string
    {
        $inicio = self::FECHA_INICIO;
        $fin = now()->addDay()->toDateString();

        return "{$alias}.fecha_emision >= '{$inicio}' AND {$alias}.fecha_emision < '{$fin}'";
    }
}
