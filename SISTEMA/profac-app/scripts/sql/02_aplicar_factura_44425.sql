START TRANSACTION;

SET @cai := CONVERT('000-001-01-00044425' USING latin1) COLLATE latin1_swedish_ci;
SET @fecha_cierre := '2026-09-24 15:43:26';
SET @monto_movimiento := 0.04;
SET @ejecutado_en := NOW();

SET @factura_id := (
    SELECT MIN(id)
    FROM factura
    WHERE cai = @cai
);

SET @aplicacion_id := (
    SELECT MIN(id)
    FROM aplicacion_pagos
    WHERE factura_id = @factura_id
      AND estado = 1
);

SET @movimiento_id := (
    SELECT MIN(id)
    FROM otros_movimientos
    WHERE factura_id = @factura_id
      AND aplicacion_pagos_id = @aplicacion_id
      AND tipo_movimiento = 2
      AND estado = 1
      AND ROUND(monto, 2) = @monto_movimiento
);

SET @usuario_id := (
    SELECT usr_registro
    FROM otros_movimientos
    WHERE id = @movimiento_id
);

SET @puede_aplicar := IF(
    (SELECT COUNT(*) FROM factura WHERE cai = @cai) = 1
    AND (
        SELECT COUNT(*)
        FROM aplicacion_pagos
        WHERE factura_id = @factura_id
          AND estado = 1
    ) = 1
    AND (
        SELECT COUNT(*)
        FROM otros_movimientos
        WHERE factura_id = @factura_id
          AND aplicacion_pagos_id = @aplicacion_id
          AND tipo_movimiento = 2
          AND estado = 1
          AND ROUND(monto, 2) = @monto_movimiento
    ) = 1
    AND (
        SELECT ROUND(saldo, 2)
        FROM aplicacion_pagos
        WHERE id = @aplicacion_id
    ) = 0.00
    AND (
        SELECT ROUND(movimiento_resta, 2)
        FROM aplicacion_pagos
        WHERE id = @aplicacion_id
    ) = @monto_movimiento
    AND (
        SELECT estado_cerrado
        FROM aplicacion_pagos
        WHERE id = @aplicacion_id
    ) <> 2
    AND (
        SELECT COUNT(*)
        FROM facturas_comision
        WHERE factura_id = @factura_id
          AND estado_id = 1
    ) = 0
    AND EXISTS (
        SELECT 1
        FROM users
        WHERE id = @usuario_id
    ),
    1,
    0
);

DROP TEMPORARY TABLE IF EXISTS tmp_44425_lineas;
CREATE TEMPORARY TABLE tmp_44425_lineas AS
SELECT
    neto.producto_id,
    neto.seccion_id,
    neto.unidad_medida_venta_id,
    neto.precios_producto_carga_id,
    neto.categoria_precios_id,
    neto.precio_venta,
    neto.cantidad
FROM (
    SELECT
        venta.producto_id,
        venta.seccion_id,
        venta.unidad_medida_venta_id,
        venta.precios_producto_carga_id,
        venta.categoria_precios_id,
        venta.precio_venta,
        GREATEST(venta.cantidad - COALESCE(devolucion.cantidad, 0), 0) AS cantidad
    FROM (
        SELECT
            vp.producto_id,
            vp.seccion_id,
            vp.unidad_medida_venta_id,
            vp.precios_producto_carga_id,
            ppc.categoria_precios_id,
            COALESCE(NULLIF(vp.precioSeleccionado, 0), vp.precio_unidad) AS precio_venta,
            COALESCE(SUM(vp.cantidad_s), SUM(vp.cantidad)) AS cantidad
        FROM venta_has_producto AS vp
        INNER JOIN precios_producto_carga AS ppc
            ON ppc.id = vp.precios_producto_carga_id
        WHERE vp.factura_id = @factura_id
        GROUP BY
            vp.factura_id,
            vp.producto_id,
            vp.seccion_id,
            vp.unidad_medida_venta_id,
            vp.precios_producto_carga_id,
            vp.precio_unidad,
            vp.precioSeleccionado,
            ppc.categoria_precios_id
    ) AS venta
    LEFT JOIN (
        SELECT
            ncp.producto_id,
            ncp.seccion_id,
            ncp.unidad_medida_venta_id,
            ncp.precios_producto_carga_id,
            ncp.precio_unidad,
            SUM(ncp.cantidad) AS cantidad
        FROM nota_credito AS nc
        INNER JOIN nota_credito_has_producto AS ncp
            ON ncp.nota_credito_id = nc.id
        WHERE nc.factura_id = @factura_id
          AND nc.estado_nota_id = 1
          AND nc.estado_rebajado IN (1, 3)
        GROUP BY
            ncp.producto_id,
            ncp.seccion_id,
            ncp.unidad_medida_venta_id,
            ncp.precios_producto_carga_id,
            ncp.precio_unidad
    ) AS devolucion
        ON devolucion.producto_id = venta.producto_id
       AND devolucion.seccion_id = venta.seccion_id
       AND devolucion.unidad_medida_venta_id = venta.unidad_medida_venta_id
       AND devolucion.precios_producto_carga_id = venta.precios_producto_carga_id
       AND ROUND(devolucion.precio_unidad, 4) = ROUND(venta.precio_venta, 4)
) AS neto
WHERE neto.cantidad > 0.005;

