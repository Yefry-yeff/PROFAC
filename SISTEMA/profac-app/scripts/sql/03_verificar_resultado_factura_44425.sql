SET @cai := CONVERT('000-001-01-00044425' USING latin1) COLLATE latin1_swedish_ci;
SET @factura_id := (SELECT MIN(id) FROM factura WHERE cai = @cai);

SELECT
    f.id AS factura_id,
    f.cai,
    ap.id AS aplicacion_id,
    ap.saldo,
    ap.movimiento_resta,
    ap.estado_cerrado,
    ap.fecha_cierre_factura,
    ap.usr_cerro,
    ap.comentario
FROM factura AS f
INNER JOIN aplicacion_pagos AS ap
    ON ap.factura_id = f.id
   AND ap.estado = 1
WHERE f.id = @factura_id;

SELECT
    fc.id,
    fc.rol_id,
    fc.tipo_comision,
    fc.monto_rol AS comision_neta,
    fc.retencion_mora_monto,
    fc.retencion_mora_dias,
    COUNT(pc.id) AS detalles
FROM facturas_comision AS fc
LEFT JOIN producto_comision AS pc
    ON pc.facturas_comision_id = fc.id
   AND pc.estado_id = 1
WHERE fc.factura_id = @factura_id
  AND fc.estado_id = 1
GROUP BY
    fc.id,
    fc.rol_id,
    fc.tipo_comision,
    fc.monto_rol,
    fc.retencion_mora_monto,
    fc.retencion_mora_dias
ORDER BY fc.rol_id;

SELECT
    rml.facturas_comision_id,
    rml.rol_id,
    rml.user_id,
    rml.fecha_aplicacion,
    rml.dias_transcurridos,
    rml.dias_gracia_configurados,
    rml.porcentaje_aplicado,
    rml.comision_original,
    rml.monto_retenido
FROM retencion_mora_log AS rml
WHERE rml.factura_id = @factura_id
ORDER BY rml.rol_id;

SELECT
    CASE
        WHEN (SELECT estado_cerrado FROM aplicacion_pagos WHERE factura_id = @factura_id AND estado = 1 LIMIT 1) <> 2
            THEN 'ERROR: la aplicacion no quedo cerrada'
        WHEN (SELECT ROUND(saldo, 2) FROM aplicacion_pagos WHERE factura_id = @factura_id AND estado = 1 LIMIT 1) <> 0.00
            THEN 'ERROR: el saldo no quedo en cero'
        WHEN (
            SELECT DATE(fecha_cierre_factura)
            FROM aplicacion_pagos
            WHERE factura_id = @factura_id AND estado = 1
            LIMIT 1
        ) <> '2026-09-24'
            THEN 'ERROR: la aplicacion no tiene fecha de cierre 2026-09-24'
        WHEN (SELECT COUNT(*) FROM facturas_comision WHERE factura_id = @factura_id AND estado_id = 1) <> 3
            THEN 'ERROR: no existen exactamente tres comisiones'
        WHEN (
            SELECT COUNT(*)
            FROM facturas_comision
            WHERE factura_id = @factura_id
              AND estado_id = 1
              AND fecha_cierre_factura = '2026-09-24'
        ) <> 3
            THEN 'ERROR: las comisiones no tienen fecha 2026-09-24'
        WHEN (
            SELECT COUNT(*)
            FROM producto_comision
            WHERE factura_id = @factura_id AND estado_id = 1
        ) <> 54
            THEN 'ERROR: no existen exactamente 54 detalles de comision'
        WHEN (SELECT COUNT(*) FROM retencion_mora_log WHERE factura_id = @factura_id) <> 3
            THEN 'ERROR: no existen exactamente tres retenciones'
        WHEN (
            SELECT COUNT(*)
            FROM retencion_mora_log
            WHERE factura_id = @factura_id
              AND fecha_aplicacion = '2026-09-24'
              AND dias_transcurridos = 28
        ) <> 3
            THEN 'ERROR: las retenciones no tienen fecha 2026-09-24 y 28 dias'
        WHEN (
            SELECT COUNT(*)
            FROM facturas_comision
            WHERE factura_id = @factura_id
              AND estado_id = 1
              AND ROUND(monto_rol, 2) = 0.00
        ) <> 3
            THEN 'ERROR: las comisiones netas no quedaron en cero'
        ELSE 'VERIFICACION CORRECTA'
    END AS resultado_final;
