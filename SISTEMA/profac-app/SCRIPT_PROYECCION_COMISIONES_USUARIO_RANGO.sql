/*
 PROYECCION DE COMISIONES POR USUARIO EN FACTURAS CERRADAS
 Reglas alineadas al motor actual:
 - Cierre por aplicacion_pagos.estado_cerrado = 1 y fecha_cierre_factura en rango.
 - Roles/capacidades: facturador fijo (rol 3), facturador rol real, vendedor (rol 2), gestor (rol 16).
 - Dedupe por rol en la factura (misma prioridad que el generador).
 - Respeta comision_rol_config.calcular (si no existe fila en config, se asume habilitado).
 - Escala por cliente_categoria_escala_id + categoria_precios_id.
 - SR usa categoria de precio mas baja de la escala del cliente.
 - Base comisionable usa precioSeleccionado; fallback a precio_unidad.

 IMPORTANTE:
 - Ajusta @target_user_id segun el usuario real que quieras proyectar.
 - En tu imagen aparecen:
   id 86 = Jose Luque (activo)
   id 95 = Facturador Jose Luque (estado 2)
*/

SET @fecha_inicio   = '2026-05-11';
SET @fecha_fin      = '2026-06-25';
SET @target_user_id = 86;

DROP TEMPORARY TABLE IF EXISTS tmp_proyeccion_comision;

CREATE TEMPORARY TABLE tmp_proyeccion_comision AS
WITH
cat_baja AS (
    SELECT cliente_categoria_escala_id, categoria_id
    FROM (
        SELECT
            cp.cliente_categoria_escala_id,
            cp.id AS categoria_id,
            ROW_NUMBER() OVER (
                PARTITION BY cp.cliente_categoria_escala_id
                ORDER BY
                    CASE WHEN cp.porc_precio_a IS NULL THEN 1 ELSE 0 END,
                    cp.porc_precio_a,
                    cp.id
            ) AS rn
        FROM categoria_precios cp
        WHERE cp.estado_id = 1
    ) x
    WHERE x.rn = 1
),
base_facturas AS (
    SELECT
        f.id AS factura_id,
        f.cai,
        DATE(ap.fecha_cierre_factura) AS fecha_cierre,
        ap.id AS aplicacion_pagos_id,
        f.users_id AS facturador_id,
        COALESCE(uf.rol_id, 0) AS facturador_rol_real,
        f.vendedor AS vendedor_id,
        f.gestor_entrega AS gestor_id,
        cl.id AS cliente_id,
        cl.nombre AS cliente,
        COALESCE(cl.cliente_categoria_escala_id, 0) AS cliente_categoria_escala_id,
        tf.codigo AS tipo_factura_codigo,
        CASE
            WHEN tf.codigo IN ('sin_restriccion_gobierno', 'sin_restriccion_precio') THEN 1
            ELSE 0
        END AS es_sr,
        cb.categoria_id AS categoria_precio_forzada
    FROM factura f
    INNER JOIN aplicacion_pagos ap
        ON ap.factura_id = f.id
           AND ap.estado_cerrado = 2
       AND DATE(ap.fecha_cierre_factura) BETWEEN @fecha_inicio AND @fecha_fin
       AND COALESCE(ap.estado, 1) = 1
    LEFT JOIN users uf
        ON uf.id = f.users_id
    LEFT JOIN cliente cl
        ON cl.id = f.cliente_id
    LEFT JOIN tipo_factura tf
        ON tf.id = f.tipo_factura_id
    LEFT JOIN cat_baja cb
        ON cb.cliente_categoria_escala_id = cl.cliente_categoria_escala_id
    WHERE COALESCE(f.estado_venta_id, 0) <> 2
),
targets_raw AS (
    /* Tipo 1: Facturador con rol fijo 3 */
    SELECT
        bf.factura_id,
        bf.cai,
        bf.fecha_cierre,
        bf.aplicacion_pagos_id,
        bf.cliente_id,
        bf.cliente,
        bf.cliente_categoria_escala_id,
        bf.es_sr,
        bf.categoria_precio_forzada,
        1 AS tipo_comision,
        3 AS rol_id,
        bf.facturador_id AS target_user_id,
        1 AS prioridad
    FROM base_facturas bf
    WHERE bf.facturador_id = @target_user_id

    UNION ALL

    /* Tipo 2: Facturador en rol real (si rol real no es 3 ni 2) */
    SELECT
        bf.factura_id,
        bf.cai,
        bf.fecha_cierre,
        bf.aplicacion_pagos_id,
        bf.cliente_id,
        bf.cliente,
        bf.cliente_categoria_escala_id,
        bf.es_sr,
        bf.categoria_precio_forzada,
        2 AS tipo_comision,
        bf.facturador_rol_real AS rol_id,
        bf.facturador_id AS target_user_id,
        2 AS prioridad
    FROM base_facturas bf
    WHERE bf.facturador_id = @target_user_id
      AND bf.facturador_rol_real NOT IN (0, 2, 3)

    UNION ALL

    /* Tipo 3: Vendedor con rol fijo 2 */
    SELECT
        bf.factura_id,
        bf.cai,
        bf.fecha_cierre,
        bf.aplicacion_pagos_id,
        bf.cliente_id,
        bf.cliente,
        bf.cliente_categoria_escala_id,
        bf.es_sr,
        bf.categoria_precio_forzada,
        3 AS tipo_comision,
        2 AS rol_id,
        bf.vendedor_id AS target_user_id,
        3 AS prioridad
    FROM base_facturas bf
    WHERE bf.vendedor_id = @target_user_id

    UNION ALL

    /* Tipo 4: Gestor entrega con rol fijo 16 */
    SELECT
        bf.factura_id,
        bf.cai,
        bf.fecha_cierre,
        bf.aplicacion_pagos_id,
        bf.cliente_id,
        bf.cliente,
        bf.cliente_categoria_escala_id,
        bf.es_sr,
        bf.categoria_precio_forzada,
        4 AS tipo_comision,
        16 AS rol_id,
        bf.gestor_id AS target_user_id,
        4 AS prioridad
    FROM base_facturas bf
    WHERE bf.gestor_id = @target_user_id
),
targets_habilitados AS (
    SELECT
        tr.*,
        COALESCE(crc.calcular, 1) AS calcular_flag
    FROM targets_raw tr
    LEFT JOIN comision_rol_config crc
        ON crc.rol_id = tr.rol_id
),
targets_dedup AS (
    SELECT *
    FROM (
        SELECT
            th.*,
            ROW_NUMBER() OVER (
                PARTITION BY th.factura_id, th.rol_id
                ORDER BY th.prioridad DESC
            ) AS rn
        FROM targets_habilitados th
        WHERE th.calcular_flag = 1
    ) z
    WHERE z.rn = 1
),
lineas AS (
    SELECT
        vp.factura_id,
        vp.producto_id,
        vp.precios_producto_carga_id,
        vp.cantidad,
        vp.precio_unidad,
        vp.precioSeleccionado,
        COALESCE(NULLIF(vp.precioSeleccionado, 0), vp.precio_unidad) AS precio_para_comision,
        ppc.categoria_precios_id,
        p.nombre AS producto
    FROM venta_has_producto vp
    LEFT JOIN precios_producto_carga ppc
        ON ppc.id = vp.precios_producto_carga_id
    LEFT JOIN producto p
        ON p.id = vp.producto_id
)
SELECT
    td.factura_id,
    td.cai,
    td.fecha_cierre,
    td.aplicacion_pagos_id,
    td.cliente_id,
    td.cliente,
    td.target_user_id AS user_id,
    u.name AS usuario,
    td.tipo_comision,
    CASE td.tipo_comision
        WHEN 1 THEN 'Facturador (rol fijo)'
        WHEN 2 THEN 'Facturador (rol real)'
        WHEN 3 THEN 'Vendedor'
        WHEN 4 THEN 'Gestor entrega'
        ELSE 'Otro'
    END AS capacidad,
    td.rol_id,
    r.nombre AS rol_nombre,
    ln.producto_id,
    ln.producto,
    ln.precios_producto_carga_id,
    ln.categoria_precios_id,
    ln.cantidad,
    ln.precio_unidad,
    ln.precioSeleccionado,
    ln.precio_para_comision,
    ce.porcentaje_comision,
    ROUND(ln.cantidad * ln.precio_para_comision, 4) AS base_comisionable,
    ROUND((ce.porcentaje_comision / 100) * ln.precio_para_comision * ln.cantidad, 4) AS monto_comision_linea
