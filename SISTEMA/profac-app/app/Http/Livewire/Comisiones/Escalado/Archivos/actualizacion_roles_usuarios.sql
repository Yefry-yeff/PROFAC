--USUARIOS QUE NO LABORAN EN VALENCIA
select id from users A where A.email in (
'francis.andino@distribucionesvalencia.hn',
'claudia.sabillon@distribucionesvalencia.hn',
'cristian.zelaya@distribucionesvalencia.hn',
'michael.irias@distribucionesvalencia.hn',
'williams.villalta@distribucionesvalencia.hn',
'oficina@distribucionesvalencia.hn',
'edwin.godoy@distribucionesvalencia.hn',
'jose.hernandez@distribucionesvalencia.hn',
'juan.betancourth@distribucionesvalencia.hn',
'wendy.munguia@distribucionesvalencia.hn',
'lester.cruz@distribucionesvalencia.hn',
'mayron.cabrera@distribucionesvalencia.hn',
'soary.arleth@distribucionesvalencia.hn',
'erling.peralta@distribucionesvalencia.hn',
'jeymi.varela@distribucionesvalencia.hn',
'olga.oseguera@distribucionesvalencia.hn',
'zully.garcia@distribucionesvalencia.hn',
'carlos.ponce@distribucionesvalencia.hn',
'nathaly.garcia@distribucionesvalencia.hn',
'heather.miranda@distribucionesvalencia.hn',
'alba.rodas@distribucionesvalencia.hn',
'mercedes.barahona@distribucionesvalencia.hn',
'daniel.dominguez@distribucionesvalencia.hn',
'carlos.alfaro@distribucionesvalencia.hn',
'ariana.villagra@distribucionesvalencia.hn',
'josue.cerritos@distribucionesvalencia.hn',
'zoila.martinez@distribucionesvalencia.hn',
'evelyn.paz@distribucionesvalencia.hn',
'glenda.martinez@distribucionesvalencia.hn',
'sofia.aguilera@distribucionesvalencia.hn',
'erick.sierra@distribucionesvalencia.hn',
'christian.estrada@distribucionesvalencia.hn',
'claudia.arias@distribucionesvalencia.hn',
'leonardo.barahona@distribucionesvalencia.hn',
'enis.pacheco@distribucionesvalencia.hn',
'alex.colindres@distribucionesvalencia.hn',
'carolina.oyuela@distribucionesvalencia.hn',
'natalia.quezada@distribucionesvalencia.hn',
'israel.galvez@distribucionesvalencia.hn',
'kevin.osorio@distribucionesvalencia.hn',
'andre.centeno@distribucionesvalencia.hn',
'scarleth.zepeda@distribucionesvalencia.hn',
'carlos.fernandez@distribucionesvalencia.hn'

);

--CREANDO ROL SIN ACCESO
INSERT INTO rol (nombre)
VALUES ('inaccesible');


INSERT INTO rol (nombre)
VALUES ('Equipo de Entrega');

