-- Ejecutar en phpMyAdmin despues de seleccionar la base de datos de PROFAC.
-- Incluye todas las compras de ALCA (activas y anuladas).
-- Presenta el total de cada compra como importe exento, segun la regla solicitada.
SELECT
    prov.rtn AS `200-RTN`,
    'FA-factura' AS `600-Clase de documento`,
    c.codigo_cai AS `7-CAI`,
    c.numero_factura AS `8-No documento`,
    prov.nombre AS `71-Nombre del proveedor`,
    DATE_FORMAT(c.fecha_emision, '%d/%m/%Y') AS `900-Fecha emision`,
    DATE_FORMAT(c.fecha_emision, '%d/%m/%Y') AS `100-Fecha contable`,
    ROUND(c.total, 2) AS `110-Importe exento`,
    0.00 AS `1511-Importe base 15%`,
    ROUND(c.total, 2) AS `270-Monto al costo`,
    NULL AS `280-Monto al gasto`,
    c.estado_compra_id AS `Estado compra`
FROM compra AS c
INNER JOIN proveedores AS prov
    ON prov.id = c.proveedores_id
WHERE prov.rtn = '08019020234099'
ORDER BY c.fecha_emision, c.id;
