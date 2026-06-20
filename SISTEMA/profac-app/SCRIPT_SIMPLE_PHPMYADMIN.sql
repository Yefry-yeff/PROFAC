-- SCRIPT PROYECCION COMISIONES MAYO 2026 - PHPMYADMIN COMPATIBLE
-- Ejecutar de una sola vez

SET @fechaInicio = '2026-05-01';
SET @fechaFin = '2026-05-31';

DROP TEMPORARY TABLE IF EXISTS tmp_cierres_pago;
CREATE TEMPORARY TABLE tmp_cierres_pago AS
SELECT ap.id AS aplicacion_pagos_id, ap.factura_id, COALESCE(DATE(ap.fecha_cierre_factura), MAX(DATE(COALESCE(ac.fecha_pago, ac.created_at)))) AS fecha_pago_cierre
FROM aplicacion_pagos ap
LEFT JOIN abonos_creditos ac ON ac.aplicacion_pagos_id = ap.id AND ac.estado_abono = 1
WHERE ap.estado = 1 AND ap.estado_cerrado = 2
GROUP BY ap.id, ap.factura_id, ap.fecha_cierre_factura
HAVING fecha_pago_cierre BETWEEN @fechaInicio AND @fechaFin;

DROP TEMPORARY TABLE IF EXISTS tmp_facturas_base;
CREATE TEMPORARY TABLE tmp_facturas_base AS
SELECT cp.aplicacion_pagos_id, cp.factura_id, cp.fecha_pago_cierre, f.cai, f.users_id AS facturador_id, uf.name AS facturador_nombre, f.vendedor AS vendedor_id, uv.name AS vendedor_nombre, f.tipo_pago_id, DATE(f.fecha_emision) AS fecha_emision, DATE(f.fecha_vencimiento) AS fecha_vencimiento, COALESCE(f.sub_total, 0) AS sub_total, tf.codigo AS tipo_factura_codigo, cl.cliente_categoria_escala_id, CASE WHEN tf.codigo IN ('sin_restriccion_gobierno', 'sin_restriccion_precio') THEN 1 ELSE 0 END AS es_sr
FROM tmp_cierres_pago cp
INNER JOIN factura f ON f.id = cp.factura_id
INNER JOIN users uf ON uf.id = f.users_id
INNER JOIN users uv ON uv.id = f.vendedor
LEFT JOIN tipo_factura tf ON tf.id = f.tipo_factura_id
LEFT JOIN cliente cl ON cl.id = f.cliente_id;

DROP TEMPORARY TABLE IF EXISTS tmp_targets;
CREATE TEMPORARY TABLE tmp_targets (aplicacion_pagos_id BIGINT, factura_id BIGINT, fecha_pago_cierre DATE, cai VARCHAR(100), capacidad VARCHAR(20), user_id BIGINT, empleado VARCHAR(255), rol_id INT, tipo_pago_id INT, fecha_emision DATE, fecha_vencimiento DATE, sub_total DECIMAL(18,4), tipo_factura_codigo VARCHAR(100), es_sr TINYINT, cliente_categoria_escala_id BIGINT, INDEX idx_factura (factura_id));

INSERT INTO tmp_targets
SELECT aplicacion_pagos_id, factura_id, fecha_pago_cierre, cai, 'FACTURADOR', facturador_id, facturador_nombre, 3, tipo_pago_id, fecha_emision, fecha_vencimiento, sub_total, tipo_factura_codigo, es_sr, cliente_categoria_escala_id FROM tmp_facturas_base;

INSERT INTO tmp_targets
SELECT aplicacion_pagos_id, factura_id, fecha_pago_cierre, cai, 'VENDEDOR', vendedor_id, vendedor_nombre, 2, tipo_pago_id, fecha_emision, fecha_vencimiento, sub_total, tipo_factura_codigo, es_sr, cliente_categoria_escala_id FROM tmp_facturas_base;

DROP TEMPORARY TABLE IF EXISTS tmp_targets_filtrados;
CREATE TEMPORARY TABLE tmp_targets_filtrados AS
SELECT t.* FROM tmp_targets t
LEFT JOIN comision_rol_config crc ON crc.rol_id = t.rol_id AND crc.calcular = 0
WHERE crc.rol_id IS NULL;

DROP TEMPORARY TABLE IF EXISTS tmp_escalas_actuales;
CREATE TEMPORARY TABLE tmp_escalas_actuales AS
SELECT ce.rol_id, ce.categoria_precios_id, ce.porcentaje_comision
FROM comision_escala ce
INNER JOIN (SELECT rol_id, categoria_precios_id, MAX(id) AS max_id FROM comision_escala WHERE estado_id = 1 AND categoria_precios_id IS NOT NULL AND rol_id IN (2, 3) GROUP BY rol_id, categoria_precios_id) ult
ON ult.max_id = ce.id;

