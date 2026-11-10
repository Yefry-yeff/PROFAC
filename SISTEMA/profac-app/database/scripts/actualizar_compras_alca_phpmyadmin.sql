-- Ejecutar solo si la base de datos aun conserva los totales originales.
-- Si ya se ejecuto una version anterior que puso total = sub_total,
-- restaure primero los valores originales desde un respaldo.
-- Haga un respaldo antes de ejecutar este script.
--
-- Este script conserva compra.total y cada precio_total de linea;
-- reclasifica ese importe completo como exento, con ISV cero.
-- En numero_factura, el correlativo CAI es el ultimo bloque despues del guion.
-- Ingrese uno o varios correlativos separados por coma, sin ceros a la izquierda.
-- Ejemplo: '1911' o '1911,1912,2050'. Vacio = no actualiza ninguna compra.
-- Aplica a todos los proveedores. Revise la consulta previa por posibles
-- coincidencias del mismo correlativo en proveedores o CAI diferentes.
-- El correlativo se compara como texto para evitar errores con facturas cuyo
-- numero tenga un separador "|" al final (por ejemplo, "1018|").

START TRANSACTION;

SET @secuencias_cai := '1911';

-- Verifique estos importes antes de actualizar. Total original debe ser mayor
-- que el subtotal cuando la compra tenia ISV.
SELECT
    c.id AS compra_id,
    prov.nombre AS proveedor,
    prov.rtn AS rtn_proveedor,
    c.codigo_cai,
    c.numero_factura,
    c.total AS total_que_se_conservara,
    c.sub_total AS subtotal_actual,
    c.isv_compra AS isv_actual
FROM compra AS c
INNER JOIN proveedores AS prov
    ON prov.id = c.proveedores_id
WHERE FIND_IN_SET(
      TRIM(LEADING '0' FROM REPLACE(SUBSTRING_INDEX(c.numero_factura, '-', -1), '|', '')),
      REPLACE(@secuencias_cai, ' ', '')
  ) > 0
ORDER BY c.fecha_emision, c.id;

UPDATE compra_has_producto AS detalle
INNER JOIN compra AS c
    ON c.id = detalle.compra_id
SET
    detalle.sub_total_producto = detalle.precio_total,
    detalle.isv = 0
WHERE FIND_IN_SET(
      TRIM(LEADING '0' FROM REPLACE(SUBSTRING_INDEX(c.numero_factura, '-', -1), '|', '')),
      REPLACE(@secuencias_cai, ' ', '')
  ) > 0;

UPDATE compra AS c
LEFT JOIN (
    SELECT compra_id, SUM(monto) AS total_pagado
    FROM pago_compra
    WHERE estado_id = 1
    GROUP BY compra_id
) AS pagos
    ON pagos.compra_id = c.id
SET
    c.sub_total = c.total,
    c.isv_compra = 0,
    c.debito = GREATEST(ROUND(c.total - COALESCE(pagos.total_pagado, 0), 2), 0)
WHERE FIND_IN_SET(
      TRIM(LEADING '0' FROM REPLACE(SUBSTRING_INDEX(c.numero_factura, '-', -1), '|', '')),
      REPLACE(@secuencias_cai, ' ', '')
  ) > 0;

-- Verifique que el total se haya conservado y el ISV sea cero.
SELECT
    COUNT(*) AS compras_seleccionadas,
    SUM(c.total) AS total_conservado,
    SUM(c.sub_total) AS importe_exento,
    SUM(c.isv_compra) AS isv_restante,
    SUM(c.debito) AS saldo_pendiente
FROM compra AS c
WHERE FIND_IN_SET(
      TRIM(LEADING '0' FROM REPLACE(SUBSTRING_INDEX(c.numero_factura, '-', -1), '|', '')),
      REPLACE(@secuencias_cai, ' ', '')
  ) > 0;

COMMIT;