DROP TEMPORARY TABLE IF EXISTS tmp_44425_targets;
CREATE TEMPORARY TABLE tmp_44425_targets (
    rol_id INT NOT NULL,
    tipo_comision INT NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (rol_id)
);

INSERT INTO tmp_44425_targets (rol_id, tipo_comision, user_id)
SELECT 3, 1, f.users_id
FROM factura AS f
WHERE f.id = @factura_id
  AND NOT EXISTS (
      SELECT 1
      FROM comision_rol_config
      WHERE rol_id = 3 AND calcular = 0
  )
UNION ALL
SELECT 2, 3, f.vendedor
FROM factura AS f
WHERE f.id = @factura_id
  AND NOT EXISTS (
      SELECT 1
      FROM comision_rol_config
      WHERE rol_id = 2 AND calcular = 0
  )
UNION ALL
SELECT 16, 4, f.gestor_entrega
FROM factura AS f
WHERE f.id = @factura_id
  AND f.gestor_entrega IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM comision_rol_config
      WHERE rol_id = 16 AND calcular = 0
  );

DROP TEMPORARY TABLE IF EXISTS tmp_44425_totales;
CREATE TEMPORARY TABLE tmp_44425_totales AS
SELECT
    target.rol_id,
    target.tipo_comision,
    target.user_id,
    ROUND(SUM(
        linea.cantidad
        * linea.precio_venta
        * (escala.porcentaje_comision / 100)
    ), 4) AS comision_original
FROM tmp_44425_targets AS target
INNER JOIN factura AS f ON f.id = @factura_id
INNER JOIN cliente AS cl ON cl.id = f.cliente_id
INNER JOIN tmp_44425_lineas AS linea
INNER JOIN comision_escala AS escala
    ON escala.rol_id = target.rol_id
   AND escala.cliente_categoria_escala_id = cl.cliente_categoria_escala_id
   AND escala.categoria_precios_id = linea.categoria_precios_id
   AND escala.estado_id = 1
GROUP BY
    target.rol_id,
    target.tipo_comision,
    target.user_id;

SET @cantidad_lineas := (SELECT COUNT(*) FROM tmp_44425_lineas);
SET @base_comisionable := (
    SELECT ROUND(SUM(cantidad * precio_venta), 2)
    FROM tmp_44425_lineas
);
SET @cantidad_targets := (SELECT COUNT(*) FROM tmp_44425_targets);
SET @cantidad_totales := (SELECT COUNT(*) FROM tmp_44425_totales);

SET @puede_aplicar := IF(
    @puede_aplicar = 1
    AND @cantidad_lineas = 18
    AND @base_comisionable = 6351.60
    AND @cantidad_targets = 3
    AND @cantidad_totales = 3,
    1,
    0
);

SELECT
    @factura_id AS factura_id,
    @aplicacion_id AS aplicacion_id,
    @movimiento_id AS movimiento_id,
    @usuario_id AS usuario_ejecutor,
    @cantidad_lineas AS lineas_comisionables,
    @base_comisionable AS base_comisionable,
    @puede_aplicar AS puede_aplicar;

SELECT
    rol_id,
    tipo_comision,
    user_id,
    comision_original
FROM tmp_44425_totales
ORDER BY rol_id;

UPDATE aplicacion_pagos
SET
    saldo = 0,
    estado_cerrado = 2,
    usr_cerro = @usuario_id,
    fecha_cierre_factura = @fecha_cierre,
    comentario = CONCAT(
        'CIERRE POR OTRO MOVIMIENTO: ',
        (SELECT TRIM(comentario) FROM otros_movimientos WHERE id = @movimiento_id)
    ),
    ultimo_usr_actualizo = @usuario_id,
    updated_at = @ejecutado_en
WHERE id = @aplicacion_id
  AND @puede_aplicar = 1;

INSERT INTO facturas_comision (
    fecha_cierre_factura,
    monto_rol,
    retencion_mora_monto,
    retencion_mora_dias,
    factura_id,
    comision_escala_id,
    aplicacion_pagos_id,
    rol_id,
    tipo_comision,
    estado_id,
    cantidad_usuariosxrol,
    created_at,
    updated_at
)
SELECT
    DATE(@fecha_cierre),
    total.comision_original,
    0,
    0,
    @factura_id,
    NULL,
    @aplicacion_id,
    total.rol_id,
    total.tipo_comision,
    1,
    1,
    @ejecutado_en,
    @ejecutado_en
FROM tmp_44425_totales AS total
WHERE @puede_aplicar = 1;

