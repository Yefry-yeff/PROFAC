-- Ajuste final de la factura 000-001-01-00044071 (factura.id = 29155).
-- Objetivo: 66,060.00 gravado + 24,031.00 exento = 90,091.00 subtotal.
-- Relacion fiscal exacta: 66,060.00 * 0.15 = 9,909.00.
-- Parte del estado original confirmado en produccion. Traslada L 2.83 de la
-- base exenta (producto 3217) a la gravada (producto 3154) y corrige L 0.03
-- de redondeo distribuidos en los productos 4372, 4390 y 4863.

-- DIAGNOSTICO DE PRODUCCION (solo lectura):
-- SELECT id, cai_id, numero_secuencia_cai, sub_total_grabado,
--        sub_total_excento, sub_total, isv, total
-- FROM factura
-- WHERE numero_secuencia_cai = 44071 OR cai = '000-001-01-00044071';

DELIMITER $$

DROP PROCEDURE IF EXISTS sp_ajustar_factura_000_001_01_00044071$$

CREATE PROCEDURE sp_ajustar_factura_000_001_01_00044071()
BEGIN
    DECLARE v_conteo INT DEFAULT 0;
    DECLARE v_gravado DECIMAL(60,2) DEFAULT 0;
    DECLARE v_exento DECIMAL(60,2) DEFAULT 0;
    DECLARE v_subtotal DECIMAL(60,2) DEFAULT 0;
    DECLARE v_isv DECIMAL(60,2) DEFAULT 0;
    DECLARE v_total DECIMAL(60,2) DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT COUNT(*) INTO v_conteo
    FROM factura
    WHERE id = 29155
      AND cai_id = 18
      AND numero_secuencia_cai = 44071
    AND sub_total_grabado = 66057.18
      AND sub_total_excento = 24033.83
    AND sub_total = 90091.01
    AND isv = 9908.67
      AND total = 100000.00;

    IF v_conteo <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Factura no encontrada, ya ajustada o con valores distintos a la auditoria.';
    END IF;

    SELECT COUNT(*) INTO v_conteo
    FROM cotizacion_has_producto
    WHERE cotizacion_id = 39637
      AND (
          (id = 280716 AND indice = 1 AND producto_id = 3154
              AND precio_unidad = 61.41 AND sub_total = 22414.65
              AND isv = 3362.20 AND total = 25776.85)
          OR
          (id = 280718 AND indice = 19 AND producto_id = 3217
              AND precio_unidad = 294.43 AND sub_total = 18843.52
              AND isv = 0.00 AND total = 18843.52)
          OR
          (id = 280722 AND indice = 14 AND producto_id = 4372
              AND sub_total = 403.30 AND isv = 60.49 AND total = 463.79)
          OR
          (id = 280725 AND indice = 26 AND producto_id = 4390
              AND sub_total = 945.50 AND isv = 141.82 AND total = 1087.33)
          OR
          (id = 280729 AND indice = 32 AND producto_id = 4863
              AND sub_total = 3518.50 AND isv = 527.77 AND total = 4046.28)
      );

    IF v_conteo <> 5 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Las lineas de la cotizacion no coinciden con el ajuste anterior.';
    END IF;

    SELECT COUNT(*) INTO v_conteo
    FROM prefactura_has_producto
    WHERE prefactura_id = 1961
      AND (
          (indice = 1 AND producto_id = 3154
              AND sub_total = 22414.6500 AND isv = 3362.2000 AND total = 25776.8500)
          OR
          (indice = 19 AND producto_id = 3217
              AND sub_total = 18843.5200 AND isv = 0.0000 AND total = 18843.5200)
          OR
          (indice = 14 AND producto_id = 4372
              AND sub_total = 403.3000 AND isv = 60.4900 AND total = 463.7900)
          OR
          (indice = 26 AND producto_id = 4390
              AND sub_total = 945.5000 AND isv = 141.8200 AND total = 1087.3300)
          OR
          (indice = 32 AND producto_id = 4863
              AND sub_total = 3518.5000 AND isv = 527.7700 AND total = 4046.2800)
      );

    IF v_conteo <> 5 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Las lineas de la prefactura no coinciden con el ajuste anterior.';
    END IF;

    SELECT COUNT(*) INTO v_conteo
    FROM venta_has_producto
    WHERE factura_id = 29155
      AND (
          (indice = 1 AND producto_id = 3154 AND lote = 37265
              AND sub_total_s = 22414.65 AND isv_s = 3362.20 AND total_s = 25776.85)
          OR
          (indice = 19 AND producto_id = 3217 AND lote = 28153
              AND sub_total_s = 18843.52 AND isv_s = 0.00 AND total_s = 18843.52)
      );

    IF v_conteo <> 2 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Las lineas de la factura no coinciden con el ajuste anterior.';
    END IF;

        -- Corrige los tres centavos de redondeo de ISV en las lineas originales.
        UPDATE cotizacion_has_producto
        SET isv = 60.50, total = 463.80, updated_at = NOW()
        WHERE id = 280722 AND cotizacion_id = 39637
            AND producto_id = 4372 AND indice = 14;

        UPDATE prefactura_has_producto
        SET isv = 60.5000, total = 463.8000, updated_at = NOW()
        WHERE prefactura_id = 1961 AND producto_id = 4372 AND indice = 14;

        UPDATE venta_has_producto
        SET isv = 60.50, total = 463.80, updated_at = NOW()
        WHERE factura_id = 29155 AND producto_id = 4372 AND indice = 14;

        UPDATE cotizacion_has_producto
        SET isv = 141.83, updated_at = NOW()
        WHERE id = 280725 AND cotizacion_id = 39637
            AND producto_id = 4390 AND indice = 26;

        UPDATE prefactura_has_producto
        SET isv = 141.8300, updated_at = NOW()
        WHERE prefactura_id = 1961 AND producto_id = 4390 AND indice = 26;

        UPDATE venta_has_producto
        SET isv = 141.83, updated_at = NOW()
        WHERE factura_id = 29155 AND producto_id = 4390 AND indice = 26;

        UPDATE cotizacion_has_producto
        SET isv = 527.78, updated_at = NOW()
        WHERE id = 280729 AND cotizacion_id = 39637
            AND producto_id = 4863 AND indice = 32;

        UPDATE prefactura_has_producto
        SET isv = 527.7800, updated_at = NOW()
        WHERE prefactura_id = 1961 AND producto_id = 4863 AND indice = 32;

        UPDATE venta_has_producto
        SET isv = 527.78, updated_at = NOW()
        WHERE factura_id = 29155 AND producto_id = 4863 AND indice = 32;

        -- Suma L 2.82 netos a la base gravada y completa el ISV a L 9,909.00.
    UPDATE cotizacion_has_producto
        SET sub_total = 22417.47, isv = 3362.59, total = 25780.06, updated_at = NOW()
    WHERE id = 280716 AND cotizacion_id = 39637
      AND producto_id = 3154 AND indice = 1;

    UPDATE prefactura_has_producto
    SET sub_total = 22417.4700, isv = 3362.5900, total = 25780.0600, updated_at = NOW()
    WHERE prefactura_id = 1961 AND producto_id = 3154 AND indice = 1;

    UPDATE venta_has_producto
    SET sub_total = 22417.47,
        isv = 3362.59,
        total = 25780.06,
        sub_total_s = 22417.47,
        isv_s = 3362.59,
        total_s = 25780.06,
        updated_at = NOW()
    WHERE factura_id = 29155 AND producto_id = 3154
      AND indice = 1 AND lote = 37265;

    -- Resta los mismos L 2.83 de la base exenta.
    UPDATE cotizacion_has_producto
    SET sub_total = 18840.69, total = 18840.69, updated_at = NOW()
    WHERE id = 280718 AND cotizacion_id = 39637
      AND producto_id = 3217 AND indice = 19;

    UPDATE prefactura_has_producto
    SET sub_total = 18840.6900, total = 18840.6900, updated_at = NOW()
    WHERE prefactura_id = 1961 AND producto_id = 3217 AND indice = 19;

    UPDATE venta_has_producto
    SET sub_total = 18840.69,
        total = 18840.69,
        sub_total_s = 18840.69,
        total_s = 18840.69,
        updated_at = NOW()
    WHERE factura_id = 29155 AND producto_id = 3217
      AND indice = 19 AND lote = 28153;

    UPDATE cotizacion
    SET sub_total_grabado = 66060.00,
        sub_total_excento = 24031.00,
        sub_total = 90091.00,
        isv = 9909.00,
        total = 100000.00,
        updated_at = NOW()
    WHERE id = 39637;

    UPDATE prefactura
    SET sub_total_grabado = 66060.0000,
        sub_total_excento = 24031.0000,
        sub_total = 90091.0000,
        isv = 9909.0000,
        total = 100000.0000,
        updated_at = NOW()
    WHERE id = 1961;

    UPDATE factura
    SET sub_total_grabado = 66060.00,
        sub_total_excento = 24031.00,
        sub_total = 90091.00,
        isv = 9909.00,
        total = 100000.00,
        updated_at = NOW()
    WHERE id = 29155;

    UPDATE aplicacion_pagos
    SET total_factura_cargo = 100000.00,
        retencion_isv_factura = 9909.00,
        saldo = 100000.00,
        updated_at = NOW()
    WHERE factura_id = 29155 AND estado = 1;

    -- Valida el detalle que usa el PDF para mostrar gravado y exento.
    SELECT
        ROUND(SUM(CASE WHEN isv_s <> 0 THEN sub_total_s ELSE 0 END), 2),
        ROUND(SUM(CASE WHEN isv_s = 0 THEN sub_total_s ELSE 0 END), 2),
        ROUND(SUM(sub_total_s), 2),
        ROUND(SUM(isv_s), 2),
        ROUND(SUM(total_s), 2)
    INTO v_gravado, v_exento, v_subtotal, v_isv, v_total
    FROM venta_has_producto
    WHERE factura_id = 29155;

    IF v_gravado <> 66060.00
       OR v_exento <> 24031.00
       OR v_subtotal <> 90091.00
       OR v_isv <> 9909.00
       OR v_total <> 100000.00 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El detalle de factura no cuadra; se revirtio la transaccion.';
    END IF;

    IF ROUND(v_gravado * 0.15, 2) <> v_isv THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El gravado por 15% no coincide con el ISV; se revirtio la transaccion.';
    END IF;

    -- Valida tambien cotizacion y prefactura.
    SELECT
        ROUND(SUM(CASE WHEN isv_producto <> 0 THEN sub_total ELSE 0 END), 2),
        ROUND(SUM(CASE WHEN isv_producto = 0 THEN sub_total ELSE 0 END), 2),
        ROUND(SUM(sub_total), 2),
        ROUND(SUM(isv), 2),
        ROUND(SUM(total), 2)
    INTO v_gravado, v_exento, v_subtotal, v_isv, v_total
    FROM cotizacion_has_producto
    WHERE cotizacion_id = 39637;

    IF v_gravado <> 66060.00 OR v_exento <> 24031.00
       OR v_subtotal <> 90091.00 OR v_isv <> 9909.00 OR v_total <> 100000.00 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La cotizacion no cuadra; se revirtio la transaccion.';
    END IF;

    SELECT
        ROUND(SUM(CASE WHEN isv_producto <> 0 THEN sub_total ELSE 0 END), 2),
        ROUND(SUM(CASE WHEN isv_producto = 0 THEN sub_total ELSE 0 END), 2),
        ROUND(SUM(sub_total), 2),
        ROUND(SUM(isv), 2),
        ROUND(SUM(total), 2)
    INTO v_gravado, v_exento, v_subtotal, v_isv, v_total
    FROM prefactura_has_producto
    WHERE prefactura_id = 1961;

    IF v_gravado <> 66060.00 OR v_exento <> 24031.00
       OR v_subtotal <> 90091.00 OR v_isv <> 9909.00 OR v_total <> 100000.00 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La prefactura no cuadra; se revirtio la transaccion.';
    END IF;

    COMMIT;

    SELECT
        f.id AS factura_id,
        f.numero_secuencia_cai,
        f.sub_total_grabado,
        f.sub_total_excento,
        f.sub_total,
        f.isv,
        f.total,
        ROUND(f.sub_total_grabado * 0.15, 2) AS isv_calculado
    FROM factura AS f
    WHERE f.id = 29155;
END$$

DELIMITER ;

CALL sp_ajustar_factura_000_001_01_00044071();
DROP PROCEDURE IF EXISTS sp_ajustar_factura_000_001_01_00044071;
