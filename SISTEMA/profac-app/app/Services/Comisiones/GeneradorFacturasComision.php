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
    const ROL_FACTURADOR_ID    = 3;
    /** Rol fijo que representa la capacidad de "vendedor" (Asesor Comercial). */
    const ROL_VENDEDOR_ID      = 2;

    /** Tipo de comisión: facturador con rol fijo (ROL_FACTURADOR_ID) */
    const TIPO_FACTURADOR_FIJO = 1;
    /** Tipo de comisión: facturador en su rol real (si difiere del fijo) */
    const TIPO_FACTURADOR_ROL  = 2;
    /** Tipo de comisión: vendedor de la factura → siempre con ROL_VENDEDOR_ID */
    const TIPO_VENDEDOR        = 3;

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
        // Prevenir doble registro solo si hay comisiones activas.
        // Si solo existen comisiones inactivas (revertidas), se permite recalcular.
        if (DB::table('facturas_comision')->where('factura_id', $facturaId)->where('estado_id', 1)->exists()) {
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
        // Una entrada por persona involucrada usando su ROL REAL:
        //   1. Facturador (quien emitió la factura) → tipo TIPO_FACTURADOR_FIJO
        //   2. Vendedor   (responsable de la venta)  → tipo TIPO_VENDEDOR
        //      Se omite si es la misma persona CON el mismo rol que el facturador
        //      (solo habría un registro, evitando doble comisión).
        // El panel de control (comision_rol_config) filtra quién calcula y quién no.
        $targetsList = [];

        // 1. Facturador: siempre con el rol fijo ROL_FACTURADOR_ID (= 3 = Televendedor)
        $targetsList[] = [
            'user_id' => (int) $fila->facturador_id,
            'rol_id'  => self::ROL_FACTURADOR_ID,
            'tipo'    => self::TIPO_FACTURADOR_FIJO,
        ];

        // 2. Facturador en su rol real (Administrador, Gerente, etc.)
        //    Se omite si:
        //    a) Su rol real coincide con ROL_FACTURADOR_ID (ya cubierto por entrada 1).
        //    b) Su rol real coincide con ROL_VENDEDOR_ID y ademas es la misma persona
        //       que el vendedor: la entrada 3 ya cubre esa comision, evitando doble pago.
        $facturadorRol     = (int) $fila->facturador_rol;
        $mismaPersona      = ((int) $fila->facturador_id === (int) $fila->vendedor_id);
        $rolRealEsVendedor = ($facturadorRol === self::ROL_VENDEDOR_ID);

        if ($facturadorRol !== self::ROL_FACTURADOR_ID && !($rolRealEsVendedor && $mismaPersona)) {
            $targetsList[] = [
                'user_id' => (int) $fila->facturador_id,
                'rol_id'  => $facturadorRol,
                'tipo'    => self::TIPO_FACTURADOR_ROL,
            ];
        }

        // 3. Vendedor: SIEMPRE con ROL_VENDEDOR_ID (2 = Asesor Comercial),
        //    independientemente del rol real del usuario en la tabla users.
        //    tipo_comision = TIPO_VENDEDOR lo distingue de las entradas 1 y 2.
        $vendedorId = (int) $fila->vendedor_id;
        $targetsList[] = [
            'user_id' => $vendedorId,
            'rol_id'  => self::ROL_VENDEDOR_ID,
            'tipo'    => self::TIPO_VENDEDOR,
        ];

        $rolIds = array_unique(array_column($targetsList, 'rol_id'));

        // ── Filtrar roles con cálculo de comisión deshabilitado ──────────────
        // comision_rol_config.calcular = 0 → excluir ese rol del procesamiento.
        // Si el registro no existe se asume habilitado (calcular = 1).
        $rolesDesactivados = DB::table('comision_rol_config')
            ->whereIn('rol_id', $rolIds)
            ->where('calcular', 0)
            ->pluck('rol_id')
            ->flip()
            ->all();

        if (!empty($rolesDesactivados)) {
            $targetsList = array_values(
                array_filter($targetsList, fn($t) => !isset($rolesDesactivados[$t['rol_id']]))
            );
            $rolIds = array_unique(array_column($targetsList, 'rol_id'));
        }

        if (empty($targetsList)) {
            return [];
        }

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
            $tipo   = $target['tipo'];

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
                'tipo_comision'         => $tipo,
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
                'tipo_comision'        => $tipo,
                'target_user_id'       => $userId,
            ];
        }

        return $resultado;
    }
}

