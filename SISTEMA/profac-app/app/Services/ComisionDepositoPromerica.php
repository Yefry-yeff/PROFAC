<?php

namespace App\Services;

class ComisionDepositoPromerica
{
    public const PORCENTAJE_COMISION_COBRADA = 3;
    public const PORCENTAJE_COMISION_BANCO = 2.6;
    private const PAGO_PAPERLAND_CENTAVOS = 553;

    public static function esPromerica(?string $nombreBanco): bool
    {
        return strtolower(trim((string) $nombreBanco)) === 'promerica';
    }

    public static function esPaperland(?string $nombreBanco): bool
    {
        return strtolower(trim((string) $nombreBanco)) === 'promerica paperland';
    }

    public static function desgloseLibroCobros(?int $movimiento, bool $esPaperland): array
    {
        $comision = self::desgloseMontoAplicadoCentavos($movimiento ?? 0, true);
        $bruto = $comision['monto_recibido'];
        $banco = self::comisionBancoCentavos($bruto);
        $pago = $esPaperland && $movimiento !== null ? self::PAGO_PAPERLAND_CENTAVOS : 0;

        return [
            'monto_recibido' => $movimiento ?? 0,
            'comision_cobrada' => $comision['comision_cobrada'],
            'comision_banco' => $banco,
            'pago_porcentaje' => $pago,
            'monto_movimiento' => $bruto - $banco - $pago,
        ];
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

    public static function desgloseMontoAplicadoCentavos(int $montoAplicado, bool $esPromerica): array
    {
        $comision = $esPromerica
            ? intdiv($montoAplicado * self::PORCENTAJE_COMISION_COBRADA + 50, 100)
            : 0;

        return [
            'monto_recibido' => $montoAplicado + $comision,
            'comision_cobrada' => $comision,
            'monto_aplicado' => $montoAplicado,
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
