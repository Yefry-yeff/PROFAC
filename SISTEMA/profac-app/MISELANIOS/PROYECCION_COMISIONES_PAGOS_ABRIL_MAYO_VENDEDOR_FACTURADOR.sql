-- ============================================================================
-- PROYECCION DE COMISIONES POR FACTURAS PAGADAS EN ABRIL Y MAYO
-- ALCANCE: SOLO VENDEDOR Y FACTURADOR
-- BASE: facturas cerradas en aplicacion_pagos durante abril/mayo
-- LOGICA: usa la parametrizacion ACTUAL de comision_escala, categoria_precios,
--         comision_rol_config y dias_gracia_comision.
--
-- IMPORTANTE
-- 1. Este script es SOLO LECTURA. No inserta ni actualiza datos permanentes.
-- 2. La proyeccion usa la parametrizacion vigente HOY, no un snapshot historico.
-- 3. Facturas SR (sin_restriccion_gobierno / sin_restriccion_precio) usan la
--    categoria de precio MAS BAJA actual de la escala del cliente, tal como el motor.
-- 4. La retencion en la fuente por empleado/periodo NO se aplica aqui.
--    Este script proyecta:
--      - comision_bruta_proyectada
--      - retencion_mora_proyectada
--      - comision_neta_mora_proyectada
-- ============================================================================

SET @fechaInicio = '2026-04-01';
SET @fechaFin    = '2026-05-31';

DROP TEMPORARY TABLE IF EXISTS tmp_proyeccion_comisiones_pagadas;

