<?php

namespace App\Services\Comisiones;

use Illuminate\Support\Facades\DB;
use App\Models\Comisiones\Escalado\modelproducto_comision;

class GeneradorFacturasComision
{
    /**
     * rol_id que se usa SIEMPRE para el facturador,
     * independientemente del rol real del usuario que creó la factura.
     */
    const ROL_FACTURADOR_ID = 3;

    /**
     * Genera comisiones para la factura indicada.
     * - Facturador: factura.users_id, siempre con rol ROL_FACTURADOR_ID.
     * - Vendedor:   factura.vendedor,  con su rol_id real.
     * - Si son la misma persona generan dos registros separados (uno por rol),
     *   salvo que su rol real ya sea ROL_FACTURADOR_ID (en ese caso solo uno).
     * - monto_comision = precio_unidad * cantidad * (porcentaje / 100)
     */
    public function generar(int $facturaId, int $aplicacionPagoId): array
    {
        // Prevenir doble registro
        if (DB::table('facturas_comision')->where('factura_id', $facturaId)->exists()) {
            return [];
        }

        // Resolver facturador y vendedor
        $fila = DB::selectOne(
            "SELECT f.users_id AS facturador_id,
                    f.vendedor  AS vendedor_id,
                    uv.rol_id   AS vendedor_rol
             FROM factura f
             INNER JOIN users uv ON uv.id = f.vendedor
             WHERE f.id = ?",
            [$facturaId]
        );

        if (!$fila || !$fila->facturador_id || !$fila->vendedor_id) {
            return [];
        }

        // Lista de actores que comisionan: [user_id, rol_id]
        // Facturador: siempre ROL_FACTURADOR_ID
        // Vendedor: su rol real. Solo se omite si es la misma persona Y mismo rol.
        $targetsList = [];
        $targetsList[] = [
            'user_id' => (int) $fila->facturador_id,
            'rol_id'  => self::ROL_FACTURADOR_ID,
        ];

        $mismaPersona = (int) $fila->vendedor_id  === (int) $fila->facturador_id;
        $mismoRol     = (int) $fila->vendedor_rol === self::ROL_FACTURADOR_ID;

        if (!$mismaPersona || !$mismoRol) {
            $targetsList[] = [
                'user_id' => (int) $fila->vendedor_id,
                'rol_id'  => (int) $fila->vendedor_rol,
            ];
        }

        $rolIds = array_unique(array_column($targetsList, 'rol_id'));

        // Escala activa indexada por "rolId_catPrecioId"
        $escalaRows = DB::table('comision_escala')
            ->whereIn('rol_id', $rolIds)
            ->where('estado_id', 1)
            ->whereNotNull('categoria_precios_id')
            ->get();

        if ($escalaRows->isEmpty()) {
            return [];
        }

        $escala = [];
        foreach ($escalaRows as $row) {
            $escala[$row->rol_id . '_' . $row->categoria_precios_id] = $row;
        }

        // Líneas de producto con su categoria_precios_id
        $productos = DB::table('venta_has_producto as vp')
            ->join('precios_producto_carga as ppc', 'ppc.id', '=', 'vp.precios_producto_carga_id')
            ->where('vp.factura_id', $facturaId)
            ->select(
                'vp.cantidad',
                'vp.precio_unidad',
                'vp.precios_producto_carga_id',
                'vp.producto_id',
                'ppc.categoria_precios_id'
            )
            ->get();

        if ($productos->isEmpty()) {
            return [];
        }

        $resultado = [];

        foreach ($targetsList as $target) {
            $userId = $target['user_id'];
            $rolId  = $target['rol_id'];

            $totalTarget    = 0.0;
            $lineasProducto = [];

            foreach ($productos as $prod) {
                $key = $rolId . '_' . $prod->categoria_precios_id;
                if (!isset($escala[$key])) {
                    continue;
                }

                $pct        = (float) $escala[$key]->porcentaje_comision;
                $montoLinea = round(($pct / 100) * $prod->precio_unidad * $prod->cantidad, 4);
                $totalTarget += $montoLinea;

                $lineasProducto[] = [
                    'cantidad'                  => $prod->cantidad,
                    'precio_venta'              => $prod->precio_unidad,
                    'monto_comision'            => $montoLinea,
                    'precios_producto_carga_id' => $prod->precios_producto_carga_id,
                    'factura_id'                => $facturaId,
                    'producto_id'               => $prod->producto_id,
                    'rol_id'                    => $rolId,
                    'estado_id'                 => 1,
                    // facturas_comision_id se asigna abajo tras insertar facturas_comision
                    'created_at'                => now(),
                    'updated_at'                => now(),
                ];
            }

            if ($totalTarget <= 0) {
                continue;
            }

            // Insertar facturas_comision y obtener ID generado
            $facturaComisionId = DB::table('facturas_comision')->insertGetId([
                'fecha_cierre_factura'  => now()->toDateString(),
                'monto_rol'             => round($totalTarget, 4),
                'factura_id'            => $facturaId,
                'comision_escala_id'    => null,
                'aplicacion_pagos_id'   => $aplicacionPagoId,
                'rol_id'                => $rolId,
                'estado_id'             => 1,
                'cantidad_usuariosxrol' => 1,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            // Enlazar cada línea al registro de facturas_comision recién insertado
            foreach ($lineasProducto as &$linea) {
                $linea['facturas_comision_id'] = $facturaComisionId;
            }
            unset($linea);

            modelproducto_comision::insert($lineasProducto);

            // Datos efímeros para ProcesadorComisiones
            $resultado[] = [
                'facturas_comision_id' => $facturaComisionId,
                'fecha_cierre_factura' => now()->toDateString(),
                'monto_rol'            => round($totalTarget, 4),
                'rol_id'               => $rolId,
                'target_user_id'       => $userId,
            ];
        }

        return $resultado;
    }
}

