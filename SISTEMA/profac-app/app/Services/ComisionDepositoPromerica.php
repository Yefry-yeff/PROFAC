<?php

namespace App\Services;

class ComisionDepositoPromerica
{
    public const PORCENTAJE_COMISION_COBRADA = 3;
    public const PORCENTAJE_COMISION_BANCO = 2.6;

    public static function esPromerica(?string $nombreBanco): bool
    {
        return strtolower(trim((string) $nombreBanco)) === 'promerica';
    }

    public static function desgloseCentavos(int $montoRecibido, bool $esPromerica): array
    {
        $comision = $esPromerica
            ? intdiv($montoRecibido * self::PORCENTAJE_COMISION_COBRADA + 50, 100)
            : 0;

        return [
            'monto_recibido' => $montoRecibido,
            'comision_cobrada' => $comision,
            'monto_aplicado' => $montoRecibido - $comision,
        ];
    }

    public static function comisionBancoCentavos(int $montoRecibido): int
    {
        return intdiv($montoRecibido * 26 + 500, 1000);
    }

    public static function centavos(string $monto): int
    {
        if (!preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D', $monto)) {
            throw new \InvalidArgumentException('El monto debe tener hasta dos decimales.');
        }

        [$entero, $decimal] = array_pad(explode('.', $monto, 2), 2, '');

        return (int) $entero * 100 + (int) str_pad($decimal, 2, '0');
    }

    public static function decimal(int $centavos): string
    {
        return intdiv($centavos, 100) . '.' . str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }
}
