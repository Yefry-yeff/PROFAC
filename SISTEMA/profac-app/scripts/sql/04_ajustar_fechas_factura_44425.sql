START TRANSACTION;

SET @cai := CONVERT('000-001-01-00044425' USING latin1) COLLATE latin1_swedish_ci;
SET @fecha_cierre := '2026-09-24 15:43:26';
SET @factura_id := (SELECT MIN(id) FROM factura WHERE cai = @cai);
SET @aplicacion_id := (
    SELECT MIN(id)
    FROM aplicacion_pagos
    WHERE factura_id = @factura_id
      AND estado = 1
);

SET @puede_ajustar := IF(
    (SELECT COUNT(*) FROM factura WHERE cai = @cai) = 1
    AND (
        SELECT COUNT(*)
        FROM aplicacion_pagos
        WHERE factura_id = @factura_id
          AND estado = 1
          AND estado_cerrado = 2
          AND ROUND(saldo, 2) = 0.00
    ) = 1
    AND (
        SELECT COUNT(*)
        FROM otros_movimientos
        WHERE factura_id = @factura_id
          AND aplicacion_pagos_id = @aplicacion_id
          AND estado = 1
          AND tipo_movimiento = 2
          AND ROUND(monto, 2) = 0.04
    ) = 1
    AND (
        SELECT COUNT(*)
        FROM facturas_comision
        WHERE factura_id = @factura_id
          AND estado_id = 1
          AND ROUND(monto_rol, 2) = 0.00
    ) = 3
    AND (
        SELECT COUNT(*)
        FROM producto_comision
        WHERE factura_id = @factura_id
          AND estado_id = 1
    ) = 54
    AND (
        SELECT COUNT(*)
        FROM retencion_mora_log
        WHERE factura_id = @factura_id
          AND porcentaje_aplicado = 100.00
    ) = 3
    AND (
        SELECT COUNT(*)
        FROM facturas_comision
        WHERE factura_id = @factura_id
          AND estado_id = 1
          AND (
              (rol_id = 2 AND ROUND(retencion_mora_monto, 2) = 135.98)
              OR (rol_id = 3 AND ROUND(retencion_mora_monto, 2) = 58.28)
              OR (rol_id = 16 AND ROUND(retencion_mora_monto, 2) = 29.14)
          )
    ) = 3,
    1,
    0
);

SELECT
    @factura_id AS factura_id,
    @aplicacion_id AS aplicacion_id,
    @puede_ajustar AS puede_ajustar,
    (
        SELECT fecha_cierre_factura
        FROM aplicacion_pagos
        WHERE id = @aplicacion_id
    ) AS fecha_aplicacion_antes,
    (
        SELECT GROUP_CONCAT(
            CONCAT(rol_id, ':', fecha_cierre_factura)
            ORDER BY rol_id SEPARATOR ' | '
        )
        FROM facturas_comision
        WHERE factura_id = @factura_id
          AND estado_id = 1
    ) AS fechas_comision_antes;

UPDATE aplicacion_pagos
SET
    fecha_cierre_factura = @fecha_cierre,
    updated_at = NOW()
WHERE id = @aplicacion_id
  AND @puede_ajustar = 1;

UPDATE facturas_comision
SET
    fecha_cierre_factura = DATE(@fecha_cierre),
    retencion_mora_dias = 28,
    updated_at = NOW()
WHERE factura_id = @factura_id
  AND aplicacion_pagos_id = @aplicacion_id
  AND estado_id = 1
  AND @puede_ajustar = 1;

UPDATE retencion_mora_log
SET
    fecha_aplicacion = DATE(@fecha_cierre),
    dias_transcurridos = 28,
    updated_at = NOW()
WHERE factura_id = @factura_id
  AND @puede_ajustar = 1;

COMMIT;

SELECT
    CASE
        WHEN @puede_ajustar = 1
            THEN 'FECHAS AJUSTADAS CORRECTAMENTE'
        ELSE 'NO SE AJUSTO: LOS DATOS NO COINCIDEN CON EL CASO AUDITADO'
    END AS resultado;

SELECT
    ap.fecha_cierre_factura,
    COUNT(DISTINCT fc.id) AS comisiones,
    MIN(fc.fecha_cierre_factura) AS fecha_minima_comision,
    MAX(fc.fecha_cierre_factura) AS fecha_maxima_comision,
    COUNT(DISTINCT rml.id) AS retenciones,
    MIN(rml.fecha_aplicacion) AS fecha_minima_retencion,
    MAX(rml.fecha_aplicacion) AS fecha_maxima_retencion,
    MIN(rml.dias_transcurridos) AS dias_minimos,
    MAX(rml.dias_transcurridos) AS dias_maximos
FROM aplicacion_pagos AS ap
LEFT JOIN facturas_comision AS fc
    ON fc.factura_id = ap.factura_id
   AND fc.estado_id = 1
LEFT JOIN retencion_mora_log AS rml
    ON rml.factura_id = ap.factura_id
WHERE ap.id = @aplicacion_id
GROUP BY ap.fecha_cierre_factura;
