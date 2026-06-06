DROP PROCEDURE IF EXISTS `estadoCuenta_sp`;
DELIMITER $$
CREATE PROCEDURE `estadoCuenta_sp` (IN `idcliente` INT)   BEGIN

	SET @acumulado = 0;

	select
        factura.nombre_cliente as 'cliente',
        factura.numero_factura as 'numero_factura',
        factura.numero_factura as 'documento',
    	factura.cai as 'correlativo',
        factura.fecha_emision as 'fecha_emision',
        factura.fecha_vencimiento as 'fecha_vencimiento',
            (
                        IF  (
                             (
                                SELECT COUNT(*) FROM numero_orden_compra WHERE id = factura.numero_orden_compra_id
                             ) = 0
                             , 'N/A'
                             ,(SELECT numero_orden FROM numero_orden_compra WHERE id = 				   factura.numero_orden_compra_id)

                            )

                        ) as 'numOrden',
        aplicacion_pagos.total_factura_cargo as 'cargo',
        aplicacion_pagos.credito_abonos as 'credito',
        aplicacion_pagos.total_notas_credito as 'notaCredito',
        aplicacion_pagos.total_nodas_debito as 'notaDebito',
        aplicacion_pagos.movimiento_suma  as 'extra',
        aplicacion_pagos.movimiento_resta  as 'debita',
        aplicacion_pagos.saldo as 'saldo',
            @acumulado := @acumulado + aplicacion_pagos.saldo AS 'Acumulado'

    from aplicacion_pagos
    inner join factura on factura.id = aplicacion_pagos.factura_id
    where factura.estado_venta_id = 1
    and aplicacion_pagos.estado = 1
    and aplicacion_pagos.cliente_id = idcliente
    and aplicacion_pagos.saldo > 0
    order by factura.fecha_emision ASC;

END$$
DELIMITER ;
