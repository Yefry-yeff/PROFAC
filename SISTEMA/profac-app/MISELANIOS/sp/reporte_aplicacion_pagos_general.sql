-- ============================================================
--  REPORTE GENERAL: TODA LA ACTIVIDAD EN APLICACIÓN DE PAGOS
--                   TODOS LOS USUARIOS / TODOS LOS ROLES
--  Fecha generación : 2026-05-21
--  Módulo           : CuentasPorCobrar / AplicacionPagos
-- ============================================================
--  Acciones incluidas:
--    1. Abono / Pago            → abonos_creditos
--    2. Otro Movimiento (Suma)  → otros_movimientos  tipo=1
--    3. Otro Movimiento (Resta) → otros_movimientos  tipo=2
--    4. Aplicación Nota Crédito → nota_credito
--    5. Aplicación Nota Débito  → notadebito
--    6. Gestión Retención ISV   → aplicacion_pagos  (retencion_aplicada=1)
--    7. Cierre de Cuenta        → aplicacion_pagos  (estado_cerrado=2)
-- ============================================================
-- FILTROS OPCIONALES:
--   • Por rango de fechas : descomenta las líneas HAVING / AND con fecha_accion
--   • Por usuario         : añade AND actividad.usuario_id = <id>
--   • Por cliente         : añade AND actividad.cliente_id  = <id>
-- ============================================================

SELECT
    actividad.accion,
    actividad.fecha_accion,
    actividad.usuario_id,
    actividad.usuario_nombre,
    r.nombre                        AS rol,
    actividad.cliente_id,
    cli.nombre                      AS cliente_nombre,
    actividad.factura_id,
    fa.cai                          AS correlativo_factura,
    fa.numero_factura,
    actividad.monto,
    actividad.comentario,
    actividad.detalle_extra

FROM (

    /* ── 1. ABONOS / PAGOS ─────────────────────────────────── */
    SELECT
        CONVERT('Abono / Pago'    USING utf8mb4) COLLATE utf8mb4_general_ci AS accion,
        ac.created_at                                                        AS fecha_accion,
        ac.usr_registro                                                      AS usuario_id,
        CONVERT(u.name            USING utf8mb4) COLLATE utf8mb4_general_ci AS usuario_nombre,
        u.rol_id,
        (SELECT fa2.cliente_id FROM factura fa2 WHERE fa2.id = ac.factura_id) AS cliente_id,
        ac.factura_id,
        ac.monto_abonado                                                     AS monto,
        CONVERT(ac.comentario     USING utf8mb4) COLLATE utf8mb4_general_ci AS comentario,
        CONCAT(
            'Recibo: ',     IFNULL(CONVERT(ac.numero_recibo USING utf8mb4) COLLATE utf8mb4_general_ci, 'N/A'),
            ' | Banco ID: ',IFNULL(CAST(ac.banco_id   AS CHAR), 'N/A'),
            ' | Fecha pago: ',IFNULL(CAST(ac.fecha_pago AS CHAR), 'N/A')
        ) COLLATE utf8mb4_general_ci                                         AS detalle_extra
    FROM abonos_creditos ac
    INNER JOIN users u ON u.id = ac.usr_registro
    WHERE ac.estado_abono = 1

    UNION ALL

    /* ── 2. OTROS MOVIMIENTOS – SUMA ───────────────────────── */
    SELECT
        CONVERT('Otro Movimiento (Suma)'            USING utf8mb4) COLLATE utf8mb4_general_ci AS accion,
        om.created_at                                                                          AS fecha_accion,
        om.usr_registro                                                                        AS usuario_id,
        CONVERT(u.name                              USING utf8mb4) COLLATE utf8mb4_general_ci AS usuario_nombre,
        u.rol_id,
        (SELECT fa2.cliente_id FROM factura fa2 WHERE fa2.id = om.factura_id)                 AS cliente_id,
        om.factura_id,
        om.monto                                                                               AS monto,
        CONVERT(om.comentario                       USING utf8mb4) COLLATE utf8mb4_general_ci AS comentario,
        CONVERT('Tipo movimiento: 1 (Suma al saldo)'USING utf8mb4) COLLATE utf8mb4_general_ci AS detalle_extra
    FROM otros_movimientos om
    INNER JOIN users u ON u.id = om.usr_registro
    WHERE om.estado = 1
      AND om.tipo_movimiento = 1

    UNION ALL

    /* ── 3. OTROS MOVIMIENTOS – RESTA ──────────────────────── */
    SELECT
        CONVERT('Otro Movimiento (Resta)'              USING utf8mb4) COLLATE utf8mb4_general_ci AS accion,
        om.created_at                                                                             AS fecha_accion,
        om.usr_registro                                                                           AS usuario_id,
        CONVERT(u.name                                 USING utf8mb4) COLLATE utf8mb4_general_ci AS usuario_nombre,
        u.rol_id,
        (SELECT fa2.cliente_id FROM factura fa2 WHERE fa2.id = om.factura_id)                    AS cliente_id,
        om.factura_id,
        om.monto                                                                                  AS monto,
        CONVERT(om.comentario                          USING utf8mb4) COLLATE utf8mb4_general_ci AS comentario,
        CONVERT('Tipo movimiento: 2 (Resta al saldo)'  USING utf8mb4) COLLATE utf8mb4_general_ci AS detalle_extra
    FROM otros_movimientos om
    INNER JOIN users u ON u.id = om.usr_registro
    WHERE om.estado = 1
      AND om.tipo_movimiento = 2

    UNION ALL

    /* ── 4. APLICACIÓN DE NOTA DE CRÉDITO ─────────────────── */
    SELECT
        CONVERT('Aplicacion Nota Credito' USING utf8mb4) COLLATE utf8mb4_general_ci AS accion,
        nc.fecha_rebajado                                                             AS fecha_accion,
        nc.user_registra_rebaja                                                       AS usuario_id,
        CONVERT(u.name                    USING utf8mb4) COLLATE utf8mb4_general_ci AS usuario_nombre,
        u.rol_id,
        (SELECT fa2.cliente_id FROM factura fa2 WHERE fa2.id = nc.factura_id)        AS cliente_id,
        nc.factura_id,
        nc.total                                                                      AS monto,
        CONVERT(nc.comentario_rebajado    USING utf8mb4) COLLATE utf8mb4_general_ci AS comentario,
        CONCAT('CAI NC: ', CONVERT(nc.cai USING utf8mb4) COLLATE utf8mb4_general_ci) COLLATE utf8mb4_general_ci AS detalle_extra
    FROM nota_credito nc
    INNER JOIN users u ON u.id = nc.user_registra_rebaja
    WHERE nc.estado_rebajado = 1

    UNION ALL

    /* ── 5. APLICACIÓN DE NOTA DE DÉBITO ──────────────────── */
    SELECT
        CONVERT('Aplicacion Nota Debito'   USING utf8mb4) COLLATE utf8mb4_general_ci AS accion,
        nd.fecha_sumado                                                                AS fecha_accion,
        nd.user_registra_sumado                                                        AS usuario_id,
        CONVERT(u.name                     USING utf8mb4) COLLATE utf8mb4_general_ci AS usuario_nombre,
        u.rol_id,
        (SELECT fa2.cliente_id FROM factura fa2 WHERE fa2.id = nd.factura_id)         AS cliente_id,
        nd.factura_id,
        nd.monto_asignado                                                              AS monto,
        CONVERT(nd.comentario_sumado       USING utf8mb4) COLLATE utf8mb4_general_ci AS comentario,
        CONCAT('CAI ND: ', CONVERT(nd.numeroCai USING utf8mb4) COLLATE utf8mb4_general_ci) COLLATE utf8mb4_general_ci AS detalle_extra
    FROM notadebito nd
    INNER JOIN users u ON u.id = nd.user_registra_sumado
    WHERE nd.estado_sumado = 1

    UNION ALL

    /* ── 6. GESTIÓN DE RETENCIÓN ISV ───────────────────────── */
    SELECT
        CONVERT('Gestion Retencion ISV'       USING utf8mb4) COLLATE utf8mb4_general_ci AS accion,
        ap.updated_at                                                                     AS fecha_accion,
        ap.ultimo_usr_actualizo                                                           AS usuario_id,
        CONVERT(u.name                        USING utf8mb4) COLLATE utf8mb4_general_ci AS usuario_nombre,
        u.rol_id,
        ap.cliente_id,
        ap.factura_id,
        ap.retencion_isv_factura                                                          AS monto,
        CONVERT(ap.comentario_retencion       USING utf8mb4) COLLATE utf8mb4_general_ci AS comentario,
        CONCAT(
            'Tipo retencion: ', CAST(ap.estado_retencion_isv AS CHAR),
            ' | Saldo resultante: ', FORMAT(ap.saldo, 2)
        ) COLLATE utf8mb4_general_ci                                                      AS detalle_extra
    FROM aplicacion_pagos ap
    INNER JOIN users u ON u.id = ap.ultimo_usr_actualizo
    WHERE ap.retencion_aplicada = 1
      AND ap.ultimo_usr_actualizo <> 0

    UNION ALL

    /* ── 7. CIERRE DE CUENTA (SALDO EN CERO) ──────────────── */
    SELECT
        CONVERT('Cierre de Cuenta'  USING utf8mb4) COLLATE utf8mb4_general_ci AS accion,
        ap.updated_at                                                           AS fecha_accion,
        ap.usr_cerro                                                            AS usuario_id,
        CONVERT(u.name              USING utf8mb4) COLLATE utf8mb4_general_ci AS usuario_nombre,
        u.rol_id,
        ap.cliente_id,
        ap.factura_id,
        ap.saldo                                                                AS monto,
        CONVERT(ap.comentario       USING utf8mb4) COLLATE utf8mb4_general_ci AS comentario,
        CONCAT('ID aplicacion_pagos: ', CAST(ap.id AS CHAR)) COLLATE utf8mb4_general_ci AS detalle_extra
    FROM aplicacion_pagos ap
    INNER JOIN users u ON u.id = ap.usr_cerro
    WHERE ap.estado_cerrado = 2
      AND ap.usr_cerro <> 0

) AS actividad

INNER JOIN factura   fa  ON fa.id  = actividad.factura_id
INNER JOIN cliente   cli ON cli.id = actividad.cliente_id
INNER JOIN rol       r   ON r.id   = actividad.rol_id

ORDER BY actividad.fecha_accion DESC;
