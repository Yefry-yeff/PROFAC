-- Corrige aplicaciones de pago creadas antes de agregar un vale a la factura.
-- Compatible con MySQL/MariaDB y ejecutable desde phpMyAdmin.
--
-- El saldo se ajusta por diferencia para conservar abonos, notas de crédito,
-- notas de débito, retenciones y otros movimientos aplicados posteriormente:
-- saldo_nuevo = saldo_actual + (total_factura - cargo_anterior)

SET @usuario_actualiza = 0;

-- 1. Vista previa de los registros que serán corregidos.
SELECT
    ap.id AS aplicacion_pago_id,
    f.id AS factura_id,
    f.cai AS numero_factura,
    f.cliente_id,
    ap.total_factura_cargo AS cargo_actual,
    f.total AS total_factura,
    ROUND(f.total - ap.total_factura_cargo, 2) AS diferencia_vale,
    ap.saldo AS saldo_actual,
    ROUND(ap.saldo + (f.total - ap.total_factura_cargo), 2) AS saldo_corregido
FROM aplicacion_pagos ap
INNER JOIN factura f ON f.id = ap.factura_id
WHERE ap.estado = 1
  AND f.estado_venta_id = 1
  AND ABS(f.total - ap.total_factura_cargo) >= 0.005
  AND EXISTS (
      SELECT 1
      FROM vale v
      WHERE v.factura_id = f.id
  )
ORDER BY f.id;

-- 2. Respaldo persistente de las filas originales.
-- INSERT IGNORE permite ejecutar el script nuevamente sin reemplazar el respaldo inicial.
CREATE TABLE IF NOT EXISTS backup_aplicacion_pagos_vales_20260728 LIKE aplicacion_pagos;

START TRANSACTION;

INSERT IGNORE INTO backup_aplicacion_pagos_vales_20260728
SELECT ap.*
FROM aplicacion_pagos ap
INNER JOIN factura f ON f.id = ap.factura_id
WHERE ap.estado = 1
  AND f.estado_venta_id = 1
  AND ABS(f.total - ap.total_factura_cargo) >= 0.005
  AND EXISTS (
      SELECT 1
      FROM vale v
      WHERE v.factura_id = f.id
  );

-- 3. Corrección del cargo, ISV y saldo sin perder movimientos existentes.
UPDATE aplicacion_pagos ap
INNER JOIN factura f ON f.id = ap.factura_id
SET ap.saldo = ROUND(ap.saldo + (f.total - ap.total_factura_cargo), 2),
    ap.total_factura_cargo = f.total,
    ap.retencion_isv_factura = f.isv,
    ap.ultimo_usr_actualizo = @usuario_actualiza,
    ap.updated_at = NOW()
WHERE ap.estado = 1
  AND f.estado_venta_id = 1
  AND ABS(f.total - ap.total_factura_cargo) >= 0.005
  AND EXISTS (
      SELECT 1
      FROM vale v
      WHERE v.factura_id = f.id
  );

SELECT ROW_COUNT() AS registros_actualizados;

COMMIT;

-- 4. Verificación: esta consulta debe devolver cero filas.
SELECT
    ap.id AS aplicacion_pago_id,
    f.id AS factura_id,
    f.cai AS numero_factura,
    ap.total_factura_cargo,
    f.total AS total_factura,
    ap.saldo
FROM aplicacion_pagos ap
INNER JOIN factura f ON f.id = ap.factura_id
WHERE ap.estado = 1
  AND f.estado_venta_id = 1
  AND ABS(f.total - ap.total_factura_cargo) >= 0.005
  AND EXISTS (
      SELECT 1
      FROM vale v
      WHERE v.factura_id = f.id
  )
ORDER BY f.id;

-- Restauración opcional de los campos modificados:
-- UPDATE aplicacion_pagos ap
-- INNER JOIN backup_aplicacion_pagos_vales_20260728 respaldo ON respaldo.id = ap.id
-- SET ap.total_factura_cargo = respaldo.total_factura_cargo,
--     ap.retencion_isv_factura = respaldo.retencion_isv_factura,
--     ap.saldo = respaldo.saldo,
--     ap.ultimo_usr_actualizo = respaldo.ultimo_usr_actualizo,
--     ap.updated_at = respaldo.updated_at;