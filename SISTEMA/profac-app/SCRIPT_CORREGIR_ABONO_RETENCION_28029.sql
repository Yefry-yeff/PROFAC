-- Corrección puntual del caso de la factura 000-001-01-00042945.
-- Factura: 28029 | Aplicación de pagos: 35503 | Seguimiento: 46
-- Ejecutar en la base de datos correcta y revisar el SELECT antes del UPDATE.

START TRANSACTION;

SELECT
    frs.id,
    frs.factura_id,
    frs.aplicacion_pagos_id,
    frs.estado,
    frs.observacion_marcado,
    frs.observacion_resolucion,
    frs.fecha_marcado,
    frs.fecha_resolucion
FROM factura_retencion_seguimiento frs
WHERE frs.factura_id = 28029
  AND frs.aplicacion_pagos_id = 35503;

UPDATE factura_retencion_seguimiento frs
JOIN (
    SELECT factura_id, aplicacion_pagos_id, MAX(id) AS abono_anulado_id
    FROM abonos_creditos
    WHERE estado_abono = 0
    GROUP BY factura_id, aplicacion_pagos_id
) aa ON aa.factura_id = frs.factura_id
    AND aa.aplicacion_pagos_id = frs.aplicacion_pagos_id
SET frs.estado = 'anulada',
    frs.observacion_resolucion = CONCAT('Se anula Abono #', aa.abono_anulado_id),
    frs.usr_resolvio = COALESCE(frs.usr_resolvio, 6),
    frs.fecha_resolucion = COALESCE(frs.fecha_resolucion, NOW()),
    frs.updated_at = NOW()
WHERE frs.factura_id = 28029
  AND frs.aplicacion_pagos_id = 35503
  AND frs.estado = 'pendiente';

SELECT
    frs.id,
    frs.factura_id,
    frs.aplicacion_pagos_id,
    frs.estado,
    frs.observacion_resolucion,
    frs.usr_resolvio,
    frs.fecha_resolucion
FROM factura_retencion_seguimiento frs
WHERE frs.factura_id = 28029
  AND frs.aplicacion_pagos_id = 35503;

COMMIT;