FROM targets_dedup td
INNER JOIN lineas ln
    ON ln.factura_id = td.factura_id
INNER JOIN comision_escala ce
    ON ce.rol_id = td.rol_id
   AND ce.estado_id = 1
   AND ce.cliente_categoria_escala_id = td.cliente_categoria_escala_id
   AND ce.categoria_precios_id = CASE
        WHEN td.es_sr = 1 THEN td.categoria_precio_forzada
        ELSE ln.categoria_precios_id
   END
LEFT JOIN users u
    ON u.id = td.target_user_id
LEFT JOIN rol r
    ON r.id = td.rol_id;

/* 1) DETALLE COMPLETO POR LINEA */
SELECT
    factura_id,
    cai,
    fecha_cierre,
    cliente,
    user_id,
    usuario,
    capacidad,
    rol_id,
    rol_nombre,
    producto_id,
    producto,
    cantidad,
    precio_unidad,
    precioSeleccionado,
    precio_para_comision,
    porcentaje_comision,
    base_comisionable,
    monto_comision_linea
FROM tmp_proyeccion_comision
ORDER BY fecha_cierre, factura_id, capacidad, producto;

/* 2) RESUMEN POR FACTURA Y CAPACIDAD */
SELECT
    factura_id,
    cai,
    fecha_cierre,
    cliente,
    user_id,
    usuario,
    capacidad,
    rol_id,
    rol_nombre,
    ROUND(SUM(base_comisionable), 4) AS base_comisionable_total,
    ROUND(SUM(monto_comision_linea), 4) AS comision_proyectada
FROM tmp_proyeccion_comision
GROUP BY
    factura_id,
    cai,
    fecha_cierre,
    cliente,
    user_id,
    usuario,
    capacidad,
    rol_id,
    rol_nombre
ORDER BY fecha_cierre, factura_id, capacidad;

/* 3) RESUMEN GENERAL DEL PERIODO */
SELECT
    user_id,
    usuario,
    capacidad,
    rol_id,
    rol_nombre,
    ROUND(SUM(base_comisionable), 4) AS base_comisionable_total,
    ROUND(SUM(monto_comision_linea), 4) AS comision_proyectada
FROM tmp_proyeccion_comision
GROUP BY user_id, usuario, capacidad, rol_id, rol_nombre
ORDER BY rol_id, capacidad;

/* 4) TOTAL GLOBAL */
SELECT
    user_id,
    usuario,
    ROUND(SUM(monto_comision_linea), 4) AS comision_total_proyectada_periodo
FROM tmp_proyeccion_comision
GROUP BY user_id, usuario;
