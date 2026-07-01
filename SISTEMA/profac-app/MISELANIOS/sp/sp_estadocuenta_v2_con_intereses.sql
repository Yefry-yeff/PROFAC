-- ============================================================
--  estadoCuenta_sp  — v2 con intereses por mora
--
--  Cambios respecto a la versión anterior:
--    - Columna "interes" calculada mediante JOIN con
--      configuracion_intereses (misma lógica que
--      sp_calcular_intereses_factura — punto único de verdad).
--    - Acumulado = saldo + interés de cada fila.
--    - Se respeta el filtro original: saldo > 0 y estado activo.
-- ============================================================

DROP PROCEDURE IF EXISTS `estadoCuenta_sp`;
DELIMITER $$

CREATE PROCEDURE `estadoCuenta_sp`(IN `idcliente` INT)
BEGIN

    SET @acumulado = 0;

    SELECT
        factura.nombre_cliente                                        AS 'cliente',
        factura.numero_factura                                        AS 'numero_factura',
        factura.numero_factura                                        AS 'documento',
        factura.cai                                                   AS 'correlativo',
        factura.fecha_emision                                         AS 'fecha_emision',
        factura.fecha_vencimiento                                     AS 'fecha_vencimiento',

        (
            IF(
                (SELECT COUNT(*) FROM numero_orden_compra WHERE id = factura.numero_orden_compra_id) = 0,
                'N/A',
                (SELECT numero_orden FROM numero_orden_compra WHERE id = factura.numero_orden_compra_id)
            )
        )                                                             AS 'numOrden',

        aplicacion_pagos.total_factura_cargo                          AS 'cargo',
        aplicacion_pagos.credito_abonos                               AS 'credito',
        aplicacion_pagos.total_notas_credito                          AS 'notaCredito',
        aplicacion_pagos.total_nodas_debito                           AS 'notaDebito',
        aplicacion_pagos.movimiento_suma                              AS 'extra',
        aplicacion_pagos.movimiento_resta                             AS 'debita',
        aplicacion_pagos.saldo                                        AS 'saldo',

        -- ── Interés por mora (cálculo inline — misma fórmula que sp_calcular_intereses_factura) ──
        IF(
            factura.estado_venta_id = 1
            AND aplicacion_pagos.saldo > 0
            AND DATEDIFF(CURDATE(), factura.fecha_vencimiento) > 0
            AND ci_activa.id IS NOT NULL,
            ROUND(
                aplicacion_pagos.saldo
                * (ci_activa.tasa_mensual / 100.0)
                * (DATEDIFF(CURDATE(), factura.fecha_vencimiento) / 30.0),
                2
            ),
            0.00
        )                                                             AS 'interes',

        DATEDIFF(CURDATE(), factura.fecha_vencimiento)                AS 'dias_vencidos',
        IFNULL(ci_activa.tasa_mensual, 0)                             AS 'tasa_mensual',
        ci_activa.id                                                  AS 'configuracion_interes_id',

        -- ── Acumulado = saldo + interés ───────────────────────────────────
        @acumulado := @acumulado + aplicacion_pagos.saldo + IF(
            factura.estado_venta_id = 1
            AND aplicacion_pagos.saldo > 0
            AND DATEDIFF(CURDATE(), factura.fecha_vencimiento) > 0
            AND ci_activa.id IS NOT NULL,
            ROUND(
                aplicacion_pagos.saldo
                * (ci_activa.tasa_mensual / 100.0)
                * (DATEDIFF(CURDATE(), factura.fecha_vencimiento) / 30.0),
                2
            ),
            0.00
        )                                                             AS 'Acumulado'

    FROM aplicacion_pagos
    INNER JOIN factura ON factura.id = aplicacion_pagos.factura_id

    -- JOIN con configuración vigente más reciente (producto cartesiano controlado con LIMIT 1)
    LEFT JOIN (
        SELECT id, tasa_mensual
        FROM   configuracion_intereses
        WHERE  estado = 1
          AND  fecha_vigencia <= CURDATE()
        ORDER  BY fecha_vigencia DESC
        LIMIT  1
    ) ci_activa ON 1 = 1

    WHERE factura.estado_venta_id    = 1
      AND aplicacion_pagos.estado    = 1
      AND aplicacion_pagos.cliente_id = idcliente
      AND aplicacion_pagos.saldo      > 0

    ORDER BY factura.fecha_emision ASC;

END$$

DELIMITER ;
