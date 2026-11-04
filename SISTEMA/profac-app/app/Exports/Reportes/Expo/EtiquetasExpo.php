<?php

namespace App\Exports\Reportes\Expo;

/** Textos y colores legibles para los códigos usados en los Excel de Expo. */
class EtiquetasExpo
{
    private const ESTADOS = [
        'GANADORA' => 'Ganadora',
        'NO_GANADORA' => 'No ganadora',
        'NO_GANADORA_CON_SECCIONES' => 'No ganadora con secciones',
        'PENDIENTE_FACTURACION' => 'Pendiente de facturación',
        'FACTURACION_PARCIAL' => 'Facturación parcial',
        'PENDIENTE_LIQUIDACION' => 'Pendiente de liquidación',
        'LIQUIDADA' => 'Liquidada',
        'SIN_REGISTRO' => 'Sin registro',
        'FACTURADA' => 'Facturada',
        'PARCIALMENTE_FACTURADA' => 'Parcialmente facturada',
        'NO_FACTURADA' => 'No facturada',
        'DEVUELTA_SECCION' => 'Devuelta a sección',
    ];

    public const COLORES_TIPO = [
        'Ganadora' => '15803D',
        'No ganadora' => '64748B',
        'No ganadora con secciones' => 'B45309',
    ];

    public const COLORES_FACTURACION = [
        'Facturada' => '15803D',
        'Parcialmente facturada' => 'B45309',
        'No facturada' => 'B91C1C',
    ];

    public static function estado(?string $codigo): string
    {
        if ($codigo === null || $codigo === '') {
            return '';
        }

        if (isset(self::ESTADOS[$codigo])) {
            return self::ESTADOS[$codigo];
        }
        $texto = str_replace('_', ' ', mb_strtolower($codigo));

        return mb_strtoupper(mb_substr($texto, 0, 1)) . mb_substr($texto, 1);
    }
}
