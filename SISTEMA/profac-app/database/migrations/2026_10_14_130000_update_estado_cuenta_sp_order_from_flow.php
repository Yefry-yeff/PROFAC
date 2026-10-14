<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateEstadoCuentaSpOrderFromFlow extends Migration
{
    public function up()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS estadoCuenta_sp');

        DB::unprepared(<<<'SQL'
CREATE PROCEDURE estadoCuenta_sp(IN idcliente INT)
BEGIN
    SET @acumulado = 0;

    SELECT
        (SELECT nombre FROM cliente WHERE id = idcliente) AS cliente,
        factura.numero_factura AS numero_factura,
        factura.numero_factura AS documento,
        factura.cai AS correlativo,
        factura.fecha_emision AS fecha_emision,
        factura.fecha_vencimiento AS fecha_vencimiento,
        COALESCE(
            NULLIF(numero_orden_compra.numero_orden, ''),
            (
                SELECT NULLIF(flujo.numero_orden_compra, '')
                FROM historico_flujo
                INNER JOIN flujo ON flujo.id = historico_flujo.flujo_id
                WHERE historico_flujo.tipo_tramite_id = 3
                  AND historico_flujo.tramite_id = factura.id
                  AND flujo.numero_orden_compra IS NOT NULL
                  AND flujo.numero_orden_compra <> ''
                ORDER BY historico_flujo.id DESC
                LIMIT 1
            ),
            'N/A'
        ) AS numOrden,
        aplicacion_pagos.total_factura_cargo AS cargo,
        aplicacion_pagos.credito_abonos AS credito,
        aplicacion_pagos.total_notas_credito AS notaCredito,
        aplicacion_pagos.total_nodas_debito AS notaDebito,
        aplicacion_pagos.movimiento_suma AS extra,
        aplicacion_pagos.movimiento_resta AS debita,
        aplicacion_pagos.saldo AS saldo,
        @acumulado := @acumulado + aplicacion_pagos.saldo AS Acumulado
    FROM aplicacion_pagos
    INNER JOIN factura ON factura.id = aplicacion_pagos.factura_id
    LEFT JOIN numero_orden_compra
        ON numero_orden_compra.id = factura.numero_orden_compra_id
    WHERE factura.estado_venta_id = 1
      AND aplicacion_pagos.estado = 1
      AND aplicacion_pagos.cliente_id = idcliente
    ORDER BY factura.created_at ASC;
END
SQL);
    }

    public function down()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS estadoCuenta_sp');

        DB::unprepared(<<<'SQL'
CREATE PROCEDURE estadoCuenta_sp(IN idcliente INT)
BEGIN
    SET @acumulado = 0;

    SELECT
        (SELECT nombre FROM cliente WHERE id = idcliente) AS cliente,
        factura.numero_factura AS numero_factura,
        factura.numero_factura AS documento,
        factura.cai AS correlativo,
        factura.fecha_emision AS fecha_emision,
        factura.fecha_vencimiento AS fecha_vencimiento,
        (
            IF(
                (SELECT COUNT(*) FROM numero_orden_compra WHERE id = factura.numero_orden_compra_id) = 0,
                'N/A',
                (SELECT numero_orden FROM numero_orden_compra WHERE id = factura.numero_orden_compra_id)
            )
        ) AS numOrden,
        aplicacion_pagos.total_factura_cargo AS cargo,
        aplicacion_pagos.credito_abonos AS credito,
        aplicacion_pagos.total_notas_credito AS notaCredito,
        aplicacion_pagos.total_nodas_debito AS notaDebito,
        aplicacion_pagos.movimiento_suma AS extra,
        aplicacion_pagos.movimiento_resta AS debita,
        aplicacion_pagos.saldo AS saldo,
        @acumulado := @acumulado + aplicacion_pagos.saldo AS Acumulado
    FROM aplicacion_pagos
    INNER JOIN factura ON factura.id = aplicacion_pagos.factura_id
    WHERE factura.estado_venta_id = 1
      AND aplicacion_pagos.estado = 1
      AND aplicacion_pagos.cliente_id = idcliente
    ORDER BY factura.created_at ASC;
END
SQL);
    }
}
