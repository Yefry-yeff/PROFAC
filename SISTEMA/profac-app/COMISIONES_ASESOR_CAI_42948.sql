SELECT
    f.cai                                                    AS factura,
    cl.nombre                                                AS cliente,
    cce.nombre_categoria                                     AS escala_cliente,
    u.name                                                   AS vendedor,
    r.nombre                                                 AS rol_comision,

    p.nombre                                                 AS producto,
    cp.nombre                                                AS categoria_precio_vendida,
    ce.porcentaje_comision                                   AS porcentaje_pct,

    b.cantidad,
    vhp.precio_unidad                                        AS precio_unitario,
    COALESCE(NULLIF(vhp.precioSeleccionado,0), vhp.precio_unidad) AS precio_para_comision,

    ROUND(b.cantidad * vhp.precio_unidad, 4)                AS base_comisionable_precio_unitario,

    ROUND(b.cantidad * COALESCE(NULLIF(vhp.precioSeleccionado,0), vhp.precio_unidad), 4)
                                                             AS base_comisionable,

    ROUND(
        (ce.porcentaje_comision / 100)
        * COALESCE(NULLIF(vhp.precioSeleccionado,0), vhp.precio_unidad)
        * b.cantidad
    , 4)                                                     AS comision_linea,

    ce.id                                                    AS escala_id,
    ce.cliente_categoria_escala_id,
    ce.categoria_precios_id,
    c.precio_a                                               AS precio_a_ppc,
    c.id                                                     AS precios_producto_carga_id

FROM factura f


JOIN venta_has_producto vhp
    ON vhp.factura_id = f.id

/* el campo "b" de tu query original */
JOIN venta_has_producto b
    ON b.factura_id = f.id
   AND b.producto_id = vhp.producto_id
   AND b.lote        = vhp.lote

JOIN precios_producto_carga c
    ON c.id = b.precios_producto_carga_id

/* categoria de precio de la linea */
JOIN categoria_precios cp
    ON cp.id = c.categoria_precios_id

/* escala activa de comision para rol Asesor Comercial (rol_id=2) */
JOIN comision_escala ce
    ON ce.rol_id                    = 2
   AND ce.estado_id                 = 1
   AND ce.cliente_categoria_escala_id = (
       SELECT COALESCE(cl2.cliente_categoria_escala_id,0)
       FROM cliente cl2
       WHERE cl2.id = f.cliente_id
   )
   AND ce.categoria_precios_id      = cp.id

/* producto */
LEFT JOIN producto p
    ON p.id = vhp.producto_id

/* cliente */
LEFT JOIN cliente cl
    ON cl.id = f.cliente_id

LEFT JOIN cliente_categoria_escala cce
    ON cce.id = cl.cliente_categoria_escala_id

/* vendedor */
LEFT JOIN users u
    ON u.id = f.vendedor

LEFT JOIN rol r
    ON r.id = 2

WHERE f.cai IN (
'000-001-01-00042948',
'000-001-01-00042924',
'000-001-01-00042917',
'000-001-01-00042848',
'000-001-01-00042830',
'000-001-01-00042825',
'000-001-01-00042775',
'000-001-01-00042716',
'000-001-01-00042643',
'000-001-01-00042605',
'000-001-01-00042575',
'000-001-01-00042551',
'000-001-01-00042529',
'000-001-01-00042457',
'000-001-01-00042456',
'000-001-01-00042325',
'000-001-01-00042307',
'000-001-01-00042111',
'000-001-01-00042279',
'000-001-01-00042755'
)

ORDER BY f.cai, p.nombre;


/* ── TOTALES POR CATEGORIA DE PRECIO ─────────────────────────────── */
SELECT
    cce.nombre_categoria        AS escala_cliente,
    cp.nombre                   AS categoria_precio,
    ce.porcentaje_comision      AS pct,
    ROUND(SUM(b.cantidad * vhp.precio_unidad), 4) AS base_total_precio_unitario,
    ROUND(SUM(b.cantidad * COALESCE(NULLIF(vhp.precioSeleccionado,0), vhp.precio_unidad)), 4) AS base_total,
    ROUND(SUM(
        (ce.porcentaje_comision/100)
        * COALESCE(NULLIF(vhp.precioSeleccionado,0), vhp.precio_unidad)
        * b.cantidad
    ), 4)                       AS comision_total_categoria
FROM factura f
JOIN venta_has_producto vhp ON vhp.factura_id = f.id
JOIN venta_has_producto b   ON b.factura_id = f.id AND b.producto_id = vhp.producto_id AND b.lote = vhp.lote
JOIN precios_producto_carga c ON c.id = b.precios_producto_carga_id
JOIN categoria_precios cp    ON cp.id = c.categoria_precios_id
JOIN comision_escala ce
    ON ce.rol_id = 2
   AND ce.estado_id = 1
   AND ce.cliente_categoria_escala_id = (SELECT COALESCE(cl2.cliente_categoria_escala_id,0) FROM cliente cl2 WHERE cl2.id = f.cliente_id)
   AND ce.categoria_precios_id = cp.id
LEFT JOIN cliente cl             ON cl.id = f.cliente_id
LEFT JOIN cliente_categoria_escala cce ON cce.id = cl.cliente_categoria_escala_id
WHERE f.cai IN (
    '000-001-01-00042948',
    '000-001-01-00042924',
    '000-001-01-00042917',
    '000-001-01-00042907',
    '000-001-01-00042899',
    '000-001-01-00042848',
    '000-001-01-00042846',
    '000-001-01-00042845',
    '000-001-01-00042830',
    '000-001-01-00042825',
    '000-001-01-00042775',
    '000-001-01-00042764',
    '000-001-01-00042716',
    '000-001-01-00042643',
    '000-001-01-00042605',
    '000-001-01-00042575',
    '000-001-01-00042551',
    '000-001-01-00042529',
    '000-001-01-00042457',
    '000-001-01-00042456',
    '000-001-01-00042325',
    '000-001-01-00042307',
    '000-001-01-00042111',
    '000-001-01-00042279',
    '000-001-01-00042755'
)
GROUP BY cce.nombre_categoria, cp.nombre, ce.porcentaje_comision
ORDER BY cp.nombre;