DROP TEMPORARY TABLE IF EXISTS tmp_lineas_base;
CREATE TEMPORARY TABLE tmp_lineas_base AS
SELECT tf.aplicacion_pagos_id, tf.factura_id, tf.fecha_pago_cierre, tf.cai, tf.capacidad, tf.user_id, tf.empleado, tf.rol_id, tf.tipo_pago_id, tf.fecha_emision, tf.fecha_vencimiento, tf.sub_total, tf.tipo_factura_codigo, tf.es_sr, vp.producto_id, vp.cantidad, vp.precio_unidad, ppc.categoria_precios_id AS categoria_vendida_id
FROM tmp_targets_filtrados tf
INNER JOIN venta_has_producto vp ON vp.factura_id = tf.factura_id
INNER JOIN precios_producto_carga ppc ON ppc.id = vp.precios_producto_carga_id;

DROP TEMPORARY TABLE IF EXISTS tmp_lineas_comisionadas;
CREATE TEMPORARY TABLE tmp_lineas_comisionadas AS
SELECT lb.*, ea.porcentaje_comision, cp.nombre AS categoria_comision_nombre, ROUND(lb.precio_unidad * lb.cantidad * (ea.porcentaje_comision / 100), 4) AS monto_linea_comision
FROM tmp_lineas_base lb
INNER JOIN tmp_escalas_actuales ea ON ea.rol_id = lb.rol_id AND ea.categoria_precios_id = lb.categoria_vendida_id
LEFT JOIN categoria_precios cp ON cp.id = lb.categoria_vendida_id;

DROP TEMPORARY TABLE IF EXISTS tmp_totales_factura_rol;
CREATE TEMPORARY TABLE tmp_totales_factura_rol AS
SELECT DATE_FORMAT(lc.fecha_pago_cierre, '%Y-%m') AS periodo_pago, lc.fecha_pago_cierre, lc.factura_id, lc.aplicacion_pagos_id, lc.cai, lc.capacidad, lc.user_id, lc.empleado, lc.rol_id, r.nombre AS rol_nombre, lc.tipo_pago_id, lc.fecha_emision, lc.fecha_vencimiento, lc.sub_total, lc.tipo_factura_codigo, lc.es_sr, ROUND(SUM(lc.monto_linea_comision), 4) AS comision_bruta, GROUP_CONCAT(DISTINCT lc.categoria_comision_nombre ORDER BY lc.categoria_comision_nombre SEPARATOR ', ') AS categorias_comision
FROM tmp_lineas_comisionadas lc
LEFT JOIN rol r ON r.id = lc.rol_id
GROUP BY DATE_FORMAT(lc.fecha_pago_cierre, '%Y-%m'), lc.fecha_pago_cierre, lc.factura_id, lc.aplicacion_pagos_id, lc.cai, lc.capacidad, lc.user_id, lc.empleado, lc.rol_id, r.nombre, lc.tipo_pago_id, lc.fecha_emision, lc.fecha_vencimiento, lc.sub_total, lc.tipo_factura_codigo, lc.es_sr;

DROP TEMPORARY TABLE IF EXISTS tmp_proyeccion_comisiones_vf;
CREATE TEMPORARY TABLE tmp_proyeccion_comisiones_vf AS
SELECT tfr.*, 0 AS retencion_mora_proyectada, tfr.comision_bruta AS comision_neta_mora
FROM tmp_totales_factura_rol tfr;

-- RESULTADO 1: RESUMEN POR EMPLEADO
SELECT periodo_pago, capacidad, user_id, empleado, rol_id, rol_nombre, COUNT(DISTINCT factura_id) AS facturas_proyectadas, ROUND(SUM(comision_bruta), 4) AS comision_bruta_total, ROUND(SUM(retencion_mora_proyectada), 4) AS retencion_mora_total, ROUND(SUM(comision_neta_mora), 4) AS comision_neta_mora_total
FROM tmp_proyeccion_comisiones_vf
GROUP BY periodo_pago, capacidad, user_id, empleado, rol_id, rol_nombre
ORDER BY periodo_pago, capacidad, empleado;

-- RESULTADO 2: DETALLE POR FACTURA
SELECT periodo_pago, fecha_pago_cierre, factura_id, aplicacion_pagos_id, cai, capacidad, user_id, empleado, rol_id, rol_nombre, tipo_factura_codigo, comision_bruta, comision_neta_mora
FROM tmp_proyeccion_comisiones_vf
ORDER BY fecha_pago_cierre, cai, capacidad, empleado
LIMIT 100;

-- RESULTADO 3: TOTALES
SELECT COUNT(DISTINCT factura_id) AS total_facturas, ROUND(SUM(comision_bruta), 4) AS comision_total FROM tmp_proyeccion_comisiones_vf;
