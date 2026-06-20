-- ============================================================================
-- SCRIPT COMPLETO PROYECCION COMISIONES ABRIL-MAYO 2026
-- VENDEDOR (role_id=2) + FACTURADOR (role_id=3)
-- Ejecutar de una sola vez - TODAS LAS PARTES JUNTAS
-- ============================================================================

SET @fechaInicio = '2026-01-01';
SET @fechaFin    = '2026-06-30';

-- ============================================================================
-- PARTE 1: CONSTRUCCION DE TABLAS INTERMEDIAS (PASOS 1-8)
-- ============================================================================

-- PASO 1: Cierres y pagos
DROP TEMPORARY TABLE IF EXISTS tmp_cierres_pago;
CREATE TEMPORARY TABLE tmp_cierres_pago AS
SELECT
    ap.id AS aplicacion_pagos_id,
    ap.factura_id,
    COALESCE(
        DATE(ap.fecha_cierre_factura),
        MAX(DATE(COALESCE(ac.fecha_pago, ac.created_at)))
    ) AS fecha_pago_cierre
FROM aplicacion_pagos ap
LEFT JOIN abonos_creditos ac
    ON ac.aplicacion_pagos_id = ap.id
   AND ac.estado_abono = 1
WHERE ap.estado = 1
  AND ap.estado_cerrado = 2
GROUP BY ap.id, ap.factura_id, ap.fecha_cierre_factura
HAVING fecha_pago_cierre BETWEEN @fechaInicio AND @fechaFin;

-- PASO 2: Facturas base con datos y SR
DROP TEMPORARY TABLE IF EXISTS tmp_facturas_base;
CREATE TEMPORARY TABLE tmp_facturas_base AS
SELECT
    cp.aplicacion_pagos_id,
    cp.factura_id,
    cp.fecha_pago_cierre,
    f.cai,
    f.users_id AS facturador_id,
    uf.name AS facturador_nombre,
    f.vendedor AS vendedor_id,
    uv.name AS vendedor_nombre,
    f.tipo_pago_id,
    DATE(f.fecha_emision) AS fecha_emision,
    DATE(f.fecha_vencimiento) AS fecha_vencimiento,
    COALESCE(f.sub_total, 0) AS sub_total,
    tf.codigo AS tipo_factura_codigo,
    cl.cliente_categoria_escala_id,
    CASE
        WHEN tf.codigo IN ('sin_restriccion_gobierno', 'sin_restriccion_precio') THEN 1
        ELSE 0
    END AS es_sr,
    (
        SELECT cp2.id
        FROM categoria_precios cp2
        WHERE cp2.cliente_categoria_escala_id = cl.cliente_categoria_escala_id
          AND cp2.estado_id = 1
        ORDER BY CASE WHEN cp2.porc_precio_a IS NULL THEN 1 ELSE 0 END ASC,
                 cp2.porc_precio_a ASC,
                 cp2.id ASC
        LIMIT 1
    ) AS categoria_precio_forzada_id
FROM tmp_cierres_pago cp
INNER JOIN factura f ON f.id = cp.factura_id
INNER JOIN users uf ON uf.id = f.users_id
INNER JOIN users uv ON uv.id = f.vendedor
LEFT JOIN tipo_factura tf ON tf.id = f.tipo_factura_id
LEFT JOIN cliente cl ON cl.id = f.cliente_id;

-- PASO 3: Targets (facturador y vendedor)
DROP TEMPORARY TABLE IF EXISTS tmp_targets;
CREATE TEMPORARY TABLE tmp_targets (
    aplicacion_pagos_id BIGINT,
    factura_id BIGINT,
    fecha_pago_cierre DATE,
    cai VARCHAR(100),
    capacidad VARCHAR(20),
    user_id BIGINT,
    empleado VARCHAR(255),
    rol_id INT,
    tipo_pago_id INT,
    fecha_emision DATE,
    fecha_vencimiento DATE,
    sub_total DECIMAL(18,4),
    tipo_factura_codigo VARCHAR(100),
    es_sr TINYINT,
    categoria_precio_forzada_id BIGINT,
    INDEX idx_factura (factura_id)
);

INSERT INTO tmp_targets
SELECT
    fb.aplicacion_pagos_id,
    fb.factura_id,
    fb.fecha_pago_cierre,
    fb.cai,
    'FACTURADOR' AS capacidad,
    fb.facturador_id AS user_id,
    fb.facturador_nombre AS empleado,
    3 AS rol_id,
    fb.tipo_pago_id,
    fb.fecha_emision,
    fb.fecha_vencimiento,
    fb.sub_total,
    fb.tipo_factura_codigo,
    fb.es_sr,
    fb.categoria_precio_forzada_id