CREATE TEMPORARY TABLE tmp_proyeccion_comisiones_pagadas AS
WITH
facturas_pagadas AS (
    SELECT
        ap.id AS aplicacion_pagos_id,
        ap.factura_id,
        DATE(ap.fecha_cierre_factura) AS fecha_cierre_pago,
        DATE_FORMAT(ap.fecha_cierre_factura, '%Y-%m') AS periodo_pago,
        f.cai,
        f.total,
        f.sub_total,
        f.tipo_pago_id,
        DATE(f.fecha_emision) AS fecha_emision,
        DATE(f.fecha_vencimiento) AS fecha_vencimiento,
        f.users_id AS facturador_id,
        uf.name AS facturador_nombre,
        uf.rol_id AS facturador_rol_real,
        f.vendedor AS vendedor_id,
        uv.name AS vendedor_nombre,
        cl.cliente_categoria_escala_id,
        tf.codigo AS tipo_factura_codigo
    FROM aplicacion_pagos ap
    INNER JOIN factura f ON f.id = ap.factura_id
    INNER JOIN users uf ON uf.id = f.users_id
    INNER JOIN users uv ON uv.id = f.vendedor
    LEFT JOIN cliente cl ON cl.id = f.cliente_id
    LEFT JOIN tipo_factura tf ON tf.id = f.tipo_factura_id
    WHERE ap.estado = 1
      AND ap.estado_cerrado = 2
      AND ap.fecha_cierre_factura IS NOT NULL
      AND DATE(ap.fecha_cierre_factura) BETWEEN @fechaInicio AND @fechaFin
),
categorias_bajas_sr AS (
    SELECT
        cp.cliente_categoria_escala_id,
        cp.id AS categoria_precios_id
    FROM categoria_precios cp
    WHERE cp.estado_id = 1
      AND cp.id = (
          SELECT cp2.id
          FROM categoria_precios cp2
          WHERE cp2.cliente_categoria_escala_id = cp.cliente_categoria_escala_id
            AND cp2.estado_id = 1
          ORDER BY
              CASE WHEN cp2.porc_precio_a IS NULL THEN 1 ELSE 0 END ASC,
              cp2.porc_precio_a ASC,
              cp2.id ASC
          LIMIT 1
      )
),
targets AS (
    -- FACTURADOR FIJO (rol 3)
    SELECT
        fp.aplicacion_pagos_id,
        fp.factura_id,
        fp.fecha_cierre_pago,
        fp.periodo_pago,
        fp.cai,
        fp.total,
        fp.sub_total,
        fp.tipo_pago_id,
        fp.fecha_emision,
        fp.fecha_vencimiento,
        fp.tipo_factura_codigo,
        fp.cliente_categoria_escala_id,
        'FACTURADOR_FIJO' AS capacidad,
        fp.facturador_id AS empleado_id,
        fp.facturador_nombre AS empleado,
        3 AS rol_id,
        1 AS prioridad
    FROM facturas_pagadas fp
    WHERE NOT EXISTS (
        SELECT 1
        FROM comision_rol_config crc
        WHERE crc.rol_id = 3
          AND crc.calcular = 0
    )

    UNION ALL

    -- FACTURADOR EN ROL REAL, solo si su rol real no es 3 ni 2
    SELECT
        fp.aplicacion_pagos_id,
        fp.factura_id,
        fp.fecha_cierre_pago,
        fp.periodo_pago,
        fp.cai,
        fp.total,
        fp.sub_total,
        fp.tipo_pago_id,
        fp.fecha_emision,
        fp.fecha_vencimiento,
        fp.tipo_factura_codigo,
        fp.cliente_categoria_escala_id,
        'FACTURADOR_ROL_REAL' AS capacidad,
        fp.facturador_id AS empleado_id,
        fp.facturador_nombre AS empleado,
        fp.facturador_rol_real AS rol_id,
        2 AS prioridad
    FROM facturas_pagadas fp
    WHERE fp.facturador_rol_real NOT IN (2, 3)
      AND NOT EXISTS (
          SELECT 1
          FROM comision_rol_config crc
          WHERE crc.rol_id = fp.facturador_rol_real
            AND crc.calcular = 0
      )

    UNION ALL

    -- VENDEDOR FIJO (rol 2)
    SELECT
        fp.aplicacion_pagos_id,
        fp.factura_id,
        fp.fecha_cierre_pago,
        fp.periodo_pago,
        fp.cai,
        fp.total,
        fp.sub_total,
        fp.tipo_pago_id,
        fp.fecha_emision,
        fp.fecha_vencimiento,
        fp.tipo_factura_codigo,
        fp.cliente_categoria_escala_id,
        'VENDEDOR' AS capacidad,
        fp.vendedor_id AS empleado_id,
        fp.vendedor_nombre AS empleado,
        2 AS rol_id,
        3 AS prioridad
    FROM facturas_pagadas fp
    WHERE NOT EXISTS (
        SELECT 1
        FROM comision_rol_config crc
        WHERE crc.rol_id = 2
          AND crc.calcular = 0
    )
),
lineas_proyectadas AS (
    SELECT
        t.periodo_pago,
        t.fecha_cierre_pago,
        t.factura_id,
        t.aplicacion_pagos_id,
        t.cai,
        t.total,
        t.sub_total,
        t.tipo_pago_id,
        t.fecha_emision,
        t.fecha_vencimiento,
        t.tipo_factura_codigo,
        t.capacidad,
        t.empleado_id,
        t.empleado,
        t.rol_id,
        r.nombre AS rol_nombre,
        vp.producto_id,
        vp.cantidad,
        vp.precio_unidad,
        ppc.categoria_precios_id AS categoria_vendida_id,
        CASE
            WHEN t.tipo_factura_codigo IN ('sin_restriccion_gobierno', 'sin_restriccion_precio')
                THEN cbs.categoria_precios_id
            ELSE ppc.categoria_precios_id
        END AS categoria_para_comision_id,
        ce.porcentaje_comision,
        ROUND((vp.precio_unidad * vp.cantidad) * (ce.porcentaje_comision / 100), 4) AS monto_linea_comision
    FROM targets t
    INNER JOIN venta_has_producto vp ON vp.factura_id = t.factura_id
    INNER JOIN precios_producto_carga ppc ON ppc.id = vp.precios_producto_carga_id
    LEFT JOIN categorias_bajas_sr cbs
        ON cbs.cliente_categoria_escala_id = t.cliente_categoria_escala_id
    INNER JOIN comision_escala ce
        ON ce.estado_id = 1
       AND ce.rol_id = t.rol_id
       AND ce.categoria_precios_id = CASE
            WHEN t.tipo_factura_codigo IN ('sin_restriccion_gobierno', 'sin_restriccion_precio')
                THEN cbs.categoria_precios_id
            ELSE ppc.categoria_precios_id
       END
    LEFT JOIN rol r ON r.id = t.rol_id
),
factura_rol_proyectado AS (
    SELECT
        lp.periodo_pago,
        lp.fecha_cierre_pago,
        lp.factura_id,
        lp.aplicacion_pagos_id,
        lp.cai,
        lp.total,
        lp.sub_total,
        lp.tipo_pago_id,
        lp.fecha_emision,
        lp.fecha_vencimiento,
        lp.tipo_factura_codigo,
        lp.capacidad,
        lp.empleado_id,
        lp.empleado,
        lp.rol_id,
        lp.rol_nombre,
        ROUND(SUM(lp.monto_linea_comision), 4) AS comision_bruta_proyectada,
        GROUP_CONCAT(DISTINCT lp.categoria_para_comision_id ORDER BY lp.categoria_para_comision_id SEPARATOR ', ') AS categorias_para_comision,
        GROUP_CONCAT(DISTINCT CONCAT(ROUND(lp.porcentaje_comision, 2), '%') ORDER BY lp.porcentaje_comision SEPARATOR ', ') AS porcentajes_aplicados
    FROM lineas_proyectadas lp
    GROUP BY
        lp.periodo_pago,
        lp.fecha_cierre_pago,
        lp.factura_id,
        lp.aplicacion_pagos_id,
        lp.cai,
        lp.total,
        lp.sub_total,
        lp.tipo_pago_id,
        lp.fecha_emision,
        lp.fecha_vencimiento,
        lp.tipo_factura_codigo,
        lp.capacidad,
        lp.empleado_id,
        lp.empleado,
        lp.rol_id,
        lp.rol_nombre
)
SELECT
    frp.periodo_pago,
    frp.fecha_cierre_pago,
    frp.factura_id,
    frp.aplicacion_pagos_id,
    frp.cai,
    frp.total AS total_factura,
    frp.sub_total AS subtotal_factura,
    frp.tipo_pago_id,
    CASE WHEN frp.tipo_pago_id = 1 THEN 'contado' ELSE 'credito' END AS tipo_pago,
    frp.fecha_emision,
    frp.fecha_vencimiento,
    frp.tipo_factura_codigo,
    frp.capacidad,
    frp.empleado_id,
    frp.empleado,
    frp.rol_id,
    frp.rol_nombre,
    frp.categorias_para_comision,
    frp.porcentajes_aplicados,
    frp.comision_bruta_proyectada,
    CASE
        WHEN frp.tipo_pago_id = 1 THEN DATEDIFF(frp.fecha_cierre_pago, frp.fecha_emision)
        ELSE DATEDIFF(frp.fecha_cierre_pago, frp.fecha_vencimiento)
    END AS dias_transcurridos,
    dgc.dias_gracia,
    dgc.porcentaje_retencion AS porcentaje_retencion_mora,
    CASE
        WHEN dgc.rol_id IS NULL THEN 0
        WHEN (CASE WHEN frp.tipo_pago_id = 1 THEN DATEDIFF(frp.fecha_cierre_pago, frp.fecha_emision)
                   ELSE DATEDIFF(frp.fecha_cierre_pago, frp.fecha_vencimiento)
              END) <= 0 THEN 0
        WHEN frp.tipo_pago_id = 1
             AND (CASE WHEN frp.tipo_pago_id = 1 THEN DATEDIFF(frp.fecha_cierre_pago, frp.fecha_emision)
                       ELSE DATEDIFF(frp.fecha_cierre_pago, frp.fecha_vencimiento)
                  END) > dgc.dias_gracia
            THEN frp.comision_bruta_proyectada
        WHEN frp.tipo_pago_id <> 1
             AND dgc.dias_gracia > 0
            THEN ROUND(
                ROUND(frp.comision_bruta_proyectada * (dgc.porcentaje_retencion / 100), 4)
                * FLOOR(
                    (CASE WHEN frp.tipo_pago_id = 1 THEN DATEDIFF(frp.fecha_cierre_pago, frp.fecha_emision)
                          ELSE DATEDIFF(frp.fecha_cierre_pago, frp.fecha_vencimiento)
                     END) / dgc.dias_gracia
                ),
                4
            )
        ELSE 0
    END AS retencion_mora_proyectada,
    CASE
        WHEN dgc.rol_id IS NULL THEN frp.comision_bruta_proyectada
        WHEN (CASE WHEN frp.tipo_pago_id = 1 THEN DATEDIFF(frp.fecha_cierre_pago, frp.fecha_emision)
                   ELSE DATEDIFF(frp.fecha_cierre_pago, frp.fecha_vencimiento)
              END) <= 0 THEN frp.comision_bruta_proyectada
        WHEN frp.tipo_pago_id = 1
             AND (CASE WHEN frp.tipo_pago_id = 1 THEN DATEDIFF(frp.fecha_cierre_pago, frp.fecha_emision)
                       ELSE DATEDIFF(frp.fecha_cierre_pago, frp.fecha_vencimiento)
                  END) > dgc.dias_gracia
            THEN 0
        WHEN frp.tipo_pago_id <> 1
             AND dgc.dias_gracia > 0
            THEN GREATEST(
                0,
                ROUND(
                    frp.comision_bruta_proyectada - ROUND(
                        ROUND(frp.comision_bruta_proyectada * (dgc.porcentaje_retencion / 100), 4)
                        * FLOOR(
                            (CASE WHEN frp.tipo_pago_id = 1 THEN DATEDIFF(frp.fecha_cierre_pago, frp.fecha_emision)
                                  ELSE DATEDIFF(frp.fecha_cierre_pago, frp.fecha_vencimiento)
                             END) / dgc.dias_gracia
                        ),
                        4
                    ),
                    4
                )
            )
        ELSE frp.comision_bruta_proyectada
    END AS comision_neta_mora_proyectada
