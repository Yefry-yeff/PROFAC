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

    public static function obtenerDetalleMovimientos(int $clienteId): array
    {
        $cliente = DB::table('cliente')
            ->where('id', $clienteId)
            ->first(['id', 'nombre', 'rtn', 'credito_inicial']);

        if (!$cliente) {
            return [
                'cliente' => null,
                'credito_aprobado' => 0.0,
                'monto_facturaciones' => 0.0,
                'pendiente_facturar' => 0.0,
                'monto_disponible' => 0.0,
                'saldo_pendiente' => 0.0,
                'cuentas' => [],
                'pendientes_facturar' => [],
                'aprobaciones' => [],
            ];
        }

        $creditoAprobado = (float) ($cliente->credito_inicial ?? 0);
        $cuentas = DB::table('aplicacion_pagos as ap')
            ->leftJoin('factura as f', 'f.id', '=', 'ap.factura_id')
            ->where('ap.cliente_id', $clienteId)
            ->where('ap.estado', 1)
            ->where('ap.estado_cerrado', '<>', 2)
            ->where('ap.saldo', '<>', 0)
            ->orderByDesc('f.fecha_emision')
            ->orderByDesc('ap.id')
            ->get([
                'ap.id',
                'ap.factura_id',
                'ap.total_factura_cargo',
                'ap.saldo',
                'ap.created_at',
                'f.numero_factura',
                'f.fecha_emision',
            ])
            ->map(fn ($cuenta) => (array) $cuenta)
            ->all();

        $montoFacturaciones = (float) collect($cuentas)->sum('saldo');
        $pendientesFacturar = self::obtenerOfertasPendientesFacturar($clienteId);
        $montoPendienteFacturar = (float) collect($pendientesFacturar)->sum('monto');
        $saldoPendiente = $creditoAprobado - ($montoFacturaciones + $montoPendienteFacturar);
        $aprobaciones = DB::table('log_credito as lc')
            ->leftJoin('users as u', 'u.id', '=', 'lc.users_id')
            ->where('lc.cliente_id', $clienteId)
            ->whereIn('lc.tipo', ['aprobacion_oferta', 'ajuste_credito_cliente'])
            ->orderByDesc('lc.id')
            ->get([
                'lc.id',
                'lc.tipo',
                'lc.descripcion',
                'lc.monto',
                'lc.flujo_id',
                'lc.cotizacion_id',
                'lc.saldo_anterior',
                'lc.saldo_resultante',
                'lc.created_at',
                'u.name as usuario',
            ])
            ->map(fn ($movimiento) => (array) $movimiento)
            ->all();

        return [
            'cliente' => (array) $cliente,
            'credito_aprobado' => $creditoAprobado,
            'monto_facturaciones' => $montoFacturaciones,
            'pendiente_facturar' => $montoPendienteFacturar,
            'monto_disponible' => $saldoPendiente,
            'saldo_pendiente' => $saldoPendiente,
            'cuentas' => $cuentas,
            'pendientes_facturar' => $pendientesFacturar,
            'aprobaciones' => $aprobaciones,
        ];
    }

    private static function obtenerOfertasPendientesFacturar(int $clienteId): array
    {
        $ultimaRevisionNormal = DB::table('credito_revision as cr_ultima')
            ->leftJoin('expo_oferta_seccion as eos_ultima', 'eos_ultima.cotizacion_id', '=', 'cr_ultima.cotizacion_id')
            ->whereNull('eos_ultima.id')
            ->select('cr_ultima.flujo_id', DB::raw('MAX(cr_ultima.id) as revision_id'))
            ->groupBy('cr_ultima.flujo_id');

        $normales = DB::table('credito_revision as cr')
            ->joinSub($ultimaRevisionNormal, 'ultima', fn ($join) => $join->on('ultima.revision_id', '=', 'cr.id'))
            ->join('cotizacion as c', 'c.id', '=', 'cr.cotizacion_id')
            ->join('flujo as f', 'f.id', '=', 'cr.flujo_id')
            ->leftJoin('users as u', 'u.id', '=', 'cr.usuario_revision')
            ->where('c.cliente_id', $clienteId)
            ->where('cr.estado', 'aprobado')
            ->whereIn('f.tipo_tramite_id', [9, 4, 3])
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('prefactura as pf')
                    ->whereColumn('pf.flujo_id', 'cr.flujo_id')
                    ->whereColumn('pf.cotizacion_id', 'cr.cotizacion_id')
                    ->where('pf.estado', 'convertida');
            })
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('historico_flujo as hf_factura')
                    ->join('factura as f_factura', 'f_factura.id', '=', 'hf_factura.tramite_id')
                    ->whereColumn('hf_factura.flujo_id', 'cr.flujo_id')
                    ->where('hf_factura.tipo_tramite_id', 3)
                    ->where('hf_factura.estado_id', '!=', 7);
            })
            ->get([
                'cr.id',
                'cr.flujo_id',
                'cr.cotizacion_id',
                'cr.fecha_aprobacion',
                'c.total as monto',
                'u.name as usuario',
            ]);

        $ultimaRevisionExpo = DB::table('credito_revision as cr_ultima')
            ->join('expo_oferta_seccion as eos_ultima', 'eos_ultima.cotizacion_id', '=', 'cr_ultima.cotizacion_id')
            ->select(
                'cr_ultima.flujo_id',
                'cr_ultima.cotizacion_id',
                DB::raw('MAX(cr_ultima.id) as revision_id')
            )
            ->groupBy('cr_ultima.flujo_id', 'cr_ultima.cotizacion_id');

        $expo = DB::table('credito_revision as cr')
            ->joinSub($ultimaRevisionExpo, 'ultima', fn ($join) => $join->on('ultima.revision_id', '=', 'cr.id'))
            ->join('cotizacion as c', 'c.id', '=', 'cr.cotizacion_id')
            ->join('flujo as f', 'f.id', '=', 'cr.flujo_id')
            ->join('expo_oferta_seccion as eos', function ($join) {
                $join->on('eos.flujo_id', '=', 'cr.flujo_id')
                    ->on('eos.cotizacion_id', '=', 'cr.cotizacion_id');
            })
            ->leftJoin('prefactura as pf', 'pf.id', '=', 'eos.prefactura_id')
            ->leftJoin('users as u', 'u.id', '=', 'cr.usuario_revision')
            ->where('c.cliente_id', $clienteId)
            ->where('cr.estado', 'aprobado')
            ->whereIn('f.tipo_tramite_id', [11, 9, 4, 3])
            ->where(function ($query) {
                $query->whereNull('eos.prefactura_id')
                    ->orWhere('pf.estado', '!=', 'convertida');
            })
            ->where('eos.estado', '!=', 'FACTURADA')
            ->get([
                'cr.id',
                'cr.flujo_id',
                'cr.cotizacion_id',
                'cr.fecha_aprobacion',
                'c.total as monto',
                'u.name as usuario',
            ]);

        return $normales
            ->concat($expo)
            ->sortByDesc('fecha_aprobacion')
            ->map(fn ($oferta) => (array) $oferta)
            ->values()
            ->all();
    }
}