FROM tmp_facturas_base fb;

INSERT INTO tmp_targets
SELECT
    fb.aplicacion_pagos_id,
    fb.factura_id,
    fb.fecha_pago_cierre,
    fb.cai,
    'VENDEDOR' AS capacidad,
    fb.vendedor_id AS user_id,
    fb.vendedor_nombre AS empleado,
    2 AS rol_id,
    fb.tipo_pago_id,
    fb.fecha_emision,
    fb.fecha_vencimiento,
    fb.sub_total,
    fb.tipo_factura_codigo,
    fb.es_sr,
    fb.categoria_precio_forzada_id
FROM tmp_facturas_base fb;

-- PASO 4: Targets filtrados
DROP TEMPORARY TABLE IF EXISTS tmp_targets_filtrados;
CREATE TEMPORARY TABLE tmp_targets_filtrados AS
SELECT t.*
FROM tmp_targets t
LEFT JOIN comision_rol_config crc
    ON crc.rol_id = t.rol_id
   AND crc.calcular = 0
WHERE crc.rol_id IS NULL;

-- PASO 5: Escalas actuales por rol y categoria
DROP TEMPORARY TABLE IF EXISTS tmp_escalas_actuales;
CREATE TEMPORARY TABLE tmp_escalas_actuales AS
SELECT
    ce.rol_id,
    ce.categoria_precios_id,
    ce.porcentaje_comision
FROM comision_escala ce
INNER JOIN (
    SELECT
        rol_id,
        categoria_precios_id,
        MAX(id) AS max_id
    FROM comision_escala
    WHERE estado_id = 1
      AND categoria_precios_id IS NOT NULL
      AND rol_id IN (2, 3)
    GROUP BY rol_id, categoria_precios_id
) ult
    ON ult.max_id = ce.id;

-- PASO 6: Lineas base con categoria de comision
DROP TEMPORARY TABLE IF EXISTS tmp_lineas_base;
CREATE TEMPORARY TABLE tmp_lineas_base AS
SELECT
    tf.aplicacion_pagos_id,
    tf.factura_id,
    tf.fecha_pago_cierre,
    tf.cai,
    tf.capacidad,
    tf.user_id,
    tf.empleado,
    tf.rol_id,
    tf.tipo_pago_id,
    tf.fecha_emision,
    tf.fecha_vencimiento,
    tf.sub_total,
    tf.tipo_factura_codigo,
    tf.es_sr,
    vp.producto_id,
    vp.cantidad,
    vp.precio_unidad,
    ppc.categoria_precios_id AS categoria_vendida_id,
    CASE
        WHEN tf.es_sr = 1 AND tf.categoria_precio_forzada_id IS NOT NULL
            THEN tf.categoria_precio_forzada_id
        ELSE ppc.categoria_precios_id
    END AS categoria_comision_id
FROM tmp_targets_filtrados tf
INNER JOIN venta_has_producto vp ON vp.factura_id = tf.factura_id
INNER JOIN precios_producto_carga ppc ON ppc.id = vp.precios_producto_carga_id;

-- PASO 7: Lineas comisionadas
DROP TEMPORARY TABLE IF EXISTS tmp_lineas_comisionadas;
CREATE TEMPORARY TABLE tmp_lineas_comisionadas AS
SELECT
    lb.*,
    ea.porcentaje_comision,
    cp.nombre AS categoria_comision_nombre,
    ROUND(lb.precio_unidad * lb.cantidad * (ea.porcentaje_comision / 100), 4) AS monto_linea_comision
FROM tmp_lineas_base lb
INNER JOIN tmp_escalas_actuales ea
    ON ea.rol_id = lb.rol_id
   AND ea.categoria_precios_id = lb.categoria_comision_id
LEFT JOIN categoria_precios cp ON cp.id = lb.categoria_comision_id;

-- PASO 8: Totales por factura y rol
DROP TEMPORARY TABLE IF EXISTS tmp_totales_factura_rol;
CREATE TEMPORARY TABLE tmp_totales_factura_rol AS
SELECT
    DATE_FORMAT(lc.fecha_pago_cierre, '%Y-%m') AS periodo_pago,
    lc.fecha_pago_cierre,
    lc.factura_id,
    lc.aplicacion_pagos_id,
    lc.cai,
    lc.capacidad,
    lc.user_id,
    lc.empleado,
    lc.rol_id,
    r.nombre AS rol_nombre,
    lc.tipo_pago_id,
    lc.fecha_emision,
    lc.fecha_vencimiento,
    lc.sub_total,
    lc.tipo_factura_codigo,
    lc.es_sr,
    ROUND(SUM(lc.monto_linea_comision), 4) AS comision_bruta,
    GROUP_CONCAT(DISTINCT lc.categoria_comision_nombre ORDER BY lc.categoria_comision_nombre SEPARATOR ', ') AS categorias_comision
