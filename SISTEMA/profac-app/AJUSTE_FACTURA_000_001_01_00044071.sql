-- Ajuste auditado de la factura 000-001-01-00044071 (factura.id = 29155).
-- Objetivo: subtotal 90,091.35 + ISV 9,908.65 = total 100,000.00.
-- Ejecutar una sola vez en la base de datos correspondiente.

DELIMITER $$

DROP PROCEDURE IF EXISTS sp_ajustar_factura_000_001_01_00044071$$

CREATE PROCEDURE sp_ajustar_factura_000_001_01_00044071()
BEGIN
    DECLARE v_conteo INT DEFAULT 0;
    DECLARE v_subtotal DECIMAL(60,2) DEFAULT 0;
    DECLARE v_isv DECIMAL(60,2) DEFAULT 0;
    DECLARE v_total DECIMAL(60,2) DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- Las validaciones evitan aplicar el ajuste a otra factura o repetirlo.
    SELECT COUNT(*) INTO v_conteo
    FROM factura
    WHERE id = 29155
      AND cai_id = 18
      AND numero_secuencia_cai = 44071
      AND sub_total = 90091.01
      AND isv = 9908.67
      AND total = 100000.00;

    IF v_conteo <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Factura 29155 no encontrada, ya ajustada o con montos distintos a la auditoria.';
    END IF;

    SELECT COUNT(*) INTO v_conteo
    FROM cotizacion
    WHERE id = 39637
      AND sub_total = 90091.01
      AND isv = 9908.58
      AND total = 99999.61;

    IF v_conteo <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La cotizacion 39637 no coincide con los valores auditados.';
    END IF;

    SELECT COUNT(*) INTO v_conteo
    FROM prefactura
    WHERE id = 1961
      AND cotizacion_id = 39637
      AND sub_total = 90091.0100
      AND isv = 9908.6700
      AND total = 100000.0000;

    IF v_conteo <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La prefactura 1961 no coincide con los valores auditados.';
    END IF;

    SELECT COUNT(*) INTO v_conteo
    FROM cotizacion_has_producto
    WHERE cotizacion_id = 39637
      AND (
          (id = 280722 AND indice = 14 AND producto_id = 4372 AND precio_unidad = 40.33 AND sub_total = 403.30 AND isv = 60.49 AND total = 463.79)
          OR (id = 280725 AND indice = 26 AND producto_id = 4390 AND precio_unidad = 15.25 AND sub_total = 945.50 AND isv = 141.82 AND total = 1087.33)
          OR (id = 280729 AND indice = 32 AND producto_id = 4863 AND precio_unidad = 70.37 AND sub_total = 3518.50 AND isv = 527.77 AND total = 4046.28)
          OR (id = 280736 AND indice = 17 AND producto_id = 8112 AND precio_unidad = 17.85 AND sub_total = 856.80 AND isv = 128.52 AND total = 985.32)
      );

    IF v_conteo <> 4 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Las lineas de la cotizacion cambiaron; ajuste cancelado.';
    END IF;

    SELECT COUNT(*) INTO v_conteo
    FROM prefactura_has_producto
    WHERE prefactura_id = 1961
      AND (
          (indice = 14 AND producto_id = 4372 AND precio_unidad = 40.3300 AND sub_total = 403.3000 AND isv = 60.4900 AND total = 463.7900)
          OR (indice = 26 AND producto_id = 4390 AND precio_unidad = 15.2500 AND sub_total = 945.5000 AND isv = 141.8200 AND total = 1087.3300)
          OR (indice = 32 AND producto_id = 4863 AND precio_unidad = 70.3700 AND sub_total = 3518.5000 AND isv = 527.7700 AND total = 4046.2800)
          OR (indice = 17 AND producto_id = 8112 AND precio_unidad = 17.8500 AND sub_total = 856.8000 AND isv = 128.5200 AND total = 985.3200)
      );

    IF v_conteo <> 4 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Las lineas de la prefactura cambiaron; ajuste cancelado.';
    END IF;

    SELECT COUNT(*) INTO v_conteo
    FROM venta_has_producto
    WHERE factura_id = 29155
      AND (
          (indice = 14 AND producto_id = 4372 AND precio_unidad = 40.33 AND sub_total = 403.30 AND isv = 60.49 AND total = 463.79)
          OR (indice = 26 AND producto_id = 4390 AND precio_unidad = 15.25 AND sub_total = 945.50 AND isv = 141.82 AND total = 1087.33)
          OR (indice = 32 AND producto_id = 4863 AND precio_unidad = 70.37 AND sub_total = 3518.50 AND isv = 527.77 AND total = 4046.28)
          OR (indice = 17 AND producto_id = 8112 AND precio_unidad = 17.85 AND sub_total = 856.80 AND isv = 128.52 AND total = 985.32)
      );

    IF v_conteo <> 6 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Los registros por lote de la factura cambiaron; ajuste cancelado.';
    END IF;

    -- Corrige los tres redondeos de ISV detectados en la auditoria.
    UPDATE cotizacion_has_producto
    SET isv = 60.50, total = 463.80, updated_at = NOW()
    WHERE id = 280722 AND cotizacion_id = 39637 AND producto_id = 4372 AND indice = 14;

    UPDATE prefactura_has_producto
    SET isv = 60.5000, total = 463.8000, updated_at = NOW()
    WHERE prefactura_id = 1961 AND producto_id = 4372 AND indice = 14;

    UPDATE venta_has_producto
    SET isv = 60.50, total = 463.80, updated_at = NOW()
    WHERE factura_id = 29155 AND producto_id = 4372 AND indice = 14;

    UPDATE cotizacion_has_producto
    SET isv = 527.78, total = 4046.28, updated_at = NOW()
    WHERE id = 280729 AND cotizacion_id = 39637 AND producto_id = 4863 AND indice = 32;

    UPDATE prefactura_has_producto
    SET isv = 527.7800, total = 4046.2800, updated_at = NOW()
    WHERE prefactura_id = 1961 AND producto_id = 4863 AND indice = 32;

    UPDATE venta_has_producto
    SET isv = 527.78, total = 4046.28, updated_at = NOW()
    WHERE factura_id = 29155 AND producto_id = 4863 AND indice = 32;

    -- Producto 4390: precio 15.25 -> 15.24 (62 unidades).
    UPDATE cotizacion_has_producto
    SET precio_unidad = 15.24, sub_total = 944.88, isv = 141.73, total = 1086.61, updated_at = NOW()
    WHERE id = 280725 AND cotizacion_id = 39637 AND producto_id = 4390 AND indice = 26;

    UPDATE prefactura_has_producto
    SET precio_unidad = 15.2400, sub_total = 944.8800, isv = 141.7300, total = 1086.6100, updated_at = NOW()
    WHERE prefactura_id = 1961 AND producto_id = 4390 AND indice = 26;

    UPDATE venta_has_producto
    SET precio_unidad = 15.24,
        sub_total = 944.88,
        isv = 141.73,
        total = 1086.61,
        sub_total_s = 944.88,
        isv_s = 141.73,
        total_s = 1086.61,
        updated_at = NOW()
    WHERE factura_id = 29155 AND producto_id = 4390 AND indice = 26;

    -- Producto 8112: precio 17.85 -> 17.87 (48 unidades).
    UPDATE cotizacion_has_producto
    SET precio_unidad = 17.87, sub_total = 857.76, isv = 128.66, total = 986.42, updated_at = NOW()
    WHERE id = 280736 AND cotizacion_id = 39637 AND producto_id = 8112 AND indice = 17;

    UPDATE prefactura_has_producto
    SET precio_unidad = 17.8700, sub_total = 857.7600, isv = 128.6600, total = 986.4200, updated_at = NOW()
    WHERE prefactura_id = 1961 AND producto_id = 8112 AND indice = 17;

    UPDATE venta_has_producto
    SET precio_unidad = 17.87,
        sub_total = 857.76,
        isv = 128.66,
        total = 986.42,
        sub_total_s = 857.76,
        isv_s = 128.66,
        total_s = 986.42,
        updated_at = NOW()
    WHERE factura_id = 29155 AND producto_id = 8112 AND indice = 17;

    -- Sincroniza los encabezados con la suma exacta de las lineas.
    UPDATE cotizacion
    SET sub_total = 90091.35,
        sub_total_grabado = 66057.52,
        sub_total_excento = 24033.83,
        isv = 9908.65,
        total = 100000.00,
        updated_at = NOW()
    WHERE id = 39637;

    UPDATE prefactura
    SET sub_total = 90091.3500,
        sub_total_grabado = 66057.5200,
        sub_total_excento = 24033.8300,
        isv = 9908.6500,
        total = 100000.0000,
        updated_at = NOW()
    WHERE id = 1961;

    UPDATE factura
    SET sub_total = 90091.35,
        sub_total_grabado = 66057.52,
        sub_total_excento = 24033.83,
        isv = 9908.65,
        total = 100000.00,
        credito = 100000.00,
        pendiente_cobro = 100000.00,
        updated_at = NOW()
    WHERE id = 29155;

    UPDATE aplicacion_pagos
    SET total_factura_cargo = 100000.00,
        retencion_isv_factura = 9908.65,
        saldo = 100000.00,
        updated_at = NOW()
    WHERE id = 36511 AND factura_id = 29155;

    -- Validacion final del detalle fisico usado por la impresion de factura.
    SELECT ROUND(SUM(sub_total_s), 2), ROUND(SUM(isv_s), 2), ROUND(SUM(total_s), 2)
    INTO v_subtotal, v_isv, v_total
    FROM venta_has_producto
    WHERE factura_id = 29155;

    IF v_subtotal <> 90091.35 OR v_isv <> 9908.65 OR v_total <> 100000.00 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La suma del detalle por lote no cuadra; se revirtio toda la transaccion.';
    END IF;

    SELECT ROUND(SUM(linea.sub_total), 2), ROUND(SUM(linea.isv), 2), ROUND(SUM(linea.total), 2)
    INTO v_subtotal, v_isv, v_total
    FROM (
        SELECT indice, MAX(sub_total) AS sub_total, MAX(isv) AS isv, MAX(total) AS total
        FROM venta_has_producto
        WHERE factura_id = 29155
        GROUP BY indice
    ) AS linea;

    IF v_subtotal <> 90091.35 OR v_isv <> 9908.65 OR v_total <> 100000.00 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La suma de lineas de la factura no cuadra; se revirtio toda la transaccion.';
    END IF;

    SELECT ROUND(SUM(sub_total), 2), ROUND(SUM(isv), 2), ROUND(SUM(total), 2)
    INTO v_subtotal, v_isv, v_total
    FROM cotizacion_has_producto
    WHERE cotizacion_id = 39637;

    IF v_subtotal <> 90091.35 OR v_isv <> 9908.65 OR v_total <> 100000.00 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La cotizacion no cuadra; se revirtio toda la transaccion.';
    END IF;

    SELECT ROUND(SUM(sub_total), 2), ROUND(SUM(isv), 2), ROUND(SUM(total), 2)
    INTO v_subtotal, v_isv, v_total
    FROM prefactura_has_producto
    WHERE prefactura_id = 1961;

    IF v_subtotal <> 90091.35 OR v_isv <> 9908.65 OR v_total <> 100000.00 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La prefactura no cuadra; se revirtio toda la transaccion.';
    END IF;

    COMMIT;

    SELECT
        f.id AS factura_id,
        f.numero_secuencia_cai,
        f.sub_total,
        f.sub_total_grabado,
        f.sub_total_excento,
        f.isv,
        f.total,
        ap.saldo
    FROM factura AS f
    LEFT JOIN aplicacion_pagos AS ap ON ap.factura_id = f.id AND ap.id = 36511
    WHERE f.id = 29155;
END$$

DELIMITER ;

CALL sp_ajustar_factura_000_001_01_00044071();
DROP PROCEDURE IF EXISTS sp_ajustar_factura_000_001_01_00044071;