<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CreditoService
{
    /**
     * Calcula el monto disponible de crédito para un cliente.
     *
     * Fuente de bloqueo alineada con Aplicacion de Pagos:
     *   tabla aplicacion_pagos, mismos filtros de la vista de saldos.
     *
     * Regla:
        *   disponible = MAX(0, MIN(limite, limite - SUM(saldo pendiente)))
     *
     * Se consideran solo registros con:
     *   - estado = 1
     *   - estado_cerrado <> 2
     *   - saldo <> 0
     *
     * @param int        $clienteId
     * @param float|null $limite    Si null, se lee credito_inicial de la tabla cliente
     * @return float
     */
    public static function calcularDisponible(int $clienteId, ?float $limite = null): float
    {
        if ($limite === null) {
            $cliente = DB::selectOne("SELECT credito_inicial FROM cliente WHERE id = ?", [$clienteId]);
            $limite  = $cliente ? (float)$cliente->credito_inicial : 0;
        }

        if ($limite <= 0) {
            return 0.0;
        }

                $bloqueado = (float) DB::selectOne(" 
                    SELECT COALESCE(SUM(ap.saldo), 0) AS bloqueado
                        FROM aplicacion_pagos ap
                        WHERE ap.cliente_id = ?
                            AND ap.estado = 1
                            AND ap.estado_cerrado <> 2
                            AND ap.saldo <> 0
                ", [$clienteId])->bloqueado;

        // Disponible no puede ser negativo ni superar el límite
        return max(0.0, min($limite, $limite - $bloqueado));
    }

    /**
     * Recalcula y persiste cliente.credito con el valor calculado.
     *
     * @param int        $clienteId
     * @param float|null $limite
     * @return float  El monto disponible actualizado
     */
    public static function actualizarDisponible(int $clienteId, ?float $limite = null): float
    {
        $disponible = self::calcularDisponible($clienteId, $limite);

        DB::table('cliente')
            ->where('id', $clienteId)
            ->update(['credito' => $disponible, 'updated_at' => now()]);

        return $disponible;
    }
}