FROM factura_rol_proyectado frp
LEFT JOIN dias_gracia_comision dgc
    ON dgc.rol_id = frp.rol_id
   AND dgc.tipo_factura = CASE WHEN frp.tipo_pago_id = 1 THEN 'contado' ELSE 'credito' END
   AND dgc.dias_gracia > 0
;

-- ============================================================================
-- 1) RESUMEN POR EMPLEADO / CAPACIDAD / ROL / MES DE PAGO
-- ============================================================================
SELECT
    periodo_pago,
    capacidad,
    rol_id,
    rol_nombre,
    empleado_id,
    empleado,
    COUNT(DISTINCT factura_id) AS facturas_pagadas,
    ROUND(SUM(total_factura), 2) AS total_facturado,
    ROUND(SUM(comision_bruta_proyectada), 2) AS comision_bruta_proyectada,
    ROUND(SUM(retencion_mora_proyectada), 2) AS retencion_mora_proyectada,
    ROUND(SUM(comision_neta_mora_proyectada), 2) AS comision_neta_mora_proyectada
FROM tmp_proyeccion_comisiones_pagadas
GROUP BY
    periodo_pago,
    capacidad,
    rol_id,
    rol_nombre,
    empleado_id,
    empleado
ORDER BY periodo_pago, capacidad, empleado;

