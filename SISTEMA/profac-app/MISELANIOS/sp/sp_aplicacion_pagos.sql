DROP PROCEDURE IF EXISTS `sp_aplicacion_pagos`;
DELIMITER $$
CREATE PROCEDURE `sp_aplicacion_pagos` (
    IN `accion` INT ,/*Obligatorio*/
    IN `pcliente_id` INT ,/*Obligatorio*/
    IN `usr_actual` INT ,/*Obligatorio*/
    IN `pfactura_id` INT,
    IN `pcomentario` VARCHAR(500),
    IN `paplic_id` INT,
    IN `ptipo` INT,
    IN `pmonto` DECIMAL(18,3),
   	OUT `estado` INT,
    OUT `msjResultado` VARCHAR(500)
)BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
				SET estado := -1;
				SET msjResultado := "Se ha hecho rollback";

               select estado,msjResultado;
  END;

  START TRANSACTION;

          IF accion = 1 THEN
            INSERT INTO aplicacion_pagos

            (
             cliente_id,
             factura_id,
             total_factura_cargo,
             retencion_isv_factura,
             estado_retencion_isv,
             total_notas_credito,
             total_nodas_debito,
             credito_abonos,
             movimiento_suma,
             movimiento_resta,
             comentario,
             saldo,
             ultimo_usr_actualizo,
             estado,
             estado_cerrado,
             usr_cerro,
             created_at)
            SELECT
                pcliente_id,     #idCliente
                fa.id,           #factura_id
                fa.total,        #total_factura_cargo
                fa.isv,          #retencion_isv_factura
                1,               #estado_retencion_isv
                0,               #total_notas_credito
                0,               #total_nodas_debito
                0,               #credito_abonos,
                0,               #movimiento_suma
                0,               #movimiento_resta
                '',              #comentario
                fa.total,        #saldo
                0,               #ultimo_usr_actualizo
                1,               #estado
                0,               #estado_cerrado
                0,               #usr_cerro
                NOW()            #created_at
            FROM factura fa
              inner join cliente cli on cli.id = fa.cliente_id
            WHERE cli.id = pcliente_id and fa.estado_venta_id = 1;
				SET estado := 1;
				SET msjResultado := "Se ha guardado con exito";

                select estado,msjResultado;
          END IF;

          IF accion = 2 THEN
            INSERT INTO aplicacion_pagos

            (
             cliente_id,
             factura_id,
             total_factura_cargo,
             retencion_isv_factura,
             estado_retencion_isv,
             total_notas_credito,
             total_nodas_debito,
             credito_abonos,
             movimiento_suma,
             movimiento_resta,
             comentario,
             saldo,
             ultimo_usr_actualizo,
             estado,
             estado_cerrado,
             usr_cerro,
             created_at)
            SELECT
                pcliente_id,     #idCliente
                pfactura_id,     #factura_id
                fa.total,        #total_factura_cargo
                fa.isv,          #retencion_isv_factura
                1,               #estado_retencion_isv
                0,               #total_notas_credito
                0,               #total_nodas_debito
                0,               #credito_abonos,
                0,               #movimiento_suma
                0,               #movimiento_resta
                '',              #comentario
                fa.total,        #saldo
                0,               #ultimo_usr_actualizo
                1,               #estado
                0,               #estado_cerrado
                0,               #usr_cerro
                NOW()            #created_at
            FROM factura fa
            WHERE fa.estado_venta_id = 1 and fa.id = pfactura_id;
				SET estado := 1;
				SET msjResultado := "Se ha guardado con exito";

                select estado,msjResultado;
          END IF;

          IF accion = 3 THEN
            INSERT INTO aplicacion_pagos

            (
             cliente_id,
             factura_id,
             total_factura_cargo,
             retencion_isv_factura,
             estado_retencion_isv,
             total_notas_credito,
             total_nodas_debito,
             credito_abonos,
             movimiento_suma,
             movimiento_resta,
             comentario,
             saldo,
             ultimo_usr_actualizo,
             estado,
             estado_cerrado,
             usr_cerro,
             created_at)
            SELECT
                pcliente_id,     #idCliente
                fa.id,           #factura_id
                fa.total,        #total_factura_cargo
                fa.isv,          #retencion_isv_factura
                1,               #estado_retencion_isv
                0,               #total_notas_credito
                0,               #total_nodas_debito
                0,               #credito_abonos,
                0,               #movimiento_suma
                0,               #movimiento_resta
                '',              #comentario
                fa.total,        #saldo
                0,               #ultimo_usr_actualizo
                1,               #estado
                0,               #estado_cerrado
                0,               #usr_cerro
                NOW()            #created_at
            FROM factura fa
              inner join cliente cli on cli.id = fa.cliente_id
            WHERE cli.id = pcliente_id
            and fa.estado_venta_id = 1
            and fa.id not in (
                select aplicacion_pagos.factura_id
                from aplicacion_pagos
                where aplicacion_pagos.estado = 1
            );
				SET estado := 1;
				SET msjResultado := "Se ha guardado con exito";

                select estado,msjResultado;
          END IF;

  COMMIT;

        IF accion = 4 THEN

            IF ptipo = 1 THEN
                UPDATE aplicacion_pagos
                    SET estado_retencion_isv = ptipo,
                     comentario_retencion = pcomentario,
                     ultimo_usr_actualizo = usr_actual,
                     retencion_aplicada = 1
                WHERE aplicacion_pagos.retencion_aplicada = 0 and aplicacion_pagos.estado = 1 and aplicacion_pagos.id =  paplic_id;
            END IF;

            IF ptipo = 2 THEN
                UPDATE aplicacion_pagos
                    SET estado_retencion_isv = ptipo,
                     saldo = (saldo - pmonto),
                     comentario_retencion = pcomentario,
                     ultimo_usr_actualizo = usr_actual,
                     retencion_aplicada = 1
                WHERE aplicacion_pagos.retencion_aplicada = 0 and aplicacion_pagos.estado = 1 and aplicacion_pagos.id =  paplic_id;
            END IF;

				SET estado := 1;
				SET msjResultado := "Se ha guardado con exito";

                select estado,msjResultado;
        END IF;

        IF accion = 5 THEN

            IF ptipo = 1 THEN

                UPDATE nota_credito
                SET estado_rebajado = 1,
                    user_registra_rebaja = usr_actual,
                    fecha_rebajado = NOW(),
                    comentario_rebajado = pcomentario
                WHERE nota_credito.id = pcliente_id
                and nota_credito.factura_id = pfactura_id;

                UPDATE aplicacion_pagos
                    SET total_notas_credito = (total_notas_credito + pmonto),
                        saldo = (saldo - pmonto)
                WHERE aplicacion_pagos.estado = 1
                and aplicacion_pagos.factura_id = pfactura_id
                and aplicacion_pagos.id =  paplic_id;

            END IF;

            IF ptipo = 2 THEN

                UPDATE nota_credito
                SET estado_rebajado = 1,
                    user_registra_rebaja = usr_actual,
                    fecha_rebajado = NOW(),
                    comentario_rebajado = pcomentario
                WHERE nota_credito.id = pcliente_id
                and nota_credito.factura_id = pfactura_id;

            END IF;

				SET estado := 1;
				SET msjResultado := "Se ha guardado con exito";

                select estado,msjResultado;
        END IF;

        IF accion = 6 THEN

            IF ptipo = 1 THEN

                UPDATE notadebito
                SET estado_sumado = 1,
                    user_registra_sumado = usr_actual,
                    fecha_sumado = NOW(),
                    comentario_sumado = pcomentario
                WHERE notadebito.id = pcliente_id
                and notadebito.factura_id = pfactura_id;

                UPDATE aplicacion_pagos
                    SET total_nodas_debito = (total_nodas_debito + pmonto),
                        saldo = (saldo + pmonto)
                WHERE aplicacion_pagos.estado = 1
                and aplicacion_pagos.factura_id = pfactura_id
                and aplicacion_pagos.id =  paplic_id;

            END IF;

            IF ptipo = 2 THEN

                UPDATE notadebito
                SET estado_sumado = 1,
                    user_registra_sumado = usr_actual,
                    fecha_sumado = NOW(),
                    comentario_sumado = pcomentario
                WHERE notadebito.id = pcliente_id
                and notadebito.factura_id = pfactura_id;

            END IF;

				SET estado := 1;
				SET msjResultado := "Se ha guardado con exito";

                select estado,msjResultado;
        END IF;

        IF accion = 7 THEN

            IF ptipo = 1 THEN
                UPDATE aplicacion_pagos
                    SET movimiento_suma = (movimiento_suma + pmonto),
                    ultimo_usr_actualizo = usr_actual,
                    saldo = (saldo + pmonto),
                    updated_at = NOW()
                WHERE aplicacion_pagos.estado = 1
                and aplicacion_pagos.factura_id = pfactura_id
                and aplicacion_pagos.id =  paplic_id;

            END IF;

            IF ptipo = 2 THEN


                UPDATE aplicacion_pagos
                    SET movimiento_resta = (movimiento_resta + pmonto),
                    ultimo_usr_actualizo = usr_actual,
                    saldo = (saldo - pmonto),
                    updated_at = NOW()
                WHERE aplicacion_pagos.estado = 1
                and aplicacion_pagos.factura_id = pfactura_id
                and aplicacion_pagos.id =  paplic_id;

            END IF;

				SET estado := 1;
				SET msjResultado := "Se ha guardado con exito";

                select estado,msjResultado;
        END IF;

        IF accion = 8 THEN
                UPDATE aplicacion_pagos
                    SET credito_abonos = (credito_abonos + pmonto),
                    ultimo_usr_actualizo = usr_actual,
                    saldo = (saldo - pmonto),
                    updated_at = NOW()
                WHERE aplicacion_pagos.estado = 1
                and aplicacion_pagos.factura_id = pfactura_id
                and aplicacion_pagos.id =  paplic_id;

				SET estado := 1;
				SET msjResultado := "Se ha guardado con exito";

                select estado,msjResultado;
        END IF;

        IF accion = 9 THEN

                UPDATE aplicacion_pagos
                    SET
                    ultimo_usr_actualizo = usr_actual,
                    usr_cerro = usr_actual,
                    estado_cerrado = 2,
                    comentario = pcomentario,
                    updated_at = NOW()
                WHERE aplicacion_pagos.estado = 1
                and aplicacion_pagos.id =  paplic_id;

				SET estado := 1;
				SET msjResultado := "Se ha guardado con exito";

                select estado,msjResultado;
        END IF;
END$$
DELIMITER ;
