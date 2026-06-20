-- ============================================================================
-- PARTE 2: FINALIZACION Y RESULTADOS (PASOS 9-13 + OUTPUTS)
-- Ejecuta DESPUES de SCRIPT_PARTE_1_SETUP.sql
-- ============================================================================

-- PASO 9: Configuracion de gracia por rol y tipo factura (tabla vacía)
DROP TEMPORARY TABLE IF EXISTS tmp_configs_gracia;
CREATE TEMPORARY TABLE tmp_configs_gracia (
    rol_id INT,
    tipo_factura VARCHAR(20),
    dias_gracia INT,
    porcentaje_retencion DECIMAL(5,2)
);

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

SELECT '✓ PROYECCIÓN COMPLETADA EXITOSAMENTE' AS resultado;