FROM tmp_lineas_comisionadas lc
LEFT JOIN rol r ON r.id = lc.rol_id
GROUP BY
    DATE_FORMAT(lc.fecha_pago_cierre, '%Y-%m'),
    lc.fecha_pago_cierre,
    lc.factura_id,
    lc.aplicacion_pagos_id,
    lc.cai,
    lc.capacidad,
    lc.user_id,
    lc.empleado,
    lc.rol_id,
    r.nombre,
    lc.tipo_pago_id,
    lc.fecha_emision,
    lc.fecha_vencimiento,
    lc.sub_total,
    lc.tipo_factura_codigo,
    lc.es_sr;

-- ============================================================================
-- PARTE 2: FINALIZACION Y RESULTADOS (PASOS 9-13 + OUTPUTS)
-- ============================================================================

-- PASO 9: Configuracion de gracia por rol y tipo factura (tabla vacía)
DROP TEMPORARY TABLE IF EXISTS tmp_configs_gracia;
CREATE TEMPORARY TABLE tmp_configs_gracia (
    rol_id INT,
    tipo_factura VARCHAR(20),
    dias_gracia INT,
    porcentaje_retencion DECIMAL(5,2)
) ENGINE=MEMORY;

-- PASO 10: Detalle con mora base
DROP TEMPORARY TABLE IF EXISTS tmp_detalle_mora_base;
CREATE TEMPORARY TABLE tmp_detalle_mora_base AS
SELECT
    tfr.*,
    CASE WHEN tfr.tipo_pago_id = 1 THEN 'contado' ELSE 'credito' END AS tipo_factura_pago,
    CASE
        WHEN tfr.tipo_pago_id = 1 THEN DATEDIFF(tfr.fecha_pago_cierre, tfr.fecha_emision)
        ELSE DATEDIFF(tfr.fecha_pago_cierre, tfr.fecha_vencimiento)
    END AS dias_transcurridos,
    COALESCE(cg.dias_gracia, 0) AS dias_gracia,
    COALESCE(cg.porcentaje_retencion, 0) AS porcentaje_retencion
FROM tmp_totales_factura_rol tfr
LEFT JOIN tmp_configs_gracia cg
    ON cg.rol_id = tfr.rol_id
   AND cg.tipo_factura = CASE WHEN tfr.tipo_pago_id = 1 THEN 'contado' ELSE 'credito' END;

-- PASO 11: Detalle final con retencion mora calculada
DROP TEMPORARY TABLE IF EXISTS tmp_detalle_final;
CREATE TEMPORARY TABLE tmp_detalle_final AS
SELECT
    dmb.*,
    CASE
        WHEN dmb.dias_gracia IS NULL OR dmb.dias_gracia <= 0 OR dmb.dias_transcurridos <= 0 THEN 0
        WHEN dmb.tipo_pago_id = 1 AND dmb.dias_transcurridos > dmb.dias_gracia THEN 1
        WHEN dmb.tipo_pago_id <> 1 AND dmb.dias_transcurridos > dmb.dias_gracia THEN FLOOR(dmb.dias_transcurridos / dmb.dias_gracia)
        ELSE 0
    END AS periodos_vencidos,
    CASE
        WHEN dmb.dias_gracia IS NULL OR dmb.dias_gracia <= 0 OR dmb.dias_transcurridos <= 0 THEN 0.0000
        WHEN dmb.tipo_pago_id = 1 AND dmb.dias_transcurridos > dmb.dias_gracia THEN ROUND(dmb.comision_bruta, 4)
        WHEN dmb.tipo_pago_id <> 1
             AND COALESCE(dmb.porcentaje_retencion, 0) > 0
             AND FLOOR(dmb.dias_transcurridos / dmb.dias_gracia) > 0
            THEN LEAST(
                ROUND(dmb.comision_bruta, 4),
                ROUND(
                    ROUND(dmb.comision_bruta * (dmb.porcentaje_retencion / 100), 4)
                    * FLOOR(dmb.dias_transcurridos / dmb.dias_gracia),
                    4
                )
            )
        ELSE 0.0000
    END AS retencion_mora_proyectada
FROM tmp_detalle_mora_base dmb;

