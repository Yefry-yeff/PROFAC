/*
 Reparacion controlada de la factura 000-001-01-00044288.

 - Cierra la aplicacion de pago con la fecha real del ajuste: 2026-09-10.
 - Genera las comisiones con la fecha efectiva del pago: 2026-08-26.
 - No aplica mora porque el pago fue realizado dentro de los 7 dias de gracia.
 - Es idempotente y se limita a la factura, aplicacion, abono y movimiento auditados.

 USO:
 1. Ejecutar sin cambios con @APLICAR = 0 para validar y previsualizar.
 2. Revisar el resultado.
 3. Cambiar @APLICAR = 1 y ejecutar nuevamente para confirmar.
*/

SET @APLICAR = 0;

DROP PROCEDURE IF EXISTS reparar_factura_44288;

DELIMITER $$

CREATE PROCEDURE reparar_factura_44288()
main: BEGIN
    DECLARE v_factura_id INT DEFAULT 29372;
    DECLARE v_aplicacion_id INT DEFAULT 37004;
    DECLARE v_abono_id INT DEFAULT 29001;
    DECLARE v_movimiento_id INT DEFAULT 248;
    DECLARE v_fecha_pago DATE DEFAULT '2026-08-26';
    DECLARE v_fecha_cierre DATETIME DEFAULT '2026-09-10 09:41:53';
    DECLARE v_monto_abono DECIMAL(15,2) DEFAULT 63924.70;
    DECLARE v_monto_movimiento DECIMAL(15,2) DEFAULT 0.85;
    DECLARE v_usuario_cierre INT DEFAULT 78;
    DECLARE v_count INT DEFAULT 0;
    DECLARE v_estado_cerrado INT DEFAULT 0;
    DECLARE v_saldo DECIMAL(15,4) DEFAULT 0;
    DECLARE v_movimiento_resta DECIMAL(15,2) DEFAULT 0;
    DECLARE v_done INT DEFAULT 0;
    DECLARE v_user_id INT;
    DECLARE v_rol_id INT;
    DECLARE v_tipo_comision INT;
    DECLARE v_monto_rol DECIMAL(18,4);
    DECLARE v_factura_comision_id INT;
    DECLARE v_comision_empleado_id INT;

    DECLARE cur_targets CURSOR FOR
        SELECT user_id, rol_id, tipo_comision, ROUND(SUM(monto_comision), 4)
        FROM tmp_44288_lineas
        GROUP BY user_id, rol_id, tipo_comision
        HAVING ROUND(SUM(monto_comision), 4) > 0
        ORDER BY rol_id;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = 1;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        DROP TEMPORARY TABLE IF EXISTS tmp_44288_lineas;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT COUNT(*)
      INTO v_count
      FROM factura
     WHERE id = v_factura_id
       AND cai = '000-001-01-00044288'
       AND users_id = 27
       AND vendedor = 24
       AND gestor_entrega = 27;

    IF v_count <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La factura 29372/44288 no coincide con la asignacion auditada.';
    END IF;

    SELECT COUNT(*), MAX(estado_cerrado), MAX(saldo), MAX(movimiento_resta)
      INTO v_count, v_estado_cerrado, v_saldo, v_movimiento_resta
      FROM aplicacion_pagos
     WHERE id = v_aplicacion_id
       AND factura_id = v_factura_id
       AND estado = 1
     FOR UPDATE;

    IF v_count <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La aplicacion de pago 37004 no existe o no esta activa.';
    END IF;

    SELECT COUNT(*)
      INTO v_count
      FROM facturas_comision
     WHERE factura_id = v_factura_id
       AND estado_id = 1
     FOR UPDATE;

    IF v_estado_cerrado = 2 AND v_count > 0 THEN
        ROLLBACK;
        SELECT
            'YA_REPARADA' AS estado,
            v_factura_id AS factura_id,
            v_count AS comisiones_activas,
            'No se realizo ningun cambio.' AS detalle;
        LEAVE main;
    END IF;

    IF v_estado_cerrado = 2 AND v_count = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La aplicacion esta cerrada pero no tiene comisiones activas.';
    END IF;

    IF v_estado_cerrado <> 2 AND v_count > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La aplicacion esta abierta pero ya tiene comisiones activas.';
    END IF;

    IF ABS(v_saldo) > 0.0001 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El saldo de la aplicacion no es cero.';
    END IF;

    IF ABS(v_movimiento_resta - v_monto_movimiento) > 0.0001 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El acumulado movimiento_resta no coincide con L 0.85.';
    END IF;

    SELECT COUNT(*)
      INTO v_count
      FROM abonos_creditos
     WHERE id = v_abono_id
       AND aplicacion_pagos_id = v_aplicacion_id
       AND factura_id = v_factura_id
       AND estado_abono = 1
       AND ABS(monto_abonado - v_monto_abono) <= 0.0001
       AND DATE(fecha_pago) = v_fecha_pago
     FOR UPDATE;

    IF v_count <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El abono 29001 no coincide en estado, monto o fecha.';
    END IF;

    SELECT COUNT(*)
      INTO v_count
      FROM otros_movimientos
     WHERE id = v_movimiento_id
       AND aplicacion_pagos_id = v_aplicacion_id
       AND factura_id = v_factura_id
       AND estado = 1
       AND tipo_movimiento = 2
       AND ABS(monto - v_monto_movimiento) <= 0.0001
       AND usr_registro = v_usuario_cierre
       AND created_at = v_fecha_cierre
     FOR UPDATE;

    IF v_count <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El movimiento 248 no coincide con el ajuste auditado.';
    END IF;

    SELECT COUNT(*)
      INTO v_count
      FROM nota_credito
     WHERE factura_id = v_factura_id
       AND estado_nota_id = 1;

    IF v_count <> 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La factura tiene notas de credito activas no contempladas.';
    END IF;

    SELECT COUNT(*)
      INTO v_count
      FROM comision_periodo
     WHERE periodo = '2026-08-01'
       AND estado = 1;

    IF v_count > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El periodo de agosto 2026 esta conciliado; no se puede acreditar.';
    END IF;

    DROP TEMPORARY TABLE IF EXISTS tmp_44288_lineas;

    CREATE TEMPORARY TABLE tmp_44288_lineas
    ENGINE=InnoDB
    AS
    SELECT
        t.user_id,
        t.rol_id,
        t.tipo_comision,
        p.producto_id,
        p.precios_producto_carga_id,
        p.cantidad,
        p.precio_para_comision AS precio_venta,
        ROUND(
            (ce.porcentaje_comision / 100)
            * p.precio_para_comision
            * p.cantidad,
            4
        ) AS monto_comision
    FROM (
        SELECT 27 AS user_id, 3 AS rol_id, 1 AS tipo_comision
        UNION ALL
        SELECT 24 AS user_id, 2 AS rol_id, 3 AS tipo_comision
        UNION ALL
        SELECT 27 AS user_id, 16 AS rol_id, 4 AS tipo_comision
    ) t
    INNER JOIN (
        SELECT
            vp.factura_id,
            vp.producto_id,
            vp.seccion_id,
            vp.unidad_medida_venta_id,
            vp.precios_producto_carga_id,
            ppc.categoria_precios_id,
            COALESCE(SUM(vp.cantidad_s), SUM(vp.cantidad)) AS cantidad,
            COALESCE(NULLIF(vp.precioSeleccionado, 0), vp.precio_unidad) AS precio_para_comision
        FROM venta_has_producto vp
        INNER JOIN precios_producto_carga ppc
                ON ppc.id = vp.precios_producto_carga_id
        WHERE vp.factura_id = 29372
        GROUP BY
            vp.factura_id,
            vp.producto_id,
            vp.seccion_id,
            vp.unidad_medida_venta_id,
            vp.precios_producto_carga_id,
            vp.precio_unidad,
            vp.precioSeleccionado,
            ppc.categoria_precios_id
    ) p
    INNER JOIN factura f
            ON f.id = p.factura_id
    INNER JOIN cliente cl
            ON cl.id = f.cliente_id
    INNER JOIN comision_escala ce
            ON ce.rol_id = t.rol_id
           AND ce.cliente_categoria_escala_id = cl.cliente_categoria_escala_id
           AND ce.categoria_precios_id = p.categoria_precios_id
           AND ce.estado_id = 1
           AND ce.id = (
               SELECT MAX(ce2.id)
               FROM comision_escala ce2
               WHERE ce2.rol_id = ce.rol_id
                 AND ce2.cliente_categoria_escala_id = ce.cliente_categoria_escala_id
                 AND ce2.categoria_precios_id = ce.categoria_precios_id
                 AND ce2.estado_id = 1
           )
    LEFT JOIN comision_rol_config crc
           ON crc.rol_id = t.rol_id
    WHERE COALESCE(crc.calcular, 1) = 1
      AND p.cantidad > 0.005;

    SELECT COUNT(DISTINCT producto_id, precios_producto_carga_id, precio_venta)
      INTO v_count
      FROM tmp_44288_lineas;

    IF v_count <> 39 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La cantidad de lineas comerciales ya no coincide con las 39 auditadas.';
    END IF;

    IF COALESCE(@APLICAR, 0) <> 1 THEN
        SELECT
            'SIMULACION' AS estado,
            v_factura_id AS factura_id,
            v_fecha_pago AS fecha_efectiva_comision,
            v_fecha_cierre AS fecha_real_cierre,
            user_id,
            rol_id,
            tipo_comision,
            COUNT(*) AS lineas,
            ROUND(SUM(monto_comision), 4) AS comision
        FROM tmp_44288_lineas
        GROUP BY user_id, rol_id, tipo_comision
        ORDER BY rol_id;

        ROLLBACK;
        DROP TEMPORARY TABLE IF EXISTS tmp_44288_lineas;
        LEAVE main;
    END IF;

    UPDATE aplicacion_pagos
       SET saldo = 0,
           estado_cerrado = 2,
           usr_cerro = v_usuario_cierre,
           fecha_cierre_factura = v_fecha_cierre,
           comentario = 'CIERRE POR OTRO MOVIMIENTO: CENTAVOS DE MAS',
           ultimo_usr_actualizo = v_usuario_cierre,
           updated_at = NOW()
     WHERE id = v_aplicacion_id;

    OPEN cur_targets;

    target_loop: LOOP
        FETCH cur_targets
         INTO v_user_id, v_rol_id, v_tipo_comision, v_monto_rol;

        IF v_done = 1 THEN
            LEAVE target_loop;
        END IF;

        INSERT INTO facturas_comision
        (
            fecha_cierre_factura,
            monto_rol,
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
        VALUES
        (
            v_fecha_pago,
            v_monto_rol,
            v_factura_id,
            NULL,
            v_aplicacion_id,
            v_rol_id,
            v_tipo_comision,
            1,
            1,
            NOW(),
            NOW()
        );

        SET v_factura_comision_id = LAST_INSERT_ID();

        INSERT INTO producto_comision
        (
            cantidad,
            precio_venta,
            monto_comision,
            precios_producto_carga_id,
            factura_id,
            producto_id,
            rol_id,
            estado_id,
            facturas_comision_id,
            created_at,
            updated_at
        )
        SELECT
            cantidad,
            precio_venta,
            monto_comision,
            precios_producto_carga_id,
            v_factura_id,
            producto_id,
            v_rol_id,
            1,
            v_factura_comision_id,
            NOW(),
            NOW()
        FROM tmp_44288_lineas
        WHERE user_id = v_user_id
          AND rol_id = v_rol_id
          AND tipo_comision = v_tipo_comision;

        SELECT MAX(id)
          INTO v_comision_empleado_id
          FROM comision_empleado
         WHERE users_comision = v_user_id
           AND rol_id = v_rol_id
           AND mes_comision = '2026-08-01'
           AND estado_id = 1
         FOR UPDATE;

        IF v_comision_empleado_id IS NULL THEN
            INSERT INTO comision_empleado
            (
                comision_acumulada,
                fecha_ult_modificacion,
                mes_comision,
                nombre_empleado,
                users_comision,
                rol_id,
                estado_id,
                created_at,
                updated_at
            )
            SELECT
                v_monto_rol,
                NOW(),
                '2026-08-01',
                u.name,
                v_user_id,
                v_rol_id,
                1,
                NOW(),
                NOW()
            FROM users u
            WHERE u.id = v_user_id;
        ELSE
            UPDATE comision_empleado
               SET comision_acumulada = comision_acumulada + v_monto_rol,
                   fecha_ult_modificacion = NOW(),
                   updated_at = NOW()
             WHERE id = v_comision_empleado_id;
        END IF;
    END LOOP;

    CLOSE cur_targets;

    COMMIT;

    SELECT
        'APLICADO' AS estado,
        fc.id AS facturas_comision_id,
        CASE fc.tipo_comision
            WHEN 1 THEN 27
            WHEN 3 THEN 24
            WHEN 4 THEN 27
        END AS user_id,
        fc.rol_id,
        fc.tipo_comision,
        fc.fecha_cierre_factura AS fecha_comision,
        fc.monto_rol
    FROM facturas_comision fc
    WHERE fc.factura_id = v_factura_id
      AND fc.estado_id = 1
    ORDER BY fc.rol_id;

    DROP TEMPORARY TABLE IF EXISTS tmp_44288_lineas;
END$$

DELIMITER ;

CALL reparar_factura_44288();

DROP PROCEDURE IF EXISTS reparar_factura_44288;