-- ============================================================================
-- 2) RESUMEN SOLO POR ROL Y MES DE PAGO
-- ============================================================================
SELECT
    periodo_pago,
    rol_id,
    rol_nombre,
    COUNT(DISTINCT factura_id) AS facturas_pagadas,
    ROUND(SUM(comision_bruta_proyectada), 2) AS comision_bruta_proyectada,
    ROUND(SUM(retencion_mora_proyectada), 2) AS retencion_mora_proyectada,
    ROUND(SUM(comision_neta_mora_proyectada), 2) AS comision_neta_mora_proyectada
FROM tmp_proyeccion_comisiones_pagadas
GROUP BY periodo_pago, rol_id, rol_nombre
ORDER BY periodo_pago, rol_id;

-- ============================================================================
-- 3) DETALLE POR FACTURA PARA AUDITORIA
-- ============================================================================
SELECT
    periodo_pago,
    fecha_cierre_pago,
    factura_id,
    aplicacion_pagos_id,
    cai,
    tipo_pago,
    tipo_factura_codigo,
    capacidad,
    empleado_id,
    empleado,
    rol_id,
    rol_nombre,
    categorias_para_comision,
    porcentajes_aplicados,
    total_factura,
    subtotal_factura,
    dias_transcurridos,
    dias_gracia,
    porcentaje_retencion_mora,
    comision_bruta_proyectada,
    retencion_mora_proyectada,
    comision_neta_mora_proyectada
FROM tmp_proyeccion_comisiones_pagadas
ORDER BY periodo_pago, fecha_cierre_pago, factura_id, rol_id, empleado;

-- ============================================================================
-- 4) CHEQUEO DE FACTURAS SR (para revisar posibles diferencias por escala actual)
-- ============================================================================
SELECT
    periodo_pago,
    factura_id,
    cai,
    tipo_factura_codigo,
    empleado,
    rol_nombre,
    categorias_para_comision,
    porcentajes_aplicados,
    comision_bruta_proyectada,
    comision_neta_mora_proyectada
FROM tmp_proyeccion_comisiones_pagadas
WHERE tipo_factura_codigo IN ('sin_restriccion_gobierno', 'sin_restriccion_precio')
ORDER BY periodo_pago, factura_id, rol_id;