-- PASO 12: Tabla final proyeccion
DROP TEMPORARY TABLE IF EXISTS tmp_proyeccion_comisiones_vf;
CREATE TEMPORARY TABLE tmp_proyeccion_comisiones_vf AS
SELECT
    df.periodo_pago,
    df.fecha_pago_cierre,
    df.factura_id,
    df.aplicacion_pagos_id,
    df.cai,
    df.capacidad,
    df.user_id,
    df.empleado,
    df.rol_id,
    df.rol_nombre,
    df.tipo_factura_codigo,
    df.tipo_factura_pago,
    df.es_sr,
    df.categorias_comision,
    ROUND(df.comision_bruta, 4) AS comision_bruta,
    df.dias_transcurridos,
    df.dias_gracia,
    COALESCE(df.porcentaje_retencion, 0) AS porcentaje_retencion_mora,
    df.periodos_vencidos,
    ROUND(df.retencion_mora_proyectada, 4) AS retencion_mora_proyectada,
    ROUND(GREATEST(0, df.comision_bruta - df.retencion_mora_proyectada), 4) AS comision_neta_mora
FROM tmp_detalle_final df;

-- PASO 13: Tabla sin escala
DROP TEMPORARY TABLE IF EXISTS tmp_proyeccion_comisiones_vf_sin_escala;
CREATE TEMPORARY TABLE tmp_proyeccion_comisiones_vf_sin_escala AS
SELECT DISTINCT
    DATE_FORMAT(lb.fecha_pago_cierre, '%Y-%m') AS periodo_pago,
    lb.fecha_pago_cierre,
    lb.factura_id,
    lb.aplicacion_pagos_id,
    lb.cai,
    lb.capacidad,
    lb.user_id,
    lb.empleado,
    lb.rol_id,
    lb.tipo_factura_codigo,
    lb.es_sr,
    lb.categoria_vendida_id,
    lb.categoria_comision_id
FROM tmp_lineas_base lb
LEFT JOIN tmp_escalas_actuales ea
    ON ea.rol_id = lb.rol_id
   AND ea.categoria_precios_id = lb.categoria_comision_id
WHERE ea.rol_id IS NULL;

-- ============================================================================
-- RESULTADO 1: RESUMEN CONSOLIDADO POR MES / EMPLEADO / CAPACIDAD / ROL
-- ============================================================================
SELECT
    periodo_pago,
    capacidad,
    user_id,
    empleado,
    rol_id,
    rol_nombre,
    COUNT(DISTINCT factura_id) AS facturas_proyectadas,
    ROUND(SUM(comision_bruta), 4) AS comision_bruta_total,
    ROUND(SUM(retencion_mora_proyectada), 4) AS retencion_mora_total,
    ROUND(SUM(comision_neta_mora), 4) AS comision_neta_mora_total
FROM tmp_proyeccion_comisiones_vf
GROUP BY
    periodo_pago,
    capacidad,
    user_id,
    empleado,
    rol_id,
    rol_nombre
ORDER BY
    periodo_pago,
    capacidad,
    empleado;

-- ============================================================================
-- RESULTADO 2: DETALLE POR FACTURA (primeros 100 registros para no saturar)
-- ============================================================================
SELECT
    periodo_pago,
    fecha_pago_cierre,
    factura_id,
    aplicacion_pagos_id,
    cai,
    capacidad,
    user_id,
    empleado,
    rol_id,
    rol_nombre,
    tipo_factura_codigo,
    tipo_factura_pago,
    es_sr,
    categorias_comision,
    comision_bruta,
    dias_transcurridos,
    dias_gracia,
    porcentaje_retencion_mora,
    periodos_vencidos,
    retencion_mora_proyectada,
    comision_neta_mora
FROM tmp_proyeccion_comisiones_vf
ORDER BY
    fecha_pago_cierre,
    cai,
    capacidad,
    empleado
LIMIT 100;

-- ============================================================================
-- RESULTADO 3: FACTURAS / CAPACIDADES SIN ESCALA ACTUAL
-- ============================================================================
SELECT *
FROM tmp_proyeccion_comisiones_vf_sin_escala
ORDER BY fecha_pago_cierre, cai, capacidad, empleado;

-- ============================================================================
-- RESULTADO 4: CONTROL RAPIDO POR TIPO SR
-- ============================================================================
SELECT
    periodo_pago,
    CASE WHEN es_sr = 1 THEN 'SR' ELSE 'NORMAL' END AS tipo_regla,
    COUNT(DISTINCT factura_id) AS facturas,
    ROUND(SUM(comision_bruta), 4) AS comision_bruta,
    ROUND(SUM(comision_neta_mora), 4) AS comision_neta_mora
FROM tmp_proyeccion_comisiones_vf
GROUP BY periodo_pago, CASE WHEN es_sr = 1 THEN 'SR' ELSE 'NORMAL' END
ORDER BY periodo_pago, tipo_regla;

-- ============================================================================
-- CONFIRMACION FINAL
-- ============================================================================
SELECT '✓ PROYECCIÓN COMPLETADA EXITOSAMENTE - MAYO 2026' AS resultado;