INSERT INTO producto_comision (
    cantidad,
    precio_venta,
    monto_comision,
    precios_producto_carga_id,
    factura_id,
    producto_id,
    estado_id,
    facturas_comision_id,
    rol_id,
    created_at,
    updated_at
)
SELECT
    linea.cantidad,
    linea.precio_venta,
    ROUND(
        linea.cantidad
        * linea.precio_venta
        * (escala.porcentaje_comision / 100),
        4
    ),
    linea.precios_producto_carga_id,
    @factura_id,
    linea.producto_id,
    1,
    fc.id,
    total.rol_id,
    @ejecutado_en,
    @ejecutado_en
FROM tmp_44425_lineas AS linea
INNER JOIN factura AS f ON f.id = @factura_id
INNER JOIN cliente AS cl ON cl.id = f.cliente_id
INNER JOIN tmp_44425_totales AS total
INNER JOIN comision_escala AS escala
    ON escala.rol_id = total.rol_id
   AND escala.cliente_categoria_escala_id = cl.cliente_categoria_escala_id
   AND escala.categoria_precios_id = linea.categoria_precios_id
   AND escala.estado_id = 1
INNER JOIN facturas_comision AS fc
    ON fc.factura_id = @factura_id
   AND fc.aplicacion_pagos_id = @aplicacion_id
   AND fc.rol_id = total.rol_id
   AND fc.estado_id = 1
WHERE @puede_aplicar = 1;

INSERT INTO retencion_mora_log (
    factura_id,
    facturas_comision_id,
    rol_id,
    user_id,
    tipo_factura,
    fecha_aplicacion,
    dias_transcurridos,
    dias_gracia_configurados,
    porcentaje_aplicado,
    periodo_numero,
    comision_original,
    monto_retenido,
    subtotal_factura,
    usuario_ejecutor,
    created_at,
    updated_at
)
SELECT
    @factura_id,
    fc.id,
    total.rol_id,
    total.user_id,
    'contado',
    DATE(@fecha_cierre),
    DATEDIFF(DATE(@fecha_cierre), f.fecha_emision),
    gracia.dias_gracia,
    100.00,
    NULL,
    total.comision_original,
    total.comision_original,
    f.sub_total,
    @usuario_id,
    @ejecutado_en,
    @ejecutado_en
FROM tmp_44425_totales AS total
INNER JOIN factura AS f ON f.id = @factura_id
INNER JOIN facturas_comision AS fc
    ON fc.factura_id = @factura_id
   AND fc.aplicacion_pagos_id = @aplicacion_id
   AND fc.rol_id = total.rol_id
   AND fc.estado_id = 1
INNER JOIN dias_gracia_comision AS gracia
    ON gracia.rol_id = total.rol_id
   AND gracia.tipo_factura = 'contado'
   AND gracia.dias_gracia > 0
WHERE @puede_aplicar = 1
  AND DATEDIFF(DATE(@fecha_cierre), f.fecha_emision) > gracia.dias_gracia;

UPDATE facturas_comision AS fc
INNER JOIN tmp_44425_totales AS total ON total.rol_id = fc.rol_id
SET
    fc.monto_rol = 0,
    fc.retencion_mora_monto = total.comision_original,
    fc.retencion_mora_dias = (
        SELECT DATEDIFF(DATE(@fecha_cierre), f.fecha_emision)
        FROM factura AS f
        WHERE f.id = @factura_id
    ),
    fc.updated_at = @ejecutado_en
WHERE fc.factura_id = @factura_id
  AND fc.aplicacion_pagos_id = @aplicacion_id
  AND fc.estado_id = 1
  AND @puede_aplicar = 1;

COMMIT;

SELECT
    CASE
        WHEN @puede_aplicar = 1
            THEN 'APLICADO CORRECTAMENTE'
        ELSE 'NO SE APLICO: LAS PRECONDICIONES NO COINCIDEN'
    END AS resultado;

SELECT
    f.cai,
    ap.id AS aplicacion_id,
    ap.saldo,
    ap.estado_cerrado,
    ap.fecha_cierre_factura,
    COUNT(DISTINCT fc.id) AS comisiones_activas,
    COUNT(DISTINCT pc.id) AS detalles_comision,
    COUNT(DISTINCT rml.id) AS retenciones
FROM factura AS f
INNER JOIN aplicacion_pagos AS ap
    ON ap.factura_id = f.id
   AND ap.estado = 1
LEFT JOIN facturas_comision AS fc
    ON fc.factura_id = f.id
   AND fc.estado_id = 1
LEFT JOIN producto_comision AS pc
    ON pc.facturas_comision_id = fc.id
   AND pc.estado_id = 1
LEFT JOIN retencion_mora_log AS rml
    ON rml.facturas_comision_id = fc.id
WHERE f.cai = @cai
GROUP BY
    f.cai,
    ap.id,
    ap.saldo,
    ap.estado_cerrado,
    ap.fecha_cierre_factura;

DROP TEMPORARY TABLE IF EXISTS tmp_44425_totales;
DROP TEMPORARY TABLE IF EXISTS tmp_44425_targets;
DROP TEMPORARY TABLE IF EXISTS tmp_44425_lineas;
