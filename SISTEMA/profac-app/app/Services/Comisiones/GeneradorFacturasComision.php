<?php

namespace App\Services\Comisiones;

use Illuminate\Support\Facades\DB;
use App\Models\Comisiones\Escalado\modelproducto_comision;

class GeneradorFacturasComision
{
    /**
     * Rol fijo que representa la capacidad de "facturador" en comision_escala.
     * Se usa independientemente del rol real del usuario que creó la factura.
     */
    const ROL_FACTURADOR_ID = 3;

    /**
     * Genera comisiones para la factura indicada.
     * Por cada factura se construyen hasta 3 entradas de comisión:
     *   1. Facturador (factura.users_id) → siempre con ROL_FACTURADOR_ID (3).
     *   2. Facturador en su rol real     → si su rol_id real ≠ ROL_FACTURADOR_ID.
     *   3. Vendedor   (factura.vendedor)  → su rol_id real, si la combinación
     *      user_id+rol_id no está ya en la lista (evita duplicados).
     * monto_comision = precio_unidad * cantidad * (porcentaje / 100)
     */
    /**
     * @param string|null $fechaPago  Fecha del pago del usuario (YYYY-MM-DD).
     *                                Si es null se usa la fecha actual del sistema.
     */
    public function generar(int $facturaId, int $aplicacionPagoId, ?string $fechaPago = null): array
    {
        // Prevenir doble registro
        if (DB::table('facturas_comision')->where('factura_id', $facturaId)->exists()) {
            return [];
        }

        $fechaComision = $fechaPago ?? now()->toDateString();

        // Resolver facturador y vendedor con sus roles reales
        $fila = DB::selectOne(
            "SELECT f.users_id AS facturador_id,
                    uf.rol_id   AS facturador_rol,
                    f.vendedor  AS vendedor_id,
                    uv.rol_id   AS vendedor_rol
             FROM factura f
             INNER JOIN users uf ON uf.id = f.users_id
             INNER JOIN users uv ON uv.id = f.vendedor
             WHERE f.id = ?",
            [$facturaId]
        );

        if (!$fila || !$fila->facturador_id || !$fila->vendedor_id) {
            return [];
        }

        // ── Construcción de targets ──────────────────────────────────────────
        // Helper: agrega una entrada solo si la combinación user_id+rol_id
        // no existe ya en la lista (evita duplicar comisiones).
        $targetsList = [];
        $pushIfNew = function (int $userId, int $rolId) use (&$targetsList): void {
            foreach ($targetsList as $t) {
                if ($t['user_id'] === $userId && $t['rol_id'] === $rolId) {
                    return;
                }
            }
            $targetsList[] = ['user_id' => $userId, 'rol_id' => $rolId];
        };

        // 1. Facturador: siempre con el rol de facturador (hardcoded = 3)
        $pushIfNew((int) $fila->facturador_id, self::ROL_FACTURADOR_ID);

        // 2. Facturador en su rol real (admin, gerente, etc.)
        //    Solo si su rol real es distinto al de facturador
        if ((int) $fila->facturador_rol !== self::ROL_FACTURADOR_ID) {
            $pushIfNew((int) $fila->facturador_id, (int) $fila->facturador_rol);
        }

        // 3. Vendedor con su rol real
        $pushIfNew((int) $fila->vendedor_id, (int) $fila->vendedor_rol);

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
                'fecha_cierre_factura'  => $fechaComision,
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
                'fecha_cierre_factura' => $fechaComision,
                'monto_rol'            => round($totalTarget, 4),
                'rol_id'               => $rolId,
                'target_user_id'       => $userId,
            ];
        }

        return $resultado;
    }
}

