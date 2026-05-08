-- ============================================================================
-- REPORTE: FACTURAS CERRADAS — MARZO Y ABRIL 2026
-- Una fila por cada línea de producto en facturas cerradas (saldo = 0)
-- ============================================================================
-- Notas:
--   • Factura cerrada  = aplicacion_pagos.estado_cerrado = 2  AND  saldo = 0
--                        AND estado = 1 (registro activo)
--   • Un único aplicacion_pagos por factura → sin riesgo de duplicar facturas
--   • El mismo producto puede aparecer 2+ veces en una factura (lotes distintos):
--     eso es correcto, son líneas reales independientes
--   • ppc se ancla al registro más reciente activo (MAX id) por escala+producto
--     para evitar no-determinismo cuando hay múltiples versiones de precio
--   • fecha_cierre_factura es NULL en esta DB → se usa updated_at de aplicacion_pagos
--   • Para cambiar el rango edita las dos fechas en el WHERE
--   • Filas esperadas Mar-Abr 2026: ~3154 líneas de 438 facturas
-- ============================================================================

SELECT

    -- ── FACTURA ──────────────────────────────────────────────────────────────
    f.id                                          AS id_factura,
    f.numero_factura,
    f.cai                                         AS cai_factura,
    f.fecha_emision,
    DATE(ap.updated_at)                           AS fecha_registro_cierre,

    -- ── CLIENTE ──────────────────────────────────────────────────────────────
    f.nombre_cliente,
    cce.nombre_categoria                          AS categoria_cliente,

    -- ── PRODUCTO ─────────────────────────────────────────────────────────────
    p.nombre                                      AS producto,

    -- ── CATEGORÍA DE PRECIOS DEL PRODUCTO (por cadena cliente→escala) ────────
    cp.nombre                                     AS categoria_precio_producto,

    -- ── PRECIO DEL CATÁLOGO ───────────────────────────────────────────────────
    -- precio_a = precio escala asignado a la categoría del cliente
    -- debe coincidir con precio_facturado_real cuando se selecciona nivel A
    ppc.precio_a                                  AS precio_catalogo_a,

    -- ── VERIFICACIÓN: precio_a siempre debe ser > precio_base_venta ──────────
    CASE WHEN ppc.precio_a > ppc.precio_base_venta THEN 'MAYOR' ELSE 'NO' END AS verificacion,

    -- ── LO QUE SE FACTURÓ EN ESTA LÍNEA ──────────────────────────────────────
    UPPER(vhp.idPrecioSeleccionado)               AS tipo_precio_seleccionado,
    vhp.precio_unidad                             AS precio_facturado_real,
    vhp.cantidad,
    vhp.sub_total                                 AS total_linea_sin_isv,
    vhp.isv                                       AS isv_linea,
    vhp.total                                     AS total_linea_con_isv,

    -- ── PERSONAL ─────────────────────────────────────────────────────────────
    uv.name                                       AS vendedor,
    uf.name                                       AS facturador,

    -- ── TOTALES DE LA FACTURA (se repiten en cada línea del mismo comprobante)
    f.sub_total                                   AS subtotal_factura_sin_isv,
    f.isv                                         AS isv_factura,
    f.monto_descuento                             AS descuento_factura,
    f.total                                       AS total_factura_con_isv

FROM aplicacion_pagos ap
INNER JOIN factura              f   ON  f.id  = ap.factura_id
INNER JOIN venta_has_producto   vhp ON  vhp.factura_id = f.id
INNER JOIN producto             p   ON  p.id  = vhp.producto_id
INNER JOIN users                uv  ON  uv.id = f.vendedor
INNER JOIN users                uf  ON  uf.id = f.users_id
LEFT  JOIN cliente              c   ON  c.id  = f.cliente_id
LEFT  JOIN cliente_categoria_escala cce ON cce.id = c.cliente_categoria_escala_id
-- Anclar al precio activo MÁS RECIENTE (MAX id) por escala+producto
-- evita no-determinismo cuando existen múltiples versiones de precio activas
LEFT  JOIN precios_producto_carga ppc ON ppc.id = (
    SELECT  MAX(ppc2.id)
    FROM    precios_producto_carga ppc2
    INNER JOIN categoria_precios  cp2 ON cp2.id = ppc2.categoria_precios_id
    WHERE   cp2.cliente_categoria_escala_id = cce.id
      AND   ppc2.producto_id               = vhp.producto_id
      AND   ppc2.estado_id                 = 1
)
LEFT  JOIN categoria_precios    cp  ON  cp.id = ppc.categoria_precios_id

WHERE ap.estado_cerrado = 2
  AND ap.saldo          = 0
  AND ap.estado         = 1
  AND ap.updated_at BETWEEN '2026-03-01 00:00:00' AND '2026-04-30 23:59:59'

ORDER BY
    ap.updated_at,
    uv.name,
    f.id,
    p.nombre;