/* ── GRAN TOTAL COMISION ASESOR COMERCIAL ────────────────────────── */
SELECT
    'Asesor Comercial' AS rol,
    COUNT(DISTINCT vhp.producto_id) AS productos,
    ROUND(SUM(b.cantidad * vhp.precio_unidad), 4) AS base_comisionable_total_precio_unitario,
    ROUND(SUM(b.cantidad * COALESCE(NULLIF(vhp.precioSeleccionado,0), vhp.precio_unidad)), 4) AS base_comisionable_total,
    ROUND(SUM(
        (ce.porcentaje_comision/100)
        * COALESCE(NULLIF(vhp.precioSeleccionado,0), vhp.precio_unidad)
        * b.cantidad
    ), 4)               AS comision_total_proyectada
FROM factura f
JOIN venta_has_producto vhp ON vhp.factura_id = f.id
JOIN venta_has_producto b   ON b.factura_id = f.id AND b.producto_id = vhp.producto_id AND b.lote = vhp.lote
JOIN precios_producto_carga c ON c.id = b.precios_producto_carga_id
JOIN categoria_precios cp    ON cp.id = c.categoria_precios_id
JOIN comision_escala ce
    ON ce.rol_id = 2
   AND ce.estado_id = 1
   AND ce.cliente_categoria_escala_id = (SELECT COALESCE(cl2.cliente_categoria_escala_id,0) FROM cliente cl2 WHERE cl2.id = f.cliente_id)
   AND ce.categoria_precios_id = cp.id
WHERE f.cai IN (
    '000-001-01-00042948',
    '000-001-01-00042924',
    '000-001-01-00042917',
    '000-001-01-00042907',
    '000-001-01-00042899',
    '000-001-01-00042848',
    '000-001-01-00042846',
    '000-001-01-00042845',
    '000-001-01-00042830',
    '000-001-01-00042825',
    '000-001-01-00042775',
    '000-001-01-00042764',
    '000-001-01-00042716',
    '000-001-01-00042643',
    '000-001-01-00042605',
    '000-001-01-00042575',
    '000-001-01-00042551',
    '000-001-01-00042529',
    '000-001-01-00042457',
    '000-001-01-00042456',
    '000-001-01-00042325',
    '000-001-01-00042307',
    '000-001-01-00042111',
    '000-001-01-00042279',
    '000-001-01-00042755'
);


/* ── CONTROL: SUB_TOTAL VS SUMA(CANTIDAD*PRECIO_UNITARIO) ───────── */
SELECT
    f.cai AS factura,
    ROUND(f.sub_total, 4) AS sub_total_factura,
    ROUND(SUM(vhp.cantidad * vhp.precio_unidad), 4) AS base_unitario_sumada,
    ROUND(ROUND(f.sub_total, 4) - ROUND(SUM(vhp.cantidad * vhp.precio_unidad), 4), 4) AS diferencia
FROM factura f
JOIN venta_has_producto vhp ON vhp.factura_id = f.id
WHERE f.cai IN (
    '000-001-01-00042948',
    '000-001-01-00042924',
    '000-001-01-00042917',
    '000-001-01-00042848',
    '000-001-01-00042830',
    '000-001-01-00042825',
    '000-001-01-00042775',
    '000-001-01-00042716',
    '000-001-01-00042643',
    '000-001-01-00042605',
    '000-001-01-00042575',
    '000-001-01-00042551',
    '000-001-01-00042529',
    '000-001-01-00042457',
    '000-001-01-00042456',
    '000-001-01-00042325',
    '000-001-01-00042307',
    '000-001-01-00042111',
    '000-001-01-00042279',
    '000-001-01-00042755'
)
GROUP BY f.id, f.cai, f.sub_total
ORDER BY f.cai;


/* ── CONTROL GLOBAL: TOTAL SUB_TOTAL VS TOTAL BASE UNITARIO ─────── */
SELECT
    ROUND(SUM(t.sub_total_factura), 4) AS total_sub_total,
    ROUND(SUM(t.base_unitario_sumada), 4) AS total_base_unitario,
    ROUND(SUM(t.sub_total_factura) - SUM(t.base_unitario_sumada), 4) AS diferencia_total
FROM (
    SELECT
        f.id,
        ROUND(f.sub_total, 4) AS sub_total_factura,
        ROUND(SUM(vhp.cantidad * vhp.precio_unidad), 4) AS base_unitario_sumada
    FROM factura f
    JOIN venta_has_producto vhp ON vhp.factura_id = f.id
    WHERE f.cai IN (
        '000-001-01-00042948',
        '000-001-01-00042924',
        '000-001-01-00042917',
        '000-001-01-00042848',
        '000-001-01-00042830',
        '000-001-01-00042825',
        '000-001-01-00042775',
        '000-001-01-00042716',
        '000-001-01-00042643',
        '000-001-01-00042605',
        '000-001-01-00042575',
        '000-001-01-00042551',
        '000-001-01-00042529',
        '000-001-01-00042457',
        '000-001-01-00042456',
        '000-001-01-00042325',
        '000-001-01-00042307',
        '000-001-01-00042111',
        '000-001-01-00042279',
        '000-001-01-00042755'
    )
    GROUP BY f.id, f.sub_total
) t;
