SET @cai := CONVERT('000-001-01-00044425' USING latin1) COLLATE latin1_swedish_ci;

SELECT
    f.id AS factura_id,
    f.cai,
    f.fecha_emision,
    f.sub_total,
    ap.id AS aplicacion_id,
    ap.saldo,
    ap.movimiento_resta,
    ap.estado,
    ap.estado_cerrado,
    ap.fecha_cierre_factura,
    ap.usr_cerro
FROM factura AS f
INNER JOIN aplicacion_pagos AS ap
    ON ap.factura_id = f.id
   AND ap.estado = 1
WHERE f.cai = @cai;

SELECT
    om.id,
    om.aplicacion_pagos_id,
    om.factura_id,
    om.monto,
    om.tipo_movimiento,
    om.estado,
    om.usr_registro,
    om.comentario,
    om.created_at
FROM otros_movimientos AS om
INNER JOIN factura AS f ON f.id = om.factura_id
WHERE f.cai = @cai
ORDER BY om.id;

SELECT
    fc.id,
    fc.rol_id,
    fc.monto_rol,
    fc.retencion_mora_monto,
    fc.estado_id,
    fc.fecha_cierre_factura
FROM facturas_comision AS fc
INNER JOIN factura AS f ON f.id = fc.factura_id
WHERE f.cai = @cai
ORDER BY fc.id;

SELECT
    CASE
        WHEN (SELECT COUNT(*) FROM factura WHERE cai = @cai) <> 1
            THEN 'NO APLICAR: la factura no existe o el CAI esta duplicado'
        WHEN (
            SELECT COUNT(*)
            FROM aplicacion_pagos AS ap
            INNER JOIN factura AS f ON f.id = ap.factura_id
            WHERE f.cai = @cai AND ap.estado = 1
        ) <> 1
            THEN 'NO APLICAR: no existe exactamente una aplicacion activa'
        WHEN (
            SELECT ROUND(ap.saldo, 2)
            FROM aplicacion_pagos AS ap
            INNER JOIN factura AS f ON f.id = ap.factura_id
            WHERE f.cai = @cai AND ap.estado = 1
            LIMIT 1
        ) <> 0.00
            THEN 'NO APLICAR: el saldo no es cero'
        WHEN (
            SELECT COUNT(*)
            FROM otros_movimientos AS om
            INNER JOIN factura AS f ON f.id = om.factura_id
            WHERE f.cai = @cai
              AND om.tipo_movimiento = 2
              AND om.estado = 1
              AND ROUND(om.monto, 2) = 0.04
        ) <> 1
            THEN 'NO APLICAR: no existe exactamente una deduccion activa de L 0.04'
        WHEN (
            SELECT COUNT(*)
            FROM facturas_comision AS fc
            INNER JOIN factura AS f ON f.id = fc.factura_id
            WHERE f.cai = @cai AND fc.estado_id = 1
        ) > 0
            THEN 'NO APLICAR: ya existen comisiones activas'
        ELSE 'LISTO PARA APLICAR'
    END AS resultado_verificacion;