--ACTUALUIZANDO TODO AL ROL SIN ACCESO
UPDATE users
SET rol_id = 10
WHERE email IN (
  'francis.andino@distribucionesvalencia.hn',
  'claudia.sabillon@distribucionesvalencia.hn',
  'cristian.zelaya@distribucionesvalencia.hn',
  'michael.irias@distribucionesvalencia.hn',
  'williams.villalta@distribucionesvalencia.hn',
  'oficina@distribucionesvalencia.hn',
  'edwin.godoy@distribucionesvalencia.hn',
  'jose.hernandez@distribucionesvalencia.hn',
  'juan.betancourth@distribucionesvalencia.hn',
  'wendy.munguia@distribucionesvalencia.hn',
  'lester.cruz@distribucionesvalencia.hn',
  'mayron.cabrera@distribucionesvalencia.hn',
  'soary.arleth@distribucionesvalencia.hn',
  'erling.peralta@distribucionesvalencia.hn',
  'jeymi.varela@distribucionesvalencia.hn',
  'olga.oseguera@distribucionesvalencia.hn',
  'zully.garcia@distribucionesvalencia.hn',
  'carlos.ponce@distribucionesvalencia.hn',
  'nathaly.garcia@distribucionesvalencia.hn',
  'heather.miranda@distribucionesvalencia.hn',
  'alba.rodas@distribucionesvalencia.hn',
  'mercedes.barahona@distribucionesvalencia.hn',
  'daniel.dominguez@distribucionesvalencia.hn',
  'carlos.alfaro@distribucionesvalencia.hn',
  'ariana.villagra@distribucionesvalencia.hn',
  'josue.cerritos@distribucionesvalencia.hn',
  'zoila.martinez@distribucionesvalencia.hn',
  'evelyn.paz@distribucionesvalencia.hn',
  'glenda.martinez@distribucionesvalencia.hn',
  'sofia.aguilera@distribucionesvalencia.hn',
  'erick.sierra@distribucionesvalencia.hn',
  'christian.estrada@distribucionesvalencia.hn',
  'claudia.arias@distribucionesvalencia.hn',
  'leonardo.barahona@distribucionesvalencia.hn',
  'enis.pacheco@distribucionesvalencia.hn',
  'alex.colindres@distribucionesvalencia.hn',
  'carolina.oyuela@distribucionesvalencia.hn',
  'natalia.quezada@distribucionesvalencia.hn',
  'israel.galvez@distribucionesvalencia.hn',
  'kevin.osorio@distribucionesvalencia.hn',
  'andre.centeno@distribucionesvalencia.hn',
  'scarleth.zepeda@distribucionesvalencia.hn',
  'carlos.fernandez@distribucionesvalencia.hn'
);



-----ACTUALIZACIÓN DE ROLES DE USUARIO por diferenciación en comisiones
UPDATE rol SET nombre = 'Asesor Comercial' where id = 2;
UPDATE rol SET nombre = 'Créditos y Cobros' where id = 4;
UPDATE rol SET nombre = 'Televendedor' where id = 3;
UPDATE rol SET nombre = 'Recursos Humanos' where id = 8;
UPDATE rol SET nombre = 'Administrador' where id = 1;

------------INSERCION MASIVA EN COMISION_EMPLEADO-----------------
INSERT INTO comision_empleado (comision_acumulada, fecha_ult_modificacion, mes_comision, nombre_empleado,users_comision, rol_id, estado_id)
SELECT 0.00, NOW(), DATE_FORMAT(NOW(), '%Y-%m-01'),A.name, A.id as 'user', B.id as 'rol', 1 as 'estado'
FROM users A
inner join rol B on B.id = A.rol_id
WHERE B.id != 10;

-------------------------ACTUALIZACIÓN DE SP DE APLICACION DE PAGOS/ REGISTRANDO FECHA EXACTA DE CIERRE PARA REFERENCIA DE COMISIÓN--------------
BEGIN
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
                pcliente_id,
                fa.id,
                fa.total,
                fa.isv,
                1,
                0,
                0,
                0,
                0,
                0,
                '',
                fa.total,
                0,
                1,
                0,
                0,
                NOW()
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
                pcliente_id,
                pfactura_id,
                fa.total,
                fa.isv,
                1,
                0,
                0,
                0,
                0,
                0,
                '',
                fa.total,
                0,
                1,
                0,
                0,
                NOW()
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
                pcliente_id,
                fa.id,
                fa.total,
                fa.isv,
                1,
                0,
                0,
                0,
                0,
                0,
                '',
                fa.total,
                0,
                1,
                0,
                0,
                NOW()
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
                    fecha_cierre_factura=NOW(),
                    comentario = pcomentario,
                    updated_at = NOW()
                WHERE aplicacion_pagos.estado = 1
                and aplicacion_pagos.id =  paplic_id;

				SET estado := 1;
				SET msjResultado := "Se ha guardado con exito";

                select estado,msjResultado;
        END IF;
END
