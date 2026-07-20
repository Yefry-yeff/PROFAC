<?php

namespace App\Services\Comisiones;

use Illuminate\Support\Facades\DB;
use App\Models\Comisiones\Escalado\modelproducto_comision;

class GeneradorFacturasComision
{
    /** Códigos de facturación SR que fuerzan comisión por categoría más baja de la escala del cliente. */
    const TIPOS_FACTURA_SR = [
        'sin_restriccion_gobierno',
        'sin_restriccion_precio',
    ];

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
    /** Tipo de comisión: gestor de entrega → siempre con ROL_GESTOR_ENTREGA_ID */
    const TIPO_GESTOR_ENTREGA  = 4;

    /** Rol fijo que representa al gestor de entrega (Gestor de entregas). */
    const ROL_GESTOR_ENTREGA_ID = 16;

    /**
     * Obtiene la categoría de precio "más baja" para una escala de cliente.
     * Prioriza menor porc_precio_a y, en empate, el menor ID.
     */
    private function obtenerCategoriaPrecioMasBaja(int $clienteCategoriaEscalaId): ?int
    {
        if ($clienteCategoriaEscalaId <= 0) {
            return null;
        }

        $categoriaId = DB::table('categoria_precios')
            ->where('cliente_categoria_escala_id', $clienteCategoriaEscalaId)
            ->where('estado_id', 1)
            ->orderByRaw('CASE WHEN porc_precio_a IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('porc_precio_a', 'asc')
            ->orderBy('id', 'asc')
            ->value('id');

        return $categoriaId ? (int) $categoriaId : null;
    }

    /**
     * Genera comisiones para la factura indicada.
     * Por cada factura se construyen hasta 4 entradas de comisión:
     *   1. Facturador (factura.users_id) → siempre con ROL_FACTURADOR_ID (3).
     *   2. Facturador en su rol real     → si su rol_id real ≠ ROL_FACTURADOR_ID.
     *   3. Vendedor   (factura.vendedor)  → siempre con ROL_VENDEDOR_ID (2).
     *   4. Gestor de entrega (factura.gestor_entrega) → siempre con ROL_GESTOR_ENTREGA_ID (16),
     *      solo si el campo no es nulo y tiene comisión activa en comision_rol_config.
    * monto_comision = precio_seleccionado * cantidad * (porcentaje / 100)
    * (si precioSeleccionado no existe/vale 0, usa precio_unidad como fallback histórico)
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

        // Resolver facturador, vendedor y gestor de entrega con sus roles reales
        $fila = DB::selectOne(
            "SELECT f.users_id      AS facturador_id,
                    uf.rol_id       AS facturador_rol,
                    f.vendedor      AS vendedor_id,
                    uv.rol_id       AS vendedor_rol,
                    f.gestor_entrega AS gestor_id,
                    f.tipo_factura_id,
                    tf.codigo       AS tipo_factura_codigo,
                    cl.cliente_categoria_escala_id
             FROM factura f
             INNER JOIN users uf ON uf.id = f.users_id
             INNER JOIN users uv ON uv.id = f.vendedor
             LEFT JOIN cliente cl ON cl.id = f.cliente_id
             LEFT JOIN tipo_factura tf ON tf.id = f.tipo_factura_id
             WHERE f.id = ?",
            [$facturaId]
        );

        if (!$fila || !$fila->facturador_id || !$fila->vendedor_id) {
            return [];
        }

        $tipoFacturaCodigo = (string) ($fila->tipo_factura_codigo ?? '');
        $clienteCategoriaEscalaId = (int) ($fila->cliente_categoria_escala_id ?? 0);
        $esFacturaSr = in_array($tipoFacturaCodigo, self::TIPOS_FACTURA_SR, true);
        $categoriaPrecioForzadaId = null;

        if ($esFacturaSr) {
            $categoriaPrecioForzadaId = $this->obtenerCategoriaPrecioMasBaja($clienteCategoriaEscalaId);
        }

        // ── Construcción de targets ──────────────────────────────────────────
        // Una entrada por persona involucrada:
        //   1. Facturador (quien emitió la factura) → tipo TIPO_FACTURADOR_FIJO
        //   2. Facturador en su rol real            → tipo TIPO_FACTURADOR_ROL
        //   3. Vendedor   (responsable de la venta) → tipo TIPO_VENDEDOR
        //   4. Gestor de entrega (si está asignado)  → tipo TIPO_GESTOR_ENTREGA
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
        //    b) Su rol real coincide con ROL_VENDEDOR_ID: la entrada 3 (VENDEDOR) ya
        //       cubre ese rol, evitando doble comisión para el mismo rol aunque sean
        //       personas distintas.
        $facturadorRol = (int) $fila->facturador_rol;

        if ($facturadorRol !== self::ROL_FACTURADOR_ID && $facturadorRol !== self::ROL_VENDEDOR_ID) {
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

        // 4. Gestor de entrega: solo si la factura tiene uno asignado.
        //    Siempre con ROL_GESTOR_ENTREGA_ID (16).
        //    Si comision_rol_config.calcular = 0 para rol 16, el filtro posterior lo excluirá.
        $gestorId = isset($fila->gestor_id) ? (int) $fila->gestor_id : 0;
        if ($gestorId > 0) {
            $targetsList[] = [
                'user_id' => $gestorId,
                'rol_id'  => self::ROL_GESTOR_ENTREGA_ID,
                'tipo'    => self::TIPO_GESTOR_ENTREGA,
            ];
        }

        // ── Deduplicar por rol_id ────────────────────────────────────────────
        // Solo puede existir UNA comisión por rol, sin importar cuántas personas
        // compartan ese rol en la factura. Prioridad: TIPO_GESTOR > TIPO_VENDEDOR > TIPO_FACTURADOR_ROL > TIPO_FACTURADOR_FIJO
        $uniqueTargets = [];
        foreach ($targetsList as $t) {
            $key = $t['rol_id'];
            if (!isset($uniqueTargets[$key]) || $t['tipo'] > $uniqueTargets[$key]['tipo']) {
                $uniqueTargets[$key] = $t;
            }
        }
        $targetsList = array_values($uniqueTargets);

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

        // Escala activa indexada por "rolId_catClienteId_catPrecioId"
        $escalaRows = DB::table('comision_escala')
            ->whereIn('rol_id', $rolIds)
            ->where('estado_id', 1)
            ->where('cliente_categoria_escala_id', $clienteCategoriaEscalaId)
            ->whereNotNull('categoria_precios_id')
            ->get();

        if ($escalaRows->isEmpty()) {
            return [];
        }

        $escala = [];
        foreach ($escalaRows as $row) {
            $escala[$row->rol_id . '_' . $row->cliente_categoria_escala_id . '_' . $row->categoria_precios_id] = $row;
        }

        // Líneas de producto con su categoria_precios_id
        $productos = DB::table('venta_has_producto as vp')
            ->join('precios_producto_carga as ppc', 'ppc.id', '=', 'vp.precios_producto_carga_id')
            ->where('vp.factura_id', $facturaId)
            ->selectRaw(
                'vp.cantidad,
                 vp.precio_unidad,
                 vp.precioSeleccionado,
                 COALESCE(NULLIF(vp.precioSeleccionado, 0), vp.precio_unidad) as precio_para_comision,
                 vp.precios_producto_carga_id,
                 vp.producto_id,
                 ppc.categoria_precios_id'
            )
            ->get();

        if ($productos->isEmpty()) {
            return [];
        }

        // Regla SR: precargar precio_a de la categoría más baja para comparar por producto.
        // La categoría forzada solo aplica si el precio vendido es MAYOR al precio de esa categoría.
        $precioRefMasBajaMap = [];
        if ($esFacturaSr && $categoriaPrecioForzadaId) {
            $precioRefs = DB::table('precios_producto_carga')
                ->where('categoria_precios_id', $categoriaPrecioForzadaId)
                ->whereIn('producto_id', $productos->pluck('producto_id')->unique()->values()->all())
                ->select('producto_id', 'precio_a')
                ->get();
            foreach ($precioRefs as $ref) {
                $precioRefMasBajaMap[(int) $ref->producto_id] = (float) $ref->precio_a;
            }
        }

        $resultado = [];

        foreach ($targetsList as $target) {
            $userId = $target['user_id'];
            $rolId  = $target['rol_id'];
            $tipo   = $target['tipo'];

            $totalTarget    = 0.0;
            $lineasProducto = [];

            foreach ($productos as $prod) {
                // Regla SR: usar la categoría más baja SOLO si el precio vendido es
                // estrictamente mayor al precio_a de esa categoría para este producto.
                // Si es menor o igual, se comisiona por la categoría real de la línea.
                if ($esFacturaSr && $categoriaPrecioForzadaId) {
                    $precioVendido  = (float) ($prod->precio_para_comision ?? $prod->precio_unidad ?? 0);
                    $precioRefBaja  = $precioRefMasBajaMap[(int) $prod->producto_id] ?? null;
                    // Penalizar con categoría más baja SOLO si vendió por DEBAJO del precio de esa categoría.
                    // Si vendió igual o por encima, comisiona por la categoría real usada en la venta.
                    $categoriaPrecioParaComision = ($precioRefBaja !== null && $precioVendido < $precioRefBaja)
                        ? $categoriaPrecioForzadaId
                        : (int) $prod->categoria_precios_id;
                } else {
                    $categoriaPrecioParaComision = (int) $prod->categoria_precios_id;
                }

                $key = $rolId . '_' . $clienteCategoriaEscalaId . '_' . $categoriaPrecioParaComision;
                if (!isset($escala[$key])) {
                    continue;
                }

                $precioParaComision = (float) ($prod->precio_para_comision ?? $prod->precio_unidad ?? 0);
                $pct        = (float) $escala[$key]->porcentaje_comision;
                $montoLinea = round(($pct / 100) * $precioParaComision * $prod->cantidad, 4);
                $totalTarget += $montoLinea;

                $lineasProducto[] = [
                    'cantidad'                  => $prod->cantidad,
                    'precio_venta'              => $precioParaComision,
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

