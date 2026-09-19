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

UPDATE factura_retencion_seguimiento
SET estado = 'descartada',
    observacion_resolucion = 'No aplica: el abono que originó el seguimiento fue anulado. Corrección del caso 28029/35503.',
    usr_resolvio = COALESCE(usr_resolvio, 6),
    fecha_resolucion = COALESCE(fecha_resolucion, NOW()),
    updated_at = NOW()
WHERE factura_id = 28029
  AND aplicacion_pagos_id = 35503
  AND estado = 'pendiente';

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