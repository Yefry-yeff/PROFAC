SET @fecha_inicio='2026-05-11 00:00:00';
SET @fecha_fin='2026-06-25 23:59:59';
SET @target_user_id=86;

DROP TEMPORARY TABLE IF EXISTS tmp_facturas_objetivo;
DROP TEMPORARY TABLE IF EXISTS tmp_cat_baja;
DROP TEMPORARY TABLE IF EXISTS tmp_targets_raw;
DROP TEMPORARY TABLE IF EXISTS tmp_targets;
DROP TEMPORARY TABLE IF EXISTS tmp_lineas;
DROP TEMPORARY TABLE IF EXISTS tmp_proyeccion_comision;

CREATE TEMPORARY TABLE tmp_facturas_objetivo AS
SELECT
  f.id AS factura_id,
  f.cai,
  ap.id AS aplicacion_pagos_id,
  ap.fecha_cierre_factura,
  f.users_id AS facturador_id,
  COALESCE(uf.rol_id,0) AS facturador_rol_real,
  f.vendedor AS vendedor_id,
  f.gestor_entrega AS gestor_id,
  f.cliente_id,
  COALESCE(cl.cliente_categoria_escala_id,0) AS cliente_categoria_escala_id,
  tf.codigo AS tipo_factura_codigo
FROM factura f
JOIN aplicacion_pagos ap
  ON ap.factura_id=f.id
 AND ap.estado_cerrado=2
 AND COALESCE(ap.estado,1)=1
 AND ap.fecha_cierre_factura BETWEEN @fecha_inicio AND @fecha_fin
LEFT JOIN users uf ON uf.id=f.users_id
LEFT JOIN cliente cl ON cl.id=f.cliente_id
LEFT JOIN tipo_factura tf ON tf.id=f.tipo_factura_id
WHERE COALESCE(f.estado_venta_id,0)<>2
  AND (f.users_id=@target_user_id OR f.vendedor=@target_user_id OR f.gestor_entrega=@target_user_id);

ALTER TABLE tmp_facturas_objetivo ADD INDEX idx_tfo_factura(factura_id);
ALTER TABLE tmp_facturas_objetivo ADD INDEX idx_tfo_cliente_cat(cliente_categoria_escala_id);

CREATE TEMPORARY TABLE tmp_cat_baja AS
SELECT cp.cliente_categoria_escala_id, MIN(cp.id) AS categoria_id
FROM categoria_precios cp
JOIN (
  SELECT cliente_categoria_escala_id, MIN(COALESCE(porc_precio_a,999999999)) AS min_porc
  FROM categoria_precios
  WHERE estado_id=1
  GROUP BY cliente_categoria_escala_id
) x
  ON x.cliente_categoria_escala_id=cp.cliente_categoria_escala_id
 AND COALESCE(cp.porc_precio_a,999999999)=x.min_porc
WHERE cp.estado_id=1
GROUP BY cp.cliente_categoria_escala_id;

ALTER TABLE tmp_cat_baja ADD INDEX idx_tcb_cliente_cat(cliente_categoria_escala_id);

CREATE TEMPORARY TABLE tmp_targets_raw (
  factura_id INT,
  cai VARCHAR(30),
  aplicacion_pagos_id INT,
  fecha_cierre DATE,
  cliente_id INT,
  cliente_categoria_escala_id INT,
  es_sr TINYINT,
  categoria_precio_forzada INT,
  tipo_comision INT,
  rol_id INT,
  target_user_id INT,
  prioridad INT
);

INSERT INTO tmp_targets_raw
SELECT
  tfo.factura_id,
  tfo.cai,
  tfo.aplicacion_pagos_id,
  DATE(tfo.fecha_cierre_factura),
  tfo.cliente_id,
  tfo.cliente_categoria_escala_id,
  CASE WHEN tfo.tipo_factura_codigo IN ('sin_restriccion_gobierno','sin_restriccion_precio') THEN 1 ELSE 0 END,
  cb.categoria_id,
  1,
  3,
  tfo.facturador_id,
  1
FROM tmp_facturas_objetivo tfo
LEFT JOIN tmp_cat_baja cb ON cb.cliente_categoria_escala_id=tfo.cliente_categoria_escala_id
WHERE tfo.facturador_id=@target_user_id;

INSERT INTO tmp_targets_raw
SELECT
  tfo.factura_id,
  tfo.cai,
  tfo.aplicacion_pagos_id,
  DATE(tfo.fecha_cierre_factura),
  tfo.cliente_id,
  tfo.cliente_categoria_escala_id,
  CASE WHEN tfo.tipo_factura_codigo IN ('sin_restriccion_gobierno','sin_restriccion_precio') THEN 1 ELSE 0 END,
  cb.categoria_id,
  2,
  tfo.facturador_rol_real,
  tfo.facturador_id,
  2
FROM tmp_facturas_objetivo tfo
LEFT JOIN tmp_cat_baja cb ON cb.cliente_categoria_escala_id=tfo.cliente_categoria_escala_id
WHERE tfo.facturador_id=@target_user_id
  AND tfo.facturador_rol_real NOT IN (0,2,3);

INSERT INTO tmp_targets_raw
SELECT
  tfo.factura_id,
  tfo.cai,
  tfo.aplicacion_pagos_id,
  DATE(tfo.fecha_cierre_factura),
  tfo.cliente_id,
  tfo.cliente_categoria_escala_id,
  CASE WHEN tfo.tipo_factura_codigo IN ('sin_restriccion_gobierno','sin_restriccion_precio') THEN 1 ELSE 0 END,
  cb.categoria_id,
  3,
  2,
  tfo.vendedor_id,
  3
FROM tmp_facturas_objetivo tfo
LEFT JOIN tmp_cat_baja cb ON cb.cliente_categoria_escala_id=tfo.cliente_categoria_escala_id
WHERE tfo.vendedor_id=@target_user_id;

INSERT INTO tmp_targets_raw
SELECT
  tfo.factura_id,
  tfo.cai,
  tfo.aplicacion_pagos_id,
  DATE(tfo.fecha_cierre_factura),
  tfo.cliente_id,
  tfo.cliente_categoria_escala_id,
  CASE WHEN tfo.tipo_factura_codigo IN ('sin_restriccion_gobierno','sin_restriccion_precio') THEN 1 ELSE 0 END,
  cb.categoria_id,
  4,
  16,
  tfo.gestor_id,
  4
FROM tmp_facturas_objetivo tfo
LEFT JOIN tmp_cat_baja cb ON cb.cliente_categoria_escala_id=tfo.cliente_categoria_escala_id
WHERE tfo.gestor_id=@target_user_id;

DELETE tr
FROM tmp_targets_raw tr
LEFT JOIN comision_rol_config crc ON crc.rol_id=tr.rol_id
WHERE COALESCE(crc.calcular,1)=0;

CREATE TEMPORARY TABLE tmp_targets AS
SELECT tr.*
FROM tmp_targets_raw tr
JOIN (
  SELECT factura_id, rol_id, MAX(prioridad) AS max_prio
  FROM tmp_targets_raw
  GROUP BY factura_id, rol_id
) m
  ON m.factura_id=tr.factura_id
 AND m.rol_id=tr.rol_id
 AND m.max_prio=tr.prioridad;

ALTER TABLE tmp_targets ADD INDEX idx_tt_factura(factura_id);
ALTER TABLE tmp_targets ADD INDEX idx_tt_rol(rol_id);

CREATE TEMPORARY TABLE tmp_lineas AS
SELECT
  vp.factura_id,
  vp.producto_id,
  vp.precios_producto_carga_id,
  vp.cantidad,
  vp.precio_unidad,
  vp.precioSeleccionado,
  COALESCE(NULLIF(vp.precioSeleccionado,0), vp.precio_unidad) AS precio_para_comision,
  ppc.categoria_precios_id,
  p.nombre AS producto
FROM venta_has_producto vp
LEFT JOIN precios_producto_carga ppc ON ppc.id=vp.precios_producto_carga_id
LEFT JOIN producto p ON p.id=vp.producto_id
JOIN tmp_facturas_objetivo tfo ON tfo.factura_id=vp.factura_id;

ALTER TABLE tmp_lineas ADD INDEX idx_tl_factura(factura_id);
ALTER TABLE tmp_lineas ADD INDEX idx_tl_cat(categoria_precios_id);

CREATE TEMPORARY TABLE tmp_proyeccion_comision AS
SELECT
  tt.factura_id,
  tt.cai,
  tt.fecha_cierre,
  tt.aplicacion_pagos_id,
  tt.cliente_id,
  cl.nombre AS cliente,
  tt.target_user_id AS user_id,
  u.name AS usuario,
  tt.tipo_comision,
  CASE tt.tipo_comision
    WHEN 1 THEN 'Facturador (rol fijo)'
    WHEN 2 THEN 'Facturador (rol real)'
    WHEN 3 THEN 'Vendedor'
    WHEN 4 THEN 'Gestor entrega'
    ELSE 'Otro'
  END AS capacidad,
  tt.rol_id,
  r.nombre AS rol_nombre,
  tl.producto_id,
  tl.producto,
  tl.cantidad,
  tl.precio_unidad,
  tl.precioSeleccionado,
  tl.precio_para_comision,
  ce.porcentaje_comision,
  ROUND(tl.cantidad * tl.precio_para_comision,4) AS base_comisionable,
  ROUND((ce.porcentaje_comision/100) * tl.precio_para_comision * tl.cantidad,4) AS monto_comision_linea
FROM tmp_targets tt
JOIN tmp_lineas tl ON tl.factura_id=tt.factura_id
JOIN comision_escala ce
  ON ce.rol_id=tt.rol_id
 AND ce.estado_id=1
 AND ce.cliente_categoria_escala_id=tt.cliente_categoria_escala_id
 AND ce.categoria_precios_id=CASE WHEN tt.es_sr=1 THEN tt.categoria_precio_forzada ELSE tl.categoria_precios_id END
LEFT JOIN users u ON u.id=tt.target_user_id
LEFT JOIN rol r ON r.id=tt.rol_id
LEFT JOIN cliente cl ON cl.id=tt.cliente_id;

ALTER TABLE tmp_proyeccion_comision ADD INDEX idx_tpc_factura(factura_id);
ALTER TABLE tmp_proyeccion_comision ADD INDEX idx_tpc_user(user_id);

SELECT 'BLOQUE_A_OK' AS estado,
       (SELECT COUNT(*) FROM tmp_facturas_objetivo) AS facturas_objetivo,
       (SELECT COUNT(*) FROM tmp_targets) AS targets_finales,
       (SELECT COUNT(*) FROM tmp_proyeccion_comision) AS lineas_proyectadas;