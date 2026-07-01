-- ============================================================
--  sp_calcular_intereses_factura
--  Punto único de cálculo de intereses por mora.
--
--  Reglas:
--    - Solo aplica a facturas con estado_venta_id = 1 (activa).
--    - Solo aplica si la fecha de vencimiento ya pasó.
--    - Solo aplica si el saldo es > 0.
--    - Obtiene la configuración vigente más reciente.
--    - Fórmula: saldo * (tasa_mensual/100) * (dias_vencidos/30)
--    - Es idempotente: NO persiste ningún registro.
--
--  Parámetros:
--    p_factura_id     INT  -- ID de la factura a consultar
--    p_fecha_calculo  DATE -- Fecha base de cálculo (NULL = CURDATE())
--
--  Resultado (una fila):
--    factura_id, fecha_vencimiento, capital_base,
--    porcentaje_mensual, dias_vencidos, monto_interes,
--    aplica (1/0), configuracion_id
-- ============================================================

DROP PROCEDURE IF EXISTS `sp_calcular_intereses_factura`;
DELIMITER $$

CREATE PROCEDURE `sp_calcular_intereses_factura`(
    IN p_factura_id    INT,
    IN p_fecha_calculo DATE
)
BEGIN
    DECLARE v_fecha              DATE;
    DECLARE v_fecha_vencimiento  DATE;
    DECLARE v_estado_venta       INT      DEFAULT 0;
    DECLARE v_saldo              DECIMAL(15,2) DEFAULT 0.00;
    DECLARE v_tasa_mensual       DECIMAL(8,4)  DEFAULT 0.0000;
    DECLARE v_configuracion_id   BIGINT   DEFAULT NULL;
    DECLARE v_dias_vencidos      INT      DEFAULT 0;
    DECLARE v_monto_interes      DECIMAL(15,2) DEFAULT 0.00;
    DECLARE v_aplica             TINYINT  DEFAULT 0;

    -- Fecha base: usa la suministrada, o hoy si es NULL
    SET v_fecha = IFNULL(p_fecha_calculo, CURDATE());

    -- ── Datos de la factura ────────────────────────────────────────────────
    SELECT fecha_vencimiento, estado_venta_id
    INTO   v_fecha_vencimiento, v_estado_venta
    FROM   factura
    WHERE  id = p_factura_id
    LIMIT  1;

    -- ── Saldo vigente ──────────────────────────────────────────────────────
    SELECT IFNULL(saldo, 0)
    INTO   v_saldo
    FROM   aplicacion_pagos
    WHERE  factura_id = p_factura_id
      AND  estado = 1
    ORDER  BY id DESC
    LIMIT  1;

    -- ── Configuración vigente más reciente ────────────────────────────────
    SELECT id, tasa_mensual
    INTO   v_configuracion_id, v_tasa_mensual
    FROM   configuracion_intereses
    WHERE  estado = 1
      AND  fecha_vigencia <= v_fecha
    ORDER  BY fecha_vigencia DESC
    LIMIT  1;

    -- ── Días vencidos ──────────────────────────────────────────────────────
    SET v_dias_vencidos = DATEDIFF(v_fecha, v_fecha_vencimiento);

    -- ── Condiciones para aplicar interés ──────────────────────────────────
    --   1. Factura activa
    --   2. Fecha de vencimiento superada
    --   3. Saldo mayor a cero
    --   4. Existe configuración vigente
    IF  v_estado_venta     = 1
    AND v_dias_vencidos    > 0
    AND v_saldo            > 0.00
    AND v_configuracion_id IS NOT NULL
    THEN
        SET v_aplica        = 1;
        SET v_monto_interes = ROUND(
            v_saldo * (v_tasa_mensual / 100.0) * (v_dias_vencidos / 30.0),
            2
        );
    END IF;

    -- ── Resultado ──────────────────────────────────────────────────────────
    SELECT
        p_factura_id        AS factura_id,
        v_fecha_vencimiento AS fecha_vencimiento,
        v_saldo             AS capital_base,
        v_tasa_mensual      AS porcentaje_mensual,
        v_dias_vencidos     AS dias_vencidos,
        v_monto_interes     AS monto_interes,
        v_aplica            AS aplica,
        v_configuracion_id  AS configuracion_id;
END$$

DELIMITER ;